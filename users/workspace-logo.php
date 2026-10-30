<?php
declare(strict_types=1);
require_once __DIR__ . '/../app/staff.php';
require_once __DIR__ . '/../app/settings.php';

$db = database();
$role = (string)($_SESSION['auth_role'] ?? '');
if ($role === 'admin') $user = admin_user($db);
elseif (in_array($role,['employee','manager'],true)) $user = staff_user($db);
else fail('Please sign in to view this workspace logo.',401);

$settings = workspace_settings($db,(int)$user['company_id']);
$path = workspace_logo_storage_file((string)$settings['company_logo_path']);
if ($path === null || !is_file($path)) {
    http_response_code(404);
    exit;
}
$mime = (new finfo(FILEINFO_MIME_TYPE))->file($path);
if (!in_array($mime,['image/png','image/jpeg','image/webp'],true)) fail('The workspace logo is not a supported image.',500);
header('Content-Type: ' . $mime);
header('Cache-Control: private, max-age=300');
header('Content-Length: ' . filesize($path));
readfile($path);
