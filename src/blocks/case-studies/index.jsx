import { registerBlockType } from '@wordpress/blocks';
import { InspectorControls, RichText, useBlockProps } from '@wordpress/block-editor';
import { ColorPalette, ColorPicker, PanelBody, RangeControl, SelectControl, TextControl, ToggleControl } from '@wordpress/components';
import { __ } from '@wordpress/i18n';

const Edit = ( { attributes, setAttributes } ) => (
	<>
		<InspectorControls>
			<PanelBody title={ __( 'Źródło realizacji', 'sparkagency' ) } initialOpen>
				<TextControl
					label={ __( 'Nazwa typu wpisu', 'sparkagency' ) }
					help={ __( 'Domyślnie: case-study. Użyj case_study, jeśli taki slug ma Twój typ wpisu.', 'sparkagency' ) }
					value={ attributes.postType }
					onChange={ ( postType ) => setAttributes( { postType } ) }
				/>
				<RangeControl label={ __( 'Liczba realizacji', 'sparkagency' ) } value={ attributes.postsPerPage } onChange={ ( postsPerPage ) => setAttributes( { postsPerPage } ) } min={ 1 } max={ 12 } />
				<TextControl label={ __( 'Taksonomia marek (opcjonalnie)', 'sparkagency' ) } value={ attributes.brandTaxonomy } onChange={ ( brandTaxonomy ) => setAttributes( { brandTaxonomy } ) } />
			</PanelBody>
			<PanelBody title={ __( 'Kolor postępu', 'sparkagency' ) } initialOpen={ false }>
				<p>{ __( 'Domyślnie kolor zmienia się wraz z marką aktualnej realizacji. Wybierz kolor, aby ustawić własny.', 'sparkagency' ) }</p>
				<ColorPalette
					value={ attributes.progressColor || undefined }
					clearable={ true }
					disableCustomColors={ false }
					enableAlpha={ false }
					onChange={ ( progressColor ) => setAttributes( { progressColor: progressColor || '' } ) }
				/>
			</PanelBody>
			<PanelBody title={ __( 'Pola wpisu', 'sparkagency' ) } initialOpen={ false }>
				<TextControl label={ __( 'Meta: klient', 'sparkagency' ) } value={ attributes.clientMetaKey } onChange={ ( clientMetaKey ) => setAttributes( { clientMetaKey } ) } />
				<TextControl label={ __( 'Meta: wartość rezultatu', 'sparkagency' ) } value={ attributes.metricValueMetaKey } onChange={ ( metricValueMetaKey ) => setAttributes( { metricValueMetaKey } ) } />
				<TextControl label={ __( 'Meta: opis rezultatu', 'sparkagency' ) } value={ attributes.metricLabelMetaKey } onChange={ ( metricLabelMetaKey ) => setAttributes( { metricLabelMetaKey } ) } />
			</PanelBody>
			<PanelBody title={ __( 'Link na końcu listy', 'sparkagency' ) } initialOpen={ false }>
				<TextControl label={ __( 'URL: wszystkie realizacje', 'sparkagency' ) } type="url" value={ attributes.allUrl } onChange={ ( allUrl ) => setAttributes( { allUrl } ) } help={ __( 'Puste pole kieruje do archiwum case studies.', 'sparkagency' ) } />
				<TextControl label={ __( 'Etykieta: wszystkie realizacje', 'sparkagency' ) } value={ attributes.allLabel } onChange={ ( allLabel ) => setAttributes( { allLabel } ) } />
				<SelectControl
					label={ __( 'Kolor końcowej karty', 'sparkagency' ) }
					value={ attributes.allCardColor || 'default' }
					options={ [
						{ label: __( 'Domyślny', 'sparkagency' ), value: 'default' },
						{ label: 'Agency', value: 'agency' },
						{ label: 'Works', value: 'works' },
						{ label: 'EXP', value: 'exp' },
						{ label: 'Warsaw', value: 'warsaw' },
						{ label: 'Health', value: 'health' },
						{ label: 'Studio', value: 'studio' },
						{ label: __( 'Własny kolor', 'sparkagency' ), value: 'custom' },
					] }
					onChange={ ( allCardColor ) => setAttributes( { allCardColor } ) }
				/>
				{ attributes.allCardColor === 'custom' && (
					<div style={ { marginTop: '12px' } }>
						<ColorPicker
							color={ attributes.allCardCustomColor || '#01B57C' }
							enableAlpha={ false }
							onChangeComplete={ ( value ) => setAttributes( { allCardCustomColor: value.hex.toUpperCase() } ) }
						/>
					</div>
				) }
			</PanelBody>
			<PanelBody title={ __( 'Przycisk pod kartami', 'sparkagency' ) } initialOpen={ false }>
				<TextControl label={ __( 'Adres URL', 'sparkagency' ) } type="url" value={ attributes.contactUrl } onChange={ ( contactUrl ) => setAttributes( { contactUrl } ) } help={ __( 'Pozostaw puste, aby ukryć przycisk.', 'sparkagency' ) } />
				<TextControl label={ __( 'Tekst przycisku', 'sparkagency' ) } value={ attributes.contactLabel } onChange={ ( contactLabel ) => setAttributes( { contactLabel } ) } />
				<ToggleControl label={ __( 'Otwórz w nowej karcie', 'sparkagency' ) } checked={ attributes.contactNewTab } onChange={ ( contactNewTab ) => setAttributes( { contactNewTab } ) } />
			</PanelBody>
		</InspectorControls>
		<section { ...useBlockProps( { className: `spark-case-studies spark-case-studies--editor${ attributes.contactUrl && attributes.contactLabel ? ' has-case-studies-contact' : '' }` } ) }>
			<header className="spark-case-studies__header">
				<RichText tagName="p" className="spark-case-studies__eyebrow" value={ attributes.eyebrow } placeholder={ __( 'Numer sekcji', 'sparkagency' ) } onChange={ ( eyebrow ) => setAttributes( { eyebrow } ) } />
				<div>
					<RichText tagName="h2" value={ attributes.title } placeholder={ __( 'Tytuł sekcji', 'sparkagency' ) } onChange={ ( title ) => setAttributes( { title } ) } />
					<RichText tagName="p" className="spark-case-studies__lede" value={ attributes.lede } placeholder={ __( 'Wprowadzenie', 'sparkagency' ) } onChange={ ( lede ) => setAttributes( { lede } ) } />
				</div>
			</header>
			<div className="spark-case-studies__editor-note">
				<strong>{ __( 'Podgląd pobiera wpisy na stronie.', 'sparkagency' ) }</strong>
				<span>{ __( 'Po zapisie blok wyświetli opublikowane wpisy typu:', 'sparkagency' ) } <code>{ attributes.postType || 'case-study' }</code>.</span>
			</div>
			{ attributes.contactUrl && attributes.contactLabel && <footer className="spark-case-studies__footer"><span className="spark-case-studies__contact"><RichText tagName="span" value={ attributes.contactLabel } placeholder={ __( 'Tekst przycisku', 'sparkagency' ) } onChange={ ( contactLabel ) => setAttributes( { contactLabel } ) } /><span className="spark-case-studies__contact-icon" aria-hidden="true">→</span></span></footer> }
		</section>
	</>
);

registerBlockType( 'sparkagency/case-studies', { edit: Edit, save: () => null } );
