( function ( wp ) {
	'use strict';

	const { registerBlockType } = wp.blocks;
	const { InspectorControls, RichText, useBlockProps } = wp.blockEditor;
	const { PanelBody, TextControl, SelectControl, Button } = wp.components;
	const { createElement: el, Fragment, RawHTML } = wp.element;

	const motifs = {
		works: '<svg class="dev-spark-group__motif dev-spark-group__m-works" viewBox="0 0 520 420" fill="none" aria-hidden="true" style="color:var(--pc)"><g stroke="currentColor" stroke-opacity=".6"><rect x="140" y="110" width="140" height="200"/><rect x="280" y="110" width="90" height="200"/><rect x="370" y="110" width="140" height="200"/><rect x="50" y="110" width="90" height="200"/><path d="M140 110 L160 40 H260 L280 110 M140 310 L160 380 H260 L280 310"/><path d="M280 110 V70 H370 V110 M280 310 V350 H370 V310" stroke-dasharray="6 5"/></g></svg>',
		exp: '<div class="dev-spark-group__motif dev-spark-group__m-exp" aria-hidden="true"></div>',
		warsaw: '<div class="dev-spark-group__motif" style="inset:16px" aria-hidden="true"><span class="dev-spark-group__reg" style="right:0;top:0"></span><span class="dev-spark-group__reg" style="right:0;bottom:0"></span><span class="dev-spark-group__cmyk" style="top:auto;bottom:48px;right:4px"><i style="background:#00AEEF"></i><i style="background:#EC008C"></i><i style="background:#FFF200"></i><i style="background:#000;outline:1px solid #444"></i></span></div>',
		health: '<div class="dev-spark-group__motif dev-spark-group__caps" style="right:4%;bottom:10%;width:45%;height:60%" aria-hidden="true"><i style="width:220px;height:78px;left:20%;top:10%;transform:rotate(-28deg)"></i><i style="width:130px;height:46px;right:0;top:55%;transform:rotate(18deg)"></i></div>',
		studio: '<div class="dev-spark-group__motif dev-spark-group__m-studio" aria-hidden="true"><span class="dev-spark-group__lbox" style="top:0"></span><span class="dev-spark-group__lbox" style="bottom:0"></span></div>',
		none: '',
	};
	const motifOptions = [
		{ label: 'Packaging (works)', value: 'works' },
		{ label: 'Software (exp)', value: 'exp' },
		{ label: 'Print (warsaw)', value: 'warsaw' },
		{ label: 'Pharma (health)', value: 'health' },
		{ label: 'Video (studio)', value: 'studio' },
		{ label: 'Brak', value: 'none' },
	];
	const brandOptions = [
		{ label: 'Agency', value: 'agency' },
		{ label: 'Works', value: 'works' },
		{ label: 'EXP', value: 'exp' },
		{ label: 'Warsaw', value: 'warsaw' },
		{ label: 'Health', value: 'health' },
		{ label: 'Studio', value: 'studio' },
	];
	const newPanel = {
		motif: 'none',
		brand: 'agency',
		category: 'Kategoria',
		prefix: 'spark',
		name: 'NOWA',
		text: 'Opis marki.',
		pills: [ 'Tag' ],
		ctaLabel: 'Podgląd strony',
		url: '',
	};

	const plain = ( html ) => String( html || '' ).replace( /<[^>]*>/g, '' ).replace( /&amp;/g, '&' );
	const pad = ( n ) => String( n ).padStart( 2, '0' );

	const text = ( edit, tagName, className, value, placeholder, onChange ) => edit
		? el( RichText, { tagName, className, value, placeholder, allowedFormats: [], onChange } )
		: el( RichText.Content, { tagName, className, value } );

	const panelView = ( panel, index, total, edit, update ) => {
		const set = ( patch ) => update( index, patch );
		const pills = Array.isArray( panel.pills ) ? panel.pills : [];
		const ctaProps = { className: 'dev-spark-group__go', 'aria-label': plain( panel.ctaLabel ) + ' ' + plain( panel.prefix ) + plain( panel.name ) };
		if ( ! edit && panel.url ) {
			ctaProps.href = panel.url;
		} else if ( ! panel.url ) {
			ctaProps[ 'aria-disabled' ] = 'true';
		}

		return el( 'article', {
			key: index,
			className: 'dev-spark-group__panel is-brand-' + ( panel.brand || 'agency' ),
			style: { '--i': index },
		},
			motifs[ panel.motif ] ? el( RawHTML, null, motifs[ panel.motif ] ) : null,
			el( 'div', { className: 'dev-spark-group__panel-l' },
				el( 'div', { className: 'dev-spark-group__panel-idx dev-spark-group__mono dev-spark-group__muted' },
					el( 'b', null, pad( index + 1 ) ),
					el( 'span', null, '/ ' + pad( total ) ),
					el( 'span', null, '· ', text( edit, 'span', '', panel.category, 'Kategoria', ( v ) => set( { category: v } ) ) )
				),
				el( 'h3', null,
					text( edit, 'span', 'dev-spark-group__l', panel.prefix, 'spark', ( v ) => set( { prefix: v } ) ),
					text( edit, 'span', 'dev-spark-group__b', panel.name, 'NAZWA', ( v ) => set( { name: v } ) )
				)
			),
			el( 'div', { className: 'dev-spark-group__panel-r' },
				text( edit, 'p', '', panel.text, 'Opis marki', ( v ) => set( { text: v } ) ),
				el( 'div', { className: 'dev-spark-group__pills' },
					pills.map( ( pill, i ) => text( edit, 'span', '', pill, 'Tag', ( v ) => set( { pills: pills.map( ( p, j ) => ( j === i ? v : p ) ) } ) ) )
						.map( ( node, i ) => el( Fragment, { key: i }, node ) )
				),
				el( 'a', ctaProps,
					text( edit, 'span', '', panel.ctaLabel, 'Podgląd strony', ( v ) => set( { ctaLabel: v } ) ),
					' ',
					el( 'span', { 'aria-hidden': 'true' }, '→' )
				)
			)
		);
	};

	const sectionView = ( attributes, edit, setAttributes ) => {
		const panels = Array.isArray( attributes.panels ) ? attributes.panels : [];
		const update = ( index, patch ) => setAttributes( { panels: panels.map( ( p, i ) => ( i === index ? { ...p, ...patch } : p ) ) } );

		return el( 'div', { className: 'dev-spark-group__wrap' },
			el( 'div', { className: 'dev-spark-group__head' },
				text( edit, 'span', 'dev-spark-group__mono dev-spark-group__muted', attributes.eyebrow, 'Etykieta sekcji', ( v ) => setAttributes( { eyebrow: v } ) ),
				el( 'div', null,
					text( edit, 'h2', '', attributes.title, 'Nagłówek sekcji', ( v ) => setAttributes( { title: v } ) ),
					text( edit, 'p', 'dev-spark-group__lede', attributes.lede, 'Opis sekcji', ( v ) => setAttributes( { lede: v } ) )
				)
			),
			el( 'div', { className: 'dev-spark-group__stack' },
				panels.map( ( panel, index ) => panelView( panel, index, panels.length, edit, update ) )
			)
		);
	};

	const move = ( list, from, to ) => {
		const next = [ ...list ];
		[ next[ from ], next[ to ] ] = [ next[ to ], next[ from ] ];
		return next;
	};

	registerBlockType( 'sparkagency/group', {
		edit: ( { attributes, setAttributes } ) => {
			const panels = Array.isArray( attributes.panels ) ? attributes.panels : [];
			const setPanels = ( next ) => setAttributes( { panels: next } );
			const update = ( index, patch ) => setPanels( panels.map( ( p, i ) => ( i === index ? { ...p, ...patch } : p ) ) );

			return el( Fragment, null,
				el( InspectorControls, null,
					panels.map( ( panel, index ) => {
						const pills = Array.isArray( panel.pills ) ? panel.pills : [];
						return el( PanelBody, {
							key: index,
							title: pad( index + 1 ) + ' · ' + ( plain( panel.prefix ) + plain( panel.name ) ),
							initialOpen: false,
						},
							el( TextControl, {
								label: 'Adres strony marki (URL)',
								type: 'url',
								value: panel.url || '',
								__nextHasNoMarginBottom: true,
								onChange: ( v ) => update( index, { url: v } ),
							} ),
							el( SelectControl, { label: 'Marka (kolor i zaokrąglenia z motywu)', value: panel.brand || 'agency', options: brandOptions, __nextHasNoMarginBottom: true, onChange: ( v ) => update( index, { brand: v } ) } ),
							el( SelectControl, { label: 'Motyw tła', value: panel.motif, options: motifOptions, __nextHasNoMarginBottom: true, onChange: ( v ) => update( index, { motif: v } ) } ),
							el( 'div', { style: { marginTop: '16px' } },
								pills.length ? el( 'strong', null, 'Tagi' ) : null,
								pills.map( ( pill, i ) => el( 'div', { key: i, style: { display: 'flex', gap: '4px', alignItems: 'flex-end', marginTop: '6px' } },
									el( 'div', { style: { flex: 1 } }, el( TextControl, {
										value: plain( pill ),
										__nextHasNoMarginBottom: true,
										onChange: ( v ) => update( index, { pills: pills.map( ( p, j ) => ( j === i ? v : p ) ) } ),
									} ) ),
									el( Button, { icon: 'trash', label: 'Usuń tag', isDestructive: true, onClick: () => update( index, { pills: pills.filter( ( p, j ) => j !== i ) } ) } )
								) ),
								el( Button, { variant: 'secondary', style: { marginTop: '8px' }, onClick: () => update( index, { pills: [ ...pills, 'Tag' ] } ) }, 'Dodaj tag' )
							),
							el( 'div', { style: { display: 'flex', gap: '4px', marginTop: '16px' } },
								el( Button, { icon: 'arrow-up-alt2', label: 'W górę', disabled: 0 === index, onClick: () => setPanels( move( panels, index, index - 1 ) ) } ),
								el( Button, { icon: 'arrow-down-alt2', label: 'W dół', disabled: index === panels.length - 1, onClick: () => setPanels( move( panels, index, index + 1 ) ) } ),
								el( Button, { icon: 'trash', isDestructive: true, variant: 'tertiary', onClick: () => setPanels( panels.filter( ( p, i ) => i !== index ) ) }, 'Usuń kartę' )
							)
						);
					} ),
					el( PanelBody, { title: 'Karty', initialOpen: panels.length === 0 },
						el( Button, { variant: 'primary', onClick: () => setPanels( [ ...panels, { ...newPanel, pills: [ ...newPanel.pills ] } ] ) }, 'Dodaj kartę' )
					)
				),
				el( 'section', useBlockProps( { className: 'dev-spark-group is-theme-light dev-spark-group--editor' } ),
					sectionView( attributes, true, setAttributes ),
					el( 'div', { className: 'dev-spark-group__wrap', style: { marginTop: '16px' } },
						el( Button, { variant: 'secondary', onClick: () => setPanels( [ ...panels, { ...newPanel, pills: [ ...newPanel.pills ] } ] ) }, 'Dodaj kartę' )
					)
				)
			);
		},
		save: ( { attributes } ) => el( 'section', wp.blockEditor.useBlockProps.save( { className: 'dev-spark-group is-theme-light', 'data-dev-spark-group': '', 'aria-label': plain( attributes.eyebrow ) } ),
			sectionView( attributes, false )
		),
	} );
} )( window.wp );
