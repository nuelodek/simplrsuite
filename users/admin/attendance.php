<?php
require_once __DIR__ . '/../../app/auth.php';
require_once __DIR__ . '/../../app/access.php';
require_once __DIR__ . '/../../app/task-work.php';
require_once __DIR__ . '/../../app/view.php';

function attendance_elapsed_seconds(?array $entry, ?int $now = null): int {
    if (!$entry || empty($entry['clock_in'])) return 0;
    $start = strtotime((string)$entry['clock_in'] . ' UTC');
    if ($start === false) return 0;
    $end = !empty($entry['clock_out']) ? strtotime((string)$entry['clock_out'] . ' UTC') : ($now ?? time());
    if ($end === false || $end <= $start) return 0;
    $breakStart = !empty($entry['break_start']) ? strtotime((string)$entry['break_start'] . ' UTC') : false;
    $breakEnd = !empty($entry['break_end'])
        ? strtotime((string)$entry['break_end'] . ' UTC')
        : ((!empty($entry['clock_out']) || ($entry['status'] ?? '') === 'on_break') ? $end : false);
    $breakSeconds = 0;
    if ($breakStart !== false && $breakEnd !== false) {
        $breakSeconds = max(0, min($breakEnd, $end) - max($breakStart, $start));
    }
    return max(0, $end - $start - $breakSeconds);
}

function attendance_duration_text(int $seconds): string {
    $seconds = max(0, $seconds);
    $minutes = intdiv($seconds, 60);
    $hours = intdiv($minutes, 60);
    $remainingMinutes = $minutes % 60;
    return $hours . 'h ' . str_pad((string)$remainingMinutes, 2, '0', STR_PAD_LEFT) . 'm ' . str_pad((string)($seconds % 60), 2, '0', STR_PAD_LEFT) . 's';
}

$db = database();
$user = workspace_user($db);
$companyId = (int)($user['company_id'] ?? 0);
$role = (string)($_SESSION['auth_role'] ?? '');
$isAdmin = $role === 'admin';
if (!$isAdmin) require_module_access($db,$user,'attendance');
$staffEmployeeId = match ($role) {
    'employee' => (int)($user['id'] ?? 0),
    'manager' => (int)($user['employee_id'] ?? 0),
    default => 0,
};
if (!$isAdmin && $staffEmployeeId < 1) fail('Your account is not linked to an employee record for attendance.',403);
$employee = !$isAdmin
    ? db_one($db,'SELECT id, unique_id, first_name, last_name FROM employees WHERE company_id = ? AND id = ? LIMIT 1',[$companyId,$staffEmployeeId])
    : null;
$employees = $isAdmin
    ? db_rows($db,'SELECT id, first_name, last_name FROM employees WHERE company_id = ? ORDER BY first_name, last_name',[$companyId])
    : ($employee ? [$employee] : []);
$defaultEmployeeId = $isAdmin ? (int)($employees[0]['id'] ?? 0) : $staffEmployeeId;
$requestedEmployeeId = filter_var($_GET['employee'] ?? $defaultEmployeeId, FILTER_VALIDATE_INT) ?: $defaultEmployeeId;
$selectedEmployeeId = $isAdmin ? $requestedEmployeeId : $staffEmployeeId;
$selectedEmployee = db_one($db,'SELECT id, unique_id, first_name, last_name FROM employees WHERE company_id = ? AND id = ? LIMIT 1',[$companyId,$selectedEmployeeId]);
if (!$selectedEmployee && $selectedEmployeeId > 0) fail('This employee could not be found in your workspace.',404);

$today = date('Y-m-d');
$current = db_one($db,'SELECT * FROM attendance WHERE company_id = ? AND employee_id = ? AND date = ? ORDER BY id DESC LIMIT 1',[$companyId,(int)($selectedEmployee['id'] ?? 0),$today]);
$history = db_rows($db,'SELECT * FROM attendance WHERE company_id = ? AND employee_id = ? ORDER BY date DESC, id DESC LIMIT 7',[$companyId,(int)($selectedEmployee['id'] ?? 0)]);

