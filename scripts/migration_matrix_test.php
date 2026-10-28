<?php
declare(strict_types=1);
// Migration matrix on disposable local databases. For a clean install and for every
// migration baseline (schema.sql plus the first k migrations, with synthetic rows),
// runs the real scripts/migrate.php and checks that it finishes, records every
// migration, keeps the rows and ends with the same schema as a clean install.
// Also checks the duplicate-email stop in 002 and lists migrations that fail when
// run twice. Creates only simplr_dev_mig_* databases and drops only those it created.
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
require_once __DIR__ . '/../app/database.php';
if (config('APP_ENV') !== 'local' || config('MAIL_TRANSPORT') !== 'log' || !in_array(config('DB_HOST'),['localhost','127.0.0.1'],true)) { fwrite(STDERR,"Local isolated database only.\n"); exit(2); }

$root = dirname(__DIR__);
$files = glob($root . '/database/migrations/*.sql'); sort($files);
$names = array_map('basename',$files);
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
$server = new mysqli(config('DB_HOST'),config('DB_USERNAME'),config('DB_PASSWORD'),'',(int)config('DB_PORT','3306'));
$created = [];
$checks = []; $notes = [];
function expect(bool $condition, string $label): void { global $checks; $checks[] = ['name'=>$label,'passed'=>$condition]; if (!$condition) fwrite(STDERR,"FAIL: $label\n"); }

