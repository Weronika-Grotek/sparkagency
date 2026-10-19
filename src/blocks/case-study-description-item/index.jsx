import { registerBlockType } from '@wordpress/blocks';
import { InnerBlocks, InspectorControls, RichText, useBlockProps, useInnerBlocksProps } from '@wordpress/block-editor';
import { PanelBody, RangeControl, TextControl, ToggleControl } from '@wordpress/components';
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
	const counterValue = Number.parseInt( attributes.counterValue, 10 ) || 0;
	const innerBlocksProps = useInnerBlocksProps( { className: 'spark-case-study-description-item__body' }, {
		allowedBlocks: ALLOWED_BLOCKS,
		templateLock: false,
		renderAppender: InnerBlocks.ButtonBlockAppender,
	} );

	return (
		<section { ...blockProps }>
			<InspectorControls>
				<PanelBody title={ __( 'Licznik', 'sparkagency' ) } initialOpen={ true }>
					<ToggleControl
						label={ __( 'Pokaż licznik w tym wierszu', 'sparkagency' ) }
						checked={ Boolean( attributes.showCounter ) }
						onChange={ ( showCounter ) => setAttributes( { showCounter } ) }
					/>
					{ attributes.showCounter && <>
						<TextControl
							label={ __( 'Wartość', 'sparkagency' ) }
							type="number"
							step="1"
							value={ String( counterValue ) }
							onChange={ ( value ) => setAttributes( { counterValue: Number.parseInt( value, 10 ) || 0 } ) }
						/>
						<TextControl label={ __( 'Tekst przed liczbą', 'sparkagency' ) } value={ attributes.counterPrefix || '' } onChange={ ( counterPrefix ) => setAttributes( { counterPrefix } ) } />
						<TextControl label={ __( 'Tekst po liczbie', 'sparkagency' ) } value={ attributes.counterSuffix || '' } onChange={ ( counterSuffix ) => setAttributes( { counterSuffix } ) } />
						<RangeControl label={ __( 'Czas animacji (ms)', 'sparkagency' ) } value={ attributes.counterDuration || 1200 } onChange={ ( counterDuration ) => setAttributes( { counterDuration } ) } min={ 300 } max={ 5000 } step={ 100 } />
					</> }
				</PanelBody>
			</InspectorControls>
			<RichText
				tagName="h2"
				className="spark-case-study-description-item__title"
				value={ attributes.title || '' }
				placeholder={ __( 'Tytuł sekcji', 'sparkagency' ) }
				onChange={ ( title ) => setAttributes( { title } ) }
			/>
			<div { ...innerBlocksProps } />
			{ attributes.showCounter && <div className="spark-case-study-description-item__counter">
				<p className="spark-case-study-description-item__counter-value"><span>{ attributes.counterPrefix || '' }</span>{ counterValue }<span>{ attributes.counterSuffix || '' }</span></p>
				<RichText tagName="p" className="spark-case-study-description-item__counter-label" value={ attributes.counterLabel || '' } placeholder={ __( 'Opis licznika', 'sparkagency' ) } onChange={ ( counterLabel ) => setAttributes( { counterLabel } ) } />
			</div> }
		</section>
	);
};

registerBlockType( 'sparkagency/case-study-description-item', { edit: Edit, save: () => <InnerBlocks.Content /> } );
