/**
 * Editor data model of the Needs Generator.
 *
 * Mirrors the migration in template-parts/blocks/needs-generator.php:
 * older blocks kept companies in steps[0].options, results and serviceGroups.
 */

export const BRANDS = [ 'agency', 'exp', 'warsaw', 'health', 'works', 'studio' ];

export const slugify = ( value ) => String( value || '' )
	.toLowerCase()
	.replace( /ł/g, 'l' )
	.normalize( 'NFD' )
	.replace( /[̀-ͯ]/g, '' )
	.replace( /[^a-z0-9]+/g, '-' )
	.replace( /^-+|-+$/g, '' );

export const blankScope = () => ( { label: '', value: '' } );
export const blankCompany = () => ( { id: '', label: '', brand: 'agency', description: '', imageId: 0, imageUrl: '', imageAlt: '', url: '', scopes: [ blankScope() ] } );
export const blankInformationOption = () => ( { label: '', value: '', visibleFor: '' } );

const scopeData = ( scope ) => ( typeof scope === 'object' && scope ? { label: scope.label || '', value: scope.value || '' } : { ...blankScope(), label: scope || '' } );

/** Companies and step 2 options, migrated from the legacy attributes when needed. */
export const readModel = ( attributes ) => {
	const steps = Array.isArray( attributes.steps ) ? attributes.steps : [];
	const rawInformation = Array.isArray( steps[ 1 ]?.options ) ? steps[ 1 ].options : [];

	if ( Array.isArray( attributes.companies ) && attributes.companies.length ) {
		return { companies: attributes.companies, informationOptions: rawInformation, isLegacy: false };
	}

	const results = Array.isArray( attributes.results ) ? attributes.results : [];
	const groups = Array.isArray( attributes.serviceGroups ) ? attributes.serviceGroups : [];
	const firstOptions = Array.isArray( steps[ 0 ]?.options ) ? steps[ 0 ].options : [];
	const legacyIds = {};
	let companies;

	if ( firstOptions.length ) {
		companies = firstOptions.map( ( option ) => {
			const id = option.serviceGroupId || option.resultId || slugify( option.label );
			const result = results.find( ( item ) => item.id === option.resultId ) || {};
			const group = groups.find( ( item ) => item.id === ( option.serviceGroupId || option.resultId ) ) || {};
			if ( option.resultId ) legacyIds[ option.resultId ] = id;
			return {
				...blankCompany(),
				id,
				label: option.label || '',
				brand: option.brand || 'agency',
				description: result.description || '',
				imageId: result.imageId || 0,
				imageUrl: result.imageUrl || '',
				imageAlt: result.imageAlt || '',
				url: result.contactUrl || option.nextUrl || '',
				scopes: ( group.categories || [] ).map( scopeData ),
			};
		} );
	} else {
		companies = groups.map( ( group ) => ( {
			...blankCompany(),
			id: group.id || slugify( group.title ),
			label: group.title || '',
			brand: BRANDS.includes( group.id ) ? group.id : 'agency',
			scopes: ( group.categories || [] ).map( scopeData ),
		} ) );
	}

	const informationOptions = rawInformation.map( ( option ) => ( {
		label: option.label || '',
		value: option.value || '',
		visibleFor: option.visibleFor || legacyIds[ option.visibleForResultId ] || option.visibleForResultId || '',
	} ) );

	return { companies, informationOptions, isLegacy: true };
};

/**
 * Attributes to save after any change: the new model plus emptied legacy
 * fields, so the next render no longer migrates.
 */
export const writeModel = ( { companies, informationOptions, titles } ) => ( {
	companies,
	results: [],
	serviceGroups: [],
	steps: [
		{ title: titles[ 0 ] || '' },
		{ title: titles[ 1 ] || '', options: informationOptions },
		{ title: titles[ 2 ] || '' },
	],
} );

export const readTitles = ( attributes ) => [ 0, 1, 2 ].map( ( index ) => ( Array.isArray( attributes.steps ) ? attributes.steps[ index ]?.title : '' ) || '' );
