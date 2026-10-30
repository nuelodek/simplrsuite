<?php
declare(strict_types=1);
require_once __DIR__ . '/database.php';
require_once __DIR__ . '/mail.php';
// In-app alerts with an email outbox. Formal notices (queries and penalties) are
// always emailed; routine alerts follow each person's preference: instant, a daily
// digest, or in-app only. Every lookup is scoped to the recipient's company.
function viewer_identity(array $user): array {
    return ['role'=>(string)($_SESSION['auth_role'] ?? ''),'id'=>(int)$user['id'],'company_id'=>(int)$user['company_id']];
}
function company_owner_id(mysqli $db, int $companyId): int {
    return (int)(db_one($db,'SELECT id FROM users WHERE company_id = ? ORDER BY id LIMIT 1',[$companyId])['id'] ?? 0);
}
function recipient_contact(mysqli $db, int $companyId, string $role, int $id): ?array {
    return match ($role) {
        'admin' => db_one($db,"SELECT email, 'Workspace owner' AS name FROM users WHERE id = ? AND company_id = ?",[$id,$companyId]),
        'employee' => db_one($db,"SELECT email, TRIM(CONCAT(first_name, ' ', last_name)) AS name FROM employees WHERE id = ? AND company_id = ? AND archived_at IS NULL",[$id,$companyId]),
        'manager' => db_one($db,'SELECT manager_email AS email, manager_name AS name FROM managers WHERE id = ? AND company_id = ? AND archived_at IS NULL',[$id,$companyId]),
        default => null,
    };
}
function notification_email_mode(mysqli $db, int $companyId, string $role, int $id): string {
    $mode = db_one($db,'SELECT email_mode FROM notification_preferences WHERE company_id = ? AND recipient_role = ? AND recipient_id = ?',[$companyId,$role,$id])['email_mode'] ?? 'instant';
    return in_array($mode,['instant','digest','off'],true) ? $mode : 'instant';
}
// Creates an alert once per dedupe key. Returns the new id, or null when it already existed.
function notify(mysqli $db, int $companyId, string $role, int $id, string $kind, string $title, string $body = '', string $url = '', bool $formal = false, ?string $dedupe = null, bool $deliverEmail = true): ?int {
    if (!in_array($role,['admin','employee','manager'],true) || $id <= 0) return null;
    if ($url !== '' && !str_starts_with($url,'/')) $url = '';
    $statement = db_run($db,'INSERT IGNORE INTO notifications (company_id,recipient_role,recipient_id,kind,title,body,url,is_formal,dedupe_key,email_status) VALUES (?,?,?,?,?,?,?,?,?,?)',[
        $companyId,$role,$id,mb_substr($kind,0,40),mb_substr($title,0,200),mb_substr($body,0,1000),mb_substr($url,0,255),$formal ? 1 : 0,$dedupe !== null ? mb_substr($dedupe,0,120) : null,$deliverEmail ? 'pending' : 'skipped',
    ]);
    if ($statement->affected_rows < 1) return null;
    $notificationId = (int)$statement->insert_id;
    // Web requests deliver straight away; the scheduler flushes the outbox for its own alerts.
    if ($deliverEmail && PHP_SAPI !== 'cli') notification_deliver($db,$notificationId);
    return $notificationId;
}
function notification_link(string $url): string {
    $base = rtrim(config('APP_URL','https://127.0.0.1:8000'),'/');
    $base = preg_replace('/^http:/i','https:',$base);
    return $base . ($url !== '' ? $url : '/users/admin/notifications.php');
}
function notification_deliver(mysqli $db, int $notificationId): string {
    // Claim the message before sending, so a web request and the scheduler never both send it.
    // A claim older than ten minutes belongs to a run that died mid-send and may be retried.
    $claim = db_run($db,"UPDATE notifications SET email_status = 'sending', email_claimed_at = UTC_TIMESTAMP() WHERE id = ? AND (email_status IN ('pending','failed') OR (email_status = 'sending' AND email_claimed_at < UTC_TIMESTAMP() - INTERVAL 10 MINUTE))",[$notificationId]);
    $row = db_one($db,'SELECT * FROM notifications WHERE id = ?',[$notificationId]);
    if (!$row || $claim->affected_rows !== 1) return (string)($row['email_status'] ?? 'missing');
    $mode = notification_email_mode($db,(int)$row['company_id'],$row['recipient_role'],(int)$row['recipient_id']);
    $contact = recipient_contact($db,(int)$row['company_id'],$row['recipient_role'],(int)$row['recipient_id']);
    $status = match (true) {
        !$contact || !filter_var($contact['email'] ?? '',FILTER_VALIDATE_EMAIL) => 'skipped',
        !$row['is_formal'] && $mode === 'off' => 'skipped',
        !$row['is_formal'] && $mode === 'digest' => 'digest',
        default => '',
    };
    if ($status === '') {
        $text = $row['title'] . "\n\n" . ($row['body'] !== '' ? $row['body'] . "\n\n" : '') . 'Open in Simplr: ' . notification_link($row['url']) . "\n\n" . ($row['is_formal'] ? "This is a formal workplace notice. Please read it and respond in Simplr.\n" : 'Manage your alerts: ' . notification_link('/users/admin/notifications.php') . "\n");
        $sent = send_account_mail($contact['email'],($row['is_formal'] ? 'Action required: ' : '') . $row['title'],$text);
        $status = $sent ? 'sent' : 'failed';
    }
    db_run($db,'UPDATE notifications SET email_status = ?, email_attempts = email_attempts + ?, emailed_at = IF(? = \'sent\', UTC_TIMESTAMP(), emailed_at) WHERE id = ?',[$status,in_array($status,['sent','failed'],true) ? 1 : 0,$status,$notificationId]);
    return $status;
}
// Sends one daily email per person who chose the digest, listing their unread routine alerts.
function notification_send_digests(mysqli $db): int {
    $sent = 0;
    foreach (db_rows($db,"SELECT DISTINCT n.company_id, n.recipient_role, n.recipient_id FROM notifications n LEFT JOIN notification_preferences p ON p.company_id = n.company_id AND p.recipient_role = n.recipient_role AND p.recipient_id = n.recipient_id WHERE n.email_status = 'digest' AND (p.last_digest_at IS NULL OR p.last_digest_at < UTC_TIMESTAMP() - INTERVAL 20 HOUR)") as $person) {
        [$companyId,$role,$id] = [(int)$person['company_id'],$person['recipient_role'],(int)$person['recipient_id']];
        $items = db_rows($db,"SELECT id, title, url FROM notifications WHERE company_id = ? AND recipient_role = ? AND recipient_id = ? AND email_status = 'digest' ORDER BY id LIMIT 50",[$companyId,$role,$id]);
        if (!$items) continue;
        $contact = recipient_contact($db,$companyId,$role,$id);
        $validEmail = $contact && filter_var($contact['email'] ?? '',FILTER_VALIDATE_EMAIL);
        $ok = $validEmail && send_account_mail($contact['email'],'Your Simplr summary: ' . count($items) . ' ' . (count($items) === 1 ? 'update' : 'updates'),"Here is what happened in your workspace:\n\n" . implode("\n",array_map(fn($item)=>'• ' . $item['title'] . ' — ' . notification_link($item['url']),$items)) . "\n\nManage your alerts: " . notification_link('/users/admin/notifications.php') . "\n");
        $ids = array_column($items,'id');
        db_run($db,'UPDATE notifications SET email_status = ?, emailed_at = IF(? = \'sent\',UTC_TIMESTAMP(),emailed_at) WHERE id IN (' . implode(',',array_fill(0,count($ids),'?')) . ')',[$ok ? 'sent' : ($validEmail ? 'digest' : 'skipped'),$ok ? 'sent' : '',...$ids]);
        if ($ok || !$validEmail) db_run($db,'INSERT INTO notification_preferences (company_id,recipient_role,recipient_id,email_mode,last_digest_at) VALUES (?,?,?,\'digest\',UTC_TIMESTAMP()) ON DUPLICATE KEY UPDATE last_digest_at = UTC_TIMESTAMP()',[$companyId,$role,$id]);
        if ($ok) $sent++;
    }
    return $sent;
}
function notification_flush_outbox(mysqli $db): array {
    $result = ['sent'=>0,'failed'=>0,'skipped'=>0,'digest'=>0];
    $lastId = 0;
    do {
        $rows = db_rows($db,"SELECT id FROM notifications WHERE id > ? AND (email_status = 'pending' OR (email_status = 'failed' AND email_attempts < 3) OR (email_status = 'sending' AND email_claimed_at < UTC_TIMESTAMP() - INTERVAL 10 MINUTE)) AND created_at > UTC_TIMESTAMP() - INTERVAL 2 DAY ORDER BY id LIMIT 500",[$lastId]);
        foreach ($rows as $row) {
            $lastId = (int)$row['id'];
            $status = notification_deliver($db,$lastId);
            if (isset($result[$status])) $result[$status]++;
        }
    } while (count($rows) === 500);
    return $result;
}
function notification_unread_count(mysqli $db, array $viewer): int {
    return (int)db_one($db,'SELECT COUNT(*) AS count FROM notifications WHERE company_id = ? AND recipient_role = ? AND recipient_id = ? AND read_at IS NULL',[$viewer['company_id'],$viewer['role'],$viewer['id']])['count'];
}
function notification_list(mysqli $db, array $viewer, int $limit, int $offset = 0, bool $unreadOnly = false): array {
    return db_rows($db,'SELECT * FROM notifications WHERE company_id = ? AND recipient_role = ? AND recipient_id = ?' . ($unreadOnly ? ' AND read_at IS NULL' : '') . ' ORDER BY id DESC LIMIT ' . max(1,$limit) . ' OFFSET ' . max(0,$offset),[$viewer['company_id'],$viewer['role'],$viewer['id']]);
}
function notification_icon(string $kind): string {
    return match (explode('.',$kind)[0]) { 'query' => 'flag', 'penalty' => 'alert', 'report' => 'chart', 'task' => 'task', default => 'bell' };
}
