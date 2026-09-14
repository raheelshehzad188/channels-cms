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
  <link href="<?= $assets; ?>css/store-admin.css?v=2" rel="stylesheet">
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
  <nav class="nav flex-column py-3 flex-grow-1">
    <a class="nav-link <?= ($currentClass === 'dashboard') ? 'active' : ''; ?>" href="<?= $storeUrl; ?>/dashboard"><i class="bi bi-speedometer2 me-2"></i> Dashboard</a>
    <a class="nav-link <?= ($currentClass === 'products' && $currentMethod === 'index') ? 'active' : ''; ?>" href="<?= $storeUrl; ?>/products"><i class="bi bi-box-seam me-2"></i> Available Products</a>
    <a class="nav-link <?= ($currentClass === 'products' && $currentMethod !== 'index') ? 'active' : ''; ?>" href="<?= $storeUrl; ?>/my-products"><i class="bi bi-bag-check me-2"></i> My Products</a>
    <a class="nav-link <?= ($currentClass === 'categories') ? 'active' : ''; ?>" href="<?= $storeUrl; ?>/categories"><i class="bi bi-tags me-2"></i> Categories</a>
    <a class="nav-link <?= ($currentClass === 'theme_settings' || $currentClass === 'homepage_hero') ? 'active' : ''; ?>" href="<?= $storeUrl; ?>/theme-settings"><i class="bi bi-palette2 me-2"></i> Theme Settings</a>
    <a class="nav-link <?= ($currentClass === 'orders') ? 'active' : ''; ?>" href="<?= $storeUrl; ?>/orders"><i class="bi bi-bag me-2"></i> Orders</a>
    <a class="nav-link <?= ($currentClass === 'customers') ? 'active' : ''; ?>" href="<?= $storeUrl; ?>/customers"><i class="bi bi-people me-2"></i> Customers</a>
    <a class="nav-link <?= ($currentClass === 'apps') ? 'active' : ''; ?>" href="<?= $storeUrl; ?>/apps"><i class="bi bi-grid me-2"></i> Apps</a>
    <a class="nav-link <?= ($currentClass === 'themes') ? 'active' : ''; ?>" href="<?= $storeUrl; ?>/themes"><i class="bi bi-palette me-2"></i> Themes</a>
    <a class="nav-link <?= ($currentClass === 'appearance') ? 'active' : ''; ?>" href="<?= $storeUrl; ?>/custom-css"><i class="bi bi-filetype-css me-2"></i> Custom CSS</a>
    <a class="nav-link <?= ($currentClass === 'staff') ? 'active' : ''; ?>" href="<?= $storeUrl; ?>/staff"><i class="bi bi-person-badge me-2"></i> Staff</a>
    <a class="nav-link <?= ($currentClass === 'settings') ? 'active' : ''; ?>" href="<?= $storeUrl; ?>/settings"><i class="bi bi-gear me-2"></i> Settings</a>
    <a class="nav-link <?= ($currentClass === 'profile') ? 'active' : ''; ?>" href="<?= $storeUrl; ?>/profile"><i class="bi bi-shop me-2"></i> Profile</a>
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
