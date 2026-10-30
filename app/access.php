<?php
declare(strict_types=1);
require_once __DIR__ . '/staff.php';
require_once __DIR__ . '/billing-catalog.php';

function billing_module_active(mysqli $db, int $companyId, string $module): bool {
    if (config('BILLING_ENFORCE','false') !== 'true') return true;
    $keys = [];
    foreach (billing_catalog() as $key=>$tool) if (in_array($module,$tool['modules'],true)) $keys[] = $key;
    if (!$keys) return true;
    $marks = implode(',',array_fill(0,count($keys),'?'));
    return (bool)db_one($db,"SELECT tool_key FROM billing_entitlements WHERE company_id = ? AND provider_mode = ? AND tool_key IN ($marks) AND valid_until > UTC_TIMESTAMP() LIMIT 1",[$companyId,config('PAYSTACK_MODE','test'),...$keys]);
}

function workspace_user(mysqli $db): array {
    if (in_array($_SESSION['auth_role'] ?? '', ['employee','manager'], true)) return staff_user($db);
    return admin_user($db);
}

function assignable_modules(): array {
    // Directory changes and account invitations remain business-owner operations.
    return array_diff_key(modules(), array_flip(['employees','managers','departments','teams'])) + [
        'payroll'=>['title'=>'Payroll','description'=>'Review your approved payslips and payment status.','nav'=>'finance','icon'=>'wallet','singular'=>'payroll record'],
    ];
}

function module_access(mysqli $db, array $user, string $key, bool $lock = false): string {
    if (($_SESSION['auth_role'] ?? '') === 'admin') return billing_module_active($db,(int)$user['company_id'],$key) ? 'edit' : 'view';
    if (!isset(assignable_modules()[$key])) return '';
    $grant = db_one($db, 'SELECT access_level FROM staff_tool_access WHERE company_id = ? AND staff_role = ? AND staff_id = ? AND module_key = ?' . ($lock ? ' FOR UPDATE' : ''), [$user['company_id'], $_SESSION['auth_role'], $user['id'], $key]);
    $level = in_array($grant['access_level'] ?? '', ['view','edit'], true) ? $grant['access_level'] : '';
    return $level === 'edit' && !billing_module_active($db,(int)$user['company_id'],$key) ? 'view' : $level;
}

function require_module_access(mysqli $db, array $user, string $key, bool $write = false, bool $lock = false): string {
    $level = module_access($db, $user, $key, $lock);
    if ($write && $level !== '' && !billing_module_active($db,(int)$user['company_id'],$key)) fail('This toolkit needs an active subscription for editing. Your workspace owner can renew it in Billing. Existing records remain readable.',402);
    if ($level === '' || ($write && $level !== 'edit')) fail($write ? 'You need edit access to change records in this tool. Contact your workspace administrator.' : 'This tool has not been assigned to you. Contact your workspace administrator.', 403);
    return $level;
}

function visible_modules(mysqli $db, array $user): array {
    if (($_SESSION['auth_role'] ?? '') === 'admin') return modules();
    $grants = db_rows($db, 'SELECT module_key FROM staff_tool_access WHERE company_id = ? AND staff_role = ? AND staff_id = ? AND access_level IN (\'view\',\'edit\')', [$user['company_id'], $_SESSION['auth_role'], $user['id']]);
    return array_intersect_key(assignable_modules(), array_flip(array_column($grants, 'module_key')));
}

function audit_event(mysqli $db, int $companyId, string $action, string $targetType, string $targetId, array $details = [], ?string $actorRole = null, ?int $actorId = null): void {
    $role = $actorRole ?? ($_SESSION['auth_role'] ?? '');
    $actor = $actorId ?? match($role) { 'admin'=>$_SESSION['user_id'] ?? 0, 'talent'=>$_SESSION['talent_account_id'] ?? 0, default=>$_SESSION['staff_id'] ?? 0 };
    db_run($db, 'INSERT INTO workspace_audit (company_id, actor_role, actor_id, action, target_type, target_id, details) VALUES (?, ?, ?, ?, ?, ?, ?)', [$companyId, $role, $actor, $action, $targetType, $targetId, json_encode($details, JSON_THROW_ON_ERROR)]);
}
