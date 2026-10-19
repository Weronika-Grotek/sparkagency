import { registerBlockType } from "@wordpress/blocks";
import {
  InspectorControls,
  RichText,
  useBlockProps,
} from "@wordpress/block-editor";
import {
  Button,
  ColorPalette,
  PanelBody,
  TextControl,
  TextareaControl,
} from "@wordpress/components";
import { __ } from "@wordpress/i18n";

const domain = "sparkagency";
const inputLabels = [
  ["nameLabel", "Imię i nazwisko"],
  ["emailLabel", "E-mail"],
  ["companyLabel", "Firma"],
  ["messageLabel", "O czym chcesz porozmawiać?"],
];

const Edit = ({ attributes, setAttributes }) => {
  const topics = Array.isArray(attributes.topics) ? attributes.topics : [];
  const updateTopic = (index, changes) =>
    setAttributes({
      topics: topics.map((topic, position) =>
        position === index ? { ...topic, ...changes } : topic,
      ),
    });
  const blockProps = useBlockProps({
    className: "spark-contact-form spark-contact-form--editor",
  });
  return (
    <>
      <InspectorControls>
        <PanelBody title={__("Link do pełnego formularza", domain)} initialOpen>
          <TextControl
            label={__("Tekst linku", domain)}
            value={attributes.fullFormLabel || ""}
            onChange={(fullFormLabel) => setAttributes({ fullFormLabel })}
          />
          <TextControl
            label={__("Adres podstrony Kontakt", domain)}
            help={__(
              "Np. /kontakt/. Kliknięcie zapisze dane tylko w bieżącej sesji przeglądarki.",
              domain,
            )}
            value={attributes.fullFormUrl || ""}
            onChange={(fullFormUrl) => setAttributes({ fullFormUrl })}
          />
        </PanelBody>
        <PanelBody
          title={__("Etykiety formularza", domain)}
          initialOpen={false}
        >
          <TextControl
            label={__("Nagłówek tematów", domain)}
            value={attributes.topicsLabel || ""}
            onChange={(topicsLabel) => setAttributes({ topicsLabel })}
          />
          {inputLabels.map(([key, label]) => (
            <TextControl
              key={key}
              label={__(label, domain)}
              value={attributes[key] || ""}
              onChange={(value) => setAttributes({ [key]: value })}
            />
          ))}
          <TextareaControl
            label={__("Treść zgody", domain)}
            value={attributes.consentText || ""}
            onChange={(consentText) => setAttributes({ consentText })}
          />
          <TextControl
            label={__("Link polityki prywatności", domain)}
            value={attributes.privacyUrl || ""}
            onChange={(privacyUrl) => setAttributes({ privacyUrl })}
          />
          <TextControl
            label={__("Etykieta załącznika", domain)}
            value={attributes.attachmentLabel || ""}
            onChange={(attachmentLabel) => setAttributes({ attachmentLabel })}
          />
          <TextControl
            label={__("Tekst przycisku wysyłki", domain)}
            value={attributes.submitLabel || ""}
            onChange={(submitLabel) => setAttributes({ submitLabel })}
          />
        </PanelBody>
        <PanelBody title={__("Tematy rozmowy", domain)} initialOpen={false}>
          {topics.map((topic, index) => (
            <div
              className="spark-contact-form__editor-topic"
              key={`${topic.value || "topic"}-${index}`}
            >
              <TextControl
                label={`${index + 1}. ${__("Nazwa", domain)}`}
                value={topic.label || ""}
                onChange={(label) => updateTopic(index, { label })}
              />
              <TextControl
                label={__("Identyfikator", domain)}
                help={__("Unikalny, małe litery bez spacji.", domain)}
                value={topic.value || ""}
                onChange={(value) => updateTopic(index, { value })}
              />
              <ColorPalette
                value={topic.color || ""}
                onChange={(color) =>
                  updateTopic(index, { color: color || "#FFFFFF" })
                }
              />
              <Button
                variant="tertiary"
                isDestructive
                onClick={() =>
                  setAttributes({
                    topics: topics.filter(
                      (item, position) => position !== index,
                    ),
                  })
                }
              >
                {__("Usuń temat", domain)}
              </Button>
            </div>
          ))}
          <Button
            variant="secondary"
            onClick={() =>
              setAttributes({
                topics: [
                  ...topics,
                  {
                    label: __("Nowy temat", domain),
                    value: `topic-${topics.length + 1}`,
                    color: "#FFFFFF",
                  },
                ],
              })
            }
          >
            {__("Dodaj temat", domain)}
          </Button>
        </PanelBody>
      </InspectorControls>
      <section {...blockProps}>
        <div className="spark-contact-form__inner">
          <div className="spark-contact-form__layout">
            <div className="spark-contact-form__intro">
              <RichText
                tagName="span"
                className="spark-contact-form__eyebrow"
                value={attributes.eyebrow || ""}
                placeholder="07 · Kontakt"
                allowedFormats={[]}
                onChange={(eyebrow) => setAttributes({ eyebrow })}
              />
              <RichText
                tagName="h2"
                value={attributes.title || ""}
                placeholder={__("Nagłówek", domain)}
                onChange={(title) => setAttributes({ title })}
              />
              <RichText
                tagName="p"
                className="spark-contact-form__lede"
                value={attributes.lede || ""}
                placeholder={__("Opis", domain)}
                onChange={(lede) => setAttributes({ lede })}
              />
              <p className="spark-contact-form__full-link">
                {attributes.fullFormLabel} <span>→</span>
              </p>
            </div>
            <form
              className="spark-contact-form__form"
              onSubmit={(event) => event.preventDefault()}
            >
              <fieldset>
                <legend>{attributes.topicsLabel}</legend>
                <div className="spark-contact-form__topics">
                  {topics.map((topic) => (
                    <button type="button" key={topic.value}>
                      <i style={{ background: topic.color }} />
                      {topic.label}
                    </button>
                  ))}
                </div>
              </fieldset>
              <div className="spark-contact-form__grid">
                <label>
                  {" "}
                  {attributes.nameLabel}{" "}
                  <input disabled placeholder="Anna Kowalska" />
                </label>
                <label>
                  {attributes.emailLabel}{" "}
                  <input disabled placeholder="anna@firma.pl" />
                </label>
                <label>
                  {attributes.companyLabel}{" "}
                  <input disabled placeholder="Firma sp. z o.o." />
                </label>
                <label className="spark-contact-form__field--full">
                  {attributes.messageLabel}
                  <textarea
                    disabled
                    rows="3"
                    placeholder="Krótko opisz projekt, cel i termin."
                  />
                </label>
              </div>
              <label className="spark-contact-form__consent">
                <input disabled type="checkbox" />
                <span>
                  {attributes.consentText} <u>{attributes.privacyLabel}</u>
                </span>
              </label>
              <label>
                {attributes.attachmentLabel}
                <input disabled type="file" />
              </label>
              <div className="spark-contact-form__foot">
                <span>
                  {__("Wymagane: imię, e-mail, wiadomość, zgoda", domain)}
                </span>
                <button type="submit">
                  {attributes.submitLabel} <b>→</b>
                </button>
              </div>
            </form>
          </div>
        </div>
      </section>
    </>
  );
};
registerBlockType("sparkagency/contact-form", { edit: Edit, save: () => null });
