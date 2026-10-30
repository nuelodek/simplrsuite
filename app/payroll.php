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
        && (int)$providerAmount === $netCents
        && $currency === 'NGN';
}

function payroll_net_cents(array $item): int {
    $gross = 0;
    $deductions = 0;
    foreach (['basic_pay','housing','transport','allowances','bonus','overtime_pay'] as $field) {
        $amount = payroll_amount_cents($item[$field] ?? (in_array($field,['overtime_pay'],true) ? '0.00' : null));
        if ($amount === null || $amount < 0) throw new InvalidArgumentException('Payroll earnings must be valid non-negative amounts.');
        $gross += $amount;
    }
    foreach (['paye','pension','nhf','voluntary_pension','salary_advance_deduction','other_deductions'] as $field) {
        $amount = payroll_amount_cents($item[$field] ?? (in_array($field,['voluntary_pension','salary_advance_deduction'],true) ? '0.00' : null));
        if ($amount === null || $amount < 0) throw new InvalidArgumentException('Payroll deductions must be valid non-negative amounts.');
        $deductions += $amount;
    }
    if ($deductions > $gross) throw new InvalidArgumentException('Total deductions cannot exceed gross pay.');
    if ($deductions > intdiv($gross,3)) throw new InvalidArgumentException('Total employee deductions exceed the one-third gross-pay safeguard. Review voluntary deductions and statutory inputs before continuing.');
    return $gross - $deductions;
}

function payroll_nigerian_2026_estimate(array $item): array {
    $basic = payroll_amount_cents($item['basic_pay'] ?? null);
    $housing = payroll_amount_cents($item['housing'] ?? null);
    $transport = payroll_amount_cents($item['transport'] ?? null);
    $allowances = payroll_amount_cents($item['allowances'] ?? null);
    $bonus = payroll_amount_cents($item['bonus'] ?? null);
    $overtime = payroll_amount_cents($item['overtime_pay'] ?? '0.00');
    $annualRent = payroll_amount_cents($item['annual_rent_paid'] ?? null);
    if (in_array(null,[$basic,$housing,$transport,$allowances,$bonus,$overtime,$annualRent],true)
        || min($basic,$housing,$transport,$allowances,$bonus,$overtime,$annualRent) < 0) {
        throw new InvalidArgumentException('Nigerian payroll estimate inputs must be valid non-negative amounts.');
    }

    $monthlyRecurringGross = $basic + $housing + $transport + $allowances;
    $monthlyGross = $monthlyRecurringGross + $bonus + $overtime;
    $annualGross = $monthlyRecurringGross * 12 + $bonus + $overtime;
    $pensionAnnual = !empty($item['pension_applicable'])
        ? intdiv(($basic + $housing + $transport) * 12 * 8 + 50,100)
        : 0;
    $nhfAnnual = !empty($item['nhf_applicable'])
        ? intdiv($basic * 12 * 25 + 500,1000)
        : 0;
    $rentReliefAnnual = !empty($item['rent_relief_documented'])
        ? min(intdiv($annualRent * 20 + 50,100),50000000)
        : 0;
    $taxableAnnual = max(0,$annualGross - $pensionAnnual - $nhfAnnual - $rentReliefAnnual);

    $remaining = $taxableAnnual;
    $annualTax = 0;
    foreach ([[80000000,0,1],[220000000,15,100],[900000000,18,100],[1300000000,21,100],[2500000000,23,100],[PHP_INT_MAX,25,100]] as [$bandLimit,$rate,$denominator]) {
        if ($remaining <= 0) break;
        $inBand = min($remaining,$bandLimit);
        $annualTax += intdiv($inBand * $rate + intdiv($denominator,2),$denominator);
        $remaining -= $inBand;
    }
    if ($monthlyGross <= 7000000) $annualTax = 0;

    return [
        'paye'=>intdiv($annualTax + 6,12),
        'pension'=>intdiv($pensionAnnual + 6,12),
        'nhf'=>intdiv($nhfAnnual + 6,12),
        'annual_gross'=>$annualGross,
        'annual_taxable'=>$taxableAnnual,
        'annual_tax'=>$annualTax,
        'minimum_wage_exempt'=>$monthlyGross <= 7000000,
    ];
}

function payroll_apply_nigerian_2026_estimates(mysqli $db, int $companyId, int $runId, int $actorId, string $actorRole): void {
    $db->begin_transaction();
    try {
        $run = db_one($db,"SELECT id,period_start,statutory_settings_version FROM payroll_runs WHERE id = ? AND company_id = ? AND status = 'draft' FOR UPDATE",[$runId,$companyId]);
        if (!$run) { $db->rollback(); fail('Only a draft payroll run in your workspace can use statutory estimates.',409); }
        if (substr((string)$run['period_start'],0,4) !== '2026') {
            $db->rollback();
            fail('The selected Nigerian estimate schedule is for the 2026 tax year only.',422);
        }
        $items = db_rows($db,'SELECT * FROM payroll_items WHERE run_id = ? AND company_id = ? ORDER BY id FOR UPDATE',[$runId,$companyId]);
        if (!$items) { $db->rollback(); fail('This payroll run has no items to calculate.',409); }
        foreach ($items as $item) {
            $estimate = payroll_nigerian_2026_estimate($item);
            $values = [
                'basic_pay'=>$item['basic_pay'],
                'housing'=>$item['housing'],
                'transport'=>$item['transport'],
                'allowances'=>$item['allowances'],
                'bonus'=>$item['bonus'],
                'overtime_pay'=>$item['overtime_pay'],
                'paye'=>accounting_decimal_from_cents($estimate['paye']),
                'pension'=>accounting_decimal_from_cents($estimate['pension']),
                'nhf'=>accounting_decimal_from_cents($estimate['nhf']),
                'voluntary_pension'=>$item['voluntary_pension'],
                'salary_advance_deduction'=>$item['salary_advance_deduction'],
                'other_deductions'=>$item['other_deductions'],
            ];
            try { $net = payroll_net_cents($values); }
            catch (InvalidArgumentException $error) {
                $db->rollback();
                fail('Estimated payroll for ' . $item['staff_name'] . ' is not valid: ' . $error->getMessage(),422);
            }
            db_run($db,'UPDATE payroll_items SET previous_paye = IF(? = \'\',paye,previous_paye), previous_pension = IF(? = \'\',pension,previous_pension), previous_nhf = IF(? = \'\',nhf,previous_nhf), paye = ?, pension = ?, nhf = ?, net_pay = ? WHERE id = ? AND company_id = ? AND run_id = ?',[
                (string)$run['statutory_settings_version'],(string)$run['statutory_settings_version'],(string)$run['statutory_settings_version'],
                $values['paye'],$values['pension'],$values['nhf'],accounting_decimal_from_cents($net),$item['id'],$companyId,$runId,
            ]);
        }
        db_run($db,"UPDATE payroll_runs SET statutory_settings_version = 'nigeria-2026-user-estimate-v1', statutory_applied_by = ?, statutory_applied_at = UTC_TIMESTAMP() WHERE id = ? AND company_id = ?",[$actorId,$runId,$companyId]);
        audit_event($db,$companyId,'payroll.nigerian_estimates_applied','payroll_run',(string)$runId,['settings_version'=>'nigeria-2026-user-estimate-v1','staff_count'=>count($items)],$actorRole,$actorId);
        $db->commit();
    } catch (Throwable $error) { $db->rollback(); throw $error; }
}

