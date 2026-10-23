/**
 * Editor data model of the Needs Generator.
 *
 * A company is a reusable destination (name, colour, description and URL).
 * Questions contain their own answers. The first answer points to a company,
 * while answers in the two following questions can be filtered by company.
 */

export const BRANDS = [ 'agency', 'exp', 'warsaw', 'health', 'works', 'studio' ];

export const slugify = ( value ) => String( value || '' )
	.toLowerCase()
	.replace( /ł/g, 'l' )
	.normalize( 'NFD' )
	.replace( /[̀-ͯ]/g, '' )
	.replace( /[^a-z0-9]+/g, '-' )
	.replace( /^-+|-+$/g, '' );

export const blankCompany = () => ( {
	id: '', label: '', brand: 'agency', highlightText: '', highlightBrand: 'agency', highlightBold: true,
	description: '', imageId: 0, imageUrl: '', imageAlt: '', url: '',
} );
export const blankAction = () => ( { label: '', value: '', companyId: '' } );
export const blankQuestionOption = () => ( { label: '', value: '', visibleFor: '' } );

const optionData = ( option ) => ( typeof option === 'object' && option ? option : { label: option || '' } );

/** Reads both the current and older block structures into the question model. */
export const readModel = ( attributes ) => {
	const steps = Array.isArray( attributes.steps ) ? attributes.steps : [];
	const results = Array.isArray( attributes.results ) ? attributes.results : [];
	const groups = Array.isArray( attributes.serviceGroups ) ? attributes.serviceGroups : [];
	const oldFirstOptions = Array.isArray( steps[ 0 ]?.options ) ? steps[ 0 ].options : [];
	let companies = Array.isArray( attributes.companies ) ? attributes.companies : [];
	let isLegacy = false;

	if ( ! companies.length && oldFirstOptions.length ) {
		companies = oldFirstOptions.map( ( option ) => {
			const source = optionData( option );
			const id = source.serviceGroupId || source.resultId || slugify( source.label );
			const result = results.find( ( item ) => item.id === source.resultId ) || {};
			return { ...blankCompany(), id, label: source.companyLabel || source.label || '', brand: source.brand || 'agency', description: result.description || '', imageId: result.imageId || 0, imageUrl: result.imageUrl || '', imageAlt: result.imageAlt || '', url: result.contactUrl || source.nextUrl || '' };
		} );
		isLegacy = true;
	} else if ( ! companies.length && groups.length ) {
		companies = groups.map( ( group ) => ( { ...blankCompany(), id: group.id || slugify( group.title ), label: group.title || '', brand: BRANDS.includes( group.id ) ? group.id : 'agency' } ) );
		isLegacy = true;
	}

	const companyIds = companies.map( ( company ) => company.id );
	const actions = oldFirstOptions.length
		? oldFirstOptions.map( ( option ) => {
			const source = optionData( option );
			return { label: source.actionLabel || source.label || '', value: source.value || '', companyId: source.companyId || source.serviceGroupId || source.resultId || ( companyIds.includes( source.id ) ? source.id : '' ) };
		} )
		: companies.map( ( company ) => ( { label: company.label || '', value: '', companyId: company.id || '' } ) );
	if ( ! oldFirstOptions.length && companies.length ) isLegacy = true;

	const normalizeOptions = ( source ) => ( Array.isArray( source ) ? source : [] ).map( ( option ) => {
		const item = optionData( option );
		return { label: item.label || '', value: item.value || '', visibleFor: item.visibleFor || item.visibleForCompany || item.visibleForResultId || '' };
	} );
	const information = normalizeOptions( steps[ 1 ]?.options );
	let scope = normalizeOptions( steps[ 2 ]?.options );
	if ( ! scope.length ) {
		scope = companies.flatMap( ( company ) => ( company.scopes || [] ).map( ( item ) => {
			const option = optionData( item );
			return { label: option.label || '', value: option.value || '', visibleFor: company.id || '' };
		} ) );
		if ( scope.length ) isLegacy = true;
	}

	return { companies, questionOptions: [ actions, information, scope ], isLegacy };
};

/** Saves the standalone company list and all three lists of answers. */
export const writeModel = ( { companies, questionOptions, titles } ) => ( {
	companies,
	results: [],
	serviceGroups: [],
	steps: [
		{ title: titles[ 0 ] || '', options: questionOptions[ 0 ] || [] },
		{ title: titles[ 1 ] || '', options: questionOptions[ 1 ] || [] },
		{ title: titles[ 2 ] || '', options: questionOptions[ 2 ] || [] },
	],
} );

const oldTitles = [ 'Dla której firmy szukasz wsparcia?', 'Jaka informacja jest dla Ciebie najważniejsza?', 'Jakiego zakresu potrzebujesz?' ];
const newTitles = [ 'Co chcesz zrobić?', 'Skala zadania', 'Czego potrzebujesz?' ];
export const readTitles = ( attributes ) => [ 0, 1, 2 ].map( ( index ) => {
	const title = ( Array.isArray( attributes.steps ) ? attributes.steps[ index ]?.title : '' ) || '';
	return title === oldTitles[ index ] ? newTitles[ index ] : title;
} );
