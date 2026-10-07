<?php
/**
 * Car seat organizer (store 8):
 * - Set extra_amount = 80 on every child (replace old, do not add)
 * - Adjust list price by (80 - old_extra)
 * - Buy 2 Get 1 Free + free shipping at offer qty (3)
 * - Clear product-level free_shipping so single pays shipping
 *
 *   php db/set_car_organizer_extra_bogo.php
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
$slug = 'multifunktionell-bilstolsorganisator-surfplattehallare-sparkskydd';
$skuPrefix = 'AE1005012323014703';
$extra = 80.0;
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

$seed = $m->query(
    "SELECT id, sku, parent_sku, name, price, extra_amount, free_shipping, slug
     FROM products
     WHERE store_id = " . (int) $storeId . "
       AND (slug = '{$slugEsc}' OR sku = '{$prefixEsc}-S8' OR sku LIKE '{$prefixEsc}%')
     ORDER BY (slug = '{$slugEsc}') DESC, (parent_sku IS NULL OR parent_sku = '') DESC, id
     LIMIT 1"
)->fetch_assoc();

if (!$seed) {
    fwrite(STDERR, "Product not found\n");
    exit(1);
}

echo "SEED id={$seed['id']} sku={$seed['sku']} parent_sku={$seed['parent_sku']} free_ship={$seed['free_shipping']}\n";

$parentSku = trim((string) $seed['parent_sku']);
$rootId = (int) $seed['id'];
$rootSku = trim((string) $seed['sku']);

if ($parentSku !== '') {
    $ps = $m->real_escape_string($parentSku);
    $pr = $m->query("SELECT id, sku FROM products WHERE store_id=" . (int) $storeId . " AND sku='{$ps}' LIMIT 1");
    $parentRow = $pr ? $pr->fetch_assoc() : null;
    if ($parentRow) {
        $rootId = (int) $parentRow['id'];
        $rootSku = trim((string) $parentRow['sku']);
    } else {
        $rootSku = $parentSku;
    }
} else {
    $pq = $m->query("SELECT id, sku FROM products WHERE store_id=" . (int) $storeId . " AND sku='{$prefixEsc}-S8' LIMIT 1");
    $pref = $pq ? $pq->fetch_assoc() : null;
    if ($pref) {
        $rootId = (int) $pref['id'];
        $rootSku = trim((string) $pref['sku']);
    }
}

echo "ROOT id={$rootId} sku={$rootSku}\n";
$rootSkuEsc = $m->real_escape_string($rootSku);

$family = $m->query(
    "SELECT id, sku, name, price, extra_amount, free_shipping, source_product_id, parent_sku
     FROM products
     WHERE store_id = " . (int) $storeId . "
       AND status = 1
       AND (
         id = " . (int) $rootId . "
         OR sku = '{$rootSkuEsc}'
         OR parent_sku = '{$rootSkuEsc}'
         OR sku LIKE '{$prefixEsc}%-S8'
       )
     ORDER BY id"
);

$rows = array();
while ($row = $family->fetch_assoc()) {
    $rows[] = $row;
}
if (!$rows) {
    fwrite(STDERR, "No family rows\n");
    exit(1);
}

$labelEsc = $m->real_escape_string($label);
$ids = array();
$catalogIds = array();

foreach ($rows as $row) {
    $id = (int) $row['id'];
    $ids[$id] = true;
    $oldExtra = round((float) $row['extra_amount'], 2);
    $oldPrice = round((float) $row['price'], 2);
    $isChild = trim((string) $row['parent_sku']) !== '';
    $isRoot = ($id === $rootId) || (trim((string) $row['sku']) === $rootSku);

    // User asked: all children get extra=80 (replace). Also bump parent listing for consistency if it is sold.
    $applyExtra = $isChild || $isRoot;
    if ($applyExtra) {
        $newPrice = round($oldPrice - $oldExtra + $extra, 2);
        if ($newPrice < 0) {
            $newPrice = 0;
        }
        $sql = "UPDATE products SET
            extra_amount = {$extra},
            price = {$newPrice},
            free_shipping = 0,
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
            offer_free_shipping = 1
          WHERE id = {$id}";
        if (!$m->query($sql)) {
            fwrite(STDERR, "FAIL id={$id} " . $m->error . "\n");
            exit(1);
        }
        echo "UPD id={$id} {$row['sku']} {$row['name']} extra {$oldExtra}->{$extra} price {$oldPrice}->{$newPrice}\n";
    }

    if (!empty($row['source_product_id'])) {
        $catalogIds[(int) $row['source_product_id']] = true;
    }
}

// Keep catalog source extra in sync so future resyncs keep +80
if ($catalogIds) {
    $catList = implode(',', array_keys($catalogIds));
    $m->query("UPDATE products SET extra_amount = {$extra} WHERE id IN ({$catList})");
    echo "CATALOG_EXTRA ids={$catList} => {$extra}\n";
}

$idList = implode(',', array_keys($ids));
$check = $m->query(
    "SELECT id, sku, name, price, extra_amount, free_shipping, offer_enabled, offer_type, offer_buy_qty, offer_get_qty, offer_free_shipping, offer_label
     FROM products WHERE id IN ({$idList}) ORDER BY id"
);
while ($row = $check->fetch_assoc()) {
    echo 'FINAL ' . json_encode($row, JSON_UNESCAPED_UNICODE) . "\n";
}
echo "DONE\n";
