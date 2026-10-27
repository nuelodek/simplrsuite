<?php
declare(strict_types=1);
require_once __DIR__ . '/../../app/auth.php';
require_once __DIR__ . '/../../app/accounting.php';
require_once __DIR__ . '/../../app/settings.php';
require_once __DIR__ . '/../../app/view.php';

$db = database();
$user = admin_user($db);
$companyId = (int)$user['company_id'];
$userId = (int)$user['id'];

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    require_post();
    $action = is_string($_POST['action'] ?? null) ? $_POST['action'] : '';
    if ($action === 'create_chart') {
        $db->begin_transaction();
        try {
            foreach (accounting_default_accounts() as [$code,$name,$category,$normalBalance]) {
                db_run($db, 'INSERT INTO accounting_accounts (company_id, account_code, account_name, category, normal_balance) VALUES (?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE account_code = VALUES(account_code)', [
                    $companyId,$code,$name,$category,$normalBalance,
                ]);
            }
            $db->commit();
        } catch (Throwable $error) {
            $db->rollback();
            throw $error;
        }
        flash('The standard chart of accounts is ready.', 'success');
        redirect('/users/admin/accounting.php#accounts');
    }
    if ($action === 'add_account') {
        $code = is_string($_POST['account_code'] ?? null) ? trim($_POST['account_code']) : '';
        $name = is_string($_POST['account_name'] ?? null) ? trim($_POST['account_name']) : '';
        $category = is_string($_POST['category'] ?? null) ? $_POST['category'] : '';
        $normalBalance = is_string($_POST['normal_balance'] ?? null) ? $_POST['normal_balance'] : '';
        if (!preg_match('/^[A-Za-z0-9][A-Za-z0-9.-]{0,19}$/D', $code)) fail('Use a code of 1–20 letters, digits, dots, or hyphens.', 422);
        if ($name === '' || mb_strlen($name) > 120) fail('Enter an account name of no more than 120 characters.', 422);
        if (!array_key_exists($category, accounting_categories()) || !in_array($normalBalance, ['debit','credit'], true)) {
            fail('Choose a valid account category and normal balance.', 422);
        }
        try {
            db_run($db, 'INSERT INTO accounting_accounts (company_id, account_code, account_name, category, normal_balance) VALUES (?, ?, ?, ?, ?)', [
                $companyId,$code,$name,$category,$normalBalance,
            ]);
        } catch (mysqli_sql_exception $error) {
            if ($error->getCode() !== 1062) throw $error;
            fail('An account already uses that code in this workspace.', 422);
        }
        flash('Account added to the chart.', 'success');
        redirect('/users/admin/accounting.php#accounts');
    }
    if ($action === 'post_entry') {
        $entryId = accounting_post_journal_entry($db, $companyId, $userId, $_POST);
        flash('Journal entry #' . $entryId . ' posted.', 'success');
        redirect('/users/admin/accounting.php#journal');
    }
    fail('Choose a valid accounting action.', 422);
}

$startDate = is_string($_GET['from'] ?? null) ? trim($_GET['from']) : date('Y-01-01');
$endDate = is_string($_GET['to'] ?? null) ? trim($_GET['to']) : date('Y-m-d');
if (!accounting_date_valid($startDate) || !accounting_date_valid($endDate) || $startDate > $endDate) {
    fail('Choose a valid reporting period with a start date no later than the end date.', 422);
}

