<?php
require_once __DIR__ . '/../../app/auth.php';
require_once __DIR__ . '/../../app/mail.php';
require_once __DIR__ . '/../../app/view.php';
$token = is_string($_GET['token'] ?? null) ? $_GET['token'] : '';
if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    require_post(); $db = database(); rate_limit($db, 'verification', '', 15);
    if ($token !== '') {
        if (!preg_match('/^[a-f0-9]{64}$/D', $token)) { flash('This verification link is invalid. Request a new one.', 'error'); redirect('/users/admin/verifyaccount.php'); }
        $db->begin_transaction();
        $record = db_one($db, "SELECT * FROM auth_tokens WHERE token_hash = ? AND purpose = 'verify' AND used_at IS NULL AND expires_at > UTC_TIMESTAMP() FOR UPDATE", [hash('sha256',$token)]);
        if ($record) {
            db_run($db, "UPDATE users SET status = 'Verified' WHERE id = ? AND status = 'Unverified'", [$record['user_id']]);
            db_run($db, "UPDATE auth_tokens SET used_at = UTC_TIMESTAMP() WHERE user_id = ? AND purpose = 'verify' AND used_at IS NULL", [$record['user_id']]);
            $db->commit(); unset($_SESSION['verification_user_id']);
            flash('Email verified. Your workspace is ready — sign in to continue.'); redirect('/users/admin/login.php');
        }
        $db->rollback(); flash('This verification link has expired or has already been used. Sign in to request another.', 'error'); redirect('/users/admin/login.php');
    }
    if (empty($_SESSION['verification_user_id'])) redirect('/users/admin/login.php');
    $user = db_one($db, "SELECT id, email FROM users WHERE id = ? AND status = 'Unverified'", [$_SESSION['verification_user_id']]);
    if (!$user) redirect('/users/admin/login.php');
    if (input('action') !== 'resend') {
        rate_limit($db,'verification-code',(string)$user['id'],8);
        $code = input('verification_code');
        if (!preg_match('/^[0-9]{6}$/D',$code)) { flash('Enter the six-digit verification code.','error'); redirect('/users/admin/verifyaccount.php'); }
        $db->begin_transaction();
        $record = db_one($db, "SELECT * FROM auth_tokens WHERE user_id = ? AND token_hash = ? AND purpose = 'verify' AND used_at IS NULL AND expires_at > UTC_TIMESTAMP() FOR UPDATE", [(int)$user['id'],verification_code_hash((int)$user['id'],$code)]);
        if ($record) {
            db_run($db, "UPDATE users SET status = 'Verified' WHERE id = ? AND status = 'Unverified'", [$user['id']]);
            db_run($db, "UPDATE auth_tokens SET used_at = UTC_TIMESTAMP() WHERE user_id = ? AND purpose = 'verify' AND used_at IS NULL", [$user['id']]);
            $db->commit(); unset($_SESSION['verification_user_id']);
            flash('Email verified. Your workspace is ready — sign in to continue.'); redirect('/users/admin/login.php');
        }
        $db->rollback(); flash('That code is invalid or expired. Request a new code and try again.','error'); redirect('/users/admin/verifyaccount.php');
    }
    rate_limit($db, 'resend', (string)$user['id'], 3);
    if (!mail_ready()) { flash('Email delivery is temporarily unavailable. Please try again later.', 'error'); redirect('/users/admin/verifyaccount.php'); }
    $code = issue_verification_code($db, (int)$user['id']);
    $sent = send_account_mail($user['email'], 'Verify your Simplr workspace', "Enter this six-digit verification code in Simplr Suite within 10 minutes:\n\n" . $code);
    flash($sent ? 'A new verification code is on its way. Check your inbox and spam folder.' : 'We could not send the email. Please try again later.', $sent ? 'success' : 'error');
    redirect('/users/admin/verifyaccount.php');
}
auth_start($token !== '' ? 'Confirm your email.' : 'Enter your verification code.', $token !== '' ? 'Finish verifying your email to activate your workspace.' : 'Enter the six-digit code sent to your email. It expires in 10 minutes.');
?>
<?php if ($token !== ''): ?>
<form method="post" class="form-stack" data-submit><?= csrf_field() ?><button class="button" type="submit">Verify email address</button></form>
<?php elseif (isset($_SESSION['verification_user_id'])): ?>
<form method="post" class="form-stack" data-submit><?= csrf_field() ?><div class="field"><label for="verification_code">Six-digit code</label><input id="verification_code" name="verification_code" type="text" inputmode="numeric" pattern="[0-9]{6}" minlength="6" maxlength="6" autocomplete="one-time-code" required></div><button class="button" type="submit">Verify email address</button></form>
<form method="post" class="form-after"><?= csrf_field() ?><button class="text-link text-button-link" type="submit" name="action" value="resend">Resend code</button></form>
<?php else: ?><p class="subtle">Sign in to request a new verification code.</p><?php endif; ?>
<p class="form-after"><a class="text-link" href="/users/admin/login.php">Back to sign in</a></p>
<?php auth_end(); ?>
