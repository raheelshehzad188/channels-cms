<div class="row wrapper border-bottom white-bg page-heading">
    <div class="col-lg-10">
        <h2>Pricing</h2>
        <ol class="breadcrumb">
            <li><a href="<?= base_url('admin/admin') ?>">Dashboard</a></li>
            <li class="active"><strong>Pricing</strong></li>
        </ol>
    </div>
</div>
<div class="wrapper wrapper-content">
    <div class="row">
        <div class="col-lg-10">
            <div class="ibox">
                <div class="ibox-title"><h5>Platform Pricing</h5></div>
                <div class="ibox-content">
                    <?php $this->load->view('flash'); ?>
                    <p class="text-muted">Storefronts show and charge PayPal in each store’s country currency. Super Admin earnings are converted into the platform country currency selected here. Platform fee is stored in that currency and converted onto other country stores using the rates below.</p>
                    <form method="post" action="<?= base_url('admin/pricing/save') ?>" class="form-horizontal">
                        <div class="form-group">
                            <label class="col-sm-3 control-label">Platform country</label>
                            <div class="col-sm-6">
                                <select name="platform_country_id" class="form-control" required>
                                    <option value="">Select country</option>
                                    <?php foreach ($countries as $country): ?>
                                        <option value="<?= (int) $country->id ?>" <?= (int) $platform_country_id === (int) $country->id ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($country->name) ?> (<?= htmlspecialchars($country->currency) ?>)
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <span class="help-block">Ecommerce earnings for Super Admin are shown in this country’s currency (currently <?= htmlspecialchars($platform_currency) ?>).</span>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-3 control-label">Platform Fee (<?= htmlspecialchars($platform_currency) ?>)</label>
                            <div class="col-sm-6">
                                <input type="number" step="0.01" min="0" name="platform_fee" class="form-control" required value="<?= htmlspecialchars($platform_fee) ?>">
                                <span class="help-block">Per product, in platform currency. Converted to the store country currency at checkout.</span>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-3 control-label">VAT (%)</label>
                            <div class="col-sm-6">
                                <input type="number" step="0.01" min="0" name="vat" class="form-control" required value="<?= htmlspecialchars($vat) ?>">
                                <span class="help-block">Added once on the checkout order subtotal. Not added per product. Use 0 to disable<?= ((float)$vat > 0) ? ' (currently ' . htmlspecialchars($vat) . '%)' : '' ?>.</span>
                            </div>
                        </div>

                        <?php if (!empty($rate_currencies)): ?>
                        <div class="hr-line-dashed"></div>
                        <h4>Exchange rates</h4>
                        <p class="text-muted">How much 1 unit of another country currency is worth in <?= htmlspecialchars($platform_currency) ?>. Used to convert platform fee onto stores and Super Admin earnings back into <?= htmlspecialchars($platform_currency) ?>.</p>
                        <?php foreach ($rate_currencies as $code): ?>
                        <div class="form-group">
                            <label class="col-sm-3 control-label">1 <?= htmlspecialchars($code) ?></label>
                            <div class="col-sm-6">
                                <div class="input-group">
                                    <input type="number" step="0.0001" min="0" name="rates[<?= htmlspecialchars($code) ?>]" class="form-control" required value="<?= htmlspecialchars(isset($rates[$code]) ? $rates[$code] : '1') ?>">
                                    <span class="input-group-addon"><?= htmlspecialchars($platform_currency) ?></span>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                        <?php endif; ?>

                        <div class="form-group">
                            <div class="col-sm-6 col-sm-offset-3">
                                <button type="submit" class="btn btn-primary">Save Pricing</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
