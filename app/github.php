<?php
declare(strict_types=1);
require_once __DIR__ . '/modules.php';
require_once __DIR__ . '/secrets.php';
require_once __DIR__ . '/outbound.php';
require_once __DIR__ . '/notifications.php';
class GitHubUnavailable extends RuntimeException {}
// Another sync of the same repository link is still running.
final class GitHubSyncBusy extends GitHubUnavailable {}
// API and OAuth hosts come only from server configuration. Plain HTTP is accepted
// for a loopback stub in local development; everything else must use HTTPS.
function github_base(string $key, string $default): string {
    $base = rtrim(config($key,$default),'/');
    $parts = parse_url($base);
    $loopback = in_array($parts['host'] ?? '',['127.0.0.1','localhost'],true) && config('APP_ENV') === 'local';
    if (!filter_var($base,FILTER_VALIDATE_URL) || isset($parts['user']) || isset($parts['pass']) || !(($parts['scheme'] ?? '') === 'https' || (($parts['scheme'] ?? '') === 'http' && $loopback))) throw new GitHubUnavailable('The GitHub address for this server is not configured correctly.');
    return $base;
}
function github_api_base(): string { return github_base('GITHUB_API_URL','https://api.github.com'); }
function github_web_base(): string { return github_base('GITHUB_OAUTH_URL','https://github.com'); }
function github_oauth_ready(): bool { return config('GITHUB_CLIENT_ID') !== '' && config('GITHUB_CLIENT_SECRET') !== '' && extension_loaded('curl'); }
// Register exactly this address as the OAuth app's authorization callback URL.
function github_redirect_uri(): string { return rtrim(config('APP_URL','http://127.0.0.1:8000'),'/') . '/users/admin/github.php'; }
function github_authorize_url(string $state): string {
    return github_web_base() . '/login/oauth/authorize?' . http_build_query(['client_id'=>config('GITHUB_CLIENT_ID'),'redirect_uri'=>github_redirect_uri(),'scope'=>'repo read:user','state'=>$state,'allow_signup'=>'false']);
}
// Exchanges the sign-in code for an access token, explaining GitHub's common setup errors.
function github_exchange_code(string $code): string {
    if (!preg_match('/^[A-Za-z0-9_\-]{8,128}$/D',$code)) throw new GitHubUnavailable('GitHub did not return a valid sign-in code. Please try again.');
    [$status,,$raw] = github_http('POST',github_web_base() . '/login/oauth/access_token',['Accept: application/json','Content-Type: application/x-www-form-urlencoded','User-Agent: Simplr-Suite'],http_build_query(['client_id'=>config('GITHUB_CLIENT_ID'),'client_secret'=>config('GITHUB_CLIENT_SECRET'),'code'=>$code,'redirect_uri'=>github_redirect_uri()]));
    $grant = json_decode($raw,true);
    if ($status === 200 && is_string($grant['access_token'] ?? null)) return $grant['access_token'];
    $error = is_array($grant) ? (string)($grant['error'] ?? '') : '';
    if ($error !== '') error_log('Simplr: GitHub OAuth exchange failed (' . preg_replace('/[^a-z_]/','',$error) . ')');
    throw new GitHubUnavailable(match ($error) {
        'incorrect_client_credentials' => 'Simplr’s GitHub connection is not set up correctly yet. Please contact Simplr support.',
        'redirect_uri_mismatch' => 'GitHub sign-in is not set up for this web address yet. Please contact Simplr support.',
        'bad_verification_code' => 'That GitHub sign-in expired. Please click Connect GitHub again.',
        default => 'GitHub did not complete the sign-in. Please try again.',
    });
}
// Revokes Simplr's access on GitHub when a workspace disconnects (best effort).
function github_revoke_grant(string $token): void {
    if (!github_oauth_ready()) return;
    try {
        $handle = secure_curl(curl_init(github_api_base() . '/applications/' . rawurlencode(config('GITHUB_CLIENT_ID')) . '/grant'));
        curl_setopt_array($handle,[CURLOPT_RETURNTRANSFER=>true,CURLOPT_CUSTOMREQUEST=>'DELETE',CURLOPT_USERPWD=>config('GITHUB_CLIENT_ID') . ':' . config('GITHUB_CLIENT_SECRET'),CURLOPT_HTTPHEADER=>['Accept: application/vnd.github+json','Content-Type: application/json','User-Agent: Simplr-Suite','X-GitHub-Api-Version: 2022-11-28'],CURLOPT_POSTFIELDS=>json_encode(['access_token'=>$token]),CURLOPT_CONNECTTIMEOUT=>5,CURLOPT_TIMEOUT=>10]);
        curl_exec($handle); curl_close($handle);
    } catch (Throwable) { /* Disconnecting in Simplr must succeed even if GitHub is unreachable. */ }
}
function github_http(string $method, string $url, array $headers, ?string $body = null): array {
    $handle = secure_curl(curl_init($url));
    $responseHeaders = [];
    curl_setopt_array($handle,[CURLOPT_RETURNTRANSFER=>true,CURLOPT_CUSTOMREQUEST=>$method,CURLOPT_HTTPHEADER=>$headers,CURLOPT_CONNECTTIMEOUT=>5,CURLOPT_TIMEOUT=>12,CURLOPT_FOLLOWLOCATION=>false,CURLOPT_PROTOCOLS=>CURLPROTO_HTTPS | CURLPROTO_HTTP,CURLOPT_HEADERFUNCTION=>function ($curl, string $line) use (&$responseHeaders): int {
        if (str_contains($line,':')) { [$name,$value] = explode(':',$line,2); $responseHeaders[strtolower(trim($name))] = trim($value); }
        return strlen($line);
    }]);
    if ($body !== null) curl_setopt($handle,CURLOPT_POSTFIELDS,$body);
    $raw = curl_exec($handle); $status = (int)curl_getinfo($handle,CURLINFO_RESPONSE_CODE); $error = curl_errno($handle); curl_close($handle);
    if (!is_string($raw) || $error) throw new GitHubUnavailable('GitHub could not be reached. Check the server’s internet connection and try again.');
    if (strlen($raw) > 4 * 1048576) throw new GitHubUnavailable('GitHub returned more data than expected.');
    return [$status,$responseHeaders,$raw];
}
function github_api(string $method, string $path, string $token, array $query = [], ?array $body = null): array {
    $url = github_api_base() . $path . ($query ? '?' . http_build_query($query) : '');
    $headers = ['Accept: application/vnd.github+json','X-GitHub-Api-Version: 2022-11-28','User-Agent: Simplr-Suite','Authorization: Bearer ' . $token];
    if ($body !== null) $headers[] = 'Content-Type: application/json';
    [$status,$responseHeaders,$raw] = github_http($method,$url,$headers,$body !== null ? json_encode($body,JSON_THROW_ON_ERROR) : null);
    $data = json_decode($raw,true);
    if ($status === 401) throw new GitHubUnavailable('GitHub rejected the saved access. Reconnect GitHub in Integrations.');
    if ($status === 403 && ($responseHeaders['x-ratelimit-remaining'] ?? '') === '0') throw new GitHubUnavailable('GitHub’s rate limit was reached. Try again in a few minutes.');
    if ($status === 403) throw new GitHubUnavailable('GitHub did not allow this request. Check that the connection can access this repository.');
    if ($status === 404) throw new GitHubUnavailable('GitHub could not find this repository, or the connection cannot see it.');
    if ($status < 200 || $status >= 300 || !is_array($data)) throw new GitHubUnavailable('GitHub did not return a valid response. Try again shortly.');
    return ['data'=>$data,'scopes'=>$responseHeaders['x-oauth-scopes'] ?? ''];
}
// A workspace may connect several GitHub accounts; each repository link names the one it uses.
function github_connections(mysqli $db, int $companyId): array {
    return db_rows($db,"SELECT * FROM integration_connections WHERE company_id = ? AND provider = 'github' ORDER BY id",[$companyId]);
}
function github_connection(mysqli $db, int $companyId, ?int $connectionId = null): ?array {
    return $connectionId === null
        ? db_one($db,"SELECT * FROM integration_connections WHERE company_id = ? AND provider = 'github' ORDER BY id LIMIT 1",[$companyId])
        : db_one($db,"SELECT * FROM integration_connections WHERE id = ? AND company_id = ? AND provider = 'github'",[$connectionId,$companyId]);
}
function github_link_connection(mysqli $db, array $link): ?array {
    return $link['connection_id'] ? github_connection($db,(int)$link['company_id'],(int)$link['connection_id']) : null;
}
function github_token(array $connection): string { return decrypt_secret($connection['token_ciphertext']); }
// Confirms a token belongs to a real account, then stores it encrypted for the workspace.
// Connecting an account that is already connected refreshes its access instead of duplicating it.
function github_connect(mysqli $db, array $user, string $token, string $authType): array {
    if (!preg_match('/^[A-Za-z0-9_\-]{20,255}$/D',$token)) throw new GitHubUnavailable('Paste the full GitHub token. It starts with github_pat_, ghp_ or gho_.');
    $account = github_api('GET','/user',$token);
    $profile = $account['data'];
    if (!is_string($profile['login'] ?? null) || $profile['login'] === '') throw new GitHubUnavailable('GitHub did not return an account for this token.');
    $companyId = (int)$user['company_id'];
    $login = mb_substr($profile['login'],0,100);
    $existing = db_one($db,"SELECT id FROM integration_connections WHERE company_id = ? AND provider = 'github' AND account_login = ?",[$companyId,$login]);
    db_run($db,"INSERT INTO integration_connections (company_id,provider,auth_type,account_login,account_name,account_url,token_ciphertext,token_scopes,connected_by,last_checked_at,last_error) VALUES (?,'github',?,?,?,?,?,?,?,UTC_TIMESTAMP(),'') ON DUPLICATE KEY UPDATE auth_type = VALUES(auth_type), account_login = VALUES(account_login), account_name = VALUES(account_name), account_url = VALUES(account_url), token_ciphertext = VALUES(token_ciphertext), token_scopes = VALUES(token_scopes), connected_by = VALUES(connected_by), connected_at = CURRENT_TIMESTAMP, last_checked_at = UTC_TIMESTAMP(), last_error = ''",[
        $companyId,$authType,$login,mb_substr((string)($profile['name'] ?? ''),0,160),github_safe_url($profile['html_url'] ?? ''),encrypt_secret($token),mb_substr($account['scopes'],0,255),(int)$user['id'],
    ]);
    $connectionId = (int)db_one($db,"SELECT id FROM integration_connections WHERE company_id = ? AND provider = 'github' AND account_login = ?",[$companyId,$login])['id'];
    // Links left without an account (after a disconnect) resume through this account when it can see them.
    foreach (db_rows($db,'SELECT id, repo_full_name FROM github_repository_links WHERE company_id = ? AND connection_id IS NULL LIMIT 50',[$companyId]) as $orphan) {
        try { github_api('GET',github_repo_path($orphan['repo_full_name']),$token); } catch (GitHubUnavailable) { continue; }
        db_run($db,'UPDATE github_repository_links SET connection_id = ? WHERE id = ? AND company_id = ? AND connection_id IS NULL',[$connectionId,(int)$orphan['id'],$companyId]);
    }
    github_cache_clear($db,$companyId);
    audit_event($db,$companyId,'integration.connected','integration','github',['account'=>$login,'method'=>$authType,'refreshed'=>(bool)$existing]);
    return ['login'=>$login,'connection_id'=>$connectionId,'refreshed'=>(bool)$existing];
}
function github_safe_url(mixed $url): string {
    if (!is_string($url) || strlen($url) > 255 || !filter_var($url,FILTER_VALIDATE_URL)) return '';
    $web = parse_url(github_web_base(),PHP_URL_HOST); $parts = parse_url($url);
    return ($parts['host'] ?? '') === $web && in_array($parts['scheme'] ?? '',['https','http'],true) && !isset($parts['user']) ? $url : '';
}
function github_valid_repo(string $name): bool { return (bool)preg_match('~^[A-Za-z0-9](?:[A-Za-z0-9-]{0,38})/[A-Za-z0-9._-]{1,100}$~D',$name) && !str_contains($name,'..'); }
function github_repo_path(string $name): string { [$owner,$repo] = explode('/',$name,2); return '/repos/' . rawurlencode($owner) . '/' . rawurlencode($repo); }
function github_cache_clear(mysqli $db, int $companyId): void { db_run($db,'DELETE FROM integration_cache WHERE company_id = ?',[$companyId]); }
// Short-lived cache keeps project pages fast and well inside GitHub's rate limits.
function github_cached(mysqli $db, array $connection, string $path, array $query = [], int $seconds = 120): array {
    $companyId = (int)$connection['company_id'];
    $key = hash('sha256',$connection['id'] . '|' . $path . '?' . http_build_query($query));
    $hit = db_one($db,'SELECT payload FROM integration_cache WHERE company_id = ? AND cache_key = ? AND expires_at > UTC_TIMESTAMP()',[$companyId,$key]);
    if ($hit) return json_decode($hit['payload'],true) ?: [];
    try {
        $data = github_api('GET',$path,github_token($connection),$query)['data'];
    } catch (GitHubUnavailable $error) {
        db_run($db,'UPDATE integration_connections SET last_error = ?, last_checked_at = UTC_TIMESTAMP() WHERE id = ? AND company_id = ?',[mb_substr($error->getMessage(),0,255),(int)$connection['id'],$companyId]);
        throw $error;
    }
    if ($connection['last_error'] !== '') db_run($db,"UPDATE integration_connections SET last_error = '', last_checked_at = UTC_TIMESTAMP() WHERE id = ? AND company_id = ?",[(int)$connection['id'],$companyId]);
    db_run($db,'REPLACE INTO integration_cache (company_id,cache_key,payload,expires_at) VALUES (?,?,?,UTC_TIMESTAMP() + INTERVAL ? SECOND)',[$companyId,$key,json_encode($data,JSON_INVALID_UTF8_SUBSTITUTE),$seconds]);
    return $data;
}
function github_repositories(mysqli $db, array $connection): array {
    $repos = [];
    foreach (github_cached($db,$connection,'/user/repos',['per_page'=>100,'sort'=>'pushed','affiliation'=>'owner,collaborator,organization_member']) as $repo) {
        if (!is_array($repo) || !is_string($repo['full_name'] ?? null) || !github_valid_repo($repo['full_name'])) continue;
        $repos[] = ['full_name'=>$repo['full_name'],'private'=>!empty($repo['private']),'description'=>(string)($repo['description'] ?? ''),'pushed_at'=>(string)($repo['pushed_at'] ?? ''),'language'=>(string)($repo['language'] ?? ''),'connection_id'=>(int)$connection['id'],'via'=>$connection['account_login']];
    }
    return $repos;
}
// Every repository the workspace's accounts can see, once each (first account wins).
function github_all_repositories(mysqli $db, int $companyId): array {
    $repos = []; $errors = [];
    foreach (github_connections($db,$companyId) as $connection) {
        try {
            foreach (github_repositories($db,$connection) as $repo) $repos[strtolower($repo['full_name'])] ??= $repo;
        } catch (GitHubUnavailable $error) { $errors[$connection['account_login']] = $error->getMessage(); }
    }
    return [array_values($repos),$errors];
}
// Links a repository through the chosen account, or the first connected account that can see it.
function github_link_repository(mysqli $db, array $user, int $projectId, string $fullName, ?int $connectionId = null): array {
    $companyId = (int)$user['company_id'];
    if (!github_valid_repo($fullName)) throw new GitHubUnavailable('Choose a repository in owner/name form.');
    if (!db_one($db,'SELECT id FROM projects WHERE id = ? AND company_id = ?',[$projectId,$companyId])) throw new GitHubUnavailable('Choose a project from your workspace.');
    $candidates = $connectionId ? array_filter([github_connection($db,$companyId,$connectionId)]) : github_connections($db,$companyId);
    if (!$candidates) throw new GitHubUnavailable($connectionId ? 'Choose a GitHub account connected to this workspace.' : 'Connect GitHub before linking a repository.');
    $repo = null; $through = null; $lastError = null;
    foreach ($candidates as $candidate) {
        try { $repo = github_api('GET',github_repo_path($fullName),github_token($candidate))['data']; $through = $candidate; break; }
        catch (GitHubUnavailable $error) { $lastError = $error; }
    }
    if (!$through) throw count($candidates) > 1 ? new GitHubUnavailable('None of your connected GitHub accounts can see ' . $fullName . '. Connect an account that can, or ask an organisation owner to approve Simplr.') : $lastError;
    if (!is_string($repo['full_name'] ?? null) || !github_valid_repo($repo['full_name'])) throw new GitHubUnavailable('GitHub did not return this repository.');
    db_run($db,'INSERT INTO github_repository_links (company_id,project_id,connection_id,repo_full_name,repo_id,html_url,is_private,default_branch,description,linked_by) VALUES (?,?,?,?,?,?,?,?,?,?) ON DUPLICATE KEY UPDATE connection_id = VALUES(connection_id), repo_id = VALUES(repo_id), html_url = VALUES(html_url), is_private = VALUES(is_private), default_branch = VALUES(default_branch), description = VALUES(description)',[
        $companyId,$projectId,(int)$through['id'],$repo['full_name'],(int)($repo['id'] ?? 0),github_safe_url($repo['html_url'] ?? ''),!empty($repo['private']) ? 1 : 0,mb_substr((string)($repo['default_branch'] ?? ''),0,120),mb_substr((string)($repo['description'] ?? ''),0,500),(int)$user['id'],
    ]);
    audit_event($db,$companyId,'github.repository_linked','projects',(string)$projectId,['repository'=>$repo['full_name'],'account'=>$through['account_login']]);
    return ['repository'=>$repo['full_name'],'via'=>$through['account_login']];
}
function github_project_links(mysqli $db, int $companyId, int $projectId): array {
    return db_rows($db,'SELECT l.*, c.account_login FROM github_repository_links l LEFT JOIN integration_connections c ON c.id = l.connection_id AND c.company_id = l.company_id WHERE l.company_id = ? AND l.project_id = ? ORDER BY l.repo_full_name',[$companyId,$projectId]);
}
// Open issues become tasks once; tasks whose issues were closed are completed.
// One sync per repository link at a time. The scheduled job and a Sync click could
// otherwise both create a task for the same new issue; the second waits, then finds it.
function github_sync_issues(mysqli $db, array $user, int $linkId): array {
    $lock = 'simplr_github_link_' . $linkId;
    if ((int)(db_one($db,'SELECT GET_LOCK(?, 20) AS acquired',[$lock])['acquired'] ?? 0) !== 1) throw new GitHubSyncBusy('This repository is already syncing. Try again in a minute.');
    try {
        return github_sync_link($db,$user,$linkId);
    } finally {
        db_one($db,'SELECT RELEASE_LOCK(?) AS released',[$lock]);
    }
}
function github_sync_link(mysqli $db, array $user, int $linkId): array {
    $companyId = (int)$user['company_id'];
    $link = db_one($db,'SELECT l.*, p.unique_id AS project_code, p.endDate FROM github_repository_links l JOIN projects p ON p.id = l.project_id AND p.company_id = l.company_id WHERE l.id = ? AND l.company_id = ?',[$linkId,$companyId]) ?? throw new GitHubUnavailable('This repository link could not be found.');
    $connection = github_link_connection($db,$link) ?? throw new GitHubUnavailable('The GitHub account this repository used was disconnected. Link the repository again through a connected account.');
    $token = github_token($connection);
    $tasks = module_config('tasks');
    $imported = 0; $completed = 0; $skipped = 0; $assigned = 0; $unmatched = [];
    $people = github_people_map($db,$companyId);
    $open = github_api('GET',github_repo_path($link['repo_full_name']) . '/issues',$token,['state'=>'open','per_page'=>50,'sort'=>'created','direction'=>'asc'])['data'];
    foreach ($open as $issue) {
        if (!is_array($issue) || isset($issue['pull_request']) || !is_int($issue['number'] ?? null)) continue;
        [$login,$employeeId,$missing] = github_issue_assignee($issue,$people);
        foreach ($missing as $name) $unmatched[strtolower($name)] = $name;
        $existing = db_one($db,'SELECT g.id AS mapping_id, g.github_assignee, t.* FROM github_issue_tasks g JOIN tasks t ON t.id = g.task_id AND t.company_id = g.company_id WHERE g.company_id = ? AND g.link_id = ? AND g.issue_number = ?',[$companyId,$linkId,$issue['number']]);
        if ($existing) {
            // GitHub decides the assignee when it changes there, or when the task has nobody yet.
            $changedOnGitHub = strcasecmp($existing['github_assignee'],$login) !== 0;
            if ($existing['status'] !== 'completed' && $employeeId !== null && (string)$existing['assignee'] !== (string)$employeeId && ($changedOnGitHub || trim((string)$existing['assignee']) === '')) {
                $task = array_diff_key($existing,['mapping_id'=>0,'github_assignee'=>0]);
                $posted = array_map(fn($value) => is_scalar($value) ? (string)$value : $value, $task);
                $posted['assignee'] = (string)$employeeId;
                [$values,$errors] = validate_record($db,$companyId,$tasks,$posted);
                if ($errors) { $skipped++; continue; }
                save_record($db,$companyId,'tasks',$values,(int)$task['id'],record_version($task));
                github_notify_assignee($db,$companyId,$employeeId,(int)$task['id'],(string)$task['title'],$link['repo_full_name'],(string)$task['due_date']);
                $assigned++;
            }
            if ($changedOnGitHub) db_run($db,'UPDATE github_issue_tasks SET github_assignee = ? WHERE id = ?',[$login,(int)$existing['mapping_id']]);
            continue;
        }
        $labels = strtolower(implode(' ',array_map(fn($label)=>is_array($label) ? (string)($label['name'] ?? '') : '',$issue['labels'] ?? [])));
        $priority = preg_match('/urgent|critical|high|p0|p1|blocker/',$labels) ? 'high' : (preg_match('/low|minor|p3|nice/',$labels) ? 'low' : 'medium');
        $due = substr((string)($issue['milestone']['due_on'] ?? ''),0,10);
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/D',$due)) $due = $link['endDate'] && $link['endDate'] >= date('Y-m-d') ? $link['endDate'] : date('Y-m-d',strtotime('+14 days'));
        $url = github_safe_url($issue['html_url'] ?? '');
        $body = trim((string)($issue['body'] ?? ''));
        $description = rich_text_from_plain(mb_substr($body,0,6000)) . ($url !== '' ? '<p><a href="' . e($url) . '">View issue #' . (int)$issue['number'] . ' on GitHub</a></p>' : '');
        [$values,$errors] = validate_record($db,$companyId,$tasks,['title'=>mb_substr('#' . $issue['number'] . ' ' . trim((string)($issue['title'] ?? 'GitHub issue')),0,255),'project_id'=>(string)$link['project_code'],'description'=>$description,'due_date'=>$due,'priority'=>$priority,'status'=>empty($issue['assignees']) ? 'pending' : 'ongoing','assignee'=>$employeeId !== null ? (string)$employeeId : '']);
        if ($errors) { $skipped++; continue; }
        $taskId = save_record($db,$companyId,'tasks',$values,null);
        db_run($db,'INSERT INTO github_issue_tasks (company_id,link_id,issue_number,issue_url,task_id,github_assignee) VALUES (?,?,?,?,?,?)',[$companyId,$linkId,$issue['number'],$url,$taskId,$login]);
        if ($employeeId !== null) { github_notify_assignee($db,$companyId,$employeeId,$taskId,(string)$values['title'],$link['repo_full_name'],(string)$values['due_date']); $assigned++; }
        $imported++;
    }
    $closed = github_api('GET',github_repo_path($link['repo_full_name']) . '/issues',$token,['state'=>'closed','per_page'=>50,'sort'=>'updated','direction'=>'desc'])['data'];
    foreach ($closed as $issue) {
        if (!is_array($issue) || isset($issue['pull_request']) || !is_int($issue['number'] ?? null)) continue;
        $mapped = db_one($db,"SELECT t.* FROM github_issue_tasks g JOIN tasks t ON t.id = g.task_id AND t.company_id = g.company_id WHERE g.company_id = ? AND g.link_id = ? AND g.issue_number = ? AND t.status != 'completed'",[$companyId,$linkId,$issue['number']]);
        if (!$mapped) continue;
        $posted = array_map(fn($value) => is_scalar($value) ? (string)$value : $value, $mapped);
        $posted['status'] = 'completed';
        [$values,$errors] = validate_record($db,$companyId,$tasks,$posted);
        if ($errors) { $skipped++; continue; }
        save_record($db,$companyId,'tasks',$values,(int)$mapped['id'],record_version($mapped));
        $completed++;
    }
    db_run($db,"UPDATE github_repository_links SET last_synced_at = UTC_TIMESTAMP(), last_sync_error = '' WHERE id = ? AND company_id = ?",[$linkId,$companyId]);
    github_cache_clear($db,$companyId);
    $automatic = PHP_SAPI === 'cli';
    audit_event($db,$companyId,'github.issues_synced','projects',(string)$link['project_id'],['repository'=>$link['repo_full_name'],'imported'=>$imported,'completed'=>$completed,'assigned'=>$assigned,'automatic'=>$automatic],$automatic ? 'system' : null,$automatic ? 0 : null);
    return ['imported'=>$imported,'completed'=>$completed,'assigned'=>$assigned,'skipped'=>$skipped,'unmatched'=>array_values($unmatched)];
}
// GitHub username (lower case) => employee id, for people matched in this workspace.
function github_people_map(mysqli $db, int $companyId): array {
    $people = [];
    foreach (db_rows($db,"SELECT id, github_login FROM employees WHERE company_id = ? AND github_login IS NOT NULL AND github_login <> '' ORDER BY id",[$companyId]) as $row) $people[strtolower($row['github_login'])] ??= (int)$row['id'];
    return $people;
}
// The first issue assignee matched to an employee wins; returns [login, employee id or null, unmatched logins].
function github_issue_assignee(array $issue, array $people): array {
    $logins = [];
    foreach ($issue['assignees'] ?? [] as $assignee) if (is_array($assignee) && is_string($assignee['login'] ?? null) && $assignee['login'] !== '') $logins[] = $assignee['login'];
    foreach ($logins as $login) if (isset($people[strtolower($login)])) return [$login,$people[strtolower($login)],array_values(array_filter($logins,fn($other)=>!isset($people[strtolower($other)])))];
    return [$logins[0] ?? '',null,$logins];
}
function github_notify_assignee(mysqli $db, int $companyId, int $employeeId, int $taskId, string $title, string $repository, string $due): void {
    notify($db,$companyId,'employee',$employeeId,'task.assigned','New task from GitHub: ' . $title,'From ' . $repository . ($due !== '' ? ' · due ' . date('j M Y',strtotime($due)) : '') . '.','/users/employees/workspace.php#tasks',false,'github-assign:' . $taskId . ':' . $employeeId);
}
// Saves GitHub username matches for employees; a username belongs to one employee at a time.
function github_save_people(mysqli $db, array $user, array $logins, array $employees): int {
    $companyId = (int)$user['company_id']; $saved = 0;
    $db->begin_transaction();
    try {
        foreach ($logins as $index => $rawLogin) {
            $login = ltrim(trim(is_string($rawLogin) ? $rawLogin : ''),'@');
            if ($login === '') continue;
            if (!preg_match('/^[A-Za-z0-9](?:[A-Za-z0-9-]{0,38})$/D',$login)) { $db->rollback(); throw new GitHubUnavailable('“' . mb_strimwidth($login,0,40,'…') . '” is not a GitHub username.'); }
            $employee = is_string($employees[$index] ?? null) && ctype_digit($employees[$index]) ? (int)$employees[$index] : 0;
            if ($employee && !db_one($db,'SELECT id FROM employees WHERE id = ? AND company_id = ?',[$employee,$companyId])) { $db->rollback(); throw new GitHubUnavailable('Choose employees from your workspace.'); }
            db_run($db,'UPDATE employees SET github_login = NULL WHERE company_id = ? AND LOWER(github_login) = LOWER(?)' . ($employee ? ' AND id <> ?' : ''),$employee ? [$companyId,$login,$employee] : [$companyId,$login]);
            if ($employee) db_run($db,'UPDATE employees SET github_login = ? WHERE id = ? AND company_id = ?',[$login,$employee,$companyId]);
            $saved++;
        }
        audit_event($db,$companyId,'github.people_matched','integration','github',['count'=>$saved]);
        $db->commit();
    } catch (GitHubUnavailable $error) { throw $error; } catch (Throwable $error) { $db->rollback(); throw $error; }
    return $saved;
}
// GitHub usernames seen as assignees on linked repositories' open issues (from the short cache).
function github_seen_assignees(mysqli $db, int $companyId): array {
    $seen = [];
    foreach (db_rows($db,'SELECT * FROM github_repository_links WHERE company_id = ? AND connection_id IS NOT NULL',[$companyId]) as $link) {
        $connection = github_link_connection($db,$link);
        if (!$connection) continue;
        try { $issues = github_cached($db,$connection,github_repo_path($link['repo_full_name']) . '/issues',['state'=>'open','per_page'=>50]); } catch (GitHubUnavailable) { continue; }
        foreach ($issues as $issue) foreach ((is_array($issue) ? $issue['assignees'] ?? [] : []) as $assignee) if (is_array($assignee) && is_string($assignee['login'] ?? null) && preg_match('/^[A-Za-z0-9-]{1,39}$/D',$assignee['login'])) $seen[strtolower($assignee['login'])] = $assignee['login'];
    }
    ksort($seen);
    return array_values($seen);
}
