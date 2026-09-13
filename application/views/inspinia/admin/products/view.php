<div class="row wrapper border-bottom white-bg page-heading">
    <div class="col-lg-8">
        <h2>Product Detail</h2>
        <ol class="breadcrumb">
            <li><a href="<?= base_url('admin/products') ?>">Products</a></li>
            <li class="active"><strong><?= htmlspecialchars($product->name) ?></strong></li>
        </ol>
    </div>
    <div class="col-lg-4 text-right">
        <a href="<?= base_url('admin/products/form/' . $product->id) ?>" class="btn btn-primary" style="margin-top:26px;">Edit Product</a>
    </div>
</div>
<div class="wrapper wrapper-content animated fadeInRight">
    <div class="row">
        <div class="col-lg-8">
            <div class="ibox float-e-margins">
                <div class="ibox-title"><h5><?= htmlspecialchars($product->name) ?></h5></div>
                <div class="ibox-content">
                    <?php $this->load->view('flash'); ?>
                    <?php if (!empty($product->image)): ?>
                        <p><img src="<?= base_url($product->image) ?>" alt="" class="img-responsive" style="max-height:220px;"></p>
                    <?php endif; ?>
                    <table class="table table-bordered">
                        <tr><th width="180">SKU</th><td><?= htmlspecialchars($product->sku ?: '-') ?></td></tr>
                        <tr><th>Price</th><td><?= format_money((float) $product->price, product_currency($product)) ?></td></tr>
                        <tr><th>Stock</th><td><?= (int) $product->stock ?></td></tr>
                        <tr><th>Supplier</th><td><?= htmlspecialchars($product->supplier_name ?: '-') ?></td></tr>
                        <tr><th>Country</th><td><?= htmlspecialchars($product->country_name ?: '-') ?></td></tr>
                        <tr>
                            <th>Status</th>
                            <td>
                                <span class="label label-<?= ((int)$product->status === 1) ? 'primary' : 'default' ?>">
                                    <?= ec_status_label($product->status) ?>
                                </span>
                            </td>
                        </tr>
                        <tr><th>Description</th><td><?= nl2br(htmlspecialchars($product->description ?: '-')) ?></td></tr>
                        <?php if (!empty($product->details)): ?>
                        <tr><th>Details</th><td><div class="product-html"><?= ec_product_details_html($product) ?></div></td></tr>
                        <?php endif; ?>
                        <tr><th>Created</th><td><?= htmlspecialchars($product->created_at) ?></td></tr>
                        <tr><th>Updated</th><td><?= htmlspecialchars($product->updated_at) ?></td></tr>
                    </table>
                    <a href="<?= base_url('admin/products') ?>" class="btn btn-white">Back to list</a>
                </div>
            </div>
        </div>
    </div>
</div>
