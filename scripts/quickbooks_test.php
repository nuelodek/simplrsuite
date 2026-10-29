<?php
declare(strict_types=1);
require_once __DIR__ . '/../app/quickbooks.php';

$checks = 0;
$check = static function (bool $condition, string $description) use (&$checks): void {
    if (!$condition) { fwrite(STDERR,'Failed: ' . $description . PHP_EOL); exit(1); }
    $checks++;
};
$throws = static function (callable $call, string $description) use ($check): void {
    try { $call(); } catch (QuickBooksUnavailable|InvalidArgumentException $error) { $check(true,$description); return; }
    $check(false,$description);
};

putenv('APP_ENV=local');
putenv('APP_URL=http://127.0.0.1:8000');
putenv('QUICKBOOKS_ENVIRONMENT=sandbox');
putenv('QUICKBOOKS_CLIENT_ID=local-client-id');
putenv('QUICKBOOKS_CLIENT_SECRET=local-client-secret');

$check(quickbooks_ready() === extension_loaded('curl'),'sandbox setup readiness follows required cURL availability');
$check(quickbooks_redirect_uri() === 'http://127.0.0.1:8000/users/admin/quickbooks.php','redirect URI uses configured app origin and connector route');
$authorize = parse_url(quickbooks_authorize_url('state-fixture'));
parse_str($authorize['query'] ?? '',$authorizeQuery);
$check(($authorize['host'] ?? '') === 'appcenter.intuit.com','authorization uses the official Intuit OAuth host');
$check(($authorizeQuery['scope'] ?? '') === 'com.intuit.quickbooks.accounting','OAuth requests only the accounting scope');
$check(($authorizeQuery['state'] ?? '') === 'state-fixture','OAuth state is carried through authorization');

$now = time();
$tokenTransport = static function (string $method, string $url, array $headers, ?string $body) use (&$checks,$check): array {
    parse_str((string)$body,$form);
    $check($method === 'POST' && $url === 'https://oauth.platform.intuit.com/oauth2/v1/tokens/bearer','token exchange uses Intuit HTTPS token endpoint');
    $check(in_array('Content-Type: application/x-www-form-urlencoded',$headers,true),'token exchange uses form encoding');
    $check(in_array('Authorization: Basic ' . base64_encode('local-client-id:local-client-secret'),$headers,true),'token exchange uses HTTP Basic client authentication');
    $check(($form['grant_type'] ?? '') === 'authorization_code' && ($form['code'] ?? '') === 'fixture-code','authorization code grant parameters are sent');
    return [200,json_encode(['access_token'=>'access-fixture','refresh_token'=>'refresh-fixture','expires_in'=>3600,'x_refresh_token_expires_in'=>7776000],JSON_THROW_ON_ERROR)];
};
$tokens = quickbooks_token_request('authorization_code',['code'=>'fixture-code','redirect_uri'=>quickbooks_redirect_uri()],$tokenTransport);
$check($tokens['access_token'] === 'access-fixture' && $tokens['refresh_token'] === 'refresh-fixture','token response returns both credentials');
$check($tokens['access_expires_at'] > $now && $tokens['refresh_expires_at'] > $tokens['access_expires_at'],'token expiries are recorded');
$refreshTransport = static function (string $method, string $url, array $headers, ?string $body) use ($check): array {
    parse_str((string)$body,$form);
    $check(($form['grant_type'] ?? '') === 'refresh_token' && ($form['refresh_token'] ?? '') === 'refresh-fixture','expired access can be refreshed with the stored refresh token');
    return [200,json_encode(['access_token'=>'refreshed-access','refresh_token'=>'rotated-refresh','expires_in'=>3600,'x_refresh_token_expires_in'=>7776000],JSON_THROW_ON_ERROR)];
};
$refreshed = quickbooks_token_request('refresh_token',['refresh_token'=>$tokens['refresh_token']],$refreshTransport);
$check($refreshed['access_token'] === 'refreshed-access' && $refreshed['refresh_token'] === 'rotated-refresh','refresh response replaces both rotating tokens');

$ciphertext = quickbooks_token_ciphertext($tokens);
$check(!str_contains($ciphertext,'access-fixture') && !str_contains($ciphertext,'refresh-fixture'),'persisted token payload is encrypted');
$check(quickbooks_decrypt_tokens($ciphertext) === $tokens,'encrypted token payload round-trips');
$throws(static fn()=>quickbooks_company_info('realm-not-numeric','fixture'), 'invalid company IDs are rejected');

$companyTransport = static function (string $method, string $url, array $headers, ?string $body) use ($check): array {
    $check($method === 'GET' && str_contains($url,'sandbox-quickbooks.api.intuit.com/v3/company/12345/companyinfo/12345'),'company verification uses sandbox Company Info endpoint');
    $check(in_array('Authorization: Bearer access-fixture',$headers,true),'company verification uses the supplied access token');
    return [200,json_encode(['CompanyInfo'=>['CompanyName'=>'Fixture Company']],JSON_THROW_ON_ERROR)];
};
$company = quickbooks_company_info('12345','access-fixture',$companyTransport);
$check($company['CompanyName'] === 'Fixture Company','company verification parses Intuit Company Info');

$revokeTransport = static function (string $method, string $url, array $headers, ?string $body) use ($check): array {
    $payload = json_decode((string)$body,true);
    $check($method === 'POST' && $url === 'https://developer.api.intuit.com/v2/oauth2/tokens/revoke','disconnect uses Intuit token revocation endpoint');
    $check(($payload['token'] ?? '') === 'refresh-fixture','disconnect revokes the stored refresh token');
    $check(in_array('Authorization: Basic ' . base64_encode('local-client-id:local-client-secret'),$headers,true),'token revocation uses client authentication');
    return [200,'{}'];
};
$check(quickbooks_revoke_tokens($tokens,$revokeTransport),'successful provider token revocation is recognized');

$unauthorizedTransport = static fn()=>[401,'{}'];
$throws(static fn()=>quickbooks_company_info('12345','invalid',$unauthorizedTransport),'unauthorized company verification fails without success-shaped fallback');
$throws(static fn()=>quickbooks_token_request('authorization_code',['code'=>'bad'],static fn()=>[400,'{"error":"invalid_grant"}']),'failed OAuth exchange is rejected');

putenv('QUICKBOOKS_ENVIRONMENT=production');
putenv('APP_ENV=local');
$check(!quickbooks_ready(),'production QuickBooks credentials cannot be used from local environment');
putenv('APP_ENV=production');
putenv('APP_URL=http://example.test');
$check(!quickbooks_ready(),'production QuickBooks connection requires HTTPS');
putenv('APP_URL=https://example.test');
$check(quickbooks_ready() === extension_loaded('curl'),'production setup requires HTTPS and cURL');
$check(str_starts_with(quickbooks_api_base(),'https://quickbooks.api.intuit.com'),'production mode selects the production API host');

echo "QuickBooks checks passed: $checks\n";