if (($ajax = ($_GET['ajax'] ?? '')) === '1') {
    header('Content-Type: application/json');
    $action = trim((string)($_POST['action'] ?? $_GET['action'] ?? ''));
    $employeeId = filter_var($_POST['employee'] ?? $_GET['employee'] ?? $selectedEmployeeId, FILTER_VALIDATE_INT) ?: $selectedEmployeeId;
    if (!$employeeId) {
        http_response_code(422);
        echo json_encode(['error' => 'No employee selected.']);
        exit;
    }
    if (!db_one($db,'SELECT id FROM employees WHERE company_id = ? AND id = ? LIMIT 1',[$companyId,$employeeId])) {
        http_response_code(404);
        echo json_encode(['error' => 'This employee could not be found in your workspace.']);
        exit;
    }
    $statusMap = ['clock_in' => 'clocked_in', 'clock_out' => 'clocked_out', 'break_start' => 'on_break', 'break_end' => 'clocked_in'];
    $row = db_one($db,'SELECT * FROM attendance WHERE company_id = ? AND employee_id = ? AND date = ? ORDER BY id DESC LIMIT 1',[$companyId,$employeeId,$today]);
    if ($action === 'clock') {
        require_post();
        if (!$isAdmin) {
            require_module_access($db,$user,'attendance',true);
            if ($employeeId !== $staffEmployeeId) fail('You can only update your own attendance.',403);
        }
        $db->begin_transaction();
        try {
        if (!db_one($db,'SELECT id FROM employees WHERE company_id = ? AND id = ? FOR UPDATE',[$companyId,$employeeId])) {
            $db->rollback();
            fail('This employee could not be found in your workspace.',404);
        }
        $row = db_one($db,'SELECT * FROM attendance WHERE company_id = ? AND employee_id = ? AND date = ? ORDER BY id DESC LIMIT 1 FOR UPDATE',[$companyId,$employeeId,$today]);
        $kind = trim((string)($_POST['kind'] ?? $_GET['kind'] ?? 'clock_in'));
        if (!isset($statusMap[$kind])) {
            $db->rollback();
            http_response_code(422);
            echo json_encode(['error' => 'Choose a valid attendance action.']);
            exit;
        }
        $status = $statusMap[$kind];
        $currentStatus = (string)($row['status'] ?? '');
        $validTransition = !$row
            ? $kind === 'clock_in'
            : match ($kind) {
                'clock_in' => false,
                'break_start' => $currentStatus === 'clocked_in',
                'break_end' => $currentStatus === 'on_break',
                'clock_out' => in_array($currentStatus,['clocked_in','on_break'],true),
            };
        if (!$validTransition) {
            $db->rollback();
            http_response_code(409);
            echo json_encode(['error' => 'That action is not available in the current attendance state.']);
            exit;
        }
        if (!$row) {
            db_run($db,'INSERT INTO attendance (employee_id, date, status, company_id, created_at, clock_in) VALUES (?, ?, ?, ?, UTC_TIMESTAMP(), UTC_TIMESTAMP())',[$employeeId, $today, $status, $companyId]);
            $row = db_one($db,'SELECT * FROM attendance WHERE company_id = ? AND employee_id = ? AND date = ? ORDER BY id DESC LIMIT 1',[$companyId,$employeeId,$today]);
        } else {
            if ($kind === 'clock_out') {
                db_run($db,'UPDATE attendance SET clock_out = UTC_TIMESTAMP(), break_end = IF(status = \'on_break\', UTC_TIMESTAMP(), break_end), status = ? WHERE id = ?',[$status, (int)$row['id']]);
            } elseif ($kind === 'break_start') {
                db_run($db,'UPDATE attendance SET break_start = UTC_TIMESTAMP(), break_end = NULL, status = ? WHERE id = ?',[$status, (int)$row['id']]);
            } elseif ($kind === 'break_end') {
                db_run($db,'UPDATE attendance SET break_end = UTC_TIMESTAMP(), status = ? WHERE id = ?',[$status, (int)$row['id']]);
            }
            $row = db_one($db,'SELECT * FROM attendance WHERE id = ?',[(int)$row['id']]);
        }
        if (in_array($kind,['clock_out','break_start'],true)) task_work_pause_active($db,$companyId,$employeeId);
        $db->commit();
        } catch (Throwable $error) {
            $db->rollback();
            throw $error;
        }
        echo json_encode([
            'ok' => true,
            'status' => $row['status'] ?? $status,
            'worked_seconds' => attendance_elapsed_seconds($row),
            'today' => $row,
            'employee' => $employeeId,
        ]);
        exit;
    }
    $status = $row['status'] ?? 'clocked_out';
    echo json_encode(['status' => $status, 'worked_seconds' => attendance_elapsed_seconds($row), 'today' => $row]);
    exit;
}

