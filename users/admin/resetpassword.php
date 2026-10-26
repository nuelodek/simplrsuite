<?php
require_once __DIR__ . '/../../app/auth.php';
require_once __DIR__ . '/../../app/mail.php';
require_once __DIR__ . '/../../app/view.php';
if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    require_post(); $email = strtolower(input('email'));
    if (!filter_var($email,FILTER_VALIDATE_EMAIL)) { flash('Enter a valid email address.','error'); redirect('/users/admin/resetpassword.php'); }
    if (!mail_ready()) { flash('Password recovery is temporarily unavailable. Please try again later.','error'); redirect('/users/admin/resetpassword.php'); }
    $db = database(); rate_limit($db,'reset-ip','',20); rate_limit($db,'reset',$email,3);
    $user = db_one($db,"SELECT id FROM users WHERE email = ? AND status IN ('Verified','Unverified')",[$email]);
    if ($user) {
        $token = issue_token($db,(int)$user['id'],'reset');
        send_account_mail($email,'Reset your Simplr password',"To reset your password, open this link within one hour:\n" . account_link('/users/admin/forgetpassword.php',$token) . "\n\nIf you did not request a reset, ignore this email. Your password has not changed.");
    }
    flash('If there is an eligible workspace for that email, you’ll receive a password reset link. Check your inbox and spam folder.'); redirect('/users/admin/resetpassword.php');
}
auth_start('Forgot your password?', 'Enter your workspace email and we’ll help you get back in.');
?>
<form class="form-stack" method="post" data-submit><?= csrf_field() ?><div class="field"><label for="email">Work email</label><input type="email" name="email" id="email" autocomplete="email" required maxlength="255" placeholder="you@company.com"></div><button class="button" type="submit">Send reset link <?= icon('arrow') ?></button></form>
<p class="form-after"><a class="text-link" href="/users/admin/login.php">Back to sign in</a></p>
<?php auth_end(); ?>
