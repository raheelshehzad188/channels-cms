<section class="ec-page ec-profile">
    <h1>My profile</h1>
    <p class="ec-lead">Update your account details.</p>
    <?php if (!empty($flash_success)): ?><div class="ec-alert success"><?= htmlspecialchars($flash_success) ?></div><?php endif; ?>
    <?php if (!empty($flash_error)): ?><div class="ec-alert error"><?= htmlspecialchars($flash_error) ?></div><?php endif; ?>
    <div class="ec-card">
        <form class="ec-form" method="post" action="<?= storefront_url('account') ?>">
            <label>Full name</label>
            <input type="text" name="name" required value="<?= htmlspecialchars($customer->name) ?>">
            <label>Email</label>
            <input type="email" name="email" required value="<?= htmlspecialchars($customer->email) ?>">
            <label>Phone</label>
            <input type="text" name="phone" value="<?= htmlspecialchars(!empty($customer->phone) ? $customer->phone : '') ?>">
            <label>Address</label>
            <textarea name="address" rows="4"><?= htmlspecialchars(!empty($customer->address) ? $customer->address : '') ?></textarea>
            <label>New password</label>
            <input type="password" name="password" placeholder="Leave blank to keep current password">
            <button class="ec-btn" type="submit">Save profile</button>
            <a class="ec-btn secondary" href="<?= storefront_url('account/logout') ?>">Logout</a>
        </form>
    </div>
</section>
