<?php
$cartProduct = isset($cart_product) && $cart_product ? $cart_product : $product;
$gallery = product_gallery_urls($product, isset($product_images) ? $product_images : array());
foreach (product_source_gallery_urls($product) as $srcUrl) {
    if (!in_array($srcUrl, $gallery, true)) {
        $gallery[] = $srcUrl;
    }
}
$childFeatures = product_child_feature_urls(isset($child_products) ? $child_products : array());
$selectedGallery = $gallery;
$isVariable = !empty($child_products);
if ($isVariable && $cartProduct) {
    $own = array();
    if (!empty($child_galleries[(int) $cartProduct->id])) {
        $own = $child_galleries[(int) $cartProduct->id];
    }
    foreach (product_source_gallery_urls($cartProduct) as $srcUrl) {
        if (!in_array($srcUrl, $own, true)) {
            $own[] = $srcUrl;
        }
    }
    $selectedGallery = product_pdp_gallery($cartProduct, $own, $gallery, $childFeatures);
    if (empty($selectedGallery)) {
        $selectedGallery = array_values(array_unique(array_merge($childFeatures, $gallery)));
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
$off = product_sale_off_percent($cartProduct);
if ($showCompare && $compare > 0 && $off < 1) {
    $off = (int) round((($compare - (float) $cartProduct->price) / $compare) * 100);
}
$variationPayload = array();
$optionDisplayLabels = function_exists('product_option_display_labels')
    ? product_option_display_labels(!empty($child_products) ? $child_products : array(), $product)
    : array();
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
    foreach (product_source_gallery_urls($child) as $srcUrl) {
        if (!in_array($srcUrl, $childGallery, true)) {
            $childGallery[] = $srcUrl;
        }
    }
    $shipWindow = product_delivery_window($child);
    $childShort = product_short_details_html($child);
    $combinedGallery = product_pdp_gallery($child, $childGallery, $gallery, $childFeatures);
    if (empty($combinedGallery)) {
        $combinedGallery = array_values(array_unique(array_merge($childFeatures, $gallery)));
    }
    $featureUrl = product_feature_url($child);
    if ($featureUrl === '') {
        $featureUrl = !empty($combinedGallery) ? $combinedGallery[0] : $main;
    }
    $optionName = isset($optionDisplayLabels[(int) $child->id])
        ? $optionDisplayLabels[(int) $child->id]
        : (trim((string) $child->name) !== '' ? trim((string) $child->name) : product_option_label($child, $product));
    $variationPayload[] = array(
        'id' => (int) $child->id,
        'sku' => isset($child->sku) ? (string) $child->sku : '',
        'name' => $optionName,
        'full_name' => $child->name,
        'attrs' => function_exists('product_child_attr_map')
            ? product_child_attr_map($child, $product, function_exists('product_attribute_axes') ? product_attribute_axes($product->id) : array())
            : array(),
        'price' => (float) $child->price,
        'price_formatted' => format_money((float) $child->price),
        'compare_price' => $childCompare,
        'compare_formatted' => $childCompare > (float) $child->price ? format_money($childCompare) : '',
        'sale_percent' => product_sale_off_percent($child),
        'offer' => function_exists('offer_payload_for_js') ? offer_payload_for_js($child) : null,
        'stock' => $childStock,
        'qty_max' => max(0, min(10, $childStock)),
        'url' => product_option_url($child, !empty($is_preview), isset($theme) ? $theme->slug : ''),
        'image' => $featureUrl,
        'gallery' => $combinedGallery,
        'in_stock' => $childStock > 0,
        'description' => trim(strip_tags((string) $child->description)),
        'short_html' => $childShort !== '' ? $childShort : product_short_details_html($product),
        'add_url' => empty($is_preview) ? storefront_url('cart/add/' . (int) $child->id) : '',
        'ship_label' => product_ship_option_label($child),
        'delivery_short' => $shipWindow ? $shipWindow['short'] : '',
        'delivery_text' => $shipWindow ? $shipWindow['text'] : '',
        'delivery_working' => $shipWindow ? $shipWindow['working'] : '',
        'delivery_eta' => $shipWindow ? $shipWindow['eta'] : '',
        'catalog_id' => !empty($child->source_product_id) ? (int) $child->source_product_id : (int) $child->id,
        'profit_url' => (function_exists('storefront_admin_bar_visible') && storefront_admin_bar_visible() && function_exists('storefront_admin_profitability_url'))
            ? storefront_admin_profitability_url($child, isset($store) ? $store : null)
            : '',
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
     data-parent-title="<?= htmlspecialchars($product->name, ENT_QUOTES, 'UTF-8') ?>"
     data-selected-id="<?= (int) $cartProduct->id ?>"
     data-variations="<?= htmlspecialchars(json_encode($variationPayload, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP), ENT_QUOTES, 'UTF-8') ?>">

  <nav class="pdp-new__crumb" aria-label="<?= e_ui('nav.breadcrumb') ?>">
    <ol>
      <li><a href="<?= storefront_url('') ?>"><?= e_ui('nav.home') ?></a></li>
      <?php if ($category && !empty($category->slug)): ?>
      <li><a href="<?= category_url($category) ?>"><?= htmlspecialchars(category_store_name($category)) ?></a></li>
      <?php endif; ?>
      <?php if ($subcategory && !empty($subcategory->slug)): ?>
      <li><a href="<?= category_url($subcategory) ?>"><?= htmlspecialchars(category_store_name($subcategory)) ?></a></li>
      <?php endif; ?>
      <li aria-current="page" data-pdp-crumb><?= htmlspecialchars($product->name) ?></li>
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
<script src="<?= storefront_asset_url('assets/frontend/shared/js/product-detail-new.js') ?>?v=24"></script>
