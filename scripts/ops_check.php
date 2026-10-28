<?php
declare(strict_types=1);
// Operations health check for cron alerting. Read-only; prints counts only, never
// personal data, tokens or links. Exit 0 = healthy, 1 = something needs attention.
//   php scripts/ops_check.php           table
//   php scripts/ops_check.php --json    machine-readable
//   */5 * * * * cd /srv/simplrsuite && php scripts/ops_check.php --quiet || <alert command>
// Optional settings: BACKUP_DIR (folder holding nightly backups; alerts when the newest
// file is older than 26 hours) and PHP_ERROR_LOG (PHP error log file to scan when PHP's
// own error_log setting points elsewhere).
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
require_once __DIR__ . '/../app/jobs.php';

$checks = [];
function check(string $name, string $state, string $message): void {
    global $checks;
    $checks[] = ['check'=>$name,'state'=>$state,'message'=>$message];
}

// Database and migrations.
try {
    $db = database();
    $db->query('SELECT 1');
    check('database','ok','Connected.');
    $applied = array_column(db_rows($db,'SELECT name FROM schema_migrations'),'name');
    $pending = array_diff(array_map('basename',glob(__DIR__ . '/../database/migrations/*.sql')),$applied);
    check('migrations',$pending ? 'alert' : 'ok',$pending ? count($pending) . ' migration(s) not applied: ' . implode(', ',$pending) : 'All applied.');
} catch (Throwable $error) {
    check('database','alert','Cannot connect (' . get_class($error) . '). Check DB_* settings and that MySQL is running.');
    $db = null;
}

if ($db) {
    // Scheduled jobs.
    foreach (scheduler_status($db) as $job=>$status) check('job:' . $job,$status['state'] === 'ok' ? 'ok' : 'alert',$status['message']);

    // Email outbox: stuck messages and messages that gave up after three attempts.
    $stuck = (int)db_one($db,"SELECT COUNT(*) AS n FROM notifications WHERE ((email_status = 'pending' AND created_at < UTC_TIMESTAMP() - INTERVAL 15 MINUTE) OR (email_status = 'sending' AND email_claimed_at < UTC_TIMESTAMP() - INTERVAL 15 MINUTE)) AND created_at > UTC_TIMESTAMP() - INTERVAL 2 DAY")['n'];
    $failed = (int)db_one($db,"SELECT COUNT(*) AS n FROM notifications WHERE email_status = 'failed' AND email_attempts >= 3 AND created_at > UTC_TIMESTAMP() - INTERVAL 1 DAY")['n'];
    check('email-outbox',$stuck || $failed ? 'alert' : 'ok',$stuck || $failed ? "$stuck waiting over 15 minutes, $failed failed for good in the last day. Check SMTP and the notifications job." : 'Nothing stuck or failed.');

    // Payments: verification mismatches and orders whose provider state is unknown.
    $mismatches = (int)db_one($db,"SELECT COUNT(*) AS n FROM billing_events WHERE result = 'mismatch' AND created_at > UTC_TIMESTAMP() - INTERVAL 1 DAY")['n'];
    $unknown = (int)db_one($db,"SELECT COUNT(*) AS n FROM billing_orders WHERE state = 'initialization_unknown' AND created_at < UTC_TIMESTAMP() - INTERVAL 1 HOUR")['n'];
    check('payments',$mismatches || $unknown ? 'alert' : 'ok',$mismatches || $unknown ? "$mismatches verification mismatch(es) in the last day, $unknown order(s) in an unknown provider state for over an hour. Reconcile with the provider dashboard." : 'No mismatches or unknown orders.');

    // GitHub repositories whose last sync failed (shown to owners; informational here).
    $githubErrors = (int)db_one($db,"SELECT COUNT(*) AS n FROM github_repository_links WHERE last_sync_error <> ''")['n'];
    check('github-sync',$githubErrors ? 'warn' : 'ok',$githubErrors ? "$githubErrors linked repositor" . ($githubErrors === 1 ? 'y' : 'ies') . ' failed their last sync; owners see the reason on the GitHub page.' : 'No sync errors.');
}

// Storage: free space where attachments are written.
$storage = SIMPLR_ROOT . '/storage';
$free = @disk_free_space($storage); $total = @disk_total_space($storage);
if ($free === false || !$total) check('storage','alert','Cannot read free space for storage/.');
else {
    $percent = $free / $total * 100;
    check('storage',$free < 1073741824 || $percent < 10 ? 'alert' : 'ok',sprintf('%.1f GB free (%.0f%%).',$free / 1073741824,$percent));
}
check('storage-writable',is_writable($storage) ? 'ok' : 'alert',is_writable($storage) ? 'storage/ is writable.' : 'storage/ is not writable by this user; uploads and mail logs will fail.');

// Backups: newest file in BACKUP_DIR.
$backupDir = config('BACKUP_DIR');
if ($backupDir === '') check('backups','warn','BACKUP_DIR is not set, so backup freshness is not checked.');
else {
    $newest = 0;
    foreach (glob(rtrim($backupDir,'/\\') . '/*') ?: [] as $file) if (is_file($file)) $newest = max($newest,(int)filemtime($file));
    $age = $newest ? (time() - $newest) / 3600 : null;
    check('backups',$age !== null && $age <= 26 ? 'ok' : 'alert',$age === null ? 'No backup files found in BACKUP_DIR.' : sprintf('Newest backup is %.1f hours old.',$age));
}

// Recent application errors: lines the exception handler writes as "Simplr [reference]".
$log = config('PHP_ERROR_LOG') ?: (string)ini_get('error_log');
if ($log === '' || !is_file($log) || !is_readable($log)) check('errors','warn','No readable PHP error log (set PHP_ERROR_LOG); recent application errors are not counted.');
else {
    $since = time() - 3600; $count = 0;
    $handle = fopen($log,'r'); $size = filesize($log);
    if ($size > 2097152) fseek($handle,-2097152,SEEK_END); // the last 2 MB is plenty for one hour
    while (($line = fgets($handle)) !== false) {
        if (!str_contains($line,'Simplr [')) continue;
        if (preg_match('/^\[([^\]]+)\]/',$line,$m) && ($time = strtotime($m[1])) !== false && $time >= $since) $count++;
    }
    fclose($handle);
    check('errors',$count >= 10 ? 'alert' : ($count ? 'warn' : 'ok'),$count ? "$count application error(s) in the last hour; look up their references in the log." : 'No application errors in the last hour.');
}

$alerts = array_values(array_filter($checks,fn($c)=>$c['state'] === 'alert'));
if (in_array('--json',$argv,true)) echo json_encode(['healthy'=>!$alerts,'checks'=>$checks],JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . PHP_EOL;
elseif (!in_array('--quiet',$argv,true) || $alerts) foreach (in_array('--quiet',$argv,true) ? $alerts : $checks as $c) printf("%-20s %-6s %s\n",$c['check'],strtoupper($c['state']),$c['message']);
exit($alerts ? 1 : 0);
