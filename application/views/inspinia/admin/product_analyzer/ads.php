<?php
$this->load->view('inspinia/admin/product_analyzer/_nav', array('section' => 'ads'));
$s = $settings;
$budgetCurrency = isset($budget_currency) ? $budget_currency : profit_budget_currency($s);
$codes = isset($currency_codes) ? $currency_codes : profit_currency_codes(isset($rates) ? $rates : array());
if (!in_array($budgetCurrency, $codes, true)) {
    $codes[] = $budgetCurrency;
}
?>
    <div class="row">
        <div class="col-md-4">
            <div class="ibox"><div class="ibox-content pa-metric">
                <h5>Daily Meta Ads Budget</h5>
                <h2><?= profit_money_plain($s['daily_ad_budget'], $budgetCurrency) ?></h2>
                <small class="text-muted">Independent of Default Product Currency</small>
            </div></div>
        </div>
        <div class="col-md-4">
            <div class="ibox"><div class="ibox-content pa-metric">
                <h5>Calculated CPA</h5>
                <h2><?= profit_money_plain($calculated_cpa, $budgetCurrency) ?></h2>
                <small class="text-muted"><?= profit_money_plain($s['daily_ad_budget'], $budgetCurrency) ?> / <?= htmlspecialchars(rtrim(rtrim(number_format((float) $s['expected_orders_per_day'], 2), '0'), '.')) ?> = <?= profit_money_plain($calculated_cpa, $budgetCurrency) ?><?php if (!empty($cpa_examples['SEK'])): ?> · <?= profit_money_plain($cpa_examples['SEK'], 'SEK') ?> for SEK products<?php endif; ?></small>
            </div></div>
        </div>
        <div class="col-md-4">
            <div class="ibox"><div class="ibox-content pa-metric">
                <h5>Active CPA mode</h5>
                <h2><?= htmlspecialchars($active_mode) ?></h2>
                <small class="text-muted">
                    <?= $actual_cpa !== null ? 'Actual ' . profit_money_plain($actual_cpa, $budgetCurrency) : ($s['manual_cpa'] !== null ? 'Manual ' . profit_money_plain($s['manual_cpa'], $budgetCurrency) : 'Using calculated CPA') ?>
                </small>
            </div></div>
        </div>
    </div>

    <div class="ibox">
        <div class="ibox-title"><h5>Shared Meta Ads budget</h5></div>
        <div class="ibox-content">
            <p class="text-muted">Ad Budget Currency is independent of Default Product Currency. CPA priority is Actual CPA, then Manual CPA, then Calculated CPA (Daily Meta Ads Budget / Expected Orders). Amounts are converted into each product’s currency using the manual PKR rates on Settings. VAT is not used.</p>
            <form method="post" action="<?= base_url('admin/product-analyzer/save-settings') ?>" class="form-horizontal">
                <div class="form-group">
                    <label class="col-sm-3 control-label">Ad Budget Currency</label>
                    <div class="col-sm-5">
                        <select name="ad_budget_currency" id="ad_budget_currency" class="form-control">
                            <?php foreach ($codes as $code): ?>
                            <option value="<?= htmlspecialchars($code) ?>" <?= strtoupper($budgetCurrency) === strtoupper($code) ? 'selected' : '' ?>><?= htmlspecialchars($code) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-sm-3 control-label">Daily Meta Ads Budget</label>
                    <div class="col-sm-5">
                        <div class="input-group">
                            <input type="number" step="0.01" min="0" name="daily_ad_budget" class="form-control" value="<?= htmlspecialchars($s['daily_ad_budget']) ?>">
                            <span class="input-group-addon pa-budget-ccy"><?= htmlspecialchars($budgetCurrency) ?></span>
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-sm-3 control-label">Expected Orders / Day</label>
                    <div class="col-sm-5">
                        <input type="number" step="0.01" min="0" name="expected_orders_per_day" class="form-control" value="<?= htmlspecialchars($s['expected_orders_per_day']) ?>">
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-sm-3 control-label">Manual CPA</label>
                    <div class="col-sm-5">
                        <div class="input-group">
                            <input type="number" step="0.01" min="0" name="manual_cpa" class="form-control" value="<?= $s['manual_cpa'] === null ? '' : htmlspecialchars($s['manual_cpa']) ?>" placeholder="Leave empty to use calculated CPA">
                            <span class="input-group-addon pa-budget-ccy"><?= htmlspecialchars($budgetCurrency) ?></span>
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-sm-3 control-label">Actual Ad Spend</label>
                    <div class="col-sm-5">
                        <div class="input-group">
                            <input type="number" step="0.01" min="0" name="actual_ad_spend" class="form-control" value="<?= $s['actual_ad_spend'] === null ? '' : htmlspecialchars($s['actual_ad_spend']) ?>">
                            <span class="input-group-addon pa-budget-ccy"><?= htmlspecialchars($budgetCurrency) ?></span>
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-sm-3 control-label">Actual Orders</label>
                    <div class="col-sm-5">
                        <input type="number" step="0.01" min="0" name="actual_orders" class="form-control" value="<?= $s['actual_orders'] === null ? '' : htmlspecialchars($s['actual_orders']) ?>">
                    </div>
                </div>
                <div class="form-group">
                    <div class="col-sm-5 col-sm-offset-3">
                        <button type="submit" class="btn btn-primary">Save ads settings</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
<script>
$(function () {
    $('#ad_budget_currency').on('change', function () {
        $('.pa-budget-ccy').text($(this).val() || 'PKR');
    });
});
</script>
