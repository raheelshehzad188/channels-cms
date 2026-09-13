<?php
$gallery = product_gallery_urls($product, isset($product_images) ? $product_images : array());
$placeholder = $assets . 'assets/images/products/mask-main.jpg';
$main = !empty($gallery) ? $gallery[0] : $placeholder;
$hasThumbs = count($gallery) > 1;
$compare = isset($product->compare_price) ? (float) $product->compare_price : 0;
$showCompare = $compare > (float) $product->price;
?>
<nav class="container breadcrumb" aria-label="Breadcrumb">
  <ol>
    <li><a href="<?= storefront_url('shop/index') ?>">Home</a></li>
    <li><a href="<?= storefront_url('shop') ?>">Shop</a></li>
    <li aria-current="page"><?= htmlspecialchars($product->name) ?></li>
  </ol>
</nav>

<div class="container pdp__top">
  <div class="pdp__gallery<?= $hasThumbs ? ' has-thumbs' : '' ?>" data-gallery>
    <?php if ($hasThumbs): ?>
    <div class="pdp__thumbs">
      <?php foreach ($gallery as $i => $src): ?>
        <button class="pdp__thumb<?= $i === 0 ? ' is-active' : '' ?>" type="button" data-gallery-thumb aria-label="Show image <?= $i + 1 ?>">
          <img src="<?= htmlspecialchars($src) ?>" data-full="<?= htmlspecialchars($src) ?>" alt="<?= htmlspecialchars($product->name) ?>">
        </button>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>
    <div class="pdp__stage">
      <img src="<?= htmlspecialchars($main) ?>" alt="<?= htmlspecialchars($product->name) ?>" data-gallery-main>
      <button class="pdp__zoom" type="button" aria-label="Zoom image" aria-pressed="false" data-zoom>
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5M11 8.5v5M8.5 11h5"/></svg>
      </button>
    </div>
  </div>

  <div class="pdp__info">
    <p class="pdp__sku"><?= htmlspecialchars($product->sku ?: 'Product') ?></p>
    <h1 class="pdp__title"><?= htmlspecialchars($product->name) ?></h1>
    <div class="pdp__price">
      <span class="pdp__price-now"><?= format_money((float) $product->price) ?></span>
      <?php if ($showCompare): ?>
        <span class="pdp__price-was"><?= format_money($compare) ?></span>
      <?php endif; ?>
    </div>
    <p class="pdp__stock">In stock: <?= (int) $product->stock ?></p>
    <?php $this->load->view('frontend/shared/shipping_eta', array('product' => $product, 'shipping_variant' => 'zenvello')); ?>
    <div class="pdp__bullets">
      <p><?= nl2br(htmlspecialchars($product->description ?: 'Selected for the ZENVello collection.')) ?></p>
    </div>
    <?php if (empty($is_preview)): ?>
      <a class="btn btn--yellow btn--lg" href="<?= storefront_url('cart/add/' . $product->id) ?>">Add to Cart</a>
    <?php else: ?>
      <a class="btn btn--yellow btn--lg" href="<?= htmlspecialchars($preview_back) ?>">Back to products</a>
    <?php endif; ?>
  </div>
</div>

<?php if (ec_product_details_html($product) !== ''): ?>
<section class="container section" aria-labelledby="product-details">
  <h2 class="section-title" id="product-details">Details</h2>
  <?php $this->load->view('frontend/shared/product_details', array('product' => $product)); ?>
</section>
<?php endif; ?>

<?php if (!empty($products)): ?>
<section class="container section" aria-labelledby="related">
  <div class="section-head">
    <div>
      <h2 class="section-title" id="related">You may also like</h2>
    </div>
  </div>
  <div class="grid-6">
    <?php foreach ($products as $related): ?>
      <?php if ((int) $related->id === (int) $product->id) continue; ?>
      <?php $this->load->view('frontend/zenvello/product_card', array('product' => $related, 'assets' => $assets, 'is_preview' => !empty($is_preview))); ?>
    <?php endforeach; ?>
  </div>
</section>
<?php endif; ?>
