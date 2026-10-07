<?php
/**
 * Reimport AE1005012577529604 hair chalk from AliExpress source:
 * - Fetch Color SKU props from source page
 * - Ensure catalog + store color children exist
 * - Child name = color name only
 * - extra_amount = 82 on every child
 * - Recalc store listing prices
 * - Keep parent offer (20% + free shipping)
 *
 *   php db/rebuild_hair_chalk_colors.php
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

$aeId = '1005012577529604';
$storeId = 8;
$extra = 82.00;
$parentSku = 'AE' . $aeId;
$storeParentSku = $parentSku . '-S' . $storeId;
$sourceUrl = 'https://aliexpress.com/item/' . $aeId . '.html';

$svMap = array(
    'pink' => 'Rosa',
    'rosa' => 'Rosa',
    'blue' => 'Blå',
    'blå' => 'Blå',
    'bla' => 'Blå',
    'green' => 'Grön',
    'grön' => 'Grön',
    'gron' => 'Grön',
    'orange' => 'Orange',
    'yellow' => 'Gul',
    'gul' => 'Gul',
    'purple' => 'Lila',
    'lila' => 'Lila',
    'violet' => 'Lila',
    'red' => 'Röd',
    'röd' => 'Röd',
    'rod' => 'Röd',
    'white' => 'Vit',
    'vit' => 'Vit',
    'black' => 'Svart',
    'svart' => 'Svart',
    'brown' => 'Brun',
    'brun' => 'Brun',
    'gold' => 'Guld',
    'guld' => 'Guld',
    'silver' => 'Silver',
);

// Fallback if source fetch fails (existing SKU codes)
$fallback = array(
    array('code' => '01-PINK', 'en' => 'Pink', 'sv' => 'Rosa'),
    array('code' => '02-BLUE', 'en' => 'Blue', 'sv' => 'Blå'),
    array('code' => '03-GREEN', 'en' => 'Green', 'sv' => 'Grön'),
    array('code' => '04-ORANGE', 'en' => 'Orange', 'sv' => 'Orange'),
    array('code' => '05-YELLOW', 'en' => 'Yellow', 'sv' => 'Gul'),
    array('code' => '06-PURPLE', 'en' => 'Purple', 'sv' => 'Lila'),
    array('code' => '07-BLUE', 'en' => 'Blue 2', 'sv' => 'Blå 2'),
    array('code' => '08-RED', 'en' => 'Red', 'sv' => 'Röd'),
);

$m = new mysqli($dbHost, $dbUser, $dbPass, $dbName);
if ($m->connect_error) {
    fwrite(STDERR, $m->connect_error . "\n");
    exit(1);
}
$m->set_charset('utf8mb4');

function hc_fetch($url)
{
    $cookie = tempnam(sys_get_temp_dir(), 'aeck');
    $ua = 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124.0.0.0 Safari/537.36';
    $headers = array(
        'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
        'Accept-Language: en-US,en;q=0.9',
        'Referer: https://www.aliexpress.com/',
    );
    foreach (array('https://www.aliexpress.com/', $url) as $u) {
        $ch = curl_init($u);
        curl_setopt_array($ch, array(
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_MAXREDIRS => 8,
            CURLOPT_TIMEOUT => 40,
            CURLOPT_CONNECTTIMEOUT => 15,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_COOKIEJAR => $cookie,
            CURLOPT_COOKIEFILE => $cookie,
            CURLOPT_USERAGENT => $ua,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_ENCODING => '',
        ));
        $html = curl_exec($ch);
        curl_close($ch);
    }
    if (is_file($cookie)) {
        @unlink($cookie);
    }
    return is_string($html) ? $html : '';
}

function hc_clean($s)
{
    $s = html_entity_decode((string) $s, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    $s = preg_replace('/\s+/u', ' ', $s);
    return trim($s);
}

function hc_color_name($raw)
{
    $name = hc_clean($raw);
    // Strip leading codes like "01 Pink", "05-Yellow"
    $name = preg_replace('/^\d{1,2}[\s\-_]+/u', '', $name);
    $name = trim($name, " \t-–—");
    return $name;
}

function hc_slug_code($name, $i)
{
    $code = strtoupper(preg_replace('/[^A-Za-z0-9]+/', '-', $name));
    $code = trim($code, '-');
    if ($code === '') {
        $code = 'COLOR';
    }
    return sprintf('%02d-%s', $i, $code);
}

function hc_sv($en, $svMap)
{
    $key = function_exists('mb_strtolower') ? mb_strtolower($en, 'UTF-8') : strtolower($en);
    $key = preg_replace('/\s+\d+$/', '', $key);
    if (isset($svMap[$key])) {
        return $svMap[$key];
    }
    // "Blue 2" → Blå 2
    if (preg_match('/^(.+?)\s+(\d+)$/u', $key, $m)) {
        $base = $m[1];
        if (isset($svMap[$base])) {
            return $svMap[$base] . ' ' . $m[2];
        }
    }
    return $en;
}

function hc_extract_colors($html)
{
    $html = (string) $html;
    $colors = array();
    if (preg_match_all('/skuPropertyName"\s*:\s*"(Color|Colour|Färg|Farbe|Couleur|Colore)"(.*?)(?:skuPropertyName"|skuPriceList)/is', $html, $blocks)) {
        foreach ($blocks[2] as $block) {
            if (preg_match_all('/"(?:propertyValueDisplayName|propertyValueName|skuPropertyTips)"\s*:\s*"([^"]+)"/i', $block, $matches)) {
                foreach ($matches[1] as $raw) {
                    $colors[] = hc_color_name($raw);
                }
            }
        }
    }
    $out = array();
    $seen = array();
    foreach ($colors as $c) {
        if ($c === '' || strlen($c) > 40) {
            continue;
        }
        // skip size-like
        if (preg_match('/^(?:XXS|XS|S|M|L|XL|XXL|[1-5]XL|\d+\s*cm)$/i', $c)) {
            continue;
        }
        $key = function_exists('mb_strtolower') ? mb_strtolower($c, 'UTF-8') : strtolower($c);
        if (isset($seen[$key])) {
            continue;
        }
        $seen[$key] = true;
        $out[] = $c;
    }
    return $out;
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

$parent = $m->query("SELECT * FROM products WHERE sku='" . $m->real_escape_string($parentSku) . "' AND (store_id IS NULL OR store_id=0) LIMIT 1")->fetch_assoc();
$storeParent = $m->query("SELECT * FROM products WHERE sku='" . $m->real_escape_string($storeParentSku) . "' AND store_id=" . (int) $storeId . " LIMIT 1")->fetch_assoc();
if (!$parent || !$storeParent) {
    fwrite(STDERR, "Parent not found\n");
    exit(1);
}
if (!empty($parent['source_url'])) {
    $sourceUrl = $parent['source_url'];
}

$store = $m->query('SELECT * FROM stores WHERE id=' . (int) $storeId)->fetch_assoc();
$storePlus = $store && isset($store['price_plus_amount']) ? (float) $store['price_plus_amount'] : 0.0;
$cost = isset($storeParent['cost_price']) && (float) $storeParent['cost_price'] > 0
    ? (float) $storeParent['cost_price']
    : 54.93;

echo "parent={$parent['id']} store_parent={$storeParent['id']} source={$sourceUrl}\n";
echo "Fetching source colors...\n";
$html = hc_fetch($sourceUrl);
$fetched = $html !== '' ? hc_extract_colors($html) : array();
echo "fetched_colors=" . count($fetched) . " html_len=" . strlen($html) . "\n";

$colors = array();
if ($fetched) {
    $i = 1;
    $enCounts = array();
    foreach ($fetched as $en) {
        $baseKey = function_exists('mb_strtolower') ? mb_strtolower($en, 'UTF-8') : strtolower($en);
        if (!isset($enCounts[$baseKey])) {
            $enCounts[$baseKey] = 0;
        }
        $enCounts[$baseKey]++;
        $enName = $en;
        $svName = hc_sv($en, $svMap);
        if ($enCounts[$baseKey] > 1) {
            $enName = $en . ' ' . $enCounts[$baseKey];
            $svName = $svName . ' ' . $enCounts[$baseKey];
        }
        $colors[] = array(
            'code' => hc_slug_code($en, $i),
            'en' => $enName,
            'sv' => $svName,
        );
        $i++;
    }
} else {
    echo "Using fallback color list from existing SKUs\n";
    $colors = $fallback;
}

echo "colors=" . count($colors) . "\n";

// Index existing children by color code / fuzzy name
function index_children($m, $parentSku, $storeId = 0)
{
    $map = array();
    if ($storeId > 0) {
        $sql = "SELECT * FROM products WHERE parent_sku='" . $m->real_escape_string($parentSku) . "' AND store_id=" . (int) $storeId;
    } else {
        $sql = "SELECT * FROM products WHERE parent_sku='" . $m->real_escape_string($parentSku) . "' AND (store_id IS NULL OR store_id=0)";
    }
    $q = $m->query($sql);
    while ($r = $q->fetch_assoc()) {
        $map[(int) $r['id']] = $r;
    }
    return $map;
}

function match_child($children, $code, $en, $parentSku)
{
    $code = strtoupper($code);
    foreach ($children as $id => $row) {
        $sku = strtoupper(preg_replace('/-S\d+$/', '', (string) $row['sku']));
        $suffix = '';
        $prefix = strtoupper($parentSku) . '-';
        if (strpos($sku, $prefix) === 0) {
            $suffix = substr($sku, strlen($prefix));
        }
        if ($suffix === $code || substr($suffix, -strlen($code)) === $code) {
            return $id;
        }
        $name = function_exists('mb_strtolower') ? mb_strtolower(trim($row['name']), 'UTF-8') : strtolower(trim($row['name']));
        $enKey = function_exists('mb_strtolower') ? mb_strtolower($en, 'UTF-8') : strtolower($en);
        if ($name === $enKey || strpos($name, $enKey) !== false) {
            return $id;
        }
    }
    return 0;
}

$catalogChildren = index_children($m, $parentSku, 0);
$storeChildren = index_children($m, $storeParentSku, $storeId);
$usedCatalog = array();
$usedStore = array();
$sort = 0;

foreach ($colors as $c) {
    $sort++;
    $code = $c['code'];
    $en = $c['en'];
    $sv = $c['sv'];
    $isDefault = $sort === 1 ? 1 : 0;

    // --- catalog ---
    $cid = match_child($catalogChildren, $code, $en, $parentSku);
    $catSku = $parentSku . '-' . $code;
    if ($cid > 0) {
        $usedCatalog[$cid] = true;
        $row = $catalogChildren[$cid];
        $stmt = $m->prepare('UPDATE products SET name=?, sku=?, extra_amount=?, sort_order=?, is_default=? WHERE id=?');
        $stmt->bind_param('ssdiii', $en, $catSku, $extra, $sort, $isDefault, $cid);
        $stmt->execute();
        $stmt->close();
        // unique sku conflict: if rename fails due to dup, keep old sku
        if ($m->errno) {
            $m->query('UPDATE products SET name=\'' . $m->real_escape_string($en) . '\', extra_amount=' . $extra . ', sort_order=' . $sort . ', is_default=' . $isDefault . ' WHERE id=' . $cid);
        }
        echo "catalog_upd id={$cid} {$en}\n";
    } else {
        $slug = unique_slug($m, $en . '-hair-chalk');
        $img = isset($parent['image']) ? $parent['image'] : '';
        $price = (float) $parent['price'];
        $costP = (float) $parent['cost_price'];
        $stmt = $m->prepare("INSERT INTO products (store_id, name, sku, parent_sku, is_default, sort_order, slug, price, cost_price, extra_amount, stock, status, image, source_url, created_by, country_id, supplier_id)
            VALUES (NULL,?,?,?,?,?,?,?,?,?,?,1,?,?,?,?,?)");
        $createdBy = (int) $parent['created_by'];
        $countryId = !empty($parent['country_id']) ? (int) $parent['country_id'] : null;
        $supplierId = !empty($parent['supplier_id']) ? (int) $parent['supplier_id'] : null;
        $stock = isset($parent['stock']) ? (int) $parent['stock'] : 100;
        $src = isset($parent['source_url']) ? $parent['source_url'] : $sourceUrl;
        $stmt->bind_param('sssisiiddiisiii', $en, $catSku, $parentSku, $isDefault, $sort, $slug, $price, $costP, $extra, $stock, $img, $src, $createdBy, $countryId, $supplierId);
        // bind types may fail with nulls — use query fallback
        $stmt->close();
        $m->query("INSERT INTO products (name, sku, parent_sku, is_default, sort_order, slug, price, cost_price, extra_amount, stock, status, image, source_url, created_by, country_id, supplier_id, brand, made_by, description, short_details, details, seo_title, seo_description, seo_keywords, ship_min_days, ship_max_days, max_sale_price, compare_price, auto_add_to_stores)
            VALUES (
            '" . $m->real_escape_string($en) . "',
            '" . $m->real_escape_string($catSku) . "',
            '" . $m->real_escape_string($parentSku) . "',
            {$isDefault}, {$sort},
            '" . $m->real_escape_string($slug) . "',
            " . (float) $parent['price'] . ",
            " . (float) $parent['cost_price'] . ",
            {$extra},
            " . (int) $parent['stock'] . ",
            1,
            '" . $m->real_escape_string((string) $parent['image']) . "',
            '" . $m->real_escape_string($src) . "',
            " . (int) $parent['created_by'] . ",
            " . (!empty($parent['country_id']) ? (int) $parent['country_id'] : 'NULL') . ",
            " . (!empty($parent['supplier_id']) ? (int) $parent['supplier_id'] : 'NULL') . ",
            '" . $m->real_escape_string((string) $parent['brand']) . "',
            '" . $m->real_escape_string((string) $parent['made_by']) . "',
            '" . $m->real_escape_string((string) $parent['description']) . "',
            '" . $m->real_escape_string((string) $parent['short_details']) . "',
            '" . $m->real_escape_string((string) $parent['details']) . "',
            '" . $m->real_escape_string($en) . "',
            '" . $m->real_escape_string((string) $parent['seo_description']) . "',
            '" . $m->real_escape_string((string) $parent['seo_keywords']) . "',
            " . (int) $parent['ship_min_days'] . ",
            " . (int) $parent['ship_max_days'] . ",
            " . (float) $parent['max_sale_price'] . ",
            0,
            " . (!empty($parent['auto_add_to_stores']) ? 1 : 0) . "
        )");
        $cid = (int) $m->insert_id;
        if ($cid > 0) {
            $usedCatalog[$cid] = true;
            echo "catalog_new id={$cid} {$en}\n";
        } else {
            echo "catalog_fail {$en} " . $m->error . "\n";
        }
    }

    // --- store ---
    $sid = match_child($storeChildren, $code, $sv, $parentSku);
    if ($sid < 1) {
        $sid = match_child($storeChildren, $code, $en, $parentSku);
    }
    $storeSku = $parentSku . '-' . $code . '-S' . $storeId;
    $newPrice = round($cost + $storePlus + $extra, 2);
    $storeSlug = unique_slug($m, $sv . '-harkrita', $sid);

    if ($sid > 0) {
        $usedStore[$sid] = true;
        $m->query("UPDATE products SET
            name='" . $m->real_escape_string($sv) . "',
            name_en='" . $m->real_escape_string($en) . "',
            sku='" . $m->real_escape_string($storeSku) . "',
            parent_sku='" . $m->real_escape_string($storeParentSku) . "',
            extra_amount={$extra},
            price={$newPrice},
            sort_order={$sort},
            is_default={$isDefault},
            offer_enabled=0,
            options_title=''
            WHERE id={$sid}");
        if ($m->errno) {
            $m->query("UPDATE products SET name='" . $m->real_escape_string($sv) . "', name_en='" . $m->real_escape_string($en) . "', extra_amount={$extra}, price={$newPrice}, sort_order={$sort}, is_default={$isDefault}, offer_enabled=0 WHERE id={$sid}");
        }
        echo "store_upd id={$sid} {$sv} price={$newPrice}\n";
    } else {
        // clone from store parent
        $m->query("INSERT INTO products (store_id, source_product_id, name, sku, parent_sku, is_default, sort_order, slug, brand, made_by, price, compare_price, cost_price, max_sale_price, extra_amount, stock, ship_min_days, ship_max_days, supplier_id, country_id, description, short_details, details, seo_title, seo_description, seo_keywords, status, image, source_url, created_by, offer_enabled)
            SELECT {$storeId},
                " . ($cid > 0 ? $cid : 'NULL') . ",
                '" . $m->real_escape_string($sv) . "',
                '" . $m->real_escape_string($storeSku) . "',
                '" . $m->real_escape_string($storeParentSku) . "',
                {$isDefault}, {$sort},
                '" . $m->real_escape_string($storeSlug) . "',
                brand, made_by,
                {$newPrice}, 0, cost_price, max_sale_price, {$extra},
                stock, ship_min_days, ship_max_days, supplier_id, country_id,
                description, short_details, details,
                '" . $m->real_escape_string($sv) . "',
                seo_description, seo_keywords, status, image, source_url, created_by, 0
            FROM products WHERE id=" . (int) $storeParent['id']);
        $sid = (int) $m->insert_id;
        if ($sid > 0) {
            $usedStore[$sid] = true;
            echo "store_new id={$sid} {$sv} price={$newPrice}\n";
        } else {
            echo "store_fail {$sv} " . $m->error . "\n";
        }
    }
}

// Soft-disable unmatched old children (keep history, hide from PDP)
foreach ($catalogChildren as $id => $row) {
    if (!empty($usedCatalog[$id])) {
        continue;
    }
    $m->query('UPDATE products SET status=0 WHERE id=' . (int) $id);
    echo "catalog_hide id={$id}\n";
}
foreach ($storeChildren as $id => $row) {
    if (!empty($usedStore[$id])) {
        continue;
    }
    $m->query('UPDATE products SET status=0 WHERE id=' . (int) $id);
    echo "store_hide id={$id}\n";
}

$m->query("UPDATE products SET options_title='Select color' WHERE id=" . (int) $parent['id']);
$m->query("UPDATE products SET options_title='Välj färg' WHERE id=" . (int) $storeParent['id']);

// Restore offer on store parent only
$m->query("UPDATE products SET
    offer_enabled=1,
    offer_type='percent',
    offer_value=20,
    offer_label='20% OFF',
    offer_show_badge=1,
    offer_show_countdown=0,
    offer_free_shipping=1
    WHERE id=" . (int) $storeParent['id']);

echo "---FINAL_STORE_CHILDREN---\n";
$q = $m->query("SELECT id,sku,name,price,extra_amount,status,sort_order FROM products WHERE parent_sku='" . $m->real_escape_string($storeParentSku) . "' AND store_id={$storeId} AND status=1 ORDER BY sort_order,id");
while ($r = $q->fetch_assoc()) {
    echo json_encode($r, JSON_UNESCAPED_UNICODE) . "\n";
}
echo "DONE\n";
