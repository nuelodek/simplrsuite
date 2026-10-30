<?php
declare(strict_types=1);
require_once __DIR__ . '/../app/google-workspace.php';

$checks = 0;
$check = static function (bool $condition, string $description) use (&$checks): void {
    if (!$condition) { fwrite(STDERR,'Failed: ' . $description . PHP_EOL); exit(1); }
    $checks++;
};
$throws = static function (callable $call, string $description) use ($check): void {
    try { $call(); } catch (GoogleWorkspaceUnavailable|InvalidArgumentException $error) { $check(true,$description); return; }
    $check(false,$description);
};

putenv('APP_ENV=local');
putenv('APP_URL=http://127.0.0.1:8000');
putenv('GOOGLE_WORKSPACE_CLIENT_ID=local-google-client-id');
putenv('GOOGLE_WORKSPACE_CLIENT_SECRET=local-google-client-secret');

$check(google_workspace_ready() === extension_loaded('curl'),'local readiness follows required cURL availability');
$check(google_workspace_redirect_uri() === 'http://127.0.0.1:8000/users/admin/google-workspace.php','redirect URI uses configured app origin and connector route');
$authorize = parse_url(google_workspace_authorize_url('state-fixture'));
parse_str($authorize['query'] ?? '',$authorizeQuery);
$check(($authorize['host'] ?? '') === 'accounts.google.com','authorization uses the official Google OAuth host');
$check(($authorize['path'] ?? '') === '/o/oauth2/v2/auth','authorization uses the OAuth 2.0 endpoint');
$check(($authorizeQuery['scope'] ?? '') === 'openid email profile','authorization requests only basic account identity scopes');
$check(($authorizeQuery['state'] ?? '') === 'state-fixture','OAuth state is carried through authorization');
$check(($authorizeQuery['access_type'] ?? '') === 'online' && !isset($authorizeQuery['offline_access']),'authorization does not request persistent offline access');
$check(($authorizeQuery['include_granted_scopes'] ?? '') === 'false','authorization does not opt into previously granted scopes');
$throws(static fn()=>google_workspace_authorize_url(''),'empty OAuth state is rejected');

$tokenTransport = static function (string $method, string $url, array $headers, ?string $body) use ($check): array {
    parse_str((string)$body,$form);
    $check($method === 'POST' && $url === 'https://oauth2.googleapis.com/token','code exchange uses the official HTTPS token endpoint');
    $check(in_array('Content-Type: application/x-www-form-urlencoded',$headers,true),'code exchange uses form encoding');
    $check(($form['client_id'] ?? '') === 'local-google-client-id'
        && ($form['client_secret'] ?? '') === 'local-google-client-secret','client credentials are submitted to Google token endpoint');
    $check(($form['grant_type'] ?? '') === 'authorization_code'
        && ($form['code'] ?? '') === 'fixture-code'
        && ($form['redirect_uri'] ?? '') === google_workspace_redirect_uri(),'code exchange binds the callback URI');
    return [200,json_encode([
        'access_token'=>'identity-only-access-fixture',
        'token_type'=>'Bearer',
        'expires_in'=>3600,
        'scope'=>'openid https://www.googleapis.com/auth/userinfo.email https://www.googleapis.com/auth/userinfo.profile',
    ],JSON_THROW_ON_ERROR)];
};
$accessToken = google_workspace_exchange_code('fixture-code',$tokenTransport);
$check($accessToken === 'identity-only-access-fixture','code exchange returns only the short-lived access token');

$userinfoTransport = static function (string $method, string $url, array $headers, ?string $body) use ($check): array {
    $check($method === 'GET' && $url === 'https://openidconnect.googleapis.com/v1/userinfo','identity verification uses Google OpenID Connect userinfo');
    $check(in_array('Authorization: Bearer identity-only-access-fixture',$headers,true),'userinfo request uses the transient access token');
    return [200,json_encode(['sub'=>'fixture-google-subject','email'=>'person@example.test','email_verified'=>true],JSON_THROW_ON_ERROR)];
};
$profile = google_workspace_userinfo($accessToken,$userinfoTransport);
$check($profile === ['sub'=>'fixture-google-subject','email'=>'person@example.test'],'verified identity is limited to subject and email');

$throws(static fn()=>google_workspace_exchange_code('',static fn()=>[200,'{}']),'empty authorization codes are rejected');
$throws(static fn()=>google_workspace_exchange_code('bad',static fn()=>[400,'{"error":"invalid_grant"}']),'failed Google code exchange is rejected');
$throws(static fn()=>google_workspace_exchange_code('bad',static fn()=>[200,'{"access_token":"x","token_type":"Bearer","scope":"https://www.googleapis.com/auth/gmail.readonly"}']),'unexpected granted scopes are rejected');
$throws(static fn()=>google_workspace_userinfo('token',static fn()=>[200,'{"sub":"s","email":"person@example.test","email_verified":false}']),'unverified Google email is rejected');
$throws(static fn()=>google_workspace_userinfo('token',static fn()=>[401,'{"error":"invalid_token"}']),'failed identity verification is rejected');
$throws(static fn()=>google_workspace_userinfo('',static fn()=>[200,'{}']),'empty access tokens are rejected');

$hostRejected = false;
try { google_workspace_http('GET','https://attacker.example/userinfo',[],null); }
catch (LogicException) { $hostRejected = true; }
$check($hostRejected,'requests to non-Google hosts are rejected');

putenv('APP_ENV=production');
putenv('APP_URL=http://example.test');
$check(!google_workspace_ready(),'production OAuth requires HTTPS');
putenv('APP_URL=https://example.test');
$check(google_workspace_ready() === extension_loaded('curl'),'production setup requires HTTPS and cURL');

echo "Google Workspace checks passed: $checks\n";
