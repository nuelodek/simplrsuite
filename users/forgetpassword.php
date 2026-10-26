<?php
require_once __DIR__ . '/../app/staff-recovery.php';
require_once __DIR__ . '/../app/view.php';
$role = is_string($_GET['role'] ?? null) ? $_GET['role'] : '';
if (isset($_GET['token']) && is_string($_GET['token'])) redirect('/users/reset_password.php?token=' . rawurlencode($_GET['token']));
if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    require_post(); $role = input('role');
    request_staff_reset(database(), $role, strtolower(input('email')), input('workspace'));
    flash('If these details match an active account, a password reset link will arrive shortly.');
    redirect('/users/forgetpassword.php?role=' . rawurlencode($role));
}
if (!in_array($role, ['employee','manager'], true)) {
    auth_start('Find your way back.', 'Choose the type of account you use to sign in.');
    echo '<div class="recovery-options">';
    foreach (['Business owner'=>'/users/admin/resetpassword.php','Employee'=>'/users/forgetpassword.php?role=employee','Manager'=>'/users/forgetpassword.php?role=manager','Talent'=>'/users/talents/resetpassword.php'] as $label=>$href) echo '<a class="button secondary" href="' . e($href) . '">' . e($label) . icon('arrow') . '</a>';
    echo '</div>'; auth_end(); exit;
}
auth_start('Reset your ' . $role . ' password.', 'We’ll send a recovery link to your work email.');
?>
<form method="post" class="form-stack" data-submit><?= csrf_field() ?><input type="hidden" name="role" value="<?= e($role) ?>">
<div class="field"><label for="email">Work email</label><input type="email" id="email" name="email" maxlength="255" autocomplete="email" required></div>
<div class="field"><label for="workspace">Workspace code</label><input id="workspace" name="workspace" inputmode="numeric" pattern="[1-9][0-9]{0,9}" maxlength="10" required><small>Use the code in your invitation. Your administrator can also share it. This selects the right company if you use the same email in multiple workspaces.</small></div>
<button class="button" type="submit">Send reset link</button>
</form>
<p class="form-after"><a class="text-link" href="/users/<?= $role === 'manager' ? 'managers' : 'employees' ?>/login.php">Back to sign in</a> · <a class="text-link" href="/users/forgetpassword.php">Choose another account type</a></p>
<?php auth_end(); ?>
