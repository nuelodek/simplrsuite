<?php
declare(strict_types=1);
require_once __DIR__ . '/accounting.php';
require_once __DIR__ . '/secrets.php';
require_once __DIR__ . '/paystack.php';

function payroll_amount_cents(mixed $value): ?int {
    return accounting_parse_amount($value);
}

function payroll_period_valid(string $start, string $end, string $payDate): bool {
    return accounting_date_valid($start)
        && accounting_date_valid($end)
        && accounting_date_valid($payDate)
        && $start <= $end
        && $payDate >= $end
        && substr($start,0,7) === substr($end,0,7)
        && substr($start,8,2) === '01'
        && substr($end,8,2) === date('t',strtotime($end));
}

function payroll_provider_amount_matches(mixed $providerAmount, mixed $currency, int $netCents): bool {
    return (is_int($providerAmount) || is_string($providerAmount))
        && ctype_digit((string)$providerAmount)
        && (int)$providerAmount === $netCents * 100
        && $currency === 'NGN';
}

function payroll_net_cents(array $item): int {
    $gross = 0;
    $deductions = 0;
    foreach (['basic_pay','allowances','bonus'] as $field) {
        $amount = payroll_amount_cents($item[$field] ?? null);
        if ($amount === null || $amount < 0) throw new InvalidArgumentException('Payroll earnings must be valid non-negative amounts.');
        $gross += $amount;
    }
    foreach (['paye','pension','nhf','other_deductions'] as $field) {
        $amount = payroll_amount_cents($item[$field] ?? null);
        if ($amount === null || $amount < 0) throw new InvalidArgumentException('Payroll deductions must be valid non-negative amounts.');
        $deductions += $amount;
    }
    if ($deductions > $gross) throw new InvalidArgumentException('Total deductions cannot exceed gross pay.');
    return $gross - $deductions;
}

function payroll_money(int $cents, string $currency = 'NGN'): string {
    return e($currency . ' ' . accounting_currency_amount($cents, ''));
}

function payroll_staff(mysqli $db, int $companyId): array {
    $employees = db_rows($db,"SELECT id, 'employee' AS staff_role, TRIM(CONCAT(first_name, ' ', last_name)) AS staff_name, email AS staff_email FROM employees WHERE company_id = ? AND archived_at IS NULL ORDER BY first_name, last_name",[$companyId]);
    $managers = db_rows($db,"SELECT id, 'manager' AS staff_role, manager_name AS staff_name, manager_email AS staff_email FROM managers WHERE company_id = ? AND archived_at IS NULL AND status NOT IN ('suspended','disabled','inactive') ORDER BY manager_name",[$companyId]);
    return [...$employees,...$managers];
}

