<?php
declare(strict_types=1);
require_once __DIR__ . '/database.php';
require_once __DIR__ . '/outbound.php';
require_once __DIR__ . '/billing-catalog.php';
require_once __DIR__ . '/audiences.php';

class PaymentUnavailable extends RuntimeException {}
class PaymentMismatch extends RuntimeException {}
require_once __DIR__ . '/flutterwave.php';

function billing_provider(string $currency): string {
    return match($currency) { 'NGN'=>'paystack', 'USD'=>'flutterwave', default=>throw new InvalidArgumentException('Choose NGN or USD.') };
}
function billing_provider_ready(string $provider): bool {
    return match($provider) { 'paystack'=>paystack_ready(), 'flutterwave'=>flutterwave_ready(), default=>false };
}
function billing_checkout_url(string $provider, mixed $url): bool {
    return match($provider) { 'paystack'=>paystack_checkout_url($url), 'flutterwave'=>flutterwave_checkout_url($url), default=>false };
}

function billing_provider_config(string $provider, string $setting): string {
    if (!in_array($provider,['PAYSTACK','FLUTTERWAVE'],true) || !in_array($setting,['SECRET_KEY','WEBHOOK_HASH'],true)) return '';
    $mode = config('PAYSTACK_MODE','test');
    if (!in_array($mode,['test','live'],true)) return '';
    $modeKey = $provider . '_' . strtoupper($mode) . '_' . $setting;
    $legacyKey = $provider . '_' . $setting;
    $runtimeModeValue = getenv($modeKey);
    if ($runtimeModeValue !== false) return $runtimeModeValue;
    $runtimeLegacyValue = getenv($legacyKey);
    if ($runtimeLegacyValue !== false) return $runtimeLegacyValue;
    global $simplrConfig;
    if (array_key_exists($modeKey,$simplrConfig)) return $simplrConfig[$modeKey];
    return $simplrConfig[$legacyKey] ?? '';
}

