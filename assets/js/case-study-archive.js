(() => {
	document.querySelectorAll('.spark-case-archive').forEach((archive) => {
		const filters = Array.from(archive.querySelectorAll('[data-case-filter]'));
		const cards = Array.from(archive.querySelectorAll('[data-case-card]'));
		if (!filters.length || !cards.length) return;
		const hideTimers = new WeakMap();
		const reduceMotion = window.matchMedia?.('(prefers-reduced-motion: reduce)').matches ?? false;

		filters.forEach((filter) => {
			filter.addEventListener('click', () => {
				const selected = filter.dataset.caseFilter;
				filters.forEach((button) => {
					const active = button === filter;
					button.classList.toggle('is-active', active);
					button.setAttribute('aria-pressed', active ? 'true' : 'false');
				});
				cards.forEach((card) => {
					const companies = (card.dataset.caseCategories || '').split(/\s+/);
					const shouldShow = selected === 'all' || companies.includes(selected);
					const hideTimer = hideTimers.get(card);
					if (hideTimer) window.clearTimeout(hideTimer);
					card.classList.remove('is-filtering-in', 'is-filtering-out');

					if (shouldShow) {
						if (card.hidden) {
							card.hidden = false;
							card.classList.add('is-filtering-in');
							card.addEventListener('animationend', () => card.classList.remove('is-filtering-in'), { once: true });
						}
						return;
					}

					if (!card.hidden) {
						if (reduceMotion) {
							card.hidden = true;
							return;
						}
						card.classList.add('is-filtering-out');
						hideTimers.set(card, window.setTimeout(() => {
							card.hidden = true;
							card.classList.remove('is-filtering-out');
						}, 280));
					}
				});
			});
		});
	});
})();
