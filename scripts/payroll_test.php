<?php
declare(strict_types=1);
require_once __DIR__ . '/../app/payroll.php';
require_once __DIR__ . '/../app/modules.php';

function payroll_expect(bool $condition, string $message): void {
    if (!$condition) {
        fwrite(STDERR,$message . PHP_EOL);
        exit(1);
    }
}

$item = [
    'basic_pay'=>'1000.00',
    'housing'=>'0.00',
    'transport'=>'0.00',
    'allowances'=>'200.00',
    'bonus'=>'50.00',
    'paye'=>'10.00',
    'pension'=>'20.00',
    'nhf'=>'5.00',
    'other_deductions'=>'15.00',
];
payroll_expect(payroll_net_cents($item) === 120000,'Net pay uses exact integer cents.');
payroll_expect(payroll_amount_cents('0.01') === 1,'One-kobo decimal parses exactly.');
payroll_expect(payroll_amount_cents('-1.00') === null,'Negative payroll amounts are rejected.');
payroll_expect(payroll_amount_cents('1e5') === null,'Scientific-notation payroll amounts are rejected.');
payroll_expect(payroll_period_valid('2024-02-01','2024-02-29','2024-03-01'),'Leap-year monthly payroll period is valid.');
payroll_expect(!payroll_period_valid('2024-02-02','2024-02-29','2024-03-01'),'Partial payroll periods are rejected.');
payroll_expect(!payroll_period_valid('2024-02-01','2024-02-29','2024-02-28'),'Pay date cannot precede the period end.');
payroll_expect(payroll_provider_amount_matches('120000','NGN',120000),'NGN transfer amount uses exact kobo units.');
payroll_expect(!payroll_provider_amount_matches('12000000','NGN',120000),'A hundredfold transfer amount is rejected.');
payroll_expect(!payroll_provider_amount_matches('120000','USD',120000),'Payroll transfer currency must be NGN.');
payroll_expect(isset(assignable_modules()['payroll']),'Payroll can be assigned through the existing staff-access controls.');
$routes = require __DIR__ . '/../app/routes.php';
payroll_expect(($routes['/api/v1/payroll/calculate'] ?? '') === 'api/payroll-calculate.php','Payroll calculation API is routable.');
$estimateItem = [
    'basic_pay'=>'1000000.00',
    'housing'=>'0.00',
    'transport'=>'0.00',
    'allowances'=>'0.00',
    'bonus'=>'0.00',
    'overtime_pay'=>'0.00',
    'annual_rent_paid'=>'0.00',
    'pension_applicable'=>0,
    'nhf_applicable'=>0,
    'rent_relief_documented'=>0,
];
$estimate = payroll_nigerian_2026_estimate($estimateItem);
payroll_expect($estimate['paye'] === 16250000,'2026 estimate applies marginal bands through NGN 12 million annual gross.');
$sampleEstimate = payroll_nigerian_2026_estimate([
    'basic_pay'=>'250000.00','housing'=>'120000.00','transport'=>'80000.00','allowances'=>'50000.00',
    'overtime_pay'=>'35000.00','bonus'=>'0.00','annual_rent_paid'=>'1500000.00',
    'pension_applicable'=>1,'nhf_applicable'=>1,'rent_relief_documented'=>1,
]);
payroll_expect($sampleEstimate['pension'] === 3600000 && $sampleEstimate['nhf'] === 625000 && $sampleEstimate['paye'] === 6092000,'Example payload uses exact pension, NHF and annualized PAYE values; the published NGN 31,000 PAYE does not match the provided bands.');
$estimateItem['basic_pay'] = '66666.66';
$estimate = payroll_nigerian_2026_estimate($estimateItem);
payroll_expect($estimate['paye'] === 0 && $estimate['minimum_wage_exempt'],'2026 estimate exempts monthly gross at or below NGN 70,000 from PAYE.');
$estimateItem['basic_pay'] = '100000.00';
$estimateItem['bonus'] = '120000.00';
$estimate = payroll_nigerian_2026_estimate($estimateItem);
payroll_expect($estimate['paye'] === 650000,'A one-off monthly bonus is included once in annual gross rather than annualized as recurring pay.');
$estimateItem['basic_pay'] = '4166666.67';
$estimateItem['bonus'] = '0.00';
$estimate = payroll_nigerian_2026_estimate($estimateItem);
payroll_expect($estimate['paye'] === 86916667,'2026 estimate applies all marginal bands through NGN 50 million annual gross.');
$estimateItem = [
    'basic_pay'=>'100000.00',
    'housing'=>'50000.00',
    'transport'=>'25000.00',
    'allowances'=>'0.00',
    'bonus'=>'0.00',
    'overtime_pay'=>'0.00',
    'annual_rent_paid'=>'0.00',
    'pension_applicable'=>1,
    'nhf_applicable'=>1,
    'rent_relief_documented'=>0,
];
$estimate = payroll_nigerian_2026_estimate($estimateItem);
payroll_expect($estimate['pension'] === 1400000 && $estimate['nhf'] === 250000,'2026 estimates use the configured pensionable pay components and basic-pay NHF base.');
$estimateItem['annual_rent_paid'] = '5000000.00';
$estimateItem['rent_relief_documented'] = 1;
$withDocumentedRent = payroll_nigerian_2026_estimate($estimateItem);
$estimateItem['rent_relief_documented'] = 0;
$withoutDocumentedRent = payroll_nigerian_2026_estimate($estimateItem);
payroll_expect($withDocumentedRent['annual_taxable'] === $withoutDocumentedRent['annual_taxable'] - 50000000,'Documented rent relief is capped at NGN 500,000 and excluded without confirmation.');
$estimateItem = [
    'basic_pay'=>'83333.33',
    'housing'=>'0.00',
    'transport'=>'0.00',
    'allowances'=>'0.00',
    'bonus'=>'0.00',
    'annual_rent_paid'=>'2500000.00',
    'pension_applicable'=>1,
    'nhf_applicable'=>1,
    'rent_relief_documented'=>1,
];
$estimate = payroll_nigerian_2026_estimate($estimateItem);
payroll_expect($estimate['paye'] === 0 && !$estimate['minimum_wage_exempt'],'The updated 2026 schedule removes the prior minimum-tax floor above NGN 70,000 monthly gross.');
$estimateItem['basic_pay'] = '-1.00';
try {
    payroll_nigerian_2026_estimate($estimateItem);
    payroll_expect(false,'Negative statutory estimate inputs are rejected.');
} catch (InvalidArgumentException) {
}
payroll_expect(($routes['/users/admin/payroll.php'] ?? '') === 'users/admin/payroll.php','Owner payroll page is routable.');
payroll_expect(($routes['/users/employees/payroll.php'] ?? '') === 'users/employees/payroll.php','Staff payslip page is routable.');

$invalid = $item;
$invalid['other_deductions'] = '5000.00';
try {
    payroll_net_cents($invalid);
    payroll_expect(false,'Deductions cannot exceed gross pay.');
} catch (InvalidArgumentException) {
}

$capItem = [
    'basic_pay'=>'1000.00','housing'=>'0.00','transport'=>'0.00','allowances'=>'0.00','bonus'=>'0.00',
    'paye'=>'200.00','pension'=>'100.00','nhf'=>'0.00','voluntary_pension'=>'20.00',
    'salary_advance_deduction'=>'20.00','other_deductions'=>'0.00',
];
try {
    payroll_net_cents($capItem);
    payroll_expect(false,'Employee deductions over one-third gross must be blocked for review.');
} catch (InvalidArgumentException $error) {
    payroll_expect(str_contains($error->getMessage(),'one-third'),'Deduction cap error identifies the one-third safeguard.');
}

echo "Payroll calculation checks passed.\n";
