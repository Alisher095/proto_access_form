const StorageKeys = {
  USER: "afp_user"
};

const setCurrentUser = (user) => localStorage.setItem(StorageKeys.USER, JSON.stringify(user));

const getCurrentUser = () => JSON.parse(localStorage.getItem(StorageKeys.USER) || "null");

const apiRequest = async (url, options = {}) => {
  const response = await fetch(url, {
    headers: {
      "Content-Type": "application/json",
      ...(options.headers || {})
    },
    ...options
  });

  let data = {};
  try {
    data = await response.json();
  } catch (_err) {
    throw new Error("Server returned invalid JSON.");
  }

  if (!response.ok || data.ok === false) {
    throw new Error(data.error || `Request failed with status ${response.status}`);
  }

  return data;
};

const fetchActiveForm = async () => {
  const data = await apiRequest("api/forms.php", { method: "GET" });
  return data.form || null;
};

const saveActiveForm = async (form) => {
  const data = await apiRequest("api/forms.php", {
    method: "POST",
    body: JSON.stringify({ form })
  });
  return data.form || null;
};

const submitFormResponse = async (formId, values) => {
  const dbFormId = typeof formId === "string" ? Number(formId.replace("form-", "")) : Number(formId);
  return apiRequest("api/form-responses.php", {
    method: "POST",
    body: JSON.stringify({ formId: dbFormId, values })
  });
};

const fetchFormResponses = async (formId) => {
  const dbFormId = typeof formId === "string" ? Number(formId.replace("form-", "")) : Number(formId);
  const query = Number.isFinite(dbFormId) && dbFormId > 0 ? `?form_id=${dbFormId}` : "";
  const data = await apiRequest(`api/form-responses.php${query}`, { method: "GET" });
  return Array.isArray(data.responses) ? data.responses : [];
};

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
  ensureAuthBadge();
  wireLogout();
});
