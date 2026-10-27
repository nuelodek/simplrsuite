<?php
declare(strict_types=1);
require_once __DIR__ . '/modules.php';
require_once __DIR__ . '/secrets.php';
final class GitHubUnavailable extends RuntimeException {}
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
function github_redirect_uri(): string { return rtrim(config('APP_URL','http://127.0.0.1:8000'),'/') . '/users/admin/github.php?oauth=callback'; }
function github_http(string $method, string $url, array $headers, ?string $body = null): array {
    $handle = curl_init($url);
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
function github_connection(mysqli $db, int $companyId): ?array {
    return db_one($db,"SELECT * FROM integration_connections WHERE company_id = ? AND provider = 'github'",[$companyId]);
}
function github_token(array $connection): string { return decrypt_secret($connection['token_ciphertext']); }
// Confirms a token belongs to a real account, then stores it encrypted for the workspace.
function github_connect(mysqli $db, array $user, string $token, string $authType): array {
    if (!preg_match('/^[A-Za-z0-9_\-]{20,255}$/D',$token)) throw new GitHubUnavailable('Paste the full GitHub token. It starts with github_pat_, ghp_ or gho_.');
    $account = github_api('GET','/user',$token);
    $profile = $account['data'];
    if (!is_string($profile['login'] ?? null) || $profile['login'] === '') throw new GitHubUnavailable('GitHub did not return an account for this token.');
    $companyId = (int)$user['company_id'];
    db_run($db,"INSERT INTO integration_connections (company_id,provider,auth_type,account_login,account_name,account_url,token_ciphertext,token_scopes,connected_by,last_checked_at,last_error) VALUES (?,'github',?,?,?,?,?,?,?,UTC_TIMESTAMP(),'') ON DUPLICATE KEY UPDATE auth_type = VALUES(auth_type), account_login = VALUES(account_login), account_name = VALUES(account_name), account_url = VALUES(account_url), token_ciphertext = VALUES(token_ciphertext), token_scopes = VALUES(token_scopes), connected_by = VALUES(connected_by), connected_at = CURRENT_TIMESTAMP, last_checked_at = UTC_TIMESTAMP(), last_error = ''",[
        $companyId,$authType,mb_substr($profile['login'],0,100),mb_substr((string)($profile['name'] ?? ''),0,160),github_safe_url($profile['html_url'] ?? ''),encrypt_secret($token),mb_substr($account['scopes'],0,255),(int)$user['id'],
    ]);
    github_cache_clear($db,$companyId);
    audit_event($db,$companyId,'integration.connected','integration','github',['account'=>$profile['login'],'method'=>$authType]);
    return $profile;
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
    $key = hash('sha256',$path . '?' . http_build_query($query));
    $hit = db_one($db,'SELECT payload FROM integration_cache WHERE company_id = ? AND cache_key = ? AND expires_at > UTC_TIMESTAMP()',[$companyId,$key]);
    if ($hit) return json_decode($hit['payload'],true) ?: [];
    try {
        $data = github_api('GET',$path,github_token($connection),$query)['data'];
    } catch (GitHubUnavailable $error) {
        db_run($db,"UPDATE integration_connections SET last_error = ?, last_checked_at = UTC_TIMESTAMP() WHERE company_id = ? AND provider = 'github'",[mb_substr($error->getMessage(),0,255),$companyId]);
        throw $error;
    }
    db_run($db,'REPLACE INTO integration_cache (company_id,cache_key,payload,expires_at) VALUES (?,?,?,UTC_TIMESTAMP() + INTERVAL ? SECOND)',[$companyId,$key,json_encode($data,JSON_INVALID_UTF8_SUBSTITUTE),$seconds]);
    return $data;
}
function github_repositories(mysqli $db, array $connection): array {
    $repos = [];
    foreach (github_cached($db,$connection,'/user/repos',['per_page'=>100,'sort'=>'pushed','affiliation'=>'owner,collaborator,organization_member']) as $repo) {
        if (!is_array($repo) || !is_string($repo['full_name'] ?? null) || !github_valid_repo($repo['full_name'])) continue;
        $repos[] = ['full_name'=>$repo['full_name'],'private'=>!empty($repo['private']),'description'=>(string)($repo['description'] ?? ''),'pushed_at'=>(string)($repo['pushed_at'] ?? ''),'language'=>(string)($repo['language'] ?? '')];
    }
    return $repos;
}
function github_link_repository(mysqli $db, array $user, int $projectId, string $fullName): void {
    $companyId = (int)$user['company_id'];
    if (!github_valid_repo($fullName)) throw new GitHubUnavailable('Choose a repository in owner/name form.');
    if (!db_one($db,'SELECT id FROM projects WHERE id = ? AND company_id = ?',[$projectId,$companyId])) throw new GitHubUnavailable('Choose a project from your workspace.');
    $connection = github_connection($db,$companyId) ?? throw new GitHubUnavailable('Connect GitHub before linking a repository.');
    $repo = github_api('GET',github_repo_path($fullName),github_token($connection))['data'];
    if (!is_string($repo['full_name'] ?? null) || !github_valid_repo($repo['full_name'])) throw new GitHubUnavailable('GitHub did not return this repository.');
    db_run($db,'INSERT INTO github_repository_links (company_id,project_id,repo_full_name,repo_id,html_url,is_private,default_branch,description,linked_by) VALUES (?,?,?,?,?,?,?,?,?) ON DUPLICATE KEY UPDATE repo_id = VALUES(repo_id), html_url = VALUES(html_url), is_private = VALUES(is_private), default_branch = VALUES(default_branch), description = VALUES(description)',[
        $companyId,$projectId,$repo['full_name'],(int)($repo['id'] ?? 0),github_safe_url($repo['html_url'] ?? ''),!empty($repo['private']) ? 1 : 0,mb_substr((string)($repo['default_branch'] ?? ''),0,120),mb_substr((string)($repo['description'] ?? ''),0,500),(int)$user['id'],
    ]);
    audit_event($db,$companyId,'github.repository_linked','projects',(string)$projectId,['repository'=>$repo['full_name']]);
}
function github_project_links(mysqli $db, int $companyId, int $projectId): array {
    return db_rows($db,'SELECT * FROM github_repository_links WHERE company_id = ? AND project_id = ? ORDER BY repo_full_name',[$companyId,$projectId]);
}
// Open issues become tasks once; tasks whose issues were closed are completed.
function github_sync_issues(mysqli $db, array $user, int $linkId): array {
    $companyId = (int)$user['company_id'];
    $link = db_one($db,'SELECT l.*, p.unique_id AS project_code, p.endDate FROM github_repository_links l JOIN projects p ON p.id = l.project_id AND p.company_id = l.company_id WHERE l.id = ? AND l.company_id = ?',[$linkId,$companyId]) ?? throw new GitHubUnavailable('This repository link could not be found.');
    $connection = github_connection($db,$companyId) ?? throw new GitHubUnavailable('Connect GitHub before syncing issues.');
    $token = github_token($connection);
    $tasks = module_config('tasks');
    $imported = 0; $completed = 0; $skipped = 0;
    $open = github_api('GET',github_repo_path($link['repo_full_name']) . '/issues',$token,['state'=>'open','per_page'=>50,'sort'=>'created','direction'=>'asc'])['data'];
    foreach ($open as $issue) {
        if (!is_array($issue) || isset($issue['pull_request']) || !is_int($issue['number'] ?? null)) continue;
        if (db_one($db,'SELECT id FROM github_issue_tasks WHERE company_id = ? AND link_id = ? AND issue_number = ?',[$companyId,$linkId,$issue['number']])) continue;
        $labels = strtolower(implode(' ',array_map(fn($label)=>is_array($label) ? (string)($label['name'] ?? '') : '',$issue['labels'] ?? [])));
        $priority = preg_match('/urgent|critical|high|p0|p1|blocker/',$labels) ? 'high' : (preg_match('/low|minor|p3|nice/',$labels) ? 'low' : 'medium');
        $due = substr((string)($issue['milestone']['due_on'] ?? ''),0,10);
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/D',$due)) $due = $link['endDate'] && $link['endDate'] >= date('Y-m-d') ? $link['endDate'] : date('Y-m-d',strtotime('+14 days'));
        $url = github_safe_url($issue['html_url'] ?? '');
        $body = trim((string)($issue['body'] ?? ''));
        $description = rich_text_from_plain(mb_substr($body,0,6000)) . ($url !== '' ? '<p><a href="' . e($url) . '">View issue #' . (int)$issue['number'] . ' on GitHub</a></p>' : '');
        [$values,$errors] = validate_record($db,$companyId,$tasks,['title'=>mb_substr('#' . $issue['number'] . ' ' . trim((string)($issue['title'] ?? 'GitHub issue')),0,255),'project_id'=>(string)$link['project_code'],'description'=>$description,'due_date'=>$due,'priority'=>$priority,'status'=>empty($issue['assignees']) ? 'pending' : 'ongoing','assignee'=>'']);
        if ($errors) { $skipped++; continue; }
        $taskId = save_record($db,$companyId,'tasks',$values,null);
        db_run($db,'INSERT INTO github_issue_tasks (company_id,link_id,issue_number,issue_url,task_id) VALUES (?,?,?,?,?)',[$companyId,$linkId,$issue['number'],$url,$taskId]);
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
    db_run($db,'UPDATE github_repository_links SET last_synced_at = UTC_TIMESTAMP() WHERE id = ? AND company_id = ?',[$linkId,$companyId]);
    github_cache_clear($db,$companyId);
    audit_event($db,$companyId,'github.issues_synced','projects',(string)$link['project_id'],['repository'=>$link['repo_full_name'],'imported'=>$imported,'completed'=>$completed]);
    return ['imported'=>$imported,'completed'=>$completed,'skipped'=>$skipped];
}
