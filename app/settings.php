<?php
declare(strict_types=1);
require_once __DIR__ . '/database.php';
function workspace_settings(mysqli $db, int $companyId): array {
    return db_one($db,'SELECT currency, timezone FROM workspace_settings WHERE company_id = ?',[$companyId]) ?: ['currency'=>'','timezone'=>config('APP_TIMEZONE','Africa/Lagos')];
}
function supported_currencies(): array {
    return ['NGN'=>'Nigerian naira','USD'=>'US dollar','GBP'=>'Pound sterling','EUR'=>'Euro','CAD'=>'Canadian dollar','GHS'=>'Ghanaian cedi','KES'=>'Kenyan shilling','ZAR'=>'South African rand'];
}
