const paletteItems = [
  { type: "text", label: "Short text" },
  { type: "email", label: "Email" },
  { type: "number", label: "Number" },
  { type: "checkbox", label: "Checkboxes" },
  { type: "radio", label: "Multiple choice" },
  { type: "dropdown", label: "Dropdown" }
];

const typeRequiresOptions = (type) => ["checkbox", "radio", "dropdown"].includes(type);

const normalizeFieldType = (type) => {
  const allowed = ["text", "email", "number", "checkbox", "radio", "dropdown"];
  return allowed.includes(type) ? type : "text";
};

const formatOptions = (options = []) => options.join("\n");

const parseOptions = (raw) => raw
  .split("\n")
  .map((item) => item.trim())
  .filter(Boolean);

const buildPalette = () => {
  const palette = document.querySelector("[data-palette]");
  if (!palette) return;
  palette.innerHTML = "";
  paletteItems.forEach((item) => {
    const el = document.createElement("div");
    el.className = "palette-item";
    el.setAttribute("draggable", "true");
    el.dataset.type = item.type;
    el.innerHTML = `<span>${item.label}</span><span class="badge badge-soft">${item.type}</span>`;
    el.addEventListener("dragstart", (event) => {
      event.dataTransfer.setData("text/plain", item.type);
    });
    palette.appendChild(el);
  });
};

const renderCanvas = (form) => {
  const canvas = document.querySelector("[data-canvas]");
  if (!canvas) return;
  canvas.innerHTML = "";
  if (!form.fields.length) {
    canvas.innerHTML = `<p class="text-muted">Drag fields here to start building your form.</p>`;
  }
  form.fields.forEach((field) => {
    const item = document.createElement("div");
    item.className = "form-item";
    item.dataset.id = field.id;
    item.tabIndex = 0;
    item.innerHTML = `
      <div class="d-flex justify-content-between align-items-start">
        <div>
          <strong>${field.label}</strong>
          <div class="text-muted small">${field.type}${field.required ? " • Required" : ""}</div>
          ${typeRequiresOptions(field.type) ? `<div class="option-list">Options: ${field.options?.join(", ") || ""}</div>` : ""}
        </div>
        <button class="btn btn-sm btn-outline-danger" type="button" data-remove>Remove</button>
      </div>
    `;
    item.addEventListener("click", () => selectField(field.id));
    item.addEventListener("keydown", (event) => {
      if (event.key === "Enter" || event.key === " ") {
        event.preventDefault();
        selectField(field.id);
      }
    });
    item.querySelector("[data-remove]").addEventListener("click", (event) => {
      event.stopPropagation();
      removeField(field.id);
    });
    canvas.appendChild(item);
  });
};

let activeForm = null;
let selectedFieldId = null;

const selectField = (id) => {
  selectedFieldId = id;
  document.querySelectorAll(".form-item").forEach((item) => {
    item.classList.toggle("active", item.dataset.id === id);
  });
  renderProperties();
};

const removeField = (id) => {
  activeForm.fields = activeForm.fields.filter((field) => field.id !== id);
  if (selectedFieldId === id) {
    selectedFieldId = null;
  }
  persistForm();
  renderCanvas(activeForm);
  renderProperties();
};

const createFieldObject = (type, label) => {
  const resolvedType = normalizeFieldType(type);
  const id = `f-${Date.now()}-${Math.floor(Math.random() * 10000)}`;
  const defaultLabel = paletteItems.find((item) => item.type === resolvedType)?.label || "Field";
  return {
    id,
    type: resolvedType,
    label: label || defaultLabel,
    required: false,
    options: typeRequiresOptions(resolvedType) ? ["Option 1", "Option 2"] : []
  };
};

const addField = (type) => {
  const newField = createFieldObject(type);
  activeForm.fields.push(newField);
  persistForm();
  renderCanvas(activeForm);
  selectField(newField.id);
};

const addSuggestedFields = (fields = []) => {
  const validFields = fields
    .filter((field) => field && typeof field === "object")
    .map((field) => {
      const resolvedType = normalizeFieldType(field.type || "text");
      return {
        id: `f-${Date.now()}-${Math.floor(Math.random() * 10000)}`,
        type: resolvedType,
        label: String(field.label || "Untitled field").trim() || "Untitled field",
        required: Boolean(field.required),
        options: typeRequiresOptions(resolvedType)
          ? (Array.isArray(field.options) ? field.options.map((opt) => String(opt).trim()).filter(Boolean) : ["Option 1", "Option 2"])
          : []
      };
    });

  if (!validFields.length) {
    return 0;
  }

  activeForm.fields.push(...validFields);
  persistForm();
  renderCanvas(activeForm);
  selectField(validFields[validFields.length - 1].id);
  return validFields.length;
};

const applyMetaSuggestions = (meta = {}) => {
  if (typeof meta.title === "string" && meta.title.trim() !== "") {
    activeForm.title = meta.title.trim();
  }
  if (typeof meta.description === "string" && meta.description.trim() !== "") {
    activeForm.description = meta.description.trim();
  }

  const titleInput = document.querySelector("[data-form-title]");
  const descInput = document.querySelector("[data-form-description]");
  const titlePreview = document.querySelector("[data-form-title-preview]");
  if (titleInput) {
    titleInput.value = activeForm.title;
  }
  if (descInput) {
    descInput.value = activeForm.description;
  }
  if (titlePreview) {
    titlePreview.textContent = activeForm.title;
  }
  persistForm();
};

const getBuilderState = () => ({
  id: activeForm.id,
  title: activeForm.title,
  description: activeForm.description,
  fields: activeForm.fields.map((field) => ({
    id: field.id,
    type: field.type,
    label: field.label,
    required: Boolean(field.required),
    options: Array.isArray(field.options) ? [...field.options] : []
  }))
});

