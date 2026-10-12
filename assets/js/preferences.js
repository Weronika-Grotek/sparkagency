/**
 * Shared appearance and accessibility preferences.
 *
 * This module intentionally owns no header or mobile-menu behaviour. Any
 * [data-theme-toggle] and [data-accessibility-open] trigger can use it.
 */
(() => {
  'use strict';

  const root = document.documentElement;
  const body = document.body;
  const panel = document.getElementById('spark-accessibility');
  const dock = document.querySelector('[data-preferences-dock]');
  const storageKey = 'spark-preferences';
  const switches = ['contrast', 'links', 'spacing', 'motion', 'focus'];
  const themes = ['light', 'dark'];
  const initialTheme = root.dataset.theme || body.dataset.theme ||
    (body.classList.contains('is-theme-light') ? 'light' : 'dark');
  let preferences = {};
  let opener = null;
  let previousOverflow = '';

  if (!panel) return;

  try {
    const saved = JSON.parse(localStorage.getItem(storageKey));
    if (saved && typeof saved === 'object' && !Array.isArray(saved)) preferences = saved;
  } catch { /* Persistence is optional. */ }

  function savePreferences() {
    try { localStorage.setItem(storageKey, JSON.stringify(preferences)); } catch { /* Persistence is optional. */ }
  }

  function currentTheme() {
    return themes.includes(preferences.theme) ? preferences.theme : initialTheme;
  }

  function applyPreferences() {
    const theme = currentTheme();
    root.dataset.theme = theme;
    root.style.colorScheme = theme;
    if (body.hasAttribute('data-theme')) body.dataset.theme = theme;
    if (body.classList.contains('is-theme-light') || body.classList.contains('is-theme-dark')) {
      body.classList.toggle('is-theme-light', theme === 'light');
      body.classList.toggle('is-theme-dark', theme === 'dark');
    }

    document.querySelectorAll('[data-theme-toggle]').forEach((button) => {
      const nextTheme = theme === 'light' ? 'dark' : 'light';
      const label = nextTheme === 'light' ? button.dataset.lightLabel : button.dataset.darkLabel;
      button.setAttribute('aria-label', label);
      button.setAttribute('title', label);
      const labelElement = button.querySelector('[data-theme-label]');
      if (labelElement) labelElement.textContent = label;
    });
    panel.querySelectorAll('[data-theme-set]').forEach((button) => {
      button.setAttribute('aria-pressed', String(button.dataset.themeSet === theme));
    });

    const size = ['100', '125', '150'].includes(preferences.text) ? preferences.text : '100';
    root.toggleAttribute('data-spark-text', size !== '100');
    if (size !== '100') root.dataset.sparkText = size;
    panel.querySelectorAll('[data-text-set]').forEach((button) => {
      button.setAttribute('aria-pressed', String(button.dataset.textSet === size));
    });
    switches.forEach((key) => {
      const enabled = preferences[key] === true;
      root.toggleAttribute(`data-spark-${key}`, enabled);
      panel.querySelector(`[data-accessibility-option="${key}"]`)?.setAttribute('aria-checked', String(enabled));
    });
    document.dispatchEvent(new CustomEvent('sparkpreferenceschange', { detail: { ...preferences, theme } }));
  }

  function setTheme(theme) {
    preferences.theme = theme;
    savePreferences();
    applyPreferences();
  }

  function openPanel(trigger) {
    opener = trigger;
    previousOverflow = body.style.overflow;
    panel.showModal();
    body.style.overflow = 'hidden';
    document.querySelectorAll('[data-accessibility-open]').forEach((button) => button.setAttribute('aria-expanded', 'true'));
    panel.querySelector('[data-accessibility-close]')?.focus();
  }

  if (dock) dock.hidden = false;
  document.querySelectorAll('[data-theme-toggle]').forEach((button) => {
    button.addEventListener('click', () => setTheme(currentTheme() === 'light' ? 'dark' : 'light'));
  });
  document.querySelectorAll('[data-accessibility-open]').forEach((button) => {
    button.addEventListener('click', () => openPanel(button));
  });
  panel.querySelector('[data-accessibility-close]')?.addEventListener('click', () => panel.close());
  panel.addEventListener('click', (event) => {
    if (event.target === panel) panel.close();
  });
  panel.addEventListener('close', () => {
    body.style.overflow = previousOverflow;
    document.querySelectorAll('[data-accessibility-open]').forEach((button) => button.setAttribute('aria-expanded', 'false'));
    opener?.focus();
    opener = null;
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
  panel.querySelector('[data-accessibility-reset]')?.addEventListener('click', () => {
    preferences = {};
    savePreferences();
    applyPreferences();
  });

  applyPreferences();
})();
