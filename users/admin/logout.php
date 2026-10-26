<?php
require_once __DIR__ . '/../../app/view.php';
if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    require_post();
    $loginPath = ($_SESSION['auth_role'] ?? '') === 'talent' ? '/users/talents/talentlogin.php' : '/users/admin/login.php';
    $_SESSION = []; session_destroy();
    $params = session_get_cookie_params();
    setcookie(session_name(), '', ['expires'=>time()-3600,'path'=>$params['path'],'secure'=>$params['secure'],'httponly'=>true,'samesite'=>'Lax']);
    redirect($loginPath);
}
auth_start('Sign out', 'Confirm that you want to sign out.');
echo '<form method="post">' . csrf_field() . '<button class="button" type="submit">Sign out</button></form><p class="form-after"><a href="/users/admin/newdashboard.php">Back to workspace</a></p>';
auth_end();
