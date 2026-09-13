<?php
$homeUrl = !empty($is_preview) ? $preview_back : storefront_url('shop/index');
$featured = !empty($products) ? array_slice($products, 0, 3) : array();
?>
<div class="container-fluid page-header py-5">
    <h1 class="text-center text-white display-6">Shop</h1>
    <ol class="breadcrumb justify-content-center mb-0">
        <li class="breadcrumb-item"><a href="<?= $homeUrl ?>">Home</a></li>
        <li class="breadcrumb-item active text-white">Shop</li>
    </ol>
</div>

<div class="container-fluid fruite py-5">
    <div class="container py-5">
        <h1 class="mb-4">Fresh fruits shop</h1>
        <div class="row g-4">
            <div class="col-lg-12">
                <div class="row g-4">
                    <div class="col-xl-3">
                        <form class="input-group w-100 mx-auto d-flex" method="get">
                            <input type="search" name="q" class="form-control p-3" placeholder="keywords" value="<?= htmlspecialchars($this->input->get('q')) ?>">
                            <button class="input-group-text p-3" type="submit"><i class="fa fa-search"></i></button>
                        </form>
                    </div>
                    <div class="col-6"></div>
                    <div class="col-xl-3">
                        <div class="bg-light ps-3 py-3 rounded d-flex justify-content-between mb-4">
                            <label for="fruits">Default Sorting:</label>
                            <select id="fruits" class="border-0 form-select-sm bg-light me-3">
                                <option>Nothing</option>
                                <option>Popularity</option>
                                <option>Organic</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="row g-4">
                    <div class="col-lg-3">
                        <div class="row g-4">
                            <div class="col-lg-12">
                                <div class="mb-3">
                                    <h4>Categories</h4>
                                    <ul class="list-unstyled fruite-categorie">
                                        <li>
                                            <div class="d-flex justify-content-between fruite-name">
                                                <a href="#"><i class="fas fa-apple-alt me-2"></i>All Products</a>
                                                <span>(<?= count($products) ?>)</span>
                                            </div>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                            <div class="col-lg-12">
                                <h4 class="mb-3">Featured products</h4>
                                <?php foreach ($featured as $item): ?>
                                    <?php $featImg = !empty($item->image) ? base_url($item->image) : $assets . 'img/featur-1.jpg'; ?>
                                    <div class="d-flex align-items-center justify-content-start mb-3">
                                        <div class="rounded me-4" style="width: 100px; height: 100px;">
                                            <img src="<?= htmlspecialchars($featImg) ?>" class="img-fluid rounded" alt="">
                                        </div>
                                        <div>
                                            <h6 class="mb-2"><?= htmlspecialchars($item->name) ?></h6>
                                            <div class="d-flex mb-2">
                                                <h5 class="fw-bold me-2"><?= format_money((float) $item->price) ?></h5>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
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
                    <div class="col-lg-9">
                        <div class="row g-4 justify-content-center">
                            <?php if (empty($products)): ?>
                                <p>No products available.</p>
                            <?php else: ?>
                                <?php foreach ($products as $product): ?>
                                    <?php $this->load->view('frontend/fruitables/product_card', array('product' => $product, 'card_col' => 'col-md-6 col-lg-6 col-xl-4')); ?>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
