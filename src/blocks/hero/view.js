( function () {
	'use strict';

	const initialize = () => {
		document.querySelectorAll( '.dev-spark-hero, [data-spark-fireworks]' ).forEach( ( hero ) => {
			if ( hero.dataset.initialized ) return;
			hero.dataset.initialized = 'true';

			const reducedMotion = window.matchMedia( '(prefers-reduced-motion: reduce)' );
			const motionReduced = () => reducedMotion.matches || document.documentElement.hasAttribute( 'data-spark-motion' );
			const brand = hero.querySelector( '.dev-spark-hero__title strong' );
			if ( brand ) {
				brand.setAttribute( 'aria-label', brand.textContent );
				const letters = Array.from( brand.textContent, ( letter, index ) => {
					const span = document.createElement( 'span' );
					span.textContent = letter;
					span.style.setProperty( '--i', index );
					span.setAttribute( 'aria-hidden', 'true' );
					return span;
				} );
				brand.replaceChildren( ...letters );
			}

			const canvas = hero.querySelector( 'canvas' );
			const ctx = canvas && canvas.getContext( '2d' );
			if ( ! ctx ) return;

			let palette = [ '#FFFFFF', '#D8AB52', '#01B57C', '#43A0ED', '#A857BC', '#D65251' ];
			let particleBlend = 'lighter';
			let width = 0;
			let height = 0;
			let particles = [];
			let x = 0.7;
			let y = 0.45;
			let targetX = x;
			let targetY = y;
			let visible = false;
			let frameId = 0;
			let lightTheme = false;
			let glowColor = '#FFFFFF';
			let particleBackgroundEnabled = false;
			let particleAnimationEnabled = true;

			const hexToRgba = ( color, alpha ) => {
				const hex = color.slice( 1 );
				const expandedHex = hex.length === 3 ? hex.split( '' ).map( ( character ) => character + character ).join( '' ) : hex;
				const red = parseInt( expandedHex.slice( 0, 2 ), 16 );
				const green = parseInt( expandedHex.slice( 2, 4 ), 16 );
				const blue = parseInt( expandedHex.slice( 4, 6 ), 16 );
				return `rgba(${ red }, ${ green }, ${ blue }, ${ alpha })`;
			};

			const getCustomColor = ( property ) => {
				const color = window.getComputedStyle( hero ).getPropertyValue( property ).trim();
				return /^#[\da-f]{3}(?:[\da-f]{3})?$/i.test( color ) ? color : '';
			};

			const getBrand = () => {
				const brandNames = [ 'agency', 'works', 'exp', 'warsaw', 'health', 'studio' ];
				if ( brandNames.includes( hero.dataset.brand ) ) return hero.dataset.brand;
				const brandNode = hero.closest( '[data-brand]' );
				if ( brandNode && brandNames.includes( brandNode.dataset.brand ) ) return brandNode.dataset.brand;
				const brand = brandNames.find( ( name ) => hero.closest( `.is-brand-${ name }` ) );
				if ( brand ) return brand;
				return 'agency';
			};

			const isLightTheme = () => {
				const pageTheme = document.documentElement.dataset.theme || document.body.dataset.theme;
				if ( pageTheme ) return pageTheme === 'light';
				if ( hero.dataset.theme ) return hero.dataset.theme === 'light';
				return hero.classList.contains( 'is-theme-light' );
			};

			const refreshColors = () => {
				const brandName = getBrand();
				const customParticleColor = getCustomColor( '--hero-particle-color' );
				const customGlowColor = getCustomColor( '--hero-particle-glow-color' );
				lightTheme = isLightTheme();
				const darkPalette = [ '#FFFFFF', '#D8AB52', '#01B57C', '#43A0ED', '#A857BC', '#D65251' ];
				const brandColors = {
					agency: lightTheme ? '#0E1116' : '#FFFFFF',
					works: lightTheme ? '#8C6A2C' : '#D8AB52',
					exp: '#01B57C',
					warsaw: '#43A0ED',
					health: lightTheme ? '#8E3FA3' : '#A857BC',
					studio: '#D65251',
				};
				palette = customParticleColor ? [ customParticleColor ] : brandName === 'agency' ? [ brandColors.agency, ...darkPalette.slice( 1 ) ] : [ brandColors[ brandName ], brandColors[ brandName ], lightTheme ? '#0E1116' : '#F3F2EF' ];
				glowColor = customGlowColor || ( lightTheme ? '#0E1116' : '#FFFFFF' );
				// Fajerwerki są wspólnym tłem każdej marki. Opcja dekoracji
				// kontroluje tylko ozdobnik marki po prawej stronie Hero.
				particleBackgroundEnabled = true;
				particleAnimationEnabled = hero.dataset.particleAnimation !== 'false' && particleBackgroundEnabled;
				particleBlend = lightTheme ? 'multiply' : 'lighter';
			};

			const paintStill = () => {
				ctx.clearRect( 0, 0, width, height );
				ctx.globalCompositeOperation = 'source-over';
				ctx.globalAlpha = 1;
				const glow = ctx.createRadialGradient( width * 0.7, height * 0.45, 0, width * 0.7, height * 0.45, Math.max( 1, width * 0.4 ) );
				glow.addColorStop( 0, hexToRgba( glowColor, lightTheme ? 0.08 : 0.12 ) );
				glow.addColorStop( 1, hexToRgba( glowColor, 0 ) );
				ctx.fillStyle = glow;
				ctx.fillRect( 0, 0, width, height );
			};

			const resize = () => {
				const rect = canvas.getBoundingClientRect();
				refreshColors();
				width = rect.width;
				height = rect.height;
				const ratio = Math.min( window.devicePixelRatio || 1, 2 );
				canvas.width = Math.max( 1, Math.round( width * ratio ) );
				canvas.height = Math.max( 1, Math.round( height * ratio ) );
				ctx.setTransform( ratio, 0, 0, ratio, 0, 0 );
				particles = [];
				if ( ! particleBackgroundEnabled ) {
					ctx.clearRect( 0, 0, width, height );
					return;
				}
				paintStill();
			};

			const animate = () => {
				frameId = 0;
				if ( ! hero.isConnected || ! visible || document.hidden || motionReduced() || ! particleAnimationEnabled ) return;
				// Keep the controller in lockstep with the display. The supplied
				// prototype updates on every animation frame; throttling this to
				// 60 fps made cursor-following visibly lag behind on 120/144 Hz displays.
				x += ( targetX - x ) * 0.04;
				y += ( targetY - y ) * 0.04;
				// Płótno jest przezroczyste, więc ślad zanika przez destination-out (jak trail w prototypie).
				ctx.globalCompositeOperation = 'destination-out';
				ctx.globalAlpha = 1;
				ctx.fillStyle = 'rgba(0, 0, 0, 0.22)';
				ctx.fillRect( 0, 0, width, height );
				ctx.globalCompositeOperation = particleBlend;

				for ( let index = 0; index < 4; index++ ) {
					const angle = Math.random() * Math.PI * 2;
					const speed = 0.4 + Math.random() * 2.2;
					particles.push( {
						x: x * width,
						y: y * height,
						vx: Math.cos( angle ) * speed,
						vy: Math.sin( angle ) * speed - 0.3,
						life: 1,
						decay: 0.006 + Math.random() * 0.012,
						color: palette[ Math.floor( Math.random() * palette.length ) ],
						size: 0.6 + Math.random() * 1.6,
					} );
				}

				particles = particles.filter( ( particle ) => {
					const oldX = particle.x;
					const oldY = particle.y;
					particle.vx *= 0.985;
					particle.vy = particle.vy * 0.985 + 0.012;
					particle.x += particle.vx;
					particle.y += particle.vy;
					particle.life -= particle.decay;
					if ( particle.life <= 0 ) return false;
					ctx.strokeStyle = particle.color;
					ctx.globalAlpha = particle.life * 0.9;
					ctx.lineWidth = particle.size;
					ctx.beginPath();
					ctx.moveTo( oldX - particle.vx * 4, oldY - particle.vy * 4 );
					ctx.lineTo( particle.x, particle.y );
					ctx.stroke();
					return true;
				} );

				ctx.globalAlpha = 1;
				const glow = ctx.createRadialGradient( x * width, y * height, 0, x * width, y * height, Math.max( 1, Math.max( width, height ) * 0.35 ) );
				glow.addColorStop( 0, hexToRgba( glowColor, 0.12 ) );
				glow.addColorStop( 1, hexToRgba( glowColor, 0 ) );
				ctx.fillStyle = glow;
				ctx.fillRect( 0, 0, width, height );
				frameId = requestAnimationFrame( animate );
			};

			const syncAnimation = () => {
				cancelAnimationFrame( frameId );
				frameId = 0;
				if ( motionReduced() || ! particleAnimationEnabled ) {
					if ( particleBackgroundEnabled ) paintStill();
					else ctx.clearRect( 0, 0, width, height );
					return;
				}
				if ( visible && ! document.hidden ) frameId = requestAnimationFrame( animate );
			};

			hero.addEventListener( 'pointermove', ( event ) => {
				if ( event.pointerType === 'touch' ) return;
				const rect = hero.getBoundingClientRect();
				targetX = ( event.clientX - rect.left ) / Math.max( 1, rect.width );
				targetY = ( event.clientY - rect.top ) / Math.max( 1, rect.height );
			}, { passive: true } );
			hero.addEventListener( 'pointerleave', () => {
				targetX = 0.7;
				targetY = 0.45;
			} );
			document.addEventListener( 'visibilitychange', syncAnimation );
			reducedMotion.addEventListener( 'change', syncAnimation );
			new ResizeObserver( resize ).observe( hero );
			new IntersectionObserver( ( [ entry ] ) => {
				visible = entry.isIntersecting;
				syncAnimation();
			} ).observe( hero );
			const refresh = () => {
				resize();
				syncAnimation();
			};
			new MutationObserver( refresh ).observe( document.documentElement, {
				attributes: true,
				attributeFilter: [ 'class', 'data-brand', 'data-theme', 'data-spark-motion' ],
			} );
			new MutationObserver( refresh ).observe( document.body, {
				attributes: true,
				attributeFilter: [ 'class', 'data-brand', 'data-theme' ],
			} );
			new MutationObserver( refresh ).observe( hero, {
				attributes: true,
				attributeFilter: [ 'class', 'data-brand', 'data-theme', 'data-background-style', 'data-particle-animation' ],
			} );
			document.addEventListener( 'sparkpreferenceschange', refresh );
			resize();
		} );
	};

	if ( document.readyState === 'loading' ) {
		document.addEventListener( 'DOMContentLoaded', initialize, { once: true } );
	} else {
		initialize();
	}
} )();
