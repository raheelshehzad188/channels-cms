<?php $this->load->view('flash'); ?>

<div class="row g-3">
  <?php foreach ($themes as $theme): ?>
  <div class="col-md-4">
    <div class="store-card h-100">
      <div class="card-body">
        <div class="rounded mb-3 chart-placeholder" style="height:140px">
          <?= htmlspecialchars($theme['theme_name']); ?>
        </div>
        <h5 class="mb-1"><?= htmlspecialchars($theme['theme_name']); ?></h5>
        <p class="text-muted small mb-3">v<?= htmlspecialchars($theme['version']); ?>
          <?php if ($theme['is_active']): ?><span class="badge text-bg-success ms-1">Active</span><?php endif; ?>
        </p>
        <div class="d-flex flex-wrap gap-2">
          <?php if (!$theme['is_active']): ?>
            <a href="<?= $storeUrl; ?>/themes/activate/<?= $theme['id']; ?>" class="btn btn-sm btn-store-primary">Activate</a>
          <?php else: ?>
            <a href="<?= $storeUrl; ?>/themes/deactivate/<?= $theme['id']; ?>" class="btn btn-sm btn-outline-secondary">Deactivate</a>
          <?php endif; ?>
          <a href="<?= $storeUrl; ?>/themes/preview/<?= $theme['id']; ?>" class="btn btn-sm btn-outline-secondary">Preview</a>
        </div>
      </div>
    </div>
  </div>
  <?php endforeach; ?>
</div>
