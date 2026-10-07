<?php
/**
 * Keep BOGO only on pet glove parent + 1 Piece; clear on multipacks.
 */
$liveConfig = '/var/www/projects/zenvello.co.uk/config.php';
$localConfig = dirname(__DIR__) . '/config.php';
foreach (array($liveConfig, $localConfig) as $configFile) {
    if (!is_file($configFile)) {
        continue;
    }
    include $configFile;
    if (isset($local_config) && is_array($local_config)) {
        break;
    }
}
$m = new mysqli($local_config['db_host'], $local_config['db_user'], $local_config['db_pass'], $local_config['db_name']);
$m->set_charset('utf8mb4');

$clear = 'UPDATE products SET
    offer_enabled = 0,
    offer_type = \'percent\',
    offer_value = 0,
    offer_buy_qty = 0,
    offer_get_qty = 0,
    offer_bundle_qty = 0,
    offer_bundle_price = 0,
    offer_bundle2_qty = 0,
    offer_bundle2_price = 0,
    offer_label = \'\',
    offer_free_shipping = 0
  WHERE id IN (2347, 2349, 2351)';
if (!$m->query($clear)) {
    fwrite(STDERR, $m->error . "\n");
    exit(1);
}
echo "CLEARED multipacks\n";

$r = $m->query(
    "SELECT id, sku, name, offer_enabled, offer_type, offer_buy_qty, offer_get_qty, offer_free_shipping, offer_label
     FROM products WHERE id IN (2347,2349,2351,2353,2354) ORDER BY id"
);
while ($row = $r->fetch_assoc()) {
    echo json_encode($row, JSON_UNESCAPED_UNICODE) . "\n";
}
echo "DONE\n";
