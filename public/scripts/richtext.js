/* Rich text fields: progressive enhancement for textarea[data-rich]. The server
   sanitizes every save; this mirror keeps pasted and loaded content tidy. */
(function () {
  'use strict';
  const fields = document.querySelectorAll('textarea[data-rich]');
  if (!fields.length || typeof document.execCommand !== 'function') return;
  const TAGS = {P:'p', DIV:'p', BR:'br', STRONG:'strong', B:'strong', EM:'em', I:'em', U:'u', S:'s', STRIKE:'s', DEL:'s', H2:'h3', H3:'h3', H4:'h3', UL:'ul', OL:'ol', LI:'li', BLOCKQUOTE:'blockquote', CODE:'code', PRE:'pre', A:'a'};
  const DROP = new Set(['SCRIPT','STYLE','IFRAME','OBJECT','EMBED','TEMPLATE','SVG','MATH','HEAD','TITLE','META','LINK','NOSCRIPT','TEXTAREA','SELECT','BUTTON','FORM','INPUT']);
  const ICONS = {
    bold: '<path d="M7 5h6a3.5 3.5 0 0 1 0 7H7zm0 7h7a3.5 3.5 0 0 1 0 7H7z"/>',
    italic: '<path d="M14 5h-4m4 14h-4m3-14-2 14"/>',
    underline: '<path d="M7 4v7a5 5 0 0 0 10 0V4M5 20h14"/>',
    strike: '<path d="M5 12h14M16 7a4 3 0 0 0-4-2c-2.2 0-4 1.2-4 3s2 2.5 4 3m4 2c.6.5 1 1.2 1 2 0 1.8-1.8 3-4 3a4.5 3 0 0 1-4.5-2"/>',
    heading: '<path d="M6 5v14M18 5v14M6 12h12"/>',
    bullets: '<path d="M10 6h10M10 12h10M10 18h10"/><circle cx="5" cy="6" r="1"/><circle cx="5" cy="12" r="1"/><circle cx="5" cy="18" r="1"/>',
    numbers: '<path d="M11 6h9M11 12h9M11 18h9M4 5l1.5-1V9M3.5 15.5a1.5 1.5 0 1 1 2.6 1L3.5 19h3"/>',
    quote: '<path d="M7 7h4v5H6m1 0c0 2-1 3-2 4m11-9h4v5h-5m1 0c0 2-1 3-2 4"/>',
    link: '<path d="M10 14a4 4 0 0 0 5.7 0l3-3a4 4 0 0 0-5.7-5.7l-1 1m2 4a4 4 0 0 0-5.7 0l-3 3a4 4 0 0 0 5.7 5.7l1-1"/>',
    unlink: '<path d="M10 14a4 4 0 0 0 5.7 0l3-3a4 4 0 0 0-5.7-5.7l-1 1m2 4a4 4 0 0 0-5.7 0l-3 3a4 4 0 0 0 5.7 5.7l1-1M4 4l16 16"/>',
    undo: '<path d="M9 14 4 9l5-5M4 9h10a6 6 0 0 1 0 12h-3"/>',
    redo: '<path d="m15 14 5-5-5-5m5 5H10a6 6 0 0 0 0 12h3"/>',
    clear: '<path d="M6 4h12M12 4l-3 16M4 20l16-16"/>'
  };
  const BUTTONS = [
    ['bold','Bold (Ctrl+B)','bold'], ['italic','Italic (Ctrl+I)','italic'], ['underline','Underline (Ctrl+U)','underline'], ['strikeThrough','Strikethrough','strike'], null,
    ['heading','Heading','heading'], ['insertUnorderedList','Bulleted list','bullets'], ['insertOrderedList','Numbered list','numbers'], ['quote','Quote','quote'], null,
    ['link','Add link (Ctrl+K)','link'], ['unlink','Remove link','unlink'], null,
    ['undo','Undo','undo'], ['redo','Redo','redo'], ['removeFormat','Clear formatting','clear']
  ];
  function svg(name) { return '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' + ICONS[name] + '</svg>'; }
  function safeHref(value) {
    try { const url = new URL(value, window.location.origin); return ['http:','https:','mailto:'].includes(url.protocol) ? url.href : null; } catch { return null; }
  }
  function copy(from, to, depth) {
    if (depth > 12) return;
    from.childNodes.forEach(node => {
      if (node.nodeType === Node.TEXT_NODE) { to.appendChild(document.createTextNode(node.nodeValue)); return; }
      if (node.nodeType !== Node.ELEMENT_NODE || DROP.has(node.nodeName)) return;
      const tag = TAGS[node.nodeName];
      if (!tag) { copy(node, to, depth + 1); return; }
      if (tag === 'a') {
        const href = safeHref(node.getAttribute('href') || '');
        if (!href) { copy(node, to, depth + 1); return; }
        const link = document.createElement('a');
        link.href = href; link.rel = 'noopener noreferrer nofollow'; link.target = '_blank';
        copy(node, link, depth + 1); to.appendChild(link); return;
      }
      const element = document.createElement(tag);
      if (tag !== 'br') copy(node, element, depth + 1);
      to.appendChild(element);
    });
  }
  function clean(html) {
    const parsed = new DOMParser().parseFromString('<body>' + html + '</body>', 'text/html');
    const holder = document.createElement('div');
    copy(parsed.body, holder, 0);
    const out = holder.innerHTML.replace(/<p>(?:\s|&nbsp;|<br>)*<\/p>/g, '').trim();
    return holder.textContent.trim() === '' && !/<li>/.test(out) ? '' : out;
  }
  function textToHtml(text) {
    const holder = document.createElement('div');
    text.split(/\r?\n\s*\r?\n/).filter(part => part.trim()).forEach(part => {
      const paragraph = document.createElement('p');
      part.split(/\r?\n/).forEach((line, index) => { if (index) paragraph.appendChild(document.createElement('br')); paragraph.appendChild(document.createTextNode(line)); });
      holder.appendChild(paragraph);
    });
    return holder.innerHTML;
  }
  fields.forEach(textarea => {
    const label = document.querySelector('label[for="' + textarea.id + '"]');
    const wrapper = document.createElement('div');
    wrapper.className = 'rte';
    const toolbar = document.createElement('div');
    toolbar.className = 'rte-toolbar';
    toolbar.setAttribute('role', 'toolbar');
    toolbar.setAttribute('aria-label', (label ? label.textContent.replace(/\s*\*|\(optional\)/g, '').trim() + ' ' : '') + 'formatting');
    const content = document.createElement('div');
    content.className = 'rte-content';
    content.contentEditable = 'true';
    content.setAttribute('role', 'textbox');
    content.setAttribute('aria-multiline', 'true');
    if (label) { label.id = label.id || textarea.id + '-label'; content.setAttribute('aria-labelledby', label.id); }
    if (textarea.getAttribute('aria-describedby')) content.setAttribute('aria-describedby', textarea.getAttribute('aria-describedby'));
    if (textarea.getAttribute('aria-invalid')) content.setAttribute('aria-invalid', 'true');
    content.dataset.placeholder = textarea.placeholder || 'Start writing…';
    const meter = document.createElement('div');
    meter.className = 'rte-meter';
    meter.setAttribute('aria-live', 'polite');
    const max = Number(textarea.getAttribute('maxlength')) || 0;
    const required = textarea.required;
    textarea.required = false;
    textarea.hidden = true;
    const buttons = {};
    BUTTONS.forEach(spec => {
      if (!spec) { const gap = document.createElement('span'); gap.className = 'rte-sep'; toolbar.appendChild(gap); return; }
      const [command, title, icon] = spec;
      const button = document.createElement('button');
      button.type = 'button';
      button.className = 'rte-button';
      button.title = title;
      button.setAttribute('aria-label', title.replace(/ \(.+\)$/, ''));
      button.innerHTML = svg(icon);
      button.addEventListener('mousedown', event => event.preventDefault());
      button.addEventListener('click', () => run(command));
      buttons[command] = button;
      toolbar.appendChild(button);
    });
    const initial = textarea.value.trim();
    content.innerHTML = clean(/<(p|br|strong|em|u|s|ul|ol|li|h3|blockquote|code|pre|a)\b/i.test(initial) ? initial : textToHtml(initial));
    textarea.before(wrapper);
    wrapper.append(toolbar, content, meter);
    wrapper.appendChild(textarea);
    function closestTag(name) {
      const selection = window.getSelection();
      let node = selection && selection.anchorNode;
      while (node && node !== content) { if (node.nodeName === name) return node; node = node.parentNode; }
      return null;
    }
    function sync() {
      const value = clean(content.innerHTML);
      textarea.value = value;
      if (max) {
        const used = value.length;
        meter.textContent = used > max * 0.8 ? used.toLocaleString() + ' / ' + max.toLocaleString() + ' characters including formatting' : '';
        wrapper.classList.toggle('over', used > max);
      }
      if (value !== '') wrapper.classList.remove('invalid');
    }
    function state() {
      ['bold','italic','underline','strikeThrough','insertUnorderedList','insertOrderedList'].forEach(command => {
        let active = false;
        try { active = document.queryCommandState(command); } catch { active = false; }
        buttons[command].setAttribute('aria-pressed', String(active));
      });
      buttons.heading.setAttribute('aria-pressed', String(!!closestTag('H3')));
      buttons.quote.setAttribute('aria-pressed', String(!!closestTag('BLOCKQUOTE')));
    }
    function run(command) {
      content.focus();
      if (command === 'heading') document.execCommand('formatBlock', false, closestTag('H3') ? 'p' : 'h3');
      else if (command === 'quote') document.execCommand('formatBlock', false, closestTag('BLOCKQUOTE') ? 'p' : 'blockquote');
      else if (command === 'link') {
        const current = closestTag('A');
        const answer = window.prompt('Link address (https://…)', current ? current.getAttribute('href') : 'https://');
        if (answer === null) return;
        const href = safeHref(answer.trim());
        if (!href) { window.Toast?.show('warning', 'Link not added', 'Use a web address that starts with https:// or an email link.'); return; }
        if (window.getSelection().isCollapsed && !current) document.execCommand('insertHTML', false, '<a href="' + href.replace(/"/g, '&quot;') + '">' + href.replace(/[<>&"]/g, c => ({'<':'&lt;','>':'&gt;','&':'&amp;','"':'&quot;'}[c])) + '</a>');
        else document.execCommand('createLink', false, href);
      } else document.execCommand(command, false, null);
      sync(); state();
    }
    document.execCommand('defaultParagraphSeparator', false, 'p');
    content.addEventListener('input', sync);
    content.addEventListener('keyup', state);
    content.addEventListener('mouseup', state);
    content.addEventListener('focus', () => wrapper.classList.add('focused'));
    content.addEventListener('blur', () => { wrapper.classList.remove('focused'); sync(); });
    content.addEventListener('keydown', event => {
      if ((event.ctrlKey || event.metaKey) && event.key.toLowerCase() === 'k') { event.preventDefault(); event.stopPropagation(); run('link'); }
    });
    content.addEventListener('paste', event => {
      event.preventDefault();
      const html = event.clipboardData.getData('text/html');
      const text = event.clipboardData.getData('text/plain');
      document.execCommand('insertHTML', false, html ? clean(html) : textToHtml(text) || text);
      sync();
    });
    content.addEventListener('drop', event => event.preventDefault());
    textarea.form?.addEventListener('submit', event => {
      sync();
      if (required && textarea.value === '') {
        event.preventDefault();
        event.stopImmediatePropagation();
        wrapper.classList.add('invalid');
        content.focus();
        window.Toast?.show('warning', 'Add ' + (label ? label.textContent.replace(/\s*\*|\(optional\)/g, '').trim().toLowerCase() : 'this field'), 'This field is required.');
      }
    }, true);
    sync(); state();
  });
})();