$settings = workspace_settings($db, $companyId);
$accounts = db_rows($db, 'SELECT id, account_code, account_name, category, normal_balance FROM accounting_accounts WHERE company_id = ? AND is_active = 1 ORDER BY account_code, id', [$companyId]);
$chartCount = count($accounts);
$balanceRows = db_rows($db, "SELECT a.id, a.account_code, a.account_name, a.category, a.normal_balance, SUM(l.debit) AS debits, SUM(l.credit) AS credits FROM accounting_accounts a JOIN accounting_journal_lines l ON l.account_id = a.id JOIN accounting_journal_entries j ON j.id = l.journal_entry_id AND j.company_id = a.company_id WHERE a.company_id = ? AND a.is_active = 1 AND a.category NOT IN ('revenue','expense') AND j.entry_date <= ? GROUP BY a.id, a.account_code, a.account_name, a.category, a.normal_balance ORDER BY a.account_code, a.id", [$companyId,$endDate]);
$incomeRows = db_rows($db, "SELECT a.id, a.account_code, a.account_name, a.category, a.normal_balance, SUM(l.debit) AS debits, SUM(l.credit) AS credits FROM accounting_accounts a JOIN accounting_journal_lines l ON l.account_id = a.id JOIN accounting_journal_entries j ON j.id = l.journal_entry_id AND j.company_id = a.company_id WHERE a.company_id = ? AND a.is_active = 1 AND a.category IN ('revenue','expense') AND j.entry_date BETWEEN ? AND ? GROUP BY a.id, a.account_code, a.account_name, a.category, a.normal_balance ORDER BY a.category, a.account_code, a.id", [$companyId,$startDate,$endDate]);
$cumulativeIncomeRows = db_rows($db, "SELECT a.category, a.normal_balance, SUM(l.debit) AS debits, SUM(l.credit) AS credits FROM accounting_accounts a JOIN accounting_journal_lines l ON l.account_id = a.id JOIN accounting_journal_entries j ON j.id = l.journal_entry_id AND j.company_id = a.company_id WHERE a.company_id = ? AND a.is_active = 1 AND a.category IN ('revenue','expense') AND j.entry_date <= ? GROUP BY a.category, a.normal_balance", [$companyId,$endDate]);
$toCents = static fn(array $row): int => accounting_database_cents((string)$row['debits']) - accounting_database_cents((string)$row['credits']);
$balanceAccounts = [];
foreach ($balanceRows as $row) {
    $difference = $toCents($row);
    $balanceAccounts[] = [
        'category'=>$row['category'],
        'balance_cents'=>$row['normal_balance'] === 'debit' ? $difference : -$difference,
    ];
}
$incomeAccounts = [];
foreach ($incomeRows as $row) {
    $difference = $toCents($row);
    $incomeAccounts[] = [
        'id'=>(int)$row['id'],
        'account_code'=>$row['account_code'],
        'account_name'=>$row['account_name'],
        'category'=>$row['category'],
        'balance_cents'=>$row['category'] === 'revenue' ? -$difference : $difference,
    ];
}
$cumulativeNetIncome = 0;
foreach ($cumulativeIncomeRows as $row) {
    $difference = accounting_database_cents((string)$row['debits']) - accounting_database_cents((string)$row['credits']);
    $signed = $row['category'] === 'revenue' ? -$difference : $difference;
    $cumulativeNetIncome += $row['category'] === 'revenue' ? $signed : -$signed;
}
$totals = accounting_report_totals([...$balanceAccounts,...$incomeAccounts], $cumulativeNetIncome);
$recentEntries = db_rows($db, 'SELECT j.id, j.entry_date, j.description, j.reference, j.created_at, CAST(SUM(l.debit) AS CHAR) AS total_debits, COUNT(l.id) AS line_count FROM accounting_journal_entries j JOIN accounting_journal_lines l ON l.journal_entry_id = j.id WHERE j.company_id = ? GROUP BY j.id, j.entry_date, j.description, j.reference, j.created_at ORDER BY j.entry_date DESC, j.id DESC LIMIT 30', [$companyId]);
$currency = (string)($settings['currency'] ?? '');
$money = static fn(int $cents): string => e(accounting_currency_amount($cents, $currency));
$ratio = static fn(?float $value): string => $value === null ? 'N/A' : number_format($value, 2) . ' : 1';

