<?php
require_once __DIR__ . '/../../app/auth.php';
require_once __DIR__ . '/../../app/mail.php';
require_post(); $db = database(); $user = admin_user($db);
$role = input('role'); $id = filter_var(input('staff_id'),FILTER_VALIDATE_INT,['options'=>['min_range'=>1]]);
if (!in_array($role,['employee','manager'],true) || !$id) fail('Choose a staff member to invite.',422);
$table = $role === 'employee' ? 'employees' : 'managers';
$emailField = $role === 'employee' ? 'email' : 'manager_email';
$staff = db_one($db,"SELECT id, `$emailField` AS email FROM `$table` WHERE id = ? AND company_id = ?",[$id,$user['company_id']]);
if (!$staff) fail('This person could not be found in your workspace.',404);
$back = '/users/admin/workspace.php?module=' . ($role === 'employee' ? 'employees' : 'managers');
if (!mail_ready()) { flash('Email delivery is not connected. Your administrator needs to connect account email before invitations can be sent.','error'); redirect($back); }
rate_limit($db,'staff-invite-' . $role,(string)$user['company_id'] . ':' . $id,3);
$token = bin2hex(random_bytes(32));
db_run($db,'INSERT INTO staff_invitations (company_id, staff_role, staff_id, email, token_hash, expires_at) VALUES (?, ?, ?, ?, ?, UTC_TIMESTAMP() + INTERVAL 24 HOUR)',[$user['company_id'],$role,$id,$staff['email'],hash('sha256',$token)]);
$sent = send_account_mail($staff['email'],'Your Simplr workspace invitation',"You have been invited to " . $user['company_name'] . " as a " . $role . ".\n\nChoose your password using this link within 24 hours:\n" . account_link('/users/accept-invitation.php',$token) . "\n\nYour workspace code: " . $user['company_id'] . "\nIf you did not expect this invitation, ignore it. Your account has not been changed.");
if ($sent) {
    flash('The mail server accepted the invitation. If it is not in the inbox, ask them to check spam or junk and confirm their address.');
} else {
    db_run($db,'UPDATE staff_invitations SET used_at = UTC_TIMESTAMP() WHERE company_id = ? AND staff_role = ? AND staff_id = ? AND token_hash = ? AND used_at IS NULL',[$user['company_id'],$role,$id,hash('sha256',$token)]);
    flash('The mail server could not accept the invitation. Try again; if it continues, check the server mail log for the SMTP error.','error');
}
redirect($back);
