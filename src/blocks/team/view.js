( function () {
	'use strict';

	const reducedMotion = window.matchMedia( '(prefers-reduced-motion: reduce)' );
	const properties = [ '--rx', '--ry', '--ax', '--ay' ];

	// Tilt avatara i przesunięcie poświaty za kursorem, jak w prototypie.
	function bind( person ) {
		const avatar = person.querySelector( '.spark-team__avatar' );

		if ( ! avatar || person.dataset.sparkTeamReady === 'true' ) {
			return;
		}

		person.dataset.sparkTeamReady = 'true';

		const reset = () => properties.forEach( ( property ) => avatar.style.removeProperty( property ) );

		person.addEventListener( 'pointermove', ( event ) => {
			if ( reducedMotion.matches || event.pointerType !== 'mouse' ) {
				return;
			}

			const rect = avatar.getBoundingClientRect();
			const x = ( event.clientX - rect.left ) / rect.width;
			const y = ( event.clientY - rect.top ) / rect.height;

			avatar.style.setProperty( '--ry', `${ ( x - 0.5 ) * 16 }deg` );
			avatar.style.setProperty( '--rx', `${ ( 0.5 - y ) * 16 }deg` );
			avatar.style.setProperty( '--ax', `${ x * 100 }%` );
			avatar.style.setProperty( '--ay', `${ y * 100 }%` );
		}, { passive: true } );
		person.addEventListener( 'pointerleave', reset );
		person.addEventListener( 'pointercancel', reset );
		reducedMotion.addEventListener( 'change', reset );
	}

	function initialize() {
		document.querySelectorAll( '.spark-team__person' ).forEach( bind );
	}

	if ( document.readyState === 'loading' ) {
		document.addEventListener( 'DOMContentLoaded', initialize, { once: true } );
	} else {
		initialize();
	}
} )();
