<?php
$keySet = !empty($agent['api_key_set']);
$hint = isset($agent['api_key_hint']) ? $agent['api_key_hint'] : '';
?>
<div class="row wrapper border-bottom white-bg page-heading">
    <div class="col-lg-8">
        <h2>AI Settings</h2>
        <ol class="breadcrumb">
            <li><a href="<?= base_url('admin/admin') ?>">Dashboard</a></li>
            <li class="active"><strong>AI Settings</strong></li>
        </ol>
    </div>
    <div class="col-lg-4 text-right">
        <a href="<?= base_url('admin/ai-content') ?>" class="btn btn-white" style="margin-top:26px;">Open AI Content</a>
    </div>
</div>
<div class="wrapper wrapper-content">
    <div class="row">
        <div class="col-lg-8">
            <div class="ibox">
                <div class="ibox-title"><h5>Content rewrite API</h5></div>
                <div class="ibox-content">
                    <?php $this->load->view('flash'); ?>
                    <p class="text-muted">Used by Write with AI on product forms and by bulk AI Content rewrite. Paste the agent <code>X-API-Key</code> here. No Gemini key or model is needed.</p>
                    <form method="post" action="<?= base_url('admin/ai-settings/save') ?>" class="form-horizontal">
                        <div class="form-group">
                            <label class="col-sm-3 control-label">API key</label>
                            <div class="col-sm-7">
                                <input type="password" name="api_key" class="form-control" value="" autocomplete="new-password" placeholder="<?= $keySet ? 'Leave blank to keep current key' : 'Paste X-API-Key' ?>">
                                <?php if ($keySet): ?>
                                <span class="help-block">Current key: <?= htmlspecialchars($hint) ?></span>
                                <?php else: ?>
                                <span class="help-block">This is the <code>X-API-Key</code> sent to <code>/api/v1/agent</code>.</span>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="col-sm-7 col-sm-offset-3">
                                <button type="submit" class="btn btn-primary">Save AI Settings</button>
                                <button type="button" class="btn btn-success" id="aiTestBtn" <?= $keySet ? '' : 'disabled' ?>>
                                    <i class="fa fa-plug"></i> Test Connection
                                </button>
                                <span class="text-muted" id="aiTestStatus" style="margin-left:8px;"></span>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            <div class="ibox">
                <div class="ibox-title"><h5>Bilingual rewrite instruction</h5></div>
                <div class="ibox-content">
                    <p class="text-muted">Paste this into the agent system prompt as well. Write with AI already sends it in each request so one response includes country language + English (<code>title_en</code>, <code>short_detail_en</code>, <code>long_detail_en</code>, SEO keys).</p>
                    <textarea class="form-control" rows="18" readonly id="bilingualInstruction"><?= htmlspecialchars(isset($bilingual_instruction) ? $bilingual_instruction : '') ?></textarea>
                    <p style="margin-top:10px;">
                        <button type="button" class="btn btn-white btn-sm" id="copyInstructionBtn">Copy instruction</button>
                        <span class="text-muted" id="copyInstructionStatus" style="margin-left:8px;"></span>
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
(function ($) {
    $('#aiTestBtn').on('click', function () {
        var $btn = $(this);
        var $status = $('#aiTestStatus');
        $btn.prop('disabled', true);
        $status.text('Calling /api/v1/agent…');
        $.post(<?= json_encode(base_url('admin/ai-settings/test')) ?>, {}, function (res) {
            if (res && res.ok) {
                $status.text(res.message || 'Connected.');
            } else {
                $status.text((res && res.error) ? res.error : 'Test failed.');
            }
        }, 'json').fail(function (xhr) {
            var msg = 'Test failed.';
            if (xhr.responseJSON && xhr.responseJSON.error) {
                msg = xhr.responseJSON.error;
            }
            $status.text(msg);
        }).always(function () {
            $btn.prop('disabled', false);
        });
    });
    $('#copyInstructionBtn').on('click', function () {
        var text = $('#bilingualInstruction').val() || '';
        var $status = $('#copyInstructionStatus');
        if (navigator.clipboard && navigator.clipboard.writeText) {
            navigator.clipboard.writeText(text).then(function () {
                $status.text('Copied.');
            }).catch(function () {
                $status.text('Copy failed. Select the text instead.');
            });
            return;
        }
        $('#bilingualInstruction').select();
        $status.text('Select and copy.');
    });
})(jQuery);
</script>
