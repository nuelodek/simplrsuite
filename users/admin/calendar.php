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
    $lines = ['BEGIN:VCALENDAR','VERSION:2.0','PRODID:-//Simplr Suite//Workspace Calendar//EN','CALSCALE:GREGORIAN','BEGIN:VEVENT','UID:event-' . $companyId . '-' . $id . '@simplr-suite','DTSTAMP:' . gmdate('Ymd\THis\Z'),'DTSTART:' . $utc($event['start_datetime']),'DTEND:' . $utc($event['end_datetime']),'SUMMARY:' . $escape($event['event_name']),'DESCRIPTION:' . $escape(trim(rich_text_plain($event['agenda']) . "\n" . rich_text_plain($event['event_description']))),'LOCATION:' . $escape($event['event_type'] === 'online' ? $event['meet_link'] : $event['meetup_venue']),'END:VEVENT','END:VCALENDAR'];
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
$tasks = [];
$projects = [];
$canViewTasks = module_access($db,$user,'tasks') !== '';
$canViewProjects = module_access($db,$user,'projects') !== '';
if ($canViewTasks) {
    $tasks = db_rows($db,'SELECT t.id,t.title,t.due_date,t.priority,t.status,t.project_id,COALESCE(NULLIF(CONCAT(eu.first_name," ",eu.last_name)," "),NULLIF(CONCAT(ei.first_name," ",ei.last_name)," ")) AS assignee_name,p.id AS linked_project_id,p.projectName FROM tasks t LEFT JOIN employees ei ON ei.company_id = t.company_id AND ei.archived_at IS NULL AND BINARY CAST(ei.id AS CHAR) = BINARY t.assignee LEFT JOIN employees eu ON eu.company_id = t.company_id AND eu.archived_at IS NULL AND BINARY eu.unique_id = BINARY t.assignee LEFT JOIN projects p ON ' . task_project_join() . ' WHERE t.company_id = ? AND t.archived_at IS NULL AND t.due_date BETWEEN ? AND ? ORDER BY t.due_date,t.id LIMIT 201',[$companyId,$date->format('Y-m-01'),$date->modify('last day of this month')->format('Y-m-d')]);
}
if ($canViewProjects) {
    $projects = db_rows($db,'SELECT p.id,p.projectName,p.startDate,p.endDate,p.milestoneDate,p.status,COALESCE(m.manager_name,mu.manager_name) AS manager_name FROM projects p LEFT JOIN managers m ON m.company_id = p.company_id AND m.archived_at IS NULL AND m.id = p.projectManager LEFT JOIN managers mu ON mu.company_id = p.company_id AND mu.archived_at IS NULL AND BINARY mu.unique_id = BINARY p.project_manager_id WHERE p.company_id = ? AND p.archived_at IS NULL AND (p.startDate BETWEEN ? AND ? OR p.endDate BETWEEN ? AND ? OR p.milestoneDate BETWEEN ? AND ?) ORDER BY COALESCE(p.startDate,p.milestoneDate,p.endDate),p.id LIMIT 201',[$companyId,$date->format('Y-m-01'),$date->modify('last day of this month')->format('Y-m-d'),$date->format('Y-m-01'),$date->modify('last day of this month')->format('Y-m-d'),$date->format('Y-m-01'),$date->modify('last day of this month')->format('Y-m-d')]);
}
$monthEnd = $date->modify('last day of this month')->format('Y-m-d');
$eventsByDay = [];
foreach (array_slice($events,0,200) as $event) {
    $startDay = max($date->format('Y-m-d'),substr((string)$event['start_datetime'],0,10));
    $endDay = min($monthEnd,substr((string)$event['end_datetime'],0,10));
    for ($day = $startDay; $day <= $endDay; $day = (new DateTimeImmutable($day))->modify('+1 day')->format('Y-m-d')) {
        $eventsByDay[$day][] = ['kind'=>'event','event'=>$event];
    }
}
foreach (array_slice($tasks,0,200) as $task) {
    $eventsByDay[$task['due_date']][] = ['kind'=>'task','task'=>$task];
}
foreach (array_slice($projects,0,200) as $project) {
    foreach (['startDate'=>'Project starts','endDate'=>'Project ends','milestoneDate'=>'Milestone'] as $dateField=>$scheduleType) {
        $projectDate = trim((string)($project[$dateField] ?? ''));
        if ($projectDate >= $date->format('Y-m-d') && $projectDate <= $monthEnd) {
            $eventsByDay[$projectDate][] = ['kind'=>'project','project'=>$project,'schedule_type'=>$scheduleType,'date'=>$projectDate];
        }
    }
}
$hasMoreItems = count($events) > 200 || count($tasks) > 200 || count($projects) > 200;
$weekdayOffset = (int)$date->format('N') - 1;
$daysInMonth = (int)$date->format('t');
$calendarCellCount = (int)(ceil(($weekdayOffset + $daysInMonth) / 7) * 7);
$previousMonth = $date->modify('-1 month')->format('Y-m');
$nextMonth = $date->modify('+1 month')->format('Y-m');
suite_start('Calendar','calendar',$user);
?>
<div class="page-heading"><div><h1>Workspace calendar</h1><p>Events, project dates, and task deadlines across your workspace, with their assigned people.</p></div><?php if ($level === 'edit'): ?><a class="button" href="/users/admin/workspace.php?module=events&amp;action=new"><?= icon('plus') ?> Add event</a><?php endif; ?></div>
<section class="panel calendar-panel">
<div class="calendar-toolbar"><div class="calendar-month-navigation"><?php if ($month > '1900-01'): ?><a class="button secondary small" href="?month=<?= e($previousMonth) ?>" aria-label="Previous month">‹</a><?php else: ?><span class="button secondary small" aria-disabled="true">‹</span><?php endif; ?><h2 id="calendar-month-heading"><?= e($date->format('F Y')) ?></h2><?php if ($month < '2200-12'): ?><a class="button secondary small" href="?month=<?= e($nextMonth) ?>" aria-label="Next month">›</a><?php else: ?><span class="button secondary small" aria-disabled="true">›</span><?php endif; ?></div><a class="text-link" href="/users/admin/workspace.php?module=events">All events</a></div>
<div class="calendar-weekdays" aria-hidden="true"><?php foreach (['Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday'] as $weekday): ?><span><?= e($weekday) ?></span><?php endforeach; ?></div>
<div class="calendar-days" role="group" aria-labelledby="calendar-month-heading">
<?php for ($cell = 0; $cell < $calendarCellCount; $cell++): $dayNumber = $cell - $weekdayOffset + 1; if ($dayNumber < 1 || $dayNumber > $daysInMonth): ?><div class="calendar-day-empty" aria-hidden="true"></div><?php else: $dayDate = $date->setDate((int)$date->format('Y'),(int)$date->format('m'),$dayNumber)->format('Y-m-d'); $dayItems = $eventsByDay[$dayDate] ?? []; ?><button type="button" class="calendar-day<?= $dayItems ? ' has-events' : '' ?>" data-calendar-day="<?= e($dayDate) ?>" aria-label="<?= e($date->setDate((int)$date->format('Y'),(int)$date->format('m'),$dayNumber)->format('l, F j, Y')) ?><?= $dayItems ? ', ' . count($dayItems) . ' ' . (count($dayItems) === 1 ? 'scheduled item' : 'scheduled items') : ', no scheduled items' ?>"><span class="calendar-day-number"><?= $dayNumber ?></span><?php if ($dayItems): ?><span class="calendar-day-event-count"><?= count($dayItems) ?> <?= count($dayItems) === 1 ? 'item' : 'items' ?></span><?php endif; ?></button><?php endif; endfor; ?>
</div>
<?php foreach ($eventsByDay as $eventDay => $dayItems): ?><template id="calendar-items-<?= e($eventDay) ?>"><?php foreach ($dayItems as $calendarItem): ?><?php if ($calendarItem['kind'] === 'event'): $event = $calendarItem['event']; $provider = normalize_event_meeting_provider($event); ?><article class="event-card"><span class="badge"><?= e($event['event_type']) ?></span><h3><?= e($event['event_name']) ?></h3><p><strong><?= e($event['start_datetime']) ?></strong> to <?= e($event['end_datetime']) ?><br><small><?= e($event['timezone'] ?: 'Timezone not yet specified — review this event before exporting.') ?></small></p><?php if ($event['event_type'] === 'online'): ?><p><strong><?= e($provider === 'simplr' ? 'Simplr room' : ucfirst(str_replace('-', ' ', $provider))) ?>:</strong> <?= e($event['meet_link']) ?></p><?php else: ?><p><?= e($event['meetup_venue']) ?></p><?php endif; ?><div class="rich-text"><?= rich_text_html($event['agenda']) ?></div><div class="form-actions"><?php if ($event['event_type'] === 'online' && !empty($event['meet_link'])): ?><a class="button small" href="/users/admin/meeting.php?event=<?= (int)$event['event_id'] ?>" target="_blank" rel="noopener noreferrer"><?= $provider === 'simplr' ? 'Join live call' : 'Open meeting' ?></a><?php endif; ?><?php if ($event['timezone']): ?><a class="button secondary small" href="?download=<?= (int)$event['event_id'] ?>">Download calendar file</a><?php endif; ?><?php if ($level === 'edit'): ?><a class="text-link" href="/users/admin/workspace.php?module=events&amp;action=edit&amp;id=<?= (int)$event['event_id'] ?>">Edit event</a><?php endif; ?></div></article><?php elseif ($calendarItem['kind'] === 'task'): $task = $calendarItem['task']; ?><article class="event-card calendar-item-card calendar-task-card"><span class="badge">Task due · <?= e(ucfirst($task['priority'])) ?> · <?= e(ucfirst($task['status'])) ?></span><h3><?= e($task['title']) ?></h3><p><strong>Due:</strong> <?= e($task['due_date']) ?><br><strong>Assigned to:</strong> <?= e(trim((string)$task['assignee_name']) ?: 'Unassigned') ?></p><?php if (!empty($task['linked_project_id'])): ?><p><strong>Project:</strong> <a href="/users/admin/workspace.php?module=projects&amp;action=view&amp;id=<?= (int)$task['linked_project_id'] ?>"><?= e($task['projectName']) ?></a></p><?php endif; ?><a class="text-link" href="/users/admin/workspace.php?module=tasks&amp;action=view&amp;id=<?= (int)$task['id'] ?>">View task</a></article><?php else: $project = $calendarItem['project']; ?><article class="event-card calendar-item-card calendar-project-card"><span class="badge"><?= e($calendarItem['schedule_type']) ?></span><h3><?= e($project['projectName']) ?></h3><p><strong>Date:</strong> <?= e($calendarItem['date']) ?><?php if (!empty($project['status'])): ?><br><strong>Status:</strong> <?= e(ucfirst($project['status'])) ?><?php endif; ?><?php if (!empty($project['manager_name'])): ?><br><strong>Project manager:</strong> <?= e($project['manager_name']) ?><?php endif; ?></p><a class="text-link" href="/users/admin/workspace.php?module=projects&amp;action=view&amp;id=<?= (int)$project['id'] ?>">View project</a></article><?php endif; ?><?php endforeach; ?></template><?php endforeach; ?>
<?php if ($hasMoreItems): ?><div class="notice info calendar-limit-notice">Showing up to 200 events, 200 tasks, and 200 projects for this month. Open the related tool to search the full list.</div><?php endif; ?>
</section>
<dialog class="calendar-day-dialog" id="calendar-day-dialog" aria-labelledby="calendar-day-dialog-title"><div class="calendar-day-dialog-head"><h2 id="calendar-day-dialog-title"></h2><button class="icon-button" type="button" data-calendar-dialog-close aria-label="Close event details">×</button></div><div class="event-list calendar-modal-events" id="calendar-day-events"></div></dialog>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const dialog = document.getElementById('calendar-day-dialog');
    const title = document.getElementById('calendar-day-dialog-title');
    const eventList = document.getElementById('calendar-day-events');
    if (!dialog || !title || !eventList) return;
    document.querySelectorAll('[data-calendar-day]').forEach(function (dayButton) {
        dayButton.addEventListener('click', function () {
            const date = dayButton.dataset.calendarDay;
            const day = new Date(date + 'T00:00:00');
            title.textContent = day.toLocaleDateString(undefined, { weekday: 'long', month: 'long', day: 'numeric', year: 'numeric' });
            eventList.replaceChildren();
            const template = document.getElementById('calendar-items-' + date);
            if (template) {
                eventList.append(template.content.cloneNode(true));
            } else {
                const empty = document.createElement('p');
                empty.className = 'subtle';
                empty.textContent = 'No items scheduled for this day.';
                eventList.append(empty);
            }
            dialog.showModal();
        });
    });
    dialog.querySelector('[data-calendar-dialog-close]').addEventListener('click', function () {
        dialog.close();
    });
});
</script>
<p class="subtle">Calendar downloads add a copy to your calendar app. Invitations, reminders and two-way calendar sync are not connected.</p>
<?php suite_end(); ?>
