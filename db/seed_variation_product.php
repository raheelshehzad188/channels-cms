<?php
/**
 * Seed one parent product with two child products (dummy images).
 * Run: php db/seed_variation_product.php
 */
$dbHost = '127.0.0.1';
$dbName = 'ecommerce';
$dbUser = 'root';
$dbPass = '';
$configFile = dirname(__DIR__) . '/config.php';
if (is_file($configFile)) {
    include $configFile;
    if (isset($local_config) && is_array($local_config)) {
        $dbHost = isset($local_config['db_host']) ? $local_config['db_host'] : $dbHost;
        $dbName = isset($local_config['db_name']) ? $local_config['db_name'] : $dbName;
        $dbUser = isset($local_config['db_user']) ? $local_config['db_user'] : $dbUser;
        $dbPass = isset($local_config['db_pass']) ? $local_config['db_pass'] : $dbPass;
    }
}

mysqli_report(MYSQLI_REPORT_OFF);
$mysqli = @new mysqli($dbHost, $dbUser, $dbPass, $dbName);
if ($mysqli->connect_errno) {
    fwrite(STDERR, "MySQL is not running.\n" . $mysqli->connect_error . "\n");
    exit(1);
}
$mysqli->set_charset('utf8mb4');

function col_exists($mysqli, $table, $col)
{
    $r = $mysqli->query("SHOW COLUMNS FROM `{$table}` LIKE '" . $mysqli->real_escape_string($col) . "'");
    return $r && $r->num_rows > 0;
}

function ensure_col($mysqli, $table, $col, $sql)
{
    if (!col_exists($mysqli, $table, $col)) {
        $mysqli->query($sql);
        if ($mysqli->error) {
            fwrite(STDERR, $mysqli->error . "\n");
        }
    }
}

