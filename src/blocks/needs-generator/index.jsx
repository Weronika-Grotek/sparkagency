import { registerBlockType } from '@wordpress/blocks';
import { InspectorControls, MediaUpload, MediaUploadCheck, RichText, useBlockProps } from '@wordpress/block-editor';
import { Button, Notice, PanelBody, SelectControl, TextControl, TextareaControl } from '@wordpress/components';
import { __ } from '@wordpress/i18n';

const domain = 'sparkagency';
const brands = [
	{ label: 'Spark Agency', value: 'agency' },
	{ label: 'Spark EXP', value: 'exp' },
	{ label: 'Spark Warsaw', value: 'warsaw' },
	{ label: 'Spark Health', value: 'health' },
	{ label: 'Spark Works', value: 'works' },
	{ label: 'Spark Studio', value: 'studio' },
];
const blankOption = () => ( { label: '', value: '', resultId: '', nextUrl: '', brand: 'agency' } );
const blankStep = ( number ) => ( { title: `Pytanie ${ number }`, param: `answer_${ number }`, markerMode: 'letters', options: [ blankOption(), blankOption() ] } );
const blankResult = () => ( { id: 'agency', title: 'Spark Agency', description: '', contactUrl: '', imageId: 0, imageUrl: '', imageAlt: '' } );
const cleanSteps = ( steps ) => Array.isArray( steps ) ? steps : [];
const cleanResults = ( results ) => Array.isArray( results ) ? results : [];

