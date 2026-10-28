<?php
require_once __DIR__ . '/../../app/auth.php';
require_once __DIR__ . '/../../app/access.php';
require_once __DIR__ . '/../../app/view.php';
require_once __DIR__ . '/../../app/notifications.php';
$db = database(); $user = workspace_user($db); $viewer = viewer_identity($user);
$scope = [$viewer['company_id'],$viewer['role'],$viewer['id']];
if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    require_post();
    if (input('action') === 'open') {
        $id = filter_var(input('id'),FILTER_VALIDATE_INT,['options'=>['min_range'=>1]]);
        $item = $id ? db_one($db,'SELECT id, url FROM notifications WHERE id = ? AND company_id = ? AND recipient_role = ? AND recipient_id = ?',[$id,...$scope]) : null;
        if (!$item) fail('This alert could not be found.',404);
        db_run($db,'UPDATE notifications SET read_at = COALESCE(read_at, UTC_TIMESTAMP()) WHERE id = ? AND company_id = ? AND recipient_role = ? AND recipient_id = ?',[(int)$item['id'],...$scope]);
        redirect(local_path((string)$item['url'],'/users/admin/notifications.php'));
    } elseif (input('action') === 'read_all') {
        db_run($db,'UPDATE notifications SET read_at = UTC_TIMESTAMP() WHERE company_id = ? AND recipient_role = ? AND recipient_id = ? AND read_at IS NULL',$scope);
        flash('All alerts marked as read.');
    } elseif (input('action') === 'preferences') {
        $mode = input('email_mode');
        if (!in_array($mode,['instant','digest','off'],true)) fail('Choose how you want email alerts.',422);
        db_run($db,'INSERT INTO notification_preferences (company_id,recipient_role,recipient_id,email_mode) VALUES (?,?,?,?) ON DUPLICATE KEY UPDATE email_mode = VALUES(email_mode)',[...$scope,$mode]);
        flash('Alert preferences saved.');
    } else fail('Choose a valid action.',422);
    redirect(local_path(input('return_to'),'/users/admin/notifications.php'));
}
$unreadOnly = ($_GET['show'] ?? '') === 'unread';
$total = (int)db_one($db,'SELECT COUNT(*) AS count FROM notifications WHERE company_id = ? AND recipient_role = ? AND recipient_id = ?' . ($unreadOnly ? ' AND read_at IS NULL' : ''),$scope)['count'];
$pages = max(1,(int)ceil($total/40)); $page = min($pages,max(1,(int)($_GET['page'] ?? 1)));
$items = notification_list($db,$viewer,40,($page-1)*40,$unreadOnly);
$mode = notification_email_mode($db,...$scope);
$unread = notification_unread_count($db,$viewer);
suite_start('Alerts','alerts',$user);
?>
<div class="page-heading"><div><h1>Alerts</h1><p>Task reminders, queries, penalties and other updates meant for you.</p></div><?php if ($unread): ?><form method="post"><?= csrf_field() ?><input type="hidden" name="action" value="read_all"><button class="button secondary small" type="submit"><?= icon('check') ?> Mark all as read</button></form><?php endif; ?></div>
<div class="alerts-layout"><section class="panel"><nav class="stage-filter" aria-label="Show"><a href="?"<?= !$unreadOnly ? ' aria-current="true"' : '' ?>>All</a><a href="?show=unread"<?= $unreadOnly ? ' aria-current="true"' : '' ?>>Unread <span><?= $unread ?></span></a></nav>
<?php if (!$items): ?><div class="empty-state compact"><?= icon('bell') ?><h3><?= $unreadOnly ? 'You’re all caught up' : 'No alerts yet' ?></h3><p>Reminders about your tasks, queries and other updates will appear here.</p></div>
<?php else: ?><ul class="alert-list"><?php foreach ($items as $item): ?><li class="<?= $item['read_at'] ? '' : 'unread' ?><?= $item['is_formal'] ? ' formal' : '' ?>"><span class="activity-icon"><?= icon(notification_icon($item['kind'])) ?></span><div><form method="post" class="alert-open-form"><?= csrf_field() ?><input type="hidden" name="action" value="open"><input type="hidden" name="id" value="<?= (int)$item['id'] ?>"><button type="submit" class="alert-open"><?= e($item['title']) ?></button></form><?php if ($item['body'] !== ''): ?><p><?= e($item['body']) ?></p><?php endif; ?><small><?= e(time_ago($item['created_at'])) ?><?= $item['is_formal'] ? ' · Formal notice' : '' ?></small></div><?php if (!$item['read_at']): ?><span class="unread-dot" aria-label="Unread"></span><?php endif; ?></li><?php endforeach; ?></ul><?php endif; ?>
<?php if ($pages > 1): ?><div class="table-toolbar"><span class="count-label">Page <?= $page ?> of <?= $pages ?></span><div class="form-actions"><?php if ($page > 1): ?><a class="button small secondary" href="?<?= e(http_build_query(['show'=>$unreadOnly ? 'unread' : null,'page'=>$page-1])) ?>">Newer</a><?php endif; ?><?php if ($page < $pages): ?><a class="button small secondary" href="?<?= e(http_build_query(['show'=>$unreadOnly ? 'unread' : null,'page'=>$page+1])) ?>">Older</a><?php endif; ?></div></div><?php endif; ?></section>
<aside><section class="panel"><div class="panel-head"><h2>Email alerts</h2></div><div class="panel-body"><form method="post" class="form-stack" data-submit><?= csrf_field() ?><input type="hidden" name="action" value="preferences"><?php foreach (['instant'=>['As they happen','Each alert is emailed straight away.'],'digest'=>['Daily summary','One email a day listing your new alerts.'],'off'=>['In Simplr only','Routine alerts stay in Simplr.']] as $value=>[$label,$help]): ?><label class="choice"><input type="radio" name="email_mode" value="<?= $value ?>"<?= $mode === $value ? ' checked' : '' ?>><span><strong><?= e($label) ?></strong><small><?= e($help) ?></small></span></label><?php endforeach; ?><p class="subtle compact-note">Queries and penalties are formal notices, so they are always emailed.</p><button class="button small" type="submit">Save preference</button></form></div></section></aside></div>
<?php suite_end(); ?>