const autoFixBasicAccessibility = () => {
  if (!activeForm.title || !activeForm.title.trim()) {
    activeForm.title = "Accessible Feedback Form";
  }
  if (!activeForm.description || activeForm.description.trim().length < 20) {
    activeForm.description = "Please complete this form. Required fields are marked clearly for accessibility.";
  }

  const seenLabels = {};
  activeForm.fields.forEach((field, index) => {
    if (!field.label || !field.label.trim()) {
      field.label = `Question ${index + 1}`;
    }

    const key = field.label.trim().toLowerCase();
    seenLabels[key] = (seenLabels[key] || 0) + 1;
    if (seenLabels[key] > 1) {
      field.label = `${field.label} (${seenLabels[key]})`;
    }

    if (typeRequiresOptions(field.type)) {
      const options = Array.isArray(field.options) ? field.options.map((opt) => String(opt).trim()).filter(Boolean) : [];
      while (options.length < 2) {
        options.push(`Option ${options.length + 1}`);
      }
      field.options = options;
    }
  });

  const titleInput = document.querySelector("[data-form-title]");
  const descInput = document.querySelector("[data-form-description]");
  const titlePreview = document.querySelector("[data-form-title-preview]");
  if (titleInput) titleInput.value = activeForm.title;
  if (descInput) descInput.value = activeForm.description;
  if (titlePreview) titlePreview.textContent = activeForm.title;

  persistForm();
  renderCanvas(activeForm);
  renderProperties();
};

const renderProperties = () => {
  const panel = document.querySelector("[data-properties]");
  if (!panel) return;
  if (!selectedFieldId) {
    panel.innerHTML = `<p class="text-muted">Select a field to edit its properties.</p>`;
    return;
  }
  const field = activeForm.fields.find((item) => item.id === selectedFieldId);
  if (!field) return;
  panel.innerHTML = `
    <div class="mb-3">
      <label class="form-label" for="fieldLabel">Field label</label>
      <input id="fieldLabel" class="form-control" type="text" value="${field.label}" />
    </div>
    <div class="form-check form-switch mb-3">
      <input id="fieldRequired" class="form-check-input" type="checkbox" ${field.required ? "checked" : ""} />
      <label class="form-check-label" for="fieldRequired">Required field</label>
    </div>
    ${typeRequiresOptions(field.type) ? `
      <div class="mb-3">
        <label class="form-label" for="fieldOptions">Options (one per line)</label>
        <textarea id="fieldOptions" class="form-control" rows="4">${formatOptions(field.options)}</textarea>
      </div>
    ` : ""}
  `;

  panel.querySelector("#fieldLabel").addEventListener("input", (event) => {
    field.label = event.target.value;
    persistForm();
    renderCanvas(activeForm);
  });
  panel.querySelector("#fieldRequired").addEventListener("change", (event) => {
    field.required = event.target.checked;
    persistForm();
    renderCanvas(activeForm);
  });
  const optionsInput = panel.querySelector("#fieldOptions");
  if (optionsInput) {
    optionsInput.addEventListener("input", (event) => {
      field.options = parseOptions(event.target.value);
      persistForm();
      renderCanvas(activeForm);
    });
  }
};

const persistForm = () => {
  if (!activeForm) return;
  saveActiveForm(activeForm)
    .then((saved) => {
      if (saved) {
        activeForm = saved;
      }
    })
    .catch((error) => {
      console.error("Failed to persist form to database:", error.message);
    });
};

const wireCanvas = () => {
  const canvas = document.querySelector("[data-canvas]");
  if (!canvas) return;
  canvas.addEventListener("dragover", (event) => {
    event.preventDefault();
  });
  canvas.addEventListener("drop", (event) => {
    event.preventDefault();
    const type = event.dataTransfer.getData("text/plain");
    if (type) {
      addField(type);
    }
  });
};

const wireSettings = () => {
  const titleInput = document.querySelector("[data-form-title]");
  const descInput = document.querySelector("[data-form-description]");
  if (titleInput) {
    titleInput.value = activeForm.title;
    titleInput.addEventListener("input", (event) => {
      activeForm.title = event.target.value;
      persistForm();
      document.querySelector("[data-form-title-preview]").textContent = activeForm.title;
    });
  }
  if (descInput) {
    descInput.value = activeForm.description;
    descInput.addEventListener("input", (event) => {
      activeForm.description = event.target.value;
      persistForm();
    });
  }
};

const wirePreviewButton = () => {
  const button = document.querySelector("[data-preview]" );
  if (!button) return;
  button.addEventListener("click", () => {
    persistForm();
    window.location.href = "preview.php";
  });
};

document.addEventListener("DOMContentLoaded", async () => {
  try {
    activeForm = await fetchActiveForm();
  } catch (error) {
    console.error("Failed to load form from database:", error.message);
  }

  if (!activeForm) {
    activeForm = { id: "form-0", dbId: 0, title: "Untitled form", description: "", fields: [] };
  }

  document.querySelector("[data-form-title-preview]").textContent = activeForm.title;
  buildPalette();
  renderCanvas(activeForm);
  renderProperties();
  wireCanvas();
  wireSettings();
  wirePreviewButton();

  // Expose safe builder hooks for the AI assistant module.
  window.AccessFormBuilder = {
    getState: getBuilderState,
    applyMeta: applyMetaSuggestions,
    addFields: addSuggestedFields,
    autoFixAccessibility: autoFixBasicAccessibility,
    refresh: () => {
      persistForm();
      renderCanvas(activeForm);
      renderProperties();
    }
  };
});