$journalLine = function () use ($accounts, $currency): string {
    $options = '';
    foreach (accounting_categories() as $category=>$label) {
        $group = array_filter($accounts, fn($account) => $account['category'] === $category);
        if (!$group) continue;
        $options .= '<optgroup label="' . e($label) . '">';
        foreach ($group as $account) $options .= '<option value="' . (int)$account['id'] . '">' . e($account['account_code'] . ' · ' . $account['account_name']) . '</option>';
        $options .= '</optgroup>';
    }
    $amount = fn(string $side) => '<span class="amount-input"><span>' . e($currency ?: '¤') . '</span><input class="control" data-' . $side . ' name="' . $side . '[]" inputmode="decimal" autocomplete="off" placeholder="0.00" aria-label="' . ucfirst($side) . ' amount"></span>';
    return '<tr data-journal-line><td><select class="control" name="account_id[]" required aria-label="Account"><option value="">Choose account</option>' . $options . '</select></td><td>' . $amount('debit') . '</td><td>' . $amount('credit') . '</td><td><button type="button" class="icon-button line-remove" data-remove-journal-line aria-label="Remove journal line" title="Remove line">' . icon('trash') . '</button></td></tr>';
};
suite_start('Accounting','accounting',$user);
?>
<script src="/public/scripts/accounting.js" defer></script>
<div class="page-heading"><div><h1>Accounting</h1><p>Post balanced journal entries and review statements based on your chart of accounts.</p></div><a class="button secondary small" href="/users/admin/workspace.php?module=ledger">Open simple ledger</a></div>
<div class="notice info">These management statements use only the entries recorded here, in <?= e($currency ?: 'an unspecified currency') ?>. Enter opening balances as a balanced journal entry. This is not tax, payroll, audited, or professional accounting advice; reconcile records and review statements with a qualified accountant.</div>

<?php if ($chartCount === 0): ?>
<section class="panel"><div class="panel-body"><h2>Set up your chart of accounts</h2><p>Start with a standard chart, then add accounts that match your business. Records in your simple ledger stay where they are.</p><form method="post"><?= csrf_field() ?><input type="hidden" name="action" value="create_chart"><button class="button" type="submit">Create standard chart</button></form></div></section>
<?php else: ?>
<section class="panel"><div class="panel-head"><h2>Financial ratios</h2><span class="count-label">Income statement period: <?= e(date('j M Y', strtotime($startDate))) ?> – <?= e(date('j M Y', strtotime($endDate))) ?></span></div><div class="panel-body"><div class="stats-grid"><article class="stat-card"><div class="stat-label"><span>Current ratio</span></div><div class="stat-value"><?= e($ratio($totals['current_ratio'])) ?></div><div class="stat-foot">Current assets ÷ current liabilities</div></article><article class="stat-card"><div class="stat-label"><span>Debt-to-equity</span></div><div class="stat-value"><?= e($ratio($totals['debt_to_equity'])) ?></div><div class="stat-foot">Total liabilities ÷ equity including earnings</div></article><article class="stat-card"><div class="stat-label"><span>Net profit margin</span></div><div class="stat-value"><?= $totals['revenue'] > 0 ? e(number_format(($totals['net_income'] / $totals['revenue']) * 100, 2)) . '%' : 'N/A' ?></div><div class="stat-foot">Period net income ÷ period revenue</div></article></div></div></section>

<form class="panel form-actions" method="get"><div class="field"><label for="from">Income statement from</label><input id="from" type="date" name="from" value="<?= e($startDate) ?>" required></div><div class="field"><label for="to">Through</label><input id="to" type="date" name="to" value="<?= e($endDate) ?>" required></div><button class="button" type="submit">Update statements</button></form>

