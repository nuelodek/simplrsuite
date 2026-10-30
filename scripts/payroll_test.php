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
payroll_expect(payroll_provider_amount_matches('12000000','NGN',120000),'Provider transfer amounts are verified in kobo.');
payroll_expect(!payroll_provider_amount_matches('12000000','USD',120000),'Payroll transfer currency must be NGN.');
payroll_expect(isset(assignable_modules()['payroll']),'Payroll can be assigned through the existing staff-access controls.');
$routes = require __DIR__ . '/../app/routes.php';
payroll_expect(($routes['/users/admin/payroll.php'] ?? '') === 'users/admin/payroll.php','Owner payroll page is routable.');
payroll_expect(($routes['/users/employees/payroll.php'] ?? '') === 'users/employees/payroll.php','Staff payslip page is routable.');

$invalid = $item;
$invalid['other_deductions'] = '5000.00';
try {
    payroll_net_cents($invalid);
    payroll_expect(false,'Deductions cannot exceed gross pay.');
} catch (InvalidArgumentException) {
}

echo "Payroll calculation checks passed.\n";
