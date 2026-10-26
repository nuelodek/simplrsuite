<?php
// Provider simulation only. Every API call receives a CLI-only fake transport.
declare(strict_types=1);
require_once __DIR__ . '/../app/paystack.php';
require_once __DIR__ . '/../app/modules.php';
if (PHP_SAPI !== 'cli' || config('APP_ENV') !== 'local' || config('MAIL_TRANSPORT') !== 'log') exit(2);
$fixture = json_decode(file_get_contents(SIMPLR_ROOT . '/.local/browser-fixture.json'),true);
if (!preg_match('/^alpha-[0-9]+@example\.test$/D',$fixture['email'] ?? '')) exit(3);
$db = database(); $user = db_one($db,'SELECT * FROM users WHERE email = ?',[$fixture['email']]);
$_SESSION = ['auth_role'=>'admin','user_id'=>$user['id'],'auth_stamp'=>hash('sha256',$user['password'])];
putenv('PAYSTACK_ENABLED=true'); putenv('PAYSTACK_MODE=test'); putenv('PAYSTACK_SECRET_KEY=sk_test_fixtureonlynotarealkey'); putenv('BILLING_ENFORCE=true');
$checks = []; $calls = [];
function verify_check(bool $ok, string $label): void { global $checks; $checks[] = ['name'=>$label,'passed'=>$ok]; if (!$ok) throw new RuntimeException($label); }
function rejects(callable $run, string $class, string $label): void { try { $run(); } catch (Throwable $error) { verify_check($error instanceof $class,$label); return; } verify_check(false,$label); }
$initialize = function($method,$path,$payload) use (&$calls) {
    $calls[] = [$method,$path];
    verify_check($method === 'POST' && $path === '/transaction/initialize','Initialization uses server endpoint');
    verify_check($payload['currency'] === 'NGN' && ctype_digit($payload['amount']) && str_starts_with($payload['reference'],'ss-'),'Initialization sends integer minor units and generated reference');
    verify_check(str_starts_with($payload['callback_url'],'http://127.0.0.1:8000/'),'Callback uses configured origin');
    return ['status'=>true,'data'=>['reference'=>$payload['reference'],'authorization_url'=>'https://checkout.paystack.com/local-test-only']];
};
try {
    verify_check(paystack_ready(),'Test configuration is recognized');
    $quote = billing_quote(['project'=>'2','recruiting'=>'1']);
    verify_check($quote['amount'] === 950000 && $quote['currency'] === 'NGN','Prices come from server in kobo');
    foreach ([[],['project'=>'-1'],['project'=>'13'],['project'=>'1.5'],['unknown'=>'1'],['project'=>[]]] as $selection) rejects(fn()=>billing_quote($selection),InvalidArgumentException::class,'Invalid cart rejected');
    foreach (['https://example.test/pay','http://checkout.paystack.com/test','https://checkout.paystack.com.evil.test/a','https://user@checkout.paystack.com/a','https://checkout.paystack.com:443/a','javascript:alert(1)'] as $url) verify_check(!paystack_checkout_url($url),'Checkout URL rejected: ' . parse_url($url,PHP_URL_SCHEME));
    verify_check(paystack_checkout_url('https://checkout.paystack.com/abc'),'Expected hosted checkout URL accepted');
    $nonce = bin2hex(random_bytes(32));
    $order = create_billing_order($db,$user,['project'=>'1'],$nonce,$initialize);
    verify_check($order['state'] === 'awaiting_payment' && (int)$order['amount_minor'] === 350000 && (int)$order['company_id'] === (int)$user['company_id'],'Order snapshots price and authenticated company');
    $again = create_billing_order($db,$user,['project'=>'1'],$nonce,$initialize);
    verify_check($order['id'] === $again['id'] && count($calls) === 1,'Duplicate form creates one provider checkout');
    rejects(fn()=>create_billing_order($db,$user,['project'=>'2'],$nonce,$initialize),InvalidArgumentException::class,'Used checkout nonce cannot change its cart');
    $payment = ['id'=>(string)random_int(1000000000,2000000000),'domain'=>'test','status'=>'success','reference'=>$order['reference'],'amount'=>350000,'currency'=>'NGN','customer'=>['email'=>$user['email']],'paid_at'=>gmdate('Y-m-d\TH:i:s\Z')];
    foreach (['amount'=>1,'currency'=>'USD','domain'=>'live','reference'=>'ss-wrong','id'=>'1.2','paid_at'=>'invalid'] as $field=>$value) rejects(fn()=>fulfill_billing_order($db,$order,array_replace($payment,[$field=>$value])),PaymentMismatch::class,'Mismatched ' . $field . ' never grants access');
    rejects(fn()=>fulfill_billing_order($db,$order,array_replace($payment,['id'=>true])),PaymentMismatch::class,'Boolean transaction identity is rejected');
    rejects(fn()=>fulfill_billing_order($db,$order,array_replace($payment,['paid_at'=>'2026-02-31T12:00:00Z'])),PaymentMismatch::class,'Invalid calendar payment date is rejected');
    rejects(fn()=>fulfill_billing_order($db,$order,array_replace($payment,['customer'=>['email'=>'other@example.test']])),PaymentMismatch::class,'Mismatched customer email never grants access');
    verify_check(fulfill_billing_order($db,$order,array_replace($payment,['status'=>'failed'])) === 'pending','Failed payment never grants access');
    $verifier = function($method,$path,$payload) use ($payment,$order) {
        verify_check($method === 'GET' && $path === '/transaction/verify/' . $order['reference'],'Verification requests stored reference');
        return ['status'=>true,'data'=>$payment];
    };
    verify_check(verify_billing_order($db,$order,$verifier) === 'paid','Verified payment fulfills order');
    $until = db_one($db,'SELECT valid_until FROM billing_entitlements WHERE company_id = ? AND tool_key = ? AND provider_mode = ?',[$user['company_id'],'project','test'])['valid_until'];
    verify_check(fulfill_billing_order($db,$order,$payment) === 'paid','Callback and webhook duplicate is idempotent');
    verify_check(db_one($db,'SELECT valid_until FROM billing_entitlements WHERE company_id = ? AND tool_key = ? AND provider_mode = ?',[$user['company_id'],'project','test'])['valid_until'] === $until,'Duplicate payment does not extend subscription twice');
    verify_check(billing_module_active($db,(int)$user['company_id'],'projects'),'Paid toolkit enables its module');
    $different = create_billing_order($db,$user,['project'=>'1'],bin2hex(random_bytes(32)),$initialize);
    rejects(fn()=>fulfill_billing_order($db,$different,array_replace($payment,['reference'=>$different['reference']])),PaymentMismatch::class,'Provider transaction cannot fulfill a second order');
    $renewalPayment = array_replace($payment,['id'=>(string)random_int(2000000001,3000000000),'reference'=>$different['reference'],'paid_at'=>gmdate('Y-m-d\TH:i:s\Z')]);
    verify_check(fulfill_billing_order($db,$different,$renewalPayment) === 'paid','Renewal payment fulfills');
    $renewed = db_one($db,'SELECT valid_until FROM billing_entitlements WHERE company_id = ? AND tool_key = ? AND provider_mode = ?',[$user['company_id'],'project','test'])['valid_until'];
    verify_check($renewed === billing_add_months(new DateTimeImmutable($until,new DateTimeZone('UTC')),1)->format('Y-m-d H:i:s'),'Renewal preserves unused subscription time');
    verify_check(billing_add_months(new DateTimeImmutable('2026-01-31 12:00:00',new DateTimeZone('UTC')),1)->format('Y-m-d H:i:s') === '2026-02-28 12:00:00','Monthly extension clamps end-of-month date');
    putenv('PAYSTACK_MODE=live');
    verify_check(!paystack_ready(),'Live payments cannot run in a local environment');
    verify_check(!billing_module_active($db,(int)$user['company_id'],'projects'),'Test payment cannot enable live entitlements');
    putenv('PAYSTACK_MODE=test');
    $raw = '{"event":"charge.success","data":{"reference":"test"}}';
    $signature = hash_hmac('sha512',$raw,config('PAYSTACK_SECRET_KEY'));
    verify_check(paystack_valid_signature($raw,$signature),'Valid raw-body HMAC is accepted');
    verify_check(!paystack_valid_signature($raw . ' ',$signature),'Altered payload is rejected');
    verify_check(!paystack_valid_signature($raw,str_repeat('0',128)),'Forged signature is rejected');
    verify_check(!paystack_valid_signature($raw,''),'Missing signature is rejected');
    $timeoutNonce = bin2hex(random_bytes(32));
    rejects(fn()=>create_billing_order($db,$user,['office'=>'1'],$timeoutNonce,fn()=>throw new PaymentUnavailable('simulated timeout')),PaymentUnavailable::class,'Transport timeout is handled');
    $timeout = create_billing_order($db,$user,['office'=>'1'],$timeoutNonce,fn()=>throw new LogicException('Must not retry initialization'));
    verify_check($timeout['state'] === 'initialization_unknown','Uncertain initialization is retained, not duplicated');
    rejects(fn()=>create_billing_order($db,$user,['office'=>'1'],bin2hex(random_bytes(32)),fn($m,$p,$data)=>['status'=>true,'data'=>['reference'=>$data['reference'],'authorization_url'=>'https://evil.test/checkout']]),PaymentUnavailable::class,'Provider response cannot create an open redirect');
    $concurrent = create_billing_order($db,$user,['product'=>'1'],bin2hex(random_bytes(32)),$initialize);
    $concurrentPayment = array_replace($payment,['id'=>(string)random_int(3000000001,4000000000),'reference'=>$concurrent['reference'],'amount'=>400000,'paid_at'=>gmdate('Y-m-d\TH:i:s\Z')]);
    file_put_contents(SIMPLR_ROOT . '/.local/billing-concurrency-fixture.json',json_encode(['reference'=>$concurrent['reference'],'payment'=>$concurrentPayment,'email'=>$user['email']]));
    putenv('PAYSTACK_ENABLED=false');
    rejects(fn()=>create_billing_order($db,$user,['project'=>'1'],bin2hex(random_bytes(32)),$initialize),PaymentUnavailable::class,'Disabled integration cannot initialize checkout');
    file_put_contents(SIMPLR_ROOT . '/.local/billing-test-fixture.json',json_encode(['reference'=>$order['reference'],'company_id'=>$user['company_id']]));
} finally {
    $report = ['passed'=>count(array_filter($checks,fn($c)=>$c['passed'])),'total'=>count($checks),'checks'=>$checks,'provider_calls'=>'simulated only'];
    file_put_contents(SIMPLR_ROOT . '/work/test-results/paystack.json',json_encode($report,JSON_PRETTY_PRINT));
    echo json_encode(['passed'=>$report['passed'],'total'=>$report['total'],'provider_calls'=>'simulated only']) . PHP_EOL;
}
