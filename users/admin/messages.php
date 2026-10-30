<?php
require_once __DIR__ . '/../../app/auth.php';
require_once __DIR__ . '/../../app/access.php';
require_once __DIR__ . '/../../app/view.php';
$db = database();
$user = workspace_user($db);
$companyId = (int)($user['company_id'] ?? 0);
$employee = db_one($db,'SELECT id, unique_id, first_name, last_name FROM employees WHERE company_id = ? AND id = ? LIMIT 1',[$companyId,(int)($user['id'] ?? 0)]);
if (!$employee) {
    $employee = db_one($db,'SELECT id, unique_id, first_name, last_name FROM employees WHERE company_id = ? ORDER BY first_name, last_name LIMIT 1',[$companyId]);
}
$contacts = db_rows($db,'SELECT id, unique_id, first_name, last_name FROM employees WHERE company_id = ? AND id <> ? ORDER BY first_name, last_name',[$companyId,(int)($employee['id'] ?? 0)]);
$selectedContact = $contacts[0] ?? null;
$selectedRecipient = (string)($selectedContact['unique_id'] ?? '');
$selectedName = trim((string)($selectedContact['first_name'] ?? '') . ' ' . (string)($selectedContact['last_name'] ?? '')) ?: 'Select a colleague';
if (($ajax = ($_GET['ajax'] ?? '')) === '1') {
    header('Content-Type: application/json');
    $recipient = trim((string)($_GET['recipient'] ?? $_POST['recipient_id'] ?? $_POST['recipient'] ?? ''));
    if (isset($_POST['action']) && $_POST['action'] === 'send') {
        $message = trim((string)($_POST['message'] ?? ''));
        if ($message === '' || $recipient === '') {
            http_response_code(422);
            echo json_encode(['error' => 'Choose a person and write a message.']);
            exit;
        }
        $target = db_one($db,'SELECT id, unique_id, first_name, last_name FROM employees WHERE company_id = ? AND unique_id = ? LIMIT 1',[$companyId,$recipient]);
        if (!$target) {
            http_response_code(404);
            echo json_encode(['error' => 'This conversation could not be found.']);
            exit;
        }
        db_run($db,'INSERT INTO messages (sender_id, recipient_id, message, status, timestamp) VALUES (?, ?, ?, ?, UTC_TIMESTAMP())',[(int)($employee['id'] ?? 0), $target['unique_id'], $message, 'sent']);
        echo json_encode(['ok' => true]);
        exit;
    }
    if ($recipient === '') {
        echo json_encode(['messages' => [], 'contact' => null]);
        exit;
    }
    $target = db_one($db,'SELECT id, unique_id, first_name, last_name FROM employees WHERE company_id = ? AND unique_id = ? LIMIT 1',[$companyId,$recipient]);
    if (!$target) {
        echo json_encode(['messages' => [], 'contact' => null]);
        exit;
    }
    $myUnique = (string)($employee['unique_id'] ?? '');
    $messages = db_rows($db,'SELECT m.id, m.sender_id, m.recipient_id, m.message, m.timestamp, s.first_name AS sender_first_name, s.last_name AS sender_last_name FROM messages m LEFT JOIN employees s ON s.id = m.sender_id WHERE ((m.sender_id = ? AND m.recipient_id = ?) OR (m.sender_id = ? AND m.recipient_id = ?)) ORDER BY m.id ASC',[(int)($employee['id'] ?? 0), $target['unique_id'], (int)$target['id'], $myUnique]);
    $payload = [];
    foreach ($messages as $row) {
        $payload[] = ['id' => (int)($row['id'] ?? 0), 'sender_id' => (int)($row['sender_id'] ?? 0), 'recipient_id' => (string)($row['recipient_id'] ?? ''), 'message' => (string)($row['message'] ?? ''), 'timestamp' => $row['timestamp'] ?? null, 'is_me' => ((int)($row['sender_id'] ?? 0) === (int)($employee['id'] ?? 0))];
    }
    echo json_encode(['messages' => $payload, 'contact' => ['id' => $target['unique_id'], 'name' => trim($target['first_name'] . ' ' . $target['last_name'])]]);
    exit;
}

suite_start('Messages','messages',$user);
?>
<div class="page-heading">
  <div>
    <h1>Messages</h1>
    <p>Live direct messaging for your team, refreshed automatically.</p>
  </div>
  <span class="count-label" id="live-indicator">Realtime updates live</span>
