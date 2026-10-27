<?php
require_once __DIR__ . '/../../app/auth.php';
require_once __DIR__ . '/../../app/view.php';
require_once __DIR__ . '/../../app/modules.php';
require_once __DIR__ . '/../../app/recruiting.php';
require_once __DIR__ . '/../../app/audiences.php';
$db = database(); $user = workspace_user($db); require_module_access($db,$user,'jobs');
$companyId = (int)$user['company_id'];
$canRecruit = module_access($db,$user,'jobs') === 'edit';

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    require_post();
    require_module_access($db,$user,'jobs',true);
    rate_limit($db,'talent-directory',(string)$companyId,40);
    $action = input('action');
    $talentId = filter_var(input('talent_id'),FILTER_VALIDATE_INT,['options'=>['min_range'=>1]]);
    if (!$talentId || !in_array($action,['shortlist','remove_shortlist','invite','hire_offer'],true)) fail('Choose a valid talent action.',422);
    if ($action === 'shortlist' || $action === 'remove_shortlist') {
        $db->begin_transaction();
        try {
            $talent = db_one($db,"SELECT a.id FROM talentprofile a JOIN talent_portfolios p ON p.account_id = a.id WHERE a.id = ? AND a.status = 'Verified' AND p.visibility = 'listed' FOR UPDATE",[$talentId]);
            if (!$talent) { $db->rollback(); fail('This profile is no longer listed.',409); }
            if ($action === 'shortlist') {
                db_run($db,'INSERT INTO talent_shortlists (company_id,talent_account_id,created_by) VALUES (?,?,?) ON DUPLICATE KEY UPDATE created_by = VALUES(created_by)',[$companyId,$talentId,(int)$user['id']]);
                audit_event($db,$companyId,'talent.shortlisted','talentprofile',(string)$talentId);
                $message = 'Talent saved to your shortlist.';
            } else {
                db_run($db,'DELETE FROM talent_shortlists WHERE company_id = ? AND talent_account_id = ?',[$companyId,$talentId]);
                audit_event($db,$companyId,'talent.removed_from_shortlist','talentprofile',(string)$talentId);
                $message = 'Talent removed from your shortlist.';
            }
            $db->commit();
        } catch (Throwable $error) { $db->rollback(); throw $error; }
        flash($message);
        redirect('/users/admin/talent-directory.php?q=' . rawurlencode(input('q')));
    }

    $db->begin_transaction();
    try {
        $talent = db_one($db,"SELECT a.id,a.full_name,a.email FROM talentprofile a JOIN talent_portfolios p ON p.account_id = a.id WHERE a.id = ? AND a.status = 'Verified' AND p.visibility = 'listed' FOR UPDATE",[$talentId]);
        if (!$talent) { $db->rollback(); fail('This profile is no longer listed.',409); }
        $job = null; $roleTitle = ''; $jobId = null;
        if ($action === 'invite') {
            $jobId = filter_var(input('job_id'),FILTER_VALIDATE_INT,['options'=>['min_range'=>1]]);
            $job = $jobId ? db_one($db,'SELECT id,company_id,public_id,title,status,closes_at FROM job_posts WHERE id = ? AND company_id = ? FOR UPDATE',[$jobId,$companyId]) : null;
            if (!$job || !job_accepts_applications($db,$job)) { $db->rollback(); fail('Choose one of your published roles that is still accepting applications.',422); }
            $roleTitle = $job['title'];
        } else {
            $roleTitle = input('role_title');
            if ($roleTitle === '' || mb_strlen($roleTitle) > 150) { $db->rollback(); fail('Enter a role title of up to 150 characters.',422); }
        }
        $message = input('message');
        if (mb_strlen($message) > 1000) { $db->rollback(); fail('Keep the personal note within 1,000 characters.',422); }
        $duplicate = db_one($db,'SELECT id FROM talent_outreach WHERE company_id = ? AND talent_account_id = ? AND action_type = ? AND status = \'pending\' AND ' . ($jobId === null ? 'role_title = ?' : 'job_id = ?') . ' LIMIT 1',[$companyId,$talentId,$action === 'invite' ? 'invite' : 'hire_offer',$jobId ?? $roleTitle]);
        if ($duplicate) { $db->rollback(); fail('A matching invitation or offer is already pending.',409); }
        db_run($db,'INSERT INTO talent_outreach (company_id,talent_account_id,action_type,job_id,role_title,message,created_by) VALUES (?,?,?,?,?,?,?)',[$companyId,$talentId,$action === 'invite' ? 'invite' : 'hire_offer',$jobId,$roleTitle,$message,(int)$user['id']]);
        $outreachId = (int)$db->insert_id;
        audit_event($db,$companyId,$action === 'invite' ? 'talent.invited' : 'talent.hire_offer_sent','talent_outreach',(string)$outreachId,['talent_account_id'=>(string)$talentId,'role_title'=>$roleTitle]);
        $db->commit();
    } catch (Throwable $error) { $db->rollback(); throw $error; }
    $url = audience_url('talents','/users/talents/opportunities.php');
    $subject = $action === 'invite' ? 'You are invited to apply on Simplr' : 'You have a hire offer on Simplr';
    $intro = $action === 'invite'
        ? $user['company_name'] . ' invited you to apply for ' . $roleTitle . '.'
        : $user['company_name'] . ' sent you a hire offer for ' . $roleTitle . '. Review it and choose whether to accept in your Simplr talent account.';
    $sent = send_account_mail($talent['email'],$subject,$intro . "\n\nSign in to review: " . $url);
    flash($sent
        ? ($action === 'invite' ? 'Invitation sent. The talent can choose whether to apply.' : 'Hire offer sent. The talent must accept before their contact details are shared.')
        : 'The invitation or offer was saved, but email delivery failed. The talent can still review it in their Simplr account.',$sent ? 'success' : 'warning');
    redirect('/users/admin/talent-directory.php?q=' . rawurlencode(input('q')));
}

