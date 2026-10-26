<?php
require_once __DIR__ . '/../../app/auth.php';
$db = database(); $user = admin_user($db);
json_response(['user_id'=>(int)$user['id']]);
