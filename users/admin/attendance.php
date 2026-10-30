<?php
require_once __DIR__ . '/../../app/auth.php';
require_once __DIR__ . '/../../app/access.php';
require_once __DIR__ . '/../../app/view.php';

function ensure_attendance_clock_columns(mysqli $db): void {
    $columns = db_rows($db, 'SELECT COLUMN_NAME AS name FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ?', ['attendance']);
    $names = array_map(static fn(array $row) => strtolower((string)($row['name'] ?? '')), $columns);
    foreach (['clock_in', 'clock_out', 'break_start', 'break_end'] as $column) {
        if (in_array($column, $names, true)) continue;
        $db->query('ALTER TABLE attendance ADD COLUMN ' . $column . ' DATETIME NULL');
    }
}

$db = database();
$user = workspace_user($db);
$companyId = (int)($user['company_id'] ?? 0);
$employee = db_one($db,'SELECT id, unique_id, first_name, last_name FROM employees WHERE company_id = ? AND id = ? LIMIT 1',[$companyId,(int)($user['id'] ?? 0)]);
if (!$employee) {
    $employee = db_one($db,'SELECT id, unique_id, first_name, last_name FROM employees WHERE company_id = ? ORDER BY first_name, last_name LIMIT 1',[$companyId]);
}
$employees = db_rows($db,'SELECT id, first_name, last_name FROM employees WHERE company_id = ? ORDER BY first_name, last_name',[$companyId]);
$selectedEmployeeId = filter_var($_GET['employee'] ?? $employee['id'] ?? null, FILTER_VALIDATE_INT) ?: (int)($employee['id'] ?? 0);
$selectedEmployee = db_one($db,'SELECT id, unique_id, first_name, last_name FROM employees WHERE company_id = ? AND id = ? LIMIT 1',[$companyId,$selectedEmployeeId]);
ensure_attendance_clock_columns($db);

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
    $statusMap = ['clock_in' => 'clocked_in', 'clock_out' => 'clocked_out', 'break_start' => 'on_break', 'break_end' => 'clocked_in'];
    $row = db_one($db,'SELECT * FROM attendance WHERE company_id = ? AND employee_id = ? AND date = ? ORDER BY id DESC LIMIT 1',[$companyId,$employeeId,$today]);
    if ($action === 'clock') {
        $kind = trim((string)($_POST['kind'] ?? $_GET['kind'] ?? 'clock_in'));
        $status = $statusMap[$kind] ?? 'clocked_in';
        if (!$row) {
            db_run($db,'INSERT INTO attendance (employee_id, date, status, company_id, created_at, clock_in) VALUES (?, ?, ?, ?, UTC_TIMESTAMP(), UTC_TIMESTAMP())',[$employeeId, $today, $status, $companyId]);
            $row = db_one($db,'SELECT * FROM attendance WHERE company_id = ? AND employee_id = ? AND date = ? ORDER BY id DESC LIMIT 1',[$companyId,$employeeId,$today]);
        } else {
            if ($kind === 'clock_out') {
                db_run($db,'UPDATE attendance SET clock_out = COALESCE(clock_out, UTC_TIMESTAMP()), status = ? WHERE id = ?',[$status, (int)$row['id']]);
            } elseif ($kind === 'break_start') {
                db_run($db,'UPDATE attendance SET break_start = COALESCE(break_start, UTC_TIMESTAMP()), status = ? WHERE id = ?',[$status, (int)$row['id']]);
            } elseif ($kind === 'break_end') {
                db_run($db,'UPDATE attendance SET break_end = COALESCE(break_end, UTC_TIMESTAMP()), status = ? WHERE id = ?',[$status, (int)$row['id']]);
            } else {
                db_run($db,'UPDATE attendance SET clock_in = COALESCE(clock_in, UTC_TIMESTAMP()), status = ? WHERE id = ?',[$status, (int)$row['id']]);
            }
            $row = db_one($db,'SELECT * FROM attendance WHERE id = ?',[(int)$row['id']]);
        }
        echo json_encode(['ok' => true, 'status' => $row['status'] ?? $status, 'employee' => $employeeId]);
        exit;
    }
    $status = $row['status'] ?? 'clocked_out';
    echo json_encode(['status' => $status, 'today' => $row, 'history' => $history]);
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
      <span class="count-label" id="attendance-status">Ready</span>
    </div>
    <div class="panel-body" style="display:grid; grid-template-columns:repeat(auto-fit,minmax(170px,1fr)); gap:1rem;">
      <button type="button" class="button" data-clock="clock_in">Clock in</button>
      <button type="button" class="button secondary" data-clock="break_start">Start break</button>
      <button type="button" class="button secondary" data-clock="break_end">Resume</button>
      <button type="button" class="button danger" data-clock="clock_out">Clock out</button>
    </div>
    <div class="panel-body" style="margin-top:1rem;">
      <p class="subtle">Current status: <strong id="current-status"><?= e((string)($current['status'] ?? 'clocked_out')) ?></strong></p>
      <div class="table-wrap">
        <table class="data-table">
          <thead>
            <tr>
              <th>Date</th>
              <th>Status</th>
              <th>Clock in</th>
              <th>Clock out</th>
            </tr>
          </thead>
          <tbody>
            <?php if (!$history): ?>
              <tr><td colspan="4">No attendance has been recorded yet.</td></tr>
            <?php else: ?>
              <?php foreach ($history as $entry): ?>
                <tr>
                  <td><?= e((string)($entry['date'] ?? '')) ?></td>
                  <td><?= e((string)($entry['status'] ?? 'clocked_out')) ?></td>
                  <td><?= e((string)($entry['clock_in'] ?? '')) ?></td>
                  <td><?= e((string)($entry['clock_out'] ?? '')) ?></td>
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
  const statusEl = document.getElementById('attendance-status');
  const currentStatusEl = document.getElementById('current-status');

  async function updateStatus(kind) {
    const form = new URLSearchParams();
    form.append('action', 'clock');
    form.append('kind', kind);
    form.append('employee', String(employee));
    const response = await fetch(window.location.href + (window.location.href.includes('?') ? '&' : '?') + 'ajax=1', {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8', 'X-Requested-With': 'XMLHttpRequest' },
      body: form.toString()
    });
    const payload = await response.json();
    if (payload.ok) {
      statusEl.textContent = payload.status || kind;
      currentStatusEl.textContent = payload.status || kind;
    }
  }

  document.querySelectorAll('[data-clock]').forEach((button) => {
    button.addEventListener('click', function () {
      updateStatus(button.dataset.clock);
    });
  });
})();
</script>
<?php suite_end(); ?>
