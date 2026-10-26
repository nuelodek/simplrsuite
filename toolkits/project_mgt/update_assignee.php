<?php
require_once __DIR__ . '/../../app/legacy-record.php';
legacy_update('tasks',['taskId'=>'id','newAssigneeId'=>'assignee']);
