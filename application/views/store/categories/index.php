<?php $this->load->view('flash'); ?>

<div class="store-card mb-3">
  <div class="card-header">Default category page hero</div>
  <div class="card-body">
    <p class="text-muted small mb-3">Used on category pages when a category has no custom hero image. You can still override hero per category.</p>
    <?php if (!empty($category_hero_default)): ?>
      <img src="<?= base_url($category_hero_default) ?>" alt="" style="height:80px;object-fit:cover;border-radius:8px" class="mb-2 d-block">
    <?php endif; ?>
  </div>
</div>

<div class="store-card mb-3">
  <div class="card-header">Theme categories</div>
  <div class="card-body">
    <?php if (empty($country_id)): ?>
      <div class="alert alert-warning mb-0">Your store has no country assigned. Ask super admin to set a country on the store first.</div>
    <?php else: ?>
      <p class="text-muted small">Select categories from your country for this theme. Checked categories appear in navigation / category pages. Mark “Home” for the circles after the home hero. Customize SEO and images per category.</p>
      <form method="post" enctype="multipart/form-data" action="<?= $storeUrl ?>/categories">
        <div class="mb-3">
          <label class="form-label">Upload / replace default category hero</label>
          <input type="file" name="category_hero_default" class="form-control" accept="image/*">
          <div class="form-text">Size: 1280 × 640 px</div>
        </div>
        <div class="table-responsive">
          <table class="table align-middle">
            <thead>
              <tr>
                <th width="40">Use</th>
                <th width="50">Home</th>
                <th>Category</th>
                <th>Parent</th>
                <th>Products</th>
                <th></th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($categories as $cat): ?>
                <?php
                  $isRoot = empty($cat->parent_id);
                  $checked = in_array((int) $cat->id, $enabled_ids, true);
                  $onHome = in_array((int) $cat->id, $home_ids, true);
                ?>
                <tr>
                  <td>
                    <input type="checkbox" name="categories[]" value="<?= (int) $cat->id ?>" <?= $checked ? 'checked' : '' ?>>
                  </td>
                  <td>
                    <?php if ($isRoot): ?>
                      <input type="checkbox" name="home_categories[]" value="<?= (int) $cat->id ?>" <?= $onHome ? 'checked' : '' ?>>
                    <?php else: ?>
                      <span class="text-muted">—</span>
                    <?php endif; ?>
                  </td>
                  <td>
                    <?php if (!$isRoot): ?><span class="text-muted">↳ </span><?php endif; ?>
                    <?= htmlspecialchars(($cat->icon ? $cat->icon . ' ' : '') . $cat->name) ?>
                  </td>
                  <td class="text-muted small"><?= $isRoot ? 'Top-level' : 'Sub' ?></td>
                  <td><?= (int) $cat->product_count ?></td>
                  <td class="text-end">
                    <a class="btn btn-sm btn-outline-secondary" href="<?= $storeUrl ?>/categories/edit/<?= (int) $cat->id ?>">SEO &amp; images</a>
                  </td>
                </tr>
              <?php endforeach; ?>
              <?php if (empty($categories)): ?>
                <tr><td colspan="6" class="text-muted">No categories for your country yet. Super admin creates them under Countries → Categories.</td></tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
        <button type="submit" class="btn btn-store-primary">Save theme categories</button>
      </form>
    <?php endif; ?>
  </div>
</div>
