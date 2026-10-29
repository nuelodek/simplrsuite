<?php
declare(strict_types=1);
require_once __DIR__ . '/database.php';

function admin_user(mysqli $db): array {
    if (($_SESSION['auth_role'] ?? '') !== 'admin' || empty($_SESSION['user_id'])) {
        if (wants_json()) fail('Please sign in to continue.', 401);
        redirect('/users/admin/login.php');
    }
    $user = db_one($db, 'SELECT * FROM users WHERE id = ?', [$_SESSION['user_id']]);
    if (!$user || $user['status'] !== 'Verified' || !hash_equals(hash('sha256', $user['password']), $_SESSION['auth_stamp'] ?? '')) {
        unset($_SESSION['auth_role'], $_SESSION['user_id'], $_SESSION['company_id']);
        if (wants_json()) fail('Please sign in to continue.', 401);
        redirect('/users/admin/login.php');
    }
    return $user;
}
function login_admin(array $user): void {
    session_regenerate_id(true);
    $_SESSION = ['csrf_token' => bin2hex(random_bytes(32)), 'auth_role' => 'admin', 'auth_stamp' => hash('sha256', $user['password']), 'user_id' => $user['id'], 'email' => $user['email'], 'company_name' => $user['company_name'], 'companyName' => $user['company_name'], 'company_id' => $user['company_id'], 'industry' => $user['industry'], 'status' => $user['status'], 'unique_id' => $user['id']];
}
function rate_limit(mysqli $db, string $action, string $identifier = '', int $limit = 10): void {
    $key = hash('sha256', $action . '|' . ($_SERVER['REMOTE_ADDR'] ?? 'cli') . '|' . strtolower($identifier));
    db_run($db, 'INSERT INTO auth_rate_limits (bucket, attempts, window_started) VALUES (?, 1, UTC_TIMESTAMP()) ON DUPLICATE KEY UPDATE attempts = IF(window_started < UTC_TIMESTAMP() - INTERVAL 15 MINUTE, 1, attempts + 1), window_started = IF(window_started < UTC_TIMESTAMP() - INTERVAL 15 MINUTE, UTC_TIMESTAMP(), window_started)', [$key]);
    $row = db_one($db, 'SELECT attempts FROM auth_rate_limits WHERE bucket = ?', [$key]);
    if ((int)$row['attempts'] > $limit) { header('Retry-After: 900'); fail('Too many attempts. Please try again in 15 minutes.', 429); }
}
function issue_token(mysqli $db, int $userId, string $purpose): string {
    $token = bin2hex(random_bytes(32));
    db_run($db, 'INSERT INTO auth_tokens (user_id, purpose, token_hash, expires_at) VALUES (?, ?, ?, UTC_TIMESTAMP() + INTERVAL 1 HOUR)', [$userId, $purpose, hash('sha256', $token)]);
    return $token;
}
function issue_verification_code(mysqli $db, int $userId): string {
    db_run($db, "UPDATE auth_tokens SET used_at = UTC_TIMESTAMP() WHERE user_id = ? AND purpose = 'verify' AND used_at IS NULL", [$userId]);
    for ($attempt = 0; $attempt < 10; $attempt++) {
        $code = str_pad((string)random_int(0,999999),6,'0',STR_PAD_LEFT);
        $hash = verification_code_hash($userId,$code);
        if (db_one($db,'SELECT id FROM auth_tokens WHERE token_hash = ?',[$hash])) continue;
        db_run($db, "INSERT INTO auth_tokens (user_id, purpose, token_hash, expires_at) VALUES (?, 'verify', ?, UTC_TIMESTAMP() + INTERVAL 10 MINUTE)", [$userId,$hash]);
        return $code;
    }
    throw new RuntimeException('Could not generate a unique verification code.');
}
function verification_code_hash(int $userId, string $code): string {
    return hash('sha256','verify|' . $userId . '|' . $code);
}
function password_error(string $password, string $confirmation): ?string {
    if (strlen($password) < 12 || strlen($password) > 72) return 'Use a password between 12 and 72 characters.';
    if ($password !== $confirmation) return 'The passwords do not match.';
    return null;
}
