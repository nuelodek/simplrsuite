<?php
declare(strict_types=1);
require_once __DIR__ . '/secrets.php';
require_once __DIR__ . '/outbound.php';

class ZohoBooksUnavailable extends RuntimeException {}

function zoho_books_data_centers(): array {
    return [
        'com'=>['oauth'=>'accounts.zoho.com','api'=>'www.zohoapis.com'],
        'eu'=>['oauth'=>'accounts.zoho.eu','api'=>'www.zohoapis.eu'],
        'in'=>['oauth'=>'accounts.zoho.in','api'=>'www.zohoapis.in'],
        'com.au'=>['oauth'=>'accounts.zoho.com.au','api'=>'www.zohoapis.com.au'],
        'jp'=>['oauth'=>'accounts.zoho.jp','api'=>'www.zohoapis.jp'],
        'com.cn'=>['oauth'=>'accounts.zoho.com.cn','api'=>'www.zohoapis.com.cn'],
        'sa'=>['oauth'=>'accounts.zoho.sa','api'=>'www.zohoapis.sa'],
    ];
}
function zoho_books_region(): string {
    $region = config('ZOHO_BOOKS_REGION','com');
    if (!array_key_exists($region,zoho_books_data_centers())) throw new ZohoBooksUnavailable('The Zoho Books data center is not configured correctly.');
    return $region;
}
function zoho_books_oauth_host(?string $region = null): string {
    $region ??= zoho_books_region();
    $host = zoho_books_data_centers()[$region]['oauth'] ?? null;
    if (!is_string($host)) throw new ZohoBooksUnavailable('The Zoho Books data center is not supported.');
    return $host;
}
function zoho_books_api_host(?string $region = null): string {
    $region ??= zoho_books_region();
    $host = zoho_books_data_centers()[$region]['api'] ?? null;
    if (!is_string($host)) throw new ZohoBooksUnavailable('The Zoho Books data center is not supported.');
    return $host;
}
function zoho_books_ready(?string $region = null): bool {
    if (config('ZOHO_BOOKS_CLIENT_ID') === '' || config('ZOHO_BOOKS_CLIENT_SECRET') === '' || !extension_loaded('curl')) return false;
    try {
        $region ??= zoho_books_region();
        if (!array_key_exists($region,zoho_books_data_centers())) return false;
    } catch (ZohoBooksUnavailable) {
        return false;
    }
    if (config('APP_ENV') === 'production') {
        $parts = parse_url(config('APP_URL'));
        if (($parts['scheme'] ?? '') !== 'https') return false;
    }
    return true;
}
function zoho_books_redirect_uri(): string {
    return rtrim(config('APP_URL','http://127.0.0.1:8000'), '/') . '/users/admin/zoho-books.php';
}
function zoho_books_authorize_url(string $state): string {
    if (!zoho_books_ready()) throw new ZohoBooksUnavailable('Zoho Books is not configured for this environment.');
    return 'https://' . zoho_books_oauth_host() . '/oauth/v2/auth?' . http_build_query([
        'client_id'=>config('ZOHO_BOOKS_CLIENT_ID'),
        'response_type'=>'code',
        'redirect_uri'=>zoho_books_redirect_uri(),
        'scope'=>'ZohoBooks.settings.READ',
        'access_type'=>'offline',
        'prompt'=>'consent',
        'state'=>$state,
    ]);
}
function zoho_books_http(string $method, string $url, array $headers, ?string $body = null, ?callable $transport = null): array {
    if ($transport !== null) {
        if (PHP_SAPI !== 'cli') throw new LogicException('Test transports are CLI-only.');
        $response = $transport($method,$url,$headers,$body);
        if (!is_array($response) || !is_int($response[0] ?? null) || !is_string($response[1] ?? null)) {
            throw new ZohoBooksUnavailable('Zoho Books returned an invalid response.');
        }
        return [$response[0],$response[1]];
    }
    $parts = parse_url($url);
    $allowedHosts = [];
    foreach (zoho_books_data_centers() as $domains) {
        $allowedHosts[] = $domains['oauth'];
        $allowedHosts[] = $domains['api'];
    }
    if (($parts['scheme'] ?? '') !== 'https' || !in_array($parts['host'] ?? '',$allowedHosts,true)
        || isset($parts['user']) || isset($parts['pass'])) {
        throw new LogicException('Zoho Books request target is invalid.');
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
        throw new ZohoBooksUnavailable('Zoho Books could not be reached. Check the connection and try again.');
    }
    return [$status,$raw];
}
function zoho_books_token_request(string $grantType, array $parameters, ?string $currentRefreshToken = null, ?callable $transport = null, ?string $region = null): array {
    $region ??= zoho_books_region();
    if (!zoho_books_ready($region)) throw new ZohoBooksUnavailable('Zoho Books is not configured for this environment.');
    if (!in_array($grantType,['authorization_code','refresh_token'],true)) throw new InvalidArgumentException('Unsupported Zoho OAuth grant.');
    [$status,$raw] = zoho_books_http('POST','https://' . zoho_books_oauth_host($region) . '/oauth/v2/token',[
        'Accept: application/json',
        'Content-Type: application/x-www-form-urlencoded',
    ],http_build_query([
        'grant_type'=>$grantType,
        'client_id'=>config('ZOHO_BOOKS_CLIENT_ID'),
        'client_secret'=>config('ZOHO_BOOKS_CLIENT_SECRET'),
    ] + $parameters),$transport);
    $result = json_decode($raw,true);
    $expiresIn = is_array($result) ? filter_var($result['expires_in_sec'] ?? $result['expires_in'] ?? null,FILTER_VALIDATE_INT) : false;
    $refreshToken = is_array($result) && is_string($result['refresh_token'] ?? null) && $result['refresh_token'] !== ''
        ? $result['refresh_token']
        : $currentRefreshToken;
    if ($status < 200 || $status >= 300 || !is_array($result)
        || !is_string($result['access_token'] ?? null) || $result['access_token'] === ''
        || !is_string($refreshToken) || $refreshToken === ''
        || $expiresIn === false || $expiresIn < 1) {
        throw new ZohoBooksUnavailable('Zoho Books could not complete authorization. Check the client credentials, data center and callback URL.');
    }
    return [
        'access_token'=>$result['access_token'],
        'refresh_token'=>$refreshToken,
        'access_expires_at'=>time()+$expiresIn,
        'refresh_expires_at'=>0,
        'region'=>$region,
    ];
}
function zoho_books_token_ciphertext(array $tokens): string {
    foreach (['access_token','refresh_token'] as $key) {
        if (!is_string($tokens[$key] ?? null) || $tokens[$key] === '') throw new InvalidArgumentException('Zoho Books token data is incomplete.');
    }
    if (!is_int($tokens['access_expires_at'] ?? null) || $tokens['access_expires_at'] <= time()
        || !is_int($tokens['refresh_expires_at'] ?? null) || ($tokens['refresh_expires_at'] !== 0 && $tokens['refresh_expires_at'] <= time())
        || !is_string($tokens['region'] ?? null) || !array_key_exists($tokens['region'],zoho_books_data_centers())) {
        throw new InvalidArgumentException('Zoho Books token metadata is invalid.');
    }
    return encrypt_secret(json_encode($tokens,JSON_THROW_ON_ERROR));
}
function zoho_books_decrypt_tokens(string $ciphertext): array {
    $tokens = json_decode(decrypt_secret($ciphertext),true);
    if (!is_array($tokens) || !is_string($tokens['access_token'] ?? null) || !is_string($tokens['refresh_token'] ?? null)
        || !is_int($tokens['access_expires_at'] ?? null) || !is_int($tokens['refresh_expires_at'] ?? null)
        || !is_string($tokens['region'] ?? null) || !array_key_exists($tokens['region'],zoho_books_data_centers())) {
        throw new ZohoBooksUnavailable('The saved Zoho Books connection needs to be reconnected.');
    }
    return $tokens;
}
function zoho_books_organizations(string $accessToken, ?string $region = null, ?callable $transport = null): array {
    $region ??= zoho_books_region();
    if ($accessToken === '') throw new ZohoBooksUnavailable('Zoho Books returned an invalid access token.');
    $url = 'https://' . zoho_books_api_host($region) . '/books/v3/organizations';
    [$status,$raw] = zoho_books_http('GET',$url,[
        'Accept: application/json',
        'Authorization: Zoho-oauthtoken ' . $accessToken,
    ],null,$transport);
    $result = json_decode($raw,true);
    $organizations = $result['organizations'] ?? null;
    if ($status < 200 || $status >= 300 || !is_array($organizations)) {
        throw new ZohoBooksUnavailable('Zoho Books could not list the organizations available to this account.');
    }
    $valid = [];
    foreach ($organizations as $organization) {
        $id = is_array($organization) ? (string)($organization['organization_id'] ?? '') : '';
        $name = is_array($organization) ? trim((string)($organization['name'] ?? '')) : '';
        if (!preg_match('/^[0-9]{1,32}$/D',$id) || $name === '' || strlen($name)>160) continue;
        $valid[] = ['organization_id'=>$id,'name'=>$name];
    }
    if (!$valid) throw new ZohoBooksUnavailable('No Zoho Books organizations were found for this account.');
    return $valid;
}
function zoho_books_organization_info(string $organizationId, string $accessToken, string $region, ?callable $transport = null): array {
    if (!preg_match('/^[0-9]{1,32}$/D',$organizationId) || $accessToken === '') {
        throw new ZohoBooksUnavailable('Zoho Books returned an invalid organization connection.');
    }
    $url = 'https://' . zoho_books_api_host($region) . '/books/v3/organizations/' . rawurlencode($organizationId);
    [$status,$raw] = zoho_books_http('GET',$url,[
        'Accept: application/json',
        'Authorization: Zoho-oauthtoken ' . $accessToken,
    ],null,$transport);
    $result = json_decode($raw,true);
    $organization = $result['organization'] ?? null;
    if ($status < 200 || $status >= 300 || !is_array($organization)
        || (string)($organization['organization_id'] ?? '') !== $organizationId
        || !is_string($organization['name'] ?? null) || trim($organization['name']) === '') {
        throw new ZohoBooksUnavailable($status === 401
            ? 'Zoho Books rejected this connection. Reconnect the organization.'
            : 'Zoho Books could not verify the selected organization.');
    }
    return $organization;
}
function zoho_books_connections(mysqli $db, int $companyId): array {
    return db_rows($db,"SELECT * FROM integration_connections WHERE company_id = ? AND provider = 'zoho_books' ORDER BY id",[$companyId]);
}
function zoho_books_connection(mysqli $db, int $companyId, int $connectionId): ?array {
    return db_one($db,"SELECT * FROM integration_connections WHERE id = ? AND company_id = ? AND provider = 'zoho_books'",[$connectionId,$companyId]);
}
function zoho_books_save_connection(mysqli $db, array $user, string $organizationId, array $organization, array $tokens): void {
    if (!preg_match('/^[0-9]{1,32}$/D',$organizationId)) throw new ZohoBooksUnavailable('Zoho Books returned an invalid organization identifier.');
    $name = trim((string)($organization['name'] ?? ''));
    if ($name === '' || strlen($name)>160) throw new ZohoBooksUnavailable('Zoho Books returned an invalid organization name.');
    $companyId = (int)$user['company_id'];
    $accountLogin = $tokens['region'] . ':' . $organizationId;
    $existing = db_one($db,"SELECT * FROM integration_connections WHERE company_id = ? AND provider = 'zoho_books' AND account_login = ? LIMIT 1",[$companyId,$accountLogin]);
    $ciphertext = zoho_books_token_ciphertext($tokens);
    if ($existing) {
        db_run($db,"UPDATE integration_connections SET auth_type = 'oauth', account_name = ?, account_url = '', token_ciphertext = ?, token_scopes = 'ZohoBooks.settings.READ', connected_by = ?, connected_at = CURRENT_TIMESTAMP, last_checked_at = UTC_TIMESTAMP(), last_error = '' WHERE id = ? AND company_id = ? AND provider = 'zoho_books'",[
            $name,$ciphertext,$user['id'],(int)$existing['id'],$companyId,
        ]);
        return;
    }
    db_run($db,"INSERT INTO integration_connections (company_id,provider,auth_type,account_login,account_name,token_ciphertext,token_scopes,connected_by,last_checked_at,last_error) VALUES (?,'zoho_books','oauth',?,?,?,'ZohoBooks.settings.READ',?,UTC_TIMESTAMP(),'')",[
        $companyId,$accountLogin,$name,$ciphertext,$user['id'],
    ]);
}
function zoho_books_refresh_connection(mysqli $db, array $connection, ?callable $transport = null): array {
    $tokens = zoho_books_decrypt_tokens($connection['token_ciphertext']);
    if ($tokens['access_expires_at'] > time()+120) return $tokens;
    $refreshed = zoho_books_token_request('refresh_token',['refresh_token'=>$tokens['refresh_token']],$tokens['refresh_token'],$transport,$tokens['region']);
    $ciphertext = zoho_books_token_ciphertext($refreshed);
    db_run($db,"UPDATE integration_connections SET token_ciphertext = ?, last_error = '' WHERE id = ? AND company_id = ? AND provider = 'zoho_books'",[
        $ciphertext,(int)$connection['id'],(int)$connection['company_id'],
    ]);
    return $refreshed;
}
function zoho_books_verify_connection(mysqli $db, array $connection, ?callable $transport = null): array {
    try {
        $tokens = zoho_books_refresh_connection($db,$connection,$transport);
        $organizationId = substr((string)$connection['account_login'],strlen($tokens['region'])+1);
        $organization = zoho_books_organization_info($organizationId,$tokens['access_token'],$tokens['region'],$transport);
        db_run($db,"UPDATE integration_connections SET account_name = ?, last_checked_at = UTC_TIMESTAMP(), last_error = '' WHERE id = ? AND company_id = ? AND provider = 'zoho_books'",[
            trim($organization['name']),(int)$connection['id'],(int)$connection['company_id'],
        ]);
        return $organization;
    } catch (ZohoBooksUnavailable $error) {
        db_run($db,"UPDATE integration_connections SET last_error = ?, last_checked_at = UTC_TIMESTAMP() WHERE id = ? AND company_id = ? AND provider = 'zoho_books'",[
            mb_substr($error->getMessage(),0,255),(int)$connection['id'],(int)$connection['company_id'],
        ]);
        throw $error;
    }
}
function zoho_books_revoke_token(array $tokens, ?callable $transport = null): bool {
    if (!zoho_books_ready((string)($tokens['region'] ?? ''))) throw new ZohoBooksUnavailable('Zoho Books is not configured for provider-side revocation.');
    if (!is_string($tokens['refresh_token'] ?? null) || $tokens['refresh_token'] === '') {
        throw new ZohoBooksUnavailable('The saved Zoho Books refresh token is unavailable for revocation.');
    }
    $url = 'https://' . zoho_books_oauth_host((string)$tokens['region']) . '/oauth/v2/token/revoke';
    [$status] = zoho_books_http('POST',$url,[
        'Accept: application/json',
        'Content-Type: application/x-www-form-urlencoded',
    ],http_build_query([
        'token'=>$tokens['refresh_token'],
        'client_id'=>config('ZOHO_BOOKS_CLIENT_ID'),
        'client_secret'=>config('ZOHO_BOOKS_CLIENT_SECRET'),
    ]),$transport);
    return $status >= 200 && $status < 300;
}
