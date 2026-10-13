/* global wp */
( function ( wp ) {
	'use strict';

	const { registerBlockType } = wp.blocks;
	const { InspectorControls, RichText, useBlockProps } = wp.blockEditor;
	const { Button, PanelBody, SelectControl, TextControl, TextareaControl } = wp.components;
	const { createElement: el, Fragment } = wp.element;
	const visuals = [
		{ label: 'Specjalizacja', value: 'specialization' },
		{ label: 'Wspólna odpowiedzialność', value: 'ownership' },
		{ label: 'Realizacja', value: 'delivery' },
		{ label: 'Technologia', value: 'technology' },
	];
	const colors = [
		{ label: 'Motyw Agency', value: 'agency' },
		{ label: 'sparkEXP', value: 'exp' },
		{ label: 'sparkWARSAW', value: 'warsaw' },
		{ label: 'sparkHEALTH', value: 'health' },
		{ label: 'sparkWORKS', value: 'works' },
		{ label: 'sparkSTUDIO', value: 'studio' },
	];
	const colorValues = {
		agency: 'var(--spark-color-accent)',
		exp: 'var(--wp--preset--color--exp, #01B57C)',
		warsaw: 'var(--wp--preset--color--warsaw, #43A0ED)',
		health: 'var(--wp--preset--color--health, #8E3FA3)',
		works: 'var(--wp--preset--color--works, #8C6A2C)',
		studio: 'var(--wp--preset--color--studio, #D65251)',
	};
	const teamColors = [ 'health', 'exp', 'works' ];

	const defaultVisualContent = ( type ) => {
		if ( type === 'ownership' ) return { color: 'agency', client: 'Klient', owner: 'Jeden właściciel projektu', teams: [ { label: 'sparkHEALTH', color: 'health' }, { label: 'sparkEXP', color: 'exp' }, { label: 'sparkWORKS', color: 'works' } ] };
		if ( type === 'delivery' ) return { color: 'agency', steps: [ 'Rekomendacja', 'Materiały', 'Produkcja', 'Wdrożenie' ] };
		if ( type === 'technology' ) return { color: 'exp', tags: [ 'Narzędzie', 'Integracja', 'Automatyzacja' ] };
		return { color: 'agency', chips: [ { label: 'Healthcare', brand: 'sparkHEALTH', color: 'health' }, { label: 'Packaging', brand: 'sparkWORKS', color: 'works' }, { label: 'Technologia', brand: 'sparkEXP', color: 'exp' }, { label: 'Video', brand: 'sparkSTUDIO', color: 'studio' }, { label: 'Multi-market', brand: 'sparkWARSAW', color: 'warsaw' } ] };
	};
	const visualContentFor = ( card ) => ( { ...defaultVisualContent( card.visual || 'specialization' ), ...( card.visualContent || {} ) } );
	const colorStyle = ( color, property = '--why-visual-accent' ) => ( { [ property ]: colorValues[ color ] || colorValues.agency } );
	const newCard = ( position ) => ( {
		number: String( position + 1 ).padStart( 2, '0' ),
		title: 'Nowy powód',
		description: 'Dodaj opis korzyści dla klienta.',
		visual: 'specialization',
		visualContent: defaultVisualContent( 'specialization' ),
	} );

	const editable = ( tagName, className, value, placeholder, onChange ) => el( RichText, { tagName, className, value: value || '', placeholder, allowedFormats: [], onChange } );
	const withoutItem = ( list, index ) => list.filter( ( item, position ) => position !== index );
	const replaceItem = ( list, index, item ) => list.map( ( current, position ) => position === index ? item : current );

	const visual = ( type, content ) => {
		const data = { ...defaultVisualContent( type ), ...( content || {} ) };
		const props = { className: `spark-whyagency__visual spark-whyagency__visual--${ type }`, style: colorStyle( data.color ), 'aria-hidden': true };
		if ( type === 'ownership' ) {
			const teams = Array.isArray( data.teams ) ? data.teams : [];
			return el( 'div', props,
				el( 'span', { className: 'spark-whyagency__node' }, data.client || '' ), el( 'span', { className: 'spark-whyagency__arrow' } ), el( 'span', { className: 'spark-whyagency__node spark-whyagency__node--accent' }, data.owner || '' ), el( 'span', { className: 'spark-whyagency__arrow' } ), el( 'span', { className: 'spark-whyagency__team' }, teams.map( ( team, index ) => {
					const item = typeof team === 'object' ? team : { label: team, color: teamColors[ index ] || data.color };
					return el( 'span', { key: index, style: colorStyle( item.color, '--why-chip' ) }, el( 'i' ), item.label || '' );
				} ) )
			);
		}
		if ( type === 'delivery' ) return el( 'div', props, el( 'ol', { className: 'spark-whyagency__timeline' }, ( Array.isArray( data.steps ) ? data.steps : [] ).map( ( step, index ) => el( 'li', { key: index }, step || '' ) ) ) );
		if ( type === 'technology' ) return el( 'div', props, el( 'span', { className: 'spark-whyagency__window' }, el( 'i' ), el( 'i' ), el( 'i' ) ), el( 'span', { className: 'spark-whyagency__tags' }, ( Array.isArray( data.tags ) ? data.tags : [] ).map( ( tag, index ) => el( 'b', { key: index }, tag || '' ) ) ) );
		return el( 'div', props, ( Array.isArray( data.chips ) ? data.chips : [] ).map( ( chip, index ) => el( 'span', { className: 'spark-whyagency__chip', key: index, style: colorStyle( chip.color, '--why-chip' ) }, el( 'i' ), `${ chip.label || '' } `, el( 'b', null, chip.brand || '' ) ) ) );
	};

	function Edit( { attributes, setAttributes } ) {
		const cards = Array.isArray( attributes.cards ) ? attributes.cards : [];
		const theme = [ 'light', 'dark' ].includes( attributes.theme ) ? attributes.theme : 'inherit';
		const updateCard = ( index, patch ) => setAttributes( { cards: cards.map( ( card, position ) => position === index ? { ...card, ...patch } : card ) } );
		const setVisualContent = ( index, patch ) => updateCard( index, { visualContent: { ...visualContentFor( cards[ index ] ), ...patch } } );
		const moveCard = ( index, offset ) => {
			const target = index + offset;
			if ( target < 0 || target >= cards.length ) return;
			const next = [ ...cards ];
			[ next[ index ], next[ target ] ] = [ next[ target ], next[ index ] ];
			setAttributes( { cards: next } );
		};
		const className = `spark-whyagency is-brand-agency${ theme === 'inherit' ? '' : ` is-theme-${ theme }` }`;

		const visualControls = ( card, index ) => {
			const type = card.visual || 'specialization';
			const data = visualContentFor( card );
			const setList = ( property, list ) => setVisualContent( index, { [ property ]: list } );
			if ( type === 'specialization' ) return el( Fragment, null,
				...( Array.isArray( data.chips ) ? data.chips : [] ).map( ( chip, chipIndex ) => el( 'div', { className: 'spark-whyagency__editor-group', key: chipIndex },
					el( TextControl, { label: `Specjalizacja ${ chipIndex + 1 }`, value: chip.label || '', onChange: ( value ) => setList( 'chips', replaceItem( data.chips, chipIndex, { ...chip, label: value } ) ) } ),
					el( TextControl, { label: 'Marka', value: chip.brand || '', onChange: ( value ) => setList( 'chips', replaceItem( data.chips, chipIndex, { ...chip, brand: value } ) ) } ),
					el( SelectControl, { label: 'Kolor punktu', value: chip.color || 'agency', options: colors, onChange: ( value ) => setList( 'chips', replaceItem( data.chips, chipIndex, { ...chip, color: value } ) ) } ),
					el( Button, { isDestructive: true, variant: 'tertiary', onClick: () => setList( 'chips', withoutItem( data.chips, chipIndex ) ) }, 'Usuń specjalizację' )
				) ),
				el( Button, { variant: 'secondary', onClick: () => setList( 'chips', [ ...( data.chips || [] ), { label: 'Nowa specjalizacja', brand: 'sparkBRAND', color: 'agency' } ] ) }, 'Dodaj specjalizację' )
			);
			if ( type === 'ownership' ) return el( Fragment, null,
				el( TextControl, { label: 'Klient / pierwszy etap', value: data.client || '', onChange: ( value ) => setVisualContent( index, { client: value } ) } ),
				el( TextControl, { label: 'Właściciel projektu', value: data.owner || '', onChange: ( value ) => setVisualContent( index, { owner: value } ) } ),
				...( Array.isArray( data.teams ) ? data.teams : [] ).map( ( team, teamIndex ) => {
					const item = typeof team === 'object' ? team : { label: team, color: teamColors[ teamIndex ] || data.color };
					return el( 'div', { className: 'spark-whyagency__editor-group', key: teamIndex },
						el( TextControl, { label: `Zespół ${ teamIndex + 1 }`, value: item.label || '', onChange: ( value ) => setList( 'teams', replaceItem( data.teams, teamIndex, { ...item, label: value } ) ) } ),
						el( SelectControl, { label: 'Kolor zespołu', value: item.color || 'agency', options: colors, onChange: ( value ) => setList( 'teams', replaceItem( data.teams, teamIndex, { ...item, color: value } ) ) } ),
						el( Button, { isDestructive: true, variant: 'tertiary', onClick: () => setList( 'teams', withoutItem( data.teams, teamIndex ) ) }, 'Usuń zespół' )
					);
				} ),
				el( Button, { variant: 'secondary', onClick: () => setList( 'teams', [ ...( data.teams || [] ), { label: 'Nowy zespół', color: 'agency' } ] ) }, 'Dodaj zespół' )
			);
			const property = type === 'delivery' ? 'steps' : 'tags';
			const label = type === 'delivery' ? 'Etap' : 'Tag';
			return el( Fragment, null,
				...( Array.isArray( data[ property ] ) ? data[ property ] : [] ).map( ( item, itemIndex ) => el( 'div', { className: 'spark-whyagency__editor-group', key: itemIndex },
					el( TextControl, { label: `${ label } ${ itemIndex + 1 }`, value: item || '', onChange: ( value ) => setList( property, replaceItem( data[ property ], itemIndex, value ) ) } ),
					el( Button, { isDestructive: true, variant: 'tertiary', onClick: () => setList( property, withoutItem( data[ property ], itemIndex ) ) }, `Usuń ${ label.toLowerCase() }` )
				) ),
				el( Button, { variant: 'secondary', onClick: () => setList( property, [ ...( data[ property ] || [] ), type === 'delivery' ? 'Nowy etap' : 'Nowy tag' ] ) }, `Dodaj ${ label.toLowerCase() }` )
			);
		};

		return el( Fragment, null,
			el( InspectorControls, null,
				el( PanelBody, { title: 'Ustawienia sekcji', initialOpen: true },
					el( TextControl, { label: 'Nadtytuł', value: attributes.eyebrow || '', onChange: ( value ) => setAttributes( { eyebrow: value } ) } ),
					el( TextControl, { label: 'Nagłówek', value: attributes.title || '', onChange: ( value ) => setAttributes( { title: value } ) } ),
					el( TextareaControl, { label: 'Opis', value: attributes.lede || '', onChange: ( value ) => setAttributes( { lede: value } ) } ),
					el( TextControl, { label: 'Tekst CTA', value: attributes.ctaLabel || '', onChange: ( value ) => setAttributes( { ctaLabel: value } ) } ),
					el( TextControl, { label: 'Adres CTA', type: 'url', value: attributes.ctaUrl || '', onChange: ( value ) => setAttributes( { ctaUrl: value } ) } ),
					el( SelectControl, { label: 'Motyw', value: theme, options: [ { label: 'Zgodny z ustawieniami użytkownika', value: 'inherit' }, { label: 'Ciemny', value: 'dark' }, { label: 'Jasny', value: 'light' } ], onChange: ( value ) => setAttributes( { theme: value } ) } )
				),
				...cards.map( ( card, index ) => {
					const data = visualContentFor( card );
					return el( PanelBody, { key: index, title: `${ card.number || String( index + 1 ).padStart( 2, '0' ) } · ${ card.title || 'Nowa karta' }`, initialOpen: false },
						el( TextControl, { label: 'Numer', value: card.number || '', onChange: ( value ) => updateCard( index, { number: value } ) } ),
						el( TextControl, { label: 'Tytuł', value: card.title || '', onChange: ( value ) => updateCard( index, { title: value } ) } ),
						el( TextareaControl, { label: 'Opis', value: card.description || '', onChange: ( value ) => updateCard( index, { description: value } ) } ),
						el( SelectControl, { label: 'Wizualizacja karty', value: card.visual || 'specialization', options: visuals, onChange: ( value ) => updateCard( index, { visual: value, visualContent: defaultVisualContent( value ) } ) } ),
						el( SelectControl, { label: 'Kolor wizualizacji', value: data.color || 'agency', options: colors, onChange: ( value ) => setVisualContent( index, { color: value } ) } ),
						visualControls( card, index ),
						el( 'div', { className: 'spark-whyagency__editor-actions' },
							el( Button, { icon: 'arrow-up-alt2', label: 'Przesuń wyżej', disabled: index === 0, onClick: () => moveCard( index, -1 ) } ),
							el( Button, { icon: 'arrow-down-alt2', label: 'Przesuń niżej', disabled: index === cards.length - 1, onClick: () => moveCard( index, 1 ) } ),
							el( Button, { isDestructive: true, variant: 'tertiary', onClick: () => setAttributes( { cards: cards.filter( ( item, position ) => position !== index ) } ) }, 'Usuń kartę' )
						)
					);
				} ),
				el( PanelBody, { title: 'Karty', initialOpen: cards.length === 0 }, el( Button, { variant: 'primary', onClick: () => setAttributes( { cards: [ ...cards, newCard( cards.length ) ] } ) }, 'Dodaj kartę' ) )
			),
			el( 'section', useBlockProps( { className } ),
				el( 'div', { className: 'spark-whyagency__inner' },
					el( 'header', { className: 'spark-whyagency__header' },
						editable( 'p', 'spark-whyagency__eyebrow', attributes.eyebrow, '04 · Dlaczego Spark Agency', ( value ) => setAttributes( { eyebrow: value } ) ),
						el( 'div', { className: 'spark-whyagency__intro' }, editable( 'h2', '', attributes.title, 'Nagłówek sekcji', ( value ) => setAttributes( { title: value } ) ), editable( 'p', '', attributes.lede, 'Opis sekcji', ( value ) => setAttributes( { lede: value } ) ) )
					),
					el( 'div', { className: 'spark-whyagency__grid' }, cards.map( ( card, index ) => el( 'article', { className: 'spark-whyagency__card', key: index }, visual( card.visual || 'specialization', card.visualContent ), el( 'div', { className: 'spark-whyagency__content' }, editable( 'span', 'spark-whyagency__number', card.number, '01', ( value ) => updateCard( index, { number: value } ) ), editable( 'h3', '', card.title, 'Tytuł karty', ( value ) => updateCard( index, { title: value } ) ), editable( 'p', '', card.description, 'Opis karty', ( value ) => updateCard( index, { description: value } ) ) ) ) ) ),
					el( 'p', { className: 'spark-whyagency__cta' }, el( 'span', null, editable( 'span', '', attributes.ctaLabel, 'Tekst CTA', ( value ) => setAttributes( { ctaLabel: value } ) ), ' →' ) ),
					el( Button, { variant: 'secondary', onClick: () => setAttributes( { cards: [ ...cards, newCard( cards.length ) ] } ) }, 'Dodaj kartę' )
				)
			)
		);
	}

	registerBlockType( 'sparkagency/whyagency', { edit: Edit, save: () => null } );
} )( window.wp );
