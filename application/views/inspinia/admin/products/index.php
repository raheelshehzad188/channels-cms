<div class="row wrapper border-bottom white-bg page-heading">
    <div class="col-lg-8">
        <h2>Products</h2>
        <ol class="breadcrumb">
            <?php if (ec_is_admin()): ?>
            <li><a href="<?= base_url('admin/admin') ?>">Dashboard</a></li>
            <?php endif; ?>
            <li class="active"><strong>Products</strong></li>
        </ol>
    </div>
    <div class="col-lg-4 text-right" style="margin-top:26px;">
        <button type="button" class="btn btn-success" id="openCsvImportBtn">Import Products</button>
        <a href="<?= base_url('admin/products/import') ?>" class="btn btn-white">Import from URL</a>
        <a href="<?= base_url('admin/products/form') ?>" class="btn btn-primary">Add Product</a>
    </div>
</div>
<div class="wrapper wrapper-content animated fadeInRight">
    <div class="row" id="csvImportSection" style="display:none;">
        <div class="col-lg-12">
            <div class="ibox float-e-margins">
                <div class="ibox-title">
                    <h5>Import Products</h5>
                    <div class="ibox-tools">
                        <a class="close-link" id="closeCsvImportBtn" href="#"><i class="fa fa-times"></i></a>
                    </div>
                </div>
                <div class="ibox-content">
                    <p class="text-muted">Select the country first, then upload a CSV. The selected country is applied to every product. The CSV must not include <code>country_id</code>.</p>
                    <form id="csvImportForm" class="form-horizontal">
                        <div class="form-group">
                            <label class="col-sm-2 control-label">Country</label>
                            <div class="col-sm-6">
                                <select name="country_id" id="csvImportCountry" class="form-control" required>
                                    <option value="">Select country</option>
                                    <?php foreach (!empty($countries) ? $countries : array() as $country): ?>
                                        <option value="<?= (int) $country->id ?>"><?= htmlspecialchars($country->name) ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <span class="help-block">Required. This country ID is saved on every imported product.</span>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-2 control-label">CSV file</label>
                            <div class="col-sm-6">
                                <input type="file" name="csv" id="csvImportFile" class="form-control" accept=".csv,text/csv">
                                <span class="help-block">
                                    Required columns: <code>product</code>, <code>link</code>, <code>category</code>, <code>sub_category</code>, <code>price</code>, <code>delivery_min_days</code>, <code>delivery_max_days</code>.
                                    <a href="<?= base_url('admin/products/csv_template') ?>">Download CSV template</a>
                                </span>
                            </div>
                        </div>
                    </form>
                    <pre class="well well-sm" style="white-space:pre-wrap;">product,link,category,sub_category,price,delivery_min_days,delivery_max_days
