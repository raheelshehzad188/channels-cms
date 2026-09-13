<?php
$homeUrl = !empty($is_preview) ? $preview_back : storefront_url('shop/index');
$shopUrl = !empty($is_preview) ? $preview_back : storefront_url('shop');
$gallery = product_gallery_urls($product, isset($product_images) ? $product_images : array());
$image = !empty($gallery) ? $gallery[0] : $assets . 'img/single-item.jpg';
$related = !empty($products) ? $products : array();
?>
<div class="container-fluid page-header py-5">
    <h1 class="text-center text-white display-6">Shop Detail</h1>
    <ol class="breadcrumb justify-content-center mb-0">
        <li class="breadcrumb-item"><a href="<?= $homeUrl ?>">Home</a></li>
        <li class="breadcrumb-item"><a href="<?= $shopUrl ?>">Shop</a></li>
        <li class="breadcrumb-item active text-white"><?= htmlspecialchars($product->name) ?></li>
    </ol>
</div>

<div class="container-fluid py-5">
    <div class="container py-5">
        <div class="row g-4 mb-5">
            <div class="col-lg-8 col-xl-9">
                <div class="row g-4">
                    <div class="col-lg-6">
                        <div class="border rounded">
                            <a href="<?= htmlspecialchars($image) ?>" data-lightbox="product">
                                <img src="<?= htmlspecialchars($image) ?>" class="img-fluid rounded" alt="<?= htmlspecialchars($product->name) ?>">
                            </a>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <h4 class="fw-bold mb-3"><?= htmlspecialchars($product->name) ?></h4>
                        <p class="mb-3">SKU: <?= htmlspecialchars(!empty($product->sku) ? $product->sku : '-') ?></p>
                        <h5 class="fw-bold mb-3"><?= format_money((float) $product->price) ?></h5>
                        <div class="d-flex mb-4">
                            <i class="fa fa-star text-secondary"></i>
                            <i class="fa fa-star text-secondary"></i>
                            <i class="fa fa-star text-secondary"></i>
                            <i class="fa fa-star text-secondary"></i>
                            <i class="fa fa-star"></i>
                        </div>
                        <p class="mb-4"><?= nl2br(htmlspecialchars($product->description ?: 'A fresh organic item from our Fruitables collection.')) ?></p>
                        <p class="mb-4">Supplier: <?= htmlspecialchars(!empty($product->supplier_name) ? $product->supplier_name : '-') ?> · Stock: <?= (int) $product->stock ?></p>
                        <?php $this->load->view('frontend/shared/shipping_eta', array('product' => $product)); ?>
                        <div class="input-group quantity mb-5" style="width: 100px;">
                            <div class="input-group-btn">
                                <button class="btn btn-sm btn-minus rounded-circle bg-light border" type="button"><i class="fa fa-minus"></i></button>
                            </div>
                            <input type="text" class="form-control form-control-sm text-center border-0" value="1">
                            <div class="input-group-btn">
                                <button class="btn btn-sm btn-plus rounded-circle bg-light border" type="button"><i class="fa fa-plus"></i></button>
                            </div>
                        </div>
                        <a href="<?= $shopUrl ?>" class="btn border border-secondary rounded-pill px-4 py-2 mb-4 text-primary">
                            <i class="fa fa-shopping-bag me-2 text-primary"></i> <?= !empty($is_preview) ? 'Back to products' : 'Back to shop' ?>
                        </a>
                    </div>
                    <div class="col-lg-12">
                        <nav>
                            <div class="nav nav-tabs mb-3">
                                <button class="nav-link active border-white border-bottom-0" type="button" role="tab">Description</button>
                            </div>
                        </nav>
                        <div class="tab-content mb-5">
                            <div class="tab-pane active">
                                <?php $this->load->view('frontend/shared/product_details', array(
                                    'product' => $product,
                                    'fallback' => $product->description ?: 'Fresh produce selected for this store.',
                                )); ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-4 col-xl-3">
                <div class="row g-4 fruite">
                    <div class="col-lg-12">
                        <div class="mb-4">
                            <h4>Categories</h4>
                            <ul class="list-unstyled fruite-categorie">
                                <li>
                                    <div class="d-flex justify-content-between fruite-name">
                                        <a href="<?= $shopUrl ?>"><i class="fas fa-apple-alt me-2"></i>All Products</a>
                                    </div>
                                </li>
                            </ul>
                        </div>
                    </div>
                    <div class="col-lg-12">
                        <div class="position-relative">
                            <img src="<?= $assets ?>img/banner-fruits.jpg" class="img-fluid w-100 rounded" alt="">
                            <div class="position-absolute" style="top: 50%; right: 10px; transform: translateY(-50%);">
                                <h3 class="text-secondary fw-bold">Fresh <br> Fruits <br> Banner</h3>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php if (!empty($related)): ?>
        <h1 class="fw-bold mb-0">Related products</h1>
        <div class="vesitable">
            <div class="owl-carousel vegetable-carousel justify-content-center">
                <?php foreach ($related as $item): ?>
                    <?php if ((int) $item->id === (int) $product->id) continue; ?>
                    <?php $this->load->view('frontend/fruitables/product_card', array(
                        'product' => $item,
                        'card_skip_col' => true,
                        'card_item_class' => 'vesitable-item border border-primary',
                        'card_img_class' => 'vesitable-img',
                        'card_badge' => !empty($item->supplier_name) ? $item->supplier_name : 'Fresh',
                        'card_badge_class' => 'bg-primary',
                        'card_badge_pos' => 'top: 10px; right: 10px;',
                    )); ?>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>
