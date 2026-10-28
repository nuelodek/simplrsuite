<?php
declare(strict_types=1);
require_once __DIR__ . '/bootstrap.php';
// Outbound HTTPS always verifies certificates. Some Windows PHP builds (Ampps) ship
// without a configured CA bundle, so use CURL_CA_BUNDLE when set, otherwise php.ini's
// curl.cainfo, otherwise the bundle that ships beside the PHP binary.
function outbound_ca_bundle(): ?string {
    $configured = config('CURL_CA_BUNDLE');
    if ($configured !== '' && is_file($configured)) return $configured;
    if (trim((string)ini_get('curl.cainfo')) !== '') return null;
    $beside = PHP_BINARY !== '' ? dirname(PHP_BINARY) . '/extras/ssl/cacert.pem' : '';
    return $beside !== '' && is_file($beside) ? $beside : null;
}
function secure_curl(CurlHandle $handle): CurlHandle {
    curl_setopt_array($handle,[CURLOPT_SSL_VERIFYPEER=>true,CURLOPT_SSL_VERIFYHOST=>2]);
    $bundle = outbound_ca_bundle();
    if ($bundle !== null) curl_setopt($handle,CURLOPT_CAINFO,$bundle);
    return $handle;
}
