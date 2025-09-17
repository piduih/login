<?php require_once __DIR__ . '/../includes/header_front.php'; ?>

<?php $token = htmlspecialchars($_GET['token'] ?? ''); ?>

<div class="row justify-content-center">
  <div class="col-md-6">
    <div class="card shadow-sm">
      <div class="card-body">
        <h3 class="card-title mb-3">Set a new password</h3>
        <p class="text-muted small">Choose a strong password for your account.</p>
        <div id="auth-alert"></div>
        <form id="reset-form">
      <input type="hidden" name="token" id="token" value="<?php echo $token; ?>">
      <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf); ?>">
      <div class="mb-3">
        <label for="password" class="form-label">New password</label>
        <input type="password" class="form-control" id="password" name="password" required>
        <div class="mt-2">
          <div class="progress" style="height:8px;"><div id="pw-strength-bar" class="progress-bar" role="progressbar" style="width:0%"></div></div>
          <small id="pw-strength-text" class="form-text text-muted"></small>
          <ul id="pw-suggestions" class="list-unstyled small mt-1 text-muted"></ul>
        </div>
      </div>
          <div class="d-flex gap-2">
            <button type="submit" class="btn btn-primary">Reset password</button>
            <a href="/auth/login.php" class="btn btn-outline-secondary">Login</a>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

<?php require_once __DIR__ . '/../includes/footer_front.php'; ?>
