<?php $this->load->view('flash'); ?>
<p class="text-muted mb-4">Select a catalog product to copy it into your store. After that you can change title, slug, images, and price.</p>

<?php if (empty($products)): ?>
  <div class="store-card"><div class="card-body text-muted">No products available for this store country.</div></div>
<?php else: ?>
<div class="store-product-grid">
  <div class="row">
    <?php foreach ($products as $product): ?>
      <?php
      $image = !empty($product->image) ? base_url($product->image) : '';
      $max = (float) $product->max_sale_price;
      $copied = !empty($product->copied_id);
      $actionUrl = $copied
        ? $storeUrl . '/products/form/' . (int) $product->copied_id
        : $storeUrl . '/products/add/' . (int) $product->id;
      $desc = $product->description ? trim(substr(strip_tags($product->description), 0, 80)) : '';
      ?>
      <div class="col-md-3">
        <div class="ibox">
          <div class="ibox-content product-box<?= $copied ? ' active' : '' ?>">
            <a href="<?= $actionUrl ?>" class="product-imitation">
              <?php if ($image): ?>
                <img src="<?= htmlspecialchars($image) ?>" alt="<?= htmlspecialchars($product->name) ?>">
              <?php else: ?>
                [ INFO ]
              <?php endif; ?>
            </a>
            <div class="product-desc">
              <span class="product-price"><?= format_money($product->customer_price) ?></span>
              <small class="text-muted"><?= htmlspecialchars($product->country_name ?: 'Catalog') ?></small>
              <a href="<?= $actionUrl ?>" class="product-name"><?= htmlspecialchars($product->name) ?></a>
              <div class="small m-t-xs">
                <?= htmlspecialchars($desc ?: 'No description.') ?>
              </div>
              <div class="product-meta">
                Cost <?= format_money($product->wholesale_price) ?>
                · Base <?= format_money($product->base_price) ?>
                <?php if ($max > 0): ?>
                  · Max <?= format_money($max) ?>
                <?php endif; ?>
              </div>
              <div class="mt-3 text-end">
                <?php if ($copied): ?>
                  <a href="<?= $actionUrl ?>" class="btn btn-xs btn-outline-muted">Edit <i class="bi bi-arrow-right"></i></a>
                <?php else: ?>
                  <a href="<?= $actionUrl ?>" class="btn btn-xs btn-outline-inspinia">Add to store <i class="bi bi-arrow-right"></i></a>
                <?php endif; ?>
              </div>
            </div>
          </div>
        </div>
      </div>
    <?php endforeach; ?>
  </div>
</div>
<?php endif; ?>
