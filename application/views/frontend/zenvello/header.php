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
$homeUrl = !empty($is_preview) ? $preview_back : storefront_url('');
$shopUrl = storefront_url('shop');
$cartUrl = storefront_url('cart');
$accountUrl = storefront_url(storefront_customer() ? 'account' : 'account/login');
$contactUrl = function_exists('contact_url') ? contact_url() : storefront_url('contact');
?>
<!DOCTYPE html>
<html lang="<?= htmlspecialchars(function_exists('storefront_html_lang') ? storefront_html_lang() : 'en') ?>">
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
<link rel="stylesheet" href="<?= $assets ?>css/reset.css?v=2">
<link rel="stylesheet" href="<?= $assets ?>css/variables.css?v=4">
<link rel="stylesheet" href="<?= $assets ?>css/global.css?v=14">
<link rel="stylesheet" href="<?= $assets ?>css/components.css?v=12">
<link rel="stylesheet" href="<?= $assets ?>css/pages/<?= htmlspecialchars($pageCss) ?>.css?v=22">
<?php foreach ($extraCss as $cssFile): ?>
<link rel="stylesheet" href="<?= $assets ?>css/pages/<?= htmlspecialchars($cssFile) ?>.css?v=17">
<?php endforeach; ?>
<link rel="stylesheet" href="<?= $assets ?>css/responsive.css?v=15">
<?php $this->load->view('frontend/shared/custom_css'); ?>
<style>:root { --brand-yellow: <?= htmlspecialchars($primary) ?>; --brand-black: <?= htmlspecialchars($secondary) ?>; }</style>
<script>window.STORE_UI = <?= json_encode(storefront_ui_js_map(), JSON_UNESCAPED_UNICODE) ?>;</script>
</head>
<body class="zenvello<?= $current === 'home' ? ' page-home' : '' ?>">
<?php $this->load->view('frontend/shared/admin_bar'); ?>
<a class="visually-hidden" href="#main"><?= e_ui('meta.skip') ?></a>

<div class="topbar">
  <div class="container topbar__inner">
    <p class="topbar__promo">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" width="15" height="15" aria-hidden="true"><path d="M1 3h15v13H1z"/><path d="M16 8h4l3 3v5h-7z"/><circle cx="5.5" cy="18.5" r="2.5"/><circle cx="18.5" cy="18.5" r="2.5"/></svg>
      <?= htmlspecialchars(theme_setting($settings, 'promo_text', store_ui('trust.free_shipping'))) ?>
    </p>
    <nav class="topbar__links" aria-label="<?= e_ui('nav.support') ?>">
      <?php if (!empty($is_preview)): ?>
        <a href="<?= htmlspecialchars($preview_back) ?>"><?= e_ui('nav.back_products') ?></a>
      <?php else: ?>
        <a href="<?= $contactUrl ?>"><?= e_ui('nav.contact') ?></a>
        <a href="<?= $accountUrl ?>"><?= e_ui('nav.help') ?></a>
        <?= function_exists('storefront_language_switcher_html') ? storefront_language_switcher_html() : '' ?>
      <?php endif; ?>
    </nav>
  </div>
</div>

<header class="site-header">
  <div class="container header__inner">
    <button class="header__burger" type="button" aria-label="<?= e_ui('nav.open_menu') ?>" data-drawer-open>
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" width="24" height="24" aria-hidden="true"><path d="M3 6h18M3 12h18M3 18h18"/></svg>
    </button>

    <a class="logo" href="<?= $homeUrl ?>" aria-label="<?= e_ui('nav.home_aria') ?>">
      <?php if ($logo): ?>
        <img src="<?= storefront_asset_url($logo) ?>" alt="<?= htmlspecialchars($store->name) ?>" style="max-height:48px">
      <?php else: ?>
        <span class="logo__word">ZEN<em>Vello</em></span>
        <span class="logo__tag"><?= htmlspecialchars($store->name) ?></span>
      <?php endif; ?>
    </a>

    <?php if (empty($is_preview)): ?>
    <form class="search" role="search" action="<?= $shopUrl ?>" method="get">
      <label class="visually-hidden" for="q"><?= e_ui('nav.search_label') ?></label>
      <input class="search__input" id="q" name="q" type="search" placeholder="<?= e_ui('nav.search_placeholder') ?>">
      <button class="search__btn" type="submit" aria-label="<?= e_ui('nav.search') ?>">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" width="18" height="18" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5"/></svg>
      </button>
    </form>
    <?php endif; ?>

    <div class="header__actions">
      <?php if (empty($is_preview)): ?>
      <a class="action" href="<?= $accountUrl ?>">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" aria-hidden="true"><circle cx="12" cy="8" r="4"/><path d="M4 21c0-4.4 3.6-7 8-7s8 2.6 8 7"/></svg>
        <span><?= e_ui('nav.account') ?></span>
      </a>
      <a class="action" href="<?= $cartUrl ?>">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M4 5h2l2.2 10.2h9.4L20 8H7"/><circle cx="10" cy="19" r="1.6"/><circle cx="17.5" cy="19" r="1.6"/></svg>
        <span><?= e_ui('nav.cart') ?></span>
        <span class="action__badge" data-cart-count="<?= $cartCount ?>"<?= $cartCount > 0 ? '' : ' hidden' ?>><?= $cartCount ?></span>
      </a>
      <?php endif; ?>
    </div>
  </div>
