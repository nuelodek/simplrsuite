<?php
declare(strict_types=1);
require_once __DIR__ . '/../../app/auth.php';
require_once __DIR__ . '/../../app/access.php';
require_once __DIR__ . '/../../app/payroll.php';
require_once __DIR__ . '/../../app/view.php';

$db = database();
$user = workspace_user($db);
$actorRole = (string)($_SESSION['auth_role'] ?? '');
$isOwner = $actorRole === 'admin';
if (!$isOwner) require_module_access($db,$user,'payroll',true);
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
        $runId = payroll_create_run($db,$companyId,$userId,$actorRole,input('period_start'),input('period_end'),input('pay_date'));
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
    if ($action === 'apply_nigerian_estimates') {
        $runId = filter_var($_POST['run_id'] ?? null,FILTER_VALIDATE_INT,['options'=>['min_range'=>1]]) ?: 0;
        if (!$runId) fail('Choose a payroll run for the statutory estimate.',422);
        payroll_apply_nigerian_2026_estimates($db,$companyId,$runId,$userId,$actorRole);
        flash('2026 Nigerian estimates applied to this draft. Review and edit every amount; the rules were supplied by your organization and are not independently verified.');
        redirect('/users/admin/payroll.php?run=' . $runId);
    }
    if ($action === 'unapply_nigerian_estimates') {
        $runId = filter_var($_POST['run_id'] ?? null,FILTER_VALIDATE_INT,['options'=>['min_range'=>1]]) ?: 0;
        if (!$runId) fail('Choose a payroll run to restore.',422);
        payroll_unapply_nigerian_2026_estimates($db,$companyId,$runId,$userId,$actorRole);
        flash('Previous PAYE, pension and NHF amounts were restored. Review the draft before submission.');
        redirect('/users/admin/payroll.php?run=' . $runId);
    }
    if ($action === 'submit_run') {
        $runId = filter_var($_POST['run_id'] ?? null,FILTER_VALIDATE_INT,['options'=>['min_range'=>1]]) ?: 0;
        if (!$runId) fail('Choose a payroll run to submit.',422);
        payroll_submit_run($db,$companyId,$runId,$userId,$actorRole);
        flash('Payroll run submitted for independent approval. Payslips are not visible until it is approved.');
        redirect('/users/admin/payroll.php?run=' . $runId);
    }
    if ($action === 'approve_run') {
        $runId = filter_var($_POST['run_id'] ?? null,FILTER_VALIDATE_INT,['options'=>['min_range'=>1]]) ?: 0;
        if (!$runId) fail('Choose a payroll run to approve.',422);
        payroll_approve_run($db,$companyId,$runId,$userId,$actorRole);
        flash('Payroll run approved. Payslips are now visible to the staff included in the run.');
        redirect('/users/admin/payroll.php?run=' . $runId);
    }
    if ($action === 'paystack_topup') {
        try {
            $funding = salary_vault_create_paystack_funding($db,$companyId,$userId,$actorRole,(string)$user['email'],$_POST['amount'] ?? null);
            redirect((string)$funding['checkout_url']);
        } catch (PaymentUnavailable $error) { flash($error->getMessage(),'error'); redirect('/users/admin/payroll.php#salary-vault'); }
    }
    if ($action === 'manual_deposit') {
        $fundingId = salary_vault_submit_manual_funding($db,$companyId,$userId,$actorRole,$_POST['amount'] ?? null,input('external_reference'),input('note'));
        flash('Deposit submitted for independent reconciliation. It will not be added to the cleared balance until approved.');
        redirect('/users/admin/payroll.php#salary-vault');
    }
    if ($action === 'approve_deposit') {
        $fundingId = filter_var($_POST['funding_id'] ?? null,FILTER_VALIDATE_INT,['options'=>['min_range'=>1]]) ?: 0;
        if (!$fundingId) fail('Choose a manual deposit to approve.',422);
        salary_vault_approve_manual_funding($db,$companyId,$fundingId,$userId,$actorRole);
        flash('Manual deposit independently approved and added to the Salary Vault.');
        redirect('/users/admin/payroll.php#salary-vault');
    }
    if ($action === 'mark_bank_paid') {
        $runId = filter_var($_POST['run_id'] ?? null,FILTER_VALIDATE_INT,['options'=>['min_range'=>1]]) ?: 0;
        $itemId = filter_var($_POST['item_id'] ?? null,FILTER_VALIDATE_INT,['options'=>['min_range'=>1]]) ?: 0;
        if (!$runId || !$itemId) fail('Choose a valid payroll item.',422);
        payroll_mark_bank_paid($db,$companyId,$runId,$itemId,input('bank_payment_reference'),$userId,$actorRole);
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
            $status = payroll_transfer($db,$companyId,$runId,$itemId,$userId,$actorRole);
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
    if ($action === 'reconcile_transfer') {
        $runId = filter_var($_POST['run_id'] ?? null,FILTER_VALIDATE_INT,['options'=>['min_range'=>1]]) ?: 0;
        $itemId = filter_var($_POST['item_id'] ?? null,FILTER_VALIDATE_INT,['options'=>['min_range'=>1]]) ?: 0;
        if (!$runId || !$itemId) fail('Choose a valid payroll transfer.',422);
        try {
            $status = payroll_reconcile_transfer($db,$companyId,$runId,$itemId);
            flash('Paystack transfer reconciled as ' . str_replace('_',' ',$status) . '.');
        } catch (PaymentUnavailable $error) { flash($error->getMessage(),'error'); }
        redirect('/users/admin/payroll.php?run=' . $runId);
    }
    fail('Choose a valid payroll action.',422);
}

