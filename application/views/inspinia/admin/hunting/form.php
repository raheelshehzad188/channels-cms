<?php
$isEdit = !empty($item);
$creatives = ($isEdit && !empty($item->creatives)) ? $item->creatives : array();
$suppliers = ($isEdit && !empty($item->suppliers)) ? $item->suppliers : array();
if (empty($creatives)) {
    $creatives = array((object) array('link' => ''));
}
if (empty($suppliers)) {
    $suppliers = array((object) array('country_id' => '', 'link' => ''));
}
$countries = !empty($countries) ? $countries : array();
?>
<div class="row wrapper border-bottom white-bg page-heading">
    <div class="col-lg-10">
        <h2><?= $isEdit ? 'Edit Hunting Product' : 'Add Hunting Product' ?></h2>
        <ol class="breadcrumb">
            <li><a href="<?= base_url('admin/hunting') ?>">Product Hunting</a></li>
            <li class="active"><strong><?= $isEdit ? 'Edit' : 'Add' ?></strong></li>
        </ol>
    </div>
</div>
<div class="wrapper wrapper-content animated fadeInRight">
    <?php $this->load->view('flash'); ?>
    <form method="post" enctype="multipart/form-data" action="<?= base_url('admin/hunting/save' . ($isEdit ? '/' . $item->id : '')) ?>">
        <div class="ibox">
            <div class="ibox-title"><h5>Product</h5></div>
            <div class="ibox-content">
                <div class="form-horizontal">
                    <div class="form-group">
                        <label class="col-sm-2 control-label">Title</label>
                        <div class="col-sm-8">
                            <input type="text" name="title" class="form-control" required value="<?= $isEdit ? htmlspecialchars($item->title) : '' ?>">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-sm-2 control-label">Image</label>
                        <div class="col-sm-8">
                            <?php if ($isEdit && !empty($item->image)): ?>
                                <div style="margin-bottom:8px;">
                                    <img src="<?= base_url($item->image) ?>" alt="" style="max-width:160px;max-height:160px;border-radius:4px;">
                                </div>
                            <?php endif; ?>
                            <input type="file" name="image" class="form-control" accept="image/jpeg,image/png,image/gif,image/webp,image/avif,.jpg,.jpeg,.png,.gif,.webp,.avif" <?= $isEdit && !empty($item->image) ? '' : 'required' ?>>
                            <span class="help-block">JPG, PNG, GIF, WEBP or AVIF. No size limit.</span>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-sm-2 control-label">Notes</label>
                        <div class="col-sm-8">
                            <textarea name="notes" class="form-control" rows="3"><?= $isEdit ? htmlspecialchars((string) $item->notes) : '' ?></textarea>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-sm-2 control-label">Status</label>
                        <div class="col-sm-4">
                            <select name="status" class="form-control">
                                <option value="1" <?= !$isEdit || (int) $item->status === 1 ? 'selected' : '' ?>>Active</option>
                                <option value="0" <?= $isEdit && (int) $item->status === 0 ? 'selected' : '' ?>>Inactive</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="ibox">
            <div class="ibox-title">
                <h5>Creatives</h5>
                <div class="ibox-tools">
                    <button type="button" class="btn btn-xs btn-primary" id="addCreativeRow">Add link</button>
                </div>
            </div>
            <div class="ibox-content">
                <p class="text-muted">Add one or more creative / ad links.</p>
                <div id="creativeRows">
                    <?php foreach ($creatives as $creative): ?>
                        <div class="repeater-row" style="margin-bottom:8px;">
                            <div class="input-group">
                                <input type="text" name="creative_links[]" class="form-control" placeholder="https://…" value="<?= htmlspecialchars($creative->link) ?>">
                                <span class="input-group-btn">
                                    <button type="button" class="btn btn-danger remove-row"><i class="fa fa-times"></i></button>
                                </span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <div class="ibox">
            <div class="ibox-title">
                <h5>Country suppliers</h5>
                <div class="ibox-tools">
                    <button type="button" class="btn btn-xs btn-primary" id="addSupplierRow">Add country link</button>
                </div>
            </div>
            <div class="ibox-content">
                <p class="text-muted">Add a supplier link for each country.</p>
                <div id="supplierRows">
                    <?php foreach ($suppliers as $supplier): ?>
                        <div class="repeater-row" style="margin-bottom:8px;">
                            <div class="row">
                                <div class="col-sm-4">
                                    <select name="supplier_country_id[]" class="form-control">
                                        <option value="">Select country</option>
                                        <?php foreach ($countries as $country): ?>
                                            <option value="<?= (int) $country->id ?>" <?= (int) $supplier->country_id === (int) $country->id ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($country->name . ($country->code ? ' (' . $country->code . ')' : '')) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-sm-7">
                                    <input type="text" name="supplier_links[]" class="form-control" placeholder="https://supplier-link…" value="<?= htmlspecialchars($supplier->link) ?>">
                                </div>
                                <div class="col-sm-1">
                                    <button type="button" class="btn btn-danger btn-block remove-row"><i class="fa fa-times"></i></button>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <div class="ibox">
            <div class="ibox-content">
                <a href="<?= base_url('admin/hunting') ?>" class="btn btn-white">Cancel</a>
                <button type="submit" class="btn btn-primary"><?= $isEdit ? 'Update' : 'Save' ?></button>
            </div>
        </div>
    </form>
</div>

<script type="text/template" id="creativeRowTpl">
    <div class="repeater-row" style="margin-bottom:8px;">
        <div class="input-group">
            <input type="text" name="creative_links[]" class="form-control" placeholder="https://…">
            <span class="input-group-btn">
                <button type="button" class="btn btn-danger remove-row"><i class="fa fa-times"></i></button>
            </span>
        </div>
    </div>
</script>
<script type="text/template" id="supplierRowTpl">
    <div class="repeater-row" style="margin-bottom:8px;">
        <div class="row">
            <div class="col-sm-4">
                <select name="supplier_country_id[]" class="form-control">
                    <option value="">Select country</option>
                    <?php foreach ($countries as $country): ?>
                        <option value="<?= (int) $country->id ?>"><?= htmlspecialchars($country->name . ($country->code ? ' (' . $country->code . ')' : '')) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-sm-7">
                <input type="text" name="supplier_links[]" class="form-control" placeholder="https://supplier-link…">
            </div>
            <div class="col-sm-1">
                <button type="button" class="btn btn-danger btn-block remove-row"><i class="fa fa-times"></i></button>
            </div>
        </div>
    </div>
</script>
<script>
(function ($) {
    function addRow(wrap, tpl) {
        $(wrap).append($(tpl).html());
    }
    $('#addCreativeRow').on('click', function () {
        addRow('#creativeRows', '#creativeRowTpl');
    });
    $('#addSupplierRow').on('click', function () {
        addRow('#supplierRows', '#supplierRowTpl');
    });
    $(document).on('click', '.remove-row', function () {
        var $wrap = $(this).closest('#creativeRows, #supplierRows');
        var $row = $(this).closest('.repeater-row');
        if ($wrap.find('.repeater-row').length > 1) {
            $row.remove();
        } else {
            $row.find('input, select').val('');
        }
    });
})(jQuery);
</script>
