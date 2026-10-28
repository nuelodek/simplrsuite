<?php
require_once __DIR__ . '/../app/access.php';
require_once __DIR__ . '/../app/view.php';
$db = database(); $user = workspace_user($db); $companyId = (int)$user['company_id'];
$role = $_SESSION['auth_role']; $owner = $role === 'admin'; $actorId = (int)$user['id'];
$scope = 'company_id = ?' . ($owner ? '' : ' AND author_role = ? AND author_id = ?');
$params = $owner ? [$companyId] : [$companyId,$role,$actorId];
$id = filter_var($_GET['id'] ?? null,FILTER_VALIDATE_INT,['options'=>['min_range'=>1]]) ?: null;
$ticket = $id ? db_one($db,"SELECT * FROM workspace_tickets WHERE id = ? AND $scope",[$id,...$params]) : null;
if (isset($_GET['id']) && !$ticket) fail('This request could not be found in your workspace.',404);
$categories = ['Account access','Tools & projects','Workspace question','Other']; $error = '';
if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    require_post(); $body = input('body'); $subject = input('subject'); $category = input('category');
    foreach ($_FILES as $file) if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE) fail('Attachments are not supported in workspace help yet.',422);
    if (mb_strlen($body)>4000 || (!$ticket && $body === '')) $error = 'Describe your request in 1–4,000 characters.';
    if (!$ticket && ($subject === '' || mb_strlen($subject)>180 || !in_array($category,$categories,true))) $error = 'Add a subject of up to 180 characters and choose a category.';
    $state = input('state') ?: ($ticket['state'] ?? 'open');
    if (!in_array($state,['open','resolved'],true)) $error = 'Choose open or resolved.';
    if ($ticket && $body === '' && (!$owner || $state === $ticket['state'])) $error = 'Add a reply or update the request status.';
    if (!$error) {
        rate_limit($db,'workspace-help',(string)$companyId . ':' . $role . ':' . $actorId,40);
        $db->begin_transaction();
        try {
            if ($ticket) {
                $current = db_one($db,"SELECT * FROM workspace_tickets WHERE id = ? AND $scope FOR UPDATE",[$id,...$params]);
                if (!$current) { $db->rollback(); fail('This request is no longer available.',404); }
                if ((string)$current['version'] !== input('version')) { $db->rollback(); fail('This request changed. Reload to read the latest replies before posting.',409); }
                if (!$owner && $state !== $current['state']) { $db->rollback(); fail('Only your workspace administrator can change the request status.',403); }
                db_run($db,'UPDATE workspace_tickets SET state = ?, version = version + 1 WHERE id = ? AND company_id = ?',[$state,$id,$companyId]);
            } else {
                db_run($db,'INSERT INTO workspace_tickets (company_id,author_role,author_id,subject,category) VALUES (?,?,?,?,?)',[$companyId,$role,$actorId,$subject,$category]);
                $id = (int)$db->insert_id;
            }
            if ($body !== '') db_run($db,'INSERT INTO workspace_ticket_comments (ticket_id,company_id,author_role,author_id,body) VALUES (?,?,?,?,?)',[$id,$companyId,$role,$actorId,$body]);
            audit_event($db,$companyId,$ticket ? 'help.updated' : 'help.created','ticket',(string)$id);
            $db->commit();
        } catch (Throwable $exception) { $db->rollback(); throw $exception; }
        flash($ticket ? 'Request updated.' : 'Your request is saved for your workspace administrator.'); redirect('/users/help.php?id=' . $id);
    }
    http_response_code(422);
}
suite_start('Workspace help','help',$user);
?>
<div class="page-heading"><div><h1><?= $ticket ? e($ticket['subject']) : 'Workspace help' ?></h1><p><?= $owner ? 'Review requests from your team.' : 'Ask your workspace administrator for help. Only you and the administrator can read your request.' ?></p></div><?php if ($ticket): ?><a class="button secondary" href="/users/help.php">All requests</a><?php endif; ?></div>
<?php if ($error): ?><div class="notice error" role="alert"><?= e($error) ?></div><?php endif; ?>
<?php if ($ticket):
$replyCount = (int)db_one($db,'SELECT COUNT(*) AS total FROM workspace_ticket_comments WHERE ticket_id = ? AND company_id = ?',[$id,$companyId])['total'];
$replyPages = max(1,(int)ceil($replyCount/50)); $replyPage = min($replyPages,max(1,(int)($_GET['replies'] ?? $replyPages))); $replyOffset = ($replyPage-1)*50;
$comments = db_rows($db,"SELECT author_role,author_id,body,created_at FROM workspace_ticket_comments WHERE ticket_id = ? AND company_id = ? ORDER BY id LIMIT 50 OFFSET $replyOffset",[$id,$companyId]); ?>
<section class="panel"><div class="panel-head"><h2>Request #<?= $id ?></h2><span class="badge"><?= e($ticket['state']) ?></span></div><div class="panel-body event-list"><?php foreach (array_slice($comments,0,200) as $comment): ?><article class="event-card"><strong><?= $comment['author_role'] === $role && (int)$comment['author_id'] === $actorId ? 'You' : ($comment['author_role'] === 'admin' ? 'Workspace administrator' : e(ucfirst($comment['author_role'])) . ' #' . (int)$comment['author_id']) ?></strong><p class="preserve-lines"><?= e($comment['body']) ?></p><small class="subtle"><?= e($comment['created_at']) ?> UTC</small></article><?php endforeach; ?></div></section>
<?php if ($replyPages>1): ?><div class="form-actions"><?php if ($replyPage>1): ?><a class="text-link" href="?id=<?= $id ?>&amp;replies=<?= $replyPage-1 ?>">Earlier replies</a><?php endif; ?><span>Replies page <?= $replyPage ?> of <?= $replyPages ?></span><?php if ($replyPage<$replyPages): ?><a class="text-link" href="?id=<?= $id ?>&amp;replies=<?= $replyPage+1 ?>">Later replies</a><?php endif; ?></div><?php endif; ?><section class="panel"><div class="panel-body"><form method="post" class="form-stack" data-submit><?= csrf_field() ?><input name="version" type="hidden" value="<?= (int)$ticket['version'] ?>"><div class="field"><label for="reply">Add a reply</label><textarea id="reply" name="body" maxlength="4000" <?= $owner ? '' : 'required' ?>><?= e(input('body')) ?></textarea></div><?php if ($owner): ?><div class="field"><label for="state">Status</label><select id="state" name="state"><option value="open" <?= $ticket['state'] === 'open' ? 'selected' : '' ?>>Open</option><option value="resolved" <?= $ticket['state'] === 'resolved' ? 'selected' : '' ?>>Resolved</option></select></div><?php endif; ?><div><button class="button" type="submit">Save reply</button></div></form></div></section>
<?php else:
    $count = (int)db_one($db,"SELECT COUNT(*) AS total FROM workspace_tickets WHERE $scope",$params)['total'];
    $pages = max(1,(int)ceil($count/25)); $page = min($pages,max(1,(int)($_GET['page'] ?? 1))); $offset = ($page-1)*25;
    $tickets = db_rows($db,"SELECT id,subject,category,state,updated_at FROM workspace_tickets WHERE $scope ORDER BY updated_at DESC,id DESC LIMIT 25 OFFSET $offset",$params);
