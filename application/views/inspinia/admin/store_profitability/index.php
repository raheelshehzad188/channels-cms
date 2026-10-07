<?php
$items = isset($items) ? $items : array();
$chartItems = isset($chart_items) ? $chart_items : $items;
$settings = isset($settings) ? $settings : array();
$store = isset($store) ? $store : null;
$summary = isset($summary) ? $summary : null;
$storeCurrency = isset($store_currency) ? $store_currency : 'SEK';
$rate = isset($rates[$storeCurrency]) ? (float) $rates[$storeCurrency] : 0;
$mode = $settings['budget_mode'];
$keep = array(
    'country_id' => $filters['country_id'],
    'store_id' => $filters['store_id'],
    'budget_mode' => $settings['budget_mode'],
    'ads_budget' => $settings['ads_budget'],
    'ads_currency' => $settings['ads_currency'],
    'expected_orders' => $settings['expected_orders'],
    'target_profit' => $settings['target_profit'],
    'analyze' => $filters['analyze'],
);
$filterUrl = function ($overrides = array()) use ($filters, $keep) {
    $q = $keep + $filters;
    unset($q['page']);
    foreach ($overrides as $key => $value) {
        $q[$key] = $value;
    }
    $q = array_filter($q, function ($v) {
        return $v !== '' && $v !== null && $v !== 0 && $v !== '0';
    });
    return base_url('admin/store-profitability') . '?' . http_build_query($q);
};
$sortLink = function ($col, $label) use ($filterUrl, $filters) {
    $dir = ($filters['sort'] === $col && $filters['dir'] !== 'asc') ? 'asc' : 'desc';
    $url = $filterUrl(array('sort' => $col, 'dir' => $dir, 'analyze' => '1'));
    $mark = $filters['sort'] === $col ? ($filters['dir'] === 'asc' ? ' ↑' : ' ↓') : '';
    return '<a href="' . htmlspecialchars($url) . '">' . $label . $mark . '</a>';
};
$rowClass = function ($status) {
    if ($status === 'above_target') {
        return 'spa-row-above';
    }
    if ($status === 'below_target') {
        return 'spa-row-below';
    }
    return 'spa-row-loss';
};
?>
<style>
.spa-subtitle { color:#888; margin:4px 0 0; }
.spa-mode { display:inline-block; padding:4px 10px; border-radius:12px; font-size:12px; font-weight:600; }
.spa-mode-per { background:#e8f8f5; color:#1ab394; }
.spa-mode-shared { background:#eaf2ff; color:#1c84c6; }
.spa-row-above td { background:#edfbf6; }
.spa-row-below td { background:#fff8e5; }
.spa-row-loss td { background:#fdeeee; }
.spa-table td, .spa-table th { white-space:nowrap; }
.spa-name { white-space:normal !important; max-width:220px; }
.spa-metric h2 { margin:4px 0 0; }
.spa-rate { font-size:16px; font-weight:600; }
.spa-help { color:#888; font-size:12px; display:block; margin-top:4px; }
.spa-chart { height:240px; }
</style>
<div class="row wrapper border-bottom white-bg page-heading">
    <div class="col-lg-8">
        <h2>Store Profitability Analyzer</h2>
        <p class="spa-subtitle">Analyze store products, advertising cost and expected profitability.</p>
        <ol class="breadcrumb">
            <li><a href="<?= base_url('admin/admin') ?>">Dashboard</a></li>
            <li class="active"><strong>Store Profitability</strong></li>
        </ol>
    </div>
    <?php if (!empty($filters['store_id'])): ?>
    <div class="col-lg-4" style="padding-top:18px;">
        <form method="post" action="<?= base_url('admin/store-profitability/fix-profit') ?>">
            <input type="hidden" name="country_id" value="<?= (int) $filters['country_id'] ?>">
            <input type="hidden" name="store_id" value="<?= (int) $filters['store_id'] ?>">
            <input type="hidden" name="budget_mode" value="<?= htmlspecialchars($settings['budget_mode']) ?>">
            <input type="hidden" name="ads_budget" value="<?= htmlspecialchars($settings['ads_budget']) ?>">
            <input type="hidden" name="ads_currency" value="<?= htmlspecialchars($settings['ads_currency']) ?>">
            <input type="hidden" name="expected_orders" value="<?= htmlspecialchars($settings['expected_orders']) ?>">
            <input type="hidden" name="target_profit" value="<?= htmlspecialchars($settings['target_profit']) ?>">
            <button type="submit" class="btn btn-warning btn-lg btn-block">Fix Profit — all products</button>
            <span class="spa-help">One click raises extra amount on every loss / below-target listing in this store until target profit. Profitable products stay unchanged.</span>
        </form>
    </div>
    <?php endif; ?>
</div>
<div class="wrapper wrapper-content">
    <?php $this->load->view('flash'); ?>

    <form method="post" action="<?= base_url('admin/store-profitability/analyze') ?>" class="form-horizontal">
        <div class="ibox">
            <div class="ibox-title"><h5>1. Country and store</h5></div>
            <div class="ibox-content">
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="control-label">Country</label>
                            <select name="country_id" id="spa-country" class="form-control">
                                <option value="">All Countries</option>
                                <?php foreach ($countries as $country): ?>
                                <option value="<?= (int) $country->id ?>" <?= (int) $filters['country_id'] === (int) $country->id ? 'selected' : '' ?>><?= htmlspecialchars($country->name) ?><?= !empty($country->currency) ? ' (' . htmlspecialchars($country->currency) . ')' : '' ?></option>
                                <?php endforeach; ?>
                            </select>
                            <span class="spa-help">Changing country reloads only stores in that country.</span>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="control-label">Store</label>
                            <select name="store_id" id="spa-store" class="form-control" required>
                                <option value="">Select Store</option>
                                <?php foreach ($stores as $option): ?>
                                <option value="<?= (int) $option->id ?>" data-currency="<?= htmlspecialchars($option->country_currency) ?>" <?= (int) $filters['store_id'] === (int) $option->id ? 'selected' : '' ?>><?= htmlspecialchars($option->name) ?></option>
                                <?php endforeach; ?>
                            </select>
                            <span class="spa-help">Products are loaded from Store Listings for the selected store only.</span>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="control-label">Store currency</label>
                            <p class="form-control-static spa-rate" id="spa-store-currency"><?= htmlspecialchars($storeCurrency) ?></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="ibox">
            <div class="ibox-title">
                <h5>2. Analysis settings</h5>
                <span class="pull-right spa-mode <?= $mode === 'shared' ? 'spa-mode-shared' : 'spa-mode-per' ?>"><?= $mode === 'shared' ? 'Mode: Shared Store Budget' : 'Mode: Budget Per Product' ?></span>
            </div>
            <div class="ibox-content">
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="control-label">Budget mode</label>
                            <select name="budget_mode" id="spa-mode" class="form-control">
                                <option value="per_product" <?= $mode === 'per_product' ? 'selected' : '' ?>>B. Budget Per Product</option>
                                <option value="shared" <?= $mode === 'shared' ? 'selected' : '' ?>>A. Shared Store Budget</option>
                            </select>
                            <span class="spa-help" id="spa-mode-help">
                                <?php if ($mode === 'shared'): ?>
                                The amount below is the entire store ads budget, not per product.
                                <?php else: ?>
                                The amount below is assigned to each product separately. It is not the store total.
                                <?php endif; ?>
                            </span>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="control-label" id="spa-budget-label"><?= $mode === 'shared' ? 'Shared Store Daily Ads Budget' : 'Ads Budget Per Product / Day' ?></label>
                            <input type="number" step="0.01" min="0" name="ads_budget" class="form-control" value="<?= htmlspecialchars($settings['ads_budget']) ?>">
                            <span class="spa-help" id="spa-budget-help"><?= $mode === 'shared' ? 'Example: 6000 PKR/day for the whole store.' : 'Example: 1000 PKR/day per product. 5 products = 5000 PKR total.' ?></span>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="control-label">Ads Budget Currency</label>
                            <select name="ads_currency" class="form-control">
                                <?php foreach ($currencies as $code): ?>
                                <option value="<?= htmlspecialchars($code) ?>" <?= $settings['ads_currency'] === $code ? 'selected' : '' ?>><?= htmlspecialchars($code) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="control-label" id="spa-orders-label"><?= $mode === 'shared' ? 'Expected Store Orders / Day' : 'Expected Orders Per Product / Day' ?></label>
                            <input type="number" step="0.01" min="0.01" name="expected_orders" class="form-control" value="<?= htmlspecialchars($settings['expected_orders']) ?>">
                            <span class="spa-help">CPA = ads budget ÷ expected orders, then converted into the store currency.</span>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="control-label">Target profit (store currency)</label>
                            <input type="number" step="0.01" name="target_profit" class="form-control" value="<?= htmlspecialchars($settings['target_profit']) ?>">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="control-label">&nbsp;</label>
                            <button type="submit" class="btn btn-primary btn-block btn-lg">Analyze Store</button>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="control-label">&nbsp;</label>
                            <button type="submit" formaction="<?= base_url('admin/store-profitability/fix-profit') ?>" class="btn btn-warning btn-block btn-lg">Fix Profit</button>
                            <span class="spa-help">One click: every loss / below-target product in this store gets extra amount until target profit. Already profitable products stay as they are.</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>

    <div class="ibox">
        <div class="ibox-title"><h5>3. Conversion rate (manual)</h5></div>
        <div class="ibox-content">
            <form method="post" action="<?= base_url('admin/store-profitability/apply-rate') ?>" class="form-inline">
                <input type="hidden" name="return_query" value="<?= htmlspecialchars(http_build_query(array_filter($keep))) ?>">
                <div class="form-group">
                    <label>Currency → PKR Rate</label>
                    <select name="rate_currency" class="form-control" style="min-width:120px;">
                        <?php foreach ($currencies as $code): if ($code === 'PKR') { continue; } ?>
                        <option value="<?= htmlspecialchars($code) ?>" <?= $code === $storeCurrency ? 'selected' : '' ?>><?= htmlspecialchars($code) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <input type="number" step="0.0001" min="0.0001" name="rate_to_pkr" class="form-control" value="<?= $rate > 0 ? htmlspecialchars($rate) : '33' ?>" placeholder="33">
                </div>
                <button type="submit" class="btn btn-success">Save / Apply Rate</button>
            </form>
            <p class="spa-rate m-t-sm">1 <?= htmlspecialchars($storeCurrency) ?> = <?= $rate > 0 ? number_format($rate, 4) : '—' ?> PKR</p>
            <span class="spa-help">This page uses the rate you enter. It is not a live exchange rate and does not change Product Analyzer settings.</span>
        </div>
    </div>

    <?php if ($filters['analyze'] !== '1' || !$store): ?>
    <div class="ibox">
        <div class="ibox-content text-muted">
            Select a country and store, set ads budget mode, then click <strong>Analyze Store</strong>. Products come from Store Listings. Selling price is the store listed price, not the catalog cost.
        </div>
    </div>
    <?php else: ?>
        <?php $s = $summary; $cur = $s['currency'] ?: $storeCurrency; $adsCur = $s['ads_currency']; ?>
        <div class="alert alert-info">
            Active mode: <strong><?= $mode === 'shared' ? 'Shared Store Budget' : 'Budget Per Product' ?></strong>
            · Store: <strong><?= htmlspecialchars($store->name) ?></strong>
            · 1 <?= htmlspecialchars($cur) ?> = <?= $rate > 0 ? number_format($rate, 4) : '—' ?> PKR
            <?php if ($mode === 'per_product'): ?>
            · Ads budget per product: <?= number_format((float) $settings['ads_budget'], 2) ?> <?= htmlspecialchars($adsCur) ?>/day
            · Total potential ads: <?= number_format((float) $s['total_ads'], 2) ?> <?= htmlspecialchars($adsCur) ?>/day
            <?php else: ?>
            · Store ads budget: <?= number_format((float) $settings['ads_budget'], 2) ?> <?= htmlspecialchars($adsCur) ?>/day for the whole store
            · CPA: <?= $settings['expected_orders'] > 0 ? number_format($settings['ads_budget'] / $settings['expected_orders'], 2) : '—' ?> <?= htmlspecialchars($adsCur) ?>/order
            <?php endif; ?>
        </div>

        <div class="row">
            <div class="col-md-2"><div class="ibox"><div class="ibox-content spa-metric"><h5>Total Products</h5><h2><?= (int) $s['total'] ?></h2></div></div></div>
            <div class="col-md-2"><div class="ibox"><div class="ibox-content spa-metric"><h5>Profitable</h5><h2 class="text-navy"><?= (int) $s['above'] ?></h2></div></div></div>
            <div class="col-md-2"><div class="ibox"><div class="ibox-content spa-metric"><h5>Below Target</h5><h2 class="text-warning"><?= (int) $s['below'] ?></h2></div></div></div>
            <div class="col-md-2"><div class="ibox"><div class="ibox-content spa-metric"><h5>Loss</h5><h2 class="text-danger"><?= (int) $s['loss'] ?></h2></div></div></div>
            <div class="col-md-2"><div class="ibox"><div class="ibox-content spa-metric"><h5>Avg Net Profit / Product</h5><h2><?= $s['mixed'] ? '—' : profit_money($s['avg_native'], $cur) ?></h2></div></div></div>
            <div class="col-md-2"><div class="ibox"><div class="ibox-content spa-metric"><h5>Avg Net Profit / Product</h5><h2><?= number_format((float) $s['avg_pkr'], 2) ?> PKR</h2><small class="text-muted">Native profit converted first</small></div></div></div>
            <div class="col-md-2"><div class="ibox"><div class="ibox-content spa-metric"><h5>Average Margin</h5><h2><?= profit_pct($s['avg_margin']) ?></h2></div></div></div>
            <div class="col-md-2"><div class="ibox"><div class="ibox-content spa-metric"><h5>Total Potential Profit</h5><h2><?= number_format((float) $s['total_pkr'], 2) ?> PKR</h2></div></div></div>
            <div class="col-md-2"><div class="ibox"><div class="ibox-content spa-metric"><h5>Average CPA</h5><h2><?= number_format((float) $s['avg_cpa_pkr'], 2) ?> PKR</h2></div></div></div>
            <div class="col-md-2"><div class="ibox"><div class="ibox-content spa-metric"><h5>Avg Product Cost</h5><h2><?= $s['mixed'] ? '—' : profit_money($s['avg_cost'], $cur) ?></h2></div></div></div>
            <div class="col-md-2"><div class="ibox"><div class="ibox-content spa-metric"><h5>Avg Selling Price</h5><h2><?= $s['mixed'] ? '—' : profit_money($s['avg_price'], $cur) ?></h2></div></div></div>
            <div class="col-md-2"><div class="ibox"><div class="ibox-content spa-metric"><h5>Total Daily Ads Spend</h5><h2><?= number_format((float) $s['total_ads'], 2) ?> <?= htmlspecialchars($adsCur) ?></h2></div></div></div>
        </div>

        <div class="row">
            <div class="col-md-2"><div class="ibox"><div class="ibox-content spa-metric"><h5>Expected Daily Orders</h5><h2><?= number_format((float) $s['total_orders'], 2) ?></h2></div></div></div>
            <div class="col-md-2"><div class="ibox"><div class="ibox-content spa-metric"><h5>Est. Daily Revenue</h5><h2><?= $s['mixed'] ? '—' : profit_money($s['total_revenue'], $cur) ?></h2></div></div></div>
            <div class="col-md-2"><div class="ibox"><div class="ibox-content spa-metric"><h5>Est. Daily Product Cost</h5><h2><?= $s['mixed'] ? '—' : profit_money($s['total_product_cost'], $cur) ?></h2></div></div></div>
            <div class="col-md-2"><div class="ibox"><div class="ibox-content spa-metric"><h5>Est. Daily Net Profit</h5><h2><?= number_format((float) $s['total_daily_pkr'], 2) ?> PKR</h2></div></div></div>
            <div class="col-md-2"><div class="ibox"><div class="ibox-content spa-metric"><h5>Highest Net Profit</h5><h2 class="text-navy" style="font-size:16px;"><?= $s['highest'] ? htmlspecialchars($s['highest']->analyzer_id) : '—' ?></h2><small><?= $s['highest'] ? profit_money($s['highest']->metrics['net_profit'], $s['highest']->currency) : '' ?></small></div></div></div>
            <div class="col-md-2"><div class="ibox"><div class="ibox-content spa-metric"><h5>Lowest Net Profit</h5><h2 class="text-danger" style="font-size:16px;"><?= $s['lowest'] ? htmlspecialchars($s['lowest']->analyzer_id) : '—' ?></h2><small><?= $s['lowest'] ? profit_money($s['lowest']->metrics['net_profit'], $s['lowest']->currency) : '' ?></small></div></div></div>
        </div>

        <?php $fixCount = (int) $s['loss'] + (int) $s['below']; ?>
        <div class="ibox">
            <div class="ibox-title"><h5>Fix Profit</h5></div>
            <div class="ibox-content">
                <form method="post" action="<?= base_url('admin/store-profitability/fix-profit') ?>">
                    <input type="hidden" name="country_id" value="<?= (int) $filters['country_id'] ?>">
                    <input type="hidden" name="store_id" value="<?= (int) $filters['store_id'] ?>">
                    <input type="hidden" name="budget_mode" value="<?= htmlspecialchars($settings['budget_mode']) ?>">
                    <input type="hidden" name="ads_budget" value="<?= htmlspecialchars($settings['ads_budget']) ?>">
                    <input type="hidden" name="ads_currency" value="<?= htmlspecialchars($settings['ads_currency']) ?>">
                    <input type="hidden" name="expected_orders" value="<?= htmlspecialchars($settings['expected_orders']) ?>">
                    <input type="hidden" name="target_profit" value="<?= htmlspecialchars($settings['target_profit']) ?>">
                    <button type="submit" class="btn btn-warning btn-lg" <?= $fixCount < 1 ? 'disabled' : '' ?>>Fix Profit</button>
                    <span class="spa-help" style="display:inline-block;margin-left:12px;max-width:720px;">
                        One click fixes all <?= (int) $s['total'] ?> store products: extra amount is added on <strong>Loss</strong> and <strong>Below Target</strong> until net profit reaches <?= profit_money($settings['target_profit'], $cur) ?>. Profitable products are not changed.
                    </span>
                </form>
            </div>
        </div>

        <div class="row">
            <div class="col-md-4"><div class="ibox"><div class="ibox-title"><h5>Products by status</h5></div><div class="ibox-content"><canvas id="spaStatus" class="spa-chart"></canvas></div></div></div>
            <div class="col-md-4"><div class="ibox"><div class="ibox-title"><h5>Profit distribution (PKR)</h5></div><div class="ibox-content"><canvas id="spaProfit" class="spa-chart"></canvas></div></div></div>
            <div class="col-md-4"><div class="ibox"><div class="ibox-title"><h5>Average margin</h5></div><div class="ibox-content"><canvas id="spaMargin" class="spa-chart"></canvas></div></div></div>
            <div class="col-md-6"><div class="ibox"><div class="ibox-title"><h5>CPA vs net profit</h5></div><div class="ibox-content"><canvas id="spaCpa" class="spa-chart"></canvas></div></div></div>
            <div class="col-md-6"><div class="ibox"><div class="ibox-title"><h5>Product cost vs selling price</h5></div><div class="ibox-content"><canvas id="spaPrice" class="spa-chart"></canvas></div></div></div>
        </div>

        <div class="ibox">
            <div class="ibox-title"><h5>Store products overview — highest net profit</h5></div>
            <div class="ibox-content">
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead><tr><th>Product</th><th>Selling Price</th><th>Product Cost</th><th>CPA</th><th>Net Profit</th><th>Margin</th><th>Status</th></tr></thead>
                        <tbody>
                        <?php foreach ($s['top'] as $item): $m = $item->metrics; ?>
                        <tr>
                            <td><?= htmlspecialchars($item->analyzer_id . ' · ' . $item->name) ?></td>
                            <td><?= profit_money($m['selling_price'], $item->currency) ?></td>
                            <td><?= profit_money($m['product_cost'], $item->currency) ?></td>
                            <td><?= profit_money($m['cpa'], $item->currency) ?></td>
                            <td><?= profit_money($m['net_profit'], $item->currency) ?></td>
                            <td><?= profit_pct($m['profit_margin']) ?></td>
                            <td><span class="label <?= profit_status_class($m['status']) ?>"><?= profit_status_label($m['status']) ?></span></td>
                        </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="ibox">
            <div class="ibox-title"><h5>Lowest net profit</h5></div>
            <div class="ibox-content">
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead><tr><th>Product</th><th>Selling Price</th><th>Product Cost</th><th>CPA</th><th>Net Profit</th><th>Margin</th><th>Status</th></tr></thead>
                        <tbody>
                        <?php foreach ($s['bottom'] as $item): $m = $item->metrics; ?>
                        <tr>
                            <td><?= htmlspecialchars($item->analyzer_id . ' · ' . $item->name) ?></td>
                            <td><?= profit_money($m['selling_price'], $item->currency) ?></td>
                            <td><?= profit_money($m['product_cost'], $item->currency) ?></td>
                            <td><?= profit_money($m['cpa'], $item->currency) ?></td>
                            <td><?= profit_money($m['net_profit'], $item->currency) ?></td>
                            <td><?= profit_pct($m['profit_margin']) ?></td>
                            <td><span class="label <?= profit_status_class($m['status']) ?>"><?= profit_status_label($m['status']) ?></span></td>
                        </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="ibox">
            <div class="ibox-title"><h5>Product filters</h5></div>
            <div class="ibox-content">
                <form method="get" action="<?= base_url('admin/store-profitability') ?>" class="form-horizontal">
                    <input type="hidden" name="country_id" value="<?= (int) $filters['country_id'] ?>">
                    <input type="hidden" name="store_id" value="<?= (int) $filters['store_id'] ?>">
                    <input type="hidden" name="budget_mode" value="<?= htmlspecialchars($settings['budget_mode']) ?>">
                    <input type="hidden" name="ads_budget" value="<?= htmlspecialchars($settings['ads_budget']) ?>">
                    <input type="hidden" name="ads_currency" value="<?= htmlspecialchars($settings['ads_currency']) ?>">
                    <input type="hidden" name="expected_orders" value="<?= htmlspecialchars($settings['expected_orders']) ?>">
                    <input type="hidden" name="analyze" value="1">
                    <div class="row">
                        <div class="col-md-3"><div class="form-group"><label class="control-label">Search / Product ID</label><input type="text" name="q" class="form-control" value="<?= htmlspecialchars($filters['q']) ?>"></div></div>
                        <div class="col-md-3"><div class="form-group"><label class="control-label">Category</label>
                            <select name="category_id" class="form-control"><option value="">All</option>
                            <?php foreach ($categories as $category): ?>
                            <option value="<?= (int) $category->id ?>" <?= (int) $filters['category_id'] === (int) $category->id ? 'selected' : '' ?>><?= htmlspecialchars($category->name) ?></option>
                            <?php endforeach; ?>
                            </select></div></div>
                        <div class="col-md-3"><div class="form-group"><label class="control-label">Supplier</label>
                            <select name="supplier_id" class="form-control"><option value="">All</option>
                            <?php foreach ($suppliers as $supplier): ?>
                            <option value="<?= (int) $supplier->id ?>" <?= (int) $filters['supplier_id'] === (int) $supplier->id ? 'selected' : '' ?>><?= htmlspecialchars($supplier->name) ?></option>
                            <?php endforeach; ?>
                            </select></div></div>
                        <div class="col-md-3"><div class="form-group"><label class="control-label">Status</label>
                            <select name="status" class="form-control">
                                <option value="">All</option>
                                <option value="above_target" <?= $filters['status'] === 'above_target' ? 'selected' : '' ?>>Profitable</option>
                                <option value="below_target" <?= $filters['status'] === 'below_target' ? 'selected' : '' ?>>Below Target</option>
                                <option value="loss" <?= $filters['status'] === 'loss' ? 'selected' : '' ?>>Loss</option>
                            </select></div></div>
                        <div class="col-md-3"><div class="form-group"><label class="control-label">Min / Max profit</label>
                            <div class="row"><div class="col-xs-6"><input type="number" step="0.01" name="min_profit" class="form-control" value="<?= htmlspecialchars($filters['min_profit']) ?>" placeholder="Min"></div>
                            <div class="col-xs-6"><input type="number" step="0.01" name="max_profit" class="form-control" value="<?= htmlspecialchars($filters['max_profit']) ?>" placeholder="Max"></div></div></div></div>
                        <div class="col-md-3"><div class="form-group"><label class="control-label">Min / Max margin %</label>
                            <div class="row"><div class="col-xs-6"><input type="number" step="0.1" name="min_margin" class="form-control" value="<?= htmlspecialchars($filters['min_margin']) ?>" placeholder="Min"></div>
                            <div class="col-xs-6"><input type="number" step="0.1" name="max_margin" class="form-control" value="<?= htmlspecialchars($filters['max_margin']) ?>" placeholder="Max"></div></div></div></div>
                        <div class="col-md-3"><div class="form-group"><label class="control-label">&nbsp;</label><button class="btn btn-primary btn-block" type="submit">Apply</button></div></div>
                    </div>
                </form>
            </div>
        </div>

        <div class="ibox">
            <div class="ibox-title">
                <h5>Store product table</h5>
                <span class="label label-primary pull-right"><?= (int) $total ?> products</span>
            </div>
            <div class="ibox-content">
                <div class="table-responsive">
                    <table class="table table-striped table-hover spa-table">
                        <thead>
                            <tr>
                                <th>Product ID</th>
                                <th>Product</th>
                                <th>Store</th>
                                <th>Country</th>
                                <th>Category</th>
                                <th>Supplier</th>
                                <th><?= $sortLink('product_cost', 'Product Cost') ?></th>
                                <th><?= $sortLink('shipping', 'Shipping') ?></th>
                                <th><?= $sortLink('selling_price', 'Selling Price') ?></th>
                                <th>Ads Budget</th>
                                <th><?= $sortLink('cpa', 'CPA') ?></th>
                                <th>Payment Fee</th>
                                <th>Return Allowance</th>
                                <th>Other Cost</th>
                                <th>Total Cost</th>
                                <th><?= $sortLink('net_profit', 'Net Profit') ?></th>
                                <th><?= $sortLink('margin', 'Profit Margin') ?></th>
                                <th><?= $sortLink('pkr_profit', 'PKR Profit') ?></th>
                                <th>Target Profit</th>
                                <th><?= $sortLink('profit_gap', 'Profit Gap') ?></th>
                                <th>Status</th>
                                <th>Details</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($items as $item):
                                $m = $item->metrics;
                                $cc = $item->currency;
                            ?>
                            <tr class="<?= $rowClass($m['status']) ?>">
                                <td><code><?= htmlspecialchars($item->analyzer_id) ?></code></td>
                                <td class="spa-name"><strong><?= htmlspecialchars($item->name) ?></strong></td>
                                <td><?= htmlspecialchars($item->store_name) ?></td>
                                <td><?= htmlspecialchars($item->country_name) ?></td>
                                <td><?= htmlspecialchars($item->category_names) ?></td>
                                <td><?= htmlspecialchars($item->supplier_name) ?></td>
                                <td><?= profit_money($m['product_cost'], $cc) ?></td>
                                <td><?= profit_money($m['shipping_cost'], $cc) ?></td>
                                <td><?= profit_money($m['selling_price'], $cc) ?></td>
                                <td><?= $mode === 'shared' ? ('Shared · ' . number_format((float) $item->ads_budget, 2) . ' ' . htmlspecialchars($item->ads_currency)) : (number_format((float) $item->ads_budget, 2) . ' ' . htmlspecialchars($item->ads_currency) . '/product') ?></td>
                                <td><?= profit_money($m['cpa'], $cc) ?></td>
                                <td><?= profit_money($m['payment_fee'], $cc) ?></td>
                                <td><?= profit_money($m['return_allowance'], $cc) ?></td>
                                <td><?= profit_money($settings['other_cost'], $cc) ?></td>
                                <td><?= profit_money($m['total_cost_before_ads'] + $m['cpa'], $cc) ?></td>
                                <td><strong><?= profit_money($m['net_profit'], $cc) ?></strong></td>
                                <td><?= profit_pct($m['profit_margin']) ?></td>
                                <td><?= $m['local_profit'] === null ? '—' : profit_money($m['local_profit'], 'PKR') ?></td>
                                <td><?= profit_money($m['target_profit'], $cc) ?></td>
                                <td><?= profit_money($m['profit_gap'], $cc) ?></td>
                                <td><span class="label <?= profit_status_class($m['status']) ?>"><?= profit_status_label($m['status']) ?></span></td>
                                <td><a class="btn btn-xs btn-primary" href="<?= base_url('admin/store-profitability/product/' . (int) $item->id) ?>?store_id=<?= (int) $item->store_id ?>&listing_id=<?= (int) $item->listing_id ?>&budget_mode=<?= urlencode($settings['budget_mode']) ?>&ads_budget=<?= urlencode($settings['ads_budget']) ?>&ads_currency=<?= urlencode($settings['ads_currency']) ?>&expected_orders=<?= urlencode($settings['expected_orders']) ?>">View Details</a></td>
                            </tr>
                            <?php endforeach; ?>
                            <?php if (empty($items)): ?>
                            <tr><td colspan="22" class="text-center text-muted">No store listings match these filters.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
                <?php if ($pages > 1):
                    $qs = http_build_query(array_filter($keep + $filters, function ($v) { return $v !== '' && $v !== null; }));
                ?>
                <ul class="pagination">
                    <?php for ($i = max(1, $page - 4); $i <= min($pages, $page + 4); $i++): ?>
                    <li class="<?= $i === $page ? 'active' : '' ?>"><a href="<?= base_url('admin/store-profitability') ?>?<?= $qs ? $qs . '&' : '' ?>page=<?= $i ?>"><?= $i ?></a></li>
                    <?php endfor; ?>
                </ul>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>
</div>
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.9.4/Chart.min.js"></script>
<script>
$(function () {
    var storesUrl = <?= json_encode(base_url('admin/store-profitability/stores')) ?>;
    function modeUi() {
        var mode = $('#spa-mode').val();
        if (mode === 'shared') {
            $('#spa-budget-label').text('Shared Store Daily Ads Budget');
            $('#spa-budget-help').text('Example: 6000 PKR/day for the whole store. This is not per product.');
            $('#spa-orders-label').text('Expected Store Orders / Day');
            $('#spa-mode-help').text('The amount is the entire store ads budget, not per product.');
        } else {
            $('#spa-budget-label').text('Ads Budget Per Product / Day');
            $('#spa-budget-help').text('Example: 1000 PKR/day per product. 5 products = 5000 PKR total.');
            $('#spa-orders-label').text('Expected Orders Per Product / Day');
            $('#spa-mode-help').text('The amount is assigned to each product separately. It is not the store total.');
        }
    }
    $('#spa-mode').on('change', modeUi);
    $('#spa-country').on('change', function () {
        var countryId = $(this).val() || 0;
        var $store = $('#spa-store');
        $store.html('<option value="">Select Store</option>');
        $.getJSON(storesUrl, {country_id: countryId}, function (rows) {
            $.each(rows || [], function (_, row) {
                $store.append($('<option/>').val(row.id).text(row.name).attr('data-currency', row.currency || ''));
            });
        });
    });
    $('#spa-store').on('change', function () {
        var cur = $(this).find('option:selected').data('currency');
        if (cur) { $('#spa-store-currency').text(cur); }
    });
<?php if ($summary && !empty($chartItems)):
    $scatterCpa = array();
    $scatterPrice = array();
    $limitPoints = 0;
    foreach ($chartItems as $item) {
        if ($limitPoints >= 80) {
            break;
        }
        $scatterCpa[] = array('x' => (float) $item->metrics['cpa'], 'y' => (float) $item->metrics['net_profit']);
        $scatterPrice[] = array('x' => (float) $item->metrics['product_cost'], 'y' => (float) $item->metrics['selling_price']);
        $limitPoints++;
    }
?>
    if (window.Chart) {
        new Chart(document.getElementById('spaStatus'), {type:'doughnut', data:{labels:['Profitable','Below Target','Loss'], datasets:[{data:[<?= (int)$summary['above'] ?>, <?= (int)$summary['below'] ?>, <?= (int)$summary['loss'] ?>], backgroundColor:['#1ab394','#f8ac59','#ed5565']}]}, options:{legend:{position:'bottom'}}});
        new Chart(document.getElementById('spaProfit'), {type:'bar', data:{labels:['Avg PKR profit'], datasets:[{label:'PKR', data:[<?= json_encode((float)$summary['avg_pkr']) ?>], backgroundColor:'#1c84c6'}]}, options:{legend:{display:false}, scales:{yAxes:[{ticks:{beginAtZero:true}}]}}});
        new Chart(document.getElementById('spaMargin'), {type:'bar', data:{labels:['Average margin %'], datasets:[{data:[<?= json_encode((float)$summary['avg_margin']) ?>], backgroundColor:'#23c6c8'}]}, options:{legend:{display:false}, scales:{yAxes:[{ticks:{beginAtZero:true}}]}}});
        new Chart(document.getElementById('spaCpa'), {type:'scatter', data:{datasets:[{label:'CPA vs profit', data:<?= json_encode($scatterCpa) ?>, backgroundColor:'#1ab394'}]}, options:{scales:{xAxes:[{scaleLabel:{display:true,labelString:'CPA'}}], yAxes:[{scaleLabel:{display:true,labelString:'Net profit'}}]}}});
        new Chart(document.getElementById('spaPrice'), {type:'scatter', data:{datasets:[{label:'Cost vs price', data:<?= json_encode($scatterPrice) ?>, backgroundColor:'#1c84c6'}]}, options:{scales:{xAxes:[{scaleLabel:{display:true,labelString:'Product cost'}}], yAxes:[{scaleLabel:{display:true,labelString:'Selling price'}}]}}});
    }
<?php endif; ?>
});
</script>
