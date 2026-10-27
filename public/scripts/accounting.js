(() => {
  const lines = document.querySelector('[data-journal-lines]');
  const add = document.querySelector('[data-add-journal-line]');
  const template = document.querySelector('[data-journal-line-template]');
  if (!lines || !add || !template) return;
  add.addEventListener('click', () => {
    if (lines.querySelectorAll('[data-journal-line]').length >= 20) return;
    lines.append(template.content.cloneNode(true));
  });
  lines.addEventListener('click', event => {
    const remove = event.target.closest('[data-remove-journal-line]');
    if (remove && lines.querySelectorAll('[data-journal-line]').length > 2) {
      remove.closest('[data-journal-line]').remove();
    }
  });
  lines.addEventListener('input', event => {
    const line = event.target.closest('[data-journal-line]');
    if (!line || !event.target.matches('[data-debit], [data-credit]')) return;
    const counterpart = line.querySelector(event.target.matches('[data-debit]') ? '[data-credit]' : '[data-debit]');
    if (event.target.value.trim() !== '') counterpart.value = '';
  });
  // Live totals in cents; the server still validates the posted entry.
  const balance = document.querySelector('[data-journal-balance]');
  const cents = value => { const match = value.replace(/,/g, '').trim().match(/^(\d{1,13})(?:\.(\d{1,2}))?$/); return match ? Number(match[1]) * 100 + Number((match[2] || '').padEnd(2, '0')) : 0; };
  const format = value => (value / 100).toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2});
  const totalDebit = document.querySelector('[data-total-debit]');
  const totalCredit = document.querySelector('[data-total-credit]');
  function updateBalance() {
    if (!balance) return;
    const sum = selector => [...lines.querySelectorAll(selector)].reduce((total, input) => total + cents(input.value), 0);
    const debits = sum('[data-debit]'), credits = sum('[data-credit]');
    if (totalDebit) totalDebit.textContent = format(debits);
    if (totalCredit) totalCredit.textContent = format(credits);
    const empty = debits === 0 && credits === 0;
    balance.classList.toggle('balanced', !empty && debits === credits);
    balance.classList.toggle('unbalanced', !empty && debits !== credits);
    balance.textContent = empty ? 'Enter amounts to check the balance.'
      : debits === credits ? `Balanced · ready to post ${format(debits)}`
      : `Out of balance by ${format(Math.abs(debits - credits))}`;
  }
  lines.addEventListener('input', updateBalance);
  lines.addEventListener('click', () => requestAnimationFrame(updateBalance));
  updateBalance();
})();
