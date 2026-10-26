<?php
require_once __DIR__ . '/app/http.php';
$path = public_request_path();
$file = realpath(__DIR__ . $path);
if ($path !== '' && public_static_file($path) && $file !== false && str_starts_with($file,__DIR__ . DIRECTORY_SEPARATOR) && is_file($file)) return false;
dispatch_public_request();
