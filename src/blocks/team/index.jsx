import { registerBlockType } from '@wordpress/blocks';
import { InspectorControls, MediaUpload, MediaUploadCheck, RichText, useBlockProps } from '@wordpress/block-editor';
import { PanelBody, TextControl, TextareaControl, SelectControl, ToggleControl, Button, Notice, Spinner } from '@wordpress/components';
import { store as coreStore } from '@wordpress/core-data';
import { useSelect } from '@wordpress/data';
import { Fragment, useMemo } from '@wordpress/element';
import { __, sprintf } from '@wordpress/i18n';
import { parse } from '@wordpress/block-serialization-default-parser';

const domain = 'sparkagency';
const BLOCK_NAME = 'sparkagency/team';

// Kolejność i etykiety muszą być zgodne z template-parts/blocks/team.php.
const brands = {
  agency: 'sparkAGENCY',
  health: 'sparkHEALTH',
  exp: 'sparkEXP',
  warsaw: 'sparkWARSAW',
  works: 'sparkWORKS',
  studio: 'sparkSTUDIO'
};
const brandOptions = Object.entries(brands).map(([value, label]) => ({
  label,
  value
}));
const blankPerson = () => ({
  name: '',
  brand: '',
  role: '',
  bio: '',
  imageId: 0,
  imageUrl: '',
  imageAlt: ''
});

// Osoba pracująca w kilku firmach jest rozpoznawana po imieniu i nazwisku.
const personKey = name => String(name || '').trim().replace(/\s+/g, ' ').toLowerCase();
const initials = name => {
  const words = String(name || '').trim().split(/\s+/).filter(Boolean);
  return words.length ? words.slice(0, 2).map(word => word.charAt(0).toUpperCase()).join('') : '—';
};
const validBrand = (brand, fallback) => brands[brand] ? brand : fallback;
const resolvePerson = (person, sourceBrand) => ({
  key: personKey(person.name),
  name: String(person.name || '').trim(),
  brand: validBrand(person.brand, sourceBrand),
  role: String(person.role || '').trim(),
  bio: String(person.bio || '').trim(),
  imageId: Number(person.imageId) || 0,
  imageUrl: String(person.imageUrl || '').trim(),
  imageAlt: String(person.imageAlt || '').trim()
});

// Zbiera osoby z bloków Zespół innych firm, tak samo jak PHP robi to na front-endzie.
const collectPeople = (blocks, found) => {
  blocks.forEach(block => {
    if (block.blockName === BLOCK_NAME) {
      const sourceBrand = validBrand(block.attrs?.brand, 'agency');
      if (sourceBrand !== 'agency') {
        (Array.isArray(block.attrs?.people) ? block.attrs.people : []).forEach(person => {
          const item = resolvePerson(person || {}, sourceBrand);
          if (item.key && !found.has(item.key)) {
            found.set(item.key, item);
          }
        });
      }
    }
    collectPeople(block.innerBlocks || [], found);
  });
  return found;
};
const applyOverride = (person, override) => ({
  ...person,
  brand: validBrand(override?.brand, person.brand),
  role: override?.role ? override.role : person.role,
  bio: override?.bio ? override.bio : person.bio,
  imageId: Number(override?.imageId) || person.imageId,
  imageUrl: override?.imageUrl ? override.imageUrl : person.imageUrl,
  imageAlt: override?.imageAlt ? override.imageAlt : person.imageAlt
});

function PersonCard({ person }) {
  return <article className={'spark-team__person has-brand-' + person.brand}><div className={'spark-team__avatar' + (person.imageUrl ? ' has-image' : '')}>{person.imageUrl ? <img src={person.imageUrl} alt={person.imageAlt || person.name || ''} /> : <span aria-hidden={true}>{initials(person.name)}</span>}<em aria-hidden={true}>{brands[person.brand]}</em></div><div><h3>{person.name || __('Imię Nazwisko', domain)}</h3>{person.role && <p className='spark-team__role'>{person.role}</p>}{person.bio && <p className='spark-team__bio'>{person.bio}</p>}</div></article>;
}

