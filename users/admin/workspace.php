<?php
require_once __DIR__ . '/../../app/auth.php';
require_once __DIR__ . '/../../app/view.php';
require_once __DIR__ . '/../../app/modules.php';
require_once __DIR__ . '/../../app/settings.php';
require_once __DIR__ . '/../../app/attachments.php';
$db = database(); $user = workspace_user($db); $companyId = (int)$user['company_id'];
$key = is_string($_GET['module'] ?? null) ? $_GET['module'] : 'projects'; $module = module_config($key);
$action = is_string($_GET['action'] ?? null) ? $_GET['action'] : 'list';
if (!in_array($action,['list','view','new','edit'],true)) fail('This page could not be found.',404);
$access = require_module_access($db,$user,$key,in_array($action,['new','edit'],true) || ($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST');
$canEdit = $access === 'edit';
$settings = workspace_settings($db,$companyId);
$id = filter_var($_GET['id'] ?? null,FILTER_VALIDATE_INT,['options'=>['min_range'=>1]]) ?: null;
$record = []; $errors = []; $version = '';
if (in_array($action,['edit','view'],true)) {
    if (!$id) fail('Choose a record to view.',404);
    $record = db_one($db,"SELECT * FROM `{$module['table']}` WHERE `{$module['pk']}` = ? AND company_id = ?",[$id,$companyId]);
    if (!$record) fail('This record could not be found in your workspace.',404);
    if ($key === 'applications' && db_one($db,'SELECT application_id FROM application_submissions WHERE application_id = ? AND company_id = ?',[$id,$companyId])) redirect('/users/admin/application.php?id=' . $id);
    $version = record_version($record);
    $record = normalize_record_input($db,$companyId,$module,$record);
}
if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    require_post();
    if (!in_array($action,['new','edit'],true)) fail('Choose a form to save a record.',405);
    [$values,$errors] = validate_record($db,$companyId,$module,$_POST);
    if (!$errors) {
        try {
            save_record($db,$companyId,$key,$values,$action === 'edit' ? $id : null,input('version'));
            flash(ucfirst($module['singular']) . ($action === 'edit' ? ' updated.' : ' added to your workspace.'));
            redirect('/users/admin/workspace.php?module=' . rawurlencode($key));
        } catch (mysqli_sql_exception $error) {
            if ($error->getCode() !== 1062) throw $error;
            $errors['_form'] = 'A record already uses these details. Review the existing record before adding another.';
        }
    }
    $record = $_POST; http_response_code(422);
}
if (in_array($key,['events','jobs'],true) && empty($record['timezone'])) $record['timezone'] = $settings['timezone'];
if ($key === 'jobs' && $action === 'new' && !isset($record['status'])) $record['status'] = 'draft';
suite_start($module['title'],$module['nav'],$user);
?>
<div class="page-heading"><div><h1><?= e($module['title']) ?></h1><p><?= e($module['description']) ?></p></div><?php if ($action === 'list' && $canEdit): ?><a class="button" href="?module=<?= e($key) ?>&amp;action=new"><?= icon('plus') ?> Add <?= e($module['singular']) ?></a><?php endif; ?></div>
<?php $siblings = array_filter(visible_modules($db,$user),fn($m)=>$m['nav'] === $module['nav']); if (count($siblings)>1 && $module['nav'] !== 'modules'): ?><nav class="module-tabs" aria-label="Related tools"><?php foreach ($siblings as $siblingKey=>$sibling): ?><a href="?module=<?= e($siblingKey) ?>" <?= $key === $siblingKey ? 'class="active" aria-current="page"' : '' ?>><?= e($sibling['title']) ?></a><?php endforeach; ?></nav><?php endif; ?>
<?php if (($_SESSION['auth_role'] ?? '') === 'admin' && in_array($key,['employees','managers'],true)): ?><div class="notice info">Add your people here, then choose Invite to email a link for them to set their own password. You can also send a new invitation if someone needs to reset access.</div><?php endif; ?>
<?php if (!billing_module_active($db,$companyId,$key)): ?><div class="notice warning">This toolkit is available to view. Your workspace owner can renew editing access in <a href="/users/admin/billing.php">Billing</a>.</div><?php endif; ?>
<?php if ($key === 'events'): ?><p><a class="text-link" href="/users/admin/calendar.php">View calendar</a></p><?php endif; ?>
<?php if ($key === 'jobs'): ?><div class="notice info">Drafts are private. Choose Published to list the role on the job board. Closing a published role stops new applications; existing applicants can still track their status. Applications close at the end of the selected date in its timezone.</div><?php endif; ?>
<?php if (array_filter($module['fields'],fn($f)=>$f['type'] === 'money')): ?><div class="notice info">Amounts are recorded in <?= e($settings['currency'] ?: 'an unspecified currency — ask your workspace owner to set it in Settings') ?>. No currency conversion is applied.</div><?php endif; ?>
<?php if ($action === 'list' && in_array($key,['employees','managers'],true)): $orgTree = organization_tree($db,$companyId); ?>
<section class="panel org-tree-panel"><div class="panel-head"><h2>Organizational chart</h2><span class="count-label">Trace reporting lines across departments</span></div>
<?php if (empty($orgTree['departments'])): ?><p class="subtle">Add a department and assign managers to begin building your org chart.</p><?php else: foreach ($orgTree['departments'] as $department): ?>
<div class="org-department">
  <h3><?= e($department['name'] ?: 'Unassigned') ?></h3>
  <?php if (!empty($department['managers'])): ?>
  <div class="org-tree">
    <?php foreach ($department['managers'] as $manager): ?>
    <div class="org-node manager-node">
      <div class="org-person">
        <strong><?= e($manager['name']) ?></strong>
        <?php if ($manager['email']): ?><span><?= e($manager['email']) ?></span><?php endif; ?>
        <?php if ($manager['department_name']): ?><small><?= e($manager['department_name']) ?></small><?php endif; ?>
      </div>
      <?php if (!empty($manager['reports'])): ?>
      <ul class="org-children">
        <?php foreach ($manager['reports'] as $report): ?>
        <li class="org-person report-person">
          <strong><?= e($report['name']) ?></strong>
          <?php if ($report['job_title']): ?><span><?= e($report['job_title']) ?></span><?php endif; ?>
          <?php if ($report['email']): ?><small><?= e($report['email']) ?></small><?php endif; ?>
        </li>
        <?php endforeach; ?>
      </ul>
      <?php else: ?><p class="org-empty">No direct reports</p><?php endif; ?>
    </div>
    <?php endforeach; ?>
  </div>
  <?php endif; ?>
  <?php if (!empty($department['unassigned_employees'])): ?>
  <div class="org-standalone">
    <h4>Unassigned</h4>
    <ul class="org-children">
      <?php foreach ($department['unassigned_employees'] as $employee): ?>
      <li class="org-person report-person"><strong><?= e($employee['name']) ?></strong><?php if ($employee['job_title']): ?><span><?= e($employee['job_title']) ?></span><?php endif; ?><?php if ($employee['email']): ?><small><?= e($employee['email']) ?></small><?php endif; ?></li>
      <?php endforeach; ?>
    </ul>
  </div>
  <?php endif; ?>
</div>
<?php endforeach; endif; ?>
</section>
<?php endif; ?>
<?php if ($action === 'view'): require __DIR__ . '/../../app/record-detail.php'; ?>
<?php elseif ($action !== 'list'): ?>
<section class="panel"><div class="panel-head"><h2><?= $action === 'edit' ? 'Edit' : 'Add' ?> <?= e($module['singular']) ?></h2><a class="text-link" href="?module=<?= e($key) ?>">Back to <?= e(strtolower($module['title'])) ?></a></div>
<form method="post" class="record-form form-stack" data-submit><?= csrf_field() ?><input type="hidden" name="version" value="<?= e($version) ?>">
<?php if ($errors): ?><div class="notice error" role="alert"><?= e($errors['_form'] ?? 'Please check the highlighted fields below.') ?></div><?php endif; ?>
<?php foreach ($module['fields'] as $name=>$field): $value = $record[$name] ?? ''; $type = $field['type']; if ($type === 'datetime-local' && is_string($value)) $value = str_replace(' ','T',$value); ?>
<div class="field"><label for="field-<?= e($name) ?>"><?= e($field['label']) ?><?= $field['required'] ? ' *' : ' (optional)' ?></label>
<?php if ($type === 'textarea'): ?><textarea id="field-<?= e($name) ?>" name="<?= e($name) ?>" maxlength="<?= $field['max'] ?>" <?= $field['required'] ? 'required' : '' ?> <?= isset($errors[$name]) ? 'aria-invalid="true" aria-describedby="error-' . e($name) . '"' : '' ?>><?= e($value) ?></textarea>
<?php elseif ($type === 'social-platforms'): $selectedValues = array_fill_keys(normalize_social_platform_targets($value), true); ?><div class="form-stack" id="field-<?= e($name) ?>-group"><?php foreach (social_platform_options() as $option=>$label): ?><label class="checkbox-option" for="field-<?= e($name) ?>-<?= e($option) ?>"><input id="field-<?= e($name) ?>-<?= e($option) ?>" type="checkbox" name="<?= e($name) ?>[]" value="<?= e($option) ?>" <?= !empty($selectedValues[$option]) ? 'checked' : '' ?>><span><?= e($label) ?></span></label><?php endforeach; ?><small>Direct posting needs provider credentials in the server environment. When configured, choosing a platform queues a social sync for the published role.</small></div>
<?php elseif ($type === 'select' || str_starts_with($type,'ref:') || str_starts_with($type,'multi:')):
    $multiple = str_starts_with($type,'multi:');
    $options = $type === 'select' ? array_combine($field['options'],$field['options']) : reference_options($db,$companyId,substr($type,$multiple ? 6 : 4));
    $selectedValues = $multiple ? (is_array($value) ? $value : (json_decode($value ?: '[]',true) ?: [])) : [(string)$value];
?><select id="field-<?= e($name) ?>" name="<?= e($name) ?><?= $multiple ? '[]' : '' ?>" <?= $multiple ? 'multiple size="5"' : '' ?> <?= $field['required'] ? 'required' : '' ?> <?= isset($errors[$name]) ? 'aria-invalid="true" aria-describedby="error-' . e($name) . '"' : '' ?>><?php if (!$multiple): ?><option value=""><?= $field['required'] ? 'Select an option' : 'None selected' ?></option><?php endif; ?><?php foreach ($options as $option=>$label): ?><option value="<?= e($option) ?>" <?= in_array((string)$option,array_map('strval',$selectedValues),true) ? 'selected' : '' ?>><?= e(ucfirst($label)) ?></option><?php endforeach; ?></select><?php if (!$options): ?><small>Add the related <?= e(str_replace('_',' ',explode(':',$type)[1] ?? 'record')) ?> first, then return here.</small><?php endif; ?><?php if ($multiple): ?><small>Hold Ctrl (Windows) or Command (Mac) to select more than one member.</small><?php endif; ?>
<?php else: ?><input id="field-<?= e($name) ?>" name="<?= e($name) ?>" type="<?= in_array($type,['integer','money'],true) ? 'number' : e($type) ?>" value="<?= e($value) ?>" <?= in_array($type,['integer','money'],true) ? 'min="0" max="' . ($type === 'money' ? '99999999.99' : '999999999') . '" step="' . ($type === 'money' ? '0.01' : '1') . '"' : 'maxlength="' . $field['max'] . '"' ?> <?= $type === 'date' ? 'min="1900-01-01" max="2200-12-31"' : '' ?> <?= $field['required'] ? 'required' : '' ?> <?= isset($errors[$name]) ? 'aria-invalid="true" aria-describedby="error-' . e($name) . '"' : '' ?>><?php endif; ?>
<?php if (isset($errors[$name])): ?><span class="field-error" id="error-<?= e($name) ?>"><?= e($errors[$name]) ?></span><?php endif; ?></div>
<?php endforeach; ?>
<div class="form-actions"><button class="button" type="submit"><?= $action === 'edit' ? 'Save changes' : 'Add ' . e($module['singular']) ?></button><a class="button secondary" href="?module=<?= e($key) ?>">Cancel</a></div>
</form></section>
<?php else:
    $query = is_string($_GET['q'] ?? null) ? mb_substr(trim($_GET['q']),0,100) : '';
    $stageField = module_stage_field($key);
    $stageOptions = $stageField ? $module['fields'][$stageField]['options'] : [];
    $stage = is_string($_GET['stage'] ?? null) && in_array($_GET['stage'],$stageOptions,true) ? $_GET['stage'] : '';
    $requestedView = is_string($_GET['view'] ?? null) ? $_GET['view'] : ($_SESSION['record_view'][$key] ?? 'list');
    $view = module_has_board($key) && $requestedView === 'board' ? 'board' : 'list';
    if (isset($_GET['view'])) $_SESSION['record_view'][$key] = $view;
    $where = 'company_id = ?'; $params = [$companyId];
    if ($query !== '') { $clauses = []; foreach ($module['columns'] as $column) { $clauses[] = "`$column` LIKE ?"; $params[] = '%' . str_replace(['!','%','_'],['!!','!%','!_'],$query) . '%'; } $where .= ' AND (' . implode(" ESCAPE '!' OR ",$clauses) . " ESCAPE '!')"; }
    $stageCounts = [];
    if ($stageField) foreach (db_rows($db,"SELECT `$stageField` AS stage, COUNT(*) AS count FROM `{$module['table']}` WHERE $where GROUP BY `$stageField`",$params) as $row) $stageCounts[(string)$row['stage']] = (int)$row['count'];
    $listWhere = $where; $listParams = $params;
    if ($stage !== '' && $view === 'list') { $listWhere .= " AND `$stageField` = ?"; $listParams[] = $stage; }
    $count = (int)db_one($db,"SELECT COUNT(*) AS count FROM `{$module['table']}` WHERE $listWhere",$listParams)['count'];
    $pages = max(1,(int)ceil($count/25)); $page = min($pages,max(1,(int)($_GET['page'] ?? 1))); $offset = ($page-1)*25;
    $rows = $view === 'board'
        ? db_rows($db,"SELECT * FROM `{$module['table']}` WHERE $listWhere ORDER BY `{$module['pk']}` DESC LIMIT 300",$listParams)
        : db_rows($db,"SELECT * FROM `{$module['table']}` WHERE $listWhere ORDER BY `{$module['pk']}` DESC LIMIT 25 OFFSET $offset",$listParams);
    $progress = $key === 'projects' ? project_task_progress($db,$companyId) : [];
    $currency = (string)($settings['currency'] ?? '');
    $link = fn(array $changes) => '?' . http_build_query(array_filter(array_merge(['module'=>$key,'q'=>$query,'stage'=>$stage],$changes),fn($value)=>$value !== '' && $value !== null));
?>
<section class="panel"><div class="table-toolbar"><form method="get" role="search" class="toolbar-search"><input type="hidden" name="module" value="<?= e($key) ?>"><?php if ($stage !== ''): ?><input type="hidden" name="stage" value="<?= e($stage) ?>"><?php endif; ?><label class="sr-only" for="search">Search <?= e(strtolower($module['title'])) ?></label><span class="search-field"><?= icon('search') ?><input class="filter-input" id="search" type="search" name="q" placeholder="Search <?= e(strtolower($module['title'])) ?>…" value="<?= e($query) ?>" maxlength="100"></span><button class="button small secondary" type="submit">Search</button></form><div class="toolbar-end"><span class="count-label"><?= number_format($count) ?> <?= $count === 1 ? 'record' : 'records' ?></span><?php if (module_has_board($key)): ?><div class="view-switch" role="group" aria-label="Layout"><a href="<?= e($link(['view'=>'list','stage'=>$stage])) ?>"<?= $view === 'list' ? ' aria-current="page"' : '' ?>><?= icon('list') ?>List</a><a href="<?= e($link(['view'=>'board','stage'=>null])) ?>"<?= $view === 'board' ? ' aria-current="page"' : '' ?>><?= icon('columns') ?>Board</a></div><?php endif; ?></div></div>
<?php if ($stageField && $view === 'list' && array_sum($stageCounts) > 0): ?><nav class="stage-filter" aria-label="Filter by <?= e(strtolower($module['fields'][$stageField]['label'])) ?>"><a href="<?= e($link(['stage'=>null])) ?>"<?= $stage === '' ? ' aria-current="true"' : '' ?>>All <span><?= number_format(array_sum($stageCounts)) ?></span></a><?php foreach ($stageOptions as $option): ?><a href="<?= e($link(['stage'=>$option])) ?>"<?= $stage === $option ? ' aria-current="true"' : '' ?>><i class="stage-dot <?= e(stage_class($option)) ?>"></i><?= e(ucfirst($option)) ?> <span><?= number_format($stageCounts[$option] ?? 0) ?></span></a><?php endforeach; ?></nav><?php endif; ?>
<?php if (!$rows && $view === 'list'): ?><div class="empty-state"><?= icon($module['icon']) ?><h3><?= $query !== '' || $stage !== '' ? 'No matching records.' : 'No ' . e(strtolower($module['title'])) . ' yet.' ?></h3><p><?= $query !== '' || $stage !== '' ? 'Try a different search or filter, or clear it to see all your records.' : 'Add your first ' . e($module['singular']) . ' to get started.' ?></p><?php if ($canEdit || $query !== '' || $stage !== ''): ?><a class="button small secondary" href="?module=<?= e($key) ?><?= $query === '' && $stage === '' ? '&amp;action=new' : '' ?>"><?= $query !== '' || $stage !== '' ? 'Clear filters' : 'Add ' . e($module['singular']) ?></a><?php endif; ?></div>
<?php elseif ($view === 'board'):
    $grouped = array_fill_keys($stageOptions,[]);
    foreach ($rows as $row) if (isset($grouped[(string)$row[$stageField]])) $grouped[(string)$row[$stageField]][] = $row;
    $metaColumns = array_slice(array_values(array_diff($module['columns'],[$module['columns'][0],$stageField,'created_at','startDate','start_date','milestoneName'])),0,2);
?>
<p class="board-help" id="board-help"><?= $canEdit ? 'Drag a card to another column, or focus it and press ← or → to change its ' . e(strtolower($module['fields'][$stageField]['label'])) . '.' : 'You can view this board. Ask your workspace administrator for edit access to move cards.' ?><?= count($rows) === 300 ? ' Showing the 300 most recent records.' : '' ?></p>
<div class="board" data-board data-module="<?= e($key) ?>" data-editable="<?= $canEdit ? 'true' : 'false' ?>" data-csrf="<?= e(csrf_token()) ?>">
<?php foreach ($grouped as $stageValue=>$cards): ?><section class="board-column" data-board-column data-stage="<?= e($stageValue) ?>" data-label="<?= e(ucfirst($stageValue)) ?>" aria-labelledby="stage-<?= e(stage_class($stageValue)) ?>"><header><i class="stage-dot <?= e(stage_class($stageValue)) ?>"></i><h3 id="stage-<?= e(stage_class($stageValue)) ?>"><?= e(ucfirst($stageValue)) ?></h3><span class="board-count" data-board-count><?= count($cards) ?></span></header><div class="board-list" data-board-list><?php foreach ($cards as $row): $title = record_title($module,$row); ?><article class="board-card" data-board-card data-id="<?= (int)$row[$module['pk']] ?>" data-version="<?= e(record_version($row)) ?>" data-title="<?= e($title) ?>" tabindex="0"<?= $canEdit ? ' draggable="true" aria-describedby="board-help"' : '' ?>><a class="board-card-title" draggable="false" href="?module=<?= e($key) ?>&amp;action=view&amp;id=<?= (int)$row[$module['pk']] ?>"><?= e($title) ?></a><?php if ($key === 'projects'): ?><?= progress_html($progress[(int)$row['id']] ?? null) ?><?php endif; ?><div class="board-card-meta"><?php foreach ($metaColumns as $column): if (($row[$column] ?? '') === '' || $row[$column] === null) continue; ?><span><?= record_cell_html($module,$column,$row,$currency,$stageField) ?></span><?php endforeach; ?></div></article><?php endforeach; ?></div><p class="board-empty" data-board-empty<?= $cards ? ' hidden' : '' ?>><?= $canEdit ? 'Drop cards here' : 'Nothing here' ?></p></section><?php endforeach; ?>
</div>
<?php else: ?><div class="table-wrap"><table class="data-table"><caption class="sr-only"><?= e($module['title']) ?> in your workspace</caption><thead><tr><?php foreach ($module['columns'] as $column): ?><th scope="col"><?= e($module['fields'][$column]['label'] ?? ucfirst(str_replace('_',' ',$column))) ?></th><?php endforeach; ?><?php if ($key === 'projects'): ?><th scope="col">Progress</th><?php endif; ?><th scope="col">Action</th></tr></thead><tbody><?php foreach ($rows as $row): ?><tr><?php foreach ($module['columns'] as $index=>$column): ?><td><?php if ($index === 0): ?><a class="row-title" href="?module=<?= e($key) ?>&amp;action=view&amp;id=<?= (int)$row[$module['pk']] ?>"><?= e(($row[$column] ?? '') !== '' && $row[$column] !== null ? $row[$column] : '—') ?></a><?php else: ?><?= record_cell_html($module,$column,$row,$currency,$stageField) ?><?php endif; ?></td><?php endforeach; ?><?php if ($key === 'projects'): ?><td><?= progress_html($progress[(int)$row['id']] ?? null) ?></td><?php endif; ?><td class="row-actions"><a class="text-link" href="?module=<?= e($key) ?>&amp;action=view&amp;id=<?= (int)$row[$module['pk']] ?>">View<span class="sr-only"> <?= e($row[$module['columns'][0]]) ?></span></a><?php if ($canEdit): ?><a class="text-link" href="?module=<?= e($key) ?>&amp;action=edit&amp;id=<?= (int)$row[$module['pk']] ?>">Edit<span class="sr-only"> <?= e($row[$module['columns'][0]]) ?></span></a><?php if (($_SESSION['auth_role'] ?? '') === 'admin' && in_array($key,['employees','managers'],true)): ?><form method="post" action="/users/admin/invite.php" style="display:inline" data-submit><?= csrf_field() ?><input type="hidden" name="role" value="<?= $key === 'employees' ? 'employee' : 'manager' ?>"><input type="hidden" name="staff_id" value="<?= (int)$row[$module['pk']] ?>"><button class="button small secondary" type="submit">Invite</button></form><?php endif; ?><?php else: ?><span class="subtle">View only</span><?php endif; ?></td></tr><?php endforeach; ?></tbody></table></div><?php endif; ?>
<?php if ($pages>1 && $view === 'list'): ?><div class="table-toolbar"><span class="count-label">Page <?= $page ?> of <?= $pages ?></span><div class="form-actions"><?php if ($page>1): ?><a class="button small secondary" href="<?= e($link(['page'=>$page-1])) ?>">Previous</a><?php endif; ?><?php if ($page<$pages): ?><a class="button small secondary" href="<?= e($link(['page'=>$page+1])) ?>">Next</a><?php endif; ?></div></div><?php endif; ?>
</section><?php endif; suite_end(); ?>
