( function () {
	'use strict';

	const clamp = ( value ) => Math.max( 0, Math.min( 1, value ) );

	const initialize = () => {
		document.querySelectorAll( '[data-dev-spark-group]' ).forEach( ( section ) => {
			if ( section.dataset.initialized === 'true' ) return;
			section.dataset.initialized = 'true';

			const panels = [ ...section.querySelectorAll( '.dev-spark-group__panel' ) ];
			const motion = window.matchMedia( '(prefers-reduced-motion: reduce)' );
			const finePointer = window.matchMedia( '(pointer: fine)' );
			const abort = new AbortController();
			let frameId = 0;

			const update = () => {
				frameId = 0;
				if ( ! section.isConnected ) {
					abort.abort();
					resize.disconnect();
					return;
				}
				panels.forEach( ( panel, index ) => {
					const next = panels[ index + 1 ];
					const disabled = motion.matches || panel.dataset.tooTall === 'true';
					const progress = ! next || disabled ? 0 : clamp( 1 - ( next.getBoundingClientRect().top - panel.getBoundingClientRect().top ) / Math.max( 1, panel.offsetHeight ) );
					panel.style.setProperty( '--p', progress.toFixed( 3 ) );
				} );
			};
			const schedule = () => {
				if ( ! frameId ) frameId = requestAnimationFrame( update );
			};
			const measure = () => {
				const head = parseFloat( getComputedStyle( section ).getPropertyValue( '--head' ) ) || 0;
				const mobile = window.matchMedia( '(max-width: 860px)' ).matches;
				panels.forEach( ( panel, index ) => {
					const top = head + ( mobile ? 10 + index * 10 : 16 + index * 14 );
					panel.dataset.tooTall = String( panel.offsetHeight + top > window.innerHeight );
				} );
				schedule();
			};

			panels.forEach( ( panel ) => {
				panel.addEventListener( 'pointermove', ( event ) => {
					if ( motion.matches || ! finePointer.matches ) return;
					const rect = panel.getBoundingClientRect();
					panel.style.setProperty( '--mx', ( ( event.clientX - rect.left ) / rect.width * 100 ) + '%' );
					panel.style.setProperty( '--my', ( ( event.clientY - rect.top ) / rect.height * 100 ) + '%' );
				}, { passive: true, signal: abort.signal } );
				panel.addEventListener( 'pointerleave', () => {
					panel.style.removeProperty( '--mx' );
					panel.style.removeProperty( '--my' );
				}, { signal: abort.signal } );
			} );

			const resize = new ResizeObserver( measure );
			panels.forEach( ( panel ) => resize.observe( panel ) );
			window.addEventListener( 'scroll', schedule, { passive: true, signal: abort.signal } );
			window.addEventListener( 'resize', measure, { passive: true, signal: abort.signal } );
			motion.addEventListener( 'change', measure, { signal: abort.signal } );
			measure();
		} );
	};

	if ( 'loading' === document.readyState ) {
		document.addEventListener( 'DOMContentLoaded', initialize );
	} else {
		initialize();
	}
} )();
