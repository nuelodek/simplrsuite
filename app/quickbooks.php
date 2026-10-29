<?php
declare(strict_types=1);
require_once __DIR__ . '/secrets.php';
require_once __DIR__ . '/outbound.php';

class QuickBooksUnavailable extends RuntimeException {}

function quickbooks_environment(): string {
    return config('QUICKBOOKS_ENVIRONMENT','sandbox');
}
function quickbooks_api_base(): string {
    return quickbooks_environment() === 'production'
        ? 'https://quickbooks.api.intuit.com'
        : 'https://sandbox-quickbooks.api.intuit.com';
}
function quickbooks_redirect_uri(): string {
    return rtrim(config('APP_URL','http://127.0.0.1:8000'), '/') . '/users/admin/quickbooks.php';
}
function quickbooks_ready(): bool {
    $environment = quickbooks_environment();
    if (!in_array($environment,['sandbox','production'],true)
        || config('QUICKBOOKS_CLIENT_ID') === ''
        || config('QUICKBOOKS_CLIENT_SECRET') === ''
        || !extension_loaded('curl')) return false;
    if ($environment === 'production') {
        $parts = parse_url(config('APP_URL'));
        if (config('APP_ENV') !== 'production' || ($parts['scheme'] ?? '') !== 'https') return false;
    }
    return true;
}
function quickbooks_authorize_url(string $state): string {
    if (!quickbooks_ready()) throw new QuickBooksUnavailable('QuickBooks is not configured for this environment.');
    return 'https://appcenter.intuit.com/connect/oauth2?' . http_build_query([
        'client_id'=>config('QUICKBOOKS_CLIENT_ID'),
        'response_type'=>'code',
        'scope'=>'com.intuit.quickbooks.accounting',
        'redirect_uri'=>quickbooks_redirect_uri(),
        'state'=>$state,
    ]);
}
function quickbooks_http(string $method, string $url, array $headers, ?string $body = null, ?callable $transport = null): array {
    if ($transport !== null) {
        if (PHP_SAPI !== 'cli') throw new LogicException('Test transports are CLI-only.');
        $response = $transport($method,$url,$headers,$body);
        if (!is_array($response) || !is_int($response[0] ?? null) || !is_string($response[1] ?? null)) {
            throw new QuickBooksUnavailable('QuickBooks returned an invalid response.');
        }
        return [$response[0],$response[1]];
    }
    $parts = parse_url($url);
    if (($parts['scheme'] ?? '') !== 'https' || !in_array($parts['host'] ?? '',[
        'oauth.platform.intuit.com','developer.api.intuit.com','sandbox-quickbooks.api.intuit.com','quickbooks.api.intuit.com',
    ],true) || isset($parts['user']) || isset($parts['pass'])) {
        throw new LogicException('QuickBooks request target is invalid.');
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
        throw new QuickBooksUnavailable('QuickBooks could not be reached. Check the connection and try again.');
    }
    return [$status,$raw];
}
function quickbooks_token_request(string $grantType, array $parameters, ?callable $transport = null): array {
    if (!quickbooks_ready()) throw new QuickBooksUnavailable('QuickBooks is not configured for this environment.');
    $headers = [
        'Accept: application/json',
        'Content-Type: application/x-www-form-urlencoded',
        'Authorization: Basic ' . base64_encode(config('QUICKBOOKS_CLIENT_ID') . ':' . config('QUICKBOOKS_CLIENT_SECRET')),
    ];
    [$status,$raw] = quickbooks_http(
        'POST',
        'https://oauth.platform.intuit.com/oauth2/v1/tokens/bearer',
        $headers,
        http_build_query(['grant_type'=>$grantType] + $parameters),
        $transport
    );
    $result = json_decode($raw,true);
    if ($status < 200 || $status >= 300 || !is_array($result)
        || !is_string($result['access_token'] ?? null) || $result['access_token'] === ''
        || !is_string($result['refresh_token'] ?? null) || $result['refresh_token'] === ''
        || !is_int($result['expires_in'] ?? null) || $result['expires_in'] < 1
        || !is_int($result['x_refresh_token_expires_in'] ?? null) || $result['x_refresh_token_expires_in'] < 1) {
        throw new QuickBooksUnavailable('QuickBooks could not complete authorization. Check the app credentials and callback URL.');
    }
    return [
        'access_token'=>$result['access_token'],
        'refresh_token'=>$result['refresh_token'],
        'access_expires_at'=>time()+$result['expires_in'],
        'refresh_expires_at'=>time()+$result['x_refresh_token_expires_in'],
    ];
}
function quickbooks_token_ciphertext(array $tokens): string {
    foreach (['access_token','refresh_token'] as $key) {
        if (!is_string($tokens[$key] ?? null) || $tokens[$key] === '') throw new InvalidArgumentException('QuickBooks token data is incomplete.');
    }
    foreach (['access_expires_at','refresh_expires_at'] as $key) {
        if (!is_int($tokens[$key] ?? null) || $tokens[$key] <= time()) throw new InvalidArgumentException('QuickBooks token expiry is invalid.');
    }
    return encrypt_secret(json_encode($tokens,JSON_THROW_ON_ERROR));
}
function quickbooks_decrypt_tokens(string $ciphertext): array {
    $tokens = json_decode(decrypt_secret($ciphertext),true);
    if (!is_array($tokens) || !is_string($tokens['access_token'] ?? null) || !is_string($tokens['refresh_token'] ?? null)
        || !is_int($tokens['access_expires_at'] ?? null) || !is_int($tokens['refresh_expires_at'] ?? null)) {
        throw new QuickBooksUnavailable('The saved QuickBooks connection needs to be reconnected.');
    }
    return $tokens;
}
function quickbooks_company_info(string $realmId, string $accessToken, ?callable $transport = null): array {
    if (!preg_match('/^[0-9]{1,32}$/D',$realmId) || $accessToken === '') throw new QuickBooksUnavailable('QuickBooks returned an invalid company connection.');
    $url = quickbooks_api_base() . '/v3/company/' . rawurlencode($realmId) . '/companyinfo/' . rawurlencode($realmId) . '?minorversion=75';
    [$status,$raw] = quickbooks_http('GET',$url,[
        'Accept: application/json',
        'Authorization: Bearer ' . $accessToken,
    ],null,$transport);
    $result = json_decode($raw,true);
    $company = $result['CompanyInfo'] ?? null;
    if ($status < 200 || $status >= 300 || !is_array($company) || !is_string($company['CompanyName'] ?? null) || trim($company['CompanyName']) === '') {
        throw new QuickBooksUnavailable($status === 401
            ? 'QuickBooks rejected this connection. Reconnect the company.'
            : 'QuickBooks could not verify the selected company.');
    }
    return $company;
}
function quickbooks_revoke_tokens(array $tokens, ?callable $transport = null): bool {
    if (!quickbooks_ready()) throw new QuickBooksUnavailable('QuickBooks is not configured for provider-side revocation.');
    if (!is_string($tokens['refresh_token'] ?? null) || $tokens['refresh_token'] === '') {
        throw new QuickBooksUnavailable('The saved QuickBooks refresh token is unavailable for revocation.');
    }
    [$status] = quickbooks_http('POST','https://developer.api.intuit.com/v2/oauth2/tokens/revoke',[
        'Accept: application/json',
        'Content-Type: application/json',
        'Authorization: Basic ' . base64_encode(config('QUICKBOOKS_CLIENT_ID') . ':' . config('QUICKBOOKS_CLIENT_SECRET')),
    ],json_encode(['token'=>$tokens['refresh_token']],JSON_THROW_ON_ERROR),$transport);
    return $status >= 200 && $status < 300;
}
function quickbooks_connections(mysqli $db, int $companyId): array {
    return db_rows($db,"SELECT * FROM integration_connections WHERE company_id = ? AND provider = 'quickbooks' ORDER BY id",[$companyId]);
}
function quickbooks_connection(mysqli $db, int $companyId): ?array {
    return db_one($db,"SELECT * FROM integration_connections WHERE company_id = ? AND provider = 'quickbooks' ORDER BY id LIMIT 1",[$companyId]);
}
function quickbooks_save_connection(mysqli $db, array $user, string $realmId, array $company, array $tokens): void {
    if (!preg_match('/^[0-9]{1,32}$/D',$realmId)) throw new QuickBooksUnavailable('QuickBooks returned an invalid company identifier.');
    $companyName = trim((string)($company['CompanyName'] ?? ''));
    if ($companyName === '' || strlen($companyName)>160) throw new QuickBooksUnavailable('QuickBooks returned an invalid company name.');
    $existing = quickbooks_connection($db,(int)$user['company_id']);
    $ciphertext = quickbooks_token_ciphertext($tokens);
    if ($existing) {
        db_run($db,"UPDATE integration_connections SET auth_type = 'oauth', account_login = ?, account_name = ?, account_url = '', token_ciphertext = ?, token_scopes = 'com.intuit.quickbooks.accounting', connected_by = ?, connected_at = CURRENT_TIMESTAMP, last_checked_at = UTC_TIMESTAMP(), last_error = '' WHERE id = ? AND company_id = ? AND provider = 'quickbooks'",[
            $realmId,$companyName,$ciphertext,$user['id'],(int)$existing['id'],(int)$user['company_id'],
        ]);
        return;
    }
    db_run($db,"INSERT INTO integration_connections (company_id,provider,auth_type,account_login,account_name,token_ciphertext,token_scopes,connected_by,last_checked_at,last_error) VALUES (?,'quickbooks','oauth',?,?,?,'com.intuit.quickbooks.accounting',?,UTC_TIMESTAMP(),'')",[
        $user['company_id'],$realmId,$companyName,$ciphertext,$user['id'],
    ]);
}
function quickbooks_refresh_connection(mysqli $db, array $connection, ?callable $transport = null): array {
    $tokens = quickbooks_decrypt_tokens($connection['token_ciphertext']);
    if ($tokens['access_expires_at'] > time()+120) return $tokens;
    if ($tokens['refresh_expires_at'] <= time()) throw new QuickBooksUnavailable('The QuickBooks refresh token has expired. Reconnect the company.');
    $refreshed = quickbooks_token_request('refresh_token',['refresh_token'=>$tokens['refresh_token']],$transport);
    $ciphertext = quickbooks_token_ciphertext($refreshed);
    db_run($db,"UPDATE integration_connections SET token_ciphertext = ?, last_error = '' WHERE id = ? AND company_id = ? AND provider = 'quickbooks'",[
        $ciphertext,(int)$connection['id'],(int)$connection['company_id'],
    ]);
    return $refreshed;
}
function quickbooks_verify_connection(mysqli $db, array $connection, ?callable $transport = null): array {
    try {
        $tokens = quickbooks_refresh_connection($db,$connection,$transport);
        $company = quickbooks_company_info((string)$connection['account_login'],$tokens['access_token'],$transport);
        db_run($db,"UPDATE integration_connections SET account_name = ?, last_checked_at = UTC_TIMESTAMP(), last_error = '' WHERE id = ? AND company_id = ? AND provider = 'quickbooks'",[
            trim($company['CompanyName']),(int)$connection['id'],(int)$connection['company_id'],
        ]);
        return $company;
    } catch (QuickBooksUnavailable $error) {
        db_run($db,"UPDATE integration_connections SET last_error = ?, last_checked_at = UTC_TIMESTAMP() WHERE id = ? AND company_id = ? AND provider = 'quickbooks'",[
            mb_substr($error->getMessage(),0,255),(int)$connection['id'],(int)$connection['company_id'],
        ]);
        throw $error;
    }
}
