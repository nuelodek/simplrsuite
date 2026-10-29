<?php
require_once __DIR__ . '/../../app/auth.php';
require_once __DIR__ . '/../../app/mail.php';
require_post();
$company = input('company_name'); $industry = input('industry'); $email = strtolower(input('email'));
$password = is_string($_POST['password'] ?? null) ? $_POST['password'] : '';
$confirmation = is_string($_POST['confirm_password'] ?? null) ? $_POST['confirm_password'] : '';
$_SESSION['signup_old'] = ['company_name'=>$company, 'industry'=>$industry, 'email'=>$email];
$error = password_error($password, $confirmation);
if ($company === '' || mb_strlen($company) > 120 || $industry === '' || mb_strlen($industry) > 100) $error = 'Enter your business name and industry.';
if (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 255) $error = 'Enter a valid email address.';
if ($error) { flash($error,'error'); redirect('/users/admin/signup.php'); }
if (!mail_ready()) { flash('Account registration is temporarily unavailable while email delivery is being connected. Please try again later.','error'); redirect('/users/admin/signup.php'); }
$db = database(); rate_limit($db, 'signup', '', 10);
if (db_one($db, 'SELECT id FROM users WHERE email = ?', [$email])) {
    flash('An account may already use this email. Try signing in or resetting your password.','error'); redirect('/users/admin/signup.php');
}
$hash = password_hash($password, PASSWORD_DEFAULT);
for ($attempt = 0; $attempt < 5; $attempt++) {
    $companyId = random_int(100000,999999999);
    try {
        db_run($db, "INSERT INTO users (company_name, industry, email, password, company_id, status, freetrial, completed_profile) VALUES (?, ?, ?, ?, ?, 'Unverified', 'inactive', '0')", [$company,$industry,$email,$hash,$companyId]);
        $userId = (int)$db->insert_id; break;
    } catch (mysqli_sql_exception $ex) {
        if ($ex->getCode() !== 1062) throw $ex;
        if (db_one($db, 'SELECT id FROM users WHERE email = ?', [$email])) { flash('Please sign in or reset your password to access this account.','error'); redirect('/users/admin/login.php'); }
    }
}
if (!isset($userId)) fail('We could not create the workspace. Please try again.',503);
session_regenerate_id(true);
$_SESSION['verification_user_id'] = $userId;
unset($_SESSION['signup_old']);
$code = issue_verification_code($db, $userId);
$sent = send_account_mail($email, 'Verify your Simplr workspace', "Welcome to Simplr Suite.\n\nEnter this six-digit verification code in Simplr Suite within 10 minutes:\n\n" . $code . "\n\nIf you did not create this workspace, you can ignore this email.");
flash($sent ? 'Your workspace has been created. Enter the six-digit code sent to your email.' : 'Your workspace has been created, but we could not send the code. Use Resend below to try again.', $sent ? 'success' : 'error');
redirect('/users/admin/verifyaccount.php');
