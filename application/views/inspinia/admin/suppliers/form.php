<?php $isEdit = !empty($supplier); ?>
<div class="row wrapper border-bottom white-bg page-heading">
    <div class="col-lg-10">
        <h2><?= $isEdit ? 'Edit Supplier' : 'Add Supplier' ?></h2>
        <ol class="breadcrumb">
            <li><a href="<?= base_url('admin/admin') ?>">Dashboard</a></li>
            <li><a href="<?= base_url('admin/suppliers') ?>">Suppliers</a></li>
            <li class="active"><strong><?= $isEdit ? 'Edit' : 'Add' ?></strong></li>
        </ol>
    </div>
</div>
<div class="wrapper wrapper-content animated fadeInRight">
    <div class="row">
        <div class="col-lg-8">
            <div class="ibox float-e-margins">
                <div class="ibox-title"><h5>Supplier Details</h5></div>
                <div class="ibox-content">
                    <?php $this->load->view('flash'); ?>
                    <form method="post" action="<?= base_url('admin/suppliers/save' . ($isEdit ? '/' . $supplier->id : '')) ?>" class="form-horizontal">
                        <div class="form-group">
                            <label class="col-sm-3 control-label">Country</label>
                            <div class="col-sm-9">
                                <select name="country_id" class="form-control" required>
                                    <option value="">Select country</option>
                                    <?php foreach ($countries as $country): ?>
                                        <option value="<?= (int) $country->id ?>" <?= $isEdit && (int)$supplier->country_id === (int)$country->id ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($country->name . ' (' . $country->code . ')') ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-3 control-label">Supplier Name</label>
                            <div class="col-sm-9">
                                <input type="text" name="name" class="form-control" required value="<?= $isEdit ? htmlspecialchars($supplier->name) : '' ?>">
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-3 control-label">Company</label>
                            <div class="col-sm-9">
                                <input type="text" name="company" class="form-control" value="<?= $isEdit ? htmlspecialchars($supplier->company) : '' ?>">
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-3 control-label">Email</label>
                            <div class="col-sm-9">
                                <input type="email" name="email" class="form-control" value="<?= $isEdit ? htmlspecialchars($supplier->email) : '' ?>">
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-3 control-label">Phone</label>
                            <div class="col-sm-9">
                                <input type="text" name="phone" class="form-control" value="<?= $isEdit ? htmlspecialchars($supplier->phone) : '' ?>">
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-3 control-label">Address</label>
                            <div class="col-sm-9">
                                <textarea name="address" class="form-control" rows="3"><?= $isEdit ? htmlspecialchars($supplier->address) : '' ?></textarea>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-3 control-label">Status</label>
                            <div class="col-sm-9">
                                <select name="status" class="form-control">
                                    <option value="1" <?= $isEdit && (int)$supplier->status === 1 ? 'selected' : '' ?>>Active</option>
                                    <option value="0" <?= $isEdit && (int)$supplier->status === 0 ? 'selected' : '' ?>>Inactive</option>
                                </select>
                            </div>
                        </div>
                        <div class="hr-line-dashed"></div>
                        <div class="form-group">
                            <div class="col-sm-9 col-sm-offset-3">
                                <a href="<?= base_url('admin/suppliers') ?>" class="btn btn-white">Cancel</a>
                                <button type="submit" class="btn btn-primary"><?= $isEdit ? 'Update' : 'Save' ?></button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
