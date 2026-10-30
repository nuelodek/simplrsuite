<?php
declare(strict_types=1);
require_once __DIR__ . '/../app/auth.php';
require_once __DIR__ . '/../app/access.php';
require_once __DIR__ . '/../app/payroll.php';

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
    header('Allow: POST');
    json_response(['ok'=>false,'error'=>'Use POST to calculate a payroll item.'],405);
}
if (strtolower(trim(explode(';',(string)($_SERVER['CONTENT_TYPE'] ?? ''))[0])) !== 'application/json') {
    json_response(['ok'=>false,'error'=>'Content-Type must be application/json.'],415);
}
$raw = file_get_contents('php://input',false,null,0,65537);
if (!is_string($raw) || strlen($raw) > 65536) json_response(['ok'=>false,'error'=>'The request body is too large.'],413);
$body = json_decode($raw,true,32,JSON_BIGINT_AS_STRING);
if (!is_array($body) || json_last_error() !== JSON_ERROR_NONE) json_response(['ok'=>false,'error'=>'Submit a valid JSON object.'],400);
$_POST['csrf_token'] = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
require_post();

$db = database();
$user = workspace_user($db);
require_module_access($db,$user,'payroll',true);
$role = (string)($_SESSION['auth_role'] ?? '');
$companyId = (int)$user['company_id'];
$actorId = (int)$user['id'];

$employeeKey = $body['employee_id'] ?? null;
$period = $body['pay_period'] ?? null;
$earnings = $body['earnings'] ?? null;
$reliefs = $body['documented_reliefs'] ?? [];
$overrides = $body['configuration_overrides'] ?? [];
if (!is_string($employeeKey) || trim($employeeKey) === '' || strlen($employeeKey) > 255
    || !is_string($period) || !preg_match('/^2026-(?:0[1-9]|1[0-2])$/D',$period)
    || !is_array($earnings) || !is_array($reliefs) || !is_array($overrides)) {
    json_response(['ok'=>false,'error'=>'Provide employee_id, a 2026 pay_period, earnings, documented_reliefs, and configuration_overrides objects.'],422);
}

$money = static function (array $source, string $key, bool $required = false): int {
    if (!array_key_exists($key,$source)) {
        if ($required) throw new InvalidArgumentException('Missing amount: ' . $key . '.');
        return 0;
    }
    $rawAmount = $source[$key];
    if (!is_string($rawAmount) && !is_int($rawAmount)) throw new InvalidArgumentException('Use decimal strings or whole integer Naira amounts for ' . $key . '.');
    $amount = payroll_amount_cents($rawAmount);
    if ($amount === null) throw new InvalidArgumentException('Enter a non-negative amount with at most two decimal places for ' . $key . '.');
    return $amount;
};

try {
    $amounts = [
        'basic_pay'=>$money($earnings,'basic_salary',true),
        'housing'=>$money($earnings,'housing_allowance',true),
        'transport'=>$money($earnings,'transport_allowance',true),
        'allowances'=>$money($earnings,'other_allowances',true),
        'overtime_pay'=>$money($earnings,'overtime_pay',true),
        'bonus'=>$money($earnings,'bonuses',true),
        'annual_rent_paid'=>$money($reliefs,'annual_rent_paid'),
        'voluntary_pension'=>$money($overrides,'voluntary_pension_contribution'),
        'salary_advance_deduction'=>$money($overrides,'salary_advance_deduction'),
    ];
} catch (InvalidArgumentException $error) {
    json_response(['ok'=>false,'error'=>$error->getMessage()],422);
}

