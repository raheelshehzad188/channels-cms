<section class="ec-page ec-signup">
    <h1><?= e_ui('auth.signup') ?></h1>
    <p class="ec-lead"><?= e_ui('auth.signup_lead') ?></p>
    <?php if (!empty($flash_error)): ?><div class="ec-alert error"><?= htmlspecialchars($flash_error) ?></div><?php endif; ?>
    <div class="ec-card">
        <form class="ec-form" method="post" action="<?= storefront_url('account/signup') ?>">
            <label><?= e_ui('checkout.full_name') ?></label>
            <input type="text" name="name" required value="<?= htmlspecialchars(set_value('name')) ?>">
            <label><?= e_ui('checkout.email') ?></label>
            <input type="email" name="email" required value="<?= htmlspecialchars(set_value('email')) ?>">
            <label><?= e_ui('auth.password') ?></label>
            <input type="password" name="password" required>
            <label><?= e_ui('checkout.whatsapp') ?></label>
            <input type="text" name="phone" value="<?= htmlspecialchars(set_value('phone')) ?>" placeholder="923004210607">
            <button class="ec-btn" type="submit"><?= e_ui('auth.create_btn') ?></button>
        </form>
        <p class="ec-links"><?= e_ui('auth.have_account') ?> <a href="<?= storefront_url('account/login') ?>"><?= e_ui('auth.login_btn') ?></a></p>
    </div>
</section>
