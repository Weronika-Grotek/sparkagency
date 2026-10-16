import { registerBlockType } from '@wordpress/blocks';
import { InspectorControls, RichText, useBlockProps } from '@wordpress/block-editor';
import { PanelBody, RangeControl, TextControl } from '@wordpress/components';
import { __ } from '@wordpress/i18n';

const Edit = ( { attributes, setAttributes } ) => {
	const blockProps = useBlockProps( { className: 'spark-counter' } );

	return <>
		<InspectorControls>
			<PanelBody title={ __( 'Ustawienia licznika', 'sparkagency' ) } initialOpen={ true }>
				<TextControl label={ __( 'Wartość', 'sparkagency' ) } type="number" value={ String( attributes.value ?? 0 ) } onChange={ ( value ) => setAttributes( { value: Number( value ) || 0 } ) } />
				<TextControl label={ __( 'Tekst przed liczbą', 'sparkagency' ) } value={ attributes.prefix || '' } onChange={ ( prefix ) => setAttributes( { prefix } ) } />
				<TextControl label={ __( 'Tekst po liczbie', 'sparkagency' ) } value={ attributes.suffix || '' } onChange={ ( suffix ) => setAttributes( { suffix } ) } />
				<RangeControl label={ __( 'Czas animacji (ms)', 'sparkagency' ) } value={ attributes.duration || 1200 } onChange={ ( duration ) => setAttributes( { duration } ) } min={ 300 } max={ 5000 } step={ 100 } />
			</PanelBody>
		</InspectorControls>
		<section { ...blockProps }>
			<p className="spark-counter__value"><span>{ attributes.prefix || '' }</span>{ attributes.value ?? 0 }<span>{ attributes.suffix || '' }</span></p>
			<RichText tagName="p" className="spark-counter__label" value={ attributes.label || '' } placeholder={ __( 'Opis licznika', 'sparkagency' ) } onChange={ ( label ) => setAttributes( { label } ) } />
		</section>
	</>;
};

registerBlockType( 'sparkagency/counter', { edit: Edit, save: () => null } );
