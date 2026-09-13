<?php $this->load->view('flash'); ?>
<div class="row g-3">
  <div class="col-lg-4">
    <div class="store-card">
      <div class="card-header">Customer</div>
      <div class="card-body">
        <h5 class="mb-1"><?= htmlspecialchars($customer->name) ?></h5>
        <p class="mb-1"><?= htmlspecialchars($customer->email) ?></p>
        <p class="mb-1"><?= htmlspecialchars($customer->phone ?: '-') ?></p>
        <p class="text-muted small mb-0"><?= nl2br(htmlspecialchars($customer->address ?: 'No address')) ?></p>
      </div>
    </div>
  </div>
  <div class="col-lg-8">
    <div class="store-card">
      <div class="card-header">Orders</div>
      <div class="card-body p-0">
        <div class="table-responsive">
          <table class="table mb-0">
            <thead><tr><th>Order</th><th>Total</th><th>Status</th><th>Date</th><th></th></tr></thead>
            <tbody>
              <?php foreach ($orders as $order): ?>
              <tr>
                <td><?= htmlspecialchars($order->order_no) ?></td>
                <td><?= format_money((float) $order->total) ?></td>
                <td><?= htmlspecialchars($order->status) ?></td>
                <td class="small text-muted"><?= htmlspecialchars($order->created_at) ?></td>
                <td class="text-end"><a href="<?= $storeUrl ?>/orders/view/<?= (int) $order->id ?>">View</a></td>
              </tr>
              <?php endforeach; ?>
              <?php if (empty($orders)): ?>
              <tr><td colspan="5" class="text-center text-muted py-4">No orders from this customer.</td></tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>
