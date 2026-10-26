<?php
require_once __DIR__ . '/../../app/recruiting.php';
$db = database(); $publicId = is_string($_GET['job'] ?? null) ? $_GET['job'] : '';
$job = public_job($db,$publicId);
if (!$job) fail('This job is unavailable. Browse current roles at /jobs/.',404);
if (($_SESSION['auth_role'] ?? '') !== 'talent') { $_SESSION['pending_job'] = $publicId; redirect('/users/talents/talentlogin.php'); }
$user = talent_user($db); $snapshot = candidate_snapshot($db,$user); $errors = [];
$existing = db_one($db,'SELECT application_id FROM application_submissions WHERE job_id = ? AND account_id = ?',[$job['id'],$user['id']]);
$open = job_accepts_applications($db,$job); $ready = !empty($snapshot['headline']);
if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    require_post(); rate_limit($db,'job-application',(string)$user['id'],20);
    if ($existing) fail('You have already applied for this role. See My applications for its status.',409);
    if (!$open) fail('This role is no longer accepting applications.',409);
    if (!$ready) $errors[] = 'Save your professional headline on My profile before applying.';
    if (input('consent') !== '1') $errors[] = 'Confirm that you want to share these details with this employer.';
    if (mb_strlen(input('candidate_note'))>4000) $errors[] = 'Keep your application note within 4,000 characters.';
    $upload = null;
    if (isset($_FILES['resume']) && ($_FILES['resume']['error'] ?? null) !== UPLOAD_ERR_NO_FILE) { [$upload,$error] = validate_attachment_upload($_FILES['resume'],true); if ($error) $errors[] = $error; }
    if (!$errors) {
        $id = submit_job_application($db,$user,$job,$snapshot,$upload);
        flash('Application submitted. You can track the employer’s updates here.');
        redirect('/users/talents/applications.php?id='.$id);
    }
    http_response_code(422);
}
suite_start('Apply for a role','jobs',['company_name'=>$user['full_name'],'email'=>$user['email']]);
?>
<a class="text-link" href="/jobs/?job=<?= e($publicId) ?>">← View job details</a><div class="page-heading"><div><span class="eyebrow"><?= e($job['company_name']) ?></span><h1>Apply for <?= e($job['title']) ?>.</h1><p>Review your profile and choose what else to share.</p></div></div>
<?php if ($errors): ?><div class="notice error" role="alert"><?php foreach ($errors as $error): ?><p><?= e($error) ?></p><?php endforeach; ?></div><?php endif; ?>
<?php if ($existing): ?><section class="panel empty-state"><h2>You have already applied.</h2><p>Each talent account can submit one application per role, including withdrawn applications.</p><a class="button" href="/users/talents/applications.php?id=<?= (int)$existing['application_id'] ?>">View my application</a></section>
<?php elseif (!$open): ?><div class="notice info">This role is no longer accepting applications.</div>
<?php elseif (!$ready): ?><section class="panel empty-state"><h2>Introduce your work first.</h2><p>Add your professional headline and save your profile before applying. Your directory profile can stay private.</p><a class="button" href="/users/talents/talentpool.php?job=<?= e($publicId) ?>">Complete my profile</a></section>
<?php else: ?>
<section class="panel"><div class="panel-head"><h2>Profile shared with this employer</h2><a class="text-link" href="/users/talents/talentpool.php?job=<?= e($publicId) ?>">Edit profile</a></div><div class="panel-body"><?php submitted_profile_view(['profile_snapshot'=>json_encode($snapshot),'candidate_note'=>'']); ?></div></section>
<section class="panel record-form"><form method="post" enctype="multipart/form-data" class="form-stack" data-submit><?= csrf_field() ?><input type="hidden" name="job_version" value="<?= e($_SERVER['REQUEST_METHOD'] === 'POST' ? input('job_version') : record_version($job)) ?>"><input type="hidden" name="profile_version" value="<?= e($_SERVER['REQUEST_METHOD'] === 'POST' ? input('profile_version') : record_version($snapshot)) ?>"><input type="hidden" name="MAX_FILE_SIZE" value="<?= attachment_limit() ?>">
<div class="field"><label for="candidate_note">Why this role? <span class="subtle">(optional)</span></label><textarea id="candidate_note" name="candidate_note" maxlength="4000" rows="5"><?= e(input('candidate_note')) ?></textarea></div>
<div class="field"><label for="resume">Resume <span class="subtle">(optional)</span></label><input type="file" id="resume" name="resume" accept=".pdf" aria-describedby="resume-help"><small id="resume-help">PDF only, up to <?= number_format(attachment_limit()/1048576,1) ?> MB. Your resume is private to you and authorized reviewers at this company.</small></div>
<div class="notice info">Submitting shares a copy of the profile above, including your contact email, with <?= e($job['company_name']) ?>. Later profile edits do not change this application. Withdrawing stops review and future resume downloads by the employer; the employer retains your submitted record and any copies already downloaded. Directory visibility is separate.</div>
<label class="check-label"><input type="checkbox" name="consent" value="1" required <?= input('consent') === '1' ? 'checked' : '' ?>> I agree to share these details and any attached resume with <?= e($job['company_name']) ?> for this application.</label>
<div class="form-actions"><button class="button" type="submit">Submit application <?= icon('arrow') ?></button><a class="text-link" href="/jobs/?job=<?= e($publicId) ?>">Cancel</a></div></form></section>
<?php endif; suite_end(); ?>
