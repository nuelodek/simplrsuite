<?php
declare(strict_types=1);
require_once __DIR__ . '/../app/workday.php';

$checks = 0;
$check = static function (bool $condition, string $description) use (&$checks): void {
    if (!$condition) { fwrite(STDERR,'Failed: ' . $description . PHP_EOL); exit(1); }
    $checks++;
};
$throws = static function (callable $call, string $description) use ($check): void {
    try { $call(); } catch (WorkdayUnavailable|InvalidArgumentException $error) { $check(true,$description); return; }
    $check(false,$description);
};

putenv('APP_ENV=local');
putenv('APP_URL=http://127.0.0.1:8000');
putenv('WORKDAY_HOST=wd5.myworkday.com');
putenv('WORKDAY_TENANT=fixture_tenant');
putenv('WORKDAY_CLIENT_ID=local-workday-client-id');
putenv('WORKDAY_CLIENT_SECRET=local-workday-client-secret');
putenv('WORKDAY_SCOPES=openid');

$check(workday_ready() === extension_loaded('curl'),'local setup readiness follows required cURL availability');
$check(workday_redirect_uri() === 'http://127.0.0.1:8000/users/admin/workday.php','redirect URI uses configured app origin and connector route');
$authorize = parse_url(workday_authorize_url('state-fixture'));
parse_str($authorize['query'] ?? '',$authorizeQuery);
$check(($authorize['host'] ?? '') === 'wd5.myworkday.com','authorization uses the configured Workday service host');
$check(($authorize['path'] ?? '') === '/ccx/oauth2/fixture_tenant/authorize','authorization path uses the configured tenant');
$check(($authorizeQuery['scope'] ?? '') === 'openid','requested scope follows the server environment');
$check(($authorizeQuery['state'] ?? '') === 'state-fixture','OAuth state is carried through authorization');

$tokenTransport = static function (string $method, string $url, array $headers, ?string $body) use ($check): array {
    parse_str((string)$body,$form);
    $check($method === 'POST' && $url === 'https://wd5.myworkday.com/ccx/oauth2/fixture_tenant/token','token exchange uses tenant OAuth token endpoint');
    $check(in_array('Content-Type: application/x-www-form-urlencoded',$headers,true),'token exchange uses form encoding');
    $check(in_array('Authorization: Basic ' . base64_encode('local-workday-client-id:local-workday-client-secret'),$headers,true),'token exchange uses HTTP Basic client authentication');
    $check(($form['grant_type'] ?? '') === 'authorization_code' && ($form['code'] ?? '') === 'fixture-code','authorization code grant parameters are sent');
    return [200,json_encode(['access_token'=>'access-fixture','refresh_token'=>'refresh-fixture','expires_in'=>3600,'refresh_token_expires_in'=>2592000],JSON_THROW_ON_ERROR)];
};
$tokens = workday_token_request('authorization_code',['code'=>'fixture-code','redirect_uri'=>workday_redirect_uri()],$tokenTransport);
$check($tokens['access_token'] === 'access-fixture' && $tokens['refresh_token'] === 'refresh-fixture','token response returns both credentials');
$check($tokens['access_expires_at'] > time() && $tokens['refresh_expires_at'] > $tokens['access_expires_at'],'token expiries are recorded when provided');

$refreshTransport = static function (string $method, string $url, array $headers, ?string $body) use ($check): array {
    parse_str((string)$body,$form);
    $check(($form['grant_type'] ?? '') === 'refresh_token' && ($form['refresh_token'] ?? '') === 'refresh-fixture','refresh grant submits saved refresh token');
    return [200,json_encode(['access_token'=>'refreshed-access','refresh_token'=>'rotated-refresh','expires_in'=>1800],JSON_THROW_ON_ERROR)];
};
$refreshed = workday_token_request('refresh_token',['refresh_token'=>$tokens['refresh_token']],$refreshTransport);
$check($refreshed['access_token'] === 'refreshed-access' && $refreshed['refresh_token'] === 'rotated-refresh','refresh response replaces rotating tokens');

$ciphertext = workday_token_ciphertext($tokens);
$check(!str_contains($ciphertext,'access-fixture') && !str_contains($ciphertext,'refresh-fixture'),'persisted token payload is encrypted');
$check(workday_decrypt_tokens($ciphertext) === $tokens,'encrypted token payload round-trips');
$unknownExpiry = $tokens;
$unknownExpiry['refresh_expires_at'] = 0;
$check(workday_decrypt_tokens(workday_token_ciphertext($unknownExpiry)) === $unknownExpiry,'provider tokens without a reported refresh expiry remain usable');
$throws(static fn()=>workday_token_request('authorization_code',['code'=>'bad'],static fn()=>[400,'{"error":"invalid_grant"}']),'failed OAuth exchange is rejected');

putenv('WORKDAY_HOST=attacker.example');
$check(!workday_ready(),'non-Workday OAuth hosts are rejected');
putenv('WORKDAY_HOST=wd5.myworkday.com');
putenv('WORKDAY_TENANT=invalid/tenant');
$check(!workday_ready(),'unsafe tenant path fragments are rejected');
putenv('WORKDAY_TENANT=fixture_tenant');
putenv('APP_ENV=production');
putenv('APP_URL=http://example.test');
$check(!workday_ready(),'production Workday connection requires HTTPS');
putenv('APP_URL=https://example.test');
$check(workday_ready() === extension_loaded('curl'),'production setup requires HTTPS and cURL');

echo "Workday checks passed: $checks\n";
