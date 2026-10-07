<link rel="stylesheet" href="<?= storefront_asset_url('assets/frontend/shared/css/pages.css') ?>?v=15">
<style>
.lang-switch { display:inline-flex; align-items:center; gap:8px; margin-left:8px; }
.lang-switch__link { font-weight:600; letter-spacing:.04em; opacity:.75; text-decoration:none; color:inherit; }
.lang-switch__link.is-active,
.lang-switch__link[aria-current="true"] { opacity:1; text-decoration:underline; }
.lang-switch__sep { opacity:.4; }
.drawer .lang-switch { display:flex; margin:10px 0 0; }
</style>
<?php if (!empty($pdp_design) && $pdp_design === 'new'): ?>
<link rel="stylesheet" href="<?= storefront_asset_url('assets/frontend/shared/css/product-detail-new.css') ?>?v=29">
<?php endif; ?>
<?php $css = store_custom_css(isset($store) ? $store : null); if ($css !== ''): ?>
<style id="store-custom-css"><?= $css ?></style>
<?php endif; ?>
<?php $this->load->view('frontend/shared/tracking'); ?>