function payroll_profile_save(mysqli $db, int $companyId, int $userId, array $input): void {
    $role = is_string($input['staff_role'] ?? null) ? $input['staff_role'] : '';
    $staffId = filter_var($input['staff_id'] ?? null,FILTER_VALIDATE_INT,['options'=>['min_range'=>1]]) ?: 0;
    if (!in_array($role,['employee','manager'],true) || !$staffId) fail('Choose an active employee or manager in your workspace.',422);
    $staff = db_one($db,$role === 'employee'
        ? "SELECT id FROM employees WHERE id = ? AND company_id = ? AND archived_at IS NULL"
        : "SELECT id FROM managers WHERE id = ? AND company_id = ? AND archived_at IS NULL AND status NOT IN ('suspended','disabled','inactive')",[$staffId,$companyId]);
    if (!$staff) fail('Choose an active person from your workspace.',422);
    $salary = is_string($input['monthly_salary'] ?? null) ? trim($input['monthly_salary']) : '';
    $allowances = is_string($input['monthly_allowances'] ?? null) ? trim($input['monthly_allowances']) : '0';
    if (($salaryCents = payroll_amount_cents($salary)) === null || $salaryCents <= 0 || ($allowanceCents = payroll_amount_cents($allowances)) === null) {
        fail('Enter a positive monthly salary and valid non-negative allowances.',422);
    }
    $bankName = is_string($input['bank_name'] ?? null) ? trim($input['bank_name']) : '';
    $bankCode = is_string($input['bank_code'] ?? null) ? trim($input['bank_code']) : '';
    $accountName = is_string($input['account_name'] ?? null) ? trim($input['account_name']) : '';
    $accountNumber = is_string($input['account_number'] ?? null) ? preg_replace('/\s+/', '', trim($input['account_number'])) : '';
    $existing = db_one($db,'SELECT account_number_ciphertext FROM payroll_profiles WHERE company_id = ? AND staff_role = ? AND staff_id = ?',[$companyId,$role,$staffId]);
    if ($accountNumber === '' && $existing) $accountNumber = decrypt_secret($existing['account_number_ciphertext']);
    if ($bankName === '' || mb_strlen($bankName) > 120 || !preg_match('/^[A-Za-z0-9_-]{1,20}$/D',$bankCode) || $accountName === '' || mb_strlen($accountName) > 160 || !preg_match('/^[0-9]{10}$/D',$accountNumber)) {
        fail('Enter a bank name, valid bank code, account name, and 10-digit Nigerian account number.',422);
    }
    $ciphertext = encrypt_secret($accountNumber);
    db_run($db,'INSERT INTO payroll_profiles (company_id,staff_role,staff_id,monthly_salary,monthly_allowances,bank_name,bank_code,account_name,account_number_ciphertext,updated_by) VALUES (?,?,?,?,?,?,?,?,?,?) ON DUPLICATE KEY UPDATE monthly_salary = VALUES(monthly_salary), monthly_allowances = VALUES(monthly_allowances), bank_name = VALUES(bank_name), bank_code = VALUES(bank_code), account_name = VALUES(account_name), account_number_ciphertext = VALUES(account_number_ciphertext), updated_by = VALUES(updated_by)',[
        $companyId,$role,$staffId,accounting_decimal_from_cents($salaryCents),accounting_decimal_from_cents($allowanceCents),$bankName,$bankCode,$accountName,$ciphertext,$userId,
    ]);
    audit_event($db,$companyId,'payroll.profile_updated',$role,(string)$staffId,['bank_account_changed'=>!$existing || decrypt_secret($existing['account_number_ciphertext']) !== $accountNumber]);
}

function payroll_create_run(mysqli $db, int $companyId, int $userId, string $start, string $end, string $payDate): int {
    if (!payroll_period_valid($start,$end,$payDate)) {
        fail('Enter one complete calendar month and a pay date on or after the period end.',422);
    }
    $people = payroll_staff($db,$companyId);
    if (!$people) fail('Add active staff before creating a payroll run.',422);
    $profiles = db_rows($db,'SELECT * FROM payroll_profiles WHERE company_id = ? ORDER BY staff_role, staff_id',[$companyId]);
    $byPerson = [];
    foreach ($profiles as $profile) $byPerson[$profile['staff_role'] . ':' . $profile['staff_id']] = $profile;
    foreach ($people as $person) if (!isset($byPerson[$person['staff_role'] . ':' . $person['id']])) {
        fail('Set up salary and bank details for every active staff member before creating a run.',422);
    }
    $db->begin_transaction();
    try {
        db_run($db,'INSERT INTO payroll_runs (company_id,period_start,period_end,pay_date,created_by) VALUES (?,?,?,?,?)',[$companyId,$start,$end,$payDate,$userId]);
        $runId = (int)$db->insert_id;
        foreach ($people as $person) {
            $profile = $byPerson[$person['staff_role'] . ':' . $person['id']];
            $basic = payroll_amount_cents($profile['monthly_salary']);
            $allowance = payroll_amount_cents($profile['monthly_allowances']);
            if ($basic === null || $allowance === null) throw new UnexpectedValueException('A saved payroll profile has invalid compensation amounts.');
            db_run($db,'INSERT INTO payroll_items (company_id,run_id,staff_role,staff_id,staff_name,staff_email,bank_name,bank_code,account_name,account_number_ciphertext,basic_pay,allowances,net_pay) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?)',[
                $companyId,$runId,$person['staff_role'],$person['id'],$person['staff_name'],$person['staff_email'],$profile['bank_name'],$profile['bank_code'],$profile['account_name'],$profile['account_number_ciphertext'],$profile['monthly_salary'],$profile['monthly_allowances'],accounting_decimal_from_cents($basic + $allowance),
            ]);
        }
        audit_event($db,$companyId,'payroll.run_created','payroll_run',(string)$runId,['staff_count'=>count($people),'period_start'=>$start,'period_end'=>$end]);
        $db->commit();
        return $runId;
    } catch (Throwable $error) {
        $db->rollback();
        if ($error instanceof mysqli_sql_exception && $error->getCode() === 1062) fail('A payroll run already exists for that period.',409);
        throw $error;
    }
}

