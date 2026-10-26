<?php
declare(strict_types=1);
require_once __DIR__ . '/mail.php';

function report_storage_directory(): string { return SIMPLR_ROOT . '/storage/report-uploads'; }

function report_frequency_options(): array {
    return ['daily'=>'Daily','weekly'=>'Weekly','biweekly'=>'Every 2 weeks','monthly'=>'Monthly'];
}

function report_due_datetime(string $value): string {
    $trimmed = trim($value);
    if ($trimmed === '') return gmdate('Y-m-d H:i:s');
    $date = new DateTimeImmutable($trimmed, new DateTimeZone('UTC'));
    return $date->format('Y-m-d H:i:s');
}

function report_next_due(string $currentDue, string $frequency): string {
    $date = new DateTimeImmutable($currentDue, new DateTimeZone('UTC'));
    return match ($frequency) {
        'daily' => $date->modify('+1 day')->format('Y-m-d H:i:s'),
        'weekly' => $date->modify('+7 days')->format('Y-m-d H:i:s'),
        'biweekly' => $date->modify('+14 days')->format('Y-m-d H:i:s'),
        'monthly' => $date->modify('+1 month')->format('Y-m-d H:i:s'),
        default => $date->modify('+7 days')->format('Y-m-d H:i:s'),
    };
}

function report_recipients(mysqli $db, array $report): array {
    $emails = [];
    $assigneeEmail = trim((string)($report['assignee_email'] ?? ''));
    if ($assigneeEmail !== '') $emails[] = $assigneeEmail;

    $assignee = trim((string)($report['assignee'] ?? ''));
    if ($assignee !== '') {
        $employee = db_one($db, 'SELECT email FROM employees WHERE company_id = ? AND (unique_id = ? OR email = ? OR CAST(id AS CHAR) = ?) LIMIT 1', [(int)$report['company_id'], $assignee, $assignee, $assignee]);
        if ($employee && trim((string)($employee['email'] ?? '')) !== '') $emails[] = trim((string)$employee['email']);

        $manager = db_one($db, 'SELECT manager_email FROM managers WHERE company_id = ? AND (unique_id = ? OR CAST(id AS CHAR) = ? OR manager_email = ?) LIMIT 1', [(int)$report['company_id'], $assignee, $assignee, $assignee]);
        if ($manager && trim((string)($manager['manager_email'] ?? '')) !== '') $emails[] = trim((string)$manager['manager_email']);
    }

    if (!empty($report['project_id'])) {
        $project = db_one($db, 'SELECT projectManager, project_manager_id FROM projects WHERE company_id = ? AND (CAST(id AS CHAR) = ? OR unique_id = ?) LIMIT 1', [(int)$report['company_id'], (string)$report['project_id'], (string)$report['project_id']]);
        if ($project) {
            $projectManager = trim((string)($project['projectManager'] ?? ''));
            if ($projectManager !== '') {
                $manager = db_one($db, 'SELECT manager_email FROM managers WHERE company_id = ? AND (unique_id = ? OR CAST(id AS CHAR) = ? OR manager_email = ?) LIMIT 1', [(int)$report['company_id'], $projectManager, $projectManager, $projectManager]);
                if ($manager && trim((string)($manager['manager_email'] ?? '')) !== '') $emails[] = trim((string)$manager['manager_email']);
            }
            $projectManagerId = trim((string)($project['project_manager_id'] ?? ''));
            if ($projectManagerId !== '') {
                $manager = db_one($db, 'SELECT manager_email FROM managers WHERE company_id = ? AND (unique_id = ? OR CAST(id AS CHAR) = ? OR manager_email = ?) LIMIT 1', [(int)$report['company_id'], $projectManagerId, $projectManagerId, $projectManagerId]);
                if ($manager && trim((string)($manager['manager_email'] ?? '')) !== '') $emails[] = trim((string)$manager['manager_email']);
            }
        }
    }

    if (!empty($report['task_id'])) {
        $task = db_one($db, 'SELECT assignee FROM tasks WHERE company_id = ? AND id = ? LIMIT 1', [(int)$report['company_id'], (int)$report['task_id']]);
        if ($task) {
            $taskAssignee = trim((string)($task['assignee'] ?? ''));
            if ($taskAssignee !== '') {
                $employee = db_one($db, 'SELECT email FROM employees WHERE company_id = ? AND (unique_id = ? OR email = ? OR CAST(id AS CHAR) = ?) LIMIT 1', [(int)$report['company_id'], $taskAssignee, $taskAssignee, $taskAssignee]);
                if ($employee && trim((string)($employee['email'] ?? '')) !== '') $emails[] = trim((string)$employee['email']);
            }
        }
    }

    $emails = array_values(array_unique(array_filter(array_map('trim', $emails), fn($email) => $email !== '' && filter_var($email, FILTER_VALIDATE_EMAIL))));
    if ($emails) return $emails;

    $owner = db_one($db, 'SELECT email FROM users WHERE company_id = ? AND status = "Verified" ORDER BY id LIMIT 1', [(int)$report['company_id']]);
    if ($owner && filter_var((string)($owner['email'] ?? ''), FILTER_VALIDATE_EMAIL)) return [(string)$owner['email']];

    return [];
}

