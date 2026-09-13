<div class="row wrapper border-bottom white-bg page-heading">
    <div class="col-lg-10">
        <h2>SMTP Settings</h2>
        <ol class="breadcrumb">
            <li><a href="<?= base_url('admin/admin') ?>">Dashboard</a></li>
            <li class="active"><strong>SMTP</strong></li>
        </ol>
    </div>
</div>
<div class="wrapper wrapper-content">
    <div class="row">
        <div class="col-lg-8">
            <div class="ibox">
                <div class="ibox-title"><h5>Outgoing mail</h5></div>
                <div class="ibox-content">
                    <?php $this->load->view('flash'); ?>
                    <p class="text-muted">Order emails go to customer, store, ecommerce users and admin on every status change. Enable SMTP to send real emails; otherwise messages are saved under <code>application/logs/mail/</code>.</p>
                    <form method="post" action="<?= base_url('admin/smtp/save') ?>" class="form-horizontal">
                        <div class="form-group">
                            <label class="col-sm-3 control-label">Enable SMTP</label>
                            <div class="col-sm-7">
                                <label class="checkbox-inline">
                                    <input type="checkbox" name="enabled" value="1" <?= $smtp['enabled'] === '1' ? 'checked' : '' ?>> Send via SMTP server
                                </label>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-3 control-label">Host</label>
                            <div class="col-sm-7"><input type="text" name="host" class="form-control" value="<?= htmlspecialchars($smtp['host']) ?>" placeholder="smtp.gmail.com"></div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-3 control-label">Port</label>
                            <div class="col-sm-3"><input type="text" name="port" class="form-control" value="<?= htmlspecialchars($smtp['port']) ?>"></div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-3 control-label">Encryption</label>
                            <div class="col-sm-3">
                                <select name="crypto" class="form-control">
                                    <option value="tls" <?= $smtp['crypto'] === 'tls' ? 'selected' : '' ?>>TLS</option>
                                    <option value="ssl" <?= $smtp['crypto'] === 'ssl' ? 'selected' : '' ?>>SSL</option>
                                    <option value="" <?= $smtp['crypto'] === '' ? 'selected' : '' ?>>None</option>
                                </select>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-3 control-label">Username</label>
                            <div class="col-sm-7"><input type="text" name="user" class="form-control" value="<?= htmlspecialchars($smtp['user']) ?>"></div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-3 control-label">Password</label>
                            <div class="col-sm-7">
                                <input type="password" name="pass" class="form-control" value="" placeholder="<?= $smtp['pass'] !== '' ? 'Leave blank to keep current' : '' ?>">
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-3 control-label">From email</label>
                            <div class="col-sm-7"><input type="email" name="from_email" class="form-control" value="<?= htmlspecialchars($smtp['from_email']) ?>"></div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-3 control-label">From name</label>
                            <div class="col-sm-7"><input type="text" name="from_name" class="form-control" value="<?= htmlspecialchars($smtp['from_name']) ?>"></div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-3 control-label">Admin notify email</label>
                            <div class="col-sm-7"><input type="email" name="admin_notify_email" class="form-control" value="<?= htmlspecialchars($smtp['admin_notify_email']) ?>"></div>
                        </div>
                        <div class="form-group">
                            <div class="col-sm-7 col-sm-offset-3">
                                <button type="submit" class="btn btn-primary">Save SMTP</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            <div class="ibox">
                <div class="ibox-title"><h5>Send test email</h5></div>
                <div class="ibox-content">
                    <form method="post" action="<?= base_url('admin/smtp/test') ?>" class="form-inline">
                        <div class="form-group">
                            <input type="email" name="test_email" class="form-control" placeholder="you@example.com" value="<?= htmlspecialchars($smtp['admin_notify_email']) ?>">
                        </div>
                        <button type="submit" class="btn btn-white">Send test</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
