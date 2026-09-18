<?php
$statuses = isset($statuses) ? $statuses : array();
$statusKey = isset($order->status) ? $order->status : '';
$statusLabel = isset($statuses[$statusKey]) ? $statuses[$statusKey] : $statusKey;
$placed = !empty($order->created_at) ? date('d M Y, H:i', strtotime($order->created_at)) : '';
$payMethod = '';
if (!empty($order->payment_method)) {
    $payMethod = $order->payment_method === 'card' ? 'Card' : ($order->payment_method === 'paypal' ? 'PayPal' : $order->payment_method);
}
$logs = isset($logs) ? $logs : array();
$items = isset($items) ? $items : array();
?>
<section class="ec-page ec-order">
    <p class="ec-back"><a href="<?= storefront_url('account') ?>">← Back to account</a></p>
    <header class="ec-order-head">
        <div>
            <h1>Order <?= htmlspecialchars($order->order_no) ?></h1>
            <p class="ec-lead">Placed <?= htmlspecialchars($placed) ?></p>
        </div>
        <span class="ec-badge is-<?= htmlspecialchars($statusKey) ?>"><?= htmlspecialchars($statusLabel) ?></span>
    </header>

    <div class="ec-flow">
        <div class="ec-flow__main">
            <h2>Items</h2>
            <ul class="ec-lines">
                <?php foreach ($items as $item): ?>
                    <?php
                    $product = (object) array(
                        'id' => isset($item->product_id) ? (int) $item->product_id : 0,
                        'slug' => isset($item->product_slug) ? $item->product_slug : '',
                    );
                    $image = !empty($item->product_image) ? product_image_url($item->product_image) : '';
                    $canLink = !empty($item->linked_product_id);
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
                            <p class="ec-line__meta">Qty <?= (int) $item->qty ?> · <?= format_money((float) $item->unit_price, $order->currency) ?> each</p>
                        </div>
                        <div class="ec-line__total"><?= format_money((float) $item->line_total, $order->currency) ?></div>
                    </li>
                <?php endforeach; ?>
            </ul>

            <div class="ec-card">
                <h2>Status</h2>
                <?php if (empty($logs)): ?>
                    <p class="ec-lead" style="margin:0">This order is <?= htmlspecialchars(strtolower($statusLabel)) ?>.</p>
                <?php else: ?>
                    <ol class="ec-timeline">
                        <?php foreach ($logs as $log): ?>
                            <?php $logLabel = isset($statuses[$log->status]) ? $statuses[$log->status] : $log->status; ?>
                            <li>
                                <strong><?= htmlspecialchars($logLabel) ?></strong>
                                <span><?= htmlspecialchars(!empty($log->created_at) ? date('d M Y, H:i', strtotime($log->created_at)) : '') ?></span>
                            </li>
                        <?php endforeach; ?>
                    </ol>
                <?php endif; ?>
            </div>
        </div>

        <aside class="ec-summary">
            <h2>Order summary</h2>
            <dl>
                <div><dt>Subtotal</dt><dd><?= format_money((float) $order->subtotal, $order->currency) ?></dd></div>
                <?php if (!empty($order->vat_amount) && (float) $order->vat_amount > 0): ?>
                    <div><dt>VAT (<?= number_format((float) $order->vat_percent, 2) ?>%)</dt><dd><?= format_money((float) $order->vat_amount, $order->currency) ?></dd></div>
                <?php endif; ?>
                <div class="ec-summary__total"><dt>Total</dt><dd><?= format_money((float) $order->total, $order->currency) ?></dd></div>
            </dl>
            <?php if ($payMethod !== ''): ?>
                <p class="ec-pay-note">Paid via <?= htmlspecialchars($payMethod) ?></p>
            <?php endif; ?>

            <h2>Shipping</h2>
            <p class="ec-ship-block">
                <strong><?= htmlspecialchars($order->customer_name) ?></strong><br>
                <?php if (!empty($order->customer_email)): ?><?= htmlspecialchars($order->customer_email) ?><br><?php endif; ?>
                <?php if (!empty($order->customer_phone)): ?><?= htmlspecialchars($order->customer_phone) ?><br><?php endif; ?>
                <?= nl2br(htmlspecialchars($order->shipping_address)) ?>
            </p>
        </aside>
    </div>
</section>
