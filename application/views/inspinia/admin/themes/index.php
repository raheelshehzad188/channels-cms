<div class="row wrapper border-bottom white-bg page-heading">
    <div class="col-lg-10">
        <h2>Themes</h2>
        <ol class="breadcrumb">
            <li><a href="<?= base_url('admin/admin') ?>">Dashboard</a></li>
            <li class="active"><strong>Themes</strong></li>
        </ol>
    </div>
</div>
<div class="wrapper wrapper-content animated fadeInRight">
    <?php $this->load->view('flash'); ?>
    <div class="row">
        <?php foreach ($themes as $theme): ?>
        <div class="col-lg-4">
            <div class="ibox">
                <div class="ibox-content product-box">
                    <div class="product-imitation" style="padding:0; min-height:180px; background:#f3f3f4;">
                        <img src="<?= theme_screenshot($theme->slug) ?>" alt="<?= htmlspecialchars($theme->name) ?>" style="width:100%; height:180px; object-fit:cover;">
                    </div>
                    <div class="product-desc">
                        <span class="product-price"><?= htmlspecialchars($theme->slug) ?></span>
                        <a href="<?= base_url('admin/themes/settings/' . $theme->id) ?>" class="product-name"><?= htmlspecialchars($theme->name) ?></a>
                        <div class="small m-t-xs"><?= htmlspecialchars($theme->description) ?></div>
                        <div class="m-t text-right">
                            <a href="<?= base_url('admin/themes/settings/' . $theme->id) ?>" class="btn btn-xs btn-outline btn-primary">Add Settings</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
        <?php if (empty($themes)): ?>
        <div class="col-lg-12">
            <div class="ibox"><div class="ibox-content">No frontend themes found in <code>application/views/frontend</code>.</div></div>
        </div>
        <?php endif; ?>
    </div>
</div>
