<div class="row wrapper border-bottom white-bg page-heading">
    <div class="col-lg-8">
        <h2>Categories</h2>
        <ol class="breadcrumb">
            <li><a href="<?= base_url('admin/admin') ?>">Dashboard</a></li>
            <li class="active"><strong>Categories</strong></li>
        </ol>
    </div>
    <div class="col-lg-4 text-right" style="margin-top:26px;">
        <button type="button" class="btn btn-success" data-toggle="modal" data-target="#importCategoriesModal">
            <i class="fa fa-upload"></i> Import Categories
        </button>
        <a href="<?= base_url('admin/categories/form' . ($country_id ? '?country_id=' . (int) $country_id : '')) ?>" class="btn btn-primary">Add Category</a>
    </div>
</div>
<div class="wrapper wrapper-content">
    <?php $this->load->view('flash'); ?>
    <div class="ibox">
        <div class="ibox-title"><h5>Filters</h5></div>
        <div class="ibox-content">
            <?php
            $filterCountryId = isset($country_id) ? (int) $country_id : 0;
            $filterParentId = isset($parent_id) ? (int) $parent_id : null;
            $filterQ = isset($q) ? $q : '';
            $filterParents = isset($parents) ? $parents : array();
            ?>
            <form method="get" action="<?= base_url('admin/categories') ?>" class="form-inline" id="categoryFilterForm">
                <div class="form-group" style="margin-right:8px; margin-bottom:8px;">
                    <input type="text" name="q" class="form-control" placeholder="Search name, slug, parent…" value="<?= htmlspecialchars($filterQ) ?>" style="min-width:240px;">
                </div>
                <div class="form-group" style="margin-right:8px; margin-bottom:8px;">
                    <select name="country_id" id="categoryFilterCountry" class="form-control">
                        <option value="0">All countries</option>
                        <?php foreach ($countries as $c): ?>
                            <option value="<?= (int) $c->id ?>" <?= $filterCountryId === (int) $c->id ? 'selected' : '' ?>><?= htmlspecialchars($c->name) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group" style="margin-right:8px; margin-bottom:8px;">
                    <select name="parent_id" id="categoryFilterParent" class="form-control">
                        <option value="">All parents</option>
                        <option value="0" <?= $filterParentId === 0 ? 'selected' : '' ?>>Top-level only</option>
                        <?php foreach ($filterParents as $parent): ?>
                            <?php
                            $parentLabel = $parent->name;
                            if (!$filterCountryId && !empty($parent->country_name)) {
                                $parentLabel = $parent->country_name . ' — ' . $parentLabel;
                            }
                            ?>
                            <option value="<?= (int) $parent->id ?>" <?= $filterParentId === (int) $parent->id ? 'selected' : '' ?>><?= htmlspecialchars($parentLabel) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <button type="submit" class="btn btn-primary" style="margin-bottom:8px;">Filter</button>
                <a href="<?= base_url('admin/categories') ?>" class="btn btn-white" style="margin-bottom:8px;">Reset</a>
            </form>
        </div>
    </div>
    <div class="ibox">
        <div class="ibox-title"><h5>Country categories</h5></div>
        <div class="ibox-content">
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Name</th>
                            <th>Local name</th>
                            <th>Country</th>
                            <th>Parent</th>
                            <th>Slug</th>
                            <th>Status</th>
                            <th width="140">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if (empty($categories)): ?>
                        <tr><td colspan="8" class="text-center"><?= ($filterQ !== '' || $filterParentId !== null || $filterCountryId) ? 'No categories match these filters.' : 'No categories yet.' ?></td></tr>
                    <?php endif; ?>
                    <?php foreach ($categories as $cat): ?>
                        <tr>
                            <td><?= (int) $cat->id ?></td>
                            <td><?= htmlspecialchars(($cat->icon ? $cat->icon . ' ' : '') . $cat->name) ?></td>
                            <td><?= htmlspecialchars(!empty($cat->local_name) ? $cat->local_name : '—') ?></td>
                            <td><?= htmlspecialchars($cat->country_name ?: '-') ?></td>
                            <td><?= htmlspecialchars($cat->parent_name ?: '—') ?></td>
                            <td><?= htmlspecialchars($cat->slug) ?></td>
                            <td><?= ((int) $cat->status === 1) ? 'Active' : 'Inactive' ?></td>
                            <td>
                                <a class="btn btn-xs btn-info" href="<?= base_url('admin/categories/form/' . $cat->id) ?>">Edit</a>
                                <a class="btn btn-xs btn-danger" href="<?= base_url('admin/categories/delete/' . $cat->id) ?>" onclick="return confirm('Delete category?');">Delete</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="modal inmodal fade" id="importCategoriesModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <form id="importCategoriesForm">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                    <h4 class="modal-title">Import Categories</h4>
                    <small class="text-muted">Select the country first, then upload a CSV. The selected country is applied to every category.</small>
                </div>
                <div class="modal-body">
                    <div class="form-horizontal">
                        <div class="form-group">
                            <label class="col-sm-3 control-label">Country</label>
                            <div class="col-sm-8">
                                <select name="country_id" id="importCountry" class="form-control" required>
                                    <option value="">Select country</option>
                                    <?php foreach ($countries as $c): ?>
                                        <option value="<?= (int) $c->id ?>" <?= $filterCountryId === (int) $c->id ? 'selected' : '' ?>><?= htmlspecialchars($c->name) ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <span class="help-block">Required. This country is saved on every imported category. Do not put country_id in the CSV.</span>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-3 control-label">CSV file</label>
                            <div class="col-sm-8">
                                <input type="file" name="csv" id="importCsv" class="form-control" accept=".csv,text/csv">
                                <p style="margin:8px 0 0;">
                                    <a href="<?= base_url('admin/categories/csv_template') ?>" class="btn btn-white btn-sm" download="category_import_template.csv">
                                        <i class="fa fa-download"></i> Download sample CSV
                                    </a>
                                </p>
                                <span class="help-block">
                                    Required columns: <code>parent</code>, <code>name</code>, <code>slug</code>, <code>icon</code>, <code>category_image_url</code>, <code>default_hero_image_url</code>, <code>hero_kicker</code>, <code>hero_title</code>, <code>hero_text</code>.
                                    Optional: <code>local_name</code> (storefront language). The parent column is the parent category name, not an ID. Do not include a country column.
                                </span>
                            </div>
                        </div>
                    </div>
                    <pre class="well well-sm" style="margin-bottom:15px; white-space:pre-wrap;">parent,name,local_name,slug,icon,category_image_url,default_hero_image_url,hero_kicker,hero_title,hero_text
