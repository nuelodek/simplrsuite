<?php
declare(strict_types=1);
require_once __DIR__ . '/modules.php';
require_once __DIR__ . '/notifications.php';

function chat_identity(array $user): array {
    $role = (string)($_SESSION['auth_role'] ?? '');
    if (!in_array($role,['admin','employee','manager'],true)) fail('Please sign in to continue.',401);
    return ['role'=>$role,'id'=>(int)$user['id'],'company_id'=>(int)$user['company_id']];
}

function chat_require_access(mysqli $db, array $user, bool $write = false): array {
    $identity = chat_identity($user);
    require_module_access($db,$user,'messages',$write);
    return $identity;
}

function chat_person(mysqli $db, int $companyId, string $role, int $id): ?array {
    if ($id < 1) return null;
    $person = match ($role) {
        'admin' => db_one($db,"SELECT id, company_name AS name, 'Workspace owner' AS role_label FROM users WHERE id = ? AND company_id = ? AND status = 'Verified'",[$id,$companyId]),
        'employee' => db_one($db,"SELECT id, TRIM(CONCAT(first_name, ' ', last_name)) AS name, COALESCE(NULLIF(job_title,''),'Employee') AS role_label FROM employees WHERE id = ? AND company_id = ? AND archived_at IS NULL",[$id,$companyId]),
        'manager' => db_one($db,"SELECT id, manager_name AS name, 'Manager' AS role_label FROM managers WHERE id = ? AND company_id = ? AND archived_at IS NULL AND status NOT IN ('suspended','disabled','inactive')",[$id,$companyId]),
        default => null,
    };
    if (!$person) return null;
    return ['role'=>$role,'id'=>(int)$person['id'],'name'=>trim((string)$person['name']),'role_label'=>(string)$person['role_label']];
}

function chat_people(mysqli $db, int $companyId, array $exclude = []): array {
    $people = [];
    foreach (db_rows($db,"SELECT id, TRIM(CONCAT(first_name, ' ', last_name)) AS name, COALESCE(NULLIF(job_title,''),'Employee') AS role_label FROM employees WHERE company_id = ? AND archived_at IS NULL ORDER BY first_name,last_name",[$companyId]) as $row) {
        $people[] = ['role'=>'employee','id'=>(int)$row['id'],'name'=>trim((string)$row['name']),'role_label'=>(string)$row['role_label']];
    }
    foreach (db_rows($db,"SELECT id, manager_name AS name, 'Manager' AS role_label FROM managers WHERE company_id = ? AND archived_at IS NULL AND status NOT IN ('suspended','disabled','inactive') ORDER BY manager_name",[$companyId]) as $row) {
        $people[] = ['role'=>'manager','id'=>(int)$row['id'],'name'=>trim((string)$row['name']),'role_label'=>'Manager'];
    }
    foreach (db_rows($db,"SELECT id, company_name AS name, 'Workspace owner' AS role_label FROM users WHERE company_id = ? AND status = 'Verified' ORDER BY id",[$companyId]) as $row) {
        $people[] = ['role'=>'admin','id'=>(int)$row['id'],'name'=>trim((string)$row['name']),'role_label'=>'Workspace owner'];
    }
    return array_values(array_filter($people,static fn(array $person): bool => !in_array($person['role'] . ':' . $person['id'],$exclude,true)));
}

function chat_is_member(mysqli $db, int $companyId, int $conversationId, array $identity): bool {
    return (bool)db_one($db,'SELECT conversation_id FROM chat_members WHERE company_id = ? AND conversation_id = ? AND member_role = ? AND member_id = ?',[$companyId,$conversationId,$identity['role'],$identity['id']]);
}

function chat_conversation(mysqli $db, int $companyId, int $conversationId, array $identity): ?array {
    return db_one($db,'SELECT c.* FROM chat_conversations c JOIN chat_members m ON m.company_id = c.company_id AND m.conversation_id = c.id WHERE c.company_id = ? AND c.id = ? AND m.member_role = ? AND m.member_id = ?',[$companyId,$conversationId,$identity['role'],$identity['id']]);
}