suite_start('Attendance','attendance',$user);
?>
<div class="page-heading">
  <div>
    <h1>Attendance</h1>
    <p>Clock in, take breaks, and keep today’s time tracked in real time.</p>
  </div>
  <span class="count-label">Live status</span>
</div>
<div class="messages-layout" style="display:grid; grid-template-columns:minmax(220px, 280px) 1fr; gap:1rem; align-items:start; margin-top:1.5rem;">
  <aside class="panel">
    <div class="panel-head">
      <h2>Team</h2>
    </div>
    <div class="panel-body" style="padding:0;">
      <?php foreach ($employees as $person): $personName = trim((string)($person['first_name'] ?? '') . ' ' . (string)($person['last_name'] ?? '')); $isSelected = ((int)($person['id'] ?? 0) === (int)$selectedEmployeeId); ?>
        <a href="?employee=<?= (int)($person['id'] ?? 0) ?>" style="display:flex; align-items:center; justify-content:space-between; width:100%; text-decoration:none; background:<?= $isSelected ? '#edf8f6' : 'transparent' ?>; border:0; color:inherit; border-bottom:1px solid #e7edea; padding:.85rem 1rem;">
          <span style="font-weight:600;"><?= e($personName) ?></span>
          <span class="count-label"><?= $isSelected ? 'Selected' : 'View' ?></span>
        </a>
      <?php endforeach; ?>
    </div>
  </aside>

  <section class="panel">
    <div class="panel-head" style="display:flex; justify-content:space-between; align-items:center; gap:1rem;">
      <h2><?= e(trim((string)($selectedEmployee['first_name'] ?? '') . ' ' . (string)($selectedEmployee['last_name'] ?? ''))) ?></h2>
      <span class="count-label" id="attendance-status"><?= e(str_replace('_', ' ', (string)($current['status'] ?? 'clocked_out'))) ?></span>
    </div>
    <div class="panel-body" style="display:grid; grid-template-columns:repeat(auto-fit,minmax(170px,1fr)); gap:1rem;">
      <button type="button" class="button" data-clock="clock_in">Clock in</button>
      <button type="button" class="button secondary" data-clock="break_start">Start break</button>
      <button type="button" class="button secondary" data-clock="break_end">Resume</button>
      <button type="button" class="button danger" data-clock="clock_out">Clock out</button>
    </div>
    <div class="panel-body" style="margin-top:1rem;">
      <p class="subtle">Current status: <strong id="current-status"><?= e((string)($current['status'] ?? 'clocked_out')) ?></strong></p>
      <article class="stat-card" style="margin-bottom:1.25rem;">
        <div class="stat-label">Time worked today</div>
        <div class="stat-value" id="worked-today" data-seconds="<?= attendance_elapsed_seconds($current) ?>"><?= e(attendance_duration_text(attendance_elapsed_seconds($current))) ?></div>
        <div class="stat-foot" id="worked-help"><?= ($current['status'] ?? '') === 'on_break' ? 'Break time is excluded.' : 'Break time is excluded; the total updates live.' ?></div>
      </article>
      <div class="table-wrap">
        <table class="data-table">
          <thead>
            <tr>
              <th>Date</th>
              <th>Status</th>
              <th>Clock in</th>
              <th>Clock out</th>
              <th>Time worked</th>
            </tr>
          </thead>
          <tbody id="attendance-history">
            <?php if (!$history): ?>
              <tr id="empty-attendance-row"><td colspan="5">No attendance has been recorded yet.</td></tr>
            <?php else: ?>
              <?php foreach ($history as $entry): ?>
                <?php $isToday = (string)($entry['date'] ?? '') === $today; ?>
                <tr<?= $isToday ? ' id="today-attendance-row"' : '' ?>>
                  <td><?= e((string)($entry['date'] ?? '')) ?></td>
                  <td<?= $isToday ? ' id="today-attendance-status"' : '' ?>><?= e((string)($entry['status'] ?? 'clocked_out')) ?></td>
                  <td<?= $isToday ? ' id="today-clock-in"' : '' ?>><?= e((string)($entry['clock_in'] ?? '')) ?></td>
                  <td<?= $isToday ? ' id="today-clock-out"' : '' ?>><?= e((string)($entry['clock_out'] ?? '')) ?></td>
                  <td<?= $isToday ? ' id="today-worked-history" data-seconds="' . attendance_elapsed_seconds($entry) . '"' : '' ?>><?= e(attendance_duration_text(attendance_elapsed_seconds($entry))) ?></td>
                </tr>
              <?php endforeach; ?>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </section>
