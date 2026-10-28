<?php
// Local stand-in for the GitHub REST API and OAuth endpoints, used only by
// scripts/github_smoke_test.py:  php -S 127.0.0.1:8091 scripts/github_stub_server.php
declare(strict_types=1);
if (PHP_SAPI !== 'cli-server') { http_response_code(404); exit; }
$base = 'http://' . ($_SERVER['HTTP_HOST'] ?? '127.0.0.1:8091');
$path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
$state = sys_get_temp_dir() . '/simplr-github-stub-closed.json';
$closed = is_file($state) ? (json_decode((string)file_get_contents($state), true) ?: []) : [];
$assignState = sys_get_temp_dir() . '/simplr-github-stub-assignees.json';
$assigned = is_file($assignState) ? (json_decode((string)file_get_contents($assignState), true) ?: []) : [];
function reply(mixed $body, int $status = 200, array $headers = []): never {
    http_response_code($status);
    header('Content-Type: application/json');
    foreach ($headers as $name => $value) header($name . ': ' . $value);
    echo json_encode($body);
    exit;
}
if ($path === '/__test/reset') { @unlink($state); @unlink($assignState); reply(['ok' => true]); }
if ($path === '/__test/assign') { $assigned[(string)(int)($_GET['issue'] ?? 0)] = (string)($_GET['login'] ?? ''); file_put_contents($assignState, json_encode($assigned)); reply(['ok' => true]); }
if ($path === '/__test/close') { $closed[] = (int)($_GET['issue'] ?? 0); file_put_contents($state, json_encode(array_values(array_unique($closed)))); reply(['ok' => true]); }
if ($path === '/login/oauth/authorize') {
    $redirect = (string)($_GET['redirect_uri'] ?? '');
    if (($_GET['client_id'] ?? '') !== 'stub-client' || !str_starts_with($redirect, 'http://127.0.0.1:')) reply(['error' => 'bad_client'], 400);
    header('Location: ' . $redirect . (str_contains($redirect, '?') ? '&' : '?') . http_build_query(['code' => 'stubcode12345', 'state' => (string)($_GET['state'] ?? '')]), true, 302);
    exit;
}
if ($path === '/login/oauth/access_token') {
    if (($_POST['client_secret'] ?? '') !== 'stub-secret' || ($_POST['code'] ?? '') !== 'stubcode12345') reply(['error' => 'bad_verification_code']);
    reply(['access_token' => 'gho_stubOAuthToken0123456789abcdef', 'token_type' => 'bearer', 'scope' => 'repo,read:user']);
}
$headers = array_change_key_case(function_exists('getallheaders') ? getallheaders() : [], CASE_LOWER);
$token = preg_replace('/^Bearer\s+/', '', (string)($_SERVER['HTTP_AUTHORIZATION'] ?? $headers['authorization'] ?? ''));
// Two accounts: simplr-demo (token or OAuth) and acme-labs (second token). Neither sees the other's repositories.
$accounts = ['ghp_stubToken0123456789abcdefABCDEF' => 'simplr-demo', 'gho_stubOAuthToken0123456789abcdef' => 'simplr-demo', 'ghp_stubSecondAccount0123456789abcd' => 'acme-labs'];
$login = $accounts[$token] ?? reply(['message' => 'Bad credentials'], 401);
$repo = fn(string $name, int $id, bool $private, string $owner = 'simplr-demo') => ['id' => $id, 'full_name' => $owner . '/' . $name, 'private' => $private, 'html_url' => $base . '/' . $owner . '/' . $name, 'description' => 'Demo ' . $name . ' repository', 'default_branch' => 'main', 'pushed_at' => '2026-10-25T10:00:00Z', 'language' => 'PHP'];
if ($login === 'acme-labs') {
    switch (true) {
        case $path === '/user': reply(['login' => 'acme-labs', 'name' => 'Acme Labs', 'html_url' => $base . '/acme-labs'], 200, ['X-OAuth-Scopes' => 'repo']);
        case $path === '/user/repos': reply([$repo('api', 201, true, 'acme-labs')]);
        case $path === '/repos/acme-labs/api': reply($repo('api', 201, true, 'acme-labs'));
        case $path === '/repos/acme-labs/api/issues': reply(($_GET['state'] ?? 'open') === 'open' ? [['number' => 7, 'title' => 'Rate limit the public API', 'body' => '', 'labels' => [['name' => 'high']], 'milestone' => null, 'assignees' => [], 'html_url' => $base . '/acme-labs/api/issues/7', 'user' => ['login' => 'acme-dev'], 'created_at' => '2026-10-22T09:00:00Z', 'state' => 'open']] : []);
        case $path === '/repos/acme-labs/api/pulls': reply([]);
        case $path === '/repos/acme-labs/api/commits': reply([['sha' => 'f00dbabe1234', 'html_url' => $base . '/acme-labs/api/commit/f00dbab', 'commit' => ['message' => 'Add API keys page', 'author' => ['name' => 'Acme Dev', 'date' => '2026-10-26T08:00:00Z']], 'author' => ['login' => 'acme-dev']]]);
        default: reply(['message' => 'Not Found'], 404);
    }
}
$issues = [
    ['number' => 1, 'title' => 'Fix checkout <script>alert(1)</script> bug', 'body' => "Steps:\n1. Open checkout\n2. See error", 'labels' => [['name' => 'urgent']], 'milestone' => ['due_on' => '2026-12-01T07:00:00Z'], 'assignees' => [['login' => 'ada']]],
    ['number' => 2, 'title' => 'Write onboarding guide', 'body' => 'Short guide for new customers.', 'labels' => [['name' => 'low priority']], 'milestone' => null, 'assignees' => []],
    ['number' => 3, 'title' => 'Add dark mode', 'body' => '', 'labels' => [], 'milestone' => null, 'assignees' => [], 'pull_request' => ['url' => 'x']],
];
$issue = fn(array $item) => (isset($assigned[(string)$item['number']]) ? ['assignees' => $assigned[(string)$item['number']] === '' ? [] : [['login' => $assigned[(string)$item['number']]]]] + $item : $item) + ['html_url' => $base . '/simplr-demo/website/issues/' . $item['number'], 'user' => ['login' => 'octo-dev'], 'created_at' => '2026-10-20T09:00:00Z', 'state' => in_array($item['number'], $closed, true) ? 'closed' : 'open'];
switch (true) {
    case $path === '/user':
        reply(['login' => 'simplr-demo', 'name' => 'Simplr Demo', 'html_url' => $base . '/simplr-demo'], 200, ['X-OAuth-Scopes' => 'repo, read:user']);
    case $path === '/user/repos':
        reply([$repo('website', 101, false), $repo('mobile-app', 102, true)]);
    case $path === '/repos/simplr-demo/website':
        reply($repo('website', 101, false));
    case $path === '/repos/simplr-demo/website/issues':
        $wanted = ($_GET['state'] ?? 'open') === 'closed' ? 'closed' : 'open';
        reply(array_values(array_filter(array_map($issue, $issues), fn($item) => $item['state'] === $wanted)));
    case $path === '/repos/simplr-demo/website/pulls':
        reply([['number' => 3, 'title' => 'Add dark mode', 'draft' => true, 'html_url' => $base . '/simplr-demo/website/pull/3', 'user' => ['login' => 'octo-dev'], 'created_at' => '2026-10-24T09:00:00Z']]);
    case $path === '/repos/simplr-demo/website/commits':
        reply([['sha' => 'a1b2c3d4e5f6', 'html_url' => $base . '/simplr-demo/website/commit/a1b2c3d', 'commit' => ['message' => "Ship invoice export\n\nDetails", 'author' => ['name' => 'Ada', 'date' => '2026-10-25T08:00:00Z']], 'author' => ['login' => 'ada']]]);
    default:
        reply(['message' => 'Not Found'], 404);
}
