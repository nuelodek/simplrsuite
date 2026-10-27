'use strict';
document.querySelectorAll('[data-billing-currency]').forEach(form => {
  const selects = [...form.querySelectorAll('[data-monthly-minor]')];
  const total = form.querySelector('[data-billing-total]');
  function updateTotal() {
    const minor = selects.reduce((sum, select) => sum + Number(select.value) * Number(select.dataset.monthlyMinor), 0);
    const rate = Math.round(Number(form.dataset.billingRate) * 100000000);
    const cents = Math.floor((minor * rate + 50000000) / 100000000);
    form.querySelector('[data-billing-summary]').hidden = false;
    total.textContent = form.dataset.billingCurrency + ' ' + (cents / 100).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
  }
  selects.forEach(select => select.addEventListener('change', updateTotal));
  updateTotal();
});
document.querySelectorAll('[data-password]').forEach(button => {
  button.addEventListener('click', () => {
    const input = document.getElementById(button.dataset.password);
    const visible = input.type === 'password';
    input.type = visible ? 'text' : 'password';
    button.textContent = visible ? 'Hide' : 'Show';
    button.setAttribute('aria-pressed', String(visible));
  });
});
const menuButton = document.querySelector('[data-menu]');
const sidebar = document.getElementById('sidebar');
const backdrop = document.querySelector('.menu-backdrop');
const mobile = window.matchMedia('(max-width: 680px)');
function setMenu(open, restoreFocus = false) {
  if (!sidebar || !menuButton) return;
  sidebar.classList.toggle('open', open);
  sidebar.inert = mobile.matches && !open;
  menuButton.setAttribute('aria-expanded', String(open));
  menuButton.setAttribute('aria-label', open ? 'Close navigation' : 'Open navigation');
  if (backdrop) backdrop.hidden = !open;
  document.body.style.overflow = open ? 'hidden' : '';
  if (restoreFocus) menuButton.focus();
  else if (open) sidebar.querySelector('[data-menu-close]')?.focus();
}
menuButton?.addEventListener('click', () => setMenu(!sidebar.classList.contains('open')));
document.querySelectorAll('[data-menu-close]').forEach(button => button.addEventListener('click', () => setMenu(false, true)));
document.addEventListener('keydown', event => {
  if (!sidebar?.classList.contains('open')) return;
  if (event.key === 'Escape') { setMenu(false, true); return; }
  if (event.key === 'Tab') {
    const controls = [...sidebar.querySelectorAll('a[href],button:not([disabled])')].filter(el => el.getClientRects().length);
    const first = controls[0], last = controls[controls.length - 1];
    if (event.shiftKey && document.activeElement === first) { event.preventDefault(); last.focus(); }
    else if (!event.shiftKey && document.activeElement === last) { event.preventDefault(); first.focus(); }
  }
});
mobile.addEventListener('change', () => setMenu(false));
setMenu(false);
const tour = document.querySelector('[data-dashboard-tour]');
const tourButton = document.querySelector('[data-tour-open]');
const tourSteps = tour ? [...tour.querySelectorAll('[data-tour-step]')] : [];
const tourProgress = tour?.querySelector('[data-tour-progress]');
const previousTourStep = tour?.querySelector('[data-tour-previous]');
const nextTourStep = tour?.querySelector('[data-tour-next]');
let activeTourStep = 0;
function showTourStep(index) {
  activeTourStep = index;
  tourSteps.forEach((step, stepIndex) => { step.hidden = stepIndex !== index; });
  if (tourProgress) tourProgress.textContent = `Step ${index + 1} of ${tourSteps.length}`;
  if (previousTourStep) previousTourStep.disabled = index === 0;
  if (nextTourStep) nextTourStep.textContent = index === tourSteps.length - 1 ? 'Finish tour' : 'Next';
}
tourButton?.addEventListener('click', () => {
  if (!tour || !tourSteps.length) return;
  showTourStep(0);
  tour.showModal();
});
tour?.querySelector('[data-tour-close]')?.addEventListener('click', () => tour.close());
previousTourStep?.addEventListener('click', () => {
  if (activeTourStep > 0) showTourStep(activeTourStep - 1);
});
nextTourStep?.addEventListener('click', () => {
  if (activeTourStep === tourSteps.length - 1) tour.close();
  else showTourStep(activeTourStep + 1);
});
document.querySelectorAll('form[data-submit]').forEach(form => {
  form.addEventListener('submit', () => {
    const button = form.querySelector('[type="submit"]');
    if (button) { button.disabled = true; button.dataset.label = button.textContent; button.textContent = 'Please wait…'; }
  });
});
window.addEventListener('pageshow', () => document.querySelectorAll('button[data-label]').forEach(button => { button.disabled = false; button.textContent = button.dataset.label; }));
document.querySelectorAll('[data-filter]').forEach(input => {
  const table = document.getElementById(input.dataset.filter);
  const rows = [...table.querySelectorAll('tbody tr[data-record]')];
  const empty = table.querySelector('[data-no-results]');
  const counter = document.querySelector('[data-result-count]');
  input.addEventListener('input', () => {
    const query = input.value.toLocaleLowerCase().trim();
    let visible = 0;
    rows.forEach(row => { row.hidden = !row.textContent.toLocaleLowerCase().includes(query); if (!row.hidden) visible++; });
    if (empty) empty.hidden = visible !== 0;
    if (counter) counter.textContent = `${visible} ${visible === 1 ? 'record' : 'records'}`;
  });
});
// Integration catalog search: filters services by name and hides empty groups.
document.querySelectorAll('[data-catalog-search]').forEach(input => {
  const groups = [...document.querySelectorAll('.legacy-integration-group')];
  const count = document.querySelector('[data-catalog-count]');
  const empty = document.querySelector('[data-catalog-empty]');
  input.addEventListener('input', () => {
    const query = input.value.trim().toLocaleLowerCase();
    let shown = 0;
    groups.forEach(group => {
      let matches = 0;
      group.querySelectorAll('.legacy-integration-item').forEach(item => {
        const hit = !query || item.textContent.toLocaleLowerCase().includes(query);
        item.hidden = !hit;
        if (hit) matches++;
      });
      group.hidden = matches === 0;
      if (query && matches) group.open = true;
      shown += matches;
    });
    if (count) count.textContent = query ? `${shown} ${shown === 1 ? 'match' : 'matches'}` : '';
    if (empty) empty.hidden = shown !== 0;
  });
});
// Destructive forms ask once before submitting.
document.querySelectorAll('form[data-confirm]').forEach(form => form.addEventListener('submit', event => {
  if (!window.confirm(form.dataset.confirm)) event.preventDefault();
}));
// Drop zones show the chosen file and suggest a title from its name.
document.querySelectorAll('[data-dropzone]').forEach(zone => {
  const input = zone.querySelector('input[type=file]');
  const label = zone.querySelector('[data-dropzone-label]');
  const title = document.getElementById(input.dataset.titleTarget || '');
  ['dragenter','dragover'].forEach(type => zone.addEventListener(type, () => zone.classList.add('is-over')));
  ['dragleave','drop'].forEach(type => zone.addEventListener(type, () => zone.classList.remove('is-over')));
  input.addEventListener('change', () => {
    const file = input.files[0];
    zone.classList.toggle('has-file', !!file);
    label.textContent = file ? file.name : 'Drop a file here or browse';
    if (file && title && !title.value) title.placeholder = file.name.replace(/\.[^.]+$/, '');
  });
});
// Topbar menus: one open at a time, closed by Escape or an outside click.
const topbarMenus = [...document.querySelectorAll('.topbar-menu')];
topbarMenus.forEach(menu => menu.addEventListener('toggle', () => {
  if (menu.open) topbarMenus.forEach(other => { if (other !== menu) other.open = false; });
}));
document.addEventListener('click', event => topbarMenus.forEach(menu => { if (menu.open && !menu.contains(event.target)) menu.open = false; }));
document.addEventListener('keydown', event => {
  if (event.key !== 'Escape') return;
  topbarMenus.forEach(menu => { if (menu.open) { menu.open = false; menu.querySelector('summary').focus(); } });
});

