<?php
function s($settings, $key, $default = '') {
  return htmlspecialchars(isset($settings[$key]) ? $settings[$key] : $default);
}
?>
<?php $this->load->view('flash'); ?>

<form method="post">
  <div class="row g-3">
    <div class="col-lg-6">
      <div class="store-card mb-3">
        <div class="card-header">General</div>
        <div class="card-body">
          <div class="mb-3"><label class="form-label">Store Name</label><input type="text" name="general_store_name" class="form-control" value="<?= s($settings, 'general_store_name', $store->name); ?>"></div>
          <div class="mb-3"><label class="form-label">Contact Email</label><input type="email" name="general_contact_email" class="form-control" value="<?= s($settings, 'general_contact_email', $store->email); ?>"></div>
          <div class="mb-0"><label class="form-label">Support Phone</label><input type="text" name="general_support_phone" class="form-control" value="<?= s($settings, 'general_support_phone', $store->phone); ?>"></div>
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
        <div class="card-header">Shipping <span class="badge text-bg-secondary">Placeholder</span></div>
        <div class="card-body">
          <div class="form-check mb-3"><input class="form-check-input" type="checkbox" name="shipping_enabled" value="1" id="ship1" <?= s($settings, 'shipping_enabled') ? 'checked' : ''; ?>><label class="form-check-label" for="ship1">Enable flat rate shipping</label></div>
          <div class="mb-0"><label class="form-label">Flat Rate</label><input type="number" step="0.01" name="shipping_flat_rate" class="form-control" value="<?= s($settings, 'shipping_flat_rate', '0'); ?>"></div>
        </div>
      </div>
    </div>
  </div>

  <div class="mt-3">
    <button type="submit" class="btn btn-store-primary">Save Settings</button>
  </div>
</form>
