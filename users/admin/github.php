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
    if (!github_oauth_ready()) fail('GitHub sign-in is not enabled on this server yet. Connect with a personal access token instead.',409);
    $state = bin2hex(random_bytes(16));
    $_SESSION['github_oauth'] = ['state'=>$state,'expires'=>time() + 600];
    redirect(github_web_base() . '/login/oauth/authorize?' . http_build_query(['client_id'=>config('GITHUB_CLIENT_ID'),'redirect_uri'=>github_redirect_uri(),'scope'=>'repo read:user','state'=>$state,'allow_signup'=>'false']));
}
if ($oauth === 'callback') {
    if (!$isOwner) fail('Only the workspace owner can connect GitHub.',403);
    $expected = $_SESSION['github_oauth'] ?? null; unset($_SESSION['github_oauth']);
    $state = is_string($_GET['state'] ?? null) ? $_GET['state'] : '';
    if (!is_array($expected) || $expected['expires'] < time() || !hash_equals($expected['state'],$state)) { flash('The GitHub sign-in expired or did not match this session. Please try again.','error'); redirect('/users/admin/github.php'); }
    if (isset($_GET['error'])) { flash('GitHub access was not granted.','warning'); redirect('/users/admin/github.php'); }
    $code = is_string($_GET['code'] ?? null) ? $_GET['code'] : '';
    try {
        if (!preg_match('/^[A-Za-z0-9_\-]{8,128}$/D',$code)) throw new GitHubUnavailable('GitHub did not return a valid sign-in code.');
        [$status,,$raw] = github_http('POST',github_web_base() . '/login/oauth/access_token',['Accept: application/json','Content-Type: application/x-www-form-urlencoded','User-Agent: Simplr-Suite'],http_build_query(['client_id'=>config('GITHUB_CLIENT_ID'),'client_secret'=>config('GITHUB_CLIENT_SECRET'),'code'=>$code,'redirect_uri'=>github_redirect_uri()]));
        $grant = json_decode($raw,true);
        if ($status !== 200 || !is_string($grant['access_token'] ?? null)) throw new GitHubUnavailable('GitHub did not complete the sign-in. Please try again.');
        $profile = github_connect($db,$user,$grant['access_token'],'oauth');
        flash('GitHub connected as ' . $profile['login'] . '.');
    } catch (GitHubUnavailable $error) { flash($error->getMessage(),'error'); }
    redirect('/users/admin/github.php');
}
if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    require_post();
    $action = input('action');
    $back = input('return_to') === 'project' && ctype_digit(input('project_id')) ? '/users/admin/workspace.php?module=projects&action=view&id=' . (int)input('project_id') . '#github' : '/users/admin/github.php';
    try {
        if (in_array($action,['connect_token','disconnect'],true) && !$isOwner) fail('Only the workspace owner can change the GitHub connection.',403);
        if (in_array($action,['link','unlink','sync'],true)) require_module_access($db,$user,'projects',true);
        if ($action === 'sync') require_module_access($db,$user,'tasks',true);
        if ($action === 'connect_token') {
            rate_limit($db,'github-connect',(string)$companyId,10);
            $profile = github_connect($db,$user,trim((string)($_POST['token'] ?? '')),'token');
            flash('GitHub connected as ' . $profile['login'] . '.');
        } elseif ($action === 'disconnect') {
            db_run($db,"DELETE FROM integration_connections WHERE company_id = ? AND provider = 'github'",[$companyId]);
            github_cache_clear($db,$companyId);
            audit_event($db,$companyId,'integration.disconnected','integration','github');
            flash('GitHub disconnected. Linked repositories stay listed until you remove them; reconnect to see live activity.','info');
        } elseif ($action === 'link') {
            github_link_repository($db,$user,(int)input('project_id'),trim(input('repository')));
            flash('Repository linked to the project.');
        } elseif ($action === 'unlink') {
            $link = db_one($db,'SELECT * FROM github_repository_links WHERE id = ? AND company_id = ?',[(int)input('link_id'),$companyId]);
            if (!$link) fail('This repository link could not be found.',404);
            db_run($db,'DELETE FROM github_repository_links WHERE id = ? AND company_id = ?',[(int)$link['id'],$companyId]);
            audit_event($db,$companyId,'github.repository_unlinked','projects',(string)$link['project_id'],['repository'=>$link['repo_full_name']]);
            flash('Repository unlinked. Tasks created from its issues were kept.','info');
        } elseif ($action === 'sync') {
            $result = github_sync_issues($db,$user,(int)input('link_id'));
            flash($result['imported'] || $result['completed'] ? sprintf('%d new %s added from GitHub issues and %d %s completed.',$result['imported'],$result['imported'] === 1 ? 'task' : 'tasks',$result['completed'],$result['completed'] === 1 ? 'task' : 'tasks') : 'Tasks are already up to date with GitHub.');
        } else fail('Choose a valid GitHub action.',422);
    } catch (GitHubUnavailable $error) { flash($error->getMessage(),'error'); }
    redirect($back);
}
$connection = github_connection($db,$companyId);
$links = db_rows($db,'SELECT l.*, p.projectName FROM github_repository_links l JOIN projects p ON p.id = l.project_id AND p.company_id = l.company_id WHERE l.company_id = ? ORDER BY p.projectName, l.repo_full_name',[$companyId]);
$projects = db_rows($db,'SELECT id, projectName FROM projects WHERE company_id = ? ORDER BY projectName',[$companyId]);
$repos = []; $repoError = '';
if ($connection && $canLink) { try { $repos = github_repositories($db,$connection); } catch (GitHubUnavailable $error) { $repoError = $error->getMessage(); } }
$preselect = (int)($_GET['project'] ?? 0);
suite_start('GitHub','integrations',$user);
?>
<a class="text-link back-crumb" href="/users/admin/integrations.php">← Integrations</a>
<div class="page-heading integration-heading"><div class="integration-title"><span class="integration-logo github"><?= icon('github') ?></span><div><h1>GitHub</h1><p>Link repositories to projects, see issues, pull requests and commits on the project page, and turn issues into tasks.</p></div></div><span class="badge <?= $connection ? ($connection['last_error'] !== '' ? 'pending' : 'active') : '' ?>"><?= $connection ? ($connection['last_error'] !== '' ? 'Needs attention' : 'Connected') : 'Not connected' ?></span></div>
<?php if (!$connection): ?>
<?php if ($isOwner): ?>
<div class="connect-grid">
<section class="panel connect-card"><div class="panel-body"><h2>Continue with GitHub</h2><p class="subtle">Sign in with GitHub and approve access. Best for most teams.</p><?php if (github_oauth_ready()): ?><a class="button github-button" href="?oauth=start"><?= icon('github') ?> Continue with GitHub</a><?php else: ?><p class="notice info">One-click sign-in needs a GitHub OAuth app for this server. Use a personal access token now, or set it up:</p><details class="setup-steps"><summary>Set up one-click sign-in</summary><ol><li>In GitHub, open <strong>Settings → Developer settings → OAuth Apps → New OAuth App</strong>.</li><li>Use this authorization callback URL:<br><code><?= e(github_redirect_uri()) ?></code></li><li>Add <code>GITHUB_CLIENT_ID</code> and <code>GITHUB_CLIENT_SECRET</code> to the server’s private environment settings, then reload this page.</li></ol></details><?php endif; ?></div></section>
<section class="panel connect-card"><div class="panel-body"><h2>Connect with a token</h2><p class="subtle">Create a <a class="text-link" href="https://github.com/settings/personal-access-tokens/new" target="_blank" rel="noopener noreferrer">fine-grained personal access token</a> with read access to <strong>Metadata</strong>, <strong>Issues</strong>, <strong>Pull requests</strong> and <strong>Contents</strong> for the repositories you want to link.</p><form method="post" class="form-stack" data-submit><?= csrf_field() ?><input type="hidden" name="action" value="connect_token"><div class="field"><label for="github-token">Personal access token</label><input id="github-token" name="token" type="password" autocomplete="off" spellcheck="false" placeholder="github_pat_…" required></div><button class="button" type="submit"><?= icon('plug') ?> Connect GitHub</button><small class="subtle">The token is encrypted before it is stored and is never shown again.</small></form></div></section>
</div>
<?php else: ?><section class="panel"><div class="empty-state"><?= icon('github') ?><h3>GitHub isn’t connected yet</h3><p>Your workspace owner can connect GitHub from Integrations.</p></div></section><?php endif; ?>
<?php else: ?>
<section class="panel account-card"><div class="panel-body account-row"><span class="account-avatar"><?= icon('github') ?></span><div><strong><?= e($connection['account_name'] !== '' ? $connection['account_name'] : $connection['account_login']) ?></strong><small>@<?= e($connection['account_login']) ?> · connected <?= e(time_ago($connection['connected_at'])) ?> with <?= $connection['auth_type'] === 'oauth' ? 'GitHub sign-in' : 'a personal access token' ?></small><?php if ($connection['last_error'] !== ''): ?><p class="notice error compact"><?= e($connection['last_error']) ?></p><?php endif; ?></div><div class="account-actions"><?php if ($connection['account_url'] !== ''): ?><a class="button small secondary" href="<?= e($connection['account_url']) ?>" target="_blank" rel="noopener noreferrer">View on GitHub</a><?php endif; ?><?php if ($isOwner): ?><form method="post" data-confirm="Disconnect GitHub? Project pages will stop showing live GitHub activity."><?= csrf_field() ?><input type="hidden" name="action" value="disconnect"><button class="button small secondary danger-text" type="submit">Disconnect</button></form><?php endif; ?></div></div></section>
<?php if ($canLink): ?><section class="panel"><div class="panel-head"><h2>Link a repository to a project</h2></div><div class="panel-body"><?php if (!$projects): ?><p class="subtle">Create a project first, then link its repository here.</p><?php else: ?><form method="post" class="link-form" data-submit><?= csrf_field() ?><input type="hidden" name="action" value="link"><div class="field"><label for="link-project">Project</label><select id="link-project" name="project_id" required><option value="">Choose a project</option><?php foreach ($projects as $project): ?><option value="<?= (int)$project['id'] ?>"<?= $preselect === (int)$project['id'] ? ' selected' : '' ?>><?= e($project['projectName']) ?></option><?php endforeach; ?></select></div><div class="field"><label for="link-repo">Repository</label><input id="link-repo" name="repository" list="github-repositories" required placeholder="owner/repository" autocomplete="off" spellcheck="false"><datalist id="github-repositories"><?php foreach ($repos as $repo): ?><option value="<?= e($repo['full_name']) ?>"><?= e(($repo['private'] ? 'Private' : 'Public') . ($repo['description'] !== '' ? ' · ' . mb_strimwidth($repo['description'],0,60,'…') : '')) ?></option><?php endforeach; ?></datalist><small class="subtle"><?= $repoError !== '' ? e($repoError) : 'Start typing to search ' . count($repos) . ' recently active repositories, or enter owner/repository.' ?></small></div><button class="button" type="submit"><?= icon('branch') ?> Link repository</button></form><?php endif; ?></div></section><?php endif; ?>
<?php endif; ?>
<section class="panel"><div class="panel-head"><h2>Linked repositories</h2><span class="count-label"><?= count($links) ?></span></div><?php if (!$links): ?><div class="empty-state compact"><?= icon('branch') ?><h3>No repositories linked yet</h3><p>Link a repository to show its activity on the project page and sync its issues into tasks.</p></div><?php else: ?><ul class="repo-list"><?php foreach ($links as $link): ?><li><span class="repo-icon"><?= icon('branch') ?></span><div class="repo-main"><a class="repo-name" href="<?= e($link['html_url'] ?: '#') ?>" target="_blank" rel="noopener noreferrer"><?= e($link['repo_full_name']) ?></a><small><span class="badge <?= $link['is_private'] ? 'draft' : 'active' ?>"><?= $link['is_private'] ? 'Private' : 'Public' ?></span> Linked to <a class="text-link" href="/users/admin/workspace.php?module=projects&amp;action=view&amp;id=<?= (int)$link['project_id'] ?>#github"><?= e($link['projectName']) ?></a> · <?= $link['last_synced_at'] ? 'synced ' . e(time_ago($link['last_synced_at'])) : 'not synced yet' ?></small></div><?php if ($canLink): ?><div class="repo-actions"><?php if ($connection && module_access($db,$user,'tasks') === 'edit'): ?><form method="post" data-submit><?= csrf_field() ?><input type="hidden" name="action" value="sync"><input type="hidden" name="link_id" value="<?= (int)$link['id'] ?>"><button class="button small" type="submit"><?= icon('refresh') ?> Sync issues</button></form><?php endif; ?><form method="post" data-confirm="Unlink <?= e($link['repo_full_name']) ?> from <?= e($link['projectName']) ?>? Tasks already created are kept."><?= csrf_field() ?><input type="hidden" name="action" value="unlink"><input type="hidden" name="link_id" value="<?= (int)$link['id'] ?>"><button class="button small secondary" type="submit">Unlink</button></form></div><?php endif; ?></li><?php endforeach; ?></ul><?php endif; ?></section>
<p class="subtle">Sync adds a task for each open issue (once), sets priority from labels like “urgent” or “low”, uses the issue milestone date when there is one, and completes tasks whose issues were closed. Nothing is written to GitHub.</p>
<?php suite_end(); ?>
