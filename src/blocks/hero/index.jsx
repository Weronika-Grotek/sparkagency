import { registerBlockType } from '@wordpress/blocks';
import { ColorPalette, InnerBlocks, InspectorControls, MediaUpload, MediaUploadCheck, useBlockProps } from '@wordpress/block-editor';
import { Button, PanelBody, SelectControl, ToggleControl } from '@wordpress/components';
const particleColors = [{
  name: 'Biały',
  color: '#FFFFFF'
}, {
  name: 'Zielony',
  color: '#01B57C'
}, {
  name: 'Niebieski',
  color: '#43A0ED'
}, {
  name: 'Fioletowy',
  color: '#A857BC'
}, {
  name: 'Czerwony',
  color: '#D65251'
}, {
  name: 'Złoty',
  color: '#D8AB52'
}];
const heroMotifs = () => <div className='dev-spark-hero__motifs' aria-hidden={true}><svg className='dev-spark-hero__motif dev-spark-hero__motif--works' viewBox='0 0 520 420' fill='none'><g stroke='currentColor' strokeOpacity='.55'><rect x='140' y='110' width='140' height='200' /><rect x='280' y='110' width='90' height='200' /><rect x='370' y='110' width='140' height='200' /><rect x='50' y='110' width='90' height='200' /><path d='M140 110 160 40h100l20 70M140 310l20 70h100l20-70M280 110V70h90v40M280 310v40h90v-40M50 110l-20 15v170l20 15' strokeDasharray='6 5' /></g></svg><div className='dev-spark-hero__motif dev-spark-hero__motif--exp'><div className='dev-spark-hero__code-card'><div className='dev-spark-hero__code-bar'><i /><i /><i /><b>{'sparkEXP.tsx'}</b></div><code><span>{'const idea = spark;'}</span><span>{'export default idea;'}</span><span className='dev-spark-hero__caret' /></code></div></div><div className='dev-spark-hero__motif dev-spark-hero__motif--warsaw'><span className='dev-spark-hero__register dev-spark-hero__register--tl' /><span className='dev-spark-hero__register dev-spark-hero__register--tr' /><span className='dev-spark-hero__register dev-spark-hero__register--bl' /><span className='dev-spark-hero__register dev-spark-hero__register--br' /><span className='dev-spark-hero__cmyk'><i /><i /><i /><i /></span></div><div className='dev-spark-hero__motif dev-spark-hero__motif--health'><i /><i /><i /></div><div className='dev-spark-hero__motif dev-spark-hero__motif--studio'><span /><span /><b><i />{' REC 00:00:00:00'}</b></div></div>;
const createTemplate = attributes => [['core/group', {
  className: 'dev-spark-hero__wrap',
  layout: {
    type: 'default'
  }
}, [['core/group', {
  className: 'dev-spark-hero__meta',
  layout: {
    type: 'default'
  }
}, [['core/paragraph', {
  className: 'dev-spark-hero__meta-item',
  content: attributes.eyebrow
}], ['core/paragraph', {
  className: 'dev-spark-hero__meta-item',
  content: attributes.address
}]]], ['core/heading', {
  level: 1,
  className: 'dev-spark-hero__title',
  content: attributes.headline + ' <strong>' + attributes.brand + '</strong>'
}], ['core/group', {
  className: 'dev-spark-hero__foot',
  layout: {
    type: 'default'
  }
}, [['core/paragraph', {
  className: 'dev-spark-hero__lead',
  content: attributes.lead
}], ['core/buttons', {
  className: 'dev-spark-hero__actions',
  layout: {
    type: 'flex',
    justifyContent: 'right'
  }
}, [['core/button', {
  className: 'dev-spark-hero__button',
  text: attributes.primaryLabel,
  url: attributes.primaryUrl
}], ['core/button', {
  className: 'dev-spark-hero__button--ghost',
  text: attributes.secondaryLabel,
  url: attributes.secondaryUrl
}]]]]]]]];
registerBlockType('sparkagency/hero', {
  edit: ({
    attributes,
    setAttributes
  }) => {
    const theme = ['light', 'dark'].includes(attributes.theme) ? attributes.theme : 'inherit';
    const brand = ['agency', 'works', 'exp', 'warsaw', 'health', 'studio'].includes(attributes.brand) ? attributes.brand : 'agency';
    const backgroundStyle = attributes.backgroundStyle === 'none' ? 'none' : 'brand';
    const hasGraphic = attributes.showGraphic === true && !!attributes.graphicUrl;
    const particleStyle = {
      '--hero-bg': attributes.backgroundColor || undefined,
      '--hero-particle-color': attributes.particleColor || undefined,
      '--hero-particle-glow-color': attributes.particleGlowColor || undefined
    };
    const heroClass = `dev-spark-hero is-brand-${brand}${theme === 'inherit' ? '' : ` is-theme-${theme}`}`;
    return <section {...useBlockProps({
      className: heroClass,
      'data-theme': theme === 'inherit' ? undefined : theme,
      'data-brand': brand,
      'data-background-style': backgroundStyle,
      style: particleStyle
    })}><InspectorControls><PanelBody title='Tło i motyw' initialOpen={true}><SelectControl label='Agencja / marka' value={brand} options={[{
            label: 'sparkAGENCY',
            value: 'agency'
          }, {
            label: 'sparkWORKS',
            value: 'works'
          }, {
            label: 'sparkEXP',
            value: 'exp'
          }, {
            label: 'sparkWARSAW',
            value: 'warsaw'
          }, {
            label: 'sparkHEALTH',
            value: 'health'
          }, {
            label: 'sparkSTUDIO',
            value: 'studio'
          }]} onChange={value => setAttributes({
            brand: value
          })} /><SelectControl label='Dekoracja tła' help='Obraz/SVG ustawiasz niezależnie w sekcji „Grafika SVG / obraz”.' value={backgroundStyle} options={[{
            label: 'Z dekoracją (motyw agencji)',
            value: 'brand'
          }, {
            label: 'Bez dekoracji',
            value: 'none'
          }]} onChange={value => setAttributes({
            backgroundStyle: value
          })} /><SelectControl label='Motyw kolorystyczny' value={theme} options={[{
            label: 'Zgodny z ustawieniami użytkownika',
            value: 'inherit'
          }, {
            label: 'Ciemny',
            value: 'dark'
          }, {
            label: 'Jasny',
            value: 'light'
          }]} onChange={value => setAttributes({
            theme: value
          })} /><ColorPalette label='Kolor tła' colors={particleColors} value={attributes.backgroundColor || undefined} clearable={true} disableCustomColors={false} onChange={value => setAttributes({
            backgroundColor: value || ''
          })} /></PanelBody><PanelBody title='Grafika SVG / obraz' initialOpen={false}><ToggleControl label='Pokaż grafikę' help='Grafika pojawi się po prawej stronie hero. Bez włączenia tej opcji komponent nie wyświetla grafiki.' checked={attributes.showGraphic === true} onChange={value => setAttributes({
            showGraphic: value
          })} />{attributes.showGraphic === true && <MediaUploadCheck><MediaUpload allowedTypes={['image']} value={attributes.graphicId || 0} onSelect={media => setAttributes({
              graphicId: media.id || 0,
              graphicUrl: media.url || '',
              graphicAlt: media.alt || ''
            })} render={({
              open
            }) => <Button variant='secondary' onClick={open}>{attributes.graphicUrl ? 'Zmień grafikę' : 'Wybierz grafikę'}</Button>} /></MediaUploadCheck>}{attributes.showGraphic === true && attributes.graphicUrl && <Button variant='tertiary' isDestructive={true} onClick={() => setAttributes({
            graphicId: 0,
            graphicUrl: '',
            graphicAlt: ''
          })}>{'Usuń wybraną grafikę'}</Button>}</PanelBody><PanelBody title='Fajerwerki przy kursorze' initialOpen={true}><ToggleControl label='Włącz animację' checked={attributes.particleAnimation !== false} onChange={value => setAttributes({
            particleAnimation: value
          })} /><ColorPalette label='Kolor fajerwerków' colors={particleColors} value={attributes.particleColor || undefined} clearable={true} disableCustomColors={false} onChange={value => setAttributes({
            particleColor: value || ''
          })} /><ColorPalette label='Kolor poświaty fajerwerków' colors={particleColors} value={attributes.particleGlowColor || undefined} clearable={true} disableCustomColors={false} onChange={value => setAttributes({
            particleGlowColor: value || ''
          })} /></PanelBody></InspectorControls><div className='dev-spark-hero__particles' aria-hidden={true}><canvas className='dev-spark-hero__canvas' /></div>{backgroundStyle === 'brand' && heroMotifs()}{hasGraphic && <figure className='dev-spark-hero__graphic'><img src={attributes.graphicUrl} alt={attributes.graphicAlt || ''} /></figure>}<InnerBlocks template={createTemplate(attributes)} templateLock={false} /></section>;
  },
  save: () => <InnerBlocks.Content />
});
