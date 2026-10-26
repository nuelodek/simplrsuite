<?php
declare(strict_types=1);
require_once __DIR__ . '/bootstrap.php';
function audiences(): array {
    return [
        'business'=>['name'=>'Business','host'=>'business.simplr.com','setting'=>'APP_BUSINESS_URL'],
        'talents'=>['name'=>'Talent','host'=>'talents.simplr.com','setting'=>'APP_TALENT_URL'],
        'work'=>['name'=>'Work','host'=>'work.simplr.com','setting'=>'APP_WORK_URL'],
    ];
}
function configured_audience_url(string $audience): string {
    $entry = audiences()[$audience] ?? null;
    if (!$entry) throw new LogicException('Unknown audience');
    $url = rtrim(config($entry['setting']), '/');
    if ($url === '') return '';
    $parts = parse_url($url);
    // URLs come from server configuration only; request Host never enters a link.
    if (!filter_var($url, FILTER_VALIDATE_URL) || !is_array($parts) || !in_array($parts['scheme'] ?? '', config('APP_ENV') === 'local' ? ['http','https'] : ['https'], true) || isset($parts['user'],$parts['pass']) || isset($parts['user']) || isset($parts['query']) || isset($parts['fragment']) || !in_array($parts['path'] ?? '', ['', '/'], true)) throw new LogicException('Audience URL must be an absolute site origin');
    return $url;
}
function audience_for_host(string $host): string {
    $hostname = strtolower((string)parse_url('http://' . $host, PHP_URL_HOST));
    foreach (audiences() as $key=>$entry) {
        $configured = configured_audience_url($key);
        if ($hostname === $entry['host'] || ($configured !== '' && $hostname === strtolower((string)parse_url($configured, PHP_URL_HOST)))) return $key;
    }
    return 'business';
}
function audience_url(string $audience, string $path = '/'): string {
    $entry = audiences()[$audience] ?? null;
    if (!$entry || !str_starts_with($path,'/') || str_starts_with($path,'//')) throw new LogicException('Invalid audience link');
    if (config('APP_ENV') === 'local') return $path === '/' ? '/landing/' . $audience . '/' : $path;
    return (configured_audience_url($audience) ?: 'https://' . $entry['host']) . $path;
}
function account_audience(string $path): string {
    if (str_starts_with($path, '/users/talents/')) return 'talents';
    if (str_starts_with($path, '/users/employees/') || str_starts_with($path, '/users/managers/') || in_array($path, ['/users/accept-invitation.php','/users/reset_password.php'], true)) return 'work';
    return 'business';
}