function payroll_update_item(mysqli $db, int $companyId, int $runId, int $itemId, array $input): void {
    $fields = ['basic_pay','allowances','bonus','paye','pension','nhf','other_deductions'];
    $values = [];
    foreach ($fields as $field) {
        $raw = is_string($input[$field] ?? null) ? trim($input[$field]) : '';
        $cents = payroll_amount_cents($raw);
        if ($cents === null || $cents < 0) fail('Enter valid non-negative amounts for every payroll line.',422);
        $values[$field] = accounting_decimal_from_cents($cents);
    }
    try { $net = payroll_net_cents($values); }
    catch (InvalidArgumentException $error) { fail($error->getMessage(),422); }
    $db->begin_transaction();
    try {
        $run = db_one($db,"SELECT id FROM payroll_runs WHERE id = ? AND company_id = ? AND status = 'draft' FOR UPDATE",[$runId,$companyId]);
        $item = db_one($db,'SELECT id FROM payroll_items WHERE id = ? AND run_id = ? AND company_id = ? FOR UPDATE',[$itemId,$runId,$companyId]);
        if (!$run || !$item) { $db->rollback(); fail('Only a draft payroll item in your workspace can be changed.',409); }
        $assignments = implode(', ',array_map(static fn(string $field): string => "`$field` = ?",[...$fields,'net_pay']));
        db_run($db,"UPDATE payroll_items SET $assignments WHERE id = ? AND run_id = ? AND company_id = ?",[...array_values($values),accounting_decimal_from_cents($net),$itemId,$runId,$companyId]);
        audit_event($db,$companyId,'payroll.item_updated','payroll_item',(string)$itemId,['net_pay'=>accounting_decimal_from_cents($net)]);
        $db->commit();
    } catch (Throwable $error) { $db->rollback(); throw $error; }
}

function payroll_cancel_draft(mysqli $db, int $companyId, int $runId): void {
    $db->begin_transaction();
    try {
        $run = db_one($db,"SELECT id FROM payroll_runs WHERE id = ? AND company_id = ? AND status = 'draft' FOR UPDATE",[$runId,$companyId]);
        if (!$run) { $db->rollback(); fail('Only a draft run can be deleted.',409); }
        db_run($db,'DELETE FROM payroll_items WHERE run_id = ? AND company_id = ?',[$runId,$companyId]);
        db_run($db,'DELETE FROM payroll_runs WHERE id = ? AND company_id = ?',[$runId,$companyId]);
        audit_event($db,$companyId,'payroll.draft_deleted','payroll_run',(string)$runId);
        $db->commit();
    } catch (Throwable $error) { $db->rollback(); throw $error; }
}

