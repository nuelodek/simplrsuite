/* Consolidated from simplr24's ToastManager; accessible, safe, and optional. */
(function () {
  'use strict';
  const TYPES = ['success', 'error', 'warning', 'info'];
  class ToastManager {
    constructor() { this.container = null; this.timers = new Map(); }
    init() {
      if (!document.body) return false;
      this.container = document.querySelector('.toast-container');
      if (!this.container) {
        this.container = document.createElement('div');
        this.container.className = 'toast-container';
        this.container.setAttribute('role', 'region');
        this.container.setAttribute('aria-label', 'Notifications');
        document.body.appendChild(this.container);
      }
      return true;
    }
    show(type, title, message, duration) {
      if (!this.init()) return null;
      type = TYPES.includes(type) ? type : 'info';
      const toast = this.createToast(type, title, message);
      const previousFocus = document.activeElement;
      this.container.appendChild(toast);
      requestAnimationFrame(() => toast.classList.add('show'));
      const defaultDuration = ['error', 'warning'].includes(type) ? 0 : 8000;
      const requested = duration === undefined ? defaultDuration : Number(duration);
      const timeout = Number.isFinite(requested) && requested > 0 ? Math.max(8000, requested) : 0;
      const state = { remaining: timeout, started: 0, timer: null, previousFocus, hovered: false, focused: false };
      this.timers.set(toast, state);
      const pause = () => {
        if (state.timer !== null) {
          clearTimeout(state.timer);
          state.remaining = Math.max(0, state.remaining - (performance.now() - state.started));
          state.timer = null;
        }
      };
      const resume = () => {
        if (!timeout || state.hovered || state.focused || !this.timers.has(toast) || state.timer !== null) return;
        state.started = performance.now();
        state.timer = setTimeout(() => this.hide(toast), state.remaining);
      };
      toast.addEventListener('mouseenter', () => { state.hovered = true; pause(); });
      toast.addEventListener('mouseleave', () => { state.hovered = false; resume(); });
      toast.addEventListener('focusin', () => { state.focused = true; pause(); });
      toast.addEventListener('focusout', event => {
        if (!toast.contains(event.relatedTarget)) { state.focused = false; resume(); }
      });
      toast.querySelector('.toast-close').addEventListener('click', () => this.hide(toast));
      resume();
      return toast;
    }
    createToast(type, title, message) {
      const toast = document.createElement('div');
      toast.className = 'toast ' + type;
      toast.setAttribute('role', type === 'error' ? 'alert' : 'status');
      toast.setAttribute('aria-atomic', 'true');
      const icon = document.createElement('span');
      icon.className = 'toast-icon';
      icon.setAttribute('aria-hidden', 'true');
      icon.textContent = { success: '✓', error: '×', warning: '!', info: 'i' }[type];
      const content = document.createElement('div');
      content.className = 'toast-content';
      const heading = document.createElement('strong');
      heading.className = 'toast-title';
      heading.textContent = String(title ?? '');
      const text = document.createElement('p');
      text.className = 'toast-message';
      text.textContent = String(message ?? '');
      content.append(heading, text);
      const close = document.createElement('button');
      close.type = 'button'; close.className = 'toast-close';
      close.setAttribute('aria-label', 'Dismiss notification'); close.textContent = '×';
      toast.append(icon, content, close);
      return toast;
    }
    hide(toast) {
      const state = this.timers.get(toast);
      if (!state) return;
      clearTimeout(state.timer);
      this.timers.delete(toast);
      if (toast.contains(document.activeElement)) {
        if (state.previousFocus?.isConnected && state.previousFocus !== document.body) state.previousFocus.focus();
        else {
          const fallback = document.querySelector('main');
          if (fallback) {
            const hadTabindex = fallback.hasAttribute('tabindex');
            if (!hadTabindex) fallback.setAttribute('tabindex', '-1');
            fallback.focus();
            if (!hadTabindex) fallback.addEventListener('blur', () => fallback.removeAttribute('tabindex'), { once: true });
          }
        }
      }
      toast.remove();
    }
    clear() { for (const toast of this.timers.keys()) this.hide(toast); }
  }
  const Toast = new ToastManager();
  window.Toast = Toast;
  window.showSuccessToast = (title, message, duration) => Toast.show('success', title, message, duration);
  window.showErrorToast = (title, message, duration) => Toast.show('error', title, message, duration);
  window.showWarningToast = (title, message, duration) => Toast.show('warning', title, message, duration);
  window.showInfoToast = (title, message, duration) => Toast.show('info', title, message, duration);
  // Preserve native alert semantics; only explicitly opted-in messages use toasts.
  function enhanceFlashMessages() {
    document.querySelectorAll('[data-flash-type="success"]').forEach(notice => {
      if (Toast.show('success', 'Success', notice.textContent, 10000)) notice.hidden = true;
    });
  }
  if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', enhanceFlashMessages, { once: true });
  else enhanceFlashMessages();
})();