function payroll_unapply_nigerian_2026_estimates(mysqli $db, int $companyId, int $runId, int $actorId, string $actorRole): void {
    $db->begin_transaction();
    try {
        $run = db_one($db,"SELECT id,statutory_settings_version FROM payroll_runs WHERE id = ? AND company_id = ? AND status = 'draft' FOR UPDATE",[$runId,$companyId]);
        if (!$run || $run['statutory_settings_version'] !== 'nigeria-2026-user-estimate-v1') {
            $db->rollback();
            fail('This draft does not have active Nigerian 2026 estimates to restore.',409);
        }
        $items = db_rows($db,'SELECT * FROM payroll_items WHERE run_id = ? AND company_id = ? ORDER BY id FOR UPDATE',[$runId,$companyId]);
        foreach ($items as $item) {
            $restored = [
                'basic_pay'=>$item['basic_pay'],'housing'=>$item['housing'],'transport'=>$item['transport'],
                'allowances'=>$item['allowances'],'bonus'=>$item['bonus'],
                'paye'=>$item['previous_paye'] ?? $item['paye'],
                'pension'=>$item['previous_pension'] ?? $item['pension'],
                'nhf'=>$item['previous_nhf'] ?? $item['nhf'],
                'voluntary_pension'=>$item['voluntary_pension'],
                'salary_advance_deduction'=>$item['salary_advance_deduction'],
                'overtime_pay'=>$item['overtime_pay'],
                'other_deductions'=>$item['other_deductions'],
            ];
            $restored['voluntary_pension'] = $item['voluntary_pension'];
            $restored['salary_advance_deduction'] = $item['salary_advance_deduction'];
            $restored['overtime_pay'] = $item['overtime_pay'];
            $net = payroll_net_cents($restored);
            db_run($db,'UPDATE payroll_items SET paye = ?, pension = ?, nhf = ?, previous_paye = NULL, previous_pension = NULL, previous_nhf = NULL, net_pay = ? WHERE id = ? AND company_id = ? AND run_id = ?',[
                $restored['paye'],$restored['pension'],$restored['nhf'],accounting_decimal_from_cents($net),$item['id'],$companyId,$runId,
            ]);
        }
        db_run($db,"UPDATE payroll_runs SET statutory_settings_version = '', statutory_applied_by = NULL, statutory_applied_at = NULL WHERE id = ? AND company_id = ?",[$runId,$companyId]);
        audit_event($db,$companyId,'payroll.nigerian_estimates_unapplied','payroll_run',(string)$runId,['staff_count'=>count($items)],$actorRole,$actorId);
        $db->commit();
    } catch (Throwable $error) { $db->rollback(); throw $error; }
}

function payroll_money(int $cents, string $currency = 'NGN'): string {
    return e($currency . ' ' . accounting_currency_amount($cents, ''));
}

function salary_vault_paystack_enabled(): bool {
    return config('SALARY_VAULT_PAYSTACK_TOPUPS_ENABLED','false') === 'true' && paystack_ready();
}

function salary_vault_balance(mysqli $db, int $companyId): int {
    db_run($db,"INSERT INTO salary_vaults (company_id) VALUES (?) ON DUPLICATE KEY UPDATE company_id = VALUES(company_id)",[$companyId]);
    $vault = db_one($db,'SELECT balance_kobo FROM salary_vaults WHERE company_id = ?',[$companyId]);
    if (!$vault) throw new RuntimeException('Salary Vault balance could not be loaded.');
    return (int)$vault['balance_kobo'];
}

function salary_vault_append_locked(mysqli $db, int $companyId, string $reference, string $type, int $amountKobo, int $actorId, string $actorRole, ?int $fundingId = null, ?int $payrollItemId = null): bool {
    if ($amountKobo === 0 || !in_array($type,['paystack_topup','manual_deposit','payroll_reservation','manual_payroll_payment','payroll_refund'],true)) {
        throw new InvalidArgumentException('Invalid Salary Vault ledger entry.');
    }
    $existing = db_one($db,'SELECT entry_type, amount_kobo FROM salary_vault_entries WHERE company_id = ? AND entry_reference = ?',[$companyId,$reference]);
    if ($existing) {
        if ($existing['entry_type'] !== $type || (int)$existing['amount_kobo'] !== $amountKobo) {
            throw new RuntimeException('A Salary Vault ledger reference was reused with different details.');
        }
        return false;
    }
    $vault = db_one($db,'SELECT balance_kobo FROM salary_vaults WHERE company_id = ? FOR UPDATE',[$companyId]);
    if (!$vault) throw new RuntimeException('Salary Vault balance row is missing.');
    $balance = (int)$vault['balance_kobo'];
    if ($amountKobo < 0 && $balance < -$amountKobo) fail('The Salary Vault does not have enough cleared funds for this payment.',409);
    if ($amountKobo > 0 && $balance > PHP_INT_MAX - $amountKobo) throw new OverflowException('Salary Vault balance exceeds the supported limit.');
    db_run($db,'INSERT INTO salary_vault_entries (company_id,entry_reference,entry_type,amount_kobo,funding_id,payroll_item_id,actor_role,actor_id) VALUES (?,?,?,?,?,?,?,?)',[$companyId,$reference,$type,$amountKobo,$fundingId,$payrollItemId,$actorRole,$actorId]);
    db_run($db,'UPDATE salary_vaults SET balance_kobo = ? WHERE company_id = ?',[$balance + $amountKobo,$companyId]);
    return true;
}

