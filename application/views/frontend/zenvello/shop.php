<?php
$filters = isset($filters) ? $filters : array('category' => '', 'q' => '', 'min' => null, 'max' => null, 'in_stock' => false, 'on_sale' => false, 'sort' => '');
$categories = isset($categories) ? $categories : array();
$activeCategory = isset($active_category) ? $active_category : null;
$bounds = isset($price_bounds) ? $price_bounds : array('min' => 0, 'max' => 100);
$minVal = $filters['min'] !== null ? $filters['min'] : $bounds['min'];
$maxVal = $filters['max'] !== null ? $filters['max'] : $bounds['max'];
$shopBase = storefront_url('shop');
$shopHero = theme_setting($settings, 'shop_hero_image', '');
$shopHeroSrc = $shopHero !== ''
    ? storefront_asset_url($shopHero)
    : $assets . 'assets/images/hero/hero-living.jpg';
?>
<nav class="container breadcrumb" aria-label="<?= e_ui('nav.breadcrumb') ?>">
  <ol>
    <li><a href="<?= storefront_url('') ?>"><?= e_ui('nav.home') ?></a></li>
    <?php if ($activeCategory): ?>
      <li><a href="<?= $shopBase ?>"><?= e_ui('nav.shop') ?></a></li>
      <li aria-current="page"><?= htmlspecialchars(category_store_name($activeCategory)) ?></li>
    <?php else: ?>
      <li aria-current="page"><?= e_ui('shop.all_products') ?></li>
    <?php endif; ?>
  </ol>
</nav>

<div class="container">
  <section class="cathero">
    <img src="<?= htmlspecialchars($shopHeroSrc) ?>" alt="<?= htmlspecialchars($store->name) ?>">
    <div class="cathero__body">
      <p class="cathero__kicker"><?= $activeCategory ? e_ui('shop.category') : e_ui('shop.everything') ?></p>
      <h1 class="cathero__title"><?= htmlspecialchars($activeCategory ? category_store_name($activeCategory) : store_ui('shop.all_products')) ?></h1>
      <p class="cathero__text"><?= htmlspecialchars($activeCategory && $activeCategory->description ? $activeCategory->description : store_ui('shop.browse_range')) ?></p>
      <a class="btn btn--yellow btn--lg" href="#products"><?= e_ui('shop.start_browsing') ?> <span class="arrow" aria-hidden="true">&rarr;</span></a>
    </div>
  </section>
</div>

