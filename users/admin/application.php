<?php
require_once __DIR__ . '/../../app/recruiting.php';
$db = database(); $user = workspace_user($db); $level = require_module_access($db,$user,'applications');
$id = filter_var($_GET['id'] ?? null,FILTER_VALIDATE_INT,['options'=>['min_range'=>1]]);
$item = $id ? application_submission($db,$id,(int)$user['company_id']) : null;
if (!$item) fail('This submitted application could not be found in your workspace.',404);
$stages = module_config('applications')['fields']['status']['options'];
if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    require_post(); require_module_access($db,$user,'applications',true);
    if (!in_array(input('status'),$stages,true)) fail('Choose an available application stage.',422);
    $db->begin_transaction();
    try {
        require_module_access($db,$user,'applications',true,true);
        $current = application_submission($db,$id,(int)$user['company_id'],false,true);
        if (!$current || !hash_equals(record_version($current),input('version'))) { $db->rollback(); fail('This application changed. Reload and review its latest status.',409); }
        if ($current['withdrawn_at']) { $db->rollback(); fail('This candidate withdrew their application. Further review is closed.',409); }
        db_run($db,'UPDATE applications SET status = ? WHERE id = ? AND company_id = ?',[input('status'),$id,$user['company_id']]);
        audit_event($db,(int)$user['company_id'],'application.stage_changed','applications',(string)$id,['from'=>$current['status'],'to'=>input('status')]);
        $db->commit();
    } catch (Throwable $error) { $db->rollback(); throw $error; }
    flash('Application stage updated. The candidate can see this status in My applications.'); redirect('/users/admin/application.php?id='.$id);
}
suite_start('Review application','recruiting',$user);
?>
<a class="text-link" href="/users/admin/workspace.php?module=applications">← Application records</a><div class="page-heading"><div><span class="eyebrow"><?= e($item['position_applied_for']) ?></span><h1><?= e($item['applicant_name']) ?></h1><p>Applied <?= e($item['consented_at']) ?> UTC</p></div><span class="badge"><?= $item['withdrawn_at'] ? 'Withdrawn' : e(ucfirst($item['status'])) ?></span></div>
<?php if ($item['withdrawn_at']): ?><div class="notice info">This candidate withdrew their application on <?= e($item['withdrawn_at']) ?> UTC. Review is closed and their resume is no longer available for download.</div><?php endif; ?>
<section class="panel"><div class="panel-head"><h2>Submitted profile</h2></div><div class="panel-body"><p class="subtle">Shared by this candidate for this role. Profile details are preserved as submitted.</p><?php submitted_profile_view($item); ?><?php if ($item['resume_attachment_id'] && !$item['withdrawn_at']): ?><p><a class="button secondary" href="/api/attachments.php?id=<?= (int)$item['resume_attachment_id'] ?>">Download submitted resume</a></p><p class="subtle">Files are not scanned for viruses. Open only files you trust.</p><?php else: ?><p class="subtle"><?= $item['withdrawn_at'] ? 'Resume access ended when this application was withdrawn.' : 'No resume attached.' ?></p><?php endif; ?></div></section>
<?php if (!$item['withdrawn_at'] && $level === 'edit'): ?><section class="panel record-form"><h2>Move the application forward</h2><p>The candidate can see the stage you choose. No email is sent.</p><form method="post" class="form-stack" data-submit><?= csrf_field() ?><input type="hidden" name="version" value="<?= e(record_version($item)) ?>"><div class="field"><label for="status">Application stage</label><select name="status" id="status" required><?php foreach ($stages as $stage): ?><option value="<?= e($stage) ?>" <?= $item['status'] === $stage ? 'selected' : '' ?>><?= e(ucfirst($stage)) ?></option><?php endforeach; ?></select></div><div><button class="button" type="submit">Save stage <?= icon('check') ?></button></div></form></section><?php elseif (!$item['withdrawn_at']): ?><div class="notice info">You have view-only access to applications. Ask your workspace owner for edit access to update stages.</div><?php endif; suite_end(); ?>
