<div class="row wrapper border-bottom white-bg page-heading">
    <div class="col-lg-10">
        <h2>Social Channels</h2>
        <ol class="breadcrumb">
            <li><a href="<?= base_url('admin/admin') ?>">Dashboard</a></li>
            <li class="active"><strong>Social</strong></li>
        </ol>
    </div>
</div>
<div class="wrapper wrapper-content">
    <div class="row">
        <div class="col-lg-10">
            <?php $this->load->view('flash'); ?>
            <p class="text-muted">Create one Meta app and one TikTok app for the whole platform. Stores never paste tokens — they only click Connect and approve their own Facebook / TikTok account.</p>

            <form method="post" action="<?= base_url('admin/social/save') ?>" class="form-horizontal">
                <div class="ibox">
                    <div class="ibox-title"><h5>Meta (Facebook &amp; Instagram)</h5></div>
                    <div class="ibox-content">
                        <p class="text-muted">Graph API <?= htmlspecialchars($meta_api_version) ?>. In Meta for Developers, add this OAuth redirect URI exactly:</p>
                        <p><code><?= htmlspecialchars($meta_redirect) ?></code></p>
                        <p class="text-muted">Required permissions: pages_show_list, business_management, catalog_management, ads_management, instagram_basic.</p>
                        <div class="form-group">
                            <label class="col-sm-3 control-label">App ID</label>
                            <div class="col-sm-8">
                                <input type="text" name="meta_app_id" class="form-control" value="<?= htmlspecialchars($meta_app_id) ?>" autocomplete="off">
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-3 control-label">App Secret</label>
                            <div class="col-sm-8">
                                <input type="password" name="meta_app_secret" class="form-control" value="" placeholder="<?= $meta_app_secret !== '' ? 'Leave blank to keep current' : '' ?>" autocomplete="off">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="ibox">
                    <div class="ibox-title"><h5>TikTok</h5></div>
                    <div class="ibox-content">
                        <p class="text-muted">Marketing API <?= htmlspecialchars($tiktok_api_version) ?>. In TikTok Marketing API / Developer portal, add this redirect URI exactly:</p>
                        <p><code><?= htmlspecialchars($tiktok_redirect) ?></code></p>
                        <div class="form-group">
                            <label class="col-sm-3 control-label">App ID</label>
                            <div class="col-sm-8">
                                <input type="text" name="tiktok_app_id" class="form-control" value="<?= htmlspecialchars($tiktok_app_id) ?>" autocomplete="off">
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-3 control-label">App Secret</label>
                            <div class="col-sm-8">
                                <input type="password" name="tiktok_app_secret" class="form-control" value="" placeholder="<?= $tiktok_app_secret !== '' ? 'Leave blank to keep current' : '' ?>" autocomplete="off">
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="col-sm-8 col-sm-offset-3">
                                <button type="submit" class="btn btn-primary">Save social apps</button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
            <div class="ibox">
                <div class="ibox-title"><h5>Catalog / event job worker</h5></div>
                <div class="ibox-content">
                    <p class="text-muted">Meta and TikTok API calls run in a background job queue. Hit this URL from cron every minute:</p>
                    <p><code><?= htmlspecialchars($cron_url) ?></code></p>
                </div>
            </div>
        </div>
    </div>
</div>
