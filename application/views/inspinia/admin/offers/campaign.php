<?php
$c = isset($campaign) ? $campaign : null;
$isEdit = !empty($c);
$type = $isEdit ? $c->discount_type : 'percent';
$starts = ($isEdit && $c->starts_at) ? date('Y-m-d\TH:i', strtotime($c->starts_at)) : '';
$ends = ($isEdit && $c->ends_at) ? date('Y-m-d\TH:i', strtotime($c->ends_at)) : '';
$productIds = isset($product_ids) ? $product_ids : array();
$categoryIds = isset($category_ids) ? $category_ids : array();
?>
<div class="row wrapper border-bottom white-bg page-heading">
    <div class="col-lg-10">
        <h2><?= $isEdit ? 'Edit Campaign' : 'New Campaign' ?></h2>
        <ol class="breadcrumb">
            <li><a href="<?= base_url('admin/offers') ?>">Offers</a></li>
            <li class="active"><strong><?= $isEdit ? 'Edit' : 'New' ?></strong></li>
        </ol>
    </div>
</div>
<div class="wrapper wrapper-content">
    <?php if (!empty($flash_error)): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($flash_error) ?></div>
    <?php endif; ?>
    <div class="ibox">
        <div class="ibox-content">
            <form method="post" action="<?= base_url('admin/offers/campaign_save') ?>" class="form-horizontal">
                <input type="hidden" name="id" value="<?= $isEdit ? (int) $c->id : 0 ?>">
                <div class="form-group">
                    <label class="col-sm-2 control-label">Campaign Name</label>
                    <div class="col-sm-9">
                        <input type="text" name="name" class="form-control" required value="<?= htmlspecialchars($isEdit ? $c->name : '') ?>" placeholder="HALLOWEEN SALE">
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-sm-2 control-label">Label</label>
                    <div class="col-sm-9">
                        <input type="text" name="label" class="form-control" value="<?= htmlspecialchars($isEdit ? $c->label : '') ?>" placeholder="UP TO 30% OFF">
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-sm-2 control-label">Store</label>
                    <div class="col-sm-9">
                        <select name="store_id" class="form-control">
                            <option value="0">All stores</option>
                            <?php foreach ($stores as $s): ?>
                                <option value="<?= (int) $s->id ?>" <?= $isEdit && (int) $c->store_id === (int) $s->id ? 'selected' : '' ?>><?= htmlspecialchars($s->name) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-sm-2 control-label">Discount Type</label>
                    <div class="col-sm-4">
                        <select name="discount_type" class="form-control">
                            <option value="percent" <?= $type === 'percent' ? 'selected' : '' ?>>Percentage</option>
                            <option value="fixed" <?= $type === 'fixed' ? 'selected' : '' ?>>Fixed amount</option>
                            <option value="buy_x_get_y" <?= $type === 'buy_x_get_y' ? 'selected' : '' ?>>Buy X Get Y</option>
                            <option value="bundle" <?= $type === 'bundle' ? 'selected' : '' ?>>Bundle</option>
                        </select>
                    </div>
                    <label class="col-sm-1 control-label">Value</label>
                    <div class="col-sm-3">
                        <input type="number" step="0.01" name="discount_value" class="form-control" value="<?= htmlspecialchars($isEdit ? $c->discount_value : '20') ?>">
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-sm-2 control-label">Buy X / Get Y</label>
                    <div class="col-sm-4"><input type="number" name="buy_qty" class="form-control" value="<?= $isEdit ? (int) $c->buy_qty : 2 ?>"></div>
                    <div class="col-sm-4"><input type="number" name="get_qty" class="form-control" value="<?= $isEdit ? (int) $c->get_qty : 1 ?>"></div>
                </div>
                <div class="form-group">
                    <label class="col-sm-2 control-label">Bundle qty / price</label>
                    <div class="col-sm-4"><input type="number" name="bundle_qty" class="form-control" value="<?= $isEdit ? (int) $c->bundle_qty : 2 ?>"></div>
                    <div class="col-sm-4"><input type="number" step="0.01" name="bundle_price" class="form-control" value="<?= htmlspecialchars($isEdit ? $c->bundle_price : '0') ?>"></div>
                </div>
                <div class="form-group">
                    <label class="col-sm-2 control-label">Start / End</label>
                    <div class="col-sm-4"><input type="datetime-local" name="starts_at" class="form-control" value="<?= htmlspecialchars($starts) ?>"></div>
                    <div class="col-sm-4"><input type="datetime-local" name="ends_at" class="form-control" value="<?= htmlspecialchars($ends) ?>"></div>
                </div>
                <div class="form-group">
                    <label class="col-sm-2 control-label">Priority</label>
                    <div class="col-sm-3"><input type="number" name="priority" class="form-control" value="<?= $isEdit ? (int) $c->priority : 0 ?>"></div>
                </div>
                <div class="form-group">
                    <label class="col-sm-2 control-label">Products</label>
                    <div class="col-sm-9">
                        <select name="product_ids[]" class="form-control" multiple size="8">
                            <?php foreach ($products as $p): ?>
                                <option value="<?= (int) $p->id ?>" <?= in_array((int) $p->id, $productIds, true) ? 'selected' : '' ?>><?= htmlspecialchars($p->name) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <span class="help-block">Leave empty (with no categories) to apply store-wide.</span>
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-sm-2 control-label">Categories</label>
                    <div class="col-sm-9">
                        <select name="category_ids[]" class="form-control" multiple size="6">
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?= (int) $cat->id ?>" <?= in_array((int) $cat->id, $categoryIds, true) ? 'selected' : '' ?>><?= htmlspecialchars($cat->name) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="form-group">
                    <div class="col-sm-9 col-sm-offset-2">
                        <label class="checkbox-inline"><input type="checkbox" name="show_badge" value="1" <?= !$isEdit || $c->show_badge ? 'checked' : '' ?>> Badge</label>
                        <label class="checkbox-inline"><input type="checkbox" name="show_countdown" value="1" <?= $isEdit && $c->show_countdown ? 'checked' : '' ?>> Countdown</label>
                        <label class="checkbox-inline"><input type="checkbox" name="status" value="1" <?= !$isEdit || $c->status ? 'checked' : '' ?>> Active</label>
                    </div>
                </div>
                <div class="form-group">
                    <div class="col-sm-9 col-sm-offset-2">
                        <button type="submit" class="btn btn-primary">Save campaign</button>
                        <a class="btn btn-white" href="<?= base_url('admin/offers') ?>">Cancel</a>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
