<?php
// Dedicated localhost helper for HTTP signature and UI tests. These are fake
// keys. Tests only view forms and acknowledge orders already paid by CLI fakes.
require_once __DIR__ . '/../app/bootstrap.php';
if (PHP_SAPI !== 'cli-server' || config('APP_ENV') !== 'local' || config('MAIL_TRANSPORT') !== 'log') { http_response_code(404); exit; }
putenv('PAYSTACK_ENABLED=true');
putenv('PAYSTACK_MODE=test');
putenv('PAYSTACK_SECRET_KEY=sk_test_httpfixtureonlynotarealkey');
putenv('FLUTTERWAVE_ENABLED=true');
putenv('FLUTTERWAVE_SECRET_KEY=FLWSECK_TEST-localfixtureonly-X');
putenv('FLUTTERWAVE_WEBHOOK_HASH=local-fixture-hash-only-not-a-real-secret');
putenv('BILLING_USD_RATE=0.00063');
return require __DIR__ . '/../router.php';
