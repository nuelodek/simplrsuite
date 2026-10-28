<?php
declare(strict_types=1);
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
require_once __DIR__ . '/../app/database.php';
if (config('APP_ENV') !== 'local' || config('MAIL_TRANSPORT') !== 'log' || !in_array(config('DB_HOST'),['localhost','127.0.0.1'],true)) {
    fwrite(STDERR,"Local isolated database only.\n");
    exit(2);
}

$root = dirname(__DIR__);
$name = 'simplr_dev_recovery_' . bin2hex(random_bytes(4));
$target = '027_linked_manager_promotions.sql';
$files = glob($root . '/database/migrations/*.sql');
sort($files);
$targetIndex = array_search($target,array_map('basename',$files),true);
if ($targetIndex === false) {
    fwrite(STDERR,"Target migration was not found.\n");
    exit(2);
}
$server = new mysqli(config('DB_HOST'),config('DB_USERNAME'),config('DB_PASSWORD'),'',(int)config('DB_PORT','3306'));
$db = null;
$checks = [];
$exit = 0;
function expect(bool $condition, string $label): void {
    global $checks;
    $checks[] = ['name'=>$label,'passed'=>$condition];
    if (!$condition) throw new RuntimeException($label);
}
function run_sql(mysqli $db, string $sql): void {
    $db->multi_query($sql);
    do { if ($result = $db->store_result()) $result->free(); } while ($db->more_results() && $db->next_result());
}
function migrate(string $database): array {
    global $root;
    $env = getenv();
    $env['DB_DATABASE'] = $database;
    $process = proc_open([PHP_BINARY,'scripts/migrate.php'],[1=>['pipe','w'],2=>['pipe','w']],$pipes,$root,$env);
    $output = stream_get_contents($pipes[1]) . stream_get_contents($pipes[2]);
    return [proc_close($process),$output];
}
function has_column(mysqli $db, string $table, string $column): bool {
    return (bool)db_one($db,'SELECT COLUMN_NAME FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = ?',[$table,$column]);
}
function has_index(mysqli $db, string $table, string $index): bool {
    return (bool)db_one($db,'SELECT INDEX_NAME FROM information_schema.STATISTICS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND INDEX_NAME = ? LIMIT 1',[$table,$index]);
}

try {
    if ($server->query("SHOW DATABASES LIKE '$name'")->num_rows) throw new RuntimeException('Name collision; nothing was changed.');
    $server->query("CREATE DATABASE `$name` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    $db = new mysqli(config('DB_HOST'),config('DB_USERNAME'),config('DB_PASSWORD'),$name,(int)config('DB_PORT','3306'));
    $db->set_charset('utf8mb4');
    run_sql($db,file_get_contents($root . '/database/schema.sql'));
    $db->query('CREATE TABLE schema_migrations (name VARCHAR(190) PRIMARY KEY, applied_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP) ENGINE=InnoDB');
    for ($index = 0; $index < $targetIndex; $index++) {
        run_sql($db,file_get_contents($files[$index]));
        db_run($db,'INSERT INTO schema_migrations (name) VALUES (?)',[basename($files[$index])]);
    }

    $companyId = 987654322;
    $departmentId = (int)db_run($db,'INSERT INTO departments (department_name,company_id) VALUES (?,?)',['Recovery probe',$companyId])->insert_id;
    $employeeRoleId = (int)db_run($db,'INSERT INTO roles (company_id,role_name) VALUES (?,?)',[$companyId,'Employee'])->insert_id;
    $managerRoleId = (int)db_run($db,'INSERT INTO roles (company_id,role_name) VALUES (?,?)',[$companyId,'Manager'])->insert_id;
    $employeeId = (int)db_run($db,'INSERT INTO employees (company_id,first_name,last_name,email,hire_date,department_id,manager_id,role_id,unique_id) VALUES (?,?,?,?,?,?,?,?,?)',[$companyId,'Recovery','Probe','recovery-probe@example.test','2026-01-01',$departmentId,0,$employeeRoleId,'emp_recovery_probe'])->insert_id;
    $managerId = (int)db_run($db,'INSERT INTO managers (company_id,unique_id,manager_name,manager_email,department_id,role_id,status) VALUES (?,?,?,?,?,?,?)',[$companyId,'man_recovery_probe','Recovery Probe','recovery-probe@example.test',$departmentId,$managerRoleId,'active'])->insert_id;

    // Simulate a pre-existing compatible column from a manual partial recovery;
    // migration 027's first ALTER and data backfill will run before this conflicts.
    $db->query('ALTER TABLE teams ADD COLUMN department_id INT NULL DEFAULT NULL AFTER team_name');
    [$failedCode] = migrate($name);
    expect($failedCode !== 0,'Runner reports the injected duplicate-column failure');
    expect(has_column($db,'managers','employee_id'),'Earlier ALTER from migration 027 remains applied after the failure');
    expect((int)db_one($db,'SELECT employee_id FROM managers WHERE id = ?',[$managerId])['employee_id'] === $employeeId,'Earlier manager-to-employee backfill remains applied');
    expect(has_column($db,'teams','department_id') && !has_index($db,'teams','teams_company_department'),'Pre-existing department column is present while the migration index is missing');
    expect(!db_one($db,'SELECT name FROM schema_migrations WHERE name = ?',[$target]),'Failed migration is not marked applied');

    $db->query('ALTER TABLE teams ADD KEY teams_company_department (company_id,department_id)');
    db_run($db,'INSERT INTO schema_migrations (name) VALUES (?)',[$target]);
    [$recoveryCode,$recoveryOutput] = migrate($name);
    expect($recoveryCode === 0 && str_contains($recoveryOutput,'Database is up to date.'),'After the verified repair and marker, the runner completes later migrations');
    expect((int)db_one($db,'SELECT COUNT(*) AS count FROM schema_migrations')['count'] === count($files),'Recovery leaves every migration recorded');
    expect(has_index($db,'teams','teams_company_department'),'Recovered schema contains the missing department index');
    expect((int)db_one($db,'SELECT employee_id FROM managers WHERE id = ?',[$managerId])['employee_id'] === $employeeId,'Recovery preserves the completed manager backfill');
    echo json_encode(['checks'=>count($checks),'passed'=>count(array_filter($checks,fn($check)=>$check['passed'])),'failures'=>[]],JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . PHP_EOL;
} catch (Throwable $error) {
    echo json_encode(['checks'=>count($checks),'passed'=>count(array_filter($checks,fn($check)=>$check['passed'])),'failures'=>[$error->getMessage()]],JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . PHP_EOL;
    $exit = 1;
} finally {
    if ($db instanceof mysqli) $db->close();
    if (str_starts_with($name,'simplr_dev_recovery_')) $server->query("DROP DATABASE IF EXISTS `$name`");
    $server->close();
}
exit($exit);
