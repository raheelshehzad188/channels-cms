<div class="row wrapper border-bottom white-bg page-heading">
    <div class="col-lg-8">
        <h2>Stores</h2>
        <ol class="breadcrumb">
            <li><a href="<?= base_url('admin/admin') ?>">Dashboard</a></li>
            <li class="active"><strong>Stores</strong></li>
        </ol>
    </div>
    <div class="col-lg-4 text-right">
        <a href="<?= base_url('admin/stores/form') ?>" class="btn btn-primary" style="margin-top:26px;">Add Store</a>
    </div>
</div>
<div class="wrapper wrapper-content animated fadeInRight">
    <div class="ibox">
        <div class="ibox-title"><h5>All Stores</h5></div>
        <div class="ibox-content">
            <?php $this->load->view('flash'); ?>
            <table class="table table-striped table-bordered">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Name</th>
                        <th>Domain</th>
                        <th>Theme</th>
                        <th>Country</th>
                        <th>Login Email</th>
                        <th>Status</th>
                        <th width="260">Action</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (empty($stores)): ?>
                    <tr><td colspan="8" class="text-center">No stores yet.</td></tr>
                <?php endif; ?>
                <?php foreach ($stores as $store): ?>
                    <tr>
                        <td><?= (int) $store->id ?></td>
                        <td><?= htmlspecialchars($store->name) ?></td>
                        <td>
                            <a href="http://<?= htmlspecialchars($store->domain) ?>/" target="_blank"><?= htmlspecialchars($store->domain) ?></a>
                            <?php
                            $localHost = preg_replace('/\.ecommerce\.test$/', '.localhost', $store->domain);
                            ?>
                            <div><a class="small" href="http://<?= htmlspecialchars($localHost) ?>/" target="_blank">Open <?= htmlspecialchars($localHost) ?></a></div>
                        </td>
                        <td><?= htmlspecialchars($store->theme_name ?: '-') ?></td>
                        <td><?= htmlspecialchars($store->country_name ?: '-') ?></td>
                        <td><?= htmlspecialchars($store->email ?: '-') ?></td>
                        <td>
                            <span class="label label-<?= ((int)$store->status === 1) ? 'primary' : 'warning' ?>">
                                <?= ((int)$store->status === 1) ? 'Active' : 'Draft' ?>
                            </span>
                        </td>
                        <td>
                            <a class="btn btn-xs btn-white" href="<?= base_url('admin/stores/form/' . $store->id) ?>">Edit</a>
                            <a class="btn btn-xs btn-info" href="<?= base_url('admin/stores/theme/' . $store->id) ?>">Theme</a>
                            <a class="btn btn-xs btn-primary" href="<?= base_url('admin/stores/settings/' . $store->id) ?>">Settings</a>
                            <a class="btn btn-xs btn-danger" href="<?= base_url('admin/stores/delete/' . $store->id) ?>" onclick="return confirm('Delete this store?');">Delete</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
