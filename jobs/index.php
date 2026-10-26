<?php
require_once __DIR__ . '/../app/recruiting.php';
$db = database();
$publicId = is_string($_GET['job'] ?? null) ? $_GET['job'] : '';
$job = $publicId !== '' ? public_job($db,$publicId) : null;
if ($publicId !== '' && !$job) fail('This job is unavailable. Browse current roles at /jobs/.',404);
$search = is_string($_GET['q'] ?? null) ? mb_substr(trim($_GET['q']),0,100) : '';
$page = max(1,min(10000,(int)($_GET['page'] ?? 1))); $offset = ($page-1)*12;
if (!$job) {
    $where = "j.status = 'published' AND j.published_at IS NOT NULL AND (j.closes_at IS NULL OR j.closes_at > UTC_TIMESTAMP()) AND u.status = 'Verified'"; $args = [];
    if (config('BILLING_ENFORCE','false') === 'true') { $where .= " AND EXISTS (SELECT 1 FROM billing_entitlements b WHERE b.company_id = j.company_id AND b.tool_key = 'recruiting' AND b.provider_mode = ? AND b.valid_until > UTC_TIMESTAMP())"; $args[] = config('PAYSTACK_MODE','test'); }
    if ($search !== '') { $where .= ' AND (j.title LIKE ? OR j.location LIKE ? OR u.company_name LIKE ?)'; array_push($args,...array_fill(0,3,'%'.$search.'%')); }
    $total = (int)db_one($db,'SELECT COUNT(*) AS total FROM job_posts j JOIN users u ON u.company_id = j.company_id WHERE '.$where,$args)['total'];
    $jobs = db_rows($db,'SELECT j.*,u.company_name FROM job_posts j JOIN users u ON u.company_id = j.company_id WHERE '.$where.' ORDER BY j.published_at DESC,j.id DESC LIMIT 12 OFFSET '.$offset,$args);
}
page_head($job ? $job['title'].' at '.$job['company_name'] : 'Find your next role',['description'=>'Explore open roles and apply with your Simplr talent profile.']);
?>
<body class="marketing"><a class="skip-link" href="#main">Skip to content</a><header class="marketing-header"><?= brand() ?><nav aria-label="Jobs navigation"><a class="text-link" href="<?= e(audience_url('talents','/landing/talents/')) ?>">For talents</a> <a class="button small secondary" href="/users/talents/talentpool.php">My talent workspace</a></nav></header>
<main id="main" class="jobs-main">
<?php if ($job): $open = job_accepts_applications($db,$job); ?>
<a class="text-link" href="/jobs/">← Browse all roles</a><div class="page-heading"><div><span class="eyebrow"><?= e($job['company_name']) ?></span><h1><?= e($job['title']) ?></h1><p><?= e($job['location']) ?> · <?= e($job['workplace']) ?> · <?= e($job['employment_type']) ?></p></div><span class="badge"><?= $open ? 'Accepting applications' : 'Applications closed' ?></span></div>
<section class="panel"><div class="panel-body"><h2>About the role</h2><p class="talent-copy"><?= e($job['description']) ?></p><?php if ($job['requirements']): ?><h2>Skills and requirements</h2><p class="talent-copy"><?= e($job['requirements']) ?></p><?php endif; ?>
<?php if ($job['closing_date']): ?><p class="subtle">Applications close at the end of <?= e($job['closing_date']) ?> (<?= e($job['timezone']) ?>).</p><?php endif; ?>
<?php if ($open): ?><a class="button" href="/users/talents/apply.php?job=<?= e($job['public_id']) ?>">Apply for this role <?= icon('arrow') ?></a><p class="subtle">Use your verified talent account. You will review the details shared with this employer before submitting.</p><?php else: ?><div class="notice info">This role is no longer accepting applications. Existing applicants can track their progress in My applications.</div><?php endif; ?></div></section>
<?php else: ?>
<div class="page-heading"><div><span class="eyebrow">YOUR NEXT CHAPTER</span><h1>Find work that fits.</h1><p>Explore roles published by businesses on Simplr.</p></div></div>
<form method="get" class="talent-search"><label class="sr-only" for="q">Search role, company or location</label><?= icon('search') ?><input type="search" id="q" name="q" maxlength="100" value="<?= e($search) ?>" placeholder="Role, company or location"><button class="button" type="submit">Search</button></form><p class="subtle"><?= $total ?> open <?= $total === 1 ? 'role' : 'roles' ?></p>
<?php if (!$jobs): ?><section class="panel empty-state"><?= icon('briefcase') ?><h2><?= $search ? 'No roles match your search.' : 'New opportunities start here.' ?></h2><p><?= $search ? 'Try a different role or location.' : 'There are no open roles yet. You can prepare your talent profile while employers publish opportunities.' ?></p><a class="button secondary" href="/users/talents/talentpool.php">Prepare my profile</a></section><?php else: ?><div class="tools-grid"><?php foreach ($jobs as $item): ?><article class="panel talent-card"><span class="eyebrow"><?= e($item['company_name']) ?></span><h2><a class="text-link" href="?job=<?= e($item['public_id']) ?>"><?= e($item['title']) ?></a></h2><p><?= e($item['location']) ?> · <?= e($item['workplace']) ?></p><p class="subtle"><?= e($item['employment_type']) ?><?= $item['closing_date'] ? ' · Closes '.e($item['closing_date']) : '' ?></p><a class="button secondary" href="?job=<?= e($item['public_id']) ?>">View role <?= icon('arrow') ?></a></article><?php endforeach; ?></div><?php endif; ?>
<nav class="pagination" aria-label="Job pages"><?php if ($page>1): ?><a class="button secondary" href="?q=<?= urlencode($search) ?>&amp;page=<?= $page-1 ?>">Previous</a><?php endif; ?><span>Page <?= $page ?></span><?php if ($offset+12<$total): ?><a class="button secondary" href="?q=<?= urlencode($search) ?>&amp;page=<?= $page+1 ?>">Next</a><?php endif; ?></nav>
<?php endif; ?></main><footer class="public-footer public-container"><?= brand() ?><a class="text-link" href="/public/privacypolicy.php">Privacy</a></footer></body></html>
