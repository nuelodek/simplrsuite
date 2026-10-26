<?php
require_once __DIR__ . '/../../app/modules.php';
require_once __DIR__ . '/../../app/settings.php';
require_once __DIR__ . '/../../app/view.php';
$db = database(); $user = workspace_user($db); $companyId = (int)$user['company_id'];
$available = visible_modules($db,$user); $settings = workspace_settings($db,$companyId);
if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'GET') fail('Use GET to read reports.',405);
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
        // Prevent spreadsheet programs from evaluating user-authored cells.
        $cells = array_map(function($value) { $value = (string)($value ?? ''); return preg_match('/^[\s\x00-\x1f]*[=+@-]/u',$value) ? "'" . $value : $value; },array_values($row));
        if ($money) $cells[] = $settings['currency'] ?: 'Not specified';
        fputcsv($out,$cells,',','"','');
    }
    fclose($out); exit;
}
suite_start('Reports','reports',$user);
?>
<div class="page-heading"><div><span class="eyebrow">A CLEARER VIEW OF YOUR WORK</span><h1>Workspace reports</h1><p>Live record counts and downloadable data for the tools you can access.</p></div></div>
<div class="notice info">Exports include up to 5,000 records and the fields managed by each tool. Financial records use <?= e($settings['currency'] ?: 'an unspecified currency') ?>. Reports do not calculate tax, payroll or audited financial statements.</div>
<?php if (!$available): ?><section class="panel"><div class="empty-state"><h2>No tools assigned yet.</h2><p>Your administrator can assign view or edit access to the tools you need.</p></div></section><?php endif; ?>
<div class="tools-grid"><?php foreach ($available as $key=>$module): $count = (int)db_one($db,"SELECT COUNT(*) AS total FROM `{$module['table']}` WHERE company_id = ?",[$companyId])['total']; ?><section class="tool-card"><div class="feature-icon"><?= icon($module['icon']) ?></div><h2><?= e($module['title']) ?></h2><p class="report-count"><?= number_format($count) ?> <small>records</small></p><div class="form-actions"><a class="text-link" href="/users/admin/workspace.php?module=<?= e($key) ?>">Open tool</a><a class="button secondary small" href="?export=<?= e($key) ?>">Export CSV</a></div></section><?php endforeach; ?></div>
<?php suite_end(); ?>
