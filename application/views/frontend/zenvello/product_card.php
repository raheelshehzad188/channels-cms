<?php
if (function_exists('storefront_listing_product')) {
    $product = storefront_listing_product($product);
}
if (!$product) {
    return;
}
$img = product_image_url(isset($product->image) ? $product->image : '', $assets . 'assets/images/products/led-mask.jpg');
if ($img === '') {
    $img = $assets . 'assets/images/products/led-mask.jpg';
}
$imgCss = htmlspecialchars($img, ENT_QUOTES, 'UTF-8');
$url = !empty($is_preview)
    ? base_url('admin/products/preview/' . $product->id . '/zenvello')
    : product_url($product);
$price = format_money((float) $product->price);
$compare = isset($product->compare_price) ? (float) $product->compare_price : 0;
$showCompare = $compare > (float) $product->price;
$off = $showCompare ? product_sale_off_percent($product) : 0;
$offer = !empty($product->active_offer) ? $product->active_offer : null;
$badgeText = '';
if ($offer && !empty($offer['show_badge']) && !empty($offer['badge'])) {
    $badgeText = $offer['badge'];
} elseif ($off > 0) {
    $badgeText = '-' . $off . '%';
}
$freeShip = (function_exists('product_has_free_shipping') && product_has_free_shipping($product))
    || ($offer && !empty($offer['free_shipping']));
?>
<article class="pcard">
  <?php if ($badgeText !== ''): ?>
    <span class="badge<?= $offer ? ' badge--offer' : '' ?>"><?= htmlspecialchars($badgeText) ?></span>
  <?php endif; ?>
  <?php if ($freeShip): ?>
    <span class="badge badge--ship"><?= e_ui(!empty($product->free_shipping) ? 'product.free_shipping' : 'product.offer_free_shipping') ?></span>
  <?php endif; ?>
  <a class="pcard__media" href="<?= $url ?>" aria-label="<?= htmlspecialchars($product->name) ?>">
    <span class="pcard__photo" style="background-image:url('<?= $imgCss ?>')"></span>
  </a>
  <h3 class="pcard__title"><a href="<?= $url ?>"><?= htmlspecialchars($product->name) ?></a></h3>
  <p class="price<?= $showCompare ? '' : ' price--plain' ?>">
    <span class="price__now"><?= $price ?></span>
    <?php if ($showCompare): ?>
      <span class="price__was"><?= format_money($compare) ?></span>
    <?php endif; ?>
  </p>
  <?php
    $this->load->view('frontend/shared/admin_card_meta', array(
        'listing' => $product,
        'store' => isset($store) ? $store : null,
    ));
  ?>
  <?php if (empty($is_preview)): ?>
  <a class="btn--cart" href="<?= storefront_url('cart/add/' . $product->id) ?>">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M4 5h2l2.2 10.2h9.4L20 8H7"/><circle cx="10" cy="19" r="1.6"/><circle cx="17.5" cy="19" r="1.6"/></svg>
    <?= e_ui('product.add_cart') ?>
  </a>
  <?php endif; ?>
</article>