function payroll_approve_run(mysqli $db, int $companyId, int $runId, int $userId): void {
    $db->begin_transaction();
    try {
        $run = db_one($db,"SELECT id FROM payroll_runs WHERE id = ? AND company_id = ? AND status = 'draft' FOR UPDATE",[$runId,$companyId]);
        $items = db_rows($db,'SELECT * FROM payroll_items WHERE run_id = ? AND company_id = ? ORDER BY id FOR UPDATE',[$runId,$companyId]);
        if (!$run || !$items) { $db->rollback(); fail('Only a non-empty draft run in your workspace can be approved.',409); }
        foreach ($items as $item) {
            try { payroll_net_cents($item); }
            catch (InvalidArgumentException $error) { $db->rollback(); fail('Payroll item ' . $item['staff_name'] . ' is invalid: ' . $error->getMessage(),422); }
            if (payroll_amount_cents($item['net_pay']) !== payroll_net_cents($item)) { $db->rollback(); fail('Payroll totals changed. Review the draft before approval.',409); }
        }
        db_run($db,"UPDATE payroll_runs SET status = 'approved', approved_by = ?, approved_at = UTC_TIMESTAMP() WHERE id = ? AND company_id = ?",[$userId,$runId,$companyId]);
        audit_event($db,$companyId,'payroll.run_approved','payroll_run',(string)$runId,['staff_count'=>count($items)]);
        $db->commit();
    } catch (Throwable $error) { $db->rollback(); throw $error; }
}

function payroll_mark_bank_paid(mysqli $db, int $companyId, int $runId, int $itemId, string $reference): void {
    $reference = trim($reference);
    if (!preg_match('/^[A-Za-z0-9][A-Za-z0-9._\/-]{0,99}$/D',$reference)) fail('Enter a bank transfer reference using letters, numbers, dots, slashes, or hyphens.',422);
    $db->begin_transaction();
    try {
        $run = db_one($db,"SELECT id FROM payroll_runs WHERE id = ? AND company_id = ? AND status IN ('approved','processing','paid') FOR UPDATE",[$runId,$companyId]);
        $item = db_one($db,"SELECT id,payout_status FROM payroll_items WHERE id = ? AND run_id = ? AND company_id = ? FOR UPDATE",[$itemId,$runId,$companyId]);
        if (!$run || !$item || !in_array($item['payout_status'],['not_started','failed'],true)) {
            $db->rollback();
            fail('Only a payroll item without a pending or ambiguous provider transfer can be marked paid manually.',409);
        }
        db_run($db,"UPDATE payroll_items SET payout_status = 'paid', bank_payment_reference = ?, paid_at = UTC_TIMESTAMP() WHERE id = ? AND run_id = ? AND company_id = ?",[$reference,$itemId,$runId,$companyId]);
        payroll_refresh_run_status($db,$companyId,$itemId);
        audit_event($db,$companyId,'payroll.bank_payment_recorded','payroll_item',(string)$itemId,['reference'=>$reference]);
        $db->commit();
    } catch (Throwable $error) { $db->rollback(); throw $error; }
}

function payroll_paystack_enabled(): bool {
    return config('PAYROLL_PAYSTACK_TRANSFERS_ENABLED','false') === 'true' && paystack_ready();
}

