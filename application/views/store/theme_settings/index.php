<?php
$v = function ($key, $fallback = '') use ($values) {
    if (isset($values[$key]) && $values[$key] !== '' && $values[$key] !== null) {
        return $values[$key];
    }
    return $fallback;
};
?>
<?php $this->load->view('store/theme_settings/_tabs'); ?>
<?php $this->load->view('flash'); ?>

<?php if ($tab === 'slider'): ?>
  <div class="d-flex justify-content-between align-items-center mb-3">
    <p class="text-muted mb-0">Schedule Halloween, Christmas, and New Year slides by date. Live seasonal slides replace year-round ones automatically.</p>
    <a href="<?= $storeUrl ?>/theme-settings/slide" class="btn btn-store-primary"><i class="bi bi-plus-lg me-1"></i> Add slide</a>
  </div>
  <div class="store-card">
    <div class="card-body p-0">
      <div class="table-responsive">
        <table class="table table-hover mb-0 align-middle">
          <thead>
            <tr>
              <th width="90">Image</th>
              <th>Text</th>
              <th>Schedule</th>
              <th width="90">Order</th>
              <th width="100">Status</th>
              <th class="text-end" width="160">Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($slides as $slide): ?>
              <?php
                $image = !empty($slide->image) ? base_url($slide->image) : '';
                $today = date('Y-m-d');
                $start = (!empty($slide->starts_on) && $slide->starts_on !== '0000-00-00') ? $slide->starts_on : '';
                $end = (!empty($slide->ends_on) && $slide->ends_on !== '0000-00-00') ? $slide->ends_on : '';
                if ($start === '' && $end === '') {
                    $scheduleBadge = '<span class="badge text-bg-light text-muted">Year-round</span>';
                    $scheduleDates = 'Always';
                } elseif ($start !== '' && $start > $today) {
                    $scheduleBadge = '<span class="badge text-bg-info">Upcoming</span>';
                    $scheduleDates = htmlspecialchars($start) . ($end !== '' ? ' – ' . htmlspecialchars($end) : '');
                } elseif ($end !== '' && $end < $today) {
                    $scheduleBadge = '<span class="badge text-bg-secondary">Ended</span>';
                    $scheduleDates = htmlspecialchars($start !== '' ? $start : '…') . ' – ' . htmlspecialchars($end);
                } else {
                    $scheduleBadge = '<span class="badge text-bg-warning">Live now</span>';
                    $scheduleDates = htmlspecialchars($start !== '' ? $start : '…') . ($end !== '' ? ' – ' . htmlspecialchars($end) : '');
                }
              ?>
              <tr>
                <td>
                  <?php if ($image): ?>
                    <img src="<?= htmlspecialchars($image) ?>" alt="" width="72" height="48" style="object-fit:cover;border-radius:6px">
                  <?php else: ?>
                    <span class="text-muted">—</span>
                  <?php endif; ?>
                </td>
                <td>
                  <?php if ($slide->kicker !== ''): ?>
                    <div class="text-muted small text-uppercase"><?= htmlspecialchars($slide->kicker) ?></div>
                  <?php endif; ?>
                  <div class="fw-medium"><?= nl2br(htmlspecialchars($slide->title)) ?></div>
                  <?php if ($slide->text !== '' && $slide->text !== null): ?>
                    <div class="text-muted small"><?= htmlspecialchars($slide->text) ?></div>
                  <?php endif; ?>
                </td>
                <td>
                  <?= $scheduleBadge ?>
                  <div class="text-muted small mt-1"><?= $scheduleDates ?></div>
                </td>
                <td><?= (int) $slide->sort_order ?></td>
                <td><?= ((int) $slide->status === 1) ? '<span class="badge text-bg-success">Active</span>' : '<span class="badge text-bg-secondary">Inactive</span>' ?></td>
                <td class="text-end">
                  <a href="<?= $storeUrl ?>/theme-settings/slide/<?= (int) $slide->id ?>" class="btn btn-sm btn-outline-secondary">Edit</a>
                  <a href="<?= $storeUrl ?>/theme-settings/slide-delete/<?= (int) $slide->id ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete this hero slide?');">Delete</a>
                </td>
              </tr>
            <?php endforeach; ?>
            <?php if (empty($slides)): ?>
              <tr><td colspan="6" class="text-center text-muted py-4">No hero slides yet. Add one for the homepage slider.</td></tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