</div>
<script>
(function () {
  const employee = <?= json_encode((int)($selectedEmployee['id'] ?? 0), JSON_THROW_ON_ERROR) ?>;
  const csrfToken = <?= json_encode(csrf_token(), JSON_THROW_ON_ERROR) ?>;
  const statusEl = document.getElementById('attendance-status');
  const currentStatusEl = document.getElementById('current-status');
  const workedEl = document.getElementById('worked-today');
  const historyWorkedEl = document.getElementById('today-worked-history');
  const workedHelpEl = document.getElementById('worked-help');
  const historyBody = document.getElementById('attendance-history');
  let workedSeconds = Number(workedEl.dataset.seconds || 0);
  let activeStatus = currentStatusEl.textContent.trim();

  function formatDuration(seconds) {
    seconds = Math.max(0, seconds);
    const minutes = Math.floor(seconds / 60);
    const hours = Math.floor(minutes / 60);
    return hours + 'h ' + String(minutes % 60).padStart(2, '0') + 'm ' + String(Math.floor(seconds % 60)).padStart(2, '0') + 's';
  }

  function renderWorkedTime() {
    const label = formatDuration(workedSeconds);
    workedEl.textContent = label;
    workedEl.dataset.seconds = String(workedSeconds);
    if (historyWorkedEl) {
      historyWorkedEl.textContent = label;
      historyWorkedEl.dataset.seconds = String(workedSeconds);
    }
  }

  function renderTodayRow(entry) {
    if (!entry) return;
    let row = document.getElementById('today-attendance-row');
    if (!row) {
      document.getElementById('empty-attendance-row')?.remove();
      row = document.createElement('tr');
      row.id = 'today-attendance-row';
      row.innerHTML = '<td></td><td id="today-attendance-status"></td><td id="today-clock-in"></td><td id="today-clock-out"></td><td id="today-worked-history"></td>';
      historyBody.prepend(row);
    }
    row.children[0].textContent = entry.date || '';
    document.getElementById('today-attendance-status').textContent = entry.status || 'clocked_out';
    document.getElementById('today-clock-in').textContent = entry.clock_in || '';
    document.getElementById('today-clock-out').textContent = entry.clock_out || '';
  }

  function applyStatus(payload) {
    activeStatus = payload.status || activeStatus;
    currentStatusEl.textContent = activeStatus;
    statusEl.textContent = activeStatus.replaceAll('_', ' ');
    workedSeconds = Number(payload.worked_seconds || 0);
    workedHelpEl.textContent = activeStatus === 'on_break'
      ? 'Break time is excluded.'
      : 'Break time is excluded; the total updates live.';
    renderTodayRow(payload.today);
    renderWorkedTime();
  }

  async function updateStatus(kind) {
    const form = new URLSearchParams();
    form.append('action', 'clock');
    form.append('kind', kind);
    form.append('employee', String(employee));
    const response = await fetch(window.location.href + (window.location.href.includes('?') ? '&' : '?') + 'ajax=1', {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8', 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-Token': csrfToken },
      body: form.toString()
    });
    const payload = await response.json();
    if (payload.ok) {
      applyStatus(payload);
    } else {
      statusEl.textContent = payload.error || 'Could not update attendance';
    }
  }

  window.setInterval(function () {
    if (activeStatus === 'clocked_in') {
      workedSeconds += 1;
      renderWorkedTime();
    }
  }, 1000);

  window.setInterval(async function () {
    try {
      const url = new URL(window.location.href);
      url.searchParams.set('ajax', '1');
      url.searchParams.set('employee', String(employee));
      const response = await fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
      if (!response.ok) return;
      applyStatus(await response.json());
    } catch (error) {
      statusEl.textContent = 'Status refresh failed';
    }
  }, 30000);

  document.querySelectorAll('[data-clock]').forEach((button) => {
    button.addEventListener('click', function () {
      updateStatus(button.dataset.clock);
    });
  });
})();
</script>
<?php suite_end(); ?>
