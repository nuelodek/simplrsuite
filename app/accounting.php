<?php
declare(strict_types=1);
require_once __DIR__ . '/database.php';

function accounting_categories(): array {
    return [
        'current_asset' => 'Current asset',
        'non_current_asset' => 'Non-current asset',
        'current_liability' => 'Current liability',
        'non_current_liability' => 'Non-current liability',
        'equity' => 'Equity',
        'revenue' => 'Revenue',
        'expense' => 'Expense',
    ];
}

function accounting_default_accounts(): array {
    return [
        ['1000','Cash and bank','current_asset','debit'],
        ['1100','Accounts receivable','current_asset','debit'],
        ['1200','Inventory','current_asset','debit'],
        ['1500','Equipment','non_current_asset','debit'],
        ['2000','Accounts payable','current_liability','credit'],
        ['2100','Short-term loans','current_liability','credit'],
        ['2500','Long-term loans','non_current_liability','credit'],
        ['3000',"Owner's equity",'equity','credit'],
        ['3100','Retained earnings','equity','credit'],
        ['4000','Sales revenue','revenue','credit'],
        ['5000','Cost of sales','expense','debit'],
        ['6000','Operating expenses','expense','debit'],
    ];
}

function accounting_parse_amount(mixed $value): ?int {
    if (!is_string($value) && !is_int($value) && !is_float($value)) return null;
    $amount = trim((string)$value);
    if (!preg_match('/^(?:0|[1-9][0-9]{0,11})(?:\.[0-9]{1,2})?$/D', $amount)) return null;
    [$whole,$fraction] = array_pad(explode('.', $amount, 2), 2, '');
    return ((int)$whole * 100) + (int)str_pad($fraction, 2, '0');
}

function accounting_database_cents(string $value): int {
    if (!preg_match('/^(?:0|[1-9][0-9]{0,12})(?:\.[0-9]{1,2})?$/D', $value)) {
        throw new UnexpectedValueException('The database returned an invalid accounting amount.');
    }
    [$whole,$fraction] = array_pad(explode('.', $value, 2), 2, '');
    return ((int)$whole * 100) + (int)str_pad($fraction, 2, '0');
}

function accounting_decimal_from_cents(int $cents): string {
    $absolute = abs($cents);
    return ($cents < 0 ? '-' : '') . intdiv($absolute, 100) . '.' . str_pad((string)($absolute % 100), 2, '0', STR_PAD_LEFT);
}

function accounting_date_valid(string $value): bool {
    $date = DateTimeImmutable::createFromFormat('!Y-m-d', $value);
    return $date !== false && $date->format('Y-m-d') === $value;
}

function accounting_currency_amount(int $cents, string $currency): string {
    $absolute = abs($cents);
    $amount = ($cents < 0 ? '-' : '') . number_format(intdiv($absolute, 100)) . '.' . str_pad((string)($absolute % 100), 2, '0', STR_PAD_LEFT);
    return $currency !== '' ? $currency . ' ' . $amount : $amount;
}

function accounting_report_totals(array $accountBalances, int $cumulativeNetIncomeCents): array {
    $totals = [
        'current_asset'=>0,
        'non_current_asset'=>0,
        'current_liability'=>0,
        'non_current_liability'=>0,
        'equity'=>0,
        'revenue'=>0,
        'expense'=>0,
    ];
    foreach ($accountBalances as $account) {
        $category = (string)($account['category'] ?? '');
        if (array_key_exists($category, $totals)) $totals[$category] += (int)$account['balance_cents'];
    }
    $totals['assets'] = $totals['current_asset'] + $totals['non_current_asset'];
    $totals['liabilities'] = $totals['current_liability'] + $totals['non_current_liability'];
    $totals['net_income'] = $totals['revenue'] - $totals['expense'];
    $totals['balance_sheet_equity'] = $totals['equity'] + $cumulativeNetIncomeCents;
    $totals['liabilities_and_equity'] = $totals['liabilities'] + $totals['balance_sheet_equity'];
    $totals['current_ratio'] = $totals['current_liability'] > 0
        ? $totals['current_asset'] / $totals['current_liability']
        : null;
    $totals['debt_to_equity'] = $totals['balance_sheet_equity'] > 0
        ? $totals['liabilities'] / $totals['balance_sheet_equity']
        : null;
    $totals['net_profit_margin'] = $totals['revenue'] > 0
        ? $totals['net_income'] / $totals['revenue']
        : null;
    return $totals;
}

