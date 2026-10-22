/**
 * Shared contract between the Needs Generator and the contact forms.
 *
 * The generator appends these GET parameters to the company URL:
 * spark_company, spark_information, spark_scope (exact values),
 * the same keys with a _label suffix (texts shown to the user)
 * and spark_return (same-origin URL of the generator).
 */

export const NEEDS_FIELDS = [
	{ key: 'company', label: 'Firma' },
	{ key: 'information', label: 'Informacja' },
	{ key: 'scope', label: 'Zakres' },
];

const VALUE_LIMIT = 100;
const LABEL_LIMIT = 150;

const clean = ( value, limit ) => ( typeof value === 'string' ? value.trim().slice( 0, limit ) : '' );

/** Build the query parameters for a set of { key: { value, label } } answers. */
export const needsToParams = ( needs, returnUrl = '' ) => {
	const params = new URLSearchParams();
	NEEDS_FIELDS.forEach( ( { key } ) => {
		const answer = needs[ key ];
		if ( ! answer?.value ) return;
		params.set( `spark_${ key }`, clean( answer.value, VALUE_LIMIT ) );
		params.set( `spark_${ key }_label`, clean( answer.label || answer.value, LABEL_LIMIT ) );
	} );
	if ( returnUrl && [ ...params.keys() ].length ) params.set( 'spark_return', returnUrl );
	return params;
};

/** Read generator answers from a query string; returns null when there are none. */
export const readNeeds = ( search = window.location.search ) => {
	const params = new URLSearchParams( search );
	// After a successful submission the form page reloads with the same query; do not prefill again.
	if ( params.get( 'spark_contact_status' ) === 'success' ) return null;
	const needs = {};
	NEEDS_FIELDS.forEach( ( { key } ) => {
		const value = clean( params.get( `spark_${ key }` ), VALUE_LIMIT );
		if ( value ) needs[ key ] = { value, label: clean( params.get( `spark_${ key }_label` ), LABEL_LIMIT ) || value };
	} );
	if ( ! Object.keys( needs ).length ) return null;

	const returnUrl = safeSameOriginUrl( params.get( 'spark_return' ) );
	return { ...needs, returnUrl };
};

/** Accept only http(s) URLs on the current origin. */
export const safeSameOriginUrl = ( value ) => {
	if ( ! value ) return '';
	try {
		const url = new URL( value, window.location.href );
		return url.origin === window.location.origin ? url.href : '';
	} catch ( error ) {
		return '';
	}
};

/** Append generator parameters to any http(s) URL, internal or external. */
export const withNeeds = ( value, params ) => {
	if ( ! value ) return '';
	try {
		const url = new URL( value, window.location.href );
		if ( ! [ 'http:', 'https:' ].includes( url.protocol ) ) return '';
		params.forEach( ( paramValue, key ) => url.searchParams.set( key, paramValue ) );
		return url.href;
	} catch ( error ) {
		return '';
	}
};

const setHidden = ( form, name, value ) => {
	let field = form.querySelector( `input[type="hidden"][name="${ name }"]` );
	if ( ! field ) {
		field = document.createElement( 'input' );
		field.type = 'hidden';
		field.name = name;
		form.append( field );
	}
	field.value = value;
};

/** Render a summary list of the answers with an optional "change" link. */
export const createNeedsSummary = ( needs, className = 'spark-needs-summary' ) => {
	const wrapper = document.createElement( 'div' );
	wrapper.className = className;
	wrapper.setAttribute( 'data-needs-summary', '' );
	const heading = document.createElement( 'p' );
	heading.className = `${ className }__title`;
	heading.textContent = 'Odpowiedzi z generatora potrzeb';
	const list = document.createElement( 'dl' );
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
	wrapper.append( heading, list );
	if ( needs.returnUrl ) {
		const link = document.createElement( 'a' );
		link.className = `${ className }__change`;
		link.href = needs.returnUrl;
		link.textContent = 'Zmień odpowiedzi';
		wrapper.append( link );
	}
	return wrapper;
};

/**
 * Prefill a contact form: hidden spark_need_* fields, the topic matching
 * the company and, unless a wrapper already shows it, a visible summary.
 * Returns the answers so the caller can resync its own state.
 */
export const applyNeedsToForm = ( root, form, needs = readNeeds() ) => {
	if ( ! needs || ! form ) return null;
	NEEDS_FIELDS.forEach( ( { key } ) => {
		setHidden( form, `spark_need_${ key }`, needs[ key ]?.value || '' );
		setHidden( form, `spark_need_${ key }_label`, needs[ key ]?.label || '' );
	} );

	const companyKey = needs.company?.value;
	if ( companyKey ) {
		root.querySelectorAll( '[data-topic-key]' ).forEach( ( button ) => {
			if ( button.dataset.topicKey === companyKey ) button.setAttribute( 'aria-pressed', 'true' );
		} );
	}

	const insideHandoff = Boolean( root.closest( '[data-spark-contact-handoff]' ) );
	if ( ! insideHandoff && ! root.querySelector( '[data-needs-summary]' ) ) {
		form.before( createNeedsSummary( needs ) );
	}
	return needs;
};
