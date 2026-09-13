<?php $this->load->view('flash'); ?>

<div class="d-flex justify-content-between align-items-center mb-3">
  <p class="text-muted mb-0">Products in your store. Edit title, slug, images, and price anytime.</p>
  <a href="<?= $storeUrl; ?>/products/form" class="btn btn-store-primary"><i class="bi bi-plus-lg me-1"></i> Add Product</a>
</div>

<div class="store-card">
  <div class="card-body p-0">
    <div class="table-responsive">
      <table class="table table-hover mb-0 align-middle">
        <thead>
          <tr>
            <th>Product</th>
            <th>Slug</th>
            <th>Price</th>
            <th>Status</th>
            <th class="text-end">Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($products as $product): ?>
            <?php $image = !empty($product->image) ? base_url($product->image) : base_url('assets/frontend/fruitables/img/fruite-item-5.jpg'); ?>
            <tr>
              <td>
                <div class="d-flex align-items-center gap-2">
                  <img src="<?= htmlspecialchars($image) ?>" alt="" width="48" height="48" style="object-fit:cover; border-radius:6px;">
                  <span class="fw-medium"><?= htmlspecialchars($product->name) ?></span>
                </div>
              </td>
              <td class="text-muted"><?= htmlspecialchars($product->slug) ?></td>
              <td><?= format_money((float) $product->price) ?></td>
              <td><?= ((int) $product->status === 1) ? '<span class="badge text-bg-success">Active</span>' : '<span class="badge text-bg-secondary">Inactive</span>' ?></td>
              <td class="text-end">
                <a href="<?= $storeUrl ?>/products/form/<?= (int) $product->id ?>" class="btn btn-sm btn-outline-secondary">Edit</a>
                <a href="<?= $storeUrl ?>/products/delete/<?= (int) $product->id ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete this product from your store?');">Delete</a>
              </td>
            </tr>
          <?php endforeach; ?>
          <?php if (empty($products)): ?>
            <tr><td colspan="5" class="text-center text-muted py-4">No products yet. Copy one from Available Products or add your own.</td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
