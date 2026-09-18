<?php
$prefs = isset($import_prefs) && is_array($import_prefs) ? $import_prefs : array();
$prefCountry = (int) (isset($prefs['country_id']) ? $prefs['country_id'] : 0);
$prefCategory = (int) (isset($prefs['category_id']) ? $prefs['category_id'] : 0);
$prefSubcategory = (int) (isset($prefs['subcategory_id']) ? $prefs['subcategory_id'] : 0);
$prefShipMin = isset($prefs['ship_min_days']) && (int) $prefs['ship_min_days'] > 0 ? (int) $prefs['ship_min_days'] : '';
$prefShipMax = isset($prefs['ship_max_days']) && (int) $prefs['ship_max_days'] > 0 ? (int) $prefs['ship_max_days'] : '';
$prefStock = array_key_exists('stock', $prefs) && $prefs['stock'] !== '' && $prefs['stock'] !== null ? (int) $prefs['stock'] : '';
$prefAutoAdd = !empty($prefs['auto_add_to_stores']);
?>
<div class="row wrapper border-bottom white-bg page-heading">
    <div class="col-lg-8">
        <h2>Import Product</h2>
        <ol class="breadcrumb">
            <li><a href="<?= base_url('admin/products') ?>">Products</a></li>
            <li class="active"><strong>Import</strong></li>
        </ol>
    </div>
</div>
<div class="wrapper wrapper-content animated fadeInRight">
    <div class="row">
        <div class="col-lg-8">
            <div class="ibox float-e-margins">
                <div class="ibox-title"><h5>Import from product URL</h5></div>
                <div class="ibox-content">
                    <?php $this->load->view('flash'); ?>
                    <p class="text-muted">Paste a product link. The country is chosen from the domain (ebay.com → United States) and remembered for the next import from that site. New products are saved as Inactive so ecommerce users can review the detail page; store owners and customers will not see them until you set them Active.</p>
                    <form method="post" action="<?= base_url('admin/products/import_save') ?>" class="form-horizontal" id="importForm">
                        <div class="form-group">
                            <label class="col-sm-3 control-label">Country</label>
                            <div class="col-sm-8">
                                <select name="country_id" id="importCountry" class="form-control">
                                    <option value="">Select country</option>
                                    <?php foreach ($countries as $country): ?>
                                        <option value="<?= (int) $country->id ?>" <?= $prefCountry === (int) $country->id ? 'selected' : '' ?>><?= htmlspecialchars($country->name) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-3 control-label">Category</label>
                            <div class="col-sm-8">
                                <select name="category_id" id="importCategory" class="form-control">
                                    <option value="">Select category</option>
                                </select>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-3 control-label">Sub category</label>
                            <div class="col-sm-8">
                                <select name="subcategory_id" id="importSubcategory" class="form-control">
                                    <option value="">Select sub category</option>
                                </select>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-3 control-label">Min shipping days</label>
                            <div class="col-sm-8">
                                <input type="number" min="0" step="1" name="ship_min_days" id="importShipMin" class="form-control" placeholder="e.g. 3" value="<?= htmlspecialchars((string) $prefShipMin) ?>">
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-3 control-label">Max shipping days</label>
                            <div class="col-sm-8">
                                <input type="number" min="0" step="1" name="ship_max_days" id="importShipMax" class="form-control" placeholder="e.g. 7" value="<?= htmlspecialchars((string) $prefShipMax) ?>">
                                <span class="help-block">Customers see a delivery date range calculated from today plus these days.</span>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-3 control-label">Stock</label>
                            <div class="col-sm-8">
                                <input type="number" min="0" step="1" name="stock" id="importStock" class="form-control" placeholder="e.g. 50" value="<?= htmlspecialchars((string) $prefStock) ?>">
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-3 control-label">Product URL</label>
                            <div class="col-sm-8">
                                <input type="url" name="source_url" id="importUrl" class="form-control" required placeholder="https://www.amazon.co.uk/..." value="">
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-3 control-label">Creatives</label>
                            <div class="col-sm-8">
                                <p class="text-muted" style="margin-top:7px;">Optional ad / creative links. Saved on the product and editable later from the Creatives tab.</p>
                                <table class="table table-bordered" id="import-creative-table" style="margin-bottom:8px;">
                                    <thead>
                                        <tr>
                                            <th width="180">Label (optional)</th>
                                            <th>Creative link</th>
                                            <th width="90"></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr class="creative-row">
                                            <td><input type="text" name="creatives[0][label]" class="form-control" placeholder="Facebook / TikTok"></td>
                                            <td><input type="text" name="creatives[0][link]" class="form-control" placeholder="https://..."></td>
                                            <td><button type="button" class="btn btn-white btn-sm js-remove-import-creative">Remove</button></td>
                                        </tr>
                                    </tbody>
                                </table>
                                <button type="button" class="btn btn-white btn-sm" id="addImportCreative">Add creative link</button>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-3 control-label">Base Price</label>
                            <div class="col-sm-8">
                                <div class="input-group">
                                    <input type="number" step="0.01" min="0.01" name="base_price" id="importBasePrice" class="form-control" placeholder="0.00">
                                    <span class="input-group-btn">
                                        <button type="button" class="btn btn-white" id="readPriceBtn">Read from URL</button>
                                    </span>
                                </div>
                                <span class="help-block">This is the catalog base price. Store owners get this plus commission and <?= number_format(platform_fee_percent(), 2) ?>% platform fee, then add their own markup on top.</span>
                                <span class="help-block" id="priceHint"></span>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="col-sm-8 col-sm-offset-3">
                                <div class="checkbox">
                                    <label>
                                        <input type="checkbox" name="auto_add_to_stores" value="1" id="importAutoAdd" <?= $prefAutoAdd ? 'checked' : '' ?>>
                                        Auto add to recommended stores
                                    </label>
                                </div>
                                <span class="help-block">Copies this product to every store in the selected country that has Auto-add enabled. Each store’s plus amount is added to the selling price.</span>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="col-sm-8 col-sm-offset-3">
                                <button type="submit" class="btn btn-success">Import</button>
                                <a href="<?= base_url('admin/products') ?>" class="btn btn-white">Cancel</a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
