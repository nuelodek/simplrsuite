<?php
require_once __DIR__ . '/../../app/modules.php';
require_once __DIR__ . '/../../app/settings.php';
require_once __DIR__ . '/../../app/view.php';
require_once __DIR__ . '/../../app/reporting.php';

$db = database();
$user = workspace_user($db);
$companyId = (int)$user['company_id'];
$available = visible_modules($db, $user);
$settings = workspace_settings($db, $companyId);

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    require_post();
    $title = trim((string)($_POST['title'] ?? ''));
    $scope = in_array($_POST['scope'] ?? '', ['project', 'task'], true) ? $_POST['scope'] : 'project';
    $projectId = filter_var($_POST['project_id'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]) ?: null;
    $taskId = filter_var($_POST['task_id'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]) ?: null;
    $frequency = in_array($_POST['frequency'] ?? '', ['daily', 'weekly', 'biweekly', 'monthly'], true) ? $_POST['frequency'] : 'weekly';
    $assignee = trim((string)($_POST['assignee'] ?? ''));
    $assigneeEmail = trim((string)($_POST['assignee_email'] ?? ''));
    $reportText = trim((string)($_POST['report_text'] ?? ''));
    $summary = trim((string)($_POST['summary'] ?? ''));
    $nextDueAt = trim((string)($_POST['next_due_at'] ?? ''));

    if ($scope === 'task' && !$taskId) fail('Choose the task this report belongs to.', 422);
    if ($scope === 'project' && !$projectId) fail('Choose the project this report belongs to.', 422);
    if ($scope === 'task' && !db_one($db,'SELECT id FROM tasks WHERE company_id = ? AND id = ? AND archived_at IS NULL',[$companyId,$taskId])) fail('Choose an active task in your workspace.',422);
    if ($scope === 'project' && !db_one($db,'SELECT id FROM projects WHERE company_id = ? AND id = ? AND archived_at IS NULL',[$companyId,$projectId])) fail('Choose an active project in your workspace.',422);
    if ($title === '') fail('Give this report a clear title.', 422);
    if ($summary === '') fail('Write a short summary for the report.', 422);
    if ($reportText === '') fail('Add your report narrative or update details.', 422);
    if ($nextDueAt === '') $nextDueAt = date('Y-m-d\TH:i');
    $appointmentDate = report_due_datetime($nextDueAt);

    $insert = db_run($db, 'INSERT INTO project_reports (company_id, project_id, task_id, report_scope, title, summary, report_text, frequency, status, assignee, assignee_email, created_by, next_due_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)', [
        $companyId,
        $scope === 'project' ? $projectId : null,
        $scope === 'task' ? $taskId : null,
        $scope,
        $title,
        $summary,
        $reportText,
        $frequency,
        'submitted',
        $assignee !== '' ? $assignee : ($scope === 'task' ? (string)($user['id'] ?? '') : ''),
        $assigneeEmail !== '' ? $assigneeEmail : '',
        (int)($user['id'] ?? 0),
        $appointmentDate,
    ]);
    $reportId = (int)$db->insert_id;

    if (isset($_FILES['report_file']) && is_array($_FILES['report_file']) && ($_FILES['report_file']['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE) {
        $upload = report_upload_file($_FILES['report_file'], $companyId, $reportId);
        if (isset($upload['error'])) {
            db_run($db, 'DELETE FROM project_reports WHERE id = ?', [$reportId]);
            fail($upload['error'], 422);
        }
        $contentHash = hash_file('sha256', $upload['path']);
        db_run($db, 'INSERT INTO record_attachments (company_id, module_key, record_id, original_name, storage_name, media_type, size_bytes, content_hash) VALUES (?, ?, ?, ?, ?, ?, ?, ?)', [
            $companyId,
            'project_reports',
            $reportId,
            $upload['original_name'],
            $upload['storage_name'],
            $upload['media_type'],
            $upload['size_bytes'],
            $contentHash,
        ]);
    }

    flash('Your report was saved and scheduled for follow-up.', 'success');
    redirect('/users/admin/reports.php#report-library');
}

if (isset($_GET['export'])) {
    $key = is_string($_GET['export']) ? $_GET['export'] : ''; $module = module_config($key);
    require_module_access($db,$user,$key);
    $fields = array_unique([$module['pk'],...array_keys($module['fields'])]);
    $columns = '`' . implode('`,`',$fields) . '`';
    $rows = db_rows($db,"SELECT $columns FROM `{$module['table']}` WHERE company_id = ? ORDER BY `{$module['pk']}` LIMIT 5001",[$companyId]);
    if (count($rows)>5000) fail('This export exceeds 5,000 records. Use the paginated records API to retrieve the complete dataset.',422);
    $money = (bool)array_filter($module['fields'],fn($field)=>$field['type'] === 'money');
    header('Content-Type: text/csv; charset=utf-8'); header('Content-Disposition: attachment; filename="simplr-' . $key . '.csv"');
    $out = fopen('php://output','w'); fwrite($out,"\xEF\xBB\xBF");
    fputcsv($out,[...$fields,...($money ? ['record_currency'] : [])],',','"','');
    foreach ($rows as $row) {
        foreach ($module['fields'] as $name=>$field) if ($field['type'] === 'textarea' && isset($row[$name])) $row[$name] = rich_text_plain($row[$name]);
        $cells = array_map(function($value) { $value = (string)($value ?? ''); return preg_match('/^[\s\x00-\x1f]*[=+@-]/u',$value) ? "'" . $value : $value; },array_values($row));
        if ($money) $cells[] = $settings['currency'] ?: 'Not specified';
        fputcsv($out,$cells,',','"','');
    }
    fclose($out); exit;
}

send_due_report_notifications($db);

$projectOptions = db_rows($db, 'SELECT id, projectName FROM projects WHERE company_id = ? AND archived_at IS NULL ORDER BY projectName ASC', [$companyId]);
$taskOptions = db_rows($db, 'SELECT t.id, t.title, t.project_id, t.due_date FROM tasks t LEFT JOIN projects p ON ' . task_project_join() . ' WHERE t.company_id = ? AND t.archived_at IS NULL AND (t.project_id IS NULL OR t.project_id = \'\' OR p.id IS NOT NULL) ORDER BY t.due_date DESC, t.id DESC', [$companyId]);
$reportRows = db_rows($db, 'SELECT pr.*, p.projectName AS project_name, t.title AS task_name FROM project_reports pr LEFT JOIN projects p ON p.id = pr.project_id AND p.company_id = pr.company_id LEFT JOIN tasks t ON t.id = pr.task_id AND t.company_id = pr.company_id WHERE pr.company_id = ? ORDER BY pr.next_due_at DESC, pr.id DESC', [$companyId]);

suite_start('Reports','reports',$user);
?>
<div class="page-heading"><div><h1>Workspace reports</h1><p>Live record counts, downloadable data, and the recurring project or task reports your team needs to deliver.</p></div></div>
<div class="notice info">Exports include up to 5,000 records and the fields managed by each tool. Financial records use <?= e($settings['currency'] ?: 'an unspecified currency') ?>. Reports do not calculate tax, payroll or audited financial statements.</div>
<?php if (!$available): ?><section class="panel"><div class="empty-state"><h2>No tools assigned yet.</h2><p>Your administrator can assign view or edit access to the tools you need.</p></div></section><?php endif; ?>
<div class="panel" id="report-library"><div class="panel-head"><h2>Project and task reports</h2><span class="count-label"><?= count($reportRows) ?> scheduled</span></div>
<div class="panel-body">
  <form method="post" enctype="multipart/form-data" class="form-stack" data-submit>
    <?= csrf_field() ?>
    <div class="field"><label for="report-title">Report title</label><input id="report-title" name="title" required maxlength="180" placeholder="Weekly project update"></div>
    <div class="field"><label for="report-scope">Scope</label><select id="report-scope" name="scope"><option value="project">Project</option><option value="task">Task</option></select></div>
    <div class="field"><label for="report-project">Project</label><select id="report-project" name="project_id"><option value="">Select a project</option><?php foreach ($projectOptions as $project): ?><option value="<?= (int)$project['id'] ?>"><?= e($project['projectName']) ?></option><?php endforeach; ?></select></div>
    <div class="field"><label for="report-task">Task</label><select id="report-task" name="task_id"><option value="">Select a task</option><?php foreach ($taskOptions as $task): ?><option value="<?= (int)$task['id'] ?>"><?= e($task['title']) ?><?= $task['project_id'] ? ' · project #' . e($task['project_id']) : '' ?></option><?php endforeach; ?></select></div>
    <div class="field"><label for="report-frequency">Frequency required</label><select id="report-frequency" name="frequency"><option value="daily">Daily</option><option value="weekly" selected>Weekly</option><option value="biweekly">Every 2 weeks</option><option value="monthly">Monthly</option></select></div>
    <div class="field"><label for="report-due">First due date</label><input id="report-due" type="datetime-local" name="next_due_at" value="<?= e(date('Y-m-d\TH:i')) ?>" required></div>
    <div class="field"><label for="report-assignee">Assigned to</label><input id="report-assignee" name="assignee" maxlength="255" placeholder="Name or team"></div>
    <div class="field"><label for="report-assignee-email">Assignee email</label><input id="report-assignee-email" type="email" name="assignee_email" maxlength="255" placeholder="assignee@example.com"></div>
    <div class="field"><label for="report-summary">Summary</label><textarea id="report-summary" name="summary" maxlength="600" required placeholder="What is the update about and what should the team watch?"></textarea></div>
    <div class="field"><label for="report-text">Report</label><textarea id="report-text" name="report_text" required placeholder="Write your report here or attach a file below." rows="7"></textarea></div>
    <div class="field"><label for="report-file">Upload report file</label><input id="report-file" type="file" name="report_file" accept=".pdf,.doc,.docx,.txt,.png,.jpg,.jpeg"></div>
    <div><button class="button" type="submit">Save report</button></div>
  </form>
</div>
</div>

<?php if ($reportRows): ?><section class="panel"><div class="panel-head"><h2>Scheduled report list</h2></div><div class="panel-body"><div class="tools-grid"><?php foreach ($reportRows as $report): $reportId = (int)$report['id']; $attachments = db_rows($db,'SELECT id, original_name, size_bytes, created_at FROM record_attachments WHERE company_id = ? AND module_key = ? AND record_id = ? ORDER BY id DESC',[$companyId,'project_reports',$reportId]); ?><article class="tool-card"><h3><?= e($report['title']) ?></h3><p><strong>Scope:</strong> <?= e(ucfirst((string)$report['report_scope'])) ?> · <?= e($report['report_scope'] === 'task' ? ($report['task_name'] ?: 'Task #' . (int)$report['task_id']) : ($report['project_name'] ?: 'Project #' . (int)$report['project_id'])) ?></p><p><strong>Frequency:</strong> <?= e(ucfirst((string)$report['frequency'])) ?> · <strong>Next due:</strong> <?= e((string)$report['next_due_at']) ?></p><p><strong>Assigned to:</strong> <?= e((string)($report['assignee'] ?: ($report['assignee_email'] ?: 'Unassigned'))) ?></p><p><strong>Status:</strong> <?= e(ucfirst((string)$report['status'])) ?></p><div class="report-summary"><?= e((string)$report['summary']) ?></div><div class="report-attachments"><?php if ($attachments): foreach ($attachments as $attachment): ?><div><a class="text-link" href="/api/attachments.php?id=<?= (int)$attachment['id'] ?>"><?= e($attachment['original_name']) ?></a> <small><?= number_format((int)$attachment['size_bytes'] / 1024, 1) ?> KB</small></div><?php endforeach; else: ?><small>No file attachments yet.</small><?php endif; ?></div></article><?php endforeach; ?></div></div></section><?php endif; ?>
<div class="tools-grid"><?php foreach ($available as $key=>$module): $count = (int)db_one($db,"SELECT COUNT(*) AS total FROM `{$module['table']}` WHERE company_id = ?",[$companyId])['total']; ?><section class="tool-card"><div class="feature-icon"><?= icon($module['icon']) ?></div><h2><?= e($module['title']) ?></h2><p class="report-count"><?= number_format($count) ?> <small>records</small></p><div class="form-actions"><a class="text-link" href="/users/admin/workspace.php?module=<?= e($key) ?>">Open tool</a><a class="button secondary small" href="?export=<?= e($key) ?>">Export CSV</a></div></section><?php endforeach; ?></div>
<?php suite_end(); ?>
