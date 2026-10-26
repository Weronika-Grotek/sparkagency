import { registerBlockType } from '@wordpress/blocks';
import { InspectorControls, RichText, useBlockProps } from '@wordpress/block-editor';
import { PanelBody, TextControl } from '@wordpress/components';
import { __ } from '@wordpress/i18n';

const domain = 'sparkagency';

const Edit = ( { attributes, setAttributes } ) => (
	<>
		<InspectorControls>
			<PanelBody title={ __( 'Teksty listy ofert', domain ) }>
				<TextControl label={ __( 'Tytuł pustej listy', domain ) } value={ attributes.emptyTitle || '' } onChange={ ( emptyTitle ) => setAttributes( { emptyTitle } ) } />
				<TextControl label={ __( 'Opis pustej listy', domain ) } value={ attributes.emptyDescription || '' } onChange={ ( emptyDescription ) => setAttributes( { emptyDescription } ) } />
				<TextControl label={ __( 'Tekst linku do oferty', domain ) } value={ attributes.detailsLabel || '' } onChange={ ( detailsLabel ) => setAttributes( { detailsLabel } ) } />
			</PanelBody>
		</InspectorControls>
		<section { ...useBlockProps( { className: 'spark-career__listing' } ) }>
			<div className="spark-career__wrap">
				<header className="spark-career__listing-heading">
					<RichText tagName="p" className="spark-career__listing-eyebrow" value={ attributes.eyebrow || '' } placeholder={ __( 'OFERTY PRACY', domain ) } allowedFormats={ [] } onChange={ ( eyebrow ) => setAttributes( { eyebrow } ) } />
					<div className="spark-career__listing-heading-copy">
						<RichText tagName="h2" className="spark-career__listing-title" value={ attributes.title || '' } placeholder={ __( 'Aktualne rekrutacje.', domain ) } allowedFormats={ [] } onChange={ ( title ) => setAttributes( { title } ) } />
						<RichText tagName="p" className="spark-career__listing-description" value={ attributes.description || '' } placeholder={ __( 'Opis ofert pracy', domain ) } onChange={ ( description ) => setAttributes( { description } ) } />
					</div>
				</header>
				<div className="spark-career__jobs"><article className="spark-career__job"><span className="spark-career__status">{ __( 'Lista aktualizuje się automatycznie', domain ) }</span><h3>{ __( 'Nazwa stanowiska', domain ) }</h3><p className="spark-career__summary">{ __( 'Karty ofert pojawią się tutaj po publikacji ogłoszeń.', domain ) }</p></article></div>
			</div>
		</section>
	</>
);

registerBlockType( 'sparkagency/career-listing', { edit: Edit, save: () => null } );
