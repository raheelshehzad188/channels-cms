<?php
$statuses = isset($statuses) ? $statuses : array();
$statusKey = isset($order->status) ? $order->status : '';
$statusLabel = isset($statuses[$statusKey]) ? $statuses[$statusKey] : $statusKey;
$placed = !empty($order->created_at) ? date('d M Y, H:i', strtotime($order->created_at)) : '';
$payMethod = '';
if (!empty($order->payment_method)) {
    $payMethod = $order->payment_method === 'card' ? store_ui('pay.method_card') : ($order->payment_method === 'paypal' ? store_ui('pay.method_paypal') : $order->payment_method);
}
$logs = isset($logs) ? $logs : array();
$itemLogs = isset($item_logs) ? $item_logs : array();
$items = isset($items) ? $items : array();

$trackSteps = array(
    array('key' => 'pending', 'label' => store_ui('order.status_pending'), 'icon' => 'pending'),
    array('key' => 'processing', 'label' => store_ui('order.track_processing'), 'icon' => 'processing'),
    array('key' => 'dispatched', 'label' => store_ui('order.track_dispatch'), 'icon' => 'dispatched'),
    array('key' => 'delivered', 'label' => store_ui('order.status_delivered'), 'icon' => 'delivered'),
    array('key' => 'complete', 'label' => store_ui('order.track_complete'), 'icon' => 'complete'),
);
$trackKeys = array();
foreach ($trackSteps as $i => $step) {
    $trackKeys[$step['key']] = $i;
}
$trackStatus = strtolower(trim((string) $statusKey));
$trackStepKey = class_exists('Ec_order_model') ? Ec_order_model::customer_track_step($trackStatus) : '';
$trackIndex = ($trackStepKey !== '' && isset($trackKeys[$trackStepKey])) ? $trackKeys[$trackStepKey] : 0;
$trackCancelled = in_array($trackStatus, array('cancelled', 'refunded', 'refund_requested'), true);

$stepTimes = array();
if (!empty($order->created_at)) {
    $stepTimes['pending'] = $order->created_at;
}
$codeToStep = array(
    'pending' => 'pending',
    'confirmed' => 'pending',
    'processing' => 'processing',
    'dispatching' => 'processing',
    'shipped' => 'dispatched',
    'delivered' => 'delivered',
    'completed' => 'complete',
);
foreach (array_merge($logs, $itemLogs) as $log) {
    $code = isset($log->status) ? strtolower(trim((string) $log->status)) : '';
    if ($code === '' || !isset($codeToStep[$code]) || empty($log->created_at)) {
        continue;
    }
    $step = $codeToStep[$code];
    if (!isset($stepTimes[$step]) || strtotime($log->created_at) < strtotime($stepTimes[$step])) {
        $stepTimes[$step] = $log->created_at;
    }
}

