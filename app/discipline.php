<?php
declare(strict_types=1);
require_once __DIR__ . '/modules.php';
require_once __DIR__ . '/notifications.php';
require_once __DIR__ . '/settings.php';
// Queries ask a person to explain something (usually a task) within a response
// window. Penalties are recorded decisions that follow a query: a manager may
// propose one, only the workspace owner approves it, and the person can
// acknowledge and appeal once. Nothing here moves money or changes payroll.
function discipline_settings(mysqli $db, int $companyId): array {
    $row = db_one($db,'SELECT query_response_minutes, query_response_hours, warning_expiry_months FROM workspace_settings WHERE company_id = ?',[$companyId]);
    $minutes = (int)($row['query_response_minutes'] ?? 0);
    if ($minutes < 1) $minutes = ((int)($row['query_response_hours'] ?? 72) ?: 72) * 60;
    return ['response_minutes'=>$minutes,'expiry_months'=>(int)($row['warning_expiry_months'] ?? 12) ?: 12];
}
function penalty_types(): array {
    return ['verbal_warning'=>'Verbal warning','written_warning'=>'Written warning','final_warning'=>'Final warning','suspension'=>'Suspension','deduction'=>'Recorded deduction','other'=>'Other action'];
}
function query_statuses(): array {
    return ['awaiting_response'=>'Awaiting response','answered'=>'Answered','no_response'=>'No response','closed'=>'Closed','escalated'=>'Escalated','withdrawn'=>'Withdrawn'];
}
function query_response_overdue(array $query): bool {
    return $query['status'] === 'awaiting_response' && strtotime((string)$query['respond_by'] . ' UTC') <= time();
}
function penalty_statuses(): array {
    return ['proposed'=>'Awaiting approval','active'=>'Active','rejected'=>'Not approved','withdrawn'=>'Withdrawn','expired'=>'Expired'];
}
function person_name(mysqli $db, int $companyId, string $role, int $id): string {
    $contact = recipient_contact($db,$companyId,$role,$id);
    return trim((string)($contact['name'] ?? '')) ?: ucfirst($role) . ' #' . $id;
}
function direct_manager_id(mysqli $db, int $companyId, int $employeeId): int {
    return (int)(db_one($db,'SELECT manager_id FROM employees WHERE id = ? AND company_id = ? AND archived_at IS NULL',[$employeeId,$companyId])['manager_id'] ?? 0);
}
// People the viewer may query: the owner may query anyone; a manager only their direct reports.
function query_candidates(mysqli $db, array $viewer): array {
    $company = $viewer['company_id'];
    $people = [];
    if ($viewer['role'] === 'admin') {
        foreach (db_rows($db,"SELECT id, TRIM(CONCAT(first_name, ' ', last_name)) AS name, job_title FROM employees WHERE company_id = ? AND archived_at IS NULL ORDER BY first_name, last_name",[$company]) as $row) $people['employee:' . $row['id']] = $row['name'] . ($row['job_title'] ? ' · ' . $row['job_title'] : '');
        foreach (db_rows($db,'SELECT id, manager_name AS name FROM managers WHERE company_id = ? AND archived_at IS NULL ORDER BY manager_name',[$company]) as $row) $people['manager:' . $row['id']] = $row['name'] . ' · Manager';
    } elseif ($viewer['role'] === 'manager') {
        foreach (db_rows($db,"SELECT id, TRIM(CONCAT(first_name, ' ', last_name)) AS name, job_title FROM employees WHERE company_id = ? AND manager_id = ? AND archived_at IS NULL ORDER BY first_name, last_name",[$company,$viewer['id']]) as $row) $people['employee:' . $row['id']] = $row['name'] . ($row['job_title'] ? ' · ' . $row['job_title'] : '');
    }
    return $people;
}
function can_manage_project_queries(mysqli $db, array $viewer, int $projectId): bool {
    $project = db_one($db,'SELECT projectManager, project_manager_id FROM projects WHERE id = ? AND company_id = ? AND archived_at IS NULL',[$projectId,$viewer['company_id']]);
    if (!$project) return false;
    if ($viewer['role'] === 'admin') return true;
    if ($viewer['role'] !== 'manager') return false;
    $managerId = (string)$viewer['id'];
    $managerUniqueId = (string)($_SESSION['unique_id'] ?? '');
    return (string)$project['projectManager'] === $managerId
        || ($managerUniqueId !== '' && (string)$project['projectManager'] === $managerUniqueId)
        || (string)$project['project_manager_id'] === $managerId
        || ($managerUniqueId !== '' && (string)$project['project_manager_id'] === $managerUniqueId);
}
function project_query_candidates(mysqli $db, array $viewer, int $projectId): array {
    if ($viewer['role'] === 'employee') fail('Only the workspace owner or a manager can issue a query.',403);
    $project = db_one($db,'SELECT id, unique_id, projectManager, project_manager_id FROM projects WHERE id = ? AND company_id = ? AND archived_at IS NULL',[$projectId,$viewer['company_id']]);
    if (!$project) fail('Choose a project from your workspace.',422);
    if (!can_manage_project_queries($db,$viewer,$projectId)) fail('You can query only people on projects you manage.',403);
    $people = [];
    $managerIdentifier = trim((string)$project['projectManager']);
    if ($managerIdentifier !== '') {
        $manager = db_one($db,'SELECT id, manager_name FROM managers WHERE company_id = ? AND archived_at IS NULL AND (id = ? OR unique_id = ?) LIMIT 1',[$viewer['company_id'],ctype_digit($managerIdentifier) ? (int)$managerIdentifier : 0,$managerIdentifier]);
        if ($manager && can_issue_query($db,$viewer,'manager',(int)$manager['id'])) $people['manager:' . $manager['id']] = $manager['manager_name'] . ' · Project manager';
    }
    if ($project['project_manager_id']) {
        $managerId = (string)$project['project_manager_id'];
        $manager = db_one($db,'SELECT id, manager_name FROM managers WHERE company_id = ? AND archived_at IS NULL AND (id = ? OR unique_id = ?) LIMIT 1',[$viewer['company_id'],ctype_digit($managerId) ? (int)$managerId : 0,$managerId]);
        if ($manager && can_issue_query($db,$viewer,'manager',(int)$manager['id'])) $people['manager:' . $manager['id']] = $manager['manager_name'] . ' · Project manager';
    }
    $employeesByIdentifier = [];
    foreach (db_rows($db,'SELECT id, unique_id, TRIM(CONCAT(first_name, \' \', last_name)) AS name, job_title FROM employees WHERE company_id = ? AND archived_at IS NULL',[$viewer['company_id']]) as $employee) {
        $person = ['id'=>(int)$employee['id'],'name'=>$employee['name'] . ($employee['job_title'] ? ' · ' . $employee['job_title'] : '')];
        $employeesByIdentifier[(string)$employee['id']] = $person;
        if ($employee['unique_id'] !== null && $employee['unique_id'] !== '') $employeesByIdentifier[(string)$employee['unique_id']] = $person;
    }
    $addEmployee = static function (string $identifier) use (&$people,$employeesByIdentifier,$db,$viewer): void {
        $employee = $employeesByIdentifier[$identifier] ?? null;
        if ($employee && can_issue_query($db,$viewer,'employee',$employee['id'])) $people['employee:' . $employee['id']] = $employee['name'];
    };
    foreach (db_rows($db,'SELECT team_members, team_leader_id FROM teams WHERE company_id = ? AND (project_id = ? OR project_id = ?)',[$viewer['company_id'],(string)$projectId,(string)$project['unique_id']]) as $team) {
        if (!empty($team['team_leader_id'])) $addEmployee((string)$team['team_leader_id']);
        $members = json_decode((string)($team['team_members'] ?? ''),true);
        if (is_array($members)) foreach ($members as $memberId) if (is_scalar($memberId)) $addEmployee((string)$memberId);
    }
    foreach (db_rows($db,'SELECT DISTINCT t.assignee FROM tasks t JOIN projects p ON ' . task_project_join() . ' WHERE t.company_id = ? AND p.id = ?',[$viewer['company_id'],$projectId]) as $task) {
        if ($task['assignee'] !== null && $task['assignee'] !== '') $addEmployee((string)$task['assignee']);
    }
    return $people;
}
function project_facts(mysqli $db, int $companyId, int $projectId): array {
    $project = db_one($db,'SELECT id, projectName, projectDescription, status, startDate, endDate FROM projects WHERE id = ? AND company_id = ?',[$projectId,$companyId]);
    if (!$project) fail('Choose a project from your workspace.',422);
    $lines = ['Project: ' . $project['projectName'],'Status: ' . ($project['status'] ?: 'Not set'),'Start date: ' . ($project['startDate'] ?: 'Not set'),'End date: ' . ($project['endDate'] ?: 'Not set')];
    if (trim((string)$project['projectDescription']) !== '') $lines[] = 'Description: ' . $project['projectDescription'];
    return [$project,'<ul>' . implode('',array_map(fn($line)=>'<li>' . e($line) . '</li>',$lines)) . '</ul>'];
}
function can_issue_query(mysqli $db, array $viewer, string $role, int $id): bool {
    if ($viewer['role'] === 'admin') {
        if (!in_array($role,['employee','manager'],true)) return false;
        $table = $role === 'employee' ? 'employees' : 'managers';
        return (bool)db_one($db,"SELECT id FROM `$table` WHERE id = ? AND company_id = ? AND archived_at IS NULL",[$id,$viewer['company_id']]);
    }
    return $viewer['role'] === 'manager' && $role === 'employee' && direct_manager_id($db,$viewer['company_id'],$id) === $viewer['id'];
}
function is_subject(array $viewer, array $record): bool { return $viewer['role'] === $record['subject_role'] && $viewer['id'] === (int)$record['subject_id']; }
function is_issuer(array $viewer, array $query): bool { return $viewer['role'] === $query['issued_by_role'] && $viewer['id'] === (int)$query['issued_by_id']; }
function is_subjects_manager(mysqli $db, array $viewer, array $record): bool {
    return $viewer['role'] === 'manager' && $record['subject_role'] === 'employee' && direct_manager_id($db,$viewer['company_id'],(int)$record['subject_id']) === $viewer['id'];
}
function can_view_discipline(mysqli $db, array $viewer, array $record, ?array $query = null): bool {
    return $viewer['role'] === 'admin' || is_subject($viewer,$record) || ($query && is_issuer($viewer,$query)) || (isset($record['issued_by_role']) && is_issuer($viewer,$record)) || is_subjects_manager($db,$viewer,$record) || has_penalty_handoff_access($db,$viewer,$record);
}
// SQL filter for lists, mirroring can_view_discipline().
function discipline_scope(array $viewer, string $alias): array {
    if ($viewer['role'] === 'admin') return ["$alias.company_id = ?",[$viewer['company_id']]];
    $handoff = $alias === 'q'
        ? " OR EXISTS (SELECT 1 FROM penalties hp JOIN penalty_handoffs hh ON hh.penalty_id = hp.id AND hh.company_id = hp.company_id WHERE hp.company_id = q.company_id AND hp.query_id = q.id AND hh.assignee_role = ? AND hh.assignee_id = ?)"
        : " OR EXISTS (SELECT 1 FROM penalty_handoffs hh WHERE hh.company_id = p.company_id AND hh.penalty_id = p.id AND hh.assignee_role = ? AND hh.assignee_id = ?)";
    if ($viewer['role'] === 'manager') {
        $scope = "($alias.subject_role = 'manager' AND $alias.subject_id = ?) OR ($alias.subject_role = 'employee' AND $alias.subject_id IN (SELECT id FROM employees WHERE company_id = ? AND manager_id = ?))";
        $params = [$viewer['id'],$viewer['company_id'],$viewer['id']];
        if ($alias === 'q') { $scope .= " OR ($alias.issued_by_role = 'manager' AND $alias.issued_by_id = ?)"; $params[] = $viewer['id']; }
        return ["$alias.company_id = ? AND ($scope$handoff)",[$viewer['company_id'],...$params,$viewer['role'],$viewer['id']]];
    }
    return ["$alias.company_id = ? AND (($alias.subject_role = ? AND $alias.subject_id = ?)$handoff)",[$viewer['company_id'],$viewer['role'],$viewer['id'],$viewer['role'],$viewer['id']]];
}
function has_penalty_handoff_access(mysqli $db, array $viewer, array $record): bool {
    $penaltyId = isset($record['penalty_type']) ? (int)($record['id'] ?? 0) : 0;
    if ($penaltyId) return (bool)db_one($db,'SELECT id FROM penalty_handoffs WHERE company_id = ? AND penalty_id = ? AND assignee_role = ? AND assignee_id = ? LIMIT 1',[$viewer['company_id'],$penaltyId,$viewer['role'],$viewer['id']]);
    $queryId = (int)($record['id'] ?? 0);
    return (bool)db_one($db,'SELECT h.id FROM penalty_handoffs h JOIN penalties p ON p.id = h.penalty_id AND p.company_id = h.company_id WHERE h.company_id = ? AND p.query_id = ? AND h.assignee_role = ? AND h.assignee_id = ? LIMIT 1',[$viewer['company_id'],$queryId,$viewer['role'],$viewer['id']]);
}
function penalty_handoff_history(mysqli $db, int $companyId, int $penaltyId): array {
    return db_rows($db,'SELECT * FROM penalty_handoffs WHERE company_id = ? AND penalty_id = ? ORDER BY id',[$companyId,$penaltyId]);
}
function penalty_handoff_function_name(string $function): string {
    return ['hr'=>'HR','accounting'=>'Accounting','other'=>'Other'][$function] ?? 'Other';
}
function penalty_handoff_candidates(mysqli $db, int $companyId): array {
    $people = [];
    foreach (db_rows($db,"SELECT e.id, TRIM(CONCAT(e.first_name, ' ', e.last_name)) AS name, e.job_title FROM employees e WHERE e.company_id = ? AND e.archived_at IS NULL AND EXISTS (SELECT 1 FROM employees_auth a WHERE a.company_id = e.company_id AND a.email = e.email AND (a.employee_id = e.unique_id OR a.employee_id IS NULL OR a.employee_id = '')) ORDER BY e.first_name, e.last_name",[$companyId]) as $person) {
        $people['employee:' . $person['id']] = $person['name'] . ($person['job_title'] ? ' · ' . $person['job_title'] : '') . ' · Employee';
    }
    foreach (db_rows($db,"SELECT m.id, m.manager_name AS name FROM managers m WHERE m.company_id = ? AND m.archived_at IS NULL AND m.status NOT IN ('suspended','disabled','inactive') AND EXISTS (SELECT 1 FROM manager_auth a WHERE a.company_id = m.company_id AND a.manager_email = m.manager_email AND (a.manager_id = m.unique_id OR BINARY a.manager_id = CAST(m.id AS BINARY) OR a.manager_id IS NULL OR a.manager_id = '')) ORDER BY m.manager_name",[$companyId]) as $person) {
        $people['manager:' . $person['id']] = $person['name'] . ' · Manager';
    }
    return $people;
}
function penalty_handoff_assignee(array $viewer, array $handoff): bool {
    return $viewer['role'] === $handoff['assignee_role'] && (int)$viewer['id'] === (int)$handoff['assignee_id'];
}
function route_penalty_handoff(mysqli $db, array $viewer, int $penaltyId, string $action, array $input): void {
    if (!in_array($action,['assign','forward','complete'],true)) fail('Choose a valid penalty handoff action.',422);
    $function = (string)($input['function'] ?? '');
    $note = trim((string)($input['note'] ?? ''));
    if (mb_strlen($note) > 1000) fail('Keep the handoff note under 1,000 characters.',422);
    $targetRole = ''; $targetId = 0;
    if ($action !== 'complete') {
        if (!in_array($function,['hr','accounting','other'],true)) fail('Choose HR, Accounting, or Other for this handoff.',422);
        $target = (string)($input['assignee'] ?? '');
        if (!preg_match('/^(employee|manager):(\d+)$/D',$target,$match)) fail('Choose an active staff account for this handoff.',422);
        $targetRole = $match[1]; $targetId = (int)$match[2];
    }
    $completion = trim((string)($input['completion_note'] ?? ''));
    if ($action === 'complete' && $completion === '') fail('Describe how the penalty was handled before marking the handoff complete.',422);
    if (mb_strlen($completion) > 1000) fail('Keep the completion note under 1,000 characters.',422);
    $db->begin_transaction();
    try {
        $penalty = penalty_lock($db,$viewer,$penaltyId);
        if ($penalty['status'] !== 'active') { $db->rollback(); fail('Only an approved penalty can be handed off.',409); }
        $current = db_one($db,"SELECT * FROM penalty_handoffs WHERE company_id = ? AND penalty_id = ? AND status = 'pending' ORDER BY id DESC LIMIT 1 FOR UPDATE",[$viewer['company_id'],$penaltyId]);
        $history = db_one($db,'SELECT COUNT(*) AS count FROM penalty_handoffs WHERE company_id = ? AND penalty_id = ?',[$viewer['company_id'],$penaltyId]);
        if ($action === 'assign') {
            if ($viewer['role'] !== 'admin') { $db->rollback(); fail('Only the workspace owner can start the penalty handoff.',403); }
            if ($current || (int)$history['count'] > 0) { $db->rollback(); fail('This penalty already has a handoff history.',409); }
        } else {
            if (!$current) { $db->rollback(); fail('There is no pending handoff to update.',409); }
            if ($viewer['role'] !== 'admin' && !penalty_handoff_assignee($viewer,$current)) { $db->rollback(); fail('Only the assigned person or workspace owner can update this handoff.',403); }
        }
        if ($action === 'complete') {
            db_run($db,"UPDATE penalty_handoffs SET status = 'completed', completed_by_role = ?, completed_by_id = ?, completed_at = UTC_TIMESTAMP(), completion_note = ? WHERE id = ? AND status = 'pending'",[$viewer['role'],$viewer['id'],$completion,$current['id']]);
            audit_event($db,$viewer['company_id'],'penalty.handoff_completed','penalties',(string)$penaltyId,['reference'=>$penalty['reference'],'function'=>$current['handoff_function']]);
        } else {
            $candidates = penalty_handoff_candidates($db,$viewer['company_id']);
            if (!isset($candidates[$targetRole . ':' . $targetId])) { $db->rollback(); fail('Choose an active staff account in this workspace.',422); }
            if ($targetRole === $penalty['subject_role'] && $targetId === (int)$penalty['subject_id']) { $db->rollback(); fail('The person this penalty applies to cannot be assigned to process it.',422); }
            if (db_one($db,'SELECT id FROM penalty_handoffs WHERE company_id = ? AND penalty_id = ? AND assignee_role = ? AND assignee_id = ? LIMIT 1',[$viewer['company_id'],$penaltyId,$targetRole,$targetId])) { $db->rollback(); fail('Choose someone who has not already handled this penalty.',422); }
            if ($current) db_run($db,"UPDATE penalty_handoffs SET status = 'forwarded' WHERE id = ? AND status = 'pending'",[$current['id']]);
            db_run($db,'INSERT INTO penalty_handoffs (company_id,penalty_id,handoff_function,assignee_role,assignee_id,assigned_by_role,assigned_by_id,note) VALUES (?,?,?,?,?,?,?,?)',[$viewer['company_id'],$penaltyId,$function,$targetRole,$targetId,$viewer['role'],$viewer['id'],$note]);
            audit_event($db,$viewer['company_id'],$action === 'assign' ? 'penalty.handoff_assigned' : 'penalty.handoff_forwarded','penalties',(string)$penaltyId,['reference'=>$penalty['reference'],'function'=>$function,'assignee_role'=>$targetRole,'assignee_id'=>$targetId]);
        }
        $db->commit();
    } catch (Throwable $error) { $db->rollback(); throw $error; }
    if ($action !== 'complete') {
        notify($db,$viewer['company_id'],$targetRole,$targetId,'penalty.handoff_assigned','Penalty action assigned · ' . $penalty['reference'],'A ' . penalty_handoff_function_name($function) . ' handoff needs your attention. No deduction or financial posting is made by Simplr.',query_url((int)$penalty['query_id']));
    } else {
        notify($db,$viewer['company_id'],'admin',company_owner_id($db,$viewer['company_id']),'penalty.handoff_completed','Penalty handoff completed · ' . $penalty['reference'],$completion,query_url((int)$penalty['query_id']));
    }
}
function query_url(int $id): string { return '/users/admin/query.php?id=' . $id; }
function query_lock(mysqli $db, array $viewer, int $id): array {
    $query = db_one($db,'SELECT * FROM task_queries WHERE id = ? AND company_id = ? FOR UPDATE',[$id,$viewer['company_id']]);
    if (!$query) { $db->rollback(); fail('This query could not be found in your workspace.',404); }
    return $query;
}
function rich_required(string $value, string $label, int $max = 10000): string {
    $clean = rich_text_clean($value);
    if ($clean === '') fail('Write the ' . $label . ' before sending.',422);
    if (mb_strlen($clean) > $max) fail('Keep the ' . $label . ' under ' . number_format($max) . ' characters.',422);
    return $clean;
}
function task_facts(mysqli $db, array $viewer, int $taskId): array {
    require_module_access($db,$viewer,'tasks');
    $companyId = (int)$viewer['company_id'];
    $task = db_one($db,'SELECT t.*, p.projectName FROM tasks t LEFT JOIN projects p ON ' . task_project_join() . ' WHERE t.id = ? AND t.company_id = ? AND t.archived_at IS NULL',[$taskId,$companyId]);
    if (!$task) fail('Choose a task from your workspace.',422);
    $assignee = trim((string)($task['assignee'] ?? ''));
    $employeeId = $assignee === '' ? 0 : (int)(db_one($db,'SELECT id FROM employees WHERE company_id = ? AND (id = ? OR unique_id = ?) LIMIT 1',[$companyId,ctype_digit($assignee) ? (int)$assignee : 0,$assignee])['id'] ?? 0);
    if (!$employeeId) fail('Only a task assigned to a workspace employee can be queried.',422);
    if ($viewer['role'] === 'manager' && direct_manager_id($db,$companyId,$employeeId) !== (int)$viewer['id']) {
        fail('You can query only tasks assigned to your direct reports.',403);
    }
    $today = new DateTimeImmutable('today',new DateTimeZone(workspace_settings($db,$companyId)['timezone'] ?: 'UTC'));
    $late = $task['due_date'] && $task['status'] !== 'completed' ? (int)$today->diff(new DateTimeImmutable($task['due_date']))->format('%r%a') : 0;
    $lines = ['Task: ' . $task['title'],'Project: ' . ($task['projectName'] ?: 'None'),'Due date: ' . ($task['due_date'] ? date('j M Y',strtotime($task['due_date'])) : 'Not set'),'Status when queried: ' . ucfirst((string)$task['status']),'Priority: ' . ucfirst((string)$task['priority'])];
    if ($late < 0) $lines[] = 'Overdue by: ' . abs($late) . ' ' . (abs($late) === 1 ? 'day' : 'days');
    return [$task,'<ul>' . implode('',array_map(fn($line)=>'<li>' . e($line) . '</li>',$lines)) . '</ul>',$employeeId];
}
function issue_query(mysqli $db, array $viewer, array $input): int {
    $subjects = $input['subjects'] ?? (isset($input['subject']) ? [$input['subject']] : []);
    if (!is_array($subjects) || !$subjects || count($subjects) > 100) fail('Choose at least one person and no more than 100 people.',422);
    $recipients = [];
    foreach (array_unique($subjects) as $subject) {
        if (!is_string($subject) || !preg_match('/^(employee|manager):(\d+)$/D',$subject,$match)) fail('Choose valid people for this query.',422);
        [$role,$subjectId] = [$match[1],(int)$match[2]];
        if (!can_issue_query($db,$viewer,$role,$subjectId)) fail('You can query only people who report to you. The workspace owner can query anyone.',403);
        $recipients[] = [$role,$subjectId];
    }
    $title = trim((string)($input['title'] ?? ''));
    if ($title === '' || mb_strlen($title) > 200) fail('Give the query a short subject of up to 200 characters.',422);
    $message = rich_required((string)($input['message'] ?? ''),'query');
    $rawMinutes = $input['respond_minutes'] ?? '';
    if ($rawMinutes !== '' && filter_var($rawMinutes,FILTER_VALIDATE_INT) === false) fail('Choose a valid response window.',422);
    $minutes = (int)$rawMinutes ?: discipline_settings($db,$viewer['company_id'])['response_minutes'];
    if (!in_array($minutes,[1,5,10,15,30,45,60,120,240,480,1440,4320,10080,20160],true)) fail('Choose a response window between one minute and 14 days.',422);
    $taskId = ctype_digit((string)($input['task_id'] ?? '')) && (int)$input['task_id'] > 0 ? (int)$input['task_id'] : null;
    $projectId = ctype_digit((string)($input['project_id'] ?? '')) && (int)$input['project_id'] > 0 ? (int)$input['project_id'] : null;
    if ($taskId && $projectId) fail('Choose either a task or a project query, not both.',422);
    if ($projectId) {
        $projectCandidates = project_query_candidates($db,$viewer,$projectId);
        foreach ($recipients as [$role,$subjectId]) if (!isset($projectCandidates[$role . ':' . $subjectId])) fail('Choose only people on this project whom you are authorised to query.',403);
    }
    $facts = ''; $project = null;
    if ($taskId) {
        [,$facts,$taskAssigneeId] = task_facts($db,$viewer,$taskId);
        $taskSubject = 'employee:' . $taskAssigneeId;
        if (!isset(query_candidates($db,$viewer)[$taskSubject])) fail('You are not authorised to query this task’s assignee.',403);
        if (count($recipients) !== 1 || $recipients[0] !== ['employee',$taskAssigneeId]) fail('A task query must be addressed to that task’s assigned employee.',403);
    }
    if ($projectId) [$project,$facts] = project_facts($db,$viewer['company_id'],$projectId);
    $queryIds = [];
    $db->begin_transaction();
    try {
        db_one($db,'SELECT id FROM users WHERE company_id = ? ORDER BY id LIMIT 1 FOR UPDATE',[$viewer['company_id']]);
        $number = (int)db_one($db,'SELECT COUNT(*) AS count FROM task_queries WHERE company_id = ?',[$viewer['company_id']])['count'];
        foreach ($recipients as [$role,$subjectId]) {
            $reference = 'Q-' . gmdate('Y') . '-' . str_pad((string)++$number,4,'0',STR_PAD_LEFT);
            $statement = db_run($db,'INSERT INTO task_queries (company_id,reference,task_id,project_id,subject_role,subject_id,issued_by_role,issued_by_id,title,facts,message,respond_by) VALUES (?,?,?,?,?,?,?,?,?,?,?,UTC_TIMESTAMP() + INTERVAL ? MINUTE)',[$viewer['company_id'],$reference,$taskId,$projectId,$role,$subjectId,$viewer['role'],$viewer['id'],$title,$facts,$message,$minutes]);
            $id = (int)$statement->insert_id;
            $queryIds[] = $id;
            audit_event($db,$viewer['company_id'],'query.issued','task_queries',(string)$id,['reference'=>$reference,'recipients_total'=>count($recipients)]);
        }
        $db->commit();
    } catch (Throwable $error) { $db->rollback(); throw $error; }
    foreach ($queryIds as $queryId) {
        $query = db_one($db,'SELECT * FROM task_queries WHERE id = ?',[$queryId]);
        $due = date('D j M, H:i',strtotime($query['respond_by'] . ' UTC'));
        notify($db,$viewer['company_id'],$query['subject_role'],(int)$query['subject_id'],'query.issued','Query ' . $query['reference'] . ': ' . $title,'Please respond in Simplr by ' . $due . '.',query_url($queryId),true);
        $owner = company_owner_id($db,$viewer['company_id']);
        if ($viewer['role'] !== 'admin') notify($db,$viewer['company_id'],'admin',$owner,'query.issued_by_manager',person_name($db,$viewer['company_id'],$viewer['role'],$viewer['id']) . ' issued query ' . $query['reference'] . ' to ' . person_name($db,$viewer['company_id'],$query['subject_role'],(int)$query['subject_id']),$title,query_url($queryId));
        elseif ($query['subject_role'] === 'employee' && ($manager = direct_manager_id($db,$viewer['company_id'],(int)$query['subject_id']))) notify($db,$viewer['company_id'],'manager',$manager,'query.issued_to_report','Query ' . $query['reference'] . ' issued to ' . person_name($db,$viewer['company_id'],$query['subject_role'],(int)$query['subject_id']),$title,query_url($queryId));
    }
    return $queryIds[0];
}
function query_parties_notify(mysqli $db, array $query, string $kind, string $title, string $body, array $except): void {
    $company = (int)$query['company_id'];
    $targets = [[$query['issued_by_role'],(int)$query['issued_by_id']],['admin',company_owner_id($db,$company)]];
    if ($query['subject_role'] === 'employee' && ($manager = direct_manager_id($db,$company,(int)$query['subject_id']))) $targets[] = ['manager',$manager];
    $seen = [];
    foreach ($targets as [$role,$id]) {
        $key = $role . ':' . $id;
        if (isset($seen[$key]) || in_array($key,$except,true)) continue;
        $seen[$key] = true;
        notify($db,$company,$role,$id,$kind,$title,$body,query_url((int)$query['id']));
    }
}
function respond_to_query(mysqli $db, array $viewer, int $id, string $response): void {
    $clean = rich_required($response,'response');
    $late = false;
    $db->begin_transaction();
    try {
        $query = query_lock($db,$viewer,$id);
        if (!is_subject($viewer,$query)) { $db->rollback(); fail('Only the person who received this query can respond.',403); }
        if (!in_array($query['status'],['awaiting_response','no_response'],true)) { $db->rollback(); fail('This query is no longer open for a response.',409); }
        $late = $query['status'] === 'no_response' || query_response_overdue($query);
        db_run($db,"UPDATE task_queries SET response = ?, responded_at = UTC_TIMESTAMP(), status = 'answered' WHERE id = ? AND company_id = ?",[$clean,$id,$viewer['company_id']]);
        audit_event($db,$viewer['company_id'],'query.responded','task_queries',(string)$id,['reference'=>$query['reference'],'late'=>$late]);
        $db->commit();
    } catch (Throwable $error) { $db->rollback(); throw $error; }
    query_parties_notify($db,$query,'query.answered',person_name($db,$viewer['company_id'],$viewer['role'],$viewer['id']) . ' responded to query ' . $query['reference'] . ($late ? ' (late)' : ''),'Review the response and close or escalate the query.',[$viewer['role'] . ':' . $viewer['id']]);
}
function decide_query(mysqli $db, array $viewer, int $id, string $outcome, string $note): void {
    if (!in_array($outcome,['closed','escalated','withdrawn'],true)) fail('Choose a valid decision.',422);
    $note = trim($note);
    if (mb_strlen($note) > 2000) fail('Keep the decision note under 2,000 characters.',422);
    $db->begin_transaction();
    try {
        $query = query_lock($db,$viewer,$id);
        if ($viewer['role'] !== 'admin' && !is_issuer($viewer,$query)) { $db->rollback(); fail('Only the person who issued this query or the workspace owner can decide it.',403); }
        $effectiveStatus = query_response_overdue($query) ? 'no_response' : $query['status'];
        $allowed = $outcome === 'withdrawn' ? ['awaiting_response','no_response'] : ['answered','no_response'];
        if (!in_array($effectiveStatus,$allowed,true)) { $db->rollback(); fail($outcome === 'withdrawn' ? 'Only an unanswered query can be withdrawn.' : 'Wait for a response, or for the response window to pass, before deciding.',409); }
        if ($outcome === 'escalated' && $note === '') { $db->rollback(); fail('Explain why the query is being escalated.',422); }
        db_run($db,'UPDATE task_queries SET status = ?, decision_note = ?, decided_by_role = ?, decided_by_id = ?, decided_at = UTC_TIMESTAMP() WHERE id = ? AND company_id = ?',[$outcome,$note,$viewer['role'],$viewer['id'],$id,$viewer['company_id']]);
        audit_event($db,$viewer['company_id'],'query.' . $outcome,'task_queries',(string)$id,['reference'=>$query['reference']]);
        $db->commit();
    } catch (Throwable $error) { $db->rollback(); throw $error; }
    $label = ['closed'=>'closed','escalated'=>'escalated for a possible penalty','withdrawn'=>'withdrawn'][$outcome];
    notify($db,$viewer['company_id'],$query['subject_role'],(int)$query['subject_id'],'query.' . $outcome,'Query ' . $query['reference'] . ' was ' . $label,$note,query_url($id),true);
}
function propose_penalty(mysqli $db, array $viewer, int $queryId, array $input): int {
    $type = (string)($input['penalty_type'] ?? '');
    if (!isset(penalty_types()[$type])) fail('Choose a penalty type.',422);
    $reason = rich_required((string)($input['reason'] ?? ''),'reason for the penalty',5000);
    $amount = null; $starts = null; $ends = null;
    if ($type === 'deduction') {
        $raw = str_replace(',','',trim((string)($input['amount'] ?? '')));
        if (!preg_match('/^\d{1,10}(?:\.\d{1,2})?$/D',$raw) || (float)$raw <= 0) fail('Enter the deduction amount to record.',422);
        $amount = $raw;
    }
    if ($type === 'suspension') {
        [$starts,$ends] = [(string)($input['starts_on'] ?? ''),(string)($input['ends_on'] ?? '')];
        foreach ([$starts,$ends] as $date) { $parsed = DateTimeImmutable::createFromFormat('!Y-m-d',$date); if (!$parsed || $parsed->format('Y-m-d') !== $date) fail('Enter the suspension start and end dates.',422); }
        if ($ends < $starts) fail('The suspension cannot end before it starts.',422);
    }
    $db->begin_transaction();
    try {
        $query = query_lock($db,$viewer,$queryId);
        if ($viewer['role'] !== 'admin' && !is_issuer($viewer,$query)) { $db->rollback(); fail('Only the person who issued this query or the workspace owner can propose a penalty.',403); }
        $effectiveStatus = query_response_overdue($query) ? 'no_response' : $query['status'];
        if (!in_array($effectiveStatus,['answered','no_response','escalated'],true)) { $db->rollback(); fail('A penalty can follow only a query that was answered, went unanswered, or was escalated.',409); }
        if (db_one($db,"SELECT id FROM penalties WHERE company_id = ? AND query_id = ? AND status IN ('proposed','active')",[$viewer['company_id'],$queryId])) { $db->rollback(); fail('This query already has a penalty awaiting approval or in force.',409); }
        if ($query['status'] !== 'escalated') {
            db_run($db,"UPDATE task_queries SET status = 'escalated', decided_by_role = ?, decided_by_id = ?, decided_at = UTC_TIMESTAMP() WHERE id = ?",[$viewer['role'],$viewer['id'],$queryId]);
            audit_event($db,$viewer['company_id'],'query.escalated','task_queries',(string)$queryId,['reference'=>$query['reference'],'reason'=>'penalty proposed']);
        }
        $owner = $viewer['role'] === 'admin';
        $statement = db_run($db,'INSERT INTO penalties (company_id,query_id,subject_role,subject_id,penalty_type,reason,amount,currency,starts_on,ends_on,status,proposed_by_role,proposed_by_id) VALUES (?,?,?,?,?,?,?,?,?,?,\'proposed\',?,?)',[$viewer['company_id'],$queryId,$query['subject_role'],$query['subject_id'],$type,$reason,$amount,(string)(workspace_settings($db,$viewer['company_id'])['currency'] ?? ''),$starts,$ends,$viewer['role'],$viewer['id']]);
        $penaltyId = (int)$statement->insert_id;
        audit_event($db,$viewer['company_id'],'penalty.proposed','penalties',(string)$penaltyId,['reference'=>$query['reference'],'type'=>$type]);
        $db->commit();
    } catch (Throwable $error) { $db->rollback(); throw $error; }
    if ($owner) decide_penalty($db,$viewer,$penaltyId,'approve','');
    else notify($db,$viewer['company_id'],'admin',company_owner_id($db,$viewer['company_id']),'penalty.proposed',penalty_types()[$type] . ' proposed for ' . person_name($db,$viewer['company_id'],$query['subject_role'],(int)$query['subject_id']),'Query ' . $query['reference'] . ' · approve or reject it in Simplr.',query_url($queryId),true);
    return $penaltyId;
}
function penalty_lock(mysqli $db, array $viewer, int $id): array {
    $penalty = db_one($db,'SELECT p.*, q.reference FROM penalties p JOIN task_queries q ON q.id = p.query_id AND q.company_id = p.company_id WHERE p.id = ? AND p.company_id = ? FOR UPDATE',[$id,$viewer['company_id']]);
    if (!$penalty) { $db->rollback(); fail('This penalty could not be found in your workspace.',404); }
    return $penalty;
}
function decide_penalty(mysqli $db, array $viewer, int $id, string $decision, string $note): void {
    if ($viewer['role'] !== 'admin') fail('Only the workspace owner can approve penalties.',403);
    if (!in_array($decision,['approve','reject'],true)) fail('Choose approve or reject.',422);
    $settings = discipline_settings($db,$viewer['company_id']);
    $db->begin_transaction();
    try {
        $penalty = penalty_lock($db,$viewer,$id);
        if ($penalty['status'] !== 'proposed') { $db->rollback(); fail('This penalty has already been decided.',409); }
        $warning = in_array($penalty['penalty_type'],['verbal_warning','written_warning','final_warning'],true);
        db_run($db,'UPDATE penalties SET status = ?, decided_by = ?, decided_at = UTC_TIMESTAMP(), decision_note = ?, expires_on = ? WHERE id = ?',[$decision === 'approve' ? 'active' : 'rejected',$viewer['id'],mb_substr(trim($note),0,1000),$decision === 'approve' && $warning ? gmdate('Y-m-d',strtotime('+' . $settings['expiry_months'] . ' months')) : null,$id]);
        audit_event($db,$viewer['company_id'],$decision === 'approve' ? 'penalty.approved' : 'penalty.rejected','penalties',(string)$id,['reference'=>$penalty['reference']]);
        $db->commit();
    } catch (Throwable $error) { $db->rollback(); throw $error; }
    $label = penalty_types()[$penalty['penalty_type']];
    if ($decision === 'approve') notify($db,$viewer['company_id'],$penalty['subject_role'],(int)$penalty['subject_id'],'penalty.recorded',$label . ' recorded after query ' . $penalty['reference'],'Please read and acknowledge it in Simplr. You may appeal once.',query_url((int)$penalty['query_id']),true);
    if ($penalty['proposed_by_role'] !== 'admin') notify($db,$viewer['company_id'],$penalty['proposed_by_role'],(int)$penalty['proposed_by_id'],'penalty.decided','Your proposed ' . strtolower($label) . ' was ' . ($decision === 'approve' ? 'approved' : 'not approved'),trim($note),query_url((int)$penalty['query_id']));
}
function acknowledge_penalty(mysqli $db, array $viewer, int $id): void {
    $db->begin_transaction();
    try {
        $penalty = penalty_lock($db,$viewer,$id);
        if (!is_subject($viewer,$penalty)) { $db->rollback(); fail('Only the person this penalty applies to can acknowledge it.',403); }
        if ($penalty['status'] !== 'active' || $penalty['acknowledged_at']) { $db->rollback(); fail('This penalty does not need acknowledging.',409); }
        db_run($db,'UPDATE penalties SET acknowledged_at = UTC_TIMESTAMP() WHERE id = ?',[$id]);
        audit_event($db,$viewer['company_id'],'penalty.acknowledged','penalties',(string)$id,['reference'=>$penalty['reference']]);
        $db->commit();
    } catch (Throwable $error) { $db->rollback(); throw $error; }
}
function appeal_penalty(mysqli $db, array $viewer, int $id, string $text): void {
    $clean = rich_required($text,'appeal',5000);
    $db->begin_transaction();
    try {
        $penalty = penalty_lock($db,$viewer,$id);
        if (!is_subject($viewer,$penalty)) { $db->rollback(); fail('Only the person this penalty applies to can appeal.',403); }
        if ($penalty['status'] !== 'active' || $penalty['appealed_at']) { $db->rollback(); fail('This penalty can no longer be appealed. Each penalty can be appealed once.',409); }
        db_run($db,'UPDATE penalties SET appeal_text = ?, appealed_at = UTC_TIMESTAMP(), acknowledged_at = COALESCE(acknowledged_at, UTC_TIMESTAMP()) WHERE id = ?',[$clean,$id]);
        audit_event($db,$viewer['company_id'],'penalty.appealed','penalties',(string)$id,['reference'=>$penalty['reference']]);
        $db->commit();
    } catch (Throwable $error) { $db->rollback(); throw $error; }
    notify($db,$viewer['company_id'],'admin',company_owner_id($db,$viewer['company_id']),'penalty.appealed',person_name($db,$viewer['company_id'],$viewer['role'],$viewer['id']) . ' appealed a ' . strtolower(penalty_types()[$penalty['penalty_type']]),'Query ' . $penalty['reference'] . ' · review and decide the appeal.',query_url((int)$penalty['query_id']),true);
}
function decide_appeal(mysqli $db, array $viewer, int $id, string $outcome, string $note, string $newType = '', string $newAmount = ''): void {
    if ($viewer['role'] !== 'admin') fail('Only the workspace owner can decide appeals.',403);
    if (!in_array($outcome,['upheld','reduced','withdrawn'],true)) fail('Choose uphold, reduce or withdraw.',422);
    if (trim($note) === '') fail('Explain the appeal decision.',422);
    $db->begin_transaction();
    try {
        $penalty = penalty_lock($db,$viewer,$id);
        if ($penalty['status'] !== 'active' || !$penalty['appealed_at'] || $penalty['appeal_outcome']) { $db->rollback(); fail('This appeal has already been decided.',409); }
        $type = $penalty['penalty_type']; $amount = $penalty['amount'];
        if ($outcome === 'reduced') {
            if (!isset(penalty_types()[$newType])) { $db->rollback(); fail('Choose the reduced penalty.',422); }
            $type = $newType;
            $amount = $newType === 'deduction' ? (preg_match('/^\d{1,10}(?:\.\d{1,2})?$/D',str_replace(',','',$newAmount)) && (float)str_replace(',','',$newAmount) > 0 ? str_replace(',','',$newAmount) : null) : null;
            if ($newType === 'deduction' && ($amount === null || (float)$amount >= (float)$penalty['amount'] && $penalty['penalty_type'] === 'deduction')) { $db->rollback(); fail('Enter a smaller deduction amount for a reduced penalty.',422); }
        }
        db_run($db,'UPDATE penalties SET appeal_outcome = ?, appeal_note = ?, appeal_decided_at = UTC_TIMESTAMP(), status = ?, penalty_type = ?, amount = ? WHERE id = ?',[$outcome,mb_substr(trim($note),0,1000),$outcome === 'withdrawn' ? 'withdrawn' : 'active',$type,$amount,$id]);
        audit_event($db,$viewer['company_id'],'penalty.appeal_decided','penalties',(string)$id,['reference'=>$penalty['reference'],'outcome'=>$outcome]);
        $db->commit();
    } catch (Throwable $error) { $db->rollback(); throw $error; }
    notify($db,$viewer['company_id'],$penalty['subject_role'],(int)$penalty['subject_id'],'penalty.appeal_decided','Your appeal was ' . ['upheld'=>'not successful; the penalty stands','reduced'=>'partly successful; the penalty was reduced','withdrawn'=>'successful; the penalty was withdrawn'][$outcome],trim($note),query_url((int)$penalty['query_id']),true);
}
function discipline_due_label(string $respondBy): array {
    $seconds = strtotime($respondBy . ' UTC') - time();
    if ($seconds <= 0) return ['Response window closed','overdue'];
    $hours = (int)ceil($seconds / 3600);
    if ($seconds < 3600) {
        $minutes = max(1,(int)ceil($seconds / 60));
        return ['Respond within ' . $minutes . ' ' . ($minutes === 1 ? 'minute' : 'minutes'),$minutes <= 10 ? 'soon' : ''];
    }
    return [$hours >= 48 ? 'Respond within ' . (int)floor($hours / 24) . ' days' : 'Respond within ' . $hours . ' ' . ($hours === 1 ? 'hour' : 'hours'),$hours <= 24 ? 'soon' : ''];
}
