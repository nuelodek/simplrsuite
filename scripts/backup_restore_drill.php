<?php
// Local verification only. Creates a new database; never overwrites/drops one.
declare(strict_types=1);
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
require_once __DIR__ . '/../app/database.php';
$source = config('DB_DATABASE');
if (config('APP_ENV') !== 'local' || config('MAIL_TRANSPORT') !== 'log' || !in_array(config('DB_HOST'),['localhost','127.0.0.1'],true) || !preg_match('/^simplr_dev[a-z0-9_]*$/D',$source)) { fwrite(STDERR,"This drill is restricted to the isolated local development database.\n"); exit(2); }
$bin = $argv[1] ?? '';
if (!is_file($bin . '/mysqldump.exe') || !is_file($bin . '/mysql.exe')) { fwrite(STDERR,"Pass the local MySQL bin directory.\n"); exit(2); }
$stamp = gmdate('Ymd_His') . '_' . bin2hex(random_bytes(3));
$target = 'simplr_restore_' . $stamp;
$dir = SIMPLR_ROOT . '/.local/backup-drills/' . $stamp;
if (!mkdir($dir,0700,true)) throw new RuntimeException('Cannot create backup directory');
$options = $dir . '/client.cnf';
$quote = fn($v)=>'"' . str_replace(["\\",'"',"\r","\n"],["\\\\",'\\"','\\r','\\n'],$v) . '"';
$client = '[client]' . PHP_EOL;
foreach (['host'=>'DB_HOST','port'=>'DB_PORT','user'=>'DB_USERNAME','password'=>'DB_PASSWORD'] as $key=>$env) $client .= $key . '=' . $quote(config($env)) . PHP_EOL;
file_put_contents($options,$client,LOCK_EX); @chmod($options,0600);
function run_backup_process(array $args, array $descriptors): void {
    $process = proc_open($args,$descriptors,$pipes,SIMPLR_ROOT);
    if (!is_resource($process) || proc_close($process) !== 0) throw new RuntimeException('Backup or restore process failed; inspect the private drill log.');
}
function table_counts(mysqli $db): array {
    $counts=[];
    foreach ($db->query('SHOW TABLES')->fetch_all() as $table) {
        $name=$table[0]; if (!preg_match('/^[a-zA-Z0-9_]+$/D',$name)) throw new RuntimeException('Unexpected table name');
        $counts[$name]=(int)$db->query("SELECT COUNT(*) FROM `$name`")->fetch_row()[0];
    }
    ksort($counts); return $counts;
}
function table_checksums(mysqli $db, array $tables): array {
    $checksums=[];
    foreach (array_keys($tables) as $name) {
        $row=$db->query("CHECKSUM TABLE `$name` EXTENDED")->fetch_assoc();
        if ($row['Checksum'] === null) throw new RuntimeException('Database table checksum unavailable');
        $checksums[$name]=(string)$row['Checksum'];
    }
    return $checksums;
}
try {
    $db = database(); $before = table_counts($db); $checksums = table_checksums($db,$before);
    run_backup_process([$bin.'/mysqldump.exe','--defaults-extra-file='.$options,'--single-transaction','--skip-lock-tables','--no-tablespaces','--set-gtid-purged=OFF','--skip-comments',$source],[0=>['pipe','r'],1=>['file',$dir.'/database.sql','w'],2=>['file',$dir.'/process.log','a']]);
    // CREATE without IF NOT EXISTS fails safely on any unexpected name collision.
    $db->query("CREATE DATABASE `$target` CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci");
    run_backup_process([$bin.'/mysql.exe','--defaults-extra-file='.$options,'--database='.$target],[0=>['file',$dir.'/database.sql','r'],1=>['file',$dir.'/restore-output.log','w'],2=>['file',$dir.'/process.log','a']]);
    putenv('DB_DATABASE='.$target); $restored=database(); putenv('DB_DATABASE='.$source);
    $after=table_counts($restored);
    if ($before !== $after || $before !== table_counts($db)) throw new RuntimeException('Table counts differ or the source changed during the drill.');
    if ($checksums !== table_checksums($restored,$after) || $checksums !== table_checksums($db,$before)) throw new RuntimeException('Table contents differ or the source changed during the drill.');
    $attachments=db_rows($db,'SELECT storage_name,content_hash FROM record_attachments');
    if (!mkdir($dir.'/attachments',0700)) throw new RuntimeException('Cannot create attachment backup directory');
    foreach ($attachments as $file) {
        if (!preg_match('/^[a-f0-9]{64}$/D',$file['storage_name'])) throw new RuntimeException('Unexpected attachment storage name');
        $path=SIMPLR_ROOT.'/storage/attachments/'.$file['storage_name'];
        $copy=$dir.'/attachments/'.$file['storage_name'];
        if (!copy($path,$copy) || hash_file('sha256',$copy) !== $file['content_hash']) throw new RuntimeException('Attachment backup integrity failed.');
    }
    $report=['passed'=>true,'tables_verified'=>count($after),'table_checksums_match'=>true,'rows_verified'=>array_sum($after),'attachments_verified'=>count($attachments),'dump_sha256'=>hash_file('sha256',$dir.'/database.sql'),'restored_database'=>$target,'backup_directory'=>'.local/backup-drills/'.$stamp,'source_preserved'=>true,'scope'=>'Local development snapshot only; no production backup or retention configured.'];
    file_put_contents(SIMPLR_ROOT.'/work/test-results/backup-restore.json',json_encode($report,JSON_PRETTY_PRINT));
    echo json_encode($report) . PHP_EOL;
} finally {
    // Only the exact credentials file created by this invocation is removed.
    if (is_file($options)) unlink($options);
}