function paystack_secret_key(): string {
    return billing_provider_config('PAYSTACK','SECRET_KEY');
}
function paystack_ready(): bool {
    $mode = config('PAYSTACK_MODE','test');
    if (config('PAYSTACK_ENABLED','false') !== 'true' || !in_array($mode,['test','live'],true)) return false;
    if (!preg_match('/^sk_' . $mode . '_[a-zA-Z0-9]+$/D',paystack_secret_key())) return false;
    if ($mode === 'live' && (config('APP_ENV') !== 'production' || !str_starts_with(config('APP_URL'),'https://'))) return false;
    return extension_loaded('curl');
}
function paystack_api(string $method, string $path, array $data = [], ?callable $transport = null): array {
    if (!paystack_ready()) throw new PaymentUnavailable('Paystack checkout is not configured for this environment.');
    if ($transport !== null) {
        if (PHP_SAPI !== 'cli') throw new LogicException('Test transports are CLI-only.');
        $response = $transport($method,$path,$data);
    } else {
        $handle = secure_curl(curl_init('https://api.paystack.co' . $path));
        curl_setopt_array($handle,[CURLOPT_RETURNTRANSFER=>true,CURLOPT_CUSTOMREQUEST=>$method,CURLOPT_HTTPHEADER=>['Authorization: Bearer ' . paystack_secret_key(),'Content-Type: application/json'],CURLOPT_CONNECTTIMEOUT=>4,CURLOPT_TIMEOUT=>12,CURLOPT_FOLLOWLOCATION=>false,CURLOPT_PROTOCOLS=>CURLPROTO_HTTPS,CURLOPT_SSL_VERIFYPEER=>true,CURLOPT_SSL_VERIFYHOST=>2]);
        if ($method === 'POST') curl_setopt($handle,CURLOPT_POSTFIELDS,json_encode($data,JSON_THROW_ON_ERROR));
        $raw = curl_exec($handle); $status = (int)curl_getinfo($handle,CURLINFO_RESPONSE_CODE); curl_close($handle);
        if (!is_string($raw) || $status<200 || $status>=300 || strlen($raw)>1048576) throw new PaymentUnavailable('Paystack could not confirm this request. Check its status before trying again.');
        $response = json_decode($raw,true,512,JSON_BIGINT_AS_STRING);
    }
    if (!is_array($response) || ($response['status'] ?? null) !== true || !is_array($response['data'] ?? null)) throw new PaymentUnavailable('Paystack did not return a valid response.');
    return $response['data'];
}
function paystack_checkout_url(mixed $url): bool {
    if (!is_string($url) || !filter_var($url,FILTER_VALIDATE_URL) || strlen($url)>512) return false;
    $parts = parse_url($url);
    return $parts['scheme'] === 'https' && $parts['host'] === 'checkout.paystack.com' && !isset($parts['user']) && !isset($parts['pass']) && !isset($parts['port']) && !isset($parts['fragment']);
}
function create_billing_order(mysqli $db, array $user, array $selection, string $nonce, ?callable $transport = null, string $currency = 'NGN'): array {
    $provider = billing_provider($currency);
    if (!billing_provider_ready($provider)) throw new PaymentUnavailable(ucfirst($provider) . ' checkout is not configured for this environment.');
    if (!preg_match('/^[a-f0-9]{64}$/D',$nonce)) throw new InvalidArgumentException('Reload the checkout form before continuing.');
    $quote = billing_quote($selection,$currency); $company = (int)$user['company_id'];
    $items = json_encode($quote['items'],JSON_THROW_ON_ERROR);
    $db->begin_transaction();
    try {
        db_one($db,'SELECT id FROM users WHERE id = ? AND company_id = ? FOR UPDATE',[$user['id'],$company]);
        $order = db_one($db,'SELECT * FROM billing_orders WHERE company_id = ? AND request_nonce = ? FOR UPDATE',[$company,$nonce]);
        if ($order) {
            if ($order['provider_mode'] !== config('PAYSTACK_MODE','test') || $order['provider'] !== $provider || $order['currency'] !== $currency) throw new InvalidArgumentException('The currency or payment environment has changed. Reload the checkout form before continuing.');
            if ($order['items_json'] !== $items) throw new InvalidArgumentException('This checkout form has already been used for another selection. Reload before changing it.');
            $db->commit(); return $order;
        }
        $reference = 'ss-' . bin2hex(random_bytes(20));
        db_run($db,'INSERT INTO billing_orders (company_id,created_by,reference,request_nonce,expected_email,amount_minor,currency,provider,provider_mode,items_json) VALUES (?,?,?,?,?,?,?,?,?,?)',[$company,$user['id'],$reference,$nonce,strtolower($user['email']),$quote['amount'],$currency,$provider,config('PAYSTACK_MODE','test'),$items]);
        $id = (int)$db->insert_id; $db->commit();
    } catch (Throwable $error) { $db->rollback(); throw $error; }
    try {
        $callback = audience_url('business','/users/admin/billing-return.php');
        // Local audience_url can be relative; provider callbacks need an origin.
        if (!str_starts_with($callback,'https://') && !str_starts_with($callback,'http://')) $callback = rtrim(config('APP_URL'),'/') . '/users/admin/billing-return.php';
        if ($provider === 'flutterwave') {
            $url = flutterwave_initialize($quote,$reference,strtolower($user['email']),$callback,$transport);
        } else {
            $result = paystack_api('POST','/transaction/initialize',['email'=>strtolower($user['email']),'amount'=>(string)$quote['amount'],'currency'=>'NGN','reference'=>$reference,'callback_url'=>$callback,'metadata'=>['simplr_reference'=>$reference,'company_id'=>(string)$company]],$transport);
            if (($result['reference'] ?? '') !== $reference) throw new PaymentUnavailable('Paystack returned an unexpected reference.');
            $url = $result['authorization_url'] ?? null;
        }
        if (!billing_checkout_url($provider,$url)) throw new PaymentUnavailable('The payment service returned an unexpected checkout address.');
        db_run($db,"UPDATE billing_orders SET checkout_url = ?, state = IF(state = 'paid',state,'awaiting_payment') WHERE id = ?",[$url,$id]);
    } catch (Throwable $error) {
        db_run($db,"UPDATE billing_orders SET state = 'initialization_unknown' WHERE id = ? AND state = 'initializing'",[$id]);
        if ($error instanceof PaymentUnavailable) throw $error;
        throw new PaymentUnavailable('Checkout could not be initialized. Please review your payment history before starting again.');
    }
    return db_one($db,'SELECT * FROM billing_orders WHERE id = ?',[$id]);
}
function billing_add_months(DateTimeImmutable $date, int $months): DateTimeImmutable {
    $target = $date->modify('first day of this month')->modify('+' . $months . ' months');
    return $target->setDate((int)$target->format('Y'),(int)$target->format('m'),min((int)$date->format('d'),(int)$target->format('t')));
}
function fulfill_billing_order(mysqli $db, array $order, array $verified): string {
    if (($verified['status'] ?? '') !== 'success') return 'pending';
    $transactionId = $verified['id'] ?? null; $amount = $verified['amount'] ?? null;
    $paid = $verified['paid_at'] ?? $verified['paidAt'] ?? null;
    $email = $verified['customer']['email'] ?? null;
    $valid = ($verified['provider'] ?? 'paystack') === ($order['provider'] ?? 'paystack') && (is_int($transactionId) || is_string($transactionId)) && preg_match('/^[1-9][0-9]{0,19}$/D',(string)$transactionId)
        && (is_int($amount) || (is_string($amount) && ctype_digit($amount))) && (string)$amount === (string)$order['amount_minor']
        && ($verified['reference'] ?? '') === $order['reference'] && ($verified['currency'] ?? '') === $order['currency']
        && ($verified['domain'] ?? '') === $order['provider_mode'] && is_string($email) && strtolower($email) === $order['expected_email']
        && is_string($paid) && preg_match('/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}(?:\.\d+)?(?:Z|[+-]\d{2}:\d{2})$/D',$paid);
    if (!$valid) {
        db_run($db,'INSERT INTO billing_events (order_id,event_type,result) VALUES (?,?,?)',[$order['id'],'verification','mismatch']);
        throw new PaymentMismatch('Payment details do not match this order. Access was not changed.');
    }
    try { $paidAt = (new DateTimeImmutable($paid))->setTimezone(new DateTimeZone('UTC')); } catch (Exception $error) { throw new PaymentMismatch('The provider payment time is invalid.'); }
    $dateErrors = DateTimeImmutable::getLastErrors();
    if ($dateErrors && ($dateErrors['warning_count'] || $dateErrors['error_count'])) throw new PaymentMismatch('The provider payment time is invalid.');
    if ($paidAt->getTimestamp()>time()+300 || $paidAt->format('Y-m-d H:i:s') < $order['created_at']) throw new PaymentMismatch('The provider payment time is outside this order.');
    $db->begin_transaction();
    try {
        // One company lock serializes extensions and prevents duplicate fulfillment.
        db_one($db,'SELECT id FROM users WHERE id = ? AND company_id = ? FOR UPDATE',[$order['created_by'],$order['company_id']]);
        $current = db_one($db,'SELECT * FROM billing_orders WHERE id = ? FOR UPDATE',[$order['id']]);
        if ($current['state'] === 'paid') { $db->commit(); return 'paid'; }
        if (db_one($db,'SELECT id FROM billing_orders WHERE provider = ? AND provider_mode = ? AND provider_transaction_id = ? AND id != ?',[$order['provider'] ?? 'paystack',$order['provider_mode'],(string)$transactionId,$order['id']])) throw new PaymentMismatch('This payment is already linked to another order.');
        $items = json_decode($current['items_json'],true,512,JSON_THROW_ON_ERROR);
        foreach ($items as $item) {
            $access = db_one($db,'SELECT valid_until FROM billing_entitlements WHERE company_id = ? AND tool_key = ? AND provider_mode = ? FOR UPDATE',[$order['company_id'],$item['key'],$order['provider_mode']]);
            $base = $access && $access['valid_until'] > $paidAt->format('Y-m-d H:i:s') ? new DateTimeImmutable($access['valid_until'],new DateTimeZone('UTC')) : $paidAt;
            $until = billing_add_months($base,(int)$item['months'])->format('Y-m-d H:i:s');
            db_run($db,'INSERT INTO billing_entitlements (company_id,tool_key,provider_mode,valid_until,last_order_id) VALUES (?,?,?,?,?) ON DUPLICATE KEY UPDATE valid_until = VALUES(valid_until), last_order_id = VALUES(last_order_id)',[$order['company_id'],$item['key'],$order['provider_mode'],$until,$order['id']]);
        }
        db_run($db,"UPDATE billing_orders SET state = 'paid', provider_transaction_id = ?, paid_at = ?, fulfilled_at = UTC_TIMESTAMP() WHERE id = ?",[(string)$transactionId,$paidAt->format('Y-m-d H:i:s'),$order['id']]);
        db_run($db,'INSERT INTO billing_events (order_id,event_type,result) VALUES (?,?,?)',[$order['id'],'fulfillment','paid']);
        $db->commit(); return 'paid';
    } catch (Throwable $error) { $db->rollback(); throw $error; }
}
function verify_billing_order(mysqli $db, array $order, ?callable $transport = null): string {
    if ($order['provider_mode'] !== config('PAYSTACK_MODE','test')) throw new PaymentUnavailable('This payment belongs to a different payment environment.');
    if ($order['state'] === 'paid') return 'paid';
    if ($order['provider'] === 'flutterwave') return flutterwave_verify_order($db,$order,$transport);
    $verified = paystack_api('GET','/transaction/verify/' . rawurlencode($order['reference']),[],$transport);
    return fulfill_billing_order($db,$order,$verified);
}
function paystack_valid_signature(string $body, string $signature): bool {
    $key = paystack_secret_key();
    return $key !== '' && preg_match('/^[a-f0-9]{128}$/D',$signature) && hash_equals(hash_hmac('sha512',$body,$key),$signature);
}
