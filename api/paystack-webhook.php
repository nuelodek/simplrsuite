<?php
require_once __DIR__ . '/../app/paystack.php';
require_once __DIR__ . '/../app/payroll.php';
if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') { header('Allow: POST'); json_response(['ok'=>false],405); }
if (!paystack_ready()) json_response(['ok'=>false,'error'=>'Payment processing is unavailable.'],503);
$body = file_get_contents('php://input',false,null,0,262145);
if (!is_string($body) || strlen($body)>262144) json_response(['ok'=>false],413);
if (!paystack_valid_signature($body,$_SERVER['HTTP_X_PAYSTACK_SIGNATURE'] ?? '')) json_response(['ok'=>false],401);
$event = json_decode($body,true,512,JSON_BIGINT_AS_STRING);
if (!is_array($event)) json_response(['ok'=>false],400);
if (in_array(($event['event'] ?? ''),['transfer.success','transfer.failed','transfer.reversed'],true)) {
    $db = database();
    json_response(['ok'=>true,'result'=>payroll_transfer_webhook($db,(string)$event['event'],is_array($event['data'] ?? null) ? $event['data'] : []) ? 'processed' : 'ignored']);
}
if (($event['event'] ?? '') !== 'charge.success') json_response(['ok'=>true,'result'=>'ignored']);
$reference = $event['data']['reference'] ?? null;
if (is_string($reference) && preg_match('/^ss-vault-[a-f0-9]{40}$/D',$reference)) {
    try {
        $credited = salary_vault_verify_paystack_funding(database(),$reference);
        json_response(['ok'=>true,'result'=>$credited ? 'credited' : 'ignored']);
    } catch (PaymentMismatch $error) {
        json_response(['ok'=>true,'result'=>'review_required']);
    } catch (PaymentUnavailable $error) {
        json_response(['ok'=>false,'error'=>'Top-up verification is temporarily unavailable.'],503);
    }
}
if (!is_string($reference) || !preg_match('/^ss-[a-f0-9]{40}$/D',$reference)) json_response(['ok'=>true,'result'=>'ignored']);
$db = database(); $order = db_one($db,'SELECT * FROM billing_orders WHERE reference = ?',[$reference]);
if (!$order || $order['provider'] !== 'paystack') json_response(['ok'=>true,'result'=>'ignored']);
try {
    $result = verify_billing_order($db,$order);
    // Acknowledge only after verification/fulfillment commits. Paystack retries
    // transient failures; repeated deliveries cannot extend access twice.
    if ($result !== 'paid') json_response(['ok'=>false,'error'=>'Payment verification is pending.'],503);
    json_response(['ok'=>true]);
} catch (PaymentMismatch $error) {
    json_response(['ok'=>true,'result'=>'review_required']);
} catch (PaymentUnavailable $error) {
    json_response(['ok'=>false,'error'=>'Payment verification is temporarily unavailable.'],503);
}
