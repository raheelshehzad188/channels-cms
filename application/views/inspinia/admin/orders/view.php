<div class="row wrapper border-bottom white-bg page-heading">
    <div class="col-lg-10">
        <h2>Order <?= htmlspecialchars($order->order_no) ?></h2>
        <ol class="breadcrumb">
            <li><a href="<?= base_url('admin/orders') ?>">Orders</a></li>
            <li class="active"><strong><?= htmlspecialchars($order->order_no) ?></strong></li>
        </ol>
    </div>
</div>
<div class="wrapper wrapper-content">
    <?php $this->load->view('flash'); ?>
    <div class="row">
        <div class="col-lg-8">
            <div class="ibox">
                <div class="ibox-title">
                    <h5>Items</h5>
                    <span class="label label-info pull-right"><?= htmlspecialchars($statuses[$order->status]) ?></span>
                </div>
                <div class="ibox-content">
                    <table class="table">
                        <thead><tr><th>Product</th><th>Qty</th><th>Unit</th><th>Platform</th><th>Commission</th><th>Total</th><th>Status</th><th></th></tr></thead>
                        <tbody>
                            <?php
                            $itemStatuses = isset($item_statuses) ? $item_statuses : Ec_order_model::item_statuses();
                            $ecommerceUserId = isset($ecommerce_user_id) ? (int) $ecommerce_user_id : 0;
                            foreach ($items as $item):
                                $itemStatus = !empty($item->fulfillment_status) ? $item->fulfillment_status : 'pending';
                                $canProcess = !empty($is_admin) || (int) $item->ecommerce_user_id === $ecommerceUserId;
                            ?>
                            <tr>
                                <td><?= htmlspecialchars($item->product_name) ?><?php if (!empty($item->sku)):
                                    $editProductId = !empty($item->product_id) ? (int) $item->product_id : (!empty($item->linked_product_id) ? (int) $item->linked_product_id : 0);
                                ?><br><small class="text-muted">SKU: <?php if ($editProductId > 0): ?><a href="<?= base_url('admin/products/form/' . $editProductId) ?>"><?= htmlspecialchars($item->sku) ?></a><?php else: ?><?= htmlspecialchars($item->sku) ?><?php endif; ?></small><?php endif; ?></td>
                                <td><?= (int) $item->qty ?></td>
                                <td><?= format_money((float) $item->unit_price, $order->currency) ?></td>
                                <td><?= format_money((float) $item->platform_fee, $order->currency) ?></td>
                                <td><?= format_money((float) $item->commission, $order->currency) ?></td>
                                <td><?= format_money((float) $item->line_total, $order->currency) ?></td>
                                <td><?= htmlspecialchars(isset($itemStatuses[$itemStatus]) ? $itemStatuses[$itemStatus] : $itemStatus) ?></td>
                                <td>
                                    <?php $this->load->view('inspinia/admin/orders/_item_fulfill', array(
                                        'item' => $item,
                                        'can_process' => $canProcess,
                                        'is_admin' => !empty($is_admin),
                                        'redirect' => 'view',
                                        'shipping_companies' => isset($shipping_companies) ? $shipping_companies : Ec_order_model::shipping_companies(),
                                    )); ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="ibox">
                <div class="ibox-title"><h5>Status history</h5></div>
                <div class="ibox-content">
                    <?php foreach ($logs as $log): ?>
                        <p><strong><?= htmlspecialchars(isset($statuses[$log->status]) ? $statuses[$log->status] : $log->status) ?></strong>
                            <small class="text-muted"><?= htmlspecialchars($log->created_at) ?> · <?= htmlspecialchars($log->created_by_type) ?></small><br>
                            <?= htmlspecialchars($log->note) ?></p>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="ibox">
                <div class="ibox-title"><h5>Summary</h5></div>
                <div class="ibox-content">
                    <p><strong>Store:</strong> <?= htmlspecialchars($order->store_name) ?></p>
                    <p><strong>Customer:</strong> <?= htmlspecialchars($order->customer_name) ?><br><?= htmlspecialchars($order->customer_email) ?></p>
                    <p><strong>Subtotal:</strong> <?= format_money((float) $order->subtotal, $order->currency) ?></p>
                    <?php if (!empty($order->shipping_amount) && ((float) $order->shipping_amount > 0 || (int) (isset($order->shipping_qty) ? $order->shipping_qty : 0) > 0)): ?>
                    <p><strong>Shipping × <?= (int) (isset($order->shipping_qty) ? $order->shipping_qty : 0) ?>:</strong> <?= format_money((float) $order->shipping_amount, $order->currency) ?></p>
                    <?php endif; ?>
                    <?php if (!empty($order->vat_amount) && (float) $order->vat_amount > 0): ?>
                    <p><strong>VAT (<?= number_format((float) $order->vat_percent, 2) ?>%):</strong> <?= format_money((float) $order->vat_amount, $order->currency) ?></p>
                    <?php endif; ?>
                    <p><strong>Total:</strong> <?= format_money((float) $order->total, $order->currency) ?></p>
                    <?php if (!empty($order->payment_method)): ?>
                    <p><strong>Payment:</strong> <?= htmlspecialchars($order->payment_method) ?> · <?= htmlspecialchars(isset($order->payment_status) ? $order->payment_status : '') ?>
                      <?php if (!empty($order->paid_amount)): ?>
                        (<?= format_money((float) $order->paid_amount, $order->payment_currency ?: $order->currency) ?>)
                      <?php endif; ?>
                    </p>
                    <?php if (!empty($order->paypal_order_id)): ?>
                    <p><strong>PayPal ref:</strong> <?= htmlspecialchars($order->paypal_order_id) ?></p>
                    <?php endif; ?>
                    <?php endif; ?>
                    <p><strong>Platform fee:</strong> <?= format_money((float) $order->platform_fee_total, $order->currency) ?>
                      <?php if (!empty($platform_currency) && strtoupper((string) $order->currency) !== strtoupper((string) $platform_currency)): ?>
                        <br><small class="text-muted"><?= format_money(order_amount_in_platform($order, 'platform_fee'), $platform_currency) ?> platform</small>
                      <?php endif; ?>
                    </p>
                    <p><strong>Commission:</strong> <?= format_money((float) $order->commission_total, $order->currency) ?>
                      <?php if (!empty($platform_currency) && strtoupper((string) $order->currency) !== strtoupper((string) $platform_currency)): ?>
                        <br><small class="text-muted"><?= format_money(order_amount_in_platform($order, 'commission'), $platform_currency) ?> platform</small>
                      <?php endif; ?>
                    </p>
                    <p><strong>Payout:</strong> <?= htmlspecialchars($order->payout_status) ?></p>
                    <p><?= nl2br(htmlspecialchars($order->shipping_address)) ?></p>
                    <?php if (!empty($order->billing_address)): ?>
                    <p><strong>Billing</strong><br><?= nl2br(htmlspecialchars($order->billing_address)) ?></p>
                    <?php endif; ?>
                </div>
            </div>
            <?php if ($is_admin): ?>
            <div class="ibox">
                <div class="ibox-title"><h5>Admin actions</h5></div>
                <div class="ibox-content">
                    <?php if ($order->status !== 'completed' && $order->status !== 'cancelled'): ?>
                        <a href="<?= base_url('admin/orders/complete/' . $order->id) ?>" class="btn btn-primary btn-block"
                           onclick="return confirm('Mark complete and release waiting platform/ecommerce payouts?');">
                            Mark as Complete (release payouts)
                        </a>
                        <hr>
                    <?php endif; ?>
                    <form method="post" action="<?= base_url('admin/orders/status/' . $order->id) ?>">
                        <div class="form-group">
                            <label>Set status</label>
                            <select name="status" class="form-control">
                                <?php foreach ($statuses as $key => $label): ?>
                                    <option value="<?= $key ?>" <?= $order->status === $key ? 'selected' : '' ?>><?= htmlspecialchars($label) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Note</label>
                            <input type="text" name="note" class="form-control">
                        </div>
                        <button type="submit" class="btn btn-white btn-block">Update &amp; email</button>
                    </form>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>
