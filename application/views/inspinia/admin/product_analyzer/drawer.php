<?php
$m = $item->metrics;
$cur = $item->currency ?: 'SEK';
?>
<p class="text-muted m-b-sm">
    Permanent Product ID <code><?= htmlspecialchars($item->analyzer_id) ?></code>
    <?php if (!empty($item->store_name)): ?>
    · Store: <?= htmlspecialchars($item->store_name) ?>
    <?php endif; ?>
    · SKU <?= htmlspecialchars($item->sku) ?>
    · <?= htmlspecialchars($item->country_name) ?>
    · <a href="<?= base_url('admin/store-analytics') ?>?product_id=<?= (int) $item->id ?>">View Live Analytics</a>
</p>
<div class="row">
    <div class="col-md-3"><div class="well well-sm"><small>Net profit</small><h3 class="<?= $m['net_profit'] < 0 ? 'text-danger' : 'text-navy' ?>"><?= profit_money($m['net_profit'], $cur) ?></h3></div></div>
    <div class="col-md-3"><div class="well well-sm"><small>Margin</small><h3><?= profit_pct($m['profit_margin']) ?></h3></div></div>
    <div class="col-md-3"><div class="well well-sm"><small>CPA / BE / Target</small><h3><?= number_format($m['cpa'], 2) ?> / <?= number_format($m['break_even_cpa'], 2) ?> / <?= number_format($m['target_cpa'], 2) ?></h3></div></div>
    <div class="col-md-3"><div class="well well-sm"><small>Status</small><h3><span class="label <?= profit_status_class($m['status']) ?>"><?= profit_status_label($m['status']) ?></span></h3></div></div>
</div>
<div class="row">
    <div class="col-md-6">
        <table class="table table-condensed">
            <tr><th>Selling price</th><td><?= profit_money($m['selling_price'], $cur) ?></td></tr>
            <tr><th>Product cost</th><td><?= profit_money($m['product_cost'], $cur) ?></td></tr>
            <tr><th>Shipping</th><td><?= profit_money($m['shipping_cost'], $cur) ?></td></tr>
            <tr><th>Landed cost</th><td><?= profit_money($m['landed_cost'], $cur) ?></td></tr>
            <tr><th>Payment fee</th><td><?= profit_money($m['payment_fee'], $cur) ?></td></tr>
            <tr><th>Return allowance</th><td><?= profit_money($m['return_allowance'], $cur) ?></td></tr>
            <tr><th>Required selling price</th><td><?= $m['required_selling_price'] === null ? '—' : profit_money($m['required_selling_price'], $cur) ?></td></tr>
            <tr><th>Max product cost</th><td><?= profit_money($m['maximum_product_cost'], $cur) ?></td></tr>
            <tr><th>Max shipping cost</th><td><?= profit_money($m['maximum_shipping_cost'], $cur) ?></td></tr>
            <tr><th>PKR profit</th><td><?= $m['local_profit'] === null ? '—' : profit_money($m['local_profit'], 'PKR') ?></td></tr>
        </table>
    </div>
    <div class="col-md-6">
        <form method="post" action="<?= base_url('admin/product-analyzer/save-product/' . (int) $item->id) ?>" class="form-horizontal">
            <div class="form-group">
                <label class="col-sm-5 control-label">Selling price</label>
                <div class="col-sm-7"><input type="number" step="0.01" name="selling_price" class="form-control" value="<?= htmlspecialchars($m['selling_price']) ?>"></div>
            </div>
            <div class="form-group">
                <label class="col-sm-5 control-label">Product cost</label>
                <div class="col-sm-7"><input type="number" step="0.01" name="product_cost" class="form-control" value="<?= htmlspecialchars($m['product_cost']) ?>"></div>
            </div>
            <div class="form-group">
                <label class="col-sm-5 control-label">Shipping cost</label>
                <div class="col-sm-7"><input type="number" step="0.01" name="shipping_cost" class="form-control" value="<?= htmlspecialchars($m['shipping_cost']) ?>"></div>
            </div>
            <div class="form-group">
                <label class="col-sm-5 control-label">Manual CPA</label>
                <div class="col-sm-7"><input type="number" step="0.01" name="manual_cpa" class="form-control" value="<?= htmlspecialchars($item->manual_cpa) ?>"></div>
            </div>
            <div class="form-group">
                <label class="col-sm-5 control-label">Actual CPA</label>
                <div class="col-sm-7"><input type="number" step="0.01" name="actual_cpa" class="form-control" value="<?= htmlspecialchars($item->actual_cpa) ?>"></div>
            </div>
            <div class="form-group">
                <label class="col-sm-5 control-label">Payment fee %</label>
                <div class="col-sm-7"><input type="number" step="0.01" name="payment_fee_percent" class="form-control" value="<?= htmlspecialchars($item->meta_payment_fee_percent) ?>"></div>
            </div>
            <div class="form-group">
                <label class="col-sm-5 control-label">Return rate %</label>
                <div class="col-sm-7"><input type="number" step="0.01" name="return_rate" class="form-control" value="<?= htmlspecialchars($item->meta_return_rate) ?>"></div>
            </div>
            <div class="form-group">
                <div class="col-sm-7 col-sm-offset-5">
                    <button type="submit" class="btn btn-primary btn-sm">Save product costs</button>
                </div>
            </div>
        </form>
    </div>
