<section class="ec-page ec-flow-page">
  <div class="ec-empty ec-thanks">
    <div class="ec-empty__icon is-ok" aria-hidden="true">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg>
    </div>
    <h1><?= e_ui('thanks.title') ?></h1>
    <?php if (!empty($order)): ?>
      <p class="ec-lead"><?= strtr(store_ui('thanks.placed'), array('{order}' => '<strong>' . htmlspecialchars($order->order_no) . '</strong>')) ?></p>
      <p>
        <?= e_ui('cart.subtotal') ?>: <?= format_money((float) $order->subtotal, $order->currency) ?>
        <?php if (!empty($order->shipping_amount) && (float) $order->shipping_amount > 0): ?>
          · <?= e_ui('cart.shipping_qty', array('{n}' => (int) (isset($order->shipping_qty) ? $order->shipping_qty : 0))) ?>: <?= format_money((float) $order->shipping_amount, $order->currency) ?>
        <?php elseif (!empty($order->shipping_qty) && (int) $order->shipping_qty > 0): ?>
          · <?= e_ui('cart.shipping') ?>: <?= e_ui('cart.shipping_free') ?>
        <?php endif; ?>
        <?php if (!empty($order->vat_amount) && (float) $order->vat_amount > 0): ?>
          · <?= e_ui('cart.vat', array('{percent}' => number_format((float) $order->vat_percent, 2))) ?>: <?= format_money((float) $order->vat_amount, $order->currency) ?>
        <?php endif; ?>
      </p>
      <p><?= e_ui('thanks.total_paid') ?>: <strong><?= format_money((float) $order->total, $order->currency) ?></strong>
        <?php if (!empty($order->payment_method)): ?>
          · <?= e_ui('thanks.via', array('{method}' => $order->payment_method === 'card' ? store_ui('pay.method_card') : store_ui('pay.method_paypal'))) ?>
        <?php endif; ?>
      </p>
      <p class="ec-lead"><?= e_ui('thanks.email') ?></p>
    <?php else: ?>
      <p class="ec-lead"><?= e_ui('thanks.ok') ?></p>
    <?php endif; ?>
    <?php if (!empty($order) && storefront_customer()): ?>
      <a class="ec-btn" href="<?= storefront_url('account/orders/' . rawurlencode($order->order_no)) ?>"><?= e_ui('thanks.view_order') ?></a>
      <a class="ec-btn secondary" href="<?= storefront_url('shop') ?>"><?= e_ui('cart.continue') ?></a>
    <?php else: ?>
      <a class="ec-btn" href="<?= storefront_url('shop') ?>"><?= e_ui('cart.continue') ?></a>
    <?php endif; ?>
  </div>
</section>