$employee = db_one($db,'SELECT id, unique_id FROM employees WHERE company_id = ? AND unique_id = ? AND archived_at IS NULL',[$companyId,trim($employeeKey)]);
if (!$employee) json_response(['ok'=>false,'error'=>'The employee was not found in your workspace.'],404);
$periodStart = $period . '-01';
$db->begin_transaction();
try {
$run = db_one($db,"SELECT id FROM payroll_runs WHERE company_id = ? AND period_start = ? AND status = 'draft' FOR UPDATE",[$companyId,$periodStart]);
if (!$run) {
    $db->rollback();
    json_response(['ok'=>false,'error'=>'Create or select the workspace draft payroll run for this period before calculating an item.'],409);
}

    $item = db_one($db,"SELECT * FROM payroll_items WHERE company_id = ? AND run_id = ? AND staff_role = 'employee' AND staff_id = ? FOR UPDATE",[$companyId,$run['id'],$employee['id']]);
    if (!$item) {
        $db->rollback();
        json_response(['ok'=>false,'error'=>'This employee does not have an item in the selected draft payroll run.'],404);
    }
    $item = array_merge($item,$amounts);
    $estimate = payroll_nigerian_2026_estimate($item);
    $item['paye'] = accounting_decimal_from_cents($estimate['paye']);
    $item['pension'] = accounting_decimal_from_cents($estimate['pension']);
    $item['nhf'] = accounting_decimal_from_cents($estimate['nhf']);
    $existingOther = payroll_amount_cents($item['other_deductions']);
    if ($existingOther === null) throw new UnexpectedValueException('The saved voluntary deduction amount is invalid.');
    $net = payroll_net_cents($item);
    $gross = $amounts['basic_pay'] + $amounts['housing'] + $amounts['transport'] + $amounts['allowances'] + $amounts['overtime_pay'] + $amounts['bonus'];
    $statutory = $estimate['paye'] + $estimate['pension'] + $estimate['nhf'];
    $voluntary = $amounts['voluntary_pension'] + $amounts['salary_advance_deduction'] + $existingOther;
    $deductions = $statutory + $voluntary;
    $pensionable = $amounts['basic_pay'] + $amounts['housing'] + $amounts['transport'];
    $employerPension = intdiv($pensionable * 10 + 50,100);
    $employerNsitf = intdiv($gross + 50,100);
    $activeStaff = (int)(db_one($db,"SELECT COUNT(*) AS count FROM employees WHERE company_id = ? AND archived_at IS NULL",[$companyId])['count'] ?? 0);
    $employerItf = $activeStaff >= 5 ? intdiv($gross + 50,100) : 0;
    $ratio = $gross > 0 ? number_format($deductions / $gross,3,'.','') : '0.000';

    $version = 'nigeria-2026-user-estimate-v1';
    db_run($db,"UPDATE payroll_items SET
        previous_paye = IF((SELECT statutory_settings_version FROM payroll_runs WHERE id = ? AND company_id = ?) = '',paye,previous_paye),
        previous_pension = IF((SELECT statutory_settings_version FROM payroll_runs WHERE id = ? AND company_id = ?) = '',pension,previous_pension),
        previous_nhf = IF((SELECT statutory_settings_version FROM payroll_runs WHERE id = ? AND company_id = ?) = '',nhf,previous_nhf),
        basic_pay = ?, housing = ?, transport = ?, allowances = ?, overtime_pay = ?, bonus = ?, annual_rent_paid = ?,
        paye = ?, pension = ?, nhf = ?, voluntary_pension = ?, salary_advance_deduction = ?,
        employer_pension = ?, employer_nsitf = ?, employer_itf = ?, net_pay = ?
        WHERE id = ? AND company_id = ? AND run_id = ?",[
            $run['id'],$companyId,$run['id'],$companyId,$run['id'],$companyId,
            accounting_decimal_from_cents($amounts['basic_pay']),accounting_decimal_from_cents($amounts['housing']),
            accounting_decimal_from_cents($amounts['transport']),accounting_decimal_from_cents($amounts['allowances']),
            accounting_decimal_from_cents($amounts['overtime_pay']),accounting_decimal_from_cents($amounts['bonus']),
            accounting_decimal_from_cents($amounts['annual_rent_paid']),accounting_decimal_from_cents($estimate['paye']),
            accounting_decimal_from_cents($estimate['pension']),accounting_decimal_from_cents($estimate['nhf']),
            accounting_decimal_from_cents($amounts['voluntary_pension']),accounting_decimal_from_cents($amounts['salary_advance_deduction']),
            accounting_decimal_from_cents($employerPension),accounting_decimal_from_cents($employerNsitf),
            accounting_decimal_from_cents($employerItf),accounting_decimal_from_cents($net),
            $item['id'],$companyId,$run['id'],
        ]);
    db_run($db,"UPDATE payroll_runs SET statutory_settings_version = ?, statutory_applied_by = ?, statutory_applied_at = UTC_TIMESTAMP() WHERE id = ? AND company_id = ? AND statutory_settings_version = ''",[$version,$actorId,$run['id'],$companyId]);
    audit_event($db,$companyId,'payroll.api_calculation_saved','payroll_item',(string)$item['id'],['period'=>$period,'settings_version'=>$version],$role,$actorId);
    $db->commit();
    json_response([
        'ok'=>true,
        'data'=>[
            'employee_id'=>$employee['unique_id'],
            'pay_period'=>$period,
            'currency'=>'NGN',
            'summary'=>[
                'gross_earnings'=>accounting_decimal_from_cents($gross),
                'total_statutory_deductions'=>accounting_decimal_from_cents($statutory),
                'total_voluntary_deductions'=>accounting_decimal_from_cents($voluntary),
                'net_take_home_pay'=>accounting_decimal_from_cents($net),
            ],
            'breakdown_earnings'=>[
                'basic_salary'=>accounting_decimal_from_cents($amounts['basic_pay']),
                'housing_allowance'=>accounting_decimal_from_cents($amounts['housing']),
                'transport_allowance'=>accounting_decimal_from_cents($amounts['transport']),
                'other_allowances'=>accounting_decimal_from_cents($amounts['allowances']),
                'overtime_pay'=>accounting_decimal_from_cents($amounts['overtime_pay']),
                'bonuses'=>accounting_decimal_from_cents($amounts['bonus']),
            ],
            'breakdown_employee_deductions'=>[
                'statutory_pension_8pc'=>accounting_decimal_from_cents($estimate['pension']),
                'statutory_nhf_2_5pc'=>accounting_decimal_from_cents($estimate['nhf']),
                'statutory_paye_tax'=>accounting_decimal_from_cents($estimate['paye']),
                'voluntary_pension_contribution'=>accounting_decimal_from_cents($amounts['voluntary_pension']),
                'salary_advance_recovery'=>accounting_decimal_from_cents($amounts['salary_advance_deduction']),
                'other_voluntary_deductions'=>accounting_decimal_from_cents($existingOther),
            ],
            'breakdown_employer_contributions'=>[
                'employer_pension_10pc'=>accounting_decimal_from_cents($employerPension),
                'employer_nsitf_1pc_gross'=>accounting_decimal_from_cents($employerNsitf),
                'employer_itf_pool_allocation'=>accounting_decimal_from_cents($employerItf),
                'itf_eligible_staff_count'=>$activeStaff,
            ],
            'compliance_flags'=>[
                'deduction_cap_exceeded'=>false,
                'actual_deduction_ratio'=>$ratio,
                'max_allowable_ratio'=>'0.333',
                'minimum_wage_paye_exemption'=>$estimate['minimum_wage_exempt'],
                'rent_relief_excluded_undocumented'=>$amounts['annual_rent_paid'] > 0 && empty($item['rent_relief_documented']),
                'review_required'=>true,
                'remitted'=>false,
            ],
            'settings_version'=>$version,
        ],
    ],200);
} catch (InvalidArgumentException $error) {
    $db->rollback();
    json_response(['ok'=>false,'error'=>$error->getMessage(),'compliance_flags'=>['deduction_cap_exceeded'=>str_contains($error->getMessage(),'one-third'),'review_required'=>true],'persisted'=>false],422);
} catch (Throwable $error) {
    $db->rollback();
    throw $error;
}
