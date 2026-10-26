<?php
declare(strict_types=1);
require_once __DIR__ . '/paystack.php';

function flutterwave_ready(): bool {
    $mode = config('PAYSTACK_MODE','test'); // Both providers share one entitlement environment.
    $key = config('FLUTTERWAVE_SECRET_KEY');
    if (config('FLUTTERWAVE_ENABLED','false') !== 'true' || !in_array($mode,['test','live'],true)) return false;
    if (!preg_match($mode === 'test' ? '/^FLWSECK_TEST-[a-zA-Z0-9-]+$/D' : '/^FLWSECK-[a-zA-Z0-9-]+$/D',$key)) return false;
    if (strlen(config('FLUTTERWAVE_WEBHOOK_HASH')) < 32) return false;
    if ($mode === 'live' && (config('APP_ENV') !== 'production' || !str_starts_with(config('APP_URL'),'https://'))) return false;
    try { billing_usd_rate(); } catch (InvalidArgumentException $error) { return false; }
    return extension_loaded('curl');
}
function flutterwave_api(string $method, string $path, array $data = [], ?callable $transport = null): array {
    if (!flutterwave_ready()) throw new PaymentUnavailable('Flutterwave checkout is not configured for this environment.');
    if ($transport !== null) {
        if (PHP_SAPI !== 'cli') throw new LogicException('Test transports are CLI-only.');
        $response = $transport($method,$path,$data);
    } else {
        $handle = curl_init('https://api.flutterwave.com/v3' . $path);
        curl_setopt_array($handle,[CURLOPT_RETURNTRANSFER=>true,CURLOPT_CUSTOMREQUEST=>$method,CURLOPT_HTTPHEADER=>['Authorization: Bearer ' . config('FLUTTERWAVE_SECRET_KEY'),'Content-Type: application/json'],CURLOPT_CONNECTTIMEOUT=>4,CURLOPT_TIMEOUT=>12,CURLOPT_FOLLOWLOCATION=>false,CURLOPT_PROTOCOLS=>CURLPROTO_HTTPS,CURLOPT_SSL_VERIFYPEER=>true,CURLOPT_SSL_VERIFYHOST=>2]);
        if ($method === 'POST') curl_setopt($handle,CURLOPT_POSTFIELDS,json_encode($data,JSON_THROW_ON_ERROR));
        $raw = curl_exec($handle); $status = (int)curl_getinfo($handle,CURLINFO_RESPONSE_CODE); curl_close($handle);
        if (!is_string($raw) || $status < 200 || $status >= 300 || strlen($raw)>1048576) throw new PaymentUnavailable('Flutterwave could not confirm this request. Check its status before trying again.');
        $response = json_decode($raw,true,512,JSON_BIGINT_AS_STRING);
    }
    if (!is_array($response) || ($response['status'] ?? '') !== 'success' || !is_array($response['data'] ?? null)) throw new PaymentUnavailable('Flutterwave did not return a valid response.');
    return $response['data'];
}
function flutterwave_checkout_url(mixed $url): bool {
    if (!is_string($url) || !filter_var($url,FILTER_VALIDATE_URL) || strlen($url)>512) return false;
    $parts = parse_url($url);
    return $parts['scheme'] === 'https' && $parts['host'] === 'checkout.flutterwave.com' && !isset($parts['user']) && !isset($parts['pass']) && !isset($parts['port']) && !isset($parts['fragment']);
}
function flutterwave_initialize(array $quote, string $reference, string $email, string $callback, ?callable $transport = null): mixed {
    $amount = intdiv($quote['amount'],100) . '.' . str_pad((string)($quote['amount']%100),2,'0',STR_PAD_LEFT);
    $hash = hash('sha256',$amount . 'USD' . $email . $reference . hash('sha256',config('FLUTTERWAVE_SECRET_KEY')));
    $data = flutterwave_api('POST','/payments',['tx_ref'=>$reference,'amount'=>$amount,'currency'=>'USD','redirect_url'=>$callback,'customer'=>['email'=>$email],'customizations'=>['title'=>'Simplr Suite','description'=>'Prepaid workspace toolkits'],'payload_hash'=>$hash],$transport);
    return $data['link'] ?? null;
}
function flutterwave_minor_units(mixed $amount): ?int {
    if (!is_int($amount) && !is_string($amount) && !is_float($amount)) return null;
    if (!preg_match('/^[0-9]{1,10}(?:\.[0-9]{1,2})?$/D',(string)$amount)) return null;
    $parts = explode('.',(string)$amount);
    return (int)$parts[0]*100 + (int)str_pad($parts[1] ?? '',2,'0');
}
function flutterwave_verify_order(mysqli $db, array $order, ?callable $transport = null): string {
    if ($order['provider'] !== 'flutterwave' || $order['currency'] !== 'USD' || $order['provider_mode'] !== config('PAYSTACK_MODE','test')) throw new PaymentMismatch('The payment provider or environment does not match this order.');
    // The stored reference chooses the verification request. Browser transaction
    // IDs, redirect status and webhook amounts never determine fulfillment.
    $verified = flutterwave_api('GET','/transactions/verify_by_reference?tx_ref=' . rawurlencode($order['reference']),[],$transport);
    if (($verified['status'] ?? '') !== 'successful') return 'pending';
    return fulfill_billing_order($db,$order,[
        'provider'=>'flutterwave','id'=>$verified['id'] ?? null,'status'=>'success',
        'reference'=>$verified['tx_ref'] ?? null,'amount'=>flutterwave_minor_units($verified['amount'] ?? null),
        'currency'=>$verified['currency'] ?? null,'customer'=>$verified['customer'] ?? null,
        // v3 uses environment-specific keys, not a `domain` field. The request
        // uses the validated key for the stored order's environment above.
        'domain'=>$order['provider_mode'],'paid_at'=>$verified['created_at'] ?? null,
    ]);
}
function flutterwave_valid_signature(string $signature): bool {
    $secret = config('FLUTTERWAVE_WEBHOOK_HASH');
    return strlen($secret)>=32 && hash_equals($secret,$signature);
}
