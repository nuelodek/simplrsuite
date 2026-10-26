<?php
require_once __DIR__ . '/../../app/auth.php';
require_post();
$email = strtolower(input('email'));
$password = is_string($_POST['password'] ?? null) ? $_POST['password'] : '';
$_SESSION['login_email'] = $email;
if (!filter_var($email, FILTER_VALIDATE_EMAIL) || $password === '') {
    flash('Enter your email and password to continue.', 'error'); redirect('/users/admin/login.php');
}
$db = database();
rate_limit($db, 'login-ip', '', 60);
rate_limit($db, 'login', $email);
$user = db_one($db, 'SELECT * FROM users WHERE email = ?', [$email]);
$valid = password_verify($password, $user['password'] ?? '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2uheWG/igi.');
if (!$user || !$valid || !in_array($user['status'], ['Verified', 'Unverified'], true)) {
    flash('The email or password is incorrect, or this account is unavailable.', 'error'); redirect('/users/admin/login.php');
}
if ($user['status'] === 'Unverified') {
    session_regenerate_id(true);
    $_SESSION['verification_user_id'] = (int)$user['id'];
    redirect('/users/admin/verifyaccount.php');
}
if (password_needs_rehash($user['password'], PASSWORD_DEFAULT)) {
    $user['password'] = password_hash($password, PASSWORD_DEFAULT);
    db_run($db, 'UPDATE users SET password = ? WHERE id = ?', [$user['password'], $user['id']]);
}
login_admin($user);
redirect('/users/admin/newdashboard.php');
