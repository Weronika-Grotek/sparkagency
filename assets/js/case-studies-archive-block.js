( function ( blocks, element, blockEditor, i18n ) {
	const el = element.createElement;
	const { __ } = i18n;
	const useBlockProps = blockEditor.useBlockProps;

	blocks.registerBlockType( 'sparkagency/case-studies-archive', {
		 edit: function () {
			return el(
				'div',
				useBlockProps( { className: 'spark-case-archive__editor' } ),
				el( 'strong', {}, __( 'Archiwum Case Studies', 'sparkagency' ) ),
				el( 'p', {}, __( 'Lista i filtry firm wyświetlą się automatycznie na stronie. Wstaw własny blok Hero nad tym blokiem.', 'sparkagency' ) )
			);
		},
		save: function () {
			return null;
		}
	} );
} )( window.wp.blocks, window.wp.element, window.wp.blockEditor, window.wp.i18n );
