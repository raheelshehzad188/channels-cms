<?php
$cfg = isset($config) ? $config : array();
$logs = isset($logs) ? $logs : array();
$connected = !empty($cfg['connected']);
$enabled = !empty($cfg['enabled']);
$capiEnabled = !empty($cfg['capi_enabled']);
$capiConnected = !empty($cfg['capi_connected']);
$pixel = isset($cfg['pixel_id']) ? $cfg['pixel_id'] : '';
$adAccountId = isset($cfg['ad_account_id']) ? $cfg['ad_account_id'] : '';
$tokenSet = !empty($cfg['token_set']);
$tokenMask = isset($cfg['token_mask']) && $cfg['token_mask'] !== '' ? $cfg['token_mask'] : '••••••••••••';
$testCode = isset($cfg['test_event_code']) ? $cfg['test_event_code'] : '';
$lastError = isset($cfg['last_error']) ? $cfg['last_error'] : '';
$lastEventAt = isset($cfg['last_event_at']) ? $cfg['last_event_at'] : '';
$lastEventStatus = isset($cfg['last_event_status']) ? $cfg['last_event_status'] : '';
?>
<?php $this->load->view('flash'); ?>

<div class="store-card mb-4">
  <div class="card-header d-flex justify-content-between align-items-center">
    <span>Meta Integration</span>
    <span class="badge <?= $connected ? 'text-bg-success' : 'text-bg-secondary' ?>"><?= $connected ? 'Connected' : 'Not Connected' ?></span>
  </div>
  <div class="card-body">
    <p class="text-muted">These values belong to this store only. The access token is encrypted and is not shown after you save it.</p>
    <form method="post" action="<?= $storeUrl ?>/settings/meta/save">
      <div class="mb-3">
        <label class="form-label" for="metaPixelId">Meta Pixel / Dataset ID</label>
        <input type="text" name="pixel_id" id="metaPixelId" class="form-control" inputmode="numeric" autocomplete="off" value="<?= htmlspecialchars($pixel) ?>">
      </div>
      <div class="mb-3">
        <label class="form-label" for="metaAdAccountId">Meta Ad Account ID</label>
        <input type="text" name="ad_account_id" id="metaAdAccountId" class="form-control" inputmode="numeric" autocomplete="off" value="<?= htmlspecialchars($adAccountId) ?>">
      </div>
      <div class="mb-3">
        <label class="form-label" for="metaCapiToken">Events Manager Access Token</label>
        <input type="password" name="capi_token" id="metaCapiToken" class="form-control" autocomplete="new-password" value="" placeholder="<?= $tokenSet ? htmlspecialchars($tokenMask) : '' ?>">
        <div class="form-text"><?= $tokenSet ? 'A token is saved for this store (' . htmlspecialchars($tokenMask) . '). Leave this blank to keep it, or paste a new token to replace it.' : 'Paste the token from Events Manager → your dataset → Settings. It is stored encrypted for this store only.' ?></div>
      </div>
      <div class="form-check form-switch mb-3">
        <input class="form-check-input" type="checkbox" name="pixel_enabled" value="1" id="metaPixelEnabled" <?= $enabled ? 'checked' : '' ?>>
        <label class="form-check-label" for="metaPixelEnabled">Enable Meta Pixel</label>
      </div>
      <div class="form-check form-switch mb-3">
        <input class="form-check-input" type="checkbox" name="capi_enabled" value="1" id="metaCapiEnabled" <?= $capiEnabled ? 'checked' : '' ?>>
        <label class="form-check-label" for="metaCapiEnabled">Enable Conversions API</label>
      </div>
      <div class="mb-3">
        <label class="form-label" for="metaTestCode">Test event code <span class="text-muted">(optional)</span></label>
        <input type="text" name="test_event_code" id="metaTestCode" class="form-control" value="<?= htmlspecialchars($testCode) ?>" autocomplete="off" placeholder="TEST12345">
        <div class="form-text">Only while Events Manager → Test events is open. Clear it before going live.</div>
      </div>
      <button type="submit" class="btn btn-store-primary">Save Meta Integration</button>
    </form>
    <div class="d-flex flex-wrap gap-2 mt-3">
      <form method="post" action="<?= $storeUrl ?>/settings/meta/test">
        <button type="submit" class="btn btn-outline-secondary">Test Meta Connection</button>
      </form>
      <?php if ($tokenSet): ?>
        <form method="post" action="<?= $storeUrl ?>/settings/meta/disconnect-capi" onsubmit="return confirm('Remove the saved Conversions API token for this store?');">
          <button type="submit" class="btn btn-outline-danger">Remove token</button>
        </form>
      <?php endif; ?>
    </div>
    <ul class="list-unstyled small mt-4 mb-0">
      <li class="<?= $enabled && $pixel !== '' ? 'text-success' : 'text-muted' ?>">● Meta Pixel <?= $enabled && $pixel !== '' ? 'enabled' : 'off' ?></li>
      <li class="<?= $capiConnected ? 'text-success' : 'text-muted' ?>">● Conversions API <?= $capiConnected ? 'enabled' : 'off' ?></li>
      <li>● Last event sent<?= $lastEventAt !== '' ? ': ' . htmlspecialchars($lastEventAt) : ': —' ?></li>
      <li class="<?= $lastEventStatus === 'sent' ? 'text-success' : ($lastEventStatus === 'failed' ? 'text-danger' : '') ?>">● Last event status<?= $lastEventStatus !== '' ? ': ' . htmlspecialchars($lastEventStatus) : ': —' ?></li>
      <li class="<?= $lastError !== '' ? 'text-danger' : '' ?>">● Last error<?= $lastError !== '' ? ': ' . htmlspecialchars($lastError) : ': —' ?></li>
    </ul>
  </div>
</div>

<div class="store-card">
  <div class="card-header">Event log</div>
  <div class="card-body p-0">
    <div class="table-responsive">
      <table class="table table-hover mb-0 align-middle">
        <thead>
          <tr>
            <th>Time</th>
            <th>Event</th>
            <th>Status</th>
            <th>HTTP</th>
            <th>Detail</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($logs)): ?>
            <tr><td colspan="5" class="text-center text-muted py-4">No server events yet.</td></tr>
          <?php endif; ?>
          <?php foreach ($logs as $log): ?>
            <tr>
              <td class="text-nowrap small"><?= htmlspecialchars($log->created_at) ?></td>
              <td>
                <div class="fw-medium"><?= htmlspecialchars($log->event_name) ?></div>
                <div class="text-muted small"><?= htmlspecialchars($log->event_id) ?></div>
              </td>
              <td>
                <?php if ($log->status === 'sent'): ?>
                  <span class="badge text-bg-success">Sent</span>
                <?php else: ?>
                  <span class="badge text-bg-danger">Failed</span>
                <?php endif; ?>
              </td>
              <td><?= (int) $log->http_code ?></td>
              <td class="small"><?= htmlspecialchars($log->error_message !== '' ? $log->error_message : '—') ?></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
