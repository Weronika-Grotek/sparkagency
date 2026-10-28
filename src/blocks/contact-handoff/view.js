import { NEEDS_FIELDS, readNeeds } from '../_shared/needs';

/**
 * Shows the Needs Generator answers above the nested contact form.
 * The form itself receives the hidden spark_need_* fields from its own view script.
 */
const initializeContactHandoffs = () => {
	document.querySelectorAll( '[data-spark-contact-handoff]' ).forEach( ( root, index ) => {
		if ( root.dataset.initialized === 'true' ) return;
		root.dataset.initialized = 'true';

		const summary = root.querySelector( '[data-contact-summary]' );
		const list = root.querySelector( '[data-contact-list]' );
		const empty = root.querySelector( '[data-contact-empty]' );
		const change = root.querySelector( '[data-contact-change]' );
		const params = new URLSearchParams( window.location.search );
		const needs = readNeeds();

		// After a successful submission the form shows its own notice.
		if ( params.get( 'spark_contact_status' ) === 'success' && empty ) empty.hidden = true;
		if ( ! needs || ! summary || ! list ) return;

		summary.hidden = false;
		if ( empty ) empty.hidden = true;
		list.replaceChildren();
		NEEDS_FIELDS.forEach( ( { key, label } ) => {
			if ( ! needs[ key ] ) return;
			const row = document.createElement( 'div' );
			const term = document.createElement( 'dt' );
			const description = document.createElement( 'dd' );
			term.textContent = label;
			description.textContent = needs[ key ].label;
			row.append( term, description );
			list.append( row );
		} );
		if ( change && needs.returnUrl ) {
			change.hidden = false;
			change.href = needs.returnUrl;
		}

		// Visitors arrive from the generator, possibly on another site: bring the first handoff into view.
		if ( index === 0 && ! window.location.hash ) root.scrollIntoView( { block: 'start' } );
	} );
};

if ( document.readyState === 'loading' ) document.addEventListener( 'DOMContentLoaded', initializeContactHandoffs, { once: true } ); else initializeContactHandoffs();
