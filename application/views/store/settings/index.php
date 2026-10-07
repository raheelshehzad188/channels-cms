<?php
function s($settings, $key, $default = '') {
  return htmlspecialchars(isset($settings[$key]) ? $settings[$key] : $default);
}
?>
<?php $this->load->view('flash'); ?>

<div class="store-card mb-3">
  <div class="card-body d-flex justify-content-between align-items-center gap-3 flex-wrap">
    <div>
      <div class="fw-medium">Theme settings</div>
      <p class="text-muted small mb-0">Logo, favicon, footer site icon, colors, homepage banners, and the Zenvello hero slider.</p>
    </div>
    <a href="<?= $storeUrl ?>/theme-settings" class="btn btn-store-primary">Open theme settings</a>
  </div>
</div>

<div class="store-card mb-3">
  <div class="card-body d-flex justify-content-between align-items-center gap-3 flex-wrap">
    <div>
      <div class="fw-medium">Meta Integration</div>
      <p class="text-muted small mb-0">This store’s Pixel ID, Conversions API token, and event log. Other stores keep their own Meta settings.</p>
    </div>
    <a href="<?= $storeUrl ?>/settings/meta" class="btn btn-outline-secondary">Open Meta Integration</a>
  </div>
</div>

<div class="store-card mb-3">
  <div class="card-body d-flex justify-content-between align-items-center gap-3 flex-wrap">
    <div>
      <div class="fw-medium">Storefront texts</div>
      <p class="text-muted small mb-0">Change every customer-facing label (Home, Cart, Add to Cart, checkout, and more). Sweden stores start in Swedish.</p>
    </div>
    <a href="<?= $storeUrl ?>/settings/texts" class="btn btn-outline-secondary">Edit storefront texts</a>
  </div>
</div>

