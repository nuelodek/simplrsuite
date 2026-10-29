<?php
declare(strict_types=1);
require_once __DIR__ . '/../app/zoho_books.php';

$checks = 0;
$check = static function (bool $condition, string $description) use (&$checks): void {
    if (!$condition) { fwrite(STDERR,'Failed: ' . $description . PHP_EOL); exit(1); }
    $checks++;
};
$throws = static function (callable $call, string $description) use ($check): void {
    try { $call(); } catch (ZohoBooksUnavailable|InvalidArgumentException $error) { $check(true,$description); return; }
    $check(false,$description);
};

putenv('APP_ENV=local');
putenv('APP_URL=http://127.0.0.1:8000');
putenv('ZOHO_BOOKS_CLIENT_ID=local-zoho-client-id');
putenv('ZOHO_BOOKS_CLIENT_SECRET=local-zoho-client-secret');
putenv('ZOHO_BOOKS_REGION=com');

$check(zoho_books_ready() === extension_loaded('curl'),'local setup readiness follows required cURL availability');
$check(zoho_books_redirect_uri() === 'http://127.0.0.1:8000/users/admin/zoho-books.php','redirect URI uses configured app origin and connector route');
$authorize = parse_url(zoho_books_authorize_url('state-fixture'));
parse_str($authorize['query'] ?? '',$authorizeQuery);
$check(($authorize['host'] ?? '') === 'accounts.zoho.com','authorization uses the configured Zoho data center');
$check(($authorize['path'] ?? '') === '/oauth/v2/auth','authorization uses Zoho OAuth endpoint');
$check(($authorizeQuery['scope'] ?? '') === 'ZohoBooks.settings.READ','authorization requests read-only settings access');
$check(($authorizeQuery['access_type'] ?? '') === 'offline' && ($authorizeQuery['prompt'] ?? '') === 'consent','authorization requests a refresh token');
$check(($authorizeQuery['state'] ?? '') === 'state-fixture','OAuth state is carried through authorization');

$tokenTransport = static function (string $method, string $url, array $headers, ?string $body) use ($check): array {
    parse_str((string)$body,$form);
    $check($method === 'POST' && $url === 'https://accounts.zoho.com/oauth/v2/token','token exchange uses regional Zoho OAuth token endpoint');
    $check(in_array('Content-Type: application/x-www-form-urlencoded',$headers,true),'token exchange uses form encoding');
    $check(($form['client_id'] ?? '') === 'local-zoho-client-id' && ($form['client_secret'] ?? '') === 'local-zoho-client-secret','token exchange sends Zoho client credentials in the form body');
    $check(($form['grant_type'] ?? '') === 'authorization_code' && ($form['code'] ?? '') === 'fixture-code','authorization code grant parameters are sent');
    return [200,json_encode(['access_token'=>'access-fixture','refresh_token'=>'refresh-fixture','expires_in_sec'=>3600],JSON_THROW_ON_ERROR)];
};
$tokens = zoho_books_token_request('authorization_code',['code'=>'fixture-code','redirect_uri'=>zoho_books_redirect_uri()],null,$tokenTransport);
$check($tokens['access_token'] === 'access-fixture' && $tokens['refresh_token'] === 'refresh-fixture','authorization response returns both credentials');
$check($tokens['access_expires_at'] > time() && $tokens['refresh_expires_at'] === 0 && $tokens['region'] === 'com','Zoho token expiry, non-expiring refresh token, and region are recorded');

$refreshTransport = static function (string $method, string $url, array $headers, ?string $body) use ($check): array {
    parse_str((string)$body,$form);
    $check(($form['grant_type'] ?? '') === 'refresh_token' && ($form['refresh_token'] ?? '') === 'refresh-fixture','refresh grant submits saved refresh token');
    return [200,json_encode(['access_token'=>'refreshed-access','expires_in_sec'=>3600],JSON_THROW_ON_ERROR)];
};
$refreshed = zoho_books_token_request('refresh_token',['refresh_token'=>$tokens['refresh_token']],$tokens['refresh_token'],$refreshTransport);
$check($refreshed['access_token'] === 'refreshed-access' && $refreshed['refresh_token'] === 'refresh-fixture','refresh response retains Zoho refresh token when not returned');