function Edit({ attributes, setAttributes }) {
  const a = attributes;
  const sectionBrand = validBrand(a.brand, 'agency');
  const isAgency = sectionBrand === 'agency';
  const people = Array.isArray(a.people) ? a.people : [];
  const overrides = a.overrides && typeof a.overrides === 'object' && !Array.isArray(a.overrides) ? a.overrides : {};

  const pages = useSelect(select => isAgency ? select(coreStore).getEntityRecords('postType', 'page', {
    per_page: -1,
    status: 'publish',
    context: 'edit'
  }) : null, [isAgency]);
  const collected = useMemo(() => {
    if (!isAgency || !Array.isArray(pages)) {
      return [];
    }
    const found = new Map();
    pages.slice().sort((first, second) => (first.menu_order - second.menu_order) || (first.id - second.id)).forEach(page => collectPeople(parse(page.content?.raw || ''), found));
    return Array.from(found.values());
  }, [isAgency, pages]);

  const ownPeople = people.map(person => resolvePerson(person, sectionBrand));
  const ownKeys = new Set(ownPeople.map(person => person.key).filter(Boolean));
  const imported = collected.filter(person => !ownKeys.has(person.key));
  const visible = [...ownPeople, ...imported].filter(person => !overrides[person.key]?.hidden).map(person => applyOverride(person, overrides[person.key]));

  const updatePerson = (index, changes) => setAttributes({
    people: people.map((person, position) => position === index ? { ...person, ...changes } : person)
  });
  const movePerson = (index, offset) => {
    const nextIndex = index + offset;
    if (nextIndex < 0 || nextIndex >= people.length) {
      return;
    }
    const next = people.slice();
    [next[index], next[nextIndex]] = [next[nextIndex], next[index]];
    setAttributes({ people: next });
  };
  const updateOverride = (key, changes) => {
    const next = { ...overrides, [key]: { ...overrides[key], ...changes } };
    Object.keys(next[key]).forEach(field => {
      if (!next[key][field]) {
        delete next[key][field];
      }
    });
    if (!Object.keys(next[key]).length) {
      delete next[key];
    }
    setAttributes({ overrides: next });
  };
  const resetOverride = key => {
    const next = { ...overrides };
    delete next[key];
    setAttributes({ overrides: next });
  };
  const inheritLabel = sprintf(__('Firma sekcji (%s)', domain), brands[sectionBrand]);
  const wrapper = useBlockProps({ className: 'spark-team is-brand-' + sectionBrand });

  return <Fragment><InspectorControls><PanelBody title={__('Sekcja', domain)} initialOpen={true}><SelectControl label={__('Firma, do której należy sekcja', domain)} help={isAgency ? __('sparkAGENCY pokazuje swoje osoby oraz wszystkie osoby z bloków Zespół na opublikowanych stronach innych firm.', domain) : __('Sekcja pokazuje tylko osoby dodane poniżej. Trafiają one też automatycznie do sekcji sparkAGENCY.', domain)} value={sectionBrand} options={brandOptions} onChange={brand => setAttributes({ brand })} /><TextControl label={__('Etykieta', domain)} value={a.eyebrow || ''} onChange={eyebrow => setAttributes({ eyebrow })} /><TextControl label={__('Nagłówek', domain)} value={a.title || ''} onChange={title => setAttributes({ title })} /><TextareaControl label={__('Opis pod nagłówkiem', domain)} value={a.lede || ''} onChange={lede => setAttributes({ lede })} /></PanelBody>{people.map((person, index) => <PanelBody key={index} title={index + 1 + '. ' + (person.name || __('Nowa osoba', domain))} initialOpen={!person.name}><TextControl label={__('Imię i nazwisko', domain)} value={person.name || ''} onChange={name => updatePerson(index, { name })} /><SelectControl label={__('Firma', domain)} help={__('Zmień tylko dla osoby, która w tej sekcji ma występować jako pracownik innej firmy.', domain)} value={person.brand || ''} options={[{ label: inheritLabel, value: '' }, ...brandOptions]} onChange={brand => updatePerson(index, { brand })} /><TextControl label={__('Stanowisko', domain)} value={person.role || ''} onChange={role => updatePerson(index, { role })} /><TextareaControl label={__('Opis', domain)} value={person.bio || ''} onChange={bio => updatePerson(index, { bio })} /><MediaUploadCheck><MediaUpload allowedTypes={['image']} value={person.imageId || 0} onSelect={media => updatePerson(index, { imageId: media.id || 0, imageUrl: media.url || '', imageAlt: media.alt || '' })} render={({ open }) => <Button variant='secondary' onClick={open}>{person.imageUrl ? __('Zmień zdjęcie', domain) : __('Wybierz zdjęcie', domain)}</Button>} /></MediaUploadCheck>{person.imageUrl && <Button isDestructive={true} variant='tertiary' onClick={() => updatePerson(index, { imageId: 0, imageUrl: '', imageAlt: '' })}>{__('Usuń zdjęcie', domain)}</Button>}<div className='spark-team-editor-actions'><Button variant='secondary' disabled={index === 0} onClick={() => movePerson(index, -1)}>{__('W górę', domain)}</Button><Button variant='secondary' disabled={index === people.length - 1} onClick={() => movePerson(index, 1)}>{__('W dół', domain)}</Button><Button isDestructive={true} variant='tertiary' onClick={() => setAttributes({ people: people.filter((item, position) => position !== index) })}>{__('Usuń osobę', domain)}</Button></div></PanelBody>)}<PanelBody title={__('Dodawanie osób', domain)} initialOpen={true}><Button variant='primary' onClick={() => setAttributes({ people: [...people, blankPerson()] })}>{__('Dodaj osobę', domain)}</Button></PanelBody>{isAgency && <PanelBody title={sprintf(__('Osoby z innych firm (%d)', domain), imported.length)} initialOpen={false}>{!Array.isArray(pages) && <Spinner />}{Array.isArray(pages) && !imported.length && <Notice status='info' isDismissible={false}>{__('Brak osób w blokach Zespół na opublikowanych stronach innych firm.', domain)}</Notice>}{imported.map(person => {
            const override = overrides[person.key] || {};
            return <PanelBody key={person.key} title={person.name + (overrides[person.key] ? ' *' : '')} initialOpen={false}><SelectControl label={__('Firma w tej sekcji', domain)} value={override.brand || ''} options={[{ label: sprintf(__('Bez zmian (%s)', domain), brands[person.brand]), value: '' }, ...brandOptions]} onChange={brand => updateOverride(person.key, { brand })} /><TextControl label={__('Stanowisko w tej sekcji', domain)} placeholder={person.role} value={override.role || ''} onChange={role => updateOverride(person.key, { role })} /><TextareaControl label={__('Opis w tej sekcji', domain)} placeholder={person.bio} value={override.bio || ''} onChange={bio => updateOverride(person.key, { bio })} /><MediaUploadCheck><MediaUpload allowedTypes={['image']} value={override.imageId || person.imageId || 0} onSelect={media => updateOverride(person.key, { imageId: media.id || 0, imageUrl: media.url || '', imageAlt: media.alt || '' })} render={({ open }) => <Button variant='secondary' onClick={open}>{override.imageUrl ? __('Zmień zdjęcie w tej sekcji', domain) : __('Ustaw inne zdjęcie', domain)}</Button>} /></MediaUploadCheck>{override.imageUrl && <Button isDestructive={true} variant='tertiary' onClick={() => updateOverride(person.key, { imageId: 0, imageUrl: '', imageAlt: '' })}>{__('Przywróć zdjęcie osoby', domain)}</Button>}<ToggleControl label={__('Ukryj w tej sekcji', domain)} checked={!!override.hidden} onChange={hidden => updateOverride(person.key, { hidden })} /><Button variant='tertiary' disabled={!overrides[person.key]} onClick={() => resetOverride(person.key)}>{__('Przywróć dane ze strony firmy', domain)}</Button></PanelBody>;
          })}</PanelBody>}</InspectorControls><section {...wrapper}><div className='spark-team__inner'><div className='spark-team__header'><RichText tagName='span' className='spark-team__label' value={a.eyebrow || ''} placeholder={__('Etykieta', domain)} allowedFormats={[]} onChange={eyebrow => setAttributes({ eyebrow })} /><div><RichText tagName='h2' value={a.title || ''} placeholder={__('Nagłówek', domain)} allowedFormats={[]} onChange={title => setAttributes({ title })} /><RichText tagName='p' className='spark-team__lede' value={a.lede || ''} placeholder={__('Opis sekcji (opcjonalnie)', domain)} allowedFormats={[]} onChange={lede => setAttributes({ lede })} /></div></div>{visible.length ? <div className='spark-team__grid'>{visible.map((person, index) => <PersonCard key={(person.key || 'new') + '-' + index} person={person} />)}</div> : <Notice status='info' isDismissible={false}>{__('Dodaj osoby w panelu bocznym bloku.', domain)}</Notice>}</div></section></Fragment>;
}

// Attributes and supports are supplied by the server from block.json.
registerBlockType(BLOCK_NAME, {
  edit: Edit,
  save: () => null
});
