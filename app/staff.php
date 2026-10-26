<?php
declare(strict_types=1);
require_once __DIR__ . '/auth.php';

function task_project_join(): string {
    // Prefer a real unique ID; accept historical numeric IDs only if unambiguous.
    return '(p.unique_id = t.project_id OR (BINARY t.project_id = CAST(p.id AS BINARY) AND NOT EXISTS (SELECT 1 FROM projects canonical WHERE canonical.company_id = p.company_id AND canonical.unique_id = t.project_id))) AND p.company_id = t.company_id';
}

function staff_accounts(mysqli $db, string $role, string $email, string $company = ''): array {
    $params = [$email];
    $companyFilter = $company !== '' ? ' AND a.company_id = ?' : '';
    if ($company !== '') $params[] = $company;
    if ($role === 'employee') {
        return db_rows($db, "SELECT a.id AS auth_id, a.password, a.email, e.*, u.company_name FROM employees_auth a JOIN employees e ON e.company_id = a.company_id AND e.email = a.email AND (a.employee_id = e.unique_id OR a.employee_id IS NULL OR a.employee_id = '') JOIN users u ON u.company_id = e.company_id AND u.status = 'Verified' WHERE a.email = ? $companyFilter", $params);
    }
    if ($role === 'manager') {
        return db_rows($db, "SELECT a.id AS auth_id, a.password, a.manager_email AS email, m.*, u.company_name FROM manager_auth a JOIN managers m ON m.company_id = a.company_id AND m.manager_email = a.manager_email AND (a.manager_id = m.unique_id OR BINARY a.manager_id = CAST(m.id AS BINARY) OR a.manager_id IS NULL OR a.manager_id = '') JOIN users u ON u.company_id = m.company_id AND u.status = 'Verified' WHERE a.manager_email = ? AND m.status NOT IN ('suspended','disabled','inactive') $companyFilter", $params);
    }
    throw new LogicException('Unsupported staff role');
}
function staff_user(mysqli $db): array {
    $role = $_SESSION['auth_role'] ?? '';
    if (!in_array($role,['employee','manager'],true)) {
        if (wants_json()) fail('Please sign in to continue.',401);
        redirect('/users/employees/login.php');
    }
    $accounts = staff_accounts($db,$role,$_SESSION['email'] ?? '',(string)($_SESSION['company_id'] ?? ''));
    foreach ($accounts as $user) {
        if ((int)$user['auth_id'] === (int)($_SESSION['auth_id'] ?? 0) && (int)$user['id'] === (int)($_SESSION['staff_id'] ?? 0) && hash_equals(hash('sha256',$user['password']),$_SESSION['auth_stamp'] ?? '')) return $user;
    }
    $_SESSION = [];
    if (wants_json()) fail('Please sign in to continue.',401);
    redirect('/users/' . ($role === 'manager' ? 'managers' : 'employees') . '/login.php');
}
function staff_login_page(string $role): void {
    require_once __DIR__ . '/view.php';
    $base = '/users/' . ($role === 'manager' ? 'managers' : 'employees');
    if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
        require_post(); $email = strtolower(input('email')); $company = input('workspace');
        $password = is_string($_POST['password'] ?? null) ? $_POST['password'] : '';
        if (!filter_var($email,FILTER_VALIDATE_EMAIL) || ($company !== '' && !ctype_digit($company))) {
            flash('Enter a valid work email and workspace code.','error'); redirect($base . '/login.php');
        }
        $db = database(); rate_limit($db,'staff-login-ip','',60); rate_limit($db,'staff-login-' . $role,$email,10);
        $accounts = staff_accounts($db,$role,$email,$company);
        $user = count($accounts) === 1 ? $accounts[0] : null;
        $valid = password_verify($password,$user['password'] ?? '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2uheWG/igi.');
        if (!$user || !$valid) {
            flash('We could not sign you in. Check your email, password, and workspace code from your invitation.','error'); redirect($base . '/login.php');
        }
        session_regenerate_id(true);
        $_SESSION = ['csrf_token'=>bin2hex(random_bytes(32)),'auth_role'=>$role,'auth_id'=>$user['auth_id'],'staff_id'=>$user['id'],'auth_stamp'=>hash('sha256',$user['password']),'user_id'=>$user['auth_id'],'company_id'=>$user['company_id'],'email'=>$email,'company_name'=>$user['company_name'],'companyName'=>$user['company_name'],'unique_id'=>$user['unique_id'],'department_id'=>$user['department_id'],'role_id'=>$user['role_id']];
        if ($role === 'employee') {
            foreach (['first_name','last_name','manager_id','phone','hire_date','job_title','created_at','updated_at'] as $name) $_SESSION[$name] = $user[$name];
            $_SESSION['employee_id'] = $user['id'];
        } else {
            $_SESSION['manager_id'] = $user['id']; $_SESSION['manager_name'] = $user['manager_name']; $_SESSION['manager_email'] = $email;
        }
        redirect('/users/employees/workspace.php');
    }
    auth_start(ucfirst($role) . ' sign in.', 'Your people, your projects, and your next steps.');
    echo '<form method="post" class="form-stack" data-submit>' . csrf_field() . '<div class="field"><label for="email">Work email</label><input id="email" name="email" type="email" autocomplete="username" required maxlength="255"></div><div class="field"><label for="password">Password</label><div class="password-wrap"><input id="password" name="password" type="password" autocomplete="current-password" required><button type="button" data-password="password" aria-controls="password" aria-pressed="false">Show</button></div></div><div class="field"><label for="workspace">Workspace code (optional)</label><input id="workspace" name="workspace" inputmode="numeric" pattern="[0-9]+" maxlength="10"><small>Use the code in your invitation if you belong to more than one workspace.</small></div><button class="button" type="submit">Sign in ' . icon('arrow') . '</button></form><p class="form-after"><a class="text-link" href="/users/forgetpassword.php?role=' . e($role) . '">Forgot your password?</a> Need an account? Ask your administrator for an invitation.</p><div class="role-links"><a href="/users/admin/login.php">Business owner</a><a href="/users/' . ($role === 'manager' ? 'employees' : 'managers') . '/login.php">' . ($role === 'manager' ? 'Employee' : 'Manager') . ' sign in</a></div>';
    auth_end();
}
