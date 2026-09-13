<div class="row wrapper border-bottom white-bg page-heading">
    <div class="col-lg-8">
        <h2>Suppliers</h2>
        <ol class="breadcrumb">
            <li><a href="<?= base_url('admin/admin') ?>">Dashboard</a></li>
            <li class="active"><strong>Suppliers</strong></li>
        </ol>
    </div>
    <div class="col-lg-4 text-right">
        <a href="<?= base_url('admin/suppliers/form') ?>" class="btn btn-primary" style="margin-top:26px;">Add Supplier</a>
    </div>
</div>
<div class="wrapper wrapper-content animated fadeInRight">
    <div class="row">
        <div class="col-lg-12">
            <div class="ibox float-e-margins">
                <div class="ibox-title"><h5>All Suppliers</h5></div>
                <div class="ibox-content">
                    <?php $this->load->view('flash'); ?>
                    <div class="table-responsive">
                        <table class="table table-striped table-bordered">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Name</th>
                                    <th>Company</th>
                                    <th>Country</th>
                                    <th>Email</th>
                                    <th>Phone</th>
                                    <th>Status</th>
                                    <th width="160">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php if (empty($suppliers)): ?>
                                <tr><td colspan="8" class="text-center">No suppliers found.</td></tr>
                            <?php endif; ?>
                            <?php foreach ($suppliers as $supplier): ?>
                                <tr>
                                    <td><?= (int) $supplier->id ?></td>
                                    <td><?= htmlspecialchars($supplier->name) ?></td>
                                    <td><?= htmlspecialchars($supplier->company) ?></td>
                                    <td><?= htmlspecialchars(($supplier->country_name ?: '-') . ($supplier->country_code ? ' (' . $supplier->country_code . ')' : '')) ?></td>
                                    <td><?= htmlspecialchars($supplier->email) ?></td>
                                    <td><?= htmlspecialchars($supplier->phone) ?></td>
                                    <td>
                                        <span class="label label-<?= ((int)$supplier->status === 1) ? 'primary' : 'default' ?>">
                                            <?= ec_status_label($supplier->status) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <a class="btn btn-xs btn-info" href="<?= base_url('admin/suppliers/form/' . $supplier->id) ?>">Edit</a>
                                        <a class="btn btn-xs btn-danger" href="<?= base_url('admin/suppliers/delete/' . $supplier->id) ?>" onclick="return confirm('Delete this supplier?');">Delete</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
