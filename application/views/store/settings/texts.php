<?php $this->load->view('flash'); ?>

<div class="store-card mb-3">
  <div class="card-body d-flex justify-content-between align-items-start gap-3 flex-wrap">
    <div>
      <div class="fw-medium">Storefront language</div>
      <p class="text-muted small mb-0">These labels appear on the public store. Empty fields fall back to the selected language. Sweden stores default to Swedish.</p>
    </div>
    <form method="post" class="d-flex gap-2">
      <button type="submit" name="seed_swedish" value="1" class="btn btn-outline-secondary" onclick="return confirm('Load Swedish defaults for all labels? Existing edits will be replaced.');">Load Swedish defaults</button>
    </form>
  </div>
</div>

<form method="post" id="storefront-texts-form">
  <div class="store-card mb-3">
    <div class="card-header">Language</div>
    <div class="card-body">
      <label class="form-label">Storefront language</label>
      <select name="ui_locale" class="form-select" style="max-width:280px">
        <option value="sv" <?= $ui_locale === 'sv' ? 'selected' : '' ?>>Svenska (Swedish)</option>
        <option value="en" <?= $ui_locale === 'en' ? 'selected' : '' ?>>English</option>
      </select>
    </div>
  </div>

  <div class="d-flex justify-content-end gap-2 mb-3">
    <button type="button" class="btn btn-sm btn-outline-secondary" id="texts-expand-all">Expand all</button>
    <button type="button" class="btn btn-sm btn-outline-secondary" id="texts-collapse-all">Collapse all</button>
  </div>

  <?php $groupIndex = 0; foreach ($ui_groups as $group => $items): $groupIndex++; ?>
    <details class="store-card mb-3 store-text-group" <?= $groupIndex === 1 ? 'open' : '' ?>>
      <summary class="card-header store-text-group__head">
        <span><?= htmlspecialchars($group) ?></span>
        <span class="text-muted small fw-normal"><?= count($items) ?> labels</span>
        <i class="bi bi-chevron-right store-text-group__caret"></i>
      </summary>
      <div class="card-body">
        <div class="row g-3">
          <?php foreach ($items as $key => $meta): ?>
            <?php
              $label = isset($meta['l']) ? $meta['l'] : $key;
              $english = isset($meta['d']) ? $meta['d'] : '';
              $value = store_ui($key);
            ?>
            <div class="col-md-6">
              <label class="form-label"><?= htmlspecialchars($label) ?></label>
              <?php if (strpos($english, "\n") !== false || strlen($english) > 80): ?>
                <textarea name="ui[<?= htmlspecialchars($key) ?>]" class="form-control" rows="2"><?= htmlspecialchars($value) ?></textarea>
              <?php else: ?>
                <input type="text" name="ui[<?= htmlspecialchars($key) ?>]" class="form-control" value="<?= htmlspecialchars($value) ?>">
              <?php endif; ?>
              <div class="form-text"><?= htmlspecialchars($key) ?> · EN: <?= htmlspecialchars($english) ?></div>
            </div>
          <?php endforeach; ?>
        </div>
      </div>
    </details>
  <?php endforeach; ?>

  <div class="d-flex gap-2 mb-5">
    <button type="submit" class="btn btn-store-primary">Save storefront texts</button>
    <a class="btn btn-outline-secondary" href="<?= $storeUrl ?>/settings">Back to settings</a>
  </div>
</form>
<script>
(function () {
  var groups = document.querySelectorAll('.store-text-group');
  var expand = document.getElementById('texts-expand-all');
  var collapse = document.getElementById('texts-collapse-all');
  if (expand) expand.addEventListener('click', function () {
    groups.forEach(function (g) { g.open = true; });
  });
  if (collapse) collapse.addEventListener('click', function () {
    groups.forEach(function (g) { g.open = false; });
  });
})();
</script>
