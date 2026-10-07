<?php
/**
 * Create ZENVello Sweden store, link country categories, auto-add catalog products.
 * Run: php db/seed_zenvello_se_store.php
 */
$dbHost = '127.0.0.1';
$dbName = 'ecommerce';
$dbUser = 'root';
$dbPass = '';
$liveConfig = '/var/www/projects/zenvello.co.uk/config.php';
if (is_file($liveConfig)) {
    include $liveConfig;
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
    fwrite(STDERR, "MySQL is not running. Start it in XAMPP, then run: php db/seed_zenvello_se_store.php\n");
    fwrite(STDERR, $mysqli->connect_error . "\n");
    exit(1);
}
$mysqli->set_charset('utf8mb4');

function col_exists($mysqli, $table, $col) {
    $r = $mysqli->query("SHOW COLUMNS FROM `{$table}` LIKE '" . $mysqli->real_escape_string($col) . "'");
    return $r && $r->num_rows > 0;
}

function table_exists($mysqli, $table) {
    $r = $mysqli->query("SHOW TABLES LIKE '" . $mysqli->real_escape_string($table) . "'");
    return $r && $r->num_rows > 0;
}

function ensure_col($mysqli, $table, $col, $sql) {
    if (!col_exists($mysqli, $table, $col)) {
        $mysqli->query($sql);
        if ($mysqli->error) {
            fwrite(STDERR, $mysqli->error . "\n");
        }
    }
}

ensure_col($mysqli, 'stores', 'auto_add_products', "ALTER TABLE stores ADD COLUMN auto_add_products TINYINT(1) NOT NULL DEFAULT 0");
ensure_col($mysqli, 'stores', 'price_plus_amount', "ALTER TABLE stores ADD COLUMN price_plus_amount DECIMAL(12,2) NOT NULL DEFAULT 0.00");
ensure_col($mysqli, 'stores', 'country_id', "ALTER TABLE stores ADD COLUMN country_id INT(11) DEFAULT NULL");
ensure_col($mysqli, 'stores', 'email', "ALTER TABLE stores ADD COLUMN email VARCHAR(190) NOT NULL DEFAULT ''");
ensure_col($mysqli, 'stores', 'password', "ALTER TABLE stores ADD COLUMN password VARCHAR(255) NOT NULL DEFAULT ''");
ensure_col($mysqli, 'stores', 'owner_name', "ALTER TABLE stores ADD COLUMN owner_name VARCHAR(150) NOT NULL DEFAULT ''");
ensure_col($mysqli, 'stores', 'currency', "ALTER TABLE stores ADD COLUMN currency VARCHAR(10) DEFAULT 'GBP'");
ensure_col($mysqli, 'stores', 'country', "ALTER TABLE stores ADD COLUMN country VARCHAR(100) DEFAULT NULL");

$mysqli->query("INSERT IGNORE INTO countries (name, code, iso3, phone_code, currency, status) VALUES ('Sweden', 'SE', 'SWE', '+46', 'SEK', 1)");

$sweden = $mysqli->query("SELECT id, name, currency FROM countries WHERE code='SE' LIMIT 1")->fetch_assoc();
if (!$sweden) {
    $sweden = $mysqli->query("SELECT id, name, currency FROM countries WHERE name LIKE '%Sweden%' LIMIT 1")->fetch_assoc();
}
if (!$sweden) {
    fwrite(STDERR, "Sweden country missing\n");
    exit(1);
}
$countryId = (int) $sweden['id'];
$currency = $sweden['currency'] ? strtoupper($sweden['currency']) : 'SEK';
echo "Sweden country_id={$countryId} currency={$currency}\n";

$theme = $mysqli->query("SELECT id FROM themes WHERE slug='zenvello' LIMIT 1")->fetch_assoc();
if (!$theme) {
    fwrite(STDERR, "ZENVello theme missing. Import db/zenvello_theme.sql first.\n");
    exit(1);
}
$themeId = (int) $theme['id'];

$email = 'owner@zenvello.se';
$password = 'ZenvelloSE26';
$domain = 'zenvello.se';
$plus = 50.00;