<div class="dashboard-columns">
<section class="panel"><div class="panel-head"><h2>Income statement</h2></div><div class="panel-body"><p class="subtle">For <?= e(date('j M Y', strtotime($startDate))) ?> – <?= e(date('j M Y', strtotime($endDate))) ?></p><h3>Revenue</h3><?php $revenueShown = false; foreach ($incomeAccounts as $row): if ($row['category'] !== 'revenue' || $row['balance_cents'] === 0) continue; $revenueShown = true; ?><p class="accounting-line"><span><?= e($row['account_code'] . ' · ' . $row['account_name']) ?></span><strong><?= $money($row['balance_cents']) ?></strong></p><?php endforeach; ?><?php if (!$revenueShown): ?><p class="subtle">No revenue recorded in this period.</p><?php endif; ?><p class="accounting-line accounting-total"><span>Total revenue</span><strong><?= $money($totals['revenue']) ?></strong></p><h3>Expenses</h3><?php $expenseShown = false; foreach ($incomeAccounts as $row): if ($row['category'] !== 'expense' || $row['balance_cents'] === 0) continue; $expenseShown = true; ?><p class="accounting-line"><span><?= e($row['account_code'] . ' · ' . $row['account_name']) ?></span><strong><?= $money($row['balance_cents']) ?></strong></p><?php endforeach; ?><?php if (!$expenseShown): ?><p class="subtle">No expenses recorded in this period.</p><?php endif; ?><p class="accounting-line accounting-total"><span>Total expenses</span><strong><?= $money($totals['expense']) ?></strong></p><p class="accounting-line accounting-grand-total"><span>Net income</span><strong><?= $money($totals['net_income']) ?></strong></p></div></section>

<section class="panel">
<div class="panel-head"><h2>Balance sheet</h2></div>
<div class="panel-body">
<p class="subtle">As of <?= e(date('j M Y', strtotime($endDate))) ?></p>
<?php foreach (['current_asset'=>'Current assets','non_current_asset'=>'Non-current assets','current_liability'=>'Current liabilities','non_current_liability'=>'Non-current liabilities','equity'=>'Equity'] as $category=>$label): ?>
<h3><?= e($label) ?></h3>
<?php $shown = false; foreach ($balanceRows as $row): if ($row['category'] !== $category) continue; $difference = $toCents($row); $balance = $row['normal_balance'] === 'debit' ? $difference : -$difference; if ($balance === 0) continue; $shown = true; ?>
<p class="accounting-line"><span><?= e($row['account_code'] . ' · ' . $row['account_name']) ?></span><strong><?= $money($balance) ?></strong></p>
<?php endforeach; ?>
<?php if (!$shown): ?><p class="subtle">No balances recorded.</p><?php endif; ?>
<p class="accounting-line accounting-total"><span><?= $category === 'equity' ? 'Posted equity balances' : 'Total ' . strtolower($label) ?></span><strong><?= $money($totals[$category]) ?></strong></p>
<?php endforeach; ?>
<p class="accounting-line"><span>Unclosed earnings through <?= e(date('j M Y', strtotime($endDate))) ?></span><strong><?= $money($cumulativeNetIncome) ?></strong></p>
<p class="accounting-line accounting-total"><span>Total equity including unclosed earnings</span><strong><?= $money($totals['balance_sheet_equity']) ?></strong></p>
<p class="accounting-line accounting-grand-total"><span>Total liabilities and equity</span><strong><?= $money($totals['liabilities_and_equity']) ?></strong></p>
<p class="accounting-line accounting-grand-total"><span>Total assets</span><strong><?= $money($totals['assets']) ?></strong></p>
<?php if ($totals['assets'] !== $totals['liabilities_and_equity']): ?><p class="notice error" role="status">The balance sheet is out of balance by <?= $money($totals['assets'] - $totals['liabilities_and_equity']) ?>. Review opening balances, account classifications, and journal entries.</p><?php endif; ?>
</div>
</section>
</div>
<?php endif; ?>

