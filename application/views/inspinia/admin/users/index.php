<div class="row wrapper border-bottom white-bg page-heading">
    <div class="col-lg-8">
        <h2>Users</h2>
        <ol class="breadcrumb">
            <li><a href="<?= base_url('admin/admin') ?>">Dashboard</a></li>
            <li class="active"><strong>Users</strong></li>
        </ol>
    </div>
    <div class="col-lg-4 text-right">
        <a href="<?= base_url('admin/users/form') ?>" class="btn btn-primary" style="margin-top:26px;">Add User</a>
    </div>
</div>
<div class="wrapper wrapper-content animated fadeInRight">
    <div class="row">
        <div class="col-lg-12">
            <div class="ibox float-e-margins">
                <div class="ibox-title"><h5>All Users</h5></div>
                <div class="ibox-content">
                    <?php $this->load->view('flash'); ?>
                    <div class="table-responsive">
                        <table class="table table-striped table-bordered">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Name</th>
                                    <th>Username</th>
                                    <th>Email</th>
                                    <th>Role</th>
                                    <th>Commission</th>
                                    <th>Phone</th>
                                    <th>Status</th>
                                    <th width="160">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php if (empty($users)): ?>
                                <tr><td colspan="9" class="text-center">No users found.</td></tr>
                            <?php endif; ?>
                            <?php foreach ($users as $user): ?>
                                <tr>
                                    <td><?= (int) $user->UserID ?></td>
                                    <td><?= htmlspecialchars($user->first_name . ' ' . $user->last_name) ?></td>
                                    <td><?= htmlspecialchars($user->uname) ?></td>
                                    <td><?= htmlspecialchars($user->email) ?></td>
                                    <td><?= htmlspecialchars(ec_role_label($user->roleID)) ?></td>
                                    <td><?= ((int)$user->roleID === ROLE_ECOMMERCE) ? number_format((float)$user->commission, 2) : '-' ?></td>
                                    <td><?= htmlspecialchars($user->phone) ?></td>
                                    <td>
                                        <span class="label label-<?= ((int)$user->status === 1) ? 'primary' : 'default' ?>">
                                            <?= ec_status_label($user->status) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <a class="btn btn-xs btn-info" href="<?= base_url('admin/users/form/' . $user->UserID) ?>">Edit</a>
                                        <a class="btn btn-xs btn-danger" href="<?= base_url('admin/users/delete/' . $user->UserID) ?>" onclick="return confirm('Delete this user?');">Delete</a>
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
