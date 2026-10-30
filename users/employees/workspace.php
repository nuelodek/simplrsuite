<?php
require_once __DIR__ . '/../../app/staff.php';
require_once __DIR__ . '/../../app/view.php';
require_once __DIR__ . '/../../app/modules.php';
require_once __DIR__ . '/../../app/discipline.php';
require_once __DIR__ . '/../../app/task-work.php';
$db = database(); $user = staff_user($db); $manager = $_SESSION['auth_role'] === 'manager';
$name = $manager ? $user['manager_name'] : trim($user['first_name'] . ' ' . $user['last_name']);
if ($manager) {
    $projects = db_rows($db,'SELECT id, projectName, status, endDate FROM projects WHERE company_id = ? AND archived_at IS NULL AND (projectManager = ? OR project_manager_id = ?) ORDER BY updated_at DESC LIMIT 50',[$user['company_id'],$user['id'],$user['unique_id']]);
    $tasks = db_rows($db,'SELECT t.id,t.title,t.status,t.due_date,t.estimated_hours,t.priority FROM tasks t JOIN projects p ON ' . task_project_join() . ' WHERE t.company_id = ? AND t.archived_at IS NULL AND (p.projectManager = ? OR p.project_manager_id = ?) ORDER BY t.due_date LIMIT 100',[$user['company_id'],$user['id'],$user['unique_id']]);
} else {
    $projects = db_rows($db,'SELECT DISTINCT p.id,p.projectName,p.status,p.endDate FROM projects p JOIN tasks t ON ' . task_project_join() . ' WHERE t.company_id = ? AND t.archived_at IS NULL AND (t.assignee = ? OR t.assignee = ?) ORDER BY p.endDate LIMIT 50',[$user['company_id'],(string)$user['id'],$user['unique_id']]);
    $tasks = db_rows($db,'SELECT id,title,status,due_date,estimated_hours,priority FROM tasks WHERE company_id = ? AND archived_at IS NULL AND (assignee = ? OR assignee = ?) ORDER BY due_date LIMIT 100',[$user['company_id'],(string)$user['id'],$user['unique_id']]);
}
$employeeToday = date('Y-m-d');
if (!$manager) task_work_reconcile_attendance($db,(int)$user['company_id'],(int)$user['id'],$employeeToday);
$taskSummaries = !$manager ? task_work_summaries($db,(int)$user['company_id'],(int)$user['id'],array_column($tasks,'id')) : [];
$activeTask = !$manager ? db_one($db,'SELECT task_id FROM task_work_sessions WHERE company_id = ? AND employee_id = ? AND ended_at IS NULL ORDER BY id DESC LIMIT 1',[$user['company_id'],$user['id']]) : null;
$activeTaskId = (int)($activeTask['task_id'] ?? 0);
foreach ($tasks as &$task) {
    $summary = $taskSummaries[(int)$task['id']] ?? ['worked_seconds'=>0,'work_days'=>0,'active'=>false];
    $task['work_summary'] = $summary;
    $task['forecast'] = task_work_forecast(
        isset($task['estimated_hours']) ? (float)$task['estimated_hours'] : null,
        $summary,
        (string)$task['status']
    );
}
unset($task);
$open = array_filter($tasks,fn($task)=>$task['status'] !== 'completed');
$overdue = array_filter($open,fn($task)=>$task['due_date'] && $task['due_date'] < date('Y-m-d'));
$department = db_one($db,'SELECT department_name FROM departments WHERE id = ? AND company_id = ?',[$user['department_id'],$user['company_id']]);
$viewer = viewer_identity($user);
$myQueries = db_rows($db,'SELECT id,reference,title,status,created_at FROM task_queries WHERE company_id = ? AND subject_role = ? AND subject_id = ? ORDER BY id DESC LIMIT 5',[$user['company_id'],$viewer['role'],$viewer['id']]);
$activePenalties = db_rows($db,"SELECT p.id,p.query_id,p.penalty_type,p.status,p.expires_on,q.reference FROM penalties p JOIN task_queries q ON q.id = p.query_id AND q.company_id = p.company_id WHERE p.company_id = ? AND p.subject_role = ? AND p.subject_id = ? AND p.status IN ('proposed','active') ORDER BY p.id DESC LIMIT 5",[$user['company_id'],$viewer['role'],$viewer['id']]);
suite_start('My workspace','overview',$user);
?>
<div class="page-heading"><div><h1>My workspace</h1><p><?= e($user['company_name']) ?> · <?= count($open) ?> open <?= count($open) === 1 ? 'task' : 'tasks' ?><?= $overdue ? ' · <strong class="due overdue">' . count($overdue) . ' overdue</strong>' : '' ?></p></div></div>
<div class="dashboard-columns"><div><section class="panel" id="tasks">
<div class="panel-head"><h2><?= $manager ? 'Tasks in your projects' : 'Your assigned tasks' ?></h2><span class="badge"><?= count($tasks) ?> shown</span></div>
<?php if (!$tasks): ?><div class="empty-state"><?= icon('task') ?><h3>No assigned tasks</h3><p><?= $manager ? 'Tasks will appear here when they are added to projects you manage.' : 'Tasks will appear here when your workspace administrator assigns work to you.' ?></p></div>
<?php else: ?>
<p class="panel-body subtle">Start a task timer while clocked in. Breaks and clock-out pause it. Forecasts are estimates based on recorded weekday work pace and never change the original due date.</p>
<div class="table-wrap"><table class="data-table"><thead><tr><th>Task</th><th>Due date</th><th>Effort</th><th>Time logged</th><th>Forecast</th><th>Priority</th><th>Status &amp; work</th></tr></thead><tbody>
<?php foreach ($tasks as $task):
    $taskId = (int)$task['id'];
    $summary = $task['work_summary'];
    $forecast = $task['forecast'];
    $isActive = !$manager && $activeTaskId === $taskId;
    $otherTaskActive = !$manager && $activeTaskId > 0 && !$isActive;
