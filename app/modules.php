<?php
declare(strict_types=1);
require_once __DIR__ . '/access.php';
// This allowlist is shared by the HTML workspace and its record API. SQL identifiers
// are never accepted from a request. Existing tables remain the source of truth.
function field(string $label, string $type = 'text', bool $required = true, int $max = 255, array $options = []): array {
    return compact('label','type','required','max','options');
}
function modules(): array {
    return [
        'jobs' => ['title'=>'Job postings','singular'=>'job posting','nav'=>'recruiting','icon'=>'briefcase','description'=>'Publish open roles and receive applications in your workspace.','table'=>'job_posts','pk'=>'id','fields'=>[
            'title'=>field('Job title','text',true,150),'location'=>field('Location','text',true,160),'workplace'=>field('Work arrangement','select',true,20,['remote','hybrid','on-site']),'employment_type'=>field('Employment type','select',true,30,['full-time','part-time','contract','internship']),'description'=>field('Role description','textarea',true,12000),'requirements'=>field('Skills and requirements','textarea',false,8000),'closing_date'=>field('Applications close on','date',false),'timezone'=>field('Closing-date timezone','select',true,64,DateTimeZone::listIdentifiers()),'status'=>field('Publication status','select',true,20,['draft','published','closed']),
        ],'columns'=>['title','location','workplace','employment_type','status','closing_date']],
        'events' => ['title'=>'Events & meetings','singular'=>'event','nav'=>'calendar','icon'=>'calendar','description'=>'Plan meetings and events with a clear time, place, and agenda.','table'=>'events','pk'=>'event_id','fields'=>[
            'event_name'=>field('Event name'),'start_datetime'=>field('Start time','datetime-local'),'end_datetime'=>field('End time','datetime-local'),'timezone'=>field('Timezone','select',true,64,DateTimeZone::listIdentifiers()),'event_type'=>field('Meeting type','select',true,100,['online','in-person']),'meetup_venue'=>field('Venue','text',false),'meet_link'=>field('Meeting link','url',false),'agenda'=>field('Agenda','textarea',false,10000),'event_description'=>field('Description','textarea',false,10000),'organizer'=>field('Organizer','text',false),
        ],'columns'=>['event_name','start_datetime','end_datetime','timezone','event_type']],
        'projects' => ['title'=>'Projects','singular'=>'project','nav'=>'projects','icon'=>'folder','description'=>'Give your next idea a plan, an owner, and a clear path forward.','table'=>'projects','pk'=>'id','fields'=>[
            'projectName'=>field('Project name'), 'projectDescription'=>field('Description','textarea',false,10000), 'startDate'=>field('Start date','date'), 'endDate'=>field('End date','date'), 'status'=>field('Status','select',true,50,['pending','ongoing','completed','on hold']), 'department'=>field('Department','ref:departments',false,50), 'projectManager'=>field('Project manager','ref:managers',false),
        ],'columns'=>['projectName','status','startDate','endDate']],
        'tasks' => ['title'=>'Tasks','singular'=>'task','nav'=>'tasks','icon'=>'task','description'=>'Know what needs doing, what matters most, and what comes next.','table'=>'tasks','pk'=>'id','fields'=>[
            'title'=>field('Task name'),'project_id'=>field('Project','ref:projects'), 'description'=>field('Description','textarea',false,10000),'due_date'=>field('Due date','date'),'priority'=>field('Priority','select',true,50,['low','medium','high']),'status'=>field('Status','select',true,50,['pending','ongoing','completed']),'assignee'=>field('Assigned to','ref:employees',false),
        ],'columns'=>['title','due_date','priority','status']],
        'departments' => ['title'=>'Departments','singular'=>'department','nav'=>'people','icon'=>'users','description'=>'Build a clear structure for the people behind your business.','table'=>'departments','pk'=>'id','fields'=>['department_name'=>field('Department name','text',true,100),'location'=>field('Location','text',false)],'columns'=>['department_name','location','created_at']],
        'managers' => ['title'=>'Managers','singular'=>'manager','nav'=>'people','icon'=>'users','description'=>'Keep your management directory organized by department.','table'=>'managers','pk'=>'id','fields'=>['manager_name'=>field('Full name'),'manager_email'=>field('Email','email'),'department_id'=>field('Department','ref:departments')],'columns'=>['manager_name','manager_email','status']],
        'employees' => ['title'=>'Employees','singular'=>'employee','nav'=>'people','icon'=>'users','description'=>'A shared directory of your people and their roles.','table'=>'employees','pk'=>'id','fields'=>['first_name'=>field('First name','text',true,50),'last_name'=>field('Last name','text',true,50),'email'=>field('Email','email',true,100),'phone'=>field('Phone','tel',false,20),'hire_date'=>field('Hire date','date'),'job_title'=>field('Job title','text',false,100),'department_id'=>field('Department','ref:departments'),'manager_id'=>field('Manager','ref:managers',false)],'columns'=>['first_name','last_name','email','job_title']],
        'teams' => ['title'=>'Teams','singular'=>'team','nav'=>'people','icon'=>'users','description'=>'Bring the right people together around a shared project.','table'=>'teams','pk'=>'id','fields'=>['team_name'=>field('Team name'),'team_description'=>field('Description','textarea',false,10000),'project_id'=>field('Project','ref:project_ids',false),'team_leader_id'=>field('Team lead','ref:employees',false),'team_members'=>field('Members','multi:employees',false),'team_status'=>field('Status','select',true,50,['active','inactive'])],'columns'=>['team_name','team_status','created_at']],
        'leads' => ['title'=>'Leads','singular'=>'lead','nav'=>'sales','icon'=>'chart','description'=>'Turn first conversations into lasting customer relationships.','table'=>'leads','pk'=>'lead_id','fields'=>['lead_name'=>field('Lead name'),'lead_email'=>field('Email','email',false),'lead_phone'=>field('Phone','tel',false,20),'lead_source'=>field('Source','select',true,50,['Website','Social-Media','Email','Referral']),'status'=>field('Status','select',true,50,['new','contacted','qualified','lost'])],'columns'=>['lead_name','lead_email','lead_source','status']],
        'customers' => ['title'=>'Customers','singular'=>'customer','nav'=>'sales','icon'=>'users','description'=>'Keep the details that help you look after your customers.','table'=>'customers','pk'=>'id','fields'=>['first_name'=>field('First name','text',true,50),'last_name'=>field('Last name','text',true,50),'business_name'=>field('Business name'),'customer_email'=>field('Email','email',true,100),'phone_number'=>field('Phone','tel',false,20),'address'=>field('Address','text',false),'acquired_item'=>field('Product or service','text',false)],'columns'=>['first_name','last_name','business_name','customer_email']],
        'contacts' => ['title'=>'Contacts','singular'=>'contact','nav'=>'sales','icon'=>'users','description'=>'Keep your business conversations connected.','table'=>'contacts','pk'=>'contact_id','fields'=>['contact_name'=>field('Full name'),'contact_email'=>field('Email','email',false),'contact_phone'=>field('Phone','tel',false,20),'lead_id'=>field('Lead','ref:leads',false,50),'customer_id'=>field('Customer','ref:customer_codes',false,50)],'columns'=>['contact_name','contact_email','contact_phone']],
        'accounts' => ['title'=>'Customer accounts','singular'=>'account','nav'=>'sales','icon'=>'briefcase','description'=>'Organize the businesses and people you work with.','table'=>'accounts','pk'=>'account_id','fields'=>['account_owner'=>field('Account owner'),'account_email'=>field('Email','email',false),'account_phone'=>field('Phone','tel',false,20),'customer_id'=>field('Customer','ref:customer_codes'),'status'=>field('Status','select',true,50,['active','inactive'])],'columns'=>['account_owner','account_email','status']],
        'deals' => ['title'=>'Deals','singular'=>'deal','nav'=>'sales','icon'=>'chart','description'=>'Follow your opportunities from proposal to outcome.','table'=>'deals','pk'=>'deal_id','fields'=>['deal_with'=>field('Deal name'),'lead_id'=>field('Lead','ref:leads',false),'account_id'=>field('Account','ref:accounts',false),'amount'=>field('Amount','money'),'stage'=>field('Stage','select',true,50,['negotiation','proposal','won','lost'])],'columns'=>['deal_with','amount','stage','created_at']],
        'invoices' => ['title'=>'Invoice register','singular'=>'invoice record','nav'=>'finance','icon'=>'wallet','description'=>'Track invoice dates, amounts, and payment status.','table'=>'invoices','pk'=>'id','fields'=>['customer_id'=>field('Customer','ref:customer_ids'),'invoice_date'=>field('Invoice date','date'),'due_date'=>field('Due date','date'),'amount'=>field('Amount','money'),'status'=>field('Status','select',true,50,['Paid','Unpaid','Cancelled'])],'columns'=>['id','invoice_date','due_date','amount','status']],
        'expenses' => ['title'=>'Expenses','singular'=>'expense','nav'=>'finance','icon'=>'wallet','description'=>'Keep an organized record of what your business spends.','table'=>'expenses','pk'=>'id','fields'=>['description'=>field('Description'),'date'=>field('Expense date','date'),'amount'=>field('Amount','money'),'category'=>field('Category','text',true,50)],'columns'=>['description','date','category','amount']],
        'ledger' => ['title'=>'Ledger','singular'=>'entry','nav'=>'finance','icon'=>'wallet','description'=>'Record income and expenses in your business ledger.','table'=>'ledger_entries','pk'=>'id','fields'=>['description'=>field('Description'),'date'=>field('Entry date','date'),'amount'=>field('Amount','money'),'type'=>field('Entry type','select',true,50,['income','expense']),'category'=>field('Category','text',true,50)],'columns'=>['description','date','type','amount']],
        'inventory' => ['title'=>'Inventory','singular'=>'item','nav'=>'finance','icon'=>'box','description'=>'Stay on top of the items your business keeps in stock.','table'=>'inventory_items','pk'=>'id','fields'=>['name'=>field('Item name','text',true,100),'description'=>field('Description','textarea',false,10000),'quantity'=>field('Quantity','integer'),'unit_price'=>field('Unit price','money'),'category'=>field('Category','text',true,50)],'columns'=>['name','quantity','unit_price','category']],
        'applications' => ['title'=>'Applications','singular'=>'application','nav'=>'recruiting','icon'=>'briefcase','description'=>'Keep track of applicants and the next step in your hiring process.','table'=>'applications','pk'=>'id','fields'=>['applicant_name'=>field('Applicant name','text',true,150),'email'=>field('Email','email',true,100),'phone'=>field('Phone','tel',false,20),'position_applied_for'=>field('Position','text',true,150),'status'=>field('Stage','select',true,50,['new','reviewing','interview','offer','hired','closed'])],'columns'=>['applicant_name','email','position_applied_for','status']],
        'incidents' => ['title'=>'Security incidents','singular'=>'incident','nav'=>'modules','icon'=>'shield','description'=>'Record security issues, assign a severity, and track follow-up.','table'=>'cyber_incidents','pk'=>'id','fields'=>['description'=>field('Description','textarea',true,10000),'severity'=>field('Severity','select',true,50,['low','medium','high','critical']),'status'=>field('Status','select',true,50,['open','investigating','resolved'])],'columns'=>['description','severity','status','reported_at']],
        'compliance' => ['title'=>'Compliance register','singular'=>'requirement','nav'=>'modules','icon'=>'shield','description'=>'Track requirements and their review status in one place.','table'=>'cyber_compliance','pk'=>'id','fields'=>['regulation'=>field('Requirement'),'status'=>field('Review status','select',true,50,['not reviewed','in review','action required','reviewed'])],'columns'=>['regulation','status','checked_at']],
        'products' => ['title'=>'Product development','singular'=>'product','nav'=>'modules','icon'=>'box','description'=>'Follow your products from the first idea to the next milestone.','table'=>'productdevelopment','pk'=>'id','fields'=>['product_name'=>field('Product name','text',true,100),'stage'=>field('Stage','select',true,100,['discovery','design','development','testing','launched']),'start_date'=>field('Start date','date'),'end_date'=>field('Target date','date'),'status'=>field('Status','select',true,50,['planned','ongoing','completed','on hold'])],'columns'=>['product_name','stage','status','end_date']],
        'assets' => ['title'=>'Office assets','singular'=>'asset','nav'=>'modules','icon'=>'briefcase','description'=>'Keep an inventory of the equipment that supports your business.','table'=>'assets','pk'=>'id','fields'=>['asset_name'=>field('Asset name','text',true,100),'description'=>field('Description','text',false),'purchase_date'=>field('Purchase date','date',false),'value'=>field('Purchase value','money',false)],'columns'=>['asset_name','description','purchase_date','value']],
        'objectives' => ['title'=>'Business strategy','singular'=>'objective','nav'=>'modules','icon'=>'chart','description'=>'Connect your priorities to clear objectives and target dates.','table'=>'business_objectives','pk'=>'id','fields'=>['title'=>field('Objective'),'description'=>field('Description','textarea',false,10000),'target_date'=>field('Target date','date'),'status'=>field('Status','select',true,50,['planned','ongoing','completed','on hold']),'measure'=>field('Success measure','text',false)],'columns'=>['title','target_date','status','measure']],
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
    foreach (db_rows($db,"SELECT `$key` AS value, $label AS label FROM `$table` WHERE company_id = ? AND `$key` IS NOT NULL AND `$key` != '' ORDER BY label",[$companyId]) as $row) $options[(string)$row['value']] = $row['label'];
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
        if ($value === '') {
            if ($field['required']) $errors[$name] = 'Enter ' . strtolower($field['label']) . '.';
            $values[$name] = in_array($type,['date','money'],true) || str_starts_with($type,'ref:') || ($type === 'email' && !$field['required']) ? null : '';
            // Legacy NOT NULL columns represent an unassigned relationship with an empty value.
            if ($name === 'assignee' || ($module['table'] === 'contacts' && in_array($name,['lead_id','customer_id'],true))) $values[$name] = '';
            if ($name === 'manager_id' && $module['table'] === 'employees') $values[$name] = 0;
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
    }
    if ($module['table'] === 'events') {
        if (($values['end_datetime'] ?? '') <= ($values['start_datetime'] ?? '')) $errors['end_datetime'] = 'The end time must be after the start time.';
        if (($values['event_type'] ?? '') === 'online' && empty($values['meet_link'])) $errors['meet_link'] = 'Add the HTTPS meeting link for an online event.';
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
        if ($id !== null) {
            $current = db_one($db,"SELECT * FROM `$table` WHERE `$pk` = ? AND company_id = ? FOR UPDATE",[$id,$companyId]);
            if (!$current) { $db->rollback(); fail('This record could not be found in your workspace.',404); }
            if ($key === 'applications' && db_one($db,'SELECT application_id FROM application_submissions WHERE application_id = ? AND company_id = ?',[$id,$companyId])) { $db->rollback(); fail('Use the application review page to update a submitted application. Applicant details cannot be overwritten.',409); }
            if (!hash_equals(record_version($current),$version)) { $db->rollback(); fail('This record changed while you were editing it. Reload the page and review the latest version before saving.',409); }
            if ($key === 'jobs' && $values['status'] === 'published' && !$current['published_at']) $values['published_at'] = gmdate('Y-m-d H:i:s');
            $assignments = implode(', ',array_map(fn($column)=>"`$column` = ?",array_keys($values)));
            db_run($db,"UPDATE `$table` SET $assignments WHERE `$pk` = ? AND company_id = ?",[...array_values($values),$id,$companyId]);
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
        }
        if (PHP_SAPI !== 'cli') audit_event($db,$companyId,'record.saved',$key,(string)$id);
        $db->commit(); return $id;
    } catch (Throwable $error) { $db->rollback(); throw $error; }
}