<form method="post">
  <div class="row g-3">
    <div class="col-lg-6">
      <div class="store-card mb-3">
        <div class="card-header">Product pricing</div>
        <div class="card-body">
          <div class="form-check mb-3">
            <input class="form-check-input" type="checkbox" name="auto_add_products" value="1" id="autoAddProducts" <?= !empty($store->auto_add_products) ? 'checked' : ''; ?>>
            <label class="form-check-label" for="autoAddProducts">Auto-add new catalog products</label>
          </div>
          <div class="form-text mb-3">When ecommerce or admin marks a product “Auto add to recommended stores”, it is copied here with your plus amount.</div>
          <div class="mb-0">
            <label class="form-label">Plus amount (<?= htmlspecialchars(store_currency($store)); ?>)</label>
            <input type="number" step="0.01" min="0" name="price_plus_amount" class="form-control" value="<?= htmlspecialchars(isset($store->price_plus_amount) ? $store->price_plus_amount : '0'); ?>">
            <div class="form-text">Added to each product’s store cost. Example: cost 10 + plus 4 = selling price 14. Packs are priced on their own catalog cost, not the parent. Saving a new plus amount recalculates listed prices.</div>
            <button type="submit" name="recalculate_prices" value="1" class="btn btn-outline-secondary mt-3" onclick="return confirm('Recalculate this store’s listed prices from cost + plus amount? Each product is priced independently.');">Recalculate prices</button>
          </div>
        </div>
      </div>

      <?php
        $discOn = !empty($settings['discount_enabled']);
        $discPercent = isset($settings['discount_percent']) ? (float) $settings['discount_percent'] : 10;
        $discScope = isset($settings['discount_scope']) ? $settings['discount_scope'] : 'store';
        $discCatId = isset($settings['discount_category_id']) ? (int) $settings['discount_category_id'] : 0;
        $discTree = isset($category_tree) ? $category_tree : array();
        $discParentId = 0;
        if ($discScope === 'subcategory' && $discCatId) {
            foreach ($discTree as $parent) {
                foreach ($parent['children'] as $child) {
                    if ((int) $child['id'] === $discCatId) {
                        $discParentId = (int) $parent['id'];
                        break 2;
                    }
                }
            }
        } elseif ($discScope === 'category') {
            $discParentId = $discCatId;
        }
      ?>
      <div class="store-card mb-3">
        <div class="card-header">Display discount</div>
        <div class="card-body">
          <div class="mb-3">
            <label class="form-label d-block">Status</label>
            <div class="form-check form-check-inline">
              <input class="form-check-input" type="radio" name="discount_enabled" id="discountOn" value="1" <?= $discOn ? 'checked' : ''; ?>>
              <label class="form-check-label" for="discountOn">On</label>
            </div>
            <div class="form-check form-check-inline">
              <input class="form-check-input" type="radio" name="discount_enabled" id="discountOff" value="0" <?= $discOn ? '' : 'checked'; ?>>
              <label class="form-check-label" for="discountOff">Off</label>
            </div>
            <p class="form-text mb-0 mt-2">Off hides sale badges and the extra regular price. Selling price never changes.</p>
          </div>
          <div id="discountFields">
            <p class="form-text mb-3">Regular price is shown as your selling price plus this percent, with a strikethrough. Customers still pay the current selling price.</p>
            <div class="mb-3">
              <label class="form-label">Discount percent</label>
              <div class="input-group" style="max-width:180px">
                <input type="number" min="0" max="90" step="1" name="discount_percent" id="discountPercent" class="form-control" value="<?= htmlspecialchars($discPercent > 0 ? (string) $discPercent : '10') ?>">
                <span class="input-group-text">%</span>
              </div>
            </div>
            <div class="mb-3">
              <label class="form-label d-block">Apply to</label>
              <div class="form-check">
                <input class="form-check-input" type="radio" name="discount_scope" id="discScopeStore" value="store" <?= $discScope === 'store' ? 'checked' : ''; ?>>
                <label class="form-check-label" for="discScopeStore">Whole store</label>
              </div>
              <div class="form-check">
                <input class="form-check-input" type="radio" name="discount_scope" id="discScopeCat" value="category" <?= $discScope === 'category' ? 'checked' : ''; ?>>
                <label class="form-check-label" for="discScopeCat">One category (includes its subcategories)</label>
              </div>
              <div class="form-check">
                <input class="form-check-input" type="radio" name="discount_scope" id="discScopeSub" value="subcategory" <?= $discScope === 'subcategory' ? 'checked' : ''; ?>>
                <label class="form-check-label" for="discScopeSub">One subcategory only</label>
              </div>
            </div>
            <div class="mb-3" id="discountCategoryWrap">
              <label class="form-label">Category</label>
              <select class="form-select" id="discountParentId">
                <option value="0">Select category</option>
                <?php foreach ($discTree as $parent): ?>
                  <option value="<?= (int) $parent['id'] ?>" <?= (int) $parent['id'] === $discParentId ? 'selected' : ''; ?>><?= htmlspecialchars($parent['name']) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="mb-0" id="discountSubcategoryWrap">
              <label class="form-label">Subcategory</label>
              <select class="form-select" id="discountSubId">
                <option value="0">Select subcategory</option>
              </select>
            </div>
          </div>
          <input type="hidden" name="discount_category_id" id="discountCategoryId" value="<?= (int) $discCatId ?>">
        </div>
      </div>

      <div class="store-card mb-3">
        <div class="card-header">General</div>
        <div class="card-body">
          <div class="mb-3"><label class="form-label">Store Name</label><input type="text" name="general_store_name" class="form-control" value="<?= s($settings, 'general_store_name', $store->name); ?>"></div>
          <div class="mb-3"><label class="form-label">Contact Email</label><input type="email" name="general_contact_email" class="form-control" value="<?= s($settings, 'general_contact_email', $store->email); ?>"></div>
          <div class="mb-0"><label class="form-label">Support Phone</label><input type="text" name="general_support_phone" class="form-control" value="<?= s($settings, 'general_support_phone', $store->phone); ?>"></div>
        </div>
      </div>

      <div class="store-card mb-3">
        <div class="card-header">Product Detail Design</div>
        <div class="card-body">
          <?php $pdpDesign = isset($settings['product_detail_design']) ? $settings['product_detail_design'] : 'old'; ?>
          <div class="mb-2">
            <div class="form-check">
              <input class="form-check-input" type="radio" name="product_detail_design" id="pdpDesignOld" value="old" <?= $pdpDesign !== 'new' ? 'checked' : '' ?>>
              <label class="form-check-label" for="pdpDesignOld">Old Design</label>
            </div>
            <div class="form-check">
              <input class="form-check-input" type="radio" name="product_detail_design" id="pdpDesignNew" value="new" <?= $pdpDesign === 'new' ? 'checked' : '' ?>>
              <label class="form-check-label" for="pdpDesignNew">New Design</label>
            </div>
          </div>
          <div class="form-text">Old Design keeps the current product page. New Design uses the modern gallery, variation cards, reviews and FAQs layout. Header and footer stay the same.</div>
        </div>
      </div>

      <div class="store-card mb-3">
        <div class="card-header">Categories</div>
        <div class="card-body">
          <div class="form-check mb-0">
            <input class="form-check-input" type="checkbox" name="hide_empty_subcategories" value="1" id="hideEmptySubcategories" <?= !empty($settings['hide_empty_subcategories']) ? 'checked' : ''; ?>>
            <label class="form-check-label" for="hideEmptySubcategories">Hide empty sub categories</label>
          </div>
          <div class="form-text">On category pages, hide sub categories that have no products in this store.</div>
        </div>
      </div>

      <div class="store-card mb-3">
        <div class="card-header">Email</div>
        <div class="card-body">
          <div class="mb-3"><label class="form-label">From Name</label><input type="text" name="email_from_name" class="form-control" value="<?= s($settings, 'email_from_name'); ?>"></div>
          <div class="mb-3"><label class="form-label">From Address</label><input type="email" name="email_from_address" class="form-control" value="<?= s($settings, 'email_from_address'); ?>"></div>
          <div class="mb-0"><label class="form-label">Reply To</label><input type="email" name="email_reply_to" class="form-control" value="<?= s($settings, 'email_reply_to'); ?>"></div>
        </div>
      </div>

      <div class="store-card">
        <div class="card-header">Notifications</div>
        <div class="card-body">
          <div class="mb-3">
            <label class="form-label">WhatsApp number</label>
            <input type="text" name="whatsapp_number" class="form-control" value="<?= s($settings, 'whatsapp_number', $store->phone); ?>" placeholder="923004210607">
            <div class="form-text">Used for new-order WhatsApp alerts. Include country code without +.</div>
          </div>
          <div class="form-check mb-2"><input class="form-check-input" type="checkbox" name="notify_new_order" value="1" <?= s($settings, 'notify_new_order') ? 'checked' : ''; ?> id="n1"><label class="form-check-label" for="n1">New order alerts</label></div>
          <div class="form-check mb-2"><input class="form-check-input" type="checkbox" name="notify_low_stock" value="1" <?= s($settings, 'notify_low_stock') ? 'checked' : ''; ?> id="n2"><label class="form-check-label" for="n2">Low stock alerts</label></div>
          <div class="form-check"><input class="form-check-input" type="checkbox" name="notify_new_customer" value="1" <?= s($settings, 'notify_new_customer') ? 'checked' : ''; ?> id="n3"><label class="form-check-label" for="n3">New customer alerts</label></div>
        </div>
      </div>
    </div>

    <div class="col-lg-6">
      <div class="store-card mb-3">
        <div class="card-header">Invoice</div>
        <div class="card-body">
          <div class="mb-3"><label class="form-label">Invoice Prefix</label><input type="text" name="invoice_prefix" class="form-control" value="<?= s($settings, 'invoice_prefix', 'INV-'); ?>"></div>
          <div class="mb-3"><label class="form-label">Footer Text</label><textarea name="invoice_footer" class="form-control" rows="2"><?= s($settings, 'invoice_footer'); ?></textarea></div>
          <div class="mb-0"><label class="form-label">Logo Text</label><input type="text" name="invoice_logo_text" class="form-control" value="<?= s($settings, 'invoice_logo_text', $store->name); ?>"></div>
        </div>
      </div>

      <div class="store-card mb-3">
        <div class="card-header">Currency</div>
        <div class="card-body">
          <p class="mb-0">This store charges and displays <strong><?= htmlspecialchars(store_currency($store)) ?></strong> from the country assigned by Super Admin. PayPal uses the same currency.</p>
        </div>
      </div>

      <div class="store-card mb-3">
        <div class="card-header">Taxes <span class="badge text-bg-secondary">Placeholder</span></div>
        <div class="card-body">
          <div class="form-check mb-3"><input class="form-check-input" type="checkbox" name="tax_enabled" value="1" id="tax1" <?= s($settings, 'tax_enabled') ? 'checked' : ''; ?>><label class="form-check-label" for="tax1">Enable taxes</label></div>
          <div class="mb-3"><label class="form-label">Tax Rate (%)</label><input type="number" step="0.01" name="tax_rate" class="form-control" value="<?= s($settings, 'tax_rate', '0'); ?>"></div>
          <div class="mb-0"><label class="form-label">Tax Label</label><input type="text" name="tax_label" class="form-control" value="<?= s($settings, 'tax_label', 'VAT'); ?>"></div>
        </div>
      </div>

      <div class="store-card">
        <div class="card-header">Shipping</div>
        <div class="card-body">
          <p class="text-muted mb-0">Checkout shipping is set by Super Admin on <strong>Pricing → Shipping per item</strong>. It is charged as that fee × cart quantity.</p>
        </div>
      </div>
    </div>
  </div>

  <div class="mt-3">
    <button type="submit" class="btn btn-store-primary">Save Settings</button>
  </div>
