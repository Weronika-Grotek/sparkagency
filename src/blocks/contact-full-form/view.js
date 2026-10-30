import { applyNeedsToForm } from "../_shared/needs";

(function () {
  "use strict";
  const draftKey = "sparkagency-contact-draft";
  const validName = (value) =>
    /^[\p{L}\p{M}]+(?:[ '\-]+[\p{L}\p{M}]+)*$/u.test(value);
  const allowedTypes = ["application/pdf", "image/jpeg", "image/png"];
  const getDraft = () => {
    try {
      const draft = JSON.parse(sessionStorage.getItem(draftKey) || "null");
      return draft && typeof draft === "object" ? draft : null;
    } catch (error) {
      return null;
    }
  };
  const validate = (form) => {
    const name = form.elements.spark_contact_name,
      email = form.elements.spark_contact_email,
      message = form.elements.spark_contact_message,
      consent = form.elements.spark_contact_consent,
      file = form.elements.spark_contact_attachment;
    [name, email, message, consent, file].forEach((field) =>
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
        message,
        Boolean(message.value.trim()) && message.value.length <= 300,
        "Opis jest wymagany i może mieć maksymalnie 300 znaków.",
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
  document
    .querySelectorAll("[data-spark-contact-full-form]")
    .forEach((root) => {
      const form = root.querySelector("form");
      if (!form) return;
      const buttons = [...root.querySelectorAll("[data-topic-key]")];
      const hidden = form.querySelector("[data-contact-topics]");
      const keys = form.querySelector("[data-contact-topic-keys]");
      const budget = form.querySelector("[data-contact-budget]");
      const budgetButtons = [...form.querySelectorAll("[data-budget]")];
      const syncTopics = () => {
        const pressed = buttons.filter(
          (button) => button.getAttribute("aria-pressed") === "true",
        );
        hidden.value = pressed
          .map((button) => button.dataset.topicLabel)
          .join(", ");
        // Keys route copies of the message to company addresses (theme settings).
        if (keys)
          keys.value = pressed.map((button) => button.dataset.topicKey).join(",");
      };
      buttons.forEach((button) =>
        button.addEventListener("click", () => {
          button.setAttribute(
            "aria-pressed",
            String(button.getAttribute("aria-pressed") !== "true"),
          );
          syncTopics();
        }),
      );
      budgetButtons.forEach((button) =>
        button.addEventListener("click", () => {
          budgetButtons.forEach((option) =>
            option.setAttribute("aria-pressed", String(option === button)),
          );
          if (budget) budget.value = button.dataset.budget;
        }),
      );
      const draft = getDraft();
      if (draft) {
        [
          ["spark_contact_name", "name", 45],
          ["spark_contact_email", "email", 254],
          ["spark_contact_message", "message", 300],
        ].forEach(([fieldName, draftName, limit]) => {
          if (typeof draft[draftName] === "string")
            form.elements[fieldName].value = draft[draftName].slice(0, limit);
        });
        const savedTopics = Array.isArray(draft.topics) ? draft.topics : [];
        const keys = savedTopics.map((topic) => topic && topic.key);
        const labels = savedTopics.map((topic) => topic && topic.label);
        buttons.forEach((button) =>
          button.setAttribute(
            "aria-pressed",
            String(
              keys.includes(button.dataset.topicKey) ||
                labels.includes(button.dataset.topicLabel),
            ),
          ),
        );
        syncTopics();
      }
      // Generator answers win over the draft: they add the company topic and the hidden fields.
      if (applyNeedsToForm(root, form)) syncTopics();
      [
        "spark_contact_name",
        "spark_contact_message",
      ].forEach((name) =>
        form.elements[name].addEventListener("input", () => {
          const field = form.elements[name];
          const limit = name === "spark_contact_name" ? 45 : 300;
          if (name === "spark_contact_name")
            field.value = field.value.replace(/[^\p{L}\p{M} '\-]/gu, "");
          field.value = field.value.slice(0, limit);
        }),
      );
      form.addEventListener("submit", (event) => {
        syncTopics();
        if (!validate(form)) event.preventDefault();
      });
      if (
        new URLSearchParams(window.location.search).get(
          "spark_contact_status",
        ) === "success"
      ) {
        try {
          sessionStorage.removeItem(draftKey);
        } catch (error) {}
      }
    });
})();