function accounting_post_journal_entry(mysqli $db, int $companyId, int $userId, array $input): int {
    $date = is_string($input['entry_date'] ?? null) ? trim($input['entry_date']) : '';
    $description = is_string($input['description'] ?? null) ? trim($input['description']) : '';
    $reference = is_string($input['reference'] ?? null) ? trim($input['reference']) : '';
    $accountIds = is_array($input['account_id'] ?? null) ? $input['account_id'] : [];
    $debits = is_array($input['debit'] ?? null) ? $input['debit'] : [];
    $credits = is_array($input['credit'] ?? null) ? $input['credit'] : [];
    if (!accounting_date_valid($date)) fail('Enter a valid journal date.', 422);
    if ($description === '' || mb_strlen($description) > 255) fail('Enter a description of no more than 255 characters.', 422);
    if (mb_strlen($reference) > 100) fail('The reference must be no more than 100 characters.', 422);
    if (count($accountIds) < 2 || count($accountIds) > 20 || count($debits) !== count($accountIds) || count($credits) !== count($accountIds)) {
        fail('Add between 2 and 20 complete journal lines.', 422);
    }

    $lines = [];
    $seenAccounts = [];
    $debitTotal = 0;
    $creditTotal = 0;
    // Each form line leaves its other column blank; a blank amount means zero.
    $lineAmount = static fn(mixed $value): ?int => is_string($value) ? (trim($value) === '' ? 0 : accounting_parse_amount(str_replace(',', '', $value))) : accounting_parse_amount($value);
    foreach ($accountIds as $index=>$rawAccountId) {
        $accountId = filter_var($rawAccountId, FILTER_VALIDATE_INT, ['options'=>['min_range'=>1]]) ?: 0;
        $debit = $lineAmount($debits[$index] ?? null);
        $credit = $lineAmount($credits[$index] ?? null);
        if (!$accountId || $debit === null || $credit === null || (($debit > 0) === ($credit > 0)) || ($debit === 0 && $credit === 0)) {
            fail('Each journal line needs a valid account and exactly one positive debit or credit.', 422);
        }
        if (isset($seenAccounts[$accountId])) fail('Use each account only once per journal entry.', 422);
        $seenAccounts[$accountId] = true;
        $debitTotal += $debit;
        $creditTotal += $credit;
        $lines[] = ['account_id'=>$accountId,'debit'=>$debit,'credit'=>$credit];
    }
    if ($debitTotal !== $creditTotal) fail('Total debits must equal total credits before this entry can be posted.', 422);
    if ($debitTotal === 0) fail('Enter a positive amount for the journal entry.', 422);

    $placeholders = implode(',', array_fill(0, count($seenAccounts), '?'));
    $accounts = db_rows($db, "SELECT id FROM accounting_accounts WHERE company_id = ? AND is_active = 1 AND id IN ($placeholders)", [$companyId, ...array_keys($seenAccounts)]);
    if (count($accounts) !== count($seenAccounts)) fail('Select active accounts from your own chart of accounts.', 422);

    $db->begin_transaction();
    try {
        db_run($db, 'INSERT INTO accounting_journal_entries (company_id, entry_date, description, reference, created_by) VALUES (?, ?, ?, ?, ?)', [$companyId,$date,$description,$reference,$userId]);
        $entryId = (int)$db->insert_id;
        foreach ($lines as $line) {
            db_run($db, 'INSERT INTO accounting_journal_lines (journal_entry_id, account_id, debit, credit) VALUES (?, ?, ?, ?)', [
                $entryId,
                $line['account_id'],
                accounting_decimal_from_cents($line['debit']),
                accounting_decimal_from_cents($line['credit']),
            ]);
        }
        $db->commit();
        return $entryId;
    } catch (Throwable $error) {
        $db->rollback();
        throw $error;
    }
}
