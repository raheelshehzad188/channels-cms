<?php $isEdit = !empty($user); ?>
<div class="row wrapper border-bottom white-bg page-heading">
    <div class="col-lg-10">
        <h2><?= $isEdit ? 'Edit User' : 'Add User' ?></h2>
        <ol class="breadcrumb">
            <li><a href="<?= base_url('admin/admin') ?>">Dashboard</a></li>
            <li><a href="<?= base_url('admin/users') ?>">Users</a></li>
            <li class="active"><strong><?= $isEdit ? 'Edit' : 'Add' ?></strong></li>
        </ol>
    </div>
</div>
<div class="wrapper wrapper-content animated fadeInRight">
    <div class="row">
        <div class="col-lg-8">
            <div class="ibox float-e-margins">
                <div class="ibox-title"><h5>User Details</h5></div>
                <div class="ibox-content">
                    <?php $this->load->view('flash'); ?>
                    <form method="post" action="<?= base_url('admin/users/save' . ($isEdit ? '/' . $user->UserID : '')) ?>" class="form-horizontal">
                        <div class="form-group">
                            <label class="col-sm-3 control-label">First Name</label>
                            <div class="col-sm-9">
                                <input type="text" name="first_name" class="form-control" required value="<?= $isEdit ? htmlspecialchars($user->first_name) : '' ?>">
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-3 control-label">Last Name</label>
                            <div class="col-sm-9">
                                <input type="text" name="last_name" class="form-control" required value="<?= $isEdit ? htmlspecialchars($user->last_name) : '' ?>">
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-3 control-label">Email</label>
                            <div class="col-sm-9">
                                <input type="email" name="email" class="form-control" required value="<?= $isEdit ? htmlspecialchars($user->email) : '' ?>">
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-3 control-label">Username</label>
                            <div class="col-sm-9">
                                <input type="text" name="uname" class="form-control" required value="<?= $isEdit ? htmlspecialchars($user->uname) : '' ?>">
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-3 control-label">Password</label>
                            <div class="col-sm-9">
                                <input type="password" name="upass" class="form-control" <?= $isEdit ? '' : 'required' ?> placeholder="<?= $isEdit ? 'Leave blank to keep current password' : '' ?>">
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-3 control-label">Phone</label>
                            <div class="col-sm-9">
                                <input type="text" name="phone" class="form-control" value="<?= $isEdit ? htmlspecialchars($user->phone) : '' ?>">
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-3 control-label">Role</label>
                            <div class="col-sm-9">
                                <select name="roleID" class="form-control" required>
                                    <?php foreach ($roles as $role): ?>
                                        <option value="<?= (int) $role->roleID ?>" <?= $isEdit && (int)$user->roleID === (int)$role->roleID ? 'selected' : (!$isEdit && (int)$role->roleID === ROLE_ECOMMERCE ? 'selected' : '') ?>>
                                            <?= htmlspecialchars(ec_role_label($role->roleID)) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="form-group" id="commission-group">
                            <label class="col-sm-3 control-label">Commission (PKR)</label>
                            <div class="col-sm-9">
                                <input type="number" step="0.01" min="0" name="commission" class="form-control" value="<?= $isEdit ? htmlspecialchars($user->commission) : '0.00' ?>">
                                <span class="help-block">Added on top of the product price for store owners.</span>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-3 control-label">Status</label>
                            <div class="col-sm-9">
                                <select name="status" class="form-control">
                                    <option value="1" <?= !$isEdit || (int)$user->status === 1 ? 'selected' : '' ?>>Active</option>
                                    <option value="0" <?= $isEdit && (int)$user->status === 0 ? 'selected' : '' ?>>Inactive</option>
                                </select>
                            </div>
                        </div>
                        <div class="hr-line-dashed"></div>
                        <div class="form-group">
                            <div class="col-sm-9 col-sm-offset-3">
                                <a href="<?= base_url('admin/users') ?>" class="btn btn-white">Cancel</a>
                                <button type="submit" class="btn btn-primary"><?= $isEdit ? 'Update' : 'Save' ?></button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
(function () {
    var role = document.querySelector('select[name="roleID"]');
    var group = document.getElementById('commission-group');
    function toggle() {
        group.style.display = role && role.value === '<?= ROLE_ECOMMERCE ?>' ? 'block' : 'none';
    }
    if (role) {
        role.addEventListener('change', toggle);
        toggle();
    }
})();
</script>
