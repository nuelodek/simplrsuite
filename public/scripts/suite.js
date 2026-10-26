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
