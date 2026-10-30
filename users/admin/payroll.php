<?php
declare(strict_types=1);
require_once __DIR__ . '/../../app/auth.php';
require_once __DIR__ . '/../../app/payroll.php';
require_once __DIR__ . '/../../app/view.php';

$db = database();
$user = admin_user($db);
$companyId = (int)$user['company_id'];
$userId = (int)$user['id'];

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    require_post();
    $action = input('action');
    if ($action === 'save_profile') {
        payroll_profile_save($db,$companyId,$userId,$_POST);
        flash('Payroll profile saved. Bank account details are encrypted at rest.');
        redirect('/users/admin/payroll.php#profiles');
    }
    if ($action === 'create_run') {
        $runId = payroll_create_run($db,$companyId,$userId,input('period_start'),input('period_end'),input('pay_date'));
        flash('Draft payroll run created. Review every line before approval.');
        redirect('/users/admin/payroll.php?run=' . $runId);
    }
    if ($action === 'save_item') {
        $runId = filter_var($_POST['run_id'] ?? null,FILTER_VALIDATE_INT,['options'=>['min_range'=>1]]) ?: 0;
        $itemId = filter_var($_POST['item_id'] ?? null,FILTER_VALIDATE_INT,['options'=>['min_range'=>1]]) ?: 0;
        if (!$runId || !$itemId) fail('Choose a valid payroll item.',422);
        payroll_update_item($db,$companyId,$runId,$itemId,$_POST);
        flash('Draft payroll item updated.');
        redirect('/users/admin/payroll.php?run=' . $runId);
    }
    if ($action === 'approve_run') {
        $runId = filter_var($_POST['run_id'] ?? null,FILTER_VALIDATE_INT,['options'=>['min_range'=>1]]) ?: 0;
        if (!$runId) fail('Choose a payroll run to approve.',422);
        payroll_approve_run($db,$companyId,$runId,$userId);
        flash('Payroll run approved. Payslips are now visible to the staff included in the run.');
        redirect('/users/admin/payroll.php?run=' . $runId);
    }
    if ($action === 'mark_bank_paid') {
        $runId = filter_var($_POST['run_id'] ?? null,FILTER_VALIDATE_INT,['options'=>['min_range'=>1]]) ?: 0;
        $itemId = filter_var($_POST['item_id'] ?? null,FILTER_VALIDATE_INT,['options'=>['min_range'=>1]]) ?: 0;
        if (!$runId || !$itemId) fail('Choose a valid payroll item.',422);
        payroll_mark_bank_paid($db,$companyId,$runId,$itemId,input('bank_payment_reference'));
        flash('Manual bank payment recorded and audited.');
        redirect('/users/admin/payroll.php?run=' . $runId);
    }
    if ($action === 'cancel_draft') {
        $runId = filter_var($_POST['run_id'] ?? null,FILTER_VALIDATE_INT,['options'=>['min_range'=>1]]) ?: 0;
        if (!$runId) fail('Choose a payroll draft to delete.',422);
        payroll_cancel_draft($db,$companyId,$runId);
        flash('Draft payroll run deleted. Approved runs and their payslips are retained.');
        redirect('/users/admin/payroll.php#runs');
    }
    if ($action === 'transfer') {
        $runId = filter_var($_POST['run_id'] ?? null,FILTER_VALIDATE_INT,['options'=>['min_range'=>1]]) ?: 0;
        $itemId = filter_var($_POST['item_id'] ?? null,FILTER_VALIDATE_INT,['options'=>['min_range'=>1]]) ?: 0;
        if (!$runId || !$itemId) fail('Choose a valid payroll item.',422);
        try {
            $status = payroll_transfer($db,$companyId,$runId,$itemId);
            flash(match ($status) {
                'paid'=>'Paystack confirmed this transfer.',
                'otp_required'=>'Paystack requires a one-time authorization code before completing this transfer.',
                default=>'Transfer submitted. The run will update when Paystack confirms its final status.',
            },$status === 'otp_required' ? 'warning' : 'success');
        } catch (PaymentUnavailable $error) { flash($error->getMessage(),'error'); }
        redirect('/users/admin/payroll.php?run=' . $runId);
    }
    if ($action === 'finalize_transfer') {
        $runId = filter_var($_POST['run_id'] ?? null,FILTER_VALIDATE_INT,['options'=>['min_range'=>1]]) ?: 0;
        $itemId = filter_var($_POST['item_id'] ?? null,FILTER_VALIDATE_INT,['options'=>['min_range'=>1]]) ?: 0;
        if (!$runId || !$itemId) fail('Choose a valid payroll transfer.',422);
        try {
            payroll_finalize_transfer($db,$companyId,$runId,$itemId,input('otp'));
            flash('Paystack transfer authorization was submitted. Check the provider status before retrying.');
        } catch (PaymentUnavailable $error) { flash($error->getMessage(),'error'); }
        redirect('/users/admin/payroll.php?run=' . $runId);
    }
    fail('Choose a valid payroll action.',422);
}

