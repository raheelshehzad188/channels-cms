<?php $this->load->view('flash'); ?>
<?php
$filterQ = isset($q) ? $q : '';
$filterCountryId = isset($country_id) ? (int) $country_id : 0;
$itemCount = is_array($items) ? count($items) : 0;
?>
<p class="text-muted mb-3">Shared hunting products with creatives and country supplier links. Super admin and ecommerce users add these; every store can view them.</p>

<form method="get" action="<?= $storeUrl ?>/hunting" class="store-card store-product-filters mb-4">
  <div class="card-body">
    <div class="row g-2 align-items-end">
      <div class="col-lg-5">
        <label class="form-label small text-muted mb-1" for="huntingQ">Search</label>
        <input type="text" name="q" id="huntingQ" class="form-control" value="<?= htmlspecialchars($filterQ) ?>" placeholder="Search title…">
      </div>
      <div class="col-md-4 col-lg-4">
        <label class="form-label small text-muted mb-1" for="huntingCountry">Supplier country</label>
        <select name="country_id" id="huntingCountry" class="form-select">
          <option value="0">All countries</option>
          <?php foreach (!empty($countries) ? $countries : array() as $country): ?>
            <option value="<?= (int) $country->id ?>" <?= $filterCountryId === (int) $country->id ? 'selected' : '' ?>><?= htmlspecialchars($country->name) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-md-4 col-lg-3 d-flex gap-2">
        <button type="submit" class="btn btn-store-primary flex-grow-1">Filter</button>
        <?php if ($filterQ !== '' || $filterCountryId): ?>
          <a href="<?= $storeUrl ?>/hunting" class="btn btn-outline-secondary">Reset</a>
        <?php endif; ?>
      </div>
    </div>
  </div>
</form>

<?php if ($itemCount): ?>
  <p class="small text-muted mb-3"><?= (int) $itemCount ?> hunting product<?= $itemCount === 1 ? '' : 's' ?>.</p>
<?php endif; ?>

<?php if (empty($items)): ?>
  <div class="store-card"><div class="card-body text-muted">No hunting products yet.</div></div>
<?php else: ?>
<div class="store-product-grid">
  <div class="row">
    <?php foreach ($items as $item): ?>
      <?php $image = !empty($item->image) ? base_url($item->image) : ''; ?>
      <div class="col-md-3 mb-4">
        <div class="ibox">
          <div class="ibox-content product-box">
            <a href="<?= $storeUrl ?>/hunting/view/<?= (int) $item->id ?>" class="product-imitation">
              <?php if ($image): ?>
                <img src="<?= $image ?>" alt="" style="width:100%;height:180px;object-fit:cover;">
              <?php else: ?>
                <span class="text-muted">No image</span>
              <?php endif; ?>
            </a>
            <div class="product-desc">
              <a href="<?= $storeUrl ?>/hunting/view/<?= (int) $item->id ?>" class="product-name"><?= htmlspecialchars($item->title) ?></a>
              <div class="small text-muted mt-1">
                <?= (int) $item->creative_count ?> creative<?= ((int) $item->creative_count === 1) ? '' : 's' ?>
                · <?= (int) $item->supplier_count ?> supplier<?= ((int) $item->supplier_count === 1) ? '' : 's' ?>
              </div>
              <a href="<?= $storeUrl ?>/hunting/view/<?= (int) $item->id ?>" class="btn btn-sm btn-store-primary mt-2">View links</a>
            </div>
          </div>
        </div>
      </div>
    <?php endforeach; ?>
  </div>
</div>
<?php endif; ?>
