( function () {
	'use strict';
	function initialize() { document.querySelectorAll( '.spark-howstart' ).forEach( section => {
		if ( section.dataset.sparkHowstartReady === 'true' ) return; section.dataset.sparkHowstartReady = 'true';
		const syncTheme = () => {
			const theme = document.documentElement.dataset.theme || document.body.dataset.theme;
			if ( theme !== 'light' && theme !== 'dark' ) return;
			section.classList.remove( 'is-theme-light', 'is-theme-dark' );
			section.classList.add( `is-theme-${ theme }` );
			section.style.colorScheme = theme;
		};
		document.addEventListener( 'sparkpreferenceschange', syncTheme );
		syncTheme();
		const steps = [ ...section.querySelectorAll( '.spark-howstart__step' ) ]; if ( ! steps.length ) return;
		const media = window.matchMedia( '(prefers-reduced-motion: reduce)' ); let queued = false;
		const update = () => { queued = false; const reduce = media.matches || document.documentElement.hasAttribute( 'data-spark-motion' ); section.classList.toggle( 'has-motion', ! reduce ); let active = 0, distance = Infinity; steps.forEach( ( step, index ) => { const rect = step.getBoundingClientRect(), next = Math.abs( rect.top + rect.height / 2 - window.innerHeight / 2 ); if ( next < distance ) { distance = next; active = index; } } ); steps.forEach( ( step, index ) => step.classList.toggle( 'is-active', reduce || index <= active ) ); };
		const schedule = () => { if ( ! queued ) { queued = true; window.requestAnimationFrame( update ); } };
		window.addEventListener( 'scroll', schedule, { passive: true } ); window.addEventListener( 'resize', schedule ); media.addEventListener( 'change', schedule ); document.addEventListener( 'sparkpreferenceschange', schedule ); if ( document.fonts ) document.fonts.ready.then( schedule ); update();
	} ); }
	if ( document.readyState === 'loading' ) document.addEventListener( 'DOMContentLoaded', initialize, { once: true } ); else initialize();
} )();
