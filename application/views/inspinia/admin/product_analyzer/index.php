<?php
$filters = isset($filters) ? $filters : array();
$page = isset($page) ? (int) $page : 1;
$limit = isset($limit) ? (int) $limit : 50;
$total = isset($total) ? (int) $total : 0;
$pages = max(1, (int) ceil($total / max(1, $limit)));
$qs = $filters;
unset($qs['page']);
$query = http_build_query(array_filter($qs, function ($v) {
    return $v !== '' && $v !== null;
}));
$productPreview = isset($product_preview) ? $product_preview : array();
$statusNow = isset($filters['status']) ? profit_status_key($filters['status']) : '';
$storeNow = isset($filters['store_id']) ? (int) $filters['store_id'] : 0;
$stores = isset($stores) ? $stores : array();
$storeSummaries = isset($store_summaries) ? $store_summaries : array();
$allStoreCount = isset($all_store_count) ? (int) $all_store_count : (int) $metrics['total'];
$filterUrl = function ($overrides = array()) use ($filters) {
    $q = $filters;
    unset($q['page']);
    foreach ($overrides as $key => $value) {
        $q[$key] = $value;
    }
    $q = array_filter($q, function ($v) {
        return $v !== '' && $v !== null && $v !== 0 && $v !== '0';
    });
    $built = http_build_query($q);
    return base_url('admin/product-analyzer') . ($built ? ('?' . $built) : '');
};
$this->load->view('inspinia/admin/product_analyzer/_nav', array('section' => 'products'));
?>
    <div class="row pa-store-strip">
        <div class="col-md-3">
            <a class="pa-card-link pa-store-card <?= $storeNow === 0 ? 'pa-card-active' : '' ?>" href="<?= htmlspecialchars($filterUrl(array('store_id' => ''))) ?>">
                <div class="ibox"><div class="ibox-content pa-metric">
                    <h5>All Stores</h5>
                    <h2><?= (int) $allStoreCount ?></h2>
                    <small class="text-muted"><?= (int) $allStoreCount === 1 ? 'product' : 'products' ?> across every store listing</small>
                </div></div>
            </a>
        </div>
        <?php foreach ($storeSummaries as $summary):
            if ((int) $summary['products'] < 1 && $storeNow !== (int) $summary['store_id']) {
                continue;
            }
        ?>
        <div class="col-md-3">
            <a class="pa-card-link pa-store-card <?= $storeNow === (int) $summary['store_id'] ? 'pa-card-active' : '' ?>" href="<?= htmlspecialchars($filterUrl(array('store_id' => (int) $summary['store_id']))) ?>">
                <div class="ibox"><div class="ibox-content">
                    <h4 class="pa-store-name"><?= htmlspecialchars($summary['store_name']) ?></h4>
                    <div><strong><?= (int) $summary['products'] ?></strong> <?= (int) $summary['products'] === 1 ? 'product' : 'products' ?></div>
                    <div class="pa-store-stats">
                        <span class="text-navy">Profitable: <?= (int) $summary['above'] ?></span>
                        <span class="text-warning">Below Target: <?= (int) $summary['below'] ?></span>
                        <span class="text-danger">Loss: <?= (int) $summary['loss'] ?></span>
                    </div>
                </div></div>
            </a>
        </div>
        <?php endforeach; ?>
        <?php if (empty($storeSummaries)): ?>
        <div class="col-md-9">
            <div class="ibox"><div class="ibox-content text-muted">No store listings found. Product Analyzer uses Store Listings for store, selling price, and profitability.</div></div>
        </div>
        <?php endif; ?>
    </div>

    <div class="row">
        <div class="col-md-3">
            <a class="pa-card-link <?= $statusNow === '' && empty($filters['cheaper']) ? 'pa-card-active' : '' ?>" href="<?= htmlspecialchars($filterUrl(array('status' => '', 'cheaper' => ''))) ?>">
                <div class="ibox"><div class="ibox-content pa-metric">
                    <h5>Total Products</h5>
                    <h2><?= (int) $metrics['total'] ?></h2>
                    <small class="text-muted">View all products</small>
                </div></div>
            </a>
        </div>
        <div class="col-md-3">
            <a class="pa-card-link <?= $statusNow === 'above_target' ? 'pa-card-active' : '' ?>" href="<?= htmlspecialchars($filterUrl(array('status' => 'above_target', 'cheaper' => ''))) ?>">
                <div class="ibox"><div class="ibox-content pa-metric">
                    <h5>Profitable / Above Target</h5>
                    <h2 class="text-navy"><?= (int) $metrics['above'] ?></h2>
                    <small class="text-muted">View Profitable Products</small>
                </div></div>
            </a>
        </div>
        <div class="col-md-3">
            <a class="pa-card-link <?= $statusNow === 'below_target' ? 'pa-card-active' : '' ?>" href="<?= htmlspecialchars($filterUrl(array('status' => 'below_target', 'cheaper' => ''))) ?>">
                <div class="ibox"><div class="ibox-content pa-metric">
                    <h5>Below Target</h5>
                    <h2 class="text-warning"><?= (int) $metrics['below'] ?></h2>
                    <small class="text-muted">View Below Target</small>
                </div></div>
            </a>
        </div>
        <div class="col-md-3">
            <a class="pa-card-link <?= $statusNow === 'loss' ? 'pa-card-active' : '' ?>" href="<?= htmlspecialchars($filterUrl(array('status' => 'loss', 'cheaper' => ''))) ?>">
                <div class="ibox"><div class="ibox-content pa-metric">
                    <h5>Loss</h5>
                    <h2 class="text-danger"><?= (int) $metrics['loss'] ?></h2>
                    <small class="text-muted">View Loss Products</small>
                </div></div>
            </a>
        </div>
        <div class="col-md-3">
            <div class="ibox"><div class="ibox-content pa-metric">
                <h5>Average Net Profit (PKR)</h5>
                <h2><?= number_format((float) $metrics['avg_profit'], 2) ?></h2>
                <small class="text-muted">Native profit converted to PKR first</small>
            </div></div>
        </div>
        <div class="col-md-3">
            <div class="ibox"><div class="ibox-content pa-metric">
                <h5>Average Profit Margin</h5>
                <h2><?= profit_pct($metrics['avg_margin']) ?></h2>
                <small class="text-muted">After CPA</small>
            </div></div>
        </div>
        <div class="col-md-3">
            <div class="ibox"><div class="ibox-content pa-metric">
                <h5>Supplier Savings</h5>
                <h2 class="text-navy"><?= number_format((float) $metrics['savings'], 2) ?> PKR</h2>
                <small class="text-muted">If cheaper suppliers were used</small>
            </div></div>
        </div>
        <div class="col-md-3">
            <a class="pa-card-link <?= !empty($filters['cheaper']) ? 'pa-card-active' : '' ?>" href="<?= htmlspecialchars($filterUrl(array('cheaper' => '1', 'status' => ''))) ?>">
                <div class="ibox"><div class="ibox-content pa-metric">
                    <h5>Cheaper Suppliers</h5>
                    <h2><?= (int) $metrics['cheaper'] ?></h2>
                    <small class="text-muted">View cheaper supplier opportunities</small>
                </div></div>
            </a>
        </div>
    </div>

    <div class="pa-status-btns">
        <a class="btn <?= $statusNow === 'above_target' ? 'btn-primary' : 'btn-white' ?>" href="<?= htmlspecialchars($filterUrl(array('status' => 'above_target', 'cheaper' => ''))) ?>">View Profitable Products</a>
        <a class="btn <?= $statusNow === 'below_target' ? 'btn-warning' : 'btn-white' ?>" href="<?= htmlspecialchars($filterUrl(array('status' => 'below_target', 'cheaper' => ''))) ?>">View Below Target</a>
        <a class="btn <?= $statusNow === 'loss' ? 'btn-danger' : 'btn-white' ?>" href="<?= htmlspecialchars($filterUrl(array('status' => 'loss', 'cheaper' => ''))) ?>">View Loss Products</a>
        <a class="btn btn-white" href="<?= htmlspecialchars($filterUrl(array('status' => '', 'cheaper' => ''))) ?>">All</a>
    </div>

    <div class="ibox">
        <div class="ibox-title">
            <h5>Filters</h5>
            <div class="ibox-tools">
                <a href="<?= base_url('admin/product-analyzer') ?>">Reset</a>
            </div>
        </div>
        <div class="ibox-content">
            <p class="text-muted">Each row is a store listing. Profit is calculated from that store’s selling price and currency. Store, country, category, supplier and status can be combined. VAT is not included.</p>
            <form method="get" action="<?= base_url('admin/product-analyzer') ?>" class="form-horizontal">
                <div class="row">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label class="control-label">Search / Product ID</label>
                            <input type="text" name="q" class="form-control" value="<?= htmlspecialchars($filters['q']) ?>" placeholder="Product ID, SKU, name, supplier">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label class="control-label">Store</label>
                            <select name="store_id" class="form-control">
                                <option value="">All Stores</option>
                                <?php foreach ($stores as $store): ?>
                                <option value="<?= (int) $store->id ?>" <?= $storeNow === (int) $store->id ? 'selected' : '' ?>><?= htmlspecialchars($store->name) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label class="control-label">Country</label>
                            <select name="country_id" class="form-control">
                                <option value="">All countries</option>
                                <?php foreach ($countries as $country): ?>
                                <option value="<?= (int) $country->id ?>" <?= (int) $filters['country_id'] === (int) $country->id ? 'selected' : '' ?>><?= htmlspecialchars($country->name) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label class="control-label">Category</label>
                            <select name="category_id" class="form-control">
                                <option value="">All categories</option>
                                <?php foreach ($categories as $category): ?>
                                <option value="<?= (int) $category->id ?>" <?= (int) $filters['category_id'] === (int) $category->id ? 'selected' : '' ?>><?= htmlspecialchars($category->name) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label class="control-label">Supplier</label>
                            <select name="supplier_id" class="form-control">
                                <option value="">All suppliers</option>
                                <?php foreach ($suppliers as $supplier): ?>
                                <option value="<?= (int) $supplier->id ?>" <?= (int) $filters['supplier_id'] === (int) $supplier->id ? 'selected' : '' ?>><?= htmlspecialchars($supplier->name) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label class="control-label">Status</label>
                            <select name="status" class="form-control">
                                <option value="">All</option>
                                <option value="above_target" <?= $statusNow === 'above_target' ? 'selected' : '' ?>>Profitable</option>
                                <option value="below_target" <?= $statusNow === 'below_target' ? 'selected' : '' ?>>Below Target</option>
                                <option value="loss" <?= $statusNow === 'loss' ? 'selected' : '' ?>>Loss</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label class="control-label">Selling price</label>
                            <div class="row">
                                <div class="col-xs-6"><input type="number" step="0.01" name="min_price" class="form-control" placeholder="Min" value="<?= htmlspecialchars($filters['min_price']) ?>"></div>
                                <div class="col-xs-6"><input type="number" step="0.01" name="max_price" class="form-control" placeholder="Max" value="<?= htmlspecialchars($filters['max_price']) ?>"></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label class="control-label">Net profit</label>
                            <div class="row">
                                <div class="col-xs-6"><input type="number" step="0.01" name="min_profit" class="form-control" placeholder="Min" value="<?= htmlspecialchars($filters['min_profit']) ?>"></div>
                                <div class="col-xs-6"><input type="number" step="0.01" name="max_profit" class="form-control" placeholder="Max" value="<?= htmlspecialchars($filters['max_profit']) ?>"></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label class="control-label">Margin %</label>
                            <div class="row">
                                <div class="col-xs-6"><input type="number" step="0.1" name="min_margin" class="form-control" placeholder="Min" value="<?= htmlspecialchars($filters['min_margin']) ?>"></div>
                                <div class="col-xs-6"><input type="number" step="0.1" name="max_margin" class="form-control" placeholder="Max" value="<?= htmlspecialchars($filters['max_margin']) ?>"></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label class="control-label">Supplier saving</label>
                            <div class="row">
                                <div class="col-xs-6"><input type="number" step="0.01" name="min_saving" class="form-control" placeholder="Min" value="<?= htmlspecialchars($filters['min_saving']) ?>"></div>
                                <div class="col-xs-6"><input type="number" step="0.01" name="max_saving" class="form-control" placeholder="Max" value="<?= htmlspecialchars($filters['max_saving']) ?>"></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label class="control-label">Cheaper suppliers</label>
                            <select name="cheaper" class="form-control">
                                <option value="">All products</option>
                                <option value="1" <?= !empty($filters['cheaper']) ? 'selected' : '' ?>>Cheaper option found</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label class="control-label">&nbsp;</label>
                            <button type="submit" class="btn btn-primary btn-block">Apply filters</button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="ibox">
        <div class="ibox-title">
            <h5>Products by store</h5>
            <span class="label label-primary pull-right"><?= (int) $total ?> shown after filters</span>
        </div>
        <div class="ibox-content">
            <div class="table-responsive">
                <table class="table table-striped table-hover pa-table">
                    <thead>
                        <tr>
                            <th>Product ID</th>
                            <th>Product</th>
                            <th>Store</th>
                            <th>Country</th>
                            <th>Supplier</th>
                            <th>Cost</th>
                            <th>Shipping</th>
                            <th>Selling Price</th>
                            <th>CPA</th>
                            <th>BE CPA</th>
                            <th>Target CPA</th>
                            <th>Net Profit</th>
                            <th>Margin</th>
                            <th>PKR Profit</th>
                            <th>Status</th>
                            <th>Category</th>
                            <th>Target Profit</th>
                            <th>Profit Gap</th>
                            <th>Cheaper</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($items as $item):
                            $m = $item->metrics;
                            $cur = $item->currency ?: $settings['default_currency'];
                            $rowClass = $m['status'] === 'above_target' ? 'pa-row-above' : ($m['status'] === 'below_target' ? 'pa-row-below' : 'pa-row-loss');
                            $profitClass = $m['net_profit'] <= 0 ? 'text-danger' : ($m['status'] === 'above_target' ? 'text-navy' : 'text-warning');
                        ?>
                        <tr class="<?= $rowClass ?>">
                            <td><code><?= htmlspecialchars($item->analyzer_id) ?></code></td>
                            <td class="pa-name"><strong><?= htmlspecialchars($item->name) ?></strong></td>
                            <td><?= htmlspecialchars(!empty($item->store_name) ? $item->store_name : '—') ?></td>
                            <td><?= htmlspecialchars($item->country_name) ?></td>
                            <td><?= htmlspecialchars($item->supplier_name) ?></td>
                            <td><?= profit_money($m['product_cost'], $cur) ?></td>
                            <td><?= profit_money($m['shipping_cost'], $cur) ?></td>
                            <td><?= profit_money($m['selling_price'], $cur) ?></td>
                            <td><?= profit_money($m['cpa'], $cur) ?><br><small class="text-muted"><?= htmlspecialchars($m['cpa_mode']) ?><?php if (isset($m['cpa_budget']) && $m['cpa_budget'] !== null): ?> · <?= profit_money_plain($m['cpa_budget'], $m['cpa_budget_currency']) ?><?php endif; ?></small></td>
                            <td><?= profit_money($m['break_even_cpa'], $cur) ?></td>
                            <td><?= profit_money($m['target_cpa'], $cur) ?></td>
                            <td class="<?= $profitClass ?>"><strong><?= profit_money($m['net_profit'], $cur) ?></strong></td>
                            <td><?= profit_pct($m['profit_margin']) ?></td>
                            <td><?= $m['local_profit'] === null ? '—' : profit_money($m['local_profit'], 'PKR') ?></td>
                            <td><span class="label <?= profit_status_class($m['status']) ?>"><?= profit_status_label($m['status']) ?></span></td>
                            <td><?= htmlspecialchars($item->category_names) ?></td>
                            <td><?= profit_money($m['target_profit'], $cur) ?></td>
                            <td class="<?= $m['profit_gap'] > 0 ? 'text-warning' : 'text-navy' ?>"><?= profit_money($m['profit_gap'], $cur) ?></td>
                            <td>
                                <?php if ($item->cheaper_count > 0): ?>
                                <span class="label label-info">Yes</span><br>
                                <small class="text-navy"><?= profit_money($item->supplier_saving, $cur) ?></small>
                                <?php else: ?>—<?php endif; ?>
                            </td>
                            <td>
                                <button type="button" class="btn btn-xs btn-primary pa-open" data-id="<?= (int) $item->id ?>" data-store="<?= (int) $item->store_id ?>" data-listing="<?= isset($item->listing_id) ? (int) $item->listing_id : 0 ?>">Open</button>
                                <a class="btn btn-xs btn-white" href="<?= base_url('admin/store-analytics') ?>?product_id=<?= (int) $item->id ?>">View Live Analytics</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if (empty($items)): ?>
                        <tr><td colspan="20" class="text-center text-muted">No store listings match these filters.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            <?php if ($pages > 1): ?>
            <ul class="pagination">
                <?php
                $start = max(1, $page - 4);
                $end = min($pages, $page + 4);
                if ($start > 1): ?>
                <li><a href="<?= base_url('admin/product-analyzer') ?>?<?= $query ? $query . '&' : '' ?>page=1">1</a></li>
                <?php endif; ?>
                <?php for ($i = $start; $i <= $end; $i++): ?>
                <li class="<?= $i === $page ? 'active' : '' ?>">
                    <a href="<?= base_url('admin/product-analyzer') ?>?<?= $query ? $query . '&' : '' ?>page=<?= $i ?>"><?= $i ?></a>
                </li>
                <?php endfor; ?>
                <?php if ($end < $pages): ?>
                <li><a href="<?= base_url('admin/product-analyzer') ?>?<?= $query ? $query . '&' : '' ?>page=<?= $pages ?>"><?= $pages ?></a></li>
                <?php endif; ?>
            </ul>
            <?php endif; ?>
        </div>
    </div>

    <div class="ibox">
        <div class="ibox-title">
            <h5>Product CSV import</h5>
            <div class="ibox-tools">
                <a href="<?= base_url('admin/product-analyzer/export/products') ?>">Download current products CSV</a>
            </div>
        </div>
        <div class="ibox-content">
            <p class="text-muted">Updates existing catalog products by permanent Product ID. Rows that do not match an existing Product ID are skipped. New products are never created here.</p>
            <form method="post" action="<?= base_url('admin/product-analyzer/import-products-preview') ?>" enctype="multipart/form-data" class="form-inline m-b-sm">
                <input type="file" name="file" class="form-control" accept=".csv,text/csv" required>
                <button type="submit" class="btn btn-success">Preview import</button>
            </form>
            <?php if ($productPreview): ?>
            <div class="table-responsive">
                <table class="table table-striped table-condensed">
                    <thead>
                        <tr>
                            <th>Row</th>
                            <th>Action</th>
                            <th>Product ID</th>
                            <th>Name</th>
                            <th>Price</th>
                            <th>Cost</th>
                            <th>Shipping</th>
                            <th>Issues</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($productPreview as $row): ?>
                        <tr class="<?= $row['action'] === 'update' ? '' : 'warning' ?>">
                            <td><?= (int) $row['row'] ?></td>
                            <td><?= htmlspecialchars($row['action']) ?></td>
                            <td><?= htmlspecialchars($row['product_id']) ?></td>
                            <td><?= htmlspecialchars($row['product_name']) ?></td>
                            <td><?= htmlspecialchars($row['selling_price']) ?></td>
                            <td><?= htmlspecialchars($row['product_cost']) ?></td>
                            <td><?= htmlspecialchars($row['shipping_cost']) ?></td>
                            <td><?= htmlspecialchars(implode(' ', $row['issues'])) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <form method="post" action="<?= base_url('admin/product-analyzer/import-products-confirm') ?>" class="form-inline">
                <button type="submit" class="btn btn-primary">Confirm updates</button>
                <a href="<?= base_url('admin/product-analyzer/import-products-cancel') ?>" class="btn btn-white">Cancel</a>
            </form>
            <?php endif; ?>
        </div>
    </div>
</div>

<div class="modal inmodal pa-drawer" id="paDrawer" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                <h4 class="modal-title">Product detail</h4>
            </div>
            <div class="modal-body" id="paDrawerBody">Loading…</div>
        </div>
    </div>
</div>
<script>
$(function () {
    $('.pa-open').on('click', function () {
        var id = $(this).data('id');
        var store = $(this).data('store') || 0;
        var listing = $(this).data('listing') || 0;
        var qs = [];
        if (store) { qs.push('store_id=' + store); }
        if (listing) { qs.push('listing_id=' + listing); }
        $('#paDrawerBody').html('Loading…');
        $('#paDrawer').modal('show');
        $.get('<?= base_url('admin/product-analyzer/product/') ?>' + id + (qs.length ? ('?' + qs.join('&')) : ''), function (html) {
            $('#paDrawerBody').html(html);
        }).fail(function () {
            $('#paDrawerBody').html('<div class="alert alert-danger">Could not load this product.</div>');
        });
    });
});
</script>
