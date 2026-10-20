( function () {
	'use strict';

	function syncTheme( section ) {
		const theme = document.documentElement.dataset.theme || document.body.dataset.theme;

		if ( theme !== 'light' && theme !== 'dark' ) {
			return;
		}

		section.classList.remove( 'is-theme-light', 'is-theme-dark' );
		section.classList.add( `is-theme-${ theme }` );
		section.style.colorScheme = theme;
	}

	function initialize() {
		document.querySelectorAll( '.spark-oneteam' ).forEach( ( section ) => {
			if ( section.dataset.sparkOneteamReady === 'true' ) {
				return;
			}

			section.dataset.sparkOneteamReady = 'true';
			syncTheme( section );
			document.addEventListener( 'sparkpreferenceschange', () => syncTheme( section ) );
		} );
	}

	if ( document.readyState === 'loading' ) {
		document.addEventListener( 'DOMContentLoaded', initialize, { once: true } );
	} else {
		initialize();
	}
} )();
