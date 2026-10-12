( function ( wp ) {
	'use strict';

	const { registerBlockType } = wp.blocks;
	const { ColorPalette, InnerBlocks, InspectorControls, MediaUpload, MediaUploadCheck, useBlockProps } = wp.blockEditor;
	const { Button, PanelBody, SelectControl, ToggleControl } = wp.components;
	const { createElement: el } = wp.element;
	const particleColors = [
		{ name: 'Biały', color: '#FFFFFF' },
		{ name: 'Zielony', color: '#01B57C' },
		{ name: 'Niebieski', color: '#43A0ED' },
		{ name: 'Fioletowy', color: '#A857BC' },
		{ name: 'Czerwony', color: '#D65251' },
		{ name: 'Złoty', color: '#D8AB52' },
	];
	const heroMotifs = () => el( 'div', { className: 'dev-spark-hero__motifs', 'aria-hidden': true },
		el( 'svg', { className: 'dev-spark-hero__motif dev-spark-hero__motif--works', viewBox: '0 0 520 420', fill: 'none' },
			el( 'g', { stroke: 'currentColor', strokeOpacity: '.55' },
				el( 'rect', { x: '140', y: '110', width: '140', height: '200' } ), el( 'rect', { x: '280', y: '110', width: '90', height: '200' } ), el( 'rect', { x: '370', y: '110', width: '140', height: '200' } ), el( 'rect', { x: '50', y: '110', width: '90', height: '200' } ),
				el( 'path', { d: 'M140 110 160 40h100l20 70M140 310l20 70h100l20-70M280 110V70h90v40M280 310v40h90v-40M50 110l-20 15v170l20 15', strokeDasharray: '6 5' } )
			)
		),
		el( 'div', { className: 'dev-spark-hero__motif dev-spark-hero__motif--exp' },
			el( 'div', { className: 'dev-spark-hero__code-card' },
				el( 'div', { className: 'dev-spark-hero__code-bar' }, el( 'i' ), el( 'i' ), el( 'i' ), el( 'b', {}, 'sparkEXP.tsx' ) ),
				el( 'code', {}, el( 'span', {}, 'const idea = spark;' ), el( 'span', {}, 'export default idea;' ), el( 'span', { className: 'dev-spark-hero__caret' } ) )
			)
		),
		el( 'div', { className: 'dev-spark-hero__motif dev-spark-hero__motif--warsaw' },
			el( 'span', { className: 'dev-spark-hero__register dev-spark-hero__register--tl' } ), el( 'span', { className: 'dev-spark-hero__register dev-spark-hero__register--tr' } ), el( 'span', { className: 'dev-spark-hero__register dev-spark-hero__register--bl' } ), el( 'span', { className: 'dev-spark-hero__register dev-spark-hero__register--br' } ), el( 'span', { className: 'dev-spark-hero__cmyk' }, el( 'i' ), el( 'i' ), el( 'i' ), el( 'i' ) )
		),
		el( 'div', { className: 'dev-spark-hero__motif dev-spark-hero__motif--health' }, el( 'i' ), el( 'i' ), el( 'i' ) ),
		el( 'div', { className: 'dev-spark-hero__motif dev-spark-hero__motif--studio' }, el( 'span' ), el( 'span' ), el( 'b', {}, el( 'i' ), ' REC 00:00:00:00' ) )
	);

	const createTemplate = ( attributes ) => [
		[ 'core/group', { className: 'dev-spark-hero__wrap', layout: { type: 'default' } }, [
			[ 'core/group', { className: 'dev-spark-hero__meta', layout: { type: 'default' } }, [
				[ 'core/paragraph', { className: 'dev-spark-hero__meta-item', content: attributes.eyebrow } ],
				[ 'core/paragraph', { className: 'dev-spark-hero__meta-item', content: attributes.address } ],
			] ],
			[ 'core/heading', {
				level: 1,
				className: 'dev-spark-hero__title',
				content: attributes.headline + ' <strong>' + attributes.brand + '</strong>',
			} ],
			[ 'core/group', { className: 'dev-spark-hero__foot', layout: { type: 'default' } }, [
				[ 'core/paragraph', { className: 'dev-spark-hero__lead', content: attributes.lead } ],
				[ 'core/buttons', { className: 'dev-spark-hero__actions', layout: { type: 'flex', justifyContent: 'right' } }, [
					[ 'core/button', {
						className: 'dev-spark-hero__button',
						text: attributes.primaryLabel,
						url: attributes.primaryUrl,
					} ],
					[ 'core/button', {
						className: 'dev-spark-hero__button--ghost',
						text: attributes.secondaryLabel,
						url: attributes.secondaryUrl,
					} ],
				] ],
			] ],
		] ],
	];

	registerBlockType( 'sparkagency/hero', {
		edit: ( { attributes, setAttributes } ) => {
			const theme = [ 'light', 'dark' ].includes( attributes.theme ) ? attributes.theme : 'inherit';
			const brand = [ 'agency', 'works', 'exp', 'warsaw', 'health', 'studio' ].includes( attributes.brand ) ? attributes.brand : 'agency';
			const backgroundStyle = attributes.backgroundStyle === 'none' ? 'none' : 'brand';
			const hasGraphic = attributes.showGraphic === true && !! attributes.graphicUrl;
			const particleStyle = {
				'--hero-bg': attributes.backgroundColor || undefined,
				'--hero-particle-color': attributes.particleColor || undefined,
				'--hero-particle-glow-color': attributes.particleGlowColor || undefined,
			};

			const heroClass = `dev-spark-hero is-brand-${ brand }${ theme === 'inherit' ? '' : ` is-theme-${ theme }` }`;

			return el( 'section', useBlockProps( { className: heroClass, 'data-theme': theme === 'inherit' ? undefined : theme, 'data-brand': brand, 'data-background-style': backgroundStyle, style: particleStyle } ),
				el( InspectorControls, {},
					el( PanelBody, { title: 'Tło i motyw', initialOpen: true },
						el( SelectControl, {
							label: 'Agencja / marka',
							value: brand,
							options: [
								{ label: 'sparkAGENCY', value: 'agency' },
								{ label: 'sparkWORKS', value: 'works' },
								{ label: 'sparkEXP', value: 'exp' },
								{ label: 'sparkWARSAW', value: 'warsaw' },
								{ label: 'sparkHEALTH', value: 'health' },
								{ label: 'sparkSTUDIO', value: 'studio' },
							],
							onChange: ( value ) => setAttributes( { brand: value } ),
						} ),
						el( SelectControl, {
							label: 'Dekoracja tła',
							help: 'Obraz/SVG ustawiasz niezależnie w sekcji „Grafika SVG / obraz”.',
							value: backgroundStyle,
							options: [
								{ label: 'Z dekoracją (motyw agencji)', value: 'brand' },
								{ label: 'Bez dekoracji', value: 'none' },
							],
							onChange: ( value ) => setAttributes( { backgroundStyle: value } ),
						} ),
						el( SelectControl, {
							label: 'Motyw kolorystyczny',
							value: theme,
							options: [
								{ label: 'Zgodny z ustawieniami użytkownika', value: 'inherit' },
								{ label: 'Ciemny', value: 'dark' },
								{ label: 'Jasny', value: 'light' },
							],
							onChange: ( value ) => setAttributes( { theme: value } ),
						} ),
						el( ColorPalette, {
							label: 'Kolor tła',
							colors: particleColors,
							value: attributes.backgroundColor || undefined,
							clearable: true,
							disableCustomColors: false,
							onChange: ( value ) => setAttributes( { backgroundColor: value || '' } ),
						} )
					),
					el( PanelBody, { title: 'Grafika SVG / obraz', initialOpen: false },
						el( ToggleControl, {
							label: 'Pokaż grafikę',
							help: 'Grafika pojawi się po prawej stronie hero. Bez włączenia tej opcji komponent nie wyświetla grafiki.',
							checked: attributes.showGraphic === true,
							onChange: ( value ) => setAttributes( { showGraphic: value } ),
						} ),
						attributes.showGraphic === true && el( MediaUploadCheck, {},
							el( MediaUpload, {
								allowedTypes: [ 'image' ],
								value: attributes.graphicId || 0,
								onSelect: ( media ) => setAttributes( {
									graphicId: media.id || 0,
									graphicUrl: media.url || '',
									graphicAlt: media.alt || '',
								} ),
								render: ( { open } ) => el( Button, {
									variant: 'secondary',
									onClick: open,
								}, attributes.graphicUrl ? 'Zmień grafikę' : 'Wybierz grafikę' ),
							} )
						),
						attributes.showGraphic === true && attributes.graphicUrl && el( Button, {
							variant: 'tertiary',
							isDestructive: true,
							onClick: () => setAttributes( { graphicId: 0, graphicUrl: '', graphicAlt: '' } ),
						}, 'Usuń wybraną grafikę' )
					),
					el( PanelBody, { title: 'Fajerwerki przy kursorze', initialOpen: true },
						el( ToggleControl, {
							label: 'Włącz animację',
							checked: attributes.particleAnimation !== false,
							onChange: ( value ) => setAttributes( { particleAnimation: value } ),
						} ),
						el( ColorPalette, {
							label: 'Kolor fajerwerków',
							colors: particleColors,
							value: attributes.particleColor || undefined,
							clearable: true,
							disableCustomColors: false,
							onChange: ( value ) => setAttributes( { particleColor: value || '' } ),
						} ),
						el( ColorPalette, {
							label: 'Kolor poświaty fajerwerków',
							colors: particleColors,
							value: attributes.particleGlowColor || undefined,
							clearable: true,
							disableCustomColors: false,
							onChange: ( value ) => setAttributes( { particleGlowColor: value || '' } ),
						} )
					)
				),
				el( 'div', { className: 'dev-spark-hero__particles', 'aria-hidden': true },
					el( 'canvas', { className: 'dev-spark-hero__canvas' } )
				),
				backgroundStyle === 'brand' && heroMotifs(),
				hasGraphic && el( 'figure', { className: 'dev-spark-hero__graphic' },
					el( 'img', { src: attributes.graphicUrl, alt: attributes.graphicAlt || '' } )
				),
				el( InnerBlocks, { template: createTemplate( attributes ), templateLock: false } )
			);
		},
		save: () => el( InnerBlocks.Content ),
	} );
} )( window.wp );