$runId = filter_var($_GET['run'] ?? null,FILTER_VALIDATE_INT,['options'=>['min_range'=>1]]) ?: 0;
if (($_GET['export'] ?? '') === 'bank' && $runId) {
    $run = db_one($db,"SELECT * FROM payroll_runs WHERE id = ? AND company_id = ? AND status IN ('approved','processing','paid')",[$runId,$companyId]);
    if (!$run) fail('Only an approved payroll run can be exported.',404);
    $items = db_rows($db,"SELECT i.* FROM payroll_items i WHERE i.run_id = ? AND i.company_id = ? AND i.payout_status IN ('not_started','failed') ORDER BY i.staff_name",[$runId,$companyId]);
    audit_event($db,$companyId,'payroll.bank_exported','payroll_run',(string)$runId,['rows'=>count($items)]);
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="payroll-' . $runId . '-bank-transfers.csv"');
    header('X-Content-Type-Options: nosniff');
    $out = fopen('php://output','wb');
    if ($out === false) throw new RuntimeException('Could not create payroll export.');
    fwrite($out,"\xEF\xBB\xBF");
    fputcsv($out,['Staff name','Email','Bank','Bank code','Account name','Account number','Currency','Amount','Reference']);
    foreach ($items as $item) {
        $amount = payroll_amount_cents($item['net_pay']);
        if ($amount === null || $amount <= 0) continue;
        $cells = [$item['staff_name'],$item['staff_email'],$item['bank_name'],$item['bank_code'],$item['account_name'],decrypt_secret($item['account_number_ciphertext']),'NGN',accounting_decimal_from_cents($amount),'SS-PAYROLL-' . $runId . '-' . $item['id']];
        foreach ($cells as &$cell) {
            $cell = (string)$cell;
            if (preg_match('/^[\s]*[=+\-@]/u',$cell)) $cell = "'" . $cell;
        }
        unset($cell);
        fputcsv($out,$cells);
    }
    fclose($out);
    exit;
}

