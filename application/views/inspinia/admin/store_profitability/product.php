<?php
$item = $item;
$m = $item->metrics;
$cur = $item->currency ?: $store_currency;
$override = isset($override) ? $override : array();
$val = function ($key, $fallback) use ($override) {
    return (isset($override[$key]) && $override[$key] !== '') ? $override[$key] : $fallback;
};
$img = !empty($item->listing_image) ? product_image_url($item->listing_image) : (!empty($item->image) ? product_image_url($item->image) : '');
$rate = isset($rates[$cur]) ? (float) $rates[$cur] : 0;
$mode = $settings['budget_mode'];
$cpaBudget = $m['cpa_budget'];
?>
<style>
.spa-kicker { color:#888; margin-top:4px; }
.spa-hero img { max-height:140px; border-radius:6px; }
.spa-break td { padding:6px 8px; }
.spa-net { font-size:22px; font-weight:700; }
.spa-flow { list-style:none; padding:0; margin:0; }
.spa-flow li { padding:8px 12px; border-left:3px solid #1ab394; margin-bottom:6px; background:#f9f9f9; }
.spa-flow li.cost { border-color:#ed5565; }
.spa-flow li.net { border-color:#1c84c6; background:#eef6ff; font-weight:600; }
</style>
<div class="row wrapper border-bottom white-bg page-heading">
    <div class="col-lg-8">
        <h2><?= htmlspecialchars($item->name) ?></h2>
        <p class="spa-kicker">In-depth store listing profitability. Analysis values do not overwrite master data unless you click Save Changes.</p>
        <ol class="breadcrumb">
            <li><a href="<?= base_url('admin/store-profitability') ?>">Store Profitability</a></li>
            <li class="active"><strong><?= htmlspecialchars($item->analyzer_id) ?></strong></li>
        </ol>
    </div>
    <div class="col-lg-4 text-right" style="margin-top:26px;">
        <a class="btn btn-white" href="<?= base_url('admin/store-profitability') ?>?<?= htmlspecialchars($back_query) ?>">Back to store</a>
    </div>
</div>
<div class="wrapper wrapper-content">
    <?php $this->load->view('flash'); ?>
    <div class="ibox">
        <div class="ibox-content">
            <div class="row">
                <div class="col-md-2 spa-hero"><?php if ($img): ?><img src="<?= htmlspecialchars($img) ?>" alt="" class="img-responsive"><?php endif; ?></div>
                <div class="col-md-10">
                    <p>
                        Product ID <code><?= htmlspecialchars($item->analyzer_id) ?></code>
                        · Store <strong><?= htmlspecialchars($item->store_name) ?></strong>
                        · <?= htmlspecialchars($item->country_name) ?>
                        · <?= htmlspecialchars($item->category_names) ?>
                        · <?= htmlspecialchars($item->supplier_name) ?>
                    </p>
                    <p>Store product URL:
                        <a href="<?= htmlspecialchars($item->storefront_url) ?>" target="_blank" rel="noopener"><?= htmlspecialchars($item->storefront_url) ?></a>
                    </p>
                    <p class="spa-rate">1 <?= htmlspecialchars($cur) ?> = <?= $rate > 0 ? number_format($rate, 4) : '—' ?> PKR
                        · Mode: <strong><?= $mode === 'shared' ? 'Shared Store Budget' : 'Budget Per Product' ?></strong></p>
                </div>
            </div>
        </div>
    </div>

    <form method="post" action="<?= base_url('admin/store-profitability/recalculate/' . (int) $item->id) ?>" class="form-horizontal">
        <input type="hidden" name="listing_id" value="<?= (int) $item->listing_id ?>">
        <input type="hidden" name="store_id" value="<?= (int) $item->store_id ?>">
        <input type="hidden" name="budget_mode" value="<?= htmlspecialchars($settings['budget_mode']) ?>">
        <input type="hidden" name="page_ads_budget" value="<?= htmlspecialchars($settings['ads_budget']) ?>">
        <input type="hidden" name="page_ads_currency" value="<?= htmlspecialchars($settings['ads_currency']) ?>">
        <input type="hidden" name="page_expected_orders" value="<?= htmlspecialchars($settings['expected_orders']) ?>">
        <input type="hidden" name="rate_currency" value="<?= htmlspecialchars($cur) ?>">
        <div class="ibox">
            <div class="ibox-title"><h5>Analysis inputs (this product only)</h5></div>
            <div class="ibox-content">
                <div class="row">
                    <div class="col-md-3"><div class="form-group"><label class="control-label">Ads Budget Per Product / Day</label>
                        <input type="number" step="0.01" name="ads_budget" class="form-control" value="<?= htmlspecialchars($val('ads_budget', $item->ads_budget)) ?>"></div></div>
                    <div class="col-md-3"><div class="form-group"><label class="control-label">Ads Budget Currency</label>
                        <select name="ads_currency" class="form-control">
                            <?php foreach ($currencies as $code): ?>
                            <option value="<?= htmlspecialchars($code) ?>" <?= $val('ads_currency', $item->ads_currency) === $code ? 'selected' : '' ?>><?= htmlspecialchars($code) ?></option>
                            <?php endforeach; ?>
                        </select></div></div>
                    <div class="col-md-3"><div class="form-group"><label class="control-label">Expected Orders Per Product / Day</label>
                        <input type="number" step="0.01" name="expected_orders" class="form-control" value="<?= htmlspecialchars($val('expected_orders', $item->expected_orders)) ?>"></div></div>
                    <div class="col-md-3"><div class="form-group"><label class="control-label">Conversion: 1 <?= htmlspecialchars($cur) ?> = PKR</label>
                        <input type="number" step="0.0001" name="rate_to_pkr" class="form-control" value="<?= htmlspecialchars($rate) ?>"></div></div>
                    <div class="col-md-3"><div class="form-group"><label class="control-label">Supplier Shipping Cost</label>
                        <input type="number" step="0.01" name="shipping_cost" class="form-control" value="<?= htmlspecialchars($val('shipping_cost', $m['shipping_cost'])) ?>">
                        <span class="help-block">Used in profit. Free customer shipping does not make this zero.</span></div></div>
                    <div class="col-md-3"><div class="form-group"><label class="control-label">Other Cost</label>
                        <input type="number" step="0.01" name="other_cost" class="form-control" value="<?= htmlspecialchars($val('other_cost', $settings['other_cost'])) ?>"></div></div>
                    <div class="col-md-3"><div class="form-group"><label class="control-label">Payment Fee %</label>
                        <input type="number" step="0.01" name="payment_fee_percent" class="form-control" value="<?= htmlspecialchars($val('payment_fee_percent', $settings['payment_fee_percent'])) ?>"></div></div>
                    <div class="col-md-3"><div class="form-group"><label class="control-label">Payment Fixed Fee</label>
                        <input type="number" step="0.01" name="payment_fixed_fee" class="form-control" value="<?= htmlspecialchars($val('payment_fixed_fee', $settings['payment_fixed_fee'])) ?>"></div></div>
                    <div class="col-md-3"><div class="form-group"><label class="control-label">Return Rate %</label>
                        <input type="number" step="0.01" name="return_rate" class="form-control" value="<?= htmlspecialchars($val('return_rate', $settings['return_rate'])) ?>"></div></div>
                    <div class="col-md-3"><div class="form-group"><label class="control-label">Target Profit</label>
                        <input type="number" step="0.01" name="target_profit" class="form-control" value="<?= htmlspecialchars($val('target_profit', $m['target_profit'])) ?>"></div></div>
                    <div class="col-md-3"><div class="form-group"><label class="control-label">Customer Shipping Charge</label>
                        <p class="form-control-static"><?= profit_money($item->customer_shipping, $cur) ?> / Free to customer</p></div></div>
                    <div class="col-md-3"><div class="form-group"><label class="control-label">&nbsp;</label>
                        <button type="submit" class="btn btn-primary btn-block">Recalculate</button></div></div>
                </div>
            </div>
        </div>
    </form>

    <div class="row">
        <div class="col-md-3"><div class="ibox"><div class="ibox-content"><small>Net profit / order</small><div class="spa-net <?= $m['net_profit'] <= 0 ? 'text-danger' : 'text-navy' ?>"><?= profit_money($m['net_profit'], $cur) ?></div></div></div></div>
        <div class="col-md-3"><div class="ibox"><div class="ibox-content"><small>PKR profit / order</small><div class="spa-net"><?= $m['local_profit'] === null ? '—' : profit_money($m['local_profit'], 'PKR') ?></div></div></div></div>
        <div class="col-md-3"><div class="ibox"><div class="ibox-content"><small>Status</small><h3><span class="label <?= profit_status_class($m['status']) ?>"><?= profit_status_label($m['status']) ?></span></h3></div></div></div>
        <div class="col-md-3"><div class="ibox"><div class="ibox-content"><small>Estimated daily profit</small><div class="spa-net"><?= profit_money($item->daily_profit, $cur) ?></div><small><?= htmlspecialchars($item->expected_orders) ?> orders × profit per order</small></div></div></div>
    </div>

    <div class="row">
        <div class="col-md-6">
            <div class="ibox">
                <div class="ibox-title"><h5>Calculations</h5></div>
                <div class="ibox-content">
                    <table class="table table-condensed spa-break">
                        <tr><th>Product Cost</th><td><?= profit_money($m['product_cost'], $cur) ?></td></tr>
                        <tr><th>Supplier Shipping</th><td><?= profit_money($m['shipping_cost'], $cur) ?></td></tr>
                        <tr><th>Customer Shipping Charge</th><td><?= profit_money($item->customer_shipping, $cur) ?></td></tr>
                        <tr><th>Selling Price</th><td><?= profit_money($m['selling_price'], $cur) ?></td></tr>
                        <tr><th>Daily Product Ad Budget</th><td><?= number_format((float) $item->ads_budget, 2) ?> <?= htmlspecialchars($item->ads_currency) ?></td></tr>
                        <tr><th>Expected Orders</th><td><?= htmlspecialchars($item->expected_orders) ?></td></tr>
                        <tr><th>Effective CPA</th><td><?= profit_money($m['cpa'], $cur) ?><?php if ($cpaBudget !== null): ?> <small class="text-muted">(<?= profit_money_plain($cpaBudget, $m['cpa_budget_currency']) ?> / order)</small><?php endif; ?></td></tr>
                        <tr><th>Payment Fee</th><td><?= profit_money($m['payment_fee'], $cur) ?></td></tr>
                        <tr><th>Return Allowance</th><td><?= profit_money($m['return_allowance'], $cur) ?></td></tr>
                        <tr><th>Other Cost</th><td><?= profit_money($settings['other_cost'], $cur) ?></td></tr>
                        <tr><th>Total Cost Before Ads</th><td><?= profit_money($m['total_cost_before_ads'], $cur) ?></td></tr>
                        <tr><th>Total Cost After Ads</th><td><?= profit_money($m['total_cost_before_ads'] + $m['cpa'], $cur) ?></td></tr>
                        <tr><th>Net Profit</th><td><?= profit_money($m['net_profit'], $cur) ?></td></tr>
                        <tr><th>Profit Margin</th><td><?= profit_pct($m['profit_margin']) ?></td></tr>
                        <tr><th>Break-even CPA</th><td><?= profit_money($m['break_even_cpa'], $cur) ?></td></tr>
                        <tr><th>Target CPA</th><td><?= profit_money($m['target_cpa'], $cur) ?></td></tr>
                        <tr><th>Required Selling Price</th><td><?= $m['required_selling_price'] === null ? '—' : profit_money($m['required_selling_price'], $cur) ?></td></tr>
                        <tr><th>Maximum Product Cost</th><td><?= profit_money($m['maximum_product_cost'], $cur) ?></td></tr>
                        <tr><th>Maximum Shipping Cost</th><td><?= profit_money($m['maximum_shipping_cost'], $cur) ?></td></tr>
                        <tr><th>Profit Gap</th><td><?= profit_money($m['profit_gap'], $cur) ?></td></tr>
                        <tr><th>PKR Profit</th><td><?= $m['local_profit'] === null ? '—' : profit_money($m['local_profit'], 'PKR') ?></td></tr>
                        <tr><th>Profit per order</th><td><?= profit_money($m['net_profit'], $cur) ?></td></tr>
                        <tr><th>Expected orders / day</th><td><?= htmlspecialchars($item->expected_orders) ?></td></tr>
                        <tr><th>Estimated daily profit</th><td><?= profit_money($item->daily_profit, $cur) ?></td></tr>
                    </table>
                    <p class="text-muted">VAT is not included.</p>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="ibox">
                <div class="ibox-title"><h5>Profit breakdown</h5></div>
                <div class="ibox-content">
                    <ul class="spa-flow">
                        <li>Selling Price <?= profit_money($m['selling_price'], $cur) ?></li>
                        <li class="cost">− Product Cost <?= profit_money($m['product_cost'], $cur) ?></li>
                        <li class="cost">− Shipping <?= profit_money($m['shipping_cost'], $cur) ?></li>
                        <li class="cost">− Payment Fee <?= profit_money($m['payment_fee'], $cur) ?></li>
                        <li class="cost">− Other Cost <?= profit_money($settings['other_cost'], $cur) ?></li>
                        <li class="cost">− Return Allowance <?= profit_money($m['return_allowance'], $cur) ?></li>
                        <li class="cost">− Ads CPA <?= profit_money($m['cpa'], $cur) ?></li>
                        <li class="net">= Net Profit <?= profit_money($m['net_profit'], $cur) ?> · <?= $m['local_profit'] === null ? '' : profit_money($m['local_profit'], 'PKR') ?></li>
                    </ul>
                </div>
            </div>
            <div class="ibox">
                <div class="ibox-title"><h5>Scenario analysis</h5></div>
                <div class="ibox-content">
                    <p class="text-muted">These values are not saved to master data.</p>
                    <div class="row">
                        <div class="col-sm-6"><label>Selling Price</label><input type="number" step="0.01" id="sc-price" class="form-control" value="<?= htmlspecialchars($m['selling_price']) ?>"></div>
                        <div class="col-sm-6"><label>Ads Budget</label><input type="number" step="0.01" id="sc-budget" class="form-control" value="<?= htmlspecialchars($item->ads_budget) ?>"></div>
                        <div class="col-sm-6"><label>Expected Orders</label><input type="number" step="0.01" id="sc-orders" class="form-control" value="<?= htmlspecialchars($item->expected_orders) ?>"></div>
                        <div class="col-sm-6"><label>Shipping</label><input type="number" step="0.01" id="sc-ship" class="form-control" value="<?= htmlspecialchars($m['shipping_cost']) ?>"></div>
                        <div class="col-sm-6"><label>Product Cost</label><input type="number" step="0.01" id="sc-cost" class="form-control" value="<?= htmlspecialchars($m['product_cost']) ?>"></div>
                    </div>
                    <button type="button" id="sc-run" class="btn btn-info m-t-sm">Run scenario</button>
                    <div id="sc-out" class="m-t-sm"></div>
                </div>
            </div>
            <form method="post" action="<?= base_url('admin/store-profitability/save-product/' . (int) $item->id) ?>">
                <input type="hidden" name="listing_id" value="<?= (int) $item->listing_id ?>">
                <input type="hidden" name="store_id" value="<?= (int) $item->store_id ?>">
                <input type="hidden" name="shipping_cost" value="<?= htmlspecialchars($val('shipping_cost', $m['shipping_cost'])) ?>">
                <button type="submit" class="btn btn-warning" onclick="return confirm('Save supplier shipping cost to the product record? Store selling price will not change.');">Save Changes</button>
                <span class="text-muted">Writes supplier shipping only. Does not change store selling price.</span>
            </form>
        </div>
    </div>
</div>
<script>
$(function () {
    $('#sc-run').on('click', function () {
        $.post(<?= json_encode(base_url('admin/store-profitability/scenario')) ?>, {
            product_id: <?= (int) $item->id ?>,
            listing_id: <?= (int) $item->listing_id ?>,
            sku: <?= json_encode($item->analyzer_id) ?>,
            currency: <?= json_encode($cur) ?>,
            selling_price: $('#sc-price').val(),
            product_cost: $('#sc-cost').val(),
            shipping_cost: $('#sc-ship').val(),
            ads_budget: $('#sc-budget').val(),
            expected_orders: $('#sc-orders').val(),
            ads_currency: <?= json_encode($item->ads_currency) ?>,
            budget_mode: <?= json_encode($settings['budget_mode']) ?>
        }, function (d) {
            $('#sc-out').html(
                '<div class="well well-sm">' +
                'Net Profit: <strong>' + d.net_profit + ' ' + d.currency + '</strong><br>' +
                'Margin: ' + d.profit_margin + '%<br>' +
                'CPA: ' + d.cpa + ' ' + d.currency + '<br>' +
                'PKR Profit: ' + (d.local_profit === null ? '—' : d.local_profit) + '<br>' +
                'Daily profit: ' + d.daily_profit + ' ' + d.currency + '<br>' +
                'Status: <span class="label ' + d.status_class + '">' + d.status_label + '</span></div>'
            );
        }, 'json');
    });
});
</script>
