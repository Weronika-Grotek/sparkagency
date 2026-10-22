import { registerBlockType } from '@wordpress/blocks';
import { InspectorControls, RichText, useBlockProps } from '@wordpress/block-editor';
import { Button, ColorPalette, PanelBody, SelectControl, TextControl } from '@wordpress/components';
import { Fragment } from '@wordpress/element';
import { __ } from '@wordpress/i18n';

const domain = 'sparkagency';
const brandOptions = [
	{ label: 'sparkWORKS', value: 'works' },
	{ label: 'sparkEXP', value: 'exp' },
	{ label: 'sparkWARSAW', value: 'warsaw' },
	{ label: 'sparkHEALTH', value: 'health' },
	{ label: 'sparkSTUDIO', value: 'studio' },
	{ label: __( 'Własny kolor', domain ), value: 'custom' },
];
const blankCompany = () => ( { prefix: 'spark', name: '', description: '', url: '', brand: 'custom', color: '' } );
const isColor = ( color ) => /^#[0-9a-f]{3}(?:[0-9a-f]{3})?$/i.test( color || '' );

function BrandRow( { company, editing, onChange } ) {
	const style = company.brand === 'custom' && isColor( company.color ) ? { '--aboutfirm-brand': company.color } : undefined;
	return <div className={ `spark-aboutfirm__row is-brand-${ company.brand || 'custom' }` } style={ style }>
		<h3>
			{ editing ? <RichText tagName="span" className="spark-aboutfirm__prefix" value={ company.prefix || '' } placeholder="spark" allowedFormats={ [] } onChange={ ( prefix ) => onChange( { prefix } ) } /> : <span className="spark-aboutfirm__prefix">{ company.prefix }</span> }
			{ editing ? <RichText tagName="span" className="spark-aboutfirm__name" value={ company.name || '' } placeholder={ __( 'NAZWA', domain ) } allowedFormats={ [] } onChange={ ( name ) => onChange( { name } ) } /> : <span className="spark-aboutfirm__name">{ company.name }</span> }
		</h3>
		{ editing ? <RichText tagName="span" className="spark-aboutfirm__description" value={ company.description || '' } placeholder={ __( 'Krótki opis', domain ) } allowedFormats={ [] } onChange={ ( description ) => onChange( { description } ) } /> : <span className="spark-aboutfirm__description">{ company.description }</span> }
	</div>;
}

function Edit( { attributes, setAttributes } ) {
	const companies = Array.isArray( attributes.companies ) ? attributes.companies : [];
	const update = ( index, changes ) => setAttributes( { companies: companies.map( ( company, companyIndex ) => companyIndex === index ? { ...company, ...changes } : company ) } );
	const add = () => setAttributes( { companies: [ ...companies, blankCompany() ] } );
	const move = ( index, direction ) => {
		const target = index + direction;
		if ( target < 0 || target >= companies.length ) return;
		const next = [ ...companies ];
		[ next[ index ], next[ target ] ] = [ next[ target ], next[ index ] ];
		setAttributes( { companies: next } );
	};

	return <Fragment><InspectorControls>
		<PanelBody title={ __( 'Wygląd', domain ) } initialOpen={ true }><SelectControl label={ __( 'Motyw sekcji', domain ) } value={ attributes.theme || 'dark' } options={ [ { label: __( 'Ciemny', domain ), value: 'dark' }, { label: __( 'Jasny', domain ), value: 'light' }, { label: __( 'Dziedzicz z witryny', domain ), value: 'inherit' } ] } onChange={ ( theme ) => setAttributes( { theme } ) } /></PanelBody>
		{ companies.map( ( company, index ) => <PanelBody key={ index } title={ `${ index + 1 }. ${ String( company.prefix || '' ).replace( /<[^>]*>/g, '' ) }${ String( company.name || __( 'Nowa marka', domain ) ).replace( /<[^>]*>/g, '' ) }` } initialOpen={ false }>
			<TextControl label={ __( 'Adres docelowej strony (URL)', domain ) } type="url" value={ company.url || '' } onChange={ ( url ) => update( index, { url } ) } />
			<SelectControl label={ __( 'Kolor marki', domain ) } value={ company.brand || 'custom' } options={ brandOptions } onChange={ ( brand ) => update( index, { brand } ) } />
			{ company.brand === 'custom' && <ColorPalette value={ company.color || '' } enableAlpha={ false } clearable={ true } onChange={ ( color ) => update( index, { color: color || '' } ) } /> }
			<div className="spark-aboutfirm-editor-actions"><Button variant="secondary" disabled={ index === 0 } onClick={ () => move( index, -1 ) }>{ __( 'W górę', domain ) }</Button><Button variant="secondary" disabled={ index === companies.length - 1 } onClick={ () => move( index, 1 ) }>{ __( 'W dół', domain ) }</Button><Button variant="secondary" onClick={ () => setAttributes( { companies: [ ...companies.slice( 0, index + 1 ), { ...company }, ...companies.slice( index + 1 ) ] } ) }>{ __( 'Duplikuj', domain ) }</Button><Button isDestructive variant="tertiary" onClick={ () => setAttributes( { companies: companies.filter( ( item, itemIndex ) => itemIndex !== index ) } ) }>{ __( 'Usuń', domain ) }</Button></div>
		</PanelBody> ) }
		<PanelBody title={ __( 'Marki', domain ) } initialOpen={ companies.length === 0 }><Button variant="primary" onClick={ add }>{ __( 'Dodaj markę', domain ) }</Button></PanelBody>
	</InspectorControls><section { ...useBlockProps( { className: `spark-aboutfirm${ attributes.theme === 'inherit' ? '' : ` is-theme-${ attributes.theme || 'dark' }` } spark-aboutfirm--editor` } ) }><div className="spark-aboutfirm__inner"><header className="spark-aboutfirm__header"><RichText tagName="span" className="spark-aboutfirm__eyebrow" value={ attributes.eyebrow || '' } placeholder={ __( 'Etykieta sekcji', domain ) } allowedFormats={ [] } onChange={ ( eyebrow ) => setAttributes( { eyebrow } ) } /><div><RichText tagName="h2" value={ attributes.title || '' } placeholder={ __( 'Nagłówek sekcji', domain ) } allowedFormats={ [] } onChange={ ( title ) => setAttributes( { title } ) } /><RichText tagName="p" className="spark-aboutfirm__lede" value={ attributes.lede || '' } placeholder={ __( 'Opis sekcji', domain ) } allowedFormats={ [] } onChange={ ( lede ) => setAttributes( { lede } ) } /></div></header><div className="spark-aboutfirm__rows">{ companies.map( ( company, index ) => <BrandRow key={ index } company={ company } editing={ true } onChange={ ( changes ) => update( index, changes ) } /> ) }</div><Button className="spark-aboutfirm__editor-add" variant="secondary" onClick={ add }>{ __( 'Dodaj markę', domain ) }</Button></div></section></Fragment>;
}

registerBlockType( 'sparkagency/spark-aboutfirm', { edit: Edit, save: () => null } );
