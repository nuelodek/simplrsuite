<?php
require_once __DIR__ . '/../app/auth.php';
require_once __DIR__ . '/../app/modules.php';
require_once __DIR__ . '/../app/archive.php';
$db = database(); $user = workspace_user($db);
$key = is_string($_GET['module'] ?? null) ? $_GET['module'] : '';
$module = module_config($key); $companyId = (int)$user['company_id'];
require_module_access($db,$user,$key,($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'GET');
if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'GET') {
    $page = max(1,min(100000,(int)($_GET['page'] ?? 1))); $offset = ($page-1)*25;
    $view = is_string($_GET['view'] ?? null) ? $_GET['view'] : '';
    $archiveable = in_array($key,['employees','managers','tasks','projects'],true);
    if ($view === 'trash' && !$archiveable) fail('Trash is unavailable for this workspace tool.',404);
    $archiveFilter = $archiveable ? ($view === 'trash' ? ' AND archived_at IS NOT NULL' : ' AND archived_at IS NULL') : '';
    $fields = array_unique([$module['pk'],...array_keys($module['fields'])]);
    if ($key === 'jobs') $fields = [...$fields,'public_id','published_at'];
    $rows = db_rows($db,"SELECT * FROM `{$module['table']}` WHERE company_id = ?$archiveFilter ORDER BY `{$module['pk']}` DESC LIMIT 25 OFFSET $offset",[$companyId]);
    $rows = array_map(fn($row)=>array_intersect_key($row,array_flip($fields)) + ['version'=>record_version($row)],$rows);
    $count = (int)db_one($db,"SELECT COUNT(*) AS count FROM `{$module['table']}` WHERE company_id = ?$archiveFilter",[$companyId])['count'];
    json_response(['ok'=>true,'data'=>$rows,'page'=>$page,'per_page'=>25,'total'=>$count]);
}
require_post();
$action = input('action') ?: 'create';
if (in_array($action,['archive','restore','purge'],true)) {
    $id = filter_var($_POST['id'] ?? null,FILTER_VALIDATE_INT,['options'=>['min_range'=>1]]);
    if (!$id) fail('Choose a record to update in Trash.',422);
    change_record_archive($db,$user,$key,(int)$id,$action,input('version'));
    json_response(['ok'=>true,'id'=>(int)$id,'action'=>$action]);
}
if ($action === 'move') {
    $id = filter_var($_POST['id'] ?? null,FILTER_VALIDATE_INT,['options'=>['min_range'=>1]]);
    if (!$id) fail('Choose a record to move.',422);
    json_response(['ok'=>true,'id'=>$id,'version'=>move_record_stage($db,$companyId,$key,$id,input('stage'),input('version'))]);
}
if (!in_array($action,['create','update'],true)) fail('Choose create or update.',422);
$id = $action === 'update' ? filter_var($_POST['id'] ?? null,FILTER_VALIDATE_INT,['options'=>['min_range'=>1]]) : null;
if ($action === 'update' && !$id) fail('Choose a record to update.',422);
[$values,$errors] = validate_record($db,$companyId,$module,$_POST);
if ($errors) json_response(['ok'=>false,'error'=>'Check the submitted fields.','fields'=>$errors],422);
try {
    $id = save_record($db,$companyId,$key,$values,$id,input('version'));
    json_response(['ok'=>true,'id'=>$id],$action === 'create' ? 201 : 200);
} catch (mysqli_sql_exception $error) {
    if ($error->getCode() === 1062) json_response(['ok'=>false,'error'=>'A record already uses these details.'],409);
    throw $error;
}
