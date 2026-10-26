<?php
require_once __DIR__ . '/../../app/recruiting.php';
$db = database(); $user = talent_user($db);
if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    require_post(); rate_limit($db,'talent-outreach-response',(string)$user['id'],30);
    $id = filter_var(input('id'),FILTER_VALIDATE_INT,['options'=>['min_range'=>1]]);
    $response = input('response');
    if (!$id || !in_array($response,['accept','decline'],true)) fail('Choose a valid response.',422);
    $db->begin_transaction();
    try {
        $current = db_one($db,'SELECT * FROM talent_outreach WHERE id = ? AND talent_account_id = ? FOR UPDATE',[$id,$user['id']]);
        if (!$current || $current['status'] !== 'pending' || ($response === 'accept' && $current['action_type'] !== 'hire_offer')) { $db->rollback(); fail('This invitation or offer is no longer awaiting a response.',409); }
        if (!hash_equals(record_version($current),input('version'))) { $db->rollback(); fail('This invitation or offer changed. Reload and review it again.',409); }
        if ($response === 'accept') {
            if (input('consent') !== '1') { $db->rollback(); fail('Confirm that you want to share your contact email and current professional profile with this employer.',422); }
            db_one($db,'SELECT id FROM talentprofile WHERE id = ? FOR UPDATE',[$user['id']]);
            $snapshot = candidate_snapshot($db,talent_user($db));
            db_run($db,"UPDATE talent_outreach SET status = 'accepted',profile_snapshot = ?,consented_at = UTC_TIMESTAMP(),responded_at = UTC_TIMESTAMP() WHERE id = ? AND talent_account_id = ?",[json_encode($snapshot,JSON_THROW_ON_ERROR),$id,$user['id']]);
            audit_event($db,(int)$current['company_id'],'talent.hire_offer_accepted','talent_outreach',(string)$id);
        } else {
            db_run($db,"UPDATE talent_outreach SET status = 'declined',responded_at = UTC_TIMESTAMP() WHERE id = ? AND talent_account_id = ?",[$id,$user['id']]);
            audit_event($db,(int)$current['company_id'],'talent.outreach_declined','talent_outreach',(string)$id);
        }
        $db->commit();
    } catch (Throwable $error) { $db->rollback(); throw $error; }
    flash($response === 'accept' ? 'Hire offer accepted. Your contact details and current profile were shared with the employer.' : 'Invitation or offer declined.');
    redirect('/users/talents/opportunities.php');
}
$rows = db_rows($db,"SELECT o.*,u.company_name,j.public_id FROM talent_outreach o JOIN users u ON u.company_id = o.company_id AND u.status = 'Verified' LEFT JOIN job_posts j ON j.id = o.job_id AND j.company_id = o.company_id WHERE o.talent_account_id = ? ORDER BY o.created_at DESC,o.id DESC",[$user['id']]);
$snapshot = candidate_snapshot($db,$user);
suite_start('Recruiter messages','opportunities',['company_name'=>$user['full_name'],'email'=>$user['email']]);
?>
<div class="page-heading"><div><h1>Invitations and offers</h1><p>Messages from employers who found your listed profile.</p></div><a class="button secondary" href="/jobs/">Browse roles</a></div>
<?php if (!$rows): ?><section class="panel empty-state"><?= icon('briefcase') ?><h2>No invitations or offers</h2><p>Employer invitations and hire offers will appear here.</p><a class="button" href="/users/talents/talentpool.php">Review my profile</a></section>
<?php else: ?><div class="tools-grid"><?php foreach ($rows as $row): ?><article class="panel talent-card"><span class="eyebrow"><?= e($row['company_name']) ?></span><h2><?= $row['action_type'] === 'invite' ? 'Invitation to apply' : 'Direct hire offer' ?></h2><h3><?= e($row['role_title']) ?></h3><p class="subtle"><?= e($row['created_at']) ?> UTC · <?= e(ucfirst($row['status'])) ?></p><?php if ($row['message'] !== ''): ?><p class="talent-copy"><?= nl2br(e($row['message'])) ?></p><?php endif; ?>
<?php if ($row['action_type'] === 'invite' && $row['status'] === 'pending'): ?><p><a class="button" href="/jobs/?job=<?= e($row['public_id'] ?? '') ?>">Review role and apply</a></p><form method="post" data-submit><?= csrf_field() ?><input type="hidden" name="id" value="<?= (int)$row['id'] ?>"><input type="hidden" name="version" value="<?= e(record_version($row)) ?>"><input type="hidden" name="response" value="decline"><button class="button secondary" type="submit">Decline invitation</button></form>
<?php elseif ($row['action_type'] === 'hire_offer' && $row['status'] === 'pending'): ?><section class="panel"><h3>Profile and contact details to be shared</h3><?php submitted_profile_view(['profile_snapshot'=>json_encode($snapshot,JSON_THROW_ON_ERROR),'candidate_note'=>'']); ?></section><div class="notice info">Accepting shares the contact email and professional profile shown above with <?= e($row['company_name']) ?> for this direct hire. Your profile stays under your control in the public directory.</div><form method="post" class="form-stack" data-submit><?= csrf_field() ?><input type="hidden" name="id" value="<?= (int)$row['id'] ?>"><input type="hidden" name="version" value="<?= e(record_version($row)) ?>"><input type="hidden" name="response" value="accept"><label class="check-label"><input type="checkbox" name="consent" value="1" required> I agree to share these details with <?= e($row['company_name']) ?> for this hire.</label><button class="button" type="submit">Accept hire offer</button></form><form method="post" data-submit><?= csrf_field() ?><input type="hidden" name="id" value="<?= (int)$row['id'] ?>"><input type="hidden" name="version" value="<?= e(record_version($row)) ?>"><input type="hidden" name="response" value="decline"><button class="button secondary" type="submit">Decline offer</button></form>
<?php elseif ($row['action_type'] === 'hire_offer' && $row['status'] === 'accepted'): ?><div class="notice success">You accepted this offer. The employer received the profile and contact details you approved.</div>
<?php endif; ?></article><?php endforeach; ?></div><?php endif; ?>
<?php suite_end(); ?>
