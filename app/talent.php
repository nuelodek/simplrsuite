<?php
declare(strict_types=1);
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/mail.php';
require_once __DIR__ . '/view.php';

function talent_user(mysqli $db): array {
    $user = ($_SESSION['auth_role'] ?? '') === 'talent' && !empty($_SESSION['talent_account_id'])
        ? db_one($db, 'SELECT * FROM talentprofile WHERE id = ?', [$_SESSION['talent_account_id']]) : null;
    if (!$user || $user['status'] !== 'Verified' || !hash_equals(hash('sha256', $user['password']), $_SESSION['auth_stamp'] ?? '')) {
        if (wants_json()) fail('Please sign in to your talent account.', 401);
        redirect('/users/talents/talentlogin.php');
    }
    return $user;
}
function talent_token(mysqli $db, int $id, string $purpose): string {
    $token = bin2hex(random_bytes(32));
    db_run($db, 'INSERT INTO talent_tokens (account_id, purpose, token_hash, expires_at) VALUES (?, ?, ?, UTC_TIMESTAMP() + INTERVAL 1 HOUR)', [$id, $purpose, hash('sha256', $token)]);
    return $token;
}
function talent_mail(mysqli $db, array $user, string $purpose): bool {
    $token = talent_token($db, (int)$user['id'], $purpose);
    $path = $purpose === 'verify' ? '/users/talents/verifyaccount.php' : '/users/talents/newpassword.php';
    $subject = $purpose === 'verify' ? 'Verify your Simplr talent account' : 'Reset your Simplr talent password';
    return send_account_mail($user['email'], $subject, "Use this link within one hour:\n" . account_link($path, $token));
}
function talent_password_field(string $name, string $label, bool $new = true): void {
    echo '<div class="field"><label for="' . e($name) . '">' . e($label) . '</label><input id="' . e($name) . '" name="' . e($name) . '" type="password" ' . ($new ? 'minlength="12" maxlength="72" autocomplete="new-password"' : 'autocomplete="current-password"') . ' required></div>';
}
