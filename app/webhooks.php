<?php
declare(strict_types=1);
require_once __DIR__ . '/secrets.php';
require_once __DIR__ . '/outbound.php';

function webhook_event_modules(mysqli $db, int $companyId, int $destinationId): array {
    return array_column(db_rows($db,'SELECT module_key FROM webhook_destination_modules WHERE company_id = ? AND destination_id = ? ORDER BY module_key',[$companyId,$destinationId]),'module_key');
}

function webhook_validate_url(string $url, ?callable $resolver = null): array {
    if ($url === '' || strlen($url) > 2048 || preg_match('/[\x00-\x20\x7f]/',$url) || !filter_var($url,FILTER_VALIDATE_URL)) {
        throw new InvalidArgumentException('Enter a valid HTTPS webhook URL.');
    }
    $parts = parse_url($url);
    $host = strtolower((string)($parts['host'] ?? ''));
    if (($parts['scheme'] ?? '') !== 'https' || $host === '' || isset($parts['user']) || isset($parts['pass']) || isset($parts['fragment']) || (isset($parts['port']) && (int)$parts['port'] !== 443)) {
        throw new InvalidArgumentException('Webhook URLs must use HTTPS on the standard port and cannot contain credentials or fragments.');
    }
    if (filter_var($host,FILTER_VALIDATE_IP) || !filter_var($host,FILTER_VALIDATE_DOMAIN,FILTER_FLAG_HOSTNAME) || in_array($host,['localhost'],true) || preg_match('/\.(local|internal|test|localhost)$/D',$host)) {
        throw new InvalidArgumentException('Use a public service hostname for the webhook destination.');
    }
    $addresses = $resolver !== null ? $resolver($host) : webhook_dns_addresses($host);
    if (!is_array($addresses) || !$addresses) throw new RuntimeException('Webhook destination hostname did not resolve.');
    $public = [];
    foreach (array_unique($addresses) as $address) {
        if (!is_string($address) || !webhook_ip_is_public($address)) {
            throw new InvalidArgumentException('Webhook destination resolves to a private or reserved network.');
        }
        $public[] = $address;
    }
    return ['url'=>$url,'host'=>$host,'port'=>443,'addresses'=>$public];
}

