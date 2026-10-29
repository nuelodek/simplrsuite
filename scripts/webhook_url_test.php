<?php
declare(strict_types=1);
require_once __DIR__ . '/../app/webhooks.php';

$checks = 0;
$failures = [];
$check = static function (bool $condition, string $description) use (&$checks, &$failures): void {
    $checks++;
    if (!$condition) $failures[] = $description;
};
$rejects = static function (string $url, array $addresses = ['93.184.216.34']) use (&$checks, &$failures): bool {
    try {
        webhook_validate_url($url, static fn(string $host): array => $addresses);
        $failures[] = 'Expected unsafe webhook destination to be rejected: ' . $url . ' DNS=' . json_encode($addresses);
        $checks++;
        return false;
    } catch (InvalidArgumentException|RuntimeException) {
        $checks++;
        return true;
    }
};

$accepted = webhook_validate_url(
    'https://hooks.example.com/events',
    static fn(string $host): array => ['93.184.216.34','2606:4700:4700::1111','93.184.216.34']
);
$check($accepted === [
    'url'=>'https://hooks.example.com/events',
    'host'=>'hooks.example.com',
    'port'=>443,
    'addresses'=>['93.184.216.34','2606:4700:4700::1111'],
], 'Valid public DNS addresses are retained once for connection pinning');

foreach ([
    'http://hooks.example.com/events',
    'https://user:pass@hooks.example.com/events',
    'https://hooks.example.com/events#fragment',
    'https://hooks.example.com:8443/events',
    'https://127.0.0.1/events',
    'https://localhost/events',
    'https://service.internal/events',
    "https://hooks.example.com/\r\nheader",
    'https://' . str_repeat('a',2050) . '.com/events',
] as $url) {
    $rejects($url);
}

$rejects('https://hooks.example.com/events', []);
foreach ([
    ['10.0.0.8'],
    ['127.0.0.1'],
    ['169.254.10.2'],
    ['100.64.0.1'],
    ['192.0.0.9'],
    ['192.0.2.10'],
    ['198.18.0.1'],
    ['198.51.100.1'],
    ['203.0.113.1'],
    ['224.0.0.1'],
    ['93.184.216.34','10.0.0.8'],
    ['::1'],
    ['fe80::1'],
    ['fc00::1'],
    ['64:ff9b::a00:1'],
    ['64:ff9b:1::1'],
    ['100::1'],
    ['2002::1'],
    ['3fff::1'],
    ['::ffff:10.0.0.1'],
] as $addresses) {
    $rejects('https://hooks.example.com/events', $addresses);
}

if ($failures) {
    fwrite(STDERR, 'Webhook URL checks failed: ' . implode('; ', $failures) . PHP_EOL);
    exit(1);
}
echo "Webhook URL checks passed: $checks" . PHP_EOL;
