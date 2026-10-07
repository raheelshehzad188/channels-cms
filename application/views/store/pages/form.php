<?php
$isEdit = !empty($item);
$titleVal = $isEdit ? $item->title : '';
$slugVal = $isEdit ? $item->slug : '';
$detailVal = $isEdit ? $item->detail : '';
$metaVal = $isEdit ? $item->meta_description : '';
?>
<?php $this->load->view('flash'); ?>

<div class="store-card" style="max-width:820px">
  <div class="card-header"><?= $isEdit ? 'Edit page' : 'Add page' ?></div>
  <div class="card-body">
    <form method="post" action="<?= $storeUrl ?>/pages/save<?= $isEdit ? '/' . (int) $item->id : '' ?>">
      <div class="mb-3">
        <label class="form-label">Title</label>
        <input type="text" name="title" id="pageTitle" class="form-control" required maxlength="255" value="<?= htmlspecialchars($titleVal) ?>">
      </div>
      <div class="mb-3">
        <label class="form-label">Slug</label>
        <div class="input-group">
          <span class="input-group-text">/page/</span>
          <input type="text" name="slug" id="pageSlug" class="form-control" maxlength="180" value="<?= htmlspecialchars($slugVal) ?>" placeholder="about-us">
        </div>
        <div class="form-text">Leave blank to generate from the title. Do not use reserved names such as contact or shop.</div>
      </div>
      <div class="mb-3">
        <label class="form-label">Detail</label>
        <textarea name="detail" class="form-control" rows="12" required><?= htmlspecialchars($detailVal) ?></textarea>
        <div class="form-text">Plain text or simple HTML is allowed (paragraphs, lists, links, headings).</div>
      </div>
      <div class="mb-3">
        <label class="form-label">Meta description <span class="text-muted">(optional)</span></label>
        <input type="text" name="meta_description" class="form-control" maxlength="320" value="<?= htmlspecialchars($metaVal) ?>">
      </div>
      <div class="row g-3">
        <div class="col-md-4">
          <label class="form-label">Sort order</label>
          <input type="number" name="sort_order" class="form-control" value="<?= htmlspecialchars($isEdit ? (string) $item->sort_order : '0') ?>">
        </div>
        <div class="col-md-8 d-flex align-items-end flex-wrap gap-3">
          <div class="form-check">
            <input class="form-check-input" type="checkbox" name="status" value="1" id="pageStatus" <?= (!$isEdit || (int) $item->status === 1) ? 'checked' : '' ?>>
            <label class="form-check-label" for="pageStatus">Published</label>
          </div>
          <div class="form-check">
            <input class="form-check-input" type="checkbox" name="show_in_nav" value="1" id="pageNav" <?= ($isEdit && (int) $item->show_in_nav === 1) ? 'checked' : '' ?>>
            <label class="form-check-label" for="pageNav">Show in header</label>
            <div class="form-text">Used only if Header menu in Theme Settings is empty.</div>
          </div>
          <div class="form-check">
            <input class="form-check-input" type="checkbox" name="show_in_footer" value="1" id="pageFooter" <?= (!$isEdit || (int) $item->show_in_footer === 1) ? 'checked' : '' ?>>
            <label class="form-check-label" for="pageFooter">Show in footer</label>
          </div>
        </div>
      </div>
      <div class="d-flex gap-2 mt-4">
        <button type="submit" class="btn btn-store-primary"><?= $isEdit ? 'Update page' : 'Create page' ?></button>
        <a href="<?= $storeUrl ?>/pages" class="btn btn-outline-secondary">Cancel</a>
      </div>
    </form>
  </div>
</div>
<script>
(function () {
  var title = document.getElementById('pageTitle');
  var slug = document.getElementById('pageSlug');
  if (!title || !slug) return;
  var touched = slug.value !== '';
  slug.addEventListener('input', function () { touched = slug.value !== ''; });
  title.addEventListener('input', function () {
    if (touched) return;
    slug.value = title.value.toLowerCase()
      .replace(/[åäáàâã]/g, 'a').replace(/[öøóòôõ]/g, 'o')
      .replace(/[^a-z0-9]+/g, '-').replace(/^-+|-+$/g, '');
  });
})();
</script>
