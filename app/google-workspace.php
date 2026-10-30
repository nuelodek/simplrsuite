<?php
declare(strict_types=1);
require_once __DIR__ . '/secrets.php';
require_once __DIR__ . '/outbound.php';

class GoogleWorkspaceUnavailable extends RuntimeException {}

function google_workspace_ready(): bool {
    if (config('GOOGLE_WORKSPACE_CLIENT_ID') === ''
        || config('GOOGLE_WORKSPACE_CLIENT_SECRET') === ''
        || !extension_loaded('curl')) return false;
    if (config('APP_ENV') === 'production') {
        $parts = parse_url(config('APP_URL'));
        if (($parts['scheme'] ?? '') !== 'https') return false;
    }
    return true;
}

function google_workspace_redirect_uri(): string {
    return rtrim(config('APP_URL','http://127.0.0.1:8000'), '/') . '/users/admin/google-workspace.php';
}

function google_workspace_authorize_url(string $state): string {
    if (!google_workspace_ready()) throw new GoogleWorkspaceUnavailable('Google Workspace is not configured for this environment.');
    if ($state === '' || strlen($state) > 256) throw new InvalidArgumentException('Google OAuth state is invalid.');
    return 'https://accounts.google.com/o/oauth2/v2/auth?' . http_build_query([
        'client_id'=>config('GOOGLE_WORKSPACE_CLIENT_ID'),
        'response_type'=>'code',
        'redirect_uri'=>google_workspace_redirect_uri(),
        'scope'=>'openid email profile',
        'state'=>$state,
        'access_type'=>'online',
        'prompt'=>'select_account',
        'include_granted_scopes'=>'false',
    ]);
}

function google_workspace_http(string $method, string $url, array $headers, ?string $body = null, ?callable $transport = null): array {
    if ($transport !== null) {
        if (PHP_SAPI !== 'cli') throw new LogicException('Test transports are CLI-only.');
        $response = $transport($method,$url,$headers,$body);
        if (!is_array($response) || !is_int($response[0] ?? null) || !is_string($response[1] ?? null)) {
            throw new GoogleWorkspaceUnavailable('Google returned an invalid response.');
        }
        return [$response[0],$response[1]];
    }
    $parts = parse_url($url);
    if (($parts['scheme'] ?? '') !== 'https' || !in_array($parts['host'] ?? '',[
        'oauth2.googleapis.com','openidconnect.googleapis.com',
    ],true) || isset($parts['user']) || isset($parts['pass'])) {
        throw new LogicException('Google request target is invalid.');
    }
    $handle = secure_curl(curl_init($url));
    curl_setopt_array($handle,[
        CURLOPT_RETURNTRANSFER=>true,
        CURLOPT_CUSTOMREQUEST=>$method,
        CURLOPT_HTTPHEADER=>$headers,
        CURLOPT_CONNECTTIMEOUT=>5,
        CURLOPT_TIMEOUT=>15,
        CURLOPT_FOLLOWLOCATION=>false,
        CURLOPT_PROTOCOLS=>CURLPROTO_HTTPS,
        CURLOPT_SSL_VERIFYPEER=>true,
        CURLOPT_SSL_VERIFYHOST=>2,
    ]);
    if ($body !== null) curl_setopt($handle,CURLOPT_POSTFIELDS,$body);
    $raw = curl_exec($handle);
    $status = (int)curl_getinfo($handle,CURLINFO_RESPONSE_CODE);
    $error = curl_errno($handle);
    curl_close($handle);
    if (!is_string($raw) || $error || strlen($raw)>1048576) {
        throw new GoogleWorkspaceUnavailable('Google could not be reached. Check the connection and try again.');
    }
    return [$status,$raw];
}

