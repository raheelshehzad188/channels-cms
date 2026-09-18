<div class="row wrapper border-bottom white-bg page-heading">
    <div class="col-lg-8">
        <h2>WhatsApp</h2>
        <ol class="breadcrumb">
            <li><a href="<?= base_url('admin/admin') ?>">Dashboard</a></li>
            <li class="active"><strong>WhatsApp</strong></li>
        </ol>
    </div>
    <div class="col-lg-4 text-right">
        <button type="button" class="btn btn-success" style="margin-top:26px;" data-toggle="modal" data-target="#whatsappTestModal">
            <i class="fa fa-whatsapp"></i> Test Connection
        </button>
    </div>
</div>
<div class="wrapper wrapper-content">
    <div class="row">
        <div class="col-lg-8">
            <div class="ibox">
                <div class="ibox-title"><h5>WhatsApp notifications</h5></div>
                <div class="ibox-content">
                    <?php $this->load->view('flash'); ?>
                    <p class="text-muted">When enabled, a WhatsApp is sent to the customer, store, ecommerce user and admin as soon as an order is placed (and on later status updates). Numbers are collected at checkout and in each party’s settings.</p>
                    <form method="post" action="<?= base_url('admin/whatsapp/save') ?>" class="form-horizontal">
                        <div class="form-group">
                            <label class="col-sm-3 control-label">Enable WhatsApp</label>
                            <div class="col-sm-7">
                                <label class="checkbox-inline">
                                    <input type="checkbox" name="enabled" value="1" <?= $whatsapp['enabled'] === '1' ? 'checked' : '' ?>> Send order notifications
                                </label>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-3 control-label">API URL</label>
                            <div class="col-sm-7">
                                <input type="text" name="api_url" class="form-control" value="<?= htmlspecialchars($whatsapp['api_url']) ?>" placeholder="https://example.com/api/send-message.php">
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-3 control-label">API key</label>
                            <div class="col-sm-7">
                                <input type="text" name="api_key" class="form-control" value="<?= htmlspecialchars($whatsapp['api_key']) ?>" autocomplete="off">
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-3 control-label">API secret</label>
                            <div class="col-sm-7">
                                <input type="password" name="api_secret" class="form-control" value="" placeholder="<?= $whatsapp['api_secret'] !== '' ? 'Leave blank to keep current' : '' ?>" autocomplete="off">
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-3 control-label">Session name</label>
                            <div class="col-sm-7">
                                <input type="text" name="session_name" class="form-control" value="<?= htmlspecialchars($whatsapp['session_name']) ?>" placeholder="user_30">
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-3 control-label">Admin notify number</label>
                            <div class="col-sm-7">
                                <input type="text" name="admin_whatsapp_number" class="form-control" value="<?= htmlspecialchars($whatsapp['admin_whatsapp_number']) ?>" placeholder="923004210607">
                                <span class="help-block">WhatsApp number that receives every new order. Use country code without +.</span>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="col-sm-7 col-sm-offset-3">
                                <button type="submit" class="btn btn-primary">Save WhatsApp</button>
                                <button type="button" class="btn btn-success" data-toggle="modal" data-target="#whatsappTestModal">
                                    <i class="fa fa-whatsapp"></i> Test Connection
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal inmodal fade" id="whatsappTestModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form id="whatsappTestForm">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                    <h4 class="modal-title">Test Connection</h4>
                    <small class="font-bold">Enter a WhatsApp number. A test message will be sent there.</small>
                </div>
                <div class="modal-body">
                    <div id="whatsappTestAlert" class="alert" style="display:none;"></div>
                    <div class="form-group">
                        <label>WhatsApp number</label>
                        <input type="text" name="test_phone" id="whatsappTestPhone" class="form-control" required placeholder="923004210607" value="<?= htmlspecialchars($whatsapp['admin_whatsapp_number']) ?>">
                        <span class="help-block">Country code without +. Example: 923004210607</span>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-white" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-success" id="whatsappTestSubmit">
                        <i class="fa fa-whatsapp"></i> Send test message
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
<script>
(function ($) {
    var $form = $('#whatsappTestForm');
    var $alert = $('#whatsappTestAlert');
    var $btn = $('#whatsappTestSubmit');
    $('#whatsappTestModal').on('shown.bs.modal', function () {
        $('#whatsappTestPhone').focus().select();
    });
    $form.on('submit', function (e) {
        e.preventDefault();
        var phone = $.trim($('#whatsappTestPhone').val());
        if (!phone) {
            $alert.removeClass('alert-success').addClass('alert-danger').text('Enter a WhatsApp number.').show();
            return;
        }
        $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Sending...');
        $alert.hide();
        $.ajax({
            url: <?= json_encode(base_url('admin/whatsapp/test')) ?>,
            type: 'POST',
            dataType: 'text',
            timeout: 30000,
            data: { test_phone: phone }
        }).done(function (raw) {
            var res = {};
            try { res = JSON.parse(raw); } catch (e) { res = {}; }
            var ok = !!(res && res.ok);
            var msg = (res && res.message) ? res.message : (ok ? 'Message sent.' : (raw ? String(raw).replace(/<[^>]+>/g, ' ').trim().slice(0, 180) : 'Failed to send.'));
            $alert
                .toggleClass('alert-success', ok)
                .toggleClass('alert-danger', !ok)
                .text(msg)
                .show();
        }).fail(function (xhr) {
            var msg = 'Could not reach the server.';
            if (xhr && xhr.responseText) {
                try {
                    var res = JSON.parse(xhr.responseText);
                    if (res && res.message) { msg = res.message; }
                } catch (e) {}
            }
            $alert.removeClass('alert-success').addClass('alert-danger').text(msg).show();
        }).always(function () {
            $btn.prop('disabled', false).html('<i class="fa fa-whatsapp"></i> Send test message');
        });
    });
})(jQuery);
</script>
