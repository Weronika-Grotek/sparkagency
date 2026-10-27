(() => {
	document.querySelectorAll('.spark-case-archive').forEach((archive) => {
		const filters = Array.from(archive.querySelectorAll('[data-case-filter]'));
		const cards = Array.from(archive.querySelectorAll('[data-case-card]'));
		if (!filters.length || !cards.length) return;

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
					card.hidden = selected !== 'all' && !companies.includes(selected);
				});
			});
		});
	});
})();
