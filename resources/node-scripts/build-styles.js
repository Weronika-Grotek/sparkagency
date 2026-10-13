const fs = require( 'fs' );
const path = require( 'path' );
const sass = require( 'sass' );

const root = path.resolve( __dirname, '..', '..' );
const expanded = process.argv.includes( '--expanded' );
const watch = process.argv.includes( '--watch' );
const blockRoot = path.join( root, 'src', 'blocks' );

const compile = ( source, destination ) => {
	const result = sass.compile( source, {
		style: expanded ? 'expanded' : 'compressed',
		sourceMap: expanded,
	});

	fs.mkdirSync( path.dirname( destination ), { recursive: true } );
	fs.writeFileSync( destination, result.css );
	console.log( `Compiled ${ path.relative( root, source ) }` );
};

const build = () => {
	compile(
		path.join( root, 'assets', 'scss', 'main.scss' ),
		path.join( root, 'assets', 'css', 'main.css' )
	);

	if ( ! fs.existsSync( blockRoot ) ) {
		return;
	}

	for ( const entry of fs.readdirSync( blockRoot, { withFileTypes: true } ) ) {
		if ( ! entry.isDirectory() ) {
			continue;
		}

		const source = path.join( blockRoot, entry.name, 'style.scss' );
		if ( fs.existsSync( source ) ) {
			compile( source, path.join( root, 'assets', 'css', 'blocks', `spark-${ entry.name }.css` ) );
		}
	}
};

build();

if ( watch ) {
	let timer;
	const rebuild = () => {
		clearTimeout( timer );
		timer = setTimeout( () => {
			try {
				build();
			} catch ( error ) {
				console.error( error.message );
			}
		}, 100 );
	};

	fs.watch( path.join( root, 'assets', 'scss' ), { recursive: true }, rebuild );
	fs.watch( blockRoot, { recursive: true }, rebuild );
	console.log( 'Watching SCSS sources…' );
}
