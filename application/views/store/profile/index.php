<?php $this->load->view('flash'); ?>

<form method="post" enctype="multipart/form-data">
  <div class="row g-3">
    <div class="col-lg-8">
      <div class="store-card mb-3">
        <div class="card-header">Store Information</div>
        <div class="card-body">
          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label">Store Name</label>
              <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($store->name); ?>" required>
            </div>
            <div class="col-md-6">
              <label class="form-label">Email</label>
              <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($store->email); ?>">
            </div>
            <div class="col-12">
              <label class="form-label">Description</label>
              <textarea name="description" class="form-control" rows="3"><?= htmlspecialchars($store->description); ?></textarea>
            </div>
            <div class="col-12">
              <label class="form-label">Address</label>
              <textarea name="address" class="form-control" rows="2"><?= htmlspecialchars($store->address); ?></textarea>
            </div>
            <div class="col-md-4">
              <label class="form-label">Country</label>
              <input type="text" class="form-control" value="<?= htmlspecialchars(!empty($store->country_name) ? $store->country_name : $store->country); ?>" disabled>
              <div class="form-text">Assigned by Super Admin. Prices and PayPal use this country’s currency.</div>
            </div>
            <div class="col-md-4">
              <label class="form-label">Currency</label>
              <input type="text" class="form-control" value="<?= htmlspecialchars(store_currency($store)); ?>" disabled>
            </div>
            <div class="col-md-4">
              <label class="form-label">Phone</label>
              <input type="text" name="phone" class="form-control" value="<?= htmlspecialchars($store->phone); ?>">
            </div>
            <div class="col-md-6">
              <label class="form-label">Timezone</label>
              <input type="text" name="timezone" class="form-control" value="<?= htmlspecialchars($store->timezone ?: 'UTC'); ?>">
            </div>
            <div class="col-md-6">
              <label class="form-label">Language</label>
              <input type="text" name="language" class="form-control" value="<?= htmlspecialchars($store->language ?: 'en'); ?>">
            </div>
          </div>
        </div>
      </div>

      <div class="store-card mb-3">
        <div class="card-header">Business Information</div>
        <div class="card-body">
          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label">Business Name</label>
              <input type="text" name="business_name" class="form-control" value="<?= htmlspecialchars($store->business_name); ?>">
            </div>
            <div class="col-md-6">
              <label class="form-label">Registration No.</label>
              <input type="text" name="business_registration" class="form-control" value="<?= htmlspecialchars($store->business_registration); ?>">
            </div>
            <div class="col-md-6">
              <label class="form-label">Tax ID</label>
              <input type="text" name="tax_id" class="form-control" value="<?= htmlspecialchars($store->tax_id); ?>">
            </div>
          </div>
        </div>
      </div>

      <div class="store-card">
        <div class="card-header">Social Links</div>
        <div class="card-body">
          <div class="row g-3">
            <div class="col-md-6"><label class="form-label">Facebook</label><input type="url" name="facebook" class="form-control" value="<?= htmlspecialchars($social['facebook'] ?? ''); ?>"></div>
            <div class="col-md-6"><label class="form-label">Instagram</label><input type="url" name="instagram" class="form-control" value="<?= htmlspecialchars($social['instagram'] ?? ''); ?>"></div>
            <div class="col-md-6"><label class="form-label">Twitter</label><input type="url" name="twitter" class="form-control" value="<?= htmlspecialchars($social['twitter'] ?? ''); ?>"></div>
            <div class="col-md-6"><label class="form-label">LinkedIn</label><input type="url" name="linkedin" class="form-control" value="<?= htmlspecialchars($social['linkedin'] ?? ''); ?>"></div>
          </div>
        </div>
      </div>
    </div>

    <div class="col-lg-4">
      <div class="store-card mb-3">
        <div class="card-header">Logo</div>
        <div class="card-body text-center">
          <?php if (!empty($store->logo)): ?>
            <img src="<?= base_url($store->logo); ?>" alt="Logo" class="img-fluid rounded mb-3" style="max-height:120px">
          <?php endif; ?>
          <input type="file" name="logo" class="form-control" accept="image/*">
        </div>
      </div>
      <button type="submit" class="btn btn-store-primary w-100">Save Profile</button>
    </div>
  </div>
</form>
