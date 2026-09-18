<?php
$off = isset($off) ? (int) $off : 0;
$reviewCount = isset($review_summary['count']) ? (int) $review_summary['count'] : 0;
$reviewAvg = isset($review_summary['average']) ? (float) $review_summary['average'] : 0;
$wishlistUrl = empty($is_preview) ? storefront_url('wishlist/toggle/' . (int) $product->id) : '#';
$shareUrl = isset($canonical_url) && $canonical_url ? $canonical_url : product_url($product);
?>
<div class="pdp-new-info">
  <?php if ($off > 0): ?>
    <span class="pdp-new-badge pdp-new-badge--sale">-<?= $off ?>%</span>
  <?php elseif ($reviewCount > 0 && $reviewAvg >= 4.5): ?>
    <span class="pdp-new-badge">Bestseller</span>
  <?php elseif (!empty($product->brand)): ?>
    <span class="pdp-new-badge pdp-new-badge--muted"><?= htmlspecialchars($product->brand) ?></span>
  <?php endif; ?>

  <h1 class="pdp-new-info__title"><?= htmlspecialchars($product->name) ?></h1>

  <div class="pdp-new-info__meta">
    <?php if ($reviewCount > 0): ?>
      <a class="pdp-new-info__rating" href="#pdp-tab-reviews" data-pdp-opentab="reviews">
        <b><?= htmlspecialchars(number_format($reviewAvg, 1)) ?></b>
        <?= product_star_html($reviewAvg) ?>
        <span>(<?= (int) $reviewCount ?> review<?= $reviewCount === 1 ? '' : 's' ?>)</span>
      </a>
    <?php endif; ?>
    <?php if ($sku !== ''): ?>
      <p class="pdp-new-info__sku">SKU: <span data-pdp-sku><?= htmlspecialchars($sku) ?></span></p>
    <?php endif; ?>
  </div>

  <?php if (!empty($product->description)): ?>
    <?php
    $short = trim(preg_replace('/\s+/', ' ', strip_tags((string) $product->description)));
    if (strlen($short) > 220) {
        $short = rtrim(substr($short, 0, 217)) . '…';
    }
    ?>
    <p class="pdp-new-info__short"><?= htmlspecialchars($short) ?></p>
  <?php endif; ?>

  <div class="pdp-new-info__price">
    <span class="pdp-new-info__now" data-pdp-price><?= format_money((float) $cart_product->price) ?></span>
    <span class="pdp-new-info__was<?= $show_compare ? '' : ' is-hidden' ?>" data-pdp-compare><?= $show_compare ? format_money($compare) : '' ?></span>
  </div>
  <p class="pdp-new-info__stock<?= $stock > 0 ? ' is-in' : ' is-out' ?>" data-pdp-stock><?= $stock > 0 ? 'In stock' : 'Out of stock' ?></p>

  <div data-pdp-delivery>
    <?php $this->load->view('frontend/shared/shipping_eta', array('product' => $cart_product, 'shipping_variant' => 'zenvello')); ?>
  </div>

  <?php $this->load->view('frontend/shared/product_detail/components/variation_selector'); ?>

  <?php if (empty($is_preview)): ?>
    <form class="pdp-new-buy" method="get" action="<?= storefront_url('cart/add/' . (int) $cart_product->id) ?>" data-pdp-cart>
      <div class="pdp-new-qty" data-pdp-qty>
        <button type="button" data-step="down" aria-label="Decrease quantity">−</button>
        <input id="pdp-new-qty" type="number" name="qty" value="1" min="1" max="<?= max(1, $qty_max) ?>" <?= $qty_max < 1 ? 'disabled' : '' ?>>
        <button type="button" data-step="up" aria-label="Increase quantity">+</button>
      </div>
      <button class="pdp-new-btn pdp-new-btn--cart" type="submit" <?= $qty_max < 1 ? 'disabled' : '' ?> data-pdp-add>Add to Cart</button>
      <button class="pdp-new-btn pdp-new-btn--buy" type="submit" name="next" value="checkout" <?= $qty_max < 1 ? 'disabled' : '' ?> data-pdp-buy>Buy Now</button>
    </form>
    <div class="pdp-new-links">
      <a class="pdp-new-link<?= !empty($in_wishlist) ? ' is-on' : '' ?>" href="<?= htmlspecialchars($wishlistUrl) ?>" data-pdp-wish>
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M12 20s-7-4.4-7-9.2A4.2 4.2 0 0 1 12 7a4.2 4.2 0 0 1 7 3.8C19 15.6 12 20 12 20z"/></svg>
        <span data-pdp-wish-label><?= !empty($in_wishlist) ? 'In Wishlist' : 'Add to Wishlist' ?></span>
      </a>
      <button class="pdp-new-link" type="button" data-pdp-share data-share-url="<?= htmlspecialchars($shareUrl) ?>" data-share-title="<?= htmlspecialchars($product->name) ?>">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="18" cy="5" r="2.4"/><circle cx="6" cy="12" r="2.4"/><circle cx="18" cy="19" r="2.4"/><path d="M8.2 10.8 15.8 6.4M8.2 13.2l7.6 4.4"/></svg>
        Share Product
      </button>
    </div>
  <?php else: ?>
    <a class="pdp-new-btn pdp-new-btn--cart" href="<?= htmlspecialchars($preview_back) ?>">Back to products</a>
  <?php endif; ?>

  <?php $this->load->view('frontend/shared/product_detail/components/trust', array('trust_items' => product_trust_items(isset($store) ? $store : null, isset($settings) ? $settings : array()))); ?>
</div>
