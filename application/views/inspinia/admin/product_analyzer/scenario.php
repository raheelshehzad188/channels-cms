<?php
$this->load->view('inspinia/admin/product_analyzer/_nav', array('section' => 'scenario'));
$s = $settings;
$scenarioCpa = profit_round2(profit_from_budget(profit_calculated_cpa_budget($s), $s, $s['default_currency'], $rates));
?>
    <div class="ibox">
        <div class="ibox-title"><h5>Scenario calculator</h5></div>
        <div class="ibox-content">
            <p class="text-muted">What-if numbers use the shared profit engine. They do not change catalog products until you save them on the product itself. VAT is not included.</p>
            <form id="paScenario" class="form-horizontal">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="col-sm-4 control-label">Selling price</label>
                            <div class="col-sm-8"><input type="number" step="0.01" name="selling_price" class="form-control" value="199"></div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-4 control-label">Product cost</label>
                            <div class="col-sm-8"><input type="number" step="0.01" name="product_cost" class="form-control" value="40"></div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-4 control-label">Shipping cost</label>
                            <div class="col-sm-8"><input type="number" step="0.01" name="shipping_cost" class="form-control" value="20"></div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-4 control-label">CPA</label>
                            <div class="col-sm-8"><input type="number" step="0.01" name="cpa" class="form-control" value="<?= htmlspecialchars($scenarioCpa) ?>"></div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-4 control-label">Target profit</label>
                            <div class="col-sm-8"><input type="number" step="0.01" name="target_profit" class="form-control" value="<?= htmlspecialchars($s['target_profit']) ?>"></div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="col-sm-4 control-label">Currency</label>
                            <div class="col-sm-8"><input type="text" name="currency" class="form-control" value="<?= htmlspecialchars($s['default_currency']) ?>"></div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-4 control-label">Payment fee %</label>
                            <div class="col-sm-8"><input type="number" step="0.01" name="payment_fee_percent" class="form-control" value="<?= htmlspecialchars($s['payment_fee_percent']) ?>"></div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-4 control-label">Payment fixed fee</label>
                            <div class="col-sm-8"><input type="number" step="0.01" name="payment_fixed_fee" class="form-control" value="<?= htmlspecialchars($s['payment_fixed_fee']) ?>"></div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-4 control-label">Other cost</label>
                            <div class="col-sm-8"><input type="number" step="0.01" name="other_cost" class="form-control" value="<?= htmlspecialchars($s['other_cost']) ?>"></div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-4 control-label">Return rate %</label>
                            <div class="col-sm-8"><input type="number" step="0.01" name="return_rate" class="form-control" value="<?= htmlspecialchars($s['return_rate']) ?>"></div>
                        </div>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary">Calculate</button>
            </form>
        </div>
    </div>

    <div class="row" id="paScenarioResult">
        <div class="col-md-8">
            <div class="ibox">
                <div class="ibox-title"><h5>Calculated results</h5></div>
                <div class="ibox-content">
                    <p class="text-muted" id="paScenarioHint">Click Calculate to fill this section from the shared engine.</p>
                    <div class="row">
                        <div class="col-sm-4"><h5>Net Profit</h5><h2 id="paNet">—</h2></div>
                        <div class="col-sm-4"><h5>Profit Margin</h5><h2 id="paMargin">—</h2></div>
                        <div class="col-sm-4"><h5>Status</h5><h2 id="paStatus">—</h2></div>
                    </div>
                    <table class="table table-striped">
                        <tbody>
                            <tr><th>Net Profit</th><td id="paNet2">—</td></tr>
                            <tr><th>Profit Margin</th><td id="paMargin2">—</td></tr>
                            <tr><th>Break-even CPA</th><td id="paBe">—</td></tr>
                            <tr><th>Target CPA</th><td id="paTargetCpa">—</td></tr>
                            <tr><th>Profit Gap</th><td id="paGap">—</td></tr>
                            <tr><th>Required Selling Price</th><td id="paReq">—</td></tr>
                            <tr><th>Maximum Product Cost</th><td id="paMaxCost">—</td></tr>
                            <tr><th>Maximum Shipping Cost</th><td id="paMaxShip">—</td></tr>
                            <tr><th>Landed cost</th><td id="paLanded">—</td></tr>
                            <tr><th>Payment fee</th><td id="paFee">—</td></tr>
                            <tr><th>Return allowance</th><td id="paReturn">—</td></tr>
                            <tr><th>CPA used</th><td id="paCpa">—</td></tr>
                            <tr><th>PKR profit</th><td id="paPkr">—</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="ibox">
                <div class="ibox-title"><h5>CPA table</h5></div>
                <div class="ibox-content">
                    <table class="table table-condensed" id="paCpaTable">
                        <thead><tr><th>CPA</th><th>Net profit</th></tr></thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
$(function () {
    function money(n, currency) {
        if (n === null || n === undefined || n === '') {
            return '—';
        }
        var num = Number(n);
        if (isNaN(num)) {
            return '—';
        }
        return currency + ' ' + num.toFixed(2);
    }
    function runScenario() {
        var currency = ($('#paScenario [name="currency"]').val() || 'SEK').toUpperCase();
        $.ajax({
            url: '<?= base_url('admin/product-analyzer/scenario-calc') ?>',
            type: 'POST',
            data: $('#paScenario').serialize(),
            dataType: 'json'
        }).done(function (data) {
            var r = data.result || {};
            $('#paScenarioHint').text('Results from the shared calculation engine.');
            $('#paNet').text(money(r.net_profit, currency));
            $('#paNet2').text(money(r.net_profit, currency));
            $('#paMargin').text((Number(r.profit_margin) || 0).toFixed(2) + '%');
            $('#paMargin2').text((Number(r.profit_margin) || 0).toFixed(2) + '%');
            $('#paStatus').text(r.status || '—');
            $('#paLanded').text(money(r.landed_cost, currency));
            $('#paFee').text(money(r.payment_fee, currency));
            $('#paReturn').text(money(r.return_allowance, currency));
            $('#paCpa').text(money(r.cpa, currency));
            $('#paBe').text(money(r.break_even_cpa, currency));
            $('#paTargetCpa').text(money(r.target_cpa, currency));
            $('#paGap').text(money(r.profit_gap, currency));
            $('#paReq').text(money(r.required_selling_price, currency));
            $('#paMaxCost').text(money(r.maximum_product_cost, currency));
            $('#paMaxShip').text(money(r.maximum_shipping_cost, currency));
            $('#paPkr').text(r.local_profit === null || r.local_profit === undefined ? '—' : money(r.local_profit, 'PKR'));
            var body = '';
            $.each(data.cpa_table || [], function (_, row) {
                body += '<tr><td>' + money(row.cpa, currency) + '</td><td>' + money(row.net_profit, currency) + '</td></tr>';
            });
            $('#paCpaTable tbody').html(body);
        }).fail(function () {
            $('#paScenarioHint').text('The calculation could not be loaded. Check that you are still signed in.');
        });
    }
    $('#paScenario').on('submit', function (e) {
        e.preventDefault();
        runScenario();
        var target = $('#paScenarioResult');
        if (target.length) {
            $('html, body').animate({scrollTop: target.offset().top - 80}, 200);
        }
    });
    runScenario();
});
</script>
