<?php
$this->load->view('flash');
$val = function ($key, $fallback = '') use ($category, $setting) {
    if ($setting && isset($setting->$key) && $setting->$key !== '' && $setting->$key !== null) {
        return $setting->$key;
    }
    if (isset($category->$key) && $category->$key !== '' && $category->$key !== null) {
        return $category->$key;
    }
    if ($key === 'seo_title') {
        return $category->seo_title !== '' ? $category->seo_title : $category->name;
    }
    if ($key === 'seo_description') {
        return $category->seo_description !== '' ? $category->seo_description : (string) $category->description;
    }
    if ($key === 'seo_keywords') {
        return $category->seo_keywords;
    }
    if ($key === 'hero_title') {
        return !empty($category->display_hero_title) ? $category->display_hero_title : $category->name;
    }
    if ($key === 'hero_text') {
        return !empty($category->display_hero_text) ? $category->display_hero_text : (string) $category->description;
    }
    if ($key === 'hero_btn_text') {
        return 'Shop Now';
    }
    if ($key === 'hero_kicker') {
        return 'Shop category';
    }
    return $fallback;
};
$displayImage = !empty($category->display_image) ? $category->display_image : $category->image;
$displayHero = !empty($category->display_hero) ? $category->display_hero : $category->hero_image;
?>

<div class="store-card mb-3">
  <div class="card-header d-flex justify-content-between align-items-center">
    <span>Customize <?= htmlspecialchars($category->name) ?></span>
    <a href="<?= $storeUrl ?>/categories" class="btn btn-sm btn-outline-secondary">Back</a>
  </div>
  <div class="card-body">
    <p class="text-muted small">Store-specific hero, SEO and images override country defaults for your storefront only.</p>
    <form method="post" enctype="multipart/form-data" action="<?= $storeUrl ?>/categories/edit/<?= (int) $category->id ?>">
      <div class="form-check mb-3">
        <input class="form-check-input" type="checkbox" name="show_on_home" value="1" id="show_home" <?= !empty($category->show_on_home) ? 'checked' : '' ?>>
        <label class="form-check-label" for="show_home">Show on home page (top-level circles)</label>
      </div>

      <h6 class="mt-2 mb-3">Category page hero</h6>
      <div class="mb-3">
        <label class="form-label">Hero kicker</label>
        <input type="text" name="hero_kicker" class="form-control" value="<?= htmlspecialchars($val('hero_kicker')) ?>" placeholder="Modern Living">
      </div>
      <div class="mb-3">
        <label class="form-label">Hero title</label>
        <textarea name="hero_title" class="form-control" rows="2"><?= htmlspecialchars($val('hero_title')) ?></textarea>
      </div>
      <div class="mb-3">
        <label class="form-label">Hero text</label>
        <textarea name="hero_text" class="form-control" rows="2"><?= htmlspecialchars($val('hero_text')) ?></textarea>
      </div>
      <div class="row g-3 mb-3">
        <div class="col-md-6">
          <label class="form-label">Button text</label>
          <input type="text" name="hero_btn_text" class="form-control" value="<?= htmlspecialchars($val('hero_btn_text')) ?>">
        </div>
        <div class="col-md-6">
          <label class="form-label">Button link</label>
          <input type="text" name="hero_btn_link" class="form-control" value="<?= htmlspecialchars($val('hero_btn_link')) ?>" placeholder="#category-products or /shop">
        </div>
      </div>
      <div class="row g-3 mb-4">
        <div class="col-md-6">
          <label class="form-label">Category image (home / circles)</label>
          <input type="file" name="image" class="form-control" accept="image/*">
          <div class="form-text">Size: 200 × 200 px</div>
          <?php if ($displayImage): ?>
            <img src="<?= base_url($displayImage) ?>" alt="" class="mt-2" style="height:72px;object-fit:cover;border-radius:8px">
          <?php endif; ?>
        </div>
        <div class="col-md-6">
          <label class="form-label">Category page hero image</label>
          <input type="file" name="hero_image" class="form-control" accept="image/*">
          <div class="form-text">Size: 1280 × 640 px</div>
          <?php if ($displayHero): ?>
            <img src="<?= base_url($displayHero) ?>" alt="" class="mt-2" style="height:72px;object-fit:cover;border-radius:8px">
          <?php endif; ?>
        </div>
      </div>

      <h6 class="mb-3">SEO</h6>
      <div class="mb-3">
        <label class="form-label">SEO title</label>
        <input type="text" name="seo_title" class="form-control" value="<?= htmlspecialchars($val('seo_title')) ?>">
      </div>
      <div class="mb-3">
        <label class="form-label">SEO description</label>
        <textarea name="seo_description" class="form-control" rows="3"><?= htmlspecialchars($val('seo_description')) ?></textarea>
      </div>
      <div class="mb-3">
        <label class="form-label">SEO keywords</label>
        <input type="text" name="seo_keywords" class="form-control" value="<?= htmlspecialchars($val('seo_keywords')) ?>">
      </div>
      <button type="submit" class="btn btn-store-primary">Save customization</button>
    </form>
  </div>
</div>
