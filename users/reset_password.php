<?php
require_once __DIR__ . '/../app/staff-recovery.php';
require_once __DIR__ . '/../app/view.php';
$token = is_string($_GET['token'] ?? null) ? $_GET['token'] : '';
$error = '';
if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    require_post(); $db = database(); rate_limit($db, 'staff-reset-confirm', '', 30);
    if ($token === '') $token = input('token');
    $password = is_string($_POST['new_password'] ?? null) ? $_POST['new_password'] : '';
    $confirm = is_string($_POST['confirm_password'] ?? null) ? $_POST['confirm_password'] : '';
    $error = password_error($password, $confirm) ?? '';
    if ($error === '') {
        $role = reset_staff_password($db, $token, $password);
        if ($role === null) $error = 'This link has expired, was already used, or no longer matches an active account. Request a new one.';
        else {
            session_regenerate_id(true); $_SESSION = [];
            $login = '/users/' . ($role === 'manager' ? 'managers' : 'employees') . '/login.php';
            if (wants_json()) json_response(['success'=>true,'message'=>'Password updated. Sign in with your new password.','redirect'=>$login]);
            flash('Password updated. Sign in with your new password.'); redirect($login);
        }
    }
    if (wants_json()) json_response(['success'=>false,'error'=>$error],422);
    http_response_code(422);
}
auth_start('Choose a new password.', 'Use a unique phrase of 12–72 characters for your work account.');
if ($error !== '') echo '<div class="notice error" role="alert">' . e($error) . '</div>';
?>
<?php if (preg_match('/^[a-f0-9]{64}$/D', $token)): ?>
<form method="post" class="form-stack" data-submit><?= csrf_field() ?><input type="hidden" name="token" value="<?= e($token) ?>">
<div class="field"><label for="new_password">New password</label><div class="password-wrap"><input id="new_password" name="new_password" type="password" minlength="12" maxlength="72" autocomplete="new-password" required><button type="button" data-password="new_password" aria-controls="new_password" aria-pressed="false">Show</button></div></div>
<div class="field"><label for="confirm_password">Confirm password</label><input id="confirm_password" name="confirm_password" type="password" minlength="12" maxlength="72" autocomplete="new-password" required></div><button class="button" type="submit">Save new password</button></form>
<?php else: ?><div class="notice info">Open the recovery link in your email to choose a new password.</div><?php endif; ?>
<p class="form-after"><a class="text-link" href="/users/forgetpassword.php">Request a new recovery link</a></p>
<?php auth_end(); ?>
