<?php defined('BASEPATH') OR exit('No direct script access allowed');
$isAuth = (strpos($this->router->fetch_class(), 'auth') !== false || $this->router->fetch_class() === 'Auth');
$currentClass = strtolower($this->router->fetch_class());
$currentMethod = strtolower($this->router->fetch_method());
?>
<!DOCTYPE html>
<html lang="en" data-bs-theme="light">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= htmlspecialchars($title); ?> | Store Admin</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
  <link href="<?= $assets; ?>css/store-admin.css?v=5" rel="stylesheet">
</head>
<body class="<?= $isAuth ? '' : 'store-admin'; ?>">

<?php if (!$isAuth): ?>
<aside class="store-sidebar" id="storeSidebar">
  <div class="brand d-flex align-items-center gap-2">
    <?php if (!empty($store->logo)): ?>
      <img src="<?= base_url($store->logo); ?>" alt="" height="28">
    <?php endif; ?>
    <span><?= htmlspecialchars($store->name); ?></span>
  </div>
  <?php
    $navOn = function ($class, $methods = null) use ($currentClass, $currentMethod) {
        if ($currentClass !== $class) {
            return false;
        }
        if ($methods === null) {
            return true;
        }
        return in_array($currentMethod, (array) $methods, true);
    };
    $productsOpen = $navOn('products') || $navOn('hunting') || $navOn('categories') || $navOn('reviews') || $navOn('faqs') || $navOn('product_analytics');
    $onlineOpen = $navOn('theme_settings') || $navOn('homepage_hero') || $navOn('themes') || $navOn('appearance') || $navOn('pages') || ($navOn('settings', 'texts'));
    $ordersOpen = $navOn('orders') || $navOn('customers') || $navOn('accounting');
    $salesOpen = $navOn('channels') || $navOn('apps');
    $settingsOpen = $navOn('staff') || $navOn('profile') || $navOn('meta_events') || ($navOn('settings') && !$navOn('settings', 'texts'));
  ?>
  <nav class="store-sidebar-nav" aria-label="Store admin">
    <a class="nav-link <?= $navOn('dashboard') ? 'active' : ''; ?>" href="<?= $storeUrl; ?>/dashboard"><i class="bi bi-speedometer2"></i> Dashboard</a>

    <details class="store-nav-group" data-nav-group="products" <?= $productsOpen ? 'open' : ''; ?>>
      <summary class="store-nav-summary<?= $productsOpen ? ' is-current' : ''; ?>"><i class="bi bi-box-seam"></i> <span>Products</span> <i class="bi bi-chevron-right store-nav-caret"></i></summary>
      <div class="store-nav-sub">
        <a class="nav-link <?= $navOn('products', array('index', 'mine', 'form', 'save', 'delete', 'delete_image')) ? 'active' : ''; ?>" href="<?= $storeUrl; ?>/products">My Products</a>
        <a class="nav-link <?= $navOn('product_analytics') ? 'active' : ''; ?>" href="<?= $storeUrl; ?>/product-analytics">Product analytics</a>
        <a class="nav-link <?= $navOn('products', array('available', 'add')) ? 'active' : ''; ?>" href="<?= $storeUrl; ?>/available-products">Available Products</a>
        <a class="nav-link <?= $navOn('hunting') ? 'active' : ''; ?>" href="<?= $storeUrl; ?>/hunting">Product Hunting</a>
        <a class="nav-link <?= $navOn('categories') ? 'active' : ''; ?>" href="<?= $storeUrl; ?>/categories">Categories</a>
        <a class="nav-link <?= $navOn('reviews') ? 'active' : ''; ?>" href="<?= $storeUrl; ?>/reviews">Reviews</a>
        <a class="nav-link <?= $navOn('faqs') ? 'active' : ''; ?>" href="<?= $storeUrl; ?>/faqs">FAQs</a>
      </div>
    </details>

    <details class="store-nav-group" data-nav-group="online" <?= $onlineOpen ? 'open' : ''; ?>>
      <summary class="store-nav-summary<?= $onlineOpen ? ' is-current' : ''; ?>"><i class="bi bi-globe"></i> <span>Online store</span> <i class="bi bi-chevron-right store-nav-caret"></i></summary>
      <div class="store-nav-sub">
        <a class="nav-link <?= ($navOn('theme_settings') || $navOn('homepage_hero')) ? 'active' : ''; ?>" href="<?= $storeUrl; ?>/theme-settings">Theme Settings</a>
        <a class="nav-link <?= $navOn('themes') ? 'active' : ''; ?>" href="<?= $storeUrl; ?>/themes">Themes</a>
        <a class="nav-link <?= $navOn('appearance') ? 'active' : ''; ?>" href="<?= $storeUrl; ?>/custom-css">Custom CSS</a>
        <a class="nav-link <?= $navOn('settings', 'texts') ? 'active' : ''; ?>" href="<?= $storeUrl; ?>/settings/texts">Storefront texts</a>
        <a class="nav-link <?= $navOn('pages') ? 'active' : ''; ?>" href="<?= $storeUrl; ?>/pages">Pages</a>
      </div>
    </details>

    <details class="store-nav-group" data-nav-group="orders" <?= $ordersOpen ? 'open' : ''; ?>>
      <summary class="store-nav-summary<?= $ordersOpen ? ' is-current' : ''; ?>"><i class="bi bi-bag"></i> <span>Orders</span> <i class="bi bi-chevron-right store-nav-caret"></i></summary>
      <div class="store-nav-sub">
        <a class="nav-link <?= $navOn('orders') ? 'active' : ''; ?>" href="<?= $storeUrl; ?>/orders">All orders</a>
        <a class="nav-link <?= $navOn('customers') ? 'active' : ''; ?>" href="<?= $storeUrl; ?>/customers">Customers</a>
        <a class="nav-link <?= $navOn('accounting') ? 'active' : ''; ?>" href="<?= $storeUrl; ?>/accounting">Accounting</a>
      </div>
    </details>

    <details class="store-nav-group" data-nav-group="sales" <?= $salesOpen ? 'open' : ''; ?>>
      <summary class="store-nav-summary<?= $salesOpen ? ' is-current' : ''; ?>"><i class="bi bi-broadcast"></i> <span>Sales channels</span> <i class="bi bi-chevron-right store-nav-caret"></i></summary>
      <div class="store-nav-sub">
        <a class="nav-link <?= $navOn('channels') ? 'active' : ''; ?>" href="<?= $storeUrl; ?>/channels">Channels</a>
        <a class="nav-link <?= $navOn('apps') ? 'active' : ''; ?>" href="<?= $storeUrl; ?>/apps">Apps</a>
      </div>
    </details>

    <details class="store-nav-group" data-nav-group="settings" <?= $settingsOpen ? 'open' : ''; ?>>
      <summary class="store-nav-summary<?= $settingsOpen ? ' is-current' : ''; ?>"><i class="bi bi-gear"></i> <span>Settings</span> <i class="bi bi-chevron-right store-nav-caret"></i></summary>
      <div class="store-nav-sub">
        <a class="nav-link <?= ($navOn('settings') && !$navOn('settings', 'texts')) ? 'active' : ''; ?>" href="<?= $storeUrl; ?>/settings">Store settings</a>
        <a class="nav-link <?= $navOn('meta_events') ? 'active' : ''; ?>" href="<?= $storeUrl; ?>/settings/meta">Meta Integration</a>
        <a class="nav-link <?= $navOn('staff') ? 'active' : ''; ?>" href="<?= $storeUrl; ?>/staff">Staff</a>
        <a class="nav-link <?= $navOn('profile') ? 'active' : ''; ?>" href="<?= $storeUrl; ?>/profile">Profile</a>
      </div>
    </details>
  </nav>
  <div class="p-3 border-top border-secondary border-opacity-25">
    <a class="nav-link text-danger" href="<?= $storeUrl; ?>/logout"><i class="bi bi-box-arrow-right me-2"></i> Logout</a>
  </div>
</aside>
<div class="store-main">
  <header class="store-topbar d-flex align-items-center justify-content-between">
    <div class="d-flex align-items-center gap-3">
      <button class="btn btn-sm btn-outline-secondary d-lg-none" type="button" onclick="document.getElementById('storeSidebar').classList.toggle('show')">
        <i class="bi bi-list"></i>
      </button>
      <h1 class="h5 mb-0"><?= htmlspecialchars($page); ?></h1>
    </div>
    <div class="d-flex align-items-center gap-3">
      <button class="btn btn-sm btn-outline-secondary" id="themeToggle" type="button"><i class="bi bi-moon"></i></button>
      <?php if (!empty($_SESSION['store_login']['impersonated'])): ?>
        <span class="badge text-bg-warning">Impersonating</span>
      <?php endif; ?>
      <span class="text-muted small"><?= isset($staff) ? htmlspecialchars($staff->name) : ''; ?></span>
    </div>
  </header>
  <main class="store-content">
<?php endif; ?>
