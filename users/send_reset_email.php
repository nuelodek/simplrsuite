<?php
require_once __DIR__ . '/../app/staff-recovery.php';
require_post();
request_staff_reset(database(), input('role'), strtolower(input('email')), input('workspace'));
json_response(['success'=>true, 'message'=>'If these details match an active account, a password reset link will arrive shortly.']);
