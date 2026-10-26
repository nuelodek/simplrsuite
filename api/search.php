<?php
// Workspace-wide record search for the command palette. Only tools the signed-in
// person can open are searched, and every query is scoped to their company.
require_once __DIR__ . '/../app/auth.php';
require_once __DIR__ . '/../app/modules.php';
$db = database(); $user = workspace_user($db); $companyId = (int)$user['company_id'];
$query = is_string($_GET['q'] ?? null) ? mb_substr(trim($_GET['q']),0,100) : '';
if (mb_strlen($query) < 2) json_response(['ok'=>true,'data'=>[]]);
$like = '%' . str_replace(['!','%','_'],['!!','!%','!_'],$query) . '%';
$results = [];
foreach (visible_modules($db,$user) as $key=>$module) {
    $columns = array_values(array_filter(array_keys($module['fields']),fn($name)=>in_array($module['fields'][$name]['type'],['text','email','tel'],true)));
    if (!$columns) continue;
    $columns = array_slice($columns,0,4);
    $clauses = implode(' OR ',array_map(fn($column)=>"`$column` LIKE ? ESCAPE '!'",$columns));
    $rows = db_rows($db,"SELECT * FROM `{$module['table']}` WHERE company_id = ? AND ($clauses) ORDER BY `{$module['pk']}` DESC LIMIT 4",[$companyId,...array_fill(0,count($columns),$like)]);
    $stage = module_stage_field($key);
    foreach ($rows as $row) {
        $detail = $stage && !empty($row[$stage]) ? ucfirst((string)$row[$stage]) : '';
        $results[] = ['label'=>record_title($module,$row),'detail'=>trim(ucfirst($module['singular']) . ($detail !== '' ? ' · ' . $detail : '')),'url'=>'/users/admin/workspace.php?module=' . rawurlencode($key) . '&action=view&id=' . (int)$row[$module['pk']],'icon'=>$module['icon']];
        if (count($results) >= 24) break 2;
    }
}
json_response(['ok'=>true,'data'=>$results]);