function salary_vault_create_paystack_funding(mysqli $db, int $companyId, int $actorId, string $actorRole, string $email, mixed $rawAmount, ?callable $transport = null): array {
    if (!salary_vault_paystack_enabled()) throw new PaymentUnavailable('Salary Vault Paystack top-ups are disabled or not configured.');
    if (config('APP_ENV') === 'local' && config('PAYSTACK_MODE','test') !== 'test') throw new PaymentUnavailable('Live Paystack top-ups are blocked in local environments.');
    $amount = payroll_amount_cents($rawAmount);
    if ($amount === null || $amount < 10000) fail('Enter a top-up amount of at least NGN 100.00.',422);
    if (!filter_var($email,FILTER_VALIDATE_EMAIL)) throw new InvalidArgumentException('A valid account email is required for Paystack checkout.');
    $reference = 'ss-vault-' . bin2hex(random_bytes(20));
    db_run($db,"INSERT INTO salary_vault_fundings (company_id,funding_method,amount_kobo,currency,status,reference,expected_email,provider_mode,created_by_role,created_by_id) VALUES (?,'paystack',?,'NGN','initializing',?,?,?, ?,?)",[$companyId,$amount,$reference,strtolower($email),config('PAYSTACK_MODE','test'),$actorRole,$actorId]);
    $fundingId = (int)$db->insert_id;
    audit_event($db,$companyId,'salary_vault.topup_requested','salary_vault_funding',(string)$fundingId,['amount'=>accounting_decimal_from_cents($amount),'reference'=>$reference],$actorRole,$actorId);
    try {
        $callback = audience_url('business','/users/admin/payroll.php');
        if (!str_starts_with($callback,'https://') && !str_starts_with($callback,'http://')) $callback = rtrim(config('APP_URL'),'/') . '/users/admin/payroll.php';
        $callback .= '?funding=' . rawurlencode($reference);
        $result = paystack_api('POST','/transaction/initialize',[
            'email'=>strtolower($email),'amount'=>(string)$amount,'currency'=>'NGN','reference'=>$reference,
            'callback_url'=>$callback,'metadata'=>['simplr_reference'=>$reference,'company_id'=>(string)$companyId,'purpose'=>'salary_vault_topup'],
        ],$transport);
        if (($result['reference'] ?? '') !== $reference || !paystack_checkout_url($result['authorization_url'] ?? null)) {
            throw new PaymentUnavailable('Paystack returned an unexpected Salary Vault checkout response.');
        }
        db_run($db,"UPDATE salary_vault_fundings SET checkout_url = ?, status = 'awaiting_payment' WHERE id = ? AND company_id = ? AND status = 'initializing'",[$result['authorization_url'],$fundingId,$companyId]);
    } catch (Throwable $error) {
        db_run($db,"UPDATE salary_vault_fundings SET status = 'initialization_unknown' WHERE id = ? AND company_id = ? AND status = 'initializing'",[$fundingId,$companyId]);
        if ($error instanceof PaymentUnavailable) throw $error;
        throw new PaymentUnavailable('Paystack checkout could not be confirmed. Check the Salary Vault funding history before trying again.');
    }
    return db_one($db,'SELECT id,reference,checkout_url,amount_kobo,status FROM salary_vault_fundings WHERE id = ? AND company_id = ?',[$fundingId,$companyId])
        ?? throw new RuntimeException('Salary Vault funding request could not be loaded.');
}

function salary_vault_submit_manual_funding(mysqli $db, int $companyId, int $actorId, string $actorRole, mixed $rawAmount, string $externalReference, string $note): int {
    $amount = payroll_amount_cents($rawAmount);
    $externalReference = trim($externalReference);
    $note = trim($note);
    if ($amount === null || $amount <= 0 || !preg_match('/^[A-Za-z0-9][A-Za-z0-9._\/-]{0,119}$/D',$externalReference) || mb_strlen($note) > 1000) {
        fail('Enter a positive amount, a valid bank deposit reference, and a note of at most 1,000 characters.',422);
    }
    $reference = 'ss-vault-manual-' . bin2hex(random_bytes(16));
    try {
        db_run($db,"INSERT INTO salary_vault_fundings (company_id,funding_method,amount_kobo,currency,status,reference,external_reference,note,created_by_role,created_by_id) VALUES (?,'manual',?,'NGN','pending_approval',?,?,?,?,?)",[$companyId,$amount,$reference,$externalReference,$note,$actorRole,$actorId]);
    } catch (mysqli_sql_exception $error) {
        if ($error->getCode() === 1062) fail('That deposit reference has already been submitted for this workspace.',409);
        throw $error;
    }
    $id = (int)$db->insert_id;
    audit_event($db,$companyId,'salary_vault.deposit_submitted','salary_vault_funding',(string)$id,['amount'=>accounting_decimal_from_cents($amount),'external_reference'=>$externalReference],$actorRole,$actorId);
    return $id;
}

