<?php
require_once __DIR__ . '/app/database.php';
try {
    $db = database();
    $db->query('SELECT 1');
    json_response(['status'=>'ok']);
} catch (Throwable $error) {
    json_response(['status'=>'unavailable'],503);
}
