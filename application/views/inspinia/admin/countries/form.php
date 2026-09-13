<?php $isEdit = !empty($country); ?>
<div class="row wrapper border-bottom white-bg page-heading">
    <div class="col-lg-10">
        <h2><?= $isEdit ? 'Edit Country' : 'Add Country' ?></h2>
        <ol class="breadcrumb">
            <li><a href="<?= base_url('admin/admin') ?>">Dashboard</a></li>
            <li><a href="<?= base_url('admin/countries') ?>">Countries</a></li>
            <li class="active"><strong><?= $isEdit ? 'Edit' : 'Add' ?></strong></li>
        </ol>
    </div>
</div>
<div class="wrapper wrapper-content animated fadeInRight">
    <div class="row">
        <div class="col-lg-8">
            <div class="ibox float-e-margins">
                <div class="ibox-title"><h5>Country Details</h5></div>
                <div class="ibox-content">
                    <?php $this->load->view('flash'); ?>
                    <form method="post" action="<?= base_url('admin/countries/save' . ($isEdit ? '/' . $country->id : '')) ?>" class="form-horizontal">
                        <div class="form-group">
                            <label class="col-sm-3 control-label">Country Name</label>
                            <div class="col-sm-9">
                                <input type="text" name="name" class="form-control" required value="<?= $isEdit ? htmlspecialchars($country->name) : '' ?>">
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-3 control-label">Code</label>
                            <div class="col-sm-9">
                                <input type="text" name="code" class="form-control" maxlength="10" required value="<?= $isEdit ? htmlspecialchars($country->code) : '' ?>" placeholder="PK">
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-3 control-label">ISO3</label>
                            <div class="col-sm-9">
                                <input type="text" name="iso3" class="form-control" maxlength="10" value="<?= $isEdit ? htmlspecialchars($country->iso3) : '' ?>" placeholder="PAK">
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-3 control-label">Phone Code</label>
                            <div class="col-sm-9">
                                <input type="text" name="phone_code" class="form-control" value="<?= $isEdit ? htmlspecialchars($country->phone_code) : '' ?>" placeholder="+92">
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-3 control-label">Currency</label>
                            <div class="col-sm-9">
                                <input type="text" name="currency" class="form-control" value="<?= $isEdit ? htmlspecialchars($country->currency) : '' ?>" placeholder="PKR">
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-3 control-label">Status</label>
                            <div class="col-sm-9">
                                <select name="status" class="form-control">
                                    <option value="1" <?= $isEdit && (int)$country->status === 1 ? 'selected' : '' ?>>Active</option>
                                    <option value="0" <?= $isEdit && (int)$country->status === 0 ? 'selected' : '' ?>>Inactive</option>
                                </select>
                            </div>
                        </div>
                        <div class="hr-line-dashed"></div>
                        <div class="form-group">
                            <div class="col-sm-9 col-sm-offset-3">
                                <a href="<?= base_url('admin/countries') ?>" class="btn btn-white">Cancel</a>
                                <button type="submit" class="btn btn-primary"><?= $isEdit ? 'Update' : 'Save' ?></button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
