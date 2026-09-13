<div class="row wrapper border-bottom white-bg page-heading">
    <div class="col-lg-10">
        <h2><?= htmlspecialchars($store->name) ?> — <?= htmlspecialchars($theme->name) ?> Settings</h2>
        <ol class="breadcrumb">
            <li><a href="<?= base_url('admin/stores') ?>">Stores</a></li>
            <li class="active"><strong>Settings</strong></li>
        </ol>
    </div>
</div>
<div class="wrapper wrapper-content">
    <div class="ibox">
        <div class="ibox-title"><h5>Required fields activate this theme on <?= htmlspecialchars($store->domain) ?></h5></div>
        <div class="ibox-content">
            <?php $this->load->view('flash'); ?>
            <form method="post" enctype="multipart/form-data" action="<?= base_url('admin/stores/save_settings/' . $store->id) ?>" class="form-horizontal">
                <?php foreach ($fields as $field): ?>
                    <?php $current = isset($values[$field->field_key]) ? $values[$field->field_key] : $field->default_value; ?>
                    <div class="form-group">
                        <label class="col-sm-3 control-label">
                            <?= htmlspecialchars($field->field_label) ?>
                            <?= $field->is_required ? '<span class="text-danger">*</span>' : '' ?>
                        </label>
                        <div class="col-sm-7">
                            <?php if ($field->field_type === 'color'): ?>
                                <input type="color" name="<?= htmlspecialchars($field->field_key) ?>" class="form-control" value="<?= htmlspecialchars($current ?: '#000000') ?>" <?= $field->is_required ? 'required' : '' ?>>
                            <?php elseif ($field->field_type === 'textarea'): ?>
                                <textarea name="<?= htmlspecialchars($field->field_key) ?>" class="form-control" rows="3" <?= $field->is_required ? 'required' : '' ?>><?= htmlspecialchars($current) ?></textarea>
                            <?php elseif ($field->field_type === 'image'): ?>
                                <?php if ($current): ?>
                                    <p><img src="<?= base_url($current) ?>" alt="" style="max-height:70px;"></p>
                                <?php endif; ?>
                                <input type="file" name="<?= htmlspecialchars($field->field_key) ?>" class="form-control" <?= $field->is_required && !$current ? 'required' : '' ?>>
                            <?php else: ?>
                                <input type="text" name="<?= htmlspecialchars($field->field_key) ?>" class="form-control" value="<?= htmlspecialchars($current) ?>" <?= $field->is_required ? 'required' : '' ?>>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
                <div class="form-group">
                    <div class="col-sm-7 col-sm-offset-3">
                        <a href="<?= base_url('admin/stores/theme/' . $store->id) ?>" class="btn btn-white">Back</a>
                        <button type="submit" class="btn btn-primary">Save &amp; Activate Theme</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
