<?php
declare(strict_types=1);
require_once __DIR__ . '/../app/xero.php';

$checks = 0;
$check = static function (bool $condition, string $description) use (&$checks): void {
    if (!$condition) { fwrite(STDERR,'Failed: ' . $description . PHP_EOL); exit(1); }
    $checks++;
};
$throws = static function (callable $call, string $description) use ($check): void {
    try { $call(); } catch (XeroUnavailable|InvalidArgumentException $error) { $check(true,$description); return; }
    $check(false,$description);
};

putenv('APP_ENV=local');
putenv('APP_URL=http://127.0.0.1:8000');
putenv('XERO_CLIENT_ID=local-xero-client-id');
putenv('XERO_CLIENT_SECRET=local-xero-client-secret');

$check(xero_ready() === extension_loaded('curl'),'local setup readiness follows required cURL availability');
$check(xero_redirect_uri() === 'http://127.0.0.1:8000/users/admin/xero.php','redirect URI uses configured app origin and connector route');
$authorize = parse_url(xero_authorize_url('state-fixture'));
parse_str($authorize['query'] ?? '',$authorizeQuery);
$check(($authorize['host'] ?? '') === 'login.xero.com','authorization uses the official Xero OAuth host');
$check(($authorizeQuery['scope'] ?? '') === 'offline_access accounting.settings.read','OAuth requests only read-only accounting settings and offline access');
$check(($authorizeQuery['state'] ?? '') === 'state-fixture','OAuth state is carried through authorization');

$now = time();
$tokenTransport = static function (string $method, string $url, array $headers, ?string $body) use ($check): array {
    parse_str((string)$body,$form);
    $check($method === 'POST' && $url === 'https://identity.xero.com/connect/token','token exchange uses Xero HTTPS token endpoint');
    $check(in_array('Content-Type: application/x-www-form-urlencoded',$headers,true),'token exchange uses form encoding');
    $check(in_array('Authorization: Basic ' . base64_encode('local-xero-client-id:local-xero-client-secret'),$headers,true),'token exchange uses HTTP Basic client authentication');
    $check(($form['grant_type'] ?? '') === 'authorization_code' && ($form['code'] ?? '') === 'fixture-code','authorization code grant parameters are sent');
    return [200,json_encode(['access_token'=>'access-fixture','refresh_token'=>'refresh-fixture','expires_in'=>1800],JSON_THROW_ON_ERROR)];
};
$tokens = xero_token_request('authorization_code',['code'=>'fixture-code','redirect_uri'=>xero_redirect_uri()],$tokenTransport);
$check($tokens['access_token'] === 'access-fixture' && $tokens['refresh_token'] === 'refresh-fixture','token response returns both credentials');
$check($tokens['access_expires_at'] > $now && $tokens['refresh_expires_at'] > $tokens['access_expires_at'],'token expiries are recorded');

$refreshTransport = static function (string $method, string $url, array $headers, ?string $body) use ($check): array {
    parse_str((string)$body,$form);
    $check(($form['grant_type'] ?? '') === 'refresh_token' && ($form['refresh_token'] ?? '') === 'refresh-fixture','access tokens can be refreshed with the stored refresh token');
    return [200,json_encode(['access_token'=>'refreshed-access','refresh_token'=>'rotated-refresh','expires_in'=>1800],JSON_THROW_ON_ERROR)];
};
$refreshed = xero_token_request('refresh_token',['refresh_token'=>$tokens['refresh_token']],$refreshTransport);
$check($refreshed['access_token'] === 'refreshed-access' && $refreshed['refresh_token'] === 'rotated-refresh','refresh response replaces both rotating tokens');

$ciphertext = xero_token_ciphertext($tokens);
$check(!str_contains($ciphertext,'access-fixture') && !str_contains($ciphertext,'refresh-fixture'),'persisted token payload is encrypted');
$check(xero_decrypt_tokens($ciphertext) === $tokens,'encrypted token payload round-trips');

$tenantId = '123e4567-e89b-12d3-a456-426614174000';
$tenantTransport = static function (string $method, string $url, array $headers, ?string $body) use ($check,$tenantId): array {
    $check($method === 'GET' && $url === 'https://api.xero.com/connections','organization discovery uses the Xero connections endpoint');
    $check(in_array('Authorization: Bearer access-fixture',$headers,true),'organization discovery uses the supplied access token');
    return [200,json_encode([['tenantId'=>$tenantId,'tenantName'=>'Fixture Org','tenantType'=>'ORGANISATION']],JSON_THROW_ON_ERROR)];
};
$tenants = xero_tenants('access-fixture',$tenantTransport);
$check(count($tenants) === 1 && $tenants[0]['tenant_id'] === $tenantId,'organization list returns valid Xero organizations');
$throws(static fn()=>xero_organisation_info('invalid-tenant','access-fixture'),'invalid organization IDs are rejected');

$organisationTransport = static function (string $method, string $url, array $headers, ?string $body) use ($check,$tenantId): array {
    $check($method === 'GET' && $url === 'https://api.xero.com/api.xro/2.0/Organisation','verification uses the read-only Organisation endpoint');
    $check(in_array('Xero-tenant-id: ' . $tenantId,$headers,true),'verification is scoped to the selected tenant');
    return [200,json_encode(['Organisations'=>[['Name'=>'Fixture Org']]],JSON_THROW_ON_ERROR)];
};
$organisation = xero_organisation_info($tenantId,'access-fixture',$organisationTransport);
$check($organisation['Name'] === 'Fixture Org','organization verification parses the Xero Organisation response');

$revokeTransport = static function (string $method, string $url, array $headers, ?string $body) use ($check): array {
    parse_str((string)$body,$form);
    $check($method === 'POST' && $url === 'https://identity.xero.com/connect/revocation','disconnect uses Xero token revocation endpoint');
    $check(($form['token'] ?? '') === 'refresh-fixture','disconnect revokes the stored refresh token');
    return [200,''];
};
$check(xero_revoke_tokens($tokens,$revokeTransport),'successful provider token revocation is recognized');
$throws(static fn()=>xero_token_request('authorization_code',['code'=>'bad'],static fn()=>[400,'{"error":"invalid_grant"}']),'failed OAuth exchange is rejected');
$throws(static fn()=>xero_tenants('access-fixture',static fn()=>[401,'{}']),'failed organization discovery is rejected');

putenv('APP_ENV=production');
putenv('APP_URL=http://example.test');
$check(!xero_ready(),'production Xero connection requires HTTPS');
putenv('APP_URL=https://example.test');
$check(xero_ready() === extension_loaded('curl'),'production setup requires HTTPS and cURL');

echo "Xero checks passed: $checks\n";
