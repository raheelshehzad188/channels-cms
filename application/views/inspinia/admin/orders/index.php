<?php
$summary = isset($summary) ? $summary : (object) array();
$currency = isset($platform_currency) ? $platform_currency : platform_currency();
$filterCountryId = isset($country_id) ? (int) $country_id : 0;
$filterStoreId = isset($store_id) ? (int) $store_id : 0;
$filterEcommerceId = isset($ecommerce_user_id) ? (int) $ecommerce_user_id : 0;
$money = function ($key) use ($summary, $currency) {
    $value = isset($summary->$key) ? $summary->$key : 0;
    return format_money((float) $value, $currency);
};
?>
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

    <?php if (!empty($is_admin)): ?>
    <div class="ibox">
        <div class="ibox-title"><h5>Filters</h5></div>
        <div class="ibox-content">
            <form method="get" action="<?= base_url('admin/orders') ?>" class="form-inline" id="orderFilterForm">
                <div class="form-group" style="margin-right:8px; margin-bottom:8px;">
                    <select name="country_id" id="orderFilterCountry" class="form-control">
                        <option value="0">All countries</option>
                        <?php foreach (!empty($countries) ? $countries : array() as $country): ?>
                            <option value="<?= (int) $country->id ?>" <?= $filterCountryId === (int) $country->id ? 'selected' : '' ?>><?= htmlspecialchars($country->name) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group" style="margin-right:8px; margin-bottom:8px;">
                    <select name="store_id" id="orderFilterStore" class="form-control">
                        <option value="0">All stores</option>
                        <?php foreach (!empty($stores) ? $stores : array() as $store): ?>
                            <option value="<?= (int) $store->id ?>" <?= $filterStoreId === (int) $store->id ? 'selected' : '' ?>><?= htmlspecialchars($store->name) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group" style="margin-right:8px; margin-bottom:8px;">
                    <select name="ecommerce_user_id" id="orderFilterEcommerce" class="form-control">
                        <option value="0">All ecommerce users</option>
                        <?php foreach (!empty($ecommerce_users) ? $ecommerce_users : array() as $owner): ?>
                            <?php $label = trim(($owner->first_name ? $owner->first_name : '') . ' ' . ($owner->last_name ? $owner->last_name : '')); ?>
                            <option value="<?= (int) $owner->UserID ?>" <?= $filterEcommerceId === (int) $owner->UserID ? 'selected' : '' ?>>
                                <?= htmlspecialchars(($label !== '' ? $label : $owner->uname) . ' (' . $owner->uname . ')') ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <button type="submit" class="btn btn-primary" style="margin-bottom:8px;">Filter</button>
                <a href="<?= base_url('admin/orders') ?>" class="btn btn-white" style="margin-bottom:8px;">Reset</a>
            </form>
        </div>
    </div>
    <?php endif; ?>

    <div class="row">
        <div class="col-sm-6 col-md-4">
            <div class="ibox"><div class="ibox-content">
                <h5>Store commission</h5>
                <h2><?= $money('store_commission') ?></h2>
                <small class="text-muted">Store plus / markup on filtered orders</small>
            </div></div>
        </div>
        <div class="col-sm-6 col-md-4">
            <div class="ibox"><div class="ibox-content">
                <h5>Ecommerce commission</h5>
                <h2><?= $money('ecommerce_commission') ?></h2>
                <small class="text-muted">Filtered order items</small>
            </div></div>
        </div>
        <div class="col-sm-6 col-md-4">
            <div class="ibox"><div class="ibox-content">
                <h5>Platform commission</h5>
                <h2><?= $money('platform_commission') ?></h2>
                <small class="text-muted">Filtered order items</small>
            </div></div>
        </div>
        <div class="col-sm-6 col-md-4">
            <div class="ibox"><div class="ibox-content">
                <h5>Total cost</h5>
                <h2><?= $money('total_cost') ?></h2>
                <small class="text-muted">Catalog base on filtered items</small>
            </div></div>
        </div>
        <div class="col-sm-6 col-md-4">
            <div class="ibox"><div class="ibox-content">
                <h5>Total sale</h5>
                <h2 class="text-navy"><?= $money('total_sale') ?></h2>
                <small class="text-muted">Customer item totals</small>
            </div></div>
        </div>
        <div class="col-sm-6 col-md-4">
            <div class="ibox"><div class="ibox-content">
                <h5>Total profit</h5>
                <h2 class="text-navy"><?= $money('total_profit') ?></h2>
                <small class="text-muted">Sale − cost · <?= htmlspecialchars($currency) ?></small>
            </div></div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-4">
            <div class="ibox"><div class="ibox-content">
                <h5>Platform fee waiting</h5>
                <h2 class="text-warning"><?= format_money($platform_waiting, $currency) ?></h2>
            </div></div>
        </div>
        <div class="col-md-4">
            <div class="ibox"><div class="ibox-content">
                <h5>Ecommerce commission waiting</h5>
                <h2 class="text-warning"><?= format_money($commission_waiting, $currency) ?></h2>
            </div></div>
        </div>
        <div class="col-md-4">
            <div class="ibox"><div class="ibox-content">
                <h5>Orders</h5>
                <h2><?= isset($summary->order_count) ? (int) $summary->order_count : count($orders) ?></h2>
                <small class="text-muted">Cards follow the current filters. Amounts are in <?= htmlspecialchars($currency) ?>.</small>
            </div></div>
        </div>
    </div>

    <div class="ibox">
        <div class="ibox-title"><h5><?= !empty($is_admin) ? 'All Orders' : 'Order items' ?></h5></div>
        <div class="ibox-content">
            <div class="table-responsive">
                <?php if (empty($is_admin)): ?>
                <?php
                $orderItems = isset($order_items) ? $order_items : array();
                $itemStatuses = isset($item_statuses) ? $item_statuses : Ec_order_model::item_statuses();
                $shipping_companies = isset($shipping_companies) ? $shipping_companies : Ec_order_model::shipping_companies();
                ?>
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Order</th>
                            <th>Product</th>
                            <th>Qty</th>
                            <th>Customer</th>
                            <th>Line total</th>
                            <th>Status</th>
                            <th>Date</th>
                            <th style="min-width:280px">Fulfill</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($orderItems as $item): ?>
                        <?php
                        $itemStatus = !empty($item->fulfillment_status) ? $item->fulfillment_status : 'pending';
                        ?>
                        <tr>
                            <td><?= htmlspecialchars($item->order_no) ?></td>
                            <td>
                                <?= htmlspecialchars($item->product_name) ?>
                                <?php if (!empty($item->sku)):
                                    $editProductId = !empty($item->product_id) ? (int) $item->product_id : (!empty($item->linked_product_id) ? (int) $item->linked_product_id : 0);
                                ?><br><small class="text-muted">SKU: <?php if ($editProductId > 0): ?><a href="<?= base_url('admin/products/form/' . $editProductId) ?>"><?= htmlspecialchars($item->sku) ?></a><?php else: ?><?= htmlspecialchars($item->sku) ?><?php endif; ?></small><?php endif; ?>
                            </td>
                            <td><?= (int) $item->qty ?></td>
                            <td><?= htmlspecialchars($item->customer_name) ?><br><small><?= htmlspecialchars($item->customer_email) ?></small></td>
                            <td><?= format_money((float) $item->line_total, $item->currency) ?></td>
                            <td><?= htmlspecialchars(isset($itemStatuses[$itemStatus]) ? $itemStatuses[$itemStatus] : $itemStatus) ?></td>
                            <td><?= htmlspecialchars($item->order_created_at) ?></td>
                            <td>
                                <?php $this->load->view('inspinia/admin/orders/_item_fulfill', array(
                                    'item' => $item,
                                    'can_process' => true,
                                    'is_admin' => false,
                                    'redirect' => '',
                                    'shipping_companies' => $shipping_companies,
                                )); ?>
                                <div style="margin-top:6px"><a class="btn btn-xs btn-white" href="<?= base_url('admin/orders/view/' . (int) $item->order_id) ?>">View</a></div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if (empty($orderItems)): ?>
                        <tr><td colspan="8" class="text-center text-muted">No order items yet.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
                <?php else: ?>
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
                        <tr><td colspan="<?= $is_admin ? 8 : 7 ?>" class="text-center text-muted">No orders match these filters.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
<?php if (!empty($is_admin)): ?>
<script>
(function ($) {
    $('#orderFilterCountry').on('change', function () {
        $('#orderFilterStore').val('0');
        $('#orderFilterForm').submit();
    });
    $('#orderFilterStore, #orderFilterEcommerce').on('change', function () {
        $('#orderFilterForm').submit();
    });
})(jQuery);
</script>
<?php endif; ?>