<div class="container catalog" id="products">
  <aside class="filters" id="filters" aria-label="<?= e_ui('shop.filters') ?>">
    <button class="filters__toggle" type="button" aria-expanded="false" data-filters-toggle>
      <span><?= e_ui('shop.filter_by') ?></span>
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" width="16" height="16" aria-hidden="true"><polyline points="6 9 12 15 18 9"/></svg>
    </button>

    <form class="filters__panel" method="get" action="<?= $shopBase ?>">
      <div class="filters__head">
        <h2><?= e_ui('shop.filter_by') ?></h2>
        <a class="filters__clear" href="<?= $shopBase ?>"><?= e_ui('shop.clear_all') ?></a>
      </div>

      <div class="fgroup">
        <button class="fgroup__head" type="button" aria-expanded="true"><?= e_ui('shop.category') ?>
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="6 9 12 15 18 9"/></svg>
        </button>
        <div class="fgroup__body">
          <label class="check">
            <input type="radio" name="category" value="" <?= $filters['category'] === '' ? 'checked' : '' ?> onchange="this.form.submit()">
            <span><?= e_ui('shop.everything_option') ?></span>
          </label>
          <?php foreach ($categories as $cat): ?>
            <?php if ((int) $cat->product_count < 1 && $filters['category'] !== $cat->slug) continue; ?>
            <label class="check">
              <input type="radio" name="category" value="<?= htmlspecialchars($cat->slug) ?>" <?= $filters['category'] === $cat->slug ? 'checked' : '' ?> onchange="this.form.submit()">
              <span><?= htmlspecialchars(($cat->icon ? $cat->icon . ' ' : '') . category_store_name($cat)) ?> <span class="check__count">(<?= (int) $cat->product_count ?>)</span></span>
            </label>
          <?php endforeach; ?>
        </div>
      </div>

      <div class="fgroup">
        <button class="fgroup__head" type="button" aria-expanded="true"><?= e_ui('shop.price_range') ?>
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="6 9 12 15 18 9"/></svg>
        </button>
        <div class="fgroup__body">
          <div class="range__inputs" style="display:grid;grid-template-columns:1fr 1fr;gap:8px">
            <label class="range__field"><?= e_ui('shop.min') ?>
              <input type="number" name="min" step="0.01" min="0" value="<?= htmlspecialchars((string) $minVal) ?>">
            </label>
            <label class="range__field"><?= e_ui('shop.max') ?>
              <input type="number" name="max" step="0.01" min="0" value="<?= htmlspecialchars((string) $maxVal) ?>">
            </label>
          </div>
        </div>
      </div>

      <div class="fgroup">
        <button class="fgroup__head" type="button" aria-expanded="true"><?= e_ui('shop.availability') ?>
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="6 9 12 15 18 9"/></svg>
        </button>
        <div class="fgroup__body">
          <label class="check">
            <input type="checkbox" name="in_stock" value="1" <?= !empty($filters['in_stock']) ? 'checked' : '' ?>>
            <span><?= e_ui('shop.in_stock') ?></span>
          </label>
          <label class="check">
            <input type="checkbox" name="on_sale" value="1" <?= !empty($filters['on_sale']) ? 'checked' : '' ?>>
            <span><?= e_ui('shop.on_sale') ?></span>
          </label>
        </div>
      </div>

      <div class="fgroup">
        <button class="fgroup__head" type="button" aria-expanded="true"><?= e_ui('shop.search_sort') ?>
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="6 9 12 15 18 9"/></svg>
        </button>
        <div class="fgroup__body">
          <input class="search__input" type="search" name="q" value="<?= htmlspecialchars($filters['q']) ?>" placeholder="<?= e_ui('shop.search_products') ?>" style="width:100%;margin-bottom:10px;padding:10px;border:1px solid var(--line);border-radius:8px">
          <select name="sort" style="width:100%;padding:10px;border:1px solid var(--line);border-radius:8px">
            <option value="" <?= $filters['sort'] === '' ? 'selected' : '' ?>><?= e_ui('shop.sort_newest') ?></option>
            <option value="price_asc" <?= $filters['sort'] === 'price_asc' ? 'selected' : '' ?>><?= e_ui('shop.sort_price_asc') ?></option>
            <option value="price_desc" <?= $filters['sort'] === 'price_desc' ? 'selected' : '' ?>><?= e_ui('shop.sort_price_desc') ?></option>
            <option value="name" <?= $filters['sort'] === 'name' ? 'selected' : '' ?>><?= e_ui('shop.sort_name') ?></option>
          </select>
        </div>
      </div>

      <button class="btn btn--yellow" type="submit" style="width:100%;margin-top:8px"><?= e_ui('shop.apply') ?></button>
    </form>
  </aside>

  <section aria-label="<?= e_ui('shop.products') ?>">
    <div class="section-head" style="margin-bottom:16px">
      <div>
        <h2 class="section-title" id="shop-title"><?= htmlspecialchars($activeCategory ? category_store_name($activeCategory) : store_ui('page.shop')) ?></h2>
        <p class="section-sub"><?= htmlspecialchars(storefront_ui_count('shop.product_one', 'shop.product_many', count($products))) ?></p>
      </div>
    </div>
    <div class="grid-4">
      <?php if (empty($products)): ?>
        <p class="section-sub"><?= e_ui('shop.no_match') ?></p>
      <?php else: ?>
        <?php foreach ($products as $product): ?>
          <?php $this->load->view('frontend/zenvello/product_card', array('product' => $product, 'assets' => $assets, 'is_preview' => !empty($is_preview))); ?>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>
  </section>
</div>
