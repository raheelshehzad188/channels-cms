<?php $this->load->view('flash'); ?>

<div class="d-flex justify-content-between align-items-center mb-3">
  <p class="text-muted mb-0">Manage staff members and their roles</p>
  <a href="<?= $storeUrl; ?>/staff/create" class="btn btn-store-primary"><i class="bi bi-plus-lg me-1"></i> Invite Staff</a>
</div>

<div class="store-card">
  <div class="card-body p-0">
    <div class="table-responsive">
      <table class="table table-hover mb-0 align-middle">
        <thead>
          <tr>
            <th>Name</th>
            <th>Email</th>
            <th>Phone</th>
            <th>Role</th>
            <th>Status</th>
            <th class="text-end">Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($staff_list as $member): ?>
          <tr>
            <td class="fw-medium"><?= htmlspecialchars($member['name']); ?></td>
            <td><?= htmlspecialchars($member['email']); ?></td>
            <td><?= htmlspecialchars($member['phone'] ?: '-'); ?></td>
            <td><span class="badge text-bg-light border text-capitalize"><?= htmlspecialchars($member['role']); ?></span></td>
            <td><?= $member['status'] ? '<span class="badge text-bg-success">Active</span>' : '<span class="badge text-bg-secondary">Inactive</span>'; ?></td>
            <td class="text-end">
              <a href="<?= $storeUrl; ?>/staff/create/<?= $member['id']; ?>" class="btn btn-sm btn-outline-secondary">Edit</a>
              <a href="<?= $storeUrl; ?>/staff/delete/<?= $member['id']; ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Remove this staff member?');">Remove</a>
            </td>
          </tr>
          <?php endforeach; ?>
          <?php if (empty($staff_list)): ?>
          <tr><td colspan="6" class="text-center text-muted py-4">No staff members yet. Invite your first team member.</td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
