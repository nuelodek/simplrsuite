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