function salary_vault_approve_manual_funding(mysqli $db, int $companyId, int $fundingId, int $actorId, string $actorRole): void {
    if (!in_array($actorRole,['admin','employee'],true)) fail('Manual deposit approval is limited to the workspace owner or an employee with payroll edit access.',403);
    $db->begin_transaction();
    try {
        $funding = db_one($db,"SELECT * FROM salary_vault_fundings WHERE id = ? AND company_id = ? AND funding_method = 'manual' AND status = 'pending_approval' FOR UPDATE",[$fundingId,$companyId]);
        if (!$funding) { $db->rollback(); fail('This manual deposit is no longer awaiting approval.',409); }
        if ($funding['created_by_role'] === $actorRole && (int)$funding['created_by_id'] === $actorId) {
            $db->rollback();
            fail('The person who submitted a deposit cannot approve it.',403);
        }
        salary_vault_balance($db,$companyId);
        $entryReference = 'funding:' . $funding['reference'];
        salary_vault_append_locked($db,$companyId,$entryReference,'manual_deposit',(int)$funding['amount_kobo'],$actorId,$actorRole,(int)$funding['id']);
        db_run($db,"UPDATE salary_vault_fundings SET status = 'credited', approved_by_role = ?, approved_by_id = ?, approved_at = UTC_TIMESTAMP(), credited_at = UTC_TIMESTAMP() WHERE id = ? AND company_id = ? AND status = 'pending_approval'",[$actorRole,$actorId,$fundingId,$companyId]);
        audit_event($db,$companyId,'salary_vault.deposit_approved','salary_vault_funding',(string)$fundingId,['amount'=>accounting_decimal_from_cents((int)$funding['amount_kobo']),'external_reference'=>$funding['external_reference']],$actorRole,$actorId);
        $db->commit();
    } catch (Throwable $error) { $db->rollback(); throw $error; }
}

function salary_vault_credit_verified_paystack(mysqli $db, array $funding, array $transaction): bool {
    $metadata = is_array($transaction['metadata'] ?? null) ? $transaction['metadata'] : [];
    if (($transaction['reference'] ?? '') !== $funding['reference']
        || ($transaction['status'] ?? '') !== 'success'
        || !payroll_provider_amount_matches($transaction['amount'] ?? null,$transaction['currency'] ?? null,(int)$funding['amount_kobo'])
        || strtolower((string)($transaction['customer']['email'] ?? '')) !== strtolower((string)$funding['expected_email'])
        || ($metadata['purpose'] ?? '') !== 'salary_vault_topup'
        || (string)($metadata['company_id'] ?? '') !== (string)$funding['company_id']) {
        throw new PaymentMismatch('The verified Paystack transaction does not match this Salary Vault top-up.');
    }
    $providerId = is_int($transaction['id'] ?? null) || (is_string($transaction['id'] ?? null) && ctype_digit($transaction['id']))
        ? (string)$transaction['id'] : '';
    if ($providerId === '') throw new PaymentMismatch('Paystack did not return a valid transaction ID for this top-up.');
    $db->begin_transaction();
    try {
        $locked = db_one($db,'SELECT * FROM salary_vault_fundings WHERE id = ? AND company_id = ? FOR UPDATE',[$funding['id'],$funding['company_id']]);
        if (!$locked) { $db->rollback(); return false; }
        if ($locked['status'] === 'credited') { $db->commit(); return true; }
        if (!in_array($locked['status'],['awaiting_payment','initialization_unknown'],true)
            || $locked['provider_mode'] !== config('PAYSTACK_MODE','test')) {
            $db->rollback();
            return false;
        }
        salary_vault_balance($db,(int)$locked['company_id']);
        salary_vault_append_locked($db,(int)$locked['company_id'],'funding:' . $locked['reference'],'paystack_topup',(int)$locked['amount_kobo'],(int)$locked['created_by_id'],(string)$locked['created_by_role'],(int)$locked['id']);
        db_run($db,"UPDATE salary_vault_fundings SET status = 'credited', provider_transaction_id = ?, credited_at = UTC_TIMESTAMP() WHERE id = ? AND company_id = ? AND status IN ('awaiting_payment','initialization_unknown')",[$providerId,$locked['id'],$locked['company_id']]);
        audit_event($db,(int)$locked['company_id'],'salary_vault.paystack_topup_credited','salary_vault_funding',(string)$locked['id'],['amount'=>accounting_decimal_from_cents((int)$locked['amount_kobo']),'reference'=>$locked['reference']],'system',0);
        $db->commit();
        return true;
    } catch (Throwable $error) { $db->rollback(); throw $error; }
}

function salary_vault_verify_paystack_funding(mysqli $db, string $reference, ?int $companyId = null, ?callable $transport = null): bool {
    if (!paystack_ready()) throw new PaymentUnavailable('Paystack verification is unavailable in this environment.');
    $params = [$reference];
    $companyFilter = '';
    if ($companyId !== null) { $companyFilter = ' AND company_id = ?'; $params[] = $companyId; }
    $funding = db_one($db,"SELECT * FROM salary_vault_fundings WHERE reference = ? AND funding_method = 'paystack'$companyFilter",$params);
    if (!$funding) return false;
    if ($funding['status'] === 'credited') return true;
    if ($funding['provider_mode'] !== config('PAYSTACK_MODE','test')) throw new PaymentUnavailable('This top-up belongs to a different Paystack environment.');
    $verified = paystack_api('GET','/transaction/verify/' . rawurlencode($reference),[],$transport);
    return salary_vault_credit_verified_paystack($db,$funding,$verified);
}

