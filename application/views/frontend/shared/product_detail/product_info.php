<?php
$cart_product = isset($cart_product) && $cart_product ? $cart_product : $product;
$off = isset($off) ? (int) $off : 0;
$reviewCount = isset($review_summary['count']) ? (int) $review_summary['count'] : 0;
$reviewAvg = isset($review_summary['average']) ? (float) $review_summary['average'] : 0;
$wishlistUrl = empty($is_preview) ? storefront_url('wishlist/toggle/' . (int) $product->id) : '#';
$shareUrl = isset($canonical_url) && $canonical_url ? $canonical_url : product_url($product);
$offer = !empty($cart_product->active_offer) ? $cart_product->active_offer : null;
$offerBadge = ($offer && !empty($offer['show_badge']) && !empty($offer['badge'])) ? $offer['badge'] : '';
$offerSave = ($offer && !empty($offer['save_amount']) && (float) $offer['save_amount'] > 0)
    ? (float) $offer['save_amount']
    : 0;
$offerCountdown = ($offer && !empty($offer['show_countdown']) && !empty($offer['ends_at_ts']));
?>
<div class="pdp-new-info" data-pdp-offer-root
     <?php if ($offerCountdown): ?>
     data-offer-ends="<?= (int) $offer['ends_at_ts'] ?>"
     data-offer-original="<?= htmlspecialchars(format_money((float) $offer['original_price']), ENT_QUOTES, 'UTF-8') ?>"
     <?php endif; ?>>
  <?php if ($offerBadge !== ''): ?>
    <span class="pdp-new-badge pdp-new-badge--offer" data-pdp-offer-badge><?= htmlspecialchars($offerBadge) ?></span>
  <?php else: ?>
    <span class="pdp-new-badge pdp-new-badge--sale<?= $off > 0 ? '' : ' is-hidden' ?>" data-pdp-sale><?= $off > 0 ? '-' . $off . '%' : '' ?></span>
    <?php if ($off < 1 && $reviewCount > 0 && $reviewAvg >= 4.5): ?>
      <span class="pdp-new-badge"><?= e_ui('product.bestseller') ?></span>
    <?php elseif ($off < 1 && !empty($product->brand)): ?>
      <span class="pdp-new-badge pdp-new-badge--muted"><?= htmlspecialchars($product->brand) ?></span>
    <?php endif; ?>
  <?php endif; ?>

  <h1 class="pdp-new-info__title" data-pdp-title><?= htmlspecialchars($product->name) ?></h1>

  <div class="pdp-new-info__meta">
    <?php if ($reviewCount > 0): ?>
      <a class="pdp-new-info__rating" href="#pdp-tab-reviews" data-pdp-opentab="reviews">
        <b><?= htmlspecialchars(number_format($reviewAvg, 1)) ?></b>
        <?= product_star_html($reviewAvg) ?>
        <span>(<?= htmlspecialchars(storefront_ui_count('product.review_one', 'product.review_many', $reviewCount)) ?>)</span>
      </a>
    <?php endif; ?>
    <?php if ($sku !== ''): ?>
      <p class="pdp-new-info__sku"><?= e_ui('product.sku') ?>: <span data-pdp-sku><?= htmlspecialchars($sku) ?></span></p>
    <?php endif; ?>
  </div>

  <?php
  $showShort = !function_exists('store_pdp_show_short_description')
      || store_pdp_show_short_description(isset($settings) ? $settings : array(), isset($store) ? $store : null);
  $shortHtml = '';
  if ($showShort) {
      $shortHtml = product_short_details_html($cart_product);
      if ($shortHtml === '') {
          $shortHtml = product_short_details_html($product);
      }
  }
  ?>
  <?php if ($showShort): ?>
  <div class="pdp-new-info__short<?= $shortHtml === '' ? ' is-hidden' : '' ?>" data-pdp-short>
    <div class="pdp-new-info__short-clip">
      <div class="pdp-new-info__short-body" data-pdp-short-body><?= $shortHtml ?></div>
    </div>
    <button class="pdp-new-info__short-more" type="button" data-pdp-short-toggle hidden aria-expanded="false">
      <span><?= e_ui('product.show_more') ?></span>
    </button>
  </div>
  <?php endif; ?>

  <div class="pdp-new-info__price">
    <span class="pdp-new-info__now" data-pdp-price><?= format_money((float) $cart_product->price) ?></span>
    <span class="pdp-new-info__was<?= $show_compare ? '' : ' is-hidden' ?>" data-pdp-compare><?= $show_compare ? format_money($compare) : '' ?></span>
    <?php $this->load->view('frontend/shared/admin_price_breakdown', array(
        'listing' => $cart_product,
        'store' => isset($store) ? $store : null,
        'as_main' => true,
    )); ?>
  </div>
  <?php if ($offerSave > 0): ?>
    <p class="pdp-new-offer-save" data-pdp-offer-save><?= e_ui('product.offer_save', array('{amount}' => format_money($offerSave))) ?></p>
  <?php endif; ?>
  <?php if ($offer && !empty($offer['free_shipping'])): ?>
    <p class="pdp-new-offer-ship" data-pdp-offer-ship><?= e_ui('product.offer_free_shipping') ?></p>
  <?php endif; ?>
  <?php if ($offer && !empty($offer['label']) && ($offer['type'] === 'percent' || $off > 0)): ?>
    <p class="pdp-new-offer-pct" data-pdp-offer-pct><?= htmlspecialchars($offer['label']) ?></p>
  <?php endif; ?>
  <?php if ($offerCountdown): ?>
    <div class="pdp-new-countdown" data-pdp-countdown>
      <span class="pdp-new-countdown__label"><?= e_ui('product.offer_ends') ?></span>
      <strong data-pdp-countdown-value>— — —</strong>
    </div>
    <p class="pdp-new-offer-urgency"><?= e_ui('product.offer_limited') ?></p>
  <?php endif; ?>
  <p class="pdp-new-info__stock<?= $stock > 0 ? ' is-in' : ' is-out' ?>" data-pdp-stock><?= $stock > 0 ? e_ui('product.in_stock') : e_ui('product.out_of_stock') ?></p>

  <div data-pdp-delivery>
    <?php $this->load->view('frontend/shared/shipping_eta', array('product' => $cart_product, 'shipping_variant' => 'zenvello')); ?>
  </div>

  <div class="pdp-new-variations">
    <?php $this->load->view('frontend/shared/product_detail/components/variation_selector', array(
        'product' => $product,
        'cart_product' => $cart_product,
        'child_products' => isset($child_products) ? $child_products : array(),
        'assets' => isset($assets) ? $assets : '',
    )); ?>
  </div>

  <?php if (empty($is_preview)): ?>
    <form id="pdp-buy-form" class="pdp-new-buy" method="get" action="<?= storefront_url('cart/add/' . (int) $cart_product->id) ?>" data-pdp-cart>
      <?php if (empty($child_products)): ?>
        <input type="hidden" name="product_id" value="<?= (int) $cart_product->id ?>" data-pdp-product-id>
      <?php endif; ?>
      <div class="pdp-new-qty" data-pdp-qty>
        <button type="button" data-step="down" aria-label="<?= e_ui('product.qty_down') ?>">−</button>
        <input id="pdp-new-qty" type="number" name="qty" value="1" min="1" max="<?= max(1, $qty_max) ?>" <?= $qty_max < 1 ? 'disabled' : '' ?>>
        <button type="button" data-step="up" aria-label="<?= e_ui('product.qty_up') ?>">+</button>
      </div>
      <button class="pdp-new-btn pdp-new-btn--cart" type="submit" <?= $qty_max < 1 ? 'disabled' : '' ?> data-pdp-add><?= e_ui('product.add_cart') ?></button>
      <button class="pdp-new-btn pdp-new-btn--buy" type="submit" name="next" value="checkout" <?= $qty_max < 1 ? 'disabled' : '' ?> data-pdp-buy><?= e_ui('product.buy_now') ?></button>
    </form>
    <div class="pdp-new-links">
      <a class="pdp-new-link<?= !empty($in_wishlist) ? ' is-on' : '' ?>" href="<?= htmlspecialchars($wishlistUrl) ?>" data-pdp-wish>
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M12 20s-7-4.4-7-9.2A4.2 4.2 0 0 1 12 7a4.2 4.2 0 0 1 7 3.8C19 15.6 12 20 12 20z"/></svg>
        <span data-pdp-wish-label><?= !empty($in_wishlist) ? e_ui('product.in_wishlist') : e_ui('product.add_wishlist') ?></span>
      </a>
      <button class="pdp-new-link" type="button" data-pdp-share data-share-url="<?= htmlspecialchars($shareUrl) ?>" data-share-title="<?= htmlspecialchars($product->name) ?>">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="18" cy="5" r="2.4"/><circle cx="6" cy="12" r="2.4"/><circle cx="18" cy="19" r="2.4"/><path d="M8.2 10.8 15.8 6.4M8.2 13.2l7.6 4.4"/></svg>
        <?= e_ui('product.share') ?>
      </button>
    </div>
  <?php else: ?>
    <a class="pdp-new-btn pdp-new-btn--cart" href="<?= htmlspecialchars($preview_back) ?>"><?= e_ui('nav.back_products') ?></a>
  <?php endif; ?>

  <?php
  $showTrustIcons = !function_exists('store_pdp_show_trust_icons')
      || store_pdp_show_trust_icons(isset($settings) ? $settings : array(), isset($store) ? $store : null);
  if ($showTrustIcons):
      $this->load->view('frontend/shared/product_detail/components/trust', array(
          'trust_items' => product_trust_items(isset($store) ? $store : null, isset($settings) ? $settings : array()),
          'show_trust_icons' => true,
      ));
  endif;
  ?>
</div>
