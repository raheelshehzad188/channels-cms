<?php
$this->load->view('inspinia/admin/product_analyzer/_nav', array('section' => 'analysis'));
$groups = array(
    'Store analysis' => isset($store_rows) ? $store_rows : array(),
    'Country analysis' => $country_rows,
    'Category analysis' => $category_rows,
    'Supplier comparison' => $supplier_rows,
);
?>
    <div class="row">
        <div class="col-md-3"><div class="ibox"><div class="ibox-content pa-metric"><h5>Products</h5><h2><?= (int) $metrics['total'] ?></h2></div></div></div>
        <div class="col-md-3"><div class="ibox"><div class="ibox-content pa-metric"><h5>Avg Net Profit (PKR)</h5><h2><?= number_format((float) $metrics['avg_profit'], 2) ?></h2></div></div></div>
        <div class="col-md-3"><div class="ibox"><div class="ibox-content pa-metric"><h5>Supplier Savings (PKR)</h5><h2 class="text-navy"><?= number_format((float) $metrics['savings'], 2) ?></h2></div></div></div>
        <div class="col-md-3"><div class="ibox"><div class="ibox-content pa-metric"><h5>Cheaper found</h5><h2><?= (int) $metrics['cheaper'] ?></h2></div></div></div>
    </div>
    <?php foreach ($groups as $title => $rows): ?>
    <div class="ibox">
        <div class="ibox-title"><h5><?= htmlspecialchars($title) ?></h5></div>
        <div class="ibox-content">
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Currency</th>
                            <th>Products</th>
                            <th>Average Selling Price</th>
                            <th>Average Product Cost</th>
                            <th>Average Shipping</th>
                            <th>Average CPA</th>
                            <th>Average Native Profit</th>
                            <th>Average Profit in PKR</th>
                            <th>Average Margin</th>
                            <th>Above Target</th>
                            <th>Below Target</th>
                            <th>Loss</th>
                            <th>Supplier Savings</th>
                            <th>Cheaper Suppliers</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($rows as $row):
                            $native = function ($value) use ($row) {
                                if (!empty($row['mixed']) || $value === null) {
                                    return '—';
                                }
                                $code = $row['currency'] ? ' ' . $row['currency'] : '';
                                return number_format((float) $value, 2) . $code;
                            };
                        ?>
                        <tr>
                            <td><?= htmlspecialchars($row['name']) ?></td>
                            <td><?= htmlspecialchars($row['currency'] ?: '—') ?></td>
                            <td><?= (int) $row['products'] ?></td>
                            <td><?= $native($row['avg_price']) ?></td>
                            <td><?= $native($row['avg_cost']) ?></td>
                            <td><?= $native($row['avg_shipping']) ?></td>
                            <td><?= $native($row['avg_cpa']) ?></td>
                            <td><?= $native($row['avg_profit']) ?></td>
                            <td><?= number_format((float) $row['avg_profit_pkr'], 2) ?> PKR</td>
                            <td><?= profit_pct($row['avg_margin']) ?></td>
                            <td><?= (int) $row['above'] ?></td>
                            <td><?= (int) $row['below'] ?></td>
                            <td><?= (int) $row['loss'] ?></td>
                            <td><?= $native($row['savings']) ?></td>
                            <td><?= (int) $row['cheaper'] ?></td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if (empty($rows)): ?>
                        <tr><td colspan="15" class="text-center text-muted">No store listings yet.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>
