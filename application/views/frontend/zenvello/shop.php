<?php
$filters = isset($filters) ? $filters : array('category' => '', 'q' => '', 'min' => null, 'max' => null, 'in_stock' => false, 'on_sale' => false, 'sort' => '');
$categories = isset($categories) ? $categories : array();
$activeCategory = isset($active_category) ? $active_category : null;
$bounds = isset($price_bounds) ? $price_bounds : array('min' => 0, 'max' => 100);
$minVal = $filters['min'] !== null ? $filters['min'] : $bounds['min'];
$maxVal = $filters['max'] !== null ? $filters['max'] : $bounds['max'];
$shopBase = storefront_url('shop');
?>
<nav class="container breadcrumb" aria-label="Breadcrumb">
  <ol>
    <li><a href="<?= storefront_url('shop/index') ?>">Home</a></li>
    <?php if ($activeCategory): ?>
      <li><a href="<?= $shopBase ?>">Shop</a></li>
      <li aria-current="page"><?= htmlspecialchars($activeCategory->name) ?></li>
    <?php else: ?>
      <li aria-current="page">All Products</li>
    <?php endif; ?>
  </ol>
</nav>

<div class="container">
  <section class="cathero">
    <img src="<?= $assets ?>assets/images/hero/hero-living.jpg" alt="<?= htmlspecialchars($store->name) ?>">
    <div class="cathero__body">
      <p class="cathero__kicker"><?= $activeCategory ? 'Category' : 'Everything In One Place' ?></p>
      <h1 class="cathero__title"><?= htmlspecialchars($activeCategory ? $activeCategory->name : 'All Products') ?></h1>
      <p class="cathero__text"><?= htmlspecialchars($activeCategory && $activeCategory->description ? $activeCategory->description : ('Browse the full ' . $store->name . ' range.')) ?></p>
      <a class="btn btn--yellow btn--lg" href="#products">Start Browsing <span class="arrow" aria-hidden="true">&rarr;</span></a>
    </div>
  </section>
</div>

<div class="container catalog" id="products">
  <aside class="filters" id="filters" aria-label="Product filters">
    <button class="filters__toggle" type="button" aria-expanded="false" data-filters-toggle>
      <span>Filter By</span>
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" width="16" height="16" aria-hidden="true"><polyline points="6 9 12 15 18 9"/></svg>
    </button>

    <form class="filters__panel" method="get" action="<?= $shopBase ?>">
      <div class="filters__head">
        <h2>Filter By</h2>
        <a class="filters__clear" href="<?= $shopBase ?>">Clear All</a>
      </div>

      <div class="fgroup">
        <button class="fgroup__head" type="button" aria-expanded="true">Category
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="6 9 12 15 18 9"/></svg>
        </button>
        <div class="fgroup__body">
          <label class="check">
            <input type="radio" name="category" value="" <?= $filters['category'] === '' ? 'checked' : '' ?> onchange="this.form.submit()">
            <span>Everything</span>
          </label>
          <?php foreach ($categories as $cat): ?>
            <?php if ((int) $cat->product_count < 1 && $filters['category'] !== $cat->slug) continue; ?>
            <label class="check">
              <input type="radio" name="category" value="<?= htmlspecialchars($cat->slug) ?>" <?= $filters['category'] === $cat->slug ? 'checked' : '' ?> onchange="this.form.submit()">
              <span><?= htmlspecialchars(($cat->icon ? $cat->icon . ' ' : '') . $cat->name) ?> <span class="check__count">(<?= (int) $cat->product_count ?>)</span></span>
            </label>
          <?php endforeach; ?>
        </div>
      </div>

      <div class="fgroup">
        <button class="fgroup__head" type="button" aria-expanded="true">Price Range
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="6 9 12 15 18 9"/></svg>
        </button>
        <div class="fgroup__body">
          <div class="range__inputs" style="display:grid;grid-template-columns:1fr 1fr;gap:8px">
            <label class="range__field">Min
              <input type="number" name="min" step="0.01" min="0" value="<?= htmlspecialchars((string) $minVal) ?>">
            </label>
            <label class="range__field">Max
              <input type="number" name="max" step="0.01" min="0" value="<?= htmlspecialchars((string) $maxVal) ?>">
            </label>
          </div>
        </div>
      </div>

      <div class="fgroup">
        <button class="fgroup__head" type="button" aria-expanded="true">Availability
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="6 9 12 15 18 9"/></svg>
        </button>
        <div class="fgroup__body">
          <label class="check">
            <input type="checkbox" name="in_stock" value="1" <?= !empty($filters['in_stock']) ? 'checked' : '' ?>>
            <span>In Stock</span>
          </label>
          <label class="check">
            <input type="checkbox" name="on_sale" value="1" <?= !empty($filters['on_sale']) ? 'checked' : '' ?>>
            <span>On Sale</span>
          </label>
        </div>
      </div>

      <div class="fgroup">
        <button class="fgroup__head" type="button" aria-expanded="true">Search / Sort
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="6 9 12 15 18 9"/></svg>
        </button>
        <div class="fgroup__body">
          <input class="search__input" type="search" name="q" value="<?= htmlspecialchars($filters['q']) ?>" placeholder="Search products..." style="width:100%;margin-bottom:10px;padding:10px;border:1px solid var(--line);border-radius:8px">
          <select name="sort" style="width:100%;padding:10px;border:1px solid var(--line);border-radius:8px">
            <option value="" <?= $filters['sort'] === '' ? 'selected' : '' ?>>Newest</option>
            <option value="price_asc" <?= $filters['sort'] === 'price_asc' ? 'selected' : '' ?>>Price: Low to High</option>
            <option value="price_desc" <?= $filters['sort'] === 'price_desc' ? 'selected' : '' ?>>Price: High to Low</option>
            <option value="name" <?= $filters['sort'] === 'name' ? 'selected' : '' ?>>Name A–Z</option>
          </select>
        </div>
      </div>

      <button class="btn btn--yellow" type="submit" style="width:100%;margin-top:8px">Apply Filters</button>
    </form>
  </aside>

  <section aria-label="Products">
    <div class="section-head" style="margin-bottom:16px">
      <div>
        <h2 class="section-title" id="shop-title"><?= htmlspecialchars($activeCategory ? $activeCategory->name : 'Shop') ?></h2>
        <p class="section-sub"><?= count($products) ?> product<?= count($products) === 1 ? '' : 's' ?></p>
      </div>
    </div>
    <div class="grid-6">
      <?php if (empty($products)): ?>
        <p class="section-sub">No products match these filters.</p>
      <?php else: ?>
        <?php foreach ($products as $product): ?>
          <?php $this->load->view('frontend/zenvello/product_card', array('product' => $product, 'assets' => $assets, 'is_preview' => !empty($is_preview))); ?>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>
  </section>
</div>
