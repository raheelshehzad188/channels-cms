<?php
$storeName = isset($store->name) ? $store->name : 'Fruitables';
$footerText = theme_setting($settings, 'footer_text', $storeName . ', All right reserved.');
$address = theme_setting($settings, 'address', '1429 Netus Rd, NY 48247');
$email = theme_setting($settings, 'email', 'example@gmail.com');
$phone = theme_setting($settings, 'phone', '+0123 4567 8910');
$homeUrl = !empty($is_preview) ? $preview_back : storefront_url('shop/index');
$shopUrl = !empty($is_preview) ? $preview_back : storefront_url('shop');
$contactUrl = !empty($is_preview) ? $preview_back : storefront_url('contact');
$payload = array(
    'page' => isset($current_page) ? $current_page : '',
    'assets' => $assets,
    'store' => $storeName,
    'urls' => array(
        'home' => $homeUrl,
        'shop' => $shopUrl,
        'contact' => $contactUrl,
        'product' => !empty($is_preview) ? base_url('admin/products/preview/') : storefront_url('product/'),
    ),
    'products' => array(),
    'product' => null,
);
if (!empty($products)) {
    foreach ($products as $item) {
        $payload['products'][] = array(
            'id' => (int) $item->id,
            'name' => $item->name,
            'price' => (float) $item->price,
            'image' => !empty($item->image) ? base_url($item->image) : $assets . 'img/fruite-item-5.jpg',
            'description' => $item->description,
            'badge' => !empty($item->supplier_name) ? $item->supplier_name : 'Fresh',
            'url' => !empty($is_preview)
                ? base_url('admin/products/preview/' . $item->id . '/fruitables')
                : product_url($item),
        );
    }
}
if (!empty($product)) {
    $payload['product'] = array(
        'id' => (int) $product->id,
        'name' => $product->name,
        'price' => (float) $product->price,
        'image' => !empty($product->image) ? base_url($product->image) : $assets . 'img/single-item.jpg',
        'description' => $product->description,
        'sku' => isset($product->sku) ? $product->sku : '',
        'stock' => isset($product->stock) ? (int) $product->stock : 0,
    );
}
?>
    <div class="container-fluid bg-dark text-white-50 footer pt-5 mt-5">
        <div class="container py-5">
            <div class="pb-4 mb-4" style="border-bottom: 1px solid rgba(226, 175, 24, 0.5);">
                <div class="row g-4">
                    <div class="col-lg-3">
                        <a href="<?= $homeUrl ?>">
                            <h1 class="text-primary mb-0"><?= htmlspecialchars($storeName) ?></h1>
                            <p class="text-secondary mb-0">Fresh products</p>
                        </a>
                    </div>
                    <div class="col-lg-6">
                        <div class="position-relative mx-auto">
                            <input class="form-control border-0 w-100 py-3 px-4 rounded-pill" type="email" placeholder="Your Email">
                            <button type="button" class="btn btn-primary border-0 border-secondary py-3 px-4 position-absolute rounded-pill text-white" style="top: 0; right: 0;">Subscribe Now</button>
                        </div>
                    </div>
                    <div class="col-lg-3">
                        <div class="d-flex justify-content-end pt-3">
                            <a class="btn btn-outline-secondary me-2 btn-md-square rounded-circle" href="#"><i class="fab fa-twitter"></i></a>
                            <a class="btn btn-outline-secondary me-2 btn-md-square rounded-circle" href="#"><i class="fab fa-facebook-f"></i></a>
                            <a class="btn btn-outline-secondary me-2 btn-md-square rounded-circle" href="#"><i class="fab fa-youtube"></i></a>
                            <a class="btn btn-outline-secondary btn-md-square rounded-circle" href="#"><i class="fab fa-linkedin-in"></i></a>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row g-5">
                <div class="col-lg-3 col-md-6">
                    <div class="footer-item">
                        <h4 class="text-light mb-3">Why People Like us!</h4>
                        <p class="mb-4"><?= htmlspecialchars(theme_setting($settings, 'about_text', 'Fresh organic fruits and vegetables delivered from our store.')) ?></p>
                        <a href="<?= $shopUrl ?>" class="btn border-secondary py-2 px-4 rounded-pill text-primary">Shop Now</a>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6">
                    <div class="d-flex flex-column text-start footer-item">
                        <h4 class="text-light mb-3">Shop Info</h4>
                        <a class="btn-link" href="<?= $homeUrl ?>">Home</a>
                        <a class="btn-link" href="<?= $contactUrl ?>">Contact Us</a>
                        <a class="btn-link" href="<?= $shopUrl ?>">Shop</a>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6">
                    <div class="d-flex flex-column text-start footer-item">
                        <h4 class="text-light mb-3">Account</h4>
                        <a class="btn-link" href="<?= $shopUrl ?>">Shopping Cart</a>
                        <a class="btn-link" href="<?= $shopUrl ?>">Wishlist</a>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6">
                    <div class="footer-item">
                        <h4 class="text-light mb-3">Contact</h4>
                        <p>Address: <?= htmlspecialchars($address) ?></p>
                        <p>Email: <?= htmlspecialchars($email) ?></p>
                        <p>Phone: <?= htmlspecialchars($phone) ?></p>
                        <p>Payment Accepted</p>
                        <img src="<?= $assets ?>img/payment.png" class="img-fluid" alt="">
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="container-fluid copyright bg-dark py-4">
        <div class="container">
            <div class="row">
                <div class="col-md-6 text-center text-md-start mb-3 mb-md-0">
                    <span class="text-light"><i class="fas fa-copyright text-light me-2"></i><?= htmlspecialchars($footerText) ?></span>
                </div>
                <div class="col-md-6 my-auto text-center text-md-end text-white">
                    Designed By <a class="border-bottom" href="https://htmlcodex.com">HTML Codex</a>
                </div>
            </div>
        </div>
    </div>

    <a href="#" class="btn btn-primary border-3 border-primary rounded-circle back-to-top"><i class="fa fa-arrow-up"></i></a>

    <script>window.FRUITABLES = <?= json_encode($payload) ?>;</script>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.4/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="<?= $assets ?>lib/easing/easing.min.js"></script>
    <script src="<?= $assets ?>lib/waypoints/waypoints.min.js"></script>
    <script src="<?= $assets ?>lib/lightbox/js/lightbox.min.js"></script>
    <script src="<?= $assets ?>lib/owlcarousel/owl.carousel.min.js"></script>
    <script src="<?= $assets ?>js/main.js"></script>
    <script src="<?= $assets ?>js/theme.js"></script>
</body>
</html>
