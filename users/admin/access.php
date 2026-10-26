<?php
require_once __DIR__ . '/../../app/modules.php';
require_once __DIR__ . '/../../app/view.php';
$db = database(); $user = admin_user($db); $companyId = (int)$user['company_id'];
$role = is_string($_GET['role'] ?? null) ? $_GET['role'] : 'employee';
if (!in_array($role,['employee','manager'],true)) fail('Choose an employee or manager.',422);
$staffId = filter_var($_GET['id'] ?? null,FILTER_VALIDATE_INT,['options'=>['min_range'=>1]]) ?: null;
$table = $role === 'employee' ? 'employees' : 'managers';
$staffList = db_rows($db,$role === 'employee' ? 'SELECT id, CONCAT(first_name, \' \', last_name) AS name, email FROM employees WHERE company_id = ? ORDER BY name' : 'SELECT id, manager_name AS name, manager_email AS email FROM managers WHERE company_id = ? ORDER BY name',[$companyId]);
$person = null;
foreach ($staffList as $candidate) if ((int)$candidate['id'] === $staffId) $person = $candidate;
if ($staffId && !$person) fail('This person could not be found in your workspace.',404);
$grants = $person ? db_rows($db,'SELECT module_key, access_level FROM staff_tool_access WHERE company_id = ? AND staff_role = ? AND staff_id = ? ORDER BY module_key',[$companyId,$role,$staffId]) : [];
$version = record_version($grants);
if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    require_post();
    if (!$person) fail('Choose a person before assigning tools.',422);
    $posted = $_POST['access'] ?? [];
    if (!is_array($posted)) fail('Choose valid tool permissions.',422);
    foreach ($posted as $key=>$level) if (!isset(assignable_modules()[$key]) || !is_string($level) || !in_array($level,['','view','edit'],true)) fail('Choose valid tool permissions.',422);
    $db->begin_transaction();
    try {
        // Serializes updates even when this person has no grants yet.
        if (!db_one($db,"SELECT id FROM `$table` WHERE id = ? AND company_id = ? FOR UPDATE",[$staffId,$companyId])) { $db->rollback(); fail('This person is no longer in your workspace.',404); }
        $current = db_rows($db,'SELECT module_key, access_level FROM staff_tool_access WHERE company_id = ? AND staff_role = ? AND staff_id = ? ORDER BY module_key FOR UPDATE',[$companyId,$role,$staffId]);
        if (!hash_equals(record_version($current),input('version'))) { $db->rollback(); fail('These permissions changed. Reload and review the latest access before saving.',409); }
        db_run($db,'DELETE FROM staff_tool_access WHERE company_id = ? AND staff_role = ? AND staff_id = ?',[$companyId,$role,$staffId]);
        foreach ($posted as $key=>$level) if ($level !== '') db_run($db,'INSERT INTO staff_tool_access (company_id, staff_role, staff_id, module_key, access_level, granted_by) VALUES (?, ?, ?, ?, ?, ?)',[$companyId,$role,$staffId,$key,$level,$user['id']]);
        audit_event($db,$companyId,'access.updated',$role,(string)$staffId,['before'=>$current,'after'=>array_filter($posted)]);
        $db->commit();
    } catch (Throwable $error) { $db->rollback(); throw $error; }
    flash('Tool access updated. Changes apply on the next request.');
    redirect('/users/admin/access.php?' . http_build_query(['role'=>$role,'id'=>$staffId]));
}
$assigned = array_column($grants,'access_level','module_key');
suite_start('Staff access','access',$user);
?>
<div class="page-heading"><div><span class="eyebrow">THE RIGHT TOOLS FOR EACH PERSON</span><h1>Staff access</h1><p>Choose who can view and edit each part of your workspace.</p></div></div>
<nav class="module-tabs" aria-label="Staff roles"><a href="?role=employee" <?= $role === 'employee' ? 'class="active" aria-current="page"' : '' ?>>Employees</a><a href="?role=manager" <?= $role === 'manager' ? 'class="active" aria-current="page"' : '' ?>>Managers</a></nav>
<section class="panel"><div class="panel-body"><form method="get" class="form-stack"><input type="hidden" name="role" value="<?= e($role) ?>"><div class="field"><label for="person">Choose <?= e($role) ?></label><select id="person" name="id" required><option value="">Select a person</option><?php foreach ($staffList as $candidate): ?><option value="<?= (int)$candidate['id'] ?>" <?= (int)$candidate['id'] === $staffId ? 'selected' : '' ?>><?= e($candidate['name'] . ' · ' . $candidate['email']) ?></option><?php endforeach; ?></select></div><div><button class="button secondary" type="submit">Review access</button></div></form><?php if (!$staffList): ?><p>Add a person to your <a class="text-link" href="/users/admin/workspace.php?module=<?= $role === 'employee' ? 'employees' : 'managers' ?>">staff directory</a> first.</p><?php endif; ?></div></section>
<?php if ($person): ?>
<section class="panel"><div class="panel-head"><h2>Tools for <?= e($person['name']) ?></h2></div><div class="panel-body">
<div class="notice info">Tool access includes all records in that tool within your company, including related names needed by its forms. View access allows reading; edit access also allows adding and updating. Account invitations, directory changes and permission management remain with the business owner. Assigned tasks remain available without a tool grant.</div>
<?php if ($role === 'employee'): $legacy = db_rows($db,'SELECT DISTINCT t.tool_name FROM tools_assigned a JOIN tools t ON t.id = a.tool_id JOIN employees e ON e.id = a.employee_id AND e.company_id = a.company_id WHERE a.company_id = ? AND a.employee_id = ?',[$companyId,$staffId]); if ($legacy): ?>
<div class="notice warning"><strong>Earlier assignments need review:</strong> <?= e(implode(', ',array_column($legacy,'tool_name'))) ?>. These older assignments are preserved. Set the specific permissions below to activate access in this workspace.</div>
<?php endif; endif; ?>
<form method="post" class="form-stack" data-submit><?= csrf_field() ?><input type="hidden" name="version" value="<?= e($version) ?>"><div class="access-grid">
<?php foreach (assignable_modules() as $key=>$module): ?><div class="field"><label for="access-<?= e($key) ?>"><?= e($module['title']) ?></label><select id="access-<?= e($key) ?>" name="access[<?= e($key) ?>]"><?php foreach ([''=>'No access','view'=>'View records','edit'=>'View, add and edit'] as $level=>$label): ?><option value="<?= e($level) ?>" <?= ($assigned[$key] ?? '') === $level ? 'selected' : '' ?>><?= e($label) ?></option><?php endforeach; ?></select></div><?php endforeach; ?>
</div><div class="form-actions"><button type="submit" class="button">Save access</button></div></form></div></section>
<?php endif; suite_end(); ?>
