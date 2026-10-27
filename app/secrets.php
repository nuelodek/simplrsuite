<?php
declare(strict_types=1);
require_once __DIR__ . '/bootstrap.php';
// Integration tokens are encrypted at rest with AES-256-GCM. Production must set
// APP_KEY (base64:<32 random bytes>); local development falls back to a private
// key file that is created once under storage/ and never served.
function app_secret_key(): string {
    $configured = config('APP_KEY');
    if ($configured !== '') {
        $key = str_starts_with($configured,'base64:') ? base64_decode(substr($configured,7),true) : $configured;
        if (!is_string($key) || strlen($key) !== 32) throw new RuntimeException('APP_KEY must be base64: followed by 32 random bytes.');
        return $key;
    }
    if (config('APP_ENV') === 'production') throw new RuntimeException('APP_KEY is required to store integration tokens.');
    $path = SIMPLR_ROOT . '/storage/keys/app.key';
    if (!is_file($path)) {
        if (!is_dir(dirname($path)) && !mkdir(dirname($path),0700,true)) throw new RuntimeException('Key storage unavailable');
        $handle = @fopen($path,'x');
        if ($handle) { fwrite($handle,base64_encode(random_bytes(32))); fclose($handle); @chmod($path,0600); }
    }
    $key = base64_decode(trim((string)file_get_contents($path)),true);
    if (!is_string($key) || strlen($key) !== 32) throw new RuntimeException('The local key file is invalid.');
    return $key;
}
function encrypt_secret(string $plain): string {
    $iv = random_bytes(12); $tag = '';
    $cipher = openssl_encrypt($plain,'aes-256-gcm',app_secret_key(),OPENSSL_RAW_DATA,$iv,$tag,'simplr-secret-v1',16);
    if ($cipher === false) throw new RuntimeException('Secret could not be encrypted');
    return 'v1:' . base64_encode($iv . $tag . $cipher);
}
function decrypt_secret(string $stored): string {
    $raw = str_starts_with($stored,'v1:') ? base64_decode(substr($stored,3),true) : false;
    if (!is_string($raw) || strlen($raw) < 29) throw new RuntimeException('Stored secret is unreadable');
    $plain = openssl_decrypt(substr($raw,28),'aes-256-gcm',app_secret_key(),OPENSSL_RAW_DATA,substr($raw,0,12),substr($raw,12,16),'simplr-secret-v1');
    if ($plain === false) throw new RuntimeException('Stored secret could not be decrypted');
    return $plain;
}
