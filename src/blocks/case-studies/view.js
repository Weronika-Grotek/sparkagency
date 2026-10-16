const clamp = ( value, min, max ) => Math.min( Math.max( value, min ), max );

const setupCaseStudies = ( section ) => {
	const pin = section.querySelector( '[data-case-pin]' );
	const sticky = section.querySelector( '.spark-case-studies__sticky' );
	const windowElement = section.querySelector( '.spark-case-studies__window' );
	const track = section.querySelector( '[data-case-track]' );
	const current = section.querySelector( '[data-case-current]' );
	const cards = Array.from( section.querySelectorAll( '[data-case-card]' ) );

	if ( ! pin || ! sticky || ! windowElement || ! track || ! cards.length ) return;

	const desktop = window.matchMedia( '(min-width: 900px) and (pointer: fine)' );
	const reducedMotion = window.matchMedia( '(prefers-reduced-motion: reduce)' );
	let frame = 0;
	let distance = 0;
	let pinTop = 0;

	const motionIsDisabled = () => reducedMotion.matches || document.documentElement.hasAttribute( 'data-spark-motion' );
	const isPinned = () => desktop.matches && ! motionIsDisabled();

	const setProgress = ( progress ) => {
		const bounded = clamp( progress, 0, 1 );
		const index = Math.min( cards.length, Math.max( 1, 1 + Math.round( bounded * ( cards.length - 1 ) ) ) );

		section.style.setProperty( '--case-progress', bounded.toFixed( 3 ) );
		if ( current ) current.textContent = String( index ).padStart( 2, '0' );
	};

	const update = () => {
		frame = 0;
		if ( ! isPinned() ) {
			track.style.removeProperty( 'transform' );
			const mobileDistance = Math.max( 0, windowElement.scrollWidth - windowElement.clientWidth );
			setProgress( mobileDistance ? windowElement.scrollLeft / mobileDistance : 0 );
			return;
		}

		const travelled = clamp( window.scrollY - pinTop, 0, distance );
		track.style.transform = `translate3d(${-travelled}px, 0, 0)`;
		setProgress( distance ? travelled / distance : 0 );
	};

	const requestUpdate = () => {
		if ( ! frame ) frame = window.requestAnimationFrame( update );
	};

	const measure = () => {
		const pinActive = isPinned();
		section.classList.toggle( 'is-case-pinned', pinActive );
		section.classList.toggle( 'is-case-static', ! pinActive );
		track.style.removeProperty( 'transform' );
		pin.style.removeProperty( 'height' );

		if ( ! pinActive ) {
			distance = 0;
			requestUpdate();
			return;
		}

		distance = Math.max( 0, track.scrollWidth - windowElement.clientWidth );
		pinTop = pin.getBoundingClientRect().top + window.scrollY;
		pin.style.height = `${sticky.offsetHeight + distance}px`;
		requestUpdate();
	};

	window.addEventListener( 'scroll', requestUpdate, { passive: true } );
	window.addEventListener( 'resize', measure, { passive: true } );
	windowElement.addEventListener( 'scroll', requestUpdate, { passive: true } );
	desktop.addEventListener( 'change', measure );
	reducedMotion.addEventListener( 'change', measure );
	document.addEventListener( 'sparkpreferenceschange', measure );

	if ( 'ResizeObserver' in window ) {
		new ResizeObserver( measure ).observe( track );
	}

	if ( 'IntersectionObserver' in window ) {
		const reveal = new IntersectionObserver( ( entries, observer ) => {
			if ( ! entries.some( ( entry ) => entry.isIntersecting ) ) return;
			section.classList.add( 'is-case-ready' );
			observer.disconnect();
		}, { threshold: 0.12 } );
		reveal.observe( section );
	} else {
		section.classList.add( 'is-case-ready' );
	}

	measure();
};

document.querySelectorAll( '[data-spark-case-studies]' ).forEach( setupCaseStudies );
