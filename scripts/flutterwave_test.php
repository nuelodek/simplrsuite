<?php
declare(strict_types=1);
require_once __DIR__ . '/../app/flutterwave.php';
require_once __DIR__ . '/../app/modules.php';
if (PHP_SAPI !== 'cli' || config('APP_ENV') !== 'local' || config('MAIL_TRANSPORT') !== 'log') exit(2);
$fixture = json_decode(file_get_contents(SIMPLR_ROOT . '/.local/browser-fixture.json'),true);
if (!preg_match('/^alpha-[0-9]+@example\.test$/D',$fixture['email'] ?? '')) exit(3);
$db = database(); $user = db_one($db,'SELECT * FROM users WHERE email = ?',[$fixture['email']]);
putenv('PAYSTACK_MODE=test'); putenv('FLUTTERWAVE_ENABLED=true'); putenv('FLUTTERWAVE_SECRET_KEY=FLWSECK_TEST-localfixtureonly-X'); putenv('FLUTTERWAVE_WEBHOOK_HASH=local-fixture-hash-only-not-a-real-secret'); putenv('BILLING_USD_RATE=0.00063'); putenv('BILLING_ENFORCE=true');
$checks=[]; $calls=[];
function check_flw(bool $ok,string $name): void { global $checks; $checks[]=['name'=>$name,'passed'=>$ok]; if (!$ok) throw new RuntimeException($name); }
function reject_flw(callable $run,string $class,string $name): void { try {$run();} catch(Throwable $e) {check_flw($e instanceof $class,$name);return;} check_flw(false,$name); }
$initialize=function($method,$path,$payload)use(&$calls){
    $calls[]=[$method,$path];
    check_flw($method==='POST' && $path==='/payments','Flutterwave uses hosted server checkout');
    check_flw($payload['currency']==='USD' && $payload['amount']==='2.21','USD amount uses decimal dollars, not minor units');
    check_flw(str_starts_with($payload['redirect_url'],'http://127.0.0.1:8000/'),'Callback uses configured local origin');
    $expected=hash('sha256',$payload['amount'].'USD'.$payload['customer']['email'].$payload['tx_ref'].hash('sha256',config('FLUTTERWAVE_SECRET_KEY')));
    check_flw(hash_equals($expected,$payload['payload_hash']),'Checkout payload hash binds stored amount, currency, customer and reference');
    return ['status'=>'success','data'=>['link'=>'https://checkout.flutterwave.com/v3/hosted/pay/local-fixture']];
};
try {
    check_flw(flutterwave_ready(),'Flutterwave test configuration recognized');
    check_flw(billing_quote(['project'=>'1'],'USD')['amount']===221,'Source conversion rounds one project month to USD 2.21');
    check_flw(billing_quote(['project'=>'2'],'USD')['amount']===441,'Source conversion rounds the cart once: two project months USD 4.41');
    check_flw(billing_quote(['project'=>'1','strategy'=>'1'],'USD')['amount']===441,'Combined cart avoids independent line-rounding overcharge');
    check_flw(billing_quote(['payroll'=>'1'],'USD')['amount']===189,'Source finance price converts to USD 1.89');
    reject_flw(fn()=>billing_quote(['project'=>'1'],'EUR'),InvalidArgumentException::class,'Unsupported currency rejected');
    foreach (['https://example.test/pay','http://checkout.flutterwave.com/pay','https://checkout.flutterwave.com.evil.test/pay','https://user@checkout.flutterwave.com/pay','https://checkout.flutterwave.com:443/pay'] as $url) check_flw(!flutterwave_checkout_url($url),'Untrusted checkout URL rejected');
    foreach ([null,[],true,'1e3','2.211','-2','NaN'] as $amount) check_flw(flutterwave_minor_units($amount)===null,'Invalid provider amount rejected');
    check_flw(flutterwave_minor_units(2.21)===221 && flutterwave_minor_units('2')===200,'Provider decimal amounts convert exactly to cents');
    $nonce=bin2hex(random_bytes(32));
    $order=create_billing_order($db,$user,['project'=>'1'],$nonce,$initialize,'USD');
    check_flw($order['provider']==='flutterwave' && $order['currency']==='USD' && (int)$order['amount_minor']===221,'Order snapshots provider and dollar price');
    check_flw(str_contains($order['items_json'],'0.00063'),'Order snapshots original conversion rate');
    check_flw(create_billing_order($db,$user,['project'=>'1'],$nonce,$initialize,'USD')['id']===$order['id'] && count($calls)===1,'Duplicate form uses one checkout');
    putenv('PAYSTACK_ENABLED=true');putenv('PAYSTACK_SECRET_KEY=sk_test_fixtureonlynotarealkey');
    reject_flw(fn()=>create_billing_order($db,$user,['project'=>'1'],$nonce,$initialize,'NGN'),InvalidArgumentException::class,'Used nonce cannot change currency or provider');
    $paystack=json_decode(file_get_contents(SIMPLR_ROOT.'/.local/billing-test-fixture.json'),true);
    $naira=db_one($db,'SELECT * FROM billing_orders WHERE reference=?',[$paystack['reference']]);
    // Provider transaction IDs may coincide; only provider+mode+ID is unique.
    $payment=['id'=>$naira['provider_transaction_id'],'tx_ref'=>$order['reference'],'amount'=>2.21,'currency'=>'USD','status'=>'successful','customer'=>['email'=>$user['email']],'created_at'=>gmdate('Y-m-d\TH:i:s\Z')];
    foreach (['amount'=>2.20,'currency'=>'NGN','tx_ref'=>'forged','id'=>'invalid','created_at'=>'yesterday'] as $field=>$value) {
        $bad=array_replace($payment,[$field=>$value]);
        reject_flw(fn()=>verify_billing_order($db,$order,fn()=>['status'=>'success','data'=>$bad]),PaymentMismatch::class,'Provider mismatch rejected: '.$field);
    }
    $before=db_one($db,'SELECT valid_until FROM billing_entitlements WHERE company_id=? AND tool_key=? AND provider_mode=?',[$user['company_id'],'project','test'])['valid_until'];
    $verify=function($method,$path,$data)use($order,$payment){check_flw($method==='GET' && $path==='/transactions/verify_by_reference?tx_ref='.$order['reference'],'Verification uses stored reference');return ['status'=>'success','data'=>$payment];};
    check_flw(verify_billing_order($db,$order,$verify)==='paid','Verified dollar payment fulfills');
    $after=db_one($db,'SELECT valid_until FROM billing_entitlements WHERE company_id=? AND tool_key=? AND provider_mode=?',[$user['company_id'],'project','test'])['valid_until'];
    check_flw($after===billing_add_months(new DateTimeImmutable($before,new DateTimeZone('UTC')),1)->format('Y-m-d H:i:s'),'Dollar renewal extends existing Paystack access');
    check_flw(verify_billing_order($db,db_one($db,'SELECT * FROM billing_orders WHERE id=?',[$order['id']]),fn()=>throw new RuntimeException('No API call expected'))==='paid','Duplicate paid confirmation makes no provider call');
    check_flw(db_one($db,'SELECT valid_until FROM billing_entitlements WHERE company_id=? AND tool_key=? AND provider_mode=?',[$user['company_id'],'project','test'])['valid_until']===$after,'Duplicate cannot extend access twice');
    $other=create_billing_order($db,$user,['project'=>'1'],bin2hex(random_bytes(32)),$initialize,'USD');
    $duplicate=array_replace($payment,['tx_ref'=>$other['reference']]);
    reject_flw(fn()=>verify_billing_order($db,$other,fn()=>['status'=>'success','data'=>$duplicate]),PaymentMismatch::class,'Flutterwave ID cannot fulfill two Flutterwave orders');
    check_flw(!flutterwave_valid_signature('') && !flutterwave_valid_signature('forged') && flutterwave_valid_signature(config('FLUTTERWAVE_WEBHOOK_HASH')),'Webhook validates configured secret hash');
    putenv('PAYSTACK_MODE=live');check_flw(!flutterwave_ready(),'Test credential cannot enable live checkout');
    check_flw(!billing_module_active($db,(int)$user['company_id'],'projects'),'Test dollar payment cannot unlock live editing');
    putenv('PAYSTACK_MODE=test');putenv('BILLING_USD_RATE=bad');check_flw(!flutterwave_ready(),'Invalid conversion configuration disables dollar checkout');
    putenv('BILLING_USD_RATE=0.00063');
    file_put_contents(SIMPLR_ROOT.'/.local/flutterwave-test-fixture.json',json_encode(['reference'=>$order['reference'],'company_id'=>$user['company_id']]));
} finally {
    $report=['passed'=>count(array_filter($checks,fn($c)=>$c['passed'])),'total'=>count($checks),'checks'=>$checks,'provider_calls'=>'simulated only'];
    file_put_contents(SIMPLR_ROOT.'/work/test-results/flutterwave.json',json_encode($report,JSON_PRETTY_PRINT));
    echo json_encode(array_intersect_key($report,array_flip(['passed','total','provider_calls']))) . PHP_EOL;
}
