/** Spark header. Author: dev. No external dependencies. */
(() => {
  'use strict';

  const header = document.querySelector('[data-spark-header]');
  if (!header) return;

  const root = document.documentElement;
  const body = document.body;
  const navigation = header.querySelector('.p-header__navigation');
  const toggle = header.querySelector('[data-menu-toggle]');
  const themeToggle = header.querySelector('[data-theme-toggle]');
  const panel = document.getElementById('spark-accessibility');
  const panelTrigger = header.querySelector('[data-accessibility-open]');
  const mobile = window.matchMedia('(max-width: 53.75rem)');
  const adminBar = document.getElementById('wpadminbar');
  const storageKey = 'spark-header-preferences';
  const switches = ['contrast', 'links', 'spacing', 'motion', 'focus'];
  const initialTheme = root.dataset.theme || body.dataset.theme ||
    (body.classList.contains('is-theme-light') ? 'light' : 'dark');
  let preferences = {};
  let frame = 0;
  let previousOverflow = '';

  try {
    const saved = JSON.parse(localStorage.getItem(storageKey));
    if (saved && typeof saved === 'object' && !Array.isArray(saved)) preferences = saved;
  } catch { /* Private browsing or disabled storage must not break navigation. */ }

  function savePreferences() {
    try { localStorage.setItem(storageKey, JSON.stringify(preferences)); } catch { /* Optional persistence. */ }
  }

  function setMenu(open, restoreFocus = false) {
    const expanded = Boolean(open && mobile.matches);
    header.classList.toggle('is-menu-open', expanded);
    toggle.setAttribute('aria-expanded', String(expanded));
    toggle.textContent = expanded ? toggle.dataset.closeLabel : toggle.dataset.openLabel;
    if (restoreFocus) toggle.focus();
    scheduleUpdate();
  }

  function updateLayout() {
    frame = 0;
    const barRect = adminBar?.getBoundingClientRect();
    const adminOffset = barRect ? Math.max(0, Math.min(barRect.height, barRect.bottom)) : 0;
    header.style.setProperty('--spark-admin-offset', `${adminOffset}px`);
    const bottom = header.getBoundingClientRect().bottom;
    header.style.setProperty('--spark-header-bottom', `${Math.max(0, bottom)}px`);
    const scrollable = root.scrollHeight - root.clientHeight;
    const progress = scrollable > 0 ? Math.min(1, Math.max(0, window.scrollY / scrollable)) : 0;
    header.style.setProperty('--spark-scroll-progress', String(progress));
  }

  function scheduleUpdate() {
    if (!frame) frame = window.requestAnimationFrame(updateLayout);
  } 

  function applyPreferences() {
    const theme = ['light', 'dark'].includes(preferences.theme) ? preferences.theme : initialTheme;
    root.dataset.theme = theme;
    root.style.colorScheme = theme;
    // Synchronize an existing global body theme; section-specific themes stay local.
    if (body.hasAttribute('data-theme')) body.dataset.theme = theme;
    if (body.classList.contains('is-theme-light') || body.classList.contains('is-theme-dark')) {
      body.classList.toggle('is-theme-light', theme === 'light');
      body.classList.toggle('is-theme-dark', theme === 'dark');
    }
    header.classList.toggle('is-light', theme === 'light');
    themeToggle.querySelector('[data-theme-label]').textContent =
      theme === 'light' ? themeToggle.dataset.darkLabel : themeToggle.dataset.lightLabel;
    panel.querySelectorAll('[data-theme-set]').forEach((button) => {
      button.setAttribute('aria-pressed', String(button.dataset.themeSet === theme));
    });
    const size = ['100', '125', '150'].includes(preferences.text) ? preferences.text : '100';
    if (size === '100') delete root.dataset.sparkText;
    else root.dataset.sparkText = size;
    panel.querySelectorAll('[data-text-set]').forEach((button) => {
      button.setAttribute('aria-pressed', String(button.dataset.textSet === size));
    });
    switches.forEach((key) => {
      const enabled = preferences[key] === true;
      root.toggleAttribute(`data-spark-${key}`, enabled);
      panel.querySelector(`[data-accessibility-option="${key}"]`).setAttribute('aria-checked', String(enabled));
    });
    scheduleUpdate();
  }

  function setTheme(theme) {
    preferences.theme = theme;
    savePreferences();
    applyPreferences();
  }

  toggle.hidden = false;
  header.querySelector('.p-header__tools').hidden = false;
  header.classList.add('is-enhanced');
  toggle.addEventListener('click', () => setMenu(toggle.getAttribute('aria-expanded') !== 'true'));
  navigation.addEventListener('click', (event) => {
    if (event.target.closest('a')) setMenu(false, mobile.matches);
  });
  document.addEventListener('click', (event) => {
    if (!header.contains(event.target)) setMenu(false);
  });
  document.addEventListener('focusin', (event) => {
    if (header.classList.contains('is-menu-open') && !header.contains(event.target)) setMenu(false);
  });
  document.addEventListener('keydown', (event) => {
    if (event.key === 'Escape' && header.classList.contains('is-menu-open')) {
      event.preventDefault();
      setMenu(false, true);
    }
  });
  mobile.addEventListener('change', () => {
    const focused = document.activeElement;
    const focusWillBeHidden = mobile.matches ? navigation.contains(focused) : focused === toggle;
    setMenu(false);
    if (focusWillBeHidden) {
      if (mobile.matches) toggle.focus();
      else header.querySelector('.p-header__brand a')?.focus();
    }
  });
  themeToggle.addEventListener('click', () => setTheme(root.dataset.theme === 'light' ? 'dark' : 'light'));

  panelTrigger.addEventListener('click', () => {
    setMenu(false, true);
    previousOverflow = body.style.overflow;
    panel.showModal();
    body.style.overflow = 'hidden';
    panelTrigger.setAttribute('aria-expanded', 'true');
    panel.querySelector('[data-accessibility-close]').focus();
  });
  panel.querySelector('[data-accessibility-close]').addEventListener('click', () => panel.close());
  panel.addEventListener('click', (event) => {
    if (event.target !== panel) return;
    const rect = panel.getBoundingClientRect();
    if (event.clientX < rect.left || event.clientX > rect.right || event.clientY < rect.top || event.clientY > rect.bottom) panel.close();
  });
  panel.addEventListener('close', () => {
    body.style.overflow = previousOverflow;
    panelTrigger.setAttribute('aria-expanded', 'false');
    if (mobile.matches) toggle.focus();
    else header.querySelector('.p-header__brand a')?.focus();
  });
  panel.querySelectorAll('[data-theme-set]').forEach((button) => {
    button.addEventListener('click', () => setTheme(button.dataset.themeSet));
  });
  panel.querySelectorAll('[data-text-set]').forEach((button) => {
    button.addEventListener('click', () => {
      preferences.text = button.dataset.textSet;
      savePreferences();
      applyPreferences();
    });
  });
  panel.querySelectorAll('[data-accessibility-option]').forEach((button) => {
    button.addEventListener('click', () => {
      const key = button.dataset.accessibilityOption;
      preferences[key] = preferences[key] !== true;
      savePreferences();
      applyPreferences();
    });
  });
  panel.querySelector('[data-accessibility-reset]').addEventListener('click', () => {
    preferences = {};
    savePreferences();
    applyPreferences();
  });

  window.addEventListener('scroll', scheduleUpdate, { passive: true });
  window.addEventListener('resize', scheduleUpdate);
  window.addEventListener('load', scheduleUpdate, { once: true });
  if ('ResizeObserver' in window) {
    const observer = new ResizeObserver(scheduleUpdate);
    observer.observe(body);
    observer.observe(header);
    if (adminBar) observer.observe(adminBar);
  }
  applyPreferences();
})();
