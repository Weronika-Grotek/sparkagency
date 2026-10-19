const initializeNeedsGenerators = () => {
	document.querySelectorAll( '[data-spark-needs-generator]' ).forEach( ( root ) => {
		if ( root.dataset.initialized === 'true' ) return;
		let config;
		try {
			config = JSON.parse( root.dataset.config || '{}' );
		} catch ( error ) {
			return;
		}
		const steps = Array.isArray( config.steps ) ? config.steps : [];
		const serviceGroups = Array.isArray( config.serviceGroups ) ? config.serviceGroups : [];
		if ( ! steps.length ) return;
		root.dataset.initialized = 'true';

		const navigation = root.querySelector( '[data-needs-steps]' );
		const progress = root.querySelector( '[data-needs-progress]' );
		const stage = root.querySelector( '[data-needs-stage]' );
		const back = root.querySelector( '[data-needs-back]' );
		const count = root.querySelector( '[data-needs-count]' );
		const answers = [];
		let activeStep = 0;

		const create = ( tag, className, text ) => {
			const element = document.createElement( tag );
			if ( className ) element.className = className;
			if ( text ) element.textContent = text;
			return element;
		};
		const selectedResult = () => {
			const answer = [ ...answers ].reverse().find( ( item ) => item?.resultId );
			return ( config.results || [] ).find( ( result ) => result.id === answer?.resultId );
		};
		const firstAnswerResultId = () => answers[ 0 ]?.resultId || '';
		const firstAnswerServiceGroupId = () => answers[ 0 ]?.serviceGroupId || answers[ 0 ]?.resultId || '';
		const optionsForStep = ( step ) => {
			if ( step.optionsSource === 'serviceGroups' ) {
				const group = serviceGroups.find( ( item ) => item.id === firstAnswerServiceGroupId() );
				return ( group?.categories || [] ).map( ( category ) => ( { label: category, value: category } ) );
			}

			return step.options.filter( ( option ) => ! option.visibleForResultId || option.visibleForResultId === firstAnswerResultId() );
		};
		const query = () => {
			const params = new URLSearchParams();
			answers.forEach( ( answer ) => {
				if ( answer?.param && answer.value ) params.set( answer.param, answer.value );
			} );
			const result = selectedResult();
			if ( result?.id ) {
				params.set( 'result', result.id );
				params.set( 'spark_result_name', result.title || result.id );
			}
			params.set( 'spark_answers', JSON.stringify( answers.map( ( answer ) => ( {
				question: answer.question,
				param: answer.param,
				label: answer.label,
				value: answer.value,
			} ) ) ) );
			return params;
		};
		const destination = ( value ) => {
			if ( ! value ) return null;
			try {
				const url = new URL( value, window.location.href );
				if ( ! [ 'http:', 'https:' ].includes( url.protocol ) ) return null;
				query().forEach( ( queryValue, key ) => url.searchParams.set( key, queryValue ) );
				return url.href;
			} catch ( error ) {
				return null;
			}
		};
		const renderNavigation = ( isResult ) => {
			navigation.replaceChildren();
			[ ...steps, { title: 'Wynik' } ].forEach( ( step, index ) => {
				const item = create( 'li' );
				item.classList.toggle( 'is-active', index === activeStep );
				item.classList.toggle( 'is-done', index < steps.length && Boolean( answers[ index ] ) );
				const button = create( 'button' );
				button.type = 'button';
				button.disabled = index > activeStep || ( index === steps.length && ! isResult );
				button.textContent = `${ String( index + 1 ).padStart( 2, '0' ) } · ${ step.title }`;
				button.addEventListener( 'click', () => render( index ) );
				item.append( button );
				if ( answers[ index ]?.label ) item.append( create( 'small', '', answers[ index ].label ) );
				navigation.append( item );
			} );
		};
		const renderResult = () => {
			stage.replaceChildren();
			const result = selectedResult();
			const wrapper = create( 'div', 'spark-needs-generator__result' );
			const main = create( 'div' );
			main.append( create( 'p', 'spark-needs-generator__progress', 'Od tego warto zacząć' ) );
			main.append( create( 'p', 'spark-needs-generator__result-brand', result?.title || 'Porozmawiajmy' ) );
			if ( result?.description ) main.append( create( 'p', 'spark-needs-generator__result-text', result.description ) );
			const actions = create( 'div', 'spark-needs-generator__result-actions' );
			const finalUrl = destination( config.finalUrl || result?.contactUrl );
			if ( finalUrl ) {
				const link = create( 'a', 'spark-needs-generator__final', config.finalButtonLabel || 'Przejdź dalej' );
				link.href = finalUrl;
				actions.append( link );
			}
			const restart = create( 'button', 'spark-needs-generator__back', '↺ Zacznij od nowa' );
			restart.type = 'button';
			restart.addEventListener( 'click', () => { answers.length = 0; render( 0 ); } );
			actions.append( restart );
			main.append( actions );
			wrapper.append( main );
			if ( result?.imageUrl ) {
				const image = document.createElement( 'img' );
				image.src = result.imageUrl;
				image.alt = result.imageAlt || result.title || '';
				wrapper.append( image );
			}
			stage.append( wrapper );
		};
		const renderQuestion = ( index ) => {
			const step = steps[ index ];
			stage.replaceChildren();
			const question = create( 'fieldset', 'spark-needs-generator__question' );
			question.append( create( 'legend', '', step.title || `Pytanie ${ index + 1 }` ) );
			const options = create( 'div', 'spark-needs-generator__options' );
			const visibleOptions = optionsForStep( step );
			if ( ! visibleOptions.length ) {
				question.append( create( 'p', 'spark-needs-generator__empty', 'Brak kategorii dla wybranej firmy. Wróć do pytania 1 i wybierz firmę lub uzupełnij jej kategorie.' ) );
			}
			visibleOptions.forEach( ( option, optionIndex ) => {
				const button = create( 'button' );
				button.type = 'button';
				if ( step.markerMode === 'brand' ) {
					button.classList.add( `is-brand-${ option.brand || 'agency' }` );
					const marker = create( 'i' );
					marker.setAttribute( 'aria-hidden', 'true' );
					button.append( marker );
				} else {
					button.append( create( 'b', '', String.fromCharCode( 65 + optionIndex ) ) );
				}
				button.append( create( 'span', '', option.label ), create( 'em', '', '→' ) );
				button.addEventListener( 'click', () => {
					answers[ index ] = { question: step.title || `Pytanie ${ index + 1 }`, param: step.param, value: option.value || option.label, label: option.label, resultId: option.resultId || answers[ index - 1 ]?.resultId || '', serviceGroupId: option.serviceGroupId || answers[ 0 ]?.serviceGroupId || '' };
					answers.length = index + 1;
					const nextUrl = destination( option.nextUrl );
					if ( nextUrl ) {
						window.location.assign( nextUrl );
						return;
					}
					render( index + 1 );
				} );
				options.append( button );
			} );
			question.append( options );
			stage.append( question );
		};
		const render = ( index ) => {
			activeStep = Math.min( Math.max( index, 0 ), steps.length );
			const isResult = activeStep === steps.length;
			root.style.setProperty( '--needs-step-count', String( steps.length + 1 ) );
			root.style.setProperty( '--needs-progress', String( Math.min( answers.length / steps.length, 1 ) ) );
			back.disabled = activeStep === 0;
			count.textContent = isResult ? 'Gotowe · możesz zmienić odpowiedzi' : `Pytanie ${ activeStep + 1 } z ${ steps.length } · wybierz jedną odpowiedź`;
			renderNavigation( isResult );
			if ( isResult ) renderResult(); else renderQuestion( activeStep );
		};

		back.addEventListener( 'click', () => render( activeStep - 1 ) );
		render( 0 );
	} );
};

if ( document.readyState === 'loading' ) document.addEventListener( 'DOMContentLoaded', initializeNeedsGenerators, { once: true } ); else initializeNeedsGenerators();
