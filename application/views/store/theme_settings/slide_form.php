<?php
$this->load->view('store/theme_settings/_tabs');
$this->load->view('flash');
$val = function ($key, $fallback = '') use ($slide) {
    if ($slide && isset($slide->$key) && $slide->$key !== null) {
        return $slide->$key;
    }
    return $fallback;
};
$dateVal = function ($key) use ($val) {
    $raw = trim((string) $val($key));
    if ($raw === '' || $raw === '0000-00-00') {
        return '';
    }
    return $raw;
};
$presets = isset($season_presets) ? $season_presets : array();
?>

<div class="store-card mb-3">
  <div class="card-header d-flex justify-content-between align-items-center">
    <span><?= $slide ? 'Edit hero slide' : 'Add hero slide' ?></span>
    <a href="<?= $storeUrl ?>/theme-settings/slider" class="btn btn-sm btn-outline-secondary">Back</a>
  </div>
  <div class="card-body">
    <p class="text-muted small">Set a date range so Halloween, Christmas, and New Year slides switch automatically. Leave dates empty for year-round slides.</p>
    <form method="post" enctype="multipart/form-data" action="<?= $storeUrl ?>/theme-settings/slide<?= $slide ? '/' . (int) $slide->id : '' ?>">
      <div class="mb-3">
        <label class="form-label">Slide image <?= $slide ? '' : '<span class="text-danger">*</span>' ?></label>
        <input type="file" name="image" class="form-control" accept="image/*" <?= $slide ? '' : 'required' ?>>
        <div class="form-text" id="hero-image-hint" data-just-img="Just image (full slider): 1600 × 480 px (JPG, PNG, or WebP). The photo covers the whole banner." data-with-text="With extra text: 1400 × 640 px (JPG, PNG, or WebP). The photo sits on the right of the slider.">Just image (full slider): 1600 × 480 px (JPG, PNG, or WebP). The photo covers the whole banner.</div>
        <?php if ($slide && !empty($slide->image)): ?>
          <img src="<?= base_url($slide->image) ?>" alt="" class="mt-2" style="height:88px;object-fit:cover;border-radius:8px">
        <?php endif; ?>
      </div>

      <?php $extraOn = $slide ? ((int) $val('extra_text', 1) === 1) : false; ?>
      <div class="form-check mb-3">
        <input class="form-check-input" type="checkbox" name="extra_text" value="1" id="extra_text" <?= $extraOn ? 'checked' : '' ?>>
        <label class="form-check-label" for="extra_text">Extra text</label>
        <div class="form-text">Leave unchecked to use only the image. Check to add kicker, title, button, and badge.</div>
      </div>

      <div id="image-only-link" class="mb-3" <?= $extraOn ? 'hidden' : '' ?>>
        <label class="form-label">URL</label>
        <input type="text" name="image_link" class="form-control" value="<?= htmlspecialchars($val('image_link')) ?>" placeholder="shop, category/halloween, or https://example.com">
        <div class="form-text">When someone clicks this image, they go to this link. Leave empty for no click.</div>
      </div>

      <div id="extra-text-fields" <?= $extraOn ? '' : 'hidden' ?>>
        <div class="mb-3">
          <label class="form-label">Kicker</label>
          <input type="text" name="kicker" class="form-control" value="<?= htmlspecialchars($val('kicker')) ?>" placeholder="Modern Living" maxlength="120">
        </div>
        <div class="mb-3">
          <label class="form-label">Title <span class="text-danger">*</span></label>
          <textarea name="title" class="form-control" rows="2" <?= $extraOn ? 'required' : '' ?>><?= htmlspecialchars($val('title')) ?></textarea>
          <div class="form-text">Use a new line for a second title row.</div>
        </div>
        <div class="mb-3">
          <label class="form-label">Text</label>
          <textarea name="text" class="form-control" rows="2"><?= htmlspecialchars($val('text')) ?></textarea>
        </div>
        <div class="row g-3 mb-3">
          <div class="col-md-6">
            <label class="form-label">Button text</label>
            <input type="text" name="btn_text" class="form-control" value="<?= htmlspecialchars($val('btn_text', 'Shop Now')) ?>">
          </div>
          <div class="col-md-6">
            <label class="form-label">Button link</label>
            <input type="text" name="btn_link" class="form-control" value="<?= htmlspecialchars($val('btn_link', 'shop')) ?>" placeholder="shop or category/halloween">
          </div>
        </div>

        <h6 class="mt-2 mb-3">Look</h6>
        <div class="row g-3 mb-3">
          <div class="col-md-4">
            <label class="form-label">Overlay color</label>
            <input type="text" name="slide_bg" class="form-control" value="<?= htmlspecialchars($val('slide_bg')) ?>" placeholder="#1b1016" maxlength="7">
          </div>
          <div class="col-md-4">
            <label class="form-label">Kicker color</label>
            <input type="text" name="kicker_color" class="form-control" value="<?= htmlspecialchars($val('kicker_color')) ?>" placeholder="#ffb74d" maxlength="7">
          </div>
          <div class="col-md-4 d-flex align-items-end">
            <div class="form-check mb-2">
              <input class="form-check-input" type="checkbox" name="light_text" value="1" id="light_text" <?= (int) $val('light_text', 0) === 1 ? 'checked' : '' ?>>
              <label class="form-check-label" for="light_text">Light text (dark slides)</label>
            </div>
          </div>
        </div>

        <h6 class="mt-2 mb-3">Discount badge (optional)</h6>
        <div class="mb-3">
          <label class="form-label">Discount badge</label>
          <select name="disc_on" class="form-select" style="max-width:180px">
            <option value="1" <?= (int) $val('disc_on', 1) === 1 ? 'selected' : '' ?>>On</option>
            <option value="0" <?= (int) $val('disc_on', 1) === 0 ? 'selected' : '' ?>>Off</option>
          </select>
        </div>
        <div class="row g-3 mb-3">
          <div class="col-md-3">
            <label class="form-label">Small</label>
            <input type="text" name="disc_small" class="form-control" value="<?= htmlspecialchars($val('disc_small')) ?>" placeholder="UP TO">
          </div>
          <div class="col-md-3">
            <label class="form-label">Big</label>
            <input type="text" name="disc_big" class="form-control" value="<?= htmlspecialchars($val('disc_big')) ?>" placeholder="50%">
          </div>
          <div class="col-md-3">
            <label class="form-label">Span</label>
            <input type="text" name="disc_span" class="form-control" value="<?= htmlspecialchars($val('disc_span')) ?>" placeholder="OFF">
          </div>
          <div class="col-md-3">
            <label class="form-label">Badge color</label>
            <input type="text" name="disc_bg" class="form-control" value="<?= htmlspecialchars($val('disc_bg')) ?>" placeholder="#111111" maxlength="7">
          </div>
        </div>
      </div>

      <h6 class="mt-2 mb-3">Schedule</h6>
      <?php if (!empty($presets)): ?>
        <div class="d-flex flex-wrap gap-2 mb-3">
          <?php foreach ($presets as $preset): ?>
            <button type="button" class="btn btn-sm btn-outline-secondary js-season-preset" data-start="<?= htmlspecialchars($preset['start']) ?>" data-end="<?= htmlspecialchars($preset['end']) ?>"><?= htmlspecialchars($preset['label']) ?></button>
          <?php endforeach; ?>
          <button type="button" class="btn btn-sm btn-outline-secondary js-season-clear">Year-round</button>
        </div>
      <?php endif; ?>
      <div class="row g-3 mb-3">
        <div class="col-md-6">
          <label class="form-label">Start date</label>
          <input type="date" name="starts_on" id="starts_on" class="form-control" value="<?= htmlspecialchars($dateVal('starts_on')) ?>">
        </div>
        <div class="col-md-6">
          <label class="form-label">End date</label>
          <input type="date" name="ends_on" id="ends_on" class="form-control" value="<?= htmlspecialchars($dateVal('ends_on')) ?>">
          <div class="form-text">Home page shows this slide only between these dates. If a seasonal slide is live, year-round slides are hidden.</div>
        </div>
      </div>

      <div class="row g-3 mb-4">
        <div class="col-md-6">
          <label class="form-label">Sort order</label>
          <input type="number" name="sort_order" class="form-control" value="<?= htmlspecialchars($val('sort_order', $slide ? $slide->sort_order : 0)) ?>">
        </div>
        <div class="col-md-6">
          <label class="form-label">Status</label>
          <select name="status" class="form-select">
            <option value="1" <?= (int) $val('status', 1) === 1 ? 'selected' : '' ?>>Active</option>
            <option value="0" <?= (int) $val('status', 1) === 0 ? 'selected' : '' ?>>Inactive</option>
          </select>
        </div>
      </div>

      <button type="submit" class="btn btn-store-primary"><?= $slide ? 'Save slide' : 'Add slide' ?></button>
    </form>
  </div>
</div>
<script>
(function () {
  var start = document.getElementById('starts_on');
  var end = document.getElementById('ends_on');
  document.querySelectorAll('.js-season-preset').forEach(function (btn) {
    btn.addEventListener('click', function () {
      start.value = btn.getAttribute('data-start') || '';
      end.value = btn.getAttribute('data-end') || '';
    });
  });
  var clearBtn = document.querySelector('.js-season-clear');
  if (clearBtn) {
    clearBtn.addEventListener('click', function () {
      start.value = '';
      end.value = '';
    });
  }
  var extra = document.getElementById('extra_text');
  var fields = document.getElementById('extra-text-fields');
  var imageLink = document.getElementById('image-only-link');
  var hint = document.getElementById('hero-image-hint');
  var title = document.querySelector('[name="title"]');
  function syncExtra() {
    var on = extra && extra.checked;
    if (fields) fields.hidden = !on;
    if (imageLink) imageLink.hidden = !!on;
    if (hint) hint.textContent = on ? hint.getAttribute('data-with-text') : hint.getAttribute('data-just-img');
    if (title) title.required = !!on;
  }
  if (extra) extra.addEventListener('change', syncExtra);
  syncExtra();
})();
</script>
