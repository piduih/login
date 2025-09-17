<?php require_once __DIR__ . '/../includes/header_front.php'; ?>

<div class="row justify-content-center">
  <div class="col-md-6">
    <div class="card shadow-sm">
      <div class="card-body">
        <h3 class="card-title mb-3">Sign in to your account</h3>
        <p class="text-muted small">Enter your email/username and password.</p>
        <div id="auth-alert"></div>
        <form id="login-form">
      <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf); ?>">
      <div class="mb-3">
        <label for="email" class="form-label">Email or Username</label>
        <input type="text" class="form-control" id="email" name="email" required>
      </div>
      <div class="mb-3">
        <label for="password" class="form-label">Password</label>
        <input type="password" class="form-control" id="password" name="password" required>
      </div>
      <div class="mb-3 form-check">
        <input type="checkbox" class="form-check-input" id="remember" name="remember" value="1" <?php echo $remember_present ? 'checked' : ''; ?>>
        <label class="form-check-label" for="remember">Remember me</label>
        <span class="ms-2" data-bs-toggle="tooltip" title="Only use on trusted private devices. This keeps you logged in for 30 days."> 
          <i class="bi bi-info-circle"></i>
        </span>
      </div>
          <div class="d-flex gap-2">
            <button type="submit" class="btn btn-primary">Login</button>
            <a href="/auth/signup.php" class="btn btn-outline-secondary">Create account</a>
            <a href="/auth/request_reset.php" class="ms-auto align-self-center small">Forgot?</a>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

<?php require_once __DIR__ . '/../includes/footer_front.php'; ?>