const Edit = ( { attributes, setAttributes } ) => {
	const steps = cleanSteps( attributes.steps );
	const results = cleanResults( attributes.results );
	const resultOptions = [ { label: __( 'Brak wyniku', domain ), value: '' }, ...results.map( ( result ) => ( { label: result.title || result.id || __( 'Wynik bez nazwy', domain ), value: result.id || '' } ) ) ];
	const setSteps = ( nextSteps ) => setAttributes( { steps: nextSteps } );
	const updateStep = ( index, changes ) => setSteps( steps.map( ( step, position ) => position === index ? { ...step, ...changes } : step ) );
	const updateOption = ( stepIndex, optionIndex, changes ) => {
		const options = Array.isArray( steps[ stepIndex ].options ) ? steps[ stepIndex ].options : [];
		updateStep( stepIndex, { options: options.map( ( option, position ) => position === optionIndex ? { ...option, ...changes } : option ) } );
	};
	const firstStep = steps[ 0 ];

	return (
		<>
			<InspectorControls>
				<PanelBody title={ __( 'Nagłówek i przekierowanie', domain ) } initialOpen>
					<TextControl label={ __( 'Etykieta sekcji', domain ) } value={ attributes.eyebrow || '' } onChange={ ( eyebrow ) => setAttributes( { eyebrow } ) } />
					<TextControl label={ __( 'Nagłówek', domain ) } value={ attributes.title || '' } onChange={ ( title ) => setAttributes( { title } ) } />
					<TextareaControl label={ __( 'Wprowadzenie', domain ) } value={ attributes.lede || '' } onChange={ ( lede ) => setAttributes( { lede } ) } />
					<TextControl label={ __( 'Adres końcowy', domain ) } type="url" help={ __( 'Po ostatnim kroku link otrzyma odpowiedzi jako parametry GET.', domain ) } value={ attributes.finalUrl || '' } onChange={ ( finalUrl ) => setAttributes( { finalUrl } ) } />
					<TextControl label={ __( 'Tekst przycisku końcowego', domain ) } value={ attributes.finalButtonLabel || '' } onChange={ ( finalButtonLabel ) => setAttributes( { finalButtonLabel } ) } />
				</PanelBody>
				{ steps.map( ( step, stepIndex ) => {
					const options = Array.isArray( step.options ) ? step.options : [];
					return <PanelBody key={ stepIndex } title={ `${ stepIndex + 1 }. ${ step.title || __( 'Pytanie bez tytułu', domain ) }` } initialOpen={ false }>
						<TextControl label={ __( 'Tytuł pytania', domain ) } value={ step.title || '' } onChange={ ( title ) => updateStep( stepIndex, { title } ) } />
						<TextControl label={ __( 'Nazwa parametru GET', domain ) } help={ __( 'Np. zakres, usluga lub rynek. Tylko litery, cyfry i podkreślenia.', domain ) } value={ step.param || '' } onChange={ ( param ) => updateStep( stepIndex, { param } ) } />
						<SelectControl label={ __( 'Znacznik odpowiedzi', domain ) } value={ step.markerMode === 'brand' ? 'brand' : 'letters' } options={ [ { label: __( 'Litery A / B / C', domain ), value: 'letters' }, { label: __( 'Kolory marek', domain ), value: 'brand' } ] } onChange={ ( markerMode ) => updateStep( stepIndex, { markerMode } ) } />
						{ options.map( ( option, optionIndex ) => <div className="spark-needs-generator__editor-option" key={ optionIndex }>
							<TextControl label={ `${ __( 'Odpowiedź', domain ) } ${ optionIndex + 1 }` } value={ option.label || '' } onChange={ ( label ) => updateOption( stepIndex, optionIndex, { label } ) } />
							<TextControl label={ __( 'Wartość GET', domain ) } value={ option.value || '' } onChange={ ( value ) => updateOption( stepIndex, optionIndex, { value } ) } />
							<SelectControl label={ __( 'Wynik po tej odpowiedzi', domain ) } help={ stepIndex === 0 ? __( 'Pierwsza odpowiedź wybiera firmę pokazywaną w wyniku.', domain ) : __( 'Opcjonalnie nadpisuje wynik wybrany wcześniej.', domain ) } value={ option.resultId || '' } options={ resultOptions } onChange={ ( resultId ) => updateOption( stepIndex, optionIndex, { resultId } ) } />
							{ step.markerMode === 'brand' && <SelectControl label={ __( 'Kolor marki', domain ) } value={ option.brand || 'agency' } options={ brands } onChange={ ( brand ) => updateOption( stepIndex, optionIndex, { brand } ) } /> }
							<TextControl label={ __( 'URL natychmiastowego przejścia', domain ) } type="url" help={ __( 'Opcjonalne: po wyborze użytkownik przejdzie pod ten adres z dotychczasowymi parametrami GET.', domain ) } value={ option.nextUrl || '' } onChange={ ( nextUrl ) => updateOption( stepIndex, optionIndex, { nextUrl } ) } />
							<Button isDestructive variant="tertiary" onClick={ () => updateStep( stepIndex, { options: options.filter( ( item, position ) => position !== optionIndex ) } ) }>{ __( 'Usuń odpowiedź', domain ) }</Button>
						</div> ) }
						<Button variant="secondary" onClick={ () => updateStep( stepIndex, { options: [ ...options, blankOption() ] } ) }>{ __( 'Dodaj odpowiedź', domain ) }</Button>
						<div className="spark-needs-generator__editor-actions">
							<Button disabled={ stepIndex === 0 } onClick={ () => setSteps( steps.map( ( item, position ) => position === stepIndex ? steps[ stepIndex - 1 ] : position === stepIndex - 1 ? step : item ) ) }>{ __( 'Przesuń wyżej', domain ) }</Button>
							<Button isDestructive variant="tertiary" onClick={ () => setSteps( steps.filter( ( item, position ) => position !== stepIndex ) ) }>{ __( 'Usuń pytanie', domain ) }</Button>
						</div>
					</PanelBody>;
				} ) }
				<PanelBody title={ __( 'Pytania', domain ) } initialOpen={ false }><Button variant="primary" onClick={ () => setSteps( [ ...steps, blankStep( steps.length + 1 ) ] ) }>{ __( 'Dodaj pytanie', domain ) }</Button></PanelBody>
				{ results.map( ( result, resultIndex ) => <PanelBody key={ `result-${ resultIndex }` } title={ `${ __( 'Wynik', domain ) }: ${ result.title || result.id || '—' }` } initialOpen={ false }>
					<TextControl label={ __( 'Identyfikator wyniku', domain ) } help={ __( 'Powinien odpowiadać opcji „Wynik po tej odpowiedzi”.', domain ) } value={ result.id || '' } onChange={ ( id ) => setAttributes( { results: results.map( ( item, position ) => position === resultIndex ? { ...item, id } : item ) } ) } />
					<TextControl label={ __( 'Nazwa firmy', domain ) } value={ result.title || '' } onChange={ ( title ) => setAttributes( { results: results.map( ( item, position ) => position === resultIndex ? { ...item, title } : item ) } ) } />
					<TextareaControl label={ __( 'Opis firmy', domain ) } value={ result.description || '' } onChange={ ( description ) => setAttributes( { results: results.map( ( item, position ) => position === resultIndex ? { ...item, description } : item ) } ) } />
					<TextControl label={ __( 'Adres strony kontaktowej firmy', domain ) } type="url" help={ __( 'Ten adres ma pierwszeństwo przed globalnym adresem końcowym.', domain ) } value={ result.contactUrl || '' } onChange={ ( contactUrl ) => setAttributes( { results: results.map( ( item, position ) => position === resultIndex ? { ...item, contactUrl } : item ) } ) } />
					<MediaUploadCheck><MediaUpload allowedTypes={ [ 'image' ] } value={ result.imageId || 0 } onSelect={ ( media ) => setAttributes( { results: results.map( ( item, position ) => position === resultIndex ? { ...item, imageId: media.id || 0, imageUrl: media.url || '', imageAlt: media.alt || '' } : item ) } ) } render={ ( { open } ) => <Button variant="secondary" onClick={ open }>{ result.imageUrl ? __( 'Zmień obraz', domain ) : __( 'Wybierz obraz', domain ) }</Button> } /></MediaUploadCheck>
					{ result.imageUrl && <Button isDestructive variant="tertiary" onClick={ () => setAttributes( { results: results.map( ( item, position ) => position === resultIndex ? { ...item, imageId: 0, imageUrl: '', imageAlt: '' } : item ) } ) }>{ __( 'Usuń obraz', domain ) }</Button> }
					<Button isDestructive variant="tertiary" onClick={ () => setAttributes( { results: results.filter( ( item, position ) => position !== resultIndex ) } ) }>{ __( 'Usuń wynik', domain ) }</Button>
				</PanelBody> ) }
				<PanelBody title={ __( 'Firmy / wyniki', domain ) } initialOpen={ false }><Button variant="primary" onClick={ () => setAttributes( { results: [ ...results, blankResult() ] } ) }>{ __( 'Dodaj firmę', domain ) }</Button></PanelBody>
			</InspectorControls>
			<section { ...useBlockProps( { className: 'spark-needs-generator is-theme-dark' } ) }>
				<header className="spark-needs-generator__header">
					<RichText tagName="p" className="spark-needs-generator__eyebrow" value={ attributes.eyebrow || '' } placeholder={ __( 'Etykieta sekcji', domain ) } allowedFormats={ [] } onChange={ ( eyebrow ) => setAttributes( { eyebrow } ) } />
					<div><RichText tagName="h2" value={ attributes.title || '' } placeholder={ __( 'Nagłówek', domain ) } onChange={ ( title ) => setAttributes( { title } ) } /><RichText tagName="p" className="spark-needs-generator__lede" value={ attributes.lede || '' } placeholder={ __( 'Opis sekcji', domain ) } onChange={ ( lede ) => setAttributes( { lede } ) } /></div>
				</header>
				<div className="spark-needs-generator__preview">
					{ ! steps.length ? <Notice status="info" isDismissible={ false }>{ __( 'Dodaj pytanie oraz co najmniej dwie odpowiedzi w panelu bocznym.', domain ) }</Notice> : <><p className="spark-needs-generator__progress">{ __( 'Pytanie 1 z', domain ) } { steps.length }</p><h3>{ firstStep.title }</h3><div className="spark-needs-generator__options">{ ( firstStep.options || [] ).map( ( option, index ) => <button className={ firstStep.markerMode === 'brand' ? `is-brand-${ option.brand || 'agency' }` : undefined } type="button" key={ index }>{ firstStep.markerMode === 'brand' ? <i aria-hidden="true" /> : <b>{ String.fromCharCode( 65 + index ) }</b> }<span>{ option.label || __( 'Odpowiedź bez nazwy', domain ) }</span><em>→</em></button> ) }</div></> }
				</div>
			</section>
		</>
	);
};

registerBlockType( 'sparkagency/needs-generator', { edit: Edit, save: () => null } );
