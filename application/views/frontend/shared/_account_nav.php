<?php
$section = isset($account_section) ? $account_section : 'orders';
?>
<nav class="ec-account-nav" aria-label="<?= htmlspecialchars(e_ui('auth.account')) ?>">
    <a class="<?= $section === 'profile' ? 'is-active' : '' ?>" href="<?= storefront_url('account/profile') ?>"><?= e_ui('auth.edit_profile') ?></a>
    <a class="<?= $section === 'orders' ? 'is-active' : '' ?>" href="<?= storefront_url('account/orders') ?>"><?= e_ui('auth.orders') ?></a>
    <a class="is-logout" href="<?= storefront_url('account/logout') ?>"><?= e_ui('auth.logout') ?></a>
</nav>
