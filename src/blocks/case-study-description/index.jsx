import { registerBlockType } from '@wordpress/blocks';
import { InnerBlocks, useBlockProps, useInnerBlocksProps } from '@wordpress/block-editor';

const ALLOWED_BLOCKS = [ 'sparkagency/case-study-description-item' ];

const Edit = () => {
	const blockProps = useBlockProps( { className: 'spark-case-study-description' } );
	const innerBlocksProps = useInnerBlocksProps( blockProps, {
		allowedBlocks: ALLOWED_BLOCKS,
		templateLock: false,
		renderAppender: InnerBlocks.ButtonBlockAppender,
	} );

	return <section { ...innerBlocksProps } />;
};

registerBlockType( 'sparkagency/case-study-description', { edit: Edit, save: () => <InnerBlocks.Content /> } );
