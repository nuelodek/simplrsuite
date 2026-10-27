<?php
require_once __DIR__ . '/../app/attachments.php';
$db = database();
$isCandidate = ($_SESSION['auth_role'] ?? '') === 'talent';
if ($isCandidate) { require_once __DIR__ . '/../app/talent.php'; $user = talent_user($db); }
else { $user = workspace_user($db); $companyId = (int)$user['company_id']; }
if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'GET') {
    $id = filter_var($_GET['id'] ?? null,FILTER_VALIDATE_INT,['options'=>['min_range'=>1]]);
    $file = !$id ? null : ($isCandidate
        ? db_one($db,"SELECT f.* FROM record_attachments f JOIN application_submissions s ON s.resume_attachment_id = f.id AND s.company_id = f.company_id AND s.application_id = f.record_id WHERE f.id = ? AND f.module_key = 'applications' AND s.account_id = ?",[$id,$user['id']])
        : db_one($db,'SELECT * FROM record_attachments WHERE id = ? AND company_id = ?',[$id,$companyId]));
    if (!$file) fail('This attachment could not be found in your workspace.',404);
    if (!$isCandidate) {
        attachment_record($db,$user,$file['module_key'],(int)$file['record_id']);
        if (db_one($db,'SELECT application_id FROM application_submissions WHERE resume_attachment_id = ? AND company_id = ? AND withdrawn_at IS NOT NULL',[$id,$companyId])) fail('This candidate withdrew their application. Resume access has ended.',403);
    }
    if (!preg_match('/^[a-f0-9]{64}$/D',$file['storage_name'])) fail('This attachment is unavailable.',404);
    $path = attachment_directory() . '/' . $file['storage_name'];
    if (!is_file($path)) fail('This attachment is unavailable. Contact your workspace administrator.',404);
    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename="simplr-attachment-' . $id . '"; filename*=UTF-8\'\'' . rawurlencode($file['original_name']));
    header('Content-Security-Policy: sandbox'); header('Content-Length: ' . filesize($path));
    header('Cache-Control: no-store');
    session_write_close(); readfile($path); exit;
}
if ($isCandidate) fail('Use the job application form to submit your resume.',403);
require_post();
$key = input('module'); $recordId = filter_var(input('record_id'),FILTER_VALIDATE_INT,['options'=>['min_range'=>1]]);
if (!$recordId) fail('Choose a record before uploading.',422);
require_module_access($db,$user,$key,true);
attachment_record($db,$user,$key,$recordId);
if (input('action') === 'remove') {
    $id = filter_var(input('id'),FILTER_VALIDATE_INT,['options'=>['min_range'=>1]]);
    $db->begin_transaction();
    try {
        db_one($db,'SELECT id FROM users WHERE company_id = ? ORDER BY id LIMIT 1 FOR UPDATE',[$companyId]);
        attachment_record($db,$user,$key,$recordId,true);
        $file = $id ? db_one($db,'SELECT * FROM record_attachments WHERE id = ? AND company_id = ? AND module_key = ? AND record_id = ? FOR UPDATE',[$id,$companyId,$key,$recordId]) : null;
        if (!$file || !preg_match('/^[a-f0-9]{64}$/D',$file['storage_name'])) { $db->rollback(); fail('This attachment could not be found in your workspace.',404); }
        if (db_one($db,'SELECT application_id FROM application_submissions WHERE resume_attachment_id = ? AND company_id = ?',[$id,$companyId])) { $db->rollback(); fail('Candidate-submitted resumes are preserved with their application and cannot be removed here.',409); }
        $path = attachment_directory() . '/' . $file['storage_name'];
        if (is_file($path) && !unlink($path)) throw new RuntimeException('Attachment could not be removed');
        db_run($db,'DELETE FROM record_attachments WHERE id = ? AND company_id = ?',[$id,$companyId]);
        audit_event($db,$companyId,'attachment.removed',$key,(string)$recordId);
        $db->commit();
    } catch (Throwable $error) { $db->rollback(); throw $error; }
    attachment_result(['ok'=>true,'message'=>'Attachment removed.']);
}
if (!in_array(input('action'),['','upload'],true)) fail('Choose a valid attachment action.',422);
[$validated,$error] = validate_attachment_upload($_FILES['attachment'] ?? null);
if ($error) attachment_result(['ok'=>false,'error'=>$error],422);
[$id,$error] = store_record_attachment($db,$user,$key,$recordId,$validated);
if ($error) attachment_result(['ok'=>false,'error'=>$error],422);
attachment_result(['ok'=>true,'id'=>$id,'message'=>'Attachment added.'],201);
