<?php
require_once __DIR__ . '/../../app/bootstrap.php';
if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'GET') fail('Open Workspace help to submit your request.',405);
redirect('/users/help.php');