function payroll_transfer(mysqli $db, int $companyId, int $runId, int $itemId, ?callable $transport = null): string {
    if (!payroll_paystack_enabled()) throw new PaymentUnavailable('Paystack payroll transfers are disabled or not configured. Enable PAYROLL_PAYSTACK_TRANSFERS_ENABLED only after verifying the workspace payout account.');
    if (config('APP_ENV') === 'local' && config('PAYSTACK_MODE','test') !== 'test') throw new PaymentUnavailable('Live Paystack transfers are blocked in local environments.');
    $db->begin_transaction();
    try {
        $item = db_one($db,"SELECT i.* FROM payroll_items i JOIN payroll_runs r ON r.id = i.run_id AND r.company_id = i.company_id WHERE i.id = ? AND i.run_id = ? AND i.company_id = ? AND r.status IN ('approved','processing','paid') FOR UPDATE",[$itemId,$runId,$companyId]);
        if (!$item) { $db->rollback(); fail('This approved payroll item could not be found in your workspace.',404); }
        if (!in_array($item['payout_status'],['not_started','failed'],true)) { $db->rollback(); fail('This payout is already pending, completed, or needs manual reconciliation. It will not be sent again.',409); }
        $net = payroll_amount_cents($item['net_pay']);
        if ($net === null || $net <= 0) { $db->rollback(); fail('A positive net pay amount is required before payout.',422); }
        $recipient = (string)$item['paystack_recipient_code'];
        $accountNumber = decrypt_secret($item['account_number_ciphertext']);
        $db->commit();
    } catch (Throwable $error) { $db->rollback(); throw $error; }

    if ($recipient === '') {
        $created = paystack_api('POST','/transferrecipient',[
            'type'=>'nuban','name'=>$item['account_name'],'account_number'=>$accountNumber,
            'bank_code'=>$item['bank_code'],'currency'=>'NGN','description'=>'Simplr payroll recipient',
        ],$transport);
        $recipient = is_string($created['recipient_code'] ?? null) ? $created['recipient_code'] : '';
        if (!preg_match('/^RCP_[A-Za-z0-9]+$/D',$recipient)) throw new PaymentUnavailable('Paystack did not return a valid bank recipient. No transfer was started.');
        $details = is_array($created['details'] ?? null) ? $created['details'] : [];
        $verifiedName = preg_replace('/[^a-z0-9]/','',strtolower((string)($details['account_name'] ?? '')));
        $expectedName = preg_replace('/[^a-z0-9]/','',strtolower((string)$item['account_name']));
        if ((string)($details['account_number'] ?? '') !== $accountNumber || (string)($details['bank_code'] ?? '') !== (string)$item['bank_code'] || $verifiedName === '' || !hash_equals($expectedName,$verifiedName)) {
            throw new PaymentUnavailable('The bank account details returned by Paystack did not match the saved payroll profile. No transfer was started.');
        }
        db_run($db,"UPDATE payroll_items SET paystack_recipient_code = ? WHERE id = ? AND run_id = ? AND company_id = ? AND payout_status = 'not_started'",[$recipient,$itemId,$runId,$companyId]);
    }

    $reference = 'ss-payroll-' . $runId . '-' . $itemId . '-' . bin2hex(random_bytes(8));
    $db->begin_transaction();
    try {
        $locked = db_one($db,"SELECT payout_status FROM payroll_items WHERE id = ? AND run_id = ? AND company_id = ? FOR UPDATE",[$itemId,$runId,$companyId]);
        if (!$locked || !in_array($locked['payout_status'],['not_started','failed'],true)) { $db->rollback(); fail('This payout is already being processed. Do not retry it.',409); }
        db_run($db,"UPDATE payroll_items SET payout_status = 'processing', payout_reference = ? WHERE id = ? AND run_id = ? AND company_id = ?",[$reference,$itemId,$runId,$companyId]);
        db_run($db,"UPDATE payroll_runs SET status = 'processing' WHERE id = ? AND company_id = ? AND status = 'approved'",[$runId,$companyId]);
        audit_event($db,$companyId,'payroll.transfer_requested','payroll_item',(string)$itemId,['reference'=>$reference,'amount'=>accounting_decimal_from_cents($net),'currency'=>'NGN']);
        $db->commit();
    } catch (Throwable $error) { $db->rollback(); throw $error; }
    try {
        $result = paystack_api('POST','/transfer',[
            'source'=>'balance','amount'=>(string)($net * 100),'recipient'=>$recipient,
            'reference'=>$reference,'reason'=>'Payroll ' . $runId . ' · ' . $item['staff_name'],'currency'=>'NGN',
        ],$transport);
        $status = (string)($result['status'] ?? '');
        if (($result['reference'] ?? '') !== $reference || !payroll_provider_amount_matches($result['amount'] ?? null,$result['currency'] ?? null,$net)) {
            throw new PaymentUnavailable('Paystack returned a transfer that did not match the approved payroll amount and reference.');
        }
        if ($status === 'otp' && is_string($result['transfer_code'] ?? null)) {
            db_run($db,"UPDATE payroll_items SET payout_status = 'otp_required', provider_transfer_code_ciphertext = ? WHERE id = ? AND run_id = ? AND company_id = ? AND payout_reference = ?",[encrypt_secret($result['transfer_code']),$itemId,$runId,$companyId,$reference]);
            return 'otp_required';
        }
        if ($status === 'success') {
            db_run($db,"UPDATE payroll_items SET payout_status = 'paid', paid_at = UTC_TIMESTAMP() WHERE id = ? AND run_id = ? AND company_id = ? AND payout_reference = ?",[$itemId,$runId,$companyId,$reference]);
            payroll_refresh_run_status($db,$companyId,$itemId);
            audit_event($db,$companyId,'payroll.transfer_initiated','payroll_item',(string)$itemId,['reference'=>$reference,'provider_status'=>'success']);
            return 'paid';
        }
        db_run($db,"UPDATE payroll_items SET payout_status = 'pending' WHERE id = ? AND run_id = ? AND company_id = ? AND payout_reference = ?",[$itemId,$runId,$companyId,$reference]);
        return 'pending';
    } catch (Throwable $error) {
        db_run($db,"UPDATE payroll_items SET payout_status = 'unknown' WHERE id = ? AND run_id = ? AND company_id = ? AND payout_reference = ? AND payout_status = 'processing'",[$itemId,$runId,$companyId,$reference]);
        error_log('Paystack payroll transfer outcome unknown for reference ' . $reference . ': ' . get_class($error));
        throw new PaymentUnavailable('The transfer outcome could not be confirmed. This payout is locked against retries; reconcile its Paystack reference before taking further action.');
    }
}

