<?php
$cartProduct = isset($cart_product) && $cart_product ? $cart_product : $product;
$gallery = product_gallery_urls($product, isset($product_images) ? $product_images : array());
$placeholder = $assets . 'assets/images/products/mask-main.jpg';
$main = !empty($gallery) ? $gallery[0] : $placeholder;
$hasThumbs = count($gallery) > 1;
$compare = isset($cartProduct->compare_price) ? (float) $cartProduct->compare_price : 0;
$showCompare = $compare > (float) $cartProduct->price;
$stock = (int) $cartProduct->stock;
$qtyMax = max(0, min(10, $stock));
$category = isset($product_category) ? $product_category : null;
$subcategory = isset($product_subcategory) ? $product_subcategory : null;
$madeBy = isset($product->made_by) ? trim((string) $product->made_by) : '';
$sku = !empty($cartProduct->sku) ? $cartProduct->sku : (isset($product->sku) ? $product->sku : '');
?>
<nav class="container breadcrumb" aria-label="<?= e_ui('nav.breadcrumb') ?>">
  <ol>
    <li><a href="<?= storefront_url('') ?>"><?= e_ui('nav.home') ?></a></li>
    <li><a href="<?= storefront_url('shop') ?>"><?= e_ui('nav.shop') ?></a></li>
    <?php if ($category && !empty($category->slug)): ?>
    <li><a href="<?= category_url($category) ?>"><?= htmlspecialchars(category_store_name($category)) ?></a></li>
    <?php endif; ?>
    <?php if ($subcategory && !empty($subcategory->slug)): ?>
    <li><a href="<?= category_url($subcategory) ?>"><?= htmlspecialchars(category_store_name($subcategory)) ?></a></li>
    <?php endif; ?>
    <li aria-current="page"><?= htmlspecialchars($product->name) ?></li>
  </ol>
</nav>

