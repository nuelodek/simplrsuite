<?php
// Read-only checks. No credentials, provider traffic or email are emitted.
declare(strict_types=1);
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
require_once __DIR__ . '/../app/paystack.php';
$routes = require __DIR__ . '/../app/routes.php';
$production = in_array('--production',$argv,true); $checks = [];
function check_requirement(string $name, bool $ok, string $action = ''): void {
    global $checks; $checks[] = ['name'=>$name,'passed'=>$ok,'action'=>$ok ? '' : $action];
}
check_requirement('PHP 8.2 or newer',PHP_VERSION_ID >= 80200,'Upgrade PHP.');
foreach (['mysqli','mysqlnd','mbstring','openssl','curl','fileinfo','session'] as $extension) check_requirement('PHP extension: ' . $extension,extension_loaded($extension),'Enable this extension.');
try {
    $db = database();
    check_requirement('Database reachable',true);
    $applied = array_column(db_rows($db,'SELECT name FROM schema_migrations'),'name');
    $pending = array_filter(glob(SIMPLR_ROOT . '/database/migrations/*.sql'),fn($p)=>!in_array(basename($p),$applied,true));
    check_requirement('All application migrations applied',!$pending,'Back up first, then run php scripts/migrate.php.');
} catch (Throwable $error) { check_requirement('Database and schema available',false,'Check database configuration and install/apply migrations.'); }
$missing = [];
foreach ($routes as $route=>$file) if (!is_file(SIMPLR_ROOT . '/' . $file)) $missing[] = $route;
check_requirement('Route manifest targets exist',!$missing,'Restore missing reviewed route files.');
$storage = SIMPLR_ROOT . '/storage';
check_requirement('Private storage writable',is_dir($storage) && is_writable($storage),'Provision a private persistent storage directory writable by the PHP worker.');
check_requirement('Local mail cannot escape through log transport',config('MAIL_TRANSPORT') !== 'log' || config('APP_ENV') === 'local','Log transport is only supported in local environments.');
if ($production) {
    check_requirement('Production environment selected',config('APP_ENV') === 'production','Set APP_ENV=production on the deployment host.');
    foreach (['APP_URL','APP_BUSINESS_URL','APP_TALENT_URL','APP_WORK_URL'] as $key) {
        $url = config($key); $parts = parse_url($url);
        $ok = filter_var($url,FILTER_VALIDATE_URL) && ($parts['scheme'] ?? '') === 'https' && !isset($parts['user']) && !isset($parts['pass']) && !isset($parts['query']) && !isset($parts['fragment']) && !in_array($parts['host'] ?? '',['localhost','127.0.0.1','::1'],true) && !str_ends_with($parts['host'] ?? '','.example');
        check_requirement('HTTPS public origin: ' . $key,(bool)$ok,'Set the real HTTPS origin after DNS and certificates are ready.');
    }
    check_requirement('SMTP configured',config('MAIL_TRANSPORT') === 'smtp' && config('SMTP_HOST') !== '' && config('SMTP_USERNAME') !== '' && config('SMTP_PASSWORD') !== '' && (bool)filter_var(config('MAIL_FROM_ADDRESS'),FILTER_VALIDATE_EMAIL) && in_array(config('SMTP_ENCRYPTION'),['tls','ssl'],true),'Configure authenticated encrypted SMTP; delivery must then be tested separately.');
    check_requirement('Live Paystack checkout configured',paystack_ready() && config('PAYSTACK_MODE') === 'live','Configure newly issued live Paystack credentials after a successful provider test-mode checkout.');
    check_requirement('Live Flutterwave USD checkout configured',flutterwave_ready() && config('PAYSTACK_MODE') === 'live','Configure newly issued live Flutterwave v3 credentials and webhook hash; confirm the fixed USD price conversion and USD account approval.');
    $appKey = config('APP_KEY'); $decodedKey = str_starts_with($appKey,'base64:') ? base64_decode(substr($appKey,7),true) : false;
    check_requirement('Integration encryption key set',is_string($decodedKey) && strlen($decodedKey) === 32,'Set APP_KEY to base64: followed by 32 random bytes before connecting GitHub; keep it with your backups.');
    check_requirement('Paid editing enforced',config('BILLING_ENFORCE') === 'true','Decide existing customer entitlements, then enable BILLING_ENFORCE for the paid launch.');
    check_requirement('Privacy owner details complete',config('PRIVACY_LEGAL_NAME') !== '' && config('PRIVACY_BUSINESS_ADDRESS') !== '' && (bool)filter_var(config('PRIVACY_CONTACT_EMAIL'),FILTER_VALIDATE_EMAIL),'Provide the legal business name, address and privacy contact email.');
    $reviewed = DateTimeImmutable::createFromFormat('!Y-m-d',config('PRIVACY_REVIEWED_ON'));
    check_requirement('Privacy notice reviewed',config('PRIVACY_APPROVED') === 'true' && $reviewed && $reviewed->format('Y-m-d') === config('PRIVACY_REVIEWED_ON') && config('PRIVACY_REVIEWED_ON') <= gmdate('Y-m-d'),'Review the actual notice, then set PRIVACY_APPROVED and its review date.');
}
$failed = count(array_filter($checks,fn($c)=>!$c['passed']));
echo json_encode(['scope'=>$production ? 'production configuration' : 'local runtime','passed'=>count($checks)-$failed,'total'=>count($checks),'checks'=>$checks,'limitations'=>'This does not verify DNS, HTTPS serving, SMTP delivery, Paystack account activation, public webhooks, backup operations or legal compliance.'],JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . PHP_EOL;
exit($failed ? 1 : 0);
