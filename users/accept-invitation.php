<?php
require_once __DIR__ . '/../app/auth.php';
require_once __DIR__ . '/../app/view.php';
$token = is_string($_GET['token'] ?? null) ? $_GET['token'] : '';
if (!preg_match('/^[a-f0-9]{64}$/D',$token)) fail('This invitation link is invalid. Ask your administrator for a new invitation.',400);
$db = database();
$invitation = db_one($db,'SELECT i.*, u.company_name FROM staff_invitations i JOIN users u ON u.company_id = i.company_id AND u.status = \'Verified\' WHERE i.token_hash = ? AND i.used_at IS NULL AND i.expires_at > UTC_TIMESTAMP()',[hash('sha256',$token)]);
if (!$invitation) fail('This invitation has expired or has already been used. Ask your administrator for a new one.',410);
if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    require_post(); rate_limit($db,'accept-invitation','',15);
    $password = is_string($_POST['password'] ?? null) ? $_POST['password'] : '';
    $confirm = is_string($_POST['confirm_password'] ?? null) ? $_POST['confirm_password'] : '';
    if ($error = password_error($password,$confirm)) { flash($error,'error'); redirect('/users/accept-invitation.php?token=' . rawurlencode($token)); }
    $db->begin_transaction();
    try {
        $locked = db_one($db,'SELECT * FROM staff_invitations WHERE id = ? AND used_at IS NULL AND expires_at > UTC_TIMESTAMP() FOR UPDATE',[$invitation['id']]);
        if (!$locked) { $db->rollback(); fail('This invitation is no longer available.',410); }
        $employee = $locked['staff_role'] === 'employee';
        $table = $employee ? 'employees' : 'managers'; $authTable = $employee ? 'employees_auth' : 'manager_auth';
        $emailField = $employee ? 'email' : 'manager_email'; $idField = $employee ? 'employee_id' : 'manager_id';
        $staff = db_one($db,"SELECT * FROM `$table` WHERE id = ? AND company_id = ? FOR UPDATE",[$locked['staff_id'],$locked['company_id']]);
        if (!$staff || strtolower($staff[$emailField]) !== strtolower($locked['email'])) { $db->rollback(); fail('The details for this invitation have changed. Request a new one.',410); }
        $existing = db_rows($db,"SELECT id FROM `$authTable` WHERE company_id = ? AND (`$idField` = ? OR `$emailField` = ?) FOR UPDATE",[$locked['company_id'],$staff['unique_id'],$staff[$emailField]]);
        if (count($existing)>1) { $db->rollback(); fail('Your administrator needs to reconcile duplicate sign-in records before you can accept this invitation.',409); }
        $hash = password_hash($password,PASSWORD_DEFAULT);
        if ($existing) db_run($db,"UPDATE `$authTable` SET password = ?, `$idField` = ?, `$emailField` = ? WHERE id = ? AND company_id = ?",[$hash,$staff['unique_id'],$staff[$emailField],$existing[0]['id'],$locked['company_id']]);
        elseif ($employee) db_run($db,'INSERT INTO employees_auth (company_id, email, password, employee_id) VALUES (?, ?, ?, ?)',[$locked['company_id'],$staff['email'],$hash,$staff['unique_id']]);
        else db_run($db,'INSERT INTO manager_auth (company_id, manager_name, manager_email, password, manager_id) VALUES (?, ?, ?, ?, ?)',[$locked['company_id'],$staff['manager_name'],$staff['manager_email'],$hash,$staff['unique_id']]);
        if (!$employee) db_run($db,"UPDATE managers SET status = 'approved' WHERE id = ? AND company_id = ?",[$staff['id'],$locked['company_id']]);
        db_run($db,'UPDATE staff_invitations SET used_at = UTC_TIMESTAMP() WHERE company_id = ? AND staff_role = ? AND staff_id = ? AND used_at IS NULL',[$locked['company_id'],$locked['staff_role'],$locked['staff_id']]);
        $db->commit();
        session_regenerate_id(true); $_SESSION = [];
        flash('Your password is ready. Sign in to ' . $invitation['company_name'] . '. Workspace code: ' . $invitation['company_id']);
        redirect('/users/' . ($employee ? 'employees' : 'managers') . '/login.php');
    } catch (Throwable $error) { $db->rollback(); throw $error; }
}
auth_start('Welcome to your team.', 'Join ' . $invitation['company_name'] . ' as a ' . $invitation['staff_role'] . '.');
?>
<p class="subtle">Set a password for <?= e($invitation['email']) ?>.</p><form class="form-stack" method="post" data-submit><?= csrf_field() ?><div class="field"><label for="password">Choose a password</label><div class="password-wrap"><input id="password" name="password" type="password" minlength="12" maxlength="72" autocomplete="new-password" required><button type="button" data-password="password" aria-controls="password" aria-pressed="false">Show</button></div><small>Use 12–72 characters.</small></div><div class="field"><label for="confirm_password">Confirm password</label><input id="confirm_password" name="confirm_password" type="password" minlength="12" maxlength="72" autocomplete="new-password" required></div><button class="button" type="submit">Join workspace <?= icon('arrow') ?></button></form>
<?php auth_end(); ?>
