<?php
$primary = theme_setting($settings, 'primary_color', '#81C408');
$secondary = theme_setting($settings, 'secondary_color', '#FFB524');
$logo = theme_setting($settings, 'logo');
$address = theme_setting($settings, 'address', '123 Street, New York');
$email = theme_setting($settings, 'email', 'email@example.com');
$storeName = isset($store->name) ? $store->name : 'Fruitables';
$page = isset($current_page) ? $current_page : '';
$homeUrl = !empty($is_preview) ? $preview_back : storefront_url('shop/index');
$shopUrl = !empty($is_preview) ? $preview_back : storefront_url('shop');
$contactUrl = !empty($is_preview) ? $preview_back : storefront_url('contact');
$cartUrl = !empty($is_preview) ? $preview_back : storefront_url('cart');
$accountUrl = !empty($is_preview) ? $preview_back : storefront_url(storefront_customer() ? 'account' : 'account/login');
$cartCount = isset($cart_count) ? (int) $cart_count : storefront_cart_count(isset($store->id) ? $store->id : 0);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <title><?= htmlspecialchars(isset($title) ? $title : $storeName) ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@400;600&family=Raleway:wght@600;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.15.4/css/all.css"/>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.4.1/font/bootstrap-icons.css" rel="stylesheet">
    <link href="<?= $assets ?>lib/lightbox/css/lightbox.min.css" rel="stylesheet">
    <link href="<?= $assets ?>lib/owlcarousel/assets/owl.carousel.min.css" rel="stylesheet">
    <link href="<?= $assets ?>css/bootstrap.min.css" rel="stylesheet">
    <link href="<?= $assets ?>css/style.css" rel="stylesheet">
    <?php $this->load->view('frontend/shared/custom_css'); ?>
    <style>
        :root {
            --bs-primary: <?= htmlspecialchars($primary) ?>;
            --bs-secondary: <?= htmlspecialchars($secondary) ?>;
        }
    </style>
</head>
<body data-theme-page="<?= htmlspecialchars($page) ?>">
    <div id="spinner" class="show w-100 vh-100 bg-white position-fixed translate-middle top-50 start-50 d-flex align-items-center justify-content-center">
        <div class="spinner-grow text-primary" role="status"></div>
    </div>

    <div class="container-fluid fixed-top">
        <div class="container topbar bg-primary d-none d-lg-block">
            <div class="d-flex justify-content-between">
                <div class="top-info ps-2">
                    <small class="me-3"><i class="fas fa-map-marker-alt me-2 text-secondary"></i> <a href="#" class="text-white"><?= htmlspecialchars($address) ?></a></small>
                    <small class="me-3"><i class="fas fa-envelope me-2 text-secondary"></i><a href="mailto:<?= htmlspecialchars($email) ?>" class="text-white"><?= htmlspecialchars($email) ?></a></small>
                </div>
                <div class="top-link pe-2">
                    <a href="<?= $contactUrl ?>" class="text-white"><small class="text-white mx-2">Privacy Policy</small>/</a>
                    <a href="<?= $contactUrl ?>" class="text-white"><small class="text-white mx-2">Terms of Use</small>/</a>
                    <a href="<?= $shopUrl ?>" class="text-white"><small class="text-white ms-2">Sales and Refunds</small></a>
                </div>
            </div>
        </div>
        <div class="container px-0">
            <nav class="navbar navbar-light bg-white navbar-expand-xl">
                <a href="<?= $homeUrl ?>" class="navbar-brand">
                    <?php if ($logo): ?>
                        <img src="<?= base_url($logo) ?>" alt="<?= htmlspecialchars($storeName) ?>" style="max-height:48px">
                    <?php else: ?>
                        <h1 class="text-primary display-6 mb-0"><?= htmlspecialchars($storeName) ?></h1>
                    <?php endif; ?>
                </a>
                <button class="navbar-toggler py-2 px-3" type="button" data-bs-toggle="collapse" data-bs-target="#navbarCollapse">
                    <span class="fa fa-bars text-primary"></span>
                </button>
                <div class="collapse navbar-collapse bg-white" id="navbarCollapse">
                    <div class="navbar-nav mx-auto">
                        <?php if (!empty($is_preview)): ?>
                            <a href="<?= $preview_back ?>" class="nav-item nav-link">Back to products</a>
                        <?php else: ?>
                            <a href="<?= $homeUrl ?>" class="nav-item nav-link <?= $page === 'home' ? 'active' : '' ?>">Home</a>
                            <a href="<?= $shopUrl ?>" class="nav-item nav-link <?= $page === 'shop' ? 'active' : '' ?>">Shop</a>
                            <a href="<?= $contactUrl ?>" class="nav-item nav-link <?= $page === 'contact' ? 'active' : '' ?>">Contact</a>
                        <?php endif; ?>
                    </div>
                    <div class="d-flex m-3 me-0">
                        <button class="btn-search btn border border-secondary btn-md-square rounded-circle bg-white me-4" data-bs-toggle="modal" data-bs-target="#searchModal"><i class="fas fa-search text-primary"></i></button>
                        <a href="<?= $cartUrl ?>" class="position-relative me-4 my-auto">
                            <i class="fa fa-shopping-bag fa-2x"></i>
                            <span class="position-absolute bg-secondary rounded-circle d-flex align-items-center justify-content-center text-dark px-1" style="top: -5px; left: 15px; height: 20px; min-width: 20px;"><?= $cartCount ?></span>
                        </a>
                        <a href="<?= $accountUrl ?>" class="my-auto" title="Account"><i class="fas fa-user fa-2x"></i></a>
                    </div>
                </div>
            </nav>
        </div>
    </div>

    <div class="modal fade" id="searchModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-fullscreen">
            <div class="modal-content rounded-0">
                <div class="modal-header">
                    <h5 class="modal-title">Search by keyword</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body d-flex align-items-center">
                    <form class="input-group w-75 mx-auto d-flex" action="<?= $shopUrl ?>" method="get">
                        <input type="search" name="q" class="form-control p-3" placeholder="keywords">
                        <button class="input-group-text p-3" type="submit"><i class="fa fa-search"></i></button>
                    </form>
                </div>
            </div>
        </div>
    </div>
