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
        <div class="form-text">Recommended: 1400 × 640 px (JPG, PNG, or WebP)</div>
        <?php if ($slide && !empty($slide->image)): ?>
          <img src="<?= base_url($slide->image) ?>" alt="" class="mt-2" style="height:88px;object-fit:cover;border-radius:8px">
        <?php endif; ?>
      </div>

      <div class="mb-3">
        <label class="form-label">Kicker</label>
        <input type="text" name="kicker" class="form-control" value="<?= htmlspecialchars($val('kicker')) ?>" placeholder="Modern Living" maxlength="120">
      </div>
      <div class="mb-3">
        <label class="form-label">Title <span class="text-danger">*</span></label>
        <textarea name="title" class="form-control" rows="2" required><?= htmlspecialchars($val('title')) ?></textarea>
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
})();
</script>
