<?php require_once __DIR__ . '/../includes/header_front.php'; ?>

<div class="row justify-content-center">
  <div class="col-md-6">
    <div class="card shadow-sm">
      <div class="card-body">
        <h3 class="card-title mb-3">Reset your password</h3>
        <p class="text-muted small">Enter your email to receive a secure reset link.</p>
        <div id="auth-alert"></div>
        <form id="request-reset-form">
      <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf); ?>">
      <div class="mb-3">
        <label for="email" class="form-label">Email</label>
        <input type="email" class="form-control" id="email" name="email" required>
      </div>
          <div class="d-flex gap-2">
            <button type="submit" class="btn btn-primary">Send reset link</button>
            <a href="/auth/login.php" class="btn btn-outline-secondary">Login</a>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

<?php require_once __DIR__ . '/../includes/footer_front.php'; ?>
