<?php $this->load->view('flash'); ?>

<div class="store-card">
  <div class="card-header">Storefront Custom CSS</div>
  <div class="card-body">
    <p class="text-muted">Home, shop and product pages stay on your theme. Cart, checkout, login, signup and profile use the shared layout. You can change colors, spacing and fonts here. HTML cannot be edited.</p>
    <p class="small">Useful classes: <code>.ec-page</code>, <code>.ec-cart</code>, <code>.ec-checkout</code>, <code>.ec-login</code>, <code>.ec-signup</code>, <code>.ec-profile</code>, <code>.ec-btn</code>, <code>.ec-card</code></p>
    <form method="post" action="<?= $storeUrl ?>/custom-css">
      <textarea name="custom_css" class="form-control font-monospace" rows="16" placeholder=".ec-btn { background: #111; color: #fff; }"><?= htmlspecialchars($custom_css) ?></textarea>
      <div class="mt-3">
        <button type="submit" class="btn btn-store-primary">Save CSS</button>
        <a class="btn btn-outline-secondary" href="http://<?= htmlspecialchars(preg_replace('/\.ecommerce\.test$/', '.localhost', $store->domain)) ?>/" target="_blank">Open storefront</a>
      </div>
    </form>
  </div>
</div>