function payroll_finalize_transfer(mysqli $db, int $companyId, int $runId, int $itemId, string $otp): void {
    if (!payroll_paystack_enabled() || !preg_match('/^[0-9]{6,8}$/D',$otp)) fail('Enter the valid one-time transfer authorization code.',422);
    $db->begin_transaction();
    try {
        $item = db_one($db,"SELECT payout_reference, provider_transfer_code_ciphertext FROM payroll_items WHERE id = ? AND run_id = ? AND company_id = ? AND payout_status = 'otp_required' FOR UPDATE",[$itemId,$runId,$companyId]);
        if (!$item || !$item['provider_transfer_code_ciphertext']) { $db->rollback(); fail('This payroll transfer is not awaiting authorization.',409); }
        $transferCode = decrypt_secret($item['provider_transfer_code_ciphertext']);
        db_run($db,"UPDATE payroll_items SET payout_status = 'authorizing' WHERE id = ? AND run_id = ? AND company_id = ? AND payout_status = 'otp_required'",[$itemId,$runId,$companyId]);
        $db->commit();
    } catch (Throwable $error) { $db->rollback(); throw $error; }
    try {
        $result = paystack_api('POST','/transfer/finalize_transfer',['transfer_code'=>$transferCode,'otp'=>$otp]);
        $status = (string)($result['status'] ?? '');
        if ($status === 'success') {
            db_run($db,"UPDATE payroll_items SET payout_status = 'paid', paid_at = UTC_TIMESTAMP(), provider_transfer_code_ciphertext = NULL WHERE id = ? AND run_id = ? AND company_id = ? AND payout_status = 'authorizing'",[$itemId,$runId,$companyId]);
            payroll_refresh_run_status($db,$companyId,$itemId);
            audit_event($db,$companyId,'payroll.transfer_authorized','payroll_item',(string)$itemId,['reference'=>$item['payout_reference']]);
            return;
        }
        db_run($db,"UPDATE payroll_items SET payout_status = 'pending', provider_transfer_code_ciphertext = NULL WHERE id = ? AND run_id = ? AND company_id = ? AND payout_status = 'authorizing'",[$itemId,$runId,$companyId]);
    } catch (Throwable $error) {
        db_run($db,"UPDATE payroll_items SET payout_status = 'unknown', provider_transfer_code_ciphertext = NULL WHERE id = ? AND run_id = ? AND company_id = ? AND payout_status = 'authorizing'",[$itemId,$runId,$companyId]);
        throw new PaymentUnavailable('Paystack could not confirm the authorization. This payout is locked against retries; reconcile its reference before taking further action.');
    }
}

