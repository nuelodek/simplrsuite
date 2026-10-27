<?php
declare(strict_types=1);
require_once __DIR__ . '/modules.php';
function attachment_record(mysqli $db, array $user, string $key, int $id, bool $write = false): array {
    $module = module_config($key); require_module_access($db,$user,$key,$write,$write);
    $record = db_one($db,"SELECT * FROM `{$module['table']}` WHERE `{$module['pk']}` = ? AND company_id = ?" . ($write ? ' FOR UPDATE' : ''),[$id,$user['company_id']]);
    if (!$record) fail('This record could not be found in your workspace.',404);
    return $record;
}
function attachment_directory(): string { return SIMPLR_ROOT . '/storage/attachments'; }
function attachment_limit(): int {
    $limits = [5 * 1024 * 1024];
    foreach (['upload_max_filesize','post_max_size'] as $setting) {
        $value = trim(ini_get($setting));
        $bytes = (int)$value * match (strtolower(substr($value,-1))) { 'g'=>1073741824, 'm'=>1048576, 'k'=>1024, default=>1 };
        if ($bytes > 0) $limits[] = $setting === 'post_max_size' ? max(1,$bytes-65536) : $bytes;
    }
    return min($limits);
}
function attachment_result(array $body, int $status = 200): never {
    if (($_POST['return_to_files'] ?? '') === '1') {
        flash($body['error'] ?? $body['message'],isset($body['error']) ? 'error' : 'success');
        redirect('/users/admin/files.php');
    }
    if (($_POST['return_to_record'] ?? '') === '1') {
        flash($body['error'] ?? $body['message'],isset($body['error']) ? 'error' : 'success');
        redirect('/users/admin/workspace.php?' . http_build_query(['module'=>input('module'),'action'=>'view','id'=>input('record_id')]));
    }
    json_response($body,$status);
}
function attachment_types_label(): string { return 'PDF, PNG, JPEG, Word, Excel, PowerPoint or CSV'; }
function attachment_accept(): string { return '.pdf,.png,.jpg,.jpeg,.docx,.xlsx,.pptx,.csv'; }
// Office files are accepted only as macro-free OOXML packages with their main part present.
function attachment_office_type(string $path, string $extension): ?string {
    $parts = ['docx'=>['word/document.xml','application/vnd.openxmlformats-officedocument.wordprocessingml.document'],'xlsx'=>['xl/workbook.xml','application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'],'pptx'=>['ppt/presentation.xml','application/vnd.openxmlformats-officedocument.presentationml.presentation']];
    if (!isset($parts[$extension]) || !class_exists('ZipArchive') || file_get_contents($path,false,null,0,4) !== "PK\x03\x04") return null;
    $zip = new ZipArchive();
    if ($zip->open($path,ZipArchive::RDONLY) !== true) return null;
    $valid = $zip->numFiles > 0 && $zip->numFiles < 5000 && $zip->locateName('[Content_Types].xml') !== false && $zip->locateName($parts[$extension][0]) !== false;
    for ($index = 0; $valid && $index < $zip->numFiles; $index++) {
        $entry = (string)$zip->getNameIndex($index);
        if (stripos($entry,'vbaProject') !== false || stripos($entry,'activeX') !== false || str_contains($entry,'..')) $valid = false;
    }
    $zip->close();
    return $valid ? $parts[$extension][1] : null;
}
function validate_attachment_upload(mixed $upload, bool $pdfOnly = false): array {
    $label = number_format(attachment_limit()/1048576,1) . ' MB';
    if (!is_array($upload) || !is_string($upload['tmp_name'] ?? null) || ($upload['error'] ?? null) !== UPLOAD_ERR_OK || !is_uploaded_file($upload['tmp_name'])) return [null,'Choose ' . ($pdfOnly ? 'a PDF file' : 'a ' . attachment_types_label() . ' file') . ' up to ' . $label . '.'];
    $size = filesize($upload['tmp_name']);
    if ($size < 1 || $size > attachment_limit()) return [null,'Choose a file between 1 byte and ' . $label . '.'];
    $name = is_string($upload['name'] ?? null) ? basename(str_replace('\\','/',$upload['name'])) : '';
    $name = preg_replace('/[\x00-\x1f\x7f]/u','',$name);
    if (!is_string($name) || $name === '' || mb_strlen($name)>180) return [null,'Use a file name between 1 and 180 characters.'];
    $extension = strtolower(pathinfo($name,PATHINFO_EXTENSION));
    $type = (new finfo(FILEINFO_MIME_TYPE))->file($upload['tmp_name']);
    if (!$pdfOnly && in_array($extension,['docx','xlsx','pptx'],true)) {
        $office = attachment_office_type($upload['tmp_name'],$extension);
        return $office ? [['tmp_name'=>$upload['tmp_name'],'size'=>$size,'type'=>$office,'name'=>$name],null] : [null,'This Office file could not be recognised. Save it as a regular .docx, .xlsx or .pptx file without macros.'];
    }
    if (!$pdfOnly && $extension === 'csv') {
        $sample = (string)file_get_contents($upload['tmp_name'],false,null,0,65536);
        if (!in_array($type,['text/plain','text/csv','application/csv','text/x-csv','application/vnd.ms-excel'],true) || str_contains($sample,"\0")) return [null,'This CSV file could not be recognised as text.'];
        return [['tmp_name'=>$upload['tmp_name'],'size'=>$size,'type'=>'text/csv','name'=>$name],null];
    }
    if (!in_array($type,$pdfOnly ? ['application/pdf'] : ['application/pdf','image/png','image/jpeg'],true)) return [null,$pdfOnly ? 'Attach a PDF resume.' : 'Only PDF, PNG, JPEG, Word, Excel, PowerPoint and CSV files are accepted.'];
    if (!in_array($extension,match($type) {'application/pdf'=>['pdf'],'image/png'=>['png'],'image/jpeg'=>['jpg','jpeg']},true)) return [null,'The file extension must match its contents.'];
    if ($type !== 'application/pdf' && !@getimagesize($upload['tmp_name'])) return [null,'This image could not be read.'];
    if ($type === 'application/pdf' && file_get_contents($upload['tmp_name'],false,null,0,5) !== '%PDF-') return [null,'This PDF could not be recognized.'];
    return [['tmp_name'=>$upload['tmp_name'],'size'=>$size,'type'=>$type,'name'=>$name],null];
}
// Stores a validated upload against a record. Returns [attachment id, null] or [null, error].
function store_record_attachment(mysqli $db, array $user, string $key, int $recordId, array $upload): array {
    $companyId = (int)$user['company_id'];
    $stored = bin2hex(random_bytes(32)); $path = attachment_directory() . '/' . $stored; $moved = false;
    $db->begin_transaction();
    try {
        // A company lock makes quotas and repeated uploads deterministic.
        db_one($db,'SELECT id FROM users WHERE company_id = ? ORDER BY id LIMIT 1 FOR UPDATE',[$companyId]);
        attachment_record($db,$user,$key,$recordId,true);
        $count = (int)db_one($db,'SELECT COUNT(*) AS total FROM record_attachments WHERE company_id = ? AND module_key = ? AND record_id = ?',[$companyId,$key,$recordId])['total'];
        $total = (int)db_one($db,'SELECT COALESCE(SUM(size_bytes),0) AS total FROM record_attachments WHERE company_id = ?',[$companyId])['total'];
        if ($count >= 20 || $total + $upload['size'] > 100*1024*1024) { $db->rollback(); return [null,'This workspace allows 20 attachments per record and 100 MB in total. Remove an unneeded attachment before uploading.']; }
        if (!is_dir(attachment_directory()) && !mkdir(attachment_directory(),0700,true)) throw new RuntimeException('Attachment storage unavailable');
        if (!move_uploaded_file($upload['tmp_name'],$path)) throw new RuntimeException('Attachment could not be saved');
        $moved = true;
        db_run($db,'INSERT INTO record_attachments (company_id,module_key,record_id,original_name,storage_name,media_type,size_bytes,content_hash) VALUES (?,?,?,?,?,?,?,?)',[$companyId,$key,$recordId,$upload['name'],$stored,$upload['type'],$upload['size'],hash_file('sha256',$path)]);
        $id = (int)$db->insert_id; audit_event($db,$companyId,'attachment.added',$key,(string)$recordId);
        $db->commit();
        return [$id,null];
    } catch (Throwable $error) { $db->rollback(); if ($moved && is_file($path)) unlink($path); throw $error; }
}
function attachment_kind(string $mediaType): string {
    return match (true) {
        $mediaType === 'application/pdf' => 'PDF',
        str_starts_with($mediaType,'image/') => 'Image',
        str_contains($mediaType,'wordprocessingml') => 'Word',
        str_contains($mediaType,'spreadsheetml') => 'Excel',
        str_contains($mediaType,'presentationml') => 'PowerPoint',
        $mediaType === 'text/csv' => 'CSV',
        default => 'File',
    };
}
function attachment_size(int $bytes): string {
    return $bytes >= 1048576 ? number_format($bytes / 1048576,1) . ' MB' : max(1,(int)round($bytes / 1024)) . ' KB';
}
