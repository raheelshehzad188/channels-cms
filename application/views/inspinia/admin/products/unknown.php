<div class="row wrapper border-bottom white-bg page-heading">
    <div class="col-lg-8">
        <h2>Failed Imports</h2>
        <ol class="breadcrumb">
            <li><a href="<?= base_url('admin/admin') ?>">Dashboard</a></li>
            <li><a href="<?= base_url('admin/products') ?>">Products</a></li>
            <li class="active"><strong>Failed imports</strong></li>
        </ol>
    </div>
    <div class="col-lg-4 text-right" style="margin-top:26px;">
        <?php if (!empty($links)): ?>
        <button type="button" class="btn btn-primary" id="retryAllFailedBtn">Train &amp; Re-import all</button>
        <?php endif; ?>
    </div>
</div>
<div class="wrapper wrapper-content animated fadeInRight">
    <div class="row">
        <div class="col-lg-12">
            <div class="ibox float-e-margins">
                <div class="ibox-title"><h5>Links that failed to import</h5></div>
                <div class="ibox-content">
                    <?php $this->load->view('flash'); ?>
                    <p class="text-muted">The system learns known marketplaces (Amazon, eBay, Fyndiq, CDON, Go Dropship, Partyhallen, Dollarstore, AW Dropship) from these URLs, then retries each row. Successful re-imports are removed from this list automatically.</p>
                    <div id="retryAllAlert" class="alert" style="display:none;"></div>
                    <div id="retryAllProgressWrap" style="display:none; margin-bottom:15px;">
                        <p id="retryAllProgressLabel" style="font-weight:600;">Re-importing 0 of 0</p>
                        <div class="progress progress-striped active">
                            <div class="progress-bar progress-bar-success" id="retryAllProgressBar" style="width:0%;">0%</div>
                        </div>
                        <p>
                            <strong>Imported:</strong> <span id="retryStatImported">0</span>
                            &nbsp; <strong>Already existed:</strong> <span id="retryStatExisting">0</span>
                            &nbsp; <strong>Failed:</strong> <span id="retryStatFailed">0</span>
                        </p>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-striped table-bordered" id="failedImportTable">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Product / URL</th>
                                    <th>Country</th>
                                    <th>User</th>
                                    <th>Category</th>
                                    <th>Price</th>
                                    <th>Ship days</th>
                                    <th>Stock</th>
                                    <th>Error</th>
                                    <th>Hits</th>
                                    <th width="150">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php if (empty($links)): ?>
                                <tr><td colspan="11" class="text-center">No failed imports yet.</td></tr>
                            <?php endif; ?>
                            <?php foreach ($links as $link): ?>
                                <?php
                                $categoryLabel = trim(($link->category_name ?: '') . ($link->subcategory_name ? ' / ' . $link->subcategory_name : ''));
                                $userLabel = trim($link->user_name) !== '' ? $link->user_name : ($link->uname ?: '-');
                                $ship = ((int) $link->ship_min_days || (int) $link->ship_max_days)
                                    ? ((int) $link->ship_min_days . '–' . (int) $link->ship_max_days)
                                    : '-';
                                $productName = isset($link->product_name) ? trim((string) $link->product_name) : '';
                                ?>
                                <tr data-failed-id="<?= (int) $link->id ?>">
                                    <td><?= (int) $link->id ?></td>
                                    <td>
                                        <?php if ($productName !== ''): ?>
                                        <div style="margin-bottom:4px;"><strong><?= htmlspecialchars($productName) ?></strong></div>
                                        <?php endif; ?>
                                        <div class="input-group input-group-sm" style="min-width:280px;">
                                            <input type="text" class="form-control" readonly value="<?= htmlspecialchars($link->url) ?>">
                                            <span class="input-group-btn">
                                                <button type="button" class="btn btn-white copy-failed-link" data-link="<?= htmlspecialchars($link->url, ENT_QUOTES) ?>">Copy</button>
                                            </span>
                                        </div>
                                        <small class="text-muted"><?= htmlspecialchars($link->domain) ?></small>
                                    </td>
                                    <td><?= htmlspecialchars($link->country_name ?: '-') ?></td>
                                    <td><?= htmlspecialchars($userLabel) ?></td>
                                    <td><?= htmlspecialchars($categoryLabel !== '' ? $categoryLabel : '-') ?></td>
                                    <td><?= (float) $link->base_price > 0 ? number_format((float) $link->base_price, 2) : '-' ?></td>
                                    <td><?= htmlspecialchars($ship) ?></td>
                                    <td><?= (int) $link->stock ?></td>
                                    <td class="js-error-cell"><?= htmlspecialchars($link->error_message ?: '-') ?></td>
                                    <td><?= (int) $link->hit_count ?></td>
                                    <td>
                                        <a class="btn btn-xs btn-primary" href="<?= base_url('admin/products/unknown_retry/' . (int) $link->id) ?>">Re-import</a>
                                        <a class="btn btn-xs btn-danger" href="<?= base_url('admin/products/unknown_delete/' . (int) $link->id) ?>" onclick="return confirm('Remove this failed import?');">Remove</a>
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
<script>
(function ($) {
    var retryUrl = <?= json_encode(base_url('admin/products/unknown_retry_row')) ?>;
    var ids = <?= json_encode(isset($failed_ids) ? $failed_ids : array()) ?>;
    var state = { index: 0, imported: 0, existing: 0, failed: 0, running: false };

    $(document).on('click', '.copy-failed-link', function () {
        var link = $(this).data('link') || '';
        var btn = $(this);
        if (!link) {
            return;
        }
        var done = function () {
            var original = btn.text();
            btn.text('Copied');
            setTimeout(function () { btn.text(original); }, 1200);
        };
        if (navigator.clipboard && navigator.clipboard.writeText) {
            navigator.clipboard.writeText(link).then(done);
            return;
        }
        var temp = $('<textarea readonly></textarea>').val(link).appendTo('body').select();
        try { document.execCommand('copy'); } catch (e) {}
        temp.remove();
        done();
    });

    function alertBox(type, message) {
        var $alert = $('#retryAllAlert');
        if (!message) {
            $alert.hide().text('');
            return;
        }
        $alert.removeClass('alert-danger alert-success alert-warning alert-info')
            .addClass('alert-' + type)
            .html(message)
            .show();
    }

    function setProgress(current, total) {
        var pct = total > 0 ? Math.round((current / total) * 100) : 0;
        $('#retryAllProgressLabel').text('Re-importing ' + current + ' of ' + total);
        $('#retryAllProgressBar').css('width', pct + '%').text(pct + '%');
        $('#retryStatImported').text(state.imported);
        $('#retryStatExisting').text(state.existing);
        $('#retryStatFailed').text(state.failed);
    }

    function removeRow(id) {
        var $row = $('#failedImportTable tbody tr[data-failed-id="' + id + '"]');
        $row.remove();
        if (!$('#failedImportTable tbody tr[data-failed-id]').length) {
            $('#failedImportTable tbody').html('<tr><td colspan="11" class="text-center">No failed imports yet.</td></tr>');
        }
    }

    function markFailed(id, error) {
        var $row = $('#failedImportTable tbody tr[data-failed-id="' + id + '"]');
        $row.addClass('danger');
        $row.find('.js-error-cell').text(error || 'Re-import failed.');
    }

    function finish() {
        state.running = false;
        $('#retryAllProgressWrap .progress').removeClass('active');
        $('#retryAllFailedBtn').prop('disabled', false).text('Train & Re-import all');
        if (!$('#failedImportTable tbody tr[data-failed-id]').length) {
            $('#retryAllFailedBtn').hide();
        }
        var leftover = $('#failedImportTable tbody tr[data-failed-id]').length;
        var type = leftover ? 'warning' : 'success';
        alertBox(type,
            '<strong>Imported:</strong> ' + state.imported +
            ' &nbsp; <strong>Already existed:</strong> ' + state.existing +
            ' &nbsp; <strong>Still failed:</strong> ' + state.failed +
            (leftover ? '<br>Rows that still failed stay on this list.' : '<br>All failed imports were re-imported and removed from this list.')
        );
    }

    function retryNext() {
        if (!state.running) {
            return;
        }
        if (state.index >= ids.length) {
            finish();
            return;
        }
        var id = ids[state.index];
        setProgress(state.index + 1, ids.length);
        $.ajax({
            url: retryUrl,
            method: 'POST',
            data: { id: id },
            dataType: 'json',
            timeout: 120000
        }).done(function (res) {
            if (res && res.ok) {
                if (res.status === 'existing') {
                    state.existing++;
                } else {
                    state.imported++;
                }
                removeRow(id);
            } else {
                state.failed++;
                markFailed(id, res && res.error ? res.error : 'Re-import failed.');
            }
            $('#retryStatImported').text(state.imported);
            $('#retryStatExisting').text(state.existing);
            $('#retryStatFailed').text(state.failed);
            state.index++;
            retryNext();
        }).fail(function (xhr) {
            var res = xhr.responseJSON || {};
            state.failed++;
            markFailed(id, res.error || 'Re-import request failed.');
            state.index++;
            retryNext();
        });
    }

    $('#retryAllFailedBtn').on('click', function () {
        if (state.running || !ids.length) {
            return;
        }
        if (!confirm('Train known marketplace domains and re-import all ' + ids.length + ' failed links? Successful rows will leave this list.')) {
            return;
        }
        state = { index: 0, imported: 0, existing: 0, failed: 0, running: true };
        $('#retryAllProgressWrap').show();
        $('#retryAllProgressWrap .progress').addClass('active');
        $(this).prop('disabled', true).text('Re-importing…');
        alertBox('info', 'Learning marketplace domains and retrying one product at a time. Keep this page open.');
        retryNext();
    });
})(jQuery);
</script>
