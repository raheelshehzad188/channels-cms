<?php $this->load->view('flash'); ?>
<?php
$creatives = !empty($item->creatives) ? $item->creatives : array();
$suppliers = !empty($item->suppliers) ? $item->suppliers : array();
$storeCountryId = isset($store_country_id) ? (int) $store_country_id : 0;
?>
<div class="mb-3">
  <a href="<?= $storeUrl ?>/hunting" class="btn btn-sm btn-outline-secondary">&larr; Back to hunting</a>
</div>
<div class="row">
  <div class="col-lg-4 mb-4">
    <div class="store-card">
      <div class="card-body text-center">
        <?php if (!empty($item->image)): ?>
          <img src="<?= base_url($item->image) ?>" alt="" class="img-fluid rounded mb-3" style="max-height:280px;object-fit:cover;">
        <?php endif; ?>
        <h2 class="h5"><?= htmlspecialchars($item->title) ?></h2>
        <?php if (!empty($item->notes)): ?>
          <p class="text-muted mb-0"><?= nl2br(htmlspecialchars((string) $item->notes)) ?></p>
        <?php endif; ?>
      </div>
    </div>
  </div>
  <div class="col-lg-8">
    <div class="store-card mb-4">
      <div class="card-body">
        <h3 class="h6">Creatives</h3>
        <?php if (empty($creatives)): ?>
          <p class="text-muted mb-0">No creative links.</p>
        <?php else: ?>
          <ul class="list-unstyled mb-0">
            <?php foreach ($creatives as $i => $creative): ?>
              <li class="mb-2">
                <a href="<?= htmlspecialchars($creative->link) ?>" target="_blank" rel="noopener">
                  Creative <?= $i + 1 ?> — <?= htmlspecialchars($creative->link) ?>
                </a>
              </li>
            <?php endforeach; ?>
          </ul>
        <?php endif; ?>
      </div>
    </div>
    <div class="store-card">
      <div class="card-body">
        <h3 class="h6">Country suppliers</h3>
        <?php if (empty($suppliers)): ?>
          <p class="text-muted mb-0">No supplier links.</p>
        <?php else: ?>
          <div class="table-responsive">
            <table class="table table-sm align-middle mb-0">
              <thead>
                <tr>
                  <th>Country</th>
                  <th>Supplier link</th>
                </tr>
              </thead>
              <tbody>
              <?php foreach ($suppliers as $supplier): ?>
                <?php $isStoreCountry = $storeCountryId && (int) $supplier->country_id === $storeCountryId; ?>
                <tr class="<?= $isStoreCountry ? 'table-success' : '' ?>">
                  <td>
                    <?= htmlspecialchars(($supplier->country_name ?: 'Unknown') . ($supplier->country_code ? ' (' . $supplier->country_code . ')' : '')) ?>
                    <?php if ($isStoreCountry): ?><span class="badge text-bg-success">Your country</span><?php endif; ?>
                  </td>
                  <td><a href="<?= htmlspecialchars($supplier->link) ?>" target="_blank" rel="noopener"><?= htmlspecialchars($supplier->link) ?></a></td>
                </tr>
              <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>
