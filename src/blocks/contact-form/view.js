import { applyNeedsToForm, needsToParams, withNeeds } from "../_shared/needs";

(function () {
  "use strict";
  const draftKey = "sparkagency-contact-draft";
  const validName = (value) =>
    /^[\p{L}\p{M}]+(?:[ '\-]+[\p{L}\p{M}]+)*$/u.test(value);
  const allowedTypes = ["application/pdf", "image/jpeg", "image/png"];
  const validate = (form) => {
    const name = form.elements.spark_contact_name,
      email = form.elements.spark_contact_email,
      company = form.elements.spark_contact_company,
      message = form.elements.spark_contact_message,
      consent = form.elements.spark_contact_consent,
      file = form.elements.spark_contact_attachment;
    [name, email, company, message, consent, file].forEach((field) =>
      field.setCustomValidity(""),
    );
    const checks = [
      [
        name,
        Boolean(name.value.trim()) &&
          name.value.length <= 45 &&
          validName(name.value.trim()),
        "Wpisz imię i nazwisko, używając tylko liter (maks. 45 znaków).",
      ],
      [
        email,
        email.validity.valid && email.value.includes("@"),
        "Podaj poprawny adres e-mail ze znakiem @.",
      ],
      [
        company,
        company.value.length <= 50,
        "Firma może mieć maksymalnie 50 znaków.",
      ],
      [
        message,
        Boolean(message.value.trim()) && message.value.length <= 300,
        "Wiadomość jest wymagana i może mieć maksymalnie 300 znaków.",
      ],
      [consent, consent.checked, "Zgoda jest wymagana."],
      [
        file,
        !file.files.length ||
          (file.files[0].size <= 10485760 &&
            allowedTypes.includes(file.files[0].type)),
        "Dodaj PDF, JPG lub PNG o wielkości do 10 MB.",
      ],
    ];
    let first = null;
    checks.forEach(([field, passed, error]) => {
      field.setCustomValidity(passed ? "" : error);
      field.closest("label")?.classList.toggle("is-invalid", !passed);
      const errorMessage = form.querySelector(
        `[data-error-for="${field.name}"]`,
      );
      if (errorMessage)
        errorMessage.textContent = passed
          ? ""
          : `Uzupełnij poprawnie: ${error}`;
      if (!passed && !first) first = field;
    });
    if (first) {
      first.focus();
      return false;
    }
    return true;
  };
  const selectedTopics = (root) =>
    [...root.querySelectorAll('[data-topic-key][aria-pressed="true"]')].map(
      (button) => ({
        key: button.dataset.topicKey,
        label: button.dataset.topicLabel,
      }),
    );
  document.querySelectorAll("[data-spark-contact-quick]").forEach((root) => {
    const form = root.querySelector("form");
    if (!form) return;
    const syncTopics = () => {
      form.querySelector("[data-contact-topics]").value = selectedTopics(root)
        .map((topic) => topic.label)
        .join(", ");
      // Keys route copies of the message to company addresses (theme settings).
      const keys = form.querySelector("[data-contact-topic-keys]");
      if (keys)
        keys.value = selectedTopics(root)
          .map((topic) => topic.key)
          .join(",");
    };
    root.querySelectorAll("[data-topic-key]").forEach((button) =>
      button.addEventListener("click", () => {
        button.setAttribute(
          "aria-pressed",
          String(button.getAttribute("aria-pressed") !== "true"),
        );
        syncTopics();
      }),
    );
    [
      "spark_contact_name",
      "spark_contact_company",
      "spark_contact_message",
    ].forEach((name) =>
      form.elements[name].addEventListener("input", () => {
        const field = form.elements[name];
        const limit =
          name === "spark_contact_name"
            ? 45
            : name === "spark_contact_company"
              ? 50
              : 300;
        if (name === "spark_contact_name")
          field.value = field.value.replace(/[^\p{L}\p{M} '\-]/gu, "");
        field.value = field.value.slice(0, limit);
      }),
    );
    form.addEventListener("submit", (event) => {
      syncTopics();
      if (!validate(form)) event.preventDefault();
    });
    // Answers from the Needs Generator travel on to the full form as well.
    const needs = applyNeedsToForm(root, form);
    syncTopics();
    const fullLink = root.querySelector("[data-contact-full-link]");
    if (needs && fullLink) {
      const href = withNeeds(fullLink.href, needsToParams(needs, needs.returnUrl));
      if (href) fullLink.href = href;
    }
    root
      .querySelector("[data-contact-full-link]")
      ?.addEventListener("click", () => {
        try {
          sessionStorage.setItem(
            draftKey,
            JSON.stringify({
              name: form.elements.spark_contact_name.value.trim(),
              email: form.elements.spark_contact_email.value.trim(),
              company: form.elements.spark_contact_company.value.trim(),
              message: form.elements.spark_contact_message.value.trim(),
              topics: selectedTopics(root),
            }),
          );
        } catch (error) {}
      });
    root.querySelector("[data-spark-contact-notice]")?.focus();
  });
})();
