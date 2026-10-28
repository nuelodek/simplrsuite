<?php
declare(strict_types=1);
// Scheduler health check for cron alerting. Exits 0 when every job ran recently and
// its last run finished, 1 otherwise. Read-only; prints no personal data.
//   php scripts/scheduler_status.php          human-readable table
//   php scripts/scheduler_status.php --json   machine-readable report
//   */5 * * * * php /path/to/simplrsuite/scripts/scheduler_status.php --quiet || <your alert command>
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
require_once __DIR__ . '/../app/jobs.php';

$report = scheduler_status(database());
$healthy = !array_filter($report,fn($job)=>$job['state'] !== 'ok');
if (in_array('--json',$argv,true)) {
    echo json_encode(['healthy'=>$healthy,'jobs'=>$report],JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . PHP_EOL;
} elseif (!in_array('--quiet',$argv,true) || !$healthy) {
    foreach ($report as $job=>$status) {
        printf("%-14s %-7s %s%s\n",$job,strtoupper($status['state']),$status['message'],$status['consecutive_failures'] > 1 ? ' (' . $status['consecutive_failures'] . ' failures in a row)' : '');
    }
}
exit($healthy ? 0 : 1);
