<?php
require_once __DIR__ . '/../../app/legacy-record.php';
legacy_create('tasks', ['taskTitle'=>'title', 'taskDescription'=>'description', 'dueDate'=>'due_date']);
