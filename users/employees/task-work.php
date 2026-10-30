<?php
require_once __DIR__ . '/../../app/access.php';
require_once __DIR__ . '/../../app/task-work.php';

require_post();
$db = database();
$user = staff_user($db);
if (($_SESSION['auth_role'] ?? '') !== 'employee') fail('Only the assigned employee can track work on this task.',403);
if (!billing_module_active($db,(int)$user['company_id'],'tasks')) fail('This toolkit needs an active subscription for editing. Contact your workspace owner.',402);
$taskId = filter_var(input('task_id'),FILTER_VALIDATE_INT,['options'=>['min_range'=>1]]);
$action = input('action');
if (!$taskId || !in_array($action,['start','pause'],true)) fail('Choose a valid task timer action.',422);

$companyId = (int)$user['company_id'];
$employeeId = (int)$user['id'];
$today = date('Y-m-d');
$db->begin_transaction();
$reject = static function (string $message, int $status) use ($db): never {
    $db->rollback();
    fail($message,$status);
};

try {
    if (!db_one($db,'SELECT id FROM employees WHERE id = ? AND company_id = ? FOR UPDATE',[$employeeId,$companyId])) $reject('Your employee record could not be found.',403);
    $task = db_one($db,'SELECT id, title, status FROM tasks WHERE id = ? AND company_id = ? AND archived_at IS NULL AND (assignee = ? OR assignee = ?) FOR UPDATE',[$taskId,$companyId,(string)$employeeId,(string)$user['unique_id']]);
    if (!$task) $reject('This task is not assigned to you.',404);
    if ($action === 'start' && $task['status'] === 'completed') $reject('Completed tasks cannot be started.',409);

    $attendance = db_one($db,'SELECT status FROM attendance WHERE company_id = ? AND employee_id = ? AND date = ? ORDER BY id DESC LIMIT 1 FOR UPDATE',[$companyId,$employeeId,$today]);
    $active = db_one($db,'SELECT id, task_id FROM task_work_sessions WHERE company_id = ? AND employee_id = ? AND ended_at IS NULL ORDER BY id DESC LIMIT 1 FOR UPDATE',[$companyId,$employeeId]);
    if ($action === 'start') {
        if (($attendance['status'] ?? '') !== 'clocked_in') {
            if ($active) task_work_pause_active($db,$companyId,$employeeId);
            $db->commit();
            fail('Clock in first. Task timers run only while you are clocked in and not on a break.',409);
        }
        if ($active) $reject((int)$active['task_id'] === $taskId ? 'This task timer is already running.' : 'Pause your current task before starting another.',409);
        db_run($db,'INSERT INTO task_work_sessions (company_id,task_id,employee_id,started_at) VALUES (?,?,?,UTC_TIMESTAMP())',[$companyId,$taskId,$employeeId]);
        if ($task['status'] === 'pending') db_run($db,'UPDATE tasks SET status = ? WHERE id = ? AND company_id = ?', ['ongoing',$taskId,$companyId]);
    } else {
        if (!$active || (int)$active['task_id'] !== $taskId) $reject('There is no running timer for this task.',409);
        db_run($db,'UPDATE task_work_sessions SET ended_at = UTC_TIMESTAMP() WHERE id = ? AND company_id = ? AND employee_id = ? AND ended_at IS NULL',[(int)$active['id'],$companyId,$employeeId]);
    }

    $summary = task_work_summaries($db,$companyId,$employeeId,[$taskId])[$taskId] ?? [];
    $db->commit();
    json_response([
        'ok'=>true,
        'action'=>$action,
        'task_id'=>$taskId,
        'active'=>(bool)($summary['active'] ?? false),
        'worked_seconds'=>(int)($summary['worked_seconds'] ?? 0),
        'status'=>$task['status'] === 'pending' && $action === 'start' ? 'ongoing' : $task['status'],
    ]);
} catch (Throwable $error) {
    $db->rollback();
    throw $error;
}
