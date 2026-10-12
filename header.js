/** Spark header. Author: dev. No external dependencies. */
(() => {
  'use strict';

  const header = document.querySelector('[data-spark-header]');
  if (!header) return;

  const root = document.documentElement;
  const body = document.body;
  const navigation = header.querySelector('.p-header__navigation');
  const toggle = header.querySelector('[data-menu-toggle]');
  const mobile = window.matchMedia('(max-width: 53.75rem)');
  const adminBar = document.getElementById('wpadminbar');
  let frame = 0;

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
  navigation.addEventListener('click', (event) => {
    if (event.target.closest('[data-accessibility-open]')) setMenu(false, true);
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
  scheduleUpdate();
})();