function report_upload_file(array $file, int $companyId, int $reportId): array {
    if (!is_array($file) || !is_uploaded_file($file['tmp_name'] ?? '')) return ['error' => 'Choose a valid report file before uploading.'];
    $tmpName = $file['tmp_name'];
    $size = filesize($tmpName);
    if (!is_numeric($size) || (int)$size < 1 || (int)$size > 10 * 1024 * 1024) return ['error' => 'Upload a report file up to 10 MB.'];

    $mime = (new finfo(FILEINFO_MIME_TYPE))->file($tmpName);
    $allowed = ['application/pdf','application/msword','application/vnd.openxmlformats-officedocument.wordprocessingml.document','text/plain','image/png','image/jpeg'];
    if (!in_array($mime, $allowed, true)) return ['error' => 'Use a PDF, Word document, text file, PNG or JPEG.'];

    $name = basename((string)($file['name'] ?? 'report'));
    if ($name === '' || mb_strlen($name) > 180) return ['error' => 'Use a report file name between 1 and 180 characters.'];
    $directory = report_storage_directory();
    if (!is_dir($directory) && !mkdir($directory, 0775, true) && !is_dir($directory)) return ['error' => 'Report storage is unavailable.'];

    $stored = bin2hex(random_bytes(32));
    $path = $directory . '/' . $stored;
    if (!move_uploaded_file($tmpName, $path)) return ['error' => 'The file could not be saved.'];
    return ['path' => $path, 'storage_name' => $stored, 'original_name' => $name, 'media_type' => $mime, 'size_bytes' => (int)$size, 'company_id' => $companyId, 'report_id' => $reportId];
}

function send_due_report_notifications(mysqli $db): int {
    $reports = db_rows($db, 'SELECT * FROM project_reports WHERE next_due_at IS NOT NULL AND next_due_at <= UTC_TIMESTAMP() AND status != "approved" AND (last_sent_at IS NULL OR last_sent_at < next_due_at) ORDER BY next_due_at ASC, id ASC');
    $count = 0;
    foreach ($reports as $report) {
        $recipients = report_recipients($db, $report);
        if (!$recipients) continue;

        $subject = 'Report due: ' . $report['title'];
        $body = "Report reminder\n\n" .
            "Title: " . $report['title'] . "\n" .
            "Scope: " . ucfirst((string)$report['report_scope']) . "\n" .
            "Due: " . $report['next_due_at'] . " UTC\n" .
            "Frequency: " . ucfirst((string)$report['frequency']) . "\n" .
            "Notes: " . trim((string)($report['summary'] ?? '')) . "\n\n" .
            "Please update or submit the report in your workspace before the next review cycle.";

        $success = false;
        foreach ($recipients as $email) {
            if (filter_var($email, FILTER_VALIDATE_EMAIL) && send_account_mail($email, $subject, $body)) {
                $success = true;
            }
        }

        if ($success) {
            db_run($db, 'UPDATE project_reports SET last_sent_at = UTC_TIMESTAMP(), next_due_at = ?, status = ? WHERE id = ?', [report_next_due($report['next_due_at'], $report['frequency']), $report['status'] === 'submitted' ? 'submitted' : 'overdue', $report['id']]);
            $count++;
        }
    }
    return $count;
}
