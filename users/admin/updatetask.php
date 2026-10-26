<?php
require_once __DIR__ . '/../../app/legacy-record.php';
legacy_update('tasks',['taskId'=>'id','updateTitle'=>'title','updateDescription'=>'description','updateDueDate'=>'due_date','updatePriority'=>'priority','updateAssignee'=>'assignee']);
