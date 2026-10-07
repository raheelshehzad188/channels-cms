<div class="row wrapper border-bottom white-bg page-heading">
    <div class="col-lg-10">
        <h2>PayPal logs</h2>
        <ol class="breadcrumb">
            <li><a href="<?= base_url('admin/admin') ?>">Dashboard</a></li>
            <li><a href="<?= base_url('admin/paypal') ?>">Gateway</a></li>
            <li class="active"><strong>Logs</strong></li>
        </ol>
    </div>
</div>
<div class="wrapper wrapper-content">
    <div class="row">
        <div class="col-lg-12">
            <div class="ibox">
                <div class="ibox-title">
                    <h5>Card / wallet request &amp; response</h5>
                    <div class="ibox-tools">
                        <a href="<?= base_url('admin/paypal') ?>" class="btn btn-xs btn-white">Back to gateway</a>
                    </div>
                </div>
                <div class="ibox-content">
                    <p class="text-muted">Current mode: <strong><?= htmlspecialchars($paypal_mode) ?></strong>. Every PayPal card and wallet call is appended here (request + HTTP response). Card number is masked to last 4; CVV and tokens are not stored.</p>
                    <?php if (!$files) { ?>
                        <p>No logs yet. Place a test card payment, then refresh this page.</p>
                    <?php } else { ?>
                        <form method="get" class="form-inline" style="margin-bottom:15px">
                            <label>Log file</label>
                            <select name="file" class="form-control" onchange="this.form.submit()">
                                <?php foreach ($files as $path) {
                                    $base = basename($path); ?>
                                    <option value="<?= htmlspecialchars($base) ?>" <?= $current === $base ? 'selected' : '' ?>><?= htmlspecialchars($base) ?></option>
                                <?php } ?>
                            </select>
                        </form>
                        <pre style="max-height:640px;overflow:auto;background:#111;color:#d6f5d6;padding:16px;font-size:12px;white-space:pre-wrap"><?= htmlspecialchars($contents !== '' ? $contents : '(empty)') ?></pre>
                    <?php } ?>
                </div>
            </div>
        </div>
    </div>
</div>