</div>

<h4>Supplier options</h4>
<div class="table-responsive">
    <table class="table table-striped table-condensed">
        <thead>
            <tr>
                <th>Supplier</th>
                <th>Cost</th>
                <th>Ship</th>
                <th>Landed</th>
                <th>Impact</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($item->options as $option): ?>
            <tr class="<?= !empty($option->is_recommended) ? 'success' : '' ?>">
                <td>
                    <?= htmlspecialchars($option->supplier_name) ?>
                    <?php if ((int) $option->is_preferred): ?><span class="label label-primary">Preferred</span><?php endif; ?>
                    <?php if (!empty($option->is_recommended)): ?><span class="label label-info">Cheaper</span><?php endif; ?>
                </td>
                <td><?= profit_money($option->product_cost, $cur) ?></td>
                <td><?= profit_money($option->shipping_cost, $cur) ?></td>
                <td><?= profit_money($option->landed, $cur) ?></td>
                <td>
                    <?php if ($option->impact): ?>
                    Save <?= profit_money($option->impact['savings'], $cur) ?>
                    · profit <?= profit_money($option->impact['new_profit'], $cur) ?>
                    <?php else: ?>—<?php endif; ?>
                </td>
                <td>
                    <?php if (!(int) $option->is_preferred): ?>
                    <form method="post" action="<?= base_url('admin/product-analyzer/set-preferred/' . (int) $item->id) ?>" onsubmit="return confirm('Set this supplier as preferred? Selling price and CPA will not change.');">
                        <input type="hidden" name="option_id" value="<?= (int) $option->id ?>">
                        <input type="hidden" name="confirm" value="1">
                        <button type="submit" class="btn btn-xs btn-primary">Set preferred</button>
                    </form>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; ?>
            <?php if (empty($item->options)): ?>
            <tr><td colspan="6" class="text-muted">No supplier options yet.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<h4>Price history</h4>
<div class="table-responsive">
    <table class="table table-condensed">
        <thead>
            <tr>
                <th>When</th>
                <th>Supplier</th>
                <th>Old landed</th>
                <th>New landed</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($history as $row): ?>
            <tr>
                <td><?= htmlspecialchars($row->changed_at) ?></td>
                <td><?= htmlspecialchars($row->supplier_name) ?></td>
                <td><?= number_format((float) $row->old_landed_cost, 2) ?></td>
                <td><?= number_format((float) $row->new_landed_cost, 2) ?></td>
            </tr>
            <?php endforeach; ?>
            <?php if (empty($history)): ?>
            <tr><td colspan="4" class="text-muted">No supplier price changes recorded.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>
