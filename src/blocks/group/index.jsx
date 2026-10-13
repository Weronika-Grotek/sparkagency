import { registerBlockType } from '@wordpress/blocks';
import { InspectorControls, RichText, useBlockProps } from '@wordpress/block-editor';
import { PanelBody, TextControl, SelectControl, Button } from '@wordpress/components';
import { Fragment, RawHTML } from '@wordpress/element';
const motifs = {
  works: '<svg class="dev-spark-group__motif dev-spark-group__m-works" viewBox="0 0 520 420" fill="none" aria-hidden="true" style="color:var(--pc)"><g stroke="currentColor" stroke-opacity=".6"><rect x="140" y="110" width="140" height="200"/><rect x="280" y="110" width="90" height="200"/><rect x="370" y="110" width="140" height="200"/><rect x="50" y="110" width="90" height="200"/><path d="M140 110 L160 40 H260 L280 110 M140 310 L160 380 H260 L280 310"/><path d="M280 110 V70 H370 V110 M280 310 V350 H370 V310" stroke-dasharray="6 5"/></g></svg>',
  exp: '<div class="dev-spark-group__motif dev-spark-group__m-exp" aria-hidden="true"></div>',
  warsaw: '<div class="dev-spark-group__motif" style="inset:16px" aria-hidden="true"><span class="dev-spark-group__reg" style="right:0;top:0"></span><span class="dev-spark-group__reg" style="right:0;bottom:0"></span><span class="dev-spark-group__cmyk" style="top:auto;bottom:48px;right:4px"><i style="background:#00AEEF"></i><i style="background:#EC008C"></i><i style="background:#FFF200"></i><i style="background:#000;outline:1px solid #444"></i></span></div>',
  health: '<div class="dev-spark-group__motif dev-spark-group__caps" style="right:4%;bottom:10%;width:45%;height:60%" aria-hidden="true"><i style="width:220px;height:78px;left:20%;top:10%;transform:rotate(-28deg)"></i><i style="width:130px;height:46px;right:0;top:55%;transform:rotate(18deg)"></i></div>',
  studio: '<div class="dev-spark-group__motif dev-spark-group__m-studio" aria-hidden="true"><span class="dev-spark-group__lbox" style="top:0"></span><span class="dev-spark-group__lbox" style="bottom:0"></span></div>',
  none: ''
};
const motifOptions = [{
  label: 'Packaging (works)',
  value: 'works'
}, {
  label: 'Software (exp)',
  value: 'exp'
}, {
  label: 'Print (warsaw)',
  value: 'warsaw'
}, {
  label: 'Pharma (health)',
  value: 'health'
}, {
  label: 'Video (studio)',
  value: 'studio'
}, {
  label: 'Brak',
  value: 'none'
}];
const brandOptions = [{
  label: 'Agency',
  value: 'agency'
}, {
  label: 'Works',
  value: 'works'
}, {
  label: 'EXP',
  value: 'exp'
}, {
  label: 'Warsaw',
  value: 'warsaw'
}, {
  label: 'Health',
  value: 'health'
}, {
  label: 'Studio',
  value: 'studio'
}];
const newPanel = {
  motif: 'none',
  brand: 'agency',
  category: 'Kategoria',
  prefix: 'spark',
  name: 'NOWA',
  text: 'Opis marki.',
  pills: ['Tag'],
  ctaLabel: 'Podgląd strony',
  url: ''
};
const plain = html => String(html || '').replace(/<[^>]*>/g, '').replace(/&amp;/g, '&');
const pad = n => String(n).padStart(2, '0');
const text = (edit, tagName, className, value, placeholder, onChange, allowedFormats = []) => edit ? <RichText tagName={tagName} className={className} value={value} placeholder={placeholder} allowedFormats={allowedFormats} onChange={onChange} /> : <RichText.Content tagName={tagName} className={className} value={value} />;
const panelView = (panel, index, total, edit, update) => {
  const set = patch => update(index, patch);
  const pills = Array.isArray(panel.pills) ? panel.pills : [];
  const ctaProps = {
    className: 'dev-spark-group__go',
    'aria-label': plain(panel.ctaLabel) + ' ' + plain(panel.prefix) + plain(panel.name)
  };
  if (!edit && panel.url) {
    ctaProps.href = panel.url;
  } else if (!panel.url) {
    ctaProps['aria-disabled'] = 'true';
  }
  return <article key={index} className={'dev-spark-group__panel is-brand-' + (panel.brand || 'agency')} style={{
    '--i': index
  }}>{motifs[panel.motif] ? <RawHTML>{motifs[panel.motif]}</RawHTML> : null}<div className='dev-spark-group__panel-1'><div className='dev-spark-group__panel-idx dev-spark-group__mono dev-spark-group__muted'><b>{pad(index + 1)}</b><span>{'/ ' + pad(total)}</span><span>{'· '}{text(edit, 'span', '', panel.category, 'Kategoria', v => set({
            category: v
          }))}</span></div><h3>{text(edit, 'span', 'group__panel-1-subtitle', panel.prefix, 'spark', v => set({
          prefix: v
        }))}{text(edit, 'span', 'group__panel-1-title', panel.name, 'NAZWA', v => set({
          name: v
        }))}</h3></div><div className='dev-spark-group__panel-2'>{text(edit, 'p', '', panel.text, 'Opis marki', v => set({
        text: v
      }))}<div className='dev-spark-group__pills'>{pills.map((pill, i) => text(edit, 'span', '', pill, 'Tag', v => set({
          pills: pills.map((p, j) => j === i ? v : p)
        }))).map((node, i) => <Fragment key={i}>{node}</Fragment>)}</div><a {...ctaProps}>{text(edit, 'span', '', panel.ctaLabel, 'Podgląd strony', v => set({
          ctaLabel: v
        }))}{' '}<span aria-hidden='true'>{'→'}</span></a></div></article>;
};
const sectionView = (attributes, edit, setAttributes) => {
  const panels = Array.isArray(attributes.panels) ? attributes.panels : [];
  const update = (index, patch) => setAttributes({
    panels: panels.map((p, i) => i === index ? {
      ...p,
      ...patch
    } : p)
  });
  const note = plain(attributes.note).trim();
  const ctaLabel = plain(attributes.ctaLabel).trim();
  const ctaUrl = String(attributes.ctaUrl || '').trim();
  const footer = edit || note || ctaLabel && ctaUrl ? <div className='dev-spark-group__footer'>{edit || note ? text(edit, 'p', 'dev-spark-group__note', attributes.note, 'Tekst pod kafelkami', v => setAttributes({
      note: v
    }), ['core/bold']) : null}{edit || ctaLabel && ctaUrl ? <p className='dev-spark-group__cta'><a className='dev-spark-group__link-arrow' href={!edit && ctaUrl ? ctaUrl : undefined} aria-disabled={edit || !ctaUrl ? 'true' : undefined}>{text(edit, 'span', '', attributes.ctaLabel, 'Tekst przycisku', v => setAttributes({
          ctaLabel: v
        }))}{' '}<span aria-hidden='true'>{'→'}</span></a></p> : null}</div> : null;
  return <div className='dev-spark-group__wrap'><div className='dev-spark-group__head'>{text(edit, 'span', 'dev-spark-group__mono dev-spark-group__muted', attributes.eyebrow, 'Etykieta sekcji', v => setAttributes({
        eyebrow: v
      }))}<div>{text(edit, 'h2', '', attributes.title, 'Nagłówek sekcji', v => setAttributes({
          title: v
        }))}{text(edit, 'p', 'dev-spark-group__lede', attributes.lede, 'Opis sekcji', v => setAttributes({
          lede: v
        }))}</div></div><div className='dev-spark-group__stack'>{panels.map((panel, index) => panelView(panel, index, panels.length, edit, update))}</div>{footer}</div>;
};
const move = (list, from, to) => {
  const next = [...list];
  [next[from], next[to]] = [next[to], next[from]];
  return next;
};
registerBlockType('sparkagency/group', {
  edit: ({
    attributes,
    setAttributes
  }) => {
    const panels = Array.isArray(attributes.panels) ? attributes.panels : [];
    const setPanels = next => setAttributes({
      panels: next
    });
    const update = (index, patch) => setPanels(panels.map((p, i) => i === index ? {
      ...p,
      ...patch
    } : p));
    return <Fragment><InspectorControls>{panels.map((panel, index) => {
          const pills = Array.isArray(panel.pills) ? panel.pills : [];
          return <PanelBody key={index} title={pad(index + 1) + ' · ' + (plain(panel.prefix) + plain(panel.name))} initialOpen={false}><TextControl label='Adres strony marki (URL)' type='url' value={panel.url || ''} __nextHasNoMarginBottom={true} onChange={v => update(index, {
              url: v
            })} /><SelectControl label='Marka (kolor i zaokrąglenia z motywu)' value={panel.brand || 'agency'} options={brandOptions} __nextHasNoMarginBottom={true} onChange={v => update(index, {
              brand: v
            })} /><SelectControl label='Motyw tła' value={panel.motif} options={motifOptions} __nextHasNoMarginBottom={true} onChange={v => update(index, {
              motif: v
            })} /><div style={{
              marginTop: '16px'
            }}>{pills.length ? <strong>{'Tagi'}</strong> : null}{pills.map((pill, i) => <div key={i} style={{
                display: 'flex',
                gap: '4px',
                alignItems: 'flex-end',
                marginTop: '6px'
              }}><div style={{
                  flex: 1
                }}><TextControl value={plain(pill)} __nextHasNoMarginBottom={true} onChange={v => update(index, {
                    pills: pills.map((p, j) => j === i ? v : p)
                  })} /></div><Button icon='trash' label='Usuń tag' isDestructive={true} onClick={() => update(index, {
                  pills: pills.filter((p, j) => j !== i)
                })} /></div>)}<Button variant='secondary' style={{
                marginTop: '8px'
              }} onClick={() => update(index, {
                pills: [...pills, 'Tag']
              })}>{'Dodaj tag'}</Button></div><div style={{
              display: 'flex',
              gap: '4px',
              marginTop: '16px'
            }}><Button icon='arrow-up-alt2' label='W górę' disabled={0 === index} onClick={() => setPanels(move(panels, index, index - 1))} /><Button icon='arrow-down-alt2' label='W dół' disabled={index === panels.length - 1} onClick={() => setPanels(move(panels, index, index + 1))} /><Button icon='trash' isDestructive={true} variant='tertiary' onClick={() => setPanels(panels.filter((p, i) => i !== index))}>{'Usuń kartę'}</Button></div></PanelBody>;
        })}<PanelBody title='Karty' initialOpen={panels.length === 0}><Button variant='primary' onClick={() => setPanels([...panels, {
            ...newPanel,
            pills: [...newPanel.pills]
          }])}>{'Dodaj kartę'}</Button></PanelBody><PanelBody title='Tekst i przycisk pod kafelkami' initialOpen={false}><TextControl label='Adres podstrony (URL)' type='url' value={attributes.ctaUrl || ''} __nextHasNoMarginBottom={true} onChange={v => setAttributes({
            ctaUrl: v
          })} /></PanelBody></InspectorControls><section {...useBlockProps({
        className: 'dev-spark-group is-theme-dark dev-spark-group--editor'
      })}>{sectionView(attributes, true, setAttributes)}<div className='dev-spark-group__wrap' style={{
          marginTop: '16px'
        }}><Button variant='secondary' onClick={() => setPanels([...panels, {
            ...newPanel,
            pills: [...newPanel.pills]
          }])}>{'Dodaj kartę'}</Button></div></section></Fragment>;
  },
  save: ({
    attributes
  }) => <section {...useBlockProps.save({
    className: 'dev-spark-group is-theme-dark',
    'data-dev-spark-group': '',
    'aria-label': plain(attributes.eyebrow)
  })}>{sectionView(attributes, false)}</section>
});
