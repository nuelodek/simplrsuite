<?php
declare(strict_types=1);
require_once __DIR__ . '/../../app/auth.php';
require_once __DIR__ . '/../../app/access.php';
require_once __DIR__ . '/../../app/payroll.php';
require_once __DIR__ . '/../../app/view.php';

$db = database();
$user = workspace_user($db);
$role = (string)($_SESSION['auth_role'] ?? '');
if (!in_array($role,['employee','manager'],true)) fail('Open payroll from the business-owner workspace.',404);
$access = module_access($db,$user,'payroll');
if ($access === '') fail('Payroll access has not been assigned to you.',403);
$items = db_rows($db,"SELECT i.*, r.period_start, r.period_end, r.pay_date FROM payroll_items i JOIN payroll_runs r ON r.id = i.run_id AND r.company_id = i.company_id WHERE i.company_id = ? AND i.staff_role = ? AND i.staff_id = ? AND r.status IN ('approved','processing','paid') ORDER BY r.period_start DESC, i.id DESC LIMIT 36",[$user['company_id'],$role,(int)$user['id']]);
$currency = 'NGN';
$money = static fn(mixed $value): string => payroll_money(payroll_amount_cents($value) ?? 0,$currency);
suite_start('My payslips','payroll',$user);
?>
<div class="page-heading"><div><h1>My payslips</h1><p>Approved payroll records for your account.</p></div></div>
<?php if (!$items): ?><section class="panel"><div class="empty-state"><h2>No approved payslips yet</h2><p>Approved payslips will appear here after your workspace administrator processes payroll.</p></div></section><?php else: ?>
<?php foreach ($items as $item): ?><article class="panel" style="max-width:760px;margin-bottom:20px"><div class="panel-head"><div><h2><?= e(date('F Y',strtotime($item['period_start']))) ?></h2><p class="subtle">Pay date <?= e(date('j F Y',strtotime($item['pay_date']))) ?> · Payment <?= e(str_replace('_',' ',$item['payout_status'])) ?></p></div><button type="button" class="button secondary small" onclick="window.print()">Print payslip</button></div><div class="panel-body"><dl class="mini-metrics"><div><dt>Basic pay</dt><dd><?= $money($item['basic_pay']) ?></dd></div><div><dt>Allowances</dt><dd><?= $money($item['allowances']) ?></dd></div><div><dt>Bonus</dt><dd><?= $money($item['bonus']) ?></dd></div><div><dt>PAYE (entered by employer)</dt><dd>−<?= $money($item['paye']) ?></dd></div><div><dt>Pension (entered by employer)</dt><dd>−<?= $money($item['pension']) ?></dd></div><div><dt>NHF (entered by employer)</dt><dd>−<?= $money($item['nhf']) ?></dd></div><div><dt>Other deductions</dt><dd>−<?= $money($item['other_deductions']) ?></dd></div><div><dt>Net pay</dt><dd><strong><?= $money($item['net_pay']) ?></strong></dd></div></dl><?php if (!empty($item['payout_reference']) || !empty($item['bank_payment_reference'])): ?><p class="subtle">Payment reference: <?= e($item['payout_reference'] ?: $item['bank_payment_reference']) ?></p><?php endif; ?><small class="subtle">For questions about statutory amounts, contact your payroll administrator. Simplr does not calculate or certify Nigerian tax or statutory contributions.</small></div></article><?php endforeach; ?>
<?php endif; suite_end(); ?>
