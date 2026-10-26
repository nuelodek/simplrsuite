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
$upload = $validated; $size = $validated['size']; $type = $validated['type']; $name = $validated['name'];
$stored = bin2hex(random_bytes(32)); $path = attachment_directory() . '/' . $stored; $moved = false;
$db->begin_transaction();
try {
    // A company lock makes quotas and repeated uploads deterministic.
    db_one($db,'SELECT id FROM users WHERE company_id = ? ORDER BY id LIMIT 1 FOR UPDATE',[$companyId]);
    attachment_record($db,$user,$key,$recordId,true);
    $count = (int)db_one($db,'SELECT COUNT(*) AS total FROM record_attachments WHERE company_id = ? AND module_key = ? AND record_id = ?',[$companyId,$key,$recordId])['total'];
    $total = (int)db_one($db,'SELECT COALESCE(SUM(size_bytes),0) AS total FROM record_attachments WHERE company_id = ?',[$companyId])['total'];
    if ($count >= 20 || $total + $size > 100*1024*1024) { $db->rollback(); attachment_result(['ok'=>false,'error'=>'This workspace allows 20 attachments per record and 100 MB in total. Remove an unneeded attachment before uploading.'],422); }
    if (!is_dir(attachment_directory()) && !mkdir(attachment_directory(),0700,true)) throw new RuntimeException('Attachment storage unavailable');
    if (!move_uploaded_file($upload['tmp_name'],$path)) throw new RuntimeException('Attachment could not be saved');
    $moved = true;
    db_run($db,'INSERT INTO record_attachments (company_id,module_key,record_id,original_name,storage_name,media_type,size_bytes,content_hash) VALUES (?,?,?,?,?,?,?,?)',[$companyId,$key,$recordId,$name,$stored,$type,$size,hash_file('sha256',$path)]);
    $id = (int)$db->insert_id; audit_event($db,$companyId,'attachment.added',$key,(string)$recordId);
    $db->commit();
} catch (Throwable $error) { $db->rollback(); if ($moved && is_file($path)) unlink($path); throw $error; }
attachment_result(['ok'=>true,'id'=>$id,'message'=>'Attachment added.'],201);