<section class="panel" id="journal"><div class="panel-head"><h2>Post a journal entry</h2><span class="count-label">Entries are permanent; corrections need a reversing entry.</span></div><div class="panel-body">
<?php if (!$accounts): ?><p class="subtle">Create a chart of accounts before posting entries.</p><?php else: ?>
<form method="post" class="form-stack" data-submit><?= csrf_field() ?><input type="hidden" name="action" value="post_entry"><div class="access-grid"><div class="field"><label for="entry-date">Date</label><input id="entry-date" name="entry_date" type="date" value="<?= e(date('Y-m-d')) ?>" required></div><div class="field"><label for="entry-description">Description</label><input id="entry-description" name="description" maxlength="255" required></div><div class="field"><label for="entry-reference">Reference (optional)</label><input id="entry-reference" name="reference" maxlength="100"></div></div><div class="table-wrap journal-wrap"><table class="journal-table"><thead><tr><th>Account</th><th class="num">Debit</th><th class="num">Credit</th><th><span class="sr-only">Remove line</span></th></tr></thead><tbody data-journal-lines><?= $journalLine() . $journalLine() ?></tbody><tfoot><tr><td><button type="button" class="text-button" data-add-journal-line><?= icon('plus') ?> Add line</button></td><td class="num" data-total-debit>0.00</td><td class="num" data-total-credit>0.00</td><td></td></tr></tfoot></table></div><div class="journal-actions"><p class="journal-balance" data-journal-balance aria-live="polite">Enter amounts to check the balance.</p><button class="button" type="submit">Post balanced entry</button></div><p class="subtle">Use a debit or credit on each line. Total debits must equal total credits. Opening balances should be entered as a balanced journal entry against equity.</p></form>
<template data-journal-line-template><?= $journalLine() ?></template>
<?php endif; ?></div></section>

<section class="panel" id="accounts"><div class="panel-head"><h2>Chart of accounts</h2><?php if ($chartCount > 0): ?><span class="count-label"><?= $chartCount ?> active accounts</span><?php endif; ?></div><div class="panel-body">
<?php if ($chartCount === 0): ?><p class="subtle">The account list is empty.</p><?php else: ?><div class="table-wrap"><table class="data-table"><thead><tr><th>Code</th><th>Account</th><th>Category</th><th>Normal balance</th></tr></thead><tbody><?php foreach ($accounts as $account): ?><tr><td><?= e($account['account_code']) ?></td><td><?= e($account['account_name']) ?></td><td><?= e(accounting_categories()[$account['category']]) ?></td><td><?= e(ucfirst($account['normal_balance'])) ?></td></tr><?php endforeach; ?></tbody></table></div><?php endif; ?>
<h3>Add an account</h3><form method="post" class="form-stack" data-submit><?= csrf_field() ?><input type="hidden" name="action" value="add_account"><div class="access-grid"><div class="field"><label for="account-code">Code</label><input id="account-code" name="account_code" maxlength="20" required></div><div class="field"><label for="account-name">Name</label><input id="account-name" name="account_name" maxlength="120" required></div><div class="field"><label for="account-category">Category</label><select id="account-category" name="category" required><?php foreach (accounting_categories() as $category=>$label): ?><option value="<?= e($category) ?>"><?= e($label) ?></option><?php endforeach; ?></select></div><div class="field"><label for="normal-balance">Normal balance</label><select id="normal-balance" name="normal_balance" required><option value="debit">Debit</option><option value="credit">Credit</option></select></div></div><button class="button" type="submit">Add account</button></form></div></section>

<section class="panel"><div class="panel-head"><h2>Journal history</h2><span class="count-label">Latest 30 posted entries</span></div><?php if (!$recentEntries): ?><div class="empty-state"><h3>No entries yet</h3><p>Post a balanced entry to begin building your statements.</p></div><?php else: ?><div class="table-wrap"><table class="data-table"><thead><tr><th>Date</th><th>Description</th><th>Reference</th><th>Lines</th><th>Total debits</th></tr></thead><tbody><?php foreach ($recentEntries as $entry): ?><tr><td><?= e(date('j M Y', strtotime($entry['entry_date']))) ?></td><td><?= e($entry['description']) ?></td><td><?= e($entry['reference'] ?: '—') ?></td><td><?= (int)$entry['line_count'] ?></td><td><?= $money(accounting_database_cents((string)$entry['total_debits'])) ?></td></tr><?php endforeach; ?></tbody></table></div><?php endif; ?></section>
<?php suite_end(); ?>
