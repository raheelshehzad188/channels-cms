<?php
$filters = isset($filters) ? $filters : array();
$basePath = isset($base_url_path) ? $base_url_path : 'store/product-analytics';
$visitorCountries = isset($visitor_countries) ? $visitor_countries : array();
$topProducts = isset($top_products) ? $top_products : array();
$newProducts = isset($new_products) ? $new_products : array();
$trend = isset($trend) ? $trend : array();
$range = isset($filters['range']) ? $filters['range'] : 'week';
if ($range === 'today') {
    $range = 'day';
}
$rangeLabel = array(
    'day' => 'Day',
    'week' => 'Week',
    'month' => 'Month',
    'year' => 'Year',
);
$rangeUrl = function ($preset) use ($basePath) {
    return base_url($basePath) . '?' . http_build_query(array('range' => $preset));
};
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
        $html .= '<span class="badge rounded-pill text-bg-light border me-1 mb-1">' . $flag . ' <strong>'
            . number_format((int) $item->views) . '</strong> from '
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
    return '<img src="' . htmlspecialchars($src) . '" alt="" width="36" height="36" class="rounded me-2" style="object-fit:cover;">';
};
?>
<?php $this->load->view('flash'); ?>

<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
    <div>
        <h1 class="h4 mb-1">Product analytics</h1>
        <p class="text-muted mb-0">Views on your store products, by visitor country.</p>
    </div>
    <div class="btn-group">
        <?php foreach ($rangeLabel as $key => $label): ?>
            <a class="btn btn-sm <?= $range === $key ? 'btn-primary' : 'btn-outline-secondary' ?>" href="<?= $rangeUrl($key) ?>"><?= $label ?></a>
        <?php endforeach; ?>
    </div>
</div>

<div class="row g-3 mb-3">
    <div class="col-md-3 col-sm-6">
        <div class="stat-card">
            <div class="label">Total views</div>
            <div class="value"><?= number_format((int) $total_views) ?></div>
            <div class="small text-muted mt-1"><?= number_format((int) $unique_visitors) ?> unique visitors</div>
        </div>
    </div>
    <?php foreach ($visitorCountries as $country): ?>
    <div class="col-md-3 col-sm-6">
        <div class="stat-card">
            <div class="label"><?= function_exists('sa_flag') ? sa_flag($country->country_code) : '' ?> <?= htmlspecialchars($country->country ? $country->country : 'Unknown') ?></div>
            <div class="value"><?= number_format((int) $country->views) ?></div>
            <div class="small text-muted mt-1">from <?= htmlspecialchars($country->country ? $country->country : 'Unknown') ?></div>
        </div>
    </div>
    <?php endforeach; ?>
    <?php if (empty($visitorCountries)): ?>
    <div class="col-md-3 col-sm-6">
        <div class="stat-card">
            <div class="label">Visitor countries</div>
            <div class="small text-muted mt-1">No product views in this period.</div>
        </div>
    </div>
    <?php endif; ?>
</div>

<div class="store-card mb-3">
    <div class="card-header">Product views — <?= htmlspecialchars($rangeLabel[$range]) ?></div>
    <div class="card-body">
        <div style="height:280px;">
            <canvas id="paViewsChart"></canvas>
        </div>
    </div>
</div>

<div class="store-card mb-3">
    <div class="card-header">New products</div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Added</th>
                        <th>Views</th>
                        <th>Views by country</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($newProducts as $row): ?>
                    <tr>
                        <td><?= $productThumb($row) ?><?= htmlspecialchars($row->product_name) ?> <span class="badge text-bg-success">New</span></td>
                        <td><?= !empty($row->created_at) ? htmlspecialchars(date('d M Y', strtotime($row->created_at))) : '—' ?></td>
                        <td><?= number_format((int) $row->views) ?></td>
                        <td><?= $countryChips($row->countries) ?></td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if (empty($newProducts)): ?>
                    <tr><td colspan="4" class="text-center text-muted py-4">No new products in the last 30 days.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="store-card mb-3">
    <div class="card-header">Top products</div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Views</th>
                        <th>Visitors</th>
                        <th>Views by country</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($topProducts as $row): ?>
                    <tr>
                        <td><?= $productThumb($row) ?><?= htmlspecialchars($row->product_name) ?><?php if (!empty($row->is_new)): ?> <span class="badge text-bg-success">New</span><?php endif; ?></td>
                        <td><?= number_format((int) $row->views) ?></td>
                        <td><?= number_format((int) $row->visitors) ?></td>
                        <td><?= $countryChips($row->countries) ?></td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if (empty($topProducts)): ?>
                    <tr><td colspan="4" class="text-center text-muted py-4">No product views in this period.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.9.4/Chart.min.js"></script>
<script>
(function () {
    var ctx = document.getElementById('paViewsChart');
    if (!ctx) return;
    new Chart(ctx.getContext('2d'), {
        type: 'line',
        data: {
            labels: <?= json_encode($chartLabels) ?>,
            datasets: [{
                label: 'Product views',
                data: <?= json_encode($chartViews) ?>,
                borderColor: '#0d6efd',
                backgroundColor: 'rgba(13,110,253,0.12)',
                pointBackgroundColor: '#0d6efd',
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
})();
</script>
