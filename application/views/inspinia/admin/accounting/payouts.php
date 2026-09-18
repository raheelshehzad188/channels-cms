<?php
$payoutLabels = array(
    'pending' => 'warning',
    'paid' => 'primary',
    'rejected' => 'danger',
);
$filterStatus = isset($status) ? $status : '';
?>
<div class="row wrapper border-bottom white-bg page-heading">
    <div class="col-lg-8">
        <h2>Payouts</h2>
        <ol class="breadcrumb">
            <li><a href="<?= base_url('admin/accounting') ?>">Accounting</a></li>
            <li class="active"><strong>Payouts</strong></li>
        </ol>
    </div>
</div>
<div class="wrapper wrapper-content">
    <?php $this->load->view('flash'); ?>
    <div class="ibox">
        <div class="ibox-title"><h5>Payout requests</h5></div>
        <div class="ibox-content">
            <form method="get" class="form-inline" style="margin-bottom:15px;">
                <select name="status" class="form-control">
                    <option value="">All statuses</option>
                    <option value="pending" <?= $filterStatus === 'pending' ? 'selected' : '' ?>>Pending</option>
                    <option value="paid" <?= $filterStatus === 'paid' ? 'selected' : '' ?>>Paid</option>
                    <option value="rejected" <?= $filterStatus === 'rejected' ? 'selected' : '' ?>>Rejected</option>
                </select>
                <button type="submit" class="btn btn-primary">Filter</button>
                <a href="<?= base_url('admin/accounting/payouts') ?>" class="btn btn-white">Reset</a>
            </form>
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Who</th>
                            <th>Amount</th>
                            <th>Account</th>
                            <th>Status</th>
                            <th>Requested</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($payouts as $payout): ?>
                        <?php $bank = $payout->bank; ?>
                        <tr>
                            <td><?= (int) $payout->id ?></td>
                            <td><?= htmlspecialchars(ucfirst($payout->party_type) . ' · ' . $payout->party_name) ?></td>
                            <td><?= format_money((float) $payout->amount, $payout->currency) ?></td>
                            <td class="small">
                                <?php if ($bank): ?>
                                    <?= htmlspecialchars($bank->account_holder) ?><br>
                                    <?= htmlspecialchars($bank->bank_name) ?>
                                    <?= htmlspecialchars($bank->account_number ?: $bank->iban) ?><br>
                                    <?php if (!empty($bank->paypal_email)): ?>PayPal: <?= htmlspecialchars($bank->paypal_email) ?><?php endif; ?>
                                <?php else: ?>
                                    —
                                <?php endif; ?>
                            </td>
                            <td><span class="label label-<?= isset($payoutLabels[$payout->status]) ? $payoutLabels[$payout->status] : 'default' ?>"><?= htmlspecialchars(ucfirst($payout->status)) ?></span></td>
                            <td><?= htmlspecialchars($payout->requested_at) ?></td>
                            <td>
                                <?php if ($payout->status === 'pending'): ?>
                                    <button type="button" class="btn btn-xs btn-primary" data-toggle="modal" data-target="#payModal<?= (int) $payout->id ?>">Mark paid</button>
                                    <form method="post" action="<?= base_url('admin/accounting/process_payout/' . $payout->id) ?>" style="display:inline;">
                                        <button name="action" value="reject" class="btn btn-xs btn-danger" onclick="return confirm('Reject and return funds to wallet?');">Reject</button>
                                    </form>
                                    <div class="modal fade" id="payModal<?= (int) $payout->id ?>" tabindex="-1" role="dialog">
                                        <div class="modal-dialog" role="document">
                                            <div class="modal-content">
                                                <form method="post" action="<?= base_url('admin/accounting/process_payout/' . $payout->id) ?>" enctype="multipart/form-data">
                                                    <div class="modal-header">
                                                        <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                                                        <h4 class="modal-title">Mark payout as paid</h4>
                                                    </div>
                                                    <div class="modal-body">
                                                        <p>
                                                            <strong><?= htmlspecialchars($payout->party_name) ?></strong><br>
                                                            Amount: <strong><?= format_money((float) $payout->amount, $payout->currency) ?></strong>
                                                        </p>
                                                        <div class="form-group">
                                                            <label>Payment slip <span class="text-danger">*</span></label>
                                                            <input type="file" name="receipt" class="form-control" accept=".jpg,.jpeg,.png,.gif,.webp,.pdf,image/*,application/pdf" required>
                                                            <p class="help-block">JPG, PNG, WEBP or PDF. This slip will be shown to the store.</p>
                                                        </div>
                                                        <div class="form-group">
                                                            <label>Note (optional)</label>
                                                            <input type="text" name="admin_note" class="form-control" placeholder="Transaction ID or comment">
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-white" data-dismiss="modal">Cancel</button>
                                                        <button type="submit" name="action" value="pay" class="btn btn-primary">Confirm paid</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                <?php else: ?>
                                    <?php if (!empty($payout->receipt)): ?>
                                        <a class="btn btn-xs btn-white" href="<?= base_url($payout->receipt) ?>" target="_blank">View receipt</a>
                                    <?php endif; ?>
                                    <?php if (!empty($payout->admin_note)): ?>
                                        <div class="small text-muted"><?= htmlspecialchars($payout->admin_note) ?></div>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (empty($payouts)): ?>
                        <tr><td colspan="7" class="text-center text-muted">No payout requests.</td></tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
