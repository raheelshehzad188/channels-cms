<?php
$section = isset($section) ? $section : 'products';
$tabs = array(
    'overview' => array('Overview', 'admin/product-analyzer/overview'),
    'products' => array('Products', 'admin/product-analyzer'),
    'research' => array('Supplier Research', 'admin/product-analyzer/research'),
    'analysis' => array('Analysis', 'admin/product-analyzer/analysis'),
    'ads' => array('Meta Ads', 'admin/product-analyzer/ads'),
    'scenario' => array('Scenario', 'admin/product-analyzer/scenario'),
    'settings' => array('Settings', 'admin/product-analyzer/settings'),
);
?>
<style>
.pa-nav { margin-bottom: 20px; }
.pa-nav .nav-tabs { display: flex; flex-wrap: wrap; }
.pa-nav .nav-tabs > li { float: none; }
.pa-nav .nav-tabs > li > a { padding: 8px 14px; }
.pa-metric h2 { margin: 4px 0 0; }
.pa-card-link { display: block; color: inherit; }
.pa-card-link:hover, .pa-card-link:focus { color: inherit; text-decoration: none; }
.pa-card-link .ibox { margin-bottom: 20px; }
.pa-card-link .ibox-content { min-height: 108px; }
.pa-card-active .ibox { box-shadow: inset 0 0 0 2px #1ab394; }
.pa-row-above td { background: #edfbf6; }
.pa-row-below td { background: #fff8e5; }
.pa-row-loss td { background: #fdeeee; }
.pa-status-btns { margin: 0 0 20px; }
.pa-table td, .pa-table th { white-space: nowrap; }
.pa-name { white-space: normal !important; max-width: 220px; }
.pa-bar { height: 8px; background: #e7eaec; border-radius: 4px; overflow: hidden; }
.pa-bar span { display: block; height: 100%; background: #1ab394; }
.pa-drawer .modal-dialog { width: 92%; max-width: 1100px; }
.pa-store-strip { margin-bottom: 8px; }
.pa-store-card .ibox-content { min-height: 118px; }
.pa-store-stats { margin-top: 8px; font-size: 12px; color: #676a6c; }
.pa-store-stats span { display: inline-block; margin-right: 10px; white-space: nowrap; }
.pa-store-name { margin: 0 0 4px; font-size: 15px; font-weight: 600; }
</style>
<div class="row wrapper border-bottom white-bg page-heading">
    <div class="col-lg-8">
        <h2>Product Analyzer</h2>
        <ol class="breadcrumb">
            <?php if (ec_is_admin()): ?>
            <li><a href="<?= base_url('admin/admin') ?>">Dashboard</a></li>
            <?php endif; ?>
            <li><a href="<?= base_url('admin/products') ?>">Products</a></li>
            <li class="active"><strong>Product Analyzer</strong></li>
        </ol>
    </div>
    <div class="col-lg-4 text-right" style="margin-top:26px;">
        <a href="<?= base_url('admin/product-analyzer/export/products') ?>" class="btn btn-white"><i class="fa fa-download"></i> Export products</a>
        <a href="<?= base_url('admin/product-analyzer/export/research') ?>" class="btn btn-white"><i class="fa fa-file-excel-o"></i> Research sheet</a>
    </div>
</div>
<div class="wrapper wrapper-content">
    <?php $this->load->view('flash'); ?>
    <div class="tabs-container pa-nav">
        <ul class="nav nav-tabs">
            <?php foreach ($tabs as $key => $tab): ?>
            <li class="<?= $section === $key ? 'active' : '' ?>">
                <a href="<?= base_url($tab[1]) ?>"><?= htmlspecialchars($tab[0]) ?></a>
            </li>
            <?php endforeach; ?>
        </ul>
    </div>