$people = payroll_staff($db,$companyId);
$profiles = db_rows($db,'SELECT staff_role,staff_id,monthly_salary,monthly_allowances,bank_name,bank_code,account_name,account_number_ciphertext FROM payroll_profiles WHERE company_id = ? ORDER BY staff_role,staff_id',[$companyId]);
$profileByStaff = [];
foreach ($profiles as $profile) $profileByStaff[$profile['staff_role'] . ':' . $profile['staff_id']] = $profile;
$runs = db_rows($db,'SELECT r.*, COUNT(i.id) AS staff_count, COALESCE(SUM(i.net_pay),0) AS net_total FROM payroll_runs r LEFT JOIN payroll_items i ON i.run_id = r.id AND i.company_id = r.company_id WHERE r.company_id = ? GROUP BY r.id ORDER BY r.period_start DESC, r.id DESC LIMIT 30',[$companyId]);
$run = $runId ? db_one($db,'SELECT * FROM payroll_runs WHERE id = ? AND company_id = ?',[$runId,$companyId]) : null;
if ($runId && !$run) fail('This payroll run could not be found in your workspace.',404);
$items = $run ? db_rows($db,'SELECT * FROM payroll_items WHERE run_id = ? AND company_id = ? ORDER BY staff_name,id',[$runId,$companyId]) : [];
$currency = 'NGN';
$money = static fn(mixed $value): string => payroll_money(payroll_amount_cents($value) ?? 0,$currency);
suite_start('Payroll','payroll',$user);
?>
<div class="page-heading"><div><h1>Payroll</h1><p>Set compensation, review draft pay runs, issue payslips, and manage bank transfers.</p></div></div>
<div class="notice warning"><strong>Professional review required.</strong> Statutory amounts are entered manually for each employee and pay period. Simplr does not calculate Nigerian tax or certify compliance. Penalties and HR deductions are never imported or applied automatically. Verify amounts with a qualified payroll professional before approval.</div>
<?php if (config('PAYROLL_PAYSTACK_TRANSFERS_ENABLED','false') !== 'true'): ?><div class="notice info">Paystack transfers are disabled. Configure and verify the payout account before enabling <code>PAYROLL_PAYSTACK_TRANSFERS_ENABLED=true</code>. Approved runs can still be exported for bank processing.</div><?php endif; ?>
<?php if ($run): ?>
<section class="panel"><div class="panel-head"><div><h2>Run · <?= e(date('j M Y',strtotime($run['period_start']))) ?> – <?= e(date('j M Y',strtotime($run['period_end']))) ?></h2><p class="subtle">Pay date <?= e(date('j M Y',strtotime($run['pay_date']))) ?> · <?= e(ucfirst($run['status'])) ?></p></div><?php if (in_array($run['status'],['approved','processing','paid'],true)): ?><a class="button secondary small" href="/users/admin/payroll.php?run=<?= (int)$run['id'] ?>&amp;export=bank">Export unpaid bank transfers</a><?php endif; ?></div>
<div class="table-wrap"><table class="data-table"><thead><tr><th>Staff member</th><th>Basic pay</th><th>Allowances</th><th>Bonus</th><th>PAYE</th><th>Pension</th><th>NHF</th><th>Other</th><th>Net pay</th><th>Payment</th></tr></thead><tbody>
<?php foreach ($items as $item): ?><tr><td><?= e($item['staff_name']) ?><small class="subtle" style="display:block"><?= e(ucfirst($item['staff_role'])) ?></small></td>
<?php if ($run['status'] === 'draft'): ?><td colspan="9"><form method="post" class="form-stack" style="min-width:750px" data-submit><?= csrf_field() ?><input type="hidden" name="action" value="save_item"><input type="hidden" name="run_id" value="<?= (int)$run['id'] ?>"><input type="hidden" name="item_id" value="<?= (int)$item['id'] ?>"><div class="form-grid"><?php foreach (['basic_pay'=>'Basic pay','allowances'=>'Allowances','bonus'=>'Bonus','paye'=>'PAYE','pension'=>'Pension','nhf'=>'NHF','other_deductions'=>'Other deductions'] as $field=>$label): ?><div class="field"><label for="<?= $field ?>-<?= (int)$item['id'] ?>"><?= e($label) ?></label><input id="<?= $field ?>-<?= (int)$item['id'] ?>" name="<?= e($field) ?>" type="number" min="0" step="0.01" value="<?= e($item[$field]) ?>" required></div><?php endforeach; ?><div class="field"><label>Net pay</label><strong><?= $money($item['net_pay']) ?></strong></div></div><button class="button secondary small" type="submit">Save item</button></form></td>
<?php else: ?><td><?= $money($item['basic_pay']) ?></td><td><?= $money($item['allowances']) ?></td><td><?= $money($item['bonus']) ?></td><td><?= $money($item['paye']) ?></td><td><?= $money($item['pension']) ?></td><td><?= $money($item['nhf']) ?></td><td><?= $money($item['other_deductions']) ?></td><td><strong><?= $money($item['net_pay']) ?></strong></td><td><span class="badge"><?= e(str_replace('_',' ',$item['payout_status'])) ?></span>
<?php if ($item['payout_status'] === 'not_started' || $item['payout_status'] === 'failed'): ?><form method="post" style="margin-top:8px" data-submit><?= csrf_field() ?><input type="hidden" name="action" value="transfer"><input type="hidden" name="run_id" value="<?= (int)$run['id'] ?>"><input type="hidden" name="item_id" value="<?= (int)$item['id'] ?>"><button class="button small" type="submit" onclick="return confirm('Send this approved net pay from the configured Paystack balance?')">Send with Paystack</button></form><form method="post" class="form-stack" style="margin-top:8px" data-submit><?= csrf_field() ?><input type="hidden" name="action" value="mark_bank_paid"><input type="hidden" name="run_id" value="<?= (int)$run['id'] ?>"><input type="hidden" name="item_id" value="<?= (int)$item['id'] ?>"><label>Bank transfer reference<input name="bank_payment_reference" maxlength="100" required></label><button class="button secondary small" type="submit" onclick="return confirm('Record this approved payroll item as already paid through the bank?')">Record bank payment</button></form><?php elseif ($item['payout_status'] === 'otp_required'): ?><form method="post" class="form-stack" style="margin-top:8px" data-submit><?= csrf_field() ?><input type="hidden" name="action" value="finalize_transfer"><input type="hidden" name="run_id" value="<?= (int)$run['id'] ?>"><input type="hidden" name="item_id" value="<?= (int)$item['id'] ?>"><label>Paystack transfer code<input name="otp" inputmode="numeric" pattern="[0-9]{6,8}" required></label><button class="button small" type="submit">Authorize transfer</button></form><?php elseif ($item['payout_status'] === 'unknown'): ?><small class="subtle">Reconcile reference <?= e($item['payout_reference']) ?> before retrying.</small><?php endif; ?><?php if (!empty($item['bank_payment_reference'])): ?><small class="subtle">Bank reference: <?= e($item['bank_payment_reference']) ?></small><?php endif; ?></td><?php endif; ?></tr><?php endforeach; ?></tbody></table></div>
<?php if ($run['status'] === 'draft'): ?><div class="panel-body"><form method="post" data-submit><?= csrf_field() ?><input type="hidden" name="action" value="approve_run"><input type="hidden" name="run_id" value="<?= (int)$run['id'] ?>"><div class="notice info">Approval locks this run, publishes individual payslips, and enables bank exports and explicitly initiated transfers. Check every gross amount and deduction first. Compensation and bank details are snapshots from when this draft was created; delete and recreate the draft if a profile changes.</div><button class="button" type="submit" onclick="return confirm('Approve and lock this entire payroll run?')">Approve payroll run</button></form><form method="post" style="margin-top:12px" data-submit><?= csrf_field() ?><input type="hidden" name="action" value="cancel_draft"><input type="hidden" name="run_id" value="<?= (int)$run['id'] ?>"><button class="button secondary small" type="submit" onclick="return confirm('Delete this draft and all of its unapproved payroll items?')">Delete draft</button></form></div><?php endif; ?></section>
<?php endif; ?>
<div class="dashboard-columns"><section class="panel" id="runs"><div class="panel-head"><h2>Pay runs</h2></div><?php if (!$runs): ?><div class="empty-state"><h3>No pay runs yet</h3><p>Create salary profiles for all active staff, then start a draft run.</p></div><?php else: ?><div class="table-wrap"><table class="data-table"><thead><tr><th>Period</th><th>Pay date</th><th>Staff</th><th>Net total</th><th>Status</th></tr></thead><tbody><?php foreach ($runs as $entry): ?><tr><td><a class="text-link" href="/users/admin/payroll.php?run=<?= (int)$entry['id'] ?>"><?= e(date('M Y',strtotime($entry['period_start']))) ?></a></td><td><?= e(date('j M Y',strtotime($entry['pay_date']))) ?></td><td><?= (int)$entry['staff_count'] ?></td><td><?= $money($entry['net_total']) ?></td><td><?= e(ucfirst($entry['status'])) ?></td></tr><?php endforeach; ?></tbody></table></div><?php endif; ?></section>
<div>
<section class="panel"><div class="panel-head"><h2>Create a draft run</h2></div><div class="panel-body"><p class="subtle">The first release supports one complete calendar month per run.</p><form method="post" class="form-stack" data-submit><?= csrf_field() ?><input type="hidden" name="action" value="create_run"><div class="field"><label for="period_start">Period starts</label><input id="period_start" name="period_start" type="date" required></div><div class="field"><label for="period_end">Period ends</label><input id="period_end" name="period_end" type="date" required></div><div class="field"><label for="pay_date">Pay date</label><input id="pay_date" name="pay_date" type="date" required></div><button class="button" type="submit">Create draft run</button></form></div></section>
<section class="panel" id="profiles"><div class="panel-head"><h2>Compensation profiles</h2></div><div class="panel-body"><form method="post" class="form-stack" data-submit><?= csrf_field() ?><input type="hidden" name="action" value="save_profile"><div class="field"><label for="person">Staff member</label><select name="staff_id" id="person" required><option value="">Choose staff</option><?php foreach ($people as $person): $profile = $profileByStaff[$person['staff_role'] . ':' . $person['id']] ?? null; ?><option value="<?= (int)$person['id'] ?>" data-role="<?= e($person['staff_role']) ?>" data-salary="<?= e($profile['monthly_salary'] ?? '') ?>" data-allowances="<?= e($profile['monthly_allowances'] ?? '0.00') ?>" data-bank="<?= e($profile['bank_name'] ?? '') ?>" data-bank-code="<?= e($profile['bank_code'] ?? '') ?>" data-account-name="<?= e($profile['account_name'] ?? '') ?>"><?= e($person['staff_name'] . ' · ' . ucfirst($person['staff_role']) . ($profile ? ' · ' . $profile['bank_name'] . ' · account ending ' . substr(decrypt_secret($profile['account_number_ciphertext']),-4) : ' · profile needed')) ?></option><?php endforeach; ?></select><input type="hidden" name="staff_role" id="staff-role"></div><div class="field"><label for="monthly_salary">Monthly basic salary (NGN)</label><input id="monthly_salary" name="monthly_salary" type="number" min="0.01" step="0.01" required></div><div class="field"><label for="monthly_allowances">Monthly allowances (NGN)</label><input id="monthly_allowances" name="monthly_allowances" type="number" min="0" step="0.01" value="0.00" required></div><div class="field"><label for="bank_name">Bank name</label><input id="bank_name" name="bank_name" maxlength="120" required></div><div class="field"><label for="bank_code">Nigerian bank code</label><input id="bank_code" name="bank_code" maxlength="20" required></div><div class="field"><label for="account_name">Account name</label><input id="account_name" name="account_name" maxlength="160" required></div><div class="field"><label for="account_number">10-digit account number</label><input id="account_number" name="account_number" type="password" inputmode="numeric" pattern="[0-9]{10}" autocomplete="off"><small class="subtle">Leave blank to keep an existing account number.</small></div><button class="button secondary" type="submit">Save compensation profile</button><small class="subtle">Account numbers are encrypted at rest. Use an account number the employee has authorized for salary payments.</small></form></div></section>
</div></div>
<script>document.getElementById('person')?.addEventListener('change',function(){const option=this.selectedOptions[0],field=(id,value)=>{document.getElementById(id).value=value||''};document.getElementById('staff-role').value=option?.dataset.role||'';field('monthly_salary',option?.dataset.salary);field('monthly_allowances',option?.dataset.allowances||'0.00');field('bank_name',option?.dataset.bank);field('bank_code',option?.dataset.bankCode);field('account_name',option?.dataset.accountName);field('account_number','')});</script>
<?php suite_end(); ?>
