<?php
require_once __DIR__ . '/../../app/auth.php';
require_once __DIR__ . '/../../app/view.php';
require_once __DIR__ . '/../../app/github.php';
$db = database(); $user = workspace_user($db); $companyId = (int)$user['company_id'];
$isOwner = ($_SESSION['auth_role'] ?? '') === 'admin';
$projectAccess = module_access($db,$user,'projects');
if (!$isOwner && $projectAccess === '') fail('GitHub is available to people who can open Projects. Contact your workspace administrator.',403);
$canLink = $projectAccess === 'edit';
$oauth = is_string($_GET['oauth'] ?? null) ? $_GET['oauth'] : '';
if ($oauth === 'start') {
    if (!$isOwner) fail('Only the workspace owner can connect GitHub.',403);
    if (!github_oauth_ready()) fail('GitHub is being set up by Simplr and will be available soon.',409);
    $state = bin2hex(random_bytes(16));
    $_SESSION['github_oauth'] = ['state'=>$state,'expires'=>time() + 600];
    redirect(github_authorize_url($state));
}
// GitHub returns to the registered callback with ?code&state (or ?error&state).
if ($oauth === 'callback' || (isset($_GET['state']) && (isset($_GET['code']) || isset($_GET['error'])))) {
    if (!$isOwner) fail('Only the workspace owner can connect GitHub.',403);
    $expected = $_SESSION['github_oauth'] ?? null; unset($_SESSION['github_oauth']);
    $state = is_string($_GET['state'] ?? null) ? $_GET['state'] : '';
    if (!is_array($expected) || $expected['expires'] < time() || !hash_equals($expected['state'],$state)) { flash('The GitHub sign-in expired or did not match this session. Please try again.','error'); redirect('/users/admin/github.php'); }
    if (isset($_GET['error'])) { flash('GitHub access was not granted.','warning'); redirect('/users/admin/github.php'); }
    $code = is_string($_GET['code'] ?? null) ? $_GET['code'] : '';
    try {
        $result = github_connect($db,$user,github_exchange_code($code),'oauth');
        flash($result['refreshed'] ? 'GitHub account @' . $result['login'] . ' was already connected; its access was refreshed. To add a different account, switch accounts on github.com first.' : 'GitHub account @' . $result['login'] . ' connected.');
    } catch (GitHubUnavailable $error) { flash($error->getMessage(),'error'); }
    redirect('/users/admin/github.php');
}
if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    require_post();
    $action = input('action');
    $back = input('return_to') === 'project' && ctype_digit(input('project_id')) ? '/users/admin/workspace.php?module=projects&action=view&id=' . (int)input('project_id') . '#github' : '/users/admin/github.php';
    try {
        if (in_array($action,['connect_token','disconnect','people'],true) && !$isOwner) fail('Only the workspace owner can change GitHub accounts or match people.',403);
        if (in_array($action,['link','unlink','sync','auto_sync'],true)) require_module_access($db,$user,'projects',true);
        if ($action === 'sync') require_module_access($db,$user,'tasks',true);
        if ($action === 'connect_token') {
            rate_limit($db,'github-connect',(string)$companyId,10);
            $result = github_connect($db,$user,trim((string)($_POST['token'] ?? '')),'token');
            flash($result['refreshed'] ? 'GitHub account @' . $result['login'] . ' was already connected; its token was updated.' : 'GitHub account @' . $result['login'] . ' connected.');
        } elseif ($action === 'disconnect') {
            $existing = github_connection($db,$companyId,(int)input('connection_id'));
            if (!$existing) fail('This GitHub account is not connected to your workspace.',404);
            if ($existing['auth_type'] === 'oauth') { try { github_revoke_grant(github_token($existing)); } catch (Throwable) { /* The local connection is still removed. */ } }
            $affected = (int)db_one($db,'SELECT COUNT(*) AS count FROM github_repository_links WHERE company_id = ? AND connection_id = ?',[$companyId,(int)$existing['id']])['count'];
            db_run($db,'UPDATE github_repository_links SET connection_id = NULL WHERE company_id = ? AND connection_id = ?',[$companyId,(int)$existing['id']]);
            db_run($db,'DELETE FROM integration_connections WHERE id = ? AND company_id = ?',[(int)$existing['id'],$companyId]);
            github_cache_clear($db,$companyId);
            audit_event($db,$companyId,'integration.disconnected','integration','github',['account'=>$existing['account_login']]);
            flash('GitHub account @' . $existing['account_login'] . ' disconnected.' . ($affected ? ' ' . $affected . ' linked ' . ($affected === 1 ? 'repository needs' : 'repositories need') . ' another account; relink ' . ($affected === 1 ? 'it' : 'them') . ' below.' : ''),'info');
        } elseif ($action === 'link') {
            $linked = github_link_repository($db,$user,(int)input('project_id'),trim(input('repository')),ctype_digit(input('connection_id')) && (int)input('connection_id') > 0 ? (int)input('connection_id') : null);
            flash($linked['repository'] . ' linked to the project through @' . $linked['via'] . '.');
        } elseif ($action === 'unlink') {
            $link = db_one($db,'SELECT * FROM github_repository_links WHERE id = ? AND company_id = ?',[(int)input('link_id'),$companyId]);
            if (!$link) fail('This repository link could not be found.',404);
            db_run($db,'DELETE FROM github_repository_links WHERE id = ? AND company_id = ?',[(int)$link['id'],$companyId]);
            audit_event($db,$companyId,'github.repository_unlinked','projects',(string)$link['project_id'],['repository'=>$link['repo_full_name']]);
            flash('Repository unlinked. Tasks created from its issues were kept.','info');
        } elseif ($action === 'sync') {
            $result = github_sync_issues($db,$user,(int)input('link_id'));
            $message = $result['imported'] || $result['completed'] || $result['assigned'] ? sprintf('%d new %s added from GitHub issues and %d %s completed.',$result['imported'],$result['imported'] === 1 ? 'task' : 'tasks',$result['completed'],$result['completed'] === 1 ? 'task' : 'tasks') . ($result['assigned'] ? ' ' . $result['assigned'] . ' assigned to employees.' : '') : 'Tasks are already up to date with GitHub.';
            if ($result['unmatched']) $message .= ' Match ' . implode(', ',array_map(fn($login)=>'@' . $login,array_slice($result['unmatched'],0,5))) . ' to ' . (count($result['unmatched']) === 1 ? 'an employee' : 'employees') . ' to assign their issues.';
            flash($message);
        } elseif ($action === 'people') {
            $saved = github_save_people($db,$user,is_array($_POST['login'] ?? null) ? $_POST['login'] : [],is_array($_POST['employee'] ?? null) ? $_POST['employee'] : []);
            flash($saved ? 'GitHub matches saved. The next sync assigns issues to these employees.' : 'No changes to save.');
        } elseif ($action === 'auto_sync') {
            $link = db_one($db,'SELECT * FROM github_repository_links WHERE id = ? AND company_id = ?',[(int)input('link_id'),$companyId]);
            if (!$link) fail('This repository link could not be found.',404);
            $enabled = input('enabled') === '1';
            db_run($db,'UPDATE github_repository_links SET auto_sync = ? WHERE id = ? AND company_id = ?',[$enabled ? 1 : 0,(int)$link['id'],$companyId]);
            audit_event($db,$companyId,'github.auto_sync_changed','projects',(string)$link['project_id'],['repository'=>$link['repo_full_name'],'enabled'=>$enabled]);
            flash('Automatic sync turned ' . ($enabled ? 'on' : 'off') . ' for ' . $link['repo_full_name'] . '.');
        } else fail('Choose a valid GitHub action.',422);
    } catch (GitHubUnavailable $error) { flash($error->getMessage(),'error'); }
    redirect($back);
}
$connections = github_connections($db,$companyId);
$connection = $connections[0] ?? null;
$links = db_rows($db,'SELECT l.*, p.projectName, c.account_login FROM github_repository_links l JOIN projects p ON p.id = l.project_id AND p.company_id = l.company_id LEFT JOIN integration_connections c ON c.id = l.connection_id AND c.company_id = l.company_id WHERE l.company_id = ? ORDER BY p.projectName, l.repo_full_name',[$companyId]);
$linkCounts = array_count_values(array_map('strval',array_filter(array_column($links,'connection_id'))));
$staff = $isOwner && $connections ? db_rows($db,"SELECT id, TRIM(CONCAT(first_name, ' ', last_name)) AS name, job_title, github_login FROM employees WHERE company_id = ? ORDER BY first_name, last_name",[$companyId]) : [];
$githubPeople = [];
if ($staff) {
    foreach (github_seen_assignees($db,$companyId) as $login) $githubPeople[strtolower($login)] = ['login'=>$login,'employee'=>null];
    foreach ($staff as $person) if ($person['github_login']) $githubPeople[strtolower($person['github_login'])] = ['login'=>$person['github_login'],'employee'=>(int)$person['id']];
    foreach ($githubPeople as $key=>$entry) if ($entry['employee'] === null) foreach ($staff as $person) if ($person['github_login'] && strcasecmp($person['github_login'],$entry['login']) === 0) $githubPeople[$key]['employee'] = (int)$person['id'];
    ksort($githubPeople);
}
$projects = db_rows($db,'SELECT id, projectName FROM projects WHERE company_id = ? ORDER BY projectName',[$companyId]);
$repos = []; $repoErrors = [];
if ($connections && $canLink) [$repos,$repoErrors] = github_all_repositories($db,$companyId);
$preselect = (int)($_GET['project'] ?? 0);
suite_start('GitHub','integrations',$user);
?>
<a class="text-link back-crumb" href="/users/admin/integrations.php">← Integrations</a>
<?php $attention = array_filter($connections, fn($account) => $account['last_error'] !== ''); ?><div class="page-heading integration-heading"><div class="integration-title"><span class="integration-logo github"><?= icon('github') ?></span><div><h1>GitHub</h1><p>Link repositories to projects, see issues, pull requests and commits on the project page, and turn issues into tasks.</p></div></div><span class="badge <?= $connections ? ($attention ? 'pending' : 'active') : '' ?>"><?= $connections ? ($attention ? 'Needs attention' : (count($connections) === 1 ? 'Connected' : count($connections) . ' accounts connected')) : 'Not connected' ?></span></div>
<?php if (!$connection): ?>
<?php if ($isOwner): ?>
<section class="panel connect-hero"><div class="panel-body">
<div class="connect-hero-main"><span class="integration-logo github"><?= icon('github') ?></span><div><h2>Connect your GitHub account</h2><p>Sign in to GitHub and approve Simplr. You can then link repositories to projects in a couple of clicks.</p><ul class="connect-points"><li><?= icon('check') ?>Simplr reads repositories, issues, pull requests and commits</li><li><?= icon('check') ?>Nothing is ever written to GitHub</li><li><?= icon('check') ?>Disconnect at any time; access is revoked on GitHub too</li></ul></div></div>
<?php if (github_oauth_ready()): ?><a class="button github-button connect-cta" href="?oauth=start"><?= icon('github') ?> Connect GitHub</a><small class="subtle">If your organisation’s repositories don’t appear afterwards, an organisation owner may need to approve Simplr in GitHub’s organisation settings.</small>
<?php else: ?><p class="notice info compact">GitHub is being set up by Simplr and will be available here soon.</p><?php endif; ?>
<?php if (config('APP_ENV') === 'local'): ?><p class="dev-note"><strong>Developer note:</strong> the GitHub OAuth app’s authorization callback URL must be <code><?= e(github_redirect_uri()) ?></code>. <?= github_oauth_ready() ? 'Client ID and secret are configured.' : 'Set GITHUB_CLIENT_ID and GITHUB_CLIENT_SECRET in the server environment.' ?></p><?php endif; ?>
<details class="other-ways"><summary>Other ways to connect</summary><form method="post" class="form-stack" data-submit><?= csrf_field() ?><input type="hidden" name="action" value="connect_token"><p class="subtle">Prefer not to sign in? Create a <a class="text-link" href="https://github.com/settings/personal-access-tokens/new" target="_blank" rel="noopener noreferrer">fine-grained personal access token</a> with read access to Metadata, Issues, Pull requests and Contents, then paste it here.</p><div class="field"><label for="github-token">Personal access token</label><input id="github-token" name="token" type="password" autocomplete="off" spellcheck="false" placeholder="github_pat_…" required></div><button class="button secondary" type="submit">Connect with token</button><small class="subtle">The token is encrypted before it is stored and is never shown again.</small></form></details>
</div></section>
<?php else: ?><section class="panel"><div class="empty-state"><?= icon('github') ?><h3>GitHub isn’t connected yet</h3><p>Your workspace owner can connect GitHub from Integrations.</p></div></section><?php endif; ?>
<?php else: ?>
<section class="panel"><div class="panel-head"><h2>Connected accounts</h2><span class="count-label"><?= count($connections) ?></span></div><ul class="account-list"><?php foreach ($connections as $account): $count = (int)($linkCounts[(string)$account['id']] ?? 0); ?><li class="account-row"><span class="account-avatar"><?= icon('github') ?></span><div><strong><?= e($account['account_name'] !== '' ? $account['account_name'] : $account['account_login']) ?></strong><small>@<?= e($account['account_login']) ?> · <?= $account['auth_type'] === 'oauth' ? 'GitHub sign-in' : 'personal access token' ?> · connected <?= e(time_ago($account['connected_at'])) ?> · <?= $count ?> linked <?= $count === 1 ? 'repository' : 'repositories' ?></small><?php if ($account['last_error'] !== ''): ?><p class="notice error compact"><?= e($account['last_error']) ?></p><?php endif; ?></div><div class="account-actions"><?php if ($account['auth_type'] === 'oauth' && github_oauth_ready()): ?><a class="button small secondary" href="<?= e(github_web_base() . '/settings/connections/applications/' . rawurlencode(config('GITHUB_CLIENT_ID'))) ?>" target="_blank" rel="noopener noreferrer" title="Grant or request access to your organisations on GitHub">Check organisation access</a><?php elseif ($account['account_url'] !== ''): ?><a class="button small secondary" href="<?= e($account['account_url']) ?>" target="_blank" rel="noopener noreferrer">View on GitHub</a><?php endif; ?><?php if ($isOwner): ?><form method="post" data-confirm="Disconnect @<?= e($account['account_login']) ?>? <?= $count ? 'Its ' . $count . ' linked ' . ($count === 1 ? 'repository' : 'repositories') . ' will need another account.' : 'No repositories use it.' ?>"><?= csrf_field() ?><input type="hidden" name="action" value="disconnect"><input type="hidden" name="connection_id" value="<?= (int)$account['id'] ?>"><button class="button small secondary danger-text" type="submit">Disconnect</button></form><?php endif; ?></div></li><?php endforeach; ?></ul>
<?php if ($isOwner): ?><div class="add-account"><div><strong>Add another GitHub account</strong><small>GitHub connects whichever account is signed in on github.com. To add a different account, switch accounts on GitHub first, then click Add account.</small></div><div class="add-account-actions"><?php if (github_oauth_ready()): ?><a class="button small github-button" href="?oauth=start"><?= icon('plus') ?> Add account</a><?php endif; ?><details class="other-ways inline"><summary>Use a token instead</summary><form method="post" class="form-stack" data-submit><?= csrf_field() ?><input type="hidden" name="action" value="connect_token"><div class="field"><label for="github-token-extra">Personal access token</label><input id="github-token-extra" name="token" type="password" autocomplete="off" spellcheck="false" placeholder="github_pat_…" required></div><button class="button small secondary" type="submit">Connect with token</button></form></details></div></div><?php endif; ?></section>
<?php if ($canLink): ?><section class="panel"><div class="panel-head"><h2>Link a repository to a project</h2></div><div class="panel-body"><?php if (!$projects): ?><p class="subtle">Create a project first, then link its repository here.</p><?php else: $several = count($connections) > 1; ?><form method="post" class="link-form<?= $several ? ' has-account' : '' ?>" data-submit><?= csrf_field() ?><input type="hidden" name="action" value="link"><div class="field"><label for="link-project">Project</label><select id="link-project" name="project_id" required><option value="">Choose a project</option><?php foreach ($projects as $project): ?><option value="<?= (int)$project['id'] ?>"<?= $preselect === (int)$project['id'] ? ' selected' : '' ?>><?= e($project['projectName']) ?></option><?php endforeach; ?></select></div><div class="field"><label for="link-repo">Repository</label><input id="link-repo" name="repository" list="github-repositories" required placeholder="owner/repository" autocomplete="off" spellcheck="false"><datalist id="github-repositories"><?php foreach ($repos as $repo): ?><option value="<?= e($repo['full_name']) ?>"><?= e(($several ? 'via @' . $repo['via'] . ' · ' : '') . ($repo['private'] ? 'Private' : 'Public') . ($repo['description'] !== '' ? ' · ' . mb_strimwidth($repo['description'],0,60,'…') : '')) ?></option><?php endforeach; ?></datalist><small class="subtle">Start typing to search <?= count($repos) ?> recently active repositories<?= $several ? ' across ' . count($connections) . ' accounts' : '' ?>, or enter owner/repository.</small><?php foreach ($repoErrors as $login => $message): ?><small class="field-error">@<?= e($login) ?>: <?= e($message) ?></small><?php endforeach; ?></div><?php if ($several): ?><div class="field"><label for="link-account">Through account</label><select id="link-account" name="connection_id"><option value="">Any account that can see it</option><?php foreach ($connections as $account): ?><option value="<?= (int)$account['id'] ?>">@<?= e($account['account_login']) ?></option><?php endforeach; ?></select></div><?php endif; ?><button class="button" type="submit"><?= icon('branch') ?> Link repository</button></form><?php endif; ?></div></section><?php endif; ?>
<?php endif; ?>
<?php if ($staff): ?><section class="panel" id="people"><div class="panel-head"><h2>Match GitHub people to employees</h2><span class="count-label"><?= count(array_filter($githubPeople,fn($entry)=>$entry['employee'] !== null)) ?> matched</span></div><div class="panel-body"><p class="subtle">Synced issues are assigned to the matched employee, who then gets the task in their workspace with due-date reminders. Usernames below were seen on open issues in your linked repositories.</p><form method="post" class="form-stack" data-submit><?= csrf_field() ?><input type="hidden" name="action" value="people"><div class="people-map"><?php foreach (array_values($githubPeople) as $index=>$entry): ?><div class="people-row"><span class="people-login"><?= icon('github') ?> @<?= e($entry['login']) ?><input type="hidden" name="login[]" value="<?= e($entry['login']) ?>"></span><label class="sr-only" for="people-<?= $index ?>">Employee for @<?= e($entry['login']) ?></label><select id="people-<?= $index ?>" name="employee[]"><option value="">Not matched</option><?php foreach ($staff as $person): ?><option value="<?= (int)$person['id'] ?>"<?= $entry['employee'] === (int)$person['id'] ? ' selected' : '' ?>><?= e($person['name'] . ($person['job_title'] ? ' · ' . $person['job_title'] : '')) ?></option><?php endforeach; ?></select></div><?php endforeach; ?><?php for ($extra = 0; $extra < 2; $extra++): ?><div class="people-row"><span class="people-login"><label class="sr-only" for="people-new-<?= $extra ?>">GitHub username</label><input id="people-new-<?= $extra ?>" name="login[]" placeholder="Another GitHub username" maxlength="40" autocomplete="off" spellcheck="false"></span><label class="sr-only" for="people-new-employee-<?= $extra ?>">Employee</label><select id="people-new-employee-<?= $extra ?>" name="employee[]"><option value="">Choose an employee</option><?php foreach ($staff as $person): ?><option value="<?= (int)$person['id'] ?>"><?= e($person['name']) ?></option><?php endforeach; ?></select></div><?php endfor; ?></div><button class="button small" type="submit">Save matches</button><small class="subtle">You can also set a GitHub username on each employee’s record.</small></form></div></section><?php endif; ?>
<section class="panel"><div class="panel-head"><h2>Linked repositories</h2><span class="count-label"><?= count($links) ?></span></div><?php if (!$links): ?><div class="empty-state compact"><?= icon('branch') ?><h3>No repositories linked yet</h3><p>Link a repository to show its activity on the project page and sync its issues into tasks.</p></div><?php else: $canSync = module_access($db,$user,'tasks') === 'edit'; ?><ul class="repo-list"><?php foreach ($links as $link): $orphan = !$link['account_login']; ?><li><span class="repo-icon"><?= icon('branch') ?></span><div class="repo-main"><a class="repo-name" href="<?= e($link['html_url'] ?: '#') ?>" target="_blank" rel="noopener noreferrer"><?= e($link['repo_full_name']) ?></a><small><span class="badge <?= $link['is_private'] ? 'draft' : 'active' ?>"><?= $link['is_private'] ? 'Private' : 'Public' ?></span> <?= $orphan ? '<span class="badge overdue">Account disconnected</span>' : 'via @' . e($link['account_login']) ?> · linked to <a class="text-link" href="/users/admin/workspace.php?module=projects&amp;action=view&amp;id=<?= (int)$link['project_id'] ?>#github"><?= e($link['projectName']) ?></a> · <?= $link['last_synced_at'] ? 'synced ' . e(time_ago($link['last_synced_at'])) : 'not synced yet' ?> · <?= $link['auto_sync'] ? 'syncs automatically' : 'manual sync only' ?></small><?php if ($link['last_sync_error'] !== ''): ?><p class="notice error compact">Last automatic sync: <?= e($link['last_sync_error']) ?></p><?php endif; ?></div><?php if ($canLink): ?><div class="repo-actions"><?php if ($orphan && $connections): ?><form method="post" data-submit><?= csrf_field() ?><input type="hidden" name="action" value="link"><input type="hidden" name="project_id" value="<?= (int)$link['project_id'] ?>"><input type="hidden" name="repository" value="<?= e($link['repo_full_name']) ?>"><button class="button small" type="submit"><?= icon('refresh') ?> Relink</button></form><?php elseif (!$orphan && $canSync): ?><form method="post" data-submit><?= csrf_field() ?><input type="hidden" name="action" value="sync"><input type="hidden" name="link_id" value="<?= (int)$link['id'] ?>"><button class="button small" type="submit"><?= icon('refresh') ?> Sync issues</button></form><?php endif; ?><?php if (!$orphan): ?><form method="post" data-submit><?= csrf_field() ?><input type="hidden" name="action" value="auto_sync"><input type="hidden" name="link_id" value="<?= (int)$link['id'] ?>"><input type="hidden" name="enabled" value="<?= $link['auto_sync'] ? '0' : '1' ?>"><button class="button small secondary" type="submit" aria-pressed="<?= $link['auto_sync'] ? 'true' : 'false' ?>"><?= $link['auto_sync'] ? 'Auto-sync on' : 'Auto-sync off' ?></button></form><?php endif; ?><form method="post" data-confirm="Unlink <?= e($link['repo_full_name']) ?> from <?= e($link['projectName']) ?>? Tasks already created are kept."><?= csrf_field() ?><input type="hidden" name="action" value="unlink"><input type="hidden" name="link_id" value="<?= (int)$link['id'] ?>"><button class="button small secondary" type="submit">Unlink</button></form></div><?php endif; ?></li><?php endforeach; ?></ul><?php endif; ?></section>
<p class="subtle">Repositories sync automatically about every 15 minutes, or click Sync issues. Each open issue becomes a task once, assigned to the matched employee, with priority from labels like “urgent” or “low” and the milestone date as its due date. Reassigning an issue on GitHub reassigns the task; closing it completes the task. Nothing is written to GitHub.</p>
<?php suite_end(); ?>
