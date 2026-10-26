<?php
declare(strict_types=1);
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/modules.php';

// Compatibility entry point for older form URLs. New forms use workspace.php.
function legacy_create(string $moduleKey, array $aliases = []): never {
    require_post(); $db = database(); $user = workspace_user($db);
    require_module_access($db,$user,$moduleKey,true);
    foreach ($_FILES as $file) {
        $errors = (array)($file['error'] ?? []);
        if (array_filter($errors,fn($error)=>$error !== UPLOAD_ERR_NO_FILE)) fail('Attachments are not available in this form. Save the record without an attachment.',422);
    }
    $posted = $_POST;
    foreach ($aliases as $old=>$new) if (isset($posted[$old])) $posted[$new] = $posted[$old];
    if (isset($posted['status']) && is_string($posted['status'])) $posted['status'] = strtolower($posted['status']);
    if (isset($posted['priority']) && is_string($posted['priority'])) $posted['priority'] = strtolower($posted['priority']);
    if ($moduleKey === 'tasks') {
        $posted['status'] ??= 'pending';
        if (is_scalar($posted['project_id'] ?? null) && ctype_digit((string)$posted['project_id'])) {
            $project = db_one($db,'SELECT unique_id FROM projects WHERE id = ? AND company_id = ?',[$posted['project_id'],$user['company_id']]);
            $posted['project_id'] = $project['unique_id'] ?? '';
        }
    }
    if ($moduleKey === 'teams') $posted['team_status'] ??= 'active';
    [$values,$errors] = validate_record($db,(int)$user['company_id'],module_config($moduleKey),$posted);
    if ($errors) json_response(['ok'=>false,'error'=>'Check the submitted fields.','fields'=>$errors],422);
    try {
        $id = save_record($db,(int)$user['company_id'],$moduleKey,$values,null);
        json_response(['ok'=>true,'id'=>$id,'message'=>ucfirst(module_config($moduleKey)['singular']) . ' created.'],201);
    } catch (mysqli_sql_exception $error) {
        if ($error->getCode() === 1062) fail('A record already uses these details.',409);
        throw $error;
    }
}

function legacy_update(string $moduleKey, array $aliases = [], string $idKey = 'id'): never {
    require_post(); $db = database(); $user = workspace_user($db);
    require_module_access($db,$user,$moduleKey,true);
    $posted = $_POST;
    if (str_contains($_SERVER['CONTENT_TYPE'] ?? '', 'application/json')) {
        $posted = json_decode(file_get_contents('php://input'),true);
        if (!is_array($posted) || array_is_list($posted)) fail('Send a valid JSON object.',422);
    }
    foreach ($aliases as $old=>$new) if (isset($posted[$old])) $posted[$new] = $posted[$old];
    $id = filter_var($posted[$idKey] ?? null,FILTER_VALIDATE_INT,['options'=>['min_range'=>1]]);
    if (!$id) fail('Choose a record to update.',422);
    $module = module_config($moduleKey);
    $record = db_one($db,"SELECT * FROM `{$module['table']}` WHERE `{$module['pk']}` = ? AND company_id = ?",[$id,$user['company_id']]);
    if (!$record) fail('This record could not be found in your workspace.',404);
    $version = is_string($posted['version'] ?? null) ? $posted['version'] : '';
    if ($version === '') fail('Reload this record and send its current version before saving.',409);
    foreach ($module['fields'] as $name=>$field) if (str_starts_with($field['type'],'multi:')) $record[$name] = json_decode($record[$name] ?: '[]',true) ?: [];
    [$values,$errors] = validate_record($db,(int)$user['company_id'],$module,array_replace($record,$posted));
    if ($errors) json_response(['ok'=>false,'error'=>'Check the submitted fields.','fields'=>$errors],422);
    save_record($db,(int)$user['company_id'],$moduleKey,$values,$id,$version);
    json_response(['ok'=>true,'id'=>$id,'message'=>'Record updated.']);
}

function legacy_lookup(string $moduleKey, ?string $idKey = null): never {
    $db = database(); $user = workspace_user($db);
    require_module_access($db,$user,$moduleKey);
    if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'GET') fail('Use GET to read records.',405);
    $module = module_config($moduleKey);
    $params = [$user['company_id']]; $where = 'company_id = ?';
    if ($idKey !== null) {
        $id = filter_var($_GET[$idKey] ?? null,FILTER_VALIDATE_INT,['options'=>['min_range'=>1]]);
        if (!$id) fail('Choose a record.',422);
        $where .= " AND `{$module['pk']}` = ?"; $params[] = $id;
    }
    $rows = db_rows($db,"SELECT * FROM `{$module['table']}` WHERE $where ORDER BY `{$module['pk']}` DESC LIMIT 100",$params);
    if ($idKey !== null && !$rows) fail('This record could not be found in your workspace.',404);
    $data = array_map(fn($row)=>array_intersect_key($row,array_flip([$module['pk'],...array_keys($module['fields'])])) + ['version'=>record_version($row)],$rows);
    json_response(['ok'=>true,'data'=>$idKey === null ? $data : $data[0]]);
}
