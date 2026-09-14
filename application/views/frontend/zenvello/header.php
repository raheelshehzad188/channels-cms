<?php
$primary = theme_setting($settings, 'primary_color', '#ffd814');
$secondary = theme_setting($settings, 'secondary_color', '#111111');
$logo = theme_setting($settings, 'logo');
$favicon = theme_setting($settings, 'favicon');
$cartCount = isset($cart_count) ? (int) $cart_count : 0;
$current = isset($current_page) ? $current_page : 'home';
$pageCss = 'home';
$extraCss = array();
if ($current === 'category') {
    $pageCss = 'home';
    $extraCss[] = 'category';
} elseif (in_array($current, array('shop', 'catalog'), true)) {
    $pageCss = 'category';
} elseif ($current === 'detail') {
    $pageCss = 'product';
} elseif ($current !== 'home') {
    $pageCss = 'simple';
}
$homeUrl = storefront_url('shop/index');
$shopUrl = storefront_url('shop');
$cartUrl = storefront_url('cart');
$accountUrl = storefront_url(storefront_customer() ? 'account' : 'account/login');
$contactUrl = storefront_url('page/contact');
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= htmlspecialchars(isset($title) ? $title : $store->name) ?></title>
<?php if ($favicon): ?>
<link rel="icon" href="<?= storefront_asset_url($favicon) ?>">
<?php endif; ?>
<?php if (!empty($meta_description)): ?>
<meta name="description" content="<?= htmlspecialchars($meta_description) ?>">
<?php endif; ?>
<?php if (!empty($meta_keywords)): ?>
<meta name="keywords" content="<?= htmlspecialchars($meta_keywords) ?>">
<?php endif; ?>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="<?= $assets ?>css/reset.css">
<link rel="stylesheet" href="<?= $assets ?>css/variables.css">
<link rel="stylesheet" href="<?= $assets ?>css/global.css">
<link rel="stylesheet" href="<?= $assets ?>css/components.css">
<link rel="stylesheet" href="<?= $assets ?>css/pages/<?= htmlspecialchars($pageCss) ?>.css">
<?php foreach ($extraCss as $cssFile): ?>
<link rel="stylesheet" href="<?= $assets ?>css/pages/<?= htmlspecialchars($cssFile) ?>.css">
<?php endforeach; ?>
<link rel="stylesheet" href="<?= $assets ?>css/responsive.css">
<?php $this->load->view('frontend/shared/custom_css'); ?>
<style>:root { --brand-yellow: <?= htmlspecialchars($primary) ?>; --brand-black: <?= htmlspecialchars($secondary) ?>; }</style>
</head>
<body class="zenvello">
<a class="visually-hidden" href="#main">Skip to content</a>

<div class="topbar">
  <div class="container topbar__inner">
    <p class="topbar__promo">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" width="15" height="15" aria-hidden="true"><path d="M1 3h15v13H1z"/><path d="M16 8h4l3 3v5h-7z"/><circle cx="5.5" cy="18.5" r="2.5"/><circle cx="18.5" cy="18.5" r="2.5"/></svg>
      <?= htmlspecialchars(theme_setting($settings, 'promo_text', 'Free Shipping on Orders Over £50')) ?>
    </p>
    <nav class="topbar__links" aria-label="Support">
      <?php if (!empty($is_preview)): ?>
        <a href="<?= htmlspecialchars($preview_back) ?>">Back to products</a>
      <?php else: ?>
        <a href="<?= $contactUrl ?>">Contact</a>
        <a href="<?= $accountUrl ?>">Help</a>
      <?php endif; ?>
    </nav>
  </div>
</div>

