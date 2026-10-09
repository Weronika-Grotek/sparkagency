/* global wp */
( function ( blocks, blockEditor, components, element, i18n ) {
	'use strict';

	const el = element.createElement;
	const { Fragment } = element;
	const { InspectorControls, RichText, useBlockProps } = blockEditor;
	const { PanelBody, TextControl, TextareaControl, SelectControl, RangeControl, Button, ColorPalette } = components;
	const { __ } = i18n;
	const domain = 'spark-oneteam';
	const blankCard = () => ( { title: '', lead: '', description: '', brand: 'agency', color: '' } );
	const brands = [
		{ label: 'Agency', value: 'agency' },
		{ label: 'Warsaw', value: 'warsaw' },
		{ label: 'Health', value: 'health' },
		{ label: 'Works', value: 'works' },
		{ label: 'EXP', value: 'exp' },
		{ label: 'Studio', value: 'studio' },
		{ label: __( 'Własny kolor', domain ), value: 'custom' },
	];

	function Edit( { attributes, setAttributes } ) {
		const a = attributes;
		const cards = Array.isArray( a.cards ) ? a.cards : [];
		const updateCard = ( index, changes ) => setAttributes( {
			cards: cards.map( ( card, position ) => position === index ? { ...card, ...changes } : card ),
		} );
		const moveCard = ( index, offset ) => {
			const nextIndex = index + offset;
			if ( nextIndex < 0 || nextIndex >= cards.length ) {
				return;
			}
			const next = cards.slice();
			[ next[ index ], next[ nextIndex ] ] = [ next[ nextIndex ], next[ index ] ];
			setAttributes( { cards: next } );
		};
		const field = ( key, label, multiline = false ) => el( multiline ? TextareaControl : TextControl, {
			label, value: a[ key ] || '', onChange: ( value ) => setAttributes( { [ key ]: value } ),
		} );
		const addCard = () => setAttributes( { cards: [ ...cards, blankCard() ] } );
		const wrapper = useBlockProps( {
			className: 'spark-oneteam' + ( a.theme === 'inherit' ? '' : ' is-theme-' + a.theme ),
			style: { '--teams-columns': a.columns },
		} );

		return el( Fragment, null,
			el( InspectorControls, null,
				el( PanelBody, { title: __( 'Treść sekcji', domain ), initialOpen: true },
					field( 'eyebrow', __( 'Etykieta (np. 03 · Jeden zespół lub kilka)', domain ) ),
					field( 'title', __( 'Nagłówek', domain ) ),
					field( 'lede', __( 'Opis pod nagłówkiem', domain ), true ),
					field( 'ctaLabel', __( 'Tekst przycisku', domain ) ),
					field( 'ctaUrl', __( 'Adres przycisku (URL lub #kontakt)', domain ) ),
				),
				el( PanelBody, { title: __( 'Wygląd', domain ), initialOpen: false },
					el( SelectControl, {
						label: __( 'Motyw', domain ), value: a.theme,
						options: [
							{ label: __( 'Ciemny', domain ), value: 'dark' },
							{ label: __( 'Jasny', domain ), value: 'light' },
							{ label: __( 'Dziedzicz zmienne motywu', domain ), value: 'inherit' },
						],
						onChange: ( theme ) => setAttributes( { theme } ),
					} ),
					el( RangeControl, { label: __( 'Kolumny na komputerze', domain ), value: a.columns, min: 1, max: 3, onChange: ( columns ) => setAttributes( { columns } ) } ),
				),
				...cards.map( ( card, index ) => el( PanelBody, {
					key: index, title: ( index + 1 ) + '. ' + ( card.title || __( 'Nowa karta', domain ) ), initialOpen: false,
				},
					el( TextControl, { label: __( 'Tytuł karty', domain ), value: card.title || '', onChange: ( title ) => updateCard( index, { title } ) } ),
					el( TextControl, { label: __( 'Opis', domain ), value: card.lead || '', onChange: ( lead ) => updateCard( index, { lead } ) } ),
					el( TextareaControl, { label: __( 'Opis karty', domain ), value: card.description || '', onChange: ( description ) => updateCard( index, { description } ) } ),
					el( SelectControl, { label: __( 'Kolor znacznika', domain ), value: card.brand || 'agency', options: brands, onChange: ( brand ) => updateCard( index, { brand } ) } ),
					card.brand === 'custom' && el( ColorPalette, {
						value: card.color || '', enableAlpha: false, clearable: true,
						onChange: ( color ) => updateCard( index, { color: color || '' } ),
					} ),
					el( 'div', { className: 'spark-oneteam-editor-actions' },
						el( Button, { variant: 'secondary', disabled: index === 0, onClick: () => moveCard( index, -1 ) }, __( 'W górę', domain ) ),
						el( Button, { variant: 'secondary', disabled: index === cards.length - 1, onClick: () => moveCard( index, 1 ) }, __( 'W dół', domain ) ),
						el( Button, { variant: 'secondary', onClick: () => setAttributes( { cards: [ ...cards.slice( 0, index + 1 ), { ...card }, ...cards.slice( index + 1 ) ] } ) }, __( 'Duplikuj', domain ) ),
						el( Button, { isDestructive: true, variant: 'tertiary', onClick: () => setAttributes( { cards: cards.filter( ( item, position ) => position !== index ) } ) }, __( 'Usuń kartę', domain ) ),
					),
				) ),
				el( PanelBody, { title: __( 'Dodawanie kart', domain ), initialOpen: true },
					el( Button, { variant: 'primary', onClick: addCard }, __( 'Dodaj kartę', domain ) ),
				),
			),
			el( 'section', wrapper,
				el( 'div', { className: 'spark-oneteam__inner' },
					el( 'div', { className: 'spark-oneteam__header' },
						el( RichText, {
							tagName: 'span', className: 'spark-oneteam__label', value: a.eyebrow || '',
							placeholder: __( 'Etykieta sekcji', domain ), allowedFormats: [],
							onChange: ( eyebrow ) => setAttributes( { eyebrow } ),
						} ),
						el( 'div', null,
							el( RichText, {
								tagName: 'h2', value: a.title || '',
								placeholder: __( 'Nagłówek', domain ), allowedFormats: [],
								onChange: ( title ) => setAttributes( { title } ),
							} ),
							el( RichText, {
								tagName: 'p', className: 'spark-oneteam__lede', value: a.lede || '',
								placeholder: __( 'Opis sekcji.', domain ), allowedFormats: [],
								onChange: ( lede ) => setAttributes( { lede } ),
							} ),
						),
					),
					el( 'div', { className: 'spark-oneteam__cards' },
						...cards.map( ( card, index ) => el( 'article', {
							key: index, className: 'spark-oneteam__card', 'data-team-brand': card.brand || 'agency',
							style: card.brand === 'custom' && /^#[0-9a-f]{3}([0-9a-f]{3})?$/i.test( card.color ) ? { '--team-accent': card.color } : undefined,
						},
							el( RichText, {
								tagName: 'h3', value: card.title || '',
								placeholder: __( 'Tytuł karty', domain ), allowedFormats: [],
								onChange: ( title ) => updateCard( index, { title } ),
							} ),
							el( RichText, {
								tagName: 'span', className: 'spark-oneteam__lead', value: card.lead || '',
								placeholder: __( 'Opis', domain ), allowedFormats: [],
								onChange: ( lead ) => updateCard( index, { lead } ),
							} ),
							el( RichText, {
								tagName: 'p', value: card.description || '',
								placeholder: __( 'Opis karty', domain ), allowedFormats: [],
								onChange: ( description ) => updateCard( index, { description } ),
							} ),
						) ),
					),
					el( Button, { variant: 'secondary', className: 'spark-oneteam-editor-add', onClick: addCard }, __( 'Dodaj kartę', domain ) ),
					el( RichText, {
						tagName: 'p', className: 'spark-oneteam__note', value: a.note,
						placeholder: __( 'Notatka', domain ),
						allowedFormats: [ 'core/bold', 'core/italic' ],
						onChange: ( note ) => setAttributes( { note } ),
					} ),
					el( 'p', { className: 'spark-oneteam__cta' },
						el( 'span', { className: 'spark-oneteam__link' },
							el( RichText, {
								tagName: 'span', className: 'spark-oneteam__link-label', value: a.ctaLabel || '',
								placeholder: __( 'Tekst przycisku', domain ), allowedFormats: [],
								onChange: ( ctaLabel ) => setAttributes( { ctaLabel } ),
							} ),
							el( 'span', { className: 'spark-oneteam__link-arrow', 'aria-hidden': true }, '→' ),
						),
					),
				),
			),
		);
	}

	// Attributes and supports are supplied by the server from block.json.
	blocks.registerBlockType( 'sparkagency/oneteam', { edit: Edit, save: () => null } );
} )( wp.blocks, wp.blockEditor, wp.components, wp.element, wp.i18n );