$fundingReference = is_string($_GET['funding'] ?? null) ? trim($_GET['funding']) : '';
if ($fundingReference !== '' && preg_match('/^ss-vault-[a-f0-9]{40}$/D',$fundingReference)) {
    try {
        if (salary_vault_verify_paystack_funding($db,$fundingReference,$companyId)) flash('Paystack confirmed the deposit and it has been added to the Salary Vault.');
        else flash('Paystack has not confirmed this deposit yet. The balance has not been credited.','warning');
    } catch (PaymentMismatch $error) {
        flash('The top-up details did not match the saved Salary Vault request. It has not been credited and needs review.','error');
    } catch (PaymentUnavailable $error) {
        flash($error->getMessage(),'error');
    }
    redirect('/users/admin/payroll.php#salary-vault');
}

$runId = filter_var($_GET['run'] ?? null,FILTER_VALIDATE_INT,['options'=>['min_range'=>1]]) ?: 0;
if (($_GET['export'] ?? '') === 'bank' && $runId) {
    $run = db_one($db,"SELECT * FROM payroll_runs WHERE id = ? AND company_id = ? AND status IN ('approved','processing','paid') AND approved_by_role IS NOT NULL",[$runId,$companyId]);
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
$profiles = db_rows($db,'SELECT staff_role,staff_id,monthly_salary,monthly_housing,monthly_transport,monthly_allowances,annual_rent_paid,pension_applicable,nhf_applicable,rent_relief_documented,rent_document_reference,bank_name,bank_code,account_name,account_number_ciphertext FROM payroll_profiles WHERE company_id = ? ORDER BY staff_role,staff_id',[$companyId]);
$profileByStaff = [];
foreach ($profiles as $profile) $profileByStaff[$profile['staff_role'] . ':' . $profile['staff_id']] = $profile;
$runs = db_rows($db,'SELECT r.*, COUNT(i.id) AS staff_count, COALESCE(SUM(i.net_pay),0) AS net_total FROM payroll_runs r LEFT JOIN payroll_items i ON i.run_id = r.id AND i.company_id = r.company_id WHERE r.company_id = ? GROUP BY r.id ORDER BY r.period_start DESC, r.id DESC LIMIT 30',[$companyId]);
$vaultBalance = salary_vault_balance($db,$companyId);
$vaultFundings = db_rows($db,'SELECT * FROM salary_vault_fundings WHERE company_id = ? ORDER BY id DESC LIMIT 30',[$companyId]);
$vaultEntries = db_rows($db,'SELECT entry_type,entry_reference,amount_kobo,actor_role,actor_id,created_at FROM salary_vault_entries WHERE company_id = ? ORDER BY id DESC LIMIT 20',[$companyId]);
$canApproveVaultFunding = in_array($actorRole,['admin','employee'],true);
$run = $runId ? db_one($db,'SELECT * FROM payroll_runs WHERE id = ? AND company_id = ?',[$runId,$companyId]) : null;
if ($runId && !$run) fail('This payroll run could not be found in your workspace.',404);
$items = $run ? db_rows($db,'SELECT * FROM payroll_items WHERE run_id = ? AND company_id = ? ORDER BY staff_name,id',[$runId,$companyId]) : [];
$currency = 'NGN';
$money = static fn(mixed $value): string => payroll_money(payroll_amount_cents($value) ?? 0,$currency);
suite_start('Payroll','payroll',$user);
?>
<div class="page-heading"><div><h1>Payroll</h1><p>Set compensation, review pay runs, manage Salary Vault funding, and track salary payments.</p></div></div>
<div class="notice warning"><strong>Professional review required.</strong> The optional 2026 Nigerian settings action uses organization-supplied rules that have not been independently verified and does not certify legal compliance. Estimates can be edited in a draft before independent approval. No tax, pension, or housing-fund remittance is made. Penalties and HR deductions are never imported or applied automatically.</div>
<?php if (config('PAYROLL_PAYSTACK_TRANSFERS_ENABLED','false') !== 'true'): ?><div class="notice info">Paystack transfers are disabled. Configure and verify the payout account before enabling <code>PAYROLL_PAYSTACK_TRANSFERS_ENABLED=true</code>. Approved runs can still be exported for bank processing.</div><?php endif; ?>
<section class="panel" id="salary-vault"><div class="panel-head"><div><h2>Salary Vault</h2><p>Cleared NGN available for this workspace’s salary payouts.</p></div><strong class="metric-value"><?= $money($vaultBalance) ?></strong></div><div class="panel-body"><div class="notice warning">Paystack funding and payouts use a shared provider merchant balance. The Vault is a company-scoped ledger allocation, not a segregated bank account or custodial wallet. Top-ups are credited only after Paystack verification. A manually reconciled deposit must already be in the same payout funding pool used by Paystack before an approver credits it.</div><div class="dashboard-columns"><div><h3>Fund the Vault</h3><?php if (salary_vault_paystack_enabled()): ?><form method="post" class="form-stack" data-submit><?= csrf_field() ?><input type="hidden" name="action" value="paystack_topup"><div class="field"><label for="vault-paystack-amount">Verified Paystack top-up (NGN)</label><input id="vault-paystack-amount" name="amount" type="number" min="100" step="0.01" required></div><button class="button" type="submit">Continue to Paystack</button></form><?php else: ?><p class="subtle">Paystack top-ups are disabled. Enable <code>SALARY_VAULT_PAYSTACK_TOPUPS_ENABLED=true</code> only after configuring and verifying Paystack.</p><?php endif; ?><form method="post" class="form-stack" style="margin-top:20px" data-submit><?= csrf_field() ?><input type="hidden" name="action" value="manual_deposit"><h3>Record an external bank deposit</h3><div class="field"><label for="vault-manual-amount">Deposit amount (NGN)</label><input id="vault-manual-amount" name="amount" type="number" min="0.01" step="0.01" required></div><div class="field"><label for="vault-external-reference">External bank reference</label><input id="vault-external-reference" name="external_reference" maxlength="120" required></div><div class="field"><label for="vault-note">Note</label><textarea id="vault-note" name="note" maxlength="1000" rows="2"></textarea></div><button class="button secondary" type="submit">Submit for independent reconciliation</button></form></div><div><h3>Funding requests</h3><?php if (!$vaultFundings): ?><p class="subtle">No funding requests yet.</p><?php else: ?><div class="table-wrap"><table class="data-table"><thead><tr><th>Method / amount</th><th>Status</th><th>Reference</th><th>Action</th></tr></thead><tbody><?php foreach ($vaultFundings as $funding): ?><tr><td><?= e(ucfirst($funding['funding_method'])) ?><br><?= $money($funding['amount_kobo']) ?></td><td><?= e(str_replace('_',' ',$funding['status'])) ?></td><td><?= e($funding['external_reference'] ?: $funding['reference']) ?></td><td><?php if ($canApproveVaultFunding && $funding['funding_method'] === 'manual' && $funding['status'] === 'pending_approval' && !($funding['created_by_role'] === $actorRole && (int)$funding['created_by_id'] === $userId)): ?><form method="post" data-submit><?= csrf_field() ?><input type="hidden" name="action" value="approve_deposit"><input type="hidden" name="funding_id" value="<?= (int)$funding['id'] ?>"><button class="button small" type="submit" onclick="return confirm('Confirm that you independently verified this bank deposit?')">Verify &amp; credit</button></form><?php elseif ($funding['funding_method'] === 'manual' && $funding['status'] === 'pending_approval'): ?><span class="subtle">Awaiting an eligible independent approver</span><?php elseif ($funding['funding_method'] === 'paystack' && in_array($funding['status'],['awaiting_payment','initialization_unknown'],true)): ?><a class="text-link" href="/users/admin/payroll.php?funding=<?= e($funding['reference']) ?>">Check payment</a><?php endif; ?></td></tr><?php endforeach; ?></tbody></table></div><?php endif; ?></div></div><h3>Recent Vault ledger</h3><?php if (!$vaultEntries): ?><p class="subtle">No cleared funding or payout entries yet.</p><?php else: ?><div class="table-wrap"><table class="data-table"><thead><tr><th>Entry</th><th>Reference</th><th>Amount</th><th>Recorded</th></tr></thead><tbody><?php foreach ($vaultEntries as $entry): ?><tr><td><?= e(str_replace('_',' ',$entry['entry_type'])) ?></td><td><?= e($entry['entry_reference']) ?></td><td><?= $money($entry['amount_kobo']) ?></td><td><?= e(date('j M Y H:i',strtotime($entry['created_at']))) ?></td></tr><?php endforeach; ?></tbody></table></div><?php endif; ?></div></section>
<?php if ($run): ?>
<section class="panel"><div class="panel-head"><div><h2>Run · <?= e(date('j M Y',strtotime($run['period_start']))) ?> – <?= e(date('j M Y',strtotime($run['period_end']))) ?></h2><p class="subtle">Pay date <?= e(date('j M Y',strtotime($run['pay_date']))) ?> · <?= e(ucfirst($run['status'])) ?><?php if (!empty($run['statutory_settings_version'])): ?> · 2026 Nigerian estimates applied <?= e($run['statutory_applied_at'] ?? '') ?><?php endif; ?></p></div><?php if (in_array($run['status'],['approved','processing','paid'],true)): ?><a class="button secondary small" href="/users/admin/payroll.php?run=<?= (int)$run['id'] ?>&amp;export=bank">Export unpaid bank transfers</a><?php endif; ?></div>
<?php if ($run['status'] === 'draft'): ?><div class="panel-body"><div class="notice warning">Optional estimate only: uses the 2026 tax bands supplied by your organization, assumes the listed pension/NHF eligibility and documented rent relief, and is not independently verified. Confirm source documents and review the editable results before submitting.</div><?php if (substr((string)$run['period_start'],0,4) === '2026'): ?><form method="post" data-submit><?= csrf_field() ?><input type="hidden" name="action" value="apply_nigerian_estimates"><input type="hidden" name="run_id" value="<?= (int)$run['id'] ?>"><button class="button secondary" type="submit" onclick="return confirm('Replace PAYE, employee pension and NHF amounts in this draft with editable 2026 estimates?')">Apply Nigerian 2026 estimates</button></form><?php else: ?><p class="subtle">The configured estimate schedule applies to 2026 runs only.</p><?php endif; ?><?php if (($run['statutory_settings_version'] ?? '') === 'nigeria-2026-user-estimate-v1'): ?><form method="post" style="margin-top:10px" data-submit><?= csrf_field() ?><input type="hidden" name="action" value="unapply_nigerian_estimates"><input type="hidden" name="run_id" value="<?= (int)$run['id'] ?>"><button class="button secondary small" type="submit" onclick="return confirm('Restore the PAYE, pension and NHF values from before estimates were applied? Later edits to these three deductions will be replaced.')">Unapply and restore previous deductions</button></form><?php endif; ?></div><?php endif; ?>
<div class="table-wrap"><table class="data-table"><thead><tr><th>Staff member</th><th>Basic pay</th><th>Housing</th><th>Transport</th><th>Allowances</th><th>Overtime</th><th>Bonus</th><th>PAYE</th><th>Pension</th><th>NHF</th><th>Voluntary pension</th><th>Salary advance</th><th>Other voluntary</th><th>Net pay</th><th>Payment</th></tr></thead><tbody>
<?php foreach ($items as $item): ?><tr><td><?= e($item['staff_name']) ?><small class="subtle" style="display:block"><?= e(ucfirst($item['staff_role'])) ?></small></td>
<?php if ($run['status'] === 'draft'): ?><td colspan="14"><form method="post" class="form-stack" style="min-width:1000px" data-submit><?= csrf_field() ?><input type="hidden" name="action" value="save_item"><input type="hidden" name="run_id" value="<?= (int)$run['id'] ?>"><input type="hidden" name="item_id" value="<?= (int)$item['id'] ?>"><input type="hidden" name="rent_document_reference" value="<?= e($item['rent_document_reference']) ?>"><div class="form-grid"><?php foreach (['basic_pay'=>'Basic pay','housing'=>'Housing','transport'=>'Transport','allowances'=>'Other allowances','overtime_pay'=>'Overtime','bonus'=>'Bonus','annual_rent_paid'=>'Annual rent paid','paye'=>'PAYE','pension'=>'Pension','nhf'=>'NHF','voluntary_pension'=>'Voluntary pension','salary_advance_deduction'=>'Salary advance recovery','other_deductions'=>'Other voluntary deductions'] as $field=>$label): ?><div class="field"><label for="<?= $field ?>-<?= (int)$item['id'] ?>"><?= e($label) ?></label><input id="<?= $field ?>-<?= (int)$item['id'] ?>" name="<?= e($field) ?>" type="number" min="0" step="0.01" value="<?= e($item[$field]) ?>" required></div><?php endforeach; ?><div class="field"><label><input name="pension_applicable" type="checkbox" value="1" <?= !empty($item['pension_applicable']) ? 'checked' : '' ?>> Pension applies</label></div><div class="field"><label><input name="nhf_applicable" type="checkbox" value="1" <?= !empty($item['nhf_applicable']) ? 'checked' : '' ?>> NHF applies</label></div><div class="field"><label><input name="rent_relief_documented" type="checkbox" value="1" <?= !empty($item['rent_relief_documented']) ? 'checked' : '' ?>> Rent relief documents verified</label></div><div class="field"><label>Net pay</label><strong><?= $money($item['net_pay']) ?></strong></div></div><button class="button secondary small" type="submit">Save item</button></form></td>
<?php else: ?><td><?= $money($item['basic_pay']) ?></td><td><?= $money($item['housing']) ?></td><td><?= $money($item['transport']) ?></td><td><?= $money($item['allowances']) ?></td><td><?= $money($item['overtime_pay']) ?></td><td><?= $money($item['bonus']) ?></td><td><?= $money($item['paye']) ?></td><td><?= $money($item['pension']) ?></td><td><?= $money($item['nhf']) ?></td><td><?= $money($item['voluntary_pension']) ?></td><td><?= $money($item['salary_advance_deduction']) ?></td><td><?= $money($item['other_deductions']) ?></td><td><strong><?= $money($item['net_pay']) ?></strong></td><td><span class="badge"><?= e(str_replace('_',' ',$item['payout_status'])) ?></span>
<?php if ($item['payout_status'] === 'not_started' || $item['payout_status'] === 'failed'): ?><form method="post" style="margin-top:8px" data-submit><?= csrf_field() ?><input type="hidden" name="action" value="transfer"><input type="hidden" name="run_id" value="<?= (int)$run['id'] ?>"><input type="hidden" name="item_id" value="<?= (int)$item['id'] ?>"><button class="button small" type="submit" onclick="return confirm('Send this approved net pay from the configured Paystack balance?')">Send with Paystack</button></form><form method="post" class="form-stack" style="margin-top:8px" data-submit><?= csrf_field() ?><input type="hidden" name="action" value="mark_bank_paid"><input type="hidden" name="run_id" value="<?= (int)$run['id'] ?>"><input type="hidden" name="item_id" value="<?= (int)$item['id'] ?>"><label>Bank transfer reference<input name="bank_payment_reference" maxlength="100" required></label><button class="button secondary small" type="submit" onclick="return confirm('Record this approved payroll item as already paid through the bank?')">Record bank payment</button></form><?php elseif (in_array($item['payout_status'],['unknown','pending','processing'],true)): ?><form method="post" style="margin-top:8px" data-submit><?= csrf_field() ?><input type="hidden" name="action" value="reconcile_transfer"><input type="hidden" name="run_id" value="<?= (int)$run['id'] ?>"><input type="hidden" name="item_id" value="<?= (int)$item['id'] ?>"><button class="button secondary small" type="submit">Check Paystack status</button></form><small class="subtle">Reference <?= e($item['payout_reference']) ?>; do not retry an uncertain transfer.</small><?php elseif ($item['payout_status'] === 'otp_required'): ?><form method="post" class="form-stack" style="margin-top:8px" data-submit><?= csrf_field() ?><input type="hidden" name="action" value="finalize_transfer"><input type="hidden" name="run_id" value="<?= (int)$run['id'] ?>"><input type="hidden" name="item_id" value="<?= (int)$item['id'] ?>"><label>Paystack transfer code<input name="otp" inputmode="numeric" pattern="[0-9]{6,8}" required></label><button class="button small" type="submit">Authorize transfer</button></form><?php endif; ?><?php if (!empty($item['bank_payment_reference'])): ?><small class="subtle">Bank reference: <?= e($item['bank_payment_reference']) ?></small><?php endif; ?></td><?php endif; ?></tr><?php endforeach; ?></tbody></table></div>
<?php if ($run['status'] === 'draft'): ?><div class="panel-body"><form method="post" data-submit><?= csrf_field() ?><input type="hidden" name="action" value="submit_run"><input type="hidden" name="run_id" value="<?= (int)$run['id'] ?>"><div class="notice info">Submitting locks this draft for an independent reviewer. Only the workspace owner or another payroll editor can approve it; the preparer cannot self-approve. Approval publishes payslips. Salary Vault funds are reserved before each Paystack transfer. Check each amount first; compensation and bank details are snapshots from when this draft was created.</div><button class="button" type="submit" onclick="return confirm('Submit and lock this payroll run for independent approval?')">Submit for approval</button></form><form method="post" style="margin-top:12px" data-submit><?= csrf_field() ?><input type="hidden" name="action" value="cancel_draft"><input type="hidden" name="run_id" value="<?= (int)$run['id'] ?>"><button class="button secondary small" type="submit" onclick="return confirm('Delete this draft and all of its unapproved payroll items?')">Delete draft</button></form></div><?php elseif ($run['status'] === 'pending_approval'): ?><div class="panel-body"><?php if ((int)$run['created_by'] === $userId && ($run['created_by_role'] ?? 'admin') === $actorRole): ?><div class="notice info">This run is awaiting approval from a different authorized person. You prepared it and cannot approve it yourself.</div><?php else: ?><form method="post" data-submit><?= csrf_field() ?><input type="hidden" name="action" value="approve_run"><input type="hidden" name="run_id" value="<?= (int)$run['id'] ?>"><div class="notice warning">Confirm every payroll line and statutory deduction before approving. You cannot approve a run that you prepared.</div><button class="button" type="submit" onclick="return confirm('Approve and publish this payroll run?')">Approve payroll run</button></form><?php endif; ?></div><?php endif; ?></section>
<?php endif; ?>
<div class="dashboard-columns"><section class="panel" id="runs"><div class="panel-head"><h2>Pay runs</h2></div><?php if (!$runs): ?><div class="empty-state"><h3>No pay runs yet</h3><p>Create salary profiles for all active staff, then start a draft run.</p></div><?php else: ?><div class="table-wrap"><table class="data-table"><thead><tr><th>Period</th><th>Pay date</th><th>Staff</th><th>Net total</th><th>Status</th></tr></thead><tbody><?php foreach ($runs as $entry): ?><tr><td><a class="text-link" href="/users/admin/payroll.php?run=<?= (int)$entry['id'] ?>"><?= e(date('M Y',strtotime($entry['period_start']))) ?></a></td><td><?= e(date('j M Y',strtotime($entry['pay_date']))) ?></td><td><?= (int)$entry['staff_count'] ?></td><td><?= $money($entry['net_total']) ?></td><td><?= e(ucfirst($entry['status'])) ?></td></tr><?php endforeach; ?></tbody></table></div><?php endif; ?></section>
<div>
<section class="panel"><div class="panel-head"><h2>Create a draft run</h2></div><div class="panel-body"><p class="subtle">The first release supports one complete calendar month per run.</p><form method="post" class="form-stack" data-submit><?= csrf_field() ?><input type="hidden" name="action" value="create_run"><div class="field"><label for="period_start">Period starts</label><input id="period_start" name="period_start" type="date" required></div><div class="field"><label for="period_end">Period ends</label><input id="period_end" name="period_end" type="date" required></div><div class="field"><label for="pay_date">Pay date</label><input id="pay_date" name="pay_date" type="date" required></div><button class="button" type="submit">Create draft run</button></form></div></section>
<section class="panel" id="profiles"><div class="panel-head"><h2>Compensation profiles</h2></div><div class="panel-body"><form method="post" class="form-stack" data-submit><?= csrf_field() ?><input type="hidden" name="action" value="save_profile"><div class="field"><label for="person">Staff member</label><select name="staff_id" id="person" required><option value="">Choose staff</option><?php foreach ($people as $person): $profile = $profileByStaff[$person['staff_role'] . ':' . $person['id']] ?? null; ?><option value="<?= (int)$person['id'] ?>" data-role="<?= e($person['staff_role']) ?>" data-salary="<?= e($profile['monthly_salary'] ?? '') ?>" data-housing="<?= e($profile['monthly_housing'] ?? '0.00') ?>" data-transport="<?= e($profile['monthly_transport'] ?? '0.00') ?>" data-allowances="<?= e($profile['monthly_allowances'] ?? '0.00') ?>" data-rent="<?= e($profile['annual_rent_paid'] ?? '0.00') ?>" data-pension="<?= (int)($profile['pension_applicable'] ?? 0) ?>" data-nhf="<?= (int)($profile['nhf_applicable'] ?? 0) ?>" data-rent-documented="<?= (int)($profile['rent_relief_documented'] ?? 0) ?>" data-rent-reference="<?= e($profile['rent_document_reference'] ?? '') ?>" data-bank="<?= e($profile['bank_name'] ?? '') ?>" data-bank-code="<?= e($profile['bank_code'] ?? '') ?>" data-account-name="<?= e($profile['account_name'] ?? '') ?>"><?= e($person['staff_name'] . ' · ' . ucfirst($person['staff_role']) . ($profile ? ' · ' . $profile['bank_name'] . ' · account ending ' . substr(decrypt_secret($profile['account_number_ciphertext']),-4) : ' · profile needed')) ?></option><?php endforeach; ?></select><input type="hidden" name="staff_role" id="staff-role"></div><div class="field"><label for="monthly_salary">Monthly basic salary (NGN)</label><input id="monthly_salary" name="monthly_salary" type="number" min="0.01" step="0.01" required></div><div class="field"><label for="monthly_housing">Monthly housing allowance (NGN)</label><input id="monthly_housing" name="monthly_housing" type="number" min="0" step="0.01" value="0.00" required></div><div class="field"><label for="monthly_transport">Monthly transport allowance (NGN)</label><input id="monthly_transport" name="monthly_transport" type="number" min="0" step="0.01" value="0.00" required></div><div class="field"><label for="monthly_allowances">Other monthly allowances (NGN)</label><input id="monthly_allowances" name="monthly_allowances" type="number" min="0" step="0.01" value="0.00" required></div><div class="field"><label for="annual_rent_paid">Annual rent paid (NGN)</label><input id="annual_rent_paid" name="annual_rent_paid" type="number" min="0" step="0.01" value="0.00" required></div><label><input id="pension_applicable" name="pension_applicable" type="checkbox" value="1"> Employee pension applies</label><label><input id="nhf_applicable" name="nhf_applicable" type="checkbox" value="1"> NHF applies</label><label><input id="rent_relief_documented" name="rent_relief_documented" type="checkbox" value="1"> Rent relief documents verified</label><div class="field"><label for="rent_document_reference">Rent document/reference</label><input id="rent_document_reference" name="rent_document_reference" maxlength="255"></div><div class="field"><label for="bank_name">Bank name</label><input id="bank_name" name="bank_name" maxlength="120" required></div><div class="field"><label for="bank_code">Nigerian bank code</label><input id="bank_code" name="bank_code" maxlength="20" required></div><div class="field"><label for="account_name">Account name</label><input id="account_name" name="account_name" maxlength="160" required></div><div class="field"><label for="account_number">10-digit account number</label><input id="account_number" name="account_number" type="password" inputmode="numeric" pattern="[0-9]{10}" autocomplete="off"><small class="subtle">Leave blank to keep an existing account number.</small></div><button class="button secondary" type="submit">Save compensation profile</button><small class="subtle">Account numbers are encrypted at rest. Use an account number the employee has authorized for salary payments.</small></form></div></section>
</div></div>
<script>document.getElementById('person')?.addEventListener('change',function(){const option=this.selectedOptions[0],field=(id,value)=>{document.getElementById(id).value=value||''};document.getElementById('staff-role').value=option?.dataset.role||'';field('monthly_salary',option?.dataset.salary);field('monthly_housing',option?.dataset.housing||'0.00');field('monthly_transport',option?.dataset.transport||'0.00');field('monthly_allowances',option?.dataset.allowances||'0.00');field('annual_rent_paid',option?.dataset.rent||'0.00');field('rent_document_reference',option?.dataset.rentReference);document.getElementById('pension_applicable').checked=option?.dataset.pension==='1';document.getElementById('nhf_applicable').checked=option?.dataset.nhf==='1';document.getElementById('rent_relief_documented').checked=option?.dataset.rentDocumented==='1';field('bank_name',option?.dataset.bank);field('bank_code',option?.dataset.bankCode);field('account_name',option?.dataset.accountName);field('account_number','')});</script>
<?php suite_end(); ?>