function webhook_ip_is_public(string $address): bool {
    if (!filter_var($address,FILTER_VALIDATE_IP,FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) return false;
    $nonPublicCidrs = [
        '0.0.0.0/8','10.0.0.0/8','100.64.0.0/10','127.0.0.0/8','169.254.0.0/16',
        '172.16.0.0/12','192.0.0.0/24','192.0.2.0/24','192.88.99.0/24',
        '192.168.0.0/16','198.18.0.0/15','198.51.100.0/24','203.0.113.0/24',
        '224.0.0.0/4','240.0.0.0/4',
        '::/96','::ffff:0:0/96','64:ff9b::/96','64:ff9b:1::/48','100::/64',
        '2001::/23','2001:db8::/32','2001:10::/28','2001:20::/28',
        '2002::/16','3fff::/20','fc00::/7','fe80::/10','fec0::/10','ff00::/8',
    ];
    $packedAddress = inet_pton($address);
    if ($packedAddress === false) return false;
    foreach ($nonPublicCidrs as $cidr) {
        [$network,$prefix] = explode('/',$cidr,2);
        $packedNetwork = inet_pton($network);
        if ($packedNetwork === false || strlen($packedNetwork) !== strlen($packedAddress)) continue;
        $prefix = (int)$prefix;
        $wholeBytes = intdiv($prefix,8);
        $remainingBits = $prefix % 8;
        if (substr($packedAddress,0,$wholeBytes) !== substr($packedNetwork,0,$wholeBytes)) continue;
        if ($remainingBits === 0) return false;
        $mask = (0xff << (8 - $remainingBits)) & 0xff;
        if ((ord($packedAddress[$wholeBytes]) & $mask) === (ord($packedNetwork[$wholeBytes]) & $mask)) return false;
    }
    return true;
}

function webhook_dns_addresses(string $host): array {
    $records = dns_get_record($host,DNS_A | DNS_AAAA);
    if (!is_array($records)) return [];
    $addresses = [];
    foreach ($records as $record) {
        if (isset($record['ip'])) $addresses[] = $record['ip'];
        if (isset($record['ipv6'])) $addresses[] = $record['ipv6'];
    }
    return $addresses;
}

function webhook_queue_record(mysqli $db, int $companyId, string $module, int $recordId, string $action, array $fields): int {
    if (!in_array($module,array_keys(modules()),true) || !in_array($action,['created','updated'],true)) return 0;
    $destinations = db_rows($db,'SELECT d.id,d.signing_secret_ciphertext FROM webhook_destinations d JOIN webhook_destination_modules m ON m.destination_id = d.id AND m.company_id = d.company_id WHERE d.company_id = ? AND d.enabled = 1 AND m.module_key = ? ORDER BY d.id',[$companyId,$module]);
    $count = 0;
    foreach ($destinations as $destination) {
        $eventId = bin2hex(random_bytes(16));
        $payload = json_encode([
            'id'=>$eventId,
            'event'=>'record.' . $action,
            'created_at'=>gmdate('Y-m-d\TH:i:s\Z'),
            'workspace_id'=>$companyId,
            'record'=>['module'=>$module,'id'=>$recordId,'fields'=>$fields],
        ],JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES | JSON_INVALID_UTF8_SUBSTITUTE);
        db_run($db,'INSERT INTO webhook_outbox (company_id,destination_id,event_id,event_name,payload_json) VALUES (?,?,?,?,?)',[$companyId,(int)$destination['id'],$eventId,'record.' . $action,$payload]);
        $count++;
    }
    return $count;
}

function webhook_dispatch(string $url, string $secret, string $eventId, string $eventName, string $payload, ?callable $transport = null): int {
    $target = webhook_validate_url($url);
    $signature = hash_hmac('sha256',$payload,$secret);
    $headers = [
        'Content-Type: application/json',
        'User-Agent: SimplrSuite-Webhooks/1.0',
        'X-Simplr-Delivery: ' . $eventId,
        'X-Simplr-Event: ' . $eventName,
        'X-Simplr-Signature: sha256=' . $signature,
    ];
    if ($transport !== null) {
        if (PHP_SAPI !== 'cli') throw new LogicException('Webhook test transports are CLI-only.');
        $status = $transport($target,$headers,$payload);
        if (!is_int($status)) throw new RuntimeException('Webhook test transport returned an invalid status.');
        return $status;
    }
    $handle = secure_curl(curl_init($url));
    $pins = [];
    foreach ($target['addresses'] as $address) {
        $pins[] = $target['host'] . ':443:' . (str_contains($address,':') ? '[' . $address . ']' : $address);
    }
    curl_setopt_array($handle,[
        CURLOPT_RETURNTRANSFER=>true,CURLOPT_POST=>true,CURLOPT_POSTFIELDS=>$payload,
        CURLOPT_HTTPHEADER=>$headers,CURLOPT_CONNECTTIMEOUT=>3,CURLOPT_TIMEOUT=>8,
        CURLOPT_FOLLOWLOCATION=>false,CURLOPT_PROTOCOLS=>CURLPROTO_HTTPS,
        CURLOPT_RESOLVE=>$pins,CURLOPT_SSL_VERIFYPEER=>true,CURLOPT_SSL_VERIFYHOST=>2,
        CURLOPT_MAXFILESIZE=>65536,
    ]);
    $response = curl_exec($handle);
    $status = (int)curl_getinfo($handle,CURLINFO_RESPONSE_CODE);
    $error = curl_errno($handle);
    curl_close($handle);
    if (!is_string($response) || strlen($response) > 65536 || $error !== 0) throw new RuntimeException('Webhook request failed before a valid response was received.');
    return $status;
}

function webhook_flush_outbox(mysqli $db, int $limit = 50, ?callable $transport = null): array {
    $result = ['sent'=>0,'retried'=>0,'failed'=>0];
    for ($i=0; $i<max(1,min(100,$limit)); $i++) {
        $db->begin_transaction();
        try {
            $row = db_one($db,"SELECT o.*,d.url_ciphertext,d.signing_secret_ciphertext FROM webhook_outbox o JOIN webhook_destinations d ON d.id = o.destination_id AND d.company_id = o.company_id WHERE d.enabled = 1 AND ((o.state = 'pending' AND o.next_attempt_at <= UTC_TIMESTAMP()) OR (o.state = 'sending' AND o.updated_at < DATE_SUB(UTC_TIMESTAMP(),INTERVAL 10 MINUTE))) ORDER BY o.id LIMIT 1 FOR UPDATE");
            if (!$row) { $db->commit(); break; }
            db_run($db,"UPDATE webhook_outbox SET state = 'sending',attempts = attempts + 1 WHERE id = ?",[(int)$row['id']]);
            $db->commit();
        } catch (Throwable $error) {
            $db->rollback();
            throw $error;
        }
        try {
            $status = webhook_dispatch(decrypt_secret($row['url_ciphertext']),decrypt_secret($row['signing_secret_ciphertext']),$row['event_id'],$row['event_name'],$row['payload_json'],$transport);
            if ($status < 200 || $status >= 300) throw new RuntimeException('Webhook endpoint returned HTTP ' . $status . '.');
            db_run($db,"UPDATE webhook_outbox SET state = 'delivered',delivered_at = UTC_TIMESTAMP(),last_error = NULL WHERE id = ? AND state = 'sending'",[(int)$row['id']]);
            db_run($db,'UPDATE webhook_destinations SET last_delivery_at = UTC_TIMESTAMP(),last_delivery_status = ? WHERE id = ? AND company_id = ?',[sprintf('%d',$status),(int)$row['destination_id'],(int)$row['company_id']]);
            $result['sent']++;
        } catch (Throwable $error) {
            $attempts = (int)$row['attempts'] + 1;
            $state = $attempts >= 5 ? 'failed' : 'pending';
            $delay = min(3600,60 * (2 ** min(6,$attempts - 1)));
            $message = $error instanceof InvalidArgumentException ? $error->getMessage() : mb_substr($error->getMessage(),0,200);
            db_run($db,'UPDATE webhook_outbox SET state = ?,next_attempt_at = DATE_ADD(UTC_TIMESTAMP(),INTERVAL ? SECOND),last_error = ? WHERE id = ? AND state = \'sending\'',[$state,$delay,$message,(int)$row['id']]);
            db_run($db,'UPDATE webhook_destinations SET last_delivery_at = UTC_TIMESTAMP(),last_delivery_status = ? WHERE id = ? AND company_id = ?',[$state === 'failed' ? 'failed' : 'retry',(int)$row['destination_id'],(int)$row['company_id']]);
            $result[$state === 'failed' ? 'failed' : 'retried']++;
        }
    }
    return $result;
}
