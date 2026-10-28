<?php
require_once __DIR__ . '/../../app/auth.php';
require_once __DIR__ . '/../../app/view.php';
require_once __DIR__ . '/../../app/discipline.php';
$db = database(); $user = workspace_user($db); $viewer = viewer_identity($user); $companyId = $viewer['company_id'];
$filter = in_array($_GET['show'] ?? '',['open','closed','all'],true) ? $_GET['show'] : 'open';
[$queryScope,$queryParams] = discipline_scope($viewer,'q');
[$penaltyScope,$penaltyParams] = discipline_scope($viewer,'p');
$statusFilter = ['open'=>" AND q.status IN ('awaiting_response','answered','no_response','escalated')",'closed'=>" AND q.status IN ('closed','withdrawn')",'all'=>''][$filter];
$queries = db_rows($db,"SELECT q.* FROM task_queries q WHERE $queryScope$statusFilter ORDER BY FIELD(q.status,'answered','no_response','awaiting_response','escalated','closed','withdrawn'), q.id DESC LIMIT 200",$queryParams);
$penalties = db_rows($db,"SELECT p.*, q.reference, q.title FROM penalties p JOIN task_queries q ON q.id = p.query_id AND q.company_id = p.company_id WHERE $penaltyScope" . ($filter === 'open' ? " AND p.status IN ('proposed','active')" : ($filter === 'closed' ? " AND p.status IN ('rejected','withdrawn','expired')" : '')) . ' ORDER BY p.id DESC LIMIT 200',$penaltyParams);
$attention = [];
if ($viewer['role'] === 'admin') {
    $proposed = (int)db_one($db,"SELECT COUNT(*) AS count FROM penalties WHERE company_id = ? AND status = 'proposed'",[$companyId])['count'];
    $appeals = (int)db_one($db,"SELECT COUNT(*) AS count FROM penalties WHERE company_id = ? AND status = 'active' AND appealed_at IS NOT NULL AND appeal_outcome IS NULL",[$companyId])['count'];
    if ($proposed) $attention[] = $proposed . ' ' . ($proposed === 1 ? 'penalty awaits' : 'penalties await') . ' your approval';
    if ($appeals) $attention[] = $appeals . ' ' . ($appeals === 1 ? 'appeal awaits' : 'appeals await') . ' your decision';
}
$toDecide = count(array_filter($queries,fn($query)=>(in_array($query['status'],['answered','no_response'],true) || query_response_overdue($query)) && ($viewer['role'] === 'admin' || is_issuer($viewer,$query))));
if ($toDecide) $attention[] = $toDecide . ' ' . ($toDecide === 1 ? 'query needs' : 'queries need') . ' a decision';
$toAnswer = count(array_filter($queries,fn($query)=>is_subject($viewer,$query) && $query['status'] === 'awaiting_response'));
if ($toAnswer) $attention[] = 'You have ' . $toAnswer . ' ' . ($toAnswer === 1 ? 'query' : 'queries') . ' to answer';
$toAcknowledge = count(array_filter($penalties,fn($penalty)=>is_subject($viewer,$penalty) && $penalty['status'] === 'active' && !$penalty['acknowledged_at']));
if ($toAcknowledge) $attention[] = $toAcknowledge . ' ' . ($toAcknowledge === 1 ? 'penalty needs' : 'penalties need') . ' your acknowledgement';
if ($viewer['role'] !== 'admin') {
    $handoffs = (int)db_one("SELECT COUNT(*) AS count FROM penalty_handoffs h JOIN penalties p ON p.id = h.penalty_id AND p.company_id = h.company_id WHERE h.company_id = ? AND h.assignee_role = ? AND h.assignee_id = ? AND h.status = 'pending' AND p.status = 'active'",[$companyId,$viewer['role'],$viewer['id']])['count'];
    if ($handoffs) $attention[] = $handoffs . ' ' . ($handoffs === 1 ? 'penalty handoff needs' : 'penalty handoffs need') . ' your action';
}
$canIssue = (bool)query_candidates($db,$viewer);
$names = [];
$name = function (string $role, int $id) use ($db,$companyId,&$names): string { return $names[$role . ':' . $id] ??= ($role === 'admin' ? 'Workspace owner' : person_name($db,$companyId,$role,$id)); };
suite_start('Queries & penalties','queries',$user);
?>
<div class="page-heading"><div><h1><?= $viewer['role'] === 'employee' ? 'My queries' : 'Queries & penalties' ?></h1><p><?= $viewer['role'] === 'employee' ? 'Formal queries you have received and any penalties that followed. Respond before the deadline and keep your own record.' : 'Ask for a written explanation within a set time, decide fairly, and keep a clear record. Only the workspace owner approves penalties.' ?></p></div><?php if ($canIssue): ?><a class="button" href="/users/admin/query.php?action=new"><?= icon('flag') ?> Issue a query</a><?php endif; ?></div>
<?php if ($attention): ?><div class="attention-strip"><?= icon('alert') ?><span><?= e(implode(' · ',$attention)) ?></span></div><?php endif; ?>
<nav class="stage-filter standalone" aria-label="Show"><?php foreach (['open'=>'Open','closed'=>'Closed','all'=>'All'] as $value=>$label): ?><a href="?show=<?= $value ?>"<?= $filter === $value ? ' aria-current="true"' : '' ?>><?= $label ?></a><?php endforeach; ?></nav>
<section class="panel"><div class="panel-head"><h2>Queries</h2><span class="count-label"><?= count($queries) ?></span></div>
<?php if (!$queries): ?><div class="empty-state compact"><?= icon('flag') ?><h3>No <?= $filter === 'all' ? '' : $filter . ' ' ?>queries</h3><p><?= $canIssue ? 'Issue a query from here, or from an overdue task with “Query assignee”.' : 'Nothing needs your attention.' ?></p></div>
<?php else: ?><ul class="query-list"><?php foreach ($queries as $query): $queryStatus = query_response_overdue($query) ? 'no_response' : $query['status']; [$dueLabel,$dueTone] = discipline_due_label($query['respond_by']); ?><li><span class="query-ref"><?= e($query['reference']) ?></span><div class="query-main"><a href="<?= e(query_url((int)$query['id'])) ?>"><?= e($query['title']) ?></a><small><?= is_subject($viewer,$query) ? 'From ' . e($name($query['issued_by_role'],(int)$query['issued_by_id'])) : 'To ' . e($name($query['subject_role'],(int)$query['subject_id'])) . ' · from ' . e($name($query['issued_by_role'],(int)$query['issued_by_id'])) ?> · <?= e(date('j M Y',strtotime($query['created_at'] . ' UTC'))) ?><?php if ($queryStatus === 'awaiting_response'): ?> · <span class="due <?= e($dueTone) ?>"><?= e($dueLabel) ?></span><?php endif; ?></small></div><span class="badge <?= e(stage_class($queryStatus)) ?>"><?= e(query_statuses()[$queryStatus] ?? $queryStatus) ?></span></li><?php endforeach; ?></ul><?php endif; ?></section>
<section class="panel"><div class="panel-head"><h2>Penalties</h2><span class="count-label"><?= count($penalties) ?></span></div>
<?php if (!$penalties): ?><div class="empty-state compact"><?= icon('alert') ?><h3>No <?= $filter === 'all' ? '' : $filter . ' ' ?>penalties</h3><p>Penalties can only follow a query, and the workspace owner approves each one.</p></div>
<?php else: ?><div class="table-wrap"><table class="data-table"><thead><tr><th>Penalty</th><th>Person</th><th>Query</th><th>Status</th><th>Expires</th></tr></thead><tbody><?php foreach ($penalties as $penalty): ?><tr><td><a class="row-title" href="<?= e(query_url((int)$penalty['query_id'])) ?>"><?= e(penalty_types()[$penalty['penalty_type']] ?? $penalty['penalty_type']) ?></a><?= $penalty['amount'] !== null ? '<br><small class="subtle">' . e(($penalty['currency'] !== '' ? $penalty['currency'] . ' ' : '') . number_format((float)$penalty['amount'],2)) . ' recorded</small>' : '' ?></td><td><?= e($name($penalty['subject_role'],(int)$penalty['subject_id'])) ?></td><td><?= e($penalty['reference']) ?></td><td><span class="badge <?= e(stage_class($penalty['status'] === 'proposed' ? 'pending' : $penalty['status'])) ?>"><?= e(penalty_statuses()[$penalty['status']] ?? $penalty['status']) ?></span><?= $penalty['appealed_at'] && !$penalty['appeal_outcome'] ? ' <span class="badge pending">Appealed</span>' : '' ?></td><td><?= $penalty['expires_on'] ? e(date('j M Y',strtotime($penalty['expires_on']))) : '<span class="subtle">—</span>' ?></td></tr><?php endforeach; ?></tbody></table></div><?php endif; ?></section>
<p class="subtle">Queries and penalties are visible only to the person concerned, their manager, the person who issued the query, the workspace owner, and staff assigned to a penalty handoff.</p>
<?php suite_end(); ?>
