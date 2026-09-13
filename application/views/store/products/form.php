<?php
$isEdit = !empty($product);
$lockCore = $isEdit;
$costPrice = $isEdit ? (float) $product->cost_price : 0;
$maxSale = $isEdit ? (float) $product->max_sale_price : 0;
$productCategoryIds = isset($product_category_ids) ? $product_category_ids : array();
$allCategories = isset($all_categories) ? $all_categories : array();
$val = function ($key, $default = '') use ($product, $isEdit) {
    return $isEdit && isset($product->$key) ? $product->$key : $default;
};
?>
<?php $this->load->view('flash'); ?>

<div class="store-card" style="max-width:920px">
  <div class="card-header d-flex justify-content-between align-items-center">
    <span><?= $isEdit ? 'Edit Product' : 'Add Product' ?></span>
    <?php if ($lockCore): ?>
      <span class="badge text-bg-light border text-muted">Images, SEO, price &amp; categories editable</span>
    <?php endif; ?>
  </div>
  <div class="card-body">
    <form method="post" enctype="multipart/form-data" action="<?= $storeUrl ?>/products/save<?= $isEdit ? '/' . (int) $product->id : '' ?>" id="storeProductForm">
      <ul class="nav nav-tabs mb-3" role="tablist">
        <li class="nav-item" role="presentation">
          <button class="nav-link<?= $lockCore ? '' : ' active' ?>" data-bs-toggle="tab" data-bs-target="#tab-general" type="button">General <?= $lockCore ? '<span class="badge text-bg-secondary ms-1">Locked</span>' : '' ?></button>
        </li>
        <li class="nav-item" role="presentation">
          <button class="nav-link<?= $lockCore ? ' active' : '' ?>" data-bs-toggle="tab" data-bs-target="#tab-images" type="button">Images</button>
        </li>
        <li class="nav-item" role="presentation">
          <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-pricing" type="button">Pricing</button>
        </li>
        <li class="nav-item" role="presentation">
          <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-categories" type="button">Categories</button>
        </li>
        <li class="nav-item" role="presentation">
          <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-shipping" type="button">Shipping Info</button>
        </li>
        <li class="nav-item" role="presentation">
          <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-seo" type="button">SEO</button>
        </li>
      </ul>

      <div class="tab-content">
        <div class="tab-pane fade<?= $lockCore ? '' : ' show active' ?>" id="tab-general">
          <div class="row g-3">
            <div class="col-12">
              <label class="form-label">Title</label>
              <input type="text" name="name" id="productName" class="form-control" <?= $lockCore ? 'disabled' : 'required' ?> value="<?= htmlspecialchars($val('name')) ?>">
            </div>
            <div class="col-md-6">
              <label class="form-label">SKU</label>
              <input type="text" name="sku" class="form-control" <?= $lockCore ? 'disabled' : '' ?> value="<?= htmlspecialchars($val('sku')) ?>">
            </div>
            <div class="col-md-6">
              <label class="form-label">Status</label>
              <select name="status" class="form-select" <?= $lockCore ? 'disabled' : '' ?>>
                <option value="1" <?= (int) $val('status', 1) === 1 ? 'selected' : '' ?>>Active</option>
                <option value="0" <?= (int) $val('status', 1) === 0 ? 'selected' : '' ?>>Inactive</option>
              </select>
            </div>
            <div class="col-12">
              <label class="form-label">Description</label>
              <textarea name="description" class="form-control" rows="5" <?= $lockCore ? 'disabled' : '' ?>><?= htmlspecialchars($val('description')) ?></textarea>
            </div>
            <div class="col-12">
              <label class="form-label">Details</label>
              <?php if ($lockCore): ?>
                <div class="border rounded p-3 bg-light product-html"><?= ec_product_details_html($product) ?: '<span class="text-muted">No details.</span>' ?></div>
              <?php else: ?>
                <textarea name="details" class="form-control" rows="8"><?= htmlspecialchars($val('details')) ?></textarea>
              <?php endif; ?>
            </div>
          </div>
        </div>

        <div class="tab-pane fade<?= $lockCore ? ' show active' : '' ?>" id="tab-images">
          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label">Main image</label>
              <input type="file" name="image" class="form-control" accept="image/*">
              <?php if ($isEdit && !empty($product->image)): ?>
                <img src="<?= base_url($product->image) ?>" alt="" class="mt-2 rounded border" style="height:96px;object-fit:cover">
              <?php endif; ?>
            </div>
            <div class="col-12">
              <label class="form-label">Gallery images</label>
              <input type="file" name="gallery[]" class="form-control" accept="image/*" multiple>
              <?php if (!empty($images)): ?>
                <div class="d-flex flex-wrap gap-2 mt-3">
                  <?php foreach ($images as $image): ?>
                    <div class="position-relative">
                      <img src="<?= base_url($image->image) ?>" alt="" class="rounded border" style="width:88px;height:88px;object-fit:cover">
                      <a href="<?= $storeUrl ?>/products/delete-image/<?= (int) $image->id ?>" class="btn btn-sm btn-danger position-absolute top-0 end-0" onclick="return confirm('Delete this image?');">&times;</a>
                    </div>
                  <?php endforeach; ?>
                </div>
              <?php endif; ?>
            </div>
          </div>
        </div>

        <div class="tab-pane fade" id="tab-pricing">
          <div class="row g-3">
            <div class="col-md-4">
              <label class="form-label">Your cost (<?= htmlspecialchars(store_currency($store)) ?>)</label>
              <input type="text" class="form-control" value="<?= number_format($costPrice, 2) ?>" disabled>
            </div>
            <div class="col-md-4">
              <label class="form-label">Recommended max</label>
              <input type="text" class="form-control" value="<?= $maxSale > 0 ? number_format($maxSale, 2) : '—' ?>" disabled>
            </div>
            <div class="col-md-4">
              <label class="form-label">Selling price (<?= htmlspecialchars(store_currency($store)) ?>)</label>
              <input type="number" step="0.01" min="<?= $costPrice > 0 ? number_format($costPrice, 2, '.', '') : '0' ?>" name="price" id="storeSellPrice" class="form-control" required value="<?= htmlspecialchars($val('price', '0.00')) ?>" data-cost="<?= number_format($costPrice, 2, '.', '') ?>" data-max="<?= number_format($maxSale, 2, '.', '') ?>">
              <div id="priceWarning" class="alert alert-warning py-2 px-3 mt-2 mb-0 small d-none">Above recommended maximum — you can still save.</div>
            </div>
          </div>
        </div>

        <div class="tab-pane fade" id="tab-categories">
          <p class="text-muted small">Link this product to one or more categories for your country (shop filters, home circles, category pages).</p>
          <div class="row g-2">
            <?php foreach ($allCategories as $cat): ?>
              <div class="col-md-4">
                <label class="border rounded p-2 d-flex gap-2 align-items-center">
                  <input type="checkbox" name="categories[]" value="<?= (int) $cat->id ?>" <?= in_array((int) $cat->id, $productCategoryIds, true) ? 'checked' : '' ?>>
                  <span><?= htmlspecialchars(($cat->icon ? $cat->icon . ' ' : '') . $cat->name) ?></span>
                </label>
              </div>
            <?php endforeach; ?>
            <?php if (empty($allCategories)): ?>
              <div class="col-12 text-muted">No categories available yet.</div>
            <?php endif; ?>
          </div>
        </div>

        <div class="tab-pane fade" id="tab-shipping">
          <p class="text-muted small">Customers see a delivery date range calculated from today plus these days.</p>
          <div class="row g-3">
            <div class="col-md-4">
              <label class="form-label">Minimum days</label>
              <input type="number" min="0" step="1" name="ship_min_days" id="shipMinDays" class="form-control" value="<?= (int) $val('ship_min_days', 0) ?>">
            </div>
            <div class="col-md-4">
              <label class="form-label">Maximum days</label>
              <input type="number" min="0" step="1" name="ship_max_days" id="shipMaxDays" class="form-control" value="<?= (int) $val('ship_max_days', 0) ?>">
            </div>
            <div class="col-12">
              <div class="alert alert-light border mb-0" id="shipPreview">Set shipping days to preview delivery dates.</div>
            </div>
          </div>
        </div>

        <div class="tab-pane fade" id="tab-seo">
          <div class="row g-3">
            <div class="col-12">
              <label class="form-label">URL slug</label>
              <input type="text" name="slug" id="productSlug" class="form-control" value="<?= htmlspecialchars($val('slug')) ?>">
            </div>
            <div class="col-12">
              <label class="form-label">Meta title</label>
              <input type="text" name="seo_title" class="form-control" value="<?= htmlspecialchars($val('seo_title')) ?>">
            </div>
            <div class="col-12">
              <label class="form-label">Meta description</label>
              <textarea name="seo_description" class="form-control" rows="4"><?= htmlspecialchars($val('seo_description')) ?></textarea>
            </div>
            <div class="col-12">
              <label class="form-label">Meta keywords</label>
              <input type="text" name="seo_keywords" class="form-control" value="<?= htmlspecialchars($val('seo_keywords')) ?>">
            </div>
          </div>
        </div>
      </div>

      <div class="d-flex gap-2 mt-4">
        <button type="submit" class="btn btn-store-primary"><?= $isEdit ? 'Update' : 'Create' ?> Product</button>
        <a href="<?= $storeUrl ?>/my-products" class="btn btn-outline-secondary">Cancel</a>
      </div>
    </form>
  </div>
