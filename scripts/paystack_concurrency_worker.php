<?php
declare(strict_types=1);
require_once __DIR__ . '/../app/paystack.php';
if (PHP_SAPI !== 'cli' || config('APP_ENV') !== 'local' || config('MAIL_TRANSPORT') !== 'log') exit(2);
$fixture = json_decode(file_get_contents(SIMPLR_ROOT . '/.local/billing-concurrency-fixture.json'),true);
if (!preg_match('/^alpha-[0-9]+@example\.test$/D',$fixture['email'] ?? '')) exit(3);
$db = database();
$order = db_one($db,"SELECT * FROM billing_orders WHERE reference = ? AND expected_email = ? AND provider_mode = 'test'",[$fixture['reference'],$fixture['email']]);
if (!$order) exit(4);
echo fulfill_billing_order($db,$order,$fixture['payment']);
