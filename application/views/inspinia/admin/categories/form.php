<?php $val = function ($key, $default = '') use ($category) {
    return $category && isset($category->$key) ? $category->$key : $default;
}; ?>
<div class="row wrapper border-bottom white-bg page-heading">
    <div class="col-lg-10">
        <h2><?= htmlspecialchars($title) ?></h2>
        <ol class="breadcrumb">
            <li><a href="<?= base_url('admin/categories') ?>">Categories</a></li>
            <li class="active"><strong><?= $category ? 'Edit' : 'Add' ?></strong></li>
        </ol>
    </div>
</div>
<div class="wrapper wrapper-content">
    <?php $this->load->view('flash'); ?>
    <div class="ibox">
        <div class="ibox-content">
            <form method="post" enctype="multipart/form-data" action="<?= base_url('admin/categories/save' . ($category ? '/' . (int) $category->id : '')) ?>" class="form-horizontal">
                <div class="form-group">
                    <label class="col-sm-3 control-label">Country</label>
                    <div class="col-sm-6">
                        <select name="country_id" id="categoryCountry" class="form-control" required>
                            <option value="">Select country</option>
                            <?php foreach ($countries as $c): ?>
                                <option value="<?= (int) $c->id ?>" <?= (int) $val('country_id', $country_id) === (int) $c->id ? 'selected' : '' ?>><?= htmlspecialchars($c->name) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-sm-3 control-label">Parent category</label>
                    <div class="col-sm-6">
                        <select name="parent_id" id="categoryParent" class="form-control">
                            <option value="0">None (top-level)</option>
                            <?php foreach ($parents as $p): ?>
                                <?php if ($category && (int) $p->id === (int) $category->id) continue; ?>
                                <option value="<?= (int) $p->id ?>" <?= (int) $val('parent_id') === (int) $p->id ? 'selected' : '' ?>><?= htmlspecialchars($p->name) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <span class="help-block">Existing top-level categories for the selected country. Choose a country to load parents.</span>
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-sm-3 control-label">Name</label>
                    <div class="col-sm-6"><input type="text" name="name" class="form-control" required value="<?= htmlspecialchars($val('name')) ?>"></div>
                </div>
                <div class="form-group">
                    <label class="col-sm-3 control-label">Slug</label>
                    <div class="col-sm-6"><input type="text" name="slug" class="form-control" value="<?= htmlspecialchars($val('slug')) ?>"></div>
                </div>
                <div class="form-group">
                    <label class="col-sm-3 control-label">Icon</label>
                    <div class="col-sm-3"><input type="text" name="icon" class="form-control" value="<?= htmlspecialchars($val('icon')) ?>" placeholder="🎃"></div>
                </div>
                <div class="form-group">
                    <label class="col-sm-3 control-label">Category image</label>
                    <div class="col-sm-6">
                        <input type="file" name="image" class="form-control" accept="image/*">
                        <span class="help-block">Size: 200 × 200 px</span>
                        <?php if ($val('image')): ?><img src="<?= base_url($val('image')) ?>" style="height:64px;margin-top:8px"><?php endif; ?>
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-sm-3 control-label">Default hero image</label>
                    <div class="col-sm-6">
                        <input type="file" name="hero_image" class="form-control" accept="image/*">
                        <span class="help-block">Size: 1280 × 640 px</span>
                        <?php if ($val('hero_image')): ?><img src="<?= base_url($val('hero_image')) ?>" style="height:64px;margin-top:8px"><?php endif; ?>
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-sm-3 control-label">Hero kicker</label>
                    <div class="col-sm-6"><input type="text" name="hero_kicker" class="form-control" value="<?= htmlspecialchars($val('hero_kicker')) ?>" placeholder="Modern Living"></div>
                </div>
                <div class="form-group">
                    <label class="col-sm-3 control-label">Hero title</label>
                    <div class="col-sm-6"><textarea name="hero_title" class="form-control" rows="2" placeholder="Category headline"><?= htmlspecialchars($val('hero_title')) ?></textarea></div>
                </div>
                <div class="form-group">
                    <label class="col-sm-3 control-label">Hero text</label>
                    <div class="col-sm-6"><textarea name="hero_text" class="form-control" rows="2"><?= htmlspecialchars($val('hero_text')) ?></textarea></div>
                </div>
                <div class="form-group">
                    <label class="col-sm-3 control-label">Hero button text</label>
                    <div class="col-sm-4"><input type="text" name="hero_btn_text" class="form-control" value="<?= htmlspecialchars($val('hero_btn_text', 'Shop Now')) ?>"></div>
                </div>
                <div class="form-group">
                    <label class="col-sm-3 control-label">Hero button link</label>
                    <div class="col-sm-6"><input type="text" name="hero_btn_link" class="form-control" value="<?= htmlspecialchars($val('hero_btn_link')) ?>" placeholder="/shop or full URL or leave blank for products"></div>
                </div>
                <div class="form-group">
                    <label class="col-sm-3 control-label">Description</label>
                    <div class="col-sm-6"><textarea name="description" class="form-control" rows="3"><?= htmlspecialchars($val('description')) ?></textarea></div>
                </div>
                <div class="form-group">
                    <label class="col-sm-3 control-label">SEO title</label>
                    <div class="col-sm-6"><input type="text" name="seo_title" class="form-control" value="<?= htmlspecialchars($val('seo_title')) ?>"></div>
                </div>
                <div class="form-group">
                    <label class="col-sm-3 control-label">SEO description</label>
                    <div class="col-sm-6"><textarea name="seo_description" class="form-control" rows="2"><?= htmlspecialchars($val('seo_description')) ?></textarea></div>
                </div>
                <div class="form-group">
                    <label class="col-sm-3 control-label">SEO keywords</label>
                    <div class="col-sm-6"><input type="text" name="seo_keywords" class="form-control" value="<?= htmlspecialchars($val('seo_keywords')) ?>"></div>
                </div>
                <div class="form-group">
                    <label class="col-sm-3 control-label">Sort order</label>
                    <div class="col-sm-2"><input type="number" name="sort_order" class="form-control" value="<?= (int) $val('sort_order', 0) ?>"></div>
                </div>
                <div class="form-group">
                    <label class="col-sm-3 control-label">Status</label>
                    <div class="col-sm-3">
                        <select name="status" class="form-control">
                            <option value="1" <?= (int) $val('status', 1) === 1 ? 'selected' : '' ?>>Active</option>
                            <option value="0" <?= (int) $val('status', 1) === 0 ? 'selected' : '' ?>>Inactive</option>
                        </select>
                    </div>
                </div>
                <div class="form-group">
                    <div class="col-sm-6 col-sm-offset-3">
                        <button type="submit" class="btn btn-primary">Save</button>
                        <a href="<?= base_url('admin/categories') ?>" class="btn btn-white">Cancel</a>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
<script>
(function ($) {
    var parentsUrl = <?= json_encode(base_url('admin/categories/parents')) ?>;
    var excludeId = <?= json_encode($category ? (int) $category->id : 0) ?>;
    $('#categoryCountry').on('change', function () {
        var countryId = $(this).val();
        var $parent = $('#categoryParent');
        var current = $parent.val();
        $parent.find('option:not(:first)').remove();
        if (!countryId) {
            return;
        }
        $.getJSON(parentsUrl, { country_id: countryId, exclude_id: excludeId })
            .done(function (items) {
                $.each(items || [], function (_, item) {
                    $parent.append($('<option>', { value: item.id, text: item.name }));
                });
                if (current) {
                    $parent.val(current);
                }
            });
    });
})(jQuery);
</script>
