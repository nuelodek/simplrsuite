<?php
declare(strict_types=1);

function public_request_path(): string {
    $raw = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
    if (!is_string($raw)) return '';
    $path = rawurldecode($raw);
    // Exact paths on every platform: no path-info, traversal or double encoding.
    if (preg_match('~[\\\\\x00-\x1f%]|(?:^|/)\.{1,2}(?:/|$)|//~', $path)) return '';
    return $path;
}
function public_route(string $path): ?string {
    $routes = require __DIR__ . '/routes.php';
    return $routes[$path] ?? null;
}
function public_static_file(string $path): bool {
    if (preg_match('~(?:^|/)\.|(?:^|/)(?:uploads|attachments|resumes|profile_pictures)(?:/|$)~i', $path)) return false;
    if (in_array($path,['/404.html','/users/admin/login.html','/users/developers/documentation.html'],true)) return true;
    return (bool)preg_match('~^/(?:public/(?:styles|scripts|images)|scripts|styles|images|img|css|assets)/[a-zA-Z0-9_./ -]+\.(?:css|js|png|jpe?g|gif|webp|svg|ico|woff2?|ttf)$~D', $path);
}
function dispatch_public_request(): never {
    $target = public_route(public_request_path());
    if ($target !== null) { require dirname(__DIR__) . '/' . $target; exit; }
    http_response_code(404);
    header('X-Content-Type-Options: nosniff');
    require dirname(__DIR__) . '/404.html';
    exit;
}
