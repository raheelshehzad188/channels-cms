<?php $this->load->view('flash'); ?>

<div class="store-card" style="max-width:720px">
  <div class="card-header"><?= $page; ?></div>
  <div class="card-body">
    <form method="post">
      <div class="row g-3">
        <div class="col-md-6">
          <label class="form-label">Name</label>
          <input type="text" name="name" class="form-control" value="<?= isset($edit) ? htmlspecialchars($edit->name) : set_value('name'); ?>" required>
        </div>
        <div class="col-md-6">
          <label class="form-label">Email</label>
          <input type="email" name="email" class="form-control" value="<?= isset($edit) ? htmlspecialchars($edit->email) : set_value('email'); ?>" required>
        </div>
        <div class="col-md-6">
          <label class="form-label">Phone</label>
          <input type="text" name="phone" class="form-control" value="<?= isset($edit) ? htmlspecialchars($edit->phone) : set_value('phone'); ?>">
        </div>
        <div class="col-md-6">
          <label class="form-label">Role</label>
          <select name="role" class="form-select" required>
            <?php foreach ($roles as $role): if ($role['role_slug'] === 'owner') continue; ?>
            <option value="<?= $role['role_slug']; ?>" <?= (isset($edit) && $edit->role === $role['role_slug']) ? 'selected' : ''; ?>><?= htmlspecialchars($role['role_name']); ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="col-md-6">
          <label class="form-label">Password <?= isset($edit) ? '(leave blank to keep)' : ''; ?></label>
          <input type="password" name="password" class="form-control" <?= isset($edit) ? '' : 'required'; ?>>
        </div>
        <div class="col-md-6">
          <label class="form-label">Confirm Password</label>
          <input type="password" name="confirm_password" class="form-control" <?= isset($edit) ? '' : 'required'; ?>>
        </div>
        <div class="col-md-6">
          <label class="form-label">Status</label>
          <select name="status" class="form-select">
            <option value="1" <?= (!isset($edit) || $edit->status) ? 'selected' : ''; ?>>Active</option>
            <option value="0" <?= (isset($edit) && !$edit->status) ? 'selected' : ''; ?>>Inactive</option>
          </select>
        </div>
        <div class="col-12">
          <label class="form-label">Custom Permissions (JSON, optional)</label>
          <textarea name="permissions" class="form-control" rows="2" placeholder='["dashboard","orders"]'><?= isset($edit) ? htmlspecialchars($edit->permissions) : ''; ?></textarea>
        </div>
      </div>
      <div class="d-flex gap-2 mt-4">
        <button type="submit" class="btn btn-store-primary"><?= isset($edit) ? 'Update' : 'Invite'; ?> Staff</button>
        <a href="<?= $storeUrl; ?>/staff" class="btn btn-outline-secondary">Cancel</a>
      </div>
    </form>
  </div>
</div>