Wireless earbuds,https://www.amazon.co.uk/dp/B0EXAMPLE,Electronics,Audio,19.99,3,7</pre>
                    <div id="csvImportAlert" class="alert" style="display:none;"></div>
                    <div id="csvImportSummary" class="m-b-sm" style="display:none;"></div>
                    <div id="csvImportPreviewWrap" style="display:none;">
                        <div class="table-responsive" style="max-height:280px; overflow:auto;">
                            <table class="table table-striped table-condensed" id="csvImportPreviewTable">
                                <thead>
                                    <tr>
                                        <th>Row</th>
                                        <th>Product</th>
                                        <th>Link</th>
                                        <th>Category</th>
                                        <th>Price</th>
                                        <th>Delivery</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>
                    </div>
                    <div id="csvImportProgressWrap" style="display:none; margin-top:15px;">
                        <p id="csvImportProgressLabel" style="font-weight:600;">Importing product 0 of 0</p>
                        <div class="progress progress-striped active">
                            <div class="progress-bar progress-bar-success" id="csvImportProgressBar" style="width:0%;">0%</div>
                        </div>
                        <p>
                            <strong>Imported:</strong> <span id="csvStatImported">0</span>
                            &nbsp; <strong>Failed:</strong> <span id="csvStatFailed">0</span>
                            &nbsp; <strong>Skipped:</strong> <span id="csvStatSkipped">0</span>
                        </p>
                    </div>
                    <div id="csvImportFailedWrap" style="display:none; margin-top:10px;">
                        <h4>Failed products</h4>
                        <div class="table-responsive" style="max-height:240px; overflow:auto;">
                            <table class="table table-bordered table-condensed" id="csvImportFailedTable">
                                <thead>
                                    <tr>
                                        <th>Row</th>
                                        <th>Product / link</th>
                                        <th>Error</th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>
                    </div>
                    <div class="text-right" style="margin-top:15px;">
                        <button type="button" class="btn btn-white" id="csvImportCancelBtn">Cancel</button>
                        <button type="button" class="btn btn-primary" id="csvImportStartBtn" disabled>Start Import</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-lg-12">
            <div class="ibox float-e-margins">
                <div class="ibox-title"><h5>Product Catalog</h5></div>
                <div class="ibox-content">
                    <?php $this->load->view('flash'); ?>
                    <div class="table-responsive">
                        <table class="table table-striped table-bordered">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Name</th>
                                    <th>SKU</th>
                                    <th>Price</th>
                                    <th>Stock</th>
                                    <th>Supplier</th>
                                    <th>Country</th>
                                    <th>Status</th>
                                    <th width="200">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php if (empty($products)): ?>
                                <tr><td colspan="9" class="text-center">No products found.</td></tr>
                            <?php endif; ?>
                            <?php foreach ($products as $product): ?>
                                <tr>
                                    <td><?= (int) $product->id ?></td>
                                    <td><?= htmlspecialchars($product->name) ?></td>
                                    <td><?= htmlspecialchars($product->sku) ?></td>
                                    <td><?= format_money((float) $product->price, product_currency($product)) ?></td>
                                    <td><?= (int) $product->stock ?></td>
                                    <td><?= htmlspecialchars($product->supplier_name ?: '-') ?></td>
                                    <td><?= htmlspecialchars($product->country_name ?: '-') ?></td>
                                    <td>
                                        <span class="label label-<?= ((int)$product->status === 1) ? 'primary' : 'default' ?>">
                                            <?= ec_status_label($product->status) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <button type="button" class="btn btn-xs btn-white js-view-theme" data-product-id="<?= (int) $product->id ?>" data-product-name="<?= htmlspecialchars($product->name, ENT_QUOTES) ?>">View</button>
                                        <a class="btn btn-xs btn-info" href="<?= base_url('admin/products/form/' . $product->id) ?>">Edit</a>
                                        <?php if (ec_is_admin() || (ec_is_ecommerce() && (int) $product->created_by === (int) ec_user()->UserID)): ?>
                                        <a class="btn btn-xs btn-danger" href="<?= base_url('admin/products/delete/' . $product->id) ?>" onclick="return confirm('Delete this product?');">Delete</a>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal inmodal fade" id="themePreviewModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                <h4 class="modal-title">Select a theme</h4>
                <small class="text-muted" id="themePreviewProductName"></small>
            </div>
            <div class="modal-body">
                <div class="row">
                    <?php if (empty($themes)): ?>
                        <div class="col-lg-12">No themes found.</div>
                    <?php endif; ?>
                    <?php foreach ($themes as $theme): ?>
                    <div class="col-sm-6">
                        <a href="#" class="js-open-theme-preview" data-theme-slug="<?= htmlspecialchars($theme->slug) ?>" target="_blank" rel="noopener" style="display:block; color:inherit; text-decoration:none;">
                            <div class="ibox">
                                <div class="ibox-content product-box">
                                    <div class="product-imitation" style="padding:0; min-height:160px;">
                                        <img src="<?= theme_screenshot($theme->slug) ?>" alt="<?= htmlspecialchars($theme->name) ?>" style="width:100%; height:160px; object-fit:cover;">
                                    </div>
                                    <div class="product-desc">
                                        <span class="product-price"><?= htmlspecialchars($theme->slug) ?></span>
                                        <div class="product-name"><?= htmlspecialchars($theme->name) ?></div>
                                        <div class="small m-t-xs"><?= htmlspecialchars($theme->description) ?></div>
                                    </div>
                                </div>
                            </div>
                        </a>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
