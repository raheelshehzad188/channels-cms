<section class="ec-page ec-signup">
    <h1>Sign up</h1>
    <p class="ec-lead">Create your <?= htmlspecialchars($store->name) ?> account.</p>
    <?php if (!empty($flash_error)): ?><div class="ec-alert error"><?= htmlspecialchars($flash_error) ?></div><?php endif; ?>
    <div class="ec-card">
        <form class="ec-form" method="post" action="<?= storefront_url('account/signup') ?>">
            <label>Full name</label>
            <input type="text" name="name" required value="<?= htmlspecialchars(set_value('name')) ?>">
            <label>Email</label>
            <input type="email" name="email" required value="<?= htmlspecialchars(set_value('email')) ?>">
            <label>Password</label>
            <input type="password" name="password" required>
            <label>Phone</label>
            <input type="text" name="phone" value="<?= htmlspecialchars(set_value('phone')) ?>">
            <button class="ec-btn" type="submit">Create account</button>
        </form>
        <p class="ec-links">Already have an account? <a href="<?= storefront_url('account/login') ?>">Login</a></p>
    </div>
</section>
