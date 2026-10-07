<?php
$f = $filters;
$qs = http_build_query(array_filter(array(
    'range' => $f['range'],
    'from' => $f['from'],
    'to' => $f['to'],
    'store_id' => $f['store_id'] ? $f['store_id'] : null,
    'country_code' => $f['country_code'] ? $f['country_code'] : null,
    'product_id' => $f['product_id'] ? $f['product_id'] : null,
)));
$liveUrl = base_url('admin/store-analytics/live') . ($qs ? ('?' . $qs) : '');
$s = $summary;
if (!function_exists('sa_sort_link')) {
function sa_sort_link($col, $label, $filters) {
    $dir = ($filters['sort'] === $col && $filters['dir'] !== 'asc') ? 'asc' : 'desc';
    $params = $filters;
    $params['sort'] = $col;
    $params['dir'] = $dir;
    $url = base_url('admin/store-analytics') . '?' . http_build_query(array_filter($params));
    return '<a href="' . $url . '">' . $label . '</a>';
}
function sa_page_label($url, $title, $productName) {
    if ($productName) {
        return $productName;
    }
    if ($title) {
        return $title;
    }
    $path = (string) parse_url((string) $url, PHP_URL_PATH);
    if (strpos($path, '/cart') !== false) {
        return 'Cart';
    }
    if (strpos($path, '/checkout') !== false) {
        return 'Checkout';
    }
    if (strpos($path, '/payment') !== false) {
        return 'Payment';
    }
    if (strpos($path, '/product') !== false) {
        return 'Product';
    }
    return $path !== '' ? $path : 'Storefront';
}
}
?>
<style>
.sa-metric h2 { margin: 4px 0 0; }
.sa-dot { display:inline-block; width:8px; height:8px; border-radius:50%; background:#1ab394; margin-right:6px; }
.sa-feed { max-height: 420px; overflow:auto; }
.sa-feed-item { padding:10px 0; border-bottom:1px solid #e7eaec; }
.sa-funnel { list-style:none; padding:0; }
.sa-funnel li { margin:0 0 8px; }
.sa-funnel .bar { height:18px; background:#1ab394; border-radius:2px; min-width:4px; }
.sa-live-product { padding:8px 0; border-bottom:1px solid #eee; }
.sa-table td, .sa-table th { white-space: nowrap; }
.sa-alert { margin:0 0 6px; }
.sa-filters select.form-control, .sa-filters input.form-control { width: auto !important; display: inline-block !important; min-width: 140px; max-width: 220px; margin: 0 6px 6px 0; }
.sa-filters .btn { margin: 0 6px 6px 0; }
</style>
<div class="row wrapper border-bottom white-bg page-heading">
    <div class="col-lg-12">
        <h2>Live Store Analytics</h2>
        <ol class="breadcrumb">
            <?php if (ec_is_admin()): ?>
            <li><a href="<?= base_url('admin/admin') ?>">Dashboard</a></li>
            <?php endif; ?>
            <li class="active"><strong>Live Store Analytics</strong></li>
        </ol>
        <form method="get" action="<?= base_url('admin/store-analytics') ?>" class="form-inline sa-filters" style="margin:12px 0 8px;">
            <select name="range" class="form-control input-sm" id="saRange">
                <option value="today" <?= $f['range'] === 'today' ? 'selected' : '' ?>>Today</option>
                <option value="yesterday" <?= $f['range'] === 'yesterday' ? 'selected' : '' ?>>Yesterday</option>
                <option value="7d" <?= $f['range'] === '7d' ? 'selected' : '' ?>>Last 7 days</option>
                <option value="30d" <?= $f['range'] === '30d' ? 'selected' : '' ?>>Last 30 days</option>
                <option value="month" <?= $f['range'] === 'month' ? 'selected' : '' ?>>This month</option>
                <option value="custom" <?= $f['range'] === 'custom' ? 'selected' : '' ?>>Custom range</option>
            </select>
            <label class="control-label" style="margin:0 4px 6px 0;font-weight:normal;color:#676a6c;">From</label>
            <input type="date" name="from" id="saFrom" class="form-control input-sm" value="<?= htmlspecialchars($f['from']) ?>">
            <label class="control-label" style="margin:0 4px 6px 0;font-weight:normal;color:#676a6c;">To</label>
            <input type="date" name="to" id="saTo" class="form-control input-sm" value="<?= htmlspecialchars($f['to']) ?>">
            <select name="store_id" class="form-control input-sm">
                <option value="">All stores</option>
                <?php foreach ($all_stores as $store): ?>
                <option value="<?= (int) $store->id ?>" <?= (int) $f['store_id'] === (int) $store->id ? 'selected' : '' ?>><?= htmlspecialchars($store->name) ?></option>
                <?php endforeach; ?>
            </select>
            <select name="country_code" class="form-control input-sm">
                <option value="">All countries</option>
                <?php foreach ($all_countries as $country): ?>
                <option value="<?= htmlspecialchars(strtoupper($country->code)) ?>" <?= $f['country_code'] === strtoupper($country->code) ? 'selected' : '' ?>><?= htmlspecialchars($country->name) ?></option>
                <?php endforeach; ?>
            </select>
            <?php if ($f['product_id']): ?><input type="hidden" name="product_id" value="<?= (int) $f['product_id'] ?>"><?php endif; ?>
            <button class="btn btn-primary btn-sm" type="submit">Apply</button>
            <button class="btn btn-white btn-sm" type="button" id="saRefresh">Refresh</button>
            <a class="btn btn-white btn-sm" href="<?= base_url('admin/store-analytics') ?>">Reset</a>
            <?php if (ec_is_admin()): ?>
            <button class="btn btn-danger btn-sm" type="button" id="saClearDataBtn" title="Delete all analytics visitors, sessions, events and summaries">Clear old data</button>
            <?php endif; ?>
        </form>
        <?php if (ec_is_admin()): ?>
        <form method="post" action="<?= base_url('admin/store-analytics/clear-data') ?>" id="saClearDataForm" style="display:none;">
            <input type="hidden" name="confirm" value="CLEAR">
        </form>
        <?php endif; ?>
    </div>
</div>
<div class="wrapper wrapper-content">
    <?php $this->load->view('flash'); ?>

    <div class="alert alert-info" id="saAlerts">
        <?php foreach ($alerts as $alert): ?>
        <div class="sa-alert"><?= htmlspecialchars($alert) ?></div>
        <?php endforeach; ?>
    </div>

    <div class="row">
        <div class="col-md-3"><div class="ibox"><div class="ibox-content sa-metric"><h5><?= $f['range'] === 'today' ? "Today's visitors" : 'Visitors' ?></h5><h2 id="saVisitors"><?= sa_int($s['visitors']) ?></h2><small class="text-muted">Sessions in range</small></div></div></div>
        <div class="col-md-3"><div class="ibox"><div class="ibox-content sa-metric"><h5>Unique visitors</h5><h2 id="saUnique"><?= sa_int($s['unique']) ?></h2></div></div></div>
        <div class="col-md-3"><div class="ibox"><div class="ibox-content sa-metric"><h5>Currently online</h5><h2 class="text-navy"><span class="sa-dot"></span><span id="saOnline"><?= sa_int($s['online']) ?></span></h2><small class="text-muted" id="saUpdated">Updated just now</small></div></div></div>
        <div class="col-md-3"><div class="ibox"><div class="ibox-content sa-metric"><h5>Product views</h5><h2 id="saViews"><?= sa_int($s['product_views']) ?></h2></div></div></div>
        <div class="col-md-3"><div class="ibox"><div class="ibox-content sa-metric"><h5>Add to cart</h5><h2 id="saCarts"><?= sa_int($s['add_to_cart']) ?></h2></div></div></div>
        <div class="col-md-3"><div class="ibox"><div class="ibox-content sa-metric"><h5>Checkout started</h5><h2 id="saCheckout"><?= sa_int($s['checkout']) ?></h2></div></div></div>
        <div class="col-md-3"><div class="ibox"><div class="ibox-content sa-metric"><h5>Orders</h5><h2 id="saOrders"><?= sa_int($s['orders']) ?></h2></div></div></div>
        <div class="col-md-3"><div class="ibox"><div class="ibox-content sa-metric"><h5>Conversion rate</h5><h2 id="saConv"><?= number_format((float) $s['conversion'], 2) ?>%</h2><small class="text-muted">Orders / unique visitors</small></div></div></div>
    </div>

    <div class="ibox">
        <div class="ibox-title"><h5>Top countries today</h5></div>
        <div class="ibox-content" id="saTopCountries">
            <?php foreach (array_slice($countries, 0, 8) as $row): ?>
            <a class="btn btn-white btn-sm" style="margin:0 8px 8px 0;" href="<?= base_url('admin/store-analytics') ?>?<?= http_build_query(array_filter(array('range' => $f['range'], 'store_id' => $f['store_id'] ?: null, 'country_code' => $row->country_code, 'product_id' => $f['product_id'] ?: null))) ?>">
                <?= sa_flag($row->country_code) ?> <?= htmlspecialchars($row->country) ?>
                · <?= sa_int($row->visitors) ?> visitors
                · <?= (int) $row->online ?> online
                · <?= sa_int($row->purchases) ?> orders
            </a>
            <?php endforeach; ?>
            <?php if (empty($countries)): ?><span class="text-muted">No country traffic yet.</span><?php endif; ?>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-5">
            <div class="ibox">
                <div class="ibox-title"><h5>Live now</h5><span class="pull-right text-navy"><span class="sa-dot"></span><span id="saOnlineLabel"><?= (int) $s['online'] ?> visitors online now</span></span></div>
                <div class="ibox-content">
                    <h4>Products being viewed right now</h4>
                    <div id="saLiveProducts">
                        <?php foreach ($live_products as $item): ?>
                        <div class="sa-live-product">
                            <a href="#" class="sa-product" data-id="<?= (int) $item->product_id ?>"><?= htmlspecialchars($item->product_name) ?></a>
                            <span class="pull-right"><strong><?= (int) $item->active_visitors ?></strong> active visitors</span>
                        </div>
                        <?php endforeach; ?>
                        <?php if (empty($live_products)): ?><p class="text-muted">No product pages are active right now.</p><?php endif; ?>
                    </div>
                    <h4 class="m-t">Current visitors</h4>
                    <div class="table-responsive" style="max-height:220px; overflow:auto;">
                        <table class="table table-condensed" id="saLiveSessions">
                            <thead><tr><th>Country</th><th>Viewing</th><th>Source</th><th>Device</th><th>Last activity</th></tr></thead>
                            <tbody>
                                <?php foreach ($live_sessions as $row): ?>
                                <tr>
                                    <td><a href="#" class="sa-session" data-id="<?= htmlspecialchars($row->session_id) ?>"><?= sa_flag($row->country_code) ?> <?= htmlspecialchars($row->country ?: 'Unknown') ?></a></td>
                                    <td><?= htmlspecialchars($row->product_name ?: sa_page_label($row->current_page, '', '')) ?></td>
                                    <td><?= htmlspecialchars(sa_source_label($row->traffic_source)) ?></td>
                                    <td><?= htmlspecialchars(ucfirst($row->device)) ?></td>
                                    <td><?= htmlspecialchars(sa_ago($row->last_activity)) ?></td>
                                </tr>
                                <?php endforeach; ?>
                                <?php if (empty($live_sessions)): ?><tr><td colspan="5" class="text-muted">No active sessions.</td></tr><?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="ibox">
                <div class="ibox-title"><h5>Visitor activity</h5></div>
                <div class="ibox-content sa-feed" id="saFeed">
                    <?php foreach ($feed as $row): ?>
                    <div class="sa-feed-item">
                        <a href="#" class="sa-session" data-id="<?= htmlspecialchars($row->session_id) ?>">
                            <?= sa_flag($row->country_code) ?> Visitor from <?= htmlspecialchars($row->country ?: 'Unknown') ?>
                        </a>
                        · <?= htmlspecialchars(sa_event_label($row->event_type)) ?><br>
                        Viewing: <strong><?= htmlspecialchars(sa_page_label($row->page_url, $row->page_title, $row->product_name)) ?></strong>
                        <div class="text-muted"><?= htmlspecialchars(sa_ago($row->created_at)) ?><?= $row->store_name ? ' · ' . htmlspecialchars($row->store_name) : '' ?></div>
                    </div>
                    <?php endforeach; ?>
                    <?php if (empty($feed)): ?><p class="text-muted">Waiting for storefront activity.</p><?php endif; ?>
                </div>
            </div>
        </div>
        <div class="col-lg-7">
            <div class="ibox">
                <div class="ibox-title"><h5>Conversion funnel</h5></div>
                <div class="ibox-content">
                    <?php
                    $funnelMax = max(1, (int) $funnel[0]['value']);
                    $prev = null;
                    ?>
                    <ul class="sa-funnel" id="saFunnel">
                        <?php foreach ($funnel as $step): ?>
                        <?php if ($prev !== null): ?>
                        <li class="text-muted text-center">↓ <?= sa_pct($step['value'], $prev) ?>%</li>
                        <?php endif; ?>
                        <li>
                            <div><?= htmlspecialchars($step['label']) ?> <strong class="pull-right"><?= sa_int($step['value']) ?></strong></div>
                            <div class="bar" style="width:<?= min(100, sa_pct($step['value'], $funnelMax)) ?>%"></div>
                        </li>
                        <?php $prev = $step['value']; endforeach; ?>
                    </ul>
                    <p class="m-t-sm">Cart → checkout <?= number_format((float) $checkout['cart_to_checkout'], 1) ?>% · Checkout → purchase <?= number_format((float) $checkout['checkout_to_purchase'], 1) ?>%</p>
                    <p class="text-muted">Checkout started <?= sa_int($checkout['started']) ?> · Payment page <?= sa_int($checkout['payment_page']) ?> · Payment attempted <?= sa_int($checkout['payment_attempt']) ?> · Purchase completed <?= sa_int($checkout['purchases']) ?></p>
                </div>
            </div>
            <div class="ibox">
                <div class="ibox-title"><h5>Visitor trend</h5></div>
                <div class="ibox-content">
                    <?php
                    $maxTrend = 1;
                    foreach ($trend as $row) {
                        $maxTrend = max($maxTrend, (int) $row->visitors, (int) $row->product_views);
                    }
                    ?>
                    <div style="overflow-x:auto; white-space:nowrap; min-height:120px;">
                        <?php foreach ($trend as $row):
                            $h = max(2, ((int) $row->visitors / $maxTrend) * 90);
                            $label = (strlen($row->bucket) > 10) ? substr($row->bucket, 11, 5) : substr($row->bucket, 5);
                        ?>
                        <div title="<?= htmlspecialchars($row->bucket) ?> · <?= (int) $row->visitors ?> visitors" style="display:inline-block; width:18px; margin-right:3px; vertical-align:bottom;">
                            <div style="height:<?= $h ?>px; background:#1ab394;"></div>
                            <div style="font-size:9px; color:#888;"><?= htmlspecialchars($label) ?></div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <small class="text-muted">Hourly for Today / Yesterday. Daily for longer ranges. Hover a bar for unique visitors, views, carts, checkout and orders.</small>
                    <div class="table-responsive m-t-sm">
                        <table class="table table-condensed">
                            <thead><tr><th>Time</th><th>Visitors</th><th>Unique</th><th>Views</th><th>Cart</th><th>Checkout</th><th>Orders</th></tr></thead>
                            <tbody>
                                <?php foreach ($trend as $row): ?>
                                <?php if ((int) $row->visitors || (int) $row->product_views || (int) $row->add_to_cart || (int) $row->checkout || (int) $row->orders): ?>
                                <tr>
                                    <td><?= htmlspecialchars((strlen($row->bucket) > 10) ? substr($row->bucket, 11, 5) : $row->bucket) ?></td>
                                    <td><?= (int) $row->visitors ?></td>
                                    <td><?= (int) $row->unique_visitors ?></td>
                                    <td><?= (int) $row->product_views ?></td>
                                    <td><?= (int) $row->add_to_cart ?></td>
                                    <td><?= (int) $row->checkout ?></td>
                                    <td><?= (int) $row->orders ?></td>
                                </tr>
                                <?php endif; ?>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="ibox">
        <div class="ibox-title"><h5>Visitors by country</h5></div>
        <div class="ibox-content">
            <div class="table-responsive">
                <table class="table table-striped sa-table">
                    <thead>
                        <tr>
                            <th><?= sa_sort_link('country', 'Country', $f) ?></th>
                            <th><?= sa_sort_link('visitors', 'Visitors', $f) ?></th>
                            <th><?= sa_sort_link('unique_visitors', 'Unique', $f) ?></th>
                            <th><?= sa_sort_link('online', 'Online', $f) ?></th>
                            <th><?= sa_sort_link('product_views', 'Product views', $f) ?></th>
                            <th><?= sa_sort_link('add_to_cart', 'Add to cart', $f) ?></th>
                            <th><?= sa_sort_link('checkout', 'Checkout', $f) ?></th>
                            <th><?= sa_sort_link('purchases', 'Orders', $f) ?></th>
                            <th><?= sa_sort_link('conversion', 'Conversion', $f) ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($countries as $row): ?>
                        <tr>
                            <td><a href="<?= base_url('admin/store-analytics') ?>?<?= http_build_query(array_filter(array('range' => $f['range'], 'store_id' => $f['store_id'] ?: null, 'country_code' => $row->country_code))) ?>"><?= sa_flag($row->country_code) ?> <?= htmlspecialchars($row->country) ?></a></td>
                            <td><?= sa_int($row->visitors) ?></td>
                            <td><?= sa_int($row->unique_visitors) ?></td>
                            <td><?= sa_int($row->online) ?></td>
                            <td><?= sa_int($row->product_views) ?></td>
                            <td><?= sa_int($row->add_to_cart) ?></td>
                            <td><?= sa_int($row->checkout) ?></td>
                            <td><?= sa_int($row->purchases) ?></td>
                            <td><?= number_format((float) $row->conversion, 2) ?>%</td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if (empty($countries)): ?><tr><td colspan="9" class="text-center text-muted">No visitor countries in this range.</td></tr><?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="ibox">
        <div class="ibox-title"><h5>Store analytics</h5></div>
        <div class="ibox-content">
            <div class="table-responsive">
                <table class="table table-striped sa-table">
                    <thead>
                        <tr>
                            <th>Store</th>
                            <th>Country</th>
                            <th>Visitors</th>
                            <th>Online</th>
                            <th>Product views</th>
                            <th>Add to cart</th>
                            <th>Checkout</th>
                            <th>Orders</th>
                            <th>Revenue</th>
                            <th>Conversion</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($stores as $row): ?>
                        <tr>
                            <td><a href="<?= base_url('admin/store-analytics') ?>?<?= http_build_query(array_filter(array('range' => $f['range'], 'store_id' => $row->store_id))) ?>"><?= htmlspecialchars($row->store_name) ?></a></td>
                            <td><?= htmlspecialchars($row->country_name) ?></td>
                            <td><?= sa_int($row->visitors) ?></td>
                            <td><?= sa_int($row->online) ?></td>
                            <td><?= sa_int($row->product_views) ?></td>
                            <td><?= sa_int($row->add_to_cart) ?></td>
                            <td><?= sa_int($row->checkout) ?></td>
                            <td><?= sa_int($row->orders) ?></td>
                            <td><?= $row->currency ? format_money($row->revenue, $row->currency) : number_format((float) $row->revenue, 2) ?></td>
                            <td><?= number_format((float) $row->conversion, 2) ?>%</td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if (empty($stores)): ?><tr><td colspan="10" class="text-center text-muted">No store traffic in this range.</td></tr><?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <?php
    $reviewStats = isset($review_stats) ? $review_stats : array('total' => 0, 'approved' => 0, 'pending' => 0, 'rejected' => 0, 'average' => 0, 'customer' => 0, 'ai_generated' => 0);
    $reviews = isset($reviews) ? $reviews : array();
    ?>
    <div class="ibox" id="saReviews">
        <div class="ibox-title">
            <h5>Reviews</h5>
            <span class="pull-right text-muted">Same date / store / country filters as above</span>
        </div>
        <div class="ibox-content">
            <div class="row m-b-md">
                <div class="col-md-2"><div class="sa-metric"><h5>Total</h5><h2><?= (int) $reviewStats['total'] ?></h2></div></div>
                <div class="col-md-2"><div class="sa-metric"><h5>Avg rating</h5><h2><?= number_format((float) $reviewStats['average'], 1) ?></h2></div></div>
                <div class="col-md-2"><div class="sa-metric"><h5>Approved</h5><h2 class="text-navy"><?= (int) $reviewStats['approved'] ?></h2></div></div>
                <div class="col-md-2"><div class="sa-metric"><h5>Pending</h5><h2 class="text-warning"><?= (int) $reviewStats['pending'] ?></h2></div></div>
                <div class="col-md-2"><div class="sa-metric"><h5>Customer</h5><h2><?= (int) $reviewStats['customer'] ?></h2></div></div>
                <div class="col-md-2"><div class="sa-metric"><h5>AI sample</h5><h2><?= (int) $reviewStats['ai_generated'] ?></h2></div></div>
            </div>
            <div class="table-responsive">
                <table class="table table-striped sa-table">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Reviewer</th>
                            <th>Product</th>
                            <th>Store</th>
                            <th>Country</th>
                            <th>Rating</th>
                            <th>Review</th>
                            <th>Source</th>
                            <th>Approval</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($reviews as $item): ?>
                        <?php
                            $snippet = strip_tags((string) $item->content);
                            if (strlen($snippet) > 100) {
                                $snippet = substr($snippet, 0, 97) . '…';
                            }
                            $source = isset($item->source) ? $item->source : 'customer';
                        ?>
                        <tr>
                            <td class="small text-muted"><?= htmlspecialchars(substr((string) $item->created_at, 0, 16)) ?></td>
                            <td><?= htmlspecialchars($item->customer_name !== '' ? $item->customer_name : '—') ?></td>
                            <td>
                                <?php if (!empty($item->product_id)): ?>
                                <a href="<?= base_url('admin/products/form/' . (int) $item->product_id) ?>"><?= htmlspecialchars($item->product_name ? $item->product_name : ('#' . (int) $item->product_id)) ?></a>
                                <?php else: ?>
                                —
                                <?php endif; ?>
                            </td>
                            <td><?= htmlspecialchars(!empty($item->store_name) ? $item->store_name : ('#' . (int) $item->store_id)) ?></td>
                            <td><?= !empty($item->country_code) ? (sa_flag($item->country_code) . ' ' . htmlspecialchars($item->country_name ?: $item->country_code)) : '—' ?></td>
                            <td><strong><?= (int) $item->rating ?></strong>/5</td>
                            <td style="white-space:normal; max-width:320px;">
                                <?php if ($item->title !== ''): ?><div><strong><?= htmlspecialchars($item->title) ?></strong></div><?php endif; ?>
                                <div class="small text-muted"><?= htmlspecialchars($snippet) ?></div>
                            </td>
                            <td><?= $source === 'ai_generated' ? '<span class="label label-info">AI</span>' : '<span class="label label-default">Customer</span>' ?></td>
                            <td>
                                <?php if ($item->approval_status === 'approved'): ?>
                                    <span class="label label-primary">Approved</span>
                                <?php elseif ($item->approval_status === 'rejected'): ?>
                                    <span class="label label-danger">Rejected</span>
                                <?php else: ?>
                                    <span class="label label-warning">Pending</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if (empty($reviews)): ?>
                        <tr><td colspan="9" class="text-center text-muted">No reviews in this range.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="ibox">
        <div class="ibox-title"><h5>Most viewed products</h5></div>
        <div class="ibox-content">
            <div class="table-responsive">
                <table class="table table-striped sa-table">
                    <thead>
                        <tr>
                            <th>Rank</th>
                            <th>Product</th>
                            <th>Product ID</th>
                            <th>Store</th>
                            <th>Country</th>
                            <th>Views</th>
                            <th>Unique</th>
                            <th>Active</th>
                            <th>Add to cart</th>
                            <th>Checkout</th>
                            <th>Orders</th>
                            <th>Conversion</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $rank = 1; foreach ($products as $row): ?>
                        <tr>
                            <td><?= $rank++ ?></td>
                            <td><a href="#" class="sa-product" data-id="<?= (int) $row->product_id ?>"><?= htmlspecialchars($row->product_name) ?></a></td>
                            <td><code><?= htmlspecialchars($row->analyzer_id) ?></code></td>
                            <td><?= htmlspecialchars($row->store_name) ?></td>
                            <td><?= htmlspecialchars($row->country_name) ?></td>
                            <td><?= sa_int($row->views) ?></td>
                            <td><?= sa_int($row->unique_visitors) ?></td>
                            <td><?= sa_int($row->active_visitors) ?></td>
                            <td><?= sa_int($row->add_to_cart) ?></td>
                            <td><?= sa_int($row->checkout) ?></td>
                            <td><?= sa_int($row->purchases) ?></td>
                            <td><?= number_format((float) $row->conversion, 2) ?>%</td>
                            <td>
                                <a class="btn btn-xs btn-white" href="<?= base_url('admin/product-analyzer') ?>?q=<?= rawurlencode($row->analyzer_id) ?>">View Profitability</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if (empty($products)): ?><tr><td colspan="13" class="text-center text-muted">No product views in this range.</td></tr><?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6">
            <div class="ibox">
                <div class="ibox-title"><h5>Traffic sources</h5></div>
                <div class="ibox-content">
                    <table class="table table-striped">
                        <thead><tr><th>Source</th><th>Visitors</th><th>Views</th><th>Cart</th><th>Checkout</th><th>Orders</th><th>Revenue</th></tr></thead>
                        <tbody>
                            <?php foreach ($sources as $row): ?>
                            <tr>
                                <td><?= htmlspecialchars($row->label) ?></td>
                                <td><?= sa_int($row->visitors) ?></td>
                                <td><?= sa_int($row->product_views) ?></td>
                                <td><?= sa_int($row->add_to_cart) ?></td>
                                <td><?= sa_int($row->checkout) ?></td>
                                <td><?= sa_int($row->purchases) ?></td>
                                <td><?= number_format((float) (isset($row->revenue) ? $row->revenue : 0), 2) ?></td>
                            </tr>
                            <?php endforeach; ?>
                            <?php if (empty($sources)): ?><tr><td colspan="7" class="text-muted">No source data yet.</td></tr><?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="ibox">
                <div class="ibox-title"><h5>Device breakdown</h5></div>
                <div class="ibox-content">
                    <table class="table table-striped">
                        <thead><tr><th>Device</th><th>Visitors</th><th>Views</th><th>Cart</th><th>Checkout</th><th>Orders</th></tr></thead>
                        <tbody>
                            <?php foreach ($devices as $row): ?>
                            <tr>
                                <td><?= htmlspecialchars(ucfirst($row->device ?: 'unknown')) ?></td>
                                <td><?= sa_int($row->visitors) ?></td>
                                <td><?= sa_int($row->product_views) ?></td>
                                <td><?= sa_int($row->add_to_cart) ?></td>
                                <td><?= sa_int($row->checkout) ?></td>
                                <td><?= sa_int($row->purchases) ?></td>
                            </tr>
                            <?php endforeach; ?>
                            <?php if (empty($devices)): ?><tr><td colspan="6" class="text-muted">No device data yet.</td></tr><?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="ibox">
        <div class="ibox-title"><h5>Meta / campaign traffic</h5></div>
        <div class="ibox-content">
            <table class="table table-striped">
                <thead><tr><th>Campaign</th><th>Source</th><th>Medium</th><th>Visitors</th><th>Views</th><th>Cart</th><th>Checkout</th><th>Orders</th><th>Revenue</th></tr></thead>
                <tbody>
                    <?php foreach ($campaigns as $row): ?>
                    <tr>
                        <td><?= htmlspecialchars($row->utm_campaign) ?></td>
                        <td><?= htmlspecialchars($row->utm_source) ?></td>
                        <td><?= htmlspecialchars($row->utm_medium) ?></td>
                        <td><?= sa_int($row->visitors) ?></td>
                        <td><?= sa_int($row->product_views) ?></td>
                        <td><?= sa_int($row->add_to_cart) ?></td>
                        <td><?= sa_int($row->checkout) ?></td>
                        <td><?= sa_int($row->purchases) ?></td>
                        <td><?= number_format((float) $row->revenue, 2) ?></td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if (empty($campaigns)): ?><tr><td colspan="9" class="text-muted">No UTM campaigns recorded yet. Add utm_source / utm_campaign to ad URLs.</td></tr><?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="ibox">
        <div class="ibox-title"><h5>Active window</h5></div>
        <div class="ibox-content">
            <form method="post" action="<?= base_url('admin/store-analytics/save-settings') ?>" class="form-inline">
                <label>A visitor is online if last activity is within</label>
                <input type="number" min="1" max="60" name="active_minutes" class="form-control input-sm" value="<?= (int) $settings['active_minutes'] ?>">
                minutes
                <button class="btn btn-primary btn-sm" type="submit">Save</button>
            </form>
        </div>
    </div>
</div>

<div class="modal inmodal" id="saDrawer" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                <h4 class="modal-title">Details</h4>
            </div>
            <div class="modal-body" id="saDrawerBody">Loading…</div>
        </div>
    </div>
</div>
<script>
$(function () {
    var liveUrl = <?= json_encode($liveUrl) ?>;
    var lastUpdate = Date.now();
    var eventLabels = {
        page_view: 'Page View', product_view: 'Product View', add_to_cart: 'Add to Cart',
        begin_checkout: 'Checkout', payment_page_view: 'Payment Page', payment_attempt: 'Payment Attempt',
        purchase: 'Purchase'
    };
    function openDrawer(url) {
        $('#saDrawerBody').html('Loading…');
        $('#saDrawer').modal('show');
        $.get(url, function (html) { $('#saDrawerBody').html(html); }).fail(function () {
            $('#saDrawerBody').html('<div class="alert alert-danger">Could not load details.</div>');
        });
    }
    $(document).on('click', '.sa-product', function (e) {
        e.preventDefault();
        openDrawer('<?= base_url('admin/store-analytics/product/') ?>' + $(this).data('id') + '<?= $qs ? ('?' . $qs) : '' ?>');
    });
    $(document).on('click', '.sa-session', function (e) {
        e.preventDefault();
        openDrawer('<?= base_url('admin/store-analytics/session/') ?>' + encodeURIComponent($(this).data('id')));
    });
    $('#saFrom, #saTo').on('change', function () {
        if ($('#saFrom').val() || $('#saTo').val()) {
            $('#saRange').val('custom');
        }
    });
    $('#saRange').on('change', function () {
        if ($(this).val() !== 'custom') {
            $('#saFrom, #saTo').val('');
        }
    });
    $('#saClearDataBtn').on('click', function () {
        var ok = window.confirm('Clear ALL store analytics data?\n\nThis permanently deletes visitors, sessions, events and summaries. Orders are not affected.\n\nContinue?');
        if (!ok) return;
        var typed = window.prompt('Type CLEAR to confirm wiping analytics data:');
        if (typed !== 'CLEAR') {
            alert('Cancelled. Nothing was deleted.');
            return;
        }
        $('#saClearDataForm').submit();
    });
    function n(v) { return Number(v || 0).toLocaleString(); }
    function esc(v) { return $('<div>').text(v || '').html(); }
    function ago(ts) {
        var d = Math.max(0, Math.round((Date.now() - new Date(String(ts).replace(' ', 'T')).getTime()) / 1000));
        if (isNaN(d)) return '';
        if (d < 60) return d + ' second' + (d === 1 ? '' : 's') + ' ago';
        if (d < 3600) { var m = Math.floor(d / 60); return m + ' minute' + (m === 1 ? '' : 's') + ' ago'; }
        var h = Math.floor(d / 3600); return h + ' hour' + (h === 1 ? '' : 's') + ' ago';
    }
    function pageLabel(row) {
        if (row.product_name) return row.product_name;
        var url = String(row.page_url || row.current_page || '');
        if (url.indexOf('/cart') !== -1) return 'Cart';
        if (url.indexOf('/checkout') !== -1) return 'Checkout';
        if (url.indexOf('/payment') !== -1) return 'Payment';
        return row.page_title || 'Storefront';
    }
    function renderFeed(feed) {
        if (!feed.length) {
            $('#saFeed').html('<p class="text-muted">Waiting for storefront activity.</p>');
            return;
        }
        $('#saFeed').html(feed.map(function (row) {
            return '<div class="sa-feed-item"><a href="#" class="sa-session" data-id="' + esc(row.session_id) + '">' +
                esc(row.country || 'Unknown') + '</a> · ' + esc(eventLabels[row.event_type] || row.event_type) +
                '<br>Viewing: <strong>' + esc(pageLabel(row)) + '</strong>' +
                '<div class="text-muted">' + esc(ago(row.created_at)) + '</div></div>';
        }).join(''));
    }
    function renderSessions(rows) {
        if (!rows.length) {
            $('#saLiveSessions tbody').html('<tr><td colspan="5" class="text-muted">No active sessions.</td></tr>');
            return;
        }
        $('#saLiveSessions tbody').html(rows.map(function (row) {
            return '<tr><td><a href="#" class="sa-session" data-id="' + esc(row.session_id) + '">' + esc(row.country || 'Unknown') +
                '</a></td><td>' + esc(row.product_name || pageLabel(row)) + '</td><td>' + esc(row.traffic_source || '') +
                '</td><td>' + esc(row.device || '') + '</td><td>' + esc(ago(row.last_activity)) + '</td></tr>';
        }).join(''));
    }
    function tick() {
        $.getJSON(liveUrl, function (data) {
            var s = data.summary || {};
            $('#saVisitors').text(n(s.visitors));
            $('#saUnique').text(n(s.unique));
            $('#saOnline').text(n(s.online));
            $('#saOnlineLabel').text((s.online || 0) + ' visitors online now');
            $('#saViews').text(n(s.product_views));
            $('#saCarts').text(n(s.add_to_cart));
            $('#saCheckout').text(n(s.checkout));
            $('#saOrders').text(n(s.orders));
            $('#saConv').text(Number(s.conversion || 0).toFixed(2) + '%');
            lastUpdate = Date.now();
            $('#saUpdated').text('Updated just now');
            var alerts = (data.alerts || []).map(function (a) { return '<div class="sa-alert">' + esc(a) + '</div>'; }).join('');
            $('#saAlerts').html(alerts);
            var products = data.live_products || [];
            if (!products.length) {
                $('#saLiveProducts').html('<p class="text-muted">No product pages are active right now.</p>');
            } else {
                $('#saLiveProducts').html(products.map(function (p) {
                    return '<div class="sa-live-product"><a href="#" class="sa-product" data-id="' + p.product_id + '">' + esc(p.product_name || 'Product') + '</a><span class="pull-right"><strong>' + p.active_visitors + '</strong> active visitors</span></div>';
                }).join(''));
            }
            renderFeed(data.feed || []);
            renderSessions(data.live_sessions || []);
        });
    }
    setInterval(function () {
        var secs = Math.round((Date.now() - lastUpdate) / 1000);
        if (secs > 0) {
            $('#saUpdated').text('Updated ' + secs + ' second' + (secs === 1 ? '' : 's') + ' ago');
        }
    }, 1000);
    $('#saRefresh').on('click', tick);
    setInterval(tick, 5000);
});
</script>
