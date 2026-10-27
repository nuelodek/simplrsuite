<?php
require_once __DIR__ . '/../../app/modules.php';
require_once __DIR__ . '/../../app/view.php';
$db = database(); $user = workspace_user($db); $companyId = (int)$user['company_id'];
$id = filter_var($_GET['event'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]) ?: null;
if (!$id) fail('Choose a meeting to join.', 404);
$event = $id ? db_one($db, 'SELECT * FROM events WHERE event_id = ? AND company_id = ?', [$id, $companyId]) : null;
if (!$event) fail('This meeting could not be found in your workspace.', 404);
if (($event['event_type'] ?? '') !== 'online' || empty($event['meet_link'])) fail('This event does not include a live meeting link.', 404);
$provider = normalize_event_meeting_provider($event);
$joinUrl = $event['meet_link'];
suite_start('Meeting · ' . $event['event_name'], 'calendar', $user);
?>
<div class="page-heading">
    <div>
        <h1><?= e($event['event_name']) ?></h1>
        <p><?= e($provider === 'simplr' ? 'Simplr-hosted room' : ucfirst(str_replace('-', ' ', $provider)) . ' meeting') ?> · <?= e($event['start_datetime']) ?> to <?= e($event['end_datetime']) ?></p>
    </div>
</div>
<section class="panel">
    <div class="panel-head">
        <h2>Join meeting</h2>
        <a class="text-link" href="/users/admin/calendar.php">Back to calendar</a>
    </div>
    <div class="panel-body" style="display:grid;gap:18px;">
        <p class="subtle">This room is set for <?= e($event['timezone'] ?: 'your workspace timezone') ?>. Open the call in a new tab to join with audio and video enabled.</p>
        <?php if ($provider === 'simplr'): ?><div class="notice info">Simplr meetings use Jitsi Meet for browser-based audio and video calls, with no extra app or vendor setup required.</div><?php endif; ?>
        <div class="form-actions">
            <a class="button" href="<?= e($joinUrl) ?>" target="_blank" rel="noopener noreferrer">Join <?= $provider === 'simplr' ? 'Simplr meeting' : 'meeting' ?></a>
            <a class="button secondary" href="<?= e($joinUrl) ?>" target="_blank" rel="noopener noreferrer">Open external link</a>
        </div>
        <dl>
            <div><dt>When</dt><dd><?= e($event['start_datetime']) ?> to <?= e($event['end_datetime']) ?></dd></div>
            <div><dt>Timezone</dt><dd><?= e($event['timezone']) ?></dd></div>
            <div><dt>Provider</dt><dd><?= e($provider === 'simplr' ? 'Simplr video room' : ucfirst(str_replace('-', ' ', $provider))) ?></dd></div>
            <div><dt>Agenda</dt><dd class="rich-text"><?= trim((string)$event['agenda']) !== '' ? rich_text_html($event['agenda']) : 'No agenda has been added yet.' ?></dd></div>
        </dl>
    </div>
</section>
<?php suite_end(); ?>
