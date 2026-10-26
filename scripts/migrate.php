<?php
declare(strict_types=1);
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
require_once __DIR__ . '/../app/database.php';
$db = database();
if (in_array('--install', $argv, true)) {
    if ($db->query('SHOW TABLES')->num_rows !== 0) { fwrite(STDERR, "Install requires an empty database; no data was changed.\n"); exit(1); }
    $db->multi_query(file_get_contents(__DIR__ . '/../database/schema.sql'));
    do { if ($result = $db->store_result()) $result->free(); } while ($db->more_results() && $db->next_result());
    echo "Installed schema without sample or customer data.\n";
}
$db->query('CREATE TABLE IF NOT EXISTS schema_migrations (name VARCHAR(190) PRIMARY KEY, applied_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP) ENGINE=InnoDB');
foreach (glob(__DIR__ . '/../database/migrations/*.sql') as $path) {
    $name = basename($path);
    if (db_one($db, 'SELECT name FROM schema_migrations WHERE name = ?', [$name])) continue;
    if ($name === '002_unique_email.sql') {
        $duplicates = db_one($db, 'SELECT COUNT(*) AS count FROM (SELECT email FROM users WHERE email IS NOT NULL GROUP BY email HAVING COUNT(*) > 1) duplicates');
        if ((int)$duplicates['count'] > 0) { fwrite(STDERR, "Email uniqueness migration requires existing duplicate accounts to be reconciled. No accounts were removed.\n"); exit(1); }
        if ($db->query("SHOW INDEX FROM users WHERE Key_name = 'users_email_unique'")->num_rows > 0) { db_run($db, 'INSERT INTO schema_migrations (name) VALUES (?)', [$name]); continue; }
    }
    $db->multi_query(file_get_contents($path));
    do { if ($result = $db->store_result()) $result->free(); } while ($db->more_results() && $db->next_result());
    db_run($db, 'INSERT INTO schema_migrations (name) VALUES (?)', [$name]);
    echo "Applied $name\n";
}
echo "Database is up to date.\n";
