<?php
require_once __DIR__ . '/../../app/auth.php';
require_once __DIR__ . '/../../app/view.php';
require_once __DIR__ . '/../../app/activity.php';
$db = database(); $user = admin_user($db); $companyId = (int)$user['company_id'];
$count = (int)db_one($db,'SELECT COUNT(*) AS total FROM workspace_audit WHERE company_id = ?',[$companyId])['total'];
$pages = max(1,(int)ceil($count/50)); $page = min($pages,max(1,(int)($_GET['page'] ?? 1))); $offset = ($page-1)*50;
$entries = activity_entries($db,$companyId,activity_rows($db,$companyId,50,$offset),(int)$user['id']);
$days = [];
foreach ($entries as $entry) {
    $date = date('Y-m-d',strtotime($entry['time'] . ' UTC'));
    $label = match ($date) { date('Y-m-d') => 'Today', date('Y-m-d',strtotime('-1 day')) => 'Yesterday', default => date('l, j F Y',strtotime($date)) };
    $days[$label][] = $entry;
}
suite_start('Activity','activity',$user);
?>
<div class="page-heading"><div><h1>Workspace activity</h1><p>Record changes, access updates, hiring steps, settings and help requests across your workspace.</p></div><span class="count-label"><?= number_format($count) ?> <?= $count === 1 ? 'event' : 'events' ?></span></div>
<?php if (!$entries): ?><section class="panel"><div class="empty-state"><?= icon('pulse') ?><h3>No activity yet</h3><p>When your team adds or updates records, it will appear here.</p></div></section><?php endif; ?>
<?php foreach ($days as $label=>$group): ?><section class="panel activity-day"><div class="panel-head"><h2><?= e($label) ?></h2><span class="count-label"><?= count($group) ?></span></div><ul class="activity-feed"><?php foreach ($group as $entry): ?><li><span class="activity-icon"><?= icon($entry['icon']) ?></span><div><p><?php if ($entry['url']): ?><a href="<?= e($entry['url']) ?>"><?= e($entry['text']) ?></a><?php else: ?><?= e($entry['text']) ?><?php endif; ?></p><time datetime="<?= e($entry['time']) ?>Z" title="<?= e($entry['time']) ?> UTC"><?= e(time_ago($entry['time'])) ?> · <?= e(date('H:i',strtotime($entry['time'] . ' UTC'))) ?></time></div></li><?php endforeach; ?></ul></section><?php endforeach; ?>
<?php if ($pages > 1): ?><div class="table-toolbar pager"><span class="count-label">Page <?= $page ?> of <?= $pages ?></span><div class="form-actions"><?php if ($page>1): ?><a class="button small secondary" href="?page=<?= $page-1 ?>">Newer</a><?php endif; ?><?php if ($page<$pages): ?><a class="button small secondary" href="?page=<?= $page+1 ?>">Older</a><?php endif; ?></div></div><?php endif; ?>
<?php suite_end(); ?>
