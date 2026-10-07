<?php
$status = isset($status) ? $status : 'all';
$filters = array(
    'all' => 'All',
    'active' => 'Active',
    'scheduled' => 'Scheduled',
    'expired' => 'Expired',
    'disabled' => 'Disabled',
);
?>
<div class="row wrapper border-bottom white-bg page-heading">
    <div class="col-lg-8">
        <h2>Offers</h2>
        <ol class="breadcrumb">
            <li><a href="<?= base_url('admin/admin') ?>">Admin</a></li>
            <li class="active"><strong>Offers</strong></li>
        </ol>
    </div>
    <div class="col-lg-4 text-right" style="padding-top:24px;">
        <a class="btn btn-primary" href="<?= base_url('admin/offers/campaign') ?>"><i class="fa fa-bullhorn"></i> New Campaign</a>
    </div>
</div>

<div class="wrapper wrapper-content">
    <?php if (!empty($flash_success)): ?>
        <div class="alert alert-success"><?= htmlspecialchars($flash_success) ?></div>
    <?php endif; ?>
    <?php if (!empty($flash_error)): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($flash_error) ?></div>
    <?php endif; ?>

    <div class="ibox">
        <div class="ibox-title">
            <h5>Product offers</h5>
            <div class="ibox-tools">
                <?php foreach ($filters as $key => $label): ?>
                    <a class="btn btn-xs <?= $status === $key ? 'btn-primary' : 'btn-white' ?>" href="<?= base_url('admin/offers?status=' . $key) ?>"><?= $label ?></a>
                <?php endforeach; ?>
            </div>
        </div>
        <div class="ibox-content">
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>Offer</th>
                            <th>Original</th>
                            <th>Discount</th>
                            <th>Final</th>
                            <th>Start</th>
                            <th>End</th>
                            <th>Status</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if (empty($items)): ?>
                        <tr><td colspan="9" class="text-muted">No offers match this filter. Enable an offer on a product’s Offer tab, or use bulk apply below.</td></tr>
                    <?php else: ?>
                        <?php foreach ($items as $item):
                            $p = $item['product'];
                            $label = trim((string) $p->offer_label);
                            if ($label === '') {
                                $label = strtoupper(str_replace('_', ' ', (string) $p->offer_type));
                                if ($p->offer_type === 'percent') {
                                    $label = rtrim(rtrim(number_format((float) $p->offer_value, 2), '0'), '.') . '% OFF';
                                }
                            }
                        ?>
                        <tr>
                            <td>
                                <a href="<?= base_url('admin/products/form/' . (int) $p->id) ?>"><?= htmlspecialchars($p->name) ?></a>
                                <div class="text-muted small"><?= htmlspecialchars($p->sku) ?></div>
                            </td>
                            <td><?= htmlspecialchars($label) ?></td>
                            <td><?= format_money($item['original']) ?></td>
                            <td><?= $item['discount'] > 0 ? format_money($item['discount']) : '—' ?></td>
                            <td><strong><?= format_money($item['final']) ?></strong></td>
                            <td><?= !empty($p->offer_starts_at) ? htmlspecialchars($p->offer_starts_at) : '—' ?></td>
                            <td><?= !empty($p->offer_ends_at) ? htmlspecialchars($p->offer_ends_at) : '—' ?></td>
                            <td>
                                <?php
                                $badge = 'default';
                                if ($item['status'] === 'active') $badge = 'primary';
                                elseif ($item['status'] === 'scheduled') $badge = 'info';
                                elseif ($item['status'] === 'expired') $badge = 'warning';
                                elseif ($item['status'] === 'disabled') $badge = 'danger';
                                ?>
                                <span class="label label-<?= $badge ?>"><?= htmlspecialchars(ucfirst($item['status'])) ?></span>
                            </td>
                            <td class="text-right">
                                <a class="btn btn-xs btn-white" href="<?= base_url('admin/products/form/' . (int) $p->id) ?>">Edit</a>
                                <a class="btn btn-xs btn-<?= !empty($p->offer_enabled) ? 'warning' : 'primary' ?>" href="<?= base_url('admin/offers/toggle/' . (int) $p->id) ?>">
                                    <?= !empty($p->offer_enabled) ? 'Disable' : 'Enable' ?>
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="ibox">
        <div class="ibox-title"><h5>Bulk apply offer</h5></div>
        <div class="ibox-content">
            <form method="post" action="<?= base_url('admin/offers/bulk') ?>" class="form-horizontal">
                <div class="form-group">
                    <label class="col-sm-2 control-label">Products</label>
                    <div class="col-sm-9">
                        <select name="product_ids[]" class="form-control" multiple size="10" required>
                            <?php foreach ((isset($bulk_products) ? $bulk_products : array()) as $row): ?>
                                <option value="<?= (int) $row->id ?>"><?= htmlspecialchars($row->name) ?> (<?= htmlspecialchars($row->sku) ?>) — <?= format_money((float) $row->price) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <span class="help-block">Hold Ctrl/Cmd to select multiple.</span>
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-sm-2 control-label">Type</label>
                    <div class="col-sm-4">
                        <select name="offer_type" class="form-control">
                            <option value="percent">Percentage</option>
                            <option value="fixed">Fixed amount</option>
                            <option value="buy_x_get_y">Buy X Get Y</option>
                            <option value="bundle">Bundle</option>
                        </select>
                    </div>
                    <label class="col-sm-1 control-label">Value</label>
                    <div class="col-sm-3">
                        <input type="number" step="0.01" min="0" name="offer_value" class="form-control" value="20" required>
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-sm-2 control-label">Label</label>
                    <div class="col-sm-9">
                        <input type="text" name="offer_label" class="form-control" placeholder="20% OFF" value="20% OFF">
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-sm-2 control-label">Dates</label>
                    <div class="col-sm-4">
                        <input type="datetime-local" name="offer_starts_at" class="form-control">
                    </div>
                    <div class="col-sm-4">
                        <input type="datetime-local" name="offer_ends_at" class="form-control">
                    </div>
                </div>
                <div class="form-group">
                    <div class="col-sm-9 col-sm-offset-2">
                        <label class="checkbox-inline"><input type="checkbox" name="offer_show_badge" value="1" checked> Badge</label>
                        <label class="checkbox-inline"><input type="checkbox" name="offer_show_countdown" value="1"> Countdown</label>
                        <input type="hidden" name="offer_enabled" value="1">
                        <input type="hidden" name="offer_buy_qty" value="2">
                        <input type="hidden" name="offer_get_qty" value="1">
                        <input type="hidden" name="offer_bundle_qty" value="2">
                        <input type="hidden" name="offer_bundle_price" value="0">
                        <input type="hidden" name="offer_priority" value="0">
                        <button type="submit" class="btn btn-primary" style="margin-left:12px;">Apply offer</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="ibox">
        <div class="ibox-title"><h5>Campaigns</h5></div>
        <div class="ibox-content">
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Type</th>
                            <th>Value</th>
                            <th>Window</th>
                            <th>Status</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if (empty($campaigns)): ?>
                        <tr><td colspan="6" class="text-muted">No campaigns yet.</td></tr>
                    <?php else: foreach ($campaigns as $c): ?>
                        <tr>
                            <td><?= htmlspecialchars($c->name) ?><?php if ($c->label): ?><div class="text-muted small"><?= htmlspecialchars($c->label) ?></div><?php endif; ?></td>
                            <td><?= htmlspecialchars($c->discount_type) ?></td>
                            <td><?= htmlspecialchars(rtrim(rtrim(number_format((float) $c->discount_value, 2), '0'), '.')) ?></td>
                            <td class="small"><?= htmlspecialchars(($c->starts_at ?: '—') . ' → ' . ($c->ends_at ?: '—')) ?></td>
                            <td><span class="label label-<?= $c->status_label === 'active' ? 'primary' : 'default' ?>"><?= htmlspecialchars(ucfirst($c->status_label)) ?></span></td>
                            <td class="text-right">
                                <a class="btn btn-xs btn-white" href="<?= base_url('admin/offers/campaign?id=' . (int) $c->id) ?>">Edit</a>
                                <a class="btn btn-xs btn-warning" href="<?= base_url('admin/offers/campaign_toggle/' . (int) $c->id) ?>">Toggle</a>
                            </td>
                        </tr>
                    <?php endforeach; endif; ?>
                    </tbody>
                </table>
            </div>
            <p class="text-muted small" style="margin:12px 0 0;">Precedence: product-level offer wins over campaign. Coupons do not stack with active offers. Prices are always recalculated server-side.</p>
        </div>
    </div>
</div>
