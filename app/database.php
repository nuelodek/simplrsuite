<?php
declare(strict_types=1);
require_once __DIR__ . '/bootstrap.php';

function database(): mysqli {
    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
    $connection = mysqli_init();
    $connection->options(MYSQLI_OPT_CONNECT_TIMEOUT, 5);
    $connection->real_connect(config('DB_HOST', '127.0.0.1'), config('DB_USERNAME', 'simplr'), config('DB_PASSWORD'), config('DB_DATABASE', 'simplr'), (int)config('DB_PORT', '3306'));
    $connection->set_charset('utf8mb4');
    $connection->query("SET time_zone = '+00:00'");
    return $connection;
}
function db_run(mysqli $db, string $sql, array $params = []): mysqli_stmt {
    $stmt = $db->prepare($sql);
    if ($params) $stmt->bind_param(str_repeat('s', count($params)), ...$params);
    $stmt->execute();
    return $stmt;
}
function db_rows(mysqli $db, string $sql, array $params = []): array { return db_run($db, $sql, $params)->get_result()->fetch_all(MYSQLI_ASSOC); }
function db_one(mysqli $db, string $sql, array $params = []): ?array { return db_run($db, $sql, $params)->get_result()->fetch_assoc(); }
