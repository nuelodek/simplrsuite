<?php
declare(strict_types=1);
require_once __DIR__ . '/database.php';
function workspace_settings(mysqli $db, int $companyId): array {
    return db_one($db,'SELECT currency, timezone, company_logo_path FROM workspace_settings WHERE company_id = ?',[$companyId]) ?: ['currency'=>'','timezone'=>config('APP_TIMEZONE','Africa/Lagos'),'company_logo_path'=>''];
}
function supported_currencies(): array {
    return ['NGN'=>'Nigerian naira','USD'=>'US dollar','GBP'=>'Pound sterling','EUR'=>'Euro','CAD'=>'Canadian dollar','GHS'=>'Ghanaian cedi','KES'=>'Kenyan shilling','ZAR'=>'South African rand'];
}
function workspace_logo_storage_directory(): string { return SIMPLR_ROOT . '/storage/company-logos'; }
function workspace_logo_storage_file(string $fileName): ?string {
    if (!preg_match('/\A[a-f0-9]{64}\.(?:png|jpg|webp)\z/D',$fileName)) return null;
    return workspace_logo_storage_directory() . '/' . $fileName;
}
function workspace_logo_upload_error(array $file): ?string {
    if (!isset($file['error']) || !is_int($file['error'])) return 'Choose a valid logo image.';
    if ($file['error'] === UPLOAD_ERR_NO_FILE) return null;
    if ($file['error'] !== UPLOAD_ERR_OK || !is_uploaded_file($file['tmp_name'] ?? '')) return 'The logo image could not be uploaded.';
    $size = filesize($file['tmp_name']);
    if (!is_int($size) || $size < 1 || $size > 2 * 1024 * 1024) return 'Upload a logo image up to 2 MB.';
    $mime = (new finfo(FILEINFO_MIME_TYPE))->file($file['tmp_name']);
    $image = getimagesize($file['tmp_name']);
    if (!is_array($image) || $image[0] < 1 || $image[1] < 1 || $image[0] > 4096 || $image[1] > 4096) return 'Use a valid PNG, JPEG or WebP logo up to 4096 pixels wide or high.';
    if (!in_array($mime,['image/png','image/jpeg','image/webp'],true)) return 'Use a PNG, JPEG or WebP logo image.';
    return null;
}
function store_workspace_logo_upload(array $file): string {
    $mime = (new finfo(FILEINFO_MIME_TYPE))->file($file['tmp_name']);
    $extension = ['image/png'=>'png','image/jpeg'=>'jpg','image/webp'=>'webp'][$mime];
    $directory = workspace_logo_storage_directory();
    if (!is_dir($directory) && !mkdir($directory,0775,true) && !is_dir($directory)) throw new RuntimeException('Company logo storage is unavailable.');
    $fileName = bin2hex(random_bytes(32)) . '.' . $extension;
    $path = workspace_logo_storage_file($fileName);
    if ($path === null || !move_uploaded_file($file['tmp_name'],$path)) throw new RuntimeException('The company logo could not be saved.');
    return $fileName;
}
function delete_workspace_logo(string $fileName): void {
    $path = workspace_logo_storage_file($fileName);
    if ($path !== null && is_file($path) && !unlink($path)) error_log('Simplr: could not remove replaced company logo ' . $fileName);
}
