<?php
require_once __DIR__ . '/../../app/legacy-record.php';
legacy_update('leads',['lead_id'=>'id','new_status'=>'status']);
