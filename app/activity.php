<?php
declare(strict_types=1);
require_once __DIR__ . '/modules.php';
// Turns audit rows into readable, company-scoped sentences. Names are looked up
// only inside the viewer's company; missing records fall back to a neutral label.
function activity_entries(mysqli $db, int $companyId, array $rows, int $viewerId): array {
    $modules = modules();
    $ids = ['employee'=>[],'manager'=>[]];
    $targets = [];
    foreach ($rows as $row) {
        if (isset($ids[$row['actor_role']])) $ids[$row['actor_role']][] = (int)$row['actor_id'];
        if (in_array($row['target_type'],['employee','manager'],true)) $ids[$row['target_type']][] = (int)$row['target_id'];
        if (isset($modules[$row['target_type']]) && ctype_digit((string)$row['target_id'])) $targets[$row['target_type']][] = (int)$row['target_id'];
    }
    $people = ['employee'=>[],'manager'=>[]];
    foreach (['employee'=>"SELECT id, CONCAT(first_name, ' ', last_name) AS name FROM employees",'manager'=>'SELECT id, manager_name AS name FROM managers'] as $role=>$sql) {
        $list = array_values(array_unique($ids[$role]));
        if (!$list) continue;
        foreach (db_rows($db,$sql . ' WHERE company_id = ? AND id IN (' . implode(',',array_fill(0,count($list),'?')) . ')',[$companyId,...$list]) as $person) $people[$role][(int)$person['id']] = trim($person['name']);
    }
    $titles = [];
    foreach ($targets as $key=>$list) {
        $module = $modules[$key]; $list = array_values(array_unique($list));
        foreach (db_rows($db,"SELECT * FROM `{$module['table']}` WHERE company_id = ? AND `{$module['pk']}` IN (" . implode(',',array_fill(0,count($list),'?')) . ')',[$companyId,...$list]) as $record) {
            $titles[$key][(int)$record[$module['pk']]] = $key === 'applications' ? ['title'=>$record['applicant_name'],'position'=>$record['position_applied_for']] : ['title'=>record_title($module,$record)];
        }
    }
    $entries = [];
    foreach ($rows as $row) {
        $role = $row['actor_role']; $actorId = (int)$row['actor_id'];
        $actor = match (true) {
            $role === 'admin' && $actorId === $viewerId => 'You',
            $role === 'admin' => 'The workspace owner',
            isset($people[$role][$actorId]) && $people[$role][$actorId] !== '' => $people[$role][$actorId],
            $role === 'talent' => 'A candidate',
            $role === 'system' => 'Simplr (automatically)',
            default => ucfirst($role ?: 'Someone'),
        };
        $key = $row['target_type']; $targetId = (int)$row['target_id'];
        $module = $modules[$key] ?? null;
        $target = $titles[$key][$targetId] ?? null;
        $name = $target ? '“' . $target['title'] . '”' : ($module ? 'a ' . $module['singular'] : 'a record');
        $details = json_decode((string)($row['details'] ?? ''),true) ?: [];
        $applicant = $target['title'] ?? 'a candidate';
        $text = match ($row['action']) {
            'record.saved' => $actor . ' saved ' . ($module ? $module['singular'] . ' ' : '') . ($target ? '“' . $target['title'] . '”' : '#' . $targetId),
            'access.updated' => $actor . ' changed tool access for ' . ($people[$key][$targetId] ?? 'a team member'),
            'application.submitted' => ($target ? $target['title'] : 'A candidate') . ' applied' . (!empty($target['position']) ? ' for ' . $target['position'] : ''),
            'application.withdrawn' => ($target ? $target['title'] : 'A candidate') . ' withdrew an application',
            'application.stage_changed' => $actor . ' moved ' . $applicant . (isset($details['to']) ? ' to ' . $details['to'] : ' to a new stage'),
            'application.interview_scheduled' => $actor . ' scheduled an interview with ' . $applicant,
            'application.interview_responded' => ($target ? $target['title'] : 'A candidate') . (isset($details['response']) ? ' ' . $details['response'] . ' an interview' : ' responded to an interview'),
            'application.interview_cancelled' => $actor . ' cancelled an interview with ' . $applicant,
            'attachment.added' => $actor . ' attached a file to ' . $name,
            'attachment.removed' => $actor . ' removed a file from ' . $name,
            'help.created' => $actor . ' sent a help request',
            'help.updated' => $actor . ' updated a help request',
            'settings.updated' => $actor . ' updated workspace settings',
            'job.social_sync_requested' => $actor . ' queued a social post for ' . $name,
            'integration.connected' => $actor . ' connected ' . (['github'=>'GitHub'][$row['target_id']] ?? ucfirst($row['target_id'])) . (isset($details['account']) ? ' as @' . $details['account'] : ''),
            'integration.disconnected' => $actor . ' disconnected ' . (['github'=>'GitHub'][$row['target_id']] ?? ucfirst($row['target_id'])) . (isset($details['account']) ? ' account @' . $details['account'] : ''),
            'github.repository_linked' => $actor . ' linked ' . ($details['repository'] ?? 'a repository') . ' to ' . $name,
            'github.repository_unlinked' => $actor . ' unlinked ' . ($details['repository'] ?? 'a repository') . ' from ' . $name,
            'github.issues_synced' => $actor . ' synced GitHub issues for ' . $name . (isset($details['imported']) ? ' (' . (int)$details['imported'] . ' new, ' . (int)($details['completed'] ?? 0) . ' completed' . (!empty($details['assigned']) ? ', ' . (int)$details['assigned'] . ' assigned' : '') . ')' : ''),
            'github.people_matched' => $actor . ' matched ' . (int)($details['count'] ?? 0) . ' GitHub ' . ((int)($details['count'] ?? 0) === 1 ? 'username' : 'usernames') . ' to employees',
            'github.auto_sync_changed' => $actor . ' turned automatic sync ' . (!empty($details['enabled']) ? 'on' : 'off') . ' for ' . ($details['repository'] ?? 'a repository'),
            'talent.shortlisted' => $actor . ' shortlisted a candidate',
            'talent.removed_from_shortlist' => $actor . ' removed a candidate from the shortlist',
            'talent.invited' => $actor . ' invited a candidate to apply',
            'talent.hire_offer_sent' => $actor . ' sent a direct hire offer' . (isset($details['role_title']) ? ' for ' . $details['role_title'] : ''),
            'talent.hire_offer_accepted' => 'A candidate accepted a hire offer',
            'talent.outreach_declined' => 'A candidate declined an invitation',
            default => $actor . ' ' . str_replace(['.','_'],' ',$row['action']),
        };
        $url = null;
        if ($module && $target) $url = $key === 'applications' ? '/users/admin/application.php?id=' . $targetId : '/users/admin/workspace.php?module=' . rawurlencode($key) . '&action=view&id=' . $targetId;
        $icon = match (explode('.',$row['action'])[0]) { 'github','integration' => 'github', 'application','talent','job' => 'briefcase', 'access' => 'shield', 'attachment' => 'folder', 'help' => 'help', 'settings' => 'settings', default => $module['icon'] ?? 'pulse' };
        $entries[] = ['text'=>$text,'url'=>$url,'icon'=>$icon,'time'=>$row['created_at'],'actor'=>$actor];
    }
    return $entries;
}
function activity_rows(mysqli $db, int $companyId, int $limit, int $offset = 0): array {
    return db_rows($db,'SELECT actor_role, actor_id, action, target_type, target_id, details, created_at FROM workspace_audit WHERE company_id = ? ORDER BY id DESC LIMIT ' . max(1,$limit) . ' OFFSET ' . max(0,$offset),[$companyId]);
}
