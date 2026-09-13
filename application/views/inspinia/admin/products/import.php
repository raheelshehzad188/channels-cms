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
                    <p class="text-muted">Select the country, paste a product link, set the catalog base price, then import. The supplier is assigned from the website automatically.</p>
                    <form method="post" action="<?= base_url('admin/products/import_save') ?>" class="form-horizontal" id="importForm">
                        <div class="form-group">
                            <label class="col-sm-3 control-label">Country</label>
                            <div class="col-sm-8">
                                <select name="country_id" id="importCountry" class="form-control" required>
                                    <option value="">Select country</option>
                                    <?php foreach ($countries as $country): ?>
                                        <option value="<?= (int) $country->id ?>"><?= htmlspecialchars($country->name) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-3 control-label">Product URL</label>
                            <div class="col-sm-8">
                                <input type="url" name="source_url" id="importUrl" class="form-control" required placeholder="https://www.amazon.co.uk/..." value="">
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
                                <span class="help-block">This is the catalog base price. Store owners get this plus commission and platform fee, then add their own markup on top.</span>
                                <span class="help-block" id="priceHint"></span>
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
    $('#readPriceBtn').on('click', function () {
        var countryId = $('#importCountry').val();
        var url = $.trim($('#importUrl').val());
        var hint = $('#priceHint');
        var btn = $(this);
        hint.removeClass('text-danger text-navy').text('');
        if (!countryId) {
            hint.addClass('text-danger').text('Select a country first.');
            return;
        }
        if (!url) {
            hint.addClass('text-danger').text('Paste a product URL first.');
            return;
        }
        btn.prop('disabled', true).text('Reading...');
        $.post(previewUrl, { country_id: countryId, source_url: url })
            .done(function (res) {
                if (res && res.cost > 0) {
                    $('#importBasePrice').val(res.cost);
                    var msg = 'Source price ' + Number(res.cost).toFixed(2) + ' loaded as base price.';
                    if (res.name) {
                        msg += ' Product: ' + res.name;
                    }
                    hint.addClass('text-navy').text(msg);
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
})(jQuery);
</script>
