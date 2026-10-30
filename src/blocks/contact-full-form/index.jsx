import { registerBlockType } from "@wordpress/blocks";
import {
  InspectorControls,
  RichText,
  useBlockProps,
} from "@wordpress/block-editor";
import {
  ColorPalette,
  PanelBody,
  TextControl,
  TextareaControl,
} from "@wordpress/components";
import { __ } from "@wordpress/i18n";
const domain = "sparkagency";
const Edit = ({ attributes, setAttributes }) => {
  const topics = Array.isArray(attributes.topics) ? attributes.topics : [];
  const departments = Array.isArray(attributes.departments) ? attributes.departments : [];
  const update = (index, changes) =>
    setAttributes({
      topics: topics.map((item, position) =>
        position === index ? { ...item, ...changes } : item,
      ),
    });
  const updateDepartment = (index, changes) =>
    setAttributes({
      departments: departments.map((item, position) =>
        position === index ? { ...item, ...changes } : item,
      ),
    });
  const props = useBlockProps({
    className: "spark-contact-full-form spark-contact-full-form--editor",
  });
  return (
    <>
      <InspectorControls>
        <PanelBody title={__("Ustawienia strony Kontakt", domain)} initialOpen>
          <TextareaControl
            label={__("Etykieta adresu", domain)}
            value={attributes.addressLabel || ""}
            onChange={(addressLabel) => setAttributes({ addressLabel })}
          />
          <TextareaControl
            label={__("Adres", domain)}
            value={attributes.address || ""}
            onChange={(address) => setAttributes({ address })}
          />
          <TextControl label={__("Link do mapy", domain)} value={attributes.mapUrl || ""} onChange={(mapUrl) => setAttributes({ mapUrl })} />
          <TextControl label={__("Tekst przycisku mapy", domain)} value={attributes.mapLabel || ""} onChange={(mapLabel) => setAttributes({ mapLabel })} />
          <TextControl label={__("Nagłówek kontaktu", domain)} value={attributes.contactTitle || ""} onChange={(contactTitle) => setAttributes({ contactTitle })} />
          <TextControl label={__("E-mail do nowych projektów", domain)} value={attributes.contactEmail || ""} onChange={(contactEmail) => setAttributes({ contactEmail })} />
          <TextControl label={__("Telefon", domain)} value={attributes.contactPhone || ""} onChange={(contactPhone) => setAttributes({ contactPhone })} />
          <TextControl label={__("Godziny kontaktu", domain)} value={attributes.contactHours || ""} onChange={(contactHours) => setAttributes({ contactHours })} />
          <TextControl label={__("Nagłówek listy zespołów", domain)} value={attributes.teamsTitle || ""} onChange={(teamsTitle) => setAttributes({ teamsTitle })} />
          <TextControl
            label={__("Link polityki prywatności", domain)}
            value={attributes.privacyUrl || ""}
            onChange={(privacyUrl) => setAttributes({ privacyUrl })}
          />
          <TextareaControl
            label={__("Komunikat sukcesu", domain)}
            value={attributes.successMessage || ""}
            onChange={(successMessage) => setAttributes({ successMessage })}
          />
        </PanelBody>
        <PanelBody title={__("Firmy i zespoły", domain)} initialOpen={false}>
          <p>{__("Edytuj nazwy, adresy e-mail i kolory firm. Możesz też dodać własną firmę lub zespół.", domain)}</p>
          {departments.map((department, index) => (
            <div className="spark-contact-full-form__department-editor" key={`${department.key}-${index}`}>
              <TextControl label={__("Nazwa wyświetlana", domain)} value={department.name || ""} onChange={(name) => updateDepartment(index, { name })} />
              <TextControl label={__("E-mail", domain)} value={department.email || ""} onChange={(email) => updateDepartment(index, { email })} />
              <TextControl label={__("Kolor", domain)} value={department.color || ""} onChange={(color) => updateDepartment(index, { color })} />
              <button type="button" className="components-button is-link is-destructive" onClick={() => setAttributes({ departments: departments.filter((_, position) => position !== index) })}>{__("Usuń z listy", domain)}</button>
            </div>
          ))}
          <button type="button" className="components-button is-secondary" onClick={() => setAttributes({ departments: [...departments, { key: "custom", name: "", email: "", color: "#FFFFFF" }] })}>{__("Dodaj firmę lub zespół", domain)}</button>
        </PanelBody>
        <PanelBody title={__("Tematy rozmowy", domain)} initialOpen={false}>
          {topics.map((topic, index) => (
            <div
              className="spark-contact-full-form__topic-editor"
              key={`${topic.value}-${index}`}
            >
              <TextControl
                label={`${index + 1}. ${__("Nazwa", domain)}`}
                value={topic.label || ""}
                onChange={(label) => update(index, { label })}
              />
              <ColorPalette
                value={topic.color || ""}
                onChange={(color) =>
                  update(index, { color: color || "#FFFFFF" })
                }
              />
            </div>
          ))}
        </PanelBody>
      </InspectorControls>
      <section {...props}>
        <div className="spark-contact-full-form__inner">
          <RichText
            tagName="h2"
            value={attributes.title || ""}
            placeholder={__("Nagłówek", domain)}
            onChange={(title) => setAttributes({ title })}
          />
          <RichText
            tagName="p"
            className="spark-contact-full-form__lede"
            value={attributes.lede || ""}
            placeholder={__("Opis", domain)}
            onChange={(lede) => setAttributes({ lede })}
          />
          <div className="spark-contact-full-form__layout">
            <form>
              <b>01 · Czego dotyczy projekt</b>
              <div className="spark-contact-full-form__topics">
                {topics.map((topic) => (
                  <button key={topic.value} type="button">
                    <i style={{ background: topic.color }} />
                    {topic.label}
                  </button>
                ))}
              </div>
              <b>02 · O projekcie</b>
              <textarea disabled placeholder="Cel, zakres, grupa docelowa..." />
              <select disabled>
                <option>Budżet</option>
              </select>
              <b>03 · Dane kontaktowe</b>
              <input disabled placeholder="Imię i nazwisko" />
              <input disabled placeholder="E-mail" />
              <label>
                <input disabled type="checkbox" /> Zgoda na kontakt
              </label>
            </form>
            <aside>
              <div><span>{attributes.addressLabel}</span><p>{attributes.address}</p><a href={attributes.mapUrl}>{attributes.mapLabel} ↗</a></div>
              <div><span>{attributes.contactTitle}</span><p>{attributes.contactEmail || "E-mail do uzupełnienia"}</p><p>{attributes.contactPhone} · {attributes.contactHours}</p></div>
              <div><span>{attributes.teamsTitle}</span>{departments.map((department, index) => <p key={`${department.key}-${index}`}>{department.name || "Nazwa firmy"} · {department.email || "E-mail do uzupełnienia"}</p>)}</div>
            </aside>
          </div>
        </div>
      </section>
    </>
  );
};
registerBlockType("sparkagency/contact-full-form", {
  edit: Edit,
  save: () => null,
});