function chat_conversation_title(mysqli $db, array $conversation, array $identity): string {
    if (trim((string)($conversation['name'] ?? '')) !== '') return (string)$conversation['name'];
    $members = db_rows($db,'SELECT member_role, member_id FROM chat_members WHERE company_id = ? AND conversation_id = ? ORDER BY joined_at, member_role, member_id',[$conversation['company_id'],$conversation['id']]);
    $names = [];
    foreach ($members as $member) {
        if ($member['member_role'] === $identity['role'] && (int)$member['member_id'] === $identity['id']) continue;
        $person = chat_person($db,(int)$conversation['company_id'],(string)$member['member_role'],(int)$member['member_id']);
        if ($person) $names[] = $person['name'];
    }
    return $names ? implode(', ',$names) : 'Conversation';
}

function chat_conversation_list(mysqli $db, array $identity): array {
    $rows = db_rows($db,'SELECT c.*, lm.id AS latest_message_id, lm.body AS latest_body, lm.quote_text AS latest_quote, lm.created_at AS latest_at FROM chat_conversations c JOIN chat_members mine ON mine.company_id = c.company_id AND mine.conversation_id = c.id AND mine.member_role = ? AND mine.member_id = ? LEFT JOIN chat_messages lm ON lm.id = (SELECT MAX(m.id) FROM chat_messages m WHERE m.company_id = c.company_id AND m.conversation_id = c.id) WHERE c.company_id = ? ORDER BY COALESCE(lm.created_at,c.updated_at) DESC,c.id DESC LIMIT 100',[$identity['role'],$identity['id'],$identity['company_id']]);
    if (!$rows) return [];
    $conversationIds = array_map(static fn(array $row): int => (int)$row['id'],$rows);
    $placeholders = implode(',',array_fill(0,count($conversationIds),'?'));
    $members = db_rows($db,"SELECT conversation_id,member_role,member_id FROM chat_members WHERE company_id = ? AND conversation_id IN ($placeholders)",[$identity['company_id'],...$conversationIds]);
    $people = [];
    foreach (chat_people($db,(int)$identity['company_id']) as $person) $people[$person['role'] . ':' . $person['id']] = $person['name'];
    $membersByConversation = [];
    foreach ($members as $member) $membersByConversation[(int)$member['conversation_id']][] = $member;
    $referencesByMessage = [];
    $latestMessageIds = array_values(array_filter(array_map(static fn(array $row): int => (int)($row['latest_message_id'] ?? 0),$rows)));
    if ($latestMessageIds) {
        $referenceRows = db_rows($db,"SELECT message_id,token,label FROM chat_message_refs WHERE company_id = ? AND message_id IN (" . implode(',',array_fill(0,count($latestMessageIds),'?')) . ')',[$identity['company_id'],...$latestMessageIds]);
        foreach ($referenceRows as $reference) $referencesByMessage[(int)$reference['message_id']][] = $reference;
    }
    foreach ($rows as &$row) {
        $names = [];
        foreach ($membersByConversation[(int)$row['id']] ?? [] as $member) {
            if ($member['member_role'] === $identity['role'] && (int)$member['member_id'] === $identity['id']) continue;
            $name = $people[$member['member_role'] . ':' . $member['member_id']] ?? '';
            if ($name !== '') $names[] = $name;
        }
        $row['title'] = trim((string)($row['name'] ?? '')) !== '' ? $row['name'] : ($names ? implode(', ',$names) : 'Conversation');
        $preview = (string)($row['latest_quote'] ?? $row['latest_body'] ?? '');
        if ($row['latest_quote'] === null && !empty($row['latest_message_id'])) {
            foreach ($referencesByMessage[(int)$row['latest_message_id']] ?? [] as $reference) {
                $preview = str_replace('[[ref:' . $reference['token'] . ']]','@' . $reference['label'],$preview);
            }
        } elseif ($row['latest_quote'] !== null) {
            $preview = 'Shared: ' . $preview;
        }
        $row['latest_preview'] = $preview;
    }
    unset($row);
    return $rows;
}

