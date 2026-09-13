<section class="ec-page ec-login">
    <h1>Login</h1>
    <p class="ec-lead">Sign in to your <?= htmlspecialchars($store->name) ?> account.</p>
    <?php if (!empty($flash_success)): ?><div class="ec-alert success"><?= htmlspecialchars($flash_success) ?></div><?php endif; ?>
    <?php if (!empty($flash_error)): ?><div class="ec-alert error"><?= htmlspecialchars($flash_error) ?></div><?php endif; ?>
    <div class="ec-card">
        <form class="ec-form" method="post" action="<?= storefront_url('account/login') ?>">
            <label>Email</label>
            <input type="email" name="email" required value="<?= htmlspecialchars(set_value('email')) ?>">
            <label>Password</label>
            <input type="password" name="password" required>
            <button class="ec-btn" type="submit">Login</button>
        </form>
        <p class="ec-links">New here? <a href="<?= storefront_url('account/signup') ?>">Create an account</a></p>
    </div>
</section>
