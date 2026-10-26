<?php
require_once __DIR__ . '/../../app/view.php';
if (($_SESSION['auth_role'] ?? '') === 'admin') redirect('/users/admin/newdashboard.php');
auth_start('Welcome back.', 'Sign in to your business workspace.');
$email = $_SESSION['login_email'] ?? ''; unset($_SESSION['login_email']);
?>
<form class="form-stack" action="/users/admin/login_process.php" method="post" data-submit>
  <?= csrf_field() ?>
  <div class="field"><label for="email">Work email</label><input id="email" name="email" type="email" autocomplete="username" value="<?= e($email) ?>" placeholder="you@company.com" maxlength="255" required></div>
  <div class="field"><label for="password">Password</label><div class="password-wrap"><input id="password" name="password" type="password" autocomplete="current-password" required><button type="button" data-password="password" aria-controls="password" aria-pressed="false">Show</button></div></div>
  <div class="form-meta"><span>Welcome to a simpler workday.</span><a href="/users/admin/resetpassword.php">Forgot password?</a></div>
  <button class="button" type="submit">Sign in <?= icon('arrow') ?></button>
</form>
<p class="form-after">New to Simplr? <a class="text-link" href="/users/admin/signup.php">Create a workspace</a></p>
<div class="role-links"><a href="/users/employees/login.php">Employee sign in</a><a href="/users/managers/login.php">Manager sign in</a><a href="/users/talents/talentlogin.php">Talent sign in</a></div>
<?php auth_end(); ?>