</div>
<div class="messages-layout" style="display:grid; grid-template-columns:minmax(220px, 300px) 1fr; gap:1rem; align-items:start; margin-top:1.5rem;">
  <aside class="panel">
    <div class="panel-head">
      <h2>Contacts</h2>
    </div>
    <div class="panel-body" style="padding:0;">
      <?php if (!$contacts): ?>
        <div class="empty-state compact" style="padding:1.25rem;">
          <h3>No colleagues yet</h3>
          <p>Add a teammate to start a live conversation.</p>
        </div>
      <?php else: ?>
        <?php foreach ($contacts as $contact): $contactName = trim((string)($contact['first_name'] ?? '') . ' ' . (string)($contact['last_name'] ?? '')); $isSelected = ($selectedRecipient === (string)($contact['unique_id'] ?? '')); ?>
          <button type="button" class="message-contact" data-recipient="<?= e((string)($contact['unique_id'] ?? '')) ?>" data-name="<?= e($contactName) ?>" style="display:flex; align-items:center; gap:.75rem; width:100%; background:<?= $isSelected ? '#edf8f6' : 'transparent' ?>; border:0; text-align:left; padding:.85rem 1rem; border-bottom:1px solid #e7edea; cursor:pointer;">
            <span style="width:2.25rem; height:2.25rem; display:grid; place-items:center; border-radius:50%; background:#d9f1e5; color:#0f3d37; font-weight:700;"><?= e(mb_substr($contactName ?: 'T', 0, 1)) ?></span>
            <span style="font-weight:600;"><?= e($contactName) ?></span>
          </button>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>
  </aside>
  <section class="panel" style="min-height: 560px;">
    <div class="panel-head" style="display:flex; justify-content:space-between; align-items:center; gap:1rem;">
      <h2 id="thread-title"><?= e($selectedName) ?></h2>
      <span class="count-label" id="thread-status">online</span>
    </div>
    <div id="message-thread" class="message-thread" style="height:430px; overflow:auto; padding:1rem; background:#f7faf9; border-radius:12px; border:1px solid #e4ece9; margin:0 1rem 1rem;">
      <div class="empty-state compact"><h3>Waiting for a colleague.</h3></div>
    </div>
    <div style="padding:0 1rem 1rem; display:flex; gap:.75rem; align-items:flex-end;">
      <textarea id="message-input" rows="3" maxlength="2000" placeholder="Type a message…" style="flex:1; resize:vertical; border:1px solid #d8e0dd; border-radius:10px; padding:.85rem 1rem; min-height:80px;"></textarea>
      <button type="button" id="send-message" class="button" style="align-self:stretch;">Send</button>
    </div>
  </section>
</div>
<script>
(function () {
  const currentRecipient = <?= json_encode((string)($selectedRecipient ?? ''), JSON_THROW_ON_ERROR) ?>;
  const thread = document.getElementById('message-thread');
  const title = document.getElementById('thread-title');
  const input = document.getElementById('message-input');
  const status = document.getElementById('thread-status');
  const contacts = Array.from(document.querySelectorAll('.message-contact'));
  let activeRecipient = currentRecipient;

  function escapeHtml(value) {
    return String(value).replace(/[&<>"']/g, function (char) {
      return ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' })[char];
    });
  }

  function renderMessages(messages) {
    if (!messages.length) {
      thread.innerHTML = '<div class="empty-state compact"><h3>No messages yet</h3><p>Say hello to start the conversation.</p></div>';
      return;
    }
    const rows = messages.map((message) => {
      const isMine = !!message.is_me;
      const sender = isMine ? 'You' : (message.sender_name || 'Team member');
      return '<div style="display:flex; justify-content:' + (isMine ? 'flex-end' : 'flex-start') + '; margin-bottom:.75rem;">' +
        '<div style="max-width:75%; background:' + (isMine ? '#173d3b' : '#ffffff') + '; color:' + (isMine ? '#ffffff' : '#1d2c2b') + '; border:' + (isMine ? '1px solid #173d3b' : '1px solid #dfe7e4') + '; border-radius:16px; padding:.7rem .9rem; box-shadow:0 8px 18px rgba(10,18,16,.04);">' +
        '<div style="font-size:.75rem; opacity:.8; margin-bottom:.35rem;">' + escapeHtml(sender) + '</div>' +
        '<div>' + escapeHtml(message.message) + '</div>' +
        '<div style="font-size:.7rem; opacity:.75; margin-top:.4rem; text-align:right;">' + escapeHtml(new Date(message.timestamp || Date.now()).toLocaleTimeString([], { hour: 'numeric', minute: '2-digit' })) + '</div>' +
        '</div>' +
        '</div>';
    }).join('');
    thread.innerHTML = rows;
    thread.scrollTop = thread.scrollHeight;
  }

  async function loadMessages() {
    if (!activeRecipient) return;
    const url = new URL(window.location.href);
    url.searchParams.set('ajax', '1');
    url.searchParams.set('recipient', activeRecipient);
    const response = await fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
    if (!response.ok) {
      status.textContent = 'offline';
      return;
    }
    const payload = await response.json();
    if (payload.contact && payload.contact.name) {
      title.textContent = payload.contact.name;
    }
    const messages = (payload.messages || []).map((message) => ({
      ...message,
      sender_name: message.is_me ? 'You' : (payload.contact && payload.contact.name) || 'Team member'
    }));
    renderMessages(messages);
    status.textContent = 'online';
  }

  async function sendMessage() {
    const message = input.value.trim();
    if (!activeRecipient || !message) return;
    const form = new URLSearchParams();
    form.append('action', 'send');
    form.append('recipient_id', activeRecipient);
    form.append('message', message);
    const response = await fetch(window.location.href + (window.location.href.includes('?') ? '&' : '?') + 'ajax=1', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8',
        'X-Requested-With': 'XMLHttpRequest'
      },
      body: form.toString()
    });
    const payload = await response.json();
    if (!response.ok || payload.error) {
      status.textContent = payload.error || 'Message failed';
      return;
    }
    input.value = '';
    await loadMessages();
  }

  contacts.forEach((button) => {
    button.addEventListener('click', function () {
      activeRecipient = button.dataset.recipient || '';
      title.textContent = button.dataset.name || 'Team member';
      contacts.forEach((item) => item.style.background = item === button ? '#edf8f6' : 'transparent');
      loadMessages();
    });
  });

  document.getElementById('send-message').addEventListener('click', sendMessage);
  input.addEventListener('keydown', function (event) {
    if (event.key === 'Enter' && !event.shiftKey) {
      event.preventDefault();
      sendMessage();
    }
  });

  if (activeRecipient) {
    loadMessages();
    setInterval(loadMessages, 5000);
  }
})();
</script>
<?php suite_end(); ?>
