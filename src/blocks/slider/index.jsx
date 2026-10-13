import { registerBlockType } from '@wordpress/blocks';
import { InspectorControls, useBlockProps } from '@wordpress/block-editor';
import { Button, PanelBody, TextControl } from '@wordpress/components';

const Edit = ( { attributes, setAttributes } ) => {
	const words = Array.isArray( attributes.words ) ? attributes.words : [];
	const updateWords = ( nextWords ) => setAttributes( { words: nextWords } );
	const moveWord = ( index, direction ) => {
		const target = index + direction;
		if ( target < 0 || target >= words.length ) {
			return;
		}
		const nextWords = [ ...words ];
		[ nextWords[ index ], nextWords[ target ] ] = [ nextWords[ target ], nextWords[ index ] ];
		updateWords( nextWords );
	};

	return (
		<>
			<InspectorControls>
				<PanelBody title="Hasła slidera" initialOpen>
					{ words.map( ( word, index ) => (
						<div key={ index } style={ { display: 'flex', gap: '4px', alignItems: 'flex-end', marginBottom: '8px' } }>
							<div style={ { flex: 1 } }>
								<TextControl
									label={ `Hasło ${ index + 1 }` }
									value={ word }
									__nextHasNoMarginBottom
									onChange={ ( value ) => updateWords( words.map( ( current, position ) => position === index ? value : current ) ) }
								/>
							</div>
							<Button icon="arrow-up-alt2" label="W górę" disabled={ index === 0 } onClick={ () => moveWord( index, -1 ) } />
							<Button icon="arrow-down-alt2" label="W dół" disabled={ index === words.length - 1 } onClick={ () => moveWord( index, 1 ) } />
							<Button icon="trash" label="Usuń" isDestructive onClick={ () => updateWords( words.filter( ( current, position ) => position !== index ) ) } />
						</div>
					) ) }
					<Button variant="secondary" onClick={ () => updateWords( [ ...words, '' ] ) }>Dodaj hasło</Button>
				</PanelBody>
			</InspectorControls>
			<div { ...useBlockProps( { className: 'dev-spark-marquee dev-spark-marquee--editor' } ) }>
				{ words.filter( Boolean ).length
					? words.filter( Boolean ).map( ( word, index ) => <span key={ index } className="dev-spark-marquee__word">{ word }</span> )
					: <em>Dodaj hasła w panelu bocznym.</em> }
			</div>
		</>
	);
};

registerBlockType( 'sparkagency/slider', {
	edit: Edit,
	save: () => null,
} );
