<?php $checkout_step = 'checkout'; ?>
<section class="ec-page ec-flow-page">
  <header class="ec-flow-head">
    <h1><?= e_ui('checkout.title') ?></h1>
    <p class="ec-lead"><?= e_ui('checkout.lead') ?></p>
    <?php $this->load->view('frontend/shared/_checkout_steps', array('checkout_step' => $checkout_step)); ?>
  </header>

  <?php if (!empty($flash_error)): ?><div class="ec-alert error"><?= htmlspecialchars($flash_error) ?></div><?php endif; ?>

  <div class="ec-flow">
    <div class="ec-flow__main">
      <div class="ec-panel">
        <h2><?= e_ui('checkout.shipping') ?></h2>
        <form class="ec-form" method="post" action="<?= storefront_url('checkout') ?>" data-address-form>
          <?php $this->load->view('frontend/shared/_address_fields', array(
              'addr' => isset($addr) ? $addr : array(),
              'countries' => isset($countries) ? $countries : array(),
              'show_billing' => true,
          )); ?>
          <h2><?= e_ui('checkout.shipping_method') ?></h2>
          <label class="ec-ship-method">
            <input type="radio" name="shipping_method" value="standard" checked>
            <span>
              <strong><?= e_ui('checkout.shipping_standard') ?></strong>
              <em><?php if (!empty($shipping_free) || (isset($shipping_amount) && (float) $shipping_amount <= 0)): ?><?= e_ui('cart.shipping_free') ?><?php else: ?><?= format_money(isset($shipping_amount) ? (float) $shipping_amount : 0) ?><?php endif; ?></em>
            </span>
          </label>
          <?php
            $checkoutMin = null;
            $checkoutMax = null;
            if (!empty($cart_items) && function_exists('product_ship_days')) {
                foreach ($cart_items as $cItem) {
                    $days = product_ship_days($cItem);
                    if (!$days) {
                        continue;
                    }
                    $checkoutMin = $checkoutMin === null ? $days['min'] : min($checkoutMin, $days['min']);
                    $checkoutMax = $checkoutMax === null ? $days['max'] : max($checkoutMax, $days['max']);
                }
            }
            if ($checkoutMin !== null && $checkoutMax !== null):
          ?>
          <p class="ec-ship-eta"><?= e_ui('cart.delivery_eta', array('{min}' => (string) $checkoutMin, '{max}' => (string) $checkoutMax)) ?></p>
          <p class="ec-ship-eta-note"><?= e_ui('product.eta_vary') ?></p>
          <?php endif; ?>
          <label class="ec-choice">
            <input type="checkbox" name="terms" value="1" required>
            <span><?= e_ui('checkout.terms') ?> * <a href="<?= storefront_url('terms-of-service') ?>" target="_blank" rel="noopener"><?= e_ui('contact.terms') ?></a></span>
          </label>
          <button class="ec-btn block" type="submit"><?= e_ui('checkout.continue') ?></button>
        </form>
      </div>
    </div>

    <aside class="ec-summary">
      <h2><?= e_ui('cart.summary') ?></h2>
      <?php if (empty($cart_items)): ?>
        <p class="ec-lead"><?= e_ui('cart.empty') ?></p>
        <a class="ec-btn" href="<?= storefront_url('shop') ?>"><?= e_ui('cart.go_shop') ?></a>
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
          <?php if (!empty($show_shipping) || (isset($shipping_amount) && (float) $shipping_amount > 0)): ?>
            <li>
              <span class="ec-line__ph"></span>
              <div>
                <b><?= e_ui('cart.shipping') ?></b>
                <span>× <?= isset($shipping_qty) ? (int) $shipping_qty : 0 ?></span>
              </div>
              <strong><?php if (!empty($shipping_free)): ?><?= e_ui('cart.shipping_free') ?><?php else: ?><?= format_money(isset($shipping_amount) ? (float) $shipping_amount : 0) ?><?php endif; ?></strong>
            </li>
          <?php endif; ?>
        </ul>
        <dl>
          <?php $this->load->view('frontend/shared/_cart_totals'); ?>
        </dl>
        <a class="ec-summary__shop" href="<?= storefront_url('cart') ?>"><?= e_ui('cart.edit') ?></a>
      <?php endif; ?>
    </aside>
  </div>
</section>
