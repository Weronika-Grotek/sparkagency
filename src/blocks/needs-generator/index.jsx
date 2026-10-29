import { registerBlockType } from '@wordpress/blocks';
import { InspectorControls, MediaUpload, MediaUploadCheck, RichText, useBlockProps } from '@wordpress/block-editor';
import { Button, Notice, PanelBody, SelectControl, TextControl, TextareaControl, ToggleControl } from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import { blankAction, blankCompany, blankQuestionOption, readModel, readTitles, slugify, writeModel } from './model';

const domain = 'sparkagency';
const brands = [
	{ label: 'Spark Agency', value: 'agency' }, { label: 'Spark EXP', value: 'exp' }, { label: 'Spark Warsaw', value: 'warsaw' },
	{ label: 'Spark Health', value: 'health' }, { label: 'Spark Works', value: 'works' }, { label: 'Spark Studio', value: 'studio' },
];
const stepLabels = [__('Krok 1 · co chcesz zrobić', domain), __('Krok 2 · skala zadania', domain), __('Krok 3 · czego potrzebujesz', domain)];
const replaceAt = (list, index, changes) => list.map((item, position) => (position === index ? { ...item, ...changes } : item));
const removeAt = (list, index) => list.filter((item, position) => position !== index);
const moveBy = (list, index, offset) => {
	const target = index + offset;
	if (target < 0 || target >= list.length) return list;
	const next = [...list];
	[next[index], next[target]] = [next[target], next[index]];
	return next;
};
const CompanyName = ({ company, fallback }) => {
	const label = company?.label || fallback;
	const highlighted = company?.highlightText || '';
	const start = highlighted ? label.toLocaleLowerCase().indexOf(highlighted.toLocaleLowerCase()) : -1;
	if (start === -1) return label;
	const end = start + highlighted.length;
	return <>{label.slice(0, start)}<span className={`spark-needs-generator__company-accent is-brand-${company.highlightBrand || company.brand || 'agency'}${company.highlightBold === false ? '' : ' is-bold'}`}>{label.slice(start, end)}</span>{label.slice(end)}</>;
};

