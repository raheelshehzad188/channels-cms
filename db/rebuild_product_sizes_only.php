<?php
/**
 * Rebuild an AE product family to SIZE-ONLY children.
 *
 * Usage:
 *   php db/rebuild_product_sizes_only.php --ae-id=1005013184073478 --prefer-set=1
 *   php db/rebuild_product_sizes_only.php --ae-id=1005013184073478 --sizes=L,1XL,2XL,3XL,4XL
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

$aeId = '';
$preferSet = '1';
$sizesArg = '';
$storeId = 8;
foreach ($argv as $arg) {
    if (strpos($arg, '--ae-id=') === 0) {
        $aeId = preg_replace('/\D+/', '', substr($arg, 8));
    } elseif (strpos($arg, '--prefer-set=') === 0) {
        $preferSet = preg_replace('/\D+/', '', substr($arg, 13));
    } elseif (strpos($arg, '--sizes=') === 0) {
        $sizesArg = substr($arg, 8);
    } elseif (strpos($arg, '--store-id=') === 0) {
        $storeId = (int) substr($arg, 11);
    }
}
if ($aeId === '') {
    fwrite(STDERR, "Missing --ae-id=\n");
    exit(1);
}

$parentSku = 'AE' . $aeId;
$storeParentSku = $parentSku . '-S' . $storeId;

$m = new mysqli($dbHost, $dbUser, $dbPass, $dbName);
if ($m->connect_error) {
    fwrite(STDERR, $m->connect_error . "\n");
    exit(1);
}
$m->set_charset('utf8mb4');

function unique_slug($m, $base, $excludeId = 0)
{
    $slug = strtolower(trim(preg_replace('/[^a-zA-Z0-9]+/', '-', $base), '-'));
    if ($slug === '') {
        $slug = 'product';
    }
    $try = $slug;
    $n = 1;
    while (true) {
        $stmt = $m->prepare('SELECT id FROM products WHERE slug = ? AND id <> ? LIMIT 1');
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

function detect_sizes_from_skus($m, $parentSku)
{
    $sizes = array();
    $esc = $m->real_escape_string($parentSku);
    $r = $m->query("SELECT sku FROM products WHERE parent_sku='{$esc}' AND (store_id IS NULL OR store_id=0)");
    while ($row = $r->fetch_assoc()) {
        if (preg_match('/-(?:SET\d+-)?([0-9]?XL|XXL|XXXL|L|M|S)(?:-S\d+)?$/i', $row['sku'], $mm)) {
            $sizes[strtoupper($mm[1])] = strtoupper($mm[1]);
        }
    }
    // Prefer natural order
    $order = array('XXS', 'XS', 'S', 'M', 'L', 'XL', '1XL', '2XL', '3XL', '4XL', '5XL', 'XXL', 'XXXL');
    $out = array();
    foreach ($order as $s) {
        if (isset($sizes[$s])) {
            $out[] = $s;
            unset($sizes[$s]);
        }
    }
    foreach ($sizes as $s) {
        $out[] = $s;
    }
    return $out;
}

function pick_child_for_size($m, $parentSku, $storeId, $size, $preferSet)
{
    $size = strtoupper($size);
    $preferSet = (string) $preferSet;
    $storeClause = ($storeId === null)
        ? '(store_id IS NULL OR store_id = 0)'
        : ('store_id = ' . (int) $storeId);
    $escParent = $m->real_escape_string($parentSku);
    $escSize = $m->real_escape_string($size);
    $escSet = $m->real_escape_string($preferSet);
    $sql = "SELECT * FROM products
            WHERE parent_sku = '{$escParent}'
              AND {$storeClause}
              AND (
                    sku REGEXP CONCAT('-SET[0-9]+-', '{$escSize}', '(-S[0-9]+)?$')
                 OR sku REGEXP CONCAT('-', '{$escSize}', '(-S[0-9]+)?$')
                 OR name REGEXP CONCAT('[[:space:]/]', '{$escSize}', '[[:space:]]*$')
              )
            ORDER BY
              CASE WHEN sku LIKE '%-SET{$escSet}-%' THEN 0
                   WHEN sku LIKE '%-SET1-%' THEN 1
                   ELSE 2 END,
              price ASC, id ASC
            LIMIT 1";
    return $m->query($sql)->fetch_assoc();
}

$parent = $m->query("SELECT * FROM products WHERE sku='" . $m->real_escape_string($parentSku) . "' AND (store_id IS NULL OR store_id=0) AND TRIM(IFNULL(parent_sku,''))='' LIMIT 1")->fetch_assoc();
if (!$parent) {
    fwrite(STDERR, "Catalog parent not found: {$parentSku}\n");
    exit(1);
}
$storeParent = $m->query("SELECT * FROM products WHERE sku='" . $m->real_escape_string($storeParentSku) . "' AND store_id=" . (int) $storeId . " AND TRIM(IFNULL(parent_sku,''))='' LIMIT 1")->fetch_assoc();
if (!$storeParent) {
    $pid = (int) $parent['id'];
    $storeParent = $m->query("SELECT * FROM products WHERE store_id=" . (int) $storeId . " AND source_product_id={$pid} AND TRIM(IFNULL(parent_sku,''))='' LIMIT 1")->fetch_assoc();
}
if (!$storeParent) {
    fwrite(STDERR, "Store parent not found\n");
    exit(1);
}

if ($sizesArg !== '') {
    $sizes = array();
    foreach (explode(',', $sizesArg) as $s) {
        $s = strtoupper(trim($s));
        if ($s !== '') {
            $sizes[] = $s;
        }
    }
} else {
    $sizes = detect_sizes_from_skus($m, $parentSku);
}
if (!$sizes) {
    $sizes = array('L', '1XL', '2XL', '3XL', '4XL');
}

echo "parent={$parent['id']} store_parent={$storeParent['id']}\n";
echo "sizes=" . implode(',', $sizes) . " prefer_set={$preferSet}\n";

$keepCatalog = array();
$keepStore = array();
foreach ($sizes as $size) {
    $c = pick_child_for_size($m, $parentSku, null, $size, $preferSet);
    if ($c) {
        $keepCatalog[$size] = $c;
        echo "keep_catalog {$size} id={$c['id']} sku={$c['sku']} price={$c['price']}\n";
    }
    $s = pick_child_for_size($m, $storeParent['sku'], $storeId, $size, $preferSet);
    if ($s) {
        $keepStore[$size] = $s;
        echo "keep_store {$size} id={$s['id']} sku={$s['sku']} price={$s['price']}\n";
    }
}

$keepIds = array();
foreach ($keepCatalog as $row) {
    $keepIds[(int) $row['id']] = true;
}
foreach ($keepStore as $row) {
    $keepIds[(int) $row['id']] = true;
}

$escParent = $m->real_escape_string($parentSku);
$escStoreParent = $m->real_escape_string($storeParent['sku']);
$q = $m->query("SELECT id, sku FROM products WHERE
    (parent_sku='{$escParent}' AND (store_id IS NULL OR store_id=0))
 OR (parent_sku='{$escStoreParent}' AND store_id=" . (int) $storeId . ")
 OR (store_id=" . (int) $storeId . " AND source_product_id IN (
        SELECT id FROM products WHERE parent_sku='{$escParent}' AND (store_id IS NULL OR store_id=0)
    ))");
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

$sort = 1;
foreach ($sizes as $size) {
    $sizeU = strtoupper($size);
    if (!empty($keepCatalog[$sizeU])) {
        $row = $keepCatalog[$sizeU];
        $id = (int) $row['id'];
        $newSku = $parentSku . '-' . $sizeU;
        $newName = $sizeU;
        $slug = unique_slug($m, $parent['slug'] . '-' . strtolower($sizeU), $id);
        $isDefault = ($sort === 1) ? 1 : 0;
        $stmt = $m->prepare('UPDATE products SET name=?, sku=?, slug=?, sort_order=?, is_default=?, options_title=\'\' WHERE id=?');
        $stmt->bind_param('sssiii', $newName, $newSku, $slug, $sort, $isDefault, $id);
        $stmt->execute();
        $stmt->close();
        echo "catalog_child {$sizeU} => id={$id} sku={$newSku}\n";
    }
    if (!empty($keepStore[$sizeU])) {
        $row = $keepStore[$sizeU];
        $id = (int) $row['id'];
        $newSku = $storeParent['sku'] . '-' . $sizeU;
        $newName = $sizeU;
        $slug = unique_slug($m, $storeParent['slug'] . '-' . strtolower($sizeU), $id);
        $isDefault = ($sort === 1) ? 1 : 0;
        $ps = $storeParent['sku'];
        $stmt = $m->prepare('UPDATE products SET name=?, sku=?, parent_sku=?, slug=?, sort_order=?, is_default=? WHERE id=?');
        $stmt->bind_param('ssssiii', $newName, $newSku, $ps, $slug, $sort, $isDefault, $id);
        $stmt->execute();
        $stmt->close();
        echo "store_child {$sizeU} => id={$id} sku={$newSku}\n";
    }
    $sort++;
}

$m->query("UPDATE products SET options_title='Välj storlek' WHERE id=" . (int) $parent['id']);
$m->query("UPDATE products SET options_title='Välj storlek' WHERE id=" . (int) $storeParent['id']);

echo "---FINAL_STORE_CHILDREN---\n";
$r = $m->query("SELECT id,sku,name,price,is_default,sort_order FROM products WHERE store_id=" . (int) $storeId . " AND parent_sku='" . $m->real_escape_string($storeParent['sku']) . "' ORDER BY sort_order,id");
while ($x = $r->fetch_assoc()) {
    echo json_encode($x) . "\n";
}
echo "DONE\n";
