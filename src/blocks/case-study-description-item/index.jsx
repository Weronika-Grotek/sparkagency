import { registerBlockType } from '@wordpress/blocks';
import { InnerBlocks, InspectorControls, RichText, useBlockProps, useInnerBlocksProps } from '@wordpress/block-editor';
import { Button, PanelBody, RangeControl, TextControl } from '@wordpress/components';
import { __ } from '@wordpress/i18n';

const ALLOWED_BLOCKS = [
	'core/paragraph',
	'core/heading',
	'core/list',
	'core/image',
	'core/gallery',
	'core/video',
	'core/quote',
	'core/group',
	'core/columns',
	'core/buttons',
];

const Edit = ( { attributes, setAttributes } ) => {
	const blockProps = useBlockProps( { className: 'spark-case-study-description-item' } );
	const legacyMetric = {
		value: attributes.counterValue ?? 0,
		prefix: attributes.counterPrefix || '',
		suffix: attributes.counterSuffix || '',
		label: attributes.counterLabel || '',
		duration: attributes.counterDuration || 1200,
	};
	const metrics = Array.isArray( attributes.metrics ) && attributes.metrics.length > 0
		? attributes.metrics
		: ( attributes.showCounter ? [ legacyMetric ] : [] );
	const updateMetric = ( index, changes ) => {
		const nextMetrics = metrics.map( ( metric, metricIndex ) => metricIndex === index ? { ...metric, ...changes } : metric );
		setAttributes( { metrics: nextMetrics, showCounter: false } );
	};
	const addMetric = () => {
		setAttributes( { metrics: [ ...metrics, { value: 0, prefix: '', suffix: '', label: '', duration: 1200 } ], showCounter: false } );
	};
	const innerBlocksProps = useInnerBlocksProps( { className: 'spark-case-study-description-item__body' }, {
		allowedBlocks: ALLOWED_BLOCKS,
		templateLock: false,
		renderAppender: InnerBlocks.ButtonBlockAppender,
	} );

	return (
		<section { ...blockProps }>
			<InspectorControls>
				<PanelBody title={ __( 'Liczby i wartości', 'sparkagency' ) } initialOpen={ true }>
					{ metrics.map( ( metric, index ) => <div className="spark-case-study-description-item__metric-editor" key={ index }>
						<strong>{ __( 'Liczba', 'sparkagency' ) } { index + 1 }</strong>
						<TextControl label={ __( 'Wartość', 'sparkagency' ) } type="number" step="1" value={ String( metric.value ?? 0 ) } onChange={ ( value ) => updateMetric( index, { value: Number.parseInt( value, 10 ) || 0 } ) } />
						<TextControl label={ __( 'Tekst przed liczbą', 'sparkagency' ) } value={ metric.prefix || '' } onChange={ ( prefix ) => updateMetric( index, { prefix } ) } />
						<TextControl label={ __( 'Tekst po liczbie', 'sparkagency' ) } value={ metric.suffix || '' } onChange={ ( suffix ) => updateMetric( index, { suffix } ) } />
						<TextControl label={ __( 'Opis', 'sparkagency' ) } value={ metric.label || '' } onChange={ ( label ) => updateMetric( index, { label } ) } />
						<RangeControl label={ __( 'Czas animacji (ms)', 'sparkagency' ) } value={ metric.duration || 1200 } onChange={ ( duration ) => updateMetric( index, { duration } ) } min={ 300 } max={ 5000 } step={ 100 } />
						<Button variant="tertiary" isDestructive onClick={ () => setAttributes( { metrics: metrics.filter( ( _, metricIndex ) => metricIndex !== index ), showCounter: false } ) }>{ __( 'Usuń liczbę', 'sparkagency' ) }</Button>
					</div> ) }
					<Button variant="secondary" onClick={ addMetric }>{ __( 'Dodaj liczbę', 'sparkagency' ) }</Button>
				</PanelBody>
			</InspectorControls>
			<RichText
				tagName="h2"
				className="spark-case-study-description-item__title"
				value={ attributes.title || '' }
				placeholder={ __( 'Tytuł sekcji', 'sparkagency' ) }
				onChange={ ( title ) => setAttributes( { title } ) }
			/>
			<div { ...innerBlocksProps } />
			{ metrics.length > 0 && <div className="spark-case-study-description-item__metrics">
				{ metrics.map( ( metric, index ) => <div className="spark-case-study-description-item__metric" key={ index }>
					<p className="spark-case-study-description-item__counter-value"><span>{ metric.prefix || '' }</span>{ Number.parseInt( metric.value, 10 ) || 0 }<span>{ metric.suffix || '' }</span></p>
					<p className="spark-case-study-description-item__counter-label">{ metric.label || __( 'Opis liczby', 'sparkagency' ) }</p>
				</div> ) }
			</div> }
		</section>
	);
};

registerBlockType( 'sparkagency/case-study-description-item', { edit: Edit, save: () => <InnerBlocks.Content /> } );
