<?php
$isEdit = !empty($item);
$selected = isset($selected_product_id) ? (int) $selected_product_id : 0;
?>
<?php $this->load->view('flash'); ?>

<div class="store-card" style="max-width:760px">
  <div class="card-header"><?= $isEdit ? 'Edit FAQ' : 'Add FAQ' ?></div>
  <div class="card-body">
    <form method="post" action="<?= $storeUrl ?>/faqs/save<?= $isEdit ? '/' . (int) $item->id : '' ?>">
      <div class="mb-3">
        <label class="form-label">Product</label>
        <select name="product_id" class="form-select" required>
          <option value="">Select a product</option>
          <?php foreach (!empty($products) ? $products : array() as $product): ?>
            <option value="<?= (int) $product->id ?>" <?= $selected === (int) $product->id ? 'selected' : '' ?>><?= htmlspecialchars($product->name) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="mb-3">
        <label class="form-label">Question</label>
        <input type="text" name="question" class="form-control" required maxlength="500" value="<?= htmlspecialchars($isEdit ? $item->question : '') ?>">
      </div>
      <div class="mb-3">
        <label class="form-label">Answer</label>
        <textarea name="answer" class="form-control" rows="6" required><?= htmlspecialchars($isEdit ? $item->answer : '') ?></textarea>
      </div>
      <div class="row g-3">
        <div class="col-md-4">
          <label class="form-label">Sort order</label>
          <input type="number" name="sort_order" class="form-control" value="<?= htmlspecialchars($isEdit ? (string) $item->sort_order : '0') ?>">
        </div>
        <div class="col-md-8 d-flex align-items-end">
          <div class="form-check">
            <input class="form-check-input" type="checkbox" name="status" value="1" id="faqStatus" <?= (!$isEdit || (int) $item->status === 1) ? 'checked' : '' ?>>
            <label class="form-check-label" for="faqStatus">Enabled</label>
          </div>
        </div>
      </div>
      <div class="d-flex gap-2 mt-4">
        <button type="submit" class="btn btn-store-primary"><?= $isEdit ? 'Update FAQ' : 'Add FAQ' ?></button>
        <a href="<?= $storeUrl ?>/faqs" class="btn btn-outline-secondary">Cancel</a>
      </div>
    </form>
  </div>
</div>