Electronics &amp; Tech,Phone Accessories,Mobilaccessoarer,phone-accessories,,,,Phone,Phone Accessories,Cases and chargers
Halloween,Halloween Decorations,Halloween dekorationer,halloween-decorations,,,,Decor,Halloween Decorations,Party décor</pre>
                    <div id="importAlert" class="alert" style="display:none;"></div>
                    <div id="importSummary" class="m-b-sm" style="display:none;"></div>
                    <div id="importPreviewWrap" style="display:none;">
                        <div class="table-responsive" style="max-height:320px; overflow:auto;">
                            <table class="table table-striped table-condensed" id="importPreviewTable">
                                <thead>
                                    <tr>
                                        <th>Row</th>
                                        <th>Country</th>
                                        <th>Parent</th>
                                        <th>Name</th>
                                        <th>Slug</th>
                                        <th>Status</th>
                                        <th>Message</th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-white" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="importSubmitBtn" disabled>Import</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
(function ($) {
    $('#categoryFilterCountry').on('change', function () {
        $('#categoryFilterParent').val('');
        $('#categoryFilterForm').submit();
    });
    $('#categoryFilterParent').on('change', function () {
        $('#categoryFilterForm').submit();
    });

    var previewUrl = <?= json_encode(base_url('admin/categories/import_preview')) ?>;
    var saveUrl = <?= json_encode(base_url('admin/categories/import_save')) ?>;
    var listUrl = <?= json_encode(base_url('admin/categories')) ?>;
    var $alert = $('#importAlert');
    var $summary = $('#importSummary');
    var $wrap = $('#importPreviewWrap');
    var $tbody = $('#importPreviewTable tbody');
    var $submit = $('#importSubmitBtn');
    var previewTimer = null;

    function setAlert(type, message) {
        if (!message) {
            $alert.hide().text('');
            return;
        }
        $alert.removeClass('alert-danger alert-success alert-warning alert-info')
            .addClass('alert-' + type)
            .html(message)
            .show();
    }

    function statusLabel(status) {
        if (status === 'import') return '<span class="label label-primary">Import</span>';
        if (status === 'duplicate') return '<span class="label label-warning">Duplicate</span>';
        return '<span class="label label-danger">Failed</span>';
    }

    function renderPreview(res) {
        $tbody.empty();
        var rows = res.rows || [];
        $.each(rows, function (_, row) {
            var tr = $('<tr/>');
            if (row.status === 'fail') tr.addClass('danger');
            else if (row.status === 'duplicate') tr.addClass('warning');
            tr.append($('<td/>').text(row.line || ''));
            tr.append($('<td/>').text($.trim($('#importCountry option:selected').text()) || row.country_id || '—'));
            tr.append($('<td/>').text(row.parent || '—'));
            tr.append($('<td/>').text(row.name || ''));
            tr.append($('<td/>').text(row.slug || ''));
            tr.append($('<td/>').html(statusLabel(row.status)));
            tr.append($('<td/>').text(row.message || ''));
            $tbody.append(tr);
        });
        $wrap.toggle(rows.length > 0);
        $summary.html(
            '<strong>Imported:</strong> ' + (res.imported || 0) +
            ' &nbsp; <strong>Skipped/Duplicate:</strong> ' + (res.skipped || 0) +
            ' &nbsp; <strong>Failed:</strong> ' + (res.failed || 0)
        ).show();
        $submit.prop('disabled', !(res.imported > 0));
    }

    function previewCsv() {
        var file = $('#importCsv')[0].files[0];
        var countryId = $('#importCountry').val();
        $submit.prop('disabled', true);
        $wrap.hide();
        $summary.hide();
        if (!file) {
            setAlert('', '');
            return;
        }
        if (!countryId) {
            setAlert('danger', 'Please select a country before uploading the CSV.');
            return;
        }
        var data = new FormData();
        data.append('csv', file);
        data.append('country_id', countryId);
        setAlert('info', 'Reading and validating CSV…');
        $.ajax({
            url: previewUrl,
            method: 'POST',
            data: data,
            processData: false,
            contentType: false,
            dataType: 'json'
        }).done(function (res) {
            if (!res || !res.ok) {
                setAlert('danger', (res && res.error) ? res.error : 'Could not validate the CSV file.');
                renderPreview({ rows: [], imported: 0, skipped: 0, failed: 0 });
                return;
            }
            var type = res.failed > 0 ? 'warning' : 'success';
            setAlert(type, res.failed > 0
                ? 'CSV validated. Fix failed rows or import the valid categories.'
                : 'CSV validated. Review the preview, then click Import.');
            renderPreview(res);
        }).fail(function (xhr) {
            var res = xhr.responseJSON || {};
            setAlert('danger', res.error || 'Could not validate the CSV file.');
            $wrap.hide();
            $summary.hide();
        });
    }

    $('#importCsv').on('change', function () {
        clearTimeout(previewTimer);
        previewTimer = setTimeout(previewCsv, 150);
    });
    $('#importCountry').on('change', function () {
        if ($('#importCsv')[0].files[0]) {
            clearTimeout(previewTimer);
            previewTimer = setTimeout(previewCsv, 150);
        }
    });

    $('#importCategoriesModal').on('hidden.bs.modal', function () {
        $('#importCategoriesForm')[0].reset();
        $submit.prop('disabled', true);
        $wrap.hide();
        $summary.hide();
        setAlert('', '');
        $tbody.empty();
    });

    $('#importCategoriesForm').on('submit', function (e) {
        e.preventDefault();
        var file = $('#importCsv')[0].files[0];
        var countryId = $('#importCountry').val();
        if (!file || $submit.prop('disabled')) {
            return;
        }
        if (!countryId) {
            setAlert('danger', 'Please select a country before importing.');
            return;
        }
        var data = new FormData();
        data.append('csv', file);
        data.append('country_id', countryId);
        $submit.prop('disabled', true).text('Importing…');
        $.ajax({
            url: saveUrl,
            method: 'POST',
            data: data,
            processData: false,
            contentType: false,
            dataType: 'json'
        }).done(function (res) {
            if (!res || !res.ok) {
                setAlert('danger', (res && res.error) ? res.error : 'Import failed.');
                $submit.prop('disabled', false).text('Import');
                return;
            }
            setAlert('success', res.summary || 'Import complete.');
            renderPreview(res);
            setTimeout(function () {
                var url = listUrl;
                if (res.country_id) {
                    url += '?country_id=' + encodeURIComponent(res.country_id);
                }
                window.location.href = url;
            }, 600);
        }).fail(function (xhr) {
            var res = xhr.responseJSON || {};
            setAlert('danger', res.error || 'Import failed and was rolled back.');
            $submit.prop('disabled', false).text('Import');
        });
    });
})(jQuery);
</script>
