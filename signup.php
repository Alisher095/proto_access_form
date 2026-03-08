<?php
$error = $_GET['error'] ?? '';
$message = '';
if ($error === 'missing') {
    $message = 'Please fill out all fields.';
} elseif ($error === 'email') {
    $message = 'Enter a valid email address.';
} elseif ($error === 'exists') {
    $message = 'An account with that email already exists.';
} elseif ($error === 'server') {
    $message = 'Something went wrong. Please try again.';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>AccessForm | Sign Up</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link href="assets/css/styles.css" rel="stylesheet" />
</head>
<body class="d-flex align-items-center min-vh-100">
  <a class="skip-link" href="#main">Skip to content</a>
  <main id="main" class="container">
    <div class="row justify-content-center">
      <div class="col-lg-5">
        <div class="card p-4">
          <div class="text-center mb-3">
            <img src="assets/images/logo.svg" alt="AccessForm logo" height="36" />
            <h1 class="h4 mt-3">Create your account</h1>
            <p class="text-muted">Start building accessible forms in minutes.</p>
          </div>
          <?php if ($message): ?>
            <div class="alert alert-danger" role="alert"><?php echo htmlspecialchars($message); ?></div>
          <?php endif; ?>
          <form data-auth-form aria-label="Sign up form" method="post" action="auth/register.php">
            <div class="mb-3">
              <label class="form-label" for="name">Full name</label>
              <input class="form-control" id="name" name="name" type="text" autocomplete="name" required />
            </div>
            <div class="mb-3">
              <label class="form-label" for="email">Email</label>
              <input class="form-control" id="email" name="email" type="email" autocomplete="email" required />
            </div>
            <div class="mb-3">
              <label class="form-label" for="password">Password</label>
              <input class="form-control" id="password" name="password" type="password" autocomplete="new-password" required />
            </div>
            <div class="mb-3">
              <label class="form-label" for="role">Role</label>
              <select class="form-select" id="role" name="role" required>
                <option value="creator" selected>Creator</option>
                <option value="viewer">Viewer</option>
                <option value="admin">Admin</option>
              </select>
            </div>
            <button class="btn btn-primary w-100" type="submit">Create account</button>
          </form>
          <div class="text-center mt-3">
            <a href="login.php">Already have an account?</a>
          </div>
        </div>
      </div>
    </div>
  </main>
  <script src="assets/js/app.js"></script>
</body>
</html>
