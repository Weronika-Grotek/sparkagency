import { registerBlockType } from '@wordpress/blocks';
import { InspectorControls, useBlockProps } from '@wordpress/block-editor';
import { PanelBody, TextControl } from '@wordpress/components';
import { useSelect } from '@wordpress/data';
import { __ } from '@wordpress/i18n';

const domain = 'sparkagency';

const Edit = ( { attributes, setAttributes } ) => {
	const title = useSelect( ( select ) => select( 'core/editor' ).getEditedPostAttribute( 'title' ), [] );
	return <>
		<InspectorControls><PanelBody title={ __( 'Teksty nagłówka', domain ) }>
			{ [ [ 'homeLabel', __( 'Link do strony głównej', domain ) ], [ 'careerLabel', __( 'Link do kariery', domain ) ], [ 'applyLabel', __( 'Przycisk aplikowania', domain ) ], [ 'allOffersLabel', __( 'Link do wszystkich ofert', domain ) ] ].map( ( [ key, label ] ) => <TextControl key={ key } label={ label } value={ attributes[ key ] || '' } onChange={ ( value ) => setAttributes( { [ key ]: value } ) } /> ) }
		</PanelBody></InspectorControls>
		<header { ...useBlockProps( { className: 'spark-career__job-hero spark-career__job-hero--editor' } ) }>
			<div className="spark-career__wrap spark-career__job-hero-wrap">
				<p className="spark-career__crumbs">{ attributes.homeLabel || __( 'Start', domain ) } / { attributes.careerLabel || __( 'Kariera', domain ) }</p>
				<h1 className="spark-career__job-hero-title">{ title || __( 'Nazwa stanowiska', domain ) }</h1>
				<p className="spark-career__job-hero-lead">{ __( 'Firma, status i szczegóły oferty pobierane są z ustawień bieżącej oferty.', domain ) }</p>
				<div className="spark-career__job-hero-actions"><span className="spark-career__button">{ attributes.applyLabel || __( 'Aplikuj', domain ) } →</span><span className="spark-career__button spark-career__button--ghost">{ attributes.allOffersLabel || __( 'Wszystkie oferty', domain ) }</span></div>
			</div>
		</header>
	</>;
};

registerBlockType( 'sparkagency/career-single-header', { edit: Edit, save: () => null } );
