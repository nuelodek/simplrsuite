<?php
require_once __DIR__ . '/../../app/recruiting.php';
$db = database(); $user = workspace_user($db); $level = require_module_access($db,$user,'applications');
$id = filter_var($_GET['id'] ?? null,FILTER_VALIDATE_INT,['options'=>['min_range'=>1]]);
$item = $id ? application_submission($db,$id,(int)$user['company_id']) : null;
if (!$item) fail('This submitted application could not be found in your workspace.',404);
$stages = module_config('applications')['fields']['status']['options'];
if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    require_post();
    $action = input('action');
    if ($action === '' && in_array(input('status'),$stages,true)) $action = 'stage';
    require_module_access($db,$user,'applications',true);
    if ($action === 'stage' && !in_array(input('status'),$stages,true)) fail('Choose an available application stage.',422);
    if (!in_array($action,['stage','interview_schedule','interview_cancel'],true)) fail('Choose a valid application action.',422);
    rate_limit($db,'application-review',(string)$id,30);
    $db->begin_transaction();
    try {
        require_module_access($db,$user,'applications',true,true);
        $current = application_submission($db,$id,(int)$user['company_id'],false,true);
        if (!$current || !hash_equals(record_version($current),input('version'))) { $db->rollback(); fail('This application changed. Reload and review its latest status.',409); }
        if ($current['withdrawn_at']) { $db->rollback(); fail('This candidate withdrew their application. Further review is closed.',409); }
        if ($action === 'stage') {
            db_run($db,'UPDATE applications SET status = ? WHERE id = ? AND company_id = ?',[input('status'),$id,$user['company_id']]);
            audit_event($db,(int)$user['company_id'],'application.stage_changed','applications',(string)$id,['from'=>$current['status'],'to'=>input('status')]);
        } elseif ($action === 'interview_schedule') {
            create_application_interview($db,$current,$user);
        } else {
            update_application_interview($db,$current,(int)input('interview_id'),'cancelled',input('interview_version'));
        }
        $db->commit();
    } catch (Throwable $error) { $db->rollback(); throw $error; }
    flash(match($action) {'stage'=>'Application stage updated. The candidate can see this status in My applications.','interview_schedule'=>'Interview scheduled. The candidate can review and respond in My applications.','interview_cancel'=>'Interview cancelled. The candidate can see the update in My applications.'});
    redirect('/users/admin/application.php?id='.$id);
}
$interviews = application_interviews($db,(int)$id);
suite_start('Review application','recruiting',$user);
?>
<a class="text-link" href="/users/admin/workspace.php?module=applications">← Application records</a><div class="page-heading"><div><span class="eyebrow"><?= e($item['position_applied_for']) ?></span><h1><?= e($item['applicant_name']) ?></h1><p>Applied <?= e($item['consented_at']) ?> UTC</p></div><span class="badge"><?= $item['withdrawn_at'] ? 'Withdrawn' : e(ucfirst($item['status'])) ?></span></div>
<?php if ($item['withdrawn_at']): ?><div class="notice info">This candidate withdrew their application on <?= e($item['withdrawn_at']) ?> UTC. Review is closed and their resume is no longer available for download.</div><?php endif; ?>
<section class="panel"><div class="panel-head"><h2>Submitted profile</h2></div><div class="panel-body"><p class="subtle">Shared by this candidate for this role. Profile details are preserved as submitted.</p><?php submitted_profile_view($item); ?><?php if ($item['resume_attachment_id'] && !$item['withdrawn_at']): ?><p><a class="button secondary" href="/api/attachments.php?id=<?= (int)$item['resume_attachment_id'] ?>">Download submitted resume</a></p><p class="subtle">Files are not scanned for viruses. Open only files you trust.</p><?php else: ?><p class="subtle"><?= $item['withdrawn_at'] ? 'Resume access ended when this application was withdrawn.' : 'No resume attached.' ?></p><?php endif; ?></div></section>
<?php application_interviews_view($interviews,false,record_version($item),!$item['withdrawn_at']); ?>
<?php if (!$item['withdrawn_at'] && $level === 'edit'): ?><section class="panel record-form"><h2>Move the application forward</h2><p>The candidate can see the stage you choose. No email is sent.</p><form method="post" class="form-stack" data-submit><?= csrf_field() ?><input type="hidden" name="action" value="stage"><input type="hidden" name="version" value="<?= e(record_version($item)) ?>"><div class="field"><label for="status">Application stage</label><select name="status" id="status" required><?php foreach ($stages as $stage): ?><option value="<?= e($stage) ?>" <?= $item['status'] === $stage ? 'selected' : '' ?>><?= e(ucfirst($stage)) ?></option><?php endforeach; ?></select></div><div><button class="button" type="submit">Save stage <?= icon('check') ?></button></div></form></section><section class="panel record-form"><h2>Schedule an interview</h2><p>The candidate can accept or decline from My applications. No email is sent.</p><form method="post" class="form-stack"><?= csrf_field() ?><input type="hidden" name="action" value="interview_schedule"><input type="hidden" name="version" value="<?= e(record_version($item)) ?>"><div class="field"><label for="interview_title">Interview title</label><input id="interview_title" name="interview_title" maxlength="150" required></div><div class="field"><label for="interview_datetime">Date and time</label><input id="interview_datetime" name="interview_datetime" type="datetime-local" required></div><div class="field"><label for="interview_timezone">Timezone</label><input id="interview_timezone" name="interview_timezone" list="interview-timezones" value="Africa/Lagos" required><datalist id="interview-timezones"><option value="Africa/Lagos"><option value="UTC"><option value="Europe/London"><option value="America/New_York"></datalist></div><div class="field"><label for="meeting_type">Format</label><select id="meeting_type" name="meeting_type" required><option value="remote">Remote</option><option value="in-person">In person</option></select></div><div class="field"><label for="meeting_url">Secure meeting link (remote)</label><input id="meeting_url" name="meeting_url" type="url" maxlength="2048" placeholder="https://"></div><div class="field"><label for="meeting_location">Location (in person)</label><input id="meeting_location" name="meeting_location" maxlength="255"></div><div class="field"><label for="interview_agenda">Agenda or preparation notes</label><textarea id="interview_agenda" name="interview_agenda" maxlength="4000"></textarea></div><div><button class="button" type="submit">Schedule interview <?= icon('calendar') ?></button></div></form></section><?php elseif (!$item['withdrawn_at']): ?><div class="notice info">You have view-only access to applications. Ask your workspace owner for edit access to update stages or schedule interviews.</div><?php endif; suite_end(); ?>
