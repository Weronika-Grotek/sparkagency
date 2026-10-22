import { registerBlockType } from '@wordpress/blocks';
import { InspectorControls, MediaUpload, MediaUploadCheck, RichText, useBlockProps } from '@wordpress/block-editor';
import { Button, Notice, PanelBody, SelectControl, TextControl, TextareaControl } from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import { blankCompany, blankInformationOption, blankScope, readModel, readTitles, slugify, writeModel } from './model';

const domain = 'sparkagency';
const brands = [
	{ label: 'Spark Agency', value: 'agency' },
	{ label: 'Spark EXP', value: 'exp' },
	{ label: 'Spark Warsaw', value: 'warsaw' },
	{ label: 'Spark Health', value: 'health' },
	{ label: 'Spark Works', value: 'works' },
	{ label: 'Spark Studio', value: 'studio' },
];
const stepLabels = [ __( 'Krok 1 · firma', domain ), __( 'Krok 2 · informacja', domain ), __( 'Krok 3 · zakres', domain ) ];
const replaceAt = ( list, index, changes ) => list.map( ( item, position ) => ( position === index ? { ...item, ...changes } : item ) );
const removeAt = ( list, index ) => list.filter( ( item, position ) => position !== index );
const moveBy = ( list, index, offset ) => {
	const target = index + offset;
	if ( target < 0 || target >= list.length ) return list;
	const next = [ ...list ];
	[ next[ index ], next[ target ] ] = [ next[ target ], next[ index ] ];
	return next;
};

