import { registerBlockType } from '@wordpress/blocks';
import { InspectorControls, RichText, URLInputButton, useBlockProps } from '@wordpress/block-editor';
import { PanelBody, SelectControl, ToggleControl } from '@wordpress/components';
import { __ } from '@wordpress/i18n';

const domain = 'sparkagency';

const brands = [
	{ label: 'sparkAGENCY', value: 'agency' },
	{ label: 'sparkWORKS', value: 'works' },
	{ label: 'sparkEXP', value: 'exp' },
	{ label: 'sparkWARSAW', value: 'warsaw' },
	{ label: 'sparkHEALTH', value: 'health' },
	{ label: 'sparkSTUDIO', value: 'studio' },
];

const Edit = ( { attributes, setAttributes } ) => {
	const brand = brands.some( ( item ) => item.value === attributes.brand ) ? attributes.brand : 'agency';
	const theme = attributes.theme === 'light' ? 'light' : 'dark';
	const showPrimaryButton = attributes.showPrimaryButton !== false;
	const showSecondaryButton = attributes.showSecondaryButton !== false;
	const blockProps = useBlockProps( {
		className: 'spark-career-cta',
		'data-brand': brand,
		'data-theme': theme,
	} );

	return (
		<>
			<InspectorControls>
				<PanelBody title={ __( 'Wygląd sekcji', domain ) } initialOpen={ true }>
					<SelectControl
						label={ __( 'Marka', domain ) }
						value={ brand }
						options={ brands }
						onChange={ ( brandValue ) => setAttributes( { brand: brandValue } ) }
					/>
					<SelectControl
						label={ __( 'Motyw kolorystyczny', domain ) }
						value={ theme }
						options={ [
							{ label: __( 'Ciemny', domain ), value: 'dark' },
							{ label: __( 'Jasny', domain ), value: 'light' },
						] }
						onChange={ ( themeValue ) => setAttributes( { theme: themeValue } ) }
					/>
				</PanelBody>
				<PanelBody title={ __( 'Przyciski', domain ) } initialOpen={ true }>
					<ToggleControl
						label={ __( 'Pokaż przycisk główny', domain ) }
						checked={ showPrimaryButton }
						onChange={ ( value ) => setAttributes( { showPrimaryButton: value } ) }
					/>
					{ showPrimaryButton && (
						<URLInputButton
							label={ __( 'Adres przycisku głównego', domain ) }
							url={ attributes.primaryUrl || '' }
							onChange={ ( primaryUrl ) => setAttributes( { primaryUrl } ) }
						/>
					) }
					<ToggleControl
						label={ __( 'Pokaż drugi przycisk', domain ) }
						checked={ showSecondaryButton }
						onChange={ ( value ) => setAttributes( { showSecondaryButton: value } ) }
					/>
					{ showSecondaryButton && (
						<URLInputButton
							label={ __( 'Adres drugiego przycisku', domain ) }
							url={ attributes.secondaryUrl || '' }
							onChange={ ( secondaryUrl ) => setAttributes( { secondaryUrl } ) }
						/>
					) }
				</PanelBody>
			</InspectorControls>
			<section { ...blockProps }>
				<div className="spark-career-cta__wrap">
					<div className="spark-career-cta__band">
						<div className="spark-career-cta__copy">
							<RichText
								tagName="span"
								className="spark-career-cta__eyebrow"
								value={ attributes.eyebrow || '' }
								placeholder={ __( 'Kariera', domain ) }
								allowedFormats={ [] }
								onChange={ ( eyebrow ) => setAttributes( { eyebrow } ) }
							/>
							<RichText
								tagName="h2"
								value={ attributes.title || '' }
								placeholder={ __( 'Dołącz do zespołu', domain ) }
								allowedFormats={ [] }
								onChange={ ( title ) => setAttributes( { title } ) }
							/>
							<RichText
								tagName="p"
								className="spark-career-cta__description"
								value={ attributes.description || '' }
								placeholder={ __( 'Opis zachęcający do kontaktu', domain ) }
								onChange={ ( description ) => setAttributes( { description } ) }
							/>
						</div>
						{ ( showPrimaryButton || showSecondaryButton ) && (
							<div className="spark-career-cta__actions">
								{ showPrimaryButton && (
									<RichText
										tagName="span"
										className="spark-career-cta__button spark-career-cta__button--primary"
										value={ attributes.primaryLabel || '' }
										placeholder={ __( 'Zobacz oferty pracy', domain ) }
										allowedFormats={ [] }
										onChange={ ( primaryLabel ) => setAttributes( { primaryLabel } ) }
									/>
								) }
								{ showSecondaryButton && (
									<RichText
										tagName="span"
										className="spark-career-cta__button spark-career-cta__button--secondary"
										value={ attributes.secondaryLabel || '' }
										placeholder={ __( 'Napisz do nas', domain ) }
										allowedFormats={ [] }
										onChange={ ( secondaryLabel ) => setAttributes( { secondaryLabel } ) }
									/>
								) }
							</div>
						) }
					</div>
				</div>
			</section>
		</>
	);
};

registerBlockType( 'sparkagency/career-cta', {
	edit: Edit,
	save: () => null,
} );
