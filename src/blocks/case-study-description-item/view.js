const reducedMotion = window.matchMedia( '(prefers-reduced-motion: reduce)' ).matches;

const formatValue = ( value ) => {
	return new Intl.NumberFormat( 'pl-PL', { maximumFractionDigits: 0 } ).format( value );
};

const animateCounter = ( element ) => {
	const output = element.querySelector( '[data-case-study-counter-value]' );
	const target = Number.parseInt( element.dataset.counterValue, 10 );
	const duration = Number( element.dataset.counterDuration ) || 1200;

	if ( ! output || ! Number.isFinite( target ) ) {
		return;
	}

	if ( reducedMotion ) {
		output.textContent = formatValue( target );
		return;
	}

	const startedAt = performance.now();
	const tick = ( now ) => {
		const progress = Math.min( 1, ( now - startedAt ) / duration );
		const eased = 1 - Math.pow( 1 - progress, 3 );

		output.textContent = formatValue( Math.round( target * eased ) );

		if ( progress < 1 ) {
			window.requestAnimationFrame( tick );
		}
	};

	window.requestAnimationFrame( tick );
};

const counters = document.querySelectorAll( '[data-case-study-counter]' );

if ( reducedMotion || !( 'IntersectionObserver' in window ) ) {
	counters.forEach( animateCounter );
} else {
	const observer = new IntersectionObserver( ( entries ) => {
		entries.forEach( ( entry ) => {
			if ( entry.isIntersecting ) {
				animateCounter( entry.target );
				observer.unobserve( entry.target );
			}
		} );
	}, { threshold: 0.35 } );

	counters.forEach( ( counter ) => observer.observe( counter ) );
}
