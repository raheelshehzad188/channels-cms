<?php
/**
 * Rebuild AE1005007662261213 family to SIZE-ONLY children (S/M/L/XL).
 * Deletes Set×Size children, keeps/creates one child per size, syncs store copies.
 *
 * Usage:
 *   php db/rebuild_underwear_sizes_only.php
 *   php db/rebuild_underwear_sizes_only.php --ae-url=https://www.aliexpress.com/item/1005007662261213.html
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

$aeUrl = 'https://www.aliexpress.com/item/1005007662261213.html';
foreach ($argv as $arg) {
    if (strpos($arg, '--ae-url=') === 0) {
        $aeUrl = substr($arg, 9);
    }
}

$aeId = '1005007662261213';
$parentSku = 'AE' . $aeId;
$storeParentSku = $parentSku . '-S8';

$m = new mysqli($dbHost, $dbUser, $dbPass, $dbName);
if ($m->connect_error) {
    fwrite(STDERR, $m->connect_error . "\n");
    exit(1);
}
$m->set_charset('utf8mb4');

function fetch_ae_sizes($url)
{
    $sizes = array();
    $ch = curl_init($url);
    curl_setopt_array($ch, array(
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_TIMEOUT => 40,
        CURLOPT_USERAGENT => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124.0.0.0 Safari/537.36',
        CURLOPT_HTTPHEADER => array(
            'Accept-Language: en-US,en;q=0.9',
            'Referer: https://www.aliexpress.com/',
        ),
    ));
    $html = curl_exec($ch);
    curl_close($ch);
    if (!is_string($html) || $html === '') {
        return $sizes;
    }
    if (preg_match_all('/skuPropertyName"\s*:\s*"(Size|Storlek)"(.*?)(?:skuPropertyName"|skuPriceList)/is', $html, $blocks)) {
        foreach ($blocks[2] as $block) {
            if (preg_match_all('/"(?:propertyValueDisplayName|propertyValueName|skuPropertyTips)"\s*:\s*"([^"]+)"/i', $block, $matches)) {
                foreach ($matches[1] as $raw) {
                    $label = trim(html_entity_decode($raw, ENT_QUOTES, 'UTF-8'));
                    if (preg_match('/^(XXS|XS|S|M|L|XL|XXL|XXXL|[1-5]XL)$/i', $label)) {
                        $sizes[strtoupper($label)] = strtoupper($label);
                    }
                }
            }
        }
    }
    return array_values($sizes);
}

$sizes = fetch_ae_sizes($aeUrl);
if (!$sizes) {
    $sizes = array('S', 'M', 'L', 'XL');
    echo "AE sizes fallback: " . implode(',', $sizes) . "\n";
} else {
    echo "AE sizes: " . implode(',', $sizes) . "\n";
}

function catalog_parent($m, $parentSku)
{
    $stmt = $m->prepare("SELECT * FROM products WHERE sku = ? AND (store_id IS NULL OR store_id = 0) AND TRIM(IFNULL(parent_sku,'')) = '' LIMIT 1");
    $stmt->bind_param('s', $parentSku);
    $stmt->execute();
    $res = $stmt->get_result();
    $row = $res ? $res->fetch_assoc() : null;
    $stmt->close();
    return $row;
}

function store_parent($m, $storeParentSku, $storeId = 8)
{
    $stmt = $m->prepare("SELECT * FROM products WHERE sku = ? AND store_id = ? AND TRIM(IFNULL(parent_sku,'')) = '' LIMIT 1");
    $stmt->bind_param('si', $storeParentSku, $storeId);
    $stmt->execute();
    $res = $stmt->get_result();
    $row = $res ? $res->fetch_assoc() : null;
    $stmt->close();
    return $row;
}

function delete_product_row($m, $id)
{
    $id = (int) $id;
    if ($id < 1) {
        return;
    }
    foreach (array(
        'product_images' => 'product_id',
        'product_categories' => 'product_id',
        'product_attributes' => 'product_id',
        'product_variations' => 'product_id',
        'product_sources' => 'product_id',
        'product_creatives' => 'product_id',
        'product_faqs' => 'product_id',
        'product_reviews' => 'product_id',
    ) as $table => $col) {
        $chk = $m->query("SHOW TABLES LIKE '" . $m->real_escape_string($table) . "'");
        if ($chk && $chk->num_rows > 0) {
            $m->query("DELETE FROM `{$table}` WHERE `{$col}` = {$id}");
        }
    }
    $m->query("DELETE FROM products WHERE id = {$id}");
}

function pick_source_child_for_size($m, $parentSku, $storeId, $size)
{
    $size = strtoupper($size);
    $sql = "SELECT * FROM products
            WHERE TRIM(IFNULL(parent_sku,'')) = ?
              AND " . ($storeId === null
                ? "(store_id IS NULL OR store_id = 0)"
                : "store_id = " . (int) $storeId) . "
              AND (
                    sku REGEXP CONCAT('-', ?, '(-S[0-9]+)?$')
                 OR name REGEXP CONCAT('[[:space:]/]', ?, '[[:space:]]*$')
              )
            ORDER BY
              CASE WHEN sku LIKE '%-SET-4-%' THEN 0
                   WHEN sku LIKE '%-SET-1-%' THEN 1
                   ELSE 2 END,
              price ASC, id ASC
            LIMIT 1";
    $stmt = $m->prepare($sql);
    $stmt->bind_param('sss', $parentSku, $size, $size);
    $stmt->execute();
    $res = $stmt->get_result();
    $row = $res ? $res->fetch_assoc() : null;
    $stmt->close();
    return $row;
}

function unique_slug($m, $base, $excludeId = 0)
{
    $slug = strtolower(trim(preg_replace('/[^a-zA-Z0-9]+/', '-', $base), '-'));
    if ($slug === '') {
        $slug = 'product';
    }
    $try = $slug;
    $n = 1;
    while (true) {
        $stmt = $m->prepare("SELECT id FROM products WHERE slug = ? AND id <> ? LIMIT 1");
        $stmt->bind_param('si', $try, $excludeId);
        $stmt->execute();
        $res = $stmt->get_result();
        $exists = $res && $res->fetch_assoc();
        $stmt->close();
        if (!$exists) {
            return $try;
        }
        $n++;
        $try = $slug . '-' . $n;
    }
}

$parent = catalog_parent($m, $parentSku);
if (!$parent) {
    fwrite(STDERR, "Catalog parent not found for {$parentSku}\n");
    exit(1);
}
echo "catalog_parent={$parent['id']}\n";

$storeParent = store_parent($m, $storeParentSku, 8);
if (!$storeParent) {
    // fallback: source_product_id link
    $pid = (int) $parent['id'];
    $storeParent = $m->query("SELECT * FROM products WHERE store_id=8 AND source_product_id={$pid} AND TRIM(IFNULL(parent_sku,''))='' LIMIT 1")->fetch_assoc();
}
if (!$storeParent) {
    fwrite(STDERR, "Store parent not found\n");
    exit(1);
}
echo "store_parent={$storeParent['id']} sku={$storeParent['sku']}\n";

// Collect keep candidates per size from catalog, then delete other children
$keepCatalog = array();
$keepStore = array();
foreach ($sizes as $size) {
    $src = pick_source_child_for_size($m, $parentSku, null, $size);
    if ($src) {
        $keepCatalog[$size] = $src;
        echo "keep_catalog {$size} id={$src['id']} sku={$src['sku']} price={$src['price']}\n";
    } else {
        echo "missing_catalog_source {$size}\n";
    }
    $srcS = pick_source_child_for_size($m, $storeParent['sku'], 8, $size);
    if ($srcS) {
        $keepStore[$size] = $srcS;
        echo "keep_store {$size} id={$srcS['id']} sku={$srcS['sku']} price={$srcS['price']}\n";
    }
}

$keepIds = array();
foreach ($keepCatalog as $row) {
    $keepIds[(int) $row['id']] = true;
}
foreach ($keepStore as $row) {
    $keepIds[(int) $row['id']] = true;
}

// Delete all other children in both catalog + store family
$q = $m->query("SELECT id, store_id, sku, name FROM products
    WHERE (
        (TRIM(IFNULL(parent_sku,'')) = '" . $m->real_escape_string($parentSku) . "' AND (store_id IS NULL OR store_id=0))
        OR (TRIM(IFNULL(parent_sku,'')) = '" . $m->real_escape_string($storeParent['sku']) . "' AND store_id=8)
        OR (store_id=8 AND source_product_id IN (
            SELECT id FROM products WHERE TRIM(IFNULL(parent_sku,'')) = '" . $m->real_escape_string($parentSku) . "' AND (store_id IS NULL OR store_id=0)
        ))
    )");
$deleted = 0;
while ($row = $q->fetch_assoc()) {
    $id = (int) $row['id'];
    if (isset($keepIds[$id])) {
        continue;
    }
    delete_product_row($m, $id);
    $deleted++;
    echo "deleted {$id} {$row['sku']}\n";
}
echo "deleted_children={$deleted}\n";

// Normalize kept children to size-only names/skus
$sort = 1;
foreach ($sizes as $size) {
    $sizeU = strtoupper($size);
    if (!empty($keepCatalog[$sizeU]) || !empty($keepCatalog[$size])) {
        $row = !empty($keepCatalog[$sizeU]) ? $keepCatalog[$sizeU] : $keepCatalog[$size];
        $id = (int) $row['id'];
        $newSku = $parentSku . '-' . $sizeU;
        $newName = $sizeU;
        $slug = unique_slug($m, $parent['slug'] . '-' . strtolower($sizeU), $id);
        $isDefault = ($sort === 1) ? 1 : 0;
        $stmt = $m->prepare("UPDATE products SET name=?, sku=?, slug=?, sort_order=?, is_default=?, options_title='' WHERE id=?");
        $stmt->bind_param('sssiii', $newName, $newSku, $slug, $sort, $isDefault, $id);
        $stmt->execute();
        $stmt->close();
        echo "catalog_child {$sizeU} => id={$id} name={$newName} sku={$newSku}\n";
    } else {
        // create from parent
        $price = (float) $parent['price'];
        $cost = (float) $parent['cost_price'];
        $newSku = $parentSku . '-' . $sizeU;
        $newName = $sizeU;
        $slug = unique_slug($m, $parent['slug'] . '-' . strtolower($sizeU), 0);
        $isDefault = ($sort === 1) ? 1 : 0;
        $stmt = $m->prepare("INSERT INTO products (store_id, name, sku, parent_sku, is_default, sort_order, slug, brand, made_by, price, compare_price, cost_price, max_sale_price, stock, ship_min_days, ship_max_days, supplier_id, country_id, description, short_details, details, seo_title, seo_description, seo_keywords, status, image, source_url, created_by)
            VALUES (NULL,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)");
        $brand = (string) $parent['brand'];
        $made = (string) $parent['made_by'];
        $compare = (float) $parent['compare_price'];
        $maxSale = (float) $parent['max_sale_price'];
        $stock = (int) $parent['stock'];
        $shipMin = (int) $parent['ship_min_days'];
        $shipMax = (int) $parent['ship_max_days'];
        $supplier = $parent['supplier_id'] !== null ? (int) $parent['supplier_id'] : null;
        $country = $parent['country_id'] !== null ? (int) $parent['country_id'] : null;
        $desc = (string) $parent['description'];
        $short = (string) $parent['short_details'];
        $details = (string) $parent['details'];
        $seoT = $newName;
        $seoD = (string) $parent['seo_description'];
        $seoK = (string) $parent['seo_keywords'];
        $status = 1;
        $image = (string) $parent['image'];
        $source = (string) $parent['source_url'];
        $createdBy = (int) $parent['created_by'];
        // mysqli bind nulls awkwardly — use query escape
        $nullStore = 'NULL';
        $supSql = $supplier === null ? 'NULL' : (string) $supplier;
        $ctySql = $country === null ? 'NULL' : (string) $country;
        $sql = "INSERT INTO products (store_id, name, sku, parent_sku, is_default, sort_order, slug, brand, made_by, price, compare_price, cost_price, max_sale_price, stock, ship_min_days, ship_max_days, supplier_id, country_id, description, short_details, details, seo_title, seo_description, seo_keywords, status, image, source_url, created_by)
            VALUES (NULL,
            '" . $m->real_escape_string($newName) . "',
            '" . $m->real_escape_string($newSku) . "',
            '" . $m->real_escape_string($parentSku) . "',
            {$isDefault}, {$sort},
            '" . $m->real_escape_string($slug) . "',
            '" . $m->real_escape_string($brand) . "',
            '" . $m->real_escape_string($made) . "',
            {$price}, {$compare}, {$cost}, {$maxSale}, {$stock}, {$shipMin}, {$shipMax},
            {$supSql}, {$ctySql},
            '" . $m->real_escape_string($desc) . "',
            '" . $m->real_escape_string($short) . "',
            '" . $m->real_escape_string($details) . "',
            '" . $m->real_escape_string($seoT) . "',
            '" . $m->real_escape_string($seoD) . "',
            '" . $m->real_escape_string($seoK) . "',
            {$status},
            '" . $m->real_escape_string($image) . "',
            '" . $m->real_escape_string($source) . "',
            {$createdBy})";
        $m->query($sql);
        echo "created_catalog {$sizeU} id=" . $m->insert_id . "\n";
    }

    // Store child
    if (!empty($keepStore[$sizeU]) || !empty($keepStore[$size])) {
        $row = !empty($keepStore[$sizeU]) ? $keepStore[$sizeU] : $keepStore[$size];
        $id = (int) $row['id'];
        $newSku = $storeParent['sku'] . '-' . $sizeU;
        $newName = $sizeU;
        $slug = unique_slug($m, $storeParent['slug'] . '-' . strtolower($sizeU), $id);
        $isDefault = ($sort === 1) ? 1 : 0;
        $ps = $storeParent['sku'];
        $stmt = $m->prepare("UPDATE products SET name=?, sku=?, parent_sku=?, slug=?, sort_order=?, is_default=? WHERE id=?");
        $stmt->bind_param('ssssiii', $newName, $newSku, $ps, $slug, $sort, $isDefault, $id);
        $stmt->execute();
        $stmt->close();
        echo "store_child {$sizeU} => id={$id} name={$newName} sku={$newSku}\n";
    }
    $sort++;
}

// Parent options title
$m->query("UPDATE products SET options_title='Välj storlek' WHERE id=" . (int) $parent['id']);
$m->query("UPDATE products SET options_title='Välj storlek' WHERE id=" . (int) $storeParent['id']);

echo "---FINAL_STORE_CHILDREN---\n";
$r = $m->query("SELECT id,sku,name,price,is_default,sort_order FROM products WHERE store_id=8 AND parent_sku='" . $m->real_escape_string($storeParent['sku']) . "' ORDER BY sort_order,id");
while ($x = $r->fetch_assoc()) {
    echo json_encode($x) . "\n";
}
echo "DONE\n";
