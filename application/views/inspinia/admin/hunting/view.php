<?php
$canManage = !empty($can_manage);
$creatives = !empty($item->creatives) ? $item->creatives : array();
$suppliers = !empty($item->suppliers) ? $item->suppliers : array();
$creator = trim((string) $item->creator_name);
if ($creator === '') {
    $creator = $item->creator_uname ? $item->creator_uname : '—';
}
?>
<div class="row wrapper border-bottom white-bg page-heading">
    <div class="col-lg-8">
        <h2><?= htmlspecialchars($item->title) ?></h2>
        <ol class="breadcrumb">
            <li><a href="<?= base_url('admin/hunting') ?>">Product Hunting</a></li>
            <li class="active"><strong>View</strong></li>
        </ol>
    </div>
    <div class="col-lg-4 text-right" style="margin-top:26px;">
        <?php if ($canManage): ?>
            <a href="<?= base_url('admin/hunting/form/' . $item->id) ?>" class="btn btn-info">Edit</a>
        <?php endif; ?>
        <a href="<?= base_url('admin/hunting') ?>" class="btn btn-white">Back</a>
    </div>
</div>
<div class="wrapper wrapper-content animated fadeInRight">
    <?php $this->load->view('flash'); ?>
    <div class="row">
        <div class="col-md-4">
            <div class="ibox">
                <div class="ibox-content text-center">
                    <?php if (!empty($item->image)): ?>
                        <img src="<?= base_url($item->image) ?>" alt="" class="img-responsive" style="margin:0 auto; max-height:320px;">
                    <?php else: ?>
                        <p class="text-muted">No image</p>
                    <?php endif; ?>
                    <h3 class="m-t"><?= htmlspecialchars($item->title) ?></h3>
                    <p>
                        <span class="label label-<?= ((int) $item->status === 1) ? 'primary' : 'default' ?>"><?= ec_status_label($item->status) ?></span>
                    </p>
                    <p class="text-muted">Added by <?= htmlspecialchars($creator) ?></p>
                    <?php if (!empty($item->notes)): ?>
                        <p><?= nl2br(htmlspecialchars((string) $item->notes)) ?></p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <div class="col-md-8">
            <div class="ibox">
                <div class="ibox-title"><h5>Creatives</h5></div>
                <div class="ibox-content">
                    <?php if (empty($creatives)): ?>
                        <p class="text-muted">No creative links.</p>
                    <?php else: ?>
                        <ul class="list-unstyled m-b-none">
                            <?php foreach ($creatives as $i => $creative): ?>
                                <li style="margin-bottom:8px;">
                                    <a href="<?= htmlspecialchars($creative->link) ?>" target="_blank" rel="noopener">
                                        Creative <?= $i + 1 ?> — <?= htmlspecialchars($creative->link) ?>
                                    </a>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                </div>
            </div>
            <div class="ibox">
                <div class="ibox-title"><h5>Country suppliers</h5></div>
                <div class="ibox-content">
                    <?php if (empty($suppliers)): ?>
                        <p class="text-muted">No supplier links.</p>
                    <?php else: ?>
                        <table class="table table-striped m-b-none">
                            <thead>
                                <tr>
                                    <th>Country</th>
                                    <th>Supplier link</th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php foreach ($suppliers as $supplier): ?>
                                <tr>
                                    <td><?= htmlspecialchars(($supplier->country_name ?: 'Unknown') . ($supplier->country_code ? ' (' . $supplier->country_code . ')' : '')) ?></td>
                                    <td><a href="<?= htmlspecialchars($supplier->link) ?>" target="_blank" rel="noopener"><?= htmlspecialchars($supplier->link) ?></a></td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
