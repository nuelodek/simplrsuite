<?php
declare(strict_types=1);
require_once __DIR__ . '/../../app/auth.php';
require_once __DIR__ . '/../../app/settings.php';
require_once __DIR__ . '/../../app/neurons.php';
require_once __DIR__ . '/../../app/view.php';

$db = database();
$user = admin_user($db);
$companyId = (int)$user['company_id'];
$settings = workspace_settings($db,$companyId);
$timezone = (string)($settings['timezone'] ?? config('APP_TIMEZONE','Africa/Lagos'));
try { new DateTimeZone($timezone); } catch (Throwable) { $timezone = 'UTC'; }

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    require_post();
    $action = input('action');
    if ($action === 'create') {
        $name = input('name');
        $type = input('neuron_type');
        $description = input('description');
        if ((int)db_one($db,'SELECT COUNT(*) AS count FROM workspace_neurons WHERE company_id = ?',[$companyId])['count'] >= 100) fail('This workspace has reached the 100 automation limit.',422);
        if ($name === '' || mb_strlen($name) > 120) fail('Give the automation a name of 1 to 120 characters.',422);
        if (!isset(neuron_types()[$type])) fail('Choose one of the available automation actions.',422);
        if (mb_strlen($description) > 500) fail('Keep the description to 500 characters or fewer.',422);
        try {
            $schedule = neuron_create_schedule(input('schedule_type'),input('once_at'),input('schedule_time'),input('weekday'),$timezone);
        } catch (InvalidArgumentException $error) {
            fail($error->getMessage(),422);
        }
        db_run($db,'INSERT INTO workspace_neurons (company_id,created_by,name,neuron_type,description,schedule_type,schedule_time,weekday,timezone,next_run_at,status) VALUES (?,?,?,?,?,?,?,?,?,?,\'active\')',[
            $companyId,(int)$user['id'],$name,$type,$description,$schedule['schedule_type'],$schedule['schedule_time'],$schedule['weekday'],$schedule['timezone'],$schedule['next_run_at'],
        ]);
        audit_event($db,$companyId,'neuron.created','neurons',(string)$db->insert_id,['type'=>$type,'schedule'=>$schedule['schedule_type']]);
        flash('Automation created. Its first run is scheduled.');
        redirect('/users/admin/neurons.php');
    }

    $id = filter_var($_POST['neuron_id'] ?? null,FILTER_VALIDATE_INT,['options'=>['min_range'=>1]]);
    if (!$id) fail('Choose a valid automation.',422);
    $transactionOpen = false;
    try {
        $db->begin_transaction();
        $transactionOpen = true;
        $neuron = db_one($db,'SELECT * FROM workspace_neurons WHERE id = ? AND company_id = ? FOR UPDATE',[(int)$id,$companyId]);
        if (!$neuron) { $db->rollback(); fail('This automation is no longer in your workspace.',404); }
        if ($action === 'pause' && $neuron['status'] === 'active') {
            db_run($db,"UPDATE workspace_neurons SET status = 'paused' WHERE id = ? AND company_id = ?",[(int)$id,$companyId]);
            audit_event($db,$companyId,'neuron.paused','neurons',(string)$id);
            flash('Automation paused.');
        } elseif ($action === 'resume' && $neuron['status'] === 'paused') {
            db_run($db,"UPDATE workspace_neurons SET status = 'active' WHERE id = ? AND company_id = ?",[(int)$id,$companyId]);
            audit_event($db,$companyId,'neuron.resumed','neurons',(string)$id);
            flash('Automation resumed.');
        } elseif ($action === 'run_now') {
            db_run($db,'UPDATE workspace_neurons SET status = \'active\', next_run_at = UTC_TIMESTAMP() WHERE id = ? AND company_id = ?',[(int)$id,$companyId]);
            audit_event($db,$companyId,'neuron.run_requested','neurons',(string)$id);
            flash('Run requested. The scheduler will pick it up shortly.');
        } else {
            $db->rollback();
            fail('That action is not available for this automation.',422);
        }
        $db->commit();
        $transactionOpen = false;
    } catch (Throwable $error) {
        if ($transactionOpen) $db->rollback();
        throw $error;
    }
    redirect('/users/admin/neurons.php');
}