const Edit = ({ attributes, setAttributes }) => {
	const { companies, questionOptions, isLegacy } = readModel(attributes);
	const titles = readTitles(attributes);
	const save = (changes) => setAttributes(writeModel({ companies, questionOptions, titles, ...changes }));
	const setCompanies = (next) => save({ companies: next });
	const updateCompany = (index, changes) => setCompanies(replaceAt(companies, index, changes));
	const setQuestion = (questionIndex, next) => save({ questionOptions: questionOptions.map((options, index) => (index === questionIndex ? next : options)) });
	const updateOption = (questionIndex, optionIndex, changes) => setQuestion(questionIndex, replaceAt(questionOptions[questionIndex], optionIndex, changes));
	const ids = companies.map((company) => company.id);
	const duplicates = ids.filter((id, index) => id && ids.indexOf(id) !== index);
	const companyOptions = companies.filter((company) => company.id).map((company) => ({ label: company.label || company.id, value: company.id }));
	const companyFilterOptions = [{ label: __('Wszystkie firmy', domain), value: '' }, ...companyOptions];
	const companyForAction = (action) => companies.find((company) => company.id === action.companyId);
	const renderQuestionOptions = (questionIndex, { companyMapping = false } = {}) => questionOptions[questionIndex].map((option, index) => <div className="spark-needs-generator__editor-option" key={index}>
		<TextControl label={`${__('Odpowiedź', domain)} ${index + 1}`} value={option.label || ''} onChange={(label) => updateOption(questionIndex, index, { label })} />
		{/* <TextControl label={__('Dokładna wartość do formularza', domain)} help={__('Puste pole = etykieta.', domain)} value={option.value || ''} onChange={(value) => updateOption(questionIndex, index, { value })} /> */}
		{companyMapping ? <SelectControl label={__('Przypisana firma i kolor kropki', domain)} value={option.companyId || ''} options={[{ label: __('Wybierz firmę', domain), value: '' }, ...companyOptions]} onChange={(companyId) => updateOption(questionIndex, index, { companyId })} /> : <SelectControl label={__('Pokaż dla firmy', domain)} help={__('Pozostaw „Wszystkie firmy”, gdy odpowiedź ma być zawsze dostępna.', domain)} value={option.visibleFor || ''} options={companyFilterOptions} onChange={(visibleFor) => updateOption(questionIndex, index, { visibleFor })} />}
		{companyMapping && !option.companyId && <Notice status="warning" isDismissible={false}>{__('Wybierz firmę, aby przekazać ją dalej i pokazać poprawny kolor kropki.', domain)}</Notice>}
		<Button isDestructive variant="tertiary" onClick={() => setQuestion(questionIndex, removeAt(questionOptions[questionIndex], index))}>{__('Usuń odpowiedź', domain)}</Button>
	</div>);

	return <>
		<InspectorControls>
			<PanelBody title={__('Nagłówek i link domyślny', domain)} initialOpen>
				{isLegacy && <Notice status="warning" isDismissible={false}>{__('Pierwszy zapis przekształci dotychczasowy układ w niezależne firmy i edytowalne odpowiedzi pytań.', domain)}</Notice>}
				<TextControl label={__('Etykieta sekcji', domain)} value={attributes.eyebrow || ''} onChange={(eyebrow) => setAttributes({ eyebrow })} />
				<TextControl label={__('Nagłówek', domain)} value={attributes.title || ''} onChange={(title) => setAttributes({ title })} />
				<TextareaControl label={__('Wprowadzenie', domain)} value={attributes.lede || ''} onChange={(lede) => setAttributes({ lede })} />
				<TextControl label={__('Link domyślny', domain)} type="url" value={attributes.finalUrl || ''} onChange={(finalUrl) => setAttributes({ finalUrl })} />
				<TextControl label={__('Tekst przycisku końcowego', domain)} value={attributes.finalButtonLabel || ''} onChange={(finalButtonLabel) => setAttributes({ finalButtonLabel })} />
			</PanelBody>
			<PanelBody title={__('Pytania i odpowiedzi', domain)} initialOpen>
				{titles.map((title, index) => <TextControl key={index} label={stepLabels[index]} value={title} onChange={(value) => save({ titles: titles.map((item, position) => (position === index ? value : item)) })} />)}
			</PanelBody>
			<PanelBody title={__('Krok 1 · Co chcesz zrobić?', domain)} initialOpen>
				<Notice status="info" isDismissible={false}>{__('To są działania widoczne dla odbiorcy. Do każdego przypisz firmę — jej nazwa, opis i kolor kropki pojawią się automatycznie.', domain)}</Notice>
				{renderQuestionOptions(0, { companyMapping: true })}
				<Button variant="secondary" onClick={() => setQuestion(0, [...questionOptions[0], blankAction()])}>{__('Dodaj działanie', domain)}</Button>
			</PanelBody>
			<PanelBody title={__('Krok 2 · odpowiedzi', domain)} initialOpen>
				{renderQuestionOptions(1)}
				<Button variant="secondary" onClick={() => setQuestion(1, [...questionOptions[1], blankQuestionOption()])}>{__('Dodaj odpowiedź', domain)}</Button>
			</PanelBody>
			<PanelBody title={__('Krok 3 · odpowiedzi', domain)} initialOpen>
				{renderQuestionOptions(2)}
				<Button variant="secondary" onClick={() => setQuestion(2, [...questionOptions[2], blankQuestionOption()])}>{__('Dodaj odpowiedź', domain)}</Button>
			</PanelBody>
			<PanelBody title={__('Firmy — lista do rozbudowy', domain)} initialOpen>
				<Notice status="info" isDismissible={false}>{__('Dodaj tu nową firmę, ustaw jej kolor i opis, a następnie przypisz ją do działań w kroku 1.', domain)}</Notice>
				{companies.map((company, companyIndex) => <div className="spark-needs-generator__editor-option" key={companyIndex}>
					{duplicates.includes(company.id) && <Notice status="error" isDismissible={false}>{__('Ten identyfikator jest już użyty. Każda firma potrzebuje unikalnego identyfikatora.', domain)}</Notice>}
					<TextControl label={__('Nazwa firmy', domain)} value={company.label || ''} onChange={(label) => updateCompany(companyIndex, { label, ...(!company.id || company.id === slugify(company.label) ? { id: slugify(label) } : {}) })} />
					<TextControl label={__('Identyfikator firmy', domain)} help={__('Przesyłany do formularza jako spark_company.', domain)} value={company.id || ''} onChange={(id) => updateCompany(companyIndex, { id: id.toLowerCase().replace(/[^a-z0-9_-]/g, '') })} />
					<SelectControl label={__('Kolor firmy i kropki', domain)} value={company.brand || 'agency'} options={brands} onChange={(brand) => updateCompany(companyIndex, { brand })} />
					<TextControl label={__('Wyróżniony fragment nazwy', domain)} help={__('Np. „WARSAW” w nazwie „sparkWARSAW”.', domain)} value={company.highlightText || ''} onChange={(highlightText) => updateCompany(companyIndex, { highlightText })} />
					<SelectControl label={__('Kolor wyróżnionego fragmentu', domain)} value={company.highlightBrand || company.brand || 'agency'} options={brands} onChange={(highlightBrand) => updateCompany(companyIndex, { highlightBrand })} />
					<ToggleControl label={__('Pogrub wyróżniony fragment', domain)} checked={company.highlightBold !== false} onChange={(highlightBold) => updateCompany(companyIndex, { highlightBold })} />
					<TextareaControl label={__('Opis w podsumowaniu', domain)} value={company.description || ''} onChange={(description) => updateCompany(companyIndex, { description })} />
					<TextControl label={__('URL strony końcowej', domain)} type="url" value={company.url || ''} onChange={(url) => updateCompany(companyIndex, { url })} />
					<MediaUploadCheck><MediaUpload allowedTypes={['image']} value={company.imageId || 0} onSelect={(media) => updateCompany(companyIndex, { imageId: media.id || 0, imageUrl: media.url || '', imageAlt: media.alt || '' })} render={({ open }) => <Button variant="secondary" onClick={open}>{company.imageUrl ? __('Zmień obraz', domain) : __('Wybierz obraz', domain)}</Button>} /></MediaUploadCheck>
					{company.imageUrl && <Button isDestructive variant="tertiary" onClick={() => updateCompany(companyIndex, { imageId: 0, imageUrl: '', imageAlt: '' })}>{__('Usuń obraz', domain)}</Button>}
					<div className="spark-needs-generator__editor-actions"><Button disabled={companyIndex === 0} onClick={() => setCompanies(moveBy(companies, companyIndex, -1))}>{__('Wyżej', domain)}</Button><Button disabled={companyIndex === companies.length - 1} onClick={() => setCompanies(moveBy(companies, companyIndex, 1))}>{__('Niżej', domain)}</Button><Button isDestructive variant="tertiary" onClick={() => setCompanies(removeAt(companies, companyIndex))}>{__('Usuń firmę', domain)}</Button></div>
				</div>)}
				<Button variant="primary" onClick={() => setCompanies([...companies, blankCompany()])}>{__('Dodaj firmę', domain)}</Button>
			</PanelBody>
		</InspectorControls>
		<section {...useBlockProps({ className: 'spark-needs-generator' })}>
			<header className="spark-needs-generator__header"><RichText tagName="p" className="spark-needs-generator__eyebrow" value={attributes.eyebrow || ''} placeholder={__('Etykieta sekcji', domain)} allowedFormats={[]} onChange={(eyebrow) => setAttributes({ eyebrow })} /><div><RichText tagName="h2" value={attributes.title || ''} placeholder={__('Nagłówek', domain)} onChange={(title) => setAttributes({ title })} /><RichText tagName="p" className="spark-needs-generator__lede" value={attributes.lede || ''} placeholder={__('Opis sekcji', domain)} onChange={(lede) => setAttributes({ lede })} /></div></header>
			<div className="spark-needs-generator__preview"><p className="spark-needs-generator__progress">{stepLabels[0]}</p><h3>{titles[0] || __('Co chcesz zrobić?', domain)}</h3><div className="spark-needs-generator__options">{questionOptions[0].map((action, index) => { const company = companyForAction(action); return <button className={`is-action is-brand-${company?.brand || 'agency'}`} type="button" key={index}><i aria-hidden="true" /><span>{action.label || __('Nowe działanie', domain)}</span></button>; })}</div></div>
		</section>
	</>;
};

registerBlockType('sparkagency/needs-generator', { edit: Edit, save: () => null });