function salary_vault_refund_payroll_locked(mysqli $db, int $companyId, int $itemId, string $reference, string $event): void {
    if (!in_array($event,['failed','reversed'],true)) return;
    $reservation = db_one($db,"SELECT amount_kobo FROM salary_vault_entries WHERE company_id = ? AND entry_reference = ? AND entry_type = 'payroll_reservation'",[$companyId,'payroll:' . $reference]);
    if (!$reservation) return;
    $amount = -(int)$reservation['amount_kobo'];
    if ($amount <= 0) throw new RuntimeException('Cannot refund an invalid payroll reservation.');
    salary_vault_balance($db,$companyId);
    salary_vault_append_locked($db,$companyId,'refund:' . $reference,'payroll_refund',$amount,0,'system',null,$itemId);
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
    $housing = is_string($input['monthly_housing'] ?? null) ? trim($input['monthly_housing']) : '0';
    $transport = is_string($input['monthly_transport'] ?? null) ? trim($input['monthly_transport']) : '0';
    $allowances = is_string($input['monthly_allowances'] ?? null) ? trim($input['monthly_allowances']) : '0';
    $annualRent = is_string($input['annual_rent_paid'] ?? null) ? trim($input['annual_rent_paid']) : '0';
    $rentReference = is_string($input['rent_document_reference'] ?? null) ? trim($input['rent_document_reference']) : '';
    if (($salaryCents = payroll_amount_cents($salary)) === null || $salaryCents <= 0
        || ($housingCents = payroll_amount_cents($housing)) === null
        || ($transportCents = payroll_amount_cents($transport)) === null
        || ($allowanceCents = payroll_amount_cents($allowances)) === null
        || ($annualRentCents = payroll_amount_cents($annualRent)) === null
        || min($housingCents,$transportCents,$allowanceCents,$annualRentCents) < 0
        || mb_strlen($rentReference) > 255) {
        fail('Enter valid non-negative compensation and rent amounts.',422);
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
    $pensionApplicable = !empty($input['pension_applicable']) ? 1 : 0;
    $nhfApplicable = !empty($input['nhf_applicable']) ? 1 : 0;
    $rentReliefDocumented = !empty($input['rent_relief_documented']) ? 1 : 0;
    if ($rentReliefDocumented && ($annualRentCents <= 0 || $rentReference === '')) fail('Documented rent relief requires a positive annual rent amount and a record reference.',422);
    db_run($db,'INSERT INTO payroll_profiles (company_id,staff_role,staff_id,monthly_salary,monthly_housing,monthly_transport,monthly_allowances,annual_rent_paid,pension_applicable,nhf_applicable,rent_relief_documented,rent_document_reference,bank_name,bank_code,account_name,account_number_ciphertext,updated_by) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?) ON DUPLICATE KEY UPDATE monthly_salary = VALUES(monthly_salary), monthly_housing = VALUES(monthly_housing), monthly_transport = VALUES(monthly_transport), monthly_allowances = VALUES(monthly_allowances), annual_rent_paid = VALUES(annual_rent_paid), pension_applicable = VALUES(pension_applicable), nhf_applicable = VALUES(nhf_applicable), rent_relief_documented = VALUES(rent_relief_documented), rent_document_reference = VALUES(rent_document_reference), bank_name = VALUES(bank_name), bank_code = VALUES(bank_code), account_name = VALUES(account_name), account_number_ciphertext = VALUES(account_number_ciphertext), updated_by = VALUES(updated_by)',[
        $companyId,$role,$staffId,accounting_decimal_from_cents($salaryCents),accounting_decimal_from_cents($housingCents),accounting_decimal_from_cents($transportCents),accounting_decimal_from_cents($allowanceCents),accounting_decimal_from_cents($annualRentCents),$pensionApplicable,$nhfApplicable,$rentReliefDocumented,$rentReference,$bankName,$bankCode,$accountName,$ciphertext,$userId,
    ]);
    audit_event($db,$companyId,'payroll.profile_updated',$role,(string)$staffId,['bank_account_changed'=>!$existing || decrypt_secret($existing['account_number_ciphertext']) !== $accountNumber]);
}

function payroll_create_run(mysqli $db, int $companyId, int $userId, string $actorRole, string $start, string $end, string $payDate): int {
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
        db_run($db,'INSERT INTO payroll_runs (company_id,period_start,period_end,pay_date,created_by,created_by_role) VALUES (?,?,?,?,?,?)',[$companyId,$start,$end,$payDate,$userId,$actorRole]);
        $runId = (int)$db->insert_id;
        foreach ($people as $person) {
            $profile = $byPerson[$person['staff_role'] . ':' . $person['id']];
            $basic = payroll_amount_cents($profile['monthly_salary']);
            $housing = payroll_amount_cents($profile['monthly_housing']);
            $transport = payroll_amount_cents($profile['monthly_transport']);
            $allowance = payroll_amount_cents($profile['monthly_allowances']);
            if ($basic === null || $housing === null || $transport === null || $allowance === null) throw new UnexpectedValueException('A saved payroll profile has invalid compensation amounts.');
            $net = $basic + $housing + $transport + $allowance;
            db_run($db,'INSERT INTO payroll_items (company_id,run_id,staff_role,staff_id,staff_name,staff_email,bank_name,bank_code,account_name,account_number_ciphertext,basic_pay,housing,transport,allowances,annual_rent_paid,pension_applicable,nhf_applicable,rent_relief_documented,rent_document_reference,net_pay) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)',[
                $companyId,$runId,$person['staff_role'],$person['id'],$person['staff_name'],$person['staff_email'],$profile['bank_name'],$profile['bank_code'],$profile['account_name'],$profile['account_number_ciphertext'],$profile['monthly_salary'],$profile['monthly_housing'],$profile['monthly_transport'],$profile['monthly_allowances'],$profile['annual_rent_paid'],$profile['pension_applicable'],$profile['nhf_applicable'],$profile['rent_relief_documented'],$profile['rent_document_reference'],accounting_decimal_from_cents($net),
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
    $fields = ['basic_pay','housing','transport','allowances','bonus','overtime_pay','annual_rent_paid','paye','pension','nhf','voluntary_pension','salary_advance_deduction','other_deductions'];
    $values = [];
    foreach ($fields as $field) {
        $raw = is_string($input[$field] ?? null) ? trim($input[$field]) : '';
        $cents = payroll_amount_cents($raw);
        if ($cents === null || $cents < 0) fail('Enter valid non-negative amounts for every payroll line.',422);
        $values[$field] = accounting_decimal_from_cents($cents);
    }
    $policyFlags = [
        'pension_applicable'=>!empty($input['pension_applicable']) ? 1 : 0,
        'nhf_applicable'=>!empty($input['nhf_applicable']) ? 1 : 0,
        'rent_relief_documented'=>!empty($input['rent_relief_documented']) ? 1 : 0,
    ];
    if ($policyFlags['rent_relief_documented'] && ($values['annual_rent_paid'] === '0.00' || trim((string)($input['rent_document_reference'] ?? '')) === '')) {
        fail('Documented rent relief requires a positive annual rent amount and a record reference on the compensation profile.',422);
    }
    try { $net = payroll_net_cents($values); }
    catch (InvalidArgumentException $error) { fail($error->getMessage(),422); }
    $db->begin_transaction();
    try {
        $run = db_one($db,"SELECT id FROM payroll_runs WHERE id = ? AND company_id = ? AND status = 'draft' FOR UPDATE",[$runId,$companyId]);
        $item = db_one($db,'SELECT id FROM payroll_items WHERE id = ? AND run_id = ? AND company_id = ? FOR UPDATE',[$itemId,$runId,$companyId]);
        if (!$run || !$item) { $db->rollback(); fail('Only a draft payroll item in your workspace can be changed.',409); }
        $assignments = implode(', ',array_map(static fn(string $field): string => "`$field` = ?",[...$fields,...array_keys($policyFlags),'net_pay']));
        db_run($db,"UPDATE payroll_items SET $assignments WHERE id = ? AND run_id = ? AND company_id = ?",[...array_values($values),...array_values($policyFlags),accounting_decimal_from_cents($net),$itemId,$runId,$companyId]);
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

function payroll_validate_run_items(array $items): void {
    if (!$items) fail('Only a non-empty draft run in your workspace can be submitted.',409);
    foreach ($items as $item) {
        try { $net = payroll_net_cents($item); }
        catch (InvalidArgumentException $error) { fail('Payroll item ' . $item['staff_name'] . ' is invalid: ' . $error->getMessage(),422); }
        if ($net <= 0) fail('Each payroll item must have a positive net pay amount.',422);
        if (payroll_amount_cents($item['net_pay']) !== $net) fail('Payroll totals changed. Review the draft before submission.',409);
    }
}

function payroll_submit_run(mysqli $db, int $companyId, int $runId, int $userId, string $actorRole): void {
    $db->begin_transaction();
    try {
        $run = db_one($db,"SELECT id FROM payroll_runs WHERE id = ? AND company_id = ? AND status = 'draft' FOR UPDATE",[$runId,$companyId]);
        $items = db_rows($db,'SELECT * FROM payroll_items WHERE run_id = ? AND company_id = ? ORDER BY id FOR UPDATE',[$runId,$companyId]);
        if (!$run) { $db->rollback(); fail('Only a draft run in your workspace can be submitted.',409); }
        payroll_validate_run_items($items);
        db_run($db,"UPDATE payroll_runs SET status = 'pending_approval' WHERE id = ? AND company_id = ?",[$runId,$companyId]);
        audit_event($db,$companyId,'payroll.run_submitted','payroll_run',(string)$runId,['staff_count'=>count($items)],$actorRole,$userId);
        $db->commit();
    } catch (Throwable $error) { $db->rollback(); throw $error; }
}

function payroll_approve_run(mysqli $db, int $companyId, int $runId, int $userId, string $actorRole): void {
    if (!in_array($actorRole,['admin','employee'],true)) fail('Payroll approval is limited to the workspace owner or an employee with payroll edit access.',403);
    $db->begin_transaction();
    try {
        $run = db_one($db,"SELECT id,created_by,created_by_role FROM payroll_runs WHERE id = ? AND company_id = ? AND status = 'pending_approval' FOR UPDATE",[$runId,$companyId]);
        $items = db_rows($db,'SELECT * FROM payroll_items WHERE run_id = ? AND company_id = ? ORDER BY id FOR UPDATE',[$runId,$companyId]);
        if (!$run) { $db->rollback(); fail('Only a submitted payroll run in your workspace can be approved.',409); }
        if ((int)$run['created_by'] === $userId && $run['created_by_role'] === $actorRole) {
            $db->rollback();
            fail('The person who prepared this payroll run cannot approve it.',403);
        }
        payroll_validate_run_items($items);
        db_run($db,"UPDATE payroll_runs SET status = 'approved', approved_by = ?, approved_by_role = ?, approved_at = UTC_TIMESTAMP() WHERE id = ? AND company_id = ?",[$userId,$actorRole,$runId,$companyId]);
        audit_event($db,$companyId,'payroll.run_approved','payroll_run',(string)$runId,['staff_count'=>count($items)],$actorRole,$userId);
        $db->commit();
    } catch (Throwable $error) { $db->rollback(); throw $error; }
}

function payroll_mark_bank_paid(mysqli $db, int $companyId, int $runId, int $itemId, string $reference, int $actorId, string $actorRole): void {
    $reference = trim($reference);
    if (!preg_match('/^[A-Za-z0-9][A-Za-z0-9._\/-]{0,99}$/D',$reference)) fail('Enter a bank transfer reference using letters, numbers, dots, slashes, or hyphens.',422);
    $db->begin_transaction();
    try {
        $run = db_one($db,"SELECT id FROM payroll_runs WHERE id = ? AND company_id = ? AND status IN ('approved','processing','paid') AND approved_by_role IS NOT NULL FOR UPDATE",[$runId,$companyId]);
        $item = db_one($db,"SELECT id,payout_status,net_pay FROM payroll_items WHERE id = ? AND run_id = ? AND company_id = ? FOR UPDATE",[$itemId,$runId,$companyId]);
        if (!$run || !$item || !in_array($item['payout_status'],['not_started','failed'],true)) {
            $db->rollback();
            fail('Only a payroll item without a pending or ambiguous provider transfer can be marked paid manually.',409);
        }
        $net = payroll_amount_cents($item['net_pay']);
        if ($net === null || $net <= 0) {
            $db->rollback();
            fail('A positive net pay amount is required before payment.',422);
        }
        salary_vault_balance($db,$companyId);
        salary_vault_append_locked($db,$companyId,'manual-payroll:' . $runId . ':' . $itemId,'manual_payroll_payment',-$net,$actorId,$actorRole,null,$itemId);
        db_run($db,"UPDATE payroll_items SET payout_status = 'paid', bank_payment_reference = ?, paid_at = UTC_TIMESTAMP() WHERE id = ? AND run_id = ? AND company_id = ?",[$reference,$itemId,$runId,$companyId]);
        payroll_refresh_run_status($db,$companyId,$itemId);
        audit_event($db,$companyId,'payroll.bank_payment_recorded','payroll_item',(string)$itemId,['reference'=>$reference],$actorRole,$actorId);
        $db->commit();
    } catch (Throwable $error) { $db->rollback(); throw $error; }
}

function payroll_paystack_enabled(): bool {
    return config('PAYROLL_PAYSTACK_TRANSFERS_ENABLED','false') === 'true' && paystack_ready();
}

function payroll_transfer(mysqli $db, int $companyId, int $runId, int $itemId, int $actorId, string $actorRole, ?callable $transport = null): string {
    if (!payroll_paystack_enabled()) throw new PaymentUnavailable('Paystack payroll transfers are disabled or not configured. Enable PAYROLL_PAYSTACK_TRANSFERS_ENABLED only after verifying the workspace payout account.');
    if (config('APP_ENV') === 'local' && config('PAYSTACK_MODE','test') !== 'test') throw new PaymentUnavailable('Live Paystack transfers are blocked in local environments.');
    $db->begin_transaction();
    try {
        $item = db_one($db,"SELECT i.* FROM payroll_items i JOIN payroll_runs r ON r.id = i.run_id AND r.company_id = i.company_id WHERE i.id = ? AND i.run_id = ? AND i.company_id = ? AND r.status IN ('approved','processing','paid') AND r.approved_by_role IS NOT NULL FOR UPDATE",[$itemId,$runId,$companyId]);
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
        salary_vault_balance($db,$companyId);
        salary_vault_append_locked($db,$companyId,'payroll:' . $reference,'payroll_reservation',-$net,$actorId,$actorRole,null,$itemId);
        db_run($db,"UPDATE payroll_items SET payout_status = 'processing', payout_reference = ? WHERE id = ? AND run_id = ? AND company_id = ?",[$reference,$itemId,$runId,$companyId]);
        db_run($db,"UPDATE payroll_runs SET status = 'processing' WHERE id = ? AND company_id = ? AND status = 'approved'",[$runId,$companyId]);
        audit_event($db,$companyId,'payroll.transfer_requested','payroll_item',(string)$itemId,['reference'=>$reference,'amount'=>accounting_decimal_from_cents($net),'currency'=>'NGN'],$actorRole,$actorId);
        $db->commit();
    } catch (Throwable $error) { $db->rollback(); throw $error; }
    try {
        $result = paystack_api('POST','/transfer',[
            'source'=>'balance','amount'=>(string)$net,'recipient'=>$recipient,
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
        if (in_array($status,['failed','reversed'],true)) {
            $db->begin_transaction();
            try {
                $current = db_one($db,'SELECT payout_status FROM payroll_items WHERE id = ? AND run_id = ? AND company_id = ? AND payout_reference = ? FOR UPDATE',[$itemId,$runId,$companyId,$reference]);
                if (!$current) { $db->rollback(); throw new PaymentUnavailable('The transfer status could not be saved for reconciliation.'); }
                db_run($db,'UPDATE payroll_items SET payout_status = ?, paid_at = NULL WHERE id = ? AND run_id = ? AND company_id = ? AND payout_reference = ?',[$status,$itemId,$runId,$companyId,$reference]);
                salary_vault_refund_payroll_locked($db,$companyId,$itemId,$reference,$status);
                payroll_refresh_run_status($db,$companyId,$itemId);
                audit_event($db,$companyId,'payroll.transfer_initiated','payroll_item',(string)$itemId,['reference'=>$reference,'provider_status'=>$status]);
                $db->commit();
                return $status;
            } catch (Throwable $error) { $db->rollback(); throw $error; }
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
        $item = db_one($db,"SELECT payout_reference, net_pay, paystack_recipient_code, provider_transfer_code_ciphertext FROM payroll_items WHERE id = ? AND run_id = ? AND company_id = ? AND payout_status = 'otp_required' FOR UPDATE",[$itemId,$runId,$companyId]);
        if (!$item || !$item['provider_transfer_code_ciphertext']) { $db->rollback(); fail('This payroll transfer is not awaiting authorization.',409); }
        $transferCode = decrypt_secret($item['provider_transfer_code_ciphertext']);
        db_run($db,"UPDATE payroll_items SET payout_status = 'authorizing' WHERE id = ? AND run_id = ? AND company_id = ? AND payout_status = 'otp_required'",[$itemId,$runId,$companyId]);
        $db->commit();
    } catch (Throwable $error) { $db->rollback(); throw $error; }
    try {
        $result = paystack_api('POST','/transfer/finalize_transfer',['transfer_code'=>$transferCode,'otp'=>$otp]);
        $status = (string)($result['status'] ?? '');
        $net = payroll_amount_cents($item['net_pay']);
        $recipientData = is_array($result['recipient'] ?? null) ? $result['recipient'] : [];
        $recipient = $result['recipient_code'] ?? ($recipientData['recipient_code'] ?? null);
        $verified = ($result['reference'] ?? '') === $item['payout_reference']
            && $net !== null
            && payroll_provider_amount_matches($result['amount'] ?? null,$result['currency'] ?? null,$net)
            && (!is_string($recipient) || $recipient === '' || hash_equals((string)$item['paystack_recipient_code'],$recipient));
        if (!$verified) {
            db_run($db,"UPDATE payroll_items SET payout_status = 'unknown', provider_transfer_code_ciphertext = NULL WHERE id = ? AND run_id = ? AND company_id = ? AND payout_status = 'authorizing'",[$itemId,$runId,$companyId]);
            throw new PaymentUnavailable('Paystack returned a transfer that did not match the approved payroll amount, currency, reference, or recipient. Reconcile before taking further action.');
        }
        if ($status === 'success') {
            db_run($db,"UPDATE payroll_items SET payout_status = 'paid', paid_at = UTC_TIMESTAMP(), provider_transfer_code_ciphertext = NULL WHERE id = ? AND run_id = ? AND company_id = ? AND payout_status = 'authorizing'",[$itemId,$runId,$companyId]);
            payroll_refresh_run_status($db,$companyId,$itemId);
            audit_event($db,$companyId,'payroll.transfer_authorized','payroll_item',(string)$itemId,['reference'=>$item['payout_reference']]);
            return;
        }
        if (in_array($status,['failed','reversed'],true)) {
            $db->begin_transaction();
            try {
                $current = db_one($db,"SELECT payout_status FROM payroll_items WHERE id = ? AND run_id = ? AND company_id = ? AND payout_status = 'authorizing' FOR UPDATE",[$itemId,$runId,$companyId]);
                if (!$current) { $db->rollback(); throw new PaymentUnavailable('The transfer status changed during authorization. Reconcile it before proceeding.'); }
                db_run($db,"UPDATE payroll_items SET payout_status = ?, provider_transfer_code_ciphertext = NULL WHERE id = ? AND run_id = ? AND company_id = ?",[$status,$itemId,$runId,$companyId]);
                salary_vault_refund_payroll_locked($db,$companyId,$itemId,(string)$item['payout_reference'],$status);
                payroll_refresh_run_status($db,$companyId,$itemId);
                audit_event($db,$companyId,'payroll.transfer_authorized','payroll_item',(string)$itemId,['reference'=>$item['payout_reference'],'provider_status'=>$status]);
                $db->commit();
                return;
            } catch (Throwable $error) { $db->rollback(); throw $error; }
        }
        db_run($db,"UPDATE payroll_items SET payout_status = 'pending', provider_transfer_code_ciphertext = NULL WHERE id = ? AND run_id = ? AND company_id = ? AND payout_status = 'authorizing'",[$itemId,$runId,$companyId]);
    } catch (Throwable $error) {
        db_run($db,"UPDATE payroll_items SET payout_status = 'unknown', provider_transfer_code_ciphertext = NULL WHERE id = ? AND run_id = ? AND company_id = ? AND payout_status = 'authorizing'",[$itemId,$runId,$companyId]);
        throw new PaymentUnavailable('Paystack could not confirm the authorization. This payout is locked against retries; reconcile its reference before taking further action.');
    }
}

function payroll_reconcile_transfer(mysqli $db, int $companyId, int $runId, int $itemId): string {
    if (!payroll_paystack_enabled()) throw new PaymentUnavailable('Paystack transfers are disabled or not configured.');
    $item = db_one($db,"SELECT payout_reference, payout_status, net_pay, paystack_recipient_code FROM payroll_items WHERE id = ? AND run_id = ? AND company_id = ? AND payout_status IN ('unknown','pending','processing')",[$itemId,$runId,$companyId]);
    if (!$item || !$item['payout_reference']) fail('This payroll item has no transfer reference to reconcile.',409);
    $result = paystack_api('GET','/transfer/verify/' . rawurlencode((string)$item['payout_reference']));
    $net = payroll_amount_cents($item['net_pay']);
    $recipientData = is_array($result['recipient'] ?? null) ? $result['recipient'] : [];
    $recipient = $result['recipient_code'] ?? ($recipientData['recipient_code'] ?? null);
    if (($result['reference'] ?? '') !== $item['payout_reference'] || $net === null
        || !payroll_provider_amount_matches($result['amount'] ?? null,$result['currency'] ?? null,$net)
        || (is_string($recipient) && $recipient !== '' && !hash_equals((string)$item['paystack_recipient_code'],$recipient))) {
        throw new PaymentUnavailable('Paystack returned a transfer that does not match this payroll item. It remains locked for manual review.');
    }
    $status = (string)($result['status'] ?? '');
    if (!in_array($status,['success','failed','reversed','pending'],true)) {
        throw new PaymentUnavailable('Paystack has not returned a final or reconcilable transfer status.');
    }
    $next = $status === 'success' ? 'paid' : $status;
    $paidAt = $next === 'paid' ? gmdate('Y-m-d H:i:s') : null;
    $db->begin_transaction();
    try {
        $locked = db_one($db,"SELECT payout_status FROM payroll_items WHERE id = ? AND run_id = ? AND company_id = ? AND payout_reference = ? FOR UPDATE",[$itemId,$runId,$companyId,$item['payout_reference']]);
        if (!$locked || !in_array($locked['payout_status'],['unknown','pending','processing'],true)) {
            $db->rollback();
            fail('This transfer status changed during reconciliation. Reload the payroll run.',409);
        }
        db_run($db,'UPDATE payroll_items SET payout_status = ?, paid_at = ?, provider_transfer_code_ciphertext = NULL WHERE id = ? AND run_id = ? AND company_id = ?',[$next,$paidAt,$itemId,$runId,$companyId]);
        salary_vault_refund_payroll_locked($db,$companyId,$itemId,(string)$item['payout_reference'],$status);
        payroll_refresh_run_status($db,$companyId,$itemId);
        audit_event($db,$companyId,'payroll.transfer_reconciled','payroll_item',(string)$itemId,['reference'=>$item['payout_reference'],'provider_status'=>$status]);
        $db->commit();
        return $next;
    } catch (Throwable $error) { $db->rollback(); throw $error; }
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
        $currentStatus = (string)$item['payout_status'];
        $acceptEvent = match ($currentStatus) {
            'reversed' => false,
            'paid' => $status === 'reversed',
            'failed' => in_array($status,['failed','reversed'],true),
            default => true,
        };
        if ($acceptEvent) {
            db_run($db,'UPDATE payroll_items SET payout_status = ?, paid_at = ? WHERE id = ? AND payout_reference = ?',[$status,$paidAt,$item['id'],$reference]);
            salary_vault_refund_payroll_locked($db,(int)$item['company_id'],(int)$item['id'],$reference,$status);
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
