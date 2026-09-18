<link rel="stylesheet" href="<?= storefront_asset_url('assets/frontend/shared/css/pages.css') ?>?v=9">
<?php if (!empty($pdp_design) && $pdp_design === 'new'): ?>
<link rel="stylesheet" href="<?= storefront_asset_url('assets/frontend/shared/css/product-detail-new.css') ?>?v=10">
<?php endif; ?>
<?php $css = store_custom_css(isset($store) ? $store : null); if ($css !== ''): ?>
<style id="store-custom-css"><?= $css ?></style>
<?php endif; ?>
<?php $this->load->view('frontend/shared/tracking'); ?>
