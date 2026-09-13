<div class="auth-wrapper">
  <div class="auth-card">
    <div class="text-center mb-4">
      <h2 class="h4 mb-1">Reset password</h2>
      <p class="text-muted small"><?= htmlspecialchars($store->domain); ?></p>
    </div>

    <?php $this->load->view('flash'); ?>

    <form method="post" action="<?= $storeUrl; ?>/reset-password/<?= $token; ?>">
      <div class="mb-3">
        <label class="form-label">New Password</label>
        <input type="password" name="password" class="form-control" required minlength="6">
      </div>
      <div class="mb-3">
        <label class="form-label">Confirm Password</label>
        <input type="password" name="confirm_password" class="form-control" required>
      </div>
      <button type="submit" class="btn btn-store-primary w-100">Update password</button>
    </form>
  </div>
</div>
