<div class="row wrapper border-bottom white-bg page-heading">
    <div class="col-lg-8">
        <h2>Shipping Couriers</h2>
        <ol class="breadcrumb">
            <li><a href="<?= base_url('admin/admin') ?>">Dashboard</a></li>
            <li class="active"><strong>Couriers</strong></li>
        </ol>
    </div>
    <div class="col-lg-4 text-right">
        <a href="<?= base_url('admin/couriers/form' . (!empty($country_id) ? '?country_id=' . (int) $country_id : '')) ?>" class="btn btn-primary" style="margin-top:26px;">Add Courier</a>
    </div>
</div>
<div class="wrapper wrapper-content animated fadeInRight">
    <div class="row">
        <div class="col-lg-12">
            <div class="ibox float-e-margins">
                <div class="ibox-title"><h5>Couriers by country</h5></div>
                <div class="ibox-content">
                    <?php $this->load->view('flash'); ?>
                    <form method="get" action="<?= base_url('admin/couriers') ?>" class="form-inline" style="margin-bottom:16px">
                        <label>Country</label>
                        <select name="country_id" class="form-control" onchange="this.form.submit()">
                            <option value="0">All countries</option>
                            <?php foreach ($countries as $country): ?>
                                <option value="<?= (int) $country->id ?>" <?= ((int) $country_id === (int) $country->id) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($country->name) ?> (<?= htmlspecialchars($country->code) ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </form>
                    <div class="table-responsive">
                        <table class="table table-striped table-bordered">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Country</th>
                                    <th>Courier</th>
                                    <th>Tracking URL</th>
                                    <th>Sort</th>
                                    <th>Status</th>
                                    <th width="160">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php if (empty($couriers)): ?>
                                <tr><td colspan="7" class="text-center">No couriers yet. Add the first one for this country.</td></tr>
                            <?php endif; ?>
                            <?php foreach ($couriers as $courier): ?>
                                <tr>
                                    <td><?= (int) $courier->id ?></td>
                                    <td><?= htmlspecialchars(trim((isset($courier->country_name) ? $courier->country_name : '') . (isset($courier->country_code) && $courier->country_code ? ' (' . $courier->country_code . ')' : ''))) ?></td>
                                    <td><?= htmlspecialchars($courier->name) ?></td>
                                    <td class="small"><?= htmlspecialchars($courier->tracking_url) ?></td>
                                    <td><?= (int) $courier->sort_order ?></td>
                                    <td>
                                        <span class="label label-<?= ((int) $courier->status === 1) ? 'primary' : 'default' ?>">
                                            <?= ((int) $courier->status === 1) ? 'Active' : 'Inactive' ?>
                                        </span>
                                    </td>
                                    <td>
                                        <a class="btn btn-xs btn-info" href="<?= base_url('admin/couriers/form/' . $courier->id) ?>">Edit</a>
                                        <a class="btn btn-xs btn-danger" href="<?= base_url('admin/couriers/delete/' . $courier->id) ?>" onclick="return confirm('Delete this courier?');">Delete</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <p class="text-muted">Each store country shows only its own couriers when an order is shipped.</p>
                </div>
            </div>
        </div>
    </div>
</div>
