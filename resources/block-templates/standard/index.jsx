import { registerBlockType } from '@wordpress/blocks';
import { RichText, useBlockProps } from '@wordpress/block-editor';
import { __ } from '@wordpress/i18n';

const Edit = ( { attributes, setAttributes } ) => (
	<section { ...useBlockProps( { className: 'spark-{{SLUG}}' } ) }>
		<RichText
			tagName="h2"
			value={ attributes.title || '' }
			placeholder={ __( '{{TITLE}}', 'sparkagency' ) }
			onChange={ ( title ) => setAttributes( { title } ) }
		/>
	</section>
);

	registerBlockType( 'sparkagency/{{SLUG}}', {
	edit: Edit,
		save: () => null,
	} );
