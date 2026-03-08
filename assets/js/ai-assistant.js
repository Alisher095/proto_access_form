const postJson = async (url, payload) => {
  const response = await fetch(url, {
    method: "POST",
    headers: { "Content-Type": "application/json" },
    body: JSON.stringify(payload)
  });

  let data = {};
  try {
    data = await response.json();
  } catch (_err) {
    throw new Error("AI endpoint returned invalid response.");
  }

  if (!response.ok || !data.ok) {
    throw new Error(data.error || `Request failed (${response.status})`);
  }

  return data;
};

const renderNotes = (notes = []) => {
  if (!Array.isArray(notes) || !notes.length) {
    return "";
  }
  return `<ul class="ai-notes">${notes.map((note) => `<li>${note}</li>`).join("")}</ul>`;
};

const initializeAiAssistant = () => {
  const panel = document.querySelector("[data-ai-assistant]");
  if (!panel) return;

  const userRole = (document.body.dataset.userRole || "creator").toLowerCase();
  const topicInput = panel.querySelector("[data-ai-topic]");
  const status = panel.querySelector("[data-ai-status]");
  const results = panel.querySelector("[data-ai-results]");
  const btnMeta = panel.querySelector("[data-ai-generate-meta]");
  const btnFields = panel.querySelector("[data-ai-suggest-fields]");
  const btnA11y = panel.querySelector("[data-ai-check-a11y]");

  const setStatus = (text, tone = "muted") => {
    status.className = `ai-status mt-2 small text-${tone}`;
    status.textContent = text;
  };

  const setBusy = (busy) => {
    [btnMeta, btnFields, btnA11y].forEach((btn) => {
      if (btn) btn.disabled = busy;
    });
  };

  if (!window.AccessFormBuilder) {
    setStatus("Builder hooks unavailable. Refresh the page.", "danger");
    return;
  }

  if (userRole !== "creator") {
    setStatus("AI assistant is available for Creator role only.", "warning");
    setBusy(true);
    results.innerHTML = '<div class="alert alert-warning py-2 mb-0">Switch to a Creator account to use AI actions.</div>';
    return;
  }

  const loadFormState = () => window.AccessFormBuilder.getState();

  btnMeta.addEventListener("click", async () => {
    setBusy(true);
    setStatus("Generating title and description...", "primary");
    try {
      const data = await postJson("api/ai-suggest.php", {
        action: "generate_meta",
        topic: topicInput.value,
        form: loadFormState()
      });

      const meta = data.result || {};
      window.AccessFormBuilder.applyMeta(meta);
      results.innerHTML = `
        <div class="ai-result-card">
          <h6 class="mb-1">Generated Meta</h6>
          <p class="mb-1"><strong>Title:</strong> ${meta.title || "-"}</p>
          <p class="mb-1"><strong>Description:</strong> ${meta.description || "-"}</p>
          ${renderNotes(meta.notes)}
        </div>
      `;
      setStatus("Title and description applied.", "success");
    } catch (error) {
      setStatus(error.message, "danger");
    } finally {
      setBusy(false);
    }
  });

  btnFields.addEventListener("click", async () => {
    setBusy(true);
    setStatus("Generating field suggestions...", "primary");
    try {
      const data = await postJson("api/ai-suggest.php", {
        action: "suggest_fields",
        topic: topicInput.value,
        form: loadFormState()
      });

      const fields = Array.isArray(data.result?.fields) ? data.result.fields : [];
      const addedCount = window.AccessFormBuilder.addFields(fields);
      results.innerHTML = `
        <div class="ai-result-card">
          <h6 class="mb-1">Suggested Fields</h6>
          <p class="mb-1">Added <strong>${addedCount}</strong> field(s) to the form.</p>
          <ul class="ai-notes mb-1">
            ${fields.map((field) => `<li>${field.label} (${field.type})</li>`).join("")}
          </ul>
          ${renderNotes(data.result?.notes || [])}
        </div>
      `;
      setStatus("Field suggestions applied.", "success");
    } catch (error) {
      setStatus(error.message, "danger");
    } finally {
      setBusy(false);
    }
  });

  btnA11y.addEventListener("click", async () => {
    setBusy(true);
    setStatus("Checking accessibility...", "primary");
    try {
      const data = await postJson("api/ai-a11y-check.php", {
        form: loadFormState()
      });

      const warnings = Array.isArray(data.result?.warnings) ? data.result.warnings : [];
      if (!warnings.length) {
        results.innerHTML = `
          <div class="ai-result-card">
            <h6 class="mb-1">Accessibility Check</h6>
            <p class="mb-1 text-success">${data.result?.summary || "No major issues found."}</p>
            <p class="mb-0">Score: <strong>${data.result?.score ?? 100}</strong></p>
          </div>
        `;
      } else {
        results.innerHTML = `
          <div class="ai-result-card">
            <h6 class="mb-1">Accessibility Check</h6>
            <p class="mb-1">Score: <strong>${data.result?.score ?? 0}</strong></p>
            <p class="mb-2">${data.result?.summary || "Issues found."}</p>
            <ul class="ai-notes mb-2">${warnings.map((item) => `<li>[${item.severity}] ${item.message}</li>`).join("")}</ul>
            <button type="button" class="btn btn-sm btn-outline-secondary" data-ai-autofix>Auto-fix basics</button>
          </div>
        `;

        const autoFixBtn = results.querySelector("[data-ai-autofix]");
        if (autoFixBtn) {
          autoFixBtn.addEventListener("click", () => {
            window.AccessFormBuilder.autoFixAccessibility();
            setStatus("Basic accessibility fixes applied. Run check again.", "success");
          });
        }
      }
      if (!warnings.length) {
        setStatus("Accessibility check complete.", "success");
      } else {
        setStatus("Accessibility issues found. Review warnings below.", "warning");
      }
    } catch (error) {
      setStatus(error.message, "danger");
    } finally {
      setBusy(false);
    }
  });
};

document.addEventListener("DOMContentLoaded", initializeAiAssistant);
