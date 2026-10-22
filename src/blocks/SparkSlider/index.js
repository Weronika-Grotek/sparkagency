( function ( wp ) {
	'use strict';

	const { registerBlockType } = wp.blocks;
	const { InspectorControls, useBlockProps } = wp.blockEditor;
	const { PanelBody, TextControl, Button } = wp.components;
	const { createElement: el, Fragment } = wp.element;

	registerBlockType( 'sparkagency/slider', {
		edit: ( { attributes, setAttributes } ) => {
			const words = Array.isArray( attributes.words ) ? attributes.words : [];
			const update = ( next ) => setAttributes( { words: next } );

			const fields = words.map( ( word, index ) => el( 'div', {
				key: index,
				style: { display: 'flex', gap: '4px', alignItems: 'flex-end', marginBottom: '8px' },
			},
				el( 'div', { style: { flex: 1 } }, el( TextControl, {
					label: 'Hasło ' + ( index + 1 ),
					value: word,
					__nextHasNoMarginBottom: true,
					onChange: ( value ) => update( words.map( ( w, i ) => ( i === index ? value : w ) ) ),
				} ) ),
				el( Button, {
					icon: 'arrow-up-alt2',
					label: 'W górę',
					disabled: 0 === index,
					onClick: () => {
						const next = [ ...words ];
						[ next[ index - 1 ], next[ index ] ] = [ next[ index ], next[ index - 1 ] ];
						update( next );
					},
				} ),
				el( Button, {
					icon: 'arrow-down-alt2',
					label: 'W dół',
					disabled: index === words.length - 1,
					onClick: () => {
						const next = [ ...words ];
						[ next[ index + 1 ], next[ index ] ] = [ next[ index ], next[ index + 1 ] ];
						update( next );
					},
				} ),
				el( Button, {
					icon: 'trash',
					label: 'Usuń',
					isDestructive: true,
					onClick: () => update( words.filter( ( w, i ) => i !== index ) ),
				} )
			) );

			return el( Fragment, null,
				el( InspectorControls, null,
					el( PanelBody, { title: 'Hasła slidera', initialOpen: true },
						fields,
						el( Button, { variant: 'secondary', onClick: () => update( [ ...words, '' ] ) }, 'Dodaj hasło' )
					)
				),
				el( 'div', useBlockProps( { className: 'dev-spark-marquee dev-spark-marquee--editor' } ),
					words.filter( Boolean ).length
						? words.filter( Boolean ).map( ( word, index ) => el( 'span', { key: index, className: 'dev-spark-marquee__word' }, word ) )
						: el( 'em', null, 'Dodaj hasła w panelu bocznym.' )
				)
			);
		},
		save: () => null,
	} );
} )( window.wp );