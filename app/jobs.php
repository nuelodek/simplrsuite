<?php
declare(strict_types=1);
// Heartbeats for the cron jobs in jobs/. Each run takes a database lock so cron
// never runs two copies at once, and records how it ended in scheduler_runs, so
// scripts/scheduler_status.php can tell a stopped or failing job from a quiet one.
require_once __DIR__ . '/database.php';

// How often each job is expected to start, in minutes. Keep in step with deploy/scheduler.md.
function scheduled_jobs(): array {
    return [
        'notifications'=>['label'=>'Alerts, reminders and email outbox','every'=>1],
        'github-sync'=>['label'=>'GitHub issue sync','every'=>max(5,(int)config('GITHUB_SYNC_MINUTES','15'))],
    ];
}

// Returns false when another run of the same job still holds the lock; the caller exits.
function job_begin(mysqli $db, string $job): bool {
    if ((int)(db_one($db,'SELECT GET_LOCK(?, 0) AS acquired',['simplr_job_' . $job])['acquired'] ?? 0) !== 1) return false;
    db_run($db,"INSERT INTO scheduler_runs (job,last_started_at,last_status) VALUES (?,UTC_TIMESTAMP(),'running') ON DUPLICATE KEY UPDATE last_started_at = UTC_TIMESTAMP(), last_status = 'running'",[$job]);
    $GLOBALS['simplr_job'] = ['name'=>$job,'started'=>microtime(true),'done'=>false];
    // An uncaught exception or fatal error still leaves a failed heartbeat behind.
    register_shutdown_function(static function () use ($db): void {
        $run = $GLOBALS['simplr_job'] ?? null;
        if (!$run || $run['done']) return;
        $error = error_get_last();
        $reason = $error && preg_match('/^Uncaught ([\\\\\w]+)/',(string)$error['message'],$match) ? 'Stopped by ' . $match[1] . '. Details are in the server log.' : 'Stopped before finishing. Details are in the server log.';
        try { job_record($db,$run['name'],'failed',$run['started'],null,$reason); } catch (Throwable) { error_log('Simplr: could not record the failed ' . $run['name'] . ' job run'); }
    });
    return true;
}

// $summary holds counts only; it is stored for operators and must not contain personal data.
function job_finish(mysqli $db, array $summary): void {
    $run = $GLOBALS['simplr_job'] ?? null;
    if (!$run || $run['done']) return;
    $GLOBALS['simplr_job']['done'] = true;
    job_record($db,$run['name'],'ok',$run['started'],$summary,null);
    db_one($db,'SELECT RELEASE_LOCK(?) AS released',['simplr_job_' . $run['name']]);
}

function job_record(mysqli $db, string $job, string $status, float $started, ?array $summary, ?string $error): void {
    $ok = $status === 'ok' ? 1 : 0;
    db_run($db,'UPDATE scheduler_runs SET last_finished_at = UTC_TIMESTAMP(), last_status = ?, last_duration_ms = ?, last_summary = ?, last_error = ?, last_succeeded_at = IF(? = 1, UTC_TIMESTAMP(), last_succeeded_at), consecutive_failures = IF(? = 1, 0, consecutive_failures + 1) WHERE job = ?',[
        $status,(int)round((microtime(true) - $started) * 1000),$summary === null ? null : mb_substr(json_encode($summary,JSON_UNESCAPED_SLASHES),0,500),$error === null ? null : mb_substr($error,0,255),$ok,$ok,$job,
    ]);
}

// One entry per expected job with state ok, never, late, stuck or failed.
function scheduler_status(mysqli $db, ?int $now = null): array {
    $now ??= time();
    $rows = [];
    foreach (db_rows($db,'SELECT * FROM scheduler_runs') as $row) $rows[$row['job']] = $row;
    $report = [];
    foreach (scheduled_jobs() as $job=>$definition) {
        $row = $rows[$job] ?? null;
        $started = $row && $row['last_started_at'] ? strtotime($row['last_started_at'] . ' UTC') : null;
        // Allow three missed starts (at least ten minutes) before calling a job late.
        $grace = max(10,$definition['every'] * 3) * 60;
        [$state,$message] = match (true) {
            $started === null => ['never','Has never run. Add it to the scheduler.'],
            $row['last_status'] === 'running' && $started < $now - 30 * 60 => ['stuck','Started ' . job_minutes($now - $started) . ' ago and has not finished.'],
            $row['last_status'] === 'failed' => ['failed',(string)$row['last_error']],
            $started < $now - $grace => ['late','Last started ' . job_minutes($now - $started) . ' ago; expected every ' . job_minutes($definition['every'] * 60) . '.'],
            default => ['ok','Last started ' . job_minutes($now - $started) . ' ago.'],
        };
        $report[$job] = ['label'=>$definition['label'],'state'=>$state,'message'=>$message,'every_minutes'=>$definition['every'],'last_started_at'=>$row['last_started_at'] ?? null,'last_succeeded_at'=>$row['last_succeeded_at'] ?? null,'last_duration_ms'=>isset($row['last_duration_ms']) ? (int)$row['last_duration_ms'] : null,'consecutive_failures'=>(int)($row['consecutive_failures'] ?? 0),'last_summary'=>isset($row['last_summary']) ? json_decode((string)$row['last_summary'],true) : null];
    }
    return $report;
}

function job_minutes(int $seconds): string {
    if ($seconds < 60) return 'less than a minute';
    $minutes = (int)round($seconds / 60);
    return $minutes === 1 ? '1 minute' : ($minutes < 120 ? $minutes . ' minutes' : round($minutes / 60) . ' hours');
}
