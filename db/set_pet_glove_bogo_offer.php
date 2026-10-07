<?php
/**
 * Pet hair removal glove (store 8) — Buy 2 Get 1 Free + free shipping
 * when offer qty met (3). Other qtys pay shipping.
 *
 *   php db/set_pet_glove_bogo_offer.php
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
$slug = 'harborttagningshandske-husdjur-elektrostatisk';
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
$q = $m->query(
    "SELECT id, store_id, name, sku, parent_sku, source_product_id, price, slug, status
     FROM products
     WHERE store_id = " . (int) $storeId . "
       AND slug = '{$slugEsc}'
     LIMIT 1"
);
$product = $q ? $q->fetch_assoc() : null;
if (!$product) {
    // Fallback: SKU family from AliExpress id in slug/page
    $q = $m->query(
        "SELECT id, store_id, name, sku, parent_sku, source_product_id, price, slug, status
         FROM products
         WHERE store_id = " . (int) $storeId . "
           AND (sku LIKE 'AE1005012917688341%' OR parent_sku LIKE 'AE1005012917688341%')
           AND (parent_sku IS NULL OR parent_sku = '' OR sku = parent_sku)
         ORDER BY id
         LIMIT 5"
    );
    $product = $q ? $q->fetch_assoc() : null;
}
if (!$product) {
    fwrite(STDERR, "Product not found for slug {$slug}\n");
    exit(1);
}

echo "FOUND parent-ish: id={$product['id']} sku={$product['sku']} parent_sku={$product['parent_sku']} slug={$product['slug']}\n";

// Resolve family root (store parent): product whose sku is used as parent_sku, or this row if it has children.
$parentSku = trim((string) $product['parent_sku']);
$rootId = (int) $product['id'];
$rootSku = trim((string) $product['sku']);

if ($parentSku !== '') {
    $ps = $m->real_escape_string($parentSku);
    $pr = $m->query(
        "SELECT id, sku, name, parent_sku FROM products
         WHERE store_id = " . (int) $storeId . " AND sku = '{$ps}' LIMIT 1"
    );
    $parentRow = $pr ? $pr->fetch_assoc() : null;
    if ($parentRow) {
        $rootId = (int) $parentRow['id'];
        $rootSku = trim((string) $parentRow['sku']);
        echo "ROOT via parent_sku: id={$rootId} sku={$rootSku}\n";
    } else {
        // Current row is a child but parent missing — apply to this + siblings by parent_sku
        $rootSku = $parentSku;
        echo "ROOT sku (missing parent row): {$rootSku}\n";
    }
} else {
    echo "ROOT is this product (no parent_sku)\n";
}

$labelEsc = $m->real_escape_string($label);
$ids = array();

// Always set on root row if it exists in DB
if ($rootId > 0) {
    $ids[$rootId] = true;
}

$rootSkuEsc = $m->real_escape_string($rootSku);
// Apply only to parent + 1 Piece (qty-based BOGO). Multipacks keep normal pricing/shipping.
$children = $m->query(
    "SELECT id, sku, name, price FROM products
     WHERE store_id = " . (int) $storeId . "
       AND (id = " . (int) $rootId . " OR parent_sku = '{$rootSkuEsc}' OR sku = '{$rootSkuEsc}')"
);
while ($row = $children->fetch_assoc()) {
    $sku = strtoupper((string) $row['sku']);
    $name = strtolower((string) $row['name']);
    $isRoot = ((int) $row['id'] === (int) $rootId) || ($sku === strtoupper($rootSku));
    $isOnePiece = (strpos($sku, '1-PACK') !== false) || (strpos($name, '1 piece') !== false);
    if (!$isRoot && !$isOnePiece) {
        echo "SKIP multipack id={$row['id']} sku={$row['sku']} name={$row['name']}\n";
        continue;
    }
    $ids[(int) $row['id']] = true;
    echo "FAMILY id={$row['id']} sku={$row['sku']} name={$row['name']} price={$row['price']}\n";
}

if (!$ids) {
    fwrite(STDERR, "No products in family\n");
    exit(1);
}

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
    "SELECT id, sku, name, offer_enabled, offer_type, offer_buy_qty, offer_get_qty, offer_free_shipping, offer_label, free_shipping
     FROM products WHERE id IN ({$idList}) ORDER BY id"
);
while ($row = $check->fetch_assoc()) {
    echo 'UPDATED ' . json_encode($row, JSON_UNESCAPED_UNICODE) . "\n";
}
echo "DONE ids={$idList} buy={$buyQty} get={$getQty} free_ship_at_qty=" . ($buyQty + $getQty) . "\n";
