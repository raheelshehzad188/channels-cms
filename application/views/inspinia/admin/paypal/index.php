<div class="row wrapper border-bottom white-bg page-heading">
    <div class="col-lg-10">
        <h2>Payment Gateway</h2>
        <ol class="breadcrumb">
            <li><a href="<?= base_url('admin/admin') ?>">Dashboard</a></li>
            <li class="active"><strong>Gateway</strong></li>
        </ol>
    </div>
</div>
<div class="wrapper wrapper-content">
    <div class="row">
        <div class="col-lg-10">
            <?php $this->load->view('flash'); ?>

            <form method="post" action="<?= base_url('admin/paypal/save') ?>" class="form-horizontal">
                <div class="ibox">
                    <div class="ibox-title"><h5>Mode &amp; active credentials</h5></div>
                    <div class="ibox-content">
                        <p class="text-muted">One PayPal account for every store. Card and wallet checkout both use these credentials. Charge currency comes from the store’s country (set on the store). Super Admin earnings currency is set under Pricing.</p>
                        <div class="form-group">
                            <label class="col-sm-3 control-label">Mode</label>
                            <div class="col-sm-4">
                                <select name="paypal_mode" class="form-control">
                                    <option value="sandbox" <?= $paypal_mode === 'sandbox' ? 'selected' : '' ?>>Sandbox (test)</option>
                                    <option value="live" <?= $paypal_mode === 'live' ? 'selected' : '' ?>>Live (production)</option>
                                </select>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-3 control-label">Sandbox app</label>
                            <div class="col-sm-4">
                                <select name="paypal_app" class="form-control">
                                    <option value="default" <?= $paypal_app === 'default' ? 'selected' : '' ?>>Default Application</option>
                                    <option value="nexa" <?= $paypal_app === 'nexa' ? 'selected' : '' ?>>Nexa Application</option>
                                </select>
                                <span class="help-block">Used only when Mode = Sandbox.</span>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-3 control-label">Fallback currency</label>
                            <div class="col-sm-3">
                                <input type="text" name="paypal_currency" class="form-control" value="<?= htmlspecialchars($paypal_currency) ?>" placeholder="USD">
                                <span class="help-block">Same PayPal account is used for every store. Checkout charges the <strong>store country’s currency</strong>. This value is only a fallback if a store has no country.</span>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-3 control-label">Sandbox region</label>
                            <div class="col-sm-3">
                                <input type="text" name="paypal_sandbox_region" class="form-control" value="<?= htmlspecialchars($paypal_sandbox_region) ?>" placeholder="AU">
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="col-sm-8 col-sm-offset-3">
                                <button type="submit" class="btn btn-primary">Save gateway settings</button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="ibox">
                    <div class="ibox-title"><h5>Sandbox — Default Application</h5></div>
                    <div class="ibox-content">
                        <div class="form-group">
                            <label class="col-sm-3 control-label">Client ID</label>
                            <div class="col-sm-8">
                                <input type="text" name="paypal_sandbox_client_id" class="form-control" value="<?= htmlspecialchars($paypal_sandbox_client_id) ?>">
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-3 control-label">Secret</label>
                            <div class="col-sm-8">
                                <input type="text" name="paypal_sandbox_secret" class="form-control" value="<?= htmlspecialchars($paypal_sandbox_secret) ?>" autocomplete="off">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="ibox">
                    <div class="ibox-title"><h5>Sandbox — Nexa Application</h5></div>
                    <div class="ibox-content">
                        <div class="form-group">
                            <label class="col-sm-3 control-label">Client ID</label>
                            <div class="col-sm-8">
                                <input type="text" name="paypal_nexa_sandbox_client_id" class="form-control" value="<?= htmlspecialchars($paypal_nexa_sandbox_client_id) ?>">
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-3 control-label">Secret</label>
                            <div class="col-sm-8">
                                <input type="text" name="paypal_nexa_sandbox_secret" class="form-control" value="<?= htmlspecialchars($paypal_nexa_sandbox_secret) ?>" autocomplete="off">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="ibox">
                    <div class="ibox-title"><h5>Live credentials</h5></div>
                    <div class="ibox-content">
                        <div class="form-group">
                            <label class="col-sm-3 control-label">Client ID</label>
                            <div class="col-sm-8">
                                <input type="text" name="paypal_live_client_id" class="form-control" value="<?= htmlspecialchars($paypal_live_client_id) ?>">
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-3 control-label">Secret</label>
                            <div class="col-sm-8">
                                <input type="text" name="paypal_live_secret" class="form-control" value="<?= htmlspecialchars($paypal_live_secret) ?>" autocomplete="off">
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-3 control-label">Primary email</label>
                            <div class="col-sm-8">
                                <input type="email" name="paypal_live_email" class="form-control" value="<?= htmlspecialchars($paypal_live_email) ?>" placeholder="merchant@example.com">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="ibox">
                    <div class="ibox-title"><h5>Sandbox buyer (reference only)</h5></div>
                    <div class="ibox-content">
                        <p class="text-muted">Not used by the API — handy when testing PayPal wallet login on sandbox.paypal.com.</p>
                        <div class="form-group">
                            <label class="col-sm-3 control-label">Buyer email</label>
                            <div class="col-sm-8">
                                <input type="email" name="paypal_sandbox_buyer_email" class="form-control" value="<?= htmlspecialchars($paypal_sandbox_buyer_email) ?>">
                            </div>
                        </div>
                        <div class="well well-sm" style="margin:0">
                            <strong>Test cards (sandbox)</strong><br>
                            VISA <code>4239535545587057</code> · Exp <code>10/2031</code> · CVC any 3 digits<br>
                            Mastercard <code>5125765546045821</code> · Exp <code>10/2031</code> · CVC any 3 digits<br>
                            Sandbox URL: <a href="https://sandbox.paypal.com" target="_blank" rel="noopener">https://sandbox.paypal.com</a>
                        </div>
                        <div class="form-group" style="margin-top:20px;margin-bottom:0">
                            <div class="col-sm-8 col-sm-offset-3">
                                <button type="submit" class="btn btn-primary">Save gateway settings</button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
