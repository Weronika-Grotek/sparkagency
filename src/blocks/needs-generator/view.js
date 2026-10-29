import { needsToParams, readNeeds, withNeeds } from '../_shared/needs';

const DEFAULT_TITLES = [ 'Co chcesz zrobić?', 'Skala zadania', 'Czego potrzebujesz?' ];

const initializeNeedsGenerators = () => {
	document.querySelectorAll( '[data-spark-needs-generator]' ).forEach( ( root ) => {
		if ( root.dataset.initialized === 'true' ) return;
		let config;
		try { config = JSON.parse( root.dataset.config || '{}' ); } catch ( error ) { return; }

		const companies = Array.isArray( config.companies ) ? config.companies : [];
		const actions = Array.isArray( config.actions ) ? config.actions : [];
		const information = Array.isArray( config.information ) ? config.information : [];
		const scope = Array.isArray( config.scope ) ? config.scope : [];
		const titles = DEFAULT_TITLES.map( ( title, index ) => config.titles?.[ index ] || title );
		if ( ! actions.length ) return;
		root.dataset.initialized = 'true';

		const section = root.closest( '.spark-needs-generator' ) || root;
		if ( ! section.id ) section.id = 'spark-needs-generator';
		const navigation = root.querySelector( '[data-needs-steps]' );
		const stage = root.querySelector( '[data-needs-stage]' );
		const back = root.querySelector( '[data-needs-back]' );
		const count = root.querySelector( '[data-needs-count]' );
		// Keyed by step: company, information, scope. Each answer is { value, label } plus its source item.
		const answers = {};
		let activeStep = 0;

		const create = ( tag, className, text ) => {
			const element = document.createElement( tag );
			if ( className ) element.className = className;
			if ( text ) element.textContent = text;
			return element;
		};
		const appendCompanyName = ( element, item, fallback ) => {
			const label = item?.label || fallback;
			const highlighted = item?.highlightText || '';
			const start = highlighted ? label.toLocaleLowerCase().indexOf( highlighted.toLocaleLowerCase() ) : -1;
			if ( start === -1 ) {
				element.textContent = label;
				return;
			}
			element.append( document.createTextNode( label.slice( 0, start ) ) );
			const accent = create( 'span', `spark-needs-generator__company-accent is-brand-${ item.highlightBrand || item.brand || 'agency' }${ item.highlightBold === false ? '' : ' is-bold' }`, label.slice( start, start + highlighted.length ) );
			element.append( accent, document.createTextNode( label.slice( start + highlighted.length ) ) );
		};
		const company = () => companies.find( ( item ) => item.id === answers.company?.value );

		// The selected action determines the company. Steps 2 and 3 can then be
		// configured per company or shown to every company.
		const flow = () => {
			const selected = company();
			const informationOptions = selected ? information.filter( ( option ) => ! option.visibleFor || option.visibleFor === selected.id ) : [];
			const scopeOptions = selected ? scope.filter( ( option ) => ! option.visibleFor || option.visibleFor === selected.id ) : [];
			return [
				{ key: 'company', title: titles[ 0 ], options: actions.map( ( item ) => {
					const mappedCompany = companies.find( ( companyItem ) => companyItem.id === item.companyId );
					return { value: mappedCompany?.id || item.value, label: item.label, brand: mappedCompany?.brand || 'agency', item };
				} ) },
				{ key: 'information', title: titles[ 1 ], options: informationOptions.map( ( option ) => ( { value: option.value, label: option.label, item: option } ) ) },
				{ key: 'scope', title: titles[ 2 ], options: scopeOptions.map( ( option ) => ( { value: option.value, label: option.label, item: option } ) ) },
			];
		};

		const returnUrl = () => {
			const url = new URL( window.location.href );
			[ ...url.searchParams.keys() ].filter( ( key ) => key.startsWith( 'spark_' ) ).forEach( ( key ) => url.searchParams.delete( key ) );
			needsToParams( answers ).forEach( ( value, key ) => url.searchParams.set( key, value ) );
			url.hash = section.id;
			return url.href;
		};
		// The company decides the final page; the block-wide link is only a fallback.
		const destination = () => withNeeds(
			company()?.url || config.finalUrl,
			needsToParams( answers, returnUrl() )
		);

		const renderNavigation = ( steps, isResult ) => {
			navigation.replaceChildren();
			[ ...steps, { key: 'result', title: 'Podsumowanie' } ].forEach( ( step, index ) => {
				const item = create( 'li' );
				item.classList.toggle( 'is-active', index === activeStep );
				item.classList.toggle( 'is-done', Boolean( answers[ step.key ] ) );
				const button = create( 'button' );
				button.type = 'button';
				button.disabled = index > activeStep || ( step.key === 'result' && ! isResult );
				button.textContent = `${ String( index + 1 ).padStart( 2, '0' ) } · ${ step.title }`;
				button.addEventListener( 'click', () => render( index ) );
				item.append( button );
				if ( answers[ step.key ]?.label ) item.append( create( 'small', '', answers[ step.key ].label ) );
				navigation.append( item );
			} );
		};

		const renderResult = ( steps ) => {
			stage.replaceChildren();
			const selected = company();
			const wrapper = create( 'div', 'spark-needs-generator__result' );
			const main = create( 'div' );
			main.append( create( 'p', 'spark-needs-generator__progress', 'Twój punkt startu' ) );
			const companyName = create( 'p', 'spark-needs-generator__result-brand' );
			appendCompanyName( companyName, selected, 'Porozmawiajmy' );
			main.append( companyName );
			if ( selected?.description ) main.append( create( 'p', 'spark-needs-generator__result-text', selected.description ) );
			if ( selected?.imageUrl ) {
				const image = document.createElement( 'img' );
				image.src = selected.imageUrl;
				image.alt = selected.imageAlt || selected.label || '';
				main.append( image );
			}
			const panel = create( 'aside', 'spark-needs-generator__result-panel' );
			panel.append( create( 'p', 'spark-needs-generator__result-panel-title', 'Twoje odpowiedzi' ) );
			const summary = create( 'dl', 'spark-needs-generator__result-summary' );
			steps.forEach( ( step ) => {
				if ( ! answers[ step.key ] ) return;
				const row = create( 'div' );
				row.append( create( 'dt', '', step.title ), create( 'dd', '', answers[ step.key ].label ) );
				summary.append( row );
			} );
			panel.append( summary );
			const actions = create( 'div', 'spark-needs-generator__result-actions' );
			const href = destination();
			if ( href ) {
				const link = create( 'a', 'spark-needs-generator__final', config.finalButtonLabel || 'Przejdź dalej' );
				link.href = href;
				actions.append( link );
			}
			const restart = create( 'button', 'spark-needs-generator__back', '↺ Zacznij od nowa' );
			restart.type = 'button';
			restart.addEventListener( 'click', () => {
				Object.keys( answers ).forEach( ( key ) => delete answers[ key ] );
				render( 0 );
			} );
			actions.append( restart );
			panel.append( actions );
			panel.append( create( 'p', 'spark-needs-generator__result-note', 'Przeniesiemy te odpowiedzi do formularza. Uzupełnisz tylko opis projektu i dane kontaktowe.' ) );
			wrapper.append( main );
			wrapper.append( panel );
			stage.append( wrapper );
		};

		const renderQuestion = ( step, index ) => {
			stage.replaceChildren();
			const question = create( 'fieldset', 'spark-needs-generator__question' );
			question.append( create( 'legend', '', step.title ) );
			const options = create( 'div', 'spark-needs-generator__options' );
			if ( ! step.options.length ) {
				question.append( create( 'p', 'spark-needs-generator__empty', 'Dla wybranej firmy ten krok nie ma odpowiedzi. Przejdź dalej.' ) );
				const next = create( 'button', 'spark-needs-generator__final', 'Dalej →' );
				next.type = 'button';
				next.addEventListener( 'click', () => render( index + 1 ) );
				question.append( next );
			}
			step.options.forEach( ( option, optionIndex ) => {
				const button = create( 'button' );
				button.type = 'button';
				if ( step.key === 'company' ) {
					button.classList.add( 'is-action' );
					button.classList.add( `is-brand-${ option.brand || 'agency' }` );
					const marker = create( 'i' );
					marker.setAttribute( 'aria-hidden', 'true' );
					button.append( marker );
				} else button.append( create( 'b', '', String.fromCharCode( 65 + optionIndex ) ) );
				if ( answers[ step.key ]?.value === option.value ) button.setAttribute( 'aria-pressed', 'true' );
				const optionLabel = create( 'span', '', option.label );
				button.append( optionLabel, create( 'em', '', '→' ) );
				button.addEventListener( 'click', () => {
					// Changing the company invalidates the answers that depend on it.
					if ( step.key === 'company' && answers.company?.value !== option.value ) {
						delete answers.information;
						delete answers.scope;
					}
					answers[ step.key ] = { value: option.value, label: option.label, item: option.item };
					render( index + 1 );
				} );
				options.append( button );
			} );
			question.append( options );
			stage.append( question );
		};

		const render = ( index ) => {
			const steps = flow();
			activeStep = Math.min( Math.max( index, 0 ), steps.length );
			const isResult = activeStep === steps.length;
			const answered = Math.min( activeStep, steps.length );
			root.style.setProperty( '--needs-step-count', String( steps.length + 1 ) );
			root.style.setProperty( '--needs-progress', String( isResult ? 1 : answered / ( steps.length + 1 ) ) );
			back.hidden = activeStep === 0;
			back.disabled = activeStep === 0;
			count.textContent = isResult ? 'Gotowe · możesz zmienić odpowiedzi' : `Pytanie ${ activeStep + 1 } z ${ steps.length } · wybierz jedną odpowiedź`;
			renderNavigation( steps, isResult );
			if ( isResult ) renderResult( steps ); else renderQuestion( steps[ activeStep ], activeStep );
		};

		// "Zmień odpowiedzi" on the form page links back here with the answers in the query.
		const restore = () => {
			const needs = readNeeds();
			const selected = companies.find( ( item ) => item.id === needs?.company?.value );
			if ( ! selected ) return 0;
			answers.company = { value: selected.id, label: selected.label, item: selected };
			const steps = flow();
			steps.slice( 1 ).forEach( ( step ) => {
				const option = step.options.find( ( item ) => item.value === needs[ step.key ]?.value );
				if ( option ) answers[ step.key ] = { value: option.value, label: option.label, item: option.item };
			} );
			const firstMissing = steps.findIndex( ( step ) => step.options.length && ! answers[ step.key ] );
			return firstMissing === -1 ? steps.length : firstMissing;
		};

		back.addEventListener( 'click', () => render( activeStep - 1 ) );
		render( restore() );
	} );
};

if ( document.readyState === 'loading' ) document.addEventListener( 'DOMContentLoaded', initializeNeedsGenerators, { once: true } ); else initializeNeedsGenerators();