$trackIcons = array(
    'pending' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="8.5"/><path d="M12 8v4.2L15 14"/></svg>',
    'processing' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 3.5l7 3.8v5.4c0 4.3-3 6.8-7 8.3-4-1.5-7-4-7-8.3V7.3l7-3.8z"/><path d="M9.2 12.2l1.9 1.9 3.8-4"/></svg>',
    'dispatched' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 7.5h11.5V16H3z"/><path d="M14.5 10.5H19l2 3V16h-6.5"/><circle cx="7.2" cy="16.8" r="1.7"/><circle cx="17.2" cy="16.8" r="1.7"/></svg>',
    'delivered' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M4 11.2L12 5l8 6.2V19a1 1 0 0 1-1 1h-5.2v-5.2H10.2V20H5a1 1 0 0 1-1-1v-7.8z"/></svg>',
    'complete' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="8.5"/><path d="M8.4 12.2l2.5 2.5 4.8-5"/></svg>',
);
$trackGaps = max(count($trackSteps) - 1, 1);
?>
<section class="ec-page ec-account ec-order">
    <h1><?= e_ui('auth.account') ?></h1>
    <div class="ec-account-dash">
        <?php $this->load->view('frontend/shared/_account_nav', array('account_section' => isset($account_section) ? $account_section : 'orders')); ?>

        <div class="ec-account-main">
            <p class="ec-back"><a href="<?= storefront_url('account/orders') ?>"><?= e_ui('auth.back') ?></a></p>
            <header class="ec-order-head">
                <div>
                    <h2><?= e_ui('order.heading', array('{order}' => $order->order_no)) ?></h2>
                    <p class="ec-lead"><?= e_ui('order.placed', array('{date}' => $placed)) ?></p>
                </div>
                <span class="ec-badge is-<?= htmlspecialchars($statusKey) ?>"><?= htmlspecialchars($statusLabel) ?></span>
            </header>

            <div class="ec-card ec-track-card">
                <h2><?= e_ui('order.status') ?></h2>
                <ol class="ec-track" style="--ec-track-progress: <?= $trackCancelled ? 0 : htmlspecialchars(number_format($trackIndex / $trackGaps, 4, '.', '')) ?>" aria-label="<?= e_ui('order.status') ?>">
                    <?php foreach ($trackSteps as $i => $step): ?>
                        <?php
                        $filled = !$trackCancelled && $i <= $trackIndex;
                        $isCurrent = !$trackCancelled && $i === $trackIndex;
                        $cls = $filled ? 'is-done' : 'is-todo';
                        if ($isCurrent) {
                            $cls .= ' is-current';
                        }
                        $when = isset($stepTimes[$step['key']]) ? $stepTimes[$step['key']] : '';
                        $whenTs = $when !== '' ? strtotime($when) : 0;
                        $icon = isset($trackIcons[$step['icon']]) ? $trackIcons[$step['icon']] : $trackIcons['pending'];
                        ?>
                        <li class="<?= $cls ?>">
                            <span class="ec-track__icon" aria-hidden="true"><?= $icon ?></span>
                            <span class="ec-track__label"><?= htmlspecialchars($step['label']) ?></span>
                            <?php if ($whenTs && ($filled || $step['key'] === 'pending')): ?>
                                <span class="ec-track__when">
                                    <span class="ec-track__date"><?= htmlspecialchars(date('d M Y', $whenTs)) ?></span>
                                    <span class="ec-track__time"><?= htmlspecialchars(date('H:i', $whenTs)) ?></span>
                                </span>
                            <?php endif; ?>
                        </li>
                    <?php endforeach; ?>
                </ol>
            </div>

            <div class="ec-flow">
                <div class="ec-flow__main">
                    <h2><?= e_ui('order.items') ?></h2>
                    <ul class="ec-lines">
                        <?php foreach ($items as $item): ?>
                            <?php
                            $product = (object) array(
                                'id' => isset($item->product_id) ? (int) $item->product_id : 0,
                                'slug' => isset($item->product_slug) ? $item->product_slug : '',
                            );
                            $image = !empty($item->product_image) ? product_image_url($item->product_image) : '';
                            $canLink = !empty($item->linked_product_id);
                            $itemStatus = !empty($item->fulfillment_status) ? $item->fulfillment_status : $statusKey;
                            $itemLabel = isset($statuses[$itemStatus]) ? $statuses[$itemStatus] : $itemStatus;
                            ?>
                            <li class="ec-line">
                                <?php if ($canLink): ?>
                                    <a class="ec-line__media" href="<?= product_url($product) ?>">
                                        <?php if ($image): ?>
                                            <img src="<?= htmlspecialchars($image) ?>" alt="">
                                        <?php else: ?>
                                            <span class="ec-line__ph"></span>
                                        <?php endif; ?>
                                    </a>
                                <?php else: ?>
                                    <span class="ec-line__media">
                                        <?php if ($image): ?>
                                            <img src="<?= htmlspecialchars($image) ?>" alt="">
                                        <?php else: ?>
                                            <span class="ec-line__ph"></span>
                                        <?php endif; ?>
                                    </span>
                                <?php endif; ?>
                                <div class="ec-line__info">
                                    <?php if ($canLink): ?>
                                        <a class="ec-line__name" href="<?= product_url($product) ?>"><?= htmlspecialchars($item->product_name) ?></a>
                                    <?php else: ?>
                                        <span class="ec-line__name"><?= htmlspecialchars($item->product_name) ?></span>
                                    <?php endif; ?>
                                    <p class="ec-line__meta"><?= e_ui('order.qty_each', array('{n}' => (int) $item->qty, '{amount}' => format_money((float) $item->unit_price, $order->currency))) ?></p>
                                    <span class="ec-badge is-<?= htmlspecialchars($itemStatus) ?>"><?= htmlspecialchars($itemLabel) ?></span>
                                    <?php if (!empty($item->tracking_number)): ?>
                                        <p class="ec-line__meta"><?= e_ui('order.tracking') ?>: <?= htmlspecialchars(trim((isset($item->shipping_company) ? $item->shipping_company . ' · ' : '') . $item->tracking_number)) ?></p>
                                    <?php endif; ?>
                                </div>
                                <div class="ec-line__total"><?= format_money((float) $item->line_total, $order->currency) ?></div>
                            </li>
                        <?php endforeach; ?>
                    </ul>

                    <?php if (!empty($logs)): ?>
                    <div class="ec-card">
                        <h2><?= e_ui('order.status') ?></h2>
                        <ol class="ec-timeline">
                            <?php foreach ($logs as $log): ?>
                                <?php $logLabel = isset($statuses[$log->status]) ? $statuses[$log->status] : $log->status; ?>
                                <li>
                                    <strong><?= htmlspecialchars($logLabel) ?></strong>
                                    <span><?= htmlspecialchars(!empty($log->created_at) ? date('d M Y, H:i', strtotime($log->created_at)) : '') ?></span>
                                </li>
                            <?php endforeach; ?>
                        </ol>
                    </div>
                    <?php endif; ?>
                </div>

                <aside class="ec-summary">
                    <h2><?= e_ui('cart.summary') ?></h2>
                    <dl>
                        <div><dt><?= e_ui('cart.subtotal') ?></dt><dd><?= format_money((float) $order->subtotal, $order->currency) ?></dd></div>
                        <?php if ((isset($order->shipping_qty) && (int) $order->shipping_qty > 0) || (!empty($order->shipping_amount) && (float) $order->shipping_amount > 0)): ?>
                            <div><dt><?= e_ui('cart.shipping_qty', array('{n}' => (int) (isset($order->shipping_qty) ? $order->shipping_qty : 0))) ?></dt><dd><?= ((float) $order->shipping_amount > 0) ? format_money((float) $order->shipping_amount, $order->currency) : e_ui('cart.shipping_free') ?></dd></div>
                        <?php endif; ?>
                        <?php if (!empty($order->vat_amount) && (float) $order->vat_amount > 0): ?>
                            <div><dt><?= e_ui('cart.vat', array('{percent}' => number_format((float) $order->vat_percent, 2))) ?></dt><dd><?= format_money((float) $order->vat_amount, $order->currency) ?></dd></div>
                        <?php endif; ?>
                        <div class="ec-summary__total"><dt><?= e_ui('cart.total') ?></dt><dd><?= format_money((float) $order->total, $order->currency) ?></dd></div>
                    </dl>
                    <?php if ($payMethod !== ''): ?>
                        <p class="ec-pay-note"><?= e_ui('order.paid_via', array('{method}' => $payMethod)) ?></p>
                    <?php endif; ?>

                    <h2><?= e_ui('order.shipping') ?></h2>
                    <p class="ec-ship-block">
                        <strong><?= htmlspecialchars($order->customer_name) ?></strong><br>
                        <?php if (!empty($order->customer_email)): ?><?= htmlspecialchars($order->customer_email) ?><br><?php endif; ?>
                        <?php if (!empty($order->customer_phone)): ?><?= htmlspecialchars($order->customer_phone) ?><br><?php endif; ?>
                        <?= nl2br(htmlspecialchars($order->shipping_address)) ?>
                        <?php if (!empty($order->billing_address)): ?>
                            <br><br><strong><?= e_ui('order.billing') ?></strong><br>
                            <?= nl2br(htmlspecialchars($order->billing_address)) ?>
                        <?php endif; ?>
                    </p>
                </aside>
            </div>
        </div>
    </div>
</section>
