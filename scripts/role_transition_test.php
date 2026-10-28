<?php
declare(strict_types=1);
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
require_once __DIR__ . '/../app/database.php';
if (config('APP_ENV') !== 'local' || config('MAIL_TRANSPORT') !== 'log' || !in_array(config('DB_HOST'),['localhost','127.0.0.1'],true)) {
    fwrite(STDERR,"Local isolated database only.\n");
    exit(2);
}
if (($argv[1] ?? '') === '--invalid-replacement-probe') {
    require_once __DIR__ . '/../app/modules.php';
    $_SERVER['HTTP_ACCEPT'] = 'application/json';
    $_SERVER['REQUEST_URI'] = '/api/role-transitions';
    $managerId = filter_var($argv[2] ?? null,FILTER_VALIDATE_INT,['options'=>['min_range'=>1]]);
    if (!$managerId) exit(2);
    demote_manager_to_employee(database(),987654321,$managerId,$managerId);
    fwrite(STDERR,"Expected invalid replacement to be rejected.\n");
    exit(1);
}

$root = dirname(__DIR__);
$name = 'simplr_dev_roles_' . bin2hex(random_bytes(4));
$server = new mysqli(config('DB_HOST'),config('DB_USERNAME'),config('DB_PASSWORD'),'',(int)config('DB_PORT','3306'));
$db = null;
$checks = [];
function expect(bool $condition, string $label): void {
    global $checks;
    $checks[] = ['name'=>$label,'passed'=>$condition];
    if (!$condition) throw new RuntimeException($label);
}
try {
    if ($server->query("SHOW DATABASES LIKE '$name'")->num_rows) throw new RuntimeException('Name collision; nothing was changed.');
    $server->query("CREATE DATABASE `$name` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    $env = getenv();
    $env['DB_DATABASE'] = $name;
    $process = proc_open([PHP_BINARY,'scripts/migrate.php','--install'],[1=>['pipe','w'],2=>['pipe','w']],$pipes,$root,$env);
    $output = stream_get_contents($pipes[1]) . stream_get_contents($pipes[2]);
    $exitCode = proc_close($process);
    if ($exitCode !== 0 || !str_contains($output,'Database is up to date.')) throw new RuntimeException('Disposable schema setup failed.');

    $db = new mysqli(config('DB_HOST'),config('DB_USERNAME'),config('DB_PASSWORD'),$name,(int)config('DB_PORT','3306'));
    $db->set_charset('utf8mb4');
    require_once __DIR__ . '/../app/modules.php';
    $_SESSION = ['auth_role'=>'admin','user_id'=>1];

    $companyId = 987654321;
    $departmentId = (int)db_run($db,'INSERT INTO departments (department_name,company_id) VALUES (?,?)',['Synthetic operations',$companyId])->insert_id;
    $employeeRoleId = (int)db_run($db,'INSERT INTO roles (company_id,role_name) VALUES (?,?)',[$companyId,'Employee'])->insert_id;
    $employeeId = (int)db_run($db,'INSERT INTO employees (company_id,first_name,last_name,email,hire_date,department_id,manager_id,role_id,unique_id) VALUES (?,?,?,?,?,?,?,?,?)',[$companyId,'Synthetic','Promote','promote@example.test','2026-01-01',$departmentId,0,$employeeRoleId,'emp_promote'])->insert_id;

    $managerId = promote_employee_to_manager($db,$companyId,$employeeId,$departmentId,true);
    $manager = db_one($db,'SELECT employee_id,status,department_id,unique_id FROM managers WHERE id = ? AND company_id = ?',[$managerId,$companyId]);
    expect((int)$manager['employee_id'] === $employeeId && $manager['status'] === 'pending','Promotion links the existing employee and creates a pending manager record');
    expect((int)$manager['department_id'] === $departmentId && (int)db_one($db,'SELECT manager_id FROM departments WHERE id = ?',[$departmentId])['manager_id'] === $managerId,'Department-head promotion assigns the manager to the department');
    expect((int)db_one($db,'SELECT department_id FROM employees WHERE id = ?',[$employeeId])['department_id'] === $departmentId,'Promotion updates the employee department');
    expect((bool)db_one($db,"SELECT id FROM workspace_audit WHERE company_id = ? AND action = 'employee.promoted_department_head' AND target_id = ?",[$companyId,(string)$employeeId]),'Promotion records a department-head audit event');

    $replacementEmployeeId = (int)db_run($db,'INSERT INTO employees (company_id,first_name,last_name,email,hire_date,department_id,manager_id,role_id,unique_id) VALUES (?,?,?,?,?,?,?,?,?)',[$companyId,'Synthetic','Replacement','replacement@example.test','2026-01-01',$departmentId,0,$employeeRoleId,'emp_replacement'])->insert_id;
    $replacementId = (int)db_run($db,"INSERT INTO managers (company_id,unique_id,manager_name,manager_email,department_id,employee_id,role_id,status) VALUES (?,?,?,?,?,?,?,'active')",[$companyId,'man_replacement','Synthetic Replacement','replacement@example.test',$departmentId,$replacementEmployeeId,$employeeRoleId])->insert_id;
    db_run($db,"UPDATE managers SET status = 'active' WHERE id = ?",[$managerId]);
    $reportId = (int)db_run($db,'INSERT INTO employees (company_id,first_name,last_name,email,hire_date,department_id,manager_id,role_id,unique_id) VALUES (?,?,?,?,?,?,?,?,?)',[$companyId,'Synthetic','Report','report@example.test','2026-01-01',$departmentId,$managerId,$employeeRoleId,'emp_report'])->insert_id;
    $projectId = (int)db_run($db,'INSERT INTO projects (projectName,projectManager,company_id,project_manager_id,unique_id) VALUES (?,?,?,?,?)',['Synthetic project',$managerId,$companyId,$manager['unique_id'],'proj_synthetic'])->insert_id;
    db_run($db,'INSERT INTO staff_invitations (company_id,staff_role,staff_id,email,token_hash,expires_at) VALUES (?,?,?,?,?,UTC_TIMESTAMP() + INTERVAL 1 DAY)',[$companyId,'manager',$managerId,'promote@example.test',hash('sha256','synthetic-pending-invitation')]);

    $env = getenv();
    $env['DB_DATABASE'] = $name;
    $process = proc_open([PHP_BINARY,'scripts/role_transition_test.php','--invalid-replacement-probe',(string)$managerId],[1=>['pipe','w'],2=>['pipe','w']],$pipes,$root,$env);
    $probeOutput = stream_get_contents($pipes[1]) . stream_get_contents($pipes[2]);
    $probeCode = proc_close($process);
    $probeResult = json_decode($probeOutput,true);
    expect($probeCode === 0 && ($probeResult['error'] ?? '') === 'Choose a replacement manager before demoting this manager with direct reports, projects, or a department-head role.','Demotion rejects a self-replacement');
    $beforeDemotion = db_one($db,'SELECT status FROM managers WHERE id = ?',[$managerId]);
    $beforeProject = db_one($db,'SELECT projectManager,project_manager_id FROM projects WHERE id = ?',[$projectId]);
    expect($beforeDemotion['status'] === 'active' && (int)db_one($db,'SELECT manager_id FROM employees WHERE id = ?',[$reportId])['manager_id'] === $managerId,'Rejected replacement leaves manager status and reporting lines unchanged');
    expect((int)db_one($db,'SELECT manager_id FROM departments WHERE id = ?',[$departmentId])['manager_id'] === $managerId,'Rejected replacement preserves department leadership');
    expect((int)$beforeProject['projectManager'] === $managerId && $beforeProject['project_manager_id'] === $manager['unique_id'],'Rejected replacement preserves project assignments');
    expect(!db_one($db,'SELECT id FROM staff_invitations WHERE company_id = ? AND staff_role = \'manager\' AND staff_id = ? AND used_at IS NOT NULL',[$companyId,$managerId]),'Rejected replacement does not invalidate the manager invitation');

    demote_manager_to_employee($db,$companyId,$managerId,$replacementId);
    $demoted = db_one($db,'SELECT status,employee_id FROM managers WHERE id = ? AND company_id = ?',[$managerId,$companyId]);
    expect($demoted['status'] === 'suspended' && (int)$demoted['employee_id'] === $employeeId,'Demotion suspends manager access and retains its linked employee');
    expect((int)db_one($db,'SELECT role_id FROM employees WHERE id = ?',[$employeeId])['role_id'] === $employeeRoleId,'Demotion restores the employee role');
    expect((int)db_one($db,'SELECT manager_id FROM employees WHERE id = ?',[$reportId])['manager_id'] === $replacementId,'Demotion reassigns direct reports to the replacement manager');
    expect((int)db_one($db,'SELECT manager_id FROM departments WHERE id = ?',[$departmentId])['manager_id'] === $replacementId,'Demotion transfers department leadership');
    $project = db_one($db,'SELECT projectManager,project_manager_id FROM projects WHERE id = ?',[$projectId]);
    expect((int)$project['projectManager'] === $replacementId && $project['project_manager_id'] === 'man_replacement','Demotion transfers both project manager references');
    expect((bool)db_one($db,"SELECT id FROM staff_invitations WHERE company_id = ? AND staff_role = 'manager' AND staff_id = ? AND used_at IS NOT NULL",[$companyId,$managerId]),'Demotion invalidates unused manager invitations');
    expect((bool)db_one($db,"SELECT id FROM workspace_audit WHERE company_id = ? AND action = 'manager.demoted_to_employee' AND target_id = ?",[$companyId,(string)$managerId]),'Demotion records a role-change audit event');

    $tree = organization_tree($db,$companyId);
    $department = array_values(array_filter($tree['departments'],fn($entry) => (int)$entry['id'] === $departmentId))[0] ?? null;
    expect($department !== null && (int)$department['manager_id'] === $replacementId,'Organization chart reflects replacement department leadership');
    expect((int)db_one($db,'SELECT company_id FROM employees WHERE id = ?',[$reportId])['company_id'] === $companyId,'Synthetic reporting relationship remains workspace-scoped');

    $restoredManagerId = promote_employee_to_manager($db,$companyId,$employeeId,$departmentId,true);
    $restored = db_one($db,'SELECT employee_id,status,unique_id FROM managers WHERE id = ? AND company_id = ?',[$restoredManagerId,$companyId]);
    expect($restoredManagerId === $managerId && (int)$restored['employee_id'] === $employeeId && $restored['status'] === 'pending','Restoration reuses the suspended manager record and linked employee');
    expect($restored['unique_id'] === $manager['unique_id'],'Restoration preserves the manager identifier rather than creating a duplicate');
    $restoredTree = organization_tree($db,$companyId);
    $restoredDepartment = array_values(array_filter($restoredTree['departments'],fn($entry) => (int)$entry['id'] === $departmentId))[0] ?? null;
    expect($restoredDepartment !== null && (int)$restoredDepartment['manager_id'] === $managerId,'Organization chart reflects restored department leadership');
    echo json_encode(['checks'=>count($checks),'passed'=>count(array_filter($checks,fn($check)=>$check['passed'])),'failures'=>[]],JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . PHP_EOL;
} catch (Throwable $error) {
    echo json_encode(['checks'=>count($checks),'passed'=>count(array_filter($checks,fn($check)=>$check['passed'])),'failures'=>[$error->getMessage()]],JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . PHP_EOL;
    $exit = 1;
} finally {
    if ($db instanceof mysqli) $db->close();
    if (str_starts_with($name,'simplr_dev_roles_')) $server->query("DROP DATABASE IF EXISTS `$name`");
    $server->close();
}
exit($exit ?? 0);
