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
    if ($key === 'hero_disc_small') {
        return 'UP TO';
    }
    if ($key === 'hero_disc_big') {
        return '50%';
    }
    if ($key === 'hero_disc_span') {
        return 'OFF';
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
      <?php $heroExtraOn = (int) $val('hero_extra_text', 1) === 1; ?>
      <div class="mb-3">
        <label class="form-label">Category page hero image</label>
        <input type="file" name="hero_image" class="form-control" accept="image/*">
        <div class="form-text" id="hero-image-hint" data-just-img="Just image (full banner): 1600 × 480 px. The photo covers the whole hero." data-with-text="With extra text: 1280 × 640 px. The photo sits on the right of the hero.">Just image (full banner): 1600 × 480 px. The photo covers the whole hero.</div>
        <?php if ($displayHero): ?>
          <img src="<?= base_url($displayHero) ?>" alt="" class="mt-2" style="height:72px;object-fit:cover;border-radius:8px">
        <?php endif; ?>
      </div>
      <div class="form-check mb-3">
        <input class="form-check-input" type="checkbox" name="hero_extra_text" value="1" id="extra_text" <?= $heroExtraOn ? 'checked' : '' ?>>
        <label class="form-check-label" for="extra_text">Extra text</label>
        <div class="form-text">Leave unchecked to use only the hero image. Check to add kicker, title, button, and badge.</div>
      </div>
      <div id="image-only-link" class="mb-3" <?= $heroExtraOn ? 'hidden' : '' ?>>
        <label class="form-label">URL</label>
        <input type="text" name="hero_btn_link" class="form-control" id="hero_image_link" value="<?= htmlspecialchars($val('hero_btn_link')) ?>" placeholder="shop, category/halloween, or https://example.com" <?= $heroExtraOn ? 'disabled' : '' ?>>
        <div class="form-text">When someone clicks this image, they go to this link. Leave empty for no click.</div>
      </div>
      <div id="extra-text-fields" <?= $heroExtraOn ? '' : 'hidden' ?>>
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
          <input type="text" name="hero_btn_link" class="form-control" id="hero_btn_link" value="<?= htmlspecialchars($val('hero_btn_link')) ?>" placeholder="#category-products or /shop" <?= $heroExtraOn ? '' : 'disabled' ?>>
        </div>
      </div>
      <h6 class="mt-1 mb-3">Discount badge</h6>
      <p class="text-muted small">Circle on the hero (for example UP TO / 50% / OFF). Change the middle value to 30%, 20%, or any text.</p>
      <div class="mb-3">
        <label class="form-label">Discount badge</label>
        <select name="hero_disc_on" class="form-select" style="max-width:180px">
          <option value="1" <?= (int) $val('hero_disc_on', 1) === 1 ? 'selected' : '' ?>>On</option>
          <option value="0" <?= (int) $val('hero_disc_on', 1) === 0 ? 'selected' : '' ?>>Off</option>
        </select>
      </div>
      <div class="row g-3 mb-4">
        <div class="col-md-4">
          <label class="form-label">Small text</label>
          <input type="text" name="hero_disc_small" class="form-control" value="<?= htmlspecialchars($val('hero_disc_small')) ?>" placeholder="UP TO">
        </div>
        <div class="col-md-4">
          <label class="form-label">Discount</label>
          <input type="text" name="hero_disc_big" class="form-control" value="<?= htmlspecialchars($val('hero_disc_big')) ?>" placeholder="50%">
        </div>
        <div class="col-md-4">
          <label class="form-label">Bottom text</label>
          <input type="text" name="hero_disc_span" class="form-control" value="<?= htmlspecialchars($val('hero_disc_span')) ?>" placeholder="OFF">
        </div>
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
<script>
(function () {
  var extra = document.getElementById('extra_text');
  var fields = document.getElementById('extra-text-fields');
  var imageLink = document.getElementById('image-only-link');
  var imageLinkInput = document.getElementById('hero_image_link');
  var btnLinkInput = document.getElementById('hero_btn_link');
  var hint = document.getElementById('hero-image-hint');
  function syncExtra() {
    var on = extra && extra.checked;
    if (fields) fields.hidden = !on;
    if (imageLink) imageLink.hidden = !!on;
    if (imageLinkInput) imageLinkInput.disabled = !!on;
    if (btnLinkInput) btnLinkInput.disabled = !on;
    if (hint) hint.textContent = on ? hint.getAttribute('data-with-text') : hint.getAttribute('data-just-img');
  }
  if (extra) extra.addEventListener('change', syncExtra);
  syncExtra();
})();
</script>
