import { registerBlockType } from '@wordpress/blocks';
import { InspectorControls, RichText, useBlockProps } from '@wordpress/block-editor';
import { Button, ColorPalette, PanelBody, SelectControl, TextControl, TextareaControl } from '@wordpress/components';
import { Fragment } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
const domain = 'sparkagency';
const newStep = index => ({
  number: String(index + 1).padStart(2, '0'),
  title: '',
  description: ''
});
function Edit({
  attributes,
  setAttributes
}) {
  const steps = Array.isArray(attributes.steps) ? attributes.steps : [];
  const theme = ['dark', 'light', 'inherit'].includes(attributes.theme) ? attributes.theme : 'dark';
  const updateStep = (index, changes) => setAttributes({
    steps: steps.map((step, position) => position === index ? {
      ...step,
      ...changes
    } : step)
  });
  const moveStep = (index, direction) => {
    const target = index + direction;
    if (target < 0 || target >= steps.length) return;
    const next = [...steps];
    [next[index], next[target]] = [next[target], next[index]];
    setAttributes({
      steps: next
    });
  };
  const addStep = () => setAttributes({
    steps: [...steps, newStep(steps.length)]
  });
  const props = useBlockProps({
    className: 'spark-howstart spark-howstart--editor' + (theme === 'inherit' ? '' : ' is-theme-' + theme),
    style: attributes.accentColor ? {
      '--howstart-accent': attributes.accentColor
    } : undefined
  });
  return <Fragment><InspectorControls><PanelBody title={__('Ustawienia sekcji', domain)} initialOpen={true}><TextControl label={__('Etykieta', domain)} value={attributes.eyebrow || ''} onChange={eyebrow => setAttributes({
    eyebrow
  })} /><TextControl label={__('Tekst linku', domain)} value={attributes.ctaLabel || ''} onChange={ctaLabel => setAttributes({
    ctaLabel
  })} /><TextControl label={__('Adres linku (URL lub #kontakt)', domain)} value={attributes.ctaUrl || ''} onChange={ctaUrl => setAttributes({
    ctaUrl
  })} /><SelectControl label={__('Motyw', domain)} value={theme} options={[{
    label: __('Ciemny', domain),
    value: 'dark'
  }, {
    label: __('Jasny', domain),
    value: 'light'
  }, {
    label: __('Dziedzicz ustawienia strony', domain),
    value: 'inherit'
  }]} onChange={value => setAttributes({
    theme: value
  })} /><p>{__('Kolor akcentu', domain)}</p><ColorPalette value={attributes.accentColor || undefined} clearable={true} disableCustomColors={false} onChange={accentColor => setAttributes({
    accentColor: accentColor || ''
  })} /></PanelBody>{steps.map((step, index) => <PanelBody key={index} title={index + 1 + '. ' + (step.title || __('Nowy krok', domain))} initialOpen={false}><TextControl label={__('Numer kroku', domain)} value={step.number || ''} onChange={number => updateStep(index, {
    number
  })} /><TextControl label={__('Tytuł kroku', domain)} value={step.title || ''} onChange={title => updateStep(index, {
    title
  })} /><TextareaControl label={__('Opis kroku', domain)} value={step.description || ''} onChange={description => updateStep(index, {
    description
  })} /><div className='spark-howstart__editor-actions'><Button variant='secondary' disabled={index === 0} onClick={() => moveStep(index, -1)}>{__('W górę', domain)}</Button><Button variant='secondary' disabled={index === steps.length - 1} onClick={() => moveStep(index, 1)}>{__('W dół', domain)}</Button><Button variant='secondary' onClick={() => setAttributes({
    steps: [...steps.slice(0, index + 1), {
      ...step
    }, ...steps.slice(index + 1)]
  })}>{__('Duplikuj', domain)}</Button><Button variant='tertiary' isDestructive={true} onClick={() => setAttributes({
    steps: steps.filter((item, position) => position !== index)
  })}>{__('Usuń', domain)}</Button></div></PanelBody>)}<PanelBody title={__('Kroki procesu', domain)} initialOpen={true}><Button variant='primary' onClick={addStep}>{__('Dodaj krok', domain)}</Button></PanelBody></InspectorControls><section {...props}><div className='spark-howstart__inner'><div className='spark-howstart__layout'><div className='spark-howstart__intro'><RichText tagName='span' className='spark-howstart__eyebrow' value={attributes.eyebrow || ''} placeholder={__('06 · Jak zacząć', domain)} allowedFormats={[]} onChange={eyebrow => setAttributes({
    eyebrow
  })} /><RichText tagName='h2' value={attributes.title || ''} placeholder={__('Nagłówek sekcji', domain)} allowedFormats={[]} onChange={title => setAttributes({
    title
  })} /><RichText tagName='p' className='spark-howstart__lede' value={attributes.lede || ''} placeholder={__('Opis sekcji', domain)} allowedFormats={[]} onChange={lede => setAttributes({
    lede
  })} /><p className='spark-howstart__cta'><RichText tagName='span' value={attributes.ctaLabel || ''} placeholder={__('Tekst linku', domain)} allowedFormats={[]} onChange={ctaLabel => setAttributes({
    ctaLabel
  })} />{' '}<span aria-hidden={true}>{'→'}</span></p></div><ol className='spark-howstart__steps'>{steps.map((step, index) => <li className='spark-howstart__step is-active' key={index}><RichText tagName='b' value={step.number || ''} placeholder={String(index + 1).padStart(2, '0')} allowedFormats={[]} onChange={number => updateStep(index, {
    number
  })} /><div><RichText tagName='h3' value={step.title || ''} placeholder={__('Tytuł kroku', domain)} allowedFormats={[]} onChange={title => updateStep(index, {
    title
  })} /><RichText tagName='p' value={step.description || ''} placeholder={__('Opis kroku', domain)} allowedFormats={[]} onChange={description => updateStep(index, {
    description
  })} /></div></li>)}<li className='spark-howstart__add-step'><Button variant='secondary' onClick={addStep}>{__('Dodaj krok', domain)}</Button></li></ol></div></div></section></Fragment>;
}
registerBlockType('sparkagency/howstart', {
  edit: Edit,
  save: () => null
});
