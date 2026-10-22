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
		el( 'div', { className: 'dev-spark-hero__motif dev-spark-hero__motif--works' } ),
		el( 'div', { className: 'dev-spark-hero__motif dev-spark-hero__motif--exp' } ),
		el( 'div', { className: 'dev-spark-hero__motif dev-spark-hero__motif--warsaw' } ),
		el( 'div', { className: 'dev-spark-hero__motif dev-spark-hero__motif--health' } ),
		el( 'div', { className: 'dev-spark-hero__motif dev-spark-hero__motif--studio' } )
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
			const theme = attributes.theme === 'light' ? 'light' : 'dark';
			const hasGraphic = attributes.showGraphic === true && !! attributes.graphicUrl;
			const particleStyle = {
				'--hero-bg': attributes.backgroundColor || undefined,
				'--hero-particle-color': attributes.particleColor || undefined,
				'--hero-particle-glow-color': attributes.particleGlowColor || undefined,
			};

			return el( 'section', useBlockProps( { className: `dev-spark-hero is-theme-${ theme } is-brand-agency`, 'data-theme': theme, style: particleStyle } ),
				el( InspectorControls, {},
					el( PanelBody, { title: 'Tło i motyw', initialOpen: true },
						el( SelectControl, {
							label: 'Motyw kolorystyczny',
							value: theme,
							options: [
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
				heroMotifs(),
				hasGraphic && el( 'figure', { className: 'dev-spark-hero__graphic' },
					el( 'img', { src: attributes.graphicUrl, alt: attributes.graphicAlt || '' } )
				),
				el( InnerBlocks, { template: createTemplate( attributes ), templateLock: false } )
			);
		},
		save: () => el( InnerBlocks.Content ),
	} );
} )( window.wp );
