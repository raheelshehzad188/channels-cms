<?php
$section = isset($account_section) ? $account_section : 'orders';
$orderItems = isset($order_items) ? $order_items : array();
$statuses = isset($statuses) ? $statuses : array();
$firstName = trim((string) $customer->name);
if ($firstName !== '' && strpos($firstName, ' ') !== false) {
    $firstName = substr($firstName, 0, strpos($firstName, ' '));
}
?>
<section class="ec-page ec-account">
    <h1><?= e_ui('auth.account') ?></h1>
    <p class="ec-lead"><?= $firstName !== '' ? e_ui('auth.welcome', array('{name}' => $firstName)) . ' ' : '' ?><?= e_ui('auth.account_lead') ?></p>
    <?php if (!empty($flash_success)): ?><div class="ec-alert success"><?= htmlspecialchars($flash_success) ?></div><?php endif; ?>
    <?php if (!empty($flash_error)): ?><div class="ec-alert error"><?= htmlspecialchars($flash_error) ?></div><?php endif; ?>

    <div class="ec-account-dash">
        <?php $this->load->view('frontend/shared/_account_nav', array('account_section' => $section)); ?>

        <div class="ec-account-main">
            <?php if ($section === 'profile'): ?>
                <div class="ec-card">
                    <h2><?= e_ui('auth.edit_profile') ?></h2>
                    <form class="ec-form" method="post" action="<?= storefront_url('account/profile') ?>" data-address-form>
                        <?php $this->load->view('frontend/shared/_address_fields', array(
                            'addr' => isset($addr) ? $addr : array(),
                            'countries' => isset($countries) ? $countries : array(),
                            'show_billing' => true,
                        )); ?>
                        <label><?= e_ui('auth.new_password') ?></label>
                        <input type="password" name="password" placeholder="<?= e_ui('auth.password_keep') ?>">
                        <button class="ec-btn" type="submit"><?= e_ui('auth.save_profile') ?></button>
                    </form>
                </div>
            <?php else: ?>
                <div class="ec-card ec-orders">
                    <h2><?= e_ui('auth.orders') ?></h2>
                    <?php
                    $hasOrders = !empty($has_orders) || !empty($orderItems);
                    $statusFilter = isset($order_status_filter) ? $order_status_filter : '';
                    $statusCounts = isset($order_status_counts) ? $order_status_counts : array();
                    $statusTabs = isset($order_status_tabs) ? $order_status_tabs : array('pending', 'dispatching', 'shipped', 'delivered', 'completed');
                    ?>
                    <?php if ($hasOrders): ?>
                        <nav class="ec-status-tabs" aria-label="<?= htmlspecialchars(e_ui('order.status')) ?>">
                            <a class="<?= $statusFilter === '' ? 'is-active' : '' ?>" href="<?= storefront_url('account/orders') ?>">
                                <?= e_ui('auth.orders_all') ?>
                                <span class="ec-status-count"><?= isset($statusCounts['all']) ? (int) $statusCounts['all'] : count($orderItems) ?></span>
                            </a>
                            <?php foreach ($statusTabs as $tab): ?>
                                <?php
                                $tabLabel = isset($statuses[$tab]) ? $statuses[$tab] : ucfirst($tab);
                                $tabCount = isset($statusCounts[$tab]) ? (int) $statusCounts[$tab] : 0;
                                $tabUrl = storefront_url('account/orders') . '?status=' . rawurlencode($tab);
                                ?>
                                <a class="<?= $statusFilter === $tab ? 'is-active' : '' ?>" href="<?= $tabUrl ?>">
                                    <?= htmlspecialchars($tabLabel) ?>
                                    <span class="ec-status-count"><?= $tabCount ?></span>
                                </a>
                            <?php endforeach; ?>
                        </nav>
                    <?php endif; ?>
                    <?php if (empty($orderItems)): ?>
                        <div class="ec-empty ec-empty--compact">
                            <div class="ec-empty__icon" aria-hidden="true">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="M4 5h2l2.2 10.2h9.4L20 8H7"/><circle cx="10" cy="19" r="1.6"/><circle cx="17.5" cy="19" r="1.6"/></svg>
                            </div>
                            <h2><?= $hasOrders ? e_ui('auth.no_status_orders') : e_ui('auth.no_orders') ?></h2>
                            <?php if (!$hasOrders): ?>
                                <p><?= e_ui('auth.no_orders_text') ?></p>
                                <a class="ec-btn" href="<?= storefront_url('shop') ?>"><?= e_ui('auth.start_shopping') ?></a>
                            <?php endif; ?>
                        </div>
                    <?php else: ?>
                        <div class="ec-table-wrap">
                            <table class="ec-item-table">
                                <thead>
                                    <tr>
                                        <th><?= e_ui('auth.order_no') ?></th>
                                        <th><?= e_ui('auth.product') ?></th>
                                        <th><?= e_ui('auth.qty') ?></th>
                                        <th><?= e_ui('order.status') ?></th>
                                        <th><?= e_ui('cart.total') ?></th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($orderItems as $row): ?>
                                        <?php
                                        $itemStatus = !empty($row->fulfillment_status) ? $row->fulfillment_status : (!empty($row->order_status) ? $row->order_status : 'pending');
                                        $statusLabel = isset($statuses[$itemStatus]) ? $statuses[$itemStatus] : $itemStatus;
                                        $placed = !empty($row->order_created_at) ? date('d M Y', strtotime($row->order_created_at)) : '';
                                        $image = !empty($row->product_image) ? product_image_url($row->product_image) : '';
                                        $product = (object) array(
                                            'id' => isset($row->product_id) ? (int) $row->product_id : 0,
                                            'slug' => isset($row->product_slug) ? $row->product_slug : '',
                                        );
                                        $canLink = !empty($row->linked_product_id);
                                        $orderUrl = storefront_url('account/orders/' . rawurlencode($row->order_no));
                                        ?>
                                        <tr>
                                            <td data-label="<?= htmlspecialchars(e_ui('auth.order_no')) ?>">
                                                <a class="ec-order-row__no" href="<?= $orderUrl ?>"><?= htmlspecialchars($row->order_no) ?></a>
                                                <?php if ($placed !== ''): ?><p class="ec-order-row__meta"><?= htmlspecialchars($placed) ?></p><?php endif; ?>
                                            </td>
                                            <td data-label="<?= htmlspecialchars(e_ui('auth.product')) ?>">
                                                <div class="ec-item-prod">
                                                    <?php if ($image): ?>
                                                        <img src="<?= htmlspecialchars($image) ?>" alt="">
                                                    <?php endif; ?>
                                                    <?php if ($canLink): ?>
                                                        <a href="<?= product_url($product) ?>"><?= htmlspecialchars($row->product_name) ?></a>
                                                    <?php else: ?>
                                                        <span><?= htmlspecialchars($row->product_name) ?></span>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                            <td data-label="<?= htmlspecialchars(e_ui('auth.qty')) ?>"><?= (int) $row->qty ?></td>
                                            <td data-label="<?= htmlspecialchars(e_ui('order.status')) ?>">
                                                <span class="ec-badge is-<?= htmlspecialchars($itemStatus) ?>"><?= htmlspecialchars($statusLabel) ?></span>
                                                <?php if (!empty($row->tracking_number)): ?>
                                                    <p class="ec-order-row__meta"><?= e_ui('order.tracking') ?>: <?= htmlspecialchars(trim((isset($row->shipping_company) ? $row->shipping_company . ' · ' : '') . $row->tracking_number)) ?></p>
                                                <?php endif; ?>
                                            </td>
                                            <td data-label="<?= htmlspecialchars(e_ui('cart.total')) ?>">
                                                <strong><?= format_money((float) $row->line_total, $row->currency) ?></strong>
                                            </td>
                                            <td>
                                                <a class="ec-btn ghost" href="<?= $orderUrl ?>"><?= e_ui('auth.view') ?></a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>
