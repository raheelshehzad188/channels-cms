<?php $isEdit = !empty($store); ?>
<div class="row wrapper border-bottom white-bg page-heading">
    <div class="col-lg-10">
        <h2><?= $isEdit ? 'Edit Store' : 'Add Store' ?></h2>
        <ol class="breadcrumb">
            <li><a href="<?= base_url('admin/stores') ?>">Stores</a></li>
            <li class="active"><strong><?= $isEdit ? 'Edit' : 'Add' ?></strong></li>
        </ol>
    </div>
</div>
<div class="wrapper wrapper-content">
    <div class="ibox">
        <div class="ibox-title"><h5>Store Details</h5></div>
        <div class="ibox-content">
            <?php $this->load->view('flash'); ?>
            <form method="post" action="<?= base_url('admin/stores/save' . ($isEdit ? '/' . $store->id : '')) ?>" class="form-horizontal">
                <div class="form-group">
                    <label class="col-sm-2 control-label">Store Name</label>
                    <div class="col-sm-8">
                        <input type="text" name="name" class="form-control" required value="<?= $isEdit ? htmlspecialchars($store->name) : '' ?>">
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-sm-2 control-label">Domain</label>
                    <div class="col-sm-8">
                        <input type="text" name="domain" class="form-control" required placeholder="theme1.ecommerce.test" value="<?= $isEdit ? htmlspecialchars($store->domain) : '' ?>">
                        <span class="help-block">Example: theme1.ecommerce.test</span>
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-sm-2 control-label">Country</label>
                    <div class="col-sm-8">
                        <select name="country_id" class="form-control" required>
                            <option value="">Select country</option>
                            <?php foreach ($countries as $country): ?>
                                <option value="<?= (int) $country->id ?>" <?= $isEdit && (int) $store->country_id === (int) $country->id ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($country->name) ?> (<?= htmlspecialchars($country->currency) ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <span class="help-block">Storefront prices and PayPal charges use this country’s currency.</span>
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-sm-2 control-label">Owner Name</label>
                    <div class="col-sm-8">
                        <input type="text" name="owner_name" class="form-control" value="<?= $isEdit ? htmlspecialchars($store->owner_name) : '' ?>">
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-sm-2 control-label">Login Email</label>
                    <div class="col-sm-8">
                        <input type="email" name="email" class="form-control" required value="<?= $isEdit ? htmlspecialchars($store->email) : '' ?>">
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-sm-2 control-label">Password</label>
                    <div class="col-sm-8">
                        <input type="password" name="password" class="form-control" <?= $isEdit ? '' : 'required' ?> placeholder="<?= $isEdit ? 'Leave blank to keep current password' : '' ?>">
                    </div>
                </div>
                <div class="form-group">
                    <div class="col-sm-8 col-sm-offset-2">
                        <a href="<?= base_url('admin/stores') ?>" class="btn btn-white">Cancel</a>
                        <button type="submit" class="btn btn-primary">Next: Select Theme</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
