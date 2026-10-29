import { registerBlockType } from '@wordpress/blocks';
import { InnerBlocks, useBlockProps, useInnerBlocksProps } from '@wordpress/block-editor';
import { useDispatch, useSelect } from '@wordpress/data';
import { useEffect, useMemo } from '@wordpress/element';
import { __ } from '@wordpress/i18n';

const DOMAIN = 'sparkagency';
const ALLOWED_BLOCKS = [ 'core/heading', 'core/paragraph', 'core/list' ];

const slugify = ( value ) => value
	.toLocaleLowerCase()
	.replace( /[łŁ]/g, 'l' )
	.normalize( 'NFD' )
	.replace( /[\u0300-\u036f]/g, '' )
	.replace( /[^a-z0-9]+/g, '-' )
	.replace( /^-+|-+$/g, '' ) || 'sekcja';

const getHeadings = ( blocks, results = [] ) => {
	blocks.forEach( ( block ) => {
		if ( block.name === 'core/heading' ) {
			const title = ( block.attributes.content || '' ).replace( /<[^>]*>/g, '' ).trim();
			if ( title ) results.push( { clientId: block.clientId, title } );
		}
		if ( block.innerBlocks?.length ) getHeadings( block.innerBlocks, results );
	} );
	return results;
};

const Edit = ( { clientId } ) => {
	const blocks = useSelect( ( select ) => select( 'core/block-editor' ).getBlocks( clientId ), [ clientId ] );
	const headings = useMemo( () => {
		const used = new Map();
		return getHeadings( blocks ).map( ( heading ) => {
			const base = slugify( heading.title );
			const count = ( used.get( base ) || 0 ) + 1;
			used.set( base, count );
			return { ...heading, anchor: count === 1 ? base : `${ base }-${ count }` };
		} );
	}, [ blocks ] );
	const { updateBlockAttributes } = useDispatch( 'core/block-editor' );

	useEffect( () => {
		headings.forEach( ( heading ) => {
			const block = blocks.find( ( item ) => item.clientId === heading.clientId );
			if ( block && block.attributes.anchor !== heading.anchor ) {
				updateBlockAttributes( heading.clientId, { anchor: heading.anchor } );
			}
		} );
	}, [ headings, blocks, updateBlockAttributes ] );

	const blockProps = useBlockProps( { className: 'spark-privacy-policy' } );
	const innerBlocksProps = useInnerBlocksProps( { className: 'spark-privacy-policy__content' }, {
		allowedBlocks: ALLOWED_BLOCKS,
		templateLock: false,
		renderAppender: InnerBlocks.ButtonBlockAppender,
	} );

	return (
		<section { ...blockProps }>
			<aside className="spark-privacy-policy__sidebar" aria-label={ __( 'Spis treści', DOMAIN ) }>
				<p className="spark-privacy-policy__label">{ __( 'Spis treści', DOMAIN ) }</p>
				<nav>
					<ol className="spark-privacy-policy__toc">
						{ headings.map( ( heading, index ) => (
							<li key={ heading.clientId }>
								<a href={ `#${ heading.anchor }` }>
									<span>{ String( index + 1 ).padStart( 2, '0' ) }.</span>
									{ heading.title }
								</a>
							</li>
						) ) }
					</ol>
				</nav>
			</aside>
			<div { ...innerBlocksProps } />
		</section>
	);
};

registerBlockType( 'sparkagency/privacy-policy', {
	edit: Edit,
	save: () => <InnerBlocks.Content />,
} );
