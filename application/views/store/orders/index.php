<?php $this->load->view('flash'); ?>
<div class="store-card">
  <div class="card-body p-0">
    <div class="table-responsive">
      <table class="table table-hover mb-0 align-middle">
        <thead>
          <tr>
            <th>Order</th>
            <th>Customer</th>
            <th>Total</th>
            <th>Status</th>
            <th>Date</th>
            <th class="text-end">Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($orders as $order): ?>
          <tr>
            <td class="fw-medium"><?= htmlspecialchars($order->order_no) ?></td>
            <td>
              <?= htmlspecialchars($order->customer_name) ?><br>
              <small class="text-muted"><?= htmlspecialchars($order->customer_email) ?></small>
            </td>
            <td><?= format_money((float) $order->total) ?></td>
            <td><span class="badge text-bg-light border"><?= htmlspecialchars(isset($statuses[$order->status]) ? $statuses[$order->status] : $order->status) ?></span></td>
            <td class="text-muted small"><?= htmlspecialchars($order->created_at) ?></td>
            <td class="text-end"><a class="btn btn-sm btn-outline-secondary" href="<?= $storeUrl ?>/orders/view/<?= (int) $order->id ?>">View</a></td>
          </tr>
          <?php endforeach; ?>
          <?php if (empty($orders)): ?>
          <tr><td colspan="6" class="text-center text-muted py-4">No orders yet.</td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
