<div class="row wrapper border-bottom white-bg page-heading">
    <div class="col-lg-10">
        <h2>Orders</h2>
        <ol class="breadcrumb">
            <li><a href="<?= base_url(ec_is_admin() ? 'admin/admin' : 'admin/products') ?>">Dashboard</a></li>
            <li class="active"><strong>Orders</strong></li>
        </ol>
    </div>
</div>
<div class="wrapper wrapper-content">
    <?php $this->load->view('flash'); ?>
    <div class="row">
        <div class="col-md-4">
            <div class="ibox"><div class="ibox-content">
                <h5>Platform fee waiting</h5>
                <h2 class="text-warning"><?= format_money($platform_waiting, $platform_currency) ?></h2>
            </div></div>
        </div>
        <div class="col-md-4">
            <div class="ibox"><div class="ibox-content">
                <h5>Ecommerce commission waiting</h5>
                <h2 class="text-warning"><?= format_money($commission_waiting, $platform_currency) ?></h2>
            </div></div>
        </div>
        <div class="col-md-4">
            <div class="ibox"><div class="ibox-content">
                <h5>Orders</h5>
                <h2><?= count($orders) ?></h2>
                <small class="text-muted">Waiting amounts are shown in <?= htmlspecialchars($platform_currency) ?> (platform country). They release when Super Admin marks an order completed.</small>
            </div></div>
        </div>
    </div>

    <div class="ibox">
        <div class="ibox-title"><h5>All Orders</h5></div>
        <div class="ibox-content">
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Order</th>
                            <?php if ($is_admin): ?><th>Store</th><?php endif; ?>
                            <th>Customer</th>
                            <th>Total</th>
                            <th>Status</th>
                            <th>Payout</th>
                            <th>Date</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($orders as $order): ?>
                        <tr>
                            <td><?= htmlspecialchars($order->order_no) ?></td>
                            <?php if ($is_admin): ?><td><?= htmlspecialchars($order->store_name) ?></td><?php endif; ?>
                            <td><?= htmlspecialchars($order->customer_name) ?><br><small><?= htmlspecialchars($order->customer_email) ?></small></td>
                            <td><?= format_money((float) $order->total, $order->currency) ?></td>
                            <td><?= htmlspecialchars(isset($statuses[$order->status]) ? $statuses[$order->status] : $order->status) ?></td>
                            <td>
                                <?php if ($order->payout_status === 'waiting'): ?>
                                    <span class="label label-warning">Waiting</span>
                                <?php elseif ($order->payout_status === 'released'): ?>
                                    <span class="label label-primary">Released</span>
                                <?php else: ?>
                                    <span class="label label-default"><?= htmlspecialchars($order->payout_status) ?></span>
                                <?php endif; ?>
                            </td>
                            <td><?= htmlspecialchars($order->created_at) ?></td>
                            <td><a href="<?= base_url('admin/orders/view/' . $order->id) ?>">View</a></td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if (empty($orders)): ?>
                        <tr><td colspan="<?= $is_admin ? 8 : 7 ?>" class="text-center text-muted">No orders yet.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
