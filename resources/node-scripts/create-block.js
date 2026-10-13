const fs = require( 'fs' );
const path = require( 'path' );

const root = path.resolve( __dirname, '..', '..' );
const argumentsMap = Object.fromEntries(
	process.argv.slice( 2 ).map( ( argument ) => {
		const [ key, value = '' ] = argument.replace( /^--/, '' ).split( '=' );
		return [ key, value ];
	} )
);
const slug = ( argumentsMap.slug || '' ).toLowerCase().replace( /[^a-z0-9]+/g, '-' ).replace( /^-|-$/g, '' );
const title = argumentsMap.title || slug.replace( /(^|-)\w/g, ( match ) => match.replace( '-', ' ' ).toUpperCase() );
const description = argumentsMap.description || `Sekcja ${ title } dla Spark Agency.`;

if ( ! slug ) {
	console.error( 'Użycie: npm run create:block -- --slug=case-study --title="Case study"' );
	process.exit( 1 );
}

const blockDirectory = path.join( root, 'src', 'blocks', slug );
if ( fs.existsSync( blockDirectory ) ) {
	console.error( `Blok „${ slug }” już istnieje.` );
	process.exit( 1 );
}

const replacements = {
	'{{SLUG}}': slug,
	'{{TITLE}}': title,
	'{{DESCRIPTION}}': description,
};
const render = ( content ) => Object.entries( replacements ).reduce(
	( output, [ token, value ] ) => output.replaceAll( token, value ),
	content
);
const copyTemplate = ( source, destination ) => {
	const content = fs.readFileSync( source, 'utf8' );
	fs.mkdirSync( path.dirname( destination ), { recursive: true } );
	fs.writeFileSync( destination, render( content ) );
};

const templateDirectory = path.join( root, 'resources', 'block-templates', 'standard' );
fs.mkdirSync( blockDirectory, { recursive: true } );

for ( const filename of [ 'block.json', 'index.jsx', 'style.scss' ] ) {
	copyTemplate( path.join( templateDirectory, filename ), path.join( blockDirectory, filename ) );
}
copyTemplate( path.join( templateDirectory, 'render.php' ), path.join( root, 'template-parts', 'blocks', `${ slug }.php` ) );

console.log( `Utworzono blok sparkagency/${ slug }.` );
