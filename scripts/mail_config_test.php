<?php
declare(strict_types=1);
require_once __DIR__ . '/../app/mail.php';

$checks = 0;
$check = static function (bool $condition, string $description) use (&$checks): void {
    if (!$condition) { fwrite(STDERR,'Failed: ' . $description . PHP_EOL); exit(1); }
    $checks++;
};
$set = static function (array $values): void {
    foreach ($values as $key=>$value) putenv($key . '=' . $value);
};

$set([
    'APP_ENV'=>'local',
    'APP_URL'=>'http://127.0.0.1:8000',
    'MAIL_TRANSPORT'=>'log',
]);
$check(mail_ready(),'local log transport remains ready for synthetic mail capture');

$smtp = [
    'APP_ENV'=>'production',
    'APP_URL'=>'https://simplr.test',
    'MAIL_TRANSPORT'=>'smtp',
    'MAIL_FROM_ADDRESS'=>'support@example.test',
    'SMTP_HOST'=>'smtp.example.test',
    'SMTP_PORT'=>'465',
    'SMTP_USERNAME'=>'smtp-user',
    'SMTP_PASSWORD'=>'smtp-password',
    'SMTP_ENCRYPTION'=>'ssl',
];
$set($smtp);
$check(mail_ready(),'complete authenticated SMTP configuration is accepted without connecting');

foreach ([
    'SMTP_HOST'=>'',
    'SMTP_USERNAME'=>'',
    'SMTP_PASSWORD'=>'',
    'SMTP_ENCRYPTION'=>'plain',
    'MAIL_FROM_ADDRESS'=>'not-an-email',
    'APP_URL'=>'https://service.example',
] as $key=>$value) {
    $configured = $smtp;
    $configured[$key] = $value;
    $set($configured);
    $check(!mail_ready(),'incomplete or invalid ' . $key . ' is rejected');
}

foreach (['0','65536','invalid'] as $port) {
    $configured = $smtp;
    $configured['SMTP_PORT'] = $port;
    $set($configured);
    $check(!mail_ready(),'invalid SMTP_PORT is rejected');
}

$set($smtp);
$set(['MAIL_TRANSPORT'=>'unknown']);
$check(!mail_ready(),'unknown mail transports are rejected');
$set(['APP_ENV'=>'production','MAIL_TRANSPORT'=>'log']);
$check(!mail_ready(),'log transport cannot be enabled outside local mode');

echo "Mail configuration checks passed: $checks\n";
