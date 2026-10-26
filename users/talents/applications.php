<?php
require_once __DIR__ . '/../../app/recruiting.php';
$db = database(); $user = talent_user($db);
$id = filter_var($_GET['id'] ?? null,FILTER_VALIDATE_INT,['options'=>['min_range'=>1]]);
$item = $id ? application_submission($db,$id,(int)$user['id'],true) : null;
if (isset($_GET['id']) && !$item) fail('This application could not be found in your account.',404);
if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    require_post(); rate_limit($db,'application-withdraw',(string)$user['id'],30);
    if (!$item || input('action') !== 'withdraw' || input('confirm') !== '1') fail('Confirm the application you want to withdraw.',422);
    $db->begin_transaction();
    try {
        $current = application_submission($db,$id,(int)$user['id'],true,true);
        if (!$current || !hash_equals(record_version($current),input('version'))) { $db->rollback(); fail('This application changed. Reload and review its latest status.',409); }
        if (!$current['withdrawn_at']) {
            db_run($db,'UPDATE application_submissions SET withdrawn_at = UTC_TIMESTAMP() WHERE application_id = ? AND account_id = ?',[$id,$user['id']]);
            db_run($db,"UPDATE applications SET status = 'closed' WHERE id = ? AND company_id = ?",[$id,$current['company_id']]);
            audit_event($db,(int)$current['company_id'],'application.withdrawn','applications',(string)$id);
        }
        $db->commit();
    } catch (Throwable $error) { $db->rollback(); throw $error; }
    flash('Application withdrawn. The employer can no longer download your resume through Simplr.'); redirect('/users/talents/applications.php?id='.$id);
}
$page = max(1,min(10000,(int)($_GET['page'] ?? 1))); $offset = ($page-1)*20;
if (!$item) {
    $total = (int)db_one($db,'SELECT COUNT(*) AS total FROM application_submissions WHERE account_id = ?',[$user['id']])['total'];
    $items = db_rows($db,'SELECT a.id,a.position_applied_for,a.status,s.consented_at,s.withdrawn_at,u.company_name FROM application_submissions s JOIN applications a ON a.id = s.application_id AND a.company_id = s.company_id JOIN users u ON u.company_id = s.company_id WHERE s.account_id = ? ORDER BY s.application_id DESC LIMIT 20 OFFSET '.$offset,[$user['id']]);
} else $company = db_one($db,'SELECT company_name FROM users WHERE company_id = ?',[$item['company_id']]);
suite_start('My applications','applications',['company_name'=>$user['full_name'],'email'=>$user['email']]);
?>
<div class="page-heading"><div><span class="eyebrow">YOUR NEXT CHAPTER</span><h1><?= $item ? e($item['position_applied_for']) : 'My applications' ?></h1><p><?= $item ? e($company['company_name'] ?? 'Employer') : 'Keep track of every role you have applied for.' ?></p></div><a class="button secondary" href="/jobs/">Browse roles</a></div>
<?php if ($item): ?>
<p><a class="text-link" href="/users/talents/applications.php">← All my applications</a></p>
<section class="panel"><div class="panel-head"><h2>Application status</h2><span class="badge"><?= $item['withdrawn_at'] ? 'Withdrawn' : e(ucfirst($item['status'])) ?></span></div><div class="panel-body"><p>Submitted <?= e($item['consented_at']) ?> UTC. Status is updated by the employer in Simplr.</p><?php if ($item['withdrawn_at']): ?><div class="notice info">You withdrew this application on <?= e($item['withdrawn_at']) ?> UTC. You cannot reapply to this role. The employer retains your submitted record and any copies previously downloaded.</div><?php endif; ?></div></section>
<section class="panel"><div class="panel-head"><h2>Your submitted profile</h2></div><div class="panel-body"><p class="subtle">This is the copy shared when you applied. Later changes to your profile do not change this record.</p><?php submitted_profile_view($item); ?><?php if ($item['resume_attachment_id']): ?><p><a class="button secondary" href="/api/attachments.php?id=<?= (int)$item['resume_attachment_id'] ?>">Download my submitted resume</a></p><?php endif; ?></div></section>
<?php if (!$item['withdrawn_at']): ?><section class="panel record-form"><h2>Withdraw application</h2><p>Withdrawal stops review and future resume downloads by the employer. It does not erase your submitted record or copies already downloaded. You cannot apply again to this role.</p><form method="post" class="form-stack" data-submit><?= csrf_field() ?><input type="hidden" name="action" value="withdraw"><input type="hidden" name="version" value="<?= e(record_version($item)) ?>"><label class="check-label"><input type="checkbox" name="confirm" value="1" required> I want to withdraw this application.</label><div><button class="button secondary" type="submit">Withdraw application</button></div></form></section><?php endif; ?>
<?php elseif (!$items): ?><section class="panel empty-state"><?= icon('briefcase') ?><h2>Your applications will appear here.</h2><p>Find a role that suits your skills and apply with your talent profile.</p><a class="button" href="/jobs/">Explore open roles</a></section>
<?php else: ?><div class="tools-grid"><?php foreach ($items as $row): ?><article class="panel talent-card"><span class="eyebrow"><?= e($row['company_name']) ?></span><h2><?= e($row['position_applied_for']) ?></h2><p><span class="badge"><?= $row['withdrawn_at'] ? 'Withdrawn' : e(ucfirst($row['status'])) ?></span></p><p class="subtle">Applied <?= e($row['consented_at']) ?> UTC</p><a class="text-link" href="?id=<?= (int)$row['id'] ?>">View application →</a></article><?php endforeach; ?></div><nav class="pagination" aria-label="Application pages"><?php if ($page>1): ?><a class="button secondary" href="?page=<?= $page-1 ?>">Previous</a><?php endif; ?><span>Page <?= $page ?></span><?php if ($offset+20<$total): ?><a class="button secondary" href="?page=<?= $page+1 ?>">Next</a><?php endif; ?></nav><?php endif; suite_end(); ?>