const Edit = ( { attributes, setAttributes } ) => {
	const { companies, informationOptions, isLegacy } = readModel( attributes );
	const titles = readTitles( attributes );
	// Every change goes through save(): it writes the new model and clears the legacy attributes.
	const save = ( changes ) => setAttributes( writeModel( { companies, informationOptions, titles, ...changes } ) );
	const setCompanies = ( next ) => save( { companies: next } );
	const updateCompany = ( index, changes ) => setCompanies( replaceAt( companies, index, changes ) );
	const updateScope = ( companyIndex, scopeIndex, changes ) => updateCompany( companyIndex, { scopes: replaceAt( companies[ companyIndex ].scopes || [], scopeIndex, changes ) } );
	const setInformation = ( next ) => save( { informationOptions: next } );
	const ids = companies.map( ( company ) => company.id );
	const duplicates = ids.filter( ( id, index ) => id && ids.indexOf( id ) !== index );
	const companyOptions = [ { label: __( 'Wszystkie firmy', domain ), value: '' }, ...companies.filter( ( company ) => company.id ).map( ( company ) => ( { label: company.label || company.id, value: company.id } ) ) ];

	return (
		<>
			<InspectorControls>
				<PanelBody title={ __( 'Nagłówek i link domyślny', domain ) } initialOpen>
					{ isLegacy && <Notice status="warning" isDismissible={ false }>{ __( 'Blok używa starego formatu danych. Pierwsza zmiana zapisze go w nowym formacie: firmy i zakresy zostaną zachowane.', domain ) }</Notice> }
					<TextControl label={ __( 'Etykieta sekcji', domain ) } value={ attributes.eyebrow || '' } onChange={ ( eyebrow ) => setAttributes( { eyebrow } ) } />
					<TextControl label={ __( 'Nagłówek', domain ) } value={ attributes.title || '' } onChange={ ( title ) => setAttributes( { title } ) } />
					<TextareaControl label={ __( 'Wprowadzenie', domain ) } value={ attributes.lede || '' } onChange={ ( lede ) => setAttributes( { lede } ) } />
					<TextControl label={ __( 'Link domyślny', domain ) } type="url" help={ __( 'Używany tylko dla firm bez własnego URL strony końcowej.', domain ) } value={ attributes.finalUrl || '' } onChange={ ( finalUrl ) => setAttributes( { finalUrl } ) } />
					<TextControl label={ __( 'Tekst przycisku końcowego', domain ) } value={ attributes.finalButtonLabel || '' } onChange={ ( finalButtonLabel ) => setAttributes( { finalButtonLabel } ) } />
				</PanelBody>
				<PanelBody title={ __( 'Pytania', domain ) } initialOpen>
					{ titles.map( ( title, index ) => <TextControl key={ index } label={ stepLabels[ index ] } value={ title } onChange={ ( value ) => save( { titles: titles.map( ( item, position ) => ( position === index ? value : item ) ) } ) } /> ) }
					<Notice status="info" isDismissible={ false }>{ __( 'Krok 1 to lista firm z paneli poniżej. Krok 3 pokazuje zakresy wybranej firmy; firma bez zakresów pomija krok 3.', domain ) }</Notice>
				</PanelBody>
				<PanelBody title={ __( 'Krok 2 · odpowiedzi informacyjne', domain ) } initialOpen>
					{ informationOptions.map( ( option, index ) => <div className="spark-needs-generator__editor-option" key={ index }>
						<TextControl label={ `${ __( 'Odpowiedź', domain ) } ${ index + 1 }` } value={ option.label || '' } onChange={ ( label ) => setInformation( replaceAt( informationOptions, index, { label } ) ) } />
						<TextControl label={ __( 'Dokładna wartość do formularza', domain ) } help={ __( 'Puste pole = etykieta.', domain ) } value={ option.value || '' } onChange={ ( value ) => setInformation( replaceAt( informationOptions, index, { value } ) ) } />
						<SelectControl label={ __( 'Pokaż dla firmy', domain ) } value={ option.visibleFor || '' } options={ companyOptions } onChange={ ( visibleFor ) => setInformation( replaceAt( informationOptions, index, { visibleFor } ) ) } />
						<Button isDestructive variant="tertiary" onClick={ () => setInformation( removeAt( informationOptions, index ) ) }>{ __( 'Usuń odpowiedź', domain ) }</Button>
					</div> ) }
					<Button variant="secondary" onClick={ () => setInformation( [ ...informationOptions, blankInformationOption() ] ) }>{ __( 'Dodaj odpowiedź', domain ) }</Button>
				</PanelBody>
				{ companies.map( ( company, companyIndex ) => {
					const scopes = Array.isArray( company.scopes ) ? company.scopes : [];
					return <PanelBody key={ companyIndex } title={ `${ __( 'Firma', domain ) }: ${ company.label || company.id || '—' }` } initialOpen>
						{ duplicates.includes( company.id ) && <Notice status="error" isDismissible={ false }>{ __( 'Ten identyfikator ma już inna firma. Identyfikatory muszą być unikalne.', domain ) }</Notice> }
						<TextControl label={ __( 'Nazwa firmy', domain ) } value={ company.label || '' } onChange={ ( label ) => updateCompany( companyIndex, { label, ...( ! company.id || company.id === slugify( company.label ) ? { id: slugify( label ) } : {} ) } ) } />
						<TextControl label={ __( 'URL strony końcowej', domain ) } type="url" help={ __( 'Strona z blokiem „Kontakt — podsumowanie potrzeb”, także w innym serwisie na tym motywie. Wszystkie wybory (firma, informacja, zakres) zostaną dopisane do adresu. Dodaj #kotwica, aby przewinąć do bloku.', domain ) } value={ company.url || '' } onChange={ ( url ) => updateCompany( companyIndex, { url } ) } />
						{ ! company.url && <Notice status="warning" isDismissible={ false }>{ attributes.finalUrl ? __( 'Brak URL: zostanie użyty link domyślny bloku.', domain ) : __( 'Brak URL: po wyborze tej firmy nie pojawi się przycisk przejścia.', domain ) }</Notice> }
						<TextControl label={ __( 'Identyfikator', domain ) } help={ __( 'Przesyłany jako spark_company. Zgodny z kluczem tematu w formularzu kontaktowym (np. exp, warsaw), aby temat zaznaczył się sam.', domain ) } value={ company.id || '' } onChange={ ( id ) => updateCompany( companyIndex, { id: id.toLowerCase().replace( /[^a-z0-9_-]/g, '' ) } ) } />
						<SelectControl label={ __( 'Kolor marki', domain ) } value={ company.brand || 'agency' } options={ brands } onChange={ ( brand ) => updateCompany( companyIndex, { brand } ) } />
						<TextareaControl label={ __( 'Opis w podsumowaniu', domain ) } value={ company.description || '' } onChange={ ( description ) => updateCompany( companyIndex, { description } ) } />
						<MediaUploadCheck><MediaUpload allowedTypes={ [ 'image' ] } value={ company.imageId || 0 } onSelect={ ( media ) => updateCompany( companyIndex, { imageId: media.id || 0, imageUrl: media.url || '', imageAlt: media.alt || '' } ) } render={ ( { open } ) => <Button variant="secondary" onClick={ open }>{ company.imageUrl ? __( 'Zmień obraz', domain ) : __( 'Wybierz obraz', domain ) }</Button> } /></MediaUploadCheck>
						{ company.imageUrl && <Button isDestructive variant="tertiary" onClick={ () => updateCompany( companyIndex, { imageId: 0, imageUrl: '', imageAlt: '' } ) }>{ __( 'Usuń obraz', domain ) }</Button> }
						<p><strong>{ __( 'Zakresy (krok 3)', domain ) }</strong></p>
						{ scopes.map( ( scope, scopeIndex ) => <div className="spark-needs-generator__editor-option" key={ scopeIndex }>
							<TextControl label={ `${ __( 'Zakres', domain ) } ${ scopeIndex + 1 }` } value={ scope.label || '' } onChange={ ( label ) => updateScope( companyIndex, scopeIndex, { label } ) } />
							<TextControl label={ __( 'Dokładna wartość do formularza', domain ) } help={ __( 'Puste pole = etykieta.', domain ) } value={ scope.value || '' } onChange={ ( value ) => updateScope( companyIndex, scopeIndex, { value } ) } />
							<Button isDestructive variant="tertiary" onClick={ () => updateCompany( companyIndex, { scopes: removeAt( scopes, scopeIndex ) } ) }>{ __( 'Usuń zakres', domain ) }</Button>
						</div> ) }
						<Button variant="secondary" onClick={ () => updateCompany( companyIndex, { scopes: [ ...scopes, blankScope() ] } ) }>{ __( 'Dodaj zakres', domain ) }</Button>
						<div className="spark-needs-generator__editor-actions">
							<Button disabled={ companyIndex === 0 } onClick={ () => setCompanies( moveBy( companies, companyIndex, -1 ) ) }>{ __( 'Wyżej', domain ) }</Button>
							<Button disabled={ companyIndex === companies.length - 1 } onClick={ () => setCompanies( moveBy( companies, companyIndex, 1 ) ) }>{ __( 'Niżej', domain ) }</Button>
							<Button isDestructive variant="tertiary" onClick={ () => setCompanies( removeAt( companies, companyIndex ) ) }>{ __( 'Usuń firmę', domain ) }</Button>
						</div>
					</PanelBody>;
				} ) }
				<PanelBody title={ __( 'Nowa firma', domain ) } initialOpen>
					<Button variant="primary" onClick={ () => setCompanies( [ ...companies, blankCompany() ] ) }>{ __( 'Dodaj firmę', domain ) }</Button>
				</PanelBody>
			</InspectorControls>
			<section { ...useBlockProps( { className: 'spark-needs-generator is-theme-dark' } ) }>
				<header className="spark-needs-generator__header">
					<RichText tagName="p" className="spark-needs-generator__eyebrow" value={ attributes.eyebrow || '' } placeholder={ __( 'Etykieta sekcji', domain ) } allowedFormats={ [] } onChange={ ( eyebrow ) => setAttributes( { eyebrow } ) } />
					<div><RichText tagName="h2" value={ attributes.title || '' } placeholder={ __( 'Nagłówek', domain ) } onChange={ ( title ) => setAttributes( { title } ) } /><RichText tagName="p" className="spark-needs-generator__lede" value={ attributes.lede || '' } placeholder={ __( 'Opis sekcji', domain ) } onChange={ ( lede ) => setAttributes( { lede } ) } /></div>
				</header>
				<div className="spark-needs-generator__preview">
					{ ! companies.length ? <Notice status="info" isDismissible={ false }>{ __( 'Dodaj firmę w panelu bocznym.', domain ) }</Notice> : <>
						<p className="spark-needs-generator__progress">{ stepLabels[ 0 ] }</p>
						<h3>{ titles[ 0 ] }</h3>
						<div className="spark-needs-generator__editor-companies">
							{ companies.map( ( company, index ) => <div className="spark-needs-generator__editor-company" key={ index }>
								<div className="spark-needs-generator__options"><button className={ `is-brand-${ company.brand || 'agency' }` } type="button"><i aria-hidden="true" /><span>{ company.label || __( 'Firma bez nazwy', domain ) }</span><em>→</em></button></div>
								<dl className="spark-needs-generator__result-summary">
									<div><dt>{ stepLabels[ 1 ] }</dt><dd>{ informationOptions.filter( ( option ) => option.label && ( ! option.visibleFor || option.visibleFor === company.id ) ).map( ( option ) => option.label ).join( ', ' ) || __( 'pominięty', domain ) }</dd></div>
									<div><dt>{ stepLabels[ 2 ] }</dt><dd>{ ( company.scopes || [] ).filter( ( scope ) => scope.label ).map( ( scope ) => scope.label ).join( ', ' ) || __( 'pominięty', domain ) }</dd></div>
									<div><dt>{ __( 'Strona końcowa', domain ) }</dt><dd>{ company.url || ( attributes.finalUrl ? `${ attributes.finalUrl } (${ __( 'domyślny', domain ) })` : __( 'brak URL', domain ) ) }</dd></div>
								</dl>
							</div> ) }
						</div>
					</> }
				</div>
			</section>
		</>
	);
};

registerBlockType( 'sparkagency/needs-generator', { edit: Edit, save: () => null } );
