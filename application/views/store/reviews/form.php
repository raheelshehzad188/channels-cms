<?php
$isEdit = !empty($item);
$selected = isset($selected_product_id) ? (int) $selected_product_id : 0;
$rating = $isEdit ? (int) $item->rating : 5;
$approval = $isEdit ? $item->approval_status : 'approved';
?>
<?php $this->load->view('flash'); ?>

<div class="store-card" style="max-width:760px">
  <div class="card-header"><?= $isEdit ? 'Edit Review' : 'Add Review' ?></div>
  <div class="card-body">
    <form method="post" action="<?= $storeUrl ?>/reviews/save<?= $isEdit ? '/' . (int) $item->id : '' ?>">
      <div class="mb-3">
        <label class="form-label">Product</label>
        <select name="product_id" class="form-select" required>
          <option value="">Select a product</option>
          <?php foreach (!empty($products) ? $products : array() as $product): ?>
            <option value="<?= (int) $product->id ?>" <?= $selected === (int) $product->id ? 'selected' : '' ?>><?= htmlspecialchars($product->name) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="row g-3">
        <div class="col-md-6">
          <label class="form-label">Reviewer name</label>
          <input type="text" name="customer_name" class="form-control" required maxlength="150" value="<?= htmlspecialchars($isEdit ? $item->customer_name : '') ?>">
        </div>
        <div class="col-md-3">
          <label class="form-label">Rating</label>
          <select name="rating" class="form-select">
            <?php for ($i = 5; $i >= 1; $i--): ?>
              <option value="<?= $i ?>" <?= $rating === $i ? 'selected' : '' ?>><?= $i ?></option>
            <?php endfor; ?>
          </select>
        </div>
        <div class="col-md-3">
          <label class="form-label">Approval</label>
          <select name="approval_status" class="form-select">
            <option value="approved" <?= $approval === 'approved' ? 'selected' : '' ?>>Approved</option>
            <option value="pending" <?= $approval === 'pending' ? 'selected' : '' ?>>Pending</option>
            <option value="rejected" <?= $approval === 'rejected' ? 'selected' : '' ?>>Rejected</option>
          </select>
        </div>
      </div>
      <div class="mb-3 mt-3">
        <label class="form-label">Title <span class="text-muted">(optional)</span></label>
        <input type="text" name="title" class="form-control" maxlength="255" value="<?= htmlspecialchars($isEdit ? $item->title : '') ?>">
      </div>
      <div class="mb-3">
        <label class="form-label">Review</label>
        <textarea name="content" class="form-control" rows="6" required><?= htmlspecialchars($isEdit ? $item->content : '') ?></textarea>
      </div>
      <div class="form-check mb-3">
        <input class="form-check-input" type="checkbox" name="status" value="1" id="revStatus" <?= (!$isEdit || (int) $item->status === 1) ? 'checked' : '' ?>>
        <label class="form-check-label" for="revStatus">Enabled</label>
      </div>
      <div class="d-flex gap-2">
        <button type="submit" class="btn btn-store-primary"><?= $isEdit ? 'Update Review' : 'Add Review' ?></button>
        <a href="<?= $storeUrl ?>/reviews" class="btn btn-outline-secondary">Cancel</a>
      </div>
    </form>
  </div>
</div>