$store = $mysqli->query("SELECT id FROM stores WHERE domain='{$domain}' LIMIT 1")->fetch_assoc();
if ($store) {
    $storeId = (int) $store['id'];
    $mysqli->query("UPDATE stores SET
        name='ZENVello',
        email='" . $mysqli->real_escape_string($email) . "',
        password=MD5('" . $mysqli->real_escape_string($password) . "'),
        owner_name='ZENVello Owner',
        theme_id={$themeId},
        country_id={$countryId},
        status=1,
        auto_add_products=1,
        price_plus_amount={$plus}
        " . (col_exists($mysqli, 'stores', 'currency') ? ", currency='{$currency}'" : '') . "
        " . (col_exists($mysqli, 'stores', 'country') ? ", country='Sweden'" : '') . "
        " . (col_exists($mysqli, 'stores', 'language') ? ", language='sv'" : '') . "
        WHERE id={$storeId}");
    echo "Updated store id={$storeId}\n";
} else {
    $cols = array('name', 'domain', 'email', 'password', 'owner_name', 'theme_id', 'country_id', 'status', 'auto_add_products', 'price_plus_amount');
    $vals = array(
        "'ZENVello'",
        "'{$domain}'",
        "'" . $mysqli->real_escape_string($email) . "'",
        "MD5('" . $mysqli->real_escape_string($password) . "')",
        "'ZENVello Owner'",
        $themeId,
        $countryId,
        1,
        1,
        $plus,
    );
    if (col_exists($mysqli, 'stores', 'currency')) {
        $cols[] = 'currency';
        $vals[] = "'{$currency}'";
    }
    if (col_exists($mysqli, 'stores', 'country')) {
        $cols[] = 'country';
        $vals[] = "'Sweden'";
    }
    if (col_exists($mysqli, 'stores', 'language')) {
        $cols[] = 'language';
        $vals[] = "'sv'";
    }
    $mysqli->query("INSERT INTO stores (`" . implode('`,`', $cols) . "`) VALUES (" . implode(',', $vals) . ")");
    if ($mysqli->error) {
        fwrite(STDERR, $mysqli->error . "\n");
        exit(1);
    }
    $storeId = (int) $mysqli->insert_id;
    echo "Created store id={$storeId}\n";
}

$settingOverrides = array(
    'footer_text' => 'ZENVello Sweden. Alla rättigheter förbehållna.',
    'footer_about' => 'Handla smart, lev bättre med ZENVello Sweden.',
    'promo_text' => 'Fri frakt på ordrar över 500 kr',
    'hero_title' => "Gör ditt hem\ntill ditt",
    'hero_subtitle' => 'Upptäck smarta, snygga och prisvärda produkter för en bättre vardag.',
    'banner_1_kicker' => 'Utforska',
    'banner_1_title' => 'Toppval',
    'banner_1_text' => 'Utvalda favoriter och mer',
    'banner_1_btn_text' => 'Handla nu',
    'banner_2_kicker' => 'Gör det extra',
    'banner_2_title' => 'Presenttips',
    'banner_2_text' => 'Fynd som hela familjen gillar',
    'banner_2_btn_text' => 'Se presenter',
    'address' => 'Sverige',
    'email' => 'hello@zenvello.se',
    'phone' => '+46 8 000 00 00',
);

$hasThemeId = col_exists($mysqli, 'store_settings', 'theme_id');
$hasFieldKey = col_exists($mysqli, 'store_settings', 'field_key');
if ($hasFieldKey) {
    $fields = $mysqli->query("SELECT field_key, default_value FROM theme_setting_fields WHERE theme_id={$themeId}");
    while ($f = $fields->fetch_assoc()) {
        $key = $f['field_key'];
        $value = isset($settingOverrides[$key]) ? $settingOverrides[$key] : $f['default_value'];
        $ek = $mysqli->real_escape_string($key);
        $ev = $mysqli->real_escape_string($value);
        $exists = $mysqli->query("SELECT id FROM store_settings WHERE store_id={$storeId} AND field_key='{$ek}' LIMIT 1")->fetch_assoc();
        if ($exists) {
            $mysqli->query("UPDATE store_settings SET field_value='{$ev}'" . ($hasThemeId ? ", theme_id={$themeId}" : '') . " WHERE id=" . (int) $exists['id']);
        } else {
            $extraCols = $hasThemeId ? ', theme_id' : '';
            $extraVals = $hasThemeId ? ", {$themeId}" : '';
            $mysqli->query("INSERT INTO store_settings (store_id{$extraCols}, field_key, field_value) VALUES ({$storeId}{$extraVals}, '{$ek}', '{$ev}')");
        }
    }
}

$mysqli->query("CREATE TABLE IF NOT EXISTS store_category_settings (
  id INT(11) NOT NULL AUTO_INCREMENT,
  store_id INT(11) NOT NULL,
  category_id INT(11) NOT NULL,
  enabled TINYINT(1) NOT NULL DEFAULT 1,
  show_on_home TINYINT(1) NOT NULL DEFAULT 0,
  image VARCHAR(255) NOT NULL DEFAULT '',
  hero_image VARCHAR(255) NOT NULL DEFAULT '',
  seo_title VARCHAR(255) NOT NULL DEFAULT '',
  seo_description TEXT NULL,
  seo_keywords VARCHAR(255) NOT NULL DEFAULT '',
  sort_order INT(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (id),
  UNIQUE KEY store_category (store_id, category_id),
  KEY category_id (category_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

$mysqli->query("CREATE TABLE IF NOT EXISTS store_home_categories (
  store_id INT(11) NOT NULL,
  category_id INT(11) NOT NULL,
  sort_order INT(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (store_id, category_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

$wanted = array();
$byId = array();
if (table_exists($mysqli, 'categories') && col_exists($mysqli, 'categories', 'country_id')) {
    $allCats = $mysqli->query("SELECT id, parent_id, name, slug, sort_order FROM categories WHERE country_id={$countryId}");
    while ($row = $allCats->fetch_assoc()) {
        $byId[(int) $row['id']] = $row;
    }
    $imagedSql = "SELECT id, parent_id, name, slug, sort_order FROM categories
        WHERE country_id={$countryId} AND status=1";
    if (col_exists($mysqli, 'categories', 'image') && col_exists($mysqli, 'categories', 'hero_image')) {
        $imagedSql .= " AND (
            (image IS NOT NULL AND TRIM(image) <> '')
            OR (hero_image IS NOT NULL AND TRIM(hero_image) <> '')
        )";
    }
    $imagedSql .= " ORDER BY sort_order ASC, name ASC";
    $imaged = $mysqli->query($imagedSql);
    while ($row = $imaged->fetch_assoc()) {
        $cid = (int) $row['id'];
        $wanted[$cid] = $row;
        $pid = (int) $row['parent_id'];
        while ($pid > 0 && isset($byId[$pid]) && !isset($wanted[$pid])) {
            $wanted[$pid] = $byId[$pid];
            $pid = (int) $byId[$pid]['parent_id'];
        }
    }

    if (empty($wanted)) {
        echo "No Sweden categories with images; enabling all Sweden roots.\n";
        $roots = $mysqli->query("SELECT id, parent_id, name, slug, sort_order FROM categories
            WHERE country_id={$countryId} AND status=1 AND (parent_id IS NULL OR parent_id=0)
            ORDER BY sort_order ASC, name ASC");
        while ($row = $roots->fetch_assoc()) {
            $wanted[(int) $row['id']] = $row;
        }
    }
}

uasort($wanted, function ($a, $b) {
    $sa = (int) $a['sort_order'];
    $sb = (int) $b['sort_order'];
    if ($sa === $sb) {
        return strcmp($a['name'], $b['name']);
    }
    return $sa - $sb;
});

$mysqli->query("UPDATE store_category_settings SET enabled=0, show_on_home=0 WHERE store_id={$storeId}");
$mysqli->query("DELETE FROM store_home_categories WHERE store_id={$storeId}");

$sort = 1;
$homeSort = 1;
echo "Linking categories:\n";
foreach ($wanted as $cid => $row) {
    $parentId = (int) $row['parent_id'];
    $onHome = ($parentId < 1) ? 1 : 0;
    $exists = $mysqli->query("SELECT id FROM store_category_settings WHERE store_id={$storeId} AND category_id={$cid} LIMIT 1")->fetch_assoc();
    if ($exists) {
        $mysqli->query("UPDATE store_category_settings SET enabled=1, show_on_home={$onHome}, sort_order={$sort} WHERE id=" . (int) $exists['id']);
    } else {
        $mysqli->query("INSERT INTO store_category_settings (store_id, category_id, enabled, show_on_home, sort_order)
            VALUES ({$storeId}, {$cid}, 1, {$onHome}, {$sort})");
    }
    if ($onHome) {
        $mysqli->query("INSERT INTO store_home_categories (store_id, category_id, sort_order) VALUES ({$storeId}, {$cid}, {$homeSort})");
        $homeSort++;
    }
    echo "  [{$cid}] {$row['name']}" . ($onHome ? ' (home)' : '') . "\n";
    $sort++;
}
if (empty($wanted)) {
    echo "  (none yet — add Sweden categories in admin)\n";
}

$feePct = 0.0;
$feeRow = $mysqli->query("SELECT setting_value FROM platform_settings WHERE setting_key='platform_fee' LIMIT 1");
if ($feeRow && ($fr = $feeRow->fetch_assoc())) {
    $feePct = (float) $fr['setting_value'];
}

$addedProducts = 0;
$catalog = $mysqli->query("SELECT p.*, u.commission AS owner_commission
    FROM products p
    LEFT JOIN users u ON u.UserID = p.created_by
    WHERE p.status=1 AND (p.store_id IS NULL OR p.store_id=0)
      AND p.country_id={$countryId}");
while ($p = $catalog->fetch_assoc()) {
    $sourceId = (int) $p['id'];
    $exists = $mysqli->query("SELECT id FROM products WHERE store_id={$storeId} AND source_product_id={$sourceId} LIMIT 1")->fetch_assoc();
    if ($exists) {
        continue;
    }
    $base = (isset($p['cost_price']) && (float) $p['cost_price'] > 0) ? (float) $p['cost_price'] : (float) $p['price'];
    $commission = (float) $p['owner_commission'];
    $platformFee = round($base * ($feePct / 100), 2);
    $wholesale = round($base + $commission + $platformFee, 2);
    $price = round($wholesale + $plus, 2);
    $slug = $mysqli->real_escape_string($p['slug'] . '-s' . $storeId);
    $sku = $mysqli->real_escape_string(trim((string) $p['sku']) !== '' ? $p['sku'] . '-S' . $storeId : '');
    $name = $mysqli->real_escape_string($p['name']);
    $desc = $mysqli->real_escape_string((string) $p['description']);
    $details = $mysqli->real_escape_string(isset($p['details']) ? (string) $p['details'] : '');
    $image = $mysqli->real_escape_string((string) $p['image']);
    $seoTitle = $mysqli->real_escape_string((string) $p['seo_title']);
    $seoDesc = $mysqli->real_escape_string((string) $p['seo_description']);
    $seoKeys = $mysqli->real_escape_string((string) $p['seo_keywords']);
    $supplierId = $p['supplier_id'] === null ? 'NULL' : (int) $p['supplier_id'];
    $createdBy = $p['created_by'] === null ? 'NULL' : (int) $p['created_by'];
    $compare = (float) $p['compare_price'];
    $maxSale = (float) $p['max_sale_price'];
    $stock = (int) $p['stock'];
    $shipMin = isset($p['ship_min_days']) ? (int) $p['ship_min_days'] : 0;
    $shipMax = isset($p['ship_max_days']) ? (int) $p['ship_max_days'] : 0;
    $prodCountry = $p['country_id'] ? (int) $p['country_id'] : $countryId;
    $brandCol = col_exists($mysqli, 'products', 'brand') ? ', brand' : '';
    $brandVal = col_exists($mysqli, 'products', 'brand') ? ", '" . $mysqli->real_escape_string(isset($p['brand']) ? (string) $p['brand'] : '') . "'" : '';

    $detailCol = col_exists($mysqli, 'products', 'details') ? ', details' : '';
    $detailVal = col_exists($mysqli, 'products', 'details') ? ", '{$details}'" : '';
    $shipCols = col_exists($mysqli, 'products', 'ship_min_days') ? ', ship_min_days, ship_max_days' : '';
    $shipVals = col_exists($mysqli, 'products', 'ship_min_days') ? ", {$shipMin}, {$shipMax}" : '';

    $mysqli->query("INSERT INTO products
        (store_id, source_product_id, supplier_id, country_id, name, sku{$brandCol}, slug, price, max_sale_price, compare_price, cost_price, stock, description{$detailCol}, seo_title, seo_description, seo_keywords, image{$shipCols}, status, created_by)
        VALUES
        ({$storeId}, {$sourceId}, {$supplierId}, {$prodCountry}, '{$name}', '{$sku}'{$brandVal}, '{$slug}', {$price}, {$maxSale}, {$compare}, {$wholesale}, {$stock}, '{$desc}'{$detailVal}, '{$seoTitle}', '{$seoDesc}', '{$seoKeys}', '{$image}'{$shipVals}, 1, {$createdBy})");
    if ($mysqli->error) {
        echo "Product copy failed {$sourceId}: {$mysqli->error}\n";
        continue;
    }
    $copyId = (int) $mysqli->insert_id;
    $addedProducts++;

    $cats = $mysqli->query("SELECT category_id FROM product_categories WHERE product_id={$sourceId}");
    while ($c = $cats->fetch_assoc()) {
        $mysqli->query("INSERT IGNORE INTO product_categories (product_id, category_id) VALUES ({$copyId}, " . (int) $c['category_id'] . ")");
    }
    $imgs = $mysqli->query("SELECT image FROM product_images WHERE product_id={$sourceId}");
    if ($imgs) {
        while ($img = $imgs->fetch_assoc()) {
            $mysqli->query("INSERT INTO product_images (product_id, image) VALUES ({$copyId}, '" . $mysqli->real_escape_string($img['image']) . "')");
        }
    }
    $attrs = $mysqli->query("SELECT name, values_text, sort_order FROM product_attributes WHERE product_id={$sourceId}");
    if ($attrs) {
        while ($a = $attrs->fetch_assoc()) {
            $mysqli->query("INSERT INTO product_attributes (product_id, name, values_text, sort_order) VALUES ({$copyId}, '" . $mysqli->real_escape_string($a['name']) . "', '" . $mysqli->real_escape_string($a['values_text']) . "', " . (int) $a['sort_order'] . ")");
        }
    }
    $vars = $mysqli->query("SELECT option_name, option_value, sku, price, stock, combination_key, attributes_json, image FROM product_variations WHERE product_id={$sourceId}");
    if ($vars) {
        while ($v = $vars->fetch_assoc()) {
            $mysqli->query("INSERT INTO product_variations (product_id, option_name, option_value, sku, price, stock, combination_key, attributes_json, image) VALUES (
                {$copyId},
                '" . $mysqli->real_escape_string($v['option_name']) . "',
                '" . $mysqli->real_escape_string($v['option_value']) . "',
                '" . $mysqli->real_escape_string($v['sku']) . "',
                " . (float) $v['price'] . ",
                " . (int) $v['stock'] . ",
                '" . $mysqli->real_escape_string($v['combination_key']) . "',
                " . ($v['attributes_json'] === null ? 'NULL' : "'" . $mysqli->real_escape_string($v['attributes_json']) . "'") . ",
                '" . $mysqli->real_escape_string($v['image']) . "'
            )");
        }
    }
}

echo "Auto-added catalog products: {$addedProducts}\n";
echo "\nZENVello Sweden login\n";
echo "  Domain:   {$domain}\n";
echo "  Email:    {$email}\n";
echo "  Password: {$password}\n";
echo "  Country:  Sweden ({$currency})\n";
echo "  Plus:     {$plus}\n";
echo "  Auto-add: on\n";
echo "  Panel:    https://zenvello.se/store/login\n";
echo "  Site:     http://zenvello.se/\n";
echo "  Local:    http://zenvellose.localhost/\n";
