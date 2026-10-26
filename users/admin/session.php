<?php
require_once __DIR__ . '/../../app/auth.php';
$sessionDatabase = database();
$sessionUser = admin_user($sessionDatabase);
$user_id = $sessionUser['id'];
$email = $sessionUser['email'];
$company_name = $sessionUser['company_name'];
$industry = $sessionUser['industry'];
$company_id = $companyId = $sessionUser['company_id'];
$status = $sessionUser['status'];
