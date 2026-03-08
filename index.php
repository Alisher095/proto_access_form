<?php
require __DIR__ . '/auth/middleware.php';
$userName = $_SESSION['user']['name'] ?? 'User';
$userRole = $_SESSION['user']['role'] ?? 'creator';
$roleLabels = [
  'admin' => 'Admin',
  'creator' => 'Creator',
  'viewer' => 'Viewer'
];
$roleLabel = $roleLabels[$userRole] ?? 'Creator';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>AccessForm | Dashboard</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link href="assets/css/styles.css" rel="stylesheet" />
</head>
<body>
  <a class="skip-link" href="#main">Skip to content</a>
  <nav class="navbar navbar-expand-lg bg-white border-bottom">
    <div class="container">
      <a class="navbar-brand d-flex align-items-center gap-2" href="index.php">
        <img src="assets/images/logo.svg" alt="AccessForm logo" height="32" />
        AccessForm
      </a>
      <div class="d-flex align-items-center gap-3">
        <span class="badge badge-soft" data-user-badge data-user-name="<?php echo htmlspecialchars($userName); ?>">Signed in</span>
        <span class="badge text-bg-light border"><?php echo htmlspecialchars($roleLabel); ?></span>
        <a class="btn btn-outline-secondary btn-sm" href="auth/logout.php">Logout</a>
      </div>
    </div>
  </nav>

  <main id="main" class="container my-4">
    <section class="hero p-4 p-lg-5 mb-4">
      <div class="row align-items-center">
        <div class="col-lg-6">
          <span class="badge badge-soft mb-3">Accessible Form Builder</span>
          <?php if ($userRole === 'admin'): ?>
            <h1 class="display-6 fw-bold">Manage platform forms and users.</h1>
            <p class="lead text-muted">Review all forms, monitor responses, and oversee role access across the system.</p>
          <?php elseif ($userRole === 'viewer'): ?>
            <h1 class="display-6 fw-bold">Review forms and responses.</h1>
            <p class="lead text-muted">Open published forms, preview content, and monitor submissions without editing structure.</p>
          <?php else: ?>
            <h1 class="display-6 fw-bold">Create inclusive forms for everyone.</h1>
            <p class="lead text-muted">Build, preview, and share accessible forms with a dashboard inspired by Google Forms. Designed for PWD-friendly navigation and clear structure.</p>
          <?php endif; ?>
          <div class="d-flex flex-wrap gap-2">
            <?php if ($userRole !== 'viewer'): ?>
              <a class="btn btn-primary" href="builder.php">Start new form</a>
            <?php endif; ?>
            <a class="btn btn-outline-primary" href="preview.php">Preview active form</a>
          </div>
        </div>
        <div class="col-lg-6 text-center mt-4 mt-lg-0">
          <img src="assets/images/hero.svg" alt="Illustration of a form builder" class="img-fluid" />
        </div>
      </div>
    </section>

    <section class="row g-4 mb-4">
      <div class="col-lg-4">
        <div class="card h-100">
          <div class="card-header">Quick actions</div>
          <div class="card-body">
            <div class="d-grid gap-2">
              <?php if ($userRole !== 'viewer'): ?>
                <a class="btn btn-primary" href="builder.php">Open form editor</a>
              <?php endif; ?>
              <a class="btn btn-outline-primary" href="preview.php">Preview form</a>
              <a class="btn btn-outline-secondary" href="responses.php">View responses</a>
              <?php if ($userRole === 'admin'): ?>
                <button class="btn btn-outline-secondary" type="button">Manage users</button>
              <?php endif; ?>
            </div>
          </div>
        </div>
      </div>
      <div class="col-lg-8">
        <div class="card h-100">
          <div class="card-header d-flex justify-content-between align-items-center">
            <span>Your forms</span>
            <span class="badge bg-primary">1 Active</span>
          </div>
          <div class="card-body">
            <div class="row g-3">
              <div class="col-md-6">
                <div class="border rounded-3 p-3 h-100">
                  <h5 class="mb-1">Student Feedback Form</h5>
                  <p class="text-muted small">Updated today • 4 questions</p>
                  <div class="d-flex flex-wrap gap-2">
                    <?php if ($userRole !== 'viewer'): ?>
                      <a class="btn btn-sm btn-outline-primary" href="builder.php">Edit</a>
                    <?php endif; ?>
                    <a class="btn btn-sm btn-outline-secondary" href="preview.php">Preview</a>
                    <a class="btn btn-sm btn-outline-secondary" href="responses.php">Responses</a>
                  </div>
                </div>
              </div>
              <div class="col-md-6">
                <div class="border rounded-3 p-3 h-100">
                  <h5 class="mb-1">New Accessibility Survey</h5>
                  <p class="text-muted small">Draft • 0 responses</p>
                  <?php if ($userRole !== 'viewer'): ?>
                    <button class="btn btn-sm btn-outline-primary" type="button">Create copy</button>
                  <?php else: ?>
                    <span class="text-muted small">View-only access</span>
                  <?php endif; ?>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <section class="row g-4">
      <div class="col-lg-6">
        <div class="card accessibility-card h-100">
          <div class="card-body">
            <?php if ($userRole === 'admin'): ?>
              <h5>Admin checklist</h5>
            <?php elseif ($userRole === 'viewer'): ?>
              <h5>Viewer checklist</h5>
            <?php else: ?>
              <h5>Creator checklist</h5>
            <?php endif; ?>
            <ul class="text-muted">
              <?php if ($userRole === 'admin'): ?>
                <li>Review account roles and monitor suspicious login attempts.</li>
                <li>Validate accessibility compliance across published forms.</li>
                <li>Check platform usage and recent response trends.</li>
                <li>Maintain secure access and audit visibility.</li>
              <?php elseif ($userRole === 'viewer'): ?>
                <li>Preview forms and confirm readable structure.</li>
                <li>Review submissions without modifying form schema.</li>
                <li>Use keyboard navigation for efficient auditing.</li>
                <li>Share response insights with creators/admins.</li>
              <?php else: ?>
                <li>Keyboard focus indicators and skip link enabled.</li>
                <li>Form labels, fieldsets, and legends for screen readers.</li>
                <li>Color contrast verified for primary actions.</li>
                <li>Responsive layout for mobile and desktop.</li>
              <?php endif; ?>
            </ul>
          </div>
        </div>
      </div>
      <div class="col-lg-6">
        <div class="card h-100">
          <div class="card-body">
            <?php if ($userRole === 'admin'): ?>
              <h5>Role management</h5>
              <p class="text-muted">Assign roles and verify access boundaries for dashboard actions.</p>
              <button class="btn btn-outline-primary" type="button">Open role controls</button>
            <?php elseif ($userRole === 'viewer'): ?>
              <h5>Viewer insights</h5>
              <p class="text-muted">Access response summaries and preview forms in read-only mode.</p>
              <button class="btn btn-outline-primary" type="button">Open response insights</button>
            <?php else: ?>
              <h5>AI assist (optional)</h5>
              <p class="text-muted">Suggest questions, auto-generate descriptions, and flag accessibility issues. Toggle in the editor (prototype placeholder).</p>
              <button class="btn btn-outline-primary" type="button">Enable AI assistant</button>
            <?php endif; ?>
          </div>
        </div>
      </div>
    </section>
  </main>

  <footer class="container py-4">
    <div class="d-flex justify-content-between flex-wrap gap-2">
      <span>AccessForm Prototype • VU LMS</span>
      <span>Designed for accessible form creation</span>
    </div>
  </footer>

  <script src="assets/js/app.js"></script>
</body>
</html>
