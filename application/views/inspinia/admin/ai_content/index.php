<?php
$countries = isset($countries) ? $countries : array();
$stores = isset($stores) ? $stores : array();
$countryId = isset($country_id) ? (int) $country_id : 0;
$storeId = isset($store_id) ? (int) $store_id : 0;
$hasKey = !empty($has_api_key);
$progress = isset($progress) ? $progress : null;
?>
<style>
.aic-metric h2 { margin: 6px 0 0; }
.aic-bar { height: 16px; background: #e7eaec; border-radius: 3px; overflow: hidden; }
.aic-bar span { display: block; height: 100%; background: #1ab394; width: 0; transition: width .3s ease; }
.aic-step { font-weight: 600; }
.aic-failed { max-height: 360px; overflow: auto; }
.aic-now { background: #f8fafc; border-left: 4px solid #1ab394; padding: 10px 12px; margin: 12px 0; }
.aic-now strong { display: block; margin-bottom: 2px; }
.aic-log { max-height: 340px; overflow: auto; background: #1f2933; color: #e8edf2; border-radius: 3px; }
.aic-log table { margin: 0; color: inherit; }
.aic-log th { background: #111827; color: #9ca3af; border-color: #374151; }
.aic-log td { border-color: #374151; vertical-align: top; }
.aic-log .ev-processing { color: #f8d775; }
.aic-log .ev-success { color: #1ab394; }
.aic-log .ev-failed, .aic-log .ev-error { color: #ed5565; }
.aic-log .ev-worker, .aic-log .ev-started, .aic-log .ev-resumed, .aic-log .ev-retry { color: #23c6c8; }
.aic-log .ev-stopping, .aic-log .ev-stopped { color: #f8ac59; }
.aic-activity { padding: 12px 14px; margin: 12px 0 16px; border-radius: 4px; border-left: 4px solid #1ab394; background: #f8fafc; }
.aic-activity.is-working, .aic-activity.is-starting { border-color: #1ab394; background: #edfff8; }
.aic-activity.is-stuck, .aic-activity.is-waiting { border-color: #f8ac59; background: #fff8ee; }
.aic-activity.is-stopping { border-color: #f8ac59; background: #fff8ee; }
.aic-activity.is-stopped, .aic-activity.is-failed { border-color: #ed5565; background: #fff5f5; }
.aic-activity.is-completed { border-color: #1ab394; background: #edfff8; }
.aic-pulse { display: inline-block; width: 8px; height: 8px; border-radius: 50%; background: #1ab394; margin-right: 8px; vertical-align: middle; animation: aicPulse 1.1s ease-in-out infinite; }
.aic-activity.is-stuck .aic-pulse, .aic-activity.is-waiting .aic-pulse { background: #f8ac59; }
.aic-activity h4 { margin: 0 0 4px; font-size: 15px; }
.aic-activity p { margin: 0 0 4px; }
.aic-meta { color: #676a6c; font-size: 12px; margin-top: 8px; }
@keyframes aicPulse { 0%, 100% { opacity: 1; } 50% { opacity: .35; } }
</style>
<div class="row wrapper border-bottom white-bg page-heading">
    <div class="col-lg-8">
        <h2>AI Content Rewrite</h2>
        <ol class="breadcrumb">
            <li><a href="<?= base_url('admin/admin') ?>">Dashboard</a></li>
            <li class="active"><strong>AI Content</strong></li>
        </ol>
    </div>
    <div class="col-lg-4 text-right">
        <a href="<?= base_url('admin/ai-settings') ?>" class="btn btn-white" style="margin-top:26px;"><i class="fa fa-key"></i> AI Settings</a>
    </div>
</div>
<div class="wrapper wrapper-content">
    <?php if (!$hasKey): ?>
    <div class="alert alert-warning">
        Add the agent API key in <a href="<?= base_url('admin/ai-settings') ?>" class="alert-link">Admin &gt; AI Settings</a> before starting a rewrite.
    </div>
    <?php endif; ?>

    <div class="ibox">
        <div class="ibox-title"><h5>Select store</h5></div>
        <div class="ibox-content">
            <div class="form-horizontal">
                <div class="form-group">
                    <label class="col-sm-2 control-label">Country</label>
                    <div class="col-sm-6">
                        <select id="aicCountry" class="form-control">
                            <option value="">Select country</option>
                            <?php foreach ($countries as $country): ?>
                                <option value="<?= (int) $country->id ?>" <?= $countryId === (int) $country->id ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($country->name) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-sm-2 control-label">Store</label>
                    <div class="col-sm-6">
                        <select id="aicStore" class="form-control">
                            <option value="">Select store</option>
                            <?php foreach ($stores as $store): ?>
                                <option value="<?= (int) $store->id ?>" <?= $storeId === (int) $store->id ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($store->name) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="form-group">
                    <div class="col-sm-offset-2 col-sm-6">
                        <button type="button" class="btn btn-primary" id="aicLoadBtn">Load Products</button>
                        <span class="text-muted" id="aicLoadStatus" style="margin-left:8px;"></span>
                    </div>
                </div>
            </div>
            <div id="aicAlready" class="alert alert-warning" style="display:none; margin-top:12px;">
                An AI content rewrite is already running for this store.
                <button type="button" class="btn btn-xs btn-warning" id="aicViewProgressBtn">View Progress</button>
                <button type="button" class="btn btn-xs btn-danger" id="aicStartNewBtnAlert">Stop Old Queue &amp; Start New</button>
            </div>
        </div>
    </div>

    <div id="aicSummaryWrap" style="<?= $progress ? '' : 'display:none;' ?>">
        <div class="row">
            <div class="col-lg-3">
                <div class="ibox"><div class="ibox-content aic-metric">
                    <span class="label label-success pull-right">Store</span>
                    <h5>Total Products</h5>
                    <h2 id="aicTotal">0</h2>
                </div></div>
            </div>
            <div class="col-lg-3">
                <div class="ibox"><div class="ibox-content aic-metric">
                    <h5>Processed</h5>
                    <h2 id="aicProcessed">0</h2>
                </div></div>
            </div>
            <div class="col-lg-3">
                <div class="ibox"><div class="ibox-content aic-metric">
                    <h5>Remaining / Pending</h5>
                    <h2 id="aicRemaining">0</h2>
                </div></div>
            </div>
            <div class="col-lg-3">
                <div class="ibox"><div class="ibox-content aic-metric">
                    <h5>Failed</h5>
                    <h2 id="aicFailed">0</h2>
                </div></div>
            </div>
        </div>
        <div class="ibox">
            <div class="ibox-content">
                <p><strong>Store:</strong> <span id="aicStoreName"></span></p>
                <p class="text-muted" style="margin-bottom:12px;">Rewrites title, short description, long description, and SEO for listings in this store only. Pricing, stock, SKU, and supplier data are never changed. Sample reviews from the agent API are imported onto the parent listing.</p>
                <div class="form-inline" style="margin-bottom:12px;">
                    <p class="text-muted" style="margin:0;">Reviews per product: random <strong>3–10</strong> (not a fixed count).</p>
                    <input type="hidden" id="aicReviewCount" value="0">
                </div>
                <button type="button" class="btn btn-primary" id="aicStartBtn">Start AI Content Rewrite</button>
                <button type="button" class="btn btn-white" id="aicResumeBtn" style="display:none;">Resume Remaining</button>
                <button type="button" class="btn btn-danger" id="aicStartNewBtn" style="display:none;">Stop Old Queue &amp; Start New</button>
            </div>
        </div>
    </div>

    <div id="aicProgressWrap" class="ibox" style="<?= $progress ? '' : 'display:none;' ?>">
        <div class="ibox-title"><h5>Progress</h5></div>
        <div class="ibox-content">
            <p>Processed: <strong id="aicProgProcessed">0 / 0</strong> &nbsp; Successful: <strong id="aicSuccess">0</strong> &nbsp; Failed: <strong id="aicProgFailed">0</strong> &nbsp; Remaining: <strong id="aicProgRemaining">0</strong></p>
            <p>Progress: <strong id="aicPercent">0%</strong></p>
            <div class="aic-bar"><span id="aicBar"></span></div>
            <div class="aic-activity" id="aicActivityBox">
                <h4><span class="aic-pulse" id="aicPulse"></span><span id="aicActivityTitle">Waiting</span></h4>
                <p id="aicActivityDetail">Load a store to see live status.</p>
                <p class="aic-meta">
                    Worker: <strong id="aicWorker">—</strong>
                    &nbsp;·&nbsp; Queue: <strong id="aicQueuePos">—</strong>
                    &nbsp;·&nbsp; Last activity: <strong id="aicLastAgo">—</strong>
                </p>
                <p class="aic-meta">Last log: <span id="aicLastLog">—</span></p>
            </div>
            <div class="aic-now">
                <strong>Current request</strong>
                <span id="aicNow">Waiting</span>
            </div>
            <p>Current Product: <strong id="aicCurrentName">—</strong></p>
            <p>Product ID: <strong id="aicCurrentId">—</strong></p>
            <p>Status: <span class="aic-step" id="aicStep">Waiting</span></p>
            <p>Started: <strong id="aicStarted">—</strong> &nbsp; Elapsed: <strong id="aicElapsed">—</strong></p>
            <p>Estimated Time Remaining: <strong id="aicEta">Calculating estimated time...</strong></p>
            <p>Estimated completion: <strong id="aicEtaAt">—</strong></p>
            <h4 style="margin-top:18px;">Live log</h4>
            <div class="aic-log">
                <table class="table table-condensed">
                    <thead>
                        <tr><th>Time</th><th>Product</th><th>Status</th><th>Message</th></tr>
                    </thead>
                    <tbody id="aicLogBody">
                        <tr><td colspan="4">No log lines yet.</td></tr>
                    </tbody>
                </table>
            </div>
            <p style="margin-top:16px;">
                <button type="button" class="btn btn-danger" id="aicStopBtn">Stop Processing</button>
                <button type="button" class="btn btn-danger" id="aicStartNewBtn2" style="display:none;">Stop Old Queue &amp; Start New</button>
                <button type="button" class="btn btn-white" id="aicFailedBtn">View Failed</button>
                <button type="button" class="btn btn-warning" id="aicRetryBtn">Retry Failed</button>
            </p>
        </div>
    </div>

    <div class="ibox" id="aicFailedWrap" style="display:none;">
        <div class="ibox-title"><h5>Failed products</h5></div>
        <div class="ibox-content aic-failed">
            <table class="table table-striped">
                <thead>
                    <tr><th>ID</th><th>Product</th><th>SKU</th><th>Error</th></tr>
                </thead>
                <tbody id="aicFailedBody"></tbody>
            </table>
        </div>
    </div>
</div>
<script>
(function ($) {
    var urls = {
        stores: <?= json_encode(base_url('admin/ai-content/stores')) ?>,
        load: <?= json_encode(base_url('admin/ai-content/load')) ?>,
        start: <?= json_encode(base_url('admin/ai-content/start')) ?>,
        progress: <?= json_encode(base_url('admin/ai-content/progress')) ?>,
        stop: <?= json_encode(base_url('admin/ai-content/stop')) ?>,
        resume: <?= json_encode(base_url('admin/ai-content/resume')) ?>,
        retry: <?= json_encode(base_url('admin/ai-content/retry')) ?>,
        startNew: <?= json_encode(base_url('admin/ai-content/start-new')) ?>,
        failed: <?= json_encode(base_url('admin/ai-content/failed')) ?>
    };
    var jobId = <?= $progress ? (int) $progress['id'] : 0 ?>;
    var pollTimer = null;
    var currentStoreId = <?= (int) $storeId ?>;
    var currentCountryId = <?= (int) $countryId ?>;

    function selectedCountry() { return parseInt($('#aicCountry').val(), 10) || 0; }
    function selectedStore() { return parseInt($('#aicStore').val(), 10) || 0; }

    function fillStores(rows, keepId) {
        var $sel = $('#aicStore');
        $sel.empty().append($('<option>', { value: '', text: 'Select store' }));
        (rows || []).forEach(function (row) {
            $sel.append($('<option>', { value: row.id, text: row.name }));
        });
        if (keepId) {
            $sel.val(String(keepId));
        }
    }

    $('#aicCountry').on('change', function () {
        currentCountryId = selectedCountry();
        $('#aicStore').prop('disabled', true);
        $.get(urls.stores, { country_id: currentCountryId }, function (rows) {
            fillStores(rows);
        }, 'json').always(function () {
            $('#aicStore').prop('disabled', false);
        });
    });

    $('#aicLoadBtn').on('click', function () {
        var $btn = $(this);
        $btn.prop('disabled', true);
        $('#aicLoadStatus').text('Loading…');
        $('#aicAlready').hide();
        $.post(urls.load, {
            country_id: selectedCountry(),
            store_id: selectedStore()
        }, function (res) {
            if (!res || !res.ok) {
                $('#aicLoadStatus').text((res && res.error) ? res.error : 'Could not load products.');
                return;
            }
            currentStoreId = res.store.id;
            currentCountryId = res.store.country_id;
            $('#aicSummaryWrap').show();
            $('#aicStoreName').text(res.store.name);
            $('#aicTotal').text(res.total_products);
            $('#aicProcessed').text(res.processed_products);
            $('#aicRemaining').text(res.remaining_products);
            $('#aicFailed').text(res.failed_products);
            $('#aicLoadStatus').text('');
            if (res.already_running && res.active_job) {
                $('#aicAlready').show();
                renderProgress(res.active_job);
                startPoll();
            } else if (res.latest_job && (res.latest_job.status === 'stopped' || res.latest_job.status === 'completed' || res.latest_job.status === 'failed')) {
                renderProgress(res.latest_job);
                $('#aicResumeBtn').toggle(res.latest_job.status === 'stopped' && res.latest_job.remaining_products > 0);
                $('#aicStartNewBtn, #aicStartNewBtn2, #aicStartNewBtnAlert').toggle(res.latest_job.status === 'stopped' && res.latest_job.remaining_products > 0);
            }
        }, 'json').fail(function (xhr) {
            var msg = 'Could not load products.';
            if (xhr.responseJSON && xhr.responseJSON.error) msg = xhr.responseJSON.error;
            $('#aicLoadStatus').text(msg);
        }).always(function () {
            $btn.prop('disabled', false);
        });
    });

    $('#aicViewProgressBtn').on('click', function () {
        $('#aicProgressWrap').show();
        $('html, body').animate({ scrollTop: $('#aicProgressWrap').offset().top - 80 }, 300);
    });

    function renderProgress(job) {
        if (!job) return;
        jobId = job.id;
        $('#aicProgressWrap').show();
        $('#aicSummaryWrap').show();
        $('#aicStoreName').text(job.store_name || $('#aicStoreName').text());
        $('#aicTotal').text(job.total_products);
        $('#aicProcessed').text(job.processed_products);
        $('#aicRemaining').text(job.remaining_products);
        $('#aicFailed').text(job.failed_products);
        $('#aicProgProcessed').text(job.processed_products + ' / ' + job.total_products);
        $('#aicSuccess').text(job.successful_products);
        $('#aicProgFailed').text(job.failed_products);
        $('#aicProgRemaining').text(job.remaining_products);
        $('#aicPercent').text(job.percent + '%');
        $('#aicBar').css('width', job.percent + '%');
        $('#aicCurrentName').text(job.current_product_name || '—');
        $('#aicCurrentId').text(job.current_product_id || '—');
        $('#aicStep').text(job.current_step || job.status);
        $('#aicNow').text(job.current_status || job.current_step || job.status || 'Waiting');
        var act = job.activity || {};
        var state = act.state || job.status || 'idle';
        $('#aicActivityBox').attr('class', 'aic-activity is-' + state);
        $('#aicActivityTitle').text(act.title || job.current_step || job.status || 'Waiting');
        $('#aicActivityDetail').text(act.detail || job.current_status || '');
        $('#aicWorker').text(act.worker_alive ? 'Running' : 'Not running');
        $('#aicQueuePos').text(act.queue_label || (job.processed_products + ' / ' + job.total_products));
        $('#aicLastAgo').text(act.last_activity_ago || '—');
        $('#aicLastLog').text(act.last_log || '—');
        $('#aicPulse').toggle(state === 'working' || state === 'starting' || state === 'waiting' || state === 'stuck' || state === 'stopping');
        renderLogs(job.logs || []);
        $('#aicStarted').text(job.started_label || '—');
        $('#aicElapsed').text(job.elapsed_label || '—');
        $('#aicEta').text(job.estimate_ready ? ('Approximately ' + job.estimated_label) : 'Calculating estimated time...');
        $('#aicEtaAt').text(job.estimated_completion || '—');
        if (job.review_count) {
            $('#aicReviewCount').val('0');
        }
        var running = job.status === 'running' || job.status === 'stopping' || job.status === 'pending';
        var canStartNew = running || (job.status === 'stopped' && job.remaining_products > 0);
        $('#aicStopBtn').toggle(job.status === 'running' || job.status === 'pending');
        $('#aicResumeBtn').toggle(job.status === 'stopped' && job.remaining_products > 0);
        $('#aicStartNewBtn, #aicStartNewBtn2, #aicStartNewBtnAlert').toggle(canStartNew);
        $('#aicStartBtn').prop('disabled', running);
        if (job.status === 'stopping') {
            $('#aicStep').text('Stopping after current product');
        }
    }

    function startPoll() {
        stopPoll();
        pollTimer = setInterval(pollProgress, 2000);
        pollProgress();
    }
    function stopPoll() {
        if (pollTimer) {
            clearInterval(pollTimer);
            pollTimer = null;
        }
    }
    function pollProgress() {
        if (!jobId) return;
        $.get(urls.progress, { job_id: jobId }, function (res) {
            if (res && res.ok && res.job) {
                renderProgress(res.job);
                if (res.job.status === 'completed' || res.job.status === 'failed' || res.job.status === 'stopped') {
                    stopPoll();
                }
            }
        }, 'json');
    }

    function esc(text) {
        return $('<div>').text(text == null ? '' : String(text)).html();
    }
    function renderLogs(logs) {
        var $wrap = $('.aic-log');
        var atBottom = !$wrap.length || ($wrap[0].scrollHeight - $wrap.scrollTop() - $wrap.outerHeight() < 40);
        if (!logs || !logs.length) {
            $('#aicLogBody').html('<tr><td colspan="4">No log lines yet.</td></tr>');
            return;
        }
        var html = logs.map(function (row) {
            var product = row.product_name || (row.product_id ? ('#' + row.product_id) : '—');
            var cls = 'ev-' + String(row.event_type || '').replace(/[^a-z0-9_-]/gi, '');
            return '<tr><td>' + esc(row.time_label || '') + '</td><td>' + esc(product) + '</td><td class="' + cls + '">' + esc(row.event_type || '') + '</td><td class="' + cls + '">' + esc(row.message || '') + '</td></tr>';
        }).join('');
        $('#aicLogBody').html(html);
        if (atBottom) {
            $wrap.scrollTop($wrap[0].scrollHeight);
        }
    }

    function postJob(url, extra) {
        extra = extra || {};
        extra.job_id = jobId;
        extra.country_id = selectedCountry() || currentCountryId;
        extra.store_id = selectedStore() || currentStoreId;
        extra.review_count = 0;
        return $.post(url, extra, null, 'json');
    }

    $('#aicStartBtn').on('click', function () {
        var $btn = $(this);
        $btn.prop('disabled', true);
        postJob(urls.start).done(function (res) {
            if (res.already_running && res.job) {
                $('#aicAlready').show();
                renderProgress(res.job);
                startPoll();
                return;
            }
            if (!res.ok) {
                alert(res.error || 'Could not start.');
                return;
            }
            $('#aicAlready').hide();
            renderProgress(res.job);
            startPoll();
        }).fail(function (xhr) {
            var res = xhr.responseJSON;
            if (res && res.already_running && res.job) {
                $('#aicAlready').show();
                renderProgress(res.job);
                startPoll();
                return;
            }
            alert((res && res.error) ? res.error : 'Could not start.');
        }).always(function () {
            $btn.prop('disabled', false);
        });
    });

    $('#aicStopBtn').on('click', function () {
        postJob(urls.stop).done(function (res) {
            if (res && res.job) renderProgress(res.job);
        });
    });

    $('#aicResumeBtn').on('click', function () {
        postJob(urls.resume).done(function (res) {
            if (res && res.ok && res.job) {
                renderProgress(res.job);
                startPoll();
            } else if (res && res.error) {
                alert(res.error);
            }
        });
    });

    $('#aicRetryBtn').on('click', function () {
        postJob(urls.retry).done(function (res) {
            if (res && res.ok && res.job) {
                renderProgress(res.job);
                startPoll();
            } else if (res && res.error) {
                alert(res.error);
            }
        });
    });

    function startNewQueue() {
        if (!confirm('Stop the old queue and start a new rewrite from the first product? The current product may still finish.')) {
            return;
        }
        $('#aicStartNewBtn, #aicStartNewBtn2, #aicStartNewBtnAlert').prop('disabled', true);
        postJob(urls.startNew).done(function (res) {
            if (!res || !res.ok) {
                alert((res && res.error) ? res.error : 'Could not start a new queue.');
                return;
            }
            $('#aicAlready').hide();
            renderProgress(res.job);
            startPoll();
        }).fail(function (xhr) {
            var res = xhr.responseJSON;
            alert((res && res.error) ? res.error : 'Could not start a new queue.');
        }).always(function () {
            $('#aicStartNewBtn, #aicStartNewBtn2, #aicStartNewBtnAlert').prop('disabled', false);
        });
    }
    $('#aicStartNewBtn, #aicStartNewBtn2, #aicStartNewBtnAlert').on('click', startNewQueue);

    $('#aicFailedBtn').on('click', function () {
        if (!jobId) return;
        $.get(urls.failed, { job_id: jobId }, function (res) {
            var rows = (res && res.items) ? res.items : [];
            var html = rows.length ? rows.map(function (row) {
                return '<tr><td>' + row.product_id + '</td><td>' + $('<div>').text(row.name || '').html() + '</td><td>' + $('<div>').text(row.sku || '').html() + '</td><td>' + $('<div>').text(row.error_message || '').html() + '</td></tr>';
            }).join('') : '<tr><td colspan="4">No failed products.</td></tr>';
            $('#aicFailedBody').html(html);
            $('#aicFailedWrap').show();
        }, 'json');
    });

    <?php if ($progress): ?>
    renderProgress(<?= json_encode($progress) ?>);
    <?php if (in_array($progress['status'], array('running', 'pending', 'stopping'), true)): ?>
    startPoll();
    <?php endif; ?>
    <?php endif; ?>
})(jQuery);
</script>
