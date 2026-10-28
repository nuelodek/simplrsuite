<?php
// Included by record-detail.php for projects and tasks after the record scope check.
require_once __DIR__ . '/github.php';
if ($key === 'tasks') {
    $issue = db_one($db,'SELECT g.issue_number, g.issue_url, l.repo_full_name FROM github_issue_tasks g JOIN github_repository_links l ON l.id = g.link_id AND l.company_id = g.company_id WHERE g.company_id = ? AND g.task_id = ?',[$companyId,$id]);
    if ($issue): ?><section class="panel github-panel"><div class="panel-body github-issue-link"><span class="integration-logo github small"><?= icon('github') ?></span><div><strong>Created from GitHub issue #<?= (int)$issue['issue_number'] ?></strong><small><?= e($issue['repo_full_name']) ?> · completes automatically when the issue is closed and the repository is synced</small></div><?php if ($issue['issue_url'] !== ''): ?><a class="button small secondary" href="<?= e($issue['issue_url']) ?>" target="_blank" rel="noopener noreferrer">Open issue</a><?php endif; ?></div></section><?php endif;
    return;
}
$githubLinks = github_project_links($db,$companyId,$id);
$githubConnection = github_connection($db,$companyId);
$canRelink = $canEdit && $githubConnection;
$canSync = $canEdit && module_access($db,$user,'tasks') === 'edit';
$ago = fn(?string $iso) => $iso ? time_ago(gmdate('Y-m-d H:i:s',strtotime($iso))) : '';
?>
<section class="panel github-panel" id="github"><div class="panel-head"><h2><span class="integration-logo github small"><?= icon('github') ?></span> GitHub</h2><?php if ($githubConnection && $canEdit): ?><a class="text-link" href="/users/admin/github.php?project=<?= $id ?>">Link a repository <?= icon('arrow') ?></a><?php endif; ?></div>
<?php if (!$githubLinks): ?><div class="empty-state compact"><?= icon('branch') ?><h3>No repository linked</h3><p><?= $githubConnection ? 'Link a repository to see its issues, pull requests and commits here, and turn issues into tasks.' : 'Connect GitHub to see code activity alongside this project.' ?></p><?php if ($githubConnection && $canEdit): ?><a class="button small secondary" href="/users/admin/github.php?project=<?= $id ?>"><?= icon('branch') ?> Link repository</a><?php elseif (!$githubConnection && ($_SESSION['auth_role'] ?? '') === 'admin'): ?><a class="button small secondary" href="/users/admin/github.php"><?= icon('github') ?> Connect GitHub</a><?php endif; ?></div>
<?php else: foreach ($githubLinks as $link):
    $issues = $pulls = $commits = []; $problem = '';
    $linkConnection = github_link_connection($db,$link);
    if (!$linkConnection) $problem = $githubConnection ? 'The GitHub account this repository used was disconnected. Relink it through one of your connected accounts.' : 'GitHub is disconnected. Reconnect it in Integrations to see live activity.';
    else {
        try {
            $path = github_repo_path($link['repo_full_name']);
            $issues = array_values(array_filter(github_cached($db,$linkConnection,$path . '/issues',['state'=>'open','per_page'=>10]),fn($item)=>is_array($item) && !isset($item['pull_request'])));
            $pulls = github_cached($db,$linkConnection,$path . '/pulls',['state'=>'open','per_page'=>6]);
            $commits = github_cached($db,$linkConnection,$path . '/commits',['per_page'=>6]);
        } catch (GitHubUnavailable $error) { $problem = $error->getMessage(); }
    }
