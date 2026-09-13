<div class="row wrapper border-bottom white-bg page-heading">
    <div class="col-lg-10">
        <h2>Dashboard</h2>
        <ol class="breadcrumb">
            <li class="active"><strong>Overview</strong></li>
        </ol>
    </div>
</div>
<div class="wrapper wrapper-content">
    <?php $this->load->view('flash'); ?>
    <div class="row">
        <div class="col-lg-3">
            <div class="ibox float-e-margins">
                <div class="ibox-title"><span class="label label-success pull-right">All</span><h5>Countries</h5></div>
                <div class="ibox-content">
                    <h1 class="no-margins"><?= (int) $counts['countries'] ?></h1>
                    <small>Active countries</small>
                    <div class="m-t-sm"><a href="<?= base_url('admin/countries') ?>">Manage countries</a></div>
                </div>
            </div>
        </div>
        <div class="col-lg-3">
            <div class="ibox float-e-margins">
                <div class="ibox-title"><span class="label label-info pull-right">All</span><h5>Suppliers</h5></div>
                <div class="ibox-content">
                    <h1 class="no-margins"><?= (int) $counts['suppliers'] ?></h1>
                    <small>Linked by country</small>
                    <div class="m-t-sm"><a href="<?= base_url('admin/suppliers') ?>">Manage suppliers</a></div>
                </div>
            </div>
        </div>
        <div class="col-lg-3">
            <div class="ibox float-e-margins">
                <div class="ibox-title"><span class="label label-warning pull-right">All</span><h5>Users</h5></div>
                <div class="ibox-content">
                    <h1 class="no-margins"><?= (int) $counts['users'] ?></h1>
                    <small>Admin &amp; ecommerce</small>
                    <div class="m-t-sm"><a href="<?= base_url('admin/users') ?>">Manage users</a></div>
                </div>
            </div>
        </div>
        <div class="col-lg-3">
            <div class="ibox float-e-margins">
                <div class="ibox-title"><span class="label label-primary pull-right">All</span><h5>Products</h5></div>
                <div class="ibox-content">
                    <h1 class="no-margins"><?= (int) $counts['products'] ?></h1>
                    <small>Catalog items</small>
                    <div class="m-t-sm"><a href="<?= base_url('admin/products') ?>">Manage products</a></div>
                </div>
            </div>
        </div>
    </div>
</div>