(function ($) {
    var previewBase = <?= json_encode(base_url('admin/products/preview/')) ?>;
    var csvPreviewUrl = <?= json_encode(base_url('admin/products/csv_preview')) ?>;
    var csvRowUrl = <?= json_encode(base_url('admin/products/csv_import_row')) ?>;
    var selectedProductId = 0;
    var importState = {
        token: '',
        total: 0,
        index: 0,
        imported: 0,
        failed: 0,
        skipped: 0,
        running: false
    };
    var previewTimer = null;

    $(document).on('click', '.js-view-theme', function () {
        selectedProductId = $(this).data('product-id');
        $('#themePreviewProductName').text($(this).data('product-name') || '');
        $('#themePreviewModal').modal('show');
    });

    $(document).on('click', '.js-open-theme-preview', function () {
        if (!selectedProductId) {
            return false;
        }
        this.href = previewBase + selectedProductId + '/' + $(this).data('theme-slug');
        $('#themePreviewModal').modal('hide');
    });

    function csvAlert(type, message) {
        var $alert = $('#csvImportAlert');
        if (!message) {
            $alert.hide().text('');
            return;
        }
        $alert.removeClass('alert-danger alert-success alert-warning alert-info')
            .addClass('alert-' + type)
            .html(message)
            .show();
    }

    function resetCsvImport(keepOpen) {
        importState = { token: '', total: 0, index: 0, imported: 0, failed: 0, skipped: 0, running: false };
        $('#csvImportForm')[0].reset();
        $('#csvImportStartBtn').prop('disabled', true).text('Start Import');
        $('#csvImportCountry, #csvImportFile').prop('disabled', false);
        $('#csvImportPreviewWrap, #csvImportSummary, #csvImportProgressWrap, #csvImportFailedWrap').hide();
        $('#csvImportPreviewTable tbody, #csvImportFailedTable tbody').empty();
        csvAlert('', '');
        if (!keepOpen) {
            $('#csvImportSection').hide();
        }
    }

    function renderPreview(res) {
        var $tbody = $('#csvImportPreviewTable tbody').empty();
        var rows = res.rows || [];
        $.each(rows, function (_, row) {
            var tr = $('<tr/>');
            if (!row.valid) {
                tr.addClass('danger');
            }
            tr.append($('<td/>').text(row.line || ''));
            tr.append($('<td/>').text(row.product || ''));
            tr.append($('<td/>').text(row.link || ''));
            tr.append($('<td/>').text((row.category || '') + (row.sub_category ? ' / ' + row.sub_category : '')));
            tr.append($('<td/>').text(row.price !== undefined && row.price !== '' ? row.price : ''));
            tr.append($('<td/>').text((row.delivery_min_days || '0') + '–' + (row.delivery_max_days || '0') + ' days'));
            tr.append($('<td/>').html(row.valid
                ? '<span class="label label-primary">Ready</span>'
                : '<span class="label label-danger">Invalid</span> ' + $('<span/>').text(row.error || '').html()
            ));
            $tbody.append(tr);
        });
        $('#csvImportPreviewWrap').toggle(rows.length > 0);
        $('#csvImportSummary').html(
            '<strong>Country:</strong> ' + $('<span/>').text(res.country_name || '').html() +
            ' &nbsp; <strong>Total products:</strong> ' + (res.total || 0) +
            ' &nbsp; <strong>Valid:</strong> ' + (res.valid || 0) +
            ' &nbsp; <strong>Invalid:</strong> ' + (res.invalid || 0)
        ).show();
        $('#csvImportStartBtn').prop('disabled', !(res.total > 0));
    }

    function previewCsv() {
        if (importState.running) {
            return;
        }
        var countryId = $('#csvImportCountry').val();
        var file = $('#csvImportFile')[0].files[0];
        $('#csvImportStartBtn').prop('disabled', true);
        importState.token = '';
        importState.total = 0;
        $('#csvImportPreviewWrap, #csvImportSummary, #csvImportProgressWrap, #csvImportFailedWrap').hide();
        $('#csvImportPreviewTable tbody, #csvImportFailedTable tbody').empty();
        if (!file) {
            csvAlert('', '');
            return;
        }
        if (!countryId) {
            csvAlert('danger', 'Select a country before uploading the CSV.');
            return;
        }
        var data = new FormData();
        data.append('country_id', countryId);
        data.append('csv', file);
        csvAlert('info', 'Validating CSV…');
        $.ajax({
            url: csvPreviewUrl,
            method: 'POST',
            data: data,
            processData: false,
            contentType: false,
            dataType: 'json'
        }).done(function (res) {
            if (!res || !res.ok) {
                csvAlert('danger', (res && res.error) ? res.error : 'Could not validate the CSV file.');
                return;
            }
            importState.token = res.token;
            importState.total = res.total || 0;
            var type = res.invalid > 0 ? 'warning' : 'success';
            csvAlert(type, res.invalid > 0
                ? 'CSV validated. Invalid rows will be recorded as failed. Review the preview, then click Start Import.'
                : 'CSV validated. ' + res.total + ' products ready for country ' + res.country_name + '. Review the preview, then click Start Import.');
            renderPreview(res);
        }).fail(function (xhr) {
            var res = xhr.responseJSON || {};
            csvAlert('danger', res.error || 'Could not validate the CSV file.');
        });
    }

    function setProgress(current, total) {
        var pct = total > 0 ? Math.round((current / total) * 100) : 0;
        $('#csvImportProgressLabel').text('Importing product ' + current + ' of ' + total);
        $('#csvImportProgressBar').css('width', pct + '%').text(pct + '%');
        $('#csvStatImported').text(importState.imported);
        $('#csvStatFailed').text(importState.failed);
        $('#csvStatSkipped').text(importState.skipped);
    }

    function addFailedRow(res) {
        var label = (res.product || '') + (res.link ? ' — ' + res.link : '');
        var tr = $('<tr class="danger"/>');
        tr.append($('<td/>').text(res.line || ''));
        tr.append($('<td/>').text(label));
        tr.append($('<td/>').text(res.error || 'Import failed.'));
        $('#csvImportFailedTable tbody').append(tr);
        $('#csvImportFailedWrap').show();
    }

    function finishImport() {
        importState.running = false;
        $('#csvImportProgressWrap .progress').removeClass('active');
        $('#csvImportCountry, #csvImportFile').prop('disabled', false);
        $('#csvImportStartBtn').prop('disabled', true).text('Start Import');
        $('#csvImportProgressLabel').text('Import complete');
        $('#csvImportCancelBtn').prop('disabled', false).text('Close / Refresh');
        csvAlert('success',
            '<strong>Total:</strong> ' + importState.total +
            ' &nbsp; <strong>Imported:</strong> ' + importState.imported +
            ' &nbsp; <strong>Skipped:</strong> ' + importState.skipped +
            ' &nbsp; <strong>Failed:</strong> ' + importState.failed +
            '<br>Reload the page to see new products in the catalog.'
        );
        $('#csvImportSummary').html(
            '<strong>Total:</strong> ' + importState.total +
            ' &nbsp; <strong>Imported:</strong> ' + importState.imported +
            ' &nbsp; <strong>Skipped:</strong> ' + importState.skipped +
            ' &nbsp; <strong>Failed:</strong> ' + importState.failed
        ).show();
    }

    function importNext() {
        if (!importState.running) {
            return;
        }
        if (importState.index >= importState.total) {
            finishImport();
            return;
        }
        var current = importState.index + 1;
        setProgress(current, importState.total);
        $.ajax({
            url: csvRowUrl,
            method: 'POST',
            data: { token: importState.token, index: importState.index },
            dataType: 'json',
            timeout: 120000
        }).done(function (res) {
            if (!res || !res.ok) {
                importState.failed++;
                addFailedRow({
                    line: res && res.line ? res.line : current,
                    product: res && res.product ? res.product : '',
                    link: res && res.link ? res.link : '',
                    error: (res && res.error) ? res.error : 'Import failed.'
                });
            } else if (res.status === 'imported') {
                importState.imported++;
            } else if (res.status === 'skipped') {
                importState.skipped++;
            } else {
                importState.failed++;
                addFailedRow(res);
            }
            $('#csvStatImported').text(importState.imported);
            $('#csvStatFailed').text(importState.failed);
            $('#csvStatSkipped').text(importState.skipped);
            importState.index++;
            importNext();
        }).fail(function (xhr) {
            var res = xhr.responseJSON || {};
            var error = res.error || 'Import request failed.';
            if (xhr.status === 400 && /expired/i.test(error)) {
                csvAlert('danger', error);
                importState.running = false;
                $('#csvImportCountry, #csvImportFile').prop('disabled', false);
                $('#csvImportStartBtn').prop('disabled', false).text('Start Import');
                $('#csvImportCancelBtn').prop('disabled', false);
                return;
            }
            importState.failed++;
            addFailedRow({
                line: res.line || current,
                product: res.product || '',
                link: res.link || '',
                error: error
            });
            $('#csvStatFailed').text(importState.failed);
            importState.index++;
            importNext();
        });
    }

    $('#openCsvImportBtn').on('click', function () {
        $('#csvImportSection').show();
        $('html, body').animate({ scrollTop: $('#csvImportSection').offset().top - 20 }, 200);
    });

    $('#closeCsvImportBtn, #csvImportCancelBtn').on('click', function (e) {
        e.preventDefault();
        if (importState.running) {
            return;
        }
        if ($('#csvImportCancelBtn').text().indexOf('Refresh') !== -1) {
            window.location.reload();
            return;
        }
        resetCsvImport(false);
    });

    $('#csvImportCountry, #csvImportFile').on('change', function () {
        clearTimeout(previewTimer);
        previewTimer = setTimeout(previewCsv, 150);
    });

    $('#csvImportStartBtn').on('click', function () {
        if (importState.running || !importState.token || !importState.total) {
            return;
        }
        importState.running = true;
        importState.index = 0;
        importState.imported = 0;
        importState.failed = 0;
        importState.skipped = 0;
        $('#csvImportCountry, #csvImportFile').prop('disabled', true);
        $('#csvImportStartBtn').prop('disabled', true).text('Importing…');
        $('#csvImportCancelBtn').prop('disabled', true);
        $('#csvImportProgressWrap').show();
        $('#csvImportFailedWrap').hide();
        $('#csvImportFailedTable tbody').empty();
        $('#csvImportProgressWrap .progress').addClass('active');
        csvAlert('info', 'Importing one product at a time. Do not close this page.');
        importNext();
    });
})(jQuery);
</script>
