import { registerBlockType } from '@wordpress/blocks';
import { InnerBlocks, RichText, useBlockProps, useInnerBlocksProps } from '@wordpress/block-editor';
import { __ } from '@wordpress/i18n';

const ALLOWED_BLOCKS = [
	'core/paragraph',
	'core/heading',
	'core/list',
	'core/image',
	'core/gallery',
	'core/video',
	'core/quote',
	'core/group',
	'core/columns',
	'core/buttons',
];

const Edit = ( { attributes, setAttributes } ) => {
	const blockProps = useBlockProps( { className: 'spark-case-study-description-item' } );
	const innerBlocksProps = useInnerBlocksProps( { className: 'spark-case-study-description-item__body' }, {
		allowedBlocks: ALLOWED_BLOCKS,
		template: [ [ 'core/paragraph', { placeholder: __( 'Dodaj opis projektu…', 'sparkagency' ) } ] ],
		templateLock: false,
		renderAppender: InnerBlocks.ButtonBlockAppender,
	} );

	return (
		<section { ...blockProps }>
			<RichText
				tagName="h2"
				className="spark-case-study-description-item__title"
				value={ attributes.title || '' }
				placeholder={ __( 'Tytuł sekcji', 'sparkagency' ) }
				onChange={ ( title ) => setAttributes( { title } ) }
			/>
			<div { ...innerBlocksProps } />
		</section>
	);
};

registerBlockType( 'sparkagency/case-study-description-item', {
	edit: Edit,
	save: () => <InnerBlocks.Content />,
} );
