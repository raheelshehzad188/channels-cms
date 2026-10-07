<?php
$isEdit = !empty($item);
$labelVal = $isEdit ? $item->label : '';
$typeVal = $isEdit ? $item->item_type : 'page';
$pageVal = $isEdit ? (int) $item->page_id : 0;
$slugVal = $isEdit ? $item->slug : '';
$statusOn = !$isEdit || (int) $item->status === 1;
$pages = isset($cms_pages) ? $cms_pages : array();
$items = isset($menu_items) ? $menu_items : array();
$pageMap = array();
foreach ($pages as $page) {
    $pageMap[(int) $page->id] = $page;
}
?>
<?php $this->load->view('store/theme_settings/_tabs'); ?>
<?php $this->load->view('flash'); ?>

<div class="row g-3">
  <div class="col-lg-5">
    <div class="store-card">
      <div class="card-header"><?= $isEdit ? 'Edit menu item' : 'Add menu item' ?></div>
      <div class="card-body">
        <form method="post" action="<?= $storeUrl ?>/theme-settings/menu-save" id="headerMenuForm">
          <?php if ($isEdit): ?>
            <input type="hidden" name="id" value="<?= (int) $item->id ?>">
          <?php endif; ?>
          <div class="mb-3">
            <label class="form-label" for="menuLabel">Label (native)</label>
            <input type="text" name="label" id="menuLabel" class="form-control" maxlength="120" value="<?= htmlspecialchars($labelVal) ?>" placeholder="Integritetspolicy">
            <div class="form-text">Shown in the store’s native language. Leave blank when linking a page to use the page title.</div>
          </div>
          <div class="mb-3">
            <label class="form-label" for="menuLabelEn">Label (English)</label>
            <input type="text" name="label_en" id="menuLabelEn" class="form-control" maxlength="120" value="<?= htmlspecialchars($isEdit && isset($item->label_en) ? $item->label_en : '') ?>" placeholder="Privacy Policy">
            <div class="form-text">Shown when the shopper switches the storefront to English.</div>
          </div>
          <div class="mb-3">
            <label class="form-label">Link type</label>
            <div class="d-flex gap-3">
              <div class="form-check">
                <input class="form-check-input" type="radio" name="item_type" id="menuTypePage" value="page" <?= $typeVal !== 'custom' ? 'checked' : '' ?>>
                <label class="form-check-label" for="menuTypePage">Select page</label>
              </div>
              <div class="form-check">
                <input class="form-check-input" type="radio" name="item_type" id="menuTypeCustom" value="custom" <?= $typeVal === 'custom' ? 'checked' : '' ?>>
                <label class="form-check-label" for="menuTypeCustom">Custom slug</label>
              </div>
            </div>
          </div>
          <div class="mb-3" id="menuPageWrap">
            <label class="form-label" for="menuPage">Page</label>
            <select name="page_id" id="menuPage" class="form-select">
              <option value="0">Choose a page…</option>
              <?php foreach ($pages as $page): ?>
                <option value="<?= (int) $page->id ?>" <?= $pageVal === (int) $page->id ? 'selected' : '' ?>>
                  <?= htmlspecialchars($page->title) ?> (<?= htmlspecialchars($page->slug) ?>)<?= ((int) $page->status === 1) ? '' : ' — draft' ?>
                </option>
              <?php endforeach; ?>
            </select>
            <?php if (empty($pages)): ?>
              <div class="form-text">No CMS pages yet. <a href="<?= $storeUrl ?>/pages/form">Create a page</a> or use a custom slug.</div>
            <?php endif; ?>
          </div>
          <div class="mb-3" id="menuSlugWrap">
            <label class="form-label" for="menuSlug">Custom slug</label>
            <div class="input-group">
              <span class="input-group-text">/</span>
              <input type="text" name="slug" id="menuSlug" class="form-control" maxlength="255" value="<?= htmlspecialchars($typeVal === 'custom' ? $slugVal : '') ?>" placeholder="privacy-policy">
            </div>
            <div class="form-text">Examples: <code>contact</code>, <code>privacy-policy</code>, <code>shop</code>, <code>page/about-us</code>.</div>
          </div>
          <div class="form-check mb-3">
            <input class="form-check-input" type="checkbox" name="status" value="1" id="menuStatus" <?= $statusOn ? 'checked' : '' ?>>
            <label class="form-check-label" for="menuStatus">Show in header</label>
          </div>
          <div class="d-flex gap-2">
            <button type="submit" class="btn btn-store-primary"><?= $isEdit ? 'Update item' : 'Add item' ?></button>
            <?php if ($isEdit): ?>
              <a href="<?= $storeUrl ?>/theme-settings/menu" class="btn btn-outline-secondary">Cancel</a>
            <?php endif; ?>
          </div>
        </form>
      </div>
    </div>
  </div>
  <div class="col-lg-7">
    <div class="store-card">
      <div class="card-header d-flex justify-content-between align-items-center">
        <span>Header items</span>
        <span class="small text-muted">Drag to change order</span>
      </div>
      <div class="card-body p-0">
        <?php if (empty($items)): ?>
          <p class="text-muted text-center py-4 mb-0">No extra header items yet. Add a page or a custom slug.</p>
        <?php else: ?>
          <ul class="list-group list-group-flush" id="headerMenuList">
            <?php foreach ($items as $row): ?>
              <?php
                $page = ($row->item_type === 'page' && isset($pageMap[(int) $row->page_id])) ? $pageMap[(int) $row->page_id] : null;
                $target = $page ? $page->slug : $row->slug;
              ?>
              <li class="list-group-item d-flex align-items-center gap-3" data-id="<?= (int) $row->id ?>">
                <button type="button" class="btn btn-sm btn-outline-secondary header-menu-handle" title="Drag to reorder" aria-label="Drag to reorder">
                  <i class="bi bi-grip-vertical"></i>
                </button>
                <div class="flex-grow-1 min-w-0">
                  <div class="fw-medium"><?= htmlspecialchars($row->label !== '' ? $row->label : ($page ? $page->title : $row->slug)) ?><?php if (!empty($row->label_en)): ?> <span class="text-muted fw-normal">/ <?= htmlspecialchars($row->label_en) ?></span><?php endif; ?></div>
                  <div class="small text-muted">
                    <?= $row->item_type === 'page' ? 'Page' : 'Custom' ?>
                    · /<?= htmlspecialchars($target) ?>
                    <?= ((int) $row->status !== 1) ? ' · hidden' : '' ?>
                  </div>
                </div>
                <div class="text-nowrap">
                  <a href="<?= $storeUrl ?>/theme-settings/menu?edit=<?= (int) $row->id ?>" class="btn btn-sm btn-outline-secondary">Edit</a>
                  <a href="<?= $storeUrl ?>/theme-settings/menu-delete/<?= (int) $row->id ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Remove this header item?');">Delete</a>
                </div>
              </li>
            <?php endforeach; ?>
          </ul>
        <?php endif; ?>
      </div>
    </div>
    <p class="small text-muted mt-2 mb-0">Home, Shop, and categories stay in the header. These items appear after them. Footer links still come from Pages.</p>
  </div>