<header class="site-header">
  <div class="container header__inner">
    <button class="header__burger" type="button" aria-label="Open menu" data-drawer-open>
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" width="24" height="24" aria-hidden="true"><path d="M3 6h18M3 12h18M3 18h18"/></svg>
    </button>

    <a class="logo" href="<?= $homeUrl ?>" aria-label="<?= htmlspecialchars($store->name) ?> home">
      <?php if ($logo): ?>
        <img src="<?= storefront_asset_url($logo) ?>" alt="<?= htmlspecialchars($store->name) ?>" style="max-height:48px">
      <?php else: ?>
        <span class="logo__word">ZEN<em>Vello</em></span>
        <span class="logo__tag"><?= htmlspecialchars($store->name) ?></span>
      <?php endif; ?>
    </a>

    <?php if (empty($is_preview)): ?>
    <form class="search" role="search" action="<?= $shopUrl ?>" method="get">
      <label class="visually-hidden" for="q">Search products</label>
      <input class="search__input" id="q" name="q" type="search" placeholder="Search for products...">
      <button class="search__btn" type="submit" aria-label="Search">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" width="18" height="18" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5"/></svg>
      </button>
    </form>
    <?php endif; ?>

    <div class="header__actions">
      <?php if (empty($is_preview)): ?>
      <a class="action" href="<?= $accountUrl ?>">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" aria-hidden="true"><circle cx="12" cy="8" r="4"/><path d="M4 21c0-4.4 3.6-7 8-7s8 2.6 8 7"/></svg>
        <span>Account</span>
      </a>
      <a class="action" href="<?= $cartUrl ?>">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M4 5h2l2.2 10.2h9.4L20 8H7"/><circle cx="10" cy="19" r="1.6"/><circle cx="17.5" cy="19" r="1.6"/></svg>
        <span>Cart</span>
        <span class="action__badge" data-cart-count><?= $cartCount ?></span>
      </a>
      <?php endif; ?>
    </div>
  </div>
</header>

<nav class="mainnav" aria-label="Main">
  <div class="container mainnav__inner">
    <button class="allcats" type="button" data-drawer-open>
      <span class="allcats__bars" aria-hidden="true"><span></span><span></span><span></span></span>
      All Categories
    </button>
    <ul class="navlist">
      <li><a class="<?= ($current === 'home') ? 'is-active' : '' ?>" href="<?= $homeUrl ?>">Home</a></li>
      <li><a class="<?= ($current === 'shop' && empty($active_category_slug)) ? 'is-active' : '' ?>" href="<?= $shopUrl ?>">Shop</a></li>
      <?php if (!empty($nav_categories)): ?>
        <?php foreach ($nav_categories as $navCat): ?>
          <?php if (!empty($navCat->product_count) && (int) $navCat->product_count < 1 && empty($navCat->home_sort)) continue; ?>
          <li>
            <a class="<?= (!empty($active_category_slug) && $active_category_slug === $navCat->slug) ? 'is-active' : '' ?><?= $navCat->slug === 'deals' ? ' nav-deals' : '' ?>"
               href="<?= storefront_url('category/' . rawurlencode($navCat->slug)) ?>">
              <?= htmlspecialchars($navCat->name) ?>
            </a>
          </li>
        <?php endforeach; ?>
      <?php else: ?>
        <li><a href="<?= storefront_url('shop?category=deals') ?>">Deals</a></li>
      <?php endif; ?>
      <li><a href="<?= $contactUrl ?>">Contact</a></li>
    </ul>
  </div>
</nav>

<div class="scrim" data-drawer-close></div>
<aside class="drawer" id="drawer" aria-label="Categories">
  <div class="drawer__head">
    <span class="logo__word" style="font-size:20px">ZEN<em>Vello</em></span>
    <button class="drawer__close" type="button" aria-label="Close menu" data-drawer-close>&times;</button>
  </div>
  <div class="drawer__body">
    <a href="<?= $homeUrl ?>">Home</a>
    <a href="<?= $shopUrl ?>">Shop</a>
    <?php if (!empty($nav_categories)): ?>
      <?php foreach ($nav_categories as $navCat): ?>
        <a href="<?= storefront_url('category/' . rawurlencode($navCat->slug)) ?>"><?= htmlspecialchars(($navCat->icon ? $navCat->icon . ' ' : '') . $navCat->name) ?></a>
      <?php endforeach; ?>
    <?php endif; ?>
    <div class="drawer__sep"></div>
    <a href="<?= $cartUrl ?>">Cart</a>
    <a href="<?= $accountUrl ?>">Account</a>
    <a href="<?= $contactUrl ?>">Contact</a>
  </div>
</aside>

<main id="main" class="page">