function google_workspace_exchange_code(string $code, ?callable $transport = null): string {
    if (!google_workspace_ready()) throw new GoogleWorkspaceUnavailable('Google Workspace is not configured for this environment.');
    if ($code === '' || strlen($code) > 2048) throw new GoogleWorkspaceUnavailable('Google returned an invalid authorization code.');
    [$status,$raw] = google_workspace_http('POST','https://oauth2.googleapis.com/token',[
        'Accept: application/json',
        'Content-Type: application/x-www-form-urlencoded',
    ],http_build_query([
        'code'=>$code,
        'client_id'=>config('GOOGLE_WORKSPACE_CLIENT_ID'),
        'client_secret'=>config('GOOGLE_WORKSPACE_CLIENT_SECRET'),
        'redirect_uri'=>google_workspace_redirect_uri(),
        'grant_type'=>'authorization_code',
    ]),$transport);
    $result = json_decode($raw,true);
    if ($status < 200 || $status >= 300 || !is_array($result)
        || !is_string($result['access_token'] ?? null) || $result['access_token'] === ''
        || strlen($result['access_token']) > 8192
        || strtolower((string)($result['token_type'] ?? '')) !== 'bearer') {
        throw new GoogleWorkspaceUnavailable('Google could not complete authorization. Check the app credentials and callback URL.');
    }
    if (isset($result['scope'])) {
        $allowedScopes = ['openid','email','profile','https://www.googleapis.com/auth/userinfo.email','https://www.googleapis.com/auth/userinfo.profile'];
        $grantedScopes = is_string($result['scope']) ? preg_split('/\s+/',trim($result['scope'])) : false;
        if (!is_array($grantedScopes) || !$grantedScopes
            || array_diff($grantedScopes,$allowedScopes)) {
            throw new GoogleWorkspaceUnavailable('Google granted scopes beyond account identity. No connection was saved.');
        }
    }
    return $result['access_token'];
}

function google_workspace_userinfo(string $accessToken, ?callable $transport = null): array {
    if ($accessToken === '' || strlen($accessToken) > 8192) throw new GoogleWorkspaceUnavailable('Google returned an invalid access token.');
    [$status,$raw] = google_workspace_http('GET','https://openidconnect.googleapis.com/v1/userinfo',[
        'Accept: application/json',
        'Authorization: Bearer ' . $accessToken,
    ],null,$transport);
    $profile = json_decode($raw,true);
    if ($status < 200 || $status >= 300 || !is_array($profile)
        || !is_string($profile['sub'] ?? null) || $profile['sub'] === '' || strlen($profile['sub']) > 255
        || !is_string($profile['email'] ?? null) || filter_var($profile['email'],FILTER_VALIDATE_EMAIL) === false
        || ($profile['email_verified'] ?? null) !== true) {
        throw new GoogleWorkspaceUnavailable('Google could not verify the authorized account. Confirm the account and try again.');
    }
    return ['sub'=>$profile['sub'],'email'=>$profile['email']];
}

function google_workspace_connections(mysqli $db, int $companyId): array {
    return db_rows($db,"SELECT * FROM integration_connections WHERE company_id = ? AND provider = 'google-workspace' ORDER BY account_name,id",[$companyId]);
}

function google_workspace_connection(mysqli $db, int $companyId, int $connectionId): ?array {
    return db_one($db,"SELECT * FROM integration_connections WHERE id = ? AND company_id = ? AND provider = 'google-workspace'",[$connectionId,$companyId]);
}

function google_workspace_save_connection(mysqli $db, array $user, array $profile): void {
    if (!is_string($profile['sub'] ?? null) || $profile['sub'] === '' || strlen($profile['sub']) > 255
        || !is_string($profile['email'] ?? null) || filter_var($profile['email'],FILTER_VALIDATE_EMAIL) === false) {
        throw new InvalidArgumentException('Verified Google account information is invalid.');
    }
    $companyId = (int)$user['company_id'];
    $accountLogin = hash('sha256',$profile['sub']);
    $existing = db_one($db,"SELECT id FROM integration_connections WHERE company_id = ? AND provider = 'google-workspace' AND account_login = ? LIMIT 1",[$companyId,$accountLogin]);
    $values = [$profile['email'],$user['id']];
    if ($existing) {
        db_run($db,"UPDATE integration_connections SET auth_type = 'oauth', account_name = ?, account_url = '', token_ciphertext = '', token_scopes = 'openid email profile', connected_by = ?, connected_at = CURRENT_TIMESTAMP, last_checked_at = UTC_TIMESTAMP(), last_error = '' WHERE id = ? AND company_id = ? AND provider = 'google-workspace'",[
            $values[0],$values[1],(int)$existing['id'],$companyId,
        ]);
        return;
    }
    db_run($db,"INSERT INTO integration_connections (company_id,provider,auth_type,account_login,account_name,account_url,token_ciphertext,token_scopes,connected_by,last_checked_at,last_error) VALUES (?,'google-workspace','oauth',?,?, '', '', 'openid email profile', ?, UTC_TIMESTAMP(), '')",[
        $companyId,$accountLogin,$profile['email'],$user['id'],
    ]);
}