?>
<tr>
  <td><?= e($task['title']) ?></td>
  <td><?= record_cell_html(['fields'=>['due_date'=>['type'=>'date']]],'due_date',$task,'','status') ?></td>
  <td><?= isset($task['estimated_hours']) ? e(number_format((float)$task['estimated_hours'],2)) . ' h' : '<span class="subtle">Not set</span>' ?></td>
  <td><span data-task-work-seconds="<?= $taskId ?>" data-seconds="<?= (int)$summary['worked_seconds'] ?>" data-running="<?= !empty($summary['active']) ? 'true' : 'false' ?>"><?= e(task_work_duration((int)$summary['worked_seconds'])) ?></span></td>
  <td>
    <?php if ($task['status'] === 'completed'): ?><span class="subtle">Completed</span>
    <?php elseif ($forecast['remaining_seconds'] === 0): ?><span class="badge warning">Estimate reached</span>
    <?php elseif ($forecast['projected_date']): ?>
      <span class="<?= $task['due_date'] && $forecast['projected_date'] > $task['due_date'] ? 'due overdue' : '' ?>">Projected <?= e(date('j M Y',strtotime($forecast['projected_date']))) ?></span>
      <?php if ($task['due_date'] && $forecast['projected_date'] > $task['due_date']): ?><small class="badge overdue">At risk</small><?php endif; ?>
    <?php elseif (!empty($task['estimated_hours'])): ?><span class="subtle"><?= (int)$summary['worked_seconds'] > 0 ? 'More work data needed' : 'Start a timer for a forecast' ?></span>
    <?php else: ?><span class="subtle">Add an effort estimate</span><?php endif; ?>
  </td>
  <td><span class="badge <?= e(stage_class($task['priority'])) ?>"><?= e($task['priority']) ?></span></td>
  <td>
    <?php if (!$manager): ?>
      <div style="display:grid;gap:.45rem">
        <form method="post" action="/users/employees/task-status.php" style="display:flex;gap:8px" data-submit><?= csrf_field() ?><input type="hidden" name="task_id" value="<?= $taskId ?>"><label class="sr-only" for="status-<?= $taskId ?>">Status for <?= e($task['title']) ?></label><select class="inline-select" name="status" id="status-<?= $taskId ?>"><?php foreach (['pending','ongoing','completed'] as $status): ?><option value="<?= $status ?>" <?= $task['status'] === $status ? 'selected' : '' ?>><?= ucfirst($status) ?></option><?php endforeach; ?></select><button type="submit" class="button small secondary">Save status</button></form>
        <?php if ($task['status'] !== 'completed'): ?><button type="button" class="button small<?= $isActive ? ' secondary' : '' ?>" data-task-timer data-task-id="<?= $taskId ?>" data-active="<?= $isActive ? 'true' : 'false' ?>"<?= $otherTaskActive ? ' disabled' : '' ?>><?= $isActive ? 'Pause timer' : ($otherTaskActive ? 'Pause other task first' : 'Start work') ?></button><small role="status" data-task-timer-message="<?= $taskId ?>"></small><?php endif; ?>
      </div>
    <?php else: ?><span class="badge <?= e(stage_class($task['status'])) ?>"><?= e($task['status']) ?></span><?php endif; ?>
  </td>