?>
<div class="dashboard-columns"><section class="panel"><div class="panel-head"><h2><?= $owner ? 'Team requests' : 'Your requests' ?></h2><span class="badge"><?= $count ?></span></div><?php if (!$tickets): ?><div class="empty-state"><h3>Nothing waiting here.</h3><p>New requests and their replies will appear here.</p></div><?php else: ?><ul class="record-list"><?php foreach ($tickets as $item): ?><li><div><a class="text-link" href="?id=<?= (int)$item['id'] ?>"><?= e($item['subject']) ?></a><small><?= e($item['category']) ?> · <?= e($item['updated_at']) ?> UTC</small></div><span class="badge"><?= e($item['state']) ?></span></li><?php endforeach; ?></ul><?php endif; ?><?php if ($pages>1): ?><div class="panel-body form-actions"><?php if ($page>1): ?><a class="text-link" href="?page=<?= $page-1 ?>">Previous</a><?php endif; ?><span>Page <?= $page ?> of <?= $pages ?></span><?php if ($page<$pages): ?><a class="text-link" href="?page=<?= $page+1 ?>">Next</a><?php endif; ?></div><?php endif; ?></section>
<section class="panel"><div class="panel-head"><h2>New request</h2></div><div class="panel-body"><form method="post" class="form-stack" data-submit><?= csrf_field() ?><div class="field"><label for="subject">Subject</label><input id="subject" name="subject" maxlength="180" required value="<?= e(input('subject')) ?>"></div><div class="field"><label for="category">Category</label><select id="category" name="category" required><?php foreach ($categories as $item): ?><option <?= input('category') === $item ? 'selected' : '' ?>><?= e($item) ?></option><?php endforeach; ?></select></div><div class="field"><label for="body">How can we help?</label><textarea id="body" name="body" maxlength="4000" required><?= e(input('body')) ?></textarea><small>Keep passwords and payment details out of your request. This sends no external email.</small></div><button type="submit" class="button">Create request</button></form></div></section></div>
<?php endif; suite_end(); ?>
