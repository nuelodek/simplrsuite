<?php
require_once __DIR__ . '/../../app/auth.php';
require_once __DIR__ . '/../../app/view.php';
$token = is_string($_GET['token'] ?? null) ? $_GET['token'] : '';
if (!preg_match('/^[a-f0-9]{64}$/D', $token)) { flash('Request a new password reset link to continue.','error'); redirect('/users/admin/resetpassword.php'); }
if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    require_post(); $db = database(); rate_limit($db,'reset-confirm','',20);
    $password = is_string($_POST['new_password'] ?? null) ? $_POST['new_password'] : '';
    $confirm = is_string($_POST['confirm_password'] ?? null) ? $_POST['confirm_password'] : '';
    if ($error = password_error($password,$confirm)) { flash($error,'error'); redirect('/users/admin/forgetpassword.php?token=' . rawurlencode($token)); }
    $db->begin_transaction();
    $record = db_one($db,"SELECT * FROM auth_tokens WHERE token_hash = ? AND purpose = 'reset' AND used_at IS NULL AND expires_at > UTC_TIMESTAMP() FOR UPDATE",[hash('sha256',$token)]);
    if (!$record) { $db->rollback(); flash('This reset link has expired or has already been used. Request a new one.','error'); redirect('/users/admin/resetpassword.php'); }
    db_run($db, "UPDATE users SET password = ? WHERE id = ? AND status IN ('Verified','Unverified')",[password_hash($password,PASSWORD_DEFAULT),$record['user_id']]);
    db_run($db,"UPDATE auth_tokens SET used_at = UTC_TIMESTAMP() WHERE user_id = ? AND purpose = 'reset' AND used_at IS NULL",[$record['user_id']]);
    $db->commit();
    session_regenerate_id(true); $_SESSION = [];
    flash('Password updated. Sign in with your new password.'); redirect('/users/admin/login.php');
}
auth_start('Choose a new password.', 'Use a memorable phrase that you don’t use elsewhere.');
?>
<form class="form-stack" method="post" data-submit><?= csrf_field() ?><div class="field"><label for="password">New password</label><div class="password-wrap"><input id="password" name="new_password" type="password" minlength="12" maxlength="72" autocomplete="new-password" required><button type="button" data-password="password" aria-controls="password" aria-pressed="false">Show</button></div><small>Use 12–72 characters.</small></div><div class="field"><label for="confirm_password">Confirm password</label><input id="confirm_password" name="confirm_password" type="password" minlength="12" maxlength="72" autocomplete="new-password" required></div><button class="button" type="submit">Save new password</button></form>
<?php auth_end(); ?>
