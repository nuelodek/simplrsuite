<?php
require_once __DIR__ . '/../../app/auth.php';
admin_user(database());
redirect('/users/admin/talent-directory.php');