</form>
<script>
(function () {
  var tree = <?= json_encode($discTree) ?>;
  var parentSel = document.getElementById('discountParentId');
  var subSel = document.getElementById('discountSubId');
  var hidden = document.getElementById('discountCategoryId');
  var wrapCat = document.getElementById('discountCategoryWrap');
  var wrapSub = document.getElementById('discountSubcategoryWrap');
  var wrapFields = document.getElementById('discountFields');
  var selectedSub = <?= (int) $discCatId ?>;
  function discountOn() {
    var el = document.querySelector('input[name="discount_enabled"]:checked');
    return el ? el.value === '1' : false;
  }
  function syncEnabled() {
    if (wrapFields) wrapFields.style.display = discountOn() ? '' : 'none';
  }
  function scope() {
    var el = document.querySelector('input[name="discount_scope"]:checked');
    return el ? el.value : 'store';
  }
  function childrenOf(parentId) {
    parentId = parseInt(parentId, 10) || 0;
    for (var i = 0; i < tree.length; i++) {
      if (parseInt(tree[i].id, 10) === parentId) return tree[i].children || [];
    }
    return [];
  }
  function fillSubs(parentId, pick) {
    if (!subSel) return;
    var kids = childrenOf(parentId);
    subSel.innerHTML = '<option value="0">Select subcategory</option>';
    for (var i = 0; i < kids.length; i++) {
      var opt = document.createElement('option');
      opt.value = String(kids[i].id);
      opt.textContent = kids[i].name;
      if (pick && parseInt(kids[i].id, 10) === parseInt(pick, 10)) opt.selected = true;
      subSel.appendChild(opt);
    }
  }
  function syncHidden() {
    var s = scope();
    if (s === 'store') hidden.value = '0';
    else if (s === 'category') hidden.value = parentSel ? parentSel.value : '0';
    else hidden.value = subSel ? subSel.value : '0';
  }
  function syncWraps() {
    var s = scope();
    if (wrapCat) wrapCat.style.display = s === 'store' ? 'none' : '';
    if (wrapSub) wrapSub.style.display = s === 'subcategory' ? '' : 'none';
    if (s === 'subcategory') fillSubs(parentSel ? parentSel.value : 0, selectedSub);
    syncHidden();
  }
  document.querySelectorAll('input[name="discount_scope"]').forEach(function (r) {
    r.addEventListener('change', function () { selectedSub = 0; syncWraps(); });
  });
  if (parentSel) parentSel.addEventListener('change', function () { selectedSub = 0; fillSubs(parentSel.value, 0); syncHidden(); });
  if (subSel) subSel.addEventListener('change', syncHidden);
  var form = parentSel ? parentSel.closest('form') : null;
  if (form) form.addEventListener('submit', syncHidden);
  document.querySelectorAll('input[name="discount_enabled"]').forEach(function (r) {
    r.addEventListener('change', syncEnabled);
  });
  syncWraps();
  syncEnabled();
})();
</script>
