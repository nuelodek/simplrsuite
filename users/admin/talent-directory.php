<?php
require_once __DIR__ . '/../../app/auth.php';
require_once __DIR__ . '/../../app/view.php';
$db = database(); $user = admin_user($db);
$search = is_string($_GET['q'] ?? null) ? mb_substr(trim($_GET['q']),0,100) : '';
$page = max(1, min(10000, (int)($_GET['page'] ?? 1))); $offset = ($page - 1) * 12;
$where = "p.visibility = 'listed' AND a.status = 'Verified'"; $args = [];
if ($search !== '') { $where .= ' AND (a.full_name LIKE ? OR p.headline LIKE ? OR p.skills LIKE ? OR p.location LIKE ?)'; $args = array_fill(0,4,'%' . $search . '%'); }
$total = (int)db_one($db, 'SELECT COUNT(*) AS total FROM talent_portfolios p JOIN talentprofile a ON a.id = p.account_id WHERE ' . $where, $args)['total'];
$profiles = db_rows($db, 'SELECT a.full_name, a.industry, p.headline, p.location, p.experience, p.skills, p.bio, p.portfolio_url, p.availability FROM talent_portfolios p JOIN talentprofile a ON a.id = p.account_id WHERE ' . $where . ' ORDER BY p.updated_at DESC, p.account_id DESC LIMIT 12 OFFSET ' . $offset, $args);
suite_start('Talent directory','recruiting',$user);
?>
<div class="page-heading"><div><span class="eyebrow">PEOPLE & POSSIBILITIES</span><h1>Meet your next collaborator.</h1><p>Explore professional profiles that people have chosen to share with employers.</p></div><div class="form-actions"><a class="button" href="/users/admin/workspace.php?module=jobs">Job postings</a><a class="button secondary" href="/users/admin/workspace.php?module=applications">Application records</a></div></div>
<form method="get" class="talent-search"><label class="sr-only" for="q">Search talent profiles</label><?= icon('search') ?><input type="search" id="q" name="q" value="<?= e($search) ?>" placeholder="Search name, skills, role, or location"><button class="button" type="submit">Search</button></form>
<p class="subtle"><?= $total ?> <?= $total === 1 ? 'profile' : 'profiles' ?> · Sign-in email addresses are private. Direct messaging is not available yet.</p>
<?php if (!$profiles): ?><section class="panel empty-state"><?= icon('users') ?><h2><?= $search !== '' ? 'No matching profiles.' : 'Good connections start with a profile.' ?></h2><p><?= $search !== '' ? 'Try a different skill, role, or location.' : 'Profiles appear here only when people choose to list them.' ?></p></section><?php else: ?><div class="tools-grid">
<?php foreach ($profiles as $profile): ?><article class="panel talent-card"><span class="eyebrow"><?= e($profile['industry']) ?></span><h2><?= e($profile['full_name']) ?></h2><h3><?= e($profile['headline']) ?></h3><p class="subtle"><?= e($profile['location']) ?><?= $profile['availability'] ? ' · ' . e($profile['availability']) : '' ?></p><p><?= e($profile['experience']) ?></p><p class="talent-copy"><?= e($profile['skills']) ?></p><details><summary>About this person’s work</summary><p class="talent-copy"><?= e($profile['bio']) ?></p></details><?php if ($profile['portfolio_url'] !== '' && strtolower(parse_url($profile['portfolio_url'], PHP_URL_SCHEME) ?? '') === 'https'): ?><a class="text-link" href="<?= e($profile['portfolio_url']) ?>" target="_blank" rel="noopener noreferrer">View portfolio ↗</a><?php endif; ?></article><?php endforeach; ?></div><?php endif; ?>
<nav class="pagination" aria-label="Talent directory pages"><?php if ($page > 1): ?><a class="button secondary" href="?q=<?= urlencode($search) ?>&page=<?= $page-1 ?>">Previous</a><?php endif; ?><span>Page <?= $page ?></span><?php if ($offset + 12 < $total): ?><a class="button secondary" href="?q=<?= urlencode($search) ?>&page=<?= $page+1 ?>">Next</a><?php endif; ?></nav>
<?php suite_end(); ?>
