const renderFieldInput = (field) => {
  const id = `input-${field.id}`;
  const required = field.required ? "required" : "";

  if (field.type === "text" || field.type === "email" || field.type === "number") {
    return `
      <label class="form-label" for="${id}">${field.label}${field.required ? " *" : ""}</label>
      <input id="${id}" name="${field.id}" type="${field.type}" class="form-control" ${required} />
    `;
  }

  if (field.type === "checkbox") {
    return `
      <fieldset>
        <legend class="form-label">${field.label}${field.required ? " *" : ""}</legend>
        ${field.options?.map((opt, index) => `
          <div class="form-check">
            <input class="form-check-input" type="checkbox" name="${field.id}" id="${id}-${index}" value="${opt}">
            <label class="form-check-label" for="${id}-${index}">${opt}</label>
          </div>
        `).join("")}
      </fieldset>
    `;
  }

  if (field.type === "radio") {
    return `
      <fieldset>
        <legend class="form-label">${field.label}${field.required ? " *" : ""}</legend>
        ${field.options?.map((opt, index) => `
          <div class="form-check">
            <input class="form-check-input" type="radio" name="${field.id}" id="${id}-${index}" value="${opt}" ${required}>
            <label class="form-check-label" for="${id}-${index}">${opt}</label>
          </div>
        `).join("")}
      </fieldset>
    `;
  }

  if (field.type === "dropdown") {
    return `
      <label class="form-label" for="${id}">${field.label}${field.required ? " *" : ""}</label>
      <select id="${id}" name="${field.id}" class="form-select" ${required}>
        <option value="">Select an option</option>
        ${field.options?.map((opt) => `<option value="${opt}">${opt}</option>`).join("")}
      </select>
    `;
  }

  return "";
};

const renderPreview = () => {
  const form = getActiveForm();
  if (!form) return;
  document.querySelector("[data-form-title]").textContent = form.title;
  document.querySelector("[data-form-description]").textContent = form.description;
  const container = document.querySelector("[data-form-body]");
  container.innerHTML = "";
  form.fields.forEach((field) => {
    const wrapper = document.createElement("div");
    wrapper.className = "mb-3";
    wrapper.innerHTML = renderFieldInput(field);
    container.appendChild(wrapper);
  });
};

const handleSubmit = (event) => {
  event.preventDefault();
  const form = getActiveForm();
  const data = {};
  form.fields.forEach((field) => {
    if (field.type === "checkbox") {
      const values = Array.from(document.querySelectorAll(`input[name="${field.id}"]:checked`)).map((el) => el.value);
      data[field.label] = values.join(", ");
      return;
    }
    const input = document.querySelector(`[name="${field.id}"]`);
    if (input) {
      data[field.label] = input.value;
    }
  });
  const responses = loadResponses();
  if (!responses[form.id]) {
    responses[form.id] = [];
  }
  responses[form.id].push({ submittedAt: new Date().toISOString(), values: data });
  saveResponses(responses);
  document.querySelector("[data-submit-alert]").classList.remove("d-none");
  event.target.reset();
};

document.addEventListener("DOMContentLoaded", () => {
  renderPreview();
  const formEl = document.querySelector("[data-preview-form]");
  if (formEl) {
    formEl.addEventListener("submit", handleSubmit);
  }
});
