<?php
/**
 * LED Projection Finger Lights — store 1 Piece child:
 * Bundle: buy 2 for 110 SEK + free shipping.
 *
 *   php db/set_finger_lights_bundle_offer.php
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

$storeId = 8;
$bundleQty = 2;
$bundlePrice = 110.00;

$m = new mysqli($dbHost, $dbUser, $dbPass, $dbName);
if ($m->connect_errno) {
    fwrite(STDERR, $m->connect_error . "\n");
    exit(1);
}
$m->set_charset('utf8mb4');

$q = $m->query(
    "SELECT id, store_id, name, sku, price, status
     FROM products
     WHERE store_id = " . (int) $storeId . "
       AND sku LIKE '%TY54734-Z40743%'
       AND (sku LIKE '%1-PIECE%' OR name = '1 Piece')
     ORDER BY id
     LIMIT 1"
);
$onePiece = $q ? $q->fetch_assoc() : null;
if (!$onePiece) {
    fwrite(STDERR, "1 Piece store child not found\n");
    exit(1);
}

$id = (int) $onePiece['id'];
$label = $m->real_escape_string('Köp 2 för 110');
$sql = "UPDATE products SET
    offer_enabled = 1,
    offer_type = 'bundle',
    offer_value = 0,
    offer_buy_qty = 0,
    offer_get_qty = 0,
    offer_bundle_qty = {$bundleQty},
    offer_bundle_price = {$bundlePrice},
    offer_label = '{$label}',
    offer_starts_at = NULL,
    offer_ends_at = NULL,
    offer_priority = 10,
    offer_show_badge = 1,
    offer_show_countdown = 0,
    offer_free_shipping = 1
  WHERE id = {$id}";
if (!$m->query($sql)) {
    fwrite(STDERR, $m->error . "\n");
    exit(1);
}

$check = $m->query(
    "SELECT id, store_id, name, sku, price, offer_enabled, offer_type, offer_bundle_qty, offer_bundle_price, offer_free_shipping, offer_label
     FROM products WHERE id = {$id}"
)->fetch_assoc();
echo 'UPDATED ' . json_encode($check) . "\n";
echo "DONE\n";
