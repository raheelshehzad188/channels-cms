<?php $this->load->view('flash'); ?>
<div class="row g-3">
  <div class="col-lg-8">
    <div class="store-card mb-3">
      <div class="card-header d-flex justify-content-between">
        <span>Order <?= htmlspecialchars($order->order_no) ?></span>
        <span class="badge text-bg-light border"><?= htmlspecialchars($statuses[$order->status]) ?></span>
      </div>
      <div class="card-body p-0">
        <div class="table-responsive">
          <table class="table mb-0">
            <thead><tr><th>Product</th><th>Qty</th><th>Price</th><th>Total</th></tr></thead>
            <tbody>
              <?php foreach ($items as $item): ?>
              <tr>
                <td><?= htmlspecialchars($item->product_name) ?><br><small class="text-muted">SKU: <?= htmlspecialchars($item->sku ?: '-') ?></small></td>
                <td><?= (int) $item->qty ?></td>
                <td><?= format_money((float) $item->unit_price) ?></td>
                <td><?= format_money((float) $item->line_total) ?></td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <div class="store-card">
      <div class="card-header">Status history</div>
      <div class="card-body">
        <?php if (empty($logs)): ?>
          <p class="text-muted mb-0">No history yet.</p>
        <?php else: ?>
          <ul class="list-unstyled mb-0">
            <?php foreach ($logs as $log): ?>
              <li class="mb-2">
                <strong><?= htmlspecialchars(isset($statuses[$log->status]) ? $statuses[$log->status] : $log->status) ?></strong>
                <span class="text-muted small">· <?= htmlspecialchars($log->created_at) ?></span>
                <div class="small"><?= htmlspecialchars($log->note) ?></div>
              </li>
            <?php endforeach; ?>
          </ul>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <div class="col-lg-4">
    <div class="store-card mb-3">
      <div class="card-header">Customer</div>
      <div class="card-body small">
        <div><strong><?= htmlspecialchars($order->customer_name) ?></strong></div>
        <div><?= htmlspecialchars($order->customer_email) ?></div>
        <div><?= htmlspecialchars($order->customer_phone ?: '-') ?></div>
        <hr>
        <div><?= nl2br(htmlspecialchars($order->shipping_address)) ?></div>
      </div>
    </div>

    <div class="store-card mb-3">
      <div class="card-header">Order totals</div>
      <div class="card-body small">
        <?php
        $yourMarkup = 0;
        foreach ($items as $item) {
            $yourMarkup += isset($item->store_markup) ? (float) $item->store_markup : 0;
        }
        ?>
        <div class="d-flex justify-content-between"><span>Order subtotal</span><strong><?= format_money((float) $order->subtotal) ?></strong></div>
        <?php if (!empty($order->vat_amount) && (float) $order->vat_amount > 0): ?>
        <div class="d-flex justify-content-between"><span>VAT (<?= number_format((float) $order->vat_percent, 2) ?>%)</span><strong><?= format_money((float) $order->vat_amount) ?></strong></div>
        <?php endif; ?>
        <div class="d-flex justify-content-between"><span>Order total</span><strong><?= format_money((float) $order->total) ?></strong></div>
        <hr>
        <div class="d-flex justify-content-between"><span>Your plus</span><strong><?= format_money($yourMarkup) ?></strong></div>
        <p class="text-muted mb-0 mt-2">Only the add-on you set on products in this order.</p>
      </div>
    </div>

    <?php if ($order->status !== 'completed'): ?>
    <div class="store-card">
      <div class="card-header">Update status</div>
      <div class="card-body">
        <form method="post" action="<?= $storeUrl ?>/orders/status/<?= (int) $order->id ?>">
          <label class="form-label">Status</label>
          <select name="status" class="form-select mb-2" required>
            <?php foreach ($store_actions as $key): ?>
              <option value="<?= $key ?>" <?= $order->status === $key ? 'selected' : '' ?>><?= htmlspecialchars($statuses[$key]) ?></option>
            <?php endforeach; ?>
          </select>
          <label class="form-label">Note</label>
          <input type="text" name="note" class="form-control mb-3" placeholder="Optional note">
          <button class="btn btn-store-primary w-100" type="submit">Save &amp; email everyone</button>
        </form>
      </div>
    </div>
    <?php endif; ?>
  </div>
</div>
