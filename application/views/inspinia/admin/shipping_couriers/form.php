<?php
$isEdit = !empty($courier);
$selectedCountry = $isEdit ? (int) $courier->country_id : (int) (isset($preselect_country_id) ? $preselect_country_id : 0);
?>
<div class="row wrapper border-bottom white-bg page-heading">
    <div class="col-lg-10">
        <h2><?= $isEdit ? 'Edit Courier' : 'Add Courier' ?></h2>
        <ol class="breadcrumb">
            <li><a href="<?= base_url('admin/admin') ?>">Dashboard</a></li>
            <li><a href="<?= base_url('admin/couriers') ?>">Couriers</a></li>
            <li class="active"><strong><?= $isEdit ? 'Edit' : 'Add' ?></strong></li>
        </ol>
    </div>
</div>
<div class="wrapper wrapper-content animated fadeInRight">
    <div class="row">
        <div class="col-lg-8">
            <div class="ibox float-e-margins">
                <div class="ibox-title"><h5>Courier details</h5></div>
                <div class="ibox-content">
                    <?php $this->load->view('flash'); ?>
                    <form method="post" action="<?= base_url('admin/couriers/save' . ($isEdit ? '/' . $courier->id : '')) ?>" class="form-horizontal">
                        <div class="form-group">
                            <label class="col-sm-3 control-label">Country</label>
                            <div class="col-sm-9">
                                <select name="country_id" class="form-control" required>
                                    <option value="">Select country</option>
                                    <?php foreach ($countries as $country): ?>
                                        <option value="<?= (int) $country->id ?>" <?= $selectedCountry === (int) $country->id ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($country->name) ?> (<?= htmlspecialchars($country->code) ?>)
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-3 control-label">Courier name</label>
                            <div class="col-sm-9">
                                <input type="text" name="name" class="form-control" required maxlength="120" value="<?= $isEdit ? htmlspecialchars($courier->name) : '' ?>" placeholder="PostNord">
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-3 control-label">Tracking URL</label>
                            <div class="col-sm-9">
                                <input type="text" name="tracking_url" class="form-control" maxlength="255" value="<?= $isEdit ? htmlspecialchars($courier->tracking_url) : '' ?>" placeholder="https://example.com/track/{tracking}">
                                <span class="help-block">Optional. Use {tracking} where the tracking number should go.</span>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-3 control-label">Sort</label>
                            <div class="col-sm-9">
                                <input type="number" name="sort_order" class="form-control" value="<?= $isEdit ? (int) $courier->sort_order : 0 ?>">
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-3 control-label">Status</label>
                            <div class="col-sm-9">
                                <select name="status" class="form-control">
                                    <option value="1" <?= !$isEdit || (int) $courier->status === 1 ? 'selected' : '' ?>>Active</option>
                                    <option value="0" <?= $isEdit && (int) $courier->status === 0 ? 'selected' : '' ?>>Inactive</option>
                                </select>
                            </div>
                        </div>
                        <div class="hr-line-dashed"></div>
                        <div class="form-group">
                            <div class="col-sm-9 col-sm-offset-3">
                                <a href="<?= base_url('admin/couriers') ?>" class="btn btn-white">Cancel</a>
                                <button type="submit" class="btn btn-primary"><?= $isEdit ? 'Update' : 'Save' ?></button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
