const renderResponses = async () => {
  let form = null;
  try {
    form = await fetchActiveForm();
  } catch (error) {
    document.querySelector("[data-response-table]").innerHTML = `<tr><td colspan="3" class="text-danger">Failed to load form: ${error.message}</td></tr>`;
    return;
  }

  if (!form) {
    document.querySelector("[data-response-table]").innerHTML = `<tr><td colspan="3" class="text-muted">No active form found.</td></tr>`;
    return;
  }

  let entries = [];
  try {
    entries = await fetchFormResponses(form.dbId || form.id);
  } catch (error) {
    document.querySelector("[data-response-table]").innerHTML = `<tr><td colspan="3" class="text-danger">Failed to load responses: ${error.message}</td></tr>`;
    return;
  }

  document.querySelector("[data-response-count]").textContent = entries.length;
  const tableBody = document.querySelector("[data-response-table]");
  tableBody.innerHTML = "";
  if (!entries.length) {
    tableBody.innerHTML = `<tr><td colspan="3" class="text-muted">No responses yet.</td></tr>`;
    return;
  }
  entries.forEach((entry, index) => {
    const row = document.createElement("tr");
    row.innerHTML = `
      <td>#${index + 1}</td>
      <td>${new Date(entry.submittedAt).toLocaleString()}</td>
      <td>${Object.entries(entry.values).map(([label, value]) => `<div><strong>${label}:</strong> ${value || "-"}</div>`).join("")}</td>
    `;
    tableBody.appendChild(row);
  });
};

document.addEventListener("DOMContentLoaded", () => {
  renderResponses();
});
