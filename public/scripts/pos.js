'use strict';
(() => {
  const app = document.querySelector('[data-api="/api/pos.php"]');
  if (!app) return;

  const apiUrl = app.dataset.api;
  const csrf = app.dataset.csrf;
  const canEdit = app.dataset.canEdit === '1';
  const isOwner = app.dataset.owner === '1';
  const state = {currency:app.dataset.currency || '',products:[],registers:[],customers:[],sales:[],report:{},shift:null,cart:new Map(),selectedSale:null,busy:false};
  const $ = (selector, root = app) => root.querySelector(selector);
  const cents = value => Math.round(Number(value || 0) * 100);
  const money = value => {
    const amount = Number(value || 0);
    if (state.currency) return new Intl.NumberFormat(undefined,{style:'currency',currency:state.currency}).format(amount);
    return (state.currency ? state.currency + ' ' : '') + amount.toFixed(2);
  };
  const setNotice = (message, success = false) => {
    const alert = $('[data-pos-alert]');
    alert.hidden = !message;
    alert.textContent = message;
    alert.className = 'pos-alert notice ' + (success ? 'info' : 'error');
    alert.setAttribute('role',success ? 'status' : 'alert');
  };
  const request = async (method, body = null, params = {}) => {
    const url = new URL(apiUrl,window.location.origin);
    Object.entries(params).forEach(([key,value]) => url.searchParams.set(key,String(value)));
    const options = {method,headers:{Accept:'application/json'}};
    if (method === 'POST') {
      options.headers['Content-Type'] = 'application/json';
      options.headers['X-CSRF-Token'] = csrf;
      options.body = JSON.stringify(body || {});
    }
    const response = await fetch(url,options);
    const result = await response.json();
    if (!response.ok || !result.ok) throw new Error(result.error || 'The POS request could not be completed.');
    return result;
  };
  const run = async task => {
    if (state.busy) return;
    state.busy = true;
    app.classList.add('is-busy');
    try { await task(); }
    catch (error) { setNotice(error instanceof Error ? error.message : 'The POS request could not be completed.'); }
    finally {
      state.busy = false;
      app.classList.remove('is-busy');
      renderProducts();
      renderCart();
    }
  };
  const element = (tag, className, text) => {
    const node = document.createElement(tag);
    if (className) node.className = className;
    if (text !== undefined) node.textContent = text;
    return node;
  };

  function renderSummary() {
    const report = state.report || {};
    const gross = Number(report.gross || 0);
    const refunds = Number(report.refunds || 0);
    const summary = $('[data-pos-summary]');
    summary.replaceChildren();
    const stats = [
      ['Sales today',Number(report.sale_count || 0).toLocaleString()],
      ['Gross sales',money(gross)],
      ['Refunds',money(refunds)],
      ['Net sales',money(gross - refunds)]
    ];
    for (const [label,value] of stats) {
      const card = element('article','pos-stat');
      card.append(element('span','',label),element('strong','',value));
      summary.append(card);
    }
    const tender = element('p','pos-tender-summary',`Today by recorded tender · Cash ${money(report.cash)} · Card ${money(report.card)} · Other ${money(report.other)}`);
    summary.append(tender);
  }

  function renderShift() {
    const controls = $('[data-pos-shift-controls]');
    controls.replaceChildren();
    const status = $('[data-pos-shift-status]');
    if (state.shift) {
      status.textContent = `${state.shift.register_name} · Opened ${new Date(state.shift.opened_at.replace(' ','T') + 'Z').toLocaleString()}`;
      const openLabel = element('span','pos-shift-open','Shift open');
      controls.append(openLabel);
      if (canEdit) {
        const form = element('form','pos-inline-form');
        form.dataset.posCloseShift = '';
        const field = element('div','field');
        const label = element('label','', 'Closing cash counted');
        label.htmlFor = 'pos-closing-cash';
        const input = element('input');
        input.id = 'pos-closing-cash'; input.type = 'number'; input.min = '0'; input.step = '0.01'; input.required = true; input.placeholder = '0.00'; input.dataset.closingCash = '';
        field.append(label,input);
        const button = element('button','button secondary','Close shift');
        button.type = 'submit';
        form.append(field,button);
        controls.append(form);
      }
    } else {
      status.textContent = state.registers.length ? 'No shift is currently open for you.' : 'Add a register before opening a shift.';
      if (!canEdit) return;
      const form = element('form','pos-inline-form');
      form.dataset.posOpenShift = '';
      const registerField = element('div','field');
      const registerLabel = element('label','', 'Register');
      registerLabel.htmlFor = 'pos-register-select';
      const select = element('select');
      select.id = 'pos-register-select'; select.required = true; select.dataset.registerId = '';
      const firstOption = element('option','','Choose a register');
      firstOption.value = '';
      select.append(firstOption);
      for (const register of state.registers) {
        const option = element('option','',register.name + (register.open_shift_id ? ' · in use' : ''));
        option.value = register.id;
        option.disabled = Boolean(register.open_shift_id);
        select.append(option);
      }
      registerField.append(registerLabel,select);
      const cashField = element('div','field');
      const cashLabel = element('label','', 'Opening cash');
      cashLabel.htmlFor = 'pos-opening-cash';
      const cashInput = element('input');
      cashInput.id = 'pos-opening-cash'; cashInput.type = 'number'; cashInput.min = '0'; cashInput.step = '0.01'; cashInput.value = '0'; cashInput.required = true; cashInput.dataset.openingCash = '';
      cashField.append(cashLabel,cashInput);
      const button = element('button','button','Open shift');
      button.type = 'submit';
      form.append(registerField,cashField,button);
      controls.append(form);
    }
  }

  function renderProducts() {
    const search = $('[data-pos-search]').value.toLocaleLowerCase().trim();
    const category = $('[data-pos-category]').value;
    const categorySelect = $('[data-pos-category]');
    const priorCategory = category;
    const categories = [...new Set(state.products.map(item => item.category).filter(Boolean))].sort((a,b) => a.localeCompare(b));
    categorySelect.replaceChildren(element('option','','All categories'));
    for (const value of categories) {
      const option = element('option','',value);
      option.value = value;
      categorySelect.append(option);
    }
    categorySelect.value = categories.includes(priorCategory) ? priorCategory : '';
    const container = $('[data-pos-products]');
    container.replaceChildren();
    const filtered = state.products.filter(item => {
      const matchesText = `${item.name} ${item.category} ${item.description || ''}`.toLocaleLowerCase().includes(search);
      return matchesText && (!category || item.category === category);
    });
    $('[data-pos-product-count]').textContent = `${filtered.length} shown`;
    if (!filtered.length) {
      container.append(element('p','empty-state compact',state.products.length ? 'No inventory products match your search.' : 'Add items in Inventory to start selling.'));
      return;
    }
    for (const product of filtered) {
      const card = element('article','pos-product');
      const body = element('div','pos-product-body');
      body.append(element('strong','',product.name),element('span','pos-product-category',product.category || 'Uncategorised'));
      const stock = Number(product.available || 0);
      body.append(element('span',stock > 0 ? 'pos-stock' : 'pos-stock is-empty',`${stock} in stock`));
      const footer = element('div','pos-product-footer');
      footer.append(element('strong','',money(product.unit_price)));
      const button = element('button','button secondary small',state.cart.has(Number(product.id)) ? 'Add another' : 'Add');
      button.type = 'button'; button.dataset.addProduct = product.id;
      button.disabled = !canEdit || stock < 1 || !state.shift || !state.currency;
      footer.append(button);
      card.append(body,footer);
      container.append(card);
    }
  }

  function renderCart() {
    const cart = $('[data-pos-cart]');
    cart.replaceChildren();
    const hasItems = state.cart.size > 0;
    $('[data-pos-cart-note]').textContent = hasItems ? `${state.cart.size} product ${state.cart.size === 1 ? 'line' : 'lines'}` : 'Add products to start.';
    let subtotal = 0;
    for (const [id,quantity] of state.cart) {
      const product = state.products.find(item => Number(item.id) === id);
      if (!product) continue;
      const lineTotal = cents(product.unit_price) * quantity;
      subtotal += lineTotal;
      const row = element('div','pos-cart-row');
      const info = element('div','pos-cart-info');
      info.append(element('strong','',product.name),element('small','',`${quantity} × ${money(product.unit_price)}`));
      const amount = element('strong','pos-cart-line-total',money(lineTotal / 100));
      const remove = element('button','icon-button pos-remove','×');
      remove.type = 'button'; remove.dataset.removeProduct = id; remove.setAttribute('aria-label',`Remove ${product.name}`);
      const quantityControl = element('div','pos-quantity-control');
      const decrease = element('button','', '−'); decrease.type = 'button'; decrease.dataset.quantityChange = '-1'; decrease.dataset.productId = id; decrease.setAttribute('aria-label',`Remove one ${product.name}`);
      const count = element('span','',String(quantity));
      const increase = element('button','', '+'); increase.type = 'button'; increase.dataset.quantityChange = '1'; increase.dataset.productId = id; increase.setAttribute('aria-label',`Add one ${product.name}`);
      const stock = Number(product.available || 0);
      increase.disabled = quantity >= stock;
      quantityControl.append(decrease,count,increase);
      row.append(info,quantityControl,amount,remove);
      cart.append(row);
    }
    if (!hasItems) cart.append(element('p','pos-empty-cart','Your cart is empty. Choose products from the inventory list.'));
    const discountPercent = Math.min(100,Math.max(0,Number($('[data-pos-discount]').value || 0)));
    const discountCents = Math.round(subtotal * discountPercent / 100);
    const totals = $('[data-pos-totals]');
    totals.replaceChildren();
    totals.append(totalRow('Subtotal',money(subtotal / 100)),totalRow(`Discount (${discountPercent.toFixed(2)}%)`,'−' + money(discountCents / 100)),totalRow('Total',money((subtotal-discountCents)/100),'pos-total'));
    $('[data-pos-reserve]').disabled = !canEdit || !state.shift || !hasItems || !state.currency || state.busy;
    $('[data-pos-clear-cart]').disabled = !hasItems || state.busy;
  }

  function totalRow(label,value,className = '') {
    const row = element('div','pos-total-row ' + className);
    row.append(element('span','',label),element('strong','',value));
    return row;
  }

  function renderCustomers() {
    const select = $('[data-pos-customer]');
    const selected = select.value;
    select.replaceChildren();
    const walkIn = element('option','','Walk-in customer');
    walkIn.value = '';
    select.append(walkIn);
    for (const customer of state.customers) {
      const option = element('option','',customer.name + (customer.business_name ? ` · ${customer.business_name}` : ''));
      option.value = customer.id;
      select.append(option);
    }
    select.value = selected;
  }

  function renderSales() {
    const tbody = $('[data-pos-sales]');
    tbody.replaceChildren();
    if (!state.sales.length) {
      const row = element('tr');
      const cell = element('td','pos-empty-table','No POS sales yet.');
      cell.colSpan = 7; row.append(cell); tbody.append(row); return;
    }
    for (const sale of state.sales) {
      const row = element('tr');
      row.append(element('td','',sale.reference),element('td','',sale.customer_name || 'Walk-in'),element('td','',sale.status.replace('_',' ')),element('td','',money(sale.total)),element('td','',money(sale.refunded_amount)),element('td','',new Date(sale.created_at.replace(' ','T') + 'Z').toLocaleString()));
      const action = element('td');
      const button = element('button','button secondary small','Details');
      button.type = 'button'; button.dataset.saleDetails = sale.id;
      action.append(button); row.append(action); tbody.append(row);
    }
  }

  async function load() {
    const data = await request('GET',null,{mode:'bootstrap'});
    state.currency = data.currency || '';
    state.products = data.products || [];
    state.registers = data.registers || [];
    state.customers = data.customers || [];
    state.sales = data.sales || [];
    state.report = data.report || {};
    state.shift = data.my_shift || null;
    $('[data-pos-register-panel]').hidden = !isOwner || !canEdit;
    renderSummary(); renderShift(); renderProducts(); renderCart(); renderCustomers(); renderSales();
    if (state.selectedSale) await openSale(state.selectedSale);
  }

  async function openSale(id) {
    const data = await request('GET',null,{mode:'sale',id});
    state.selectedSale = Number(id);
    const panel = $('[data-pos-detail-panel]');
    const sale = data.sale;
    panel.hidden = false;
    $('[data-pos-detail-heading]').textContent = `${sale.reference} · ${sale.status.replace('_',' ')}`;
    const target = $('[data-pos-detail]');
    target.replaceChildren();
    const lines = element('div','table-wrap');
    const table = element('table','data-table pos-item-table');
    const head = element('thead');
    const header = element('tr');
    for (const text of ['Item','Qty','Unit price','Line total']) header.append(element('th','',text));
    head.append(header);
    const body = element('tbody');
    for (const item of data.items) {
      const row = element('tr');
      row.append(element('td','',item.item_name),element('td','',String(item.quantity)),element('td','',money(item.unit_price)),element('td','',money(item.line_total)));
      body.append(row);
    }
    table.append(head,body); lines.append(table);
    const details = element('dl','pos-sale-facts');
    const facts = [
      ['Customer',sale.customer_name || 'Walk-in'],
      ['Subtotal',money(sale.subtotal)],
      ['Discount',`${Number(sale.discount_percent).toFixed(2)}% · ${money(sale.discount_amount)}`],
      ['Total',money(sale.total)],
      ['Payment',sale.payment_method || 'Not recorded'],
      ['Refunded',money(sale.refunded_amount)],
      ['Currency',sale.currency]
    ];
    for (const [label,value] of facts) {
      const wrapper = element('div');
      wrapper.append(element('dt','',label),element('dd','',value));
      details.append(wrapper);
    }
    target.append(lines,details);
    if (data.refunds.length) {
      const refundTitle = element('h3','','Refund history');
      target.append(refundTitle);
      for (const refund of data.refunds) {
        const note = `${money(refund.amount)} · ${refund.refund_method} · ${refund.restocked ? 'stock restored' : 'not restocked'}${refund.note ? ' · ' + refund.note : ''}`;
        target.append(element('p','pos-refund-entry',note));
      }
    }
    renderSaleActions(sale);
    panel.scrollIntoView({behavior:'smooth',block:'start'});
  }

  function renderSaleActions(sale) {
    const actions = $('[data-pos-detail-actions]');
    actions.replaceChildren();
    if (!canEdit) return;
    if (sale.status === 'reserved') {
      const form = element('form','pos-action-form');
      form.dataset.posCompleteSale = sale.id;
      const tenderField = element('div','field');
      const tenderLabel = element('label','', 'Recorded payment method');
      tenderLabel.htmlFor = 'pos-payment-method';
      const tender = element('select');
      tender.id = 'pos-payment-method'; tender.required = true; tender.dataset.paymentMethod = '';
      for (const [value,label] of [['cash','Cash'],['card','Card (record only)'],['other','Other / external']]) {
        const option = element('option','',label); option.value = value; tender.append(option);
      }
      tenderField.append(tenderLabel,tender);
      const cashField = element('div','field');
      const cashLabel = element('label','', 'Cash received');
      cashLabel.htmlFor = 'pos-cash-received';
      const cash = element('input');
      cash.id = 'pos-cash-received'; cash.type = 'number'; cash.min = '0'; cash.step = '0.01'; cash.required = true; cash.value = sale.total; cash.dataset.cashReceived = '';
      cashField.append(cashLabel,cash);
      const complete = element('button','button','Record payment & complete sale');
      complete.type = 'submit';
      const cancel = element('button','button secondary','Cancel reservation');
      cancel.type = 'button'; cancel.dataset.cancelSale = sale.id;
      form.append(tenderField,cashField,complete,cancel);
      actions.append(form);
      tender.addEventListener('change',() => {
        cashField.hidden = tender.value !== 'cash';
        cash.required = tender.value === 'cash';
      });
      if (sale.expires_at) actions.append(element('small','pos-expiry',`Reservation expires ${new Date(sale.expires_at.replace(' ','T') + 'Z').toLocaleString()}.`));
      return;
    }
    if (['completed','partial_refund'].includes(sale.status)) {
      const remaining = Math.max(0,Number(sale.total) - Number(sale.refunded_amount));
      const form = element('form','pos-action-form');
      form.dataset.posRefundSale = sale.id;
      const amountField = element('div','field');
      const amountLabel = element('label','', 'Refund amount');
      amountLabel.htmlFor = 'pos-refund-amount';
      const amount = element('input');
      amount.id = 'pos-refund-amount'; amount.type = 'number'; amount.min = '0.01'; amount.max = remaining.toFixed(2); amount.step = '0.01'; amount.value = remaining.toFixed(2); amount.required = true; amount.dataset.refundAmount = '';
      amountField.append(amountLabel,amount);
      const methodField = element('div','field');
      const methodLabel = element('label','', 'Refund method (record only)');
      methodLabel.htmlFor = 'pos-refund-method';
      const method = element('select');
      method.id = 'pos-refund-method'; method.required = true; method.dataset.refundMethod = '';
      for (const [value,label] of [['cash','Cash'],['card','Card'],['other','Other']]) {
        const option = element('option','',label); option.value = value; method.append(option);
      }
      methodField.append(methodLabel,method);
      const noteField = element('div','field');
      const noteLabel = element('label','', 'Refund note (optional)');
      noteLabel.htmlFor = 'pos-refund-note';
      const note = element('input');
      note.id = 'pos-refund-note'; note.maxLength = 255; note.dataset.refundNote = '';
      noteField.append(noteLabel,note);
      const restockLabel = element('label','checkbox-option');
      const restock = element('input');
      restock.type = 'checkbox'; restock.dataset.restock = '';
      restockLabel.append(restock,element('span','', 'Restock all items from this sale (only if refunding it in full)'));
      const submit = element('button','button','Record refund');
      submit.type = 'submit';
      form.append(amountField,methodField,noteField,restockLabel,submit);
      actions.append(form);
      return;
    }
    actions.append(element('p','subtle','This sale has no further available actions.'));
  }

  async function refreshAndNotify(message) {
    await load();
    if (message) setNotice(message,true);
  }

  app.addEventListener('click',event => {
    const target = event.target instanceof Element ? event.target : null;
    if (!target) return;
    const add = target.closest('[data-add-product]');
    if (add) {
      const id = Number(add.dataset.addProduct);
      const product = state.products.find(item => Number(item.id) === id);
      if (product && Number(product.available) > (state.cart.get(id) || 0)) state.cart.set(id,(state.cart.get(id) || 0) + 1);
      renderCart(); renderProducts(); return;
    }
    const change = target.closest('[data-quantity-change]');
    if (change) {
      const id = Number(change.dataset.productId);
      const quantity = (state.cart.get(id) || 0) + Number(change.dataset.quantityChange);
      if (quantity < 1) state.cart.delete(id); else state.cart.set(id,quantity);
      renderCart(); renderProducts(); return;
    }
    const remove = target.closest('[data-remove-product]');
    if (remove) {
      state.cart.delete(Number(remove.dataset.removeProduct)); renderCart(); renderProducts(); return;
    }
    const sale = target.closest('[data-sale-details]');
    if (sale) { run(() => openSale(Number(sale.dataset.saleDetails))); return; }
    const cancel = target.closest('[data-cancel-sale]');
    if (cancel) {
      if (!window.confirm('Cancel this reservation and release its stock?')) return;
      run(async () => { await request('POST',{action:'cancel_sale',sale_id:Number(cancel.dataset.cancelSale)}); await refreshAndNotify('Reservation cancelled; stock is available again.'); }); return;
    }
    if (target.closest('[data-pos-clear-cart]')) {
      state.cart.clear(); $('[data-pos-discount]').value = '0'; $('[data-pos-customer]').value = '';
      renderCart(); renderProducts(); return;
    }
    if (target.closest('[data-pos-refresh]')) { run(load); return; }
    if (target.closest('[data-pos-close-detail]')) { $('[data-pos-detail-panel]').hidden = true; state.selectedSale = null; return; }
    if (target.closest('[data-pos-print]')) { window.print(); return; }
    if (target.closest('[data-pos-reserve]')) {
      const items = [...state.cart].map(([id,quantity]) => ({id,quantity}));
      const discount = $('[data-pos-discount]').value || '0';
      const customer = $('[data-pos-customer]').value;
      run(async () => {
        const result = await request('POST',{action:'create_sale',shift_id:state.shift.id,items,discount_percent:discount,customer_id:customer || null});
        state.cart.clear(); $('[data-pos-discount]').value = '0'; $('[data-pos-customer]').value = '';
        state.selectedSale = result.id;
        await refreshAndNotify(`Stock reserved for ${result.reference}. Complete payment within 20 minutes.`);
      });
    }
  });

  app.addEventListener('input',event => {
    const target = event.target;
    if (target.matches('[data-pos-search]') || target.matches('[data-pos-category]')) renderProducts();
    if (target.matches('[data-pos-discount]')) renderCart();
  });
  app.addEventListener('change',event => {
    if (event.target.matches('[data-pos-category]')) renderProducts();
  });

  app.addEventListener('submit',event => {
    const form = event.target;
    if (!(form instanceof HTMLFormElement)) return;
    event.preventDefault();
    if (form.matches('[data-pos-create-register]')) {
      run(async () => {
        const name = new FormData(form).get('name');
        await request('POST',{action:'create_register',name});
        form.reset();
        await refreshAndNotify('Register added.');
      });
      return;
    }
    if (form.matches('[data-pos-open-shift]')) {
      run(async () => {
        await request('POST',{action:'open_shift',register_id:$('[data-register-id]',form).value,opening_cash:$('[data-opening-cash]',form).value});
        await refreshAndNotify('Register shift opened.');
      });
      return;
    }
    if (form.matches('[data-pos-close-shift]')) {
      run(async () => {
        const result = await request('POST',{action:'close_shift',shift_id:state.shift.id,closing_cash:$('[data-closing-cash]',form).value});
        await refreshAndNotify(`Shift closed. Expected cash ${money(result.expected_cash)}; variance ${money(result.variance)}.`);
      });
      return;
    }
    if (form.matches('[data-pos-complete-sale]')) {
      run(async () => {
        const method = $('[data-payment-method]',form).value;
        const cash = $('[data-cash-received]',form);
        const result = await request('POST',{action:'complete_sale',sale_id:Number(form.dataset.posCompleteSale),payment_method:method,cash_received:method === 'cash' ? cash.value : null});
        await refreshAndNotify(`Sale ${result.reference} completed.${method === 'cash' ? ' Change due: ' + money(result.change_due) : ''}`);
      });
      return;
    }
    if (form.matches('[data-pos-refund-sale]')) {
      run(async () => {
        const result = await request('POST',{action:'refund_sale',sale_id:Number(form.dataset.posRefundSale),amount:$('[data-refund-amount]',form).value,refund_method:$('[data-refund-method]',form).value,restock:$('[data-restock]',form).checked,note:$('[data-refund-note]',form).value});
        await refreshAndNotify(`Refund recorded. Sale status: ${result.status.replace('_',' ')}.`);
      });
    }
  });

  $('[data-pos-detail-panel]').hidden = true;
  run(load);
})();