$ciphertext = zoho_books_token_ciphertext($tokens);
$check(!str_contains($ciphertext,'access-fixture') && !str_contains($ciphertext,'refresh-fixture'),'persisted token payload is encrypted');
$check(zoho_books_decrypt_tokens($ciphertext) === $tokens,'encrypted token payload round-trips');

$organizationTransport = static function (string $method, string $url, array $headers, ?string $body) use ($check): array {
    $check($method === 'GET' && $url === 'https://www.zohoapis.com/books/v3/organizations','organization discovery uses the regional read endpoint');
    $check(in_array('Authorization: Zoho-oauthtoken access-fixture',$headers,true),'organization discovery uses Zoho authorization header');
    return [200,json_encode(['code'=>0,'organizations'=>[['organization_id'=>'123456789','name'=>'Fixture Books Org']]],JSON_THROW_ON_ERROR)];
};
$organizations = zoho_books_organizations('access-fixture','com',$organizationTransport);
$check(count($organizations) === 1 && $organizations[0]['organization_id'] === '123456789','organization discovery returns valid organization entries');
$throws(static fn()=>zoho_books_organization_info('bad-id','access-fixture','com'),'invalid organization IDs are rejected');

$verifyTransport = static function (string $method, string $url, array $headers, ?string $body) use ($check): array {
    $check($method === 'GET' && $url === 'https://www.zohoapis.com/books/v3/organizations/123456789','verification uses the read-only organization endpoint');
    return [200,json_encode(['code'=>0,'organization'=>['organization_id'=>'123456789','name'=>'Fixture Books Org']],JSON_THROW_ON_ERROR)];
};
$organization = zoho_books_organization_info('123456789','access-fixture','com',$verifyTransport);
$check($organization['name'] === 'Fixture Books Org','organization verification parses the API response');

$revokeTransport = static function (string $method, string $url, array $headers, ?string $body) use ($check): array {
    parse_str((string)$body,$form);
    $check($method === 'POST' && $url === 'https://accounts.zoho.com/oauth/v2/token/revoke','disconnect uses the regional Zoho revocation endpoint');
    $check(($form['token'] ?? '') === 'refresh-fixture','disconnect revokes the stored refresh token');
    return [200,'{"status":"success"}'];
};
$check(zoho_books_revoke_token($tokens,$revokeTransport),'successful provider token revocation is recognized');
$throws(static fn()=>zoho_books_token_request('authorization_code',['code'=>'bad'],null,static fn()=>[400,'{"error":"invalid_code"}']),'failed OAuth exchange is rejected');
$throws(static fn()=>zoho_books_organizations('access-fixture','com',static fn()=>[401,'{}']),'failed organization discovery is rejected');

putenv('ZOHO_BOOKS_REGION=eu');
$euAuthorize = parse_url(zoho_books_authorize_url('state-eu'));
$check(($euAuthorize['host'] ?? '') === 'accounts.zoho.eu','OAuth host follows the selected European data center');
$check(zoho_books_api_host() === 'www.zohoapis.eu','API host follows the selected European data center');
putenv('ZOHO_BOOKS_REGION=invalid');
$check(!zoho_books_ready(),'unsupported data centers disable provider calls');
putenv('ZOHO_BOOKS_REGION=com');
putenv('APP_ENV=production');
putenv('APP_URL=http://example.test');
$check(!zoho_books_ready(),'production connection requires HTTPS');
putenv('APP_URL=https://example.test');
$check(zoho_books_ready() === extension_loaded('curl'),'production setup requires HTTPS and cURL');

echo "Zoho Books checks passed: $checks\n";
