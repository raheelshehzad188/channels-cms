<?php
$platformCurrency = isset($platform_currency) ? $platform_currency : platform_currency();
$isAdmin = !empty($is_admin);
$payoutLabels = array(
    'pending' => 'warning',
    'paid' => 'primary',
    'rejected' => 'danger',
);
?>
<div class="row wrapper border-bottom white-bg page-heading">
    <div class="col-lg-8">
        <h2>Accounting</h2>
        <ol class="breadcrumb">
            <?php if ($isAdmin): ?>
            <li><a href="<?= base_url('admin/admin') ?>">Dashboard</a></li>
            <?php endif; ?>
            <li class="active"><strong>Accounting</strong></li>
        </ol>
    </div>
    <?php if ($isAdmin): ?>
    <div class="col-lg-4 text-right">
        <a href="<?= base_url('admin/accounting/payouts') ?>" class="btn btn-primary" style="margin-top:26px;">All payouts</a>
    </div>
    <?php endif; ?>
</div>
<div class="wrapper wrapper-content">
    <?php $this->load->view('flash'); ?>

    <?php if ($isAdmin): ?>
    <div class="row">
        <div class="col-md-3">
            <div class="ibox"><div class="ibox-content">
                <h5>Total VAT collected</h5>
                <h2><?= format_money($vat_total, $platformCurrency) ?></h2>
                <small class="text-muted">Paid, non-cancelled orders in <?= htmlspecialchars($platformCurrency) ?></small>
            </div></div>
        </div>
        <div class="col-md-3">
            <div class="ibox"><div class="ibox-content">
                <h5>Store earnings</h5>
                <h2><?= format_money(isset($store_totals['earnings_platform']) ? $store_totals['earnings_platform'] : 0, $platformCurrency) ?></h2>
                <small class="text-muted">Store plus / markup</small>
            </div></div>
        </div>
        <div class="col-md-3">
            <div class="ibox"><div class="ibox-content">
                <h5>Waiting</h5>
                <h2 class="text-warning"><?= format_money(isset($store_totals['waiting_platform']) ? $store_totals['waiting_platform'] : 0, $platformCurrency) ?></h2>
                <small class="text-muted">Not yet completed by Super Admin</small>
            </div></div>
        </div>
        <div class="col-md-3">
            <div class="ibox"><div class="ibox-content">
                <h5>Store wallets</h5>
                <h2 class="text-navy"><?= format_money(isset($store_totals['wallet_platform']) ? $store_totals['wallet_platform'] : 0, $platformCurrency) ?></h2>
                <small class="text-muted">Available to withdraw</small>
            </div></div>
        </div>
    </div>

    <div class="ibox">
        <div class="ibox-title"><h5>Stores</h5></div>
        <div class="ibox-content">
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Store</th>
                            <th>VAT</th>
                            <th>Earnings</th>
                            <th>Waiting</th>
                            <th>Wallet</th>
                            <th>Pending payout</th>
                            <th>Paid out</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($stores as $row): ?>
                        <tr>
                            <td><?= htmlspecialchars($row['name']) ?><br><small class="text-muted"><?= htmlspecialchars($row['currency']) ?></small></td>
                            <td><?= format_money($row['vat'], $row['currency']) ?></td>
                            <td><?= format_money($row['earnings'], $row['currency']) ?></td>
                            <td><?= format_money($row['waiting'], $row['currency']) ?></td>
                            <td><?= format_money($row['wallet'], $row['currency']) ?></td>
                            <td><?= format_money($row['pending_payout'], $row['currency']) ?></td>
                            <td><?= format_money($row['paid_out'], $row['currency']) ?></td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if (empty($stores)): ?>
                        <tr><td colspan="7" class="text-center text-muted">No stores yet.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="ibox">
        <div class="ibox-title"><h5>Ecommerce users</h5></div>
        <div class="ibox-content">
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>User</th>
                            <th>VAT share</th>
                            <th>Commission earned</th>
                            <th>Waiting</th>
                            <th>Wallet</th>
                            <th>Pending payout</th>
                            <th>Paid out</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($ecommerce_rows as $row): ?>
                        <tr>
                            <td><?= htmlspecialchars($row['name']) ?></td>
                            <td><?= format_money($row['vat'], $platformCurrency) ?></td>
                            <td><?= format_money($row['earnings'], $platformCurrency) ?></td>
                            <td><?= format_money($row['waiting'], $platformCurrency) ?></td>
                            <td><?= format_money($row['wallet'], $platformCurrency) ?></td>
                            <td><?= format_money($row['pending_payout'], $platformCurrency) ?></td>
                            <td><?= format_money($row['paid_out'], $platformCurrency) ?></td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if (empty($ecommerce_rows)): ?>
                        <tr><td colspan="7" class="text-center text-muted">No ecommerce users yet.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="ibox">
        <div class="ibox-title"><h5>Pending payouts</h5></div>
        <div class="ibox-content">
            <?php if (empty($payouts)): ?>
                <p class="text-muted mb-0">No pending payout requests.</p>
            <?php else: ?>
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Who</th>
                            <th>Amount</th>
                            <th>Requested</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($payouts as $payout): ?>
                        <tr>
                            <td><?= (int) $payout->id ?></td>
                            <td><?= htmlspecialchars(ucfirst($payout->party_type) . ' · ' . $payout->party_name) ?></td>
                            <td><?= format_money((float) $payout->amount, $payout->currency) ?></td>
                            <td><?= htmlspecialchars($payout->requested_at) ?></td>
                            <td><a href="<?= base_url('admin/accounting/payouts') ?>">Review</a></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <?php else: ?>
        <?php $s = $summary; ?>
        <div class="row">
            <div class="col-md-3">
                <div class="ibox"><div class="ibox-content">
                    <h5>VAT on your items</h5>
                    <h2><?= format_money($s['vat'], $platformCurrency) ?></h2>
                </div></div>
            </div>
            <div class="col-md-3">
                <div class="ibox"><div class="ibox-content">
                    <h5>Commission earned</h5>
                    <h2><?= format_money($s['earnings'], $platformCurrency) ?></h2>
                </div></div>
            </div>
            <div class="col-md-3">
                <div class="ibox"><div class="ibox-content">
                    <h5>Waiting</h5>
                    <h2 class="text-warning"><?= format_money($s['waiting'], $platformCurrency) ?></h2>
                    <small class="text-muted">Released when Super Admin completes the item</small>
                </div></div>
            </div>
            <div class="col-md-3">
                <div class="ibox"><div class="ibox-content">
                    <h5>Wallet</h5>
                    <h2 class="text-navy"><?= format_money($s['wallet'], $platformCurrency) ?></h2>
                    <small class="text-muted">Pending payout <?= format_money($s['pending_payout'], $platformCurrency) ?></small>
                </div></div>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-6">
                <div class="ibox">
                    <div class="ibox-title"><h5>WhatsApp notifications</h5></div>
                    <div class="ibox-content">
                        <form method="post" action="<?= base_url('admin/accounting/save_whatsapp') ?>" class="form-horizontal">
                            <div class="form-group">
                                <label class="col-sm-3 control-label">WhatsApp number</label>
                                <div class="col-sm-9">
                                    <input type="text" name="whatsapp_number" class="form-control" value="<?= htmlspecialchars(isset($whatsapp_number) ? $whatsapp_number : '') ?>" placeholder="923004210607">
                                    <span class="help-block">You receive a WhatsApp when a customer orders one of your products. Use country code without +.</span>
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="col-sm-offset-3 col-sm-9">
                                    <button type="submit" class="btn btn-primary">Save WhatsApp number</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
                <div class="ibox">
                    <div class="ibox-title"><h5>Account details</h5></div>
                    <div class="ibox-content">
                        <form method="post" action="<?= base_url('admin/accounting/save_account') ?>" class="form-horizontal">
                            <?php $this->load->view('inspinia/admin/accounting/bank_fields', array('bank' => $bank)); ?>
                            <div class="form-group">
                                <div class="col-sm-offset-3 col-sm-9">
                                    <button type="submit" class="btn btn-primary">Save account details</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="ibox">
                    <div class="ibox-title"><h5>Request payout</h5></div>
                    <div class="ibox-content">
                        <form method="post" action="<?= base_url('admin/accounting/request_payout') ?>">
                            <div class="form-group">
                                <label>Amount (<?= htmlspecialchars($platformCurrency) ?>)</label>
                                <input type="number" step="0.01" min="0.01" name="amount" class="form-control" required value="<?= htmlspecialchars(number_format((float) $s['wallet'], 2, '.', '')) ?>">
                            </div>
                            <button type="submit" class="btn btn-primary" <?= ((float) $s['wallet'] <= 0) ? 'disabled' : '' ?>>Request payout</button>
                        </form>
                    </div>
                </div>
                <div class="ibox">
                    <div class="ibox-title"><h5>Payout history</h5></div>
                    <div class="ibox-content">
                        <?php if (empty($payouts)): ?>
                            <p class="text-muted mb-0">No payout requests yet.</p>
                        <?php else: ?>
                        <table class="table table-striped">
                            <thead><tr><th>Date</th><th>Amount</th><th>Status</th><th></th></tr></thead>
                            <tbody>
                            <?php foreach ($payouts as $payout): ?>
                                <tr>
                                    <td><?= htmlspecialchars($payout->requested_at) ?></td>
                                    <td><?= format_money((float) $payout->amount, $payout->currency) ?></td>
                                    <td><span class="label label-<?= isset($payoutLabels[$payout->status]) ? $payoutLabels[$payout->status] : 'default' ?>"><?= htmlspecialchars(ucfirst($payout->status)) ?></span></td>
                                    <td>
                                        <?php if (!empty($payout->receipt)): ?>
                                            <a class="btn btn-xs btn-white" href="<?= base_url($payout->receipt) ?>" target="_blank">View receipt</a>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="ibox">
            <div class="ibox-title"><h5>Wallet ledger</h5></div>
            <div class="ibox-content">
                <?php if (empty($ledger)): ?>
                    <p class="text-muted mb-0">No wallet movements yet. Amounts move here when Super Admin completes items.</p>
                <?php else: ?>
                <table class="table table-striped">
                    <thead><tr><th>Date</th><th>Type</th><th>Amount</th><th>Balance</th><th>Note</th></tr></thead>
                    <tbody>
                    <?php foreach ($ledger as $row): ?>
                        <tr>
                            <td><?= htmlspecialchars($row->created_at) ?></td>
                            <td><?= htmlspecialchars(str_replace('_', ' ', $row->entry_type)) ?></td>
                            <td><?= format_money((float) $row->amount, $row->currency) ?></td>
                            <td><?= format_money((float) $row->balance_after, $row->currency) ?></td>
                            <td><?= htmlspecialchars($row->note) ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>
</div>
