<section class="ec-page ec-flow-page">
  <div class="ec-empty ec-thanks">
    <div class="ec-empty__icon is-ok" aria-hidden="true">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg>
    </div>
    <h1>Thank you</h1>
    <?php if (!empty($order)): ?>
      <p class="ec-lead">Order <strong><?= htmlspecialchars($order->order_no) ?></strong> was placed successfully.</p>
      <p>
        Subtotal: <?= format_money((float) $order->subtotal, $order->currency) ?>
        <?php if (!empty($order->vat_amount) && (float) $order->vat_amount > 0): ?>
          · VAT (<?= number_format((float) $order->vat_percent, 2) ?>%): <?= format_money((float) $order->vat_amount, $order->currency) ?>
        <?php endif; ?>
      </p>
      <p>Total paid: <strong><?= format_money((float) $order->total, $order->currency) ?></strong>
        <?php if (!empty($order->payment_method)): ?>
          · via <?= htmlspecialchars($order->payment_method === 'card' ? 'Card' : 'PayPal') ?>
        <?php endif; ?>
      </p>
      <p class="ec-lead">A confirmation email is on its way. You can keep shopping in the meantime.</p>
    <?php else: ?>
      <p class="ec-lead">Your order was placed successfully.</p>
    <?php endif; ?>
    <a class="ec-btn" href="<?= storefront_url('shop') ?>">Continue shopping</a>
  </div>
</section>
