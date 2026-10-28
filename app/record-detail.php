<?php
// Included by the authenticated workspace controller after the record scope check.
$files = db_rows($db,'SELECT id,original_name,size_bytes,media_type,created_at FROM record_attachments WHERE company_id = ? AND module_key = ? AND record_id = ? ORDER BY id DESC',[$companyId,$key,$id]);
?>
<?php if ($key === 'jobs' && $record['status'] !== 'draft' && $record['published_at']): require_once __DIR__ . '/audiences.php'; ?><p><a class="button secondary" href="<?= e(audience_url('talents','/jobs/?job=' . $record['public_id'])) ?>"><?= $record['status'] === 'draft' || !$record['published_at'] ? 'Job link (available after publishing)' : 'View public job page' ?></a></p><?php endif; ?>
<section class="panel"><div class="panel-head"><h2><?= e(ucfirst($module['singular'])) ?> details</h2><div class="form-actions"><a class="text-link" href="?module=<?= e($key) ?>">Back to list</a><?php if ($canEdit): ?><a class="button small" href="?module=<?= e($key) ?>&amp;action=edit&amp;id=<?= $id ?>">Edit record</a><?php endif; ?></div></div>
<div class="panel-body"><dl class="record-details">
<?php foreach ($module['fields'] as $name=>$field):
    if ($key === 'projects' && in_array($name,['milestoneName','milestoneDate','milestoneStatus','milestoneDescription'],true)) continue;
    $value = $record[$name] ?? ''; $type = $field['type'];
    if (str_starts_with($type,'ref:') || str_starts_with($type,'multi:')) {
        $multiple = str_starts_with($type,'multi:'); $options = reference_options($db,$companyId,substr($type,$multiple ? 6 : 4));
        $values = $multiple ? (is_array($value) ? $value : (json_decode($value ?: '[]',true) ?: [])) : [$value];
        $value = implode(', ',array_map(fn($v)=>$options[(string)$v] ?? ($v === '' || $v === null ? '' : 'Unavailable record'),$values));
    }
    if ($type === 'social-platforms') {
        $selected = normalize_social_platform_targets($value);
        $value = $selected ? implode(', ', array_map(fn($platform)=>social_platform_options()[$platform] ?? ucfirst($platform), $selected)) : 'Not selected';
    }
    if ($type === 'money' && $value !== '' && $value !== null) $value = ($settings['currency'] ?: 'Currency unspecified') . ' ' . number_format((float)$value,2);
?><div<?= $type === 'textarea' ? ' class="wide"' : '' ?>><dt><?= e($field['label']) ?></dt><dd<?= $type === 'textarea' ? ' class="rich-text"' : '' ?>><?= $type === 'textarea' && trim((string)$value) !== '' ? rich_text_html((string)$value) : e($value === '' || $value === null ? 'Not provided' : $value) ?></dd></div><?php endforeach; ?>
</dl></div></section>
<?php if ($key === 'tasks'):
    require_once __DIR__ . '/discipline.php';
    $viewer = viewer_identity($user);
    [$queryScope,$queryParams] = discipline_scope($viewer,'q');
    $taskQueries = db_rows($db,"SELECT q.id,q.reference,q.title,q.status,q.created_at,q.subject_role,q.subject_id FROM task_queries q WHERE q.task_id = ? AND $queryScope ORDER BY q.id DESC LIMIT 10",[$id,...$queryParams]);
    $assigneeId = 0;
    if (trim((string)($record['assignee'] ?? '')) !== '') {
        $assignee = trim((string)$record['assignee']);
        $assigneeId = (int)(db_one($db,'SELECT id FROM employees WHERE company_id = ? AND (id = ? OR unique_id = ?) LIMIT 1',[$companyId,ctype_digit($assignee) ? (int)$assignee : 0,$assignee])['id'] ?? 0);
    }
    $canQueryAssignee = $assigneeId > 0 && can_issue_query($db,$viewer,'employee',$assigneeId);
?>
<section class="panel"><div class="panel-head"><h2>Queries about this task</h2><?php if ($canQueryAssignee): ?><a class="button small" href="/users/admin/query.php?action=new&amp;task=<?= $id ?>&amp;subject=employee:<?= $assigneeId ?>"><?= icon('flag') ?> Query assignee</a><?php endif; ?></div>
<?php if ($taskQueries): ?><ul class="query-list"><?php foreach ($taskQueries as $taskQuery): ?><li><span class="query-ref"><?= e($taskQuery['reference']) ?></span><div class="query-main"><a href="<?= e(query_url((int)$taskQuery['id'])) ?>"><?= e($taskQuery['title']) ?></a><small><?= e(date('j M Y',strtotime($taskQuery['created_at'] . ' UTC'))) ?></small></div><span class="badge <?= e(stage_class($taskQuery['status'])) ?>"><?= e(query_statuses()[$taskQuery['status']] ?? $taskQuery['status']) ?></span></li><?php endforeach; ?></ul><?php else: ?><div class="panel-body"><p class="subtle">No queries are recorded about this task.</p></div><?php endif; ?></section>
<?php endif; ?>
<?php if ($key === 'projects'):
    require_once __DIR__ . '/discipline.php';
    $viewer = viewer_identity($user);
    [$queryScope,$queryParams] = discipline_scope($viewer,'q');
    $projectQueries = db_rows($db,"SELECT q.id,q.reference,q.title,q.status,q.created_at FROM task_queries q WHERE q.project_id = ? AND $queryScope ORDER BY q.id DESC LIMIT 20",[$id,...$queryParams]);
    $projectCandidates = can_manage_project_queries($db,$viewer,(int)$id) ? project_query_candidates($db,$viewer,(int)$id) : [];
