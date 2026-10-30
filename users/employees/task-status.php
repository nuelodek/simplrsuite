<?php
require_once __DIR__ . '/../../app/access.php';
require_once __DIR__ . '/../../app/task-work.php';
require_post(); $db = database(); $user = staff_user($db);
$id = filter_var(input('task_id'),FILTER_VALIDATE_INT,['options'=>['min_range'=>1]]);
$status = input('status');
if (!$id || !in_array($status,['pending','ongoing','completed'],true)) fail('Choose a valid task and status.',422);
$task = db_one($db,'SELECT id FROM tasks WHERE id = ? AND company_id = ? AND archived_at IS NULL AND (assignee = ? OR assignee = ?)',[$id,$user['company_id'],(string)$user['id'],$user['unique_id']]);
if (($_SESSION['auth_role'] ?? '') !== 'employee' || !$task) fail('This task is not assigned to you.',404);
if (!billing_module_active($db,(int)$user['company_id'],'tasks')) fail('This toolkit needs an active subscription for editing. Contact your workspace owner.',402);
db_run($db,'UPDATE tasks SET status = ? WHERE id = ? AND company_id = ? AND archived_at IS NULL AND (assignee = ? OR assignee = ?)',[$status,$id,$user['company_id'],(string)$user['id'],$user['unique_id']]);
if ($status === 'completed') task_work_pause_task($db,(int)$user['company_id'],$id);
flash('Task status updated.'); redirect('/users/employees/workspace.php#tasks');