?>
<div class="repo-block"><div class="repo-block-head"><div><a class="repo-name" href="<?= e($link['html_url'] ?: '#') ?>" target="_blank" rel="noopener noreferrer"><?= icon('branch') ?><?= e($link['repo_full_name']) ?></a><small><span class="badge <?= $link['is_private'] ? 'draft' : 'active' ?>"><?= $link['is_private'] ? 'Private' : 'Public' ?></span><?= $link['default_branch'] !== '' ? ' ' . e($link['default_branch']) . ' ·' : '' ?> <?= $link['last_synced_at'] ? 'issues synced ' . e(time_ago($link['last_synced_at'])) : 'issues not synced yet' ?><?= $link['account_login'] ? ' · via @' . e($link['account_login']) : '' ?></small></div><?php if (!$linkConnection && $canRelink): ?><form method="post" action="/users/admin/github.php" data-submit><?= csrf_field() ?><input type="hidden" name="action" value="link"><input type="hidden" name="project_id" value="<?= $id ?>"><input type="hidden" name="repository" value="<?= e($link['repo_full_name']) ?>"><input type="hidden" name="return_to" value="project"><button class="button small" type="submit"><?= icon('refresh') ?> Relink</button></form><?php endif; ?><?php if ($canSync && $linkConnection): ?><form method="post" action="/users/admin/github.php" data-submit><?= csrf_field() ?><input type="hidden" name="action" value="sync"><input type="hidden" name="link_id" value="<?= (int)$link['id'] ?>"><input type="hidden" name="return_to" value="project"><input type="hidden" name="project_id" value="<?= $id ?>"><button class="button small" type="submit"><?= icon('refresh') ?> Sync issues to tasks</button></form><?php endif; ?></div>
<?php if ($problem !== ''): ?><p class="notice warning compact"><?= e($problem) ?></p><?php else: ?>
<div class="repo-columns">
<div><h3>Open issues <span class="count-label"><?= count($issues) ?><?= count($issues) === 10 ? '+' : '' ?></span></h3><?php if (!$issues): ?><p class="subtle">No open issues.</p><?php else: ?><ul class="gh-list"><?php foreach (array_slice($issues,0,5) as $item): ?><li><a href="<?= e(github_safe_url($item['html_url'] ?? '') ?: '#') ?>" target="_blank" rel="noopener noreferrer"><?= e((string)($item['title'] ?? 'Untitled issue')) ?></a><small>#<?= (int)($item['number'] ?? 0) ?> · <?= e((string)($item['user']['login'] ?? 'unknown')) ?> · <?= e($ago($item['created_at'] ?? null)) ?><?php foreach (array_slice($item['labels'] ?? [],0,2) as $label): if (!is_array($label)) continue; ?> <span class="gh-label"><?= e(mb_strimwidth((string)($label['name'] ?? ''),0,18,'…')) ?></span><?php endforeach; ?></small></li><?php endforeach; ?></ul><?php endif; ?></div>
<div><h3>Pull requests <span class="count-label"><?= count($pulls) ?></span></h3><?php if (!$pulls): ?><p class="subtle">No open pull requests.</p><?php else: ?><ul class="gh-list"><?php foreach (array_slice($pulls,0,5) as $item): if (!is_array($item)) continue; ?><li><a href="<?= e(github_safe_url($item['html_url'] ?? '') ?: '#') ?>" target="_blank" rel="noopener noreferrer"><?= e((string)($item['title'] ?? 'Untitled pull request')) ?></a><small>#<?= (int)($item['number'] ?? 0) ?> · <?= e((string)($item['user']['login'] ?? 'unknown')) ?> · <?= e($ago($item['created_at'] ?? null)) ?><?= !empty($item['draft']) ? ' <span class="gh-label">Draft</span>' : '' ?></small></li><?php endforeach; ?></ul><?php endif; ?></div>
<div><h3>Recent commits</h3><?php if (!$commits): ?><p class="subtle">No commits yet.</p><?php else: ?><ul class="gh-list"><?php foreach (array_slice($commits,0,5) as $item): if (!is_array($item)) continue; $message = strtok((string)($item['commit']['message'] ?? ''),"\n"); ?><li><a href="<?= e(github_safe_url($item['html_url'] ?? '') ?: '#') ?>" target="_blank" rel="noopener noreferrer"><?= e(mb_strimwidth($message !== false ? $message : 'Commit',0,90,'…')) ?></a><small><code><?= e(substr((string)($item['sha'] ?? ''),0,7)) ?></code> · <?= e((string)($item['author']['login'] ?? $item['commit']['author']['name'] ?? 'unknown')) ?> · <?= e($ago($item['commit']['author']['date'] ?? null)) ?></small></li><?php endforeach; ?></ul><?php endif; ?></div>
</div><?php endif; ?></div>
<?php endforeach; endif; ?>
</section>
