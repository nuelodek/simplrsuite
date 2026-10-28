<?php
declare(strict_types=1);
require_once __DIR__ . '/access.php';
require_once __DIR__ . '/richtext.php';
require_once __DIR__ . '/webhooks.php';
// This allowlist is shared by the HTML workspace and its record API. SQL identifiers
// are never accepted from a request. Existing tables remain the source of truth.
function field(string $label, string $type = 'text', bool $required = true, int $max = 255, array $options = []): array {
    return compact('label','type','required','max','options');
}
function normalize_event_meeting_provider(array $event): string {
    $link = trim((string)($event['meet_link'] ?? ''));
    if (($event['event_type'] ?? '') !== 'online') return '';
    if ($link === '') return 'simplr';
    $host = strtolower(parse_url($link, PHP_URL_HOST) ?? '');
    if (str_contains($host, 'meet.jit.si') || str_contains($host, 'jitsi.org')) return 'simplr';
    if (str_contains($host, 'zoom.us') || str_contains($host, 'zoom.com')) return 'zoom';
    if (str_contains($host, 'meet.google.com')) return 'google-meet';
    if (str_contains($host, 'teams.microsoft.com') || str_contains($host, 'microsoft.com')) return 'microsoft-teams';
    return 'custom';
}
function build_simplr_meeting_link(int $companyId, string $eventName, ?int $eventId = null): string {
    $slug = preg_replace('/[^a-z0-9]+/i', '-', strtolower(trim($eventName)));
    $slug = trim((string)$slug, '-');
    $slug = $slug !== '' ? $slug : 'simplr-call';
    $suffix = $eventId !== null ? '-' . $eventId : '-' . bin2hex(random_bytes(4));
    return 'https://meet.jit.si/simplr-' . $companyId . '-' . $slug . $suffix;
}
function social_platform_options(): array {
    return ['linkedin'=>'LinkedIn','facebook'=>'Facebook','instagram'=>'Instagram'];
}
function normalize_social_platform_targets(mixed $value): array {
    $raw = is_array($value) ? $value : [is_string($value) ? $value : ''];
    $targets = [];
    foreach ($raw as $entry) {
        if (!is_scalar($entry)) continue;
        foreach (preg_split('/\s*,\s*|\s*\|\s*/', trim((string)$entry), -1, PREG_SPLIT_NO_EMPTY) as $platform) {
            $key = strtolower($platform);
            if (in_array($key,['linkedin','facebook','instagram'],true)) $targets[$key] = true;
        }
    }
    return array_keys($targets);
}
function job_social_post_target_url(array $job): string {
    $base = rtrim(config('APP_URL', 'https://example.com'), '/');
    return $base . '/jobs/?job=' . rawurlencode($job['public_id']);
}
function social_platform_env_keys(string $platform): array {
    return [
        'linkedin' => ['LINKEDIN_ACCESS_TOKEN','LINKEDIN_ORGANIZATION_URN'],
        'facebook' => ['FACEBOOK_PAGE_ACCESS_TOKEN','FACEBOOK_PAGE_ID'],
        'instagram' => ['INSTAGRAM_ACCESS_TOKEN','INSTAGRAM_BUSINESS_ACCOUNT_ID'],
    ][$platform] ?? [];
}
function job_social_sync_ready(string $platform): bool {
    foreach (social_platform_env_keys($platform) as $key) { if (trim((string)config($key,'')) === '') return false; }
    return true;
}
function queue_social_sync(mysqli $db, int $companyId, int $jobId, string $socialTargets): void {
    $targets = normalize_social_platform_targets($socialTargets);
    if (!$targets) return;
    $job = db_one($db,'SELECT id, company_id, title, location, public_id, status FROM job_posts WHERE id = ? AND company_id = ?',[$jobId,$companyId]);
    if (!$job) return;
    foreach ($targets as $platform) {
        if (!job_social_sync_ready($platform)) continue;
        audit_event($db,$companyId,'job.social_sync_requested','job_posts',(string)$jobId,['platform'=>$platform,'job_url'=>job_social_post_target_url($job)]);
    }
}
function modules(): array {
    return [
        'jobs' => ['title'=>'Job postings','singular'=>'job posting','nav'=>'recruiting','icon'=>'briefcase','description'=>'Publish open roles and receive applications in your workspace.','table'=>'job_posts','pk'=>'id','fields'=>[
            'title'=>field('Job title','text',true,150),'location'=>field('Location','text',true,160),'workplace'=>field('Work arrangement','select',true,20,['remote','hybrid','on-site']),'employment_type'=>field('Employment type','select',true,30,['full-time','part-time','contract','internship']),'description'=>field('Role description','textarea',true,12000),'requirements'=>field('Skills and requirements','textarea',false,8000),'closing_date'=>field('Applications close on','date',false),'timezone'=>field('Closing-date timezone','select',true,64,DateTimeZone::listIdentifiers()),'status'=>field('Publication status','select',true,20,['draft','published','closed']),'social_targets'=>field('Share to social media','social-platforms',false,255,['linkedin','facebook','instagram']),
        ],'columns'=>['title','location','workplace','employment_type','status','closing_date']],
        'documents' => ['title'=>'Documents','singular'=>'document','nav'=>'files','icon'=>'file','description'=>'Keep contracts, policies and shared files in one organised library.','table'=>'documents','pk'=>'id','fields'=>[
            'title'=>field('Document title','text',true,160),'category'=>field('Category','select',true,50,['General','Contracts','Finance','HR & people','Policies','Sales & marketing','Product','Legal']),'notes'=>field('Notes','textarea',false,10000),
        ],'columns'=>['title','category','created_at']],
        'events' => ['title'=>'Events & meetings','singular'=>'event','nav'=>'calendar','icon'=>'calendar','description'=>'Plan meetings and events with a clear time, place, and agenda.','table'=>'events','pk'=>'event_id','fields'=>[
            'event_name'=>field('Event name'),'start_datetime'=>field('Start time','datetime-local'),'end_datetime'=>field('End time','datetime-local'),'timezone'=>field('Timezone','select',true,64,DateTimeZone::listIdentifiers()),'event_type'=>field('Meeting type','select',true,100,['online','in-person']),'meeting_provider'=>field('Meeting provider','select',false,50,['simplr','zoom','google-meet','microsoft-teams','custom']),'meetup_venue'=>field('Venue','text',false),'meet_link'=>field('Meeting link','url',false),'agenda'=>field('Agenda','textarea',false,10000),'event_description'=>field('Description','textarea',false,10000),'organizer'=>field('Organizer','text',false),
        ],'columns'=>['event_name','start_datetime','end_datetime','timezone','event_type']],
        'projects' => ['title'=>'Projects','singular'=>'project','nav'=>'projects','icon'=>'folder','description'=>'Give your next idea a plan, an owner, and a clear path forward.','table'=>'projects','pk'=>'id','fields'=>[
            'projectName'=>field('Project name'), 'projectDescription'=>field('Description','textarea',false,10000), 'milestoneName'=>field('Milestone name','text',false,160), 'milestoneDate'=>field('Milestone date','date',false), 'milestoneStatus'=>field('Milestone status','select',false,50,['not started','in progress','completed']), 'milestoneDescription'=>field('Milestone description','textarea',false,4000), 'startDate'=>field('Start date','date'), 'endDate'=>field('End date','date'), 'status'=>field('Status','select',true,50,['pending','ongoing','completed','on hold']), 'department'=>field('Department','ref:departments',false,50), 'projectManager'=>field('Project manager','ref:managers',false),
        ],'columns'=>['projectName','status','startDate','endDate','milestoneName','milestoneDate']],
        'tasks' => ['title'=>'Tasks','singular'=>'task','nav'=>'tasks','icon'=>'task','description'=>'Know what needs doing, what matters most, and what comes next.','table'=>'tasks','pk'=>'id','fields'=>[
            'title'=>field('Task name'),'project_id'=>field('Project','ref:projects'), 'description'=>field('Description','textarea',false,10000),'due_date'=>field('Due date','date'),'priority'=>field('Priority','select',true,50,['low','medium','high']),'status'=>field('Status','select',true,50,['pending','ongoing','completed']),'assignee'=>field('Assigned to','ref:employees',false),
        ],'columns'=>['title','due_date','priority','status']],
        'departments' => ['title'=>'Departments','singular'=>'department','nav'=>'people','icon'=>'users','description'=>'Build a clear structure for the people behind your business.','table'=>'departments','pk'=>'id','fields'=>['department_name'=>field('Department name','text',true,100),'location'=>field('Location','text',false),'manager_id'=>field('Department head','ref:managers',false)],'columns'=>['department_name','location','created_at']],
        'managers' => ['title'=>'Managers','singular'=>'manager','nav'=>'people','icon'=>'users','description'=>'Keep your management directory organized by department.','table'=>'managers','pk'=>'id','fields'=>['manager_name'=>field('Full name'),'manager_email'=>field('Email','email'),'department_id'=>field('Department','ref:departments')],'columns'=>['manager_name','manager_email','status']],
        'employees' => ['title'=>'Employees','singular'=>'employee','nav'=>'people','icon'=>'users','description'=>'A shared directory of your people and their roles.','table'=>'employees','pk'=>'id','fields'=>['first_name'=>field('First name','text',true,50),'last_name'=>field('Last name','text',true,50),'email'=>field('Email','email',true,100),'phone'=>field('Phone','tel',false,20),'hire_date'=>field('Hire date','date'),'job_title'=>field('Job title','text',false,100),'department_id'=>field('Department','ref:departments'),'manager_id'=>field('Manager','ref:managers',false),'github_login'=>field('GitHub username','text',false,39)],'columns'=>['first_name','last_name','email','job_title']],
        'teams' => ['title'=>'Teams','singular'=>'team','nav'=>'people','icon'=>'users','description'=>'Bring the right people together around a shared project.','table'=>'teams','pk'=>'id','fields'=>['team_name'=>field('Team name'),'department_id'=>field('Department','ref:departments',false),'team_description'=>field('Description','textarea',false,10000),'project_id'=>field('Project','ref:project_ids',false),'team_leader_id'=>field('Team lead','ref:employees',false),'team_members'=>field('Members','multi:employees',false),'team_status'=>field('Status','select',true,50,['active','inactive'])],'columns'=>['team_name','team_status','created_at']],
        'leads' => ['title'=>'Leads','singular'=>'lead','nav'=>'sales','icon'=>'chart','description'=>'Turn first conversations into lasting customer relationships.','table'=>'leads','pk'=>'lead_id','fields'=>['lead_name'=>field('Lead name'),'lead_email'=>field('Email','email',false),'lead_phone'=>field('Phone','tel',false,20),'lead_source'=>field('Source','select',true,50,['Website','Social-Media','Email','Referral']),'status'=>field('Status','select',true,50,['new','contacted','qualified','lost'])],'columns'=>['lead_name','lead_email','lead_source','status']],
        'customers' => ['title'=>'Customers','singular'=>'customer','nav'=>'sales','icon'=>'users','description'=>'Keep the details that help you look after your customers.','table'=>'customers','pk'=>'id','fields'=>['first_name'=>field('First name','text',true,50),'last_name'=>field('Last name','text',true,50),'business_name'=>field('Business name'),'customer_email'=>field('Email','email',true,100),'phone_number'=>field('Phone','tel',false,20),'address'=>field('Address','text',false),'acquired_item'=>field('Product or service','text',false)],'columns'=>['first_name','last_name','business_name','customer_email']],
        'contacts' => ['title'=>'Contacts','singular'=>'contact','nav'=>'sales','icon'=>'users','description'=>'Keep your business conversations connected.','table'=>'contacts','pk'=>'contact_id','fields'=>['contact_name'=>field('Full name'),'contact_email'=>field('Email','email',false),'contact_phone'=>field('Phone','tel',false,20),'lead_id'=>field('Lead','ref:leads',false,50),'customer_id'=>field('Customer','ref:customer_codes',false,50)],'columns'=>['contact_name','contact_email','contact_phone']],
        'accounts' => ['title'=>'Customer accounts','singular'=>'account','nav'=>'sales','icon'=>'briefcase','description'=>'Organize the businesses and people you work with.','table'=>'accounts','pk'=>'account_id','fields'=>['account_owner'=>field('Account owner'),'account_email'=>field('Email','email',false),'account_phone'=>field('Phone','tel',false,20),'customer_id'=>field('Customer','ref:customer_codes'),'status'=>field('Status','select',true,50,['active','inactive'])],'columns'=>['account_owner','account_email','status']],
        'deals' => ['title'=>'Deals','singular'=>'deal','nav'=>'sales','icon'=>'chart','description'=>'Follow your opportunities from proposal to outcome.','table'=>'deals','pk'=>'deal_id','fields'=>['deal_with'=>field('Deal name'),'lead_id'=>field('Lead','ref:leads',false),'account_id'=>field('Account','ref:accounts',false),'amount'=>field('Amount','money'),'stage'=>field('Stage','select',true,50,['negotiation','proposal','won','lost'])],'columns'=>['deal_with','amount','stage','created_at']],
        'invoices' => ['title'=>'Invoice register','singular'=>'invoice record','nav'=>'finance','icon'=>'wallet','description'=>'Track invoice dates, amounts, and payment status.','table'=>'invoices','pk'=>'id','fields'=>['customer_id'=>field('Customer','ref:customer_ids'),'invoice_date'=>field('Invoice date','date'),'due_date'=>field('Due date','date'),'amount'=>field('Amount','money'),'status'=>field('Status','select',true,50,['Paid','Unpaid','Cancelled'])],'columns'=>['id','invoice_date','due_date','amount','status']],
        'expenses' => ['title'=>'Expenses','singular'=>'expense','nav'=>'finance','icon'=>'wallet','description'=>'Keep an organized record of what your business spends.','table'=>'expenses','pk'=>'id','fields'=>['description'=>field('Description'),'date'=>field('Expense date','date'),'amount'=>field('Amount','money'),'category'=>field('Category','text',true,50)],'columns'=>['description','date','category','amount']],
        'ledger' => ['title'=>'Ledger','singular'=>'ledger entry','nav'=>'finance','icon'=>'wallet','description'=>'Record income and expenses in your business ledger.','table'=>'ledger_entries','pk'=>'id','fields'=>['description'=>field('Description'),'date'=>field('Entry date','date'),'amount'=>field('Amount','money'),'type'=>field('Entry type','select',true,50,['income','expense']),'category'=>field('Category','text',true,50)],'columns'=>['description','date','type','amount']],
        'inventory' => ['title'=>'Inventory','singular'=>'item','nav'=>'finance','icon'=>'box','description'=>'Stay on top of the items your business keeps in stock.','table'=>'inventory_items','pk'=>'id','fields'=>['name'=>field('Item name','text',true,100),'description'=>field('Description','textarea',false,10000),'quantity'=>field('Quantity','integer'),'unit_price'=>field('Unit price','money'),'category'=>field('Category','text',true,50)],'columns'=>['name','quantity','unit_price','category']],
        'applications' => ['title'=>'Applications','singular'=>'application','nav'=>'recruiting','icon'=>'briefcase','description'=>'Keep track of applicants and the next step in your hiring process.','table'=>'applications','pk'=>'id','fields'=>['applicant_name'=>field('Applicant name','text',true,150),'email'=>field('Email','email',true,100),'phone'=>field('Phone','tel',false,20),'position_applied_for'=>field('Position','text',true,150),'status'=>field('Stage','select',true,50,['new','reviewing','interview','offer','hired','closed'])],'columns'=>['applicant_name','email','position_applied_for','status']],
        'incidents' => ['title'=>'Security incidents','singular'=>'incident','nav'=>'modules','icon'=>'shield','description'=>'Record security issues, assign a severity, and track follow-up.','table'=>'cyber_incidents','pk'=>'id','fields'=>['description'=>field('Description','textarea',true,10000),'severity'=>field('Severity','select',true,50,['low','medium','high','critical']),'status'=>field('Status','select',true,50,['open','investigating','resolved'])],'columns'=>['description','severity','status','reported_at']],
        'compliance' => ['title'=>'Compliance register','singular'=>'requirement','nav'=>'modules','icon'=>'shield','description'=>'Track requirements and their review status in one place.','table'=>'cyber_compliance','pk'=>'id','fields'=>['regulation'=>field('Requirement'),'status'=>field('Review status','select',true,50,['not reviewed','in review','action required','reviewed'])],'columns'=>['regulation','status','checked_at']],
        'products' => ['title'=>'Product development','singular'=>'product','nav'=>'modules','icon'=>'box','description'=>'Follow your products from the first idea to the next milestone.','table'=>'productdevelopment','pk'=>'id','fields'=>['product_name'=>field('Product name','text',true,100),'stage'=>field('Stage','select',true,100,['discovery','design','development','testing','launched']),'start_date'=>field('Start date','date'),'end_date'=>field('Target date','date'),'status'=>field('Status','select',true,50,['planned','ongoing','completed','on hold'])],'columns'=>['product_name','stage','status','end_date']],
        'assets' => ['title'=>'Office assets','singular'=>'asset','nav'=>'modules','icon'=>'briefcase','description'=>'Keep an inventory of the equipment that supports your business.','table'=>'assets','pk'=>'id','fields'=>['asset_name'=>field('Asset name','text',true,100),'description'=>field('Description','text',false),'purchase_date'=>field('Purchase date','date',false),'value'=>field('Purchase value','money',false)],'columns'=>['asset_name','description','purchase_date','value']],
        'objectives' => ['title'=>'Business strategy','singular'=>'objective','nav'=>'modules','icon'=>'chart','description'=>'Connect your priorities to clear objectives and target dates.','table'=>'business_objectives','pk'=>'id','fields'=>['title'=>field('Objective'),'description'=>field('Description','textarea',false,10000),'target_date'=>field('Target date','date'),'status'=>field('Status','select',true,50,['planned','ongoing','completed','on hold']),'measure'=>field('Success measure','text',false)],'columns'=>['title','target_date','status','measure']],
    ];
}
function promote_employee_to_manager(mysqli $db, int $companyId, int $employeeId, int $departmentId, bool $departmentHead): int {
    if ($departmentId < 1 || !db_one($db,'SELECT id FROM departments WHERE id = ? AND company_id = ?',[$departmentId,$companyId])) fail('Choose a department in this workspace.',422);
    $db->begin_transaction();
    try {
        $employee = db_one($db,'SELECT * FROM employees WHERE id = ? AND company_id = ? FOR UPDATE',[$employeeId,$companyId]);
        if (!$employee) { $db->rollback(); fail('This employee could not be found in your workspace.',404); }
        $existingManager = db_one($db,'SELECT * FROM managers WHERE company_id = ? AND employee_id = ? FOR UPDATE',[$companyId,$employeeId]);
        if ($existingManager && !in_array($existingManager['status'],['suspended','disabled','inactive'],true)) { $db->rollback(); fail('This employee already has an active manager record.',409); }
        $emailManager = db_one($db,'SELECT id,employee_id FROM managers WHERE company_id = ? AND LOWER(manager_email) = LOWER(?) LIMIT 1',[$companyId,$employee['email']]);
        if ($emailManager && (!$existingManager || (int)$emailManager['id'] !== (int)$existingManager['id'])) { $db->rollback(); fail('A manager record already uses this email. Review that record before promoting this employee.',409); }
        $role = db_one($db,'SELECT id FROM roles WHERE company_id = ? AND role_name = ?',[$companyId,'Manager']);
        if (!$role) {
            db_run($db,'INSERT INTO roles (company_id,role_name) VALUES (?,?)',[$companyId,'Manager']);
            $role = ['id'=>(int)$db->insert_id];
        }
        if ($existingManager) {
            $managerId = (int)$existingManager['id'];
            db_run($db,"UPDATE managers SET manager_name = ?, manager_email = ?, department_id = ?, role_id = ?, status = 'pending' WHERE id = ? AND company_id = ?",[trim($employee['first_name'] . ' ' . $employee['last_name']),$employee['email'],$departmentId,$role['id'],$managerId,$companyId]);
        } else {
            $uniqueId = 'man_' . bin2hex(random_bytes(10));
            db_run($db,"INSERT INTO managers (company_id,unique_id,manager_name,manager_email,department_id,employee_id,role_id,status) VALUES (?,?,?,?,?,?,?,'pending')",[$companyId,$uniqueId,trim($employee['first_name'] . ' ' . $employee['last_name']),$employee['email'],$departmentId,$employeeId,$role['id']]);
            $managerId = (int)$db->insert_id;
        }
        db_run($db,'UPDATE employees SET department_id = ? WHERE id = ? AND company_id = ?',[$departmentId,$employeeId,$companyId]);
        if ($departmentHead) db_run($db,'UPDATE departments SET manager_id = ? WHERE id = ? AND company_id = ?',[$managerId,$departmentId,$companyId]);
        audit_event($db,$companyId,$departmentHead ? 'employee.promoted_department_head' : 'employee.promoted_manager','employees',(string)$employeeId,['manager_id'=>$managerId,'department_id'=>$departmentId]);
        $db->commit();
        return $managerId;
    } catch (Throwable $error) { $db->rollback(); throw $error; }
}
function demote_manager_to_employee(mysqli $db, int $companyId, int $managerId, int $replacementId): int {
    $db->begin_transaction();
    try {
        $manager = db_one($db,'SELECT * FROM managers WHERE id = ? AND company_id = ? FOR UPDATE',[$managerId,$companyId]);
        if (!$manager) { $db->rollback(); fail('This manager could not be found in your workspace.',404); }
        $employeeId = (int)($manager['employee_id'] ?? 0);
        if ($employeeId < 1) { $db->rollback(); fail('This manager has no linked employee record. Add or reconcile their employee record before demoting them.',409); }
        $employee = db_one($db,'SELECT id FROM employees WHERE id = ? AND company_id = ? FOR UPDATE',[$employeeId,$companyId]);
        if (!$employee) { $db->rollback(); fail('The linked employee record could not be found.',409); }
        if (in_array($manager['status'],['suspended','disabled','inactive'],true)) { $db->rollback(); fail('This manager is already inactive.',409); }
        $departmentId = (int)$manager['department_id'];
        $reports = (int)db_one($db,'SELECT COUNT(*) AS count FROM employees WHERE company_id = ? AND manager_id = ?',[$companyId,$managerId])['count'];
        $isDepartmentHead = (bool)db_one($db,'SELECT id FROM departments WHERE company_id = ? AND manager_id = ? LIMIT 1',[$companyId,$managerId]);
        $managedProjects = (int)db_one($db,'SELECT COUNT(*) AS count FROM projects WHERE company_id = ? AND (projectManager = ? OR project_manager_id = ?)',[$companyId,(string)$managerId,(string)$manager['unique_id']])['count'];
        $replacement = null;
        if ($reports > 0 || $isDepartmentHead) {
            if ($replacementId < 1 || $replacementId === $managerId) { $db->rollback(); fail('Choose a replacement manager before demoting this manager with direct reports, projects, or a department-head role.',422); }
            $replacement = db_one($db,"SELECT id,unique_id,department_id FROM managers WHERE id = ? AND company_id = ? AND status NOT IN ('suspended','disabled','inactive') FOR UPDATE",[$replacementId,$companyId]);
            if (!$replacement || ($departmentId > 0 && (int)$replacement['department_id'] !== $departmentId)) { $db->rollback(); fail('Choose an active replacement manager in the same department.',422); }
        }
        if ($replacement) {
            db_run($db,'UPDATE employees SET manager_id = ? WHERE company_id = ? AND manager_id = ?',[$replacementId,$companyId,$managerId]);
            db_run($db,'UPDATE departments SET manager_id = ? WHERE company_id = ? AND manager_id = ?',[$replacementId,$companyId,$managerId]);
            db_run($db,'UPDATE projects SET projectManager = ?, project_manager_id = ? WHERE company_id = ? AND (projectManager = ? OR project_manager_id = ?)',[$replacementId,$replacement['unique_id'],$companyId,(string)$managerId,(string)$manager['unique_id']]);
        } elseif ($managedProjects > 0) {
            db_run($db,'UPDATE projects SET projectManager = NULL, project_manager_id = NULL WHERE company_id = ? AND (projectManager = ? OR project_manager_id = ?)',[$companyId,(string)$managerId,(string)$manager['unique_id']]);
        }
        db_run($db,"UPDATE managers SET status = 'suspended' WHERE id = ? AND company_id = ?",[$managerId,$companyId]);
        $employeeRole = db_one($db,'SELECT id FROM roles WHERE company_id = ? AND role_name = ?',[$companyId,'Employee']);
        if (!$employeeRole) {
            db_run($db,'INSERT INTO roles (company_id,role_name) VALUES (?,?)',[$companyId,'Employee']);
            $employeeRole = ['id'=>(int)$db->insert_id];
        }
        db_run($db,'UPDATE employees SET role_id = ? WHERE id = ? AND company_id = ?',[$employeeRole['id'],$employeeId,$companyId]);
        db_run($db,'UPDATE staff_invitations SET used_at = UTC_TIMESTAMP() WHERE company_id = ? AND staff_role = \'manager\' AND staff_id = ? AND used_at IS NULL',[$companyId,$managerId]);
        audit_event($db,$companyId,'manager.demoted_to_employee','managers',(string)$managerId,['employee_id'=>$employeeId,'replacement_manager_id'=>$replacementId ?: null,'reports_reassigned'=>$reports,'projects_reassigned'=>$managedProjects,'department_head_reassigned'=>$isDepartmentHead]);
        $db->commit();
        return $employeeId;
    } catch (Throwable $error) { $db->rollback(); throw $error; }
}
function organization_tree(mysqli $db, int $companyId): array {
    $owner = db_one($db,'SELECT company_name, email FROM users WHERE company_id = ? ORDER BY id LIMIT 1',[$companyId]) ?? [];
    $companyName = trim((string)($owner['company_name'] ?? ''));
    if ($companyName === '') $companyName = 'Company';
    $departments = db_rows($db,'SELECT * FROM departments WHERE company_id = ? ORDER BY department_name',[$companyId]);
    $managers = db_rows($db,"SELECT m.*, d.department_name FROM managers m LEFT JOIN departments d ON d.id = m.department_id AND d.company_id = m.company_id WHERE m.company_id = ? AND m.archived_at IS NULL AND m.status NOT IN ('suspended','disabled','inactive') ORDER BY m.manager_name",[$companyId]);
    $employees = db_rows($db,'SELECT e.*, CONCAT(e.first_name, " ", e.last_name) AS full_name, d.department_name FROM employees e LEFT JOIN departments d ON d.id = e.department_id AND d.company_id = e.company_id WHERE e.company_id = ? AND e.archived_at IS NULL ORDER BY e.last_name, e.first_name',[$companyId]);
    $teams = db_rows($db,'SELECT t.id, t.team_name, t.department_id, t.team_leader_id, t.team_members, p.department AS project_department FROM teams t LEFT JOIN projects p ON p.company_id = t.company_id AND p.id = t.project_id AND p.archived_at IS NULL WHERE t.company_id = ? ORDER BY t.team_name',[$companyId]);

    $reportsByManager = [];
    $employeeDepartments = [];
    $employeeManagerAssignments = [];
    $employeesRepresentedAsManagers = [];
    foreach ($managers as $manager) if ((int)($manager['employee_id'] ?? 0) > 0) $employeesRepresentedAsManagers[(int)$manager['employee_id']] = true;
    $chartManagerIds = [];
    $managersByDepartment = [];
    foreach ($managers as $manager) {
        $managerId = (int)$manager['id'];
        $chartManagerIds[$managerId] = true;
        $departmentId = (int)($manager['department_id'] ?? 0);
        if ($departmentId > 0) $managersByDepartment[$departmentId][] = $managerId;
    }
    $defaultManagerByDepartment = [];
    foreach ($departments as $department) {
        $departmentId = (int)$department['id'];
        $headId = (int)($department['manager_id'] ?? 0);
        if ($headId > 0 && isset($chartManagerIds[$headId])) {
            $defaultManagerByDepartment[$departmentId] = $headId;
        } elseif (count($managersByDepartment[$departmentId] ?? []) === 1) {
            $defaultManagerByDepartment[$departmentId] = $managersByDepartment[$departmentId][0];
        }
    }
    foreach ($employees as $employee) {
        $employeeId = (int)$employee['id'];
        $departmentId = (int)($employee['department_id'] ?? 0);
        $employeeDepartments[$employeeId] = $departmentId;
        if (isset($employeesRepresentedAsManagers[$employeeId])) continue;
        $managerId = (int)($employee['manager_id'] ?? 0);
        if ($managerId > 0 && !isset($chartManagerIds[$managerId])) $managerId = 0;
        if ($managerId === 0) $managerId = $defaultManagerByDepartment[$departmentId] ?? 0;
        $employeeManagerAssignments[$employeeId] = $managerId;
        if ($managerId > 0) {
            $reportsByManager[$managerId][] = [
                'id' => $employeeId,
                'name' => trim((string)$employee['full_name']),
                'email' => trim((string)($employee['email'] ?? '')),
                'job_title' => trim((string)($employee['job_title'] ?? '')),
                'department_name' => trim((string)($employee['department_name'] ?? '')),
            ];
        }
    }

    $departmentMap = [];
    foreach ($departments as $department) {
        $departmentMap[(int)$department['id']] = [
            'id' => (int)$department['id'],
            'name' => trim((string)($department['department_name'] ?? '')),
            'manager_id' => (int)($department['manager_id'] ?? 0),
            'head' => null,
            'teams' => [],
            'managers' => [],
            'unassigned_employees' => [],
        ];
    }

    $departmentsByName = [];
    foreach ($departmentMap as $departmentId => $department) {
        if ($department['name'] !== '') $departmentsByName[mb_strtolower($department['name'])] = $departmentId;
    }
    $unassignedTeams = [];
    foreach ($teams as $team) {
        $departmentId = (int)($team['department_id'] ?? 0);
        if ($departmentId > 0 && !isset($departmentMap[$departmentId])) $departmentId = 0;
        $projectDepartment = trim((string)($team['project_department'] ?? ''));
        if ($departmentId === 0 && $projectDepartment !== '') {
            if (ctype_digit($projectDepartment) && isset($departmentMap[(int)$projectDepartment])) {
                $departmentId = (int)$projectDepartment;
            } else {
                $departmentId = $departmentsByName[mb_strtolower($projectDepartment)] ?? 0;
            }
        }
        if ($departmentId === 0) {
            $leaderId = (int)($team['team_leader_id'] ?? 0);
            $leaderDepartment = $employeeDepartments[$leaderId] ?? 0;
            if ($leaderDepartment > 0 && isset($departmentMap[$leaderDepartment])) $departmentId = $leaderDepartment;
        }
        if ($departmentId === 0) {
            $members = json_decode((string)($team['team_members'] ?? ''),true);
            $memberDepartments = [];
            if (is_array($members)) {
                foreach ($members as $memberId) {
                    $memberDepartment = $employeeDepartments[(int)$memberId] ?? 0;
                    if ($memberDepartment > 0 && isset($departmentMap[$memberDepartment])) $memberDepartments[$memberDepartment] = true;
                }
            }
            if (count($memberDepartments) === 1) $departmentId = (int)array_key_first($memberDepartments);
        }
        $teamNode = [
            'id' => (int)$team['id'],
            'name' => trim((string)($team['team_name'] ?? '')),
        ];
        if ($departmentId > 0) {
            $departmentMap[$departmentId]['teams'][] = $teamNode;
        } else {
            $unassignedTeams[] = $teamNode;
        }
    }

    $standaloneManagers = [];
    $managerIndex = [];
    foreach ($managers as $manager) {
        $departmentId = (int)($manager['department_id'] ?? 0);
        $node = [
            'id' => (int)$manager['id'],
            'name' => trim((string)($manager['manager_name'] ?? '')),
            'email' => trim((string)($manager['manager_email'] ?? '')),
            'department_id' => $departmentId,
            'department_name' => trim((string)($manager['department_name'] ?? '')),
            'reports' => $reportsByManager[(int)$manager['id']] ?? [],
        ];
        $managerIndex[(int)$manager['id']] = $node;
        if ($departmentId > 0 && isset($departmentMap[$departmentId])) {
            $departmentMap[$departmentId]['managers'][] = $node;
        } else {
            $standaloneManagers[] = $node;
        }
    }

    foreach ($departments as $department) {
        $departmentId = (int)$department['id'];
        $departmentHeadId = (int)($department['manager_id'] ?? 0);
        if ($departmentHeadId > 0 && isset($departmentMap[$departmentId])) {
            $head = $managerIndex[$departmentHeadId] ?? null;
            if ($head) {
                $departmentMap[$departmentId]['managers'] = array_values(array_filter($departmentMap[$departmentId]['managers'], fn($node) => (int)$node['id'] !== $departmentHeadId));
                $departmentMap[$departmentId]['head'] = $head;
            }
        }
    }

    $unassignedByDepartment = [];
    foreach ($employees as $employee) {
        if (isset($employeesRepresentedAsManagers[(int)$employee['id']]) || ($employeeManagerAssignments[(int)$employee['id']] ?? 0) > 0) continue;
        $departmentId = (int)($employee['department_id'] ?? 0);
        $person = [
            'id' => (int)$employee['id'],
            'name' => trim((string)$employee['full_name']),
            'email' => trim((string)($employee['email'] ?? '')),
            'job_title' => trim((string)($employee['job_title'] ?? '')),
        ];
        if ($departmentId > 0 && isset($departmentMap[$departmentId])) {
            $departmentMap[$departmentId]['unassigned_employees'][] = $person;
        } else {
            $unassignedByDepartment[0][] = $person;
        }
    }

    $departmentList = array_values($departmentMap);
    if ($standaloneManagers || !empty($unassignedByDepartment[0]) || $unassignedTeams) {
        $departmentList[] = ['id' => 0, 'name' => 'Unassigned', 'manager_id' => 0, 'head' => null, 'teams' => $unassignedTeams, 'managers' => $standaloneManagers, 'unassigned_employees' => $unassignedByDepartment[0] ?? []];
    }

    return [
        'company' => ['name' => $companyName],
        'owner' => ['name' => 'Business owner', 'email' => trim((string)($owner['email'] ?? ''))],
        'departments' => $departmentList,
    ];
}
function module_config(string $key): array {
    $all = modules();
    if (!isset($all[$key])) fail('This workspace tool could not be found.',404);
    return $all[$key];
}
function reference_options(mysqli $db, int $companyId, string $type): array {
    $map = [
        'departments'=>['departments','id','department_name'], 'managers'=>['managers','id','manager_name'],
        'employees'=>['employees','id',"CONCAT(first_name, ' ', last_name)"], 'projects'=>['projects','unique_id','projectName'],
        'project_ids'=>['projects','id','projectName'], 'leads'=>['leads','unique_id','lead_name'], 'customer_codes'=>['customers','customer_id',"CONCAT(first_name, ' ', last_name)"], 'customer_ids'=>['customers','id',"CONCAT(first_name, ' ', last_name)"], 'accounts'=>['accounts','unique_id','account_owner'],
    ];
    if (!isset($map[$type])) throw new LogicException('Unknown reference type');
    [$table,$key,$label] = $map[$type];
    $options = [];
    $activeManagers = $table === 'managers' ? " AND status NOT IN ('suspended','disabled','inactive')" : '';
    $activeArchive = in_array($table,['employees','managers','projects'],true) ? ' AND archived_at IS NULL' : '';
    foreach (db_rows($db,"SELECT `$key` AS value, $label AS label FROM `$table` WHERE company_id = ? AND `$key` IS NOT NULL AND `$key` != ''$activeManagers$activeArchive ORDER BY label",[$companyId]) as $row) $options[(string)$row['value']] = $row['label'];
    return $options;
}
function validate_record(mysqli $db, int $companyId, array $module, array $posted): array {
    $posted = normalize_record_input($db,$companyId,$module,$posted);
    $values = []; $errors = [];
    foreach ($module['fields'] as $name=>$field) {
        $value = is_string($posted[$name] ?? null) ? trim($posted[$name]) : '';
        $type = $field['type'];
        if (str_starts_with($type,'multi:')) {
            $ids = $posted[$name] ?? [];
            if (!is_array($ids)) { $errors[$name] = 'Choose valid members.'; continue; }
            $options = reference_options($db,$companyId,substr($type,6));
            $selected = [];
            foreach ($ids as $id) {
                if (!is_scalar($id) || !array_key_exists((string)$id,$options)) { $errors[$name] = 'Choose members from your workspace.'; break; }
                $selected[] = (string)$id;
            }
            $values[$name] = json_encode(array_values(array_unique($selected))); continue;
        }
        if ($type === 'social-platforms') {
            $chosen = normalize_social_platform_targets($posted[$name] ?? []);
            if ($posted[$name] ?? null) {
                $allowed = array_keys(social_platform_options());
                foreach ($chosen as $platform) {
                    if (!in_array($platform,$allowed,true)) { $errors[$name] = 'Choose only supported social channels.'; break; }
                }
            }
            $values[$name] = implode(',', $chosen);
            continue;
        }
        if ($type === 'textarea') $value = rich_text_clean($value);
        if ($value === '') {
            if ($field['required']) $errors[$name] = 'Enter ' . strtolower($field['label']) . '.';
            $values[$name] = in_array($type,['date','money'],true) || str_starts_with($type,'ref:') || ($type === 'email' && !$field['required']) ? null : '';
            // Legacy NOT NULL columns represent an unassigned relationship with an empty value.
            if ($name === 'assignee' || ($module['table'] === 'contacts' && in_array($name,['lead_id','customer_id'],true))) $values[$name] = '';
            if ($name === 'manager_id' && $module['table'] === 'employees') $values[$name] = 0;
            if ($name === 'milestoneStatus') $values[$name] = 'not started';
            continue;
        }
        if (mb_strlen($value) > $field['max'] && !in_array($type,['money','integer'],true)) $errors[$name] = 'Use at most ' . $field['max'] . ' characters.';
        if ($type === 'email' && !filter_var($value,FILTER_VALIDATE_EMAIL)) $errors[$name] = 'Enter a valid email address.';
        if ($type === 'email') $value = strtolower($value);
        if ($type === 'url' && (!filter_var($value,FILTER_VALIDATE_URL) || parse_url($value,PHP_URL_SCHEME) !== 'https' || parse_url($value,PHP_URL_USER) !== null || parse_url($value,PHP_URL_PASS) !== null)) $errors[$name] = 'Enter an HTTPS link without sign-in details.';
        if ($type === 'datetime-local') {
            $value = str_replace('T',' ',$value);
            if (strlen($value) === 16) $value .= ':00';
            $zone = is_string($posted['timezone'] ?? null) && in_array($posted['timezone'],DateTimeZone::listIdentifiers(),true) ? $posted['timezone'] : 'UTC';
            $date = DateTimeImmutable::createFromFormat('!Y-m-d H:i:s',$value,new DateTimeZone($zone));
            if (!$date || $date->format('Y-m-d H:i:s') !== $value || $value < '1900-01-01' || $value > '2200-12-31 23:59:59') $errors[$name] = 'Enter a valid date and time in the selected timezone.';
        }
        if ($type === 'date') {
            $date = DateTimeImmutable::createFromFormat('!Y-m-d',$value);
            if (!$date || $date->format('Y-m-d') !== $value || $value < '1900-01-01' || $value > '2200-12-31') $errors[$name] = 'Enter a valid date between 1900 and 2200.';
        }
        if ($type === 'money' && !preg_match('/^\d{1,8}(?:\.\d{1,2})?$/D',$value)) $errors[$name] = 'Enter a non-negative amount with up to two decimal places.';
        if ($type === 'integer' && (!ctype_digit($value) || strlen($value)>9)) $errors[$name] = 'Enter a whole number between 0 and 999,999,999.';
        if ($type === 'select' && !in_array($value,$field['options'],true)) $errors[$name] = 'Choose one of the available options.';
        if (str_starts_with($type,'ref:') && !array_key_exists($value,reference_options($db,$companyId,substr($type,4)))) $errors[$name] = 'Choose a record from your workspace.';
        $values[$name] = $value;
    }
    foreach ([['startDate','endDate'],['start_date','end_date'],['invoice_date','due_date']] as [$start,$end]) if (!empty($values[$start]) && !empty($values[$end]) && $values[$end] < $values[$start]) $errors[$end] = 'The end date cannot be before the start date.';
    if ($module['table'] === 'job_posts') {
        $values['closes_at'] = null;
        if (!empty($values['closing_date']) && !isset($errors['closing_date']) && !isset($errors['timezone']) && in_array($values['timezone'] ?? '',DateTimeZone::listIdentifiers(),true)) {
            $end = new DateTimeImmutable($values['closing_date'] . ' 23:59:59',new DateTimeZone($values['timezone']));
            $values['closes_at'] = $end->setTimezone(new DateTimeZone('UTC'))->format('Y-m-d H:i:s');
            if (($values['status'] ?? '') === 'published' && $values['closes_at'] <= gmdate('Y-m-d H:i:s')) $errors['closing_date'] = 'Choose a future closing date before publishing.';
        }
    if (($values['status'] ?? '') === 'published' && trim((string)($values['social_targets'] ?? '')) !== '') {
        $targets = normalize_social_platform_targets($values['social_targets']);
        foreach ($targets as $platform) {
            if (!job_social_sync_ready($platform)) {
                $errors['social_targets'] = 'Connect the ' . ucfirst($platform) . ' credentials before publishing to that social channel.';
                break;
            }
        }
    }
    }
    if ($module['table'] === 'employees' && array_key_exists('github_login',$values)) {
        // GitHub usernames link synced issues to people; stored without a leading @.
        $login = ltrim(trim((string)$values['github_login']),'@');
        $values['github_login'] = $login !== '' ? $login : null;
        if ($login !== '' && !preg_match('/^[A-Za-z0-9](?:[A-Za-z0-9-]{0,38})$/D',$login)) $errors['github_login'] = 'Enter a GitHub username: letters, numbers and single hyphens.';
    }
    if ($module['table'] === 'events') {
        if (($values['end_datetime'] ?? '') <= ($values['start_datetime'] ?? '')) $errors['end_datetime'] = 'The end time must be after the start time.';
        $meetingProvider = trim((string)($values['meeting_provider'] ?? ''));
        if ($meetingProvider === '' && !empty($values['meet_link'])) $meetingProvider = normalize_event_meeting_provider($values);
        if (($values['event_type'] ?? '') === 'online') {
            if ($meetingProvider === 'simplr') {
                $values['meet_link'] = trim((string)($values['meet_link'] ?? '')) !== '' ? trim((string)$values['meet_link']) : '';
            } elseif (empty($values['meet_link'])) {
                $errors['meet_link'] = 'Add the HTTPS meeting link for an online event.';
            }
            if (!empty($values['meet_link']) && (!filter_var($values['meet_link'], FILTER_VALIDATE_URL) || parse_url($values['meet_link'], PHP_URL_SCHEME) !== 'https' || parse_url($values['meet_link'], PHP_URL_USER) !== null || parse_url($values['meet_link'], PHP_URL_PASS) !== null)) {
                $errors['meet_link'] = 'Use a valid HTTPS meeting link without sign-in details.';
            }
        }
        if (($values['event_type'] ?? '') === 'in-person' && empty($values['meetup_venue'])) $errors['meetup_venue'] = 'Add a venue for an in-person event.';
        if (!isset($errors['start_datetime'])) $values['event_date'] = substr($values['start_datetime'],0,10);
    }
    return [$values,$errors];
}
function normalize_record_input(mysqli $db, int $companyId, array $module, array $record): array {
    if ($module['table'] === 'tasks') {
        $project = is_string($record['project_id'] ?? null) ? $record['project_id'] : '';
        if ($project !== '' && ctype_digit($project) && !db_one($db,'SELECT id FROM projects WHERE unique_id = ? AND company_id = ?',[$project,$companyId])) {
            $found = db_one($db,'SELECT unique_id FROM projects WHERE id = ? AND company_id = ?',[$project,$companyId]);
            if ($found && $found['unique_id']) $record['project_id'] = $found['unique_id'];
        }
        $assignee = is_string($record['assignee'] ?? null) ? $record['assignee'] : '';
        if ($assignee !== '' && !ctype_digit($assignee)) {
            $found = db_one($db,'SELECT id FROM employees WHERE unique_id = ? AND company_id = ?',[$assignee,$companyId]);
            if ($found) $record['assignee'] = (string)$found['id'];
        }
    }
    if ($module['table'] === 'projects' && empty($record['projectManager']) && !empty($record['project_manager_id'])) {
        $found = db_one($db,'SELECT id FROM managers WHERE unique_id = ? AND company_id = ?',[$record['project_manager_id'],$companyId]);
        if ($found) $record['projectManager'] = (string)$found['id'];
    }
    if ($module['table'] === 'events') {
        $record['meeting_provider'] = normalize_event_meeting_provider($record);
        if (($record['event_type'] ?? '') === 'online' && empty($record['meet_link']) && ($record['meeting_provider'] ?? '') === 'simplr') {
            $record['meet_link'] = build_simplr_meeting_link((int)$companyId, (string)($record['event_name'] ?? 'Simplr meeting'), (int)($record['event_id'] ?? 0));
        }
    }
    return $record;
}
function record_version(array $record): string { return hash('sha256',json_encode($record,JSON_INVALID_UTF8_SUBSTITUTE)); }
function save_record(mysqli $db, int $companyId, string $key, array $values, ?int $id, string $version = ''): int {
    $module = module_config($key); $table = $module['table']; $pk = $module['pk'];
    $db->begin_transaction();
    try {
        // Recheck a grant under a lock so a revoked or downgraded permission
        // cannot be reused by a form that was opened earlier.
        if (PHP_SAPI !== 'cli') {
            $actor = workspace_user($db);
            if ((int)$actor['company_id'] !== $companyId) { $db->rollback(); fail('Choose your own workspace.',403); }
            require_module_access($db,$actor,$key,true,true);
        }
        if ($key === 'projects') {
            $manager = !empty($values['projectManager']) ? db_one($db,'SELECT unique_id FROM managers WHERE id = ? AND company_id = ?',[$values['projectManager'],$companyId]) : null;
            // Keep both historical manager representations in sync on save.
            $values['project_manager_id'] = $manager['unique_id'] ?? null;
        }
        if ($key === 'events') {
            $provider = trim((string)($values['meeting_provider'] ?? ''));
            $link = trim((string)($values['meet_link'] ?? ''));
            if (($values['event_type'] ?? '') === 'in-person') {
                $values['meet_link'] = '';
                $provider = '';
            } elseif (($values['event_type'] ?? '') === 'online') {
                if ($provider === 'simplr') {
                    $values['meet_link'] = $link !== '' ? $link : build_simplr_meeting_link($companyId, (string)($values['event_name'] ?? 'Simplr meeting'), $id);
                } elseif ($provider === '' && $link !== '') {
                    $values['meet_link'] = $link;
                }
                if ($provider === '') {
                    $values['meeting_provider'] = normalize_event_meeting_provider($values);
                } else {
                    $values['meeting_provider'] = $provider;
                }
                if (($values['meeting_provider'] ?? '') === 'simplr' && $values['meet_link'] === '') {
                    $values['meet_link'] = build_simplr_meeting_link($companyId, (string)($values['event_name'] ?? 'Simplr meeting'), $id);
                }
            }
            unset($values['meeting_provider']);
        }
        $webhookAction = $id !== null ? 'updated' : 'created';
        if ($id !== null) {
            $current = db_one($db,"SELECT * FROM `$table` WHERE `$pk` = ? AND company_id = ? FOR UPDATE",[$id,$companyId]);
            if (!$current || !empty($current['archived_at'])) { $db->rollback(); fail('This record could not be found in your active workspace.',404); }
            if ($key === 'applications' && db_one($db,'SELECT application_id FROM application_submissions WHERE application_id = ? AND company_id = ?',[$id,$companyId])) { $db->rollback(); fail('Use the application review page to update a submitted application. Applicant details cannot be overwritten.',409); }
            if (!hash_equals(record_version($current),$version)) { $db->rollback(); fail('This record changed while you were editing it. Reload the page and review the latest version before saving.',409); }
            if ($key === 'jobs' && $values['status'] === 'published' && !$current['published_at']) $values['published_at'] = gmdate('Y-m-d H:i:s');
            $assignments = implode(', ',array_map(fn($column)=>"`$column` = ?",array_keys($values)));
            db_run($db,"UPDATE `$table` SET $assignments WHERE `$pk` = ? AND company_id = ?",[...array_values($values),$id,$companyId]);
            webhook_queue_record($db,$companyId,$key,$id,$webhookAction,$values);
            if ($key === 'jobs' && ($values['status'] ?? 'draft') === 'published' && !empty($values['social_targets'] ?? '')) queue_social_sync($db,$companyId,$id,$values['social_targets']);
        } else {
            $values['company_id'] = $companyId;
            if ($key === 'jobs') { $values['public_id'] = bin2hex(random_bytes(16)); $values['published_at'] = $values['status'] === 'published' ? gmdate('Y-m-d H:i:s') : null; }
            if (in_array($key,['projects','leads','contacts','employees','managers','accounts'],true)) $values['unique_id'] = substr($key,0,3) . '_' . bin2hex(random_bytes(10));
            if ($key === 'customers') $values['customer_id'] = 'cus_' . bin2hex(random_bytes(8));
            if (in_array($key,['employees','managers'],true)) {
                $roleName = $key === 'employees' ? 'Employee' : 'Manager';
                $role = db_one($db,'SELECT id FROM roles WHERE company_id = ? AND role_name = ? LIMIT 1',[$companyId,$roleName]);
                if (!$role) { db_run($db,'INSERT INTO roles (company_id, role_name) VALUES (?, ?)',[$companyId,$roleName]); $role = ['id'=>$db->insert_id]; }
                $values['role_id'] = $role['id'];
            }
            $columns = implode('`, `',array_keys($values)); $marks = implode(',',array_fill(0,count($values),'?'));
            db_run($db,"INSERT INTO `$table` (`$columns`) VALUES ($marks)",array_values($values));
            $id = (int)$db->insert_id;
            webhook_queue_record($db,$companyId,$key,$id,$webhookAction,$values);
            if ($key === 'jobs' && ($values['status'] ?? '') === 'published' && !empty($values['social_targets'] ?? '')) queue_social_sync($db,$companyId,$id,$values['social_targets']);
        }
        if (PHP_SAPI !== 'cli') audit_event($db,$companyId,'record.saved',$key,(string)$id);
        $db->commit(); return $id;
    } catch (Throwable $error) { $db->rollback(); throw $error; }
}
// The select field that describes where a record is in its workflow.
function module_stage_field(string $key): ?string {
    return ['tasks'=>'status','projects'=>'status','deals'=>'stage','leads'=>'status','products'=>'stage','objectives'=>'status','incidents'=>'status','compliance'=>'status','jobs'=>'status','invoices'=>'status','accounts'=>'status','applications'=>'status','teams'=>'team_status'][$key] ?? null;
}
// Boards change only the stage field; submitted applications and job publishing keep their own review flows.
function module_has_board(string $key): bool {
    return in_array($key, ['tasks','projects','deals','leads','products','objectives','incidents','compliance'], true);
}
function stage_class(?string $value): string { return preg_replace('/[^a-z]/','',strtolower((string)$value)); }
function stage_is_final(?string $value): bool {
    return in_array(stage_class($value),['completed','won','lost','launched','resolved','reviewed','closed','hired','paid','cancelled','inactive'],true);
}
// Deadline columns are flagged when the record has not reached a final stage.
function record_cell_html(array $module, string $column, array $row, string $currency, ?string $stageField): string {
    $value = $row[$column] ?? null;
    $type = $module['fields'][$column]['type'] ?? '';
    if ($value === null || $value === '') return '<span class="subtle">—</span>';
    if (in_array($column,['status','stage','priority','severity','team_status'],true)) return '<span class="badge ' . e(stage_class($value)) . '">' . e($value) . '</span>';
    if ($type === 'textarea') return e(mb_strimwidth(preg_replace('/\s+/u',' ',rich_text_plain((string)$value)) ?? '',0,120,'…'));
    if ($type === 'money') return '<span class="money">' . ($currency !== '' ? '<small>' . e($currency) . '</small> ' : '') . e(number_format((float)$value,2)) . '</span>';
    if ($type === 'date' || preg_match('/^\d{4}-\d{2}-\d{2}( \d{2}:\d{2}:\d{2})?$/D',(string)$value)) {
        $time = strtotime((string)$value);
        if ($time === false) return e($value);
        $deadline = in_array($column,['due_date','endDate','end_date','target_date','milestoneDate','closing_date'],true);
        $overdue = $deadline && date('Y-m-d',$time) < date('Y-m-d') && !stage_is_final($stageField ? ($row[$stageField] ?? '') : '');
        $label = date(strlen((string)$value) > 10 ? 'j M Y, H:i' : 'j M Y',$time);
        return '<time datetime="' . e($value) . '"' . ($overdue ? ' class="overdue" title="Overdue"' : '') . '>' . e($label) . '</time>' . ($overdue ? ' <span class="badge overdue">Overdue</span>' : '');
    }
    return e($value);
}
function project_task_progress(mysqli $db, int $companyId): array {
    $progress = [];
    foreach (db_rows($db,'SELECT p.id, COUNT(t.id) AS total, SUM(t.status = \'completed\') AS done FROM projects p JOIN tasks t ON ' . task_project_join() . ' AND t.archived_at IS NULL WHERE p.company_id = ? AND p.archived_at IS NULL GROUP BY p.id',[$companyId]) as $row) $progress[(int)$row['id']] = ['total'=>(int)$row['total'],'done'=>(int)$row['done']];
    return $progress;
}
function progress_html(?array $progress): string {
    if (!$progress || $progress['total'] === 0) return '<span class="subtle">No tasks yet</span>';
    $percent = (int)round($progress['done'] / $progress['total'] * 100);
    return '<span class="progress" title="' . $progress['done'] . ' of ' . $progress['total'] . ' tasks complete"><span class="progress-track"><span style="width:' . $percent . '%"></span></span><small>' . $percent . '%</small></span>';
}
function record_title(array $module, array $row): string {
    if (isset($module['fields']['first_name'], $module['fields']['last_name'])) return trim(($row['first_name'] ?? '') . ' ' . ($row['last_name'] ?? ''));
    $first = $module['columns'][0];
    $title = ($module['fields'][$first]['type'] ?? '') === 'textarea' ? mb_strimwidth(preg_replace('/\s+/u',' ',rich_text_plain($row[$first] ?? '')),0,90,'…') : (string)($row[$first] ?? '');
    return trim($title) ?: ucfirst($module['singular']) . ' #' . ($row[$module['pk']] ?? '');
}
function move_record_stage(mysqli $db, int $companyId, string $key, int $id, string $stage, string $version): string {
    $module = module_config($key); $field = module_stage_field($key);
    if (!module_has_board($key) || $field === null) fail('Records in this tool cannot be moved between stages.',422);
    if (!in_array($stage, $module['fields'][$field]['options'], true)) fail('Choose one of the available stages.',422);
    $current = db_one($db,"SELECT * FROM `{$module['table']}` WHERE `{$module['pk']}` = ? AND company_id = ?",[$id,$companyId]);
    if (!$current || !empty($current['archived_at'])) fail('This record could not be found in your active workspace.',404);
    // Revalidate the whole record so a stage change cannot bypass the normal form rules.
    $posted = array_map(fn($value) => is_scalar($value) ? (string)$value : $value, $current);
    foreach ($module['fields'] as $name=>$definition) if (str_starts_with($definition['type'],'multi:')) $posted[$name] = json_decode((string)($current[$name] ?? '') ?: '[]', true) ?: [];
    $posted[$field] = $stage;
    [$values,$errors] = validate_record($db,$companyId,$module,$posted);
    if ($errors) json_response(['ok'=>false,'error'=>'Open this ' . $module['singular'] . ' and complete its details before moving it.','fields'=>$errors],422);
    save_record($db,$companyId,$key,$values,$id,$version);
    return record_version(db_one($db,"SELECT * FROM `{$module['table']}` WHERE `{$module['pk']}` = ? AND company_id = ?",[$id,$companyId]));
}
