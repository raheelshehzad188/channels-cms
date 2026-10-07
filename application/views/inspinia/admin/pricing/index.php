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
                    <p class="text-muted">Storefronts show and charge PayPal in each store’s country currency. Super Admin earnings are converted into the platform country currency selected here. Platform fee is a percent of catalog base price and is added to every store’s product cost. Saving this, or Recalculate, resets listed selling prices from: store cost (catalog cost + ecommerce commission + this fee) + store plus amount. Each listed product is priced on its own catalog numbers.</p>
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
                            <label class="col-sm-3 control-label">Platform Fee (%)</label>
                            <div class="col-sm-6">
                                <div class="input-group">
                                    <input type="number" step="0.01" min="0" max="100" name="platform_fee" class="form-control" required value="<?= htmlspecialchars($platform_fee) ?>">
                                    <span class="input-group-addon">%</span>
                                </div>
                                <span class="help-block">Percent of catalog base price, added to store cost: base + ecommerce commission + this fee. Saving this recalculates cost and selling price on every store-listed product from the current plus amount.</span>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-3 control-label">VAT (%)</label>
                            <div class="col-sm-6">
                                <input type="number" step="0.01" min="0" name="vat" class="form-control" required value="<?= htmlspecialchars($vat) ?>">
                                <span class="help-block">Added once on checkout when greater than 0. Use 0 to hide VAT (0 kr). Not added per product<?= ((float)$vat > 0) ? ' (currently ' . htmlspecialchars($vat) . '%)' : '' ?>.</span>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-3 control-label">Shipping per item</label>
                            <div class="col-sm-6">
                                <input type="number" step="0.01" min="0" name="shipping_per_item" class="form-control" required value="<?= htmlspecialchars($shipping_per_item) ?>">
                                <span class="help-block">Charged on checkout in each store’s currency as this amount × cart quantity. Example: 2 of product A + 1 of product B = 3 × this fee. Use 0 to hide shipping. Does not change product listing prices.</span>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-3 control-label">Shipping discount</label>
                            <div class="col-sm-6">
                                <div class="checkbox">
                                    <label>
                                        <input type="checkbox" name="shipping_discount_enabled" value="1" id="shippingDiscountOn" <?= in_array(strtolower(trim((string) $shipping_discount_enabled)), array('1', 'true', 'on', 'yes'), true) ? 'checked' : '' ?>>
                                        Free shipping when the cart reaches a minimum purchase amount
                                    </label>
                                </div>
                            </div>
                        </div>
                        <div class="form-group" id="shippingFreeMinWrap">
                            <label class="col-sm-3 control-label">Min purchase amount</label>
                            <div class="col-sm-6">
                                <input type="number" step="0.01" min="0" name="shipping_free_min" class="form-control" value="<?= htmlspecialchars($shipping_free_min) ?>">
                                <span class="help-block">If the product subtotal is this amount or more, shipping is free. Example: 500 → cart of 500+ gets free shipping.</span>
                            </div>
                        </div>

                        <?php if (!empty($rate_currencies)): ?>
                        <div class="hr-line-dashed"></div>
                        <h4>Exchange rates</h4>
                        <p class="text-muted">How much 1 unit of another country currency is worth in <?= htmlspecialchars($platform_currency) ?>. Used to convert Super Admin earnings back into <?= htmlspecialchars($platform_currency) ?>.</p>
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
                    <div class="hr-line-dashed"></div>
                    <form method="post" action="<?= base_url('admin/pricing/recalculate') ?>" onsubmit="return confirm('Recalculate every store listing from catalog cost + commission + platform fee + that store’s plus amount? Each product is priced independently.');">
                        <p class="text-muted">Use this after catalog costs or a store plus amount change. Selling price becomes store cost + plus for every listed product, including packs.</p>
                        <button type="submit" class="btn btn-warning">Recalculate store prices</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
(function () {
  var box = document.getElementById('shippingDiscountOn');
  var wrap = document.getElementById('shippingFreeMinWrap');
  if (!box || !wrap) return;
  function sync() {
    wrap.style.display = box.checked ? '' : 'none';
  }
  box.addEventListener('change', sync);
  sync();
})();
</script>
