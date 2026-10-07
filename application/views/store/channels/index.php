<?php $this->load->view('flash'); ?>

<p class="text-muted mb-4">Connect Facebook / Instagram and TikTok with one click. Each store links its own account — no API keys or tokens to paste. For Pixel + Conversions API only, use <a href="<?= $storeUrl; ?>/settings/meta">Settings → Meta Integration</a>.</p>

<div class="row g-4">
  <div class="col-lg-6">
    <div class="store-card channel-card h-100">
      <div class="card-body">
        <div class="d-flex align-items-start justify-content-between gap-3 mb-3">
          <div class="d-flex align-items-center gap-3">
            <span class="channel-logo meta"><i class="bi bi-meta"></i></span>
            <div>
              <h5 class="mb-0">Meta</h5>
              <div class="text-muted small">Facebook Shop, Instagram Shopping, Pixel &amp; catalog ads</div>
            </div>
          </div>
          <?php if ($meta && $meta->status === 'connected'): ?>
            <span class="badge text-bg-success">Connected</span>
          <?php elseif ($meta && $meta->status === 'needs_assets'): ?>
            <span class="badge text-bg-warning">Choose assets</span>
          <?php elseif ($meta && $meta->status === 'token_expired'): ?>
            <span class="badge text-bg-danger">Reconnect needed</span>
          <?php else: ?>
            <span class="badge text-bg-secondary">Not connected</span>
          <?php endif; ?>
        </div>

        <?php if (!$meta_ready): ?>
          <div class="alert alert-warning mb-0">Platform Meta app is not set yet. Ask Super Admin to add it under Social Channels.</div>
        <?php elseif (!$meta || $meta->status === 'disconnected' || empty($meta->has_token)): ?>
          <p class="small text-muted">You will be sent to Facebook to approve access. Come back here to pick your Page, catalog and pixel.</p>
          <a class="btn btn-store-primary" href="<?= $storeUrl; ?>/channels/meta/connect"><i class="bi bi-box-arrow-in-right me-1"></i> Connect Meta</a>
        <?php else: ?>
          <?php if ($meta->page_name || $meta->catalog_name || $meta->pixel_name): ?>
            <ul class="list-unstyled small mb-3">
              <?php if ($meta->page_name): ?><li><strong>Page:</strong> <?= htmlspecialchars($meta->page_name); ?></li><?php endif; ?>
              <?php if ($meta->instagram_username): ?><li><strong>Instagram:</strong> @<?= htmlspecialchars($meta->instagram_username); ?></li><?php endif; ?>
              <?php if ($meta->catalog_name): ?><li><strong>Catalog:</strong> <?= htmlspecialchars($meta->catalog_name); ?></li><?php endif; ?>
              <?php if ($meta->pixel_name || $meta->pixel_id): ?><li><strong>Pixel:</strong> <?= htmlspecialchars($meta->pixel_name ?: $meta->pixel_id); ?></li><?php endif; ?>
              <?php if ($meta->last_sync_at): ?><li><strong>Last sync:</strong> <?= htmlspecialchars($meta->last_sync_at); ?></li><?php endif; ?>
            </ul>
          <?php endif; ?>

          <?php if ($meta && $meta->status === 'token_expired'): ?>
            <div class="alert alert-danger small">The Meta login expired. Click Reconnect and approve access again.</div>
          <?php endif; ?>
            <?php if (empty($meta_assets['businesses'])): ?>
              <div class="alert alert-warning small">No Meta Business Portfolio was returned. The Facebook user must have a Business Manager that owns the Page.</div>
            <?php endif; ?>
            <?php if (empty($meta_assets['pages'])): ?>
              <div class="alert alert-warning small">No Facebook Pages were returned. Make sure this Facebook user is admin of the Page.</div>
            <?php endif; ?>
            <form method="post" action="<?= $storeUrl; ?>/channels/meta/save" class="mb-3">
            <div class="mb-2">
              <label class="form-label small">Business portfolio</label>
              <select name="business_id" class="form-select form-select-sm">
                <?php foreach ($meta_assets['businesses'] as $biz): ?>
                  <option value="<?= htmlspecialchars($biz['id']); ?>" <?= ($meta->business_id === (string) $biz['id']) ? 'selected' : ''; ?>><?= htmlspecialchars($biz['name']); ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="mb-2">
              <label class="form-label small">Facebook Page</label>
              <select name="page_id" class="form-select form-select-sm" required>
                <option value="">Select page</option>
                <?php foreach ($meta_assets['pages'] as $page): ?>
                  <option value="<?= htmlspecialchars($page['id']); ?>" <?= ($meta->page_id === (string) $page['id']) ? 'selected' : ''; ?>><?= htmlspecialchars($page['name']); ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="mb-2">
              <label class="form-label small">Product catalog</label>
              <select name="catalog_id" class="form-select form-select-sm" required>
                <option value="">Select catalog</option>
                <?php foreach ($meta_assets['catalogs'] as $cat): ?>
                  <option value="<?= htmlspecialchars($cat['id']); ?>" <?= ($meta->catalog_id === (string) $cat['id']) ? 'selected' : ''; ?>><?= htmlspecialchars($cat['name']); ?></option>
                <?php endforeach; ?>
                <option value="__create__">+ Create a new catalog</option>
              </select>
            </div>
            <div class="mb-2">
              <label class="form-label small">Meta pixel</label>
              <select name="pixel_id" class="form-select form-select-sm" required>
                <option value="">Select pixel</option>
                <?php foreach ($meta_assets['pixels'] as $pixel): ?>
                  <option value="<?= htmlspecialchars($pixel['id']); ?>" <?= ($meta->pixel_id === (string) $pixel['id']) ? 'selected' : ''; ?>><?= htmlspecialchars($pixel['name'] . ' (' . $pixel['id'] . ')'); ?></option>
                <?php endforeach; ?>
                <option value="__create__">+ Create a new pixel</option>
              </select>
            </div>
            <?php if (!empty($meta_assets['ad_accounts'])): ?>
            <div class="mb-3">
              <label class="form-label small">Ad account (optional)</label>
              <select name="ad_account_id" class="form-select form-select-sm">
                <option value="">None</option>
                <?php foreach ($meta_assets['ad_accounts'] as $ad): ?>
                  <?php $adId = isset($ad['id']) ? $ad['id'] : ''; ?>
                  <option value="<?= htmlspecialchars($adId); ?>" <?= ($meta->ad_account_id === (string) $adId) ? 'selected' : ''; ?>><?= htmlspecialchars(isset($ad['name']) ? $ad['name'] : $adId); ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <?php endif; ?>
            <button type="submit" class="btn btn-sm btn-store-primary">Save Meta assets</button>
          </form>

          <?php if (!empty($meta->last_error)): ?>
            <div class="alert alert-danger py-2 small"><?= htmlspecialchars($meta->last_error); ?></div>
          <?php endif; ?>

          <div class="d-flex flex-wrap gap-2">
            <form method="post" action="<?= $storeUrl; ?>/channels/meta/sync">
              <button type="submit" class="btn btn-sm btn-outline-secondary">Sync products now</button>
            </form>
            <a class="btn btn-sm btn-outline-secondary" href="<?= $storeUrl; ?>/channels/meta/connect">Reconnect</a>
            <form method="post" action="<?= $storeUrl; ?>/channels/meta/disconnect" onsubmit="return confirm('Disconnect Meta from this store?');">
              <button type="submit" class="btn btn-sm btn-outline-danger">Disconnect</button>
            </form>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <div class="col-lg-6">
    <div class="store-card channel-card h-100">
      <div class="card-body">
        <div class="d-flex align-items-start justify-content-between gap-3 mb-3">
          <div class="d-flex align-items-center gap-3">
            <span class="channel-logo tiktok"><i class="bi bi-tiktok"></i></span>
            <div>
              <h5 class="mb-0">TikTok</h5>
              <div class="text-muted small">TikTok catalog, pixel and CompletePayment events</div>
            </div>
          </div>
          <?php if ($tiktok && $tiktok->status === 'connected'): ?>
            <span class="badge text-bg-success">Connected</span>
          <?php elseif ($tiktok && $tiktok->status === 'needs_assets'): ?>
            <span class="badge text-bg-warning">Choose assets</span>
          <?php elseif ($tiktok && $tiktok->status === 'token_expired'): ?>
            <span class="badge text-bg-danger">Reconnect needed</span>
          <?php else: ?>
            <span class="badge text-bg-secondary">Not connected</span>
          <?php endif; ?>
        </div>

        <?php if (!$tiktok_ready): ?>
          <div class="alert alert-warning mb-0">Platform TikTok app is not set yet. Ask Super Admin to add it under Social Channels.</div>
        <?php elseif (!$tiktok || $tiktok->status === 'disconnected' || empty($tiktok->has_token)): ?>
          <p class="small text-muted">You will be sent to TikTok to approve access. Come back here to pick advertiser, catalog and pixel.</p>
          <a class="btn btn-dark" href="<?= $storeUrl; ?>/channels/tiktok/connect"><i class="bi bi-box-arrow-in-right me-1"></i> Connect TikTok</a>
        <?php else: ?>
          <?php if ($tiktok && $tiktok->status === 'token_expired'): ?>
            <div class="alert alert-danger small">The TikTok login expired. Click Reconnect and approve access again.</div>
          <?php endif; ?>
          <?php if ($tiktok->advertiser_name || $tiktok->catalog_name || $tiktok->pixel_id): ?>
            <ul class="list-unstyled small mb-3">
              <?php if ($tiktok->advertiser_name): ?><li><strong>Advertiser:</strong> <?= htmlspecialchars($tiktok->advertiser_name); ?></li><?php endif; ?>
              <?php if ($tiktok->catalog_name): ?><li><strong>Catalog:</strong> <?= htmlspecialchars($tiktok->catalog_name); ?></li><?php endif; ?>
              <?php if ($tiktok->pixel_id): ?><li><strong>Pixel:</strong> <?= htmlspecialchars($tiktok->pixel_name ?: $tiktok->pixel_id); ?></li><?php endif; ?>
              <?php if ($tiktok->last_sync_at): ?><li><strong>Last sync:</strong> <?= htmlspecialchars($tiktok->last_sync_at); ?></li><?php endif; ?>
            </ul>
          <?php endif; ?>

          <form method="post" action="<?= $storeUrl; ?>/channels/tiktok/save" class="mb-3">
            <div class="mb-2">
              <label class="form-label small">Advertiser account</label>
              <select name="advertiser_id" class="form-select form-select-sm" required>
                <option value="">Select advertiser</option>
                <?php foreach ($tiktok_assets['advertisers'] as $adv): ?>
                  <?php $aid = isset($adv['advertiser_id']) ? (string) $adv['advertiser_id'] : ''; ?>
                  <option value="<?= htmlspecialchars($aid); ?>" <?= ($tiktok->advertiser_id === $aid) ? 'selected' : ''; ?>><?= htmlspecialchars(isset($adv['advertiser_name']) ? $adv['advertiser_name'] : $aid); ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="mb-2">
              <label class="form-label small">Catalog</label>
              <select name="catalog_id" class="form-select form-select-sm">
                <option value="">Select catalog (optional)</option>
                <?php foreach ($tiktok_assets['catalogs'] as $cat): ?>
                  <?php $cid = isset($cat['catalog_id']) ? (string) $cat['catalog_id'] : (isset($cat['id']) ? (string) $cat['id'] : ''); ?>
                  <?php $cname = isset($cat['catalog_name']) ? $cat['catalog_name'] : (isset($cat['name']) ? $cat['name'] : $cid); ?>
                  <option value="<?= htmlspecialchars($cid); ?>" <?= ($tiktok->catalog_id === $cid) ? 'selected' : ''; ?>><?= htmlspecialchars($cname); ?></option>
                <?php endforeach; ?>
                <option value="__create__">+ Create a new catalog</option>
              </select>
            </div>
            <div class="mb-3">
              <label class="form-label small">Pixel</label>
              <select name="pixel_id" class="form-select form-select-sm" required>
                <option value="">Select pixel</option>
                <?php foreach ($tiktok_assets['pixels'] as $pixel): ?>
                  <?php $pid = isset($pixel['pixel_code']) ? (string) $pixel['pixel_code'] : (isset($pixel['pixel_id']) ? (string) $pixel['pixel_id'] : (isset($pixel['id']) ? (string) $pixel['id'] : '')); ?>
                  <?php $pname = isset($pixel['pixel_name']) ? $pixel['pixel_name'] : (isset($pixel['name']) ? $pixel['name'] : $pid); ?>
                  <option value="<?= htmlspecialchars($pid); ?>" <?= ($tiktok->pixel_id === $pid) ? 'selected' : ''; ?>><?= htmlspecialchars($pname); ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <button type="submit" class="btn btn-sm btn-dark">Save TikTok assets</button>
          </form>

          <?php if (!empty($tiktok->last_error)): ?>
            <div class="alert alert-danger py-2 small"><?= htmlspecialchars($tiktok->last_error); ?></div>
          <?php endif; ?>

          <div class="d-flex flex-wrap gap-2">
            <form method="post" action="<?= $storeUrl; ?>/channels/tiktok/sync">
              <button type="submit" class="btn btn-sm btn-outline-secondary">Sync products now</button>
            </form>
            <a class="btn btn-sm btn-outline-secondary" href="<?= $storeUrl; ?>/channels/tiktok/connect">Reconnect</a>
            <form method="post" action="<?= $storeUrl; ?>/channels/tiktok/disconnect" onsubmit="return confirm('Disconnect TikTok from this store?');">
              <button type="submit" class="btn btn-sm btn-outline-danger">Disconnect</button>
            </form>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>
