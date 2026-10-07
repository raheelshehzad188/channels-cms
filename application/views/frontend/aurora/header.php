<?php
$primary = theme_setting($settings, 'primary_color', '#c9a227');
$headerBg = theme_setting($settings, 'header_bg', '#fffaf3');
$logo = theme_setting($settings, 'logo');
?>
<!DOCTYPE html>
<html lang="<?= htmlspecialchars(function_exists('storefront_html_lang') ? storefront_html_lang() : 'en') ?>">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= htmlspecialchars(isset($title) ? $title : $store->name) ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@500;700&family=Source+Sans+3:wght@400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= $assets ?>css/style.css">
    <?php $this->load->view('frontend/shared/custom_css'); ?>
    <style>
        :root { --primary: <?= htmlspecialchars($primary) ?>; --header-bg: <?= htmlspecialchars($headerBg) ?>; }
    </style>
</head>
<body class="aurora">
<?php $this->load->view('frontend/shared/admin_bar'); ?>
<header class="site-header">
    <div class="container header-inner">
        <a class="logo" href="<?= storefront_url('') ?>">
            <?php if ($logo): ?>
                <img src="<?= base_url($logo) ?>" alt="<?= htmlspecialchars($store->name) ?>">
            <?php else: ?>
                <?= htmlspecialchars($store->name) ?>
            <?php endif; ?>
        </a>
        <nav>
            <?php if (!empty($is_preview)): ?>
                <a href="<?= $preview_back ?>">Back to products</a>
            <?php else: ?>
                <a href="<?= storefront_url('') ?>">Home</a>
                <a href="<?= storefront_url('shop') ?>">Shop</a>
                <a href="<?= storefront_url('cart') ?>">Cart</a>
                <a href="<?= storefront_url(storefront_customer() ? 'account' : 'account/login') ?>">Account</a>
                <?= function_exists('storefront_language_switcher_html') ? storefront_language_switcher_html() : '' ?>
                <?php if (!empty($header_menu)): ?>
                    <?php foreach ($header_menu as $menuItem): ?>
                        <a href="<?= htmlspecialchars($menuItem->url) ?>"><?= htmlspecialchars($menuItem->label) ?></a>
                    <?php endforeach; ?>
                <?php endif; ?>
            <?php endif; ?>
        </nav>
    </div>
</header>
<main>
