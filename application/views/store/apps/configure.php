<?php $this->load->view('flash'); ?>

<div class="store-card" style="max-width:640px">
  <div class="card-header">Configure <?= htmlspecialchars($app->app_name); ?></div>
  <div class="card-body">
    <form method="post">
      <div class="mb-3">
        <label class="form-label">API Key</label>
        <input type="text" name="api_key" class="form-control" value="<?= htmlspecialchars($config['api_key'] ?? ''); ?>">
      </div>
      <div class="mb-3">
        <label class="form-label">Webhook URL</label>
        <input type="url" name="webhook_url" class="form-control" value="<?= htmlspecialchars($config['webhook_url'] ?? ''); ?>">
      </div>
      <div class="mb-3">
        <label class="form-label">Notes</label>
        <textarea name="notes" class="form-control" rows="3"><?= htmlspecialchars($config['notes'] ?? ''); ?></textarea>
      </div>
      <div class="d-flex gap-2">
        <button type="submit" class="btn btn-store-primary">Save Configuration</button>
        <a href="<?= $storeUrl; ?>/apps" class="btn btn-outline-secondary">Back</a>
      </div>
    </form>
  </div>
</div>
