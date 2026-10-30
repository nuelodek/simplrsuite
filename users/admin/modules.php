<?php
require_once __DIR__ . '/../../app/auth.php';
require_once __DIR__ . '/../../app/view.php';
require_once __DIR__ . '/../../app/modules.php';
$db = database(); $user = workspace_user($db); $available = visible_modules($db,$user);
suite_start('All tools','modules',$user);
?>
<div class="page-heading"><div><h1>Workspace tools</h1><p>Choose a tool to open.</p></div></div>
<?php foreach (['projects'=>'Plan & deliver','calendar'=>'Events & meetings','people'=>'People & teams','sales'=>'Sales & CRM','finance'=>'Finance & inventory','recruiting'=>'Recruiting','files'=>'Files & documents','modules'=>'Operations & strategy'] as $group=>$title): if (!array_filter($available,fn($m)=>$m['nav'] === $group || ($group === 'projects' && $m['nav'] === 'tasks'))) continue; ?><section id="<?= e($group) ?>" style="margin-bottom:32px"><div class="section-title"><h2><?= e($title) ?></h2></div><div class="tools-grid"><?php foreach ($available as $key=>$module): if ($module['nav'] !== $group && !($group === 'projects' && $module['nav'] === 'tasks')) continue; $toolUrl = $key === 'payroll' ? '/users/employees/payroll.php' : '/users/admin/workspace.php?module=' . rawurlencode($key); ?><a class="tool-card" href="<?= e($toolUrl) ?>"><div class="feature-icon"><?= icon($module['icon']) ?></div><h3><?= e($module['title']) ?></h3><p><?= e($module['description']) ?></p></a><?php endforeach; ?></div></section><?php endforeach; ?>
<?php if (!$available): ?><section class="panel"><div class="empty-state"><h2>No tools assigned</h2><p>Ask your administrator to assign tools. Your assigned tasks are available in My workspace.</p></div></section><?php endif; ?>
<?php if (($_SESSION['auth_role'] ?? '') === 'admin'): ?><div class="notice info">Payroll calculations and bank exports are available. Paystack transfers require separate owner configuration and review.</div><p><a class="button secondary" href="/users/admin/payroll.php">Open payroll</a></p>
<section class="panel"><div class="panel-body"><h2>Accounting statements</h2><p>Build a chart of accounts, post balanced journal entries, and review management statements and ratios.</p><a class="button secondary" href="/users/admin/accounting.php">Open accounting</a></div></section>
<?php endif; suite_end(); ?>
