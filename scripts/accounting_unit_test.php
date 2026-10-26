<?php
declare(strict_types=1);
require_once __DIR__ . '/../app/accounting.php';

$checks = 0;
$check = static function (bool $condition, string $description) use (&$checks): void {
    if (!$condition) { fwrite(STDERR, 'Failed: ' . $description . PHP_EOL); exit(1); }
    $checks++;
};

$check(accounting_parse_amount('1250') === 125000, 'whole currency amounts convert to cents');
$check(accounting_parse_amount('1250.4') === 125040, 'fractional currency amounts are padded to cents');
$check(accounting_parse_amount('0.00') === 0, 'zero amount is accepted');
$check(accounting_parse_amount('-1.00') === null, 'negative amounts are rejected');
$check(accounting_parse_amount('1.234') === null, 'amounts with sub-cent precision are rejected');
$check(accounting_parse_amount('1e3') === null, 'exponent notation is rejected');
$check(accounting_database_cents('1000000000000.01') === 100000000000001, 'database decimals convert without floating-point arithmetic');
$check(accounting_decimal_from_cents(120005) === '1200.05', 'journal amounts preserve cents when stored as decimals');
$check(accounting_currency_amount(-125005, 'NGN') === 'NGN -1,250.05', 'negative report amounts format without floating-point conversion');
$check(accounting_date_valid('2024-02-29'), 'valid leap dates are accepted');
$check(!accounting_date_valid('2025-02-29'), 'invalid calendar dates are rejected');

$totals = accounting_report_totals([
    ['category'=>'current_asset','balance_cents'=>120000],
    ['category'=>'equity','balance_cents'=>100000],
    ['category'=>'revenue','balance_cents'=>30000],
    ['category'=>'expense','balance_cents'=>10000],
], 20000);
$check($totals['assets'] === 120000, 'asset totals include current and non-current asset balances');
$check($totals['balance_sheet_equity'] === 120000, 'unclosed earnings are included in balance sheet equity');
$check($totals['assets'] === $totals['liabilities_and_equity'], 'balanced example balances');
$check($totals['net_income'] === 20000, 'period net income equals revenue less expenses');
$check($totals['net_profit_margin'] === 2 / 3, 'net profit margin uses period revenue');
$check($totals['current_ratio'] === null, 'current ratio is unavailable without current liabilities');
$check(accounting_currency_amount(120000, 'USD') === 'USD 1,200.00', 'statement amounts use configured currency');

$ratioTotals = accounting_report_totals([
    ['category'=>'current_asset','balance_cents'=>100000],
    ['category'=>'current_liability','balance_cents'=>50000],
    ['category'=>'equity','balance_cents'=>100000],
    ['category'=>'revenue','balance_cents'=>30000],
    ['category'=>'expense','balance_cents'=>10000],
], 20000);
$check(abs((float)$ratioTotals['current_ratio'] - 2.0) < 1e-12, 'current ratio uses current assets and current liabilities');
$check($ratioTotals['debt_to_equity'] === 0.4166666666666667, 'debt-to-equity includes unclosed earnings in equity');

$contraTotals = accounting_report_totals([
    ['category'=>'revenue','balance_cents'=>-5000],
    ['category'=>'expense','balance_cents'=>-3000],
], -2000);
$check($contraTotals['net_income'] === -2000, 'contra-revenue and contra-expense balances keep their statement signs');

echo "Accounting unit checks passed: $checks\n";