</div>
<script>
(function () {
  var price = document.getElementById('storeSellPrice');
  var warn = document.getElementById('priceWarning');
  if (!price) return;
  function sync() {
    var cost = parseFloat(price.getAttribute('data-cost') || '0') || 0;
    var max = parseFloat(price.getAttribute('data-max') || '0') || 0;
    var val = parseFloat(price.value || '0') || 0;
    if (warn) warn.classList.toggle('d-none', !(max > 0 && val > max));
    if (cost > 0 && val < cost) price.setCustomValidity('Price cannot be less than cost.');
    else price.setCustomValidity('');
  }
  price.addEventListener('input', sync);
  sync();
})();
(function () {
  var minEl = document.getElementById('shipMinDays');
  var maxEl = document.getElementById('shipMaxDays');
  var out = document.getElementById('shipPreview');
  if (!minEl || !maxEl || !out) return;
  function label(days) {
    var d = new Date();
    d.setDate(d.getDate() + days);
    var months = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
    return d.getDate() + ' ' + months[d.getMonth()];
  }
  function preview() {
    var min = parseInt(minEl.value, 10) || 0;
    var max = parseInt(maxEl.value, 10) || 0;
    if (!min && !max) {
      out.textContent = 'Set shipping days to preview delivery dates.';
      return;
    }
    if (max && min && min > max) { var t = min; min = max; max = t; }
    if (!min) min = max;
    if (!max) max = min;
    out.textContent = min === max
      ? 'This product will arrive on ' + label(min) + '.'
      : 'This product will arrive between ' + label(min) + ' and ' + label(max) + '.';
  }
  minEl.addEventListener('input', preview);
  maxEl.addEventListener('input', preview);
  preview();
})();
</script>
