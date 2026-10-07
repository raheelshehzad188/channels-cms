<?php
$session = $session;
$cur = $session->current_page;
?>
<p class="text-muted">Anonymous session. No customer login or payment details are stored here.</p>
<table class="table table-condensed">
    <tr><th>Session ID</th><td><code><?= htmlspecialchars($session->session_id) ?></code></td></tr>
    <tr><th>Country</th><td><?= sa_flag($session->country_code) ?> <?= htmlspecialchars($session->country) ?></td></tr>
    <tr><th>Store</th><td><?= htmlspecialchars($session->store_name) ?></td></tr>
    <tr><th>Landing page</th><td><?= htmlspecialchars($session->landing_page) ?></td></tr>
    <tr><th>Current page</th><td><?= htmlspecialchars($session->current_page) ?></td></tr>
    <tr><th>Current product</th><td>
        <?= htmlspecialchars($session->product_name ?: '—') ?>
        <?php if ($session->current_product_id): ?>
        · <a href="<?= base_url('admin/product-analyzer') ?>?q=<?= rawurlencode($session->analyzer_code ?: ('P' . (int) $session->current_product_id)) ?>">View Profitability</a>
        <?php endif; ?>
    </td></tr>
    <tr><th>Source</th><td><?= htmlspecialchars(sa_source_label($session->traffic_source)) ?></td></tr>
    <tr><th>UTM</th><td><?= htmlspecialchars(trim($session->utm_source . ' / ' . $session->utm_medium . ' / ' . $session->utm_campaign, ' /')) ?></td></tr>
    <tr><th>Device</th><td><?= htmlspecialchars(ucfirst($session->device)) ?> · <?= htmlspecialchars($session->browser) ?></td></tr>
    <tr><th>First seen</th><td><?= htmlspecialchars($session->started_at) ?></td></tr>
    <tr><th>Last activity</th><td><?= htmlspecialchars($session->last_activity) ?></td></tr>
</table>
<h4>Events</h4>
<table class="table table-striped table-condensed">
    <thead><tr><th>Time</th><th>Event</th><th>Page / product</th></tr></thead>
    <tbody>
        <?php foreach ($events as $event): ?>
        <tr>
            <td><?= htmlspecialchars($event->created_at) ?></td>
            <td><?= htmlspecialchars(sa_event_label($event->event_type)) ?></td>
            <td><?= htmlspecialchars($event->product_name ?: $event->page_title ?: $event->page_url) ?></td>
        </tr>
        <?php endforeach; ?>
        <?php if (empty($events)): ?><tr><td colspan="3" class="text-muted">No events.</td></tr><?php endif; ?>
    </tbody>
</table>
