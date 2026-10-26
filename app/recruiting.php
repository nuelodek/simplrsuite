<?php
declare(strict_types=1);
require_once __DIR__ . '/talent.php';
require_once __DIR__ . '/attachments.php';
require_once __DIR__ . '/audiences.php';

function public_job(mysqli $db, string $publicId, bool $lock = false): ?array {
    if (!preg_match('/^[a-f0-9]{32}$/D',$publicId)) return null;
    return db_one($db,"SELECT j.*, u.company_name FROM job_posts j JOIN users u ON u.company_id = j.company_id AND u.status = 'Verified' WHERE j.public_id = ? AND j.published_at IS NOT NULL AND j.status IN ('published','closed')" . ($lock ? ' FOR UPDATE' : ''),[$publicId]);
}
function job_accepts_applications(mysqli $db, array $job): bool {
    return $job['status'] === 'published' && (!$job['closes_at'] || $job['closes_at'] > gmdate('Y-m-d H:i:s')) && billing_module_active($db,(int)$job['company_id'],'jobs');
}
function candidate_snapshot(mysqli $db, array $user): array {
    $profile = db_one($db,'SELECT headline,location,experience,skills,bio,portfolio_url,availability FROM talent_portfolios WHERE account_id = ?',[$user['id']]);
    return ['full_name'=>$user['full_name'],'email'=>$user['email'],'industry'=>$user['industry'],...($profile ?? [])];
}
function application_submission(mysqli $db, int $id, int $scope, bool $candidate = false, bool $lock = false): ?array {
    $column = $candidate ? 'account_id' : 'company_id';
    return db_one($db,"SELECT a.*, s.job_id,s.account_id,s.profile_snapshot,s.candidate_note,s.resume_attachment_id,s.consented_at,s.withdrawn_at FROM applications a JOIN application_submissions s ON s.application_id = a.id AND s.company_id = a.company_id WHERE a.id = ? AND s.$column = ?" . ($lock ? ' FOR UPDATE' : ''),[$id,$scope]);
}
function application_interviews(mysqli $db, int $applicationId): array {
    return db_rows($db,'SELECT * FROM application_interviews WHERE application_id = ? ORDER BY scheduled_at,id',[$applicationId]);
}
function interview_datetime_utc(string $localTime, string $timezone): ?string {
    if (!in_array($timezone,DateTimeZone::listIdentifiers(),true) || !preg_match('/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}$/D',$localTime)) return null;
    $zone = new DateTimeZone($timezone);
    $date = DateTimeImmutable::createFromFormat('!Y-m-d\TH:i',$localTime,$zone);
    $errors = DateTimeImmutable::getLastErrors();
    if (!$date || ($errors && ($errors['warning_count'] || $errors['error_count'])) || $date->format('Y-m-d\TH:i') !== $localTime) return null;
    $utc = $date->setTimezone(new DateTimeZone('UTC'));
    if ($utc <= new DateTimeImmutable('+5 minutes',new DateTimeZone('UTC'))) return null;
    return $utc->format('Y-m-d H:i:s');
}
function interview_time_label(array $interview): string {
    $date = new DateTimeImmutable($interview['scheduled_at'],new DateTimeZone('UTC'));
    return $date->setTimezone(new DateTimeZone($interview['timezone']))->format('D, j M Y \a\t H:i T');
}
function create_application_interview(mysqli $db, array $application, array $user): void {
    $title = input('interview_title');
    $timezone = input('interview_timezone');
    $scheduledAt = interview_datetime_utc(input('interview_datetime'),$timezone);
    $meetingType = input('meeting_type');
    $location = input('meeting_location');
    $url = input('meeting_url');
    $agenda = input('interview_agenda');
    if ($title === '' || mb_strlen($title) > 150 || !$scheduledAt || mb_strlen($agenda) > 4000) { $db->rollback(); fail('Enter a valid interview title, future date and time, timezone, and agenda (up to 4,000 characters).',422); }
    if (!in_array($meetingType,['remote','in-person'],true)) { $db->rollback(); fail('Choose a valid interview format.',422); }
    if ($meetingType === 'remote') {
        $parts = parse_url($url);
        if (!filter_var($url,FILTER_VALIDATE_URL) || !is_array($parts) || strtolower($parts['scheme'] ?? '') !== 'https' || isset($parts['user']) || isset($parts['pass']) || mb_strlen($url) > 2048) { $db->rollback(); fail('Remote interviews need a valid HTTPS meeting link.',422); }
        $location = '';
    } else {
        if ($location === '' || mb_strlen($location) > 255) { $db->rollback(); fail('Enter an interview location of up to 255 characters.',422); }
        $url = '';
    }
    db_run($db,'INSERT INTO application_interviews (application_id,company_id,account_id,title,scheduled_at,timezone,meeting_type,meeting_location,meeting_url,agenda,created_by) VALUES (?,?,?,?,?,?,?,?,?,?,?)',[$application['id'],$application['company_id'],$application['account_id'],$title,$scheduledAt,$timezone,$meetingType,$location,$url,$agenda,$user['id']]);
    $id = (string)$db->insert_id;
    audit_event($db,(int)$application['company_id'],'application.interview_scheduled','applications',(string)$application['id'],['interview_id'=>$id,'scheduled_at'=>$scheduledAt,'meeting_type'=>$meetingType]);
}
function update_application_interview(mysqli $db, array $application, int $interviewId, string $status, string $version): void {
    if (!in_array($status,['accepted','declined','cancelled'],true)) { $db->rollback(); fail('Choose a valid interview response.',422); }
    $interview = db_one($db,'SELECT * FROM application_interviews WHERE id = ? AND application_id = ? AND company_id = ? FOR UPDATE',[$interviewId,$application['id'],$application['company_id']]);
    if (!$interview || !hash_equals(record_version($interview),$version)) { $db->rollback(); fail('This interview changed. Reload and review its latest details.',409); }
    if ($status === 'cancelled') {
        if (!in_array($interview['status'],['pending','accepted'],true)) { $db->rollback(); fail('This interview can no longer be cancelled.',409); }
        db_run($db,"UPDATE application_interviews SET status = 'cancelled' WHERE id = ?",[$interviewId]);
        audit_event($db,(int)$application['company_id'],'application.interview_cancelled','applications',(string)$application['id'],['interview_id'=>(string)$interviewId]);
        return;
    }
    if ($interview['account_id'] !== $application['account_id'] || $interview['status'] !== 'pending') { $db->rollback(); fail('This interview is no longer awaiting your response.',409); }
    if ($status === 'accepted' && new DateTimeImmutable($interview['scheduled_at'],new DateTimeZone('UTC')) <= new DateTimeImmutable('now',new DateTimeZone('UTC'))) { $db->rollback(); fail('This interview has already started. Decline it or contact the employer.',409); }
    db_run($db,'UPDATE application_interviews SET status = ?, responded_at = UTC_TIMESTAMP() WHERE id = ?',[$status,$interviewId]);
    audit_event($db,(int)$application['company_id'],'application.interview_responded','applications',(string)$application['id'],['interview_id'=>(string)$interviewId,'response'=>$status]);
}
function application_interviews_view(array $interviews, bool $candidate, string $applicationVersion, bool $actionsAllowed = true): void {
    echo '<section class="panel"><div class="panel-head"><h2>Interviews</h2></div><div class="panel-body">';
    if (!$interviews) { echo '<p class="subtle">No interviews have been scheduled.</p>'; }
    foreach ($interviews as $interview) {
        $upcoming = new DateTimeImmutable($interview['scheduled_at'],new DateTimeZone('UTC')) > new DateTimeImmutable('now',new DateTimeZone('UTC'));
        echo '<article class="panel talent-card"><div class="page-heading"><div><h3>' . e($interview['title']) . '</h3><p>' . e(interview_time_label($interview)) . '</p></div><span class="badge">' . e(ucfirst($interview['status'])) . '</span></div>';
        echo '<p>' . ($interview['meeting_type'] === 'remote' ? 'Remote' : 'In person') . '</p>';
        if ($interview['meeting_type'] === 'remote' && $interview['meeting_url'] !== '') echo '<p><a class="text-link" href="' . e($interview['meeting_url']) . '" target="_blank" rel="noopener noreferrer">Open meeting link ↗</a></p>';
        if ($interview['meeting_location'] !== '') echo '<p>' . e($interview['meeting_location']) . '</p>';
        if ($interview['agenda'] !== '') echo '<p class="talent-copy">' . nl2br(e($interview['agenda'])) . '</p>';
        if ($actionsAllowed && $candidate && $interview['status'] === 'pending') {
            echo '<form method="post" class="form-stack">' . csrf_field() . '<input type="hidden" name="version" value="' . e($applicationVersion) . '"><input type="hidden" name="action" value="interview_response"><input type="hidden" name="interview_id" value="' . (int)$interview['id'] . '"><input type="hidden" name="interview_version" value="' . e(record_version($interview)) . '"><div class="form-actions">' . ($upcoming ? '<button class="button" type="submit" name="response" value="accepted">Accept interview</button>' : '') . '<button class="button secondary" type="submit" name="response" value="declined">Decline</button></div></form>';
        } elseif ($actionsAllowed && !$candidate && in_array($interview['status'],['pending','accepted'],true)) {
            echo '<form method="post">' . csrf_field() . '<input type="hidden" name="version" value="' . e($applicationVersion) . '"><input type="hidden" name="action" value="interview_cancel"><input type="hidden" name="interview_id" value="' . (int)$interview['id'] . '"><input type="hidden" name="interview_version" value="' . e(record_version($interview)) . '"><button class="button secondary" type="submit">Cancel interview</button></form>';
        }
        echo '</article>';
    }
    echo '</div></section>';
}
function submitted_profile_view(array $submission): void {
    $snapshot = json_decode($submission['profile_snapshot'],true,512,JSON_THROW_ON_ERROR);
    echo '<dl class="record-details">';
    foreach (['full_name'=>'Name','email'=>'Contact email','industry'=>'Industry','headline'=>'Professional headline','location'=>'Location','experience'=>'Experience','skills'=>'Skills','bio'=>'About their work','portfolio_url'=>'Portfolio link','availability'=>'Availability'] as $key=>$label) {
        $value = $snapshot[$key] ?? '';
        if ($value === '') continue;
        echo '<div><dt>' . e($label) . '</dt><dd class="talent-copy">';
        if ($key === 'portfolio_url' && filter_var($value,FILTER_VALIDATE_URL) && strtolower(parse_url($value,PHP_URL_SCHEME) ?? '') === 'https') echo '<a class="text-link" href="' . e($value) . '" target="_blank" rel="noopener noreferrer">' . e($value) . ' ↗</a>';
        else echo e($value);
        echo '</dd></div>';
    }
    echo '</dl>';
    if ($submission['candidate_note'] !== '') echo '<h3>Application note</h3><p class="talent-copy">' . e($submission['candidate_note']) . '</p>';
}
function submit_job_application(mysqli $db, array $user, array $job, array $snapshot, ?array $upload): int {
    $path = null; $db->begin_transaction();
    try {
        // Shared company lock serializes attachment quotas. Job and account locks
        // prevent closing a role or changing a profile during submission.
        db_one($db,'SELECT id FROM users WHERE company_id = ? ORDER BY id LIMIT 1 FOR UPDATE',[$job['company_id']]);
        $current = public_job($db,$job['public_id'],true);
        if (!$current || !job_accepts_applications($db,$current)) { $db->rollback(); fail('This role is no longer accepting applications.',409); }
        if (!hash_equals(record_version($current),input('job_version'))) { $db->rollback(); fail('This job changed. Reload the application and review the latest details.',409); }
        db_one($db,'SELECT id FROM talentprofile WHERE id = ? FOR UPDATE',[$user['id']]);
        $snapshot = candidate_snapshot($db,talent_user($db));
        if (!hash_equals(record_version($snapshot),input('profile_version'))) { $db->rollback(); fail('Your profile changed. Reload the application and review what you are sharing.',409); }
        if (db_one($db,'SELECT application_id FROM application_submissions WHERE job_id = ? AND account_id = ?',[$job['id'],$user['id']])) { $db->rollback(); fail('You have already applied for this role. See My applications for its status.',409); }
        if ($upload) {
            $used = (int)db_one($db,'SELECT COALESCE(SUM(size_bytes),0) AS total FROM record_attachments WHERE company_id = ?',[$job['company_id']])['total'];
            if ($used + $upload['size'] > 100*1024*1024) { $db->rollback(); fail('This employer cannot accept more files right now. Submit without a resume or try again later.',422); }
        }
        db_run($db,"INSERT INTO applications (company_id,applicant_name,email,position_applied_for,status) VALUES (?,?,?,?,'new')",[$job['company_id'],$snapshot['full_name'],$snapshot['email'],$current['title']]);
        $id = (int)$db->insert_id; $attachmentId = null;
        if ($upload) {
            if (!is_dir(attachment_directory()) && !mkdir(attachment_directory(),0700,true)) throw new RuntimeException('Attachment storage unavailable');
            $stored = bin2hex(random_bytes(32)); $path = attachment_directory() . '/' . $stored;
            if (!move_uploaded_file($upload['tmp_name'],$path)) throw new RuntimeException('Resume could not be saved');
            db_run($db,"INSERT INTO record_attachments (company_id,module_key,record_id,original_name,storage_name,media_type,size_bytes,content_hash) VALUES (?,'applications',?,?,?,?,?,?)",[$job['company_id'],$id,$upload['name'],$stored,$upload['type'],$upload['size'],hash_file('sha256',$path)]);
            $attachmentId = (int)$db->insert_id;
        }
        db_run($db,'INSERT INTO application_submissions (application_id,company_id,job_id,account_id,profile_snapshot,candidate_note,resume_attachment_id,consented_at) VALUES (?,?,?,?,?,?,?,UTC_TIMESTAMP())',[$id,$job['company_id'],$job['id'],$user['id'],json_encode($snapshot,JSON_THROW_ON_ERROR),input('candidate_note'),$attachmentId]);
        audit_event($db,(int)$job['company_id'],'application.submitted','applications',(string)$id);
        $db->commit(); return $id;
    } catch (Throwable $error) { $db->rollback(); if ($path && is_file($path)) unlink($path); throw $error; }
}
