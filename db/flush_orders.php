<?php
/**
 * Flush ALL store orders and related rows so test data does not mix with live orders.
 * Does NOT delete products, stores, customers, settings, or catalog data.
 *
 * Usage:
 *   php db/flush_orders.php --confirm=YES
 */
$dbHost = '127.0.0.1';
$dbName = 'ecommerce';
$dbUser = 'root';
$dbPass = '';
$liveConfig = '/var/www/projects/zenvello.co.uk/config.php';
$localConfig = dirname(__DIR__) . '/config.php';
foreach (array($liveConfig, $localConfig) as $configFile) {
    if (!is_file($configFile)) {
        continue;
    }
    include $configFile;
    if (isset($local_config) && is_array($local_config)) {
        $dbHost = isset($local_config['db_host']) ? $local_config['db_host'] : $dbHost;
        $dbName = isset($local_config['db_name']) ? $local_config['db_name'] : $dbName;
        $dbUser = isset($local_config['db_user']) ? $local_config['db_user'] : $dbUser;
        $dbPass = isset($local_config['db_pass']) ? $local_config['db_pass'] : $dbPass;
        break;
    }
}

$opts = getopt('', array('confirm:'));
if (!isset($opts['confirm']) || strtoupper(trim((string) $opts['confirm'])) !== 'YES') {
    fwrite(STDERR, "Refusing to run. Pass --confirm=YES to flush all orders.\n");
    exit(1);
}

mysqli_report(MYSQLI_REPORT_OFF);
$mysqli = @new mysqli($dbHost, $dbUser, $dbPass, $dbName);
if ($mysqli->connect_errno) {
    fwrite(STDERR, "MySQL connection failed: " . $mysqli->connect_error . "\n");
    exit(1);
}
$mysqli->set_charset('utf8mb4');

function flush_table_exists($mysqli, $table)
{
    $esc = $mysqli->real_escape_string($table);
    $r = $mysqli->query("SHOW TABLES LIKE '{$esc}'");
    return $r && $r->num_rows > 0;
}

function flush_count($mysqli, $table)
{
    if (!flush_table_exists($mysqli, $table)) {
        return null;
    }
    $r = $mysqli->query("SELECT COUNT(*) AS c FROM `{$table}`");
    $row = $r ? $r->fetch_assoc() : null;
    return $row ? (int) $row['c'] : 0;
}

function flush_table($mysqli, $table)
{
    if (!flush_table_exists($mysqli, $table)) {
        echo "skip  {$table} (missing)\n";
        return;
    }
    $before = flush_count($mysqli, $table);
    if (!$mysqli->query("DELETE FROM `{$table}`")) {
        fwrite(STDERR, "Failed DELETE {$table}: " . $mysqli->error . "\n");
        exit(1);
    }
    // Reset AUTO_INCREMENT when possible
    $mysqli->query("ALTER TABLE `{$table}` AUTO_INCREMENT = 1");
    echo "flushed {$table}: {$before} -> 0\n";
}

$beforeOrders = flush_count($mysqli, 'store_orders');
$beforeItems = flush_count($mysqli, 'store_order_items');
echo "Before: store_orders=" . ($beforeOrders === null ? 'n/a' : $beforeOrders)
    . " store_order_items=" . ($beforeItems === null ? 'n/a' : $beforeItems) . "\n";

// Child / related tables first, then orders.
$tables = array(
    'order_status_logs',
    'store_order_items',
    'store_meta_event_logs',
    'store_orders',
    // Optional leftovers tied to order money movement / payment debug
    'accounting_payouts',
    'payouts',
    'store_payouts',
    'paypal_logs',
    'paypal_api_logs',
);

foreach ($tables as $table) {
    flush_table($mysqli, $table);
}

// Analytics rows that reference purchases / checkout (keep product views if column exists)
if (flush_table_exists($mysqli, 'store_analytics_events')) {
    $cols = array();
    $cr = $mysqli->query('SHOW COLUMNS FROM store_analytics_events');
    while ($cr && ($col = $cr->fetch_assoc())) {
        $cols[$col['Field']] = true;
    }
    if (isset($cols['event_name'])) {
        $mysqli->query("DELETE FROM store_analytics_events WHERE event_name IN (
            'purchase','Purchase','payment_page_view','payment_attempt','checkout_start','add_to_cart','BeginCheckout','AddToCart','AddPaymentInfo'
        )");
        echo "flushed store_analytics_events purchase/checkout rows: affected=" . (int) $mysqli->affected_rows . "\n";
    } else {
        flush_table($mysqli, 'store_analytics_events');
    }
}

if (flush_table_exists($mysqli, 'store_channel_jobs')) {
    $mysqli->query("DELETE FROM store_channel_jobs WHERE job_type IN ('meta_event','tiktok_event') OR type IN ('meta_event','tiktok_event')");
    if ($mysqli->error === '') {
        echo "flushed store_channel_jobs order-event rows: affected=" . (int) $mysqli->affected_rows . "\n";
    } else {
        // Column names may differ — leave jobs alone rather than fail hard
        echo "skip  store_channel_jobs selective delete (" . $mysqli->error . ")\n";
    }
}

$afterOrders = flush_count($mysqli, 'store_orders');
$afterItems = flush_count($mysqli, 'store_order_items');
echo "After: store_orders=" . ($afterOrders === null ? 'n/a' : $afterOrders)
    . " store_order_items=" . ($afterItems === null ? 'n/a' : $afterItems) . "\n";
echo "Done. Catalog, customers, and store settings were not deleted.\n";
