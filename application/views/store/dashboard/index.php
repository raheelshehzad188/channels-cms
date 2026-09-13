<?php $this->load->view('flash'); ?>

<div class="row g-3 mb-4">
  <div class="col-md-4 col-sm-6">
    <div class="stat-card">
      <div class="label">Total Products</div>
      <div class="value"><?= number_format($stats['products']); ?></div>
    </div>
  </div>
  <div class="col-md-4 col-sm-6">
    <div class="stat-card">
      <div class="label">Total Orders</div>
      <div class="value"><?= number_format($stats['orders']); ?></div>
    </div>
  </div>
  <div class="col-md-4 col-sm-6">
    <div class="stat-card">
      <div class="label">Customers</div>
      <div class="value"><?= number_format($stats['customers']); ?></div>
    </div>
  </div>
  <div class="col-md-4 col-sm-6">
    <div class="stat-card">
      <div class="label">Your plus</div>
      <div class="value"><?= format_money($stats['markup_earnings']); ?></div>
      <div class="small text-muted mt-1">Only the add-on you set on products</div>
    </div>
  </div>
</div>

<div class="row g-3">
  <div class="col-lg-8">
    <div class="store-card">
      <div class="card-header d-flex justify-content-between align-items-center">
        <span>Recent Orders</span>
        <a href="<?= $storeUrl ?>/orders" class="small">View all</a>
      </div>
      <div class="card-body p-0">
        <div class="table-responsive">
          <table class="table table-hover mb-0">
            <thead>
              <tr>
                <th>Order</th>
                <th>Customer</th>
                <th>Total</th>
                <th>Status</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($recent_orders as $order): ?>
              <tr>
                <td><a href="<?= $storeUrl ?>/orders/view/<?= (int) $order->id ?>"><?= htmlspecialchars($order->order_no) ?></a></td>
                <td><?= htmlspecialchars($order->customer_name) ?></td>
                <td><?= format_money((float) $order->total) ?></td>
                <td><?= htmlspecialchars($order->status) ?></td>
              </tr>
              <?php endforeach; ?>
              <?php if (empty($recent_orders)): ?>
              <tr>
                <td colspan="4" class="text-center text-muted py-4">No orders yet</td>
              </tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
  <div class="col-lg-4">
    <div class="store-card">
      <div class="card-header d-flex justify-content-between align-items-center">
        <span>Recent Customers</span>
        <a href="<?= $storeUrl ?>/customers" class="small">View all</a>
      </div>
      <div class="card-body">
        <?php if (empty($recent_customers)): ?>
          <p class="text-muted mb-0 small">No customers yet</p>
        <?php else: ?>
          <ul class="list-unstyled mb-0">
            <?php foreach ($recent_customers as $customer): ?>
              <li class="mb-2">
                <a href="<?= $storeUrl ?>/customers/view/<?= (int) $customer->id ?>"><?= htmlspecialchars($customer->name) ?></a>
                <div class="small text-muted"><?= htmlspecialchars($customer->email) ?></div>
              </li>
            <?php endforeach; ?>
          </ul>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>
