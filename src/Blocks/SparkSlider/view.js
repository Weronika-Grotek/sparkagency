( function () {
	'use strict';

	const clamp = ( value, min, max ) => Math.max( min, Math.min( max, value ) );

	const initialize = () => {
		document.querySelectorAll( '[data-dev-spark-marquee]' ).forEach( ( slider ) => {
			if ( slider.dataset.initialized === 'true' ) return;
			slider.dataset.initialized = 'true';

			let words;
			try {
				words = JSON.parse( slider.dataset.words || '[]' );
			} catch ( error ) {
				words = [];
			}
			if ( ! Array.isArray( words ) || ! words.length ) return;

			const rows = [ ...slider.querySelectorAll( '.dev-spark-marquee__row' ) ];
			const motion = window.matchMedia( '(prefers-reduced-motion: reduce)' );
			const widths = [ 0, 0 ];
			const offsets = [ 0, 0 ];
			let frameId = 0;
			let lastTime = 0;
			let lastY = window.scrollY;
			let velocity = 0;
			let visible = false;

			const rebuild = () => {
				rows.forEach( ( row, rowIndex ) => {
					const items = rowIndex === 0 ? words : [ ...words ].reverse();
					const group = document.createElement( 'div' );
					group.className = 'dev-spark-marquee__group';

					for ( let index = 0; index < items.length * 3; index++ ) {
						const word = document.createElement( 'span' );
						word.className = 'dev-spark-marquee__word';
						word.classList.toggle( 'dev-spark-marquee__word--filled', index % 3 === 1 );
						word.textContent = items[ index % items.length ];
						group.append( word );
					}

					row.replaceChildren( group );
					widths[ rowIndex ] = group.getBoundingClientRect().width;
					const copies = Math.max( 2, Math.ceil( slider.clientWidth / ( widths[ rowIndex ] || 1 ) ) + 2 );
					for ( let index = 1; index < copies; index++ ) row.append( group.cloneNode( true ) );
					offsets[ rowIndex ] = -widths[ rowIndex ];
					row.style.transform = `translate3d(${ offsets[ rowIndex ] }px, 0, 0)`;
				} );
			};

			const stop = () => {
				cancelAnimationFrame( frameId );
				frameId = 0;
				lastTime = 0;
				velocity = 0;
			};

			const frame = ( time ) => {
				if ( ! slider.isConnected ) {
					stop();
					intersection.disconnect();
					resize.disconnect();
					motion.removeEventListener( 'change', sync );
					document.removeEventListener( 'visibilitychange', sync );
					return;
				}

				const delta = lastTime ? clamp( ( time - lastTime ) / ( 1000 / 60 ), 0.1, 3 ) : 1;
				lastTime = time;
				const y = window.scrollY;
				velocity += ( ( y - lastY ) / delta - velocity ) * ( 1 - Math.pow( 0.88, delta ) );
				lastY = y;
				const boost = clamp( Math.abs( velocity ) * 0.35, 0, 18 );
				const direction = velocity < -0.5 ? -1 : 1;
				const skew = clamp( velocity * 0.25, -8, 8 );

				rows.forEach( ( row, index ) => {
					const width = widths[ index ];
					if ( ! width ) return;
					const sign = index === 0 ? -1 : 1;
					offsets[ index ] += ( 0.6 + boost ) * direction * sign * delta;
					offsets[ index ] = ( ( offsets[ index ] % width ) + width ) % width - width;
					row.style.transform = `translate3d(${ offsets[ index ] }px, 0, 0) skewX(${ skew * sign }deg)`;
				} );
				frameId = requestAnimationFrame( frame );
			};

			function sync() {
				if ( motion.matches || document.hidden || ! visible ) {
					stop();
					return;
				}
				if ( ! frameId ) {
					lastY = window.scrollY;
					frameId = requestAnimationFrame( frame );
				}
			}

			const intersection = new IntersectionObserver( ( [ entry ] ) => {
				visible = entry.isIntersecting;
				sync();
			} );
			const resize = new ResizeObserver( rebuild );

			rebuild();
			intersection.observe( slider );
			resize.observe( slider );
			motion.addEventListener( 'change', sync );
			document.addEventListener( 'visibilitychange', sync );
			if ( document.fonts ) document.fonts.ready.then( () => {
				if ( slider.isConnected ) rebuild();
			} );
		} );
	};

	if ( document.readyState === 'loading' ) {
		document.addEventListener( 'DOMContentLoaded', initialize, { once: true } );
	} else {
		initialize();
	}
} )();