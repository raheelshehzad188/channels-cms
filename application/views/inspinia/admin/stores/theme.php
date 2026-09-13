<div class="row wrapper border-bottom white-bg page-heading">
    <div class="col-lg-10">
        <h2>Select Theme for <?= htmlspecialchars($store->name) ?></h2>
        <ol class="breadcrumb">
            <li><a href="<?= base_url('admin/stores') ?>">Stores</a></li>
            <li class="active"><strong>Theme</strong></li>
        </ol>
    </div>
</div>
<div class="wrapper wrapper-content">
    <?php $this->load->view('flash'); ?>
    <form method="post" action="<?= base_url('admin/stores/save_theme/' . $store->id) ?>">
        <div class="row">
            <?php foreach ($themes as $theme): ?>
            <div class="col-lg-4">
                <div class="ibox">
                    <div class="ibox-content product-box">
                        <div class="product-imitation" style="padding:0;">
                            <img src="<?= theme_screenshot($theme->slug) ?>" alt="" style="width:100%; height:180px; object-fit:cover;">
                        </div>
                        <div class="product-desc">
                            <label class="product-name" style="cursor:pointer;">
                                <input type="radio" name="theme_id" value="<?= (int) $theme->id ?>" <?= ((int)$store->theme_id === (int)$theme->id) ? 'checked' : '' ?> required>
                                <?= htmlspecialchars($theme->name) ?>
                            </label>
                            <div class="small m-t-xs"><?= htmlspecialchars($theme->description) ?></div>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <button type="submit" class="btn btn-primary">Next: Theme Settings</button>
        <a href="<?= base_url('admin/stores/form/' . $store->id) ?>" class="btn btn-white">Back</a>
    </form>
</div>
