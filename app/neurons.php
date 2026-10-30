<?php
declare(strict_types=1);
require_once __DIR__ . '/database.php';

function neuron_types(): array {
    return [
        'workload_pulse'=>[
            'title'=>'Workload pulse',
            'description'=>'Counts active projects, open tasks and overdue tasks in this workspace.',
            'metrics'=>['active_projects'=>'Active projects','open_tasks'=>'Open tasks','overdue_tasks'=>'Overdue tasks'],
        ],
        'hiring_pulse'=>[
            'title'=>'Hiring pulse',
            'description'=>'Counts published roles, applications and applications awaiting review.',
            'metrics'=>['published_roles'=>'Published roles','applications'=>'Applications','awaiting_review'=>'Awaiting review'],
        ],
    ];
}

function neuron_schedule_next(array $neuron, DateTimeImmutable $after): DateTimeImmutable {
    $timezone = new DateTimeZone((string)$neuron['timezone']);
    $localAfter = $after->setTimezone($timezone);
    $time = (string)$neuron['schedule_time'];
    if (!preg_match('/^([01]\d|2[0-3]):([0-5]\d)(?::[0-5]\d)?$/D', $time, $matches)) {
        throw new InvalidArgumentException('The automation schedule time is invalid.');
    }
    $hour = (int)$matches[1];
    $minute = (int)$matches[2];

    if ($neuron['schedule_type'] === 'daily') {
        $next = $localAfter->setTime($hour, $minute);
        if ($next <= $localAfter) $next = $next->modify('+1 day');
        return $next;
    }

    if ($neuron['schedule_type'] === 'weekly') {
        $weekday = filter_var($neuron['weekday'] ?? null, FILTER_VALIDATE_INT);
        if ($weekday === false || $weekday < 1 || $weekday > 7) {
            throw new InvalidArgumentException('The automation weekday is invalid.');
        }
        $days = ((int)$weekday - (int)$localAfter->format('N') + 7) % 7;
        $next = $localAfter->setTime($hour, $minute)->modify('+' . $days . ' days');
        if ($next <= $localAfter) $next = $next->modify('+7 days');
        return $next;
    }

    throw new InvalidArgumentException('This automation does not have a recurring schedule.');
}

function neuron_next_run_utc(array $neuron, DateTimeImmutable $after): string {
    return neuron_schedule_next($neuron, $after)->setTimezone(new DateTimeZone('UTC'))->format('Y-m-d H:i:s');
}

function neuron_create_schedule(string $type, string $onceAt, string $time, string $weekday, string $timezone, ?DateTimeImmutable $now = null): array {
    $zone = new DateTimeZone($timezone);
    $now ??= new DateTimeImmutable('now', new DateTimeZone('UTC'));
    $schedule = ['schedule_type'=>$type,'schedule_time'=>null,'weekday'=>null,'timezone'=>$timezone];

    if ($type === 'once') {
        $date = DateTimeImmutable::createFromFormat('!Y-m-d\TH:i', $onceAt, $zone);
        $errors = DateTimeImmutable::getLastErrors();
        if (!$date || ($errors !== false && ($errors['warning_count'] || $errors['error_count'])) || $date->format('Y-m-d\TH:i') !== $onceAt) {
            throw new InvalidArgumentException('Choose a valid date and time for this one-time automation.');
        }
        if ($date->getTimestamp() < $now->getTimestamp() + 60) {
            throw new InvalidArgumentException('Choose a time at least one minute in the future.');
        }
        $schedule['next_run_at'] = $date->setTimezone(new DateTimeZone('UTC'))->format('Y-m-d H:i:s');
        return $schedule;
    }

    if (!in_array($type, ['daily','weekly'], true)) {
        throw new InvalidArgumentException('Choose a supported automation schedule.');
    }
    if (!preg_match('/^(?:[01]\d|2[0-3]):[0-5]\d$/D', $time)) {
        throw new InvalidArgumentException('Choose a valid time for this automation.');
    }
    $schedule['schedule_time'] = $time . ':00';
    if ($type === 'weekly') {
        $day = filter_var($weekday, FILTER_VALIDATE_INT);
        if ($day === false || $day < 1 || $day > 7) {
            throw new InvalidArgumentException('Choose a valid weekday for this automation.');
        }
        $schedule['weekday'] = $day;
    }
    $schedule['next_run_at'] = neuron_next_run_utc($schedule, $now);
    return $schedule;
}

function neuron_run_action(mysqli $db, int $companyId, string $type, DateTimeImmutable $at): array {
    if ($companyId < 1 || !isset(neuron_types()[$type])) {
        throw new InvalidArgumentException('This automation action is not available.');
    }
    if ($type === 'workload_pulse') {
        $result = db_one($db,"SELECT
            (SELECT COUNT(*) FROM projects WHERE company_id = ? AND archived_at IS NULL AND status IN ('ongoing','pending')) AS active_projects,
            (SELECT COUNT(*) FROM tasks WHERE company_id = ? AND archived_at IS NULL AND status <> 'completed') AS open_tasks,
            (SELECT COUNT(*) FROM tasks WHERE company_id = ? AND archived_at IS NULL AND status <> 'completed' AND due_date < UTC_DATE()) AS overdue_tasks",
            [$companyId,$companyId,$companyId]);
    } else {
        $result = db_one($db,"SELECT
            (SELECT COUNT(*) FROM job_posts WHERE company_id = ? AND status = 'published') AS published_roles,
            (SELECT COUNT(*) FROM applications WHERE company_id = ?) AS applications,
            (SELECT COUNT(*) FROM applications WHERE company_id = ? AND status IN ('new','reviewing')) AS awaiting_review",
            [$companyId,$companyId,$companyId]);
    }
    if (!$result) throw new RuntimeException('The automation did not return a result.');
    foreach ($result as $key=>$value) $result[$key] = (int)$value;
    $result['generated_at'] = $at->setTimezone(new DateTimeZone('UTC'))->format(DateTimeInterface::ATOM);
    return $result;
}

function neuron_schedule_label(array $neuron): string {
    $time = substr((string)($neuron['schedule_time'] ?? ''),0,5);
    return match ($neuron['schedule_type']) {
        'once' => 'Once',
        'daily' => 'Daily at ' . $time,
        'weekly' => 'Weekly on ' . (['Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday'][(int)($neuron['weekday'] ?? 0) - 1] ?? 'unknown day') . ' at ' . $time,
        default => 'Unsupported schedule',
    };
}
