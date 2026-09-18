<?php
$related = isset($products) ? $products : array();
$currentId = isset($product->id) ? (int) $product->id : 0;
$items = array();
foreach ($related as $item) {
    if ((int) $item->id === $currentId) {
        continue;
    }
    $items[] = $item;
}
if (empty($items)) {
    return;
}
$shopUrl = !empty($is_preview) ? (isset($preview_back) ? $preview_back : '#') : storefront_url('shop');
$showNav = count($items) > 1;
?>
<section class="pdp-new-related<?= $showNav ? ' has-slider' : '' ?>" aria-labelledby="pdp-related-title">
  <div class="pdp-new-related__head">
    <h2 id="pdp-related-title">Related Products</h2>
    <a href="<?= htmlspecialchars($shopUrl) ?>">View All →</a>
  </div>
  <div class="pdp-new-related__slider">
    <?php if ($showNav): ?>
    <button class="pdp-new-related__arrow pdp-new-related__arrow--prev" type="button" data-pdp-rail-prev aria-label="Previous products">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M15 5l-7 7 7 7"/></svg>
    </button>
    <?php endif; ?>
    <div class="pdp-new-related__rail" data-pdp-rail>
      <?php foreach ($items as $item): ?>
        <?php
        $url = !empty($is_preview)
            ? base_url('admin/products/preview/' . $item->id . '/' . (isset($theme) && $theme ? $theme->slug : 'zenvello'))
            : product_url($item);
        $img = !empty($item->image) ? product_image_url($item->image) : (isset($assets) ? $assets . 'assets/images/products/led-mask.jpg' : '');
        $compare = isset($item->compare_price) ? (float) $item->compare_price : 0;
        $showCompare = $compare > (float) $item->price;
        ?>
        <article class="pdp-new-card">
          <a class="pdp-new-card__media" href="<?= htmlspecialchars($url) ?>">
            <?php if ($img !== ''): ?>
              <img src="<?= htmlspecialchars($img) ?>" alt="<?= htmlspecialchars($item->name) ?>">
            <?php endif; ?>
          </a>
          <h3><a href="<?= htmlspecialchars($url) ?>"><?= htmlspecialchars($item->name) ?></a></h3>
          <p class="pdp-new-card__price">
            <b><?= format_money((float) $item->price) ?></b>
            <?php if ($showCompare): ?>
              <s><?= format_money($compare) ?></s>
            <?php endif; ?>
          </p>
          <?php if (empty($is_preview)): ?>
            <div class="pdp-new-card__actions">
              <a class="pdp-new-card__cart" href="<?= storefront_url('cart/add/' . (int) $item->id) ?>" aria-label="Add to cart">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M4 5h2l2.2 10.2h9.4L20 8H7"/><circle cx="10" cy="19" r="1.6"/><circle cx="17.5" cy="19" r="1.6"/></svg>
              </a>
              <a class="pdp-new-card__wish" href="<?= storefront_url('wishlist/toggle/' . (int) $item->id) ?>" aria-label="Add to wishlist">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M12 20s-7-4.4-7-9.2A4.2 4.2 0 0 1 12 7a4.2 4.2 0 0 1 7 3.8C19 15.6 12 20 12 20z"/></svg>
              </a>
            </div>
          <?php endif; ?>
        </article>
      <?php endforeach; ?>
    </div>
    <?php if ($showNav): ?>
    <button class="pdp-new-related__arrow pdp-new-related__arrow--next" type="button" data-pdp-rail-next aria-label="Next products">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 5l7 7-7 7"/></svg>
    </button>
    <?php endif; ?>
  </div>
</section>
