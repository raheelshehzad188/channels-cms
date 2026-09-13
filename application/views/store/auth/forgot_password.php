<div class="auth-wrapper">
  <div class="auth-card">
    <div class="text-center mb-4">
      <h2 class="h4 mb-1">Forgot password</h2>
      <p class="text-muted small">We'll help you reset your store password</p>
    </div>

    <?php $this->load->view('flash'); ?>

    <form method="post" action="<?= $storeUrl; ?>/forgot-password">
      <div class="mb-3">
        <label class="form-label">Email</label>
        <input type="email" name="email" class="form-control" required>
      </div>
      <button type="submit" class="btn btn-store-primary w-100 mb-3">Send reset link</button>
      <div class="text-center">
        <a href="<?= $storeUrl; ?>/login" class="small">Back to login</a>
      </div>
    </form>
  </div>
</div>
