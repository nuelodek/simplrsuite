<?php
require_once __DIR__ . '/../../app/bootstrap.php';
if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'GET') fail('Open the current workspace form to make changes.',405);
redirect('/users/employees/workspace.php#profile');
