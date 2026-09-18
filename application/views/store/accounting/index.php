<?php
$s = $summary;
$payoutLabels = array(
    'pending' => 'warning',
    'paid' => 'primary',
    'rejected' => 'danger',
);
?>
<?php $this->load->view('flash'); ?>

<?php $receivedPayouts = isset($received_payouts) ? $received_payouts : array(); ?>
<div class="row g-3 mb-4">
  <div class="col-md-4 col-sm-6">
    <div class="stat-card">
      <div class="label">Your earnings</div>
      <div class="value"><?= format_money($s['earnings'], $s['currency']); ?></div>
      <div class="small text-muted mt-1">Your product plus</div>
    </div>
  </div>
  <div class="col-md-4 col-sm-6">
    <div class="stat-card">
      <div class="label">Waiting</div>
      <div class="value"><?= format_money($s['waiting'], $s['currency']); ?></div>
      <div class="small text-muted mt-1">Until Super Admin completes items</div>
    </div>
  </div>
  <div class="col-md-4 col-sm-6">
    <div class="stat-card">
      <div class="label">Wallet</div>
      <div class="value"><?= format_money($s['wallet'], $s['currency']); ?></div>
      <div class="small text-muted mt-1">Pending payout <?= format_money($s['pending_payout'], $s['currency']); ?></div>
    </div>
  </div>
</div>

<div class="row g-3">
  <div class="col-lg-6">
    <div class="store-card mb-3">
      <div class="card-header">Account details</div>
      <div class="card-body">
        <form method="post" action="<?= $storeUrl ?>/accounting/save-bank">
          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label">Account holder</label>
              <input type="text" name="account_holder" class="form-control" value="<?= htmlspecialchars($bank->account_holder); ?>">
            </div>
            <div class="col-md-6">
              <label class="form-label">Bank name</label>
              <input type="text" name="bank_name" class="form-control" value="<?= htmlspecialchars($bank->bank_name); ?>">
            </div>
            <div class="col-md-6">
              <label class="form-label">Account number</label>
              <input type="text" name="account_number" class="form-control" value="<?= htmlspecialchars($bank->account_number); ?>">
            </div>
            <div class="col-md-6">
              <label class="form-label">IBAN</label>
              <input type="text" name="iban" class="form-control" value="<?= htmlspecialchars($bank->iban); ?>">
            </div>
            <div class="col-md-6">
              <label class="form-label">SWIFT / BIC</label>
              <input type="text" name="swift" class="form-control" value="<?= htmlspecialchars($bank->swift); ?>">
            </div>
            <div class="col-md-6">
              <label class="form-label">Routing number</label>
              <input type="text" name="routing_number" class="form-control" value="<?= htmlspecialchars($bank->routing_number); ?>">
            </div>
            <div class="col-12">
              <label class="form-label">PayPal email</label>
              <input type="email" name="paypal_email" class="form-control" value="<?= htmlspecialchars($bank->paypal_email); ?>">
              <div class="form-text">Add bank details or PayPal before requesting a payout.</div>
            </div>
            <div class="col-12">
              <button type="submit" class="btn btn-store-primary">Save account details</button>
            </div>
          </div>
        </form>
      </div>
    </div>
  </div>
  <div class="col-lg-6">
    <div class="store-card mb-3">
      <div class="card-header">Request payout</div>
      <div class="card-body">
        <form method="post" action="<?= $storeUrl ?>/accounting/payout">
          <label class="form-label">Amount (<?= htmlspecialchars($s['currency']); ?>)</label>
          <input type="number" step="0.01" min="0.01" name="amount" class="form-control mb-3" required value="<?= htmlspecialchars(number_format((float) $s['wallet'], 2, '.', '')); ?>">
          <button type="submit" class="btn btn-store-primary w-100" <?= ((float) $s['wallet'] <= 0) ? 'disabled' : '' ?>>Request payout</button>
        </form>
      </div>
    </div>
    <div class="store-card mb-3">
      <div class="card-header">Open payout requests</div>
      <div class="card-body p-0">
        <div class="table-responsive">
          <table class="table mb-0">
            <thead><tr><th>Requested</th><th>Amount</th><th>Status</th></tr></thead>
            <tbody>
              <?php foreach ($payouts as $payout): ?>
              <tr>
                <td class="small text-muted"><?= htmlspecialchars($payout->requested_at); ?></td>
                <td><?= format_money((float) $payout->amount, $payout->currency); ?></td>
                <td><span class="badge text-bg-<?= isset($payoutLabels[$payout->status]) ? $payoutLabels[$payout->status] : 'light' ?>"><?= htmlspecialchars(ucfirst($payout->status)); ?></span></td>
              </tr>
              <?php endforeach; ?>
              <?php if (empty($payouts)): ?>
              <tr><td colspan="3" class="text-center text-muted py-3">No open payout requests.</td></tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>

