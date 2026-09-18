<?php
$cartProduct = isset($cart_product) && $cart_product ? $cart_product : $product;
$gallery = product_gallery_urls($product, isset($product_images) ? $product_images : array());
$selectedGallery = $gallery;
if (!empty($cartProduct) && (int) $cartProduct->id !== (int) $product->id) {
    $own = array();
    if (!empty($child_galleries) && isset($child_galleries[(int) $cartProduct->id])) {
        $own = $child_galleries[(int) $cartProduct->id];
    } elseif (!empty($cartProduct->image)) {
        $own = product_gallery_urls($cartProduct, array());
    }
    if (!empty($own)) {
        $selectedGallery = array_values(array_unique(array_merge($own, $gallery)));
    }
}
$placeholder = isset($assets) ? $assets . 'assets/images/products/mask-main.jpg' : '';
$main = !empty($selectedGallery) ? $selectedGallery[0] : $placeholder;
$compare = isset($cartProduct->compare_price) ? (float) $cartProduct->compare_price : 0;
$showCompare = $compare > (float) $cartProduct->price;
$stock = (int) $cartProduct->stock;
$qtyMax = max(0, min(10, $stock));
$category = isset($product_category) ? $product_category : null;
$subcategory = isset($product_subcategory) ? $product_subcategory : null;
$sku = !empty($cartProduct->sku) ? $cartProduct->sku : (isset($product->sku) ? $product->sku : '');
$attributes = isset($product_attributes) ? $product_attributes : array();
$trustItems = product_trust_items(isset($store) ? $store : null, isset($settings) ? $settings : array());
$reviewSummary = isset($review_summary) ? $review_summary : array('count' => 0, 'average' => 0, 'breakdown' => array(5 => 0, 4 => 0, 3 => 0, 2 => 0, 1 => 0));
$openTab = isset($open_tab) ? strtolower((string) $open_tab) : '';
if (!in_array($openTab, array('description', 'specifications', 'reviews', 'faqs'), true)) {
    $openTab = 'description';
}
$videoEmbed = product_video_embed($product);
$off = 0;
if ($showCompare && $compare > 0) {
    $off = (int) round((($compare - (float) $cartProduct->price) / $compare) * 100);
}
$variationPayload = array();
foreach (!empty($child_products) ? $child_products : array() as $child) {
    $childCompare = isset($child->compare_price) ? (float) $child->compare_price : 0;
    $childStock = (int) $child->stock;
    $childGallery = array();
    if (!empty($child_galleries) && isset($child_galleries[(int) $child->id])) {
        $childGallery = $child_galleries[(int) $child->id];
    }
    if (empty($childGallery) && !empty($child->image)) {
        $childGallery = product_gallery_urls($child, array());
    }
    $shipWindow = product_delivery_window($child);
    $variationPayload[] = array(
        'id' => (int) $child->id,
        'sku' => isset($child->sku) ? (string) $child->sku : '',
        'name' => product_option_label($child, $product),
        'full_name' => $child->name,
        'price' => (float) $child->price,
        'price_formatted' => format_money((float) $child->price),
        'compare_price' => $childCompare,
        'compare_formatted' => $childCompare > (float) $child->price ? format_money($childCompare) : '',
        'stock' => $childStock,
        'qty_max' => max(0, min(10, $childStock)),
        'url' => product_option_url($child, !empty($is_preview), isset($theme) ? $theme->slug : ''),
        'image' => !empty($childGallery) ? $childGallery[0] : (!empty($child->image) ? product_image_url($child->image) : $main),
        'gallery' => !empty($childGallery) ? $childGallery : $gallery,
        'in_stock' => $childStock > 0,
        'description' => trim(strip_tags((string) $child->description)),
        'add_url' => empty($is_preview) ? storefront_url('cart/add/' . (int) $child->id) : '',
        'ship_label' => product_ship_option_label($child),
        'delivery_short' => $shipWindow ? $shipWindow['short'] : '',
        'delivery_text' => $shipWindow ? $shipWindow['text'] : '',
    );
}
$themeColor = trim((string) theme_setting(isset($settings) ? $settings : array(), 'primary_color', '#2f9e4d'));
if (!preg_match('/^#([0-9a-fA-F]{3}|[0-9a-fA-F]{6})$/', $themeColor)) {
    $themeColor = '#2f9e4d';
}
$hex = ltrim($themeColor, '#');
if (strlen($hex) === 3) {
    $hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
}
$lr = hexdec(substr($hex, 0, 2));
$lg = hexdec(substr($hex, 2, 2));
$lb = hexdec(substr($hex, 4, 2));
$themeInk = (((0.299 * $lr) + (0.587 * $lg) + (0.114 * $lb)) / 255) > 0.55 ? '#111111' : '#ffffff';
?>
<div class="pdp-new" data-pdp-new
     style="--pdp-theme: <?= htmlspecialchars($themeColor) ?>; --pdp-theme-ink: <?= htmlspecialchars($themeInk) ?>;"
     data-parent-id="<?= (int) $product->id ?>"
     data-selected-id="<?= (int) $cartProduct->id ?>"
     data-variations="<?= htmlspecialchars(json_encode($variationPayload, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP), ENT_QUOTES, 'UTF-8') ?>">

  <nav class="pdp-new__crumb" aria-label="Breadcrumb">
    <ol>
      <li><a href="<?= storefront_url('shop/index') ?>">Home</a></li>
      <?php if ($category && !empty($category->slug)): ?>
      <li><a href="<?= storefront_url('category/' . rawurlencode($category->slug)) ?>"><?= htmlspecialchars(category_store_name($category)) ?></a></li>
      <?php endif; ?>
      <?php if ($subcategory && !empty($subcategory->slug)): ?>
      <li><a href="<?= storefront_url('category/' . rawurlencode($subcategory->slug)) ?>"><?= htmlspecialchars(category_store_name($subcategory)) ?></a></li>
      <?php endif; ?>
      <li aria-current="page"><?= htmlspecialchars($product->name) ?></li>
    </ol>
  </nav>

  <?php if (!empty($flash_error)): ?>
    <p class="pdp-new__flash pdp-new__flash--error"><?= htmlspecialchars($flash_error) ?></p>
  <?php endif; ?>
  <?php if (!empty($flash_success)): ?>
    <p class="pdp-new__flash pdp-new__flash--ok"><?= htmlspecialchars($flash_success) ?></p>
  <?php endif; ?>

  <div class="pdp-new__top">
    <div class="pdp-new__left">
      <?php $this->load->view('frontend/shared/product_detail/components/gallery', array(
          'product' => $product,
          'gallery' => $selectedGallery,
          'main' => $main,
          'placeholder' => $placeholder,
          'video_embed' => $videoEmbed,
      )); ?>
    </div>
    <div class="pdp-new__right">
      <?php $this->load->view('frontend/shared/product_detail/components/product_info', array(
          'product' => $product,
          'cart_product' => $cartProduct,
          'sku' => $sku,
          'stock' => $stock,
          'qty_max' => $qtyMax,
          'show_compare' => $showCompare,
          'compare' => $compare,
          'off' => $off,
          'review_summary' => $reviewSummary,
          'is_preview' => !empty($is_preview),
          'preview_back' => isset($preview_back) ? $preview_back : '',
          'in_wishlist' => !empty($in_wishlist),
          'canonical_url' => isset($canonical_url) ? $canonical_url : product_url($product),
      )); ?>
    </div>
  </div>

  <?php $this->load->view('frontend/shared/product_detail/components/product_tabs', array(
      'product' => $product,
      'cart_product' => $cartProduct,
      'attributes' => $attributes,
      'video_embed' => $videoEmbed,
      'main' => $main,
      'open_tab' => $openTab,
      'review_summary' => $reviewSummary,
  )); ?>

  <?php $this->load->view('frontend/shared/product_detail/components/related_products', array(
      'products' => isset($products) ? $products : array(),
      'product' => $product,
      'assets' => isset($assets) ? $assets : '',
      'is_preview' => !empty($is_preview),
      'theme' => isset($theme) ? $theme : null,
  )); ?>
</div>
<script src="<?= storefront_asset_url('assets/frontend/shared/js/product-detail-new.js') ?>?v=6"></script>
