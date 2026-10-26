import { registerBlockType } from '@wordpress/blocks';
import { InspectorControls, RichText, useBlockProps } from '@wordpress/block-editor';
import { PanelBody, TextControl } from '@wordpress/components';
import { __ } from '@wordpress/i18n';

const domain = 'sparkagency';

const Edit = ( { attributes, setAttributes } ) => <>
	<InspectorControls><PanelBody title={ __( 'Teksty panelu aplikowania', domain ) }>
		<TextControl label={ __( 'Nadtytuł', domain ) } value={ attributes.eyebrow || '' } onChange={ ( eyebrow ) => setAttributes( { eyebrow } ) } />
		<TextControl label={ __( 'Opis', domain ) } value={ attributes.description || '' } onChange={ ( description ) => setAttributes( { description } ) } />
		<TextControl label={ __( 'Tekst przycisku', domain ) } value={ attributes.buttonLabel || '' } onChange={ ( buttonLabel ) => setAttributes( { buttonLabel } ) } />
	</PanelBody></InspectorControls>
	<aside { ...useBlockProps( { className: 'spark-career__apply spark-career__apply--editor' } ) }>
		<RichText tagName="span" value={ attributes.eyebrow || '' } placeholder={ __( 'Zainteresowała Cię ta oferta?', domain ) } allowedFormats={ [] } onChange={ ( eyebrow ) => setAttributes( { eyebrow } ) } />
		<RichText tagName="p" value={ attributes.description || '' } placeholder={ __( 'Informacja o aplikowaniu', domain ) } onChange={ ( description ) => setAttributes( { description } ) } />
		<span className="spark-career__button">{ attributes.buttonLabel || __( 'Aplikuj', domain ) } →</span>
	</aside>
</>;

registerBlockType( 'sparkagency/career-single-apply', { edit: Edit, save: () => null } );
