<?php
require_once __DIR__ . '/../../app/auth.php';
require_once __DIR__ . '/../../app/paystack.php';
require_once __DIR__ . '/../../app/view.php';
$db = database(); $user = admin_user($db);
$referenceValue = $_GET['reference'] ?? $_GET['tx_ref'] ?? '';
$reference = is_string($referenceValue) ? $referenceValue : '';
$order = preg_match('/^ss-[a-f0-9]{40}$/D',$reference) ? db_one($db,'SELECT * FROM billing_orders WHERE reference = ? AND company_id = ?',[$reference,$user['company_id']]) : null;
if (!$order) fail('This payment could not be found in your workspace.',404);
$providerName = $order['provider'] === 'flutterwave' ? 'Flutterwave' : 'Paystack';
$error = '';
if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    require_post(); rate_limit($db,'billing-verify',$reference,20);
    try {
        $state = verify_billing_order($db,$order);
        flash($state === 'paid' ? 'Payment verified. Your toolkit access is updated.' : $providerName . ' has not confirmed a successful payment yet. Check again shortly.',$state === 'paid' ? 'success' : 'info');
        redirect('/users/admin/billing-return.php?reference=' . rawurlencode($reference));
    } catch (PaymentMismatch $exception) { $error = $exception->getMessage(); http_response_code(409); }
    catch (PaymentUnavailable $exception) { $error = $exception->getMessage(); http_response_code(503); }
}
suite_start('Payment status','billing',$user);
?>
<div class="page-heading"><div><h1><?= $order['state'] === 'paid' ? 'Payment verified' : 'Payment status' ?></h1><p>Payment status confirmed with <?= e($providerName) ?>.</p></div><a class="button secondary" href="/users/admin/billing.php">Back to billing</a></div>
<?php if ($error): ?><div class="notice error" role="alert"><?= e($error) ?></div><?php endif; ?>
<section class="panel"><div class="panel-body"><p><strong>Amount:</strong> <?= e($order['currency']) ?> <?= number_format($order['amount_minor']/100,2) ?></p><p><strong>Status:</strong> <?= e(str_replace('_',' ',$order['state'])) ?></p><p class="preserve-lines"><strong>Reference:</strong> <?= e($reference) ?></p><ul><?php foreach (json_decode($order['items_json'],true) as $item): ?><li><?= e($item['name']) ?> · <?= (int)$item['months'] ?> month(s)</li><?php endforeach; ?></ul><?php if ($order['state'] !== 'paid'): ?><form method="post" data-submit><?= csrf_field() ?><button class="button" type="submit" <?= billing_provider_ready($order['provider']) ? '' : 'disabled' ?>>Check payment status</button></form><p class="subtle">A return to this page alone does not confirm payment. If you were charged but access has not updated, keep this reference and contact support. Avoid paying again while confirmation is pending.</p><?php else: ?><a class="button" href="/users/admin/modules.php">Open your tools</a><?php endif; ?></div></section>
<?php suite_end(); ?>
