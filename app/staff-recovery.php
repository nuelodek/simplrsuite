<?php
declare(strict_types=1);
// Consolidates simplr24's shared recovery journey with explicit role/tenant identity.
require_once __DIR__ . '/staff.php';
require_once __DIR__ . '/mail.php';

function request_staff_reset(mysqli $db, string $role, string $email, string $workspace): void {
    if (!in_array($role, ['employee','manager'], true)) fail('Choose employee or manager recovery, or use the business or talent recovery page.', 422);
    if (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 255 || !preg_match('/^[1-9][0-9]{0,9}$/D', $workspace)) fail('Enter your work email and the workspace code from your invitation.', 422);
    rate_limit($db, 'staff-reset-ip', '', 20);
    rate_limit($db, 'staff-reset-account', $role . '|' . $email . '|' . $workspace, 3);
    if (!mail_ready()) fail('Account email is temporarily unavailable. Please try again later.', 503);
    $accounts = staff_accounts($db, $role, $email, $workspace);
    // Never choose the first matching identity in an ambiguous legacy directory.
    if (count($accounts) !== 1) return;
    $user = $accounts[0]; $token = bin2hex(random_bytes(32));
    db_run($db, 'INSERT INTO staff_password_resets (staff_role, auth_id, staff_id, company_id, email, auth_stamp, token_hash, expires_at) VALUES (?, ?, ?, ?, ?, ?, ?, UTC_TIMESTAMP() + INTERVAL 1 HOUR)', [$role, $user['auth_id'], $user['id'], $user['company_id'], $email, hash('sha256',$user['password']), hash('sha256',$token)]);
    $sent = send_account_mail($email, 'Reset your Simplr ' . $role . ' password', 'Reset your password for ' . $user['company_name'] . ' (workspace ' . $user['company_id'] . "). This link expires in one hour:\n" . account_link('/users/reset_password.php', $token));
    if (!$sent) db_run($db, 'UPDATE staff_password_resets SET used_at = UTC_TIMESTAMP() WHERE token_hash = ?', [hash('sha256',$token)]);
}
function reset_staff_password(mysqli $db, string $token, string $password): ?string {
    if (!preg_match('/^[a-f0-9]{64}$/D', $token)) return null;
    $db->begin_transaction();
    try {
        $reset = db_one($db, 'SELECT * FROM staff_password_resets WHERE token_hash = ? AND used_at IS NULL AND expires_at > UTC_TIMESTAMP() FOR UPDATE', [hash('sha256',$token)]);
        if (!$reset) { $db->rollback(); return null; }
        $table = $reset['staff_role'] === 'employee' ? 'employees_auth' : 'manager_auth';
        $auth = db_one($db, "SELECT id, password FROM $table WHERE id = ? AND company_id = ? FOR UPDATE", [$reset['auth_id'], $reset['company_id']]);
        $accounts = staff_accounts($db, $reset['staff_role'], $reset['email'], (string)$reset['company_id']);
        if (!$auth || !hash_equals($reset['auth_stamp'], hash('sha256',$auth['password'])) || count($accounts) !== 1 || (int)$accounts[0]['auth_id'] !== (int)$reset['auth_id'] || (int)$accounts[0]['id'] !== (int)$reset['staff_id']) {
            $db->rollback(); return null;
        }
        db_run($db, "UPDATE $table SET password = ? WHERE id = ? AND company_id = ?", [password_hash($password, PASSWORD_DEFAULT), $reset['auth_id'], $reset['company_id']]);
        db_run($db, 'UPDATE staff_password_resets SET used_at = UTC_TIMESTAMP() WHERE company_id = ? AND staff_role = ? AND auth_id = ? AND used_at IS NULL', [$reset['company_id'], $reset['staff_role'], $reset['auth_id']]);
        $db->commit(); return $reset['staff_role'];
    } catch (Throwable $error) { $db->rollback(); throw $error; }
}
