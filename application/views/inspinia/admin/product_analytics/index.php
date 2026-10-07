<?php
$filters = isset($filters) ? $filters : array();
$isAdmin = !empty($is_admin);
$basePath = isset($base_url_path) ? $base_url_path : 'admin/product-analytics';
$visitorCountries = isset($visitor_countries) ? $visitor_countries : array();
$topProducts = isset($top_products) ? $top_products : array();
$newProducts = isset($new_products) ? $new_products : array();
$trend = isset($trend) ? $trend : array();
$range = isset($filters['range']) ? $filters['range'] : 'week';
if ($range === 'today') {
    $range = 'day';
}
$filterCountryId = isset($filters['country_id']) ? (int) $filters['country_id'] : 0;
$filterStoreId = isset($filters['store_id']) ? (int) $filters['store_id'] : 0;

$keep = array();
if ($filterCountryId) {
    $keep['country_id'] = $filterCountryId;
}
if ($isAdmin && $filterStoreId) {
    $keep['store_id'] = $filterStoreId;
}
$rangeUrl = function ($preset) use ($basePath, $keep) {
    $q = $keep;
    $q['range'] = $preset;
    return base_url($basePath) . '?' . http_build_query($q);
};
$rangeLabel = array(
    'day' => 'Day',
    'week' => 'Week',
    'month' => 'Month',
    'year' => 'Year',
);
$chartLabels = array();
$chartViews = array();
foreach ($trend as $point) {
    $bucket = (string) $point->bucket;
    if (strlen($bucket) === 7) {
        $chartLabels[] = date('M Y', strtotime($bucket . '-01'));
    } elseif (strlen($bucket) > 10) {
        $chartLabels[] = date('H:i', strtotime($bucket));
    } else {
        $chartLabels[] = date('d M', strtotime($bucket));
    }
    $chartViews[] = (int) $point->views;
}
$countryChips = function ($list) {
    if (empty($list)) {
        return '<span class="text-muted">No country data</span>';
    }
    $html = '';
    foreach ($list as $item) {
        $flag = function_exists('sa_flag') ? sa_flag($item->country_code) : '';
        $html .= '<span class="pa-chip">' . $flag . ' <strong>' . number_format((int) $item->views) . '</strong> from '
            . htmlspecialchars($item->country ? $item->country : 'Unknown') . '</span>';
    }
    return $html;
};
$productThumb = function ($row) {
    $src = isset($row->image) ? trim((string) $row->image) : '';
    if ($src === '') {
        return '';
    }
    if (strpos($src, 'http://') !== 0 && strpos($src, 'https://') !== 0) {
        $src = base_url(ltrim($src, '/'));
    }
    return '<img src="' . htmlspecialchars($src) . '" alt="" class="pa-thumb">';
};
?>
<style>
.pa-chip { display:inline-block; margin:0 6px 6px 0; padding:4px 10px; background:#f4f6f8; border-radius:12px; font-size:12px; color:#333; }
.pa-thumb { width:36px; height:36px; object-fit:cover; border-radius:4px; margin-right:8px; vertical-align:middle; }
.pa-range .btn { margin-right:4px; }
.pa-country-card h2 { margin:4px 0; }
.pa-new { background:#1ab394; color:#fff; font-size:10px; padding:2px 6px; border-radius:8px; margin-left:6px; vertical-align:middle; }
</style>
<div class="row wrapper border-bottom white-bg page-heading">
    <div class="col-lg-10">
        <h2>Product Analytics</h2>
        <ol class="breadcrumb">
            <li><a href="<?= base_url('admin/admin') ?>">Dashboard</a></li>
            <li class="active"><strong>Product Analytics</strong></li>
        </ol>
    </div>
</div>
<div class="wrapper wrapper-content">
    <?php $this->load->view('flash'); ?>

    <div class="ibox">
        <div class="ibox-title"><h5>Filters</h5></div>
        <div class="ibox-content">
            <form method="get" action="<?= base_url($basePath) ?>" class="form-inline" id="paFilterForm">
                <input type="hidden" name="range" value="<?= htmlspecialchars($range) ?>">
                <div class="form-group" style="margin-right:8px; margin-bottom:8px;">
                    <select name="country_id" id="paFilterCountry" class="form-control">
                        <option value="0">All countries</option>
                        <?php foreach (!empty($countries) ? $countries : array() as $country): ?>
                            <option value="<?= (int) $country->id ?>" <?= $filterCountryId === (int) $country->id ? 'selected' : '' ?>><?= htmlspecialchars($country->name) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group" style="margin-right:8px; margin-bottom:8px;">
                    <select name="store_id" id="paFilterStore" class="form-control">
                        <option value="0">All stores</option>
                        <?php foreach (!empty($stores) ? $stores : array() as $store): ?>
                            <option value="<?= (int) $store->id ?>" <?= $filterStoreId === (int) $store->id ? 'selected' : '' ?>><?= htmlspecialchars($store->name) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <button type="submit" class="btn btn-primary" style="margin-bottom:8px;">Filter</button>
                <a href="<?= base_url($basePath) ?>" class="btn btn-white" style="margin-bottom:8px;">Reset</a>
            </form>
            <div class="pa-range" style="margin-top:12px;">
                <?php foreach ($rangeLabel as $key => $label): ?>
                    <a class="btn btn-sm <?= $range === $key ? 'btn-primary' : 'btn-white' ?>" href="<?= $rangeUrl($key) ?>"><?= $label ?></a>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-sm-6 col-md-3">
            <div class="ibox"><div class="ibox-content pa-country-card">
                <h5>Total views</h5>
                <h2><?= number_format((int) $total_views) ?></h2>
                <small class="text-muted"><?= number_format((int) $unique_visitors) ?> unique visitors</small>
            </div></div>
        </div>
        <?php foreach ($visitorCountries as $country): ?>
        <div class="col-sm-6 col-md-3">
            <div class="ibox"><div class="ibox-content pa-country-card">
                <h5><?= function_exists('sa_flag') ? sa_flag($country->country_code) : '' ?> <?= htmlspecialchars($country->country ? $country->country : 'Unknown') ?></h5>
                <h2><?= number_format((int) $country->views) ?></h2>
                <small class="text-muted">from <?= htmlspecialchars($country->country ? $country->country : 'Unknown') ?><?php if (!empty($country->visitors)): ?> · <?= number_format((int) $country->visitors) ?> visitors<?php endif; ?></small>
            </div></div>
        </div>
        <?php endforeach; ?>
        <?php if (empty($visitorCountries)): ?>
        <div class="col-sm-6 col-md-3">
            <div class="ibox"><div class="ibox-content">
                <h5>Visitor countries</h5>
                <p class="text-muted" style="margin:0;">No product views in this period.</p>
            </div></div>
        </div>
        <?php endif; ?>
    </div>

    <div class="ibox">
        <div class="ibox-title"><h5>Product views — <?= htmlspecialchars($rangeLabel[$range]) ?></h5></div>
        <div class="ibox-content">
            <div style="height:280px;">
                <canvas id="paViewsChart"></canvas>
            </div>
        </div>
    </div>

    <div class="ibox">
        <div class="ibox-title"><h5>New products</h5></div>
        <div class="ibox-content">
            <p class="text-muted">Store products added in the last 30 days, with views in the selected period.</p>
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>Store</th>
                            <th>Added</th>
                            <th>Views</th>
                            <th>Views by country</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($newProducts as $row): ?>
                        <tr>
                            <td><?= $productThumb($row) ?><?= htmlspecialchars($row->product_name) ?> <span class="pa-new">New</span></td>
                            <td><?= htmlspecialchars($row->store_name ? $row->store_name : '—') ?></td>
                            <td><?= !empty($row->created_at) ? htmlspecialchars(date('d M Y', strtotime($row->created_at))) : '—' ?></td>
                            <td><?= number_format((int) $row->views) ?></td>
                            <td><?= $countryChips($row->countries) ?></td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if (empty($newProducts)): ?>
                        <tr><td colspan="5" class="text-center text-muted">No new products in the last 30 days for these filters.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="ibox">
        <div class="ibox-title"><h5>Top products</h5></div>
        <div class="ibox-content">
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>Store</th>
                            <th>Views</th>
                            <th>Visitors</th>
                            <th>Views by country</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($topProducts as $row): ?>
                        <tr>
                            <td><?= $productThumb($row) ?><?= htmlspecialchars($row->product_name) ?><?php if (!empty($row->is_new)): ?> <span class="pa-new">New</span><?php endif; ?></td>
                            <td><?= htmlspecialchars($row->store_name ? $row->store_name : '—') ?></td>
                            <td><?= number_format((int) $row->views) ?></td>
                            <td><?= number_format((int) $row->visitors) ?></td>
                            <td><?= $countryChips($row->countries) ?></td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if (empty($topProducts)): ?>
                        <tr><td colspan="5" class="text-center text-muted">No product views in this period.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.9.4/Chart.min.js"></script>
<script>
(function ($) {
    $('#paFilterCountry').on('change', function () {
        $('#paFilterStore').val('0');
        $('#paFilterForm').submit();
    });
    $('#paFilterStore').on('change', function () {
        $('#paFilterForm').submit();
    });
    var ctx = document.getElementById('paViewsChart');
    if (ctx) {
        new Chart(ctx.getContext('2d'), {
            type: 'line',
            data: {
                labels: <?= json_encode($chartLabels) ?>,
                datasets: [{
                    label: 'Product views',
                    data: <?= json_encode($chartViews) ?>,
                    borderColor: '#1ab394',
                    backgroundColor: 'rgba(26,179,148,0.12)',
                    pointBackgroundColor: '#1ab394',
                    fill: true,
                    lineTension: 0.25
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                legend: { display: false },
                scales: {
                    yAxes: [{ ticks: { beginAtZero: true, precision: 0 } }]
                }
            }
        });
    }
})(jQuery);
</script>