<div class="container pdp__top">
    <div class="pdp__gallery<?= $hasThumbs ? ' has-thumbs' : '' ?>" data-gallery>
    <div class="pdp__stage">
      <img src="<?= htmlspecialchars($main) ?>" alt="<?= htmlspecialchars($product->name) ?>" data-gallery-main>
      <button class="pdp__zoom" type="button" aria-label="<?= e_ui('product.zoom') ?>" aria-pressed="false" data-zoom>
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5M11 8.5v5M8.5 11h5"/></svg>
      </button>
    </div>
    <?php if ($hasThumbs): ?>
    <div class="pdp__thumbs">
      <?php foreach ($gallery as $i => $src): ?>
        <button class="pdp__thumb<?= $i === 0 ? ' is-active' : '' ?>" type="button" data-gallery-thumb aria-label="<?= e_ui('product.show_image', array('{n}' => $i + 1)) ?>">
          <img src="<?= htmlspecialchars($src) ?>" data-full="<?= htmlspecialchars($src) ?>" alt="<?= htmlspecialchars($product->name) ?>">
        </button>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>
  </div>

  <div class="pdp__info">
    <?php if (!empty($product->brand)): ?>
    <p class="pdp__brand"><?= htmlspecialchars($product->brand) ?></p>
    <?php endif; ?>
    <h1 class="pdp__title"><?= htmlspecialchars($product->name) ?></h1>
    <?php if ($sku !== ''): ?>
    <p class="pdp__sku"><?= e_ui('product.sku') ?>: <?= htmlspecialchars($sku) ?></p>
    <?php endif; ?>
    <?php if ($category || $subcategory || $madeBy !== ''): ?>
    <ul class="pdp__facts">
      <?php if ($category): ?>
      <li><span><?= e_ui('product.category') ?></span>
        <?php if (!empty($category->slug)): ?>
          <a href="<?= category_url($category) ?>"><?= htmlspecialchars(category_store_name($category)) ?></a>
        <?php else: ?>
          <b><?= htmlspecialchars(category_store_name($category)) ?></b>
        <?php endif; ?>
      </li>
      <?php endif; ?>
      <?php if ($subcategory): ?>
      <li><span><?= e_ui('product.subcategory') ?></span>
        <?php if (!empty($subcategory->slug)): ?>
          <a href="<?= category_url($subcategory) ?>"><?= htmlspecialchars(category_store_name($subcategory)) ?></a>
        <?php else: ?>
          <b><?= htmlspecialchars(category_store_name($subcategory)) ?></b>
        <?php endif; ?>
      </li>
      <?php endif; ?>
      <?php if ($madeBy !== ''): ?>
      <li><span><?= e_ui('product.made_by') ?></span> <b><?= htmlspecialchars($madeBy) ?></b></li>
      <?php endif; ?>
    </ul>
    <?php endif; ?>
    <div class="pdp__price">
      <?php $off = product_sale_off_percent($cartProduct); ?>
      <?php if ($off > 0): ?>
        <span class="pdp__save">-<?= $off ?>%</span>
      <?php endif; ?>
      <span class="pdp__price-now"><?= format_money((float) $cartProduct->price) ?></span>
      <?php if ($showCompare): ?>
        <span class="pdp__price-was"><?= format_money($compare) ?></span>
      <?php endif; ?>
      <?php $this->load->view('frontend/shared/admin_price_breakdown', array(
          'listing' => $cartProduct,
          'store' => isset($store) ? $store : null,
          'as_main' => true,
      )); ?>
    </div>
    <p class="pdp__stock"><?= e_ui('product.in_stock_qty', array('{n}' => $stock)) ?></p>
    <?php $this->load->view('frontend/shared/shipping_eta', array('product' => $cartProduct, 'shipping_variant' => 'zenvello')); ?>
    <?php $this->load->view('frontend/shared/child_options'); ?>
    <?php if (empty($is_preview)): ?>
      <?php if ($qtyMax > 0): ?>
      <form class="pdp__buy" method="get" action="<?= storefront_url('cart/add/' . $cartProduct->id) ?>">
        <label class="visually-hidden" for="pdp-qty"><?= e_ui('product.qty') ?></label>
        <select class="qty-select" id="pdp-qty" name="qty" aria-label="<?= e_ui('product.qty') ?>">
          <?php for ($i = 1; $i <= $qtyMax; $i++): ?>
            <option value="<?= $i ?>"><?= $i ?></option>
          <?php endfor; ?>
        </select>
        <button class="btn btn--yellow" type="submit"><?= e_ui('product.add_cart') ?></button>
      </form>
      <?php else: ?>
        <p class="pdp__oos"><?= e_ui('product.out_of_stock') ?></p>
      <?php endif; ?>
    <?php else: ?>
      <a class="btn btn--yellow btn--lg" href="<?= htmlspecialchars($preview_back) ?>"><?= e_ui('nav.back_products') ?></a>
    <?php endif; ?>
    <div class="pdp__bullets">
      <p><?= nl2br(htmlspecialchars($product->description ?: store_ui('product.fallback_desc'))) ?></p>
    </div>
  </div>
</div>

<?php if (ec_product_details_html($product) !== ''): ?>
<section class="container section" aria-labelledby="product-details">
  <h2 class="section-title" id="product-details"><?= e_ui('product.details') ?></h2>
  <?php $this->load->view('frontend/shared/product_details', array('product' => $product)); ?>
</section>
<?php endif; ?>

<?php if (!empty($products)): ?>
<section class="container section" aria-labelledby="trending-picks">
  <div class="section-head">
    <div>
      <h2 class="section-title" id="trending-picks"><?= e_ui('product.trending') ?></h2>
    </div>
  </div>
  <div class="grid-6">
    <?php
    $shown = 0;
    foreach ($products as $related):
      if ((int) $related->id === (int) $product->id) continue;
      if ($shown >= 4) break;
      $shown++;
      $this->load->view('frontend/zenvello/product_card', array('product' => $related, 'assets' => $assets, 'is_preview' => !empty($is_preview)));
    endforeach;
    ?>
  </div>
</section>
<?php endif; ?>
