<?php
declare(strict_types=1);

// Environment variables supplied by the host take precedence over local files.
const SIMPLR_ROOT = __DIR__ . '/..';
global $simplrConfig;
$simplrConfig = [];
foreach (['.env', '.env.local'] as $configFile) {
    $path = SIMPLR_ROOT . '/' . $configFile;
    if (!is_file($path)) continue;
    foreach (file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        if (str_starts_with(ltrim($line), '#') || !str_contains($line, '=')) continue;
        [$name, $value] = explode('=', $line, 2);
        $simplrConfig[trim($name)] = trim(trim($value), "\"'");
    }
}
function config(string $key, string $default = ''): string {
    global $simplrConfig;
    $value = getenv($key);
    return $value !== false ? $value : ($simplrConfig[$key] ?? $default);
}
date_default_timezone_set(config('APP_TIMEZONE', 'Africa/Lagos'));
ini_set('display_errors', '0');
ini_set('log_errors', '1');
error_reporting(E_ALL);

function e(mixed $value): string { return htmlspecialchars((string)($value ?? ''), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); }
function redirect(string $path): never { header('Location: ' . $path, true, 303); exit; }
function wants_json(): bool {
    return str_contains($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json') || str_starts_with($_SERVER['REQUEST_URI'] ?? '', '/api/');
}
function json_response(array $body, int $status = 200): never {
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($body, JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);
    exit;
}
function fail(string $message, int $status = 400): never {
    if (wants_json()) json_response(['ok' => false, 'error' => $message], $status);
    http_response_code($status);
    [$home,$label] = match ($_SESSION['auth_role'] ?? '') {
        'admin' => ['/users/admin/newdashboard.php','Return to your workspace'],
        'employee','manager' => ['/users/employees/workspace.php','Return to your workspace'],
        'talent' => ['/users/talents/talentpool.php','Return to your profile'],
        default => ['/users/admin/login.php','Return to sign in'],
    };
    echo '<!doctype html><html lang="en"><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Simplr · Please try again</title><link rel="icon" href="/public/images/brand.svg" type="image/svg+xml"><link rel="stylesheet" href="/public/styles/suite.css"><body class="auth-page"><main class="auth-card error-card"><a class="brand" href="/" aria-label="Simplr Suite home"><svg class="brand-logo" viewBox="0 0 40 40" aria-hidden="true"><rect width="40" height="40" rx="12" fill="#172f2b"/><path d="M27 12H17a5 5 0 0 0 0 10h6a3 3 0 0 1 0 6H13" fill="none" stroke="#cce987" stroke-width="5" stroke-linecap="round"/></svg><span class="brand-words"><span class="brand-name">simplr</span><span class="brand-suffix">suite</span></span></a><h1>Let’s get you back on track.</h1><p role="alert">' . e($message) . '</p><a class="button" href="' . $home . '">' . $label . '</a></main></body></html>';
    exit;
}
set_exception_handler(function (Throwable $error): never {
    $reference = bin2hex(random_bytes(4));
    error_log('Simplr [' . $reference . '] ' . get_class($error) . ' at ' . basename($error->getFile()) . ':' . $error->getLine() . ' code=' . $error->getCode());
    if (PHP_SAPI === 'cli') { fwrite(STDERR, 'Operation failed. Reference: ' . $reference . PHP_EOL); exit(1); }
    fail('This service is temporarily unavailable. Please try again shortly. Reference: ' . $reference, 503);
});
if (PHP_SAPI !== 'cli') {
    header('X-Content-Type-Options: nosniff');
    header('X-Frame-Options: SAMEORIGIN');
    header('Referrer-Policy: strict-origin-when-cross-origin');
    header('Cache-Control: no-store');
    ini_set('session.use_strict_mode', '1');
    ini_set('session.use_only_cookies', '1');
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_set_cookie_params(['lifetime' => 0, 'path' => '/', 'secure' => (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || str_starts_with(config('APP_URL'), 'https://'), 'httponly' => true, 'samesite' => 'Lax']);
        session_start();
    }
}
function csrf_token(): string {
    return $_SESSION['csrf_token'] ??= bin2hex(random_bytes(32));
}
function csrf_field(): string { return '<input type="hidden" name="csrf_token" value="' . e(csrf_token()) . '">'; }
function check_csrf(): void {
    $token = $_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
    if (!is_string($token) || !isset($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $token)) fail('Your session expired. Refresh the page and try again.', 419);
}
function require_post(): void {
    if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') { header('Allow: POST'); fail('Use the form to submit this request.', 405); }
    check_csrf();
}
function flash(string $message, string $type = 'success'): void { $_SESSION['flash'] = ['message' => $message, 'type' => $type]; }
function show_flash(): void {
    if (!isset($_SESSION['flash'])) return;
    $flash = $_SESSION['flash']; unset($_SESSION['flash']);
    $type = in_array($flash['type'], ['success','error','info','warning'], true) ? $flash['type'] : 'info';
    echo '<div class="notice ' . e($type) . '" data-flash-type="' . e($type) . '" role="' . ($type === 'error' ? 'alert' : 'status') . '">' . e($flash['message']) . '</div>';
}
function input(string $key): string { return is_string($_POST[$key] ?? null) ? trim($_POST[$key]) : ''; }
