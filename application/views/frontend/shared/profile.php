<?php
$orders = isset($orders) ? $orders : array();
$statuses = isset($statuses) ? $statuses : array();
$firstName = trim((string) $customer->name);
if ($firstName !== '' && strpos($firstName, ' ') !== false) {
    $firstName = substr($firstName, 0, strpos($firstName, ' '));
}
?>
<section class="ec-page ec-account">
    <h1>My account</h1>
    <p class="ec-lead"><?= $firstName !== '' ? 'Welcome back, ' . htmlspecialchars($firstName) . '. ' : '' ?>Update your details and review your orders.</p>
    <?php if (!empty($flash_success)): ?><div class="ec-alert success"><?= htmlspecialchars($flash_success) ?></div><?php endif; ?>
    <?php if (!empty($flash_error)): ?><div class="ec-alert error"><?= htmlspecialchars($flash_error) ?></div><?php endif; ?>

    <div class="ec-account-grid">
        <div class="ec-card ec-orders" id="orders">
            <h2>Your orders</h2>
            <?php if (empty($orders)): ?>
                <div class="ec-empty ec-empty--compact">
                    <div class="ec-empty__icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="M4 5h2l2.2 10.2h9.4L20 8H7"/><circle cx="10" cy="19" r="1.6"/><circle cx="17.5" cy="19" r="1.6"/></svg>
                    </div>
                    <h2>No orders yet</h2>
                    <p>When you place an order, it will show up here.</p>
                    <a class="ec-btn" href="<?= storefront_url('shop') ?>">Start shopping</a>
                </div>
            <?php else: ?>
                <ul class="ec-order-list">
                    <?php foreach ($orders as $order): ?>
                        <?php
                        $statusKey = isset($order->status) ? $order->status : '';
                        $statusLabel = isset($statuses[$statusKey]) ? $statuses[$statusKey] : $statusKey;
                        $itemCount = isset($order->item_count) ? (int) $order->item_count : 0;
                        $placed = !empty($order->created_at) ? date('d M Y', strtotime($order->created_at)) : '';
                        ?>
                        <li class="ec-order-row">
                            <div class="ec-order-row__info">
                                <a class="ec-order-row__no" href="<?= storefront_url('account/orders/' . rawurlencode($order->order_no)) ?>"><?= htmlspecialchars($order->order_no) ?></a>
                                <p class="ec-order-row__meta">
                                    <?= htmlspecialchars($placed) ?>
                                    <?php if ($itemCount > 0): ?>
                                        · <?= $itemCount ?> item<?= $itemCount === 1 ? '' : 's' ?>
                                    <?php endif; ?>
                                </p>
                            </div>
                            <span class="ec-badge is-<?= htmlspecialchars($statusKey) ?>"><?= htmlspecialchars($statusLabel) ?></span>
                            <strong class="ec-order-row__total"><?= format_money((float) $order->total, $order->currency) ?></strong>
                            <a class="ec-btn ghost" href="<?= storefront_url('account/orders/' . rawurlencode($order->order_no)) ?>">View</a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </div>

        <div class="ec-card">
            <h2>Profile</h2>
            <form class="ec-form" method="post" action="<?= storefront_url('account') ?>">
                <label>Full name</label>
                <input type="text" name="name" required value="<?= htmlspecialchars($customer->name) ?>">
                <label>Email</label>
                <input type="email" name="email" required value="<?= htmlspecialchars($customer->email) ?>">
                <label>WhatsApp number</label>
                <input type="text" name="phone" value="<?= htmlspecialchars(!empty($customer->phone) ? $customer->phone : '') ?>" placeholder="923004210607">
                <label>Address</label>
                <textarea name="address" rows="4"><?= htmlspecialchars(!empty($customer->address) ? $customer->address : '') ?></textarea>
                <label>New password</label>
                <input type="password" name="password" placeholder="Leave blank to keep current password">
                <button class="ec-btn" type="submit">Save profile</button>
                <a class="ec-btn secondary" href="<?= storefront_url('account/logout') ?>">Logout</a>
            </form>
        </div>
    </div>
</section>
