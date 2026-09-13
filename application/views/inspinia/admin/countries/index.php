<div class="row wrapper border-bottom white-bg page-heading">
    <div class="col-lg-8">
        <h2>Countries</h2>
        <ol class="breadcrumb">
            <li><a href="<?= base_url('admin/admin') ?>">Dashboard</a></li>
            <li class="active"><strong>Countries</strong></li>
        </ol>
    </div>
    <div class="col-lg-4 text-right">
        <a href="<?= base_url('admin/countries/form') ?>" class="btn btn-primary" style="margin-top:26px;">Add Country</a>
    </div>
</div>
<div class="wrapper wrapper-content animated fadeInRight">
    <div class="row">
        <div class="col-lg-12">
            <div class="ibox float-e-margins">
                <div class="ibox-title"><h5>All Countries</h5></div>
                <div class="ibox-content">
                    <?php $this->load->view('flash'); ?>
                    <div class="table-responsive">
                        <table class="table table-striped table-bordered">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Name</th>
                                    <th>Code</th>
                                    <th>ISO3</th>
                                    <th>Phone</th>
                                    <th>Currency</th>
                                    <th>Status</th>
                                    <th width="160">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php if (empty($countries)): ?>
                                <tr><td colspan="8" class="text-center">No countries found.</td></tr>
                            <?php endif; ?>
                            <?php foreach ($countries as $country): ?>
                                <tr>
                                    <td><?= (int) $country->id ?></td>
                                    <td><?= htmlspecialchars($country->name) ?></td>
                                    <td><?= htmlspecialchars($country->code) ?></td>
                                    <td><?= htmlspecialchars($country->iso3) ?></td>
                                    <td><?= htmlspecialchars($country->phone_code) ?></td>
                                    <td><?= htmlspecialchars($country->currency) ?></td>
                                    <td>
                                        <span class="label label-<?= ((int)$country->status === 1) ? 'primary' : 'default' ?>">
                                            <?= ec_status_label($country->status) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <a class="btn btn-xs btn-info" href="<?= base_url('admin/countries/form/' . $country->id) ?>">Edit</a>
                                        <a class="btn btn-xs btn-danger" href="<?= base_url('admin/countries/delete/' . $country->id) ?>" onclick="return confirm('Delete this country?');">Delete</a>
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
