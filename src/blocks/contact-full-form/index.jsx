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
  const update = (index, changes) =>
    setAttributes({
      topics: topics.map((item, position) =>
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
            label={__("Adres", domain)}
            value={attributes.address || ""}
            onChange={(address) => setAttributes({ address })}
          />
          <TextareaControl
            label={__("Informacja kontaktowa", domain)}
            value={attributes.contactInfo || ""}
            onChange={(contactInfo) => setAttributes({ contactInfo })}
          />
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
              <input disabled placeholder="Firma" />
              <input disabled placeholder="E-mail" />
              <label>
                <input disabled type="checkbox" /> Zgoda na kontakt
              </label>
            </form>
            <aside>
              <p>{attributes.address}</p>
              <p>{attributes.contactInfo}</p>
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
