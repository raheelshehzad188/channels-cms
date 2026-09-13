<?php
$step = isset($checkout_step) ? $checkout_step : 'cart';
$order = array('cart' => 'Cart', 'checkout' => 'Shipping', 'payment' => 'Payment');
$keys = array_keys($order);
$currentIndex = array_search($step, $keys, true);
if ($currentIndex === false) {
    $currentIndex = 0;
}
?>
<ol class="ec-steps" aria-label="Checkout progress">
  <?php foreach ($order as $key => $label): ?>
    <?php
      $i = array_search($key, $keys, true);
      $state = $i < $currentIndex ? 'is-done' : ($i === $currentIndex ? 'is-current' : '');
      $href = $key === 'cart' ? storefront_url('cart') : ($key === 'checkout' ? storefront_url('checkout') : '');
    ?>
    <li class="<?= $state ?>">
      <?php if ($state === 'is-done' && $href): ?>
        <a href="<?= $href ?>"><?= htmlspecialchars($label) ?></a>
      <?php else: ?>
        <span><?= htmlspecialchars($label) ?></span>
      <?php endif; ?>
    </li>
  <?php endforeach; ?>
</ol>
