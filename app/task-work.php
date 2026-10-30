<?php
declare(strict_types=1);

function task_work_pause_active(mysqli $db, int $companyId, int $employeeId): void {
    db_run($db,'UPDATE task_work_sessions SET ended_at = UTC_TIMESTAMP() WHERE company_id = ? AND employee_id = ? AND ended_at IS NULL',[$companyId,$employeeId]);
}

function task_work_pause_task(mysqli $db, int $companyId, int $taskId): void {
    db_run($db,'UPDATE task_work_sessions SET ended_at = UTC_TIMESTAMP() WHERE company_id = ? AND task_id = ? AND ended_at IS NULL',[$companyId,$taskId]);
}

function task_work_reconcile_attendance(mysqli $db, int $companyId, int $employeeId, string $today): void {
    $attendance = db_one($db,'SELECT status FROM attendance WHERE company_id = ? AND employee_id = ? AND date = ? ORDER BY id DESC LIMIT 1',[$companyId,$employeeId,$today]);
    if (($attendance['status'] ?? '') !== 'clocked_in') task_work_pause_active($db,$companyId,$employeeId);
}

function task_work_summaries(mysqli $db, int $companyId, int $employeeId, array $taskIds): array {
    $taskIds = array_values(array_unique(array_filter(array_map('intval',$taskIds),static fn(int $id): bool => $id > 0)));
    if (!$taskIds) return [];
    $placeholders = implode(',',array_fill(0,count($taskIds),'?'));
    $rows = db_rows($db,"SELECT task_id, SUM(TIMESTAMPDIFF(SECOND,started_at,COALESCE(ended_at,UTC_TIMESTAMP()))) AS worked_seconds, COUNT(DISTINCT DATE(started_at)) AS work_days, SUM(ended_at IS NULL) AS active_sessions FROM task_work_sessions WHERE company_id = ? AND employee_id = ? AND task_id IN ($placeholders) GROUP BY task_id",[$companyId,$employeeId,...$taskIds]);
    $summaries = [];
    foreach ($rows as $row) {
        $summaries[(int)$row['task_id']] = [
            'worked_seconds' => (int)$row['worked_seconds'],
            'work_days' => (int)$row['work_days'],
            'active' => (int)$row['active_sessions'] > 0,
        ];
    }
    return $summaries;
}

function task_work_forecast(?float $estimatedHours, array $summary, string $status): array {
    if ($estimatedHours === null || $estimatedHours <= 0 || $status === 'completed') return ['remaining_seconds'=>null,'projected_date'=>null];
    $workedSeconds = max(0,(int)($summary['worked_seconds'] ?? 0));
    $remainingSeconds = max(0,(int)round($estimatedHours * 3600) - $workedSeconds);
    if ($remainingSeconds === 0) return ['remaining_seconds'=>0,'projected_date'=>null];
    $workDays = max(0,(int)($summary['work_days'] ?? 0));
    if ($workedSeconds === 0 || $workDays === 0) return ['remaining_seconds'=>$remainingSeconds,'projected_date'=>null];
    $averageSecondsPerDay = $workedSeconds / $workDays;
    if ($averageSecondsPerDay <= 0) return ['remaining_seconds'=>$remainingSeconds,'projected_date'=>null];
    $daysNeeded = max(1,(int)ceil($remainingSeconds / $averageSecondsPerDay));
    $date = new DateTimeImmutable('today');
    while ($daysNeeded > 0) {
        $date = $date->modify('+1 day');
        if ((int)$date->format('N') < 6) $daysNeeded--;
    }
    return ['remaining_seconds'=>$remainingSeconds,'projected_date'=>$date->format('Y-m-d')];
}

function task_work_duration(int $seconds): string {
    $minutes = intdiv(max(0,$seconds),60);
    return intdiv($minutes,60) . 'h ' . str_pad((string)($minutes % 60),2,'0',STR_PAD_LEFT) . 'm';
}
