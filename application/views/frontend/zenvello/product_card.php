<?php
$img = !empty($product->image)
    ? base_url($product->image)
    : $assets . 'assets/images/products/led-mask.jpg';
$url = !empty($is_preview)
    ? base_url('admin/products/preview/' . $product->id . '/zenvello')
    : product_url($product);
$price = format_money((float) $product->price);
$compare = isset($product->compare_price) ? (float) $product->compare_price : 0;
$showCompare = $compare > (float) $product->price;
?>
<article class="pcard">
  <?php if ($showCompare): ?>
    <?php $off = (int) round((($compare - (float) $product->price) / $compare) * 100); ?>
    <span class="badge">-<?= $off ?>%</span>
  <?php endif; ?>
  <a class="pcard__media" href="<?= $url ?>">
    <img src="<?= htmlspecialchars($img) ?>" alt="<?= htmlspecialchars($product->name) ?>">
  </a>
  <h3 class="pcard__title"><a href="<?= $url ?>"><?= htmlspecialchars($product->name) ?></a></h3>
  <p class="price<?= $showCompare ? '' : ' price--plain' ?>">
    <span class="price__now"><?= $price ?></span>
    <?php if ($showCompare): ?>
      <span class="price__was"><?= format_money($compare) ?></span>
    <?php endif; ?>
  </p>
  <?php if (empty($is_preview)): ?>
  <a class="btn--cart" href="<?= storefront_url('cart/add/' . $product->id) ?>">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M4 5h2l2.2 10.2h9.4L20 8H7"/><circle cx="10" cy="19" r="1.6"/><circle cx="17.5" cy="19" r="1.6"/></svg>
    Add to Cart
  </a>
  <?php endif; ?>
</article>