</div>

<style>
  .header-menu-handle { cursor: grab; }
  .header-menu-handle:active { cursor: grabbing; }
  #headerMenuList .sortable-ghost { opacity: .45; }
</style>
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.2/Sortable.min.js"></script>
<script>
(function () {
  var typePage = document.getElementById('menuTypePage');
  var typeCustom = document.getElementById('menuTypeCustom');
  var pageWrap = document.getElementById('menuPageWrap');
  var slugWrap = document.getElementById('menuSlugWrap');
  function syncType() {
    var isPage = typePage && typePage.checked;
    if (pageWrap) pageWrap.style.display = isPage ? '' : 'none';
    if (slugWrap) slugWrap.style.display = isPage ? 'none' : '';
  }
  if (typePage) typePage.addEventListener('change', syncType);
  if (typeCustom) typeCustom.addEventListener('change', syncType);
  syncType();

  var list = document.getElementById('headerMenuList');
  if (!list || typeof Sortable === 'undefined') return;
  Sortable.create(list, {
    handle: '.header-menu-handle',
    animation: 150,
    onEnd: function () {
      var ids = Array.prototype.map.call(list.querySelectorAll('[data-id]'), function (el) {
        return el.getAttribute('data-id');
      });
      var body = new URLSearchParams();
      ids.forEach(function (id) { body.append('ids[]', id); });
      fetch('<?= $storeUrl ?>/theme-settings/menu-sort', {
        method: 'POST',
        headers: { 'X-Requested-With': 'XMLHttpRequest' },
        body: body
      });
    }
  });
})();
</script>