?>
<section class="panel"><div class="panel-head"><h2>Queries about this project</h2><?php if ($projectCandidates): ?><a class="button small" href="/users/admin/query.php?action=new&amp;project=<?= $id ?>"><?= icon('flag') ?> Query project team</a><?php endif; ?></div>
<?php if ($projectQueries): ?><ul class="query-list"><?php foreach ($projectQueries as $projectQuery): ?><li><span class="query-ref"><?= e($projectQuery['reference']) ?></span><div class="query-main"><a href="<?= e(query_url((int)$projectQuery['id'])) ?>"><?= e($projectQuery['title']) ?></a><small><?= e(date('j M Y',strtotime($projectQuery['created_at'] . ' UTC'))) ?></small></div><span class="badge <?= e(stage_class($projectQuery['status'])) ?>"><?= e(query_statuses()[$projectQuery['status']] ?? $projectQuery['status']) ?></span></li><?php endforeach; ?></ul><?php else: ?><div class="panel-body"><p class="subtle">No queries are recorded about this project.</p></div><?php endif; ?></section>
<?php endif; ?>
<?php if ($key === 'projects'):
    $milestoneStatus = trim((string)($record['milestoneStatus'] ?? ''));
    $hasMilestone = (bool)array_filter(['milestoneName','milestoneDate','milestoneDescription'],fn($name)=>trim((string)($record[$name] ?? '')) !== '')
        || ($milestoneStatus !== '' && $milestoneStatus !== 'not started');
?>
<section class="panel"><div class="panel-head"><h2>Milestone</h2><?php if ($hasMilestone && $milestoneStatus !== ''): ?><span class="badge"><?= e(ucfirst($milestoneStatus)) ?></span><?php endif; ?></div><div class="panel-body">
<?php if ($hasMilestone): ?><dl class="record-details">
<div><dt>Name</dt><dd><?= e(trim((string)($record['milestoneName'] ?? '')) ?: 'Unnamed milestone') ?></dd></div>
<div><dt>Date</dt><dd><?= e(trim((string)($record['milestoneDate'] ?? '')) ?: 'Not scheduled') ?></dd></div>
<div><dt>Status</dt><dd><?= e($milestoneStatus !== '' ? ucfirst($milestoneStatus) : 'Not provided') ?></dd></div>
<div class="wide"><dt>Description</dt><dd class="rich-text"><?= trim((string)($record['milestoneDescription'] ?? '')) !== '' ? rich_text_html((string)$record['milestoneDescription']) : 'Not provided' ?></dd></div>
</dl><?php else: ?><p class="subtle">No milestone has been added to this project yet.</p><?php if ($canEdit): ?><a class="button small secondary" href="?module=<?= e($key) ?>&amp;action=edit&amp;id=<?= $id ?>">Add milestone</a><?php endif; ?><?php endif; ?>
</div></section>
<?php endif; ?>
<?php if (in_array($key,['projects','tasks'],true)) require __DIR__ . '/github-panel.php'; ?>
<section class="panel"><div class="panel-head"><h2>Attachments</h2><span class="count-label"><?= count($files) ?> of 20</span></div><div class="panel-body">
<p class="subtle">Files are private to workspace members with access to this tool. Downloads are not scanned for viruses; open only files you trust.</p>
<?php if ($files): ?><ul class="attachment-list"><?php foreach ($files as $file): ?><li><div><a class="text-link" href="/api/attachments.php?id=<?= (int)$file['id'] ?>"><?= e($file['original_name']) ?></a><small><?= e(attachment_kind($file['media_type'])) ?> · <?= e(attachment_size((int)$file['size_bytes'])) ?> · <?= e(date('j M Y, H:i',strtotime($file['created_at'] . ' UTC'))) ?></small></div><?php if ($canEdit): ?><form method="post" action="/api/attachments.php" data-submit><?= csrf_field() ?><input type="hidden" name="module" value="<?= e($key) ?>"><input type="hidden" name="record_id" value="<?= $id ?>"><input type="hidden" name="id" value="<?= (int)$file['id'] ?>"><input type="hidden" name="action" value="remove"><input type="hidden" name="return_to_record" value="1"><button class="button small secondary" type="submit" aria-label="Remove <?= e($file['original_name']) ?>">Remove</button></form><?php endif; ?></li><?php endforeach; ?></ul><?php else: ?><p>No attachments yet.</p><?php endif; ?>
<?php if ($canEdit): ?><form method="post" action="/api/attachments.php" enctype="multipart/form-data" class="form-stack" data-submit><?= csrf_field() ?><input type="hidden" name="module" value="<?= e($key) ?>"><input type="hidden" name="record_id" value="<?= $id ?>"><input type="hidden" name="return_to_record" value="1"><input type="hidden" name="MAX_FILE_SIZE" value="<?= attachment_limit() ?>"><div class="field"><label for="attachment">Add a file</label><input id="attachment" type="file" name="attachment" accept="<?= e(attachment_accept()) ?>" required aria-describedby="attachment-help"><small id="attachment-help"><?= e(attachment_types_label()) ?>, up to <?= number_format(attachment_limit()/1048576,1) ?> MB each. Workspace limit: 100 MB.</small></div><div><button class="button" type="submit">Upload attachment</button></div></form><?php endif; ?>
</div></section>
