<?php
require_once __DIR__ . '/../../app/auth.php';
require_once __DIR__ . '/../../app/messaging.php';
require_once __DIR__ . '/../../app/view.php';

$db = database();
$user = workspace_user($db);
$identity = chat_require_access($db,$user,false);
if (($_GET['ajax'] ?? '') === '1') chat_api($db,$user,$identity);

$exclude = [$identity['role'] . ':' . $identity['id']];
$people = chat_people($db,(int)$identity['company_id'],$exclude);
$conversations = chat_conversation_list($db,$identity);
$initialConversation = filter_var($_GET['conversation'] ?? null,FILTER_VALIDATE_INT,['options'=>['min_range'=>1]]) ?: (int)($conversations[0]['id'] ?? 0);
$initialConversationRow = $initialConversation ? chat_conversation($db,(int)$identity['company_id'],$initialConversation,$identity) : null;
if ($initialConversation && !$initialConversationRow) $initialConversation = 0;

suite_start('Messages','messages',$user);
?>
<div class="page-heading">
  <div>
    <h1>Messages</h1>
    <p>Chat with teammates, share workspace records, and keep project conversations connected.</p>
  </div>
  <span class="count-label" id="live-indicator">Refreshes every 5 seconds</span>
</div>
<div class="messages-layout" style="display:grid;grid-template-columns:minmax(240px,320px) minmax(0,1fr);gap:1rem;align-items:start;margin-top:1.5rem;">
  <aside class="panel">
    <div class="panel-head" style="display:flex;justify-content:space-between;align-items:center;gap:.5rem;">
      <h2>Chats</h2>
      <button type="button" class="button small secondary" id="new-group-toggle">New group</button>
    </div>
    <details id="group-create" style="padding:0 1rem 1rem;">
      <summary class="text-link" style="cursor:pointer;">Create a private group</summary>
      <div style="display:grid;gap:.65rem;margin-top:.75rem;">
        <label for="group-name">Group name <span class="subtle">(optional)</span></label>
        <input id="group-name" type="text" maxlength="120" placeholder="e.g. Website launch">
        <label for="group-search">Add teammates</label>
        <input id="group-search" type="search" placeholder="Find teammates…" autocomplete="off">
        <div id="group-people" style="max-height:180px;overflow:auto;border:1px solid #e4ece9;border-radius:8px;">
          <?php foreach ($people as $person): ?>
            <label data-group-person="<?= e(mb_strtolower($person['name'])) ?>" style="display:flex;align-items:center;gap:.5rem;padding:.45rem .6rem;border-bottom:1px solid #edf1ef;">
              <input type="checkbox" value="<?= e($person['role'] . ':' . $person['id']) ?>" data-role="<?= e($person['role']) ?>" data-id="<?= (int)$person['id'] ?>">
              <span><?= e($person['name']) ?> <small class="subtle">· <?= e($person['role_label']) ?></small></span>
            </label>
          <?php endforeach; ?>
        </div>
        <button type="button" class="button small" id="create-group">Create group</button>
        <small id="group-status" role="status"></small>
      </div>
    </details>
    <div class="panel-head"><h3>Recent conversations</h3></div>
    <nav id="conversation-list" aria-label="Recent conversations" style="padding:0;">
      <?php if (!$conversations): ?><p class="panel-body subtle">No conversations yet. Choose a teammate to start one.</p><?php endif; ?>
      <?php foreach ($conversations as $conversation): ?>
        <button type="button" class="message-contact conversation-choice" data-conversation="<?= (int)$conversation['id'] ?>" style="display:block;width:100%;text-align:left;background:<?= (int)$conversation['id'] === $initialConversation ? '#edf8f6' : 'transparent' ?>;border:0;border-bottom:1px solid #e7edea;padding:.8rem 1rem;cursor:pointer;">
          <strong><?= e($conversation['title']) ?></strong>
          <small class="subtle" style="display:block;margin-top:.2rem;"><?= $conversation['kind'] === 'group' ? 'Group chat' : 'Direct message' ?><?= !empty($conversation['latest_preview']) ? ' · ' . e(mb_strimwidth((string)$conversation['latest_preview'],0,54,'…')) : '' ?></small>
        </button>
      <?php endforeach; ?>
    </nav>
    <div class="panel-head"><h3>Start a direct chat</h3></div>
    <div id="contact-list" style="max-height:260px;overflow:auto;">
      <?php if (!$people): ?><p class="panel-body subtle">No active teammates are available.</p><?php endif; ?>
      <?php foreach ($people as $person): ?>
        <button type="button" class="message-contact start-direct" data-role="<?= e($person['role']) ?>" data-id="<?= (int)$person['id'] ?>" style="display:flex;align-items:center;gap:.65rem;width:100%;background:transparent;border:0;border-bottom:1px solid #e7edea;text-align:left;padding:.65rem 1rem;cursor:pointer;">
          <span style="width:2rem;height:2rem;display:grid;place-items:center;border-radius:50%;background:#d9f1e5;color:#0f3d37;font-weight:700;"><?= e(mb_substr($person['name'] ?: 'T',0,1)) ?></span>
          <span><strong><?= e($person['name']) ?></strong><small class="subtle" style="display:block;"><?= e($person['role_label']) ?></small></span>
        </button>
      <?php endforeach; ?>
    </div>
  </aside>
  <section class="panel" style="min-height:620px;">
    <div class="panel-head" style="display:flex;justify-content:space-between;align-items:center;gap:1rem;">
      <div><h2 id="thread-title"><?= e($initialConversationRow ? chat_conversation_title($db,$initialConversationRow,$identity) : 'Select a conversation') ?></h2><small id="thread-kind" class="subtle"><?= $initialConversationRow ? e(ucfirst($initialConversationRow['kind']) . ' chat') : '' ?></small></div>
      <span class="count-label" id="thread-status">Connecting…</span>
    </div>
    <div id="message-thread" aria-live="polite" style="height:440px;overflow:auto;padding:1rem;background:#f7faf9;border-radius:12px;border:1px solid #e4ece9;margin:0 1rem 1rem;">
      <div class="empty-state compact"><h3><?= $initialConversation ? 'Loading conversation…' : 'Choose a teammate or create a group to begin.' ?></h3></div>
    </div>
    <div id="reference-suggestions" role="listbox" aria-label="Workspace references" hidden style="margin:0 1rem .5rem;max-height:180px;overflow:auto;border:1px solid #dfe7e4;border-radius:8px;background:#fff;"></div>
    <div style="padding:0 1rem 1rem;display:flex;gap:.75rem;align-items:flex-end;">
      <div style="flex:1;position:relative;">
        <label class="sr-only" for="message-input">Write a message</label>
        <textarea id="message-input" rows="3" maxlength="4000" placeholder="Message… Use @ to mention a teammate or reference a record." style="width:100%;resize:vertical;border:1px solid #d8e0dd;border-radius:10px;padding:.85rem 1rem;min-height:80px;"></textarea>
        <small class="subtle">Type @ to find teammates and workspace records.</small>
      </div>
      <button type="button" id="send-message" class="button" style="align-self:stretch;"<?= !$initialConversation ? ' disabled' : '' ?>>Send</button>
    </div>
    <div id="share-panel" hidden style="padding:0 1rem 1rem;display:flex;gap:.5rem;align-items:center;">
      <label for="share-target">Share selected message to</label>
      <select id="share-target"><option value="">Choose a conversation</option></select>
      <button type="button" id="share-message" class="button small secondary">Share</button>
      <button type="button" id="cancel-share" class="button small secondary">Cancel</button>
      <small id="share-status" role="status"></small>
    </div>
  </section>
