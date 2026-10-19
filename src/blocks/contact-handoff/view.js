const initializeContactHandoffs = () => {
	document.querySelectorAll( '[data-spark-contact-handoff]' ).forEach( ( root ) => {
		if ( root.dataset.initialized === 'true' ) return;
		root.dataset.initialized = 'true';
		const params = new URLSearchParams( window.location.search );
		const rawAnswers = params.get( 'spark_answers' ) || '';
		const result = params.get( 'result' ) || '';
		const resultName = params.get( 'spark_result_name' ) || '';
		let answers = [];
		try {
			const decoded = JSON.parse( rawAnswers );
			if ( Array.isArray( decoded ) ) answers = decoded.filter( ( answer ) => answer && typeof answer.question === 'string' && typeof answer.label === 'string' && typeof answer.param === 'string' ).slice( 0, 12 );
		} catch ( error ) {
			answers = [];
		}

		const setFieldValue = ( field, value ) => {
			if ( field.disabled || ! value ) return;
			const type = ( field.type || '' ).toLowerCase();
			if ( type === 'radio' || type === 'checkbox' ) {
				field.checked = field.value === value;
			} else if ( field.tagName === 'SELECT' ) {
				field.value = value;
			} else if ( ! [ 'submit', 'button', 'reset', 'file' ].includes( type ) ) {
				field.value = value;
			}
			field.dispatchEvent( new Event( 'input', { bubbles: true } ) );
			field.dispatchEvent( new Event( 'change', { bubbles: true } ) );
		};
		const fillFields = ( name, value ) => {
			if ( ! name || ! value ) return;
			root.querySelectorAll( '[name]' ).forEach( ( field ) => {
				if ( field.getAttribute( 'name' ) === name ) setFieldValue( field, value );
			} );
		};

		const summary = root.querySelector( '[data-contact-summary]' );
		const list = root.querySelector( '[data-contact-list]' );
		const empty = root.querySelector( '[data-contact-empty]' );
		const resultLabel = root.querySelector( '[data-contact-result]' );
		if ( answers.length && summary && list ) {
			summary.hidden = false;
			empty.hidden = true;
			answers.forEach( ( answer ) => {
				const row = document.createElement( 'div' );
				const question = document.createElement( 'dt' );
				const response = document.createElement( 'dd' );
				question.textContent = answer.question;
				response.textContent = answer.label;
				row.append( question, response );
				list.append( row );
				fillFields( answer.param, answer.value || answer.label );
			} );
		}
		if ( resultName && resultLabel ) {
			resultLabel.hidden = false;
			resultLabel.textContent = `Rozmowę rozpoczniesz ze: ${ resultName }`;
		}

		root.querySelectorAll( 'input[name="spark_answers"], textarea[name="spark_answers"]' ).forEach( ( field ) => { field.value = rawAnswers; } );
		root.querySelectorAll( 'input[name="result"], textarea[name="result"]' ).forEach( ( field ) => { field.value = result; } );
		[ 'spark_company', 'spark_company_id', 'spark_information', 'spark_category', 'spark_category_name', 'spark_result_name' ].forEach( ( name ) => fillFields( name, params.get( name ) || '' ) );
	} );
};

if ( document.readyState === 'loading' ) document.addEventListener( 'DOMContentLoaded', initializeContactHandoffs, { once: true } ); else initializeContactHandoffs();
