<?php
declare(strict_types=1);
require_once __DIR__ . '/modules.php';

function archiveable_record(string $key): array {
    if (!in_array($key,['employees','managers','tasks','projects'],true)) fail('Trash is available for employees, managers, tasks, and projects.',404);
    return module_config($key);
}
function archive_actor_id(array $viewer): int {
    return (int)$viewer['id'];
}
function archive_blockers(mysqli $db, int $companyId, string $key, int $id, bool $permanent = false): array {
    $blockers = [];
    if ($key === 'projects') {
        $count = (int)db_one($db,'SELECT COUNT(*) AS count FROM tasks t JOIN projects p ON ' . task_project_join(true) . ' WHERE t.company_id = ? AND p.id = ?',[$companyId,$id])['count'];
        if ($count) $blockers[] = $count . ' task(s) still reference this project; reassign or archive them first.';
        $count = (int)db_one($db,'SELECT COUNT(*) AS count FROM teams WHERE company_id = ? AND project_id = ?',[$companyId,$id])['count'];
        if ($count) $blockers[] = $count . ' team(s) still reference this project.';
        if (!$permanent) {
            $count = (int)db_one($db,'SELECT COUNT(*) AS count FROM github_repository_links WHERE company_id = ? AND project_id = ?',[$companyId,$id])['count'];
            if ($count) $blockers[] = 'This project is linked to GitHub. Unlink the repository before archiving it.';
        }
        if ($permanent) {
            foreach ([
                ['github_repository_links','project_id'],
                ['project_reports','project_id'],
                ['task_queries','project_id'],
                ['record_attachments','record_id'],
            ] as [$table,$column]) {
                $extra = $table === 'record_attachments' ? " AND module_key = 'projects'" : '';
                $count = (int)db_one($db,"SELECT COUNT(*) AS count FROM `$table` WHERE company_id = ? AND `$column` = ?$extra",[$companyId,$id])['count'];
                if ($count) $blockers[] = $count . ' historical record(s) in ' . $table . ' still reference this project.';
            }
        }
    } elseif ($key === 'tasks') {
        if (!$permanent) {
            $count = (int)db_one($db,'SELECT COUNT(*) AS count FROM github_issue_tasks WHERE company_id = ? AND task_id = ?',[$companyId,$id])['count'];
            if ($count) $blockers[] = 'This task is synchronized with GitHub. Unlink the repository issue before archiving it.';
        } else {
            foreach ([
                ['github_issue_tasks','task_id'],
                ['task_queries','task_id'],
                ['record_attachments','record_id'],
                ['project_reports','task_id'],
            ] as [$table,$column]) {
                $extra = $table === 'record_attachments' ? " AND module_key = 'tasks'" : '';
                $count = (int)db_one($db,"SELECT COUNT(*) AS count FROM `$table` WHERE company_id = ? AND `$column` = ?$extra",[$companyId,$id])['count'];
                if ($count) $blockers[] = $count . ' historical record(s) in ' . $table . ' still reference this task.';
            }
        }
    } elseif ($key === 'employees') {
        $employee = db_one($db,'SELECT id, unique_id FROM employees WHERE id = ? AND company_id = ?',[$id,$companyId]);
        if (!$employee) return ['The employee could not be found in this workspace.'];
        if (!$permanent) {
            $count = (int)db_one($db,'SELECT COUNT(*) AS count FROM employees WHERE company_id = ? AND manager_id = ?',[$companyId,$id])['count'];
            if ($count) $blockers[] = $count . ' employee(s) report to this person; reassign them first.';
            $count = (int)db_one($db,'SELECT COUNT(*) AS count FROM managers WHERE company_id = ? AND employee_id = ?',[$companyId,$id])['count'];
            if ($count) $blockers[] = 'This employee also has a manager role. Demote that role before archiving the employee.';
            $count = (int)db_one($db,'SELECT COUNT(*) AS count FROM tasks WHERE company_id = ? AND (assignee = ? OR assignee = ?)',[$companyId,(string)$id,(string)$employee['unique_id']])['count'];
            if ($count) $blockers[] = $count . ' task(s) are assigned to this employee; reassign them first.';
            $count = (int)db_one($db,"SELECT COUNT(*) AS count FROM task_queries WHERE company_id = ? AND subject_role = 'employee' AND subject_id = ? AND status IN ('awaiting_response','no_response')",[$companyId,$id])['count'];
            if ($count) $blockers[] = $count . ' open query record(s) must be resolved before archiving this employee.';
            $count = (int)db_one($db,"SELECT COUNT(*) AS count FROM penalties WHERE company_id = ? AND subject_role = 'employee' AND subject_id = ? AND status IN ('proposed','active','appealed')",[$companyId,$id])['count'];
            if ($count) $blockers[] = $count . ' unresolved or active penalty record(s) must be resolved before archiving this employee.';
            $count = (int)db_one($db,'SELECT COUNT(*) AS count FROM teams WHERE company_id = ? AND team_leader_id = ?',[$companyId,$id])['count'];
            if ($count) $blockers[] = $count . ' team(s) list this employee as leader; reassign the team lead first.';
            foreach (db_rows($db,'SELECT team_members FROM teams WHERE company_id = ? AND team_members IS NOT NULL',[$companyId]) as $team) {
                $members = json_decode((string)$team['team_members'],true);
                if (is_array($members) && in_array((string)$id,array_map('strval',$members),true)) {
                    $blockers[] = 'A team roster still includes this employee; update the roster first.';
                    break;
                }
            }
        } else {
            $checks = [
                ['employees','manager_id','Employee reporting lines',''],
                ['managers','employee_id','Linked manager records',''],
                ['tasks','assignee','Task assignments',''],
                ['task_queries','subject_id','Query history'," AND subject_role = 'employee'"],
                ['penalties','subject_id','Penalty history'," AND subject_role = 'employee'"],
                ['task_queries','issued_by_id','Issued query history'," AND issued_by_role = 'employee'"],
                ['penalties','proposed_by_id','Proposed penalty history'," AND proposed_by_role = 'employee'"],
                ['record_attachments','record_id','Attachments'," AND module_key = 'employees'"],
            ];
            foreach ($checks as [$table,$column,$label,$extra]) {
                $value = in_array($table,['tasks'],true) ? (string)$employee['unique_id'] : $id;
                $condition = $table === 'tasks' ? '(assignee = ? OR assignee = ?)' : "`$column` = ?";
                $params = $table === 'tasks' ? [$companyId,(string)$id,(string)$employee['unique_id']] : [$companyId,$value];
                $count = (int)db_one($db,"SELECT COUNT(*) AS count FROM `$table` WHERE company_id = ? AND $condition$extra",$params)['count'];
                if ($count) $blockers[] = $count . ' ' . strtolower($label) . ' record(s) still reference this employee.';
            }
            $count = (int)db_one($db,'SELECT COUNT(*) AS count FROM teams WHERE company_id = ? AND team_leader_id = ?',[$companyId,$id])['count'];
            if ($count) $blockers[] = $count . ' team(s) list this employee as leader.';
            foreach (db_rows($db,'SELECT id, team_members FROM teams WHERE company_id = ? AND team_members IS NOT NULL',[$companyId]) as $team) {
                $members = json_decode((string)$team['team_members'],true);
                if (is_array($members) && in_array((string)$id,array_map('strval',$members),true)) $blockers[] = 'A team roster still includes this employee.';
            }
        }
    } elseif ($key === 'managers') {
        $manager = db_one($db,'SELECT id, unique_id FROM managers WHERE id = ? AND company_id = ?',[$id,$companyId]);
        if (!$manager) return ['The manager could not be found in this workspace.'];
        if (!$permanent) {
            $count = (int)db_one($db,'SELECT COUNT(*) AS count FROM employees WHERE company_id = ? AND manager_id = ?',[$companyId,$id])['count'];
            if ($count) $blockers[] = $count . ' employee(s) report to this manager; reassign them first.';
            $count = (int)db_one($db,'SELECT COUNT(*) AS count FROM departments WHERE company_id = ? AND manager_id = ?',[$companyId,$id])['count'];
            if ($count) $blockers[] = 'This manager leads a department; assign a replacement department head first.';
            $count = (int)db_one($db,'SELECT COUNT(*) AS count FROM projects WHERE company_id = ? AND (projectManager = ? OR project_manager_id = ?)',[$companyId,$id,(string)$manager['unique_id']])['count'];
            if ($count) $blockers[] = $count . ' project(s) are managed by this person; reassign them first.';
            $count = (int)db_one($db,"SELECT COUNT(*) AS count FROM task_queries WHERE company_id = ? AND subject_role = 'manager' AND subject_id = ? AND status IN ('awaiting_response','no_response')",[$companyId,$id])['count'];
            if ($count) $blockers[] = $count . ' open query record(s) must be resolved before archiving this manager.';
            $count = (int)db_one($db,"SELECT COUNT(*) AS count FROM penalties WHERE company_id = ? AND subject_role = 'manager' AND subject_id = ? AND status IN ('proposed','active','appealed')",[$companyId,$id])['count'];
            if ($count) $blockers[] = $count . ' unresolved or active penalty record(s) must be resolved before archiving this manager.';
        } else {
            foreach ([
                ['employees','manager_id','Employee reporting lines',''],
                ['departments','manager_id','Department leadership',''],
                ['task_queries','subject_id','Query history'," AND subject_role = 'manager'"],
                ['penalties','subject_id','Penalty history'," AND subject_role = 'manager'"],
                ['task_queries','issued_by_id','Issued query history'," AND issued_by_role = 'manager'"],
                ['penalties','proposed_by_id','Proposed penalty history'," AND proposed_by_role = 'manager'"],
                ['record_attachments','record_id','Attachments'," AND module_key = 'managers'"],
            ] as [$table,$column,$label,$extra]) {
                $count = (int)db_one($db,"SELECT COUNT(*) AS count FROM `$table` WHERE company_id = ? AND `$column` = ?$extra",[$companyId,$id])['count'];
                if ($count) $blockers[] = $count . ' ' . strtolower($label) . ' record(s) still reference this manager.';
            }
            $count = (int)db_one($db,'SELECT COUNT(*) AS count FROM projects WHERE company_id = ? AND (projectManager = ? OR project_manager_id = ?)',[$companyId,$id,(string)$manager['unique_id']])['count'];
            if ($count) $blockers[] = $count . ' project(s) still reference this manager.';
        }
    }
    return $blockers;
}
function change_record_archive(mysqli $db, array $viewer, string $key, int $id, string $action, string $version = ''): void {
    $module = archiveable_record($key);
    if (!in_array($action,['archive','restore','purge'],true)) fail('Choose a valid Trash action.',422);
    $viewer['role'] = (string)($_SESSION['auth_role'] ?? '');
    $viewer['id'] = (int)($viewer['id'] ?? ($viewer['role'] === 'admin' ? ($_SESSION['user_id'] ?? 0) : ($_SESSION['staff_id'] ?? 0)));
    $viewer['company_id'] = (int)$viewer['company_id'];
    if (in_array($key,['employees','managers'],true) && $viewer['role'] !== 'admin') fail('Only the workspace owner can archive people.',403);
    if ($action === 'purge' && $viewer['role'] !== 'admin') fail('Only the workspace owner can permanently delete archived records.',403);
    if (PHP_SAPI !== 'cli') {
        $actor = workspace_user($db);
        if ((int)$actor['company_id'] !== (int)$viewer['company_id']) fail('Choose your own workspace.',403);
        require_module_access($db,$actor,$key,true,true);
    }
    $table = $module['table']; $pk = $module['pk']; $companyId = (int)$viewer['company_id'];
    $db->begin_transaction();
    try {
        $record = db_one($db,"SELECT * FROM `$table` WHERE `$pk` = ? AND company_id = ? FOR UPDATE",[$id,$companyId]);
        if (!$record) { $db->rollback(); fail('This record could not be found in your workspace.',404); }
        if ($version !== '' && !hash_equals(record_version($record),$version)) { $db->rollback(); fail('This record changed before the Trash action. Reload and review it.',409); }
        $isArchived = !empty($record['archived_at']);
        if ($action === 'archive' && $isArchived) { $db->rollback(); fail('This record is already in Trash.',409); }
        if ($action !== 'archive' && !$isArchived) { $db->rollback(); fail('Only archived records can be restored or permanently deleted.',409); }
        $blockers = archive_blockers($db,$companyId,$key,$id,$action === 'purge');
        if ($blockers) { $db->rollback(); fail(implode(' ',$blockers),409); }
        if ($action === 'purge') {
            $identityColumn = $key === 'employees' ? 'email' : 'manager_email';
            $identity = in_array($key,['employees','managers'],true) ? (string)$record[$identityColumn] : '';
            if ($identity !== '') {
                $authTable = $key === 'employees' ? 'employees_auth' : 'manager_auth';
                $authColumn = $key === 'employees' ? 'email' : 'manager_email';
                db_run($db,"DELETE FROM `$authTable` WHERE company_id = ? AND `$authColumn` = ?",[$companyId,$identity]);
                $role = $key === 'employees' ? 'employee' : 'manager';
                db_run($db,'DELETE FROM staff_password_resets WHERE company_id = ? AND staff_role = ? AND staff_id = ?',[$companyId,$role,$id]);
                db_run($db,'DELETE FROM staff_tool_access WHERE company_id = ? AND staff_role = ? AND staff_id = ?',[$companyId,$role,$id]);
                db_run($db,'DELETE FROM staff_invitations WHERE company_id = ? AND staff_role = ? AND staff_id = ?',[$companyId,$role,$id]);
                db_run($db,'DELETE FROM notifications WHERE company_id = ? AND recipient_role = ? AND recipient_id = ?',[$companyId,$role,$id]);
                db_run($db,'DELETE FROM notification_preferences WHERE company_id = ? AND recipient_role = ? AND recipient_id = ?',[$companyId,$role,$id]);
            }
            audit_event($db,$companyId,'record.permanently_deleted',$key,(string)$id,['record_title'=>record_title($module,$record)]);
            db_run($db,"DELETE FROM `$table` WHERE `$pk` = ? AND company_id = ?",[$id,$companyId]);
        } else {
            $archivedAt = $action === 'archive' ? 'UTC_TIMESTAMP()' : 'NULL';
            $archivedBy = $action === 'archive' ? '?' : 'NULL';
            $params = $action === 'archive' ? [archive_actor_id($viewer),$id,$companyId] : [$id,$companyId];
            db_run($db,"UPDATE `$table` SET archived_at = $archivedAt, archived_by = $archivedBy WHERE `$pk` = ? AND company_id = ?", $params);
            audit_event($db,$companyId,$action === 'archive' ? 'record.archived' : 'record.restored',$key,(string)$id,['record_title'=>record_title($module,$record)]);
        }
        $db->commit();
    } catch (Throwable $error) { $db->rollback(); throw $error; }
}
