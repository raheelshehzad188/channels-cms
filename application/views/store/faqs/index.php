<?php $this->load->view('flash'); ?>
<?php
$filters = isset($filters) ? $filters : array();
$q = isset($filters['q']) ? $filters['q'] : '';
$productId = isset($filters['product_id']) ? (int) $filters['product_id'] : 0;
$status = isset($filters['status']) ? $filters['status'] : '';
?>
<div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
  <p class="text-muted mb-0">FAQs appear on the new product detail page for the selected product.</p>
  <a href="<?= $storeUrl ?>/faqs/form" class="btn btn-store-primary"><i class="bi bi-plus-lg me-1"></i> Add FAQ</a>
</div>

<form method="get" action="<?= $storeUrl ?>/faqs" class="store-card mb-3">
  <div class="card-body">
    <div class="row g-2 align-items-end">
      <div class="col-md-4">
        <label class="form-label small text-muted mb-1" for="faqQ">Search</label>
        <input type="text" name="q" id="faqQ" class="form-control" value="<?= htmlspecialchars($q) ?>" placeholder="Question or answer…">
      </div>
      <div class="col-md-4">
        <label class="form-label small text-muted mb-1" for="faqProduct">Product</label>
        <select name="product_id" id="faqProduct" class="form-select">
          <option value="0">All products</option>
          <?php foreach (!empty($products) ? $products : array() as $product): ?>
            <option value="<?= (int) $product->id ?>" <?= $productId === (int) $product->id ? 'selected' : '' ?>><?= htmlspecialchars($product->name) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-md-2">
        <label class="form-label small text-muted mb-1" for="faqStatus">Status</label>
        <select name="status" id="faqStatus" class="form-select">
          <option value="">All</option>
          <option value="1" <?= (string) $status === '1' ? 'selected' : '' ?>>Enabled</option>
          <option value="0" <?= (string) $status === '0' ? 'selected' : '' ?>>Disabled</option>
        </select>
      </div>
      <div class="col-md-2 d-flex gap-2">
        <button type="submit" class="btn btn-store-primary flex-grow-1">Filter</button>
        <a href="<?= $storeUrl ?>/faqs" class="btn btn-outline-secondary">Reset</a>
      </div>
    </div>
  </div>
</form>

<div class="store-card">
  <div class="card-body p-0">
    <div class="table-responsive">
      <table class="table table-hover mb-0 align-middle">
        <thead>
          <tr>
            <th>Question</th>
            <th>Product</th>
            <th>Status</th>
            <th>Sort</th>
            <th class="text-end">Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach (!empty($items) ? $items : array() as $item): ?>
          <tr>
            <td>
              <div class="fw-medium"><?= htmlspecialchars($item->question) ?></div>
              <?php $snippet = strip_tags((string) $item->answer); if (strlen($snippet) > 90) { $snippet = substr($snippet, 0, 87) . '…'; } ?>
              <div class="small text-muted"><?= htmlspecialchars($snippet) ?></div>
            </td>
            <td><?= htmlspecialchars($item->product_name ? $item->product_name : '—') ?></td>
            <td><?= ((int) $item->status === 1) ? '<span class="badge text-bg-success">Enabled</span>' : '<span class="badge text-bg-secondary">Disabled</span>' ?></td>
            <td><?= (int) $item->sort_order ?></td>
            <td class="text-end text-nowrap">
              <a href="<?= $storeUrl ?>/faqs/form/<?= (int) $item->id ?>" class="btn btn-sm btn-outline-secondary">Edit</a>
              <a href="<?= $storeUrl ?>/faqs/toggle/<?= (int) $item->id ?>" class="btn btn-sm btn-outline-secondary"><?= ((int) $item->status === 1) ? 'Disable' : 'Enable' ?></a>
              <a href="<?= $storeUrl ?>/faqs/delete/<?= (int) $item->id ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete this FAQ?');">Delete</a>
            </td>
          </tr>
          <?php endforeach; ?>
          <?php if (empty($items)): ?>
          <tr><td colspan="5" class="text-center text-muted py-4">No FAQs yet.</td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
