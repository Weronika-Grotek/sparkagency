/* global wp */
( function ( blocks, blockEditor, components, element, i18n ) {
	'use strict';
	const el = element.createElement, Fragment = element.Fragment;
	const { InspectorControls, RichText, useBlockProps } = blockEditor;
	const { Button, ColorPalette, PanelBody, SelectControl, TextControl, TextareaControl } = components;
	const { __ } = i18n, domain = 'spark-howstart';
	const newStep = ( index ) => ( { number: String( index + 1 ).padStart( 2, '0' ), title: '', description: '' } );
	function Edit( { attributes, setAttributes } ) {
		const steps = Array.isArray( attributes.steps ) ? attributes.steps : [];
		const theme = [ 'dark', 'light', 'inherit' ].includes( attributes.theme ) ? attributes.theme : 'dark';
		const updateStep = ( index, changes ) => setAttributes( { steps: steps.map( ( step, position ) => position === index ? { ...step, ...changes } : step ) } );
		const moveStep = ( index, direction ) => { const target = index + direction; if ( target < 0 || target >= steps.length ) return; const next = [ ...steps ]; [ next[ index ], next[ target ] ] = [ next[ target ], next[ index ] ]; setAttributes( { steps: next } ); };
		const addStep = () => setAttributes( { steps: [ ...steps, newStep( steps.length ) ] } );
		const props = useBlockProps( { className: 'spark-howstart spark-howstart--editor' + ( theme === 'inherit' ? '' : ' is-theme-' + theme ), style: attributes.accentColor ? { '--howstart-accent': attributes.accentColor } : undefined } );
		return el( Fragment, null,
			el( InspectorControls, null,
				el( PanelBody, { title: __( 'Ustawienia sekcji', domain ), initialOpen: true },
					el( TextControl, { label: __( 'Etykieta', domain ), value: attributes.eyebrow || '', onChange: eyebrow => setAttributes( { eyebrow } ) } ),
					el( TextControl, { label: __( 'Tekst linku', domain ), value: attributes.ctaLabel || '', onChange: ctaLabel => setAttributes( { ctaLabel } ) } ),
					el( TextControl, { label: __( 'Adres linku (URL lub #kontakt)', domain ), value: attributes.ctaUrl || '', onChange: ctaUrl => setAttributes( { ctaUrl } ) } ),
					el( SelectControl, { label: __( 'Motyw', domain ), value: theme, options: [ { label: __( 'Ciemny', domain ), value: 'dark' }, { label: __( 'Jasny', domain ), value: 'light' }, { label: __( 'Dziedzicz ustawienia strony', domain ), value: 'inherit' } ], onChange: value => setAttributes( { theme: value } ) } ),
					el( 'p', null, __( 'Kolor akcentu', domain ) ), el( ColorPalette, { value: attributes.accentColor || undefined, clearable: true, disableCustomColors: false, onChange: accentColor => setAttributes( { accentColor: accentColor || '' } ) } )
				),
				...steps.map( ( step, index ) => el( PanelBody, { key: index, title: ( index + 1 ) + '. ' + ( step.title || __( 'Nowy krok', domain ) ), initialOpen: false },
					el( TextControl, { label: __( 'Numer kroku', domain ), value: step.number || '', onChange: number => updateStep( index, { number } ) } ), el( TextControl, { label: __( 'Tytuł kroku', domain ), value: step.title || '', onChange: title => updateStep( index, { title } ) } ), el( TextareaControl, { label: __( 'Opis kroku', domain ), value: step.description || '', onChange: description => updateStep( index, { description } ) } ),
					el( 'div', { className: 'spark-howstart__editor-actions' }, el( Button, { variant: 'secondary', disabled: index === 0, onClick: () => moveStep( index, -1 ) }, __( 'W górę', domain ) ), el( Button, { variant: 'secondary', disabled: index === steps.length - 1, onClick: () => moveStep( index, 1 ) }, __( 'W dół', domain ) ), el( Button, { variant: 'secondary', onClick: () => setAttributes( { steps: [ ...steps.slice( 0, index + 1 ), { ...step }, ...steps.slice( index + 1 ) ] } ) }, __( 'Duplikuj', domain ) ), el( Button, { variant: 'tertiary', isDestructive: true, onClick: () => setAttributes( { steps: steps.filter( ( item, position ) => position !== index ) } ) }, __( 'Usuń', domain ) ) )
				) ), el( PanelBody, { title: __( 'Kroki procesu', domain ), initialOpen: true }, el( Button, { variant: 'primary', onClick: addStep }, __( 'Dodaj krok', domain ) ) )
			),
			el( 'section', props, el( 'div', { className: 'spark-howstart__inner' }, el( 'div', { className: 'spark-howstart__layout' },
				el( 'div', { className: 'spark-howstart__intro' }, el( RichText, { tagName: 'span', className: 'spark-howstart__eyebrow', value: attributes.eyebrow || '', placeholder: __( '06 · Jak zacząć', domain ), allowedFormats: [], onChange: eyebrow => setAttributes( { eyebrow } ) } ), el( RichText, { tagName: 'h2', value: attributes.title || '', placeholder: __( 'Nagłówek sekcji', domain ), allowedFormats: [], onChange: title => setAttributes( { title } ) } ), el( RichText, { tagName: 'p', className: 'spark-howstart__lede', value: attributes.lede || '', placeholder: __( 'Opis sekcji', domain ), allowedFormats: [], onChange: lede => setAttributes( { lede } ) } ), el( 'p', { className: 'spark-howstart__cta' }, el( RichText, { tagName: 'span', value: attributes.ctaLabel || '', placeholder: __( 'Tekst linku', domain ), allowedFormats: [], onChange: ctaLabel => setAttributes( { ctaLabel } ) } ), ' ', el( 'span', { 'aria-hidden': true }, '→' ) ) ),
				el( 'ol', { className: 'spark-howstart__steps' }, ...steps.map( ( step, index ) => el( 'li', { className: 'spark-howstart__step is-active', key: index }, el( RichText, { tagName: 'b', value: step.number || '', placeholder: String( index + 1 ).padStart( 2, '0' ), allowedFormats: [], onChange: number => updateStep( index, { number } ) } ), el( 'div', null, el( RichText, { tagName: 'h3', value: step.title || '', placeholder: __( 'Tytuł kroku', domain ), allowedFormats: [], onChange: title => updateStep( index, { title } ) } ), el( RichText, { tagName: 'p', value: step.description || '', placeholder: __( 'Opis kroku', domain ), allowedFormats: [], onChange: description => updateStep( index, { description } ) } ) ) ) ), el( 'li', { className: 'spark-howstart__add-step' }, el( Button, { variant: 'secondary', onClick: addStep }, __( 'Dodaj krok', domain ) ) ) )
			) ) )
		);
	}
	blocks.registerBlockType( 'sparkagency/howstart', { edit: Edit, save: () => null } );
} )( wp.blocks, wp.blockEditor, wp.components, wp.element, wp.i18n );
