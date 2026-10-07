<section class="ec-page ec-login">
    <h1><?= e_ui('auth.login') ?></h1>
    <p class="ec-lead"><?= e_ui('auth.login_lead') ?></p>
    <?php if (!empty($flash_success)): ?><div class="ec-alert success"><?= htmlspecialchars($flash_success) ?></div><?php endif; ?>
    <?php if (!empty($flash_error)): ?><div class="ec-alert error"><?= htmlspecialchars($flash_error) ?></div><?php endif; ?>
    <div class="ec-card">
        <form class="ec-form" method="post" action="<?= storefront_url('account/login') ?>">
            <label><?= e_ui('checkout.email') ?></label>
            <input type="email" name="email" required value="<?= htmlspecialchars(set_value('email')) ?>">
            <label><?= e_ui('auth.password') ?></label>
            <input type="password" name="password" required>
            <button class="ec-btn" type="submit"><?= e_ui('auth.login_btn') ?></button>
        </form>
        <?php if (!empty($guest_checkout)): ?>
          <p class="ec-links"><?= e_ui('auth.guest_note') ?></p>
          <a class="ec-btn ghost block" href="<?= storefront_url('checkout/guest') ?>"><?= e_ui('auth.guest_checkout') ?></a>
        <?php endif; ?>
        <p class="ec-links"><?= e_ui('auth.new_here') ?> <a href="<?= storefront_url('account/signup') ?>"><?= e_ui('auth.create_account') ?></a></p>
    </div>
</section>
