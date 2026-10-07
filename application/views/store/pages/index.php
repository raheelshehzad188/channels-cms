<?php $this->load->view('flash'); ?>
<?php
$filters = isset($filters) ? $filters : array();
$q = isset($filters['q']) ? $filters['q'] : '';
$status = isset($filters['status']) ? $filters['status'] : '';
?>
<div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
  <p class="text-muted mb-0">Create storefront pages such as About, Privacy, or Shipping. Most pages are at <code>/page/your-slug</code>. Privacy Policy, Terms of Service, and Data Deletion also publish at <code>/privacy-policy</code>, <code>/terms-of-service</code>, and <code>/data-deletion</code>.</p>
  <a href="<?= $storeUrl ?>/pages/form" class="btn btn-store-primary"><i class="bi bi-plus-lg me-1"></i> Add page</a>
</div>

<form method="get" action="<?= $storeUrl ?>/pages" class="store-card mb-3">
  <div class="card-body">
    <div class="row g-2 align-items-end">
      <div class="col-md-6">
        <label class="form-label small text-muted mb-1" for="pageQ">Search</label>
        <input type="text" name="q" id="pageQ" class="form-control" value="<?= htmlspecialchars($q) ?>" placeholder="Title or slug…">
      </div>
      <div class="col-md-3">
        <label class="form-label small text-muted mb-1" for="pageStatus">Status</label>
        <select name="status" id="pageStatus" class="form-select">
          <option value="">All</option>
          <option value="1" <?= (string) $status === '1' ? 'selected' : '' ?>>Published</option>
          <option value="0" <?= (string) $status === '0' ? 'selected' : '' ?>>Draft</option>
        </select>
      </div>
      <div class="col-md-3 d-flex gap-2">
        <button type="submit" class="btn btn-store-primary flex-grow-1">Filter</button>
        <a href="<?= $storeUrl ?>/pages" class="btn btn-outline-secondary">Reset</a>
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
            <th>Title</th>
            <th>Slug</th>
            <th>Status</th>
            <th>Menu</th>
            <th class="text-end">Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach (!empty($items) ? $items : array() as $item): ?>
          <tr>
            <td>
              <div class="fw-medium"><?= htmlspecialchars($item->title) ?></div>
              <?php $snippet = strip_tags((string) $item->detail); if (strlen($snippet) > 90) { $snippet = substr($snippet, 0, 87) . '…'; } ?>
              <div class="small text-muted"><?= htmlspecialchars($snippet) ?></div>
            </td>
            <td>
              <code><?= htmlspecialchars($item->slug) ?></code>
              <?php if ((int) $item->status === 1): ?>
                <div><a class="small" href="<?= htmlspecialchars(page_url($item)) ?>" target="_blank" rel="noopener">View</a></div>
              <?php endif; ?>
            </td>
            <td><?= ((int) $item->status === 1) ? '<span class="badge text-bg-success">Published</span>' : '<span class="badge text-bg-secondary">Draft</span>' ?></td>
            <td class="small">
              <?= !empty($item->show_in_nav) ? 'Header' : '' ?>
              <?= !empty($item->show_in_nav) && !empty($item->show_in_footer) ? ' · ' : '' ?>
              <?= !empty($item->show_in_footer) ? 'Footer' : '' ?>
              <?= empty($item->show_in_nav) && empty($item->show_in_footer) ? '—' : '' ?>
            </td>
            <td class="text-end text-nowrap">
              <a href="<?= $storeUrl ?>/pages/form/<?= (int) $item->id ?>" class="btn btn-sm btn-outline-secondary">Edit</a>
              <a href="<?= $storeUrl ?>/pages/toggle/<?= (int) $item->id ?>" class="btn btn-sm btn-outline-secondary"><?= ((int) $item->status === 1) ? 'Unpublish' : 'Publish' ?></a>
              <a href="<?= $storeUrl ?>/pages/delete/<?= (int) $item->id ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete this page?');">Delete</a>
            </td>
          </tr>
          <?php endforeach; ?>
          <?php if (empty($items)): ?>
          <tr><td colspan="5" class="text-center text-muted py-4">No pages yet. Add About, Privacy Policy, or Shipping info.</td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