$search = is_string($_GET['q'] ?? null) ? mb_substr(trim($_GET['q']),0,100) : '';
$shortlistOnly = ($_GET['shortlist'] ?? '') === '1';
$page = max(1, min(10000, (int)($_GET['page'] ?? 1))); $offset = ($page - 1) * 12;
$where = "p.visibility = 'listed' AND a.status = 'Verified' AND EXISTS (SELECT 1 FROM users u WHERE u.status = 'Verified')"; $args = [];
if ($shortlistOnly) { $where .= ' AND EXISTS (SELECT 1 FROM talent_shortlists s WHERE s.company_id = ? AND s.talent_account_id = a.id)'; $args[] = $companyId; }
if ($search !== '') { $where .= ' AND (a.full_name LIKE ? OR p.headline LIKE ? OR p.skills LIKE ? OR p.location LIKE ?)'; array_push($args,...array_fill(0,4,'%' . $search . '%')); }
$total = (int)db_one($db, 'SELECT COUNT(*) AS total FROM talent_portfolios p JOIN talentprofile a ON a.id = p.account_id WHERE ' . $where, $args)['total'];
$profiles = db_rows($db, 'SELECT a.id AS account_id,a.full_name,a.industry,p.headline,p.location,p.experience,p.skills,p.bio,p.portfolio_url,p.availability FROM talent_portfolios p JOIN talentprofile a ON a.id = p.account_id WHERE ' . $where . ' ORDER BY p.updated_at DESC, p.account_id DESC LIMIT 12 OFFSET ' . $offset, $args);
$shortlistIds = array_fill_keys(array_map('intval',array_column(db_rows($db,'SELECT talent_account_id FROM talent_shortlists WHERE company_id = ?',[$companyId]),'talent_account_id')),true);
$outreachRows = db_rows($db,'SELECT talent_account_id,action_type,status,role_title FROM talent_outreach WHERE company_id = ? AND status = \'pending\' ORDER BY id DESC',[$companyId]);
$pendingByTalent = [];
foreach ($outreachRows as $outreach) { $pendingByTalent[(int)$outreach['talent_account_id']][] = $outreach; }
$jobs = $canRecruit ? db_rows($db,"SELECT id,title FROM job_posts WHERE company_id = ? AND status = 'published' AND (closes_at IS NULL OR closes_at > UTC_TIMESTAMP()) ORDER BY title",[$companyId]) : [];
$hires = db_rows($db,"SELECT role_title,profile_snapshot,consented_at FROM talent_outreach WHERE company_id = ? AND action_type = 'hire_offer' AND status = 'accepted' ORDER BY responded_at DESC,id DESC",[$companyId]);
suite_start('Talent directory','recruiting',$user);
?>
<div class="page-heading"><div><h1>Talent directory</h1><p>Explore professional profiles that people have chosen to share with employers.</p></div><div class="form-actions"><a class="button" href="/users/admin/workspace.php?module=jobs">Job postings</a><a class="button secondary" href="/users/admin/workspace.php?module=applications">Application records</a></div></div>
<nav class="module-tabs" aria-label="Talent directory views"><a href="/users/admin/talent-directory.php" <?= !$shortlistOnly ? 'class="active" aria-current="page"' : '' ?>>All listed talent</a><a href="/users/admin/talent-directory.php?shortlist=1" <?= $shortlistOnly ? 'class="active" aria-current="page"' : '' ?>>Shortlist</a></nav>
<form method="get" class="talent-search"><label class="sr-only" for="q">Search talent profiles</label><?= icon('search') ?><input type="search" id="q" name="q" value="<?= e($search) ?>" placeholder="Search name, skills, role, or location"><button class="button" type="submit">Search</button></form>
<p class="subtle"><?= $total ?> <?= $total === 1 ? 'profile' : 'profiles' ?> · Email addresses stay private unless a talent accepts a hire offer.</p>
<?php if (!$canRecruit): ?><div class="notice info">You have view-only recruiting access. Ask your workspace owner for edit access to shortlist or contact talent.</div><?php endif; ?>
<?php if (!$profiles): ?><section class="panel empty-state"><?= icon('users') ?><h2><?= $shortlistOnly ? 'Your shortlist is empty.' : ($search !== '' ? 'No matching profiles.' : 'No profiles listed yet') ?></h2><p><?= $shortlistOnly ? 'Shortlist a profile to add it here.' : ($search !== '' ? 'Try a different skill, role, or location.' : 'Profiles appear here when people choose to list them.') ?></p></section><?php else: ?><div class="tools-grid">
<?php foreach ($profiles as $profile): $talentId=(int)$profile['account_id']; $saved=isset($shortlistIds[$talentId]); ?>
<article class="panel talent-card"><span class="eyebrow"><?= e($profile['industry']) ?></span><h2><?= e($profile['full_name']) ?></h2><h3><?= e($profile['headline']) ?></h3><p class="subtle"><?= e($profile['location']) ?><?= $profile['availability'] ? ' · ' . e($profile['availability']) : '' ?></p><p><?= e($profile['experience']) ?></p><p class="talent-copy"><?= e($profile['skills']) ?></p><details><summary>About this person’s work</summary><p class="talent-copy"><?= e($profile['bio']) ?></p></details><?php if ($profile['portfolio_url'] !== '' && strtolower(parse_url($profile['portfolio_url'], PHP_URL_SCHEME) ?? '') === 'https'): ?><a class="text-link" href="<?= e($profile['portfolio_url']) ?>" target="_blank" rel="noopener noreferrer">View portfolio ↗</a><?php endif; ?>
<?php foreach ($pendingByTalent[$talentId] ?? [] as $pending): ?><p class="subtle"><span class="badge"><?= $pending['action_type'] === 'invite' ? 'Invitation pending' : 'Hire offer pending' ?></span> <?= e($pending['role_title']) ?></p><?php endforeach; ?>
<?php if ($canRecruit): ?><div class="form-actions"><form method="post"><?= csrf_field() ?><input type="hidden" name="q" value="<?= e($search) ?>"><input type="hidden" name="talent_id" value="<?= $talentId ?>"><input type="hidden" name="action" value="<?= $saved ? 'remove_shortlist' : 'shortlist' ?>"><button class="button secondary" type="submit"><?= $saved ? 'Remove from shortlist' : 'Shortlist' ?></button></form></div>
<?php if ($jobs): ?><form method="post" class="form-stack"><?= csrf_field() ?><input type="hidden" name="q" value="<?= e($search) ?>"><input type="hidden" name="talent_id" value="<?= $talentId ?>"><input type="hidden" name="action" value="invite"><div class="field"><label for="invite-job-<?= $talentId ?>">Invite to apply</label><select id="invite-job-<?= $talentId ?>" name="job_id" required><option value="">Choose a published role</option><?php foreach ($jobs as $job): ?><option value="<?= (int)$job['id'] ?>"><?= e($job['title']) ?></option><?php endforeach; ?></select></div><button class="button secondary" type="submit">Send invitation</button></form><?php else: ?><p class="subtle">Publish a role to invite talent to apply.</p><?php endif; ?>
<form method="post" class="form-stack"><?= csrf_field() ?><input type="hidden" name="q" value="<?= e($search) ?>"><input type="hidden" name="talent_id" value="<?= $talentId ?>"><input type="hidden" name="action" value="hire_offer"><div class="field"><label for="offer-title-<?= $talentId ?>">Direct hire offer</label><input id="offer-title-<?= $talentId ?>" name="role_title" maxlength="150" placeholder="Role title" required></div><div class="field"><label for="offer-note-<?= $talentId ?>">Note <span class="subtle">(optional)</span></label><textarea id="offer-note-<?= $talentId ?>" name="message" maxlength="1000" rows="2"></textarea></div><button class="button" type="submit">Send hire offer</button><small>The person must accept before their contact details are shared with your company.</small></form><?php endif; ?></article><?php endforeach; ?></div><?php endif; ?>
<nav class="pagination" aria-label="Talent directory pages"><?php if ($page > 1): ?><a class="button secondary" href="?q=<?= urlencode($search) ?>&amp;shortlist=<?= $shortlistOnly ? '1' : '0' ?>&amp;page=<?= $page-1 ?>">Previous</a><?php endif; ?><span>Page <?= $page ?></span><?php if ($offset + 12 < $total): ?><a class="button secondary" href="?q=<?= urlencode($search) ?>&amp;shortlist=<?= $shortlistOnly ? '1' : '0' ?>&amp;page=<?= $page+1 ?>">Next</a><?php endif; ?></nav>
<?php if ($hires): ?><section class="panel"><div class="panel-heading"><div><h2>Accepted hire offers</h2><p class="subtle">These people accepted your offer and consented to share the profile snapshot below.</p></div></div><?php foreach ($hires as $hire): ?><article class="talent-card"><h3><?= e($hire['role_title']) ?></h3><p class="subtle">Offer accepted <?= e($hire['consented_at']) ?> UTC</p><?php submitted_profile_view(['profile_snapshot'=>$hire['profile_snapshot'],'candidate_note'=>'']); ?></article><?php endforeach; ?></section><?php endif; ?>
<?php suite_end(); ?>
