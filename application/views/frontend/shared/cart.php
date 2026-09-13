<?php
$checkout_step = 'cart';
$itemCount = 0;
foreach ($cart_items as $item) {
    $itemCount += (int) $item->qty;
}
?>
<section class="ec-page ec-flow-page">
  <header class="ec-flow-head">
    <h1>Your cart</h1>
    <p class="ec-lead"><?= $itemCount ? $itemCount . ' item' . ($itemCount === 1 ? '' : 's') . ' ready to checkout.' : 'Add something you like — it will show up here.' ?></p>
    <?php $this->load->view('frontend/shared/_checkout_steps', array('checkout_step' => $checkout_step)); ?>
  </header>

  <?php if (!empty($flash_success)): ?><div class="ec-alert success"><?= htmlspecialchars($flash_success) ?></div><?php endif; ?>
  <?php if (!empty($flash_error)): ?><div class="ec-alert error"><?= htmlspecialchars($flash_error) ?></div><?php endif; ?>

  <?php if (empty($cart_items)): ?>
    <div class="ec-empty">
      <div class="ec-empty__icon" aria-hidden="true">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="M4 5h2l2.2 10.2h9.4L20 8H7"/><circle cx="10" cy="19" r="1.6"/><circle cx="17.5" cy="19" r="1.6"/></svg>
      </div>
      <h2>Your cart is empty</h2>
      <p>Browse the shop and add items when you are ready.</p>
      <a class="ec-btn" href="<?= storefront_url('shop') ?>">Continue shopping</a>
    </div>
  <?php else: ?>
    <form class="ec-flow" method="post" action="<?= storefront_url('cart/update') ?>">
      <div class="ec-flow__main">
        <ul class="ec-lines">
          <?php foreach ($cart_items as $item): ?>
            <li class="ec-line">
              <a class="ec-line__media" href="<?= product_url($item) ?>">
                <?php if (!empty($item->image)): ?>
                  <img src="<?= htmlspecialchars($item->image) ?>" alt="">
                <?php else: ?>
                  <span class="ec-line__ph"></span>
                <?php endif; ?>
              </a>
              <div class="ec-line__info">
                <a class="ec-line__name" href="<?= product_url($item) ?>"><?= htmlspecialchars($item->name) ?></a>
                <p class="ec-line__meta"><?= format_money((float) $item->price) ?> each</p>
                <a class="ec-line__remove" href="<?= storefront_url('cart/remove/' . $item->id) ?>">Remove</a>
              </div>
              <div class="ec-qtybox">
                <button type="button" data-qty="minus" aria-label="Decrease quantity">−</button>
                <input type="number" min="1" name="qty[<?= (int) $item->id ?>]" value="<?= (int) $item->qty ?>">
                <button type="button" data-qty="plus" aria-label="Increase quantity">+</button>
              </div>
              <div class="ec-line__total"><?= format_money((float) $item->line_total) ?></div>
            </li>
          <?php endforeach; ?>
        </ul>
        <button class="ec-btn ghost" type="submit">Update quantities</button>
      </div>

      <aside class="ec-summary">
        <h2>Order summary</h2>
        <dl>
          <div><dt>Subtotal</dt><dd><?= format_money((float) $cart_subtotal) ?></dd></div>
          <?php if (!empty($vat_percent) && (float) $vat_percent > 0): ?>
            <div><dt>VAT (<?= number_format((float) $vat_percent, 2) ?>%)</dt><dd><?= format_money((float) $vat_amount) ?></dd></div>
          <?php endif; ?>
          <div class="ec-summary__total"><dt>Total</dt><dd><?= format_money((float) $cart_total) ?></dd></div>
        </dl>
        <a class="ec-btn block" href="<?= storefront_url('checkout') ?>">Checkout</a>
        <a class="ec-summary__shop" href="<?= storefront_url('shop') ?>">Continue shopping</a>
      </aside>
    </form>
  <?php endif; ?>
</section>
<script>
(function () {
  document.querySelectorAll('.ec-qtybox').forEach(function (box) {
    var input = box.querySelector('input');
    box.addEventListener('click', function (e) {
      var btn = e.target.closest('[data-qty]');
      if (!btn || !input) return;
      var n = parseInt(input.value, 10) || 1;
      input.value = btn.getAttribute('data-qty') === 'plus' ? n + 1 : Math.max(1, n - 1);
    });
  });
})();
</script>
