<div class="row wrapper border-bottom white-bg page-heading">
    <div class="col-lg-10">
        <h2><?= htmlspecialchars($theme->name) ?> Settings</h2>
        <ol class="breadcrumb">
            <li><a href="<?= base_url('admin/admin') ?>">Dashboard</a></li>
            <li><a href="<?= base_url('admin/themes') ?>">Themes</a></li>
            <li class="active"><strong>Settings</strong></li>
        </ol>
    </div>
</div>
<div class="wrapper wrapper-content animated fadeInRight">
    <?php $this->load->view('flash'); ?>
    <div class="row">
        <div class="col-lg-7">
            <div class="ibox">
                <div class="ibox-title"><h5>Existing Fields</h5></div>
                <div class="ibox-content">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Label</th>
                                <th>Key</th>
                                <th>Type</th>
                                <th>Required</th>
                                <th>Default</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php if (empty($fields)): ?>
                            <tr><td colspan="6" class="text-center">No settings yet. Add fields from the form.</td></tr>
                        <?php endif; ?>
                        <?php foreach ($fields as $field): ?>
                            <tr>
                                <td><?= htmlspecialchars($field->field_label) ?></td>
                                <td><code><?= htmlspecialchars($field->field_key) ?></code></td>
                                <td><?= htmlspecialchars($field->field_type) ?></td>
                                <td><?= $field->is_required ? 'Yes' : 'No' ?></td>
                                <td><?= htmlspecialchars($field->default_value) ?></td>
                                <td>
                                    <a class="btn btn-xs btn-danger" href="<?= base_url('admin/themes/delete_field/' . $theme->id . '/' . $field->id) ?>" onclick="return confirm('Delete this field?');">Delete</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="col-lg-5">
            <div class="ibox">
                <div class="ibox-title"><h5>Add Setting Field</h5></div>
                <div class="ibox-content">
                    <form method="post" action="<?= base_url('admin/themes/save_field/' . $theme->id) ?>" class="form-horizontal">
                        <div class="form-group">
                            <label class="col-sm-4 control-label">Label</label>
                            <div class="col-sm-8"><input type="text" name="field_label" class="form-control" required placeholder="Primary Color"></div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-4 control-label">Key</label>
                            <div class="col-sm-8"><input type="text" name="field_key" class="form-control" required placeholder="primary_color"></div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-4 control-label">Type</label>
                            <div class="col-sm-8">
                                <select name="field_type" class="form-control">
                                    <?php foreach ($field_types as $type => $label): ?>
                                        <option value="<?= $type ?>"><?= $label ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-4 control-label">Default</label>
                            <div class="col-sm-8"><input type="text" name="default_value" class="form-control"></div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-4 control-label">Sort</label>
                            <div class="col-sm-8"><input type="number" name="sort_order" class="form-control" value="0"></div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-4 control-label">Required</label>
                            <div class="col-sm-8">
                                <label class="checkbox-inline"><input type="checkbox" name="is_required" value="1"> Required when activating on a store</label>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="col-sm-8 col-sm-offset-4">
                                <button type="submit" class="btn btn-primary">Add Field</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
