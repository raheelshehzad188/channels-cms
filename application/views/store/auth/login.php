<div class="auth-wrapper">
  <div class="auth-card">
    <div class="text-center mb-4">
      <h2 class="h4 mb-1">Sign in to your store</h2>
      <p class="text-muted small">Store owner & staff access</p>
    </div>

    <?php $this->load->view('flash'); ?>

    <?php
    $defaults = isset($login_defaults) ? $login_defaults : array('store_domain' => '', 'email' => '', 'password' => '');
    $domainVal = set_value('store_domain', $defaults['store_domain']);
    $emailVal = set_value('email', $defaults['email']);
    ?>
    <form method="post" action="<?= $storeUrl; ?>/login">
      <div class="mb-3">
        <label class="form-label">Store Domain</label>
        <input type="text" name="store_domain" class="form-control" placeholder="fruitables.ecommerce.test" value="<?= htmlspecialchars($domainVal); ?>">
        <div class="form-text">Optional. Each store signs in with its own email and password.</div>
      </div>
      <div class="mb-3">
        <label class="form-label">Email</label>
        <input type="email" name="email" class="form-control" placeholder="owner@store.com" value="<?= htmlspecialchars($emailVal); ?>" required>
      </div>
      <div class="mb-3">
        <label class="form-label">Password</label>
        <input type="password" name="password" class="form-control" value="<?= htmlspecialchars($defaults['password']); ?>" required>
      </div>
      <button type="submit" class="btn btn-store-primary w-100 mb-3">Sign in</button>
      <div class="text-center">
        <a href="<?= $storeUrl; ?>/forgot-password" class="small">Forgot password?</a>
      </div>
    </form>
  </div>
</div>
