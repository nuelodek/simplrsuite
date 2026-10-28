<?php
declare(strict_types=1);
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
require_once __DIR__ . '/../app/discipline.php';
require_once __DIR__ . '/../app/jobs.php';

$db = database();
if (!job_begin($db,'notifications')) { echo json_encode(['skipped'=>'The previous run is still working.']) . PHP_EOL; exit; }
$result = ['task_reminders'=>0,'queries_timed_out'=>0,'warnings_expired'=>0,'emails'=>[],'digests_sent'=>0];

do {
    $dueQueries = db_rows($db,"SELECT q.id,q.company_id FROM task_queries q WHERE q.status = 'awaiting_response' AND q.respond_by <= UTC_TIMESTAMP() ORDER BY q.id LIMIT 500");
    foreach ($dueQueries as $dueQuery) {
        $db->begin_transaction();
        try {
            $query = db_one($db,"SELECT * FROM task_queries WHERE id = ? AND company_id = ? FOR UPDATE",[(int)$dueQuery['id'],(int)$dueQuery['company_id']]);
            if ($query && $query['status'] === 'awaiting_response' && strtotime($query['respond_by'] . ' UTC') <= time()) {
                db_run($db,"UPDATE task_queries SET status = 'no_response' WHERE id = ? AND company_id = ? AND status = 'awaiting_response'",[(int)$query['id'],(int)$query['company_id']]);
                audit_event($db,(int)$query['company_id'],'query.no_response','task_queries',(string)$query['id'],['reference'=>$query['reference']],'system',0);
                $db->commit();
                query_parties_notify($db,$query,'query.no_response','No response received for query ' . $query['reference'],'The response window has ended. Review the record and decide whether to close or escalate it.',[]);
                $result['queries_timed_out']++;
            } else {
                $db->commit();
            }
        } catch (Throwable $error) {
            $db->rollback();
            throw $error;
        }
    }
} while (count($dueQueries) === 500);

do {
    $expiredWarnings = db_rows($db,"SELECT p.id,p.company_id,p.subject_role,p.subject_id,p.penalty_type,p.query_id,q.reference FROM penalties p JOIN task_queries q ON q.id = p.query_id AND q.company_id = p.company_id WHERE p.status = 'active' AND p.expires_on IS NOT NULL AND p.expires_on <= UTC_DATE() AND p.penalty_type IN ('verbal_warning','written_warning','final_warning') ORDER BY p.id LIMIT 500");
    foreach ($expiredWarnings as $warning) {
        $db->begin_transaction();
        try {
            $current = db_one($db,'SELECT status,expires_on FROM penalties WHERE id = ? AND company_id = ? FOR UPDATE',[(int)$warning['id'],(int)$warning['company_id']]);
            if ($current && $current['status'] === 'active' && $current['expires_on'] !== null && $current['expires_on'] <= gmdate('Y-m-d')) {
                db_run($db,"UPDATE penalties SET status = 'expired' WHERE id = ? AND company_id = ? AND status = 'active'",[(int)$warning['id'],(int)$warning['company_id']]);
                audit_event($db,(int)$warning['company_id'],'penalty.expired','penalties',(string)$warning['id'],['reference'=>$warning['reference']],'system',0);
                $db->commit();
                $title = penalty_types()[$warning['penalty_type']] . ' expired';
                $body = 'The warning recorded after query ' . $warning['reference'] . ' has expired and is no longer active.';
                notify($db,(int)$warning['company_id'],$warning['subject_role'],(int)$warning['subject_id'],'penalty.expired',$title,$body,query_url((int)$warning['query_id']),false,'penalty-expired:' . (int)$warning['id']);
                $result['warnings_expired']++;
            } else {
                $db->commit();
            }
        } catch (Throwable $error) {
            $db->rollback();
            throw $error;
        }
    }
} while (count($expiredWarnings) === 500);

$dueTasksLastId = 0;
do {
    $dueTasks = db_rows($db,"SELECT t.id,t.company_id,t.title,t.due_date,t.assignee,COALESCE(NULLIF(s.timezone,''),?) AS timezone FROM tasks t LEFT JOIN workspace_settings s ON s.company_id = t.company_id WHERE t.id > ? AND t.archived_at IS NULL AND t.status <> 'completed' AND t.due_date IS NOT NULL AND t.assignee IS NOT NULL AND t.assignee <> '' AND t.due_date <= DATE_ADD(UTC_DATE(), INTERVAL 1 DAY) ORDER BY t.id LIMIT 500",[config('APP_TIMEZONE','Africa/Lagos'),$dueTasksLastId]);
    foreach ($dueTasks as $task) {
        $dueTasksLastId = (int)$task['id'];
        $timezone = new DateTimeZone((string)$task['timezone']);
        $localToday = new DateTimeImmutable('today',$timezone);
        $dueDate = new DateTimeImmutable($task['due_date'],$timezone);
        if ($dueDate > $localToday) continue;
        $assignee = trim((string)$task['assignee']);
        $employee = db_one($db,'SELECT id FROM employees WHERE company_id = ? AND archived_at IS NULL AND (id = ? OR unique_id = ?) LIMIT 1',[(int)$task['company_id'],ctype_digit($assignee) ? (int)$assignee : 0,$assignee]);
        if (!$employee) continue;
        $daysLate = (int)$dueDate->diff($localToday)->format('%r%a');
        $body = $daysLate > 0 ? 'This task is overdue by ' . $daysLate . ' ' . ($daysLate === 1 ? 'day' : 'days') . '.' : 'This task is due today.';
        $alertId = notify($db,(int)$task['company_id'],'employee',(int)$employee['id'],'task.due',$daysLate > 0 ? 'Overdue task: ' . $task['title'] : 'Task due today: ' . $task['title'],$body,'/users/employees/workspace.php#tasks',false,'task-reminder:' . (int)$task['id'] . ':' . $localToday->format('Y-m-d'));
        if ($alertId !== null) $result['task_reminders']++;
    }
} while (count($dueTasks) === 500);

$result['emails'] = notification_flush_outbox($db);
$result['digests_sent'] = notification_send_digests($db);
job_finish($db,$result);
echo json_encode($result,JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES) . PHP_EOL;
