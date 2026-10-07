<?php
$this->load->view('inspinia/admin/product_analyzer/_nav', array('section' => 'overview'));
$best = $metrics['best'];
$worst = $metrics['worst'];
$maxCountry = 0;
foreach ($metrics['country_chart'] as $row) {
    $maxCountry = max($maxCountry, abs($row['profit']), abs($row['savings']));
}
$maxCat = 0;
foreach ($metrics['category_chart'] as $row) {
    $maxCat = max($maxCat, abs($row['profit']));
}
?>
    <div class="row">
        <div class="col-md-2"><div class="ibox"><div class="ibox-content pa-metric"><h5>Total Products</h5><h2><?= (int) $metrics['total'] ?></h2></div></div></div>
        <div class="col-md-2"><a class="pa-card-link" href="<?= base_url('admin/product-analyzer') ?>?status=above_target"><div class="ibox"><div class="ibox-content pa-metric"><h5>Profitable / Above Target</h5><h2 class="text-navy"><?= (int) $metrics['above'] ?></h2><small class="text-muted">View Profitable Products</small></div></div></a></div>
        <div class="col-md-2"><a class="pa-card-link" href="<?= base_url('admin/product-analyzer') ?>?status=below_target"><div class="ibox"><div class="ibox-content pa-metric"><h5>Below Target</h5><h2 class="text-warning"><?= (int) $metrics['below'] ?></h2><small class="text-muted">View Below Target</small></div></div></a></div>
        <div class="col-md-2"><a class="pa-card-link" href="<?= base_url('admin/product-analyzer') ?>?status=loss"><div class="ibox"><div class="ibox-content pa-metric"><h5>Loss</h5><h2 class="text-danger"><?= (int) $metrics['loss'] ?></h2><small class="text-muted">View Loss Products</small></div></div></a></div>
        <div class="col-md-2"><div class="ibox"><div class="ibox-content pa-metric"><h5>Average Net Profit (PKR)</h5><h2><?= number_format((float) $metrics['avg_profit'], 2) ?></h2></div></div></div>
        <div class="col-md-2"><div class="ibox"><div class="ibox-content pa-metric"><h5>Average Profit Margin</h5><h2><?= profit_pct($metrics['avg_margin']) ?></h2></div></div></div>
        <div class="col-md-2"><div class="ibox"><div class="ibox-content pa-metric"><h5>Avg CPA (PKR)</h5><h2><?= number_format((float) $metrics['avg_cpa'], 2) ?></h2></div></div></div>
        <div class="col-md-2"><div class="ibox"><div class="ibox-content pa-metric"><h5>Target achievement</h5><h2><?= profit_pct($metrics['achievement']) ?></h2></div></div></div>
        <div class="col-md-2"><div class="ibox"><div class="ibox-content pa-metric"><h5>Avg Product Cost (PKR)</h5><h2><?= number_format((float) $metrics['avg_cost'], 2) ?></h2></div></div></div>
        <div class="col-md-2"><div class="ibox"><div class="ibox-content pa-metric"><h5>Avg Selling Price (PKR)</h5><h2><?= number_format((float) $metrics['avg_price'], 2) ?></h2></div></div></div>
        <div class="col-md-2"><div class="ibox"><div class="ibox-content pa-metric"><h5>Supplier Savings (PKR)</h5><h2 class="text-navy"><?= number_format((float) $metrics['savings'], 2) ?></h2></div></div></div>
        <div class="col-md-2"><div class="ibox"><div class="ibox-content pa-metric"><h5>Cheaper Suppliers</h5><h2><?= (int) $metrics['cheaper'] ?></h2></div></div></div>
    </div>

    <div class="row">
        <div class="col-md-6">
            <div class="ibox">
                <div class="ibox-title"><h5>Best / worst profit product</h5></div>
                <div class="ibox-content">
                    <p><strong>Best:</strong>
                        <?= $best ? htmlspecialchars($best->analyzer_id . ' · ' . $best->name) . ' · ' . profit_money($best->metrics['net_profit'], $best->currency) : '—' ?>
                    </p>
                    <p><strong>Worst:</strong>
                        <?= $worst ? htmlspecialchars($worst->analyzer_id . ' · ' . $worst->name) . ' · ' . profit_money($worst->metrics['net_profit'], $worst->currency) : '—' ?>
                    </p>
                    <a href="<?= base_url('admin/product-analyzer') ?>" class="btn btn-primary btn-sm">Open products</a>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="ibox">
                <div class="ibox-title"><h5>Status distribution</h5></div>
                <div class="ibox-content">
                    <?php $sum = max(1, (int) $metrics['total']); ?>
                    <p>Above target <?= (int) $metrics['above'] ?></p>
                    <div class="progress"><div class="progress-bar progress-bar-primary" style="width:<?= ($metrics['above'] / $sum) * 100 ?>%"></div></div>
                    <p>Below target <?= (int) $metrics['below'] ?></p>
                    <div class="progress"><div class="progress-bar progress-bar-warning" style="width:<?= ($metrics['below'] / $sum) * 100 ?>%"></div></div>
                    <p>Loss <?= (int) $metrics['loss'] ?></p>
                    <div class="progress"><div class="progress-bar progress-bar-danger" style="width:<?= ($metrics['loss'] / $sum) * 100 ?>%"></div></div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6">
            <div class="ibox">
                <div class="ibox-title"><h5>Profit and savings by country (PKR)</h5></div>
                <div class="ibox-content">
                    <?php foreach ($metrics['country_chart'] as $row): $w = $maxCountry > 0 ? abs($row['profit']) / $maxCountry * 100 : 0; ?>
                    <p><?= htmlspecialchars($row['name']) ?>
                        <span class="pull-right">profit PKR <?= number_format($row['profit'], 2) ?> · save PKR <?= number_format($row['savings'], 2) ?></span>
                    </p>
                    <div class="pa-bar m-b"><span style="width:<?= $w ?>%;background:<?= $row['profit'] < 0 ? '#ed5565' : '#1ab394' ?>"></span></div>
                    <?php endforeach; ?>
                    <?php if (empty($metrics['country_chart'])): ?><p class="text-muted">No catalog products yet.</p><?php endif; ?>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="ibox">
                <div class="ibox-title"><h5>Average profit by category (PKR)</h5></div>
                <div class="ibox-content">
                    <?php foreach ($metrics['category_chart'] as $row): $w = $maxCat > 0 ? abs($row['profit']) / $maxCat * 100 : 0; ?>
                    <p><?= htmlspecialchars($row['name']) ?> <span class="pull-right"><?= number_format($row['profit'], 2) ?></span></p>
                    <div class="pa-bar m-b"><span style="width:<?= $w ?>%"></span></div>
                    <?php endforeach; ?>
                    <?php if (empty($metrics['category_chart'])): ?><p class="text-muted">No catalog products yet.</p><?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <div class="ibox">
        <div class="ibox-title"><h5>CPA vs break-even by country</h5></div>
        <div class="ibox-content">
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Country</th>
                            <th>Avg CPA (PKR)</th>
                            <th>Avg break-even CPA (PKR)</th>
                            <th>Avg margin</th>
                            <th>Supplier savings (PKR)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($metrics['country_chart'] as $row): ?>
                        <tr>
                            <td><?= htmlspecialchars($row['name']) ?></td>
                            <td><?= number_format($row['cpa'], 2) ?></td>
                            <td><?= number_format($row['break_even'], 2) ?></td>
                            <td><?= profit_pct($row['margin']) ?></td>
                            <td><?= number_format($row['savings'], 2) ?></td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if (empty($metrics['country_chart'])): ?>
                        <tr><td colspan="5" class="text-center text-muted">No data yet.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
