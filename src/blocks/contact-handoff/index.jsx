import { registerBlockType } from '@wordpress/blocks';
import { InnerBlocks, InspectorControls, RichText, useBlockProps, useInnerBlocksProps } from '@wordpress/block-editor';
import { PanelBody, TextControl } from '@wordpress/components';
import { __ } from '@wordpress/i18n';

const domain = 'sparkagency';

const Edit = ( { attributes, setAttributes } ) => {
	const blockProps = useBlockProps( { className: 'spark-contact-handoff' } );
	const innerBlocksProps = useInnerBlocksProps( { className: 'spark-contact-handoff__form' } );

	return (
		<>
			<InspectorControls>
				<PanelBody title={ __( 'Teksty podsumowania', domain ) } initialOpen>
					<TextControl label={ __( 'Nagłówek', domain ) } value={ attributes.title || '' } onChange={ ( title ) => setAttributes( { title } ) } />
					<TextControl label={ __( 'Opis', domain ) } value={ attributes.lede || '' } onChange={ ( lede ) => setAttributes( { lede } ) } />
					<TextControl label={ __( 'Tekst bez danych z generatora', domain ) } value={ attributes.emptyText || '' } onChange={ ( emptyText ) => setAttributes( { emptyText } ) } />
				</PanelBody>
			</InspectorControls>
			<section { ...blockProps }>
				<div className="spark-contact-handoff__summary">
					<RichText tagName="h2" value={ attributes.title || '' } placeholder={ __( 'Nagłówek podsumowania', domain ) } onChange={ ( title ) => setAttributes( { title } ) } />
					<dl><div><dt>{ __( 'Wybrana potrzeba', domain ) }</dt><dd>{ __( 'Odczytana z generatora potrzeb', domain ) }</dd></div></dl>
					<RichText tagName="p" value={ attributes.lede || '' } placeholder={ __( 'Opis podsumowania', domain ) } onChange={ ( lede ) => setAttributes( { lede } ) } />
				</div>
				<div { ...innerBlocksProps } />
			</section>
		</>
	);
};

registerBlockType( 'sparkagency/contact-handoff', {
	edit: Edit,
	save: () => <InnerBlocks.Content />,
} );