</tr>
<?php endforeach; ?></tbody></table></div><?php endif; ?></section>
<section class="panel"><div class="panel-head"><h2><?= $manager ? 'Projects you manage' : 'Projects you’re part of' ?></h2></div><?php if (!$projects): ?><div class="empty-state"><?= icon('folder') ?><h3>No projects assigned</h3><p>Your assigned projects will appear here.</p></div><?php else: ?><ul class="record-list"><?php foreach ($projects as $project): ?><li><span class="record-icon"><?= icon('folder') ?></span><div><strong><?= e($project['projectName']) ?></strong><small><?= $project['endDate'] ? 'Due ' . e(date('j M Y',strtotime($project['endDate']))) : 'No due date set' ?></small></div><span class="badge <?= e(stage_class($project['status'])) ?>"><?= e($project['status'] ?: 'Planned') ?></span></li><?php endforeach; ?></ul><?php endif; ?></section></div>
<aside><section class="panel" id="profile"><div class="panel-head"><h2>Your profile</h2></div><div class="panel-body"><span class="avatar" style="margin-bottom:18px"><?= e(mb_strtoupper(mb_substr($name,0,1))) ?></span><h3><?= e($name) ?></h3><p class="subtle"><?= e($user['email']) ?></p><p><strong>Department</strong><br><?= e($department['department_name'] ?? 'Not assigned') ?></p><p><strong>Workspace code</strong><br><?= e($user['company_id']) ?></p><small class="subtle">Contact your workspace administrator to update your profile or access.</small></div></section>
<section class="panel"><div class="panel-head"><h2>Queries &amp; penalties</h2><a class="text-link" href="/users/admin/queries.php">View all</a></div><?php if ($myQueries || $activePenalties): ?><ul class="query-list"><?php foreach ($myQueries as $myQuery): ?><li><span class="query-ref"><?= e($myQuery['reference']) ?></span><div class="query-main"><a href="<?= e(query_url((int)$myQuery['id'])) ?>"><?= e($myQuery['title']) ?></a><small><?= e(query_statuses()[$myQuery['status']] ?? $myQuery['status']) ?></small></div></li><?php endforeach; ?><?php foreach ($activePenalties as $penalty): ?><li><span class="query-ref">Penalty</span><div class="query-main"><a href="<?= e(query_url((int)$penalty['query_id'])) ?>"><?= e(penalty_types()[$penalty['penalty_type']] ?? $penalty['penalty_type']) ?> · <?= e($penalty['reference']) ?></a><small><?= e(penalty_statuses()[$penalty['status']] ?? $penalty['status']) ?><?= $penalty['expires_on'] ? ' · expires ' . e(date('j M Y',strtotime($penalty['expires_on']))) : '' ?></small></div></li><?php endforeach; ?></ul><?php else: ?><div class="panel-body"><p class="subtle">You have no formal queries or active penalties.</p></div><?php endif; ?></section></aside></div>
<?php if (!$manager): ?>
<script>
(function () {
  const csrfToken = <?= json_encode(csrf_token(), JSON_THROW_ON_ERROR) ?>;
  const buttons = Array.from(document.querySelectorAll('[data-task-timer]'));
  function formatDuration(seconds) {
    const minutes = Math.floor(Math.max(0, seconds) / 60);
    return Math.floor(minutes / 60) + 'h ' + String(minutes % 60).padStart(2, '0') + 'm';
  }
  function renderTimers() {
    document.querySelectorAll('[data-task-work-seconds][data-running="true"]').forEach((timer) => {
      const seconds = Number(timer.dataset.seconds || 0) + 1;
      timer.dataset.seconds = String(seconds);
      timer.textContent = formatDuration(seconds);
    });
  }
  function setActiveTask(taskId, active) {
    buttons.forEach((button) => {
      const isActive = active && Number(button.dataset.taskId) === taskId;
      button.dataset.active = isActive ? 'true' : 'false';
      button.disabled = active && !isActive;
      button.textContent = isActive ? 'Pause timer' : (button.disabled ? 'Pause current timer first' : 'Start work');
      const timer = document.querySelector('[data-task-work-seconds="' + button.dataset.taskId + '"]');
      if (timer) timer.dataset.running = isActive ? 'true' : 'false';
    });
  }
  buttons.forEach((button) => {
    button.addEventListener('click', async function () {
      button.disabled = true;
      const message = document.querySelector('[data-task-timer-message="' + button.dataset.taskId + '"]');
      message.textContent = 'Saving…';
      const form = new URLSearchParams();
      form.append('task_id', button.dataset.taskId);
      form.append('action', button.dataset.active === 'true' ? 'pause' : 'start');
      try {
        const response = await fetch('/users/employees/task-work.php', {
          method: 'POST',
          headers: {
            'Accept': 'application/json',
            'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8',
            'X-CSRF-Token': csrfToken
          },
          body: form.toString()
        });
        const payload = await response.json();
        if (!response.ok || !payload.ok) throw new Error(payload.error || 'Could not update task timer.');
        const timer = document.querySelector('[data-task-work-seconds="' + payload.task_id + '"]');
        if (timer) {
          timer.dataset.seconds = String(payload.worked_seconds || 0);
          timer.textContent = formatDuration(Number(payload.worked_seconds || 0));
        }
        setActiveTask(Number(payload.task_id), !!payload.active);
        const taskRow = button.closest('tr');
        const statusSelect = taskRow ? taskRow.querySelector('select[name="status"]') : null;
        if (statusSelect && payload.status) statusSelect.value = payload.status;
        message.textContent = payload.active ? 'Task timer running.' : 'Task timer paused.';
      } catch (error) {
        message.textContent = error.message || 'Could not update task timer.';
        button.disabled = false;
      }
    });
  });
  window.setInterval(renderTimers, 1000);
})();
</script>
<?php endif; ?>
<?php suite_end(); ?>
