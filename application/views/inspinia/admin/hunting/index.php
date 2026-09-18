<?php
$canManage = !empty($can_manage);
$filterQ = isset($q) ? $q : '';
$filterStatus = isset($status) ? $status : '';
$filterCountryId = isset($country_id) ? (int) $country_id : 0;
?>
<div class="row wrapper border-bottom white-bg page-heading">
    <div class="col-lg-8">
        <h2>Product Hunting</h2>
        <ol class="breadcrumb">
            <?php if (ec_is_admin()): ?>
            <li><a href="<?= base_url('admin/admin') ?>">Dashboard</a></li>
            <?php endif; ?>
            <li class="active"><strong>Product Hunting</strong></li>
        </ol>
    </div>
    <?php if ($canManage): ?>
    <div class="col-lg-4 text-right">
        <a href="<?= base_url('admin/hunting/form') ?>" class="btn btn-primary" style="margin-top:26px;">Add Hunting Product</a>
    </div>
    <?php endif; ?>
</div>
<div class="wrapper wrapper-content animated fadeInRight">
    <?php $this->load->view('flash'); ?>
    <div class="ibox">
        <div class="ibox-title"><h5>Filters</h5></div>
        <div class="ibox-content">
            <form method="get" action="<?= base_url('admin/hunting') ?>" class="form-inline">
                <div class="form-group" style="margin-right:8px; margin-bottom:8px;">
                    <input type="text" name="q" class="form-control" placeholder="Search title…" value="<?= htmlspecialchars($filterQ) ?>" style="min-width:220px;">
                </div>
                <div class="form-group" style="margin-right:8px; margin-bottom:8px;">
                    <select name="status" class="form-control">
                        <option value="">All statuses</option>
                        <option value="1" <?= $filterStatus === 1 ? 'selected' : '' ?>>Active</option>
                        <option value="0" <?= $filterStatus === 0 ? 'selected' : '' ?>>Inactive</option>
                    </select>
                </div>
                <div class="form-group" style="margin-right:8px; margin-bottom:8px;">
                    <select name="country_id" class="form-control">
                        <option value="0">All supplier countries</option>
                        <?php foreach (!empty($countries) ? $countries : array() as $country): ?>
                            <option value="<?= (int) $country->id ?>" <?= $filterCountryId === (int) $country->id ? 'selected' : '' ?>>
                                <?= htmlspecialchars($country->name) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <button type="submit" class="btn btn-primary" style="margin-bottom:8px;">Filter</button>
                <a href="<?= base_url('admin/hunting') ?>" class="btn btn-white" style="margin-bottom:8px;">Reset</a>
            </form>
        </div>
    </div>
    <div class="ibox">
        <div class="ibox-title"><h5>All hunting products</h5></div>
        <div class="ibox-content">
            <div class="table-responsive">
                <table class="table table-striped table-bordered">
                    <thead>
                        <tr>
                            <th width="70">Image</th>
                            <th>Title</th>
                            <th>Creatives</th>
                            <th>Suppliers</th>
                            <th>Added by</th>
                            <th>Status</th>
                            <th width="180">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if (empty($items)): ?>
                        <tr><td colspan="7" class="text-center">No hunting products yet.</td></tr>
                    <?php endif; ?>
                    <?php foreach ($items as $item): ?>
                        <?php
                        $creator = trim((string) $item->creator_name);
                        if ($creator === '') {
                            $creator = $item->creator_uname ? $item->creator_uname : '—';
                        }
                        ?>
                        <tr>
                            <td>
                                <?php if (!empty($item->image)): ?>
                                    <img src="<?= base_url($item->image) ?>" alt="" style="width:48px;height:48px;object-fit:cover;border-radius:4px;">
                                <?php else: ?>
                                    —
                                <?php endif; ?>
                            </td>
                            <td><?= htmlspecialchars($item->title) ?></td>
                            <td><?= (int) $item->creative_count ?></td>
                            <td><?= (int) $item->supplier_count ?></td>
                            <td><?= htmlspecialchars($creator) ?></td>
                            <td>
                                <span class="label label-<?= ((int) $item->status === 1) ? 'primary' : 'default' ?>">
                                    <?= ec_status_label($item->status) ?>
                                </span>
                            </td>
                            <td>
                                <a class="btn btn-xs btn-white" href="<?= base_url('admin/hunting/view/' . $item->id) ?>">View</a>
                                <?php if ($canManage): ?>
                                    <a class="btn btn-xs btn-info" href="<?= base_url('admin/hunting/form/' . $item->id) ?>">Edit</a>
                                    <a class="btn btn-xs btn-danger" href="<?= base_url('admin/hunting/delete/' . $item->id) ?>" onclick="return confirm('Delete this hunting product?');">Delete</a>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
