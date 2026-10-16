/** Spark Agency header behaviour. No external dependencies. */
(() => {
  'use strict';

  const header = document.querySelector('[data-spark-header]');
  if (!header) return;

  const root = document.documentElement;
  const body = document.body;
  const navigation = header.querySelector('.p-header__navigation');
  const toggle = header.querySelector('[data-menu-toggle]');
  const mobile = window.matchMedia('(max-width: 53.75rem)');
  const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)');
  const adminBar = document.getElementById('wpadminbar');
  const pageTransition = document.querySelector('[data-page-transition]');
  let frame = 0;
  let isNavigating = false;
  const prefetchedPages = new Set();

  function motionIsDisabled() {
    return reducedMotion.matches || root.hasAttribute('data-spark-motion');
  }

  function pageTransitionDuration() {
    const duration = getComputedStyle(pageTransition)
      .getPropertyValue('--spark-page-transition-duration-in')
      .trim();
    const value = Number.parseFloat(duration);

    if (!Number.isFinite(value)) return 360;
    return duration.endsWith('ms') ? value : value * 1000;
  }

  function destinationForLink(link) {
    if (
      link.hasAttribute('download') ||
      link.hasAttribute('data-no-page-transition') ||
      link.closest('#wpadminbar')
    ) return null;

    const target = link.getAttribute('target');
    if (target && target.toLowerCase() !== '_self') return null;

    try {
      const destination = new URL(link.href, window.location.href);
      const isSamePage = destination.pathname === window.location.pathname && destination.search === window.location.search;

      if (
        destination.protocol !== window.location.protocol ||
        destination.origin !== window.location.origin ||
        isSamePage
      ) return null;

      return destination;
    } catch (error) {
      return null;
    }
  }

  function prefetchPage(link) {
    if (!link) return;

    const connection = navigator.connection;
    const destination = destinationForLink(link);
    if (
      !destination ||
      link.dataset.sparkPrefetched ||
      prefetchedPages.has(destination.href) ||
      connection?.saveData ||
      /(^|-)2g$/.test(connection?.effectiveType || '')
    ) return;

    const prefetch = document.createElement('link');
    prefetch.rel = 'prefetch';
    prefetch.href = destination.href;
    prefetch.as = 'document';
    document.head.append(prefetch);
    prefetchedPages.add(destination.href);
    link.dataset.sparkPrefetched = 'true';
  }

  function canAnimateLink(event, link) {
    if (
      !pageTransition ||
      isNavigating ||
      motionIsDisabled() ||
      event.defaultPrevented ||
      event.button !== 0 ||
      event.metaKey ||
      event.ctrlKey ||
      event.shiftKey ||
      event.altKey
    ) return false;

    return Boolean(destinationForLink(link));
  }

  function beginPageTransition(event) {
    if (!(event.target instanceof Element)) return;
    const link = event.target.closest('a[href]');
    if (!link || !canAnimateLink(event, link)) return;

    const destination = destinationForLink(link);
    if (!destination) return;
    event.preventDefault();
    isNavigating = true;

    try {
      sessionStorage.setItem('spark-page-transition', String(Date.now()));
    } catch (error) { /* The leave animation still works without storage. */ }

    root.classList.add('is-page-transition-leaving');
    window.setTimeout(() => window.location.assign(destination.href), pageTransitionDuration());
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

  document.addEventListener('click', beginPageTransition, true);
  document.addEventListener('pointerover', (event) => {
    if (!(event.target instanceof Element)) return;
    prefetchPage(event.target.closest('a[href]'));
  }, { passive: true, capture: true });
  document.addEventListener('focusin', (event) => {
    if (!(event.target instanceof Element)) return;
    prefetchPage(event.target.closest('a[href]'));
  });
  document.addEventListener('touchstart', (event) => {
    if (!(event.target instanceof Element)) return;
    prefetchPage(event.target.closest('a[href]'));
  }, { passive: true, capture: true });
  window.addEventListener('pageshow', (event) => {
    if (!event.persisted) return;
    root.classList.remove('is-page-transition-leaving');
    root.removeAttribute('data-spark-page-enter');
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