function payroll_transfer_webhook(mysqli $db, string $event, array $data): bool {
    if (!in_array($event,['transfer.success','transfer.failed','transfer.reversed'],true)) return false;
    $reference = $data['reference'] ?? null;
    if (!is_string($reference) || !preg_match('/^ss-payroll-[0-9]+-[0-9]+-[a-f0-9]{16}$/D',$reference)) return false;
    $status = match($event) { 'transfer.success'=>'paid','transfer.failed'=>'failed','transfer.reversed'=>'reversed' };
    $paidAt = $status === 'paid' ? gmdate('Y-m-d H:i:s') : null;
    $db->begin_transaction();
    try {
        $item = db_one($db,'SELECT id, company_id, payout_status, net_pay, paystack_recipient_code FROM payroll_items WHERE payout_reference = ? FOR UPDATE',[$reference]);
        if (!$item) { $db->commit(); return false; }
        $expectedCents = payroll_amount_cents($item['net_pay']);
        if ($expectedCents === null || !payroll_provider_amount_matches($data['amount'] ?? null,$data['currency'] ?? null,$expectedCents)) {
            $db->commit();
            return false;
        }
        $recipientData = is_array($data['recipient'] ?? null) ? $data['recipient'] : [];
        $recipient = $data['recipient_code'] ?? ($recipientData['recipient_code'] ?? null);
        if (is_string($recipient) && $recipient !== '' && !hash_equals((string)$item['paystack_recipient_code'],$recipient)) {
            $db->commit();
            return false;
        }
        if ($item['payout_status'] !== 'reversed' && !($item['payout_status'] === 'paid' && $status !== 'reversed')) {
            db_run($db,'UPDATE payroll_items SET payout_status = ?, paid_at = ? WHERE id = ? AND payout_reference = ?',[$status,$paidAt,$item['id'],$reference]);
            audit_event($db,(int)$item['company_id'],'payroll.transfer_webhook','payroll_item',(string)$item['id'],['event'=>$event,'reference'=>$reference],'system',0);
        }
        payroll_refresh_run_status($db,(int)$item['company_id'],(int)$item['id']);
        $db->commit();
        return true;
    } catch (Throwable $error) { $db->rollback(); throw $error; }
}

function payroll_refresh_run_status(mysqli $db, int $companyId, int $itemId): void {
    $item = db_one($db,'SELECT run_id FROM payroll_items WHERE id = ? AND company_id = ?',[$itemId,$companyId]);
    if (!$item) return;
    $counts = db_one($db,"SELECT COUNT(*) AS total, SUM(payout_status = 'paid') AS paid FROM payroll_items WHERE run_id = ? AND company_id = ?",[$item['run_id'],$companyId]);
    if ((int)$counts['total'] > 0 && (int)$counts['total'] === (int)$counts['paid']) {
        db_run($db,"UPDATE payroll_runs SET status = 'paid' WHERE id = ? AND company_id = ? AND status IN ('approved','processing','paid')",[$item['run_id'],$companyId]);
    } elseif ((int)$counts['paid'] < (int)$counts['total']) {
        db_run($db,"UPDATE payroll_runs SET status = 'processing' WHERE id = ? AND company_id = ? AND status = 'paid'",[$item['run_id'],$companyId]);
    }
}
