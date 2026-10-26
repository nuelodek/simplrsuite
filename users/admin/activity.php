<?php
require_once __DIR__ . '/../../app/auth.php';
require_once __DIR__ . '/../../app/view.php';
$db = database(); $user = admin_user($db);
$count = (int)db_one($db,'SELECT COUNT(*) AS total FROM workspace_audit WHERE company_id = ?',[$user['company_id']])['total'];
$pages = max(1,(int)ceil($count/50)); $page = min($pages,max(1,(int)($_GET['page'] ?? 1))); $offset = ($page-1)*50;
$rows = db_rows($db,"SELECT actor_role,actor_id,action,target_type,target_id,created_at FROM workspace_audit WHERE company_id = ? ORDER BY id DESC LIMIT 50 OFFSET $offset",[$user['company_id']]);
suite_start('Activity','activity',$user);
?>
<div class="page-heading"><div><span class="eyebrow">KNOW WHAT CHANGED</span><h1>Workspace activity</h1><p>Record saves, permission changes, settings and help requests made through the current workspace.</p></div></div>
<section class="panel"><div class="table-wrap"><table class="data-table"><caption class="sr-only">Workspace activity, newest first</caption><thead><tr><th>Time (UTC)</th><th>Actor</th><th>Action</th><th>Record</th></tr></thead><tbody><?php foreach ($rows as $row): ?><tr><td><?= e($row['created_at']) ?></td><td><?= e(ucfirst($row['actor_role'])) ?> #<?= (int)$row['actor_id'] ?></td><td><?= e(str_replace('.',' · ',$row['action'])) ?></td><td><?= e($row['target_type']) ?> #<?= e($row['target_id']) ?></td></tr><?php endforeach; ?><?php if (!$rows): ?><tr><td colspan="4">No activity has been recorded yet.</td></tr><?php endif; ?></tbody></table></div><div class="table-toolbar"><span>Page <?= $page ?> of <?= $pages ?></span><div class="form-actions"><?php if ($page>1): ?><a class="text-link" href="?page=<?= $page-1 ?>">Previous</a><?php endif; ?><?php if ($page<$pages): ?><a class="text-link" href="?page=<?= $page+1 ?>">Next</a><?php endif; ?></div></div></section><p class="subtle">Historical actions from earlier versions of the application are not reconstructed here.</p>
<?php suite_end(); ?>
