<link rel="stylesheet" href="<?= base_url('assets/frontend/shared/css/pages.css') ?>?v=5">
<?php $css = store_custom_css(isset($store) ? $store : null); if ($css !== ''): ?>
<style id="store-custom-css"><?= $css ?></style>
<?php endif; ?>