// Command palette: pages and actions are local; records come from the scoped search API.
const command = document.querySelector('[data-command]');
if (command) {
  const input = command.querySelector('[data-command-input]');
  const list = command.querySelector('[data-command-results]');
  const items = JSON.parse(command.querySelector('[data-command-items]').textContent || '[]');
  const icons = Object.fromEntries([...command.querySelectorAll('[data-command-icons] [data-icon]')].map(span => [span.dataset.icon, span.firstElementChild]));
  const recordSearch = command.dataset.recordSearch;
  let records = [], active = 0, timer = null, request = 0;
  const normalize = text => text.toLocaleLowerCase().normalize('NFKD').replace(/[̀-ͯ]/g, '');
  function score(item, query) {
    const label = normalize(item.label);
    if (!query) return item.group === 'Go to' ? 1 : 0;
    if (label.startsWith(query)) return 3;
    if (label.split(/\s+/).some(word => word.startsWith(query))) return 2;
    return label.includes(query) ? 1 : 0;
  }
  function results() {
    const query = normalize(input.value.trim());
    const local = items.map(item => [score(item, query), item]).filter(([value]) => value > 0).sort((a, b) => b[0] - a[0]).map(([, item]) => item);
    const limited = query ? local.slice(0, 8) : local.slice(0, 10);
    return [...(query ? records.map(record => ({...record, group: 'Records'})) : []), ...limited];
  }
  function highlight() {
    const options = [...list.querySelectorAll('.command-option')];
    options.forEach((option, index) => option.setAttribute('aria-selected', String(index === active)));
    if (options[active]) { input.setAttribute('aria-activedescendant', options[active].id); options[active].scrollIntoView({block: 'nearest'}); }
  }
  function render() {
    const shown = results();
    active = Math.min(active, Math.max(0, shown.length - 1));
    list.replaceChildren();
    if (!shown.length) {
      const empty = document.createElement('p');
      empty.className = 'command-empty';
      empty.textContent = recordSearch ? 'No matching pages, actions or records.' : 'No matching pages.';
      list.append(empty);
      input.removeAttribute('aria-activedescendant');
      return;
    }
    let group = '';
    shown.forEach((item, index) => {
      if (item.group !== group) {
        group = item.group;
        const caption = document.createElement('span');
        caption.className = 'command-group';
        caption.textContent = group;
        list.append(caption);
      }
      const option = document.createElement('a');
      option.href = item.url;
      option.id = 'command-option-' + index;
      option.className = 'command-option';
      option.setAttribute('role', 'option');
      const glyph = icons[item.icon] || icons.arrow;
      if (glyph) option.append(glyph.cloneNode(true));
      const text = document.createElement('span');
      const label = document.createElement('strong');
      label.textContent = item.label;
      text.append(label);
      if (item.detail) { const detail = document.createElement('small'); detail.textContent = item.detail; text.append(detail); }
      option.append(text);
      option.addEventListener('mousemove', () => { if (active !== index) { active = index; highlight(); } });
      list.append(option);
    });
    highlight();
  }
  function searchRecords() {
    const query = input.value.trim();
    clearTimeout(timer);
    if (!recordSearch || query.length < 2) { records = []; render(); return; }
    timer = setTimeout(async () => {
      const current = ++request;
      try {
        const response = await fetch(recordSearch + '?q=' + encodeURIComponent(query), {headers: {Accept: 'application/json'}, credentials: 'same-origin'});
        const body = await response.json();
        if (current === request && body.ok) { records = body.data; render(); }
      } catch { /* Pages and actions remain available without record results. */ }
    }, 180);
  }
  function openCommand() {
    if (command.open) return;
    input.value = ''; records = []; active = 0;
    render();
    command.showModal();
    input.focus();
  }
  document.querySelectorAll('[data-command-open]').forEach(button => button.addEventListener('click', openCommand));
  document.addEventListener('keydown', event => {
    if ((event.ctrlKey || event.metaKey) && event.key.toLowerCase() === 'k') { event.preventDefault(); if (command.open) command.close(); else openCommand(); }
    else if (event.key === '/' && !command.open && !document.querySelector('dialog[open]') && !event.target.closest('input,textarea,select,[contenteditable]')) { event.preventDefault(); openCommand(); }
  });
  input.addEventListener('input', () => { active = 0; render(); searchRecords(); });
  input.addEventListener('keydown', event => {
    const count = list.querySelectorAll('.command-option').length;
    if (event.key === 'ArrowDown' && count) { event.preventDefault(); active = (active + 1) % count; highlight(); }
    else if (event.key === 'ArrowUp' && count) { event.preventDefault(); active = (active - 1 + count) % count; highlight(); }
    else if (event.key === 'Enter') { const option = list.querySelectorAll('.command-option')[active]; if (option) { event.preventDefault(); window.location.href = option.href; } }
  });
  command.addEventListener('click', event => { if (event.target === command) command.close(); });
}

