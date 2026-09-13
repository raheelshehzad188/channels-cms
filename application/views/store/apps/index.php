<?php $this->load->view('flash'); ?>

<div class="row g-3">
  <?php foreach ($apps as $app): ?>
  <div class="col-md-4">
    <div class="store-card h-100">
      <div class="card-body d-flex flex-column">
        <div class="d-flex justify-content-between align-items-start mb-2">
          <h5 class="mb-0"><?= htmlspecialchars($app['app_name']); ?></h5>
          <?= $app['is_enabled'] ? '<span class="badge text-bg-success">Enabled</span>' : '<span class="badge text-bg-secondary">Disabled</span>'; ?>
        </div>
        <p class="text-muted small flex-grow-1"><?= htmlspecialchars($app['app_slug']); ?></p>
        <div class="d-flex flex-wrap gap-2">
          <?php if ($app['is_enabled']): ?>
            <a href="<?= $storeUrl; ?>/apps/toggle/<?= $app['id']; ?>/disable" class="btn btn-sm btn-outline-secondary">Disable</a>
          <?php else: ?>
            <a href="<?= $storeUrl; ?>/apps/toggle/<?= $app['id']; ?>/enable" class="btn btn-sm btn-store-primary">Enable</a>
          <?php endif; ?>
          <a href="<?= $storeUrl; ?>/apps/configure/<?= $app['id']; ?>" class="btn btn-sm btn-outline-secondary">Configure</a>
        </div>
      </div>
    </div>
  </div>
  <?php endforeach; ?>
</div>
