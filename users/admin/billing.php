<?php
require_once __DIR__ . '/../../app/auth.php';
require_once __DIR__ . '/../../app/paystack.php';
require_once __DIR__ . '/../../app/view.php';
$db = database(); $user = admin_user($db); $error = '';
$currency = $_POST['currency'] ?? $_GET['currency'] ?? 'NGN';
if (!is_string($currency) || !in_array($currency,['NGN','USD'],true)) fail('Choose NGN or USD.',422);
$provider = billing_provider($currency); $providerName = $provider === 'paystack' ? 'Paystack' : 'Flutterwave';
$ready = billing_provider_ready($provider);
$usdRate = null;
try { if ($currency === 'USD') $usdRate = billing_usd_rate(); } catch (InvalidArgumentException $exception) { $error = $exception->getMessage(); }
$selection = []; $nonce = bin2hex(random_bytes(32));
if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    require_post(); $selection = $_POST['months'] ?? [];
    if (!is_array($selection)) fail('Choose a valid toolkit selection.',422);
    $nonce = input('nonce');
    try {
        rate_limit($db,'billing-initialize',(string)$user['company_id'],10);
        $order = create_billing_order($db,$user,$selection,$nonce,null,$currency);
        if ($order['state'] === 'paid' || !billing_checkout_url($provider,$order['checkout_url'])) redirect('/users/admin/billing-return.php?reference=' . rawurlencode($order['reference']));
        redirect($order['checkout_url']);
    } catch (InvalidArgumentException $exception) { $error = $exception->getMessage(); http_response_code(422); }
    catch (PaymentUnavailable $exception) { $error = $exception->getMessage(); http_response_code(503); }
}
$mode = config('PAYSTACK_MODE','test');
$access = array_column(db_rows($db,'SELECT tool_key,valid_until FROM billing_entitlements WHERE company_id = ? AND provider_mode = ?',[$user['company_id'],$mode]),'valid_until','tool_key');
$orders = db_rows($db,'SELECT reference,amount_minor,currency,provider,state,created_at,provider_mode FROM billing_orders WHERE company_id = ? ORDER BY id DESC LIMIT 50',[$user['company_id']]);
suite_start('Billing','billing',$user);
?>
<div class="page-heading"><div><span class="eyebrow">TOOLS THAT GROW WITH YOU</span><h1>Workspace billing</h1><p>Choose the toolkits you need for 1–12 months. Pay in naira with Paystack or US dollars with Flutterwave.</p></div><span class="badge"><?= $mode === 'test' ? 'Test mode' : 'Live mode' ?></span></div>
<nav class="module-tabs" aria-label="Payment currency"><a href="?currency=NGN" <?= $currency === 'NGN' ? 'class="active" aria-current="page"' : '' ?>>NGN · Paystack</a><a href="?currency=USD" <?= $currency === 'USD' ? 'class="active" aria-current="page"' : '' ?>>USD · Flutterwave</a></nav>
<?php if (!$ready): ?><div class="notice warning"><?= e($providerName) ?> checkout is not configured yet. No payment can be started here until the service connection is ready.</div><?php endif; ?>
<?php if ($currency === 'USD' && $usdRate !== null): ?><p class="subtle">Dollar totals use our fixed price conversion: 1 NGN = <?= e($usdRate) ?> USD. The combined total is rounded to the nearest cent. This is not a live exchange-rate quote.</p><?php endif; ?>
<?php if ($error): ?><div class="notice error" role="alert"><?= e($error) ?></div><?php endif; ?>
<div class="notice info">Renewals are manual. There are no automatic charges. An active toolkit subscription enables editing; existing records stay available to read and export after expiry. Finance tools cover record keeping, not payroll payouts or bank accounts. Sales and CRM are one toolkit.</div>
<?php if (config('BILLING_ENFORCE','false') !== 'true'): ?><p class="subtle">Subscription enforcement is disabled in this preview environment.</p><?php endif; ?>
<section class="panel"><div class="panel-body"><form method="post" class="form-stack" data-submit data-billing-currency="<?= e($currency) ?>" data-billing-rate="<?= e($currency === 'USD' ? ($usdRate ?? '0') : '1') ?>"><?= csrf_field() ?><input type="hidden" name="currency" value="<?= e($currency) ?>"><input type="hidden" name="nonce" value="<?= e($nonce) ?>"><div class="access-grid"><?php foreach (billing_catalog() as $key=>$tool): ?><div class="field"><label for="months-<?= e($key) ?>"><?= e($tool['name']) ?></label><strong><?= e($currency) ?> <?= number_format($tool['amount']/100 * ($currency === 'USD' ? (float)$usdRate : 1),$currency === 'USD' ? 3 : 2) ?> / month</strong><select data-monthly-minor="<?= $tool['amount'] ?>" id="months-<?= e($key) ?>" name="months[<?= e($key) ?>]" <?= $ready ? '' : 'disabled' ?>><?php for ($months=0;$months<=12;$months++): ?><option value="<?= $months ?>" <?= (is_scalar($selection[$key] ?? 0) ? (string)($selection[$key] ?? 0) : '') === (string)$months ? 'selected' : '' ?>><?= $months === 0 ? 'Not selected' : $months . ($months === 1 ? ' month' : ' months') . ' · ' . $currency . ' ' . number_format($months*$tool['amount']/100*($currency === 'USD' ? (float)$usdRate : 1),2) ?></option><?php endfor; ?></select><?php if (isset($access[$key])): ?><small>Current access ends <?= e($access[$key]) ?> UTC</small><?php endif; ?></div><?php endforeach; ?></div><p data-billing-summary hidden><strong>Selected total: <output data-billing-total aria-live="polite"><?= e($currency) ?> 0.00</output></strong></p><p>You review the final total and enter payment details on <?= e($providerName) ?>’s checkout.</p><div><button class="button" type="submit" <?= $ready ? '' : 'disabled' ?>>Continue to <?= e($providerName) ?></button></div></form></div></section>
<section class="panel"><div class="panel-head"><h2>Recent payments</h2></div><?php if (!$orders): ?><div class="empty-state"><h3>No payments yet.</h3><p>Your checkout status and verified payments will appear here.</p></div><?php else: ?><div class="table-wrap"><table class="data-table"><thead><tr><th>Date (UTC)</th><th>Amount</th><th>Provider</th><th>Mode</th><th>Status</th><th>Action</th></tr></thead><tbody><?php foreach ($orders as $order): ?><tr><td><?= e($order['created_at']) ?></td><td><?= e($order['currency']) ?> <?= number_format($order['amount_minor']/100,2) ?></td><td><?= e(ucfirst($order['provider'])) ?></td><td><?= e($order['provider_mode']) ?></td><td><?= e(str_replace('_',' ',$order['state'])) ?></td><td><a class="text-link" href="/users/admin/billing-return.php?reference=<?= e($order['reference']) ?>">View payment</a></td></tr><?php endforeach; ?></tbody></table></div><?php endif; ?></section>
<?php suite_end(); ?>
