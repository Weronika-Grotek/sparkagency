import { registerBlockType } from '@wordpress/blocks';
import { InspectorControls, RichText, useBlockProps } from '@wordpress/block-editor';
import { Button, PanelBody, TextControl } from '@wordpress/components';
import { __ } from '@wordpress/i18n';

const domain = 'sparkagency';

const CheckIcon = () => <svg className="spark-requirement__icon" viewBox="0 0 24 24" aria-hidden="true"><path d="m5 12.5 4.5 4.5L19 7" /></svg>;

const Edit = ( { attributes, setAttributes } ) => {
	const items = Array.isArray( attributes.items ) ? attributes.items : [];
	const updateItem = ( index, value ) => {
		const nextItems = [ ...items ];
		nextItems[ index ] = value;
		setAttributes( { items: nextItems } );
	};

	return <>
		<InspectorControls>
			<PanelBody title={ __( 'Nagłówki sekcji', domain ) }>
				<TextControl label={ __( 'Etykieta', domain ) } value={ attributes.eyebrow || '' } onChange={ ( eyebrow ) => setAttributes( { eyebrow } ) } />
				<TextControl label={ __( 'Tytuł', domain ) } value={ attributes.title || '' } onChange={ ( title ) => setAttributes( { title } ) } />
			</PanelBody>
		</InspectorControls>
		<section { ...useBlockProps( { className: 'spark-requirement' } ) }>
			<RichText tagName="span" className="spark-requirement__eyebrow" value={ attributes.eyebrow || '' } placeholder={ __( 'Czym się zajmiesz', domain ) } allowedFormats={ [] } onChange={ ( eyebrow ) => setAttributes( { eyebrow } ) } />
			<div className="spark-requirement__content">
				<RichText tagName="h2" className="spark-requirement__title" value={ attributes.title || '' } placeholder={ __( 'Zakres pracy.', domain ) } onChange={ ( title ) => setAttributes( { title } ) } />
				<ul className="spark-requirement__list">
					{ items.map( ( item, index ) => <li className="spark-requirement__item" key={ index }>
						<CheckIcon />
						<RichText tagName="span" value={ item } placeholder={ __( 'Wpisz punkt listy…', domain ) } onChange={ ( value ) => updateItem( index, value ) } />
						<Button className="spark-requirement__remove" icon="no-alt" label={ __( 'Usuń punkt', domain ) } onClick={ () => setAttributes( { items: items.filter( ( _, itemIndex ) => itemIndex !== index ) } ) } />
					</li> ) }
				</ul>
				<Button variant="secondary" className="spark-requirement__add" onClick={ () => setAttributes( { items: [ ...items, '' ] } ) }>
					{ __( 'Dodaj punkt', domain ) }
				</Button>
			</div>
		</section>
	</>;
};

registerBlockType( 'sparkagency/requirement', { edit: Edit, save: () => null } );
