<?php $checkout_step = 'checkout'; ?>
<section class="ec-page ec-flow-page">
  <header class="ec-flow-head">
    <h1>Checkout</h1>
    <p class="ec-lead">Shipping details for <?= htmlspecialchars($store->name) ?>.</p>
    <?php $this->load->view('frontend/shared/_checkout_steps', array('checkout_step' => $checkout_step)); ?>
  </header>

  <?php if (!empty($flash_error)): ?><div class="ec-alert error"><?= htmlspecialchars($flash_error) ?></div><?php endif; ?>

  <div class="ec-flow">
    <div class="ec-flow__main">
      <div class="ec-panel">
        <h2>Shipping details</h2>
        <form class="ec-form" method="post" action="<?= storefront_url('checkout') ?>">
          <label>Full name</label>
          <input type="text" name="name" required value="<?= htmlspecialchars($customer ? $customer->name : '') ?>" autocomplete="name">
          <div class="ec-form__row">
            <div>
              <label>Email</label>
              <input type="email" name="email" required value="<?= htmlspecialchars($customer ? $customer->email : '') ?>" autocomplete="email">
            </div>
            <div>
              <label>Phone</label>
              <input type="text" name="phone" value="<?= htmlspecialchars($customer && !empty($customer->phone) ? $customer->phone : '') ?>" autocomplete="tel">
            </div>
          </div>
          <label>Address</label>
          <textarea name="address" rows="4" required><?= htmlspecialchars($customer && !empty($customer->address) ? $customer->address : '') ?></textarea>
          <button class="ec-btn block" type="submit">Continue to payment</button>
        </form>
      </div>
    </div>

    <aside class="ec-summary">
      <h2>Order summary</h2>
      <?php if (empty($cart_items)): ?>
        <p class="ec-lead">Your cart is empty.</p>
        <a class="ec-btn" href="<?= storefront_url('shop') ?>">Go to shop</a>
      <?php else: ?>
        <ul class="ec-mini">
          <?php foreach ($cart_items as $item): ?>
            <li>
              <?php if (!empty($item->image)): ?>
                <img src="<?= htmlspecialchars($item->image) ?>" alt="">
              <?php else: ?>
                <span class="ec-line__ph"></span>
              <?php endif; ?>
              <div>
                <b><?= htmlspecialchars($item->name) ?></b>
                <span>× <?= (int) $item->qty ?></span>
              </div>
              <strong><?= format_money((float) $item->line_total) ?></strong>
            </li>
          <?php endforeach; ?>
        </ul>
        <dl>
          <div><dt>Subtotal</dt><dd><?= format_money((float) $cart_subtotal) ?></dd></div>
          <?php if (!empty($vat_percent) && (float) $vat_percent > 0): ?>
            <div><dt>VAT (<?= number_format((float) $vat_percent, 2) ?>%)</dt><dd><?= format_money((float) $vat_amount) ?></dd></div>
          <?php endif; ?>
          <div class="ec-summary__total"><dt>Total</dt><dd><?= format_money((float) $cart_total) ?></dd></div>
        </dl>
        <a class="ec-summary__shop" href="<?= storefront_url('cart') ?>">Edit cart</a>
      <?php endif; ?>
    </aside>
  </div>
</section>
