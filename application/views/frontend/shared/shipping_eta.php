<?php
$window = product_delivery_window($product);
$freeShip = function_exists('product_has_free_shipping') && product_has_free_shipping($product);
if (!$window && !$freeShip) {
    return;
}
$variant = isset($shipping_variant) ? $shipping_variant : 'default';
$freeLabel = function_exists('e_ui') ? e_ui('product.free_shipping') : 'Free shipping';
$showNote = !empty($window) && !empty($window['note']);
?>
<?php if ($variant === 'zenvello'): ?>
<div class="delivery">
  <p class="delivery__head">
    <svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M12 2a7 7 0 0 0-7 7c0 5 7 13 7 13s7-8 7-13a7 7 0 0 0-7-7Zm0 9.5A2.5 2.5 0 1 1 12 6.5a2.5 2.5 0 0 1 0 5Z"/></svg>
    <?= e_ui('product.delivery') ?>
  </p>
  <?php if ($window): ?>
  <p class="delivery__eta">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M1 4h14v11H1z"/><path d="M15 8h4l4 4v3h-8z"/><circle cx="5.5" cy="18" r="2.2"/><circle cx="18" cy="18" r="2.2"/></svg>
    <span><?= htmlspecialchars($window['working']) ?><small><?= htmlspecialchars($window['eta']) ?></small></span>
  </p>
  <?php if ($showNote): ?>
  <p class="delivery__note"><?= htmlspecialchars($window['note']) ?></p>
  <?php endif; ?>
  <?php endif; ?>
  <?php if ($freeShip): ?>
  <p class="delivery__eta"><span><?= htmlspecialchars($freeLabel) ?></span></p>
  <?php endif; ?>
</div>
<?php else: ?>
<p class="shipping-eta"><?php
  $bits = array();
  if ($window) {
      $bits[] = $window['working'] !== '' ? $window['working'] : $window['text'];
  }
  if ($freeShip) {
      $bits[] = $freeLabel;
  }
  echo htmlspecialchars(implode(' ', $bits));
?></p>
<?php if ($showNote): ?>
<p class="shipping-eta-note"><?= htmlspecialchars($window['note']) ?></p>
<?php endif; ?>
<?php endif; ?>