function chat_search_people(mysqli $db, int $companyId, string $query, array $conversation = []): array {
    $members = [];
    if ($conversation) {
        foreach (db_rows($db,'SELECT member_role, member_id FROM chat_members WHERE company_id = ? AND conversation_id = ?',[$companyId,(int)$conversation['id']]) as $member) $members[$member['member_role'] . ':' . $member['member_id']] = true;
    }
    $results = [];
    foreach (chat_people($db,$companyId) as $person) {
        if ($conversation && !isset($members[$person['role'] . ':' . $person['id']])) continue;
        if (stripos($person['name'],$query) === false) continue;
        $results[] = $person + ['type'=>'person','label'=>$person['name'] . ' · ' . $person['role_label']];
        if (count($results) >= 8) break;
    }
    return $results;
}

function chat_record_reference(mysqli $db, array $user, string $moduleKey, int $recordId): ?array {
    $identity = chat_identity($user);
    $accessible = visible_modules($db,$user);
    if (!isset($accessible[$moduleKey]) || $moduleKey === 'messages' || $recordId < 1) return null;
    $module = module_config($moduleKey);
    $table = $module['table'];
    $pk = $module['pk'];
    $archive = in_array($moduleKey,['employees','managers','tasks','projects'],true) ? ' AND archived_at IS NULL' : '';
    $record = db_one($db,"SELECT * FROM `$table` WHERE `$pk` = ? AND company_id = ?$archive",[$recordId,$identity['company_id']]);
    if (!$record) return null;
    return [
        'type'=>'record',
        'module_key'=>$moduleKey,
        'ref_id'=>(string)$recordId,
        'label'=>$module['title'] . ' · ' . record_title($module,$record),
        'target_url'=>'/users/admin/workspace.php?module=' . rawurlencode($moduleKey) . '&action=view&id=' . $recordId,
    ];
}

function chat_resolve_reference(mysqli $db, array $user, array $reference, int $conversationId, int $messageId): array {
    $identity = chat_identity($user);
    $type = is_string($reference['type'] ?? null) ? $reference['type'] : '';
    if ($type === 'person') {
        $role = is_string($reference['role'] ?? null) ? $reference['role'] : '';
        $id = filter_var($reference['id'] ?? null,FILTER_VALIDATE_INT,['options'=>['min_range'=>1]]);
        if (!$id) fail('Choose a valid teammate mention.',422);
        $target = chat_person($db,$identity['company_id'],$role,(int)$id);
        if (!$target || !chat_is_member($db,$identity['company_id'],$conversationId,$target)) fail('Only participants in this conversation can be mentioned.',403);
        return [
            'type'=>'person',
            'role'=>$target['role'],
            'ref_id'=>(string)$target['id'],
            'label'=>$target['name'],
            'target_url'=>null,
            'mention'=>['role'=>$target['role'],'id'=>$target['id'],'name'=>$target['name']],
        ];
    }
    if ($type === 'record') {
        $moduleKey = is_string($reference['module'] ?? null) ? $reference['module'] : '';
        $id = filter_var($reference['id'] ?? null,FILTER_VALIDATE_INT,['options'=>['min_range'=>1]]);
        if (!$id) fail('Choose a valid workspace record.',422);
        $record = chat_record_reference($db,$user,$moduleKey,(int)$id);
        if (!$record) fail('This record is unavailable or you do not have access to it.',403);
        return $record + ['role'=>null,'mention'=>null];
    }
    fail('Choose a valid workspace reference.',422);
}

function chat_message_payload(mysqli $db, array $message, array $identity): array {
    $sender = chat_person($db,(int)$identity['company_id'],(string)$message['sender_role'],(int)$message['sender_id']);
    $references = [];
    foreach (db_rows($db,'SELECT token, ref_type, ref_role, ref_id, module_key, label, target_url FROM chat_message_refs WHERE company_id = ? AND message_id = ?',[$identity['company_id'],$message['id']]) as $reference) {
        $reference['target_url'] = $reference['ref_type'] === 'person' ? null : $reference['target_url'];
        $references[] = $reference;
    }
    $quoteUrl = null;
    if (!empty($message['quote_conversation_id']) && chat_is_member($db,(int)$identity['company_id'],(int)$message['quote_conversation_id'],$identity)) {
        $quoteUrl = '/users/admin/messages.php?conversation=' . (int)$message['quote_conversation_id'];
    }
    return [
        'id'=>(int)$message['id'],
        'sender_name'=>$sender['name'] ?? 'Former team member',
        'is_me'=>$message['sender_role'] === $identity['role'] && (int)$message['sender_id'] === $identity['id'],
        'body'=>(string)$message['body'],
        'quote_text'=>$message['quote_text'] ?? null,
        'quote_url'=>$quoteUrl,
        'created_at'=>$message['created_at'],
        'references'=>$references,
    ];
}

