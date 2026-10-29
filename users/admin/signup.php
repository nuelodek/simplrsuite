<?php
require_once __DIR__ . '/../../app/view.php';
auth_start('Create a workspace', 'Set up your business workspace.');
$old = $_SESSION['signup_old'] ?? []; unset($_SESSION['signup_old']);
?>
<form class="form-stack" method="post" action="/users/admin/congrats.php" data-submit>
<?= csrf_field() ?>
<div class="field"><label for="company_name">Business name</label><input id="company_name" name="company_name" autocomplete="organization" maxlength="120" value="<?= e($old['company_name'] ?? '') ?>" placeholder="Your company" required></div>
<div class="field"><label for="industry">Industry</label><select name="industry" id="industry" required><option value="">Choose your industry</option><?php foreach (['Agriculture','Construction','Consulting','Education','Energy','Entertainment','Finance','Healthcare','Hospitality','Legal','Manufacturing','Marketing','Real estate','Retail','Technology','Transport','Other'] as $industry): ?><option <?= ($old['industry'] ?? '') === $industry ? 'selected' : '' ?>><?= e($industry) ?></option><?php endforeach; ?></select></div>
<div class="field"><label for="email">Work email</label><input id="email" name="email" type="email" autocomplete="email" maxlength="255" value="<?= e($old['email'] ?? '') ?>" placeholder="you@company.com" required></div>
<div class="field"><label for="password">Password</label><div class="password-wrap"><input id="password" type="password" name="password" minlength="12" maxlength="72" autocomplete="new-password" aria-describedby="password-help" required><button type="button" data-password="password" aria-controls="password" aria-pressed="false">Show</button></div><small id="password-help">Use 12–72 characters. A memorable phrase works well.</small></div>
<div class="field"><label for="confirm_password">Confirm password</label><input id="confirm_password" type="password" name="confirm_password" minlength="12" maxlength="72" autocomplete="new-password" required></div>
<p class="subtle" style="font-size:11px;margin:0">We’ll email you a six-digit code to verify your address. Read our <a href="/public/privacypolicy.php">privacy policy</a>.</p>
<button type="submit" class="button">Create workspace <?= icon('arrow') ?></button>
</form><p class="form-after">Already have a workspace? <a class="text-link" href="/users/admin/login.php">Sign in</a></p>
<?php auth_end(); ?>
