<?php
require_once __DIR__ . '/../../app/modules.php';
require_once __DIR__ . '/../../app/view.php';
$db = database(); $user = workspace_user($db); $level = require_module_access($db,$user,'events'); $companyId = (int)$user['company_id'];
if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'GET') fail('Use the event form to make changes.',405);
if (isset($_GET['download'])) {
    $id = filter_var($_GET['download'],FILTER_VALIDATE_INT,['options'=>['min_range'=>1]]);
    $event = $id ? db_one($db,'SELECT * FROM events WHERE event_id = ? AND company_id = ?',[$id,$companyId]) : null;
    if (!$event) fail('This event could not be found in your workspace.',404);
    if (!in_array($event['timezone'],DateTimeZone::listIdentifiers(),true)) fail('Choose and save this event\'s timezone before downloading it.',409);
    $zone = new DateTimeZone($event['timezone']);
    $escape = fn($value)=>str_replace(["\\","\r\n","\r","\n",';',','],["\\\\","\\n","\\n","\\n","\\;","\\,"],(string)$value);
    $utc = fn($value)=>(new DateTimeImmutable($value,$zone))->setTimezone(new DateTimeZone('UTC'))->format('Ymd\THis\Z');
    $lines = ['BEGIN:VCALENDAR','VERSION:2.0','PRODID:-//Simplr Suite//Workspace Calendar//EN','CALSCALE:GREGORIAN','BEGIN:VEVENT','UID:event-' . $companyId . '-' . $id . '@simplr-suite','DTSTAMP:' . gmdate('Ymd\THis\Z'),'DTSTART:' . $utc($event['start_datetime']),'DTEND:' . $utc($event['end_datetime']),'SUMMARY:' . $escape($event['event_name']),'DESCRIPTION:' . $escape(trim($event['agenda'] . "\n" . $event['event_description'])),'LOCATION:' . $escape($event['event_type'] === 'online' ? $event['meet_link'] : $event['meetup_venue']),'END:VEVENT','END:VCALENDAR'];
    header('Content-Type: text/calendar; charset=utf-8'); header('Content-Disposition: attachment; filename="simplr-event-' . $id . '.ics"');
    foreach ($lines as $line) {
        while (strlen($line)>73) { $chunk = mb_strcut($line,0,73,'UTF-8'); echo $chunk . "\r\n "; $line = substr($line,strlen($chunk)); }
        echo $line . "\r\n";
    }
    exit;
}
$month = is_string($_GET['month'] ?? null) ? $_GET['month'] : date('Y-m');
$date = DateTimeImmutable::createFromFormat('!Y-m',$month);
if (!$date || $date->format('Y-m') !== $month || $month < '1900-01' || $month > '2200-12') fail('Choose a valid calendar month.',422);
$events = db_rows($db,'SELECT * FROM events WHERE company_id = ? AND start_datetime < ? AND end_datetime >= ? ORDER BY start_datetime, event_id LIMIT 201',[$companyId,$date->modify('+1 month')->format('Y-m-d'),$date->format('Y-m-d')]);
suite_start('Calendar','calendar',$user);
?>
<div class="page-heading"><div><span class="eyebrow">A LITTLE ROOM TO PLAN AHEAD</span><h1>Your calendar</h1><p>Meetings and events across your workspace. Times appear in each event’s timezone.</p></div><?php if ($level === 'edit'): ?><a class="button" href="/users/admin/workspace.php?module=events&amp;action=new"><?= icon('plus') ?> Add event</a><?php endif; ?></div>
<section class="panel"><div class="table-toolbar"><form method="get" class="form-actions"><label for="month">Month</label><input id="month" name="month" type="month" value="<?= e($month) ?>" min="1900-01" max="2200-12" required><button type="submit" class="button secondary small">View month</button></form><a class="text-link" href="/users/admin/workspace.php?module=events">All events</a></div>
<?php if (!$events): ?><div class="empty-state"><?= icon('calendar') ?><h2>A little space in your calendar.</h2><p>No events are scheduled for <?= e($date->format('F Y')) ?>.</p></div><?php else: ?><div class="panel-body event-list"><?php foreach (array_slice($events,0,200) as $event): ?><article class="event-card"><span class="badge"><?= e($event['event_type']) ?></span><h2><?= e($event['event_name']) ?></h2><p><strong><?= e($event['start_datetime']) ?></strong> to <?= e($event['end_datetime']) ?><br><small><?= e($event['timezone'] ?: 'Timezone not yet specified — review this event before exporting.') ?></small></p><p><?= e($event['event_type'] === 'online' ? $event['meet_link'] : $event['meetup_venue']) ?></p><p class="preserve-lines"><?= e($event['agenda']) ?></p><div class="form-actions"><?php if ($event['timezone']): ?><a class="button secondary small" href="?download=<?= (int)$event['event_id'] ?>">Download calendar file</a><?php endif; ?><?php if ($level === 'edit'): ?><a class="text-link" href="/users/admin/workspace.php?module=events&amp;action=edit&amp;id=<?= (int)$event['event_id'] ?>">Edit event</a><?php endif; ?></div></article><?php endforeach; ?></div><?php endif; ?>
<?php if (count($events)>200): ?><div class="notice info">Showing the first 200 events for this month. Use All events to search the full list.</div><?php endif; ?></section>
<p class="subtle">Calendar downloads add a copy to your calendar app. Invitations, reminders and two-way calendar sync are not connected.</p>
<?php suite_end(); ?>
