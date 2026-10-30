<?php
declare(strict_types=1);
require_once __DIR__ . '/../../app/auth.php';
require_once __DIR__ . '/../../app/view.php';
require_once __DIR__ . '/../../app/modules.php';
require_once __DIR__ . '/../../app/settings.php';

$db = database();
$user = workspace_user($db);
$companyId = (int)$user['company_id'];
$access = require_module_access($db,$user,'pos');
$settings = workspace_settings($db,$companyId);
$currency = (string)($settings['currency'] ?? '');
suite_start('Point of sale','pos',$user);
?>
<script src="/public/scripts/pos.js" defer></script>
<div id="pos-app" data-api="/api/pos.php" data-csrf="<?= e(csrf_token()) ?>" data-can-edit="<?= $access === 'edit' ? '1' : '0' ?>" data-owner="<?= ($_SESSION['auth_role'] ?? '') === 'admin' ? '1' : '0' ?>" data-currency="<?= e($currency) ?>">
<div class="page-heading pos-heading"><div><span class="eyebrow">SALES &amp; CRM</span><h1>Point of sale</h1><p>Sell from your inventory, keep stock reservations safe, and track each register shift.</p></div><div class="heading-actions"><a class="button secondary small" href="/users/admin/workspace.php?module=inventory"><?= icon('box') ?> Inventory</a><a class="button secondary small" href="/users/admin/workspace.php?module=customers"><?= icon('users') ?> Customers</a></div></div>
<?php if ($currency === ''): ?><div class="notice warning">Set your workspace currency in <a class="text-link" href="/users/admin/settings.php">Settings</a> before taking sales. POS totals never convert currencies.</div><?php endif; ?>
<div class="notice info">Stock is reserved for 20 minutes when you start a sale and deducted only after payment is recorded. Simplr does not process card or other payments here; confirm external payments before recording them.</div>
<div class="pos-alert notice error" data-pos-alert role="alert" hidden></div>
<div class="pos-summary" data-pos-summary aria-live="polite"></div>
<section class="panel pos-shift-panel"><div class="panel-head"><div><h2>Register shift</h2><p data-pos-shift-status>Loading register status…</p></div></div><div class="panel-body"><div class="pos-shift-controls" data-pos-shift-controls></div></div></section>
<div class="pos-layout">
<section class="panel pos-catalog-panel"><div class="panel-head"><div><h2>Products</h2><p>Items come directly from your workspace inventory.</p></div><span class="count-label" data-pos-product-count></span></div><div class="pos-filter-row"><label class="sr-only" for="pos-product-search">Search products</label><input id="pos-product-search" type="search" placeholder="Search products or categories" data-pos-search><label class="sr-only" for="pos-category">Filter by category</label><select id="pos-category" data-pos-category><option value="">All categories</option></select></div><div class="pos-products" data-pos-products></div></section>
<section class="panel pos-cart-panel"><div class="panel-head"><div><h2>Current sale</h2><p data-pos-cart-note>Add products to start.</p></div><button class="button secondary small" type="button" data-pos-clear-cart>Clear</button></div><div class="panel-body"><div class="pos-cart-items" data-pos-cart></div><div class="field"><label for="pos-customer">Customer (optional)</label><select id="pos-customer" data-pos-customer><option value="">Walk-in customer</option></select></div><div class="field"><label for="pos-discount">Discount %</label><input id="pos-discount" type="number" min="0" max="100" step="0.01" value="0" data-pos-discount></div><div class="pos-totals" data-pos-totals></div><button class="button pos-checkout" type="button" data-pos-reserve>Reserve stock &amp; continue to payment</button><small>Reservations expire after 20 minutes if not completed.</small></div></section>
</div>
<div class="pos-lower-grid">
<section class="panel"><div class="panel-head"><div><h2>Recent sales</h2><p>Open a sale to complete payment, cancel a reservation, or record a refund.</p></div><button class="button secondary small" type="button" data-pos-refresh>Refresh</button></div><div class="table-wrap"><table class="data-table pos-sales-table"><thead><tr><th>Sale</th><th>Customer</th><th>Status</th><th>Total</th><th>Refunded</th><th>Time</th><th></th></tr></thead><tbody data-pos-sales></tbody></table></div></section>
<section class="panel pos-register-panel" data-pos-register-panel hidden><div class="panel-head"><div><h2>Registers</h2><p>Owner-managed checkout locations.</p></div></div><div class="panel-body"><form class="pos-inline-form" data-pos-create-register><div class="field"><label for="pos-register-name">Register name</label><input id="pos-register-name" name="name" maxlength="100" required placeholder="e.g. Front counter"></div><button class="button" type="submit">Add register</button></form></div></section>
</div>
<section class="panel pos-detail-panel" data-pos-detail-panel hidden><div class="panel-head"><div><h2>Sale details</h2><p data-pos-detail-heading></p></div><div class="heading-actions"><button class="button secondary small" type="button" data-pos-print>Print receipt</button><button class="icon-button" type="button" aria-label="Close sale details" data-pos-close-detail>×</button></div></div><div class="panel-body"><div data-pos-detail></div><div class="pos-detail-actions" data-pos-detail-actions></div></div></section>
<p class="pos-footer-note">Inventory counts are shared with <a class="text-link" href="/users/admin/workspace.php?module=inventory">Inventory</a>. Sales, reservations, refunds, and shift activity are scoped to this workspace.</p>
</div>
<?php suite_end(); ?>
