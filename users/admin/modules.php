<?php
require_once __DIR__ . '/../../app/auth.php';
require_once __DIR__ . '/../../app/view.php';
require_once __DIR__ . '/../../app/modules.php';
$db = database(); $user = workspace_user($db); $available = visible_modules($db,$user);
suite_start('All tools','modules',$user);
?>
<div class="page-heading"><div><span class="eyebrow">MAKE SPACE FOR YOUR BEST WORK</span><h1>One suite. Your whole business.</h1><p>Choose a tool and pick up where you left off.</p></div></div>
<?php foreach (['projects'=>'Plan & deliver','calendar'=>'Events & meetings','people'=>'People & teams','sales'=>'Sales & CRM','finance'=>'Finance & inventory','recruiting'=>'Recruiting','modules'=>'Operations & strategy'] as $group=>$title): if (!array_filter($available,fn($m)=>$m['nav'] === $group || ($group === 'projects' && $m['nav'] === 'tasks'))) continue; ?><section id="<?= e($group) ?>" style="margin-bottom:32px"><div class="section-title"><h2><?= e($title) ?></h2></div><div class="tools-grid"><?php foreach ($available as $key=>$module): if ($module['nav'] !== $group && !($group === 'projects' && $module['nav'] === 'tasks')) continue; ?><a class="tool-card" href="/users/admin/workspace.php?module=<?= e($key) ?>"><div class="feature-icon"><?= icon($module['icon']) ?></div><h3><?= e($module['title']) ?></h3><p><?= e($module['description']) ?></p></a><?php endforeach; ?></div></section><?php endforeach; ?>
<?php if (!$available): ?><section class="panel"><div class="empty-state"><h2>Your tools will appear here.</h2><p>Ask your administrator to assign the tools you need. Your assigned tasks are available in My workspace.</p></div></section><?php endif; ?>
<?php if (($_SESSION['auth_role'] ?? '') === 'admin'): ?><div class="notice info">Payment collection, payroll processing, and connected bank accounts need a configured provider before they can be used. <a href="/users/admin/integrations.php">View integration availability</a>.</div>
<?php endif; suite_end(); ?>
