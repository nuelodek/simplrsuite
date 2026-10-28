<?php
declare(strict_types=1);
// Scheduled GitHub issue sync. Run every 15 minutes from cron or Plesk Scheduled Tasks:
//   */15 * * * * cd /path/to/simplrsuite && php jobs/github-sync.php
// Optional: --company=ID limits a run to one workspace.
// Run jobs/notifications.php too: it emails the alerts this job creates and sends
// due-date reminders for the tasks it assigns.
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
require_once __DIR__ . '/../app/github.php';
require_once __DIR__ . '/../app/jobs.php';

$db = database();
if (!job_begin($db,'github-sync')) {
    echo json_encode(['skipped'=>'Another GitHub sync is still running.']) . PHP_EOL;
    exit;
}
$minutes = max(5,(int)config('GITHUB_SYNC_MINUTES','15'));
// Stop picking up repositories a minute before the next run is due; the rest go first next time.
$deadline = time() + $minutes * 60 - 60;
$company = 0;
foreach (array_slice($argv,1) as $argument) if (preg_match('/^--company=(\d+)$/D',$argument,$match)) $company = (int)$match[1];
$result = ['links'=>0,'imported'=>0,'completed'=>0,'assigned'=>0,'failed'=>0];
$links = db_rows($db,'SELECT id, company_id FROM github_repository_links WHERE auto_sync = 1 AND connection_id IS NOT NULL AND (last_synced_at IS NULL OR last_synced_at < UTC_TIMESTAMP() - INTERVAL ? MINUTE)' . ($company ? ' AND company_id = ?' : '') . ' ORDER BY last_synced_at IS NOT NULL, last_synced_at, id LIMIT 200',$company ? [$minutes - 1,$company] : [$minutes - 1]);
foreach ($links as $link) {
    if (time() >= $deadline) { $result['deferred'] = count($links) - $result['links']; break; }
    $result['links']++;
    try {
        $synced = github_sync_issues($db,['company_id'=>(int)$link['company_id'],'id'=>0],(int)$link['id']);
        foreach (['imported','completed','assigned'] as $key) $result[$key] += $synced[$key];
    } catch (GitHubSyncBusy) {
        // A Sync click is handling this repository right now; it records its own result.
        $result['busy'] = ($result['busy'] ?? 0) + 1;
    } catch (Throwable $error) {
        $result['failed']++;
        $message = $error instanceof GitHubUnavailable ? $error->getMessage() : 'Automatic sync failed. Details are in the server log.';
        if (!$error instanceof GitHubUnavailable) error_log('Simplr: GitHub sync failed for link ' . (int)$link['id'] . ' (' . get_class($error) . ')');
        // Recording the attempt time keeps a failing repository from being retried every minute.
        db_run($db,'UPDATE github_repository_links SET last_sync_error = ?, last_synced_at = UTC_TIMESTAMP() WHERE id = ?',[mb_substr($message,0,255),(int)$link['id']]);
    }
}
job_finish($db,$result);
echo json_encode($result) . PHP_EOL;
