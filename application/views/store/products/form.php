<?php
$isEdit = !empty($product);
$lockSku = $isEdit && !empty($product->source_product_id);
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
    <?php if ($lockSku): ?>
      <span class="badge text-bg-light border text-muted">Edits apply to this store only. SKU stays from catalog.</span>
    <?php endif; ?>
  </div>
  <div class="card-body">
    <form method="post" enctype="multipart/form-data" action="<?= $storeUrl ?>/products/save<?= $isEdit ? '/' . (int) $product->id : '' ?>" id="storeProductForm">
      <ul class="nav nav-tabs mb-3" role="tablist">
        <li class="nav-item" role="presentation">
          <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#tab-general" type="button">General</button>
        </li>
        <li class="nav-item" role="presentation">
          <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-images" type="button">Images</button>
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
          <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-english" type="button">English</button>
        </li>
        <li class="nav-item" role="presentation">
          <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-seo" type="button">SEO</button>
        </li>
      </ul>

      <div class="tab-content">
        <div class="tab-pane fade show active" id="tab-general">
          <div class="row g-3">
            <div class="col-12">
              <label class="form-label">Title</label>
              <input type="text" name="name" id="productName" class="form-control" required value="<?= htmlspecialchars($val('name')) ?>">
            </div>
            <div class="col-md-6">
              <label class="form-label">SKU</label>
              <input type="text" name="sku" class="form-control" <?= $lockSku ? 'disabled' : '' ?> value="<?= htmlspecialchars($val('sku')) ?>">
            </div>
            <div class="col-md-6">
              <label class="form-label">Parent SKU</label>
              <input type="text" name="parent_sku" class="form-control" maxlength="100" <?= $lockSku ? 'disabled' : '' ?> placeholder="Empty for parent product" value="<?= htmlspecialchars($val('parent_sku')) ?>">
            </div>
            <div class="col-12 js-parent-field">
              <label class="form-label">Variation type</label>
              <input type="text" name="options_title" class="form-control" maxlength="150" placeholder="Select color, Select size" value="<?= htmlspecialchars($val('options_title')) ?>">
              <div class="form-text">Parent products only. Shown above the option boxes on the product page. Write Select color, Select size, or any heading you want.</div>
            </div>
            <div class="col-md-6">
              <label class="form-label">Brand</label>
              <input type="text" name="brand" class="form-control" maxlength="150" value="<?= htmlspecialchars($val('brand')) ?>">
            </div>
            <div class="col-md-6">
              <label class="form-label">Made by</label>
              <input type="text" name="made_by" class="form-control" maxlength="150" placeholder="Manufacturer / made by" value="<?= htmlspecialchars($val('made_by')) ?>">
            </div>
            <div class="col-md-6">
              <label class="form-label">Status</label>
              <select name="status" class="form-select">
                <option value="1" <?= (int) $val('status', 1) === 1 ? 'selected' : '' ?>>Active</option>
                <option value="0" <?= (int) $val('status', 1) === 0 ? 'selected' : '' ?>>Inactive</option>
              </select>
            </div>
            <div class="col-md-6">
              <label class="form-label d-block">Trending Picks</label>
              <div class="form-check mt-2">
                <input class="form-check-input" type="checkbox" name="is_trending" value="1" id="isTrending" <?= $isEdit && !empty($product->is_trending) ? 'checked' : '' ?>>
                <label class="form-check-label" for="isTrending">Show in Trending Picks</label>
              </div>
              <div class="form-text">Display this product in the Trending Picks section on product pages.</div>
            </div>
            <div class="col-md-6">
              <label class="form-label">Trending Order</label>
              <input type="number" name="trending_order" class="form-control" min="0" step="1" value="<?= htmlspecialchars((string) (int) $val('trending_order', 0)) ?>">
              <div class="form-text">Lower number = higher priority. Max 4 products shown.</div>
            </div>
            <div class="col-md-6">
              <label class="form-label d-block">Default child</label>
              <div class="form-check mt-2">
                <input class="form-check-input" type="checkbox" name="is_default" value="1" id="isDefaultChild" <?= $lockSku ? 'disabled' : '' ?> <?= $isEdit && !empty($product->is_default) ? 'checked' : '' ?>>
                <label class="form-check-label" for="isDefaultChild">Selected by default when the parent product opens</label>
              </div>
            </div>
            <div class="col-md-6">
              <label class="form-label">Sort</label>
              <input type="number" name="sort_order" class="form-control" min="0" step="1" value="<?= htmlspecialchars((string) (int) $val('sort_order', 0)) ?>">
              <div class="form-text">Lower numbers appear first on the product page.</div>
            </div>
            <div class="col-12">
              <label class="form-label">Description</label>
              <textarea name="description" class="form-control" rows="5"><?= htmlspecialchars($val('description')) ?></textarea>
            </div>
            <div class="col-12">
              <label class="form-label">Details</label>
              <textarea name="details" class="form-control" rows="10"><?= htmlspecialchars($val('details')) ?></textarea>
              <span class="help-block text-muted">Shown on your storefront product page. HTML is allowed.</span>
            </div>
          </div>
        </div>

        <div class="tab-pane fade" id="tab-images">
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
              <span class="help-block text-muted">Includes ecommerce commission and <?= number_format(platform_fee_percent(), 2) ?>% platform fee.</span>
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
          <p class="text-muted small">Estimated Delivery (working days). Customers see a delivery range from today plus these days. AliExpress without a scraped ETA defaults to 7–15.</p>
          <div class="row g-3">
            <div class="col-md-4">
              <label class="form-label">Est. delivery min</label>
              <input type="number" min="0" step="1" name="ship_min_days" id="shipMinDays" class="form-control" value="<?= (int) $val('ship_min_days', 0) ?>">
            </div>
            <div class="col-md-4">
              <label class="form-label">Est. delivery max</label>
              <input type="number" min="0" step="1" name="ship_max_days" id="shipMaxDays" class="form-control" value="<?= (int) $val('ship_max_days', 0) ?>">
            </div>
            <div class="col-12">
              <div class="alert alert-light border mb-0" id="shipPreview">Set shipping days to preview delivery dates.</div>
            </div>
          </div>
        </div>

        <div class="tab-pane fade" id="tab-english">
          <p class="text-muted small">English copy shown when a shopper switches the storefront to English. Bulk AI rewrite fills these in the same request as the country language.</p>
          <div class="row g-3">
            <div class="col-12">
              <label class="form-label">Title (English)</label>
              <input type="text" name="name_en" class="form-control" value="<?= htmlspecialchars($val('name_en')) ?>">
            </div>
            <div class="col-12">
              <label class="form-label">Short detail (English)</label>
              <textarea name="short_details_en" class="form-control" rows="5"><?= htmlspecialchars((string) $val('short_details_en')) ?></textarea>
            </div>
            <div class="col-12">
              <label class="form-label">Details (English)</label>
              <textarea name="details_en" class="form-control" rows="10"><?= htmlspecialchars((string) $val('details_en')) ?></textarea>
            </div>
            <div class="col-12">
              <label class="form-label">Meta title (English)</label>
              <input type="text" name="seo_title_en" class="form-control" value="<?= htmlspecialchars($val('seo_title_en')) ?>">
            </div>
            <div class="col-12">
              <label class="form-label">Meta description (English)</label>
              <textarea name="seo_description_en" class="form-control" rows="3"><?= htmlspecialchars($val('seo_description_en')) ?></textarea>
            </div>
            <div class="col-12">
              <label class="form-label">Meta keywords (English)</label>
              <input type="text" name="seo_keywords_en" class="form-control" value="<?= htmlspecialchars($val('seo_keywords_en')) ?>">
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
        <a href="<?= $storeUrl ?>/products" class="btn btn-outline-secondary">Cancel</a>
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
(function () {
  var parentSku = document.querySelector('input[name="parent_sku"]');
  var fields = document.querySelectorAll('.js-parent-field');
  if (!parentSku || !fields.length) return;
  function sync() {
    var isChild = (parentSku.value || '').trim() !== '';
    fields.forEach(function (el) { el.style.display = isChild ? 'none' : ''; });
  }
  parentSku.addEventListener('input', sync);
  sync();
})();
</script>