function chat_api(mysqli $db, array $user, array $identity): never {
    header('Content-Type: application/json; charset=utf-8');
    $companyId = (int)$identity['company_id'];
    $action = is_string($_GET['action'] ?? null) ? $_GET['action'] : '';
    if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
        require_post();
        chat_require_access($db,$user,true);
    }

    if ($action === 'list') json_response(['conversations'=>chat_conversation_list($db,$identity)]);

    if ($action === 'thread') {
        $conversationId = filter_var($_GET['conversation'] ?? null,FILTER_VALIDATE_INT,['options'=>['min_range'=>1]]);
        $conversation = $conversationId ? chat_conversation($db,$companyId,(int)$conversationId,$identity) : null;
        if (!$conversation) fail('This conversation could not be found.',404);
        $rows = db_rows($db,'SELECT * FROM chat_messages WHERE company_id = ? AND conversation_id = ? ORDER BY id DESC LIMIT 100',[$companyId,(int)$conversation['id']]);
        $rows = array_reverse($rows);
        $messages = array_map(fn(array $message): array => chat_message_payload($db,$message,$identity),$rows);
        json_response(['conversation'=>['id'=>(int)$conversation['id'],'title'=>chat_conversation_title($db,$conversation,$identity),'kind'=>$conversation['kind']],'messages'=>$messages]);
    }

    if ($action === 'search') {
        $query = trim(is_string($_GET['q'] ?? null) ? $_GET['q'] : '');
        if (mb_strlen($query) < 1 || mb_strlen($query) > 80) json_response(['results'=>[]]);
        $conversation = null;
        if (isset($_GET['conversation'])) {
            $conversationId = filter_var($_GET['conversation'],FILTER_VALIDATE_INT,['options'=>['min_range'=>1]]);
            $conversation = $conversationId ? chat_conversation($db,$companyId,(int)$conversationId,$identity) : null;
            if (!$conversation) fail('This conversation could not be found.',404);
        }
        $results = chat_search_people($db,$companyId,$query,$conversation ?? []);
        $accessible = visible_modules($db,$user);
        foreach ($accessible as $key=>$module) {
            if ($key === 'messages' || count($results) >= 24) continue;
            $column = $module['columns'][0] ?? '';
            if (!preg_match('/^[A-Za-z_][A-Za-z0-9_]*$/D',$column)) continue;
            $table = $module['table']; $pk = $module['pk'];
            $archive = in_array($key,['employees','managers','tasks','projects'],true) ? ' AND archived_at IS NULL' : '';
            $rows = db_rows($db,"SELECT * FROM `$table` WHERE company_id = ? AND `$column` LIKE ?$archive ORDER BY `$pk` DESC LIMIT 8",[$companyId,'%' . $query . '%']);
            foreach ($rows as $record) {
                $results[] = ['type'=>'record','module'=>$key,'id'=>(int)$record[$pk],'label'=>$module['title'] . ' · ' . record_title($module,$record)];
                if (count($results) >= 24) break;
            }
        }
        json_response(['results'=>$results]);
    }

    if ($action === 'create_direct') {
        $role = is_string($_POST['role'] ?? null) ? $_POST['role'] : '';
        $id = filter_var($_POST['id'] ?? null,FILTER_VALIDATE_INT,['options'=>['min_range'=>1]]);
        $target = $id ? chat_person($db,$companyId,$role,(int)$id) : null;
        if (!$target || ($target['role'] === $identity['role'] && $target['id'] === $identity['id'])) fail('Choose another active teammate.',422);
        $first = $identity['role'] . ':' . $identity['id'];
        $second = $target['role'] . ':' . $target['id'];
        $directKey = hash('sha256',strcmp($first,$second) < 0 ? $first . '|' . $second : $second . '|' . $first);
        $db->begin_transaction();
        try {
            db_run($db,'INSERT IGNORE INTO chat_conversations (company_id,kind,direct_key,created_by_role,created_by_id) VALUES (?,\'direct\',?,?,?)',[$companyId,$directKey,$identity['role'],$identity['id']]);
            $conversation = db_one($db,'SELECT * FROM chat_conversations WHERE company_id = ? AND direct_key = ? FOR UPDATE',[$companyId,$directKey]);
            db_run($db,'INSERT IGNORE INTO chat_members (company_id,conversation_id,member_role,member_id) VALUES (?,?,?,?),(?,?,?,?)',[$companyId,$conversation['id'],$identity['role'],$identity['id'],$companyId,$conversation['id'],$target['role'],$target['id']]);
            $db->commit();
        } catch (Throwable $error) { $db->rollback(); throw $error; }
        json_response(['ok'=>true,'conversation_id'=>(int)$conversation['id']]);
    }

    if ($action === 'create_group') {
        $name = trim(is_string($_POST['name'] ?? null) ? $_POST['name'] : '');
        if (mb_strlen($name) > 120) fail('Use a group name of at most 120 characters.',422);
        $members = json_decode(is_string($_POST['members'] ?? null) ? $_POST['members'] : '[]',true);
        if (!is_array($members) || count($members) > 29) fail('Choose up to 29 teammates for this group.',422);
        $selected = [$identity['role'] . ':' . $identity['id'] => $identity];
        foreach ($members as $member) {
            if (!is_array($member)) fail('Choose valid group members.',422);
            $role = is_string($member['role'] ?? null) ? $member['role'] : '';
            $id = filter_var($member['id'] ?? null,FILTER_VALIDATE_INT,['options'=>['min_range'=>1]]);
            $person = $id ? chat_person($db,$companyId,$role,(int)$id) : null;
            if (!$person) fail('A selected group member is no longer active in this workspace.',422);
            $selected[$person['role'] . ':' . $person['id']] = $person;
        }
        if (count($selected) < 3) fail('Choose at least two other teammates for a group chat.',422);
        $db->begin_transaction();
        try {
            db_run($db,'INSERT INTO chat_conversations (company_id,kind,name,created_by_role,created_by_id) VALUES (?,\'group\',?,?,?)',[$companyId,$name !== '' ? $name : null,$identity['role'],$identity['id']]);
            $conversationId = (int)$db->insert_id;
            foreach ($selected as $person) db_run($db,'INSERT INTO chat_members (company_id,conversation_id,member_role,member_id) VALUES (?,?,?,?)',[$companyId,$conversationId,$person['role'],$person['id']]);
            $db->commit();
        } catch (Throwable $error) { $db->rollback(); throw $error; }
        json_response(['ok'=>true,'conversation_id'=>$conversationId]);
    }

    if ($action === 'send') {
        $conversationId = filter_var($_POST['conversation'] ?? null,FILTER_VALIDATE_INT,['options'=>['min_range'=>1]]);
        $conversation = $conversationId ? chat_conversation($db,$companyId,(int)$conversationId,$identity) : null;
        if (!$conversation) fail('This conversation could not be found.',404);
        $body = trim(is_string($_POST['body'] ?? null) ? $_POST['body'] : '');
        if ($body === '' || mb_strlen($body) > 4000) fail('Write a message up to 4,000 characters.',422);
        $refs = json_decode(is_string($_POST['references'] ?? null) ? $_POST['references'] : '[]',true);
        if (!is_array($refs) || count($refs) > 20) fail('A message can contain up to 20 references.',422);
        $db->begin_transaction();
        try {
            db_run($db,'INSERT INTO chat_messages (company_id,conversation_id,sender_role,sender_id,body) VALUES (?,?,?,?,?)',[$companyId,$conversationId,$identity['role'],$identity['id'],$body]);
            $messageId = (int)$db->insert_id;
            $mentions = [];
            $usedPositions = [];
            foreach ($refs as $index=>$reference) {
                if (!is_array($reference)) fail('Choose valid references from the picker.',422);
                $resolved = chat_resolve_reference($db,$user,$reference,(int)$conversationId,$messageId);
                $needle = '@' . $resolved['label'];
                $position = strpos($body,$needle);
                while ($position !== false && isset($usedPositions[$position])) $position = strpos($body,$needle,$position + 1);
                if ($position === false) fail('A selected reference is missing from the message text.',422);
                $usedPositions[$position] = true;
                $token = bin2hex(random_bytes(16));
                $body = substr_replace($body,'[[ref:' . $token . ']]',$position,strlen($needle));
                db_run($db,'INSERT INTO chat_message_refs (company_id,message_id,token,ref_type,ref_role,ref_id,module_key,label,target_url) VALUES (?,?,?,?,?,?,?,?,?)',[$companyId,$messageId,$token,$resolved['type'],$resolved['role'] ?? null,$resolved['ref_id'],$resolved['module_key'] ?? null,$resolved['label'],$resolved['target_url'] ?? null]);
                if (!empty($resolved['mention'])) $mentions[$resolved['mention']['role'] . ':' . $resolved['mention']['id']] = $resolved['mention'];
            }
            db_run($db,'UPDATE chat_messages SET body = ? WHERE id = ? AND company_id = ?',[$body,$messageId,$companyId]);
            db_run($db,'UPDATE chat_conversations SET updated_at = UTC_TIMESTAMP() WHERE id = ? AND company_id = ?',[$conversationId,$companyId]);
            $db->commit();
        } catch (Throwable $error) { $db->rollback(); throw $error; }
        foreach ($mentions as $mention) {
            if ($mention['role'] === $identity['role'] && $mention['id'] === $identity['id']) continue;
            notify($db,$companyId,$mention['role'],$mention['id'],'message.mention','You were mentioned in a message',chat_conversation_title($db,$conversation,$identity),'/users/admin/messages.php?conversation=' . $conversationId,false,'chat-mention:' . $messageId . ':' . $mention['role'] . ':' . $mention['id'],false);
        }
        json_response(['ok'=>true,'id'=>$messageId]);
    }

    if ($action === 'share') {
        $sourceId = filter_var($_POST['message_id'] ?? null,FILTER_VALIDATE_INT,['options'=>['min_range'=>1]]);
        $targetId = filter_var($_POST['conversation'] ?? null,FILTER_VALIDATE_INT,['options'=>['min_range'=>1]]);
        $source = $sourceId ? db_one($db,'SELECT m.*, c.name AS conversation_name FROM chat_messages m JOIN chat_conversations c ON c.company_id = m.company_id AND c.id = m.conversation_id JOIN chat_members mine ON mine.company_id = m.company_id AND mine.conversation_id = m.conversation_id AND mine.member_role = ? AND mine.member_id = ? WHERE m.company_id = ? AND m.id = ?',[$identity['role'],$identity['id'],$companyId,$sourceId]) : null;
        $target = $targetId ? chat_conversation($db,$companyId,(int)$targetId,$identity) : null;
        if (!$source || !$target) fail('Choose a message and a conversation you can access.',403);
        $quote = (string)$source['body'];
        foreach (db_rows($db,'SELECT token,label FROM chat_message_refs WHERE company_id = ? AND message_id = ?',[$companyId,$sourceId]) as $reference) $quote = str_replace('[[ref:' . $reference['token'] . ']]','@' . $reference['label'],$quote);
        $db->begin_transaction();
        try {
            db_run($db,'INSERT INTO chat_messages (company_id,conversation_id,sender_role,sender_id,body,quote_text,quote_conversation_id) VALUES (?,?,?,?,?,?,?)',[$companyId,$targetId,$identity['role'],$identity['id'],'',(string)$quote,(int)$source['conversation_id']]);
            $messageId = (int)$db->insert_id;
            db_run($db,'UPDATE chat_conversations SET updated_at = UTC_TIMESTAMP() WHERE id = ? AND company_id = ?',[$targetId,$companyId]);
            $db->commit();
        } catch (Throwable $error) { $db->rollback(); throw $error; }
        json_response(['ok'=>true,'id'=>$messageId]);
    }

    fail('Choose a valid messaging action.',422);
}