if (!col_exists($mysqli, 'products', 'parent_sku')) {
    $mysqli->query("ALTER TABLE products ADD COLUMN parent_sku VARCHAR(100) NOT NULL DEFAULT '' AFTER sku");
}
if (!col_exists($mysqli, 'products', 'is_default')) {
    $mysqli->query("ALTER TABLE products ADD COLUMN is_default TINYINT(1) NOT NULL DEFAULT 0 AFTER parent_sku");
}
$mysqli->query("CREATE TABLE IF NOT EXISTS product_images (
  id INT(11) NOT NULL AUTO_INCREMENT,
  product_id INT(11) NOT NULL,
  image VARCHAR(255) NOT NULL DEFAULT '',
  sort_order INT(11) NOT NULL DEFAULT 0,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY product_id (product_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

$root = dirname(__DIR__);
$uploadDir = $root . '/uploads/products';
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

$copies = array(
    'led-mask-parent.jpg' => $root . '/assets/frontend/zenvello/assets/images/products/mask-main.jpg',
    'led-mask-black.jpg' => $root . '/assets/frontend/zenvello/assets/images/products/h-led-mask.jpg',
    'led-mask-white.jpg' => $root . '/assets/frontend/zenvello/assets/images/products/led-mask.jpg',
    'led-mask-thumb1.jpg' => $root . '/assets/frontend/zenvello/assets/images/products/mask-thumb1.jpg',
    'led-mask-thumb2.jpg' => $root . '/assets/frontend/zenvello/assets/images/products/mask-thumb3.jpg',
);
foreach ($copies as $dest => $src) {
    if (is_file($src) && !is_file($uploadDir . '/' . $dest)) {
        @copy($src, $uploadDir . '/' . $dest);
    }
}

$mysqli->query("CREATE TABLE IF NOT EXISTS stores (
  id INT(11) NOT NULL AUTO_INCREMENT,
  name VARCHAR(255) NOT NULL,
  domain VARCHAR(255) NOT NULL,
  theme_id INT(11) DEFAULT NULL,
  status TINYINT(1) NOT NULL DEFAULT 1,
  country_id INT(11) DEFAULT NULL,
  currency VARCHAR(10) NOT NULL DEFAULT 'GBP',
  auto_add_products TINYINT(1) NOT NULL DEFAULT 0,
  price_plus_amount DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  email VARCHAR(190) NOT NULL DEFAULT '',
  password VARCHAR(255) NOT NULL DEFAULT '',
  owner_name VARCHAR(150) NOT NULL DEFAULT '',
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY domain (domain)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

ensure_col($mysqli, 'stores', 'country_id', "ALTER TABLE stores ADD COLUMN country_id INT(11) DEFAULT NULL");
ensure_col($mysqli, 'stores', 'currency', "ALTER TABLE stores ADD COLUMN currency VARCHAR(10) NOT NULL DEFAULT 'GBP'");
ensure_col($mysqli, 'stores', 'theme_id', "ALTER TABLE stores ADD COLUMN theme_id INT(11) DEFAULT NULL");
ensure_col($mysqli, 'stores', 'auto_add_products', "ALTER TABLE stores ADD COLUMN auto_add_products TINYINT(1) NOT NULL DEFAULT 0");
ensure_col($mysqli, 'stores', 'price_plus_amount', "ALTER TABLE stores ADD COLUMN price_plus_amount DECIMAL(12,2) NOT NULL DEFAULT 0.00");

$theme = $mysqli->query("SELECT id FROM themes WHERE slug='zenvello' LIMIT 1")->fetch_assoc();
$themeId = $theme ? (int) $theme['id'] : 4;
$targetStores = array();
$storeRows = $mysqli->query("SELECT id, name, domain, country_id FROM stores WHERE status=1 ORDER BY id");
if ($storeRows) {
    while ($row = $storeRows->fetch_assoc()) {
        $targetStores[] = $row;
    }
}
if (empty($targetStores)) {
    $store = $mysqli->query("SELECT id, name, domain, country_id FROM stores WHERE domain IN ('zenvello.ecommerce.test','zenvello.localhost') LIMIT 1")->fetch_assoc();
    if (!$store) {
        $mysqli->query("INSERT INTO stores (name, domain, theme_id, status, country_id, currency, auto_add_products, owner_name, email)
            VALUES ('ZENVello Test', 'zenvello.ecommerce.test', {$themeId}, 1, 5, 'GBP', 0, 'Test Owner', 'owner@zenvello.test')");
        $targetStores[] = array(
            'id' => (int) $mysqli->insert_id,
            'name' => 'ZENVello Test',
            'domain' => 'zenvello.ecommerce.test',
            'country_id' => 5,
        );
        echo "Created store domain=zenvello.ecommerce.test\n";
    } else {
        $targetStores[] = $store;
    }
}

$mysqli->query("CREATE TABLE IF NOT EXISTS store_settings (
  id INT(11) NOT NULL AUTO_INCREMENT,
  store_id INT(11) NOT NULL,
  theme_id INT(11) NOT NULL,
  field_key VARCHAR(100) NOT NULL,
  field_value TEXT,
  PRIMARY KEY (id),
  UNIQUE KEY store_field (store_id, field_key)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

$hasBrand = col_exists($mysqli, 'products', 'brand');
$hasMadeBy = col_exists($mysqli, 'products', 'made_by');

function upsert_product($mysqli, $row)
{
    $sku = $mysqli->real_escape_string($row['sku']);
    $storeSql = $row['store_id'] === null ? 'store_id IS NULL' : 'store_id=' . (int) $row['store_id'];
    $existing = $mysqli->query("SELECT id FROM products WHERE sku='{$sku}' AND {$storeSql} LIMIT 1")->fetch_assoc();
    $fields = array();
    foreach ($row as $key => $value) {
        if ($value === null) {
            continue;
        }
        $fields[] = "`{$key}`='" . $mysqli->real_escape_string((string) $value) . "'";
    }
    $set = implode(', ', $fields);
    if ($existing) {
        $id = (int) $existing['id'];
        $mysqli->query("UPDATE products SET {$set} WHERE id={$id}");
        return $id;
    }
    $mysqli->query("INSERT INTO products SET {$set}");
    if ($mysqli->error) {
        fwrite(STDERR, $mysqli->error . "\n");
    }
    return (int) $mysqli->insert_id;
}

function set_gallery($mysqli, $productId, $images)
{
    $mysqli->query("DELETE FROM product_images WHERE product_id=" . (int) $productId);
    $sort = 1;
    foreach ($images as $image) {
        $img = $mysqli->real_escape_string($image);
        $mysqli->query("INSERT INTO product_images (product_id, image, sort_order) VALUES (" . (int) $productId . ", '{$img}', {$sort})");
        $sort++;
    }
}

$parentDetails = '<h2>About this item</h2><ul><li>Light-up LED Halloween mask</li><li>Two colour options: Black and White</li><li>Dummy test product for parent/child options</li></ul>';

$catalog = array(
    array(
        'role' => 'parent',
        'name' => 'LED Halloween Mask',
        'sku' => 'LED-MASK',
        'parent_sku' => '',
        'is_default' => 0,
        'slug' => 'led-halloween-mask',
        'price' => '24.99',
        'compare_price' => '34.99',
        'stock' => 20,
        'image' => 'uploads/products/led-mask-parent.jpg',
        'gallery' => array('uploads/products/led-mask-thumb1.jpg', 'uploads/products/led-mask-thumb2.jpg'),
    ),
    array(
        'role' => 'black',
        'name' => 'Black',
        'sku' => 'LED-MASK-BLK',
        'parent_sku' => 'LED-MASK',
        'is_default' => 1,
        'slug' => 'led-halloween-mask-black',
        'price' => '24.99',
        'compare_price' => '34.99',
        'stock' => 12,
        'image' => 'uploads/products/led-mask-black.jpg',
        'gallery' => array(),
    ),
    array(
        'role' => 'white',
        'name' => 'White',
        'sku' => 'LED-MASK-WHT',
        'parent_sku' => 'LED-MASK',
        'is_default' => 0,
        'slug' => 'led-halloween-mask-white',
        'price' => '26.99',
        'compare_price' => '36.99',
        'stock' => 8,
        'image' => 'uploads/products/led-mask-white.jpg',
        'gallery' => array(),
    ),
);

$catalogIds = array();
foreach ($catalog as $item) {
    $payload = array(
        'store_id' => null,
        'supplier_id' => 5,
        'country_id' => 5,
        'created_by' => 2,
        'name' => $item['name'],
        'slug' => $item['slug'],
        'sku' => $item['sku'],
        'parent_sku' => $item['parent_sku'],
        'is_default' => $item['is_default'],
        'price' => $item['price'],
        'compare_price' => $item['compare_price'],
        'cost_price' => $item['price'],
        'max_sale_price' => '39.99',
        'stock' => $item['stock'],
        'ship_min_days' => 3,
        'ship_max_days' => 7,
        'description' => 'Choose a colour. Opening the parent page shows both options above Add to Cart. Details stay on the parent product.',
        'details' => $parentDetails,
        'image' => $item['image'],
        'status' => 1,
        'seo_title' => $item['name'] === 'LED Halloween Mask' ? 'LED Halloween Mask' : ('LED Halloween Mask — ' . $item['name']),
        'seo_description' => 'Dummy parent/child variation product for testing.',
    );
    if ($hasBrand) {
        $payload['brand'] = 'ZENVello';
    }
    if ($hasMadeBy) {
        $payload['made_by'] = 'ZENVello';
    }
    $id = upsert_product($mysqli, $payload);
    $catalogIds[$item['role']] = $id;
    set_gallery($mysqli, $id, $item['gallery']);
    echo "Catalog {$item['role']} id={$id} sku={$item['sku']}\n";
}

foreach ($targetStores as $store) {
    $storeId = (int) $store['id'];
    $storeCountry = !empty($store['country_id']) ? (int) $store['country_id'] : 5;
    echo "Store {$store['domain']} id={$storeId}\n";
    foreach ($catalog as $item) {
        $storeSku = $item['sku'] . '-S' . $storeId;
        $storeParent = $item['parent_sku'] === '' ? '' : $item['parent_sku'] . '-S' . $storeId;
        $payload = array(
            'store_id' => $storeId,
            'source_product_id' => $catalogIds[$item['role']],
            'supplier_id' => 5,
            'country_id' => $storeCountry,
            'created_by' => 2,
            'name' => $item['name'],
            'slug' => $item['slug'],
            'sku' => $storeSku,
            'parent_sku' => $storeParent,
            'is_default' => $item['is_default'],
            'price' => $item['price'],
            'compare_price' => $item['compare_price'],
            'cost_price' => $item['price'],
            'max_sale_price' => '39.99',
            'stock' => $item['stock'],
            'ship_min_days' => 3,
            'ship_max_days' => 7,
            'description' => 'Choose a colour. Opening the parent page shows both options above Add to Cart. Details stay on the parent product.',
            'details' => $parentDetails,
            'image' => $item['image'],
            'status' => 1,
            'seo_title' => $item['name'] === 'LED Halloween Mask' ? 'LED Halloween Mask' : ('LED Halloween Mask — ' . $item['name']),
            'seo_description' => 'Dummy parent/child variation product for testing.',
        );
        if ($hasBrand) {
            $payload['brand'] = 'ZENVello';
        }
        if ($hasMadeBy) {
            $payload['made_by'] = 'ZENVello';
        }
        $id = upsert_product($mysqli, $payload);
        set_gallery($mysqli, $id, $item['gallery']);
        echo "  {$item['role']} id={$id} sku={$storeSku}\n";
    }
}

echo "\nTest links:\n";
foreach ($targetStores as $store) {
    $host = $store['domain'];
    $scheme = (strpos($host, 'localhost') !== false || strpos($host, '.test') !== false) ? 'http' : 'https';
    echo "{$store['name']}: {$scheme}://{$host}/product/led-halloween-mask\n";
}
