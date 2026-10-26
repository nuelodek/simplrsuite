<?php
require_once __DIR__ . '/../../app/bootstrap.php';
if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'GET') fail('Open the current form to make changes.',405);
redirect('/users/admin/workspace.php?module=events&action=new');