// Boards: drag a card, or focus it and press the arrow keys, to change its stage.
document.querySelectorAll('[data-board]').forEach(board => {
  const columns = [...board.querySelectorAll('[data-board-column]')];
  const editable = board.dataset.editable === 'true';
  let dragged = null;
  function counts() {
    columns.forEach(column => {
      const total = column.querySelectorAll('[data-board-card]').length;
      column.querySelector('[data-board-count]').textContent = total;
      const empty = column.querySelector('[data-board-empty]');
      if (empty) empty.hidden = total > 0;
    });
  }
  async function move(card, column) {
    const from = card.closest('[data-board-column]');
    if (!column || column === from || !editable || card.classList.contains('is-saving')) return;
    const placeholder = document.createComment('board-card');
    card.replaceWith(placeholder);
    column.querySelector('[data-board-list]').prepend(card);
    card.classList.add('is-saving');
    counts();
    const body = new FormData();
    body.append('action', 'move');
    body.append('id', card.dataset.id);
    body.append('stage', column.dataset.stage);
    body.append('version', card.dataset.version);
    body.append('csrf_token', board.dataset.csrf);
    try {
      const response = await fetch('/api/records.php?module=' + encodeURIComponent(board.dataset.module), {method: 'POST', body, headers: {Accept: 'application/json'}, credentials: 'same-origin'});
      const result = await response.json().catch(() => ({ok: false}));
      if (!response.ok || !result.ok) throw new Error(result.error || 'This change could not be saved. Reload the page and try again.');
      card.dataset.version = result.version;
      window.Toast?.show('success', 'Moved to ' + column.dataset.label, card.dataset.title);
    } catch (error) {
      placeholder.replaceWith(card);
      window.Toast?.show('error', 'Not moved', error.message);
    } finally {
      placeholder.remove();
      card.classList.remove('is-saving');
      counts();
      card.focus();
    }
  }
  board.addEventListener('dragstart', event => {
    const card = event.target.closest?.('[data-board-card]');
    if (!card || !editable) return;
    dragged = card;
    card.classList.add('is-dragging');
    event.dataTransfer.effectAllowed = 'move';
    event.dataTransfer.setData('text/plain', card.dataset.id);
  });
  board.addEventListener('dragend', () => { dragged?.classList.remove('is-dragging'); dragged = null; columns.forEach(column => column.classList.remove('is-over')); });
  columns.forEach(column => {
    column.addEventListener('dragover', event => { if (!dragged) return; event.preventDefault(); column.classList.add('is-over'); });
    column.addEventListener('dragleave', event => { if (!column.contains(event.relatedTarget)) column.classList.remove('is-over'); });
    column.addEventListener('drop', event => { event.preventDefault(); column.classList.remove('is-over'); if (dragged) move(dragged, column); });
  });
  board.addEventListener('keydown', event => {
    const card = event.target.closest?.('[data-board-card]');
    if (!card || event.target !== card || !['ArrowLeft','ArrowRight'].includes(event.key)) return;
    event.preventDefault();
    const index = columns.indexOf(card.closest('[data-board-column]'));
    move(card, columns[index + (event.key === 'ArrowRight' ? 1 : -1)]);
  });
});
