import { registerBlockType } from '@wordpress/blocks';
import { InspectorControls, MediaUpload, MediaUploadCheck, RichText, useBlockProps } from '@wordpress/block-editor';
import { Button, PanelBody, SelectControl, TextControl } from '@wordpress/components';
import { __ } from '@wordpress/i18n';

const domain = 'sparkagency';
const aspectOptions = [
	{ label: '21:9', value: '21/9' },
	{ label: '16:9', value: '16/9' },
	{ label: '4:3', value: '4/3' },
	{ label: '1:1', value: '1/1' },
	{ label: '3:4', value: '3/4' },
];

const blankItem = (type = 'image') => ({
	type,
	mediaId: 0,
	url: '',
	mediaType: '',
	alt: '',
	caption: '',
	aspectRatio: type === 'video' ? '16/9' : '4/3',
	width: '50',
	posterId: 0,
	posterUrl: '',
});

const Edit = ({ attributes, setAttributes }) => {
	const items = Array.isArray(attributes.items) ? attributes.items : [];
	const updateItem = (index, changes) => setAttributes({
		items: items.map((item, itemIndex) => itemIndex === index ? { ...item, ...changes } : item),
	});
	const removeItem = (index) => setAttributes({ items: items.filter((_, itemIndex) => itemIndex !== index) });
	const moveItem = (index, direction) => {
		const target = index + direction;
		if (target < 0 || target >= items.length) return;
		const next = [...items];
		[next[index], next[target]] = [next[target], next[index]];
		setAttributes({ items: next });
	};

	return <>
		<InspectorControls>
			<PanelBody title={__('Ustawienia sekcji', domain)} initialOpen>
				<TextControl label={__('Nadtytuł', domain)} value={attributes.eyebrow || ''} onChange={(eyebrow) => setAttributes({ eyebrow })} />
				<TextControl label={__('Nagłówek', domain)} value={attributes.title || ''} onChange={(title) => setAttributes({ title })} />
				<TextControl label={__('Opis', domain)} value={attributes.lede || ''} onChange={(lede) => setAttributes({ lede })} />
			</PanelBody>
			{items.map((item, index) => <PanelBody key={`${index}-${item.mediaId || item.url}`} title={`${index + 1}. ${item.type === 'video' ? __('Wideo', domain) : __('Zdjęcie', domain)}`} initialOpen={false}>
				<SelectControl label={__('Szerokość w siatce', domain)} value={item.width === '100' ? '100' : '50'} options={[{ label: __('50% — jedna kolumna', domain), value: '50' }, { label: __('100% — dwie kolumny', domain), value: '100' }]} onChange={(width) => updateItem(index, { width })} />
				<SelectControl label={__('Proporcje obrazu', domain)} value={item.aspectRatio || (item.type === 'video' ? '16/9' : '4/3')} options={aspectOptions} onChange={(aspectRatio) => updateItem(index, { aspectRatio })} />
				<TextControl label={__('Podpis', domain)} value={item.caption || ''} onChange={(caption) => updateItem(index, { caption })} />
				{item.type === 'image' && <TextControl label={__('Tekst alternatywny', domain)} value={item.alt || ''} onChange={(alt) => updateItem(index, { alt })} help={__('Opisz krótko zawartość zdjęcia.', domain)} />}
				{item.type === 'video' && <>
					<MediaUploadCheck><MediaUpload allowedTypes={['image']} value={item.posterId || 0} onSelect={(media) => updateItem(index, { posterId: media.id || 0, posterUrl: media.url || '' })} render={({ open }) => <Button variant="secondary" onClick={open}>{item.posterUrl ? __('Zmień plakat wideo', domain) : __('Wybierz plakat wideo', domain)}</Button>} /></MediaUploadCheck>
					{item.posterUrl && <Button isDestructive variant="tertiary" onClick={() => updateItem(index, { posterId: 0, posterUrl: '' })}>{__('Usuń plakat', domain)}</Button>}
				</>}
				<div className="spark-case-media-editor-actions">
					<Button variant="secondary" disabled={index === 0} onClick={() => moveItem(index, -1)}>{__('W górę', domain)}</Button>
					<Button variant="secondary" disabled={index === items.length - 1} onClick={() => moveItem(index, 1)}>{__('W dół', domain)}</Button>
					<Button isDestructive variant="tertiary" onClick={() => removeItem(index)}>{__('Usuń', domain)}</Button>
				</div>
			</PanelBody>)}
			<PanelBody title={__('Dodaj do galerii', domain)} initialOpen>
				<MediaUploadCheck>
					<MediaUpload allowedTypes={['image']} multiple gallery value={items.filter((item) => item.type === 'image').map((item) => item.mediaId).filter(Boolean)} onSelect={(media) => {
						const selected = Array.isArray(media) ? media : [media];
						setAttributes({ items: [...items, ...selected.map((entry) => ({ ...blankItem(), mediaId: entry.id || 0, url: entry.url || '', mediaType: entry.mime || '', alt: entry.alt || '', caption: entry.caption || '' }))] });
					}} render={({ open }) => <Button variant="secondary" onClick={open}>{__('Dodaj zdjęcia', domain)}</Button>} />
				</MediaUploadCheck>
				<MediaUploadCheck>
					<MediaUpload allowedTypes={['video']} value={0} onSelect={(media) => addItemWithMedia(media, 'video')} render={({ open }) => <Button variant="secondary" onClick={open}>{__('Dodaj wideo', domain)}</Button>} />
				</MediaUploadCheck>
			</PanelBody>
		</InspectorControls>
		<section {...useBlockProps({ className: 'spark-case-media spark-case-media--editor' })}>
			<div className="spark-case-media__inner">
				<header className="spark-case-media__heading">
					<div>
						<RichText tagName="p" className="spark-case-media__eyebrow" value={attributes.eyebrow || ''} placeholder={__('Nadtytuł', domain)} allowedFormats={[]} onChange={(eyebrow) => setAttributes({ eyebrow })} />
						<RichText tagName="h2" className="spark-case-media__title" value={attributes.title || ''} placeholder={__('Media z realizacji', domain)} allowedFormats={[]} onChange={(title) => setAttributes({ title })} />
					</div>
					<RichText tagName="p" className="spark-case-media__lead" value={attributes.lede || ''} placeholder={__('Opis materiałów', domain)} allowedFormats={[]} onChange={(lede) => setAttributes({ lede })} />
				</header>
				<div className="spark-case-media__grid">
					{items.map((item, index) => <figure className={`spark-case-media__item${item.width === '100' ? ' spark-case-media__item--full' : ''}${item.type === 'video' ? ' spark-case-media__item--video' : ''}`} key={`${item.mediaId || item.url || 'media'}-${index}`} style={{ aspectRatio: (item.aspectRatio || '4/3').replace('/', ' / ') }}>
						{item.type === 'video' ? (item.url ? <video controls playsInline preload={item.posterUrl ? 'metadata' : 'auto'} poster={item.posterUrl || undefined} autoPlay={!item.posterUrl} muted={!item.posterUrl} loop={!item.posterUrl}><source src={item.url} type={item.mediaType || undefined} /></video> : <MediaUploadCheck><MediaUpload allowedTypes={['video']} value={item.mediaId || 0} onSelect={(media) => updateItem(index, mediaAttributes(media, 'video'))} render={({ open }) => <Button variant="secondary" onClick={open}>{__('Wybierz wideo', domain)}</Button>} /></MediaUploadCheck>) : (item.url ? <img src={item.url} alt={item.alt || ''} /> : <MediaUploadCheck><MediaUpload allowedTypes={['image']} value={item.mediaId || 0} onSelect={(media) => updateItem(index, mediaAttributes(media, 'image'))} render={({ open }) => <Button variant="secondary" onClick={open}>{__('Wybierz zdjęcie', domain)}</Button>} /></MediaUploadCheck>)}
						<RichText tagName="figcaption" className="spark-case-media__caption" value={item.caption || ''} placeholder={__('Podpis materiału', domain)} allowedFormats={[]} onChange={(caption) => updateItem(index, { caption })} />
					</figure>)}
					{!items.length && <p className="spark-case-media__empty">{__('Dodaj zdjęcia lub wideo w panelu bocznym bloku.', domain)}</p>}
				</div>
			</div>
		</section>
	</>;

	function addItemWithMedia(media, type) {
		setAttributes({ items: [...items, { ...blankItem(type), ...mediaAttributes(media, type) }] });
	}
};

const mediaAttributes = (media, type) => ({
	type,
	mediaId: media.id || 0,
	url: media.url || '',
	mediaType: media.mime || '',
	alt: media.alt || '',
	caption: media.caption || '',
	...(type === 'video' ? { posterUrl: media.image?.src || '', posterId: media.image?.id || 0 } : {}),
});

registerBlockType('sparkagency/case-media', { edit: Edit, save: () => null });
