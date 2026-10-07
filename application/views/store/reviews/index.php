<?php $this->load->view('flash'); ?>
<?php
$filters = isset($filters) ? $filters : array();
$q = isset($filters['q']) ? $filters['q'] : '';
$productId = isset($filters['product_id']) ? (int) $filters['product_id'] : 0;
$rating = isset($filters['rating']) ? (int) $filters['rating'] : 0;
$status = isset($filters['status']) ? $filters['status'] : '';
$approval = isset($filters['approval_status']) ? $filters['approval_status'] : '';
?>
<div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
  <p class="text-muted mb-0">Manage customer and store-created reviews for this store only.</p>
  <a href="<?= $storeUrl ?>/reviews/form" class="btn btn-store-primary"><i class="bi bi-plus-lg me-1"></i> Add Review</a>
</div>

<form method="get" action="<?= $storeUrl ?>/reviews" class="store-card mb-3">
  <div class="card-body">
    <div class="row g-2 align-items-end">
      <div class="col-md-3">
        <label class="form-label small text-muted mb-1" for="revQ">Search</label>
        <input type="text" name="q" id="revQ" class="form-control" value="<?= htmlspecialchars($q) ?>" placeholder="Name, title, content…">
      </div>
      <div class="col-md-3">
        <label class="form-label small text-muted mb-1" for="revProduct">Product</label>
        <select name="product_id" id="revProduct" class="form-select">
          <option value="0">All products</option>
          <?php foreach (!empty($products) ? $products : array() as $product): ?>
            <option value="<?= (int) $product->id ?>" <?= $productId === (int) $product->id ? 'selected' : '' ?>><?= htmlspecialchars($product->name) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-md-2">
        <label class="form-label small text-muted mb-1" for="revRating">Rating</label>
        <select name="rating" id="revRating" class="form-select">
          <option value="0">All</option>
          <?php for ($i = 5; $i >= 1; $i--): ?>
            <option value="<?= $i ?>" <?= $rating === $i ? 'selected' : '' ?>><?= $i ?> star<?= $i === 1 ? '' : 's' ?></option>
          <?php endfor; ?>
        </select>
      </div>
      <div class="col-md-2">
        <label class="form-label small text-muted mb-1" for="revApproval">Approval</label>
        <select name="approval_status" id="revApproval" class="form-select">
          <option value="">All</option>
          <option value="pending" <?= $approval === 'pending' ? 'selected' : '' ?>>Pending</option>
          <option value="approved" <?= $approval === 'approved' ? 'selected' : '' ?>>Approved</option>
          <option value="rejected" <?= $approval === 'rejected' ? 'selected' : '' ?>>Rejected</option>
        </select>
      </div>
      <div class="col-md-2">
        <label class="form-label small text-muted mb-1" for="revStatus">Status</label>
        <select name="status" id="revStatus" class="form-select">
          <option value="">All</option>
          <option value="1" <?= (string) $status === '1' ? 'selected' : '' ?>>Enabled</option>
          <option value="0" <?= (string) $status === '0' ? 'selected' : '' ?>>Disabled</option>
        </select>
      </div>
      <div class="col-12 d-flex gap-2">
        <button type="submit" class="btn btn-store-primary">Filter</button>
        <a href="<?= $storeUrl ?>/reviews" class="btn btn-outline-secondary">Reset</a>
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
            <th>Reviewer</th>
            <th>Product</th>
            <th>Rating</th>
            <th>Review</th>
            <th>Approval</th>
            <th>Status</th>
            <th>Date</th>
            <th class="text-end">Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach (!empty($items) ? $items : array() as $item): ?>
          <tr>
            <td class="fw-medium">
              <?= htmlspecialchars($item->customer_name) ?>
              <?php if (isset($item->source) && $item->source === 'ai_generated'): ?>
                <div><span class="badge text-bg-info">AI sample</span></div>
              <?php endif; ?>
            </td>
            <td><?= htmlspecialchars($item->product_name ? $item->product_name : '—') ?></td>
            <td><?= (int) $item->rating ?>/5</td>
            <td>
              <?php if ($item->title !== ''): ?>
                <div class="fw-medium"><?= htmlspecialchars($item->title) ?></div>
              <?php endif; ?>
              <?php $snippet = strip_tags((string) $item->content); if (strlen($snippet) > 80) { $snippet = substr($snippet, 0, 77) . '…'; } ?>
              <div class="small text-muted"><?= htmlspecialchars($snippet) ?></div>
            </td>
            <td>
              <?php if ($item->approval_status === 'approved'): ?>
                <span class="badge text-bg-success">Approved</span>
              <?php elseif ($item->approval_status === 'rejected'): ?>
                <span class="badge text-bg-danger">Rejected</span>
              <?php else: ?>
                <span class="badge text-bg-warning">Pending</span>
              <?php endif; ?>
            </td>
            <td><?= ((int) $item->status === 1) ? '<span class="badge text-bg-light border">Enabled</span>' : '<span class="badge text-bg-secondary">Disabled</span>' ?></td>
            <td class="small text-muted"><?= htmlspecialchars($item->created_at) ?></td>
            <td class="text-end text-nowrap">
              <?php if ($item->approval_status !== 'approved'): ?>
                <a href="<?= $storeUrl ?>/reviews/approve/<?= (int) $item->id ?>" class="btn btn-sm btn-outline-success">Approve</a>
              <?php endif; ?>
              <?php if ($item->approval_status !== 'rejected'): ?>
                <a href="<?= $storeUrl ?>/reviews/reject/<?= (int) $item->id ?>" class="btn btn-sm btn-outline-secondary">Reject</a>
              <?php endif; ?>
              <a href="<?= $storeUrl ?>/reviews/form/<?= (int) $item->id ?>" class="btn btn-sm btn-outline-secondary">Edit</a>
              <a href="<?= $storeUrl ?>/reviews/toggle/<?= (int) $item->id ?>" class="btn btn-sm btn-outline-secondary"><?= ((int) $item->status === 1) ? 'Disable' : 'Enable' ?></a>
              <a href="<?= $storeUrl ?>/reviews/delete/<?= (int) $item->id ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete this review?');">Delete</a>
            </td>
          </tr>
          <?php endforeach; ?>
          <?php if (empty($items)): ?>
          <tr><td colspan="8" class="text-center text-muted py-4">No reviews yet.</td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
