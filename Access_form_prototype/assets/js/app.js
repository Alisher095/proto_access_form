const StorageKeys = {
  FORMS: "afp_forms",
  ACTIVE_FORM: "afp_active_form",
  RESPONSES: "afp_responses",
  USER: "afp_user"
};

const seedForm = () => ({
  id: "form-001",
  title: "Student Feedback Form",
  description: "Help us improve by sharing your experience.",
  fields: [
    { id: "f1", type: "text", label: "Full name", required: true },
    { id: "f2", type: "email", label: "Email address", required: true },
    { id: "f3", type: "radio", label: "Overall satisfaction", required: true, options: ["Excellent", "Good", "Average", "Needs improvement"] },
    { id: "f4", type: "dropdown", label: "Program", required: false, options: ["BSCS", "BBA", "MBA", "Other"] }
  ]
});

const ensureSeed = () => {
  if (!localStorage.getItem(StorageKeys.FORMS)) {
    localStorage.setItem(StorageKeys.FORMS, JSON.stringify([seedForm()]));
    localStorage.setItem(StorageKeys.ACTIVE_FORM, "form-001");
  }
  if (!localStorage.getItem(StorageKeys.RESPONSES)) {
    localStorage.setItem(StorageKeys.RESPONSES, JSON.stringify({ "form-001": [] }));
  }
};

const loadForms = () => JSON.parse(localStorage.getItem(StorageKeys.FORMS) || "[]");

const saveForms = (forms) => localStorage.setItem(StorageKeys.FORMS, JSON.stringify(forms));

const loadResponses = () => JSON.parse(localStorage.getItem(StorageKeys.RESPONSES) || "{}");

const saveResponses = (responses) => localStorage.setItem(StorageKeys.RESPONSES, JSON.stringify(responses));

const setActiveForm = (formId) => localStorage.setItem(StorageKeys.ACTIVE_FORM, formId);

const getActiveFormId = () => localStorage.getItem(StorageKeys.ACTIVE_FORM);

const getActiveForm = () => {
  const forms = loadForms();
  const activeId = getActiveFormId();
  return forms.find((form) => form.id === activeId) || forms[0];
};

const setCurrentUser = (user) => localStorage.setItem(StorageKeys.USER, JSON.stringify(user));

const getCurrentUser = () => JSON.parse(localStorage.getItem(StorageKeys.USER) || "null");

const ensureAuthBadge = () => {
  const badge = document.querySelector("[data-user-badge]");
  if (!badge) return;
  const serverName = badge.dataset.userName;
  if (serverName) {
    badge.textContent = `Signed in: ${serverName}`;
    return;
  }
  const user = getCurrentUser();
  badge.textContent = user ? `Signed in: ${user.name}` : "Guest mode";
};

const wireLogout = () => {
  const btn = document.querySelector("[data-logout]");
  if (!btn) return;
  btn.addEventListener("click", () => {
    localStorage.removeItem(StorageKeys.USER);
    window.location.href = "login.php";
  });
};

document.addEventListener("DOMContentLoaded", () => {
  ensureSeed();
  ensureAuthBadge();
  wireLogout();
});
