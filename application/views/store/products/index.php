<?php $this->load->view('flash'); ?>
<?php
$filters = isset($filters) ? $filters : array('q' => '', 'category_id' => 0, 'subcategory_id' => 0);
$filterQ = isset($filters['q']) ? $filters['q'] : '';
$filterCategoryId = isset($filters['category_id']) ? (int) $filters['category_id'] : 0;
$filterSubcategoryId = isset($filters['subcategory_id']) ? (int) $filters['subcategory_id'] : 0;
$categoryTree = isset($category_tree) ? $category_tree : array();
$hasFilters = !empty($has_filters);
$productCount = is_array($products) ? count($products) : 0;
?>
<p class="text-muted mb-3">These are catalog products not yet in your store. Add one to copy it, then you can change title, slug, images, and price. Cost already includes ecommerce commission and <?= number_format((float) (isset($platform_fee_percent) ? $platform_fee_percent : platform_fee_percent()), 2) ?>% platform fee<?php $plus = store_price_plus_amount($store); if ($plus > 0): ?> · selling price is cost + <?= format_money($plus) ?> plus amount<?php endif; ?>.</p>

<form method="get" action="<?= $storeUrl ?>/available-products" class="store-card store-product-filters mb-4" id="storeProductFilterForm">
  <div class="card-body">
    <div class="row g-2 align-items-end">
      <div class="col-lg-4">
        <label class="form-label small text-muted mb-1" for="filterQ">Search</label>
        <input type="text" name="q" id="filterQ" class="form-control" value="<?= htmlspecialchars($filterQ) ?>" placeholder="Search name, SKU, brand…">
      </div>
      <div class="col-md-4 col-lg-3">
        <label class="form-label small text-muted mb-1" for="filterCategory">Category</label>
        <select name="category_id" id="filterCategory" class="form-select">
          <option value="0">All categories</option>
          <?php foreach ($categoryTree as $parent): ?>
            <option value="<?= (int) $parent['id'] ?>" <?= $filterCategoryId === (int) $parent['id'] ? 'selected' : '' ?>><?= htmlspecialchars($parent['name']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-md-4 col-lg-3">
        <label class="form-label small text-muted mb-1" for="filterSubcategory">Sub category</label>
        <select name="subcategory_id" id="filterSubcategory" class="form-select" <?= $filterCategoryId < 1 ? 'disabled' : '' ?>>
          <option value="0">All sub categories</option>
          <?php foreach ($categoryTree as $parent): ?>
            <?php if ((int) $parent['id'] !== $filterCategoryId) continue; ?>
            <?php foreach ($parent['children'] as $child): ?>
              <option value="<?= (int) $child['id'] ?>" <?= $filterSubcategoryId === (int) $child['id'] ? 'selected' : '' ?>><?= htmlspecialchars($child['name']) ?></option>
            <?php endforeach; ?>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-md-4 col-lg-2 d-flex gap-2">
        <button type="submit" class="btn btn-store-primary flex-grow-1">Filter</button>
        <?php if ($hasFilters): ?>
          <a href="<?= $storeUrl ?>/available-products" class="btn btn-outline-secondary">Reset</a>
        <?php endif; ?>
      </div>
    </div>
  </div>
</form>

<?php if ($productCount): ?>
  <p class="small text-muted mb-3"><?= (int) $productCount ?> product<?= $productCount === 1 ? '' : 's' ?> not yet in your store<?= $hasFilters ? ' for these filters' : '' ?>.</p>
<?php endif; ?>

<?php if (empty($products)): ?>
  <div class="store-card"><div class="card-body text-muted">
    <?php if ($hasFilters): ?>
      No products match these filters. <a href="<?= $storeUrl ?>/available-products">Clear filters</a>
    <?php else: ?>
      No products left to add for this store. <a href="<?= $storeUrl ?>/products">View my products</a>
    <?php endif; ?>
  </div></div>
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

<script>
(function () {
  var tree = <?= json_encode($categoryTree, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
  var selectedSub = <?= (int) $filterSubcategoryId ?>;
  var categoryEl = document.getElementById('filterCategory');
  var subEl = document.getElementById('filterSubcategory');
  if (!categoryEl || !subEl) {
    return;
  }

  function childrenFor(categoryId) {
    var id = parseInt(categoryId, 10) || 0;
    for (var i = 0; i < tree.length; i++) {
      if (parseInt(tree[i].id, 10) === id) {
        return tree[i].children || [];
      }
    }
    return [];
  }

  function fillSubcategories(keepSelected) {
    var parentId = parseInt(categoryEl.value, 10) || 0;
    var children = childrenFor(parentId);
    var stillValid = false;
    subEl.innerHTML = '';
    var allOpt = document.createElement('option');
    allOpt.value = '0';
    allOpt.textContent = 'All sub categories';
    subEl.appendChild(allOpt);
    for (var i = 0; i < children.length; i++) {
      var childId = parseInt(children[i].id, 10);
      if (keepSelected && childId === selectedSub) {
        stillValid = true;
      }
      var opt = document.createElement('option');
      opt.value = String(childId);
      opt.textContent = children[i].name || '';
      subEl.appendChild(opt);
    }
    subEl.disabled = parentId < 1 || children.length === 0;
    if (subEl.disabled) {
      subEl.value = '0';
      return;
    }
    subEl.value = stillValid ? String(selectedSub) : '0';
  }

  fillSubcategories(true);
  categoryEl.addEventListener('change', function () {
    selectedSub = 0;
    fillSubcategories(false);
  });
  var form = document.getElementById('storeProductFilterForm');
  if (form) {
    form.addEventListener('submit', function () {
      subEl.disabled = false;
    });
  }
})();
</script>
