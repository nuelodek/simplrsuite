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
    if ($purpose === 'verify') {
        db_run($db, "UPDATE talent_tokens SET used_at = UTC_TIMESTAMP() WHERE account_id = ? AND purpose = 'verify' AND used_at IS NULL", [(int)$user['id']]);
        for ($attempt = 0; $attempt < 10; $attempt++) {
            $code = str_pad((string)random_int(0,999999),6,'0',STR_PAD_LEFT);
            $hash = verification_code_hash((int)$user['id'],$code);
            if (db_one($db,'SELECT id FROM talent_tokens WHERE token_hash = ?',[$hash])) continue;
            db_run($db, "INSERT INTO talent_tokens (account_id, purpose, token_hash, expires_at) VALUES (?, 'verify', ?, UTC_TIMESTAMP() + INTERVAL 10 MINUTE)", [(int)$user['id'],$hash]);
            return send_account_mail($user['email'], 'Verify your Simplr talent account', "Enter this six-digit verification code in Simplr Suite within 10 minutes:\n\n" . $code);
        }
        throw new RuntimeException('Could not generate a unique talent verification code.');
    }
    $token = talent_token($db, (int)$user['id'], $purpose);
    return send_account_mail($user['email'], 'Reset your Simplr talent password', "Use this link within one hour:\n" . account_link('/users/talents/newpassword.php', $token));
}
function talent_password_field(string $name, string $label, bool $new = true): void {
    echo '<div class="field"><label for="' . e($name) . '">' . e($label) . '</label><input id="' . e($name) . '" name="' . e($name) . '" type="password" ' . ($new ? 'minlength="12" maxlength="72" autocomplete="new-password"' : 'autocomplete="current-password"') . ' required></div>';
}
