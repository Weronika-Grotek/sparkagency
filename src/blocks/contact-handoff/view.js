const initializeContactHandoffs = () => {
	document.querySelectorAll( '[data-spark-contact-handoff]' ).forEach( ( root ) => {
		if ( root.dataset.initialized === 'true' ) return;
		root.dataset.initialized = 'true';
		const rawAnswers = new URLSearchParams( window.location.search ).get( 'spark_answers' ) || '';
		const result = new URLSearchParams( window.location.search ).get( 'result' ) || '';
		const resultName = new URLSearchParams( window.location.search ).get( 'spark_result_name' ) || '';
		let answers = [];
		try {
			const decoded = JSON.parse( rawAnswers );
			if ( Array.isArray( decoded ) ) {
				answers = decoded.filter( ( answer ) => answer && typeof answer.question === 'string' && typeof answer.label === 'string' ).slice( 0, 12 );
			}
		} catch ( error ) {
			answers = [];
		}

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
			} );
		}
		if ( resultName && resultLabel ) {
			resultLabel.hidden = false;
			resultLabel.textContent = `Rozmowę rozpoczniesz ze: ${ resultName }`;
		}

		root.querySelectorAll( 'input[name="spark_answers"], textarea[name="spark_answers"]' ).forEach( ( field ) => { field.value = rawAnswers; } );
		root.querySelectorAll( 'input[name="result"], textarea[name="result"]' ).forEach( ( field ) => { field.value = result; } );
	} );
};

if ( document.readyState === 'loading' ) document.addEventListener( 'DOMContentLoaded', initializeContactHandoffs, { once: true } ); else initializeContactHandoffs();
