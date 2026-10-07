<?php
$p = $product;
$s = $summary;
$id = $p ? profit_analyzer_id($p) : $analyzer_id;
?>
<p>
    Permanent Product ID <code><?= htmlspecialchars($id) ?></code>
    · <?= htmlspecialchars($p ? $p->name : 'Product') ?>
    <?php if (!empty($p->store_name)): ?> · <?= htmlspecialchars($p->store_name) ?><?php endif; ?>
    <?php if (!empty($p->country_name)): ?> · <?= htmlspecialchars($p->country_name) ?><?php endif; ?>
</p>
<p>
    <a class="btn btn-primary btn-sm" href="<?= base_url('admin/product-analyzer') ?>?q=<?= rawurlencode($id) ?>">View Profitability</a>
    <a class="btn btn-white btn-sm" href="<?= base_url('admin/store-analytics') ?>?product_id=<?= (int) $p->id ?>">Filter dashboard to this product</a>
</p>
<div class="row">
    <div class="col-sm-3"><div class="well well-sm"><small>Views</small><h3><?= sa_int($s['product_views']) ?></h3></div></div>
    <div class="col-sm-3"><div class="well well-sm"><small>Unique visitors</small><h3><?= sa_int($s['unique']) ?></h3></div></div>
    <div class="col-sm-3"><div class="well well-sm"><small>Current visitors</small><h3><?= sa_int($online) ?></h3></div></div>
    <div class="col-sm-3"><div class="well well-sm"><small>Add to cart</small><h3><?= sa_int($s['add_to_cart']) ?></h3></div></div>
    <div class="col-sm-3"><div class="well well-sm"><small>Checkout</small><h3><?= sa_int($s['checkout']) ?></h3></div></div>
    <div class="col-sm-3"><div class="well well-sm"><small>Orders</small><h3><?= sa_int($s['orders']) ?></h3></div></div>
    <div class="col-sm-3"><div class="well well-sm"><small>Conversion</small><h3><?= number_format((float) $s['conversion'], 2) ?>%</h3></div></div>
    <div class="col-sm-3"><div class="well well-sm"><small>Add-to-cart rate</small><h3><?= sa_pct($s['add_to_cart'], $s['product_views']) ?>%</h3></div></div>
</div>
<p>Checkout rate <?= sa_pct($s['checkout'], $s['add_to_cart']) ?>% · Purchase rate <?= sa_pct($s['orders'], $s['checkout']) ?>%</p>

<div class="row">
    <div class="col-md-6">
        <h4>Traffic sources</h4>
        <table class="table table-condensed">
            <?php foreach ($sources as $row): ?>
            <tr><td><?= htmlspecialchars($row->label) ?></td><td><?= sa_int($row->visitors) ?></td></tr>
            <?php endforeach; ?>
            <?php if (empty($sources)): ?><tr><td class="text-muted">No source data.</td></tr><?php endif; ?>
        </table>
        <h4>Countries</h4>
        <table class="table table-condensed">
            <?php foreach ($countries as $row): ?>
            <tr><td><?= sa_flag($row->country_code) ?> <?= htmlspecialchars($row->country) ?></td><td><?= sa_int($row->visitors) ?></td></tr>
            <?php endforeach; ?>
            <?php if (empty($countries)): ?><tr><td class="text-muted">No country data.</td></tr><?php endif; ?>
        </table>
    </div>
    <div class="col-md-6">
        <h4>Devices</h4>
        <table class="table table-condensed">
            <?php foreach ($devices as $row): ?>
            <tr><td><?= htmlspecialchars(ucfirst($row->device)) ?></td><td><?= sa_int($row->visitors) ?></td></tr>
            <?php endforeach; ?>
        </table>
        <h4>Hourly views / visitors</h4>
        <table class="table table-condensed">
            <thead><tr><th>Time</th><th>Visitors</th><th>Views</th><th>Cart</th></tr></thead>
            <tbody>
                <?php foreach ($trend as $row): ?>
                <tr>
                    <td><?= htmlspecialchars($row->bucket) ?></td>
                    <td><?= (int) $row->visitors ?></td>
                    <td><?= (int) $row->product_views ?></td>
                    <td><?= (int) $row->add_to_cart ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
