<?php $this->load->view('flash'); ?>
<div class="store-card">
  <div class="card-body p-0">
    <div class="table-responsive">
      <table class="table table-hover mb-0 align-middle">
        <thead>
          <tr>
            <th>Name</th>
            <th>Email</th>
            <th>Phone</th>
            <th>Orders</th>
            <th>Spent</th>
            <th>Joined</th>
            <th class="text-end">Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($customers as $customer): ?>
          <tr>
            <td class="fw-medium"><?= htmlspecialchars($customer->name) ?></td>
            <td><?= htmlspecialchars($customer->email) ?></td>
            <td><?= htmlspecialchars($customer->phone ?: '-') ?></td>
            <td><?= (int) $customer->order_count ?></td>
            <td><?= format_money((float) $customer->spent) ?></td>
            <td class="text-muted small"><?= htmlspecialchars($customer->created_at) ?></td>
            <td class="text-end"><a class="btn btn-sm btn-outline-secondary" href="<?= $storeUrl ?>/customers/view/<?= (int) $customer->id ?>">View</a></td>
          </tr>
          <?php endforeach; ?>
          <?php if (empty($customers)): ?>
          <tr><td colspan="7" class="text-center text-muted py-4">No customers yet.</td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
