const handleAuth = (event) => {
  event.preventDefault();
  const form = event.target;
  const name = form.querySelector("[name='name']")?.value || "User";
  const email = form.querySelector("[name='email']")?.value || "user@example.com";
  setCurrentUser({ name, email });
  window.location.href = "index.php";
};

document.addEventListener("DOMContentLoaded", () => {
  const form = document.querySelector("[data-auth-form]");
  if (form) {
    form.addEventListener("submit", handleAuth);
  }
});