function disposable(mysqli $server): array {
    global $created;
    $name = 'simplr_dev_mig_' . bin2hex(random_bytes(4));
    if ($server->query("SHOW DATABASES LIKE '$name'")->num_rows) throw new RuntimeException('Name collision; nothing was changed.');
    $server->query("CREATE DATABASE `$name` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    $created[] = $name;
    $db = new mysqli(config('DB_HOST'),config('DB_USERNAME'),config('DB_PASSWORD'),$name,(int)config('DB_PORT','3306'));
    $db->set_charset('utf8mb4');
    return [$name,$db];
}
function run_sql(mysqli $db, string $sql): void {
    $db->multi_query($sql);
    do { if ($result = $db->store_result()) $result->free(); } while ($db->more_results() && $db->next_result());
}
function migrate(string $name, array $args = []): array {
    global $root;
    $env = getenv(); $env['DB_DATABASE'] = $name;
    $process = proc_open([PHP_BINARY,'scripts/migrate.php',...$args],[1=>['pipe','w'],2=>['pipe','w']],$pipes,$root,$env);
    $out = stream_get_contents($pipes[1]) . stream_get_contents($pipes[2]);
    return [proc_close($process),$out];
}
function schema_of(mysqli $db): array {
    $rows = $db->query("SELECT TABLE_NAME, COLUMN_NAME, COLUMN_TYPE, IS_NULLABLE, COALESCE(COLUMN_DEFAULT,'∅') AS d FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() ORDER BY TABLE_NAME, COLUMN_NAME")->fetch_all(MYSQLI_ASSOC);
    $indexes = $db->query("SELECT TABLE_NAME, INDEX_NAME, GROUP_CONCAT(COLUMN_NAME ORDER BY SEQ_IN_INDEX) AS cols, NON_UNIQUE FROM information_schema.STATISTICS WHERE TABLE_SCHEMA = DATABASE() GROUP BY TABLE_NAME, INDEX_NAME, NON_UNIQUE ORDER BY TABLE_NAME, INDEX_NAME")->fetch_all(MYSQLI_ASSOC);
    $foreignKeys = $db->query("SELECT TABLE_NAME, CONSTRAINT_NAME, UPDATE_RULE, DELETE_RULE FROM information_schema.REFERENTIAL_CONSTRAINTS WHERE CONSTRAINT_SCHEMA = DATABASE() ORDER BY TABLE_NAME, CONSTRAINT_NAME")->fetch_all(MYSQLI_ASSOC);
    $foreignKeyColumns = $db->query("SELECT TABLE_NAME, CONSTRAINT_NAME, COLUMN_NAME, REFERENCED_TABLE_NAME, REFERENCED_COLUMN_NAME, ORDINAL_POSITION FROM information_schema.KEY_COLUMN_USAGE WHERE TABLE_SCHEMA = DATABASE() AND REFERENCED_TABLE_NAME IS NOT NULL ORDER BY TABLE_NAME, CONSTRAINT_NAME, ORDINAL_POSITION")->fetch_all(MYSQLI_ASSOC);
    $out = [];
    foreach ($rows as $row) $out[] = implode('|',$row);
    foreach ($indexes as $row) $out[] = 'index:' . implode('|',$row);
    foreach ($foreignKeys as $row) $out[] = 'foreign-key:' . implode('|',$row);
    foreach ($foreignKeyColumns as $row) $out[] = 'foreign-key-column:' . implode('|',$row);
    return $out;
}
// Fills every required column the baseline has, so seeding works on any version of a table.
function seed(mysqli $db, string $table, array $values): int {
    $columns = $db->query("SELECT COLUMN_NAME, DATA_TYPE, COLUMN_TYPE, EXTRA FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = '$table' AND IS_NULLABLE = 'NO' AND COLUMN_DEFAULT IS NULL")->fetch_all(MYSQLI_ASSOC);
    $present = array_column($db->query("SELECT COLUMN_NAME FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = '$table'")->fetch_all(MYSQLI_ASSOC),'COLUMN_NAME');
    $values = array_intersect_key($values,array_flip($present));
    foreach ($columns as $column) {
        $name = $column['COLUMN_NAME'];
        if (array_key_exists($name,$values) || str_contains($column['EXTRA'],'auto_increment')) continue;
        $values[$name] = match (true) {
            $column['DATA_TYPE'] === 'enum' => trim(explode(',',substr($column['COLUMN_TYPE'],5))[0],"')"),
            in_array($column['DATA_TYPE'],['int','bigint','tinyint','smallint','mediumint','decimal','float','double'],true) => 1,
            in_array($column['DATA_TYPE'],['date'],true) => '2026-01-01',
            in_array($column['DATA_TYPE'],['datetime','timestamp'],true) => '2026-01-01 00:00:00',
            $column['DATA_TYPE'] === 'json' => '{}',
            default => 'synthetic',
        };
    }
    $statement = $db->prepare("INSERT INTO `$table` (`" . implode('`,`',array_keys($values)) . '`) VALUES (' . implode(',',array_fill(0,count($values),'?')) . ')');
    $statement->execute(array_map(fn($value)=>(string)$value,array_values($values)));
    return (int)$db->insert_id;
}
$probe = ['users'=>['id'=>9001,'email'=>'mig-owner@example.test','company_id'=>4242,'company_name'=>'Migration Probe Ltd','status'=>'Verified'],
    'employees'=>['id'=>9002,'company_id'=>4242,'first_name'=>'Mig','last_name'=>'Probe','email'=>'mig-employee@example.test','unique_id'=>'EMP-MIG-1','hire_date'=>'2026-01-05'],
    'projects'=>['id'=>9003,'company_id'=>4242,'projectName'=>'Migration probe project','unique_id'=>'PRJ-MIG-1'],
    'tasks'=>['id'=>9004,'company_id'=>4242,'title'=>'Migration probe task','project_id'=>'PRJ-MIG-1','due_date'=>'2026-12-01','assignee'=>'9002']];
$keys = ['users'=>'email','employees'=>'email','projects'=>'projectName','tasks'=>'title'];

try {
    $started = microtime(true);
    // 1. Clean install, then a rerun that must change nothing.
    [$clean,$cleanDb] = disposable($server);
    [$code,$out] = migrate($clean,['--install']);
    expect($code === 0 && str_contains($out,'Database is up to date.'),'Clean install finishes');
    expect((int)$cleanDb->query('SELECT COUNT(*) AS n FROM schema_migrations')->fetch_assoc()['n'] === count($names),'Clean install records all ' . count($names) . ' migrations');
    [$code,$out] = migrate($clean);
    expect($code === 0 && !str_contains($out,'Applied'),'Rerunning the migrator on an up-to-date database changes nothing');
    $reference = schema_of($cleanDb);

    // 2. Upgrade from baselines with synthetic rows in the core tables. Each build takes
    // about 25 seconds, so the default run samples the early baselines, every fifth one
    // and every recent one; --all covers every baseline.
    $baselines = in_array('--all',$argv,true) ? range(0,count($files) - 1) : array_values(array_unique(array_filter([0,1,2,...range(5,count($files) - 1,5),...range(max(0,count($files) - 10),count($files) - 1)],fn($k)=>$k < count($files))));
    sort($baselines);
    $notes['baselines'] = count($baselines) . ' of ' . count($files);
    foreach ($baselines as $k) {
        [$name,$db] = disposable($server);
        run_sql($db,file_get_contents($root . '/database/schema.sql'));
        $db->query('CREATE TABLE IF NOT EXISTS schema_migrations (name VARCHAR(190) PRIMARY KEY, applied_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP) ENGINE=InnoDB');
        for ($i = 0; $i < $k; $i++) { run_sql($db,file_get_contents($files[$i])); $db->query("INSERT INTO schema_migrations (name) VALUES ('" . $db->real_escape_string($names[$i]) . "')"); }
        foreach ($probe as $table=>$values) seed($db,$table,$values);
        [$code,$out] = migrate($name);
        $label = 'baseline ' . ($k === 0 ? 'schema.sql' : 'after ' . $names[$k - 1]);
        expect($code === 0,"Upgrade from $label finishes" . ($code === 0 ? '' : ': ' . trim(substr($out,-200))));
        expect((int)$db->query('SELECT COUNT(*) AS n FROM schema_migrations')->fetch_assoc()['n'] === count($names),"Upgrade from $label records every migration");
        $kept = true;
        foreach ($probe as $table=>$values) {
            $row = $db->query("SELECT * FROM `$table` WHERE id = " . (int)$values['id'])->fetch_assoc();
            if (!$row || $row[$keys[$table]] !== $values[$keys[$table]]) $kept = false;
        }
        expect($kept,"Upgrade from $label keeps the synthetic owner, employee, project and task");
        $difference = array_merge(array_diff($reference,schema_of($db)),array_map(fn($line)=>'extra:' . $line,array_diff(schema_of($db),$reference)));
        expect(!$difference,"Upgrade from $label ends with the clean-install schema" . ($difference ? ': ' . implode('; ',array_slice($difference,0,4)) : ''));
        $db->close();
        $server->query("DROP DATABASE `$name`"); $created = array_values(array_diff($created,[$name]));
    }

    // 3. The email uniqueness migration stops on duplicates and removes nothing.
    [$name,$db] = disposable($server);
    run_sql($db,file_get_contents($root . '/database/schema.sql'));
    $db->query('CREATE TABLE IF NOT EXISTS schema_migrations (name VARCHAR(190) PRIMARY KEY, applied_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP) ENGINE=InnoDB');
    run_sql($db,file_get_contents($files[0])); $db->query("INSERT INTO schema_migrations (name) VALUES ('{$names[0]}')");
    // The legacy case: one email owning two companies (company_id is unique per owner).
    seed($db,'users',['id'=>9101] + $probe['users']); seed($db,'users',['id'=>9102,'company_id'=>4243] + $probe['users']);
    [$code,$out] = migrate($name);
    expect($code === 1 && str_contains($out,'requires existing duplicate accounts to be reconciled'),'Duplicate owner emails stop the upgrade with an explanation');
    expect((int)$db->query("SELECT COUNT(*) AS n FROM users WHERE email = 'mig-owner@example.test'")->fetch_assoc()['n'] === 2,'Stopped upgrade removes no accounts');
    expect(!$db->query("SELECT name FROM schema_migrations WHERE name = '002_unique_email.sql'")->num_rows,'Stopped migration is not recorded as applied');

    // 4. Which migrations fail if run a second time. Informational: shows which files must
    // never be replayed by hand. All run on the clean install from step 1, in order.
    foreach ($files as $index=>$file) {
        try { run_sql($cleanDb,file_get_contents($file)); $notes['rerun_safe'][] = $names[$index]; }
        catch (mysqli_sql_exception $error) { $notes['rerun_fails'][] = $names[$index] . ' (MySQL ' . $error->getCode() . ')'; }
    }
    $notes['seconds'] = round(microtime(true) - $started,1);
} finally {
    foreach ($created as $name) if (str_starts_with($name,'simplr_dev_mig_')) $server->query("DROP DATABASE IF EXISTS `$name`");
    echo json_encode(['checks'=>count($checks),'passed'=>count(array_filter($checks,fn($c)=>$c['passed'])),'failures'=>array_values(array_map(fn($c)=>$c['name'],array_filter($checks,fn($c)=>!$c['passed']))),'notes'=>$notes],JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . PHP_EOL;
}
exit(count(array_filter($checks,fn($c)=>!$c['passed'])) ? 1 : 0);