<div class="store-card mb-3">
  <div class="card-header d-flex justify-content-between align-items-center">
    <span>Payouts received</span>
    <strong><?= format_money($s['paid_out'], $s['currency']); ?></strong>
  </div>
  <div class="card-body p-0">
    <div class="table-responsive">
      <table class="table mb-0">
        <thead>
          <tr>
            <th>Paid on</th>
            <th>Requested</th>
            <th>Amount</th>
            <th>Note</th>
            <th></th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($receivedPayouts as $payout): ?>
          <tr>
            <td><?= htmlspecialchars($payout->processed_at ?: $payout->requested_at); ?></td>
            <td class="small text-muted"><?= htmlspecialchars($payout->requested_at); ?></td>
            <td><?= format_money((float) $payout->amount, $payout->currency); ?></td>
            <td class="small"><?= htmlspecialchars($payout->admin_note ?: '—'); ?></td>
            <td class="text-end">
              <?php if (!empty($payout->receipt)): ?>
                <button type="button" class="btn btn-sm btn-outline-secondary js-view-receipt"
                  data-bs-toggle="modal" data-bs-target="#receiptModal"
                  data-receipt="<?= htmlspecialchars(base_url($payout->receipt)) ?>"
                  data-name="<?= htmlspecialchars(pathinfo($payout->receipt, PATHINFO_EXTENSION)) ?>">View receipt</button>
              <?php else: ?>
                <span class="text-muted small">No slip</span>
              <?php endif; ?>
            </td>
          </tr>
          <?php endforeach; ?>
          <?php if (empty($receivedPayouts)): ?>
          <tr><td colspan="5" class="text-center text-muted py-3">No payouts received yet.</td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<div class="store-card">
  <div class="card-header">Wallet ledger</div>
  <div class="card-body p-0">
    <div class="table-responsive">
      <table class="table mb-0">
        <thead><tr><th>Date</th><th>Type</th><th>Amount</th><th>Balance</th><th>Note</th></tr></thead>
        <tbody>
          <?php foreach ($ledger as $row): ?>
          <tr>
            <td class="small text-muted"><?= htmlspecialchars($row->created_at); ?></td>
            <td><?= htmlspecialchars(str_replace('_', ' ', $row->entry_type)); ?></td>
            <td><?= format_money((float) $row->amount, $row->currency); ?></td>
            <td><?= format_money((float) $row->balance_after, $row->currency); ?></td>
            <td class="small"><?= htmlspecialchars($row->note); ?></td>
          </tr>
          <?php endforeach; ?>
          <?php if (empty($ledger)): ?>
          <tr><td colspan="5" class="text-center text-muted py-3">No wallet movements yet.</td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<div class="modal fade" id="receiptModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Payout receipt</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body text-center">
        <img id="receiptImage" src="" alt="Receipt" class="img-fluid rounded" style="display:none;max-height:70vh;">
        <iframe id="receiptFrame" src="" style="display:none;width:100%;min-height:70vh;border:0;"></iframe>
      </div>
      <div class="modal-footer">
        <a id="receiptOpen" href="#" target="_blank" class="btn btn-outline-secondary">Open original</a>
        <button type="button" class="btn btn-store-primary" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>
<script>
document.addEventListener('DOMContentLoaded', function () {
  var modal = document.getElementById('receiptModal');
  if (!modal) return;
  modal.addEventListener('show.bs.modal', function (event) {
    var btn = event.relatedTarget;
    if (!btn) return;
    var url = btn.getAttribute('data-receipt') || '';
    var ext = (btn.getAttribute('data-name') || '').toLowerCase();
    var img = document.getElementById('receiptImage');
    var frame = document.getElementById('receiptFrame');
    var open = document.getElementById('receiptOpen');
    open.href = url;
    if (ext === 'pdf') {
      img.style.display = 'none';
      img.removeAttribute('src');
      frame.style.display = 'block';
      frame.src = url;
    } else {
      frame.style.display = 'none';
      frame.removeAttribute('src');
      img.style.display = 'inline-block';
      img.src = url;
    }
  });
});
</script>