</div>
<script>
(function () {
  const csrfToken = <?= json_encode(csrf_token(),JSON_THROW_ON_ERROR) ?>;
  const initialConversation = <?= (int)$initialConversation ?>;
  let activeConversation = initialConversation;
  let conversations = [];
  let selectedReferences = [];
  let sharingMessage = 0;
  let searchTimer = 0;
  const thread = document.getElementById('message-thread');
  const title = document.getElementById('thread-title');
  const kind = document.getElementById('thread-kind');
  const status = document.getElementById('thread-status');
  const composer = document.getElementById('message-input');
  const suggestionBox = document.getElementById('reference-suggestions');
  const sendButton = document.getElementById('send-message');
  const sharePanel = document.getElementById('share-panel');

  function escapeHtml(value) {
    return String(value).replace(/[&<>"']/g, function (char) {
      return ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'})[char];
    });
  }

  function apiUrl(action, params) {
    const url = new URL('/users/admin/messages.php',window.location.origin);
    url.searchParams.set('ajax','1');
    url.searchParams.set('action',action);
    Object.entries(params || {}).forEach(([key,value]) => url.searchParams.set(key,String(value)));
    return url;
  }

  async function request(action, params) {
    const response = await fetch(apiUrl(action),{
      method:'POST',
      headers:{'Accept':'application/json','Content-Type':'application/x-www-form-urlencoded; charset=UTF-8'},
      body:new URLSearchParams(Object.assign({csrf_token:csrfToken},params || {})).toString()
    });
    const payload = await response.json();
    if (!response.ok || payload.ok === false) throw new Error(payload.error || 'The request could not be completed.');
    return payload;
  }

  async function get(action, params) {
    const response = await fetch(apiUrl(action,params),{headers:{'Accept':'application/json','X-Requested-With':'XMLHttpRequest'}});
    const payload = await response.json();
    if (!response.ok || payload.ok === false) throw new Error(payload.error || 'The request could not be completed.');
    return payload;
  }

  function renderBody(body, references) {
    const byToken = new Map((references || []).map((reference) => [reference.token,reference]));
    const matcher = /\[\[ref:([a-f0-9]{32})\]\]/g;
    let output = '';
    let cursor = 0;
    let match;
    while ((match = matcher.exec(body || '')) !== null) {
      output += escapeHtml((body || '').slice(cursor,match.index));
      const reference = byToken.get(match[1]);
      if (reference) {
        const label = '@' + escapeHtml(reference.label);
        output += reference.target_url
          ? '<a class="message-reference" href="' + escapeHtml(reference.target_url) + '">' + label + '</a>'
          : '<span class="message-reference">' + label + '</span>';
      } else {
        output += escapeHtml(match[0]);
      }
      cursor = matcher.lastIndex;
    }
    output += escapeHtml((body || '').slice(cursor));
    return output.replace(/\n/g,'<br>');
  }

  function renderMessages(messages) {
    if (!messages.length) {
      thread.innerHTML = '<div class="empty-state compact"><h3>No messages yet</h3><p>Start the conversation with a message or a workspace reference.</p></div>';
      return;
    }
    const wasAtBottom = thread.scrollHeight - thread.scrollTop <= thread.clientHeight + 80;
    thread.innerHTML = messages.map((message) => {
      const mine = !!message.is_me;
      const quoted = message.quote_text !== null && message.quote_text !== undefined;
      return '<article style="display:flex;justify-content:' + (mine ? 'flex-end' : 'flex-start') + ';margin-bottom:.8rem;">' +
        '<div style="max-width:82%;background:' + (mine ? '#173d3b' : '#fff') + ';color:' + (mine ? '#fff' : '#1d2c2b') + ';border:1px solid ' + (mine ? '#173d3b' : '#dfe7e4') + ';border-radius:16px;padding:.7rem .9rem;box-shadow:0 8px 18px rgba(10,18,16,.04);">' +
        '<div style="font-size:.75rem;opacity:.8;margin-bottom:.35rem;">' + escapeHtml(mine ? 'You' : message.sender_name) + '</div>' +
        (quoted ? '<blockquote style="margin:.25rem 0 .55rem;padding:.6rem .7rem;border-left:3px solid #cce987;background:rgba(127,160,139,.12);border-radius:4px;"><small>Shared message</small><div>' + renderBody(message.quote_text,message.references || []) + '</div>' + (message.quote_url ? '<a href="' + escapeHtml(message.quote_url) + '" style="color:inherit;">Open original conversation</a>' : '') + '</blockquote>' : '') +
        (message.body ? '<div>' + renderBody(message.body,message.references) + '</div>' : '') +
        '<div style="font-size:.7rem;opacity:.75;margin-top:.4rem;text-align:right;">' + escapeHtml(new Date(message.created_at).toLocaleString([], {month:'short',day:'numeric',hour:'numeric',minute:'2-digit'})) + '</div>' +
        '<button type="button" data-share-message="' + Number(message.id) + '" style="margin-top:.3rem;border:0;background:transparent;color:inherit;text-decoration:underline;cursor:pointer;font-size:.75rem;">Share message</button>' +
        '</div></article>';
    }).join('');
    if (wasAtBottom) thread.scrollTop = thread.scrollHeight;
  }

  function renderConversationList() {
    const list = document.getElementById('conversation-list');
    if (!conversations.length) {
      list.innerHTML = '<p class="panel-body subtle">No conversations yet. Choose a teammate to start one.</p>';
      return;
    }
    list.innerHTML = conversations.map((conversation) =>
      '<button type="button" class="message-contact conversation-choice" data-conversation="' + Number(conversation.id) + '" style="display:block;width:100%;text-align:left;background:' + (Number(conversation.id) === activeConversation ? '#edf8f6' : 'transparent') + ';border:0;border-bottom:1px solid #e7edea;padding:.8rem 1rem;cursor:pointer;">' +
      '<strong>' + escapeHtml(conversation.title) + '</strong><small class="subtle" style="display:block;margin-top:.2rem;">' + (conversation.kind === 'group' ? 'Group chat' : 'Direct message') + (conversation.latest_preview ? ' · ' + escapeHtml(conversation.latest_preview.slice(0,54)) : '') + '</small></button>'
    ).join('');
  }

  async function loadConversations() {
    const payload = await get('list');
    conversations = payload.conversations || [];
    renderConversationList();
    const select = document.getElementById('share-target');
    const selected = select.value;
    select.innerHTML = '<option value="">Choose a conversation</option>' + conversations
      .filter((conversation) => Number(conversation.id) !== activeConversation)
      .map((conversation) => '<option value="' + Number(conversation.id) + '">' + escapeHtml(conversation.title) + '</option>').join('');
    if (selected) select.value = selected;
  }

  async function openConversation(conversationId) {
    activeConversation = Number(conversationId);
    sendButton.disabled = !activeConversation;
    suggestionBox.hidden = true;
    renderConversationList();
    const payload = await get('thread',{conversation:activeConversation});
    title.textContent = payload.conversation.title;
    kind.textContent = payload.conversation.kind === 'group' ? 'Group chat' : 'Direct message';
    renderMessages(payload.messages || []);
    status.textContent = 'Live';
    const url = new URL(window.location.href);
    url.searchParams.set('conversation',String(activeConversation));
    history.replaceState(null,'',url);
  }

  async function refresh() {
    try {
      await loadConversations();
      if (activeConversation) await openConversation(activeConversation);
      else status.textContent = 'Live';
    } catch (error) {
      status.textContent = 'Connection issue';
    }
  }

  document.getElementById('conversation-list').addEventListener('click',function (event) {
    const button = event.target.closest('[data-conversation]');
    if (!button) return;
    openConversation(button.dataset.conversation).catch((error) => { status.textContent = error.message; });
  });

  document.getElementById('contact-list').addEventListener('click',async function (event) {
    const button = event.target.closest('.start-direct');
    if (!button) return;
    status.textContent = 'Opening chat…';
    try {
      const payload = await request('create_direct',{role:button.dataset.role,id:button.dataset.id});
      await loadConversations();
      await openConversation(payload.conversation_id);
    } catch (error) {
      status.textContent = error.message;
    }
  });

  document.getElementById('new-group-toggle').addEventListener('click',function () {
    const details = document.getElementById('group-create');
    details.open = !details.open;
  });

  document.getElementById('group-search').addEventListener('input',function () {
    const query = this.value.trim().toLocaleLowerCase();
    document.querySelectorAll('[data-group-person]').forEach((item) => {
      item.hidden = query !== '' && !item.dataset.groupPerson.includes(query);
    });
  });

  document.getElementById('create-group').addEventListener('click',async function () {
    const selected = Array.from(document.querySelectorAll('#group-people input:checked')).map((checkbox) => ({
      role:checkbox.dataset.role,id:checkbox.dataset.id
    }));
    const message = document.getElementById('group-status');
    message.textContent = 'Creating group…';
    try {
      const payload = await request('create_group',{
        name:document.getElementById('group-name').value.trim(),
        members:JSON.stringify(selected)
      });
      document.querySelectorAll('#group-people input:checked').forEach((checkbox) => { checkbox.checked = false; });
      document.getElementById('group-name').value = '';
      document.getElementById('group-create').open = false;
      message.textContent = '';
      await loadConversations();
      await openConversation(payload.conversation_id);
    } catch (error) {
      message.textContent = error.message;
    }
  });

  function currentMentionQuery() {
    const caret = composer.selectionStart;
    const before = composer.value.slice(0,caret);
    const match = before.match(/(?:^|\s)@([\w .'-]{0,60})$/u);
    if (!match) return null;
    const at = caret - match[0].length + match[0].lastIndexOf('@');
    return {query:match[1].trim(),start:at,end:caret};
  }

  function showSuggestions(results,range) {
    suggestionBox.dataset.start = String(range.start);
    suggestionBox.dataset.end = String(range.end);
    suggestionBox.innerHTML = results.length ? results.map((item,index) =>
      '<button type="button" role="option" data-result-index="' + index + '" style="display:block;width:100%;text-align:left;padding:.55rem .7rem;border:0;border-bottom:1px solid #edf1ef;background:#fff;cursor:pointer;"><strong>' + escapeHtml(item.label) + '</strong><small class="subtle" style="display:block;">' + (item.type === 'person' ? 'Teammate mention' : 'Workspace record') + '</small></button>'
    ).join('') : '<p class="subtle" style="padding:.5rem .7rem;">No matching teammates or accessible records.</p>';
    suggestionBox._results = results;
    suggestionBox.hidden = false;
  }

  composer.addEventListener('input',function () {
    const range = currentMentionQuery();
    if (!range || !activeConversation) {
      suggestionBox.hidden = true;
      return;
    }
    window.clearTimeout(searchTimer);
    searchTimer = window.setTimeout(async function () {
      try {
        const payload = await get('search',{q:range.query,conversation:activeConversation});
        showSuggestions(payload.results || [],range);
      } catch (error) {
        suggestionBox.innerHTML = '<p class="subtle" style="padding:.5rem .7rem;">' + escapeHtml(error.message) + '</p>';
        suggestionBox.hidden = false;
      }
    },180);
  });

  suggestionBox.addEventListener('click',function (event) {
    const button = event.target.closest('[data-result-index]');
    if (!button) return;
    const item = suggestionBox._results[Number(button.dataset.resultIndex)];
    const start = Number(suggestionBox.dataset.start);
    const end = Number(suggestionBox.dataset.end);
    const label = '@' + (item.type === 'person' ? item.label.split(' · ')[0] : item.label);
    composer.setRangeText(label + ' ',start,end,'end');
    selectedReferences.push(item.type === 'person'
      ? {type:'person',role:item.role,id:item.id,label:label.slice(1)}
      : {type:'record',module:item.module,id:item.id,label:label.slice(1)});
    suggestionBox.hidden = true;
    composer.focus();
  });

  async function sendMessage() {
    if (!activeConversation) return;
    const body = composer.value.trim();
    if (!body) return;
    sendButton.disabled = true;
    status.textContent = 'Sending…';
    try {
      await request('send',{
        conversation:String(activeConversation),
        body,
        references:JSON.stringify(selectedReferences)
      });
      composer.value = '';
      selectedReferences = [];
      await openConversation(activeConversation);
      await loadConversations();
    } catch (error) {
      status.textContent = error.message;
    } finally {
      sendButton.disabled = !activeConversation;
    }
  }

  sendButton.addEventListener('click',sendMessage);
  composer.addEventListener('keydown',function (event) {
    if (event.key === 'Enter' && !event.shiftKey && suggestionBox.hidden) {
      event.preventDefault();
      sendMessage();
    }
    if (event.key === 'Escape') suggestionBox.hidden = true;
  });

  thread.addEventListener('click',function (event) {
    const button = event.target.closest('[data-share-message]');
    if (!button) return;
    sharingMessage = Number(button.dataset.shareMessage);
    document.getElementById('share-status').textContent = '';
    sharePanel.hidden = false;
    document.getElementById('share-target').focus();
  });

  document.getElementById('cancel-share').addEventListener('click',function () {
    sharingMessage = 0;
    sharePanel.hidden = true;
  });

  document.getElementById('share-message').addEventListener('click',async function () {
    const target = document.getElementById('share-target').value;
    const message = document.getElementById('share-status');
    if (!sharingMessage || !target) {
      message.textContent = 'Choose a destination conversation.';
      return;
    }
    message.textContent = 'Sharing…';
    try {
      await request('share',{message_id:String(sharingMessage),conversation:target});
      sharePanel.hidden = true;
      sharingMessage = 0;
      await loadConversations();
      status.textContent = 'Message shared.';
    } catch (error) {
      message.textContent = error.message;
    }
  });

  loadConversations().then(async function () {
    if (activeConversation) await openConversation(activeConversation);
    else status.textContent = 'Live';
  }).catch((error) => { status.textContent = error.message; });
  window.setInterval(refresh,5000);
})();
</script>
<?php suite_end(); ?>
