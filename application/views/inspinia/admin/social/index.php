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
            <p class="text-muted">Platform apps are entered here once. Store admins never see the App Secret or access tokens — they only click Connect Meta on their store.</p>

            <form method="post" action="<?= base_url('admin/social/save-meta') ?>" class="form-horizontal">
                <div class="ibox">
                    <div class="ibox-title">
                        <h5>Meta Integration</h5>
                        <?php if (!empty($meta_configured)): ?>
                            <span class="label label-primary pull-right" style="margin-top:8px;">App configured</span>
                        <?php else: ?>
                            <span class="label label-warning pull-right" style="margin-top:8px;">Not configured</span>
                        <?php endif; ?>
                    </div>
                    <div class="ibox-content">
                        <p class="text-muted">Graph API <?= htmlspecialchars($meta_api_version) ?>. Create the app in Meta for Developers, add the Facebook Login product, then paste the App ID and App Secret here. Redirect URIs are generated from each store domain — copy them into Meta → Facebook Login → Settings → Valid OAuth Redirect URIs.</p>
                        <div class="form-group">
                            <label class="col-sm-3 control-label">Meta App ID</label>
                            <div class="col-sm-8">
                                <input type="text" name="meta_app_id" class="form-control" value="<?= htmlspecialchars($meta_app_id) ?>" autocomplete="off" placeholder="From Meta for Developers → App settings → Basic">
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-3 control-label">Meta App Secret</label>
                            <div class="col-sm-8">
                                <input type="password" name="meta_app_secret" class="form-control" value="" placeholder="<?= !empty($meta_app_secret) ? 'Leave blank to keep current secret' : 'From Meta for Developers → App settings → Basic' ?>" autocomplete="new-password">
                                <span class="help-block">Stored encrypted. Never shown to store admins or in storefront JavaScript.</span>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-3 control-label">OAuth Redirect URI</label>
                            <div class="col-sm-8">
                                <p class="help-block" style="margin-top:7px;">Generated automatically for each store. Add every Event API URI below to the Meta app. Do not change these in ZENvello.</p>
                                <?php if (!empty($meta_store_uris)): ?>
                                    <?php foreach ($meta_store_uris as $uri): ?>
                                        <label class="text-muted" style="margin-top:8px;"><?= htmlspecialchars($uri['name']) ?> — Event API / Connect Meta</label>
                                        <input type="text" class="form-control" value="<?= htmlspecialchars($uri['events']) ?>" readonly onclick="this.select()">
                                        <label class="text-muted" style="margin-top:6px;"><?= htmlspecialchars($uri['name']) ?> — Sales channels (catalog)</label>
                                        <input type="text" class="form-control" value="<?= htmlspecialchars($uri['channels']) ?>" readonly onclick="this.select()">
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <input type="text" class="form-control" value="<?= htmlspecialchars($meta_events_redirect) ?>" readonly onclick="this.select()">
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="col-sm-8 col-sm-offset-3">
                                <button type="submit" class="btn btn-primary">Save Meta Settings</button>
                            </div>
                        </div>
                        <p class="text-muted" style="margin-bottom:0;">Facebook Login on Event API is the Meta account connection only (<code>public_profile</code>). Server events use a separate Events Manager / Dataset CAPI access token that the store keeper pastes and verifies. Do not treat the Login token as the CAPI token.</p>
                    </div>
                </div>
            </form>

            <form method="post" action="<?= base_url('admin/social/save-tiktok') ?>" class="form-horizontal">
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
                                <button type="submit" class="btn btn-primary">Save TikTok settings</button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
            <div class="ibox">
                <div class="ibox-title"><h5>Catalog / event job worker</h5></div>
                <div class="ibox-content">
                    <p class="text-muted">Meta and TikTok catalog API calls run in a background job queue. Hit this URL from cron every minute:</p>
                    <p><code><?= htmlspecialchars($cron_url) ?></code></p>
                </div>
            </div>
        </div>
    </div>
</div>
