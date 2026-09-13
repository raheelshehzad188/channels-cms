<?php
$window = product_delivery_window($product);
if (!$window) {
    return;
}
$variant = isset($shipping_variant) ? $shipping_variant : 'default';
?>
<?php if ($variant === 'zenvello'): ?>
<div class="delivery">
  <p class="delivery__head">
    <svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M12 2a7 7 0 0 0-7 7c0 5 7 13 7 13s7-8 7-13a7 7 0 0 0-7-7Zm0 9.5A2.5 2.5 0 1 1 12 6.5a2.5 2.5 0 0 1 0 5Z"/></svg>
    Delivery
  </p>
  <p class="delivery__eta">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M1 4h14v11H1z"/><path d="M15 8h4l4 4v3h-8z"/><circle cx="5.5" cy="18" r="2.2"/><circle cx="18" cy="18" r="2.2"/></svg>
    <span>Estimated delivery: <?= htmlspecialchars($window['short']) ?>
      <small><?= htmlspecialchars($window['text']) ?></small>
    </span>
  </p>
</div>
<?php else: ?>
<p class="shipping-eta"><?= htmlspecialchars($window['text']) ?></p>
<?php endif; ?>