<?php else: ?>
<form method="post" enctype="multipart/form-data" action="<?= $storeUrl ?>/theme-settings/save">
  <div class="store-card mb-3">
    <div class="card-header">Branding</div>
    <div class="card-body">
      <div class="row g-3">
        <div class="col-md-6">
          <label class="form-label">Logo</label>
          <input type="file" name="logo" class="form-control" accept="image/*">
          <div class="form-text">PNG or WebP, recommended height 80 px</div>
          <?php if ($v('logo') !== ''): ?>
            <img src="<?= base_url($v('logo')) ?>" alt="" class="mt-2" style="max-height:48px">
          <?php endif; ?>
        </div>
        <div class="col-md-6">
          <label class="form-label">Favicon</label>
          <input type="file" name="favicon" class="form-control" accept="image/*,.ico">
          <div class="form-text">Square PNG or ICO, 32 × 32 or 64 × 64</div>
          <?php if ($v('favicon') !== ''): ?>
            <img src="<?= base_url($v('favicon')) ?>" alt="" class="mt-2" style="width:32px;height:32px;object-fit:contain">
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>

  <div class="store-card mb-3">
    <div class="card-header">Color scheme</div>
    <div class="card-body">
      <div class="row g-3">
        <div class="col-md-6">
          <label class="form-label">Primary color</label>
          <input type="color" name="primary_color" class="form-control form-control-color" value="<?= htmlspecialchars($v('primary_color', '#ffd814')) ?>">
        </div>
        <div class="col-md-6">
          <label class="form-label">Secondary color</label>
          <input type="color" name="secondary_color" class="form-control form-control-color" value="<?= htmlspecialchars($v('secondary_color', '#111111')) ?>">
        </div>
      </div>
    </div>
  </div>

  <div class="store-card mb-3">
    <div class="card-header">Store copy</div>
    <div class="card-body">
      <div class="mb-3">
        <label class="form-label">Top promo text</label>
        <input type="text" name="promo_text" class="form-control" value="<?= htmlspecialchars($v('promo_text')) ?>">
      </div>
      <div class="mb-3">
        <label class="form-label">Footer about</label>
        <textarea name="footer_about" class="form-control" rows="2"><?= htmlspecialchars($v('footer_about')) ?></textarea>
      </div>
      <div class="mb-0">
        <label class="form-label">Footer text</label>
        <input type="text" name="footer_text" class="form-control" value="<?= htmlspecialchars($v('footer_text')) ?>">
      </div>
    </div>
  </div>

  <div class="store-card mb-3">
    <div class="card-header">Homepage marketing banners</div>
    <div class="card-body">
      <p class="text-muted small">Two promotional tiles under featured products on the home page.</p>
      <div class="row g-4">
        <?php foreach (array(1, 2) as $n): ?>
          <div class="col-lg-6">
            <h6 class="mb-3">Banner <?= $n ?></h6>
            <div class="mb-3">
              <label class="form-label">Image</label>
              <input type="file" name="banner_<?= $n ?>_image" class="form-control" accept="image/*">
              <div class="form-text">Recommended: 800 × 420 px</div>
              <?php if ($v('banner_' . $n . '_image') !== ''): ?>
                <img src="<?= base_url($v('banner_' . $n . '_image')) ?>" alt="" class="mt-2" style="height:80px;object-fit:cover;border-radius:8px">
              <?php endif; ?>
            </div>
            <div class="mb-3">
              <label class="form-label">Kicker</label>
              <input type="text" name="banner_<?= $n ?>_kicker" class="form-control" value="<?= htmlspecialchars($v('banner_' . $n . '_kicker')) ?>">
            </div>
            <div class="mb-3">
              <label class="form-label">Title</label>
              <input type="text" name="banner_<?= $n ?>_title" class="form-control" value="<?= htmlspecialchars($v('banner_' . $n . '_title')) ?>">
            </div>
            <div class="mb-3">
              <label class="form-label">Text</label>
              <input type="text" name="banner_<?= $n ?>_text" class="form-control" value="<?= htmlspecialchars($v('banner_' . $n . '_text')) ?>">
            </div>
            <div class="row g-3">
              <div class="col-md-6">
                <label class="form-label">Button text</label>
                <input type="text" name="banner_<?= $n ?>_btn_text" class="form-control" value="<?= htmlspecialchars($v('banner_' . $n . '_btn_text')) ?>">
              </div>
              <div class="col-md-6">
                <label class="form-label">Button link</label>
                <input type="text" name="banner_<?= $n ?>_btn_link" class="form-control" value="<?= htmlspecialchars($v('banner_' . $n . '_btn_link')) ?>" placeholder="shop">
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
  </div>

  <button type="submit" class="btn btn-store-primary">Save theme settings</button>
</form>
<?php endif; ?>