</header>

<nav class="mainnav" aria-label="<?= e_ui('nav.main') ?>">
  <div class="container mainnav__inner">
    <button class="allcats" type="button" data-drawer-open>
      <span class="allcats__bars" aria-hidden="true"><span></span><span></span><span></span></span>
      <?= e_ui('nav.all_categories') ?>
    </button>
    <ul class="navlist">
      <li><a class="<?= ($current === 'home') ? 'is-active' : '' ?>" href="<?= $homeUrl ?>"><?= e_ui('nav.home') ?></a></li>
      <li><a class="<?= ($current === 'shop' && empty($active_category_slug)) ? 'is-active' : '' ?>" href="<?= $shopUrl ?>"><?= e_ui('nav.shop') ?></a></li>
      <?php if (!empty($nav_categories)): ?>
        <?php foreach ($nav_categories as $navCat): ?>
          <?php if (!empty($navCat->product_count) && (int) $navCat->product_count < 1 && empty($navCat->home_sort)) continue; ?>
          <li>
            <a class="<?= (!empty($active_category_slug) && $active_category_slug === $navCat->slug) ? 'is-active' : '' ?><?= $navCat->slug === 'deals' ? ' nav-deals' : '' ?>"
               href="<?= category_url($navCat) ?>">
              <?= htmlspecialchars(category_store_name($navCat)) ?>
            </a>
          </li>
        <?php endforeach; ?>
      <?php else: ?>
        <li><a href="<?= storefront_url('shop?category=deals') ?>"><?= e_ui('nav.deals') ?></a></li>
      <?php endif; ?>
      <li><a class="<?= ($current === 'contact') ? 'is-active' : '' ?>" href="<?= $contactUrl ?>"><?= e_ui('nav.contact') ?></a></li>
      <?php if (!empty($header_menu)): ?>
        <?php foreach ($header_menu as $menuItem): ?>
          <li><a class="<?= !empty($menuItem->active) ? 'is-active' : '' ?>" href="<?= htmlspecialchars($menuItem->url) ?>"><?= htmlspecialchars($menuItem->label) ?></a></li>
        <?php endforeach; ?>
      <?php endif; ?>
    </ul>
  </div>
</nav>

<div class="scrim" data-drawer-close></div>
<aside class="drawer" id="drawer" aria-label="<?= e_ui('nav.categories') ?>">
  <div class="drawer__head">
    <span class="logo__word" style="font-size:20px">ZEN<em>Vello</em></span>
    <button class="drawer__close" type="button" aria-label="<?= e_ui('nav.close_menu') ?>" data-drawer-close>&times;</button>
  </div>
  <div class="drawer__body">
    <a href="<?= $homeUrl ?>"><?= e_ui('nav.home') ?></a>
    <a href="<?= $shopUrl ?>"><?= e_ui('nav.shop') ?></a>
    <?php if (!empty($nav_categories)): ?>
      <?php foreach ($nav_categories as $navCat): ?>
        <a href="<?= category_url($navCat) ?>"><?= htmlspecialchars(($navCat->icon ? $navCat->icon . ' ' : '') . category_store_name($navCat)) ?></a>
      <?php endforeach; ?>
    <?php endif; ?>
    <div class="drawer__sep"></div>
    <a href="<?= $cartUrl ?>"><?= e_ui('nav.cart') ?></a>
    <a href="<?= $accountUrl ?>"><?= e_ui('nav.account') ?></a>
    <a href="<?= $contactUrl ?>"><?= e_ui('nav.contact') ?></a>
    <?= function_exists('storefront_language_switcher_html') ? storefront_language_switcher_html() : '' ?>
    <?php if (!empty($header_menu)): ?>
      <?php foreach ($header_menu as $menuItem): ?>
        <a href="<?= htmlspecialchars($menuItem->url) ?>"><?= htmlspecialchars($menuItem->label) ?></a>
      <?php endforeach; ?>
    <?php endif; ?>
  </div>
</aside>

<main id="main" class="page">