(function ($) {
    var previewUrl = <?= json_encode(base_url('admin/products/import_preview')) ?>;
    var sourceUrl = <?= json_encode(base_url('admin/products/import_source')) ?>;
    var categoriesUrl = <?= json_encode(base_url('admin/products/import_categories')) ?>;
    var cookieName = 'ec_import_prefs';
    var savedCategory = <?= (int) $prefCategory ?>;
    var savedSubcategory = <?= (int) $prefSubcategory ?>;
    var categoryTree = [];

    function readCookiePrefs() {
        var match = document.cookie.match(new RegExp('(?:^|; )' + cookieName + '=([^;]*)'));
        if (!match) {
            return {};
        }
        try {
            return JSON.parse(decodeURIComponent(match[1])) || {};
        } catch (e) {
            return {};
        }
    }

    function writeCookiePrefs(prefs) {
        document.cookie = cookieName + '=' + encodeURIComponent(JSON.stringify({
            country_id: prefs.country_id || '',
            category_id: prefs.category_id || '',
            subcategory_id: prefs.subcategory_id || '',
            ship_min_days: prefs.ship_min_days || '',
            ship_max_days: prefs.ship_max_days || '',
            stock: (prefs.stock === 0 || prefs.stock === '0') ? '0' : (prefs.stock || ''),
            auto_add_to_stores: prefs.auto_add_to_stores ? 1 : 0
        })) + '; path=/; max-age=' + (365 * 24 * 3600) + '; SameSite=Lax';
    }

    function currentPrefs() {
        return {
            country_id: $('#importCountry').val() || '',
            category_id: $('#importCategory').val() || '',
            subcategory_id: $('#importSubcategory').val() || '',
            ship_min_days: $.trim($('#importShipMin').val() || ''),
            ship_max_days: $.trim($('#importShipMax').val() || ''),
            stock: $.trim($('#importStock').val() || ''),
            auto_add_to_stores: $('#importAutoAdd').is(':checked') ? 1 : 0
        };
    }

    function savePrefs() {
        var prefs = currentPrefs();
        if ($('#importCategory').prop('disabled')) {
            var existing = readCookiePrefs();
            prefs.category_id = existing.category_id || '';
            prefs.subcategory_id = existing.subcategory_id || '';
        }
        writeCookiePrefs(prefs);
    }

    function fillSelect($el, placeholder, items, selectedId) {
        $el.empty().append($('<option/>').val('').text(placeholder));
        $.each(items || [], function (_, item) {
            var opt = $('<option/>').val(item.id).text(item.name);
            if (selectedId && String(item.id) === String(selectedId)) {
                opt.prop('selected', true);
            }
            $el.append(opt);
        });
    }

    function selectedParent() {
        var id = parseInt($('#importCategory').val(), 10) || 0;
        var found = null;
        $.each(categoryTree, function (_, parent) {
            if (parent.id === id) {
                found = parent;
                return false;
            }
        });
        return found;
    }

    function renderSubcategories(selectedId) {
        var parent = selectedParent();
        fillSelect($('#importSubcategory'), 'Select sub category', parent ? parent.children : [], selectedId || '');
    }

    function loadCategories(countryId, categoryId, subcategoryId) {
        var $cat = $('#importCategory');
        var $sub = $('#importSubcategory');
        categoryTree = [];
        fillSelect($cat, 'Select category', [], '');
        fillSelect($sub, 'Select sub category', [], '');
        if (!countryId) {
            return $.Deferred().resolve().promise();
        }
        $cat.prop('disabled', true);
        $sub.prop('disabled', true);
        return $.get(categoriesUrl, { country_id: countryId })
            .done(function (res) {
                categoryTree = (res && res.categories) ? res.categories : [];
                fillSelect($cat, 'Select category', categoryTree, categoryId || '');
                renderSubcategories(subcategoryId || '');
                savePrefs();
            })
            .fail(function () {
                fillSelect($cat, 'Could not load categories', [], '');
            })
            .always(function () {
                $cat.prop('disabled', false);
                $sub.prop('disabled', false);
            });
    }

    var cookiePrefs = readCookiePrefs();
    if (cookiePrefs.country_id && !$('#importCountry').val()) {
        $('#importCountry').val(String(cookiePrefs.country_id));
    }
    if (cookiePrefs.ship_min_days && !$('#importShipMin').val()) {
        $('#importShipMin').val(cookiePrefs.ship_min_days);
    }
    if (cookiePrefs.ship_max_days && !$('#importShipMax').val()) {
        $('#importShipMax').val(cookiePrefs.ship_max_days);
    }
    if (cookiePrefs.stock !== undefined && cookiePrefs.stock !== '' && !$('#importStock').val()) {
        $('#importStock').val(cookiePrefs.stock);
    }
    if (cookiePrefs.auto_add_to_stores) {
        $('#importAutoAdd').prop('checked', true);
    }
    if (cookiePrefs.category_id) {
        savedCategory = parseInt(cookiePrefs.category_id, 10) || savedCategory;
    }
    if (cookiePrefs.subcategory_id) {
        savedSubcategory = parseInt(cookiePrefs.subcategory_id, 10) || savedSubcategory;
    }

    loadCategories($('#importCountry').val(), savedCategory, savedSubcategory);

    function applyCountry(countryId, countryName) {
        if (!countryId) {
            return $.Deferred().resolve().promise();
        }
        var current = $('#importCountry').val();
        if (String(current) === String(countryId)) {
            return $.Deferred().resolve().promise();
        }
        $('#importCountry').val(String(countryId));
        savedCategory = 0;
        savedSubcategory = 0;
        return loadCategories(countryId, '', '').always(savePrefs).done(function () {
            if (countryName) {
                $('#priceHint').removeClass('text-danger').addClass('text-navy')
                    .text('This domain imports into ' + countryName + '.');
            }
        });
    }

    var urlTimer = null;
    function detectCountryFromUrl() {
        var url = $.trim($('#importUrl').val());
        if (!url) {
            return;
        }
        $.post(sourceUrl, { source_url: url })
            .done(function (res) {
                if (res && res.ok && res.country_id) {
                    applyCountry(res.country_id, res.country_name);
                }
            });
    }
    $('#importUrl').on('change blur paste', function () {
        clearTimeout(urlTimer);
        urlTimer = setTimeout(detectCountryFromUrl, 250);
    });

    $('#importCountry').on('change', function () {
        savedCategory = 0;
        savedSubcategory = 0;
        loadCategories($(this).val(), '', '').always(savePrefs);
    });
    $('#importCategory').on('change', function () {
        renderSubcategories('');
        savePrefs();
    });
    $('#importSubcategory, #importShipMin, #importShipMax, #importStock, #importAutoAdd').on('change', savePrefs);
    $('#importForm').on('submit', savePrefs);

    $('#readPriceBtn').on('click', function () {
        var countryId = $('#importCountry').val();
        var url = $.trim($('#importUrl').val());
        var hint = $('#priceHint');
        var btn = $(this);
        hint.removeClass('text-danger text-navy').text('');
        if (!url) {
            hint.addClass('text-danger').text('Paste a product URL first.');
            return;
        }
        savePrefs();
        btn.prop('disabled', true).text('Reading...');
        $.post(previewUrl, {
            country_id: countryId || '',
            source_url: url,
            category_id: $('#importCategory').val() || '',
            subcategory_id: $('#importSubcategory').val() || '',
            ship_min_days: $('#importShipMin').val() || '',
            ship_max_days: $('#importShipMax').val() || '',
            stock: $('#importStock').val() || '',
            base_price: $('#importBasePrice').val() || ''
        })
            .done(function (res) {
                if (res && res.country_id) {
                    applyCountry(res.country_id, res.country_name);
                }
                if (res && res.cost > 0) {
                    $('#importBasePrice').val(Number(res.cost).toFixed(2));
                    var msg = 'Source price ' + Number(res.cost).toFixed(2) + ' loaded as base price.';
                    if (res.country_name) {
                        msg += ' Country: ' + res.country_name + '.';
                    }
                    if (res.name) {
                        msg += ' Product: ' + res.name;
                    }
                    hint.removeClass('text-danger').addClass('text-navy').text(msg);
                } else {
                    hint.addClass('text-danger').text('Could not read a price from this product page.');
                }
            })
            .fail(function (xhr) {
                var error = 'Could not read this product page.';
                if (xhr.responseJSON && xhr.responseJSON.error) {
                    error = xhr.responseJSON.error;
                }
                hint.addClass('text-danger').text(error);
            })
            .always(function () {
                btn.prop('disabled', false).text('Read from URL');
            });
    });

    var importCreativeIndex = $('#import-creative-table tbody tr').length;
    $('#addImportCreative').on('click', function () {
        var html = '<tr class="creative-row">' +
            '<td><input type="text" name="creatives[' + importCreativeIndex + '][label]" class="form-control" placeholder="Facebook / TikTok"></td>' +
            '<td><input type="text" name="creatives[' + importCreativeIndex + '][link]" class="form-control" placeholder="https://..."></td>' +
            '<td><button type="button" class="btn btn-white btn-sm js-remove-import-creative">Remove</button></td>' +
            '</tr>';
        $('#import-creative-table tbody').append(html);
        importCreativeIndex++;
    });
    $(document).on('click', '.js-remove-import-creative', function () {
        var $tbody = $('#import-creative-table tbody');
        if ($tbody.find('tr').length <= 1) {
            $(this).closest('tr').find('input').val('');
            return;
        }
        $(this).closest('tr').remove();
    });
})(jQuery);
</script>
