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
    if (($_POST['return_to_record'] ?? '') === '1') {
        flash($body['error'] ?? $body['message'],isset($body['error']) ? 'error' : 'success');
        redirect('/users/admin/workspace.php?' . http_build_query(['module'=>input('module'),'action'=>'view','id'=>input('record_id')]));
    }
    json_response($body,$status);
}
function validate_attachment_upload(mixed $upload, bool $pdfOnly = false): array {
    $label = number_format(attachment_limit()/1048576,1) . ' MB';
    if (!is_array($upload) || !is_string($upload['tmp_name'] ?? null) || ($upload['error'] ?? null) !== UPLOAD_ERR_OK || !is_uploaded_file($upload['tmp_name'])) return [null,'Choose ' . ($pdfOnly ? 'a PDF file' : 'a PDF, PNG or JPEG file') . ' up to ' . $label . '.'];
    $size = filesize($upload['tmp_name']);
    if ($size < 1 || $size > attachment_limit()) return [null,'Choose a file between 1 byte and ' . $label . '.'];
    $type = (new finfo(FILEINFO_MIME_TYPE))->file($upload['tmp_name']);
    if (!in_array($type,$pdfOnly ? ['application/pdf'] : ['application/pdf','image/png','image/jpeg'],true)) return [null,$pdfOnly ? 'Attach a PDF resume.' : 'Only PDF, PNG and JPEG files are accepted.'];
    $name = is_string($upload['name'] ?? null) ? basename(str_replace('\\','/',$upload['name'])) : '';
    $name = preg_replace('/[\x00-\x1f\x7f]/u','',$name);
    if (!is_string($name) || $name === '' || mb_strlen($name)>180) return [null,'Use a file name between 1 and 180 characters.'];
    $extension = strtolower(pathinfo($name,PATHINFO_EXTENSION));
    if (!in_array($extension,match($type) {'application/pdf'=>['pdf'],'image/png'=>['png'],'image/jpeg'=>['jpg','jpeg']},true)) return [null,'The file extension must match its contents.'];
    if ($type !== 'application/pdf' && !@getimagesize($upload['tmp_name'])) return [null,'This image could not be read.'];
    if ($type === 'application/pdf' && file_get_contents($upload['tmp_name'],false,null,0,5) !== '%PDF-') return [null,'This PDF could not be recognized.'];
    return [['tmp_name'=>$upload['tmp_name'],'size'=>$size,'type'=>$type,'name'=>$name],null];
}
