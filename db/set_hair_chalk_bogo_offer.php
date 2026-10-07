<?php
/**
 * Hair chalk (store 8) — replace old 2-for-200 / percent with
 * Buy 2 Get 1 Free + free shipping (at qty 3). Same on every color.
 * Single unit still pays shipping.
 *
 *   php db/set_hair_chalk_bogo_offer.php
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
$slug = 'vattenloslig-harkrita-tillfallig-harfarg-event';
$skuPrefix = 'AE1005012577529604';
$buyQty = 2;
$getQty = 1;
$label = 'Köp 2 få 1 gratis';

$m = new mysqli($dbHost, $dbUser, $dbPass, $dbName);
if ($m->connect_errno) {
    fwrite(STDERR, $m->connect_error . "\n");
    exit(1);
}
$m->set_charset('utf8mb4');

$slugEsc = $m->real_escape_string($slug);
$prefixEsc = $m->real_escape_string($skuPrefix);

$q = $m->query(
    "SELECT id, store_id, name, sku, parent_sku, price, slug, offer_enabled, offer_type, offer_bundle_qty, offer_bundle_price, offer_label
     FROM products
     WHERE store_id = " . (int) $storeId . "
       AND (slug = '{$slugEsc}' OR sku = '{$prefixEsc}-S8' OR sku LIKE '{$prefixEsc}%')
     ORDER BY (slug = '{$slugEsc}') DESC, (parent_sku IS NULL OR parent_sku = '') DESC, id
     LIMIT 1"
);
$seed = $q ? $q->fetch_assoc() : null;
if (!$seed) {
    fwrite(STDERR, "Hair chalk product not found\n");
    exit(1);
}

echo "SEED id={$seed['id']} sku={$seed['sku']} parent_sku={$seed['parent_sku']} old_offer={$seed['offer_type']}/{$seed['offer_label']} bundle={$seed['offer_bundle_qty']}@{$seed['offer_bundle_price']}\n";

$parentSku = trim((string) $seed['parent_sku']);
$rootId = (int) $seed['id'];
$rootSku = trim((string) $seed['sku']);

if ($parentSku !== '') {
    $ps = $m->real_escape_string($parentSku);
    $pr = $m->query(
        "SELECT id, sku FROM products WHERE store_id = " . (int) $storeId . " AND sku = '{$ps}' LIMIT 1"
    );
    $parentRow = $pr ? $pr->fetch_assoc() : null;
    if ($parentRow) {
        $rootId = (int) $parentRow['id'];
        $rootSku = trim((string) $parentRow['sku']);
    } else {
        $rootSku = $parentSku;
    }
} elseif (substr($rootSku, -3) !== '-S8' && strpos($rootSku, $skuPrefix) === 0) {
    // Prefer store parent SKU if this seed is a color child without parent_sku set oddly
    $pq = $m->query(
        "SELECT id, sku FROM products WHERE store_id = " . (int) $storeId . " AND sku = '{$prefixEsc}-S8' LIMIT 1"
    );
    $pref = $pq ? $pq->fetch_assoc() : null;
    if ($pref) {
        $rootId = (int) $pref['id'];
        $rootSku = trim((string) $pref['sku']);
    }
}

echo "ROOT id={$rootId} sku={$rootSku}\n";

$rootSkuEsc = $m->real_escape_string($rootSku);
$family = $m->query(
    "SELECT id, sku, name, price, offer_enabled, offer_type, offer_bundle_qty, offer_bundle_price, offer_label
     FROM products
     WHERE store_id = " . (int) $storeId . "
       AND (
         id = " . (int) $rootId . "
         OR sku = '{$rootSkuEsc}'
         OR parent_sku = '{$rootSkuEsc}'
         OR sku LIKE '{$prefixEsc}%-S8'
         OR sku LIKE '{$prefixEsc}-%-S8'
       )
       AND status = 1"
);

$ids = array();
while ($row = $family->fetch_assoc()) {
    $ids[(int) $row['id']] = true;
    echo "FAMILY id={$row['id']} sku={$row['sku']} name={$row['name']} old={$row['offer_type']} {$row['offer_label']} bundle={$row['offer_bundle_qty']}@{$row['offer_bundle_price']}\n";
}

if (!$ids) {
    fwrite(STDERR, "No family rows\n");
    exit(1);
}

$labelEsc = $m->real_escape_string($label);
$idList = implode(',', array_keys($ids));
$sql = "UPDATE products SET
    offer_enabled = 1,
    offer_type = 'buy_x_get_y',
    offer_value = 0,
    offer_buy_qty = {$buyQty},
    offer_get_qty = {$getQty},
    offer_bundle_qty = 0,
    offer_bundle_price = 0,
    offer_bundle2_qty = 0,
    offer_bundle2_price = 0,
    offer_label = '{$labelEsc}',
    offer_starts_at = NULL,
    offer_ends_at = NULL,
    offer_priority = 20,
    offer_show_badge = 1,
    offer_show_countdown = 0,
    offer_free_shipping = 1,
    free_shipping = 0
  WHERE id IN ({$idList})";

if (!$m->query($sql)) {
    fwrite(STDERR, $m->error . "\n");
    exit(1);
}

$check = $m->query(
    "SELECT id, sku, name, price, offer_enabled, offer_type, offer_buy_qty, offer_get_qty, offer_bundle_qty, offer_bundle_price, offer_free_shipping, offer_label, free_shipping
     FROM products WHERE id IN ({$idList}) ORDER BY id"
);
while ($row = $check->fetch_assoc()) {
    echo 'UPDATED ' . json_encode($row, JSON_UNESCAPED_UNICODE) . "\n";
}
echo "DONE ids={$idList} buy={$buyQty} get={$getQty} free_ship_at_qty=" . ($buyQty + $getQty) . "\n";
