<?php
declare(strict_types=1);
require_once __DIR__ . '/secrets.php';
require_once __DIR__ . '/outbound.php';

class XeroUnavailable extends RuntimeException {}

function xero_ready(): bool {
    if (config('XERO_CLIENT_ID') === '' || config('XERO_CLIENT_SECRET') === '' || !extension_loaded('curl')) return false;
    if (config('APP_ENV') === 'production') {
        $parts = parse_url(config('APP_URL'));
        if (($parts['scheme'] ?? '') !== 'https') return false;
    }
    return true;
}
function xero_redirect_uri(): string {
    return rtrim(config('APP_URL','http://127.0.0.1:8000'), '/') . '/users/admin/xero.php';
}
function xero_authorize_url(string $state): string {
    if (!xero_ready()) throw new XeroUnavailable('Xero is not configured for this environment.');
    return 'https://login.xero.com/identity/connect/authorize?' . http_build_query([
        'client_id'=>config('XERO_CLIENT_ID'),
        'response_type'=>'code',
        'redirect_uri'=>xero_redirect_uri(),
        'scope'=>'offline_access accounting.settings.read',
        'state'=>$state,
    ]);
}
function xero_http(string $method, string $url, array $headers, ?string $body = null, ?callable $transport = null): array {
    if ($transport !== null) {
        if (PHP_SAPI !== 'cli') throw new LogicException('Test transports are CLI-only.');
        $response = $transport($method,$url,$headers,$body);
        if (!is_array($response) || !is_int($response[0] ?? null) || !is_string($response[1] ?? null)) {
            throw new XeroUnavailable('Xero returned an invalid response.');
        }
        return [$response[0],$response[1]];
    }
    $parts = parse_url($url);
    if (($parts['scheme'] ?? '') !== 'https' || !in_array($parts['host'] ?? '',[
        'login.xero.com','identity.xero.com','api.xero.com',
    ],true) || isset($parts['user']) || isset($parts['pass'])) {
        throw new LogicException('Xero request target is invalid.');
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
        throw new XeroUnavailable('Xero could not be reached. Check the connection and try again.');
    }
    return [$status,$raw];
}
function xero_token_request(string $grantType, array $parameters, ?callable $transport = null): array {
    if (!xero_ready()) throw new XeroUnavailable('Xero is not configured for this environment.');
    if (!in_array($grantType,['authorization_code','refresh_token'],true)) throw new InvalidArgumentException('Unsupported Xero OAuth grant.');
    [$status,$raw] = xero_http('POST','https://identity.xero.com/connect/token',[
        'Accept: application/json',
        'Content-Type: application/x-www-form-urlencoded',
        'Authorization: Basic ' . base64_encode(config('XERO_CLIENT_ID') . ':' . config('XERO_CLIENT_SECRET')),
    ],http_build_query(['grant_type'=>$grantType] + $parameters),$transport);
    $result = json_decode($raw,true);
    if ($status < 200 || $status >= 300 || !is_array($result)
        || !is_string($result['access_token'] ?? null) || $result['access_token'] === ''
        || !is_string($result['refresh_token'] ?? null) || $result['refresh_token'] === ''
        || !is_int($result['expires_in'] ?? null) || $result['expires_in'] < 1) {
        throw new XeroUnavailable('Xero could not complete authorization. Check the app credentials and callback URL.');
    }
    return [
        'access_token'=>$result['access_token'],
        'refresh_token'=>$result['refresh_token'],
        'access_expires_at'=>time()+$result['expires_in'],
        'refresh_expires_at'=>time()+60*86400,
    ];
}
function xero_token_ciphertext(array $tokens): string {
    foreach (['access_token','refresh_token'] as $key) {
        if (!is_string($tokens[$key] ?? null) || $tokens[$key] === '') throw new InvalidArgumentException('Xero token data is incomplete.');
    }
    foreach (['access_expires_at','refresh_expires_at'] as $key) {
        if (!is_int($tokens[$key] ?? null) || $tokens[$key] <= time()) throw new InvalidArgumentException('Xero token expiry is invalid.');
    }
    return encrypt_secret(json_encode($tokens,JSON_THROW_ON_ERROR));
}
function xero_decrypt_tokens(string $ciphertext): array {
    $tokens = json_decode(decrypt_secret($ciphertext),true);
    if (!is_array($tokens) || !is_string($tokens['access_token'] ?? null) || !is_string($tokens['refresh_token'] ?? null)
        || !is_int($tokens['access_expires_at'] ?? null) || !is_int($tokens['refresh_expires_at'] ?? null)) {
        throw new XeroUnavailable('The saved Xero connection needs to be reconnected.');
    }
    return $tokens;
}
function xero_tenants(string $accessToken, ?callable $transport = null): array {
    if ($accessToken === '') throw new XeroUnavailable('Xero returned an invalid access token.');
    [$status,$raw] = xero_http('GET','https://api.xero.com/connections',[
        'Accept: application/json',
        'Authorization: Bearer ' . $accessToken,
    ],null,$transport);
    $result = json_decode($raw,true);
    if ($status < 200 || $status >= 300 || !is_array($result)) throw new XeroUnavailable('Xero could not list the organizations available to this account.');
    $tenants = [];
    foreach ($result as $tenant) {
        if (!is_array($tenant) || ($tenant['tenantType'] ?? '') !== 'ORGANISATION'
            || !is_string($tenant['tenantId'] ?? null) || !preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[1-8][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/iD',$tenant['tenantId'])
            || !is_string($tenant['tenantName'] ?? null) || trim($tenant['tenantName']) === '') continue;
        $tenants[] = ['tenant_id'=>$tenant['tenantId'],'tenant_name'=>trim($tenant['tenantName'])];
    }
    if (!$tenants) throw new XeroUnavailable('No Xero organizations were found for this account. Connect an organization and try again.');
    return $tenants;
}
function xero_organisation_info(string $tenantId, string $accessToken, ?callable $transport = null): array {
    if (!preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[1-8][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/iD',$tenantId) || $accessToken === '') {
        throw new XeroUnavailable('Xero returned an invalid organization connection.');
    }
    [$status,$raw] = xero_http('GET','https://api.xero.com/api.xro/2.0/Organisation',[
        'Accept: application/json',
        'Authorization: Bearer ' . $accessToken,
        'Xero-tenant-id: ' . $tenantId,
    ],null,$transport);
    $result = json_decode($raw,true);
    $organisations = $result['Organisations'] ?? null;
    $organisation = is_array($organisations) ? ($organisations[0] ?? null) : null;
    if ($status < 200 || $status >= 300 || !is_array($organisation)
        || !is_string($organisation['Name'] ?? null) || trim($organisation['Name']) === '') {
        throw new XeroUnavailable($status === 401
            ? 'Xero rejected this connection. Reconnect the organization.'
            : 'Xero could not verify the selected organization.');
    }
    return $organisation;
}
function xero_revoke_tokens(array $tokens, ?callable $transport = null): bool {
    if (!xero_ready()) throw new XeroUnavailable('Xero is not configured for provider-side revocation.');
    if (!is_string($tokens['refresh_token'] ?? null) || $tokens['refresh_token'] === '') {
        throw new XeroUnavailable('The saved Xero refresh token is unavailable for revocation.');
    }
    [$status] = xero_http('POST','https://identity.xero.com/connect/revocation',[
        'Accept: application/json',
        'Content-Type: application/x-www-form-urlencoded',
        'Authorization: Basic ' . base64_encode(config('XERO_CLIENT_ID') . ':' . config('XERO_CLIENT_SECRET')),
    ],http_build_query(['token'=>$tokens['refresh_token']]),$transport);
    return $status >= 200 && $status < 300;
}
function xero_connections(mysqli $db, int $companyId): array {
    return db_rows($db,"SELECT * FROM integration_connections WHERE company_id = ? AND provider = 'xero' ORDER BY id",[$companyId]);
}
function xero_connection(mysqli $db, int $companyId, int $connectionId): ?array {
    return db_one($db,"SELECT * FROM integration_connections WHERE id = ? AND company_id = ? AND provider = 'xero'",[$connectionId,$companyId]);
}
function xero_save_connection(mysqli $db, array $user, string $tenantId, array $organisation, array $tokens): void {
    if (!preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[1-8][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/iD',$tenantId)) {
        throw new XeroUnavailable('Xero returned an invalid organization identifier.');
    }
    $name = trim((string)($organisation['Name'] ?? ''));
    if ($name === '' || strlen($name)>160) throw new XeroUnavailable('Xero returned an invalid organization name.');
    $companyId = (int)$user['company_id'];
    $existing = db_one($db,"SELECT * FROM integration_connections WHERE company_id = ? AND provider = 'xero' AND account_login = ? LIMIT 1",[$companyId,$tenantId]);
    $ciphertext = xero_token_ciphertext($tokens);
    if ($existing) {
        db_run($db,"UPDATE integration_connections SET auth_type = 'oauth', account_name = ?, account_url = '', token_ciphertext = ?, token_scopes = 'offline_access accounting.settings.read', connected_by = ?, connected_at = CURRENT_TIMESTAMP, last_checked_at = UTC_TIMESTAMP(), last_error = '' WHERE id = ? AND company_id = ? AND provider = 'xero'",[
            $name,$ciphertext,$user['id'],(int)$existing['id'],$companyId,
        ]);
        return;
    }
    db_run($db,"INSERT INTO integration_connections (company_id,provider,auth_type,account_login,account_name,token_ciphertext,token_scopes,connected_by,last_checked_at,last_error) VALUES (?,'xero','oauth',?,?,?,'offline_access accounting.settings.read',?,UTC_TIMESTAMP(),'')",[
        $companyId,$tenantId,$name,$ciphertext,$user['id'],
    ]);
}
function xero_refresh_connection(mysqli $db, array $connection, ?callable $transport = null): array {
    $tokens = xero_decrypt_tokens($connection['token_ciphertext']);
    if ($tokens['access_expires_at'] > time()+120) return $tokens;
    if ($tokens['refresh_expires_at'] <= time()) throw new XeroUnavailable('The Xero refresh token may have expired. Reconnect the organization.');
    $refreshed = xero_token_request('refresh_token',['refresh_token'=>$tokens['refresh_token']],$transport);
    $ciphertext = xero_token_ciphertext($refreshed);
    db_run($db,"UPDATE integration_connections SET token_ciphertext = ?, last_error = '' WHERE id = ? AND company_id = ? AND provider = 'xero'",[
        $ciphertext,(int)$connection['id'],(int)$connection['company_id'],
    ]);
    return $refreshed;
}
function xero_verify_connection(mysqli $db, array $connection, ?callable $transport = null): array {
    try {
        $tokens = xero_refresh_connection($db,$connection,$transport);
        $organisation = xero_organisation_info((string)$connection['account_login'],$tokens['access_token'],$transport);
        db_run($db,"UPDATE integration_connections SET account_name = ?, last_checked_at = UTC_TIMESTAMP(), last_error = '' WHERE id = ? AND company_id = ? AND provider = 'xero'",[
            trim($organisation['Name']),(int)$connection['id'],(int)$connection['company_id'],
        ]);
        return $organisation;
    } catch (XeroUnavailable $error) {
        db_run($db,"UPDATE integration_connections SET last_error = ?, last_checked_at = UTC_TIMESTAMP() WHERE id = ? AND company_id = ? AND provider = 'xero'",[
            mb_substr($error->getMessage(),0,255),(int)$connection['id'],(int)$connection['company_id'],
        ]);
        throw $error;
    }
}
