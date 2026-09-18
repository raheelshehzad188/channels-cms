<?php $bank = isset($bank) ? $bank : (object) array(); ?>
<div class="form-group">
    <label class="col-sm-3 control-label">Account holder</label>
    <div class="col-sm-9">
        <input type="text" name="account_holder" class="form-control" value="<?= htmlspecialchars(isset($bank->account_holder) ? $bank->account_holder : '') ?>">
    </div>
</div>
<div class="form-group">
    <label class="col-sm-3 control-label">Bank name</label>
    <div class="col-sm-9">
        <input type="text" name="bank_name" class="form-control" value="<?= htmlspecialchars(isset($bank->bank_name) ? $bank->bank_name : '') ?>">
    </div>
</div>
<div class="form-group">
    <label class="col-sm-3 control-label">Account number</label>
    <div class="col-sm-9">
        <input type="text" name="account_number" class="form-control" value="<?= htmlspecialchars(isset($bank->account_number) ? $bank->account_number : '') ?>">
    </div>
</div>
<div class="form-group">
    <label class="col-sm-3 control-label">IBAN</label>
    <div class="col-sm-9">
        <input type="text" name="iban" class="form-control" value="<?= htmlspecialchars(isset($bank->iban) ? $bank->iban : '') ?>">
    </div>
</div>
<div class="form-group">
    <label class="col-sm-3 control-label">SWIFT / BIC</label>
    <div class="col-sm-9">
        <input type="text" name="swift" class="form-control" value="<?= htmlspecialchars(isset($bank->swift) ? $bank->swift : '') ?>">
    </div>
</div>
<div class="form-group">
    <label class="col-sm-3 control-label">Routing number</label>
    <div class="col-sm-9">
        <input type="text" name="routing_number" class="form-control" value="<?= htmlspecialchars(isset($bank->routing_number) ? $bank->routing_number : '') ?>">
    </div>
</div>
<div class="form-group">
    <label class="col-sm-3 control-label">PayPal email</label>
    <div class="col-sm-9">
        <input type="email" name="paypal_email" class="form-control" value="<?= htmlspecialchars(isset($bank->paypal_email) ? $bank->paypal_email : '') ?>">
        <span class="help-block">Bank account or PayPal email is required before a payout request.</span>
    </div>
</div>