$neurons = db_rows($db,'SELECT * FROM workspace_neurons WHERE company_id = ? ORDER BY id DESC LIMIT 100',[$companyId]);
$runs = [];
if ($neurons) {
    $ids = array_map(static fn(array $row): int => (int)$row['id'],$neurons);
    foreach (db_rows($db,'SELECT * FROM workspace_neuron_runs WHERE company_id = ? AND neuron_id IN (' . implode(',',array_fill(0,count($ids),'?')) . ') ORDER BY id DESC LIMIT 300',[$companyId,...$ids]) as $run) {
        $run['data'] = $run['result_json'] ? json_decode((string)$run['result_json'],true) : null;
        if (count($runs[(int)$run['neuron_id']] ?? []) < 3) $runs[(int)$run['neuron_id']][] = $run;
    }
}

suite_start('Neurons','neurons',$user);
?>
<div class="page-heading"><div><h1>Neurons</h1><p>Schedule read-only workspace snapshots and keep a history of each run. Schedules use your workspace timezone: <?= e($timezone) ?>.</p></div></div>
<div class="notice info">These first automations only count workspace records. They do not change data, send email, or make financial decisions.</div>
<section class="panel"><div class="panel-head"><h2>Create an automation</h2></div><div class="panel-body">
<form method="post" class="form-stack" data-submit>
<?= csrf_field() ?><input type="hidden" name="action" value="create">
<div class="field"><label for="neuron-name">Name</label><input id="neuron-name" name="name" maxlength="120" required placeholder="Monday workload check"></div>
<div class="field"><label for="neuron-type">What should it measure?</label><select id="neuron-type" name="neuron_type" required><?php foreach (neuron_types() as $key=>$type): ?><option value="<?= e($key) ?>"><?= e($type['title']) ?> — <?= e($type['description']) ?></option><?php endforeach; ?></select></div>
<div class="field"><label for="neuron-description">Note (optional)</label><input id="neuron-description" name="description" maxlength="500" placeholder="A short reminder about this automation"></div>
<div class="field"><label for="neuron-schedule">Schedule</label><select id="neuron-schedule" name="schedule_type"><option value="once">One time</option><option value="daily">Every day</option><option value="weekly">Every week</option></select></div>
<div class="field" data-neuron-once><label for="neuron-once">Run at (<?= e($timezone) ?>)</label><input id="neuron-once" type="datetime-local" name="once_at"></div>
<div class="field" data-neuron-repeat hidden><label for="neuron-time">Run time (<?= e($timezone) ?>)</label><input id="neuron-time" type="time" name="schedule_time"></div>
<div class="field" data-neuron-weekday hidden><label for="neuron-weekday">Day of week</label><select id="neuron-weekday" name="weekday"><?php foreach (['Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday'] as $day=>$label): ?><option value="<?= $day + 1 ?>"><?= e($label) ?></option><?php endforeach; ?></select></div>
<div><button class="button" type="submit"><?= icon('plus') ?> Create neuron</button></div>
</form></div></section>
<section class="panel"><div class="panel-head"><h2>Your automations</h2><span class="count-label"><?= count($neurons) ?> / 100</span></div><div class="panel-body">
<?php if (!$neurons): ?><div class="empty-state"><h3>No neurons yet</h3><p>Create a workload or hiring pulse above. It will run automatically when the scheduler is installed.</p></div><?php else: ?>
<div class="neuron-list"><?php foreach ($neurons as $neuron): $type = neuron_types()[$neuron['neuron_type']] ?? null; $history = $runs[(int)$neuron['id']] ?? []; ?>
<article class="neuron-card"><div class="neuron-card-head"><div><h3><?= e($neuron['name']) ?></h3><p><?= e($type['title'] ?? 'Unavailable action') ?> · <?= e(neuron_schedule_label($neuron)) ?> · <?= e($neuron['timezone']) ?></p><?php if ($neuron['description'] !== ''): ?><p><?= e($neuron['description']) ?></p><?php endif; ?></div><span class="badge <?= $neuron['status'] === 'active' ? 'active' : ($neuron['status'] === 'paused' ? 'inactive' : 'completed') ?>"><?= e(ucfirst($neuron['status'])) ?></span></div>
<p class="subtle">Next run: <?= $neuron['next_run_at'] ? e((new DateTimeImmutable($neuron['next_run_at'],new DateTimeZone('UTC')))->setTimezone(new DateTimeZone($neuron['timezone']))->format('j M Y, H:i')) . ' ' . e($neuron['timezone']) : '—' ?> · Last run: <?= $neuron['last_run_at'] ? e((new DateTimeImmutable($neuron['last_run_at'],new DateTimeZone('UTC')))->setTimezone(new DateTimeZone($timezone))->format('j M Y, H:i')) . ' ' . e($timezone) : 'Never' ?></p>
<div class="neuron-actions"><form method="post" data-submit><?= csrf_field() ?><input type="hidden" name="neuron_id" value="<?= (int)$neuron['id'] ?>"><input type="hidden" name="action" value="run_now"><button class="button secondary small" type="submit">Run now</button></form><?php if ($neuron['status'] === 'active'): ?><form method="post" data-submit><?= csrf_field() ?><input type="hidden" name="neuron_id" value="<?= (int)$neuron['id'] ?>"><input type="hidden" name="action" value="pause"><button class="button secondary small" type="submit">Pause</button></form><?php elseif ($neuron['status'] === 'paused'): ?><form method="post" data-submit><?= csrf_field() ?><input type="hidden" name="neuron_id" value="<?= (int)$neuron['id'] ?>"><input type="hidden" name="action" value="resume"><button class="button secondary small" type="submit">Resume</button></form><?php endif; ?></div>
<?php if ($history): ?><details><summary>Recent runs (<?= count($history) ?>)</summary><div class="table-wrap"><table><thead><tr><th>Started</th><th>Status</th><th>Result</th></tr></thead><tbody><?php foreach ($history as $run): ?><tr><td><?= e((new DateTimeImmutable($run['started_at'],new DateTimeZone('UTC')))->setTimezone(new DateTimeZone($timezone))->format('j M Y, H:i')) ?></td><td><?= e(ucfirst($run['status'])) ?><?= $run['error_message'] !== '' ? ': ' . e($run['error_message']) : '' ?></td><td><?php if (is_array($run['data'])): $labels = $type['metrics'] ?? []; ?><?= implode(' · ',array_map(static fn(string $key,string $label): string => $label . ': ' . (int)($run['data'][$key] ?? 0),array_keys($labels),array_values($labels))) ?><?php else: ?>—<?php endif; ?></td></tr><?php endforeach; ?></tbody></table></div></details><?php endif; ?>
</article><?php endforeach; ?></div><?php endif; ?>
</div></section>
<script>
(() => {
  const schedule = document.getElementById('neuron-schedule');
  const once = document.querySelector('[data-neuron-once]');
  const repeat = document.querySelector('[data-neuron-repeat]');
  const weekday = document.querySelector('[data-neuron-weekday]');
  function updateSchedule() {
    once.hidden = schedule.value !== 'once';
    repeat.hidden = !['daily', 'weekly'].includes(schedule.value);
    weekday.hidden = schedule.value !== 'weekly';
    document.getElementById('neuron-once').required = schedule.value === 'once';
    document.getElementById('neuron-time').required = ['daily', 'weekly'].includes(schedule.value);
  }
  schedule.addEventListener('change', updateSchedule);
  updateSchedule();
})();
</script>
<?php suite_end(); ?>
