<?php $this->load->view('flash'); ?>
<?php
$filters = isset($filters) ? $filters : array('q' => '', 'category_id' => 0, 'subcategory_id' => 0, 'trending' => '');
$filterQ = isset($filters['q']) ? $filters['q'] : '';
$filterCategoryId = isset($filters['category_id']) ? (int) $filters['category_id'] : 0;
$filterSubcategoryId = isset($filters['subcategory_id']) ? (int) $filters['subcategory_id'] : 0;
$filterTrending = isset($filters['trending']) ? (string) $filters['trending'] : '';
$categoryTree = isset($category_tree) ? $category_tree : array();
$hasFilters = !empty($has_filters);
$productCount = is_array($products) ? count($products) : 0;
$fallbackImg = base_url('assets/frontend/fruitables/img/fruite-item-5.jpg');
?>
<div class="d-flex justify-content-between align-items-center mb-3">
  <p class="text-muted mb-0">Products in your store. Edit title, slug, images, and price anytime.</p>
  <div class="d-flex gap-2">
    <a href="<?= $storeUrl; ?>/available-products" class="btn btn-outline-secondary">Add from catalog</a>
    <a href="<?= $storeUrl; ?>/products/form" class="btn btn-store-primary"><i class="bi bi-plus-lg me-1"></i> Add Product</a>
  </div>
</div>

<form method="get" action="<?= $storeUrl ?>/products" class="store-card store-product-filters mb-4" id="storeProductFilterForm">
  <div class="card-body">
    <div class="row g-2 align-items-end">
      <div class="col-lg-3">
        <label class="form-label small text-muted mb-1" for="filterQ">Search</label>
        <input type="text" name="q" id="filterQ" class="form-control" value="<?= htmlspecialchars($filterQ) ?>" placeholder="Search name, SKU, brand…">
      </div>
      <div class="col-md-4 col-lg-2">
        <label class="form-label small text-muted mb-1" for="filterCategory">Category</label>
        <select name="category_id" id="filterCategory" class="form-select">
          <option value="0">All categories</option>
          <?php foreach ($categoryTree as $parent): ?>
            <option value="<?= (int) $parent['id'] ?>" <?= $filterCategoryId === (int) $parent['id'] ? 'selected' : '' ?>><?= htmlspecialchars($parent['name']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-md-4 col-lg-2">
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
      <div class="col-md-4 col-lg-2">
        <label class="form-label small text-muted mb-1" for="filterTrending">Trending</label>
        <select name="trending" id="filterTrending" class="form-select">
          <option value="" <?= $filterTrending === '' ? 'selected' : '' ?>>All</option>
          <option value="1" <?= $filterTrending === '1' ? 'selected' : '' ?>>Trending</option>
          <option value="0" <?= $filterTrending === '0' ? 'selected' : '' ?>>Not Trending</option>
        </select>
      </div>
      <div class="col-md-4 col-lg-3 d-flex gap-2">
        <button type="submit" class="btn btn-store-primary flex-grow-1">Filter</button>
        <?php if ($hasFilters): ?>
          <a href="<?= $storeUrl ?>/products" class="btn btn-outline-secondary">Reset</a>
        <?php endif; ?>
      </div>
    </div>
  </div>
</form>

<?php if ($productCount): ?>
  <p class="small text-muted mb-3"><?= (int) $productCount ?> product<?= $productCount === 1 ? '' : 's' ?> in your store<?= $hasFilters ? ' for these filters' : '' ?>.</p>
<?php endif; ?>

<div class="store-card">
  <div class="card-body p-0">
    <div class="table-responsive">
      <table class="table table-hover mb-0 align-middle">
        <thead>
          <tr>
            <th>Product</th>
            <th>Slug</th>
            <th>Price</th>
            <th>Status</th>
            <th class="text-end">Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($products as $product): ?>
            <?php $image = product_image_url(isset($product->image) ? $product->image : '', $fallbackImg); ?>
            <tr>
              <td>
                <div class="d-flex align-items-center gap-2">
                  <img src="<?= htmlspecialchars($image) ?>" alt="" width="48" height="48" style="object-fit:cover; border-radius:6px;">
                  <span class="fw-medium"><?= htmlspecialchars($product->name) ?></span>
                </div>
              </td>
              <td class="text-muted"><?= htmlspecialchars($product->sku ? $product->sku : $product->slug) ?></td>
              <td><?= format_money((float) $product->price) ?></td>
              <td><?= ((int) $product->status === 1) ? '<span class="badge text-bg-success">Active</span>' : '<span class="badge text-bg-secondary">Inactive</span>' ?></td>
              <td class="text-end">
                <a href="<?= $storeUrl ?>/products/form/<?= (int) $product->id ?>" class="btn btn-sm btn-outline-secondary">Edit</a>
                <a href="<?= $storeUrl ?>/products/delete/<?= (int) $product->id ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete this product from your store?');">Delete</a>
              </td>
            </tr>
          <?php endforeach; ?>
          <?php if (empty($products)): ?>
            <tr>
              <td colspan="5" class="text-center text-muted py-4">
                <?php if ($hasFilters): ?>
                  No products match these filters. <a href="<?= $storeUrl ?>/products">Clear filters</a>
                <?php else: ?>
                  No products yet. <a href="<?= $storeUrl ?>/available-products">Copy from Available Products</a> or add your own.
                <?php endif; ?>
              </td>
            </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

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

  categoryEl.addEventListener('change', function () {
    selectedSub = 0;
    fillSubcategories(false);
  });
  fillSubcategories(true);
})();
</script>
