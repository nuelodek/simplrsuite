<?php
declare(strict_types=1);
require_once __DIR__ . '/secrets.php';
require_once __DIR__ . '/outbound.php';

class WorkdayUnavailable extends RuntimeException {}

function workday_tenant(): string {
    $tenant = config('WORKDAY_TENANT');
    if ($tenant === '' || !preg_match('/^[A-Za-z0-9_-]{1,80}$/D',$tenant)) {
        throw new WorkdayUnavailable('The Workday tenant is not configured correctly.');
    }
    return $tenant;
}
function workday_host(): string {
    $host = strtolower(config('WORKDAY_HOST'));
    if ($host === '' || strlen($host)>253 || !preg_match('/^(?=.{1,253}$)(?:[a-z0-9](?:[a-z0-9-]{0,61}[a-z0-9])?\.)+(?:my)?workday\.com$/D',$host)) {
        throw new WorkdayUnavailable('The Workday service host must be a Workday domain, for example wd5.myworkday.com.');
    }
    return $host;
}
function workday_ready(): bool {
    if (config('WORKDAY_CLIENT_ID') === '' || config('WORKDAY_CLIENT_SECRET') === '' || !extension_loaded('curl')) return false;
    try {
        workday_host();
        workday_tenant();
    } catch (WorkdayUnavailable) {
        return false;
    }
    if (config('APP_ENV') === 'production') {
        $parts = parse_url(config('APP_URL'));
        if (($parts['scheme'] ?? '') !== 'https') return false;
    }
    return true;
}
function workday_redirect_uri(): string {
    return rtrim(config('APP_URL','http://127.0.0.1:8000'), '/') . '/users/admin/workday.php';
}
function workday_oauth_base(): string {
    if (!workday_ready()) throw new WorkdayUnavailable('Workday is not configured for this environment.');
    return 'https://' . workday_host() . '/ccx/oauth2/' . rawurlencode(workday_tenant());
}
function workday_authorize_url(string $state): string {
    $parameters = [
        'client_id'=>config('WORKDAY_CLIENT_ID'),
        'response_type'=>'code',
        'redirect_uri'=>workday_redirect_uri(),
        'state'=>$state,
    ];
    $scopes = trim(config('WORKDAY_SCOPES'));
    if ($scopes !== '') $parameters['scope']=$scopes;
    return workday_oauth_base() . '/authorize?' . http_build_query($parameters);
}
function workday_http(string $method, string $url, array $headers, ?string $body = null, ?callable $transport = null): array {
    if ($transport !== null) {
        if (PHP_SAPI !== 'cli') throw new LogicException('Test transports are CLI-only.');
        $response = $transport($method,$url,$headers,$body);
        if (!is_array($response) || !is_int($response[0] ?? null) || !is_string($response[1] ?? null)) {
            throw new WorkdayUnavailable('Workday returned an invalid response.');
        }
        return [$response[0],$response[1]];
    }
    $parts = parse_url($url);
    if (($parts['scheme'] ?? '') !== 'https' || ($parts['host'] ?? '') !== workday_host()
        || isset($parts['user']) || isset($parts['pass'])) {
        throw new LogicException('Workday request target is invalid.');
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
        throw new WorkdayUnavailable('Workday could not be reached. Check the tenant host and try again.');
    }
    return [$status,$raw];
}
function workday_token_request(string $grantType, array $parameters, ?callable $transport = null): array {
    if (!workday_ready()) throw new WorkdayUnavailable('Workday is not configured for this environment.');
    if (!in_array($grantType,['authorization_code','refresh_token'],true)) throw new InvalidArgumentException('Unsupported Workday OAuth grant.');
    [$status,$raw] = workday_http('POST',workday_oauth_base() . '/token',[
        'Accept: application/json',
        'Content-Type: application/x-www-form-urlencoded',
        'Authorization: Basic ' . base64_encode(config('WORKDAY_CLIENT_ID') . ':' . config('WORKDAY_CLIENT_SECRET')),
    ],http_build_query(['grant_type'=>$grantType] + $parameters),$transport);
    $result = json_decode($raw,true);
    $expiresIn = filter_var($result['expires_in'] ?? null,FILTER_VALIDATE_INT);
    if ($status < 200 || $status >= 300 || !is_array($result)
        || !is_string($result['access_token'] ?? null) || $result['access_token'] === ''
        || !is_string($result['refresh_token'] ?? null) || $result['refresh_token'] === ''
        || $expiresIn === false || $expiresIn < 1) {
        throw new WorkdayUnavailable('Workday could not complete authorization. Check the tenant, OAuth client, scopes and callback URL.');
    }
    $refreshExpiresIn = filter_var($result['refresh_token_expires_in'] ?? null,FILTER_VALIDATE_INT);
    return [
        'access_token'=>$result['access_token'],
        'refresh_token'=>$result['refresh_token'],
        'access_expires_at'=>time()+$expiresIn,
        'refresh_expires_at'=>$refreshExpiresIn !== false && $refreshExpiresIn > 0 ? time()+$refreshExpiresIn : 0,
    ];
}
function workday_token_ciphertext(array $tokens): string {
    foreach (['access_token','refresh_token'] as $key) {
        if (!is_string($tokens[$key] ?? null) || $tokens[$key] === '') throw new InvalidArgumentException('Workday token data is incomplete.');
    }
    if (!is_int($tokens['access_expires_at'] ?? null) || $tokens['access_expires_at'] <= time()
        || !is_int($tokens['refresh_expires_at'] ?? null) || ($tokens['refresh_expires_at'] !== 0 && $tokens['refresh_expires_at'] <= time())) {
        throw new InvalidArgumentException('Workday token expiry is invalid.');
    }
    return encrypt_secret(json_encode($tokens,JSON_THROW_ON_ERROR));
}
function workday_decrypt_tokens(string $ciphertext): array {
    $tokens = json_decode(decrypt_secret($ciphertext),true);
    if (!is_array($tokens) || !is_string($tokens['access_token'] ?? null) || !is_string($tokens['refresh_token'] ?? null)
        || !is_int($tokens['access_expires_at'] ?? null) || !is_int($tokens['refresh_expires_at'] ?? null)) {
        throw new WorkdayUnavailable('The saved Workday connection needs to be reconnected.');
    }
    return $tokens;
}
function workday_connections(mysqli $db, int $companyId): array {
    return db_rows($db,"SELECT * FROM integration_connections WHERE company_id = ? AND provider = 'workday' ORDER BY id",[$companyId]);
}
function workday_connection(mysqli $db, int $companyId, int $connectionId): ?array {
    return db_one($db,"SELECT * FROM integration_connections WHERE id = ? AND company_id = ? AND provider = 'workday'",[$connectionId,$companyId]);
}
function workday_save_connection(mysqli $db, array $user, array $tokens): void {
    $tenant = workday_tenant();
    $companyId = (int)$user['company_id'];
    $existing = db_one($db,"SELECT * FROM integration_connections WHERE company_id = ? AND provider = 'workday' AND account_login = ? LIMIT 1",[$companyId,$tenant]);
    $ciphertext = workday_token_ciphertext($tokens);
    $accountName = 'Workday (' . $tenant . ')';
    $scopes = trim(config('WORKDAY_SCOPES'));
    if ($existing) {
        db_run($db,"UPDATE integration_connections SET auth_type = 'oauth', account_name = ?, account_url = '', token_ciphertext = ?, token_scopes = ?, connected_by = ?, connected_at = CURRENT_TIMESTAMP, last_checked_at = UTC_TIMESTAMP(), last_error = '' WHERE id = ? AND company_id = ? AND provider = 'workday'",[
            $accountName,$ciphertext,$scopes,$user['id'],(int)$existing['id'],$companyId,
        ]);
        return;
    }
    db_run($db,"INSERT INTO integration_connections (company_id,provider,auth_type,account_login,account_name,token_ciphertext,token_scopes,connected_by,last_checked_at,last_error) VALUES (?,'workday','oauth',?,?,?,?,?,UTC_TIMESTAMP(),'')",[
        $companyId,$tenant,$accountName,$ciphertext,$scopes,$user['id'],
    ]);
}
function workday_refresh_connection(mysqli $db, array $connection, ?callable $transport = null): array {
    $tokens = workday_decrypt_tokens($connection['token_ciphertext']);
    if ($tokens['refresh_expires_at'] > 0 && $tokens['refresh_expires_at'] <= time()) {
        throw new WorkdayUnavailable('The Workday refresh token has expired. Reconnect the tenant.');
    }
    $refreshed = workday_token_request('refresh_token',['refresh_token'=>$tokens['refresh_token']],$transport);
    $ciphertext = workday_token_ciphertext($refreshed);
    db_run($db,"UPDATE integration_connections SET token_ciphertext = ?, last_checked_at = UTC_TIMESTAMP(), last_error = '' WHERE id = ? AND company_id = ? AND provider = 'workday'",[
        $ciphertext,(int)$connection['id'],(int)$connection['company_id'],
    ]);
    return $refreshed;
}
