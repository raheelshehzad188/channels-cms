<?php
/**
 * Backup + safely update AliExpress product delivery windows.
 *
 * - Detects AliExpress products via source_url / AE* SKU / AliExpress supplier
 * - Backs up id,sku,source_url,ship_min_days,ship_max_days to CSV before changes
 * - Updates ONLY missing (0/0) or old importer fallback (7/9) → 7–15
 * - Keeps scraped / product-specific windows (incl. faster EU/local ranges)
 * - Does NOT touch prices, titles, images, stock, descriptions
 *
 * Usage (on Contabo or local):
 *   php db/update_aliexpress_delivery.php --dry-run
 *   php db/update_aliexpress_delivery.php --confirm=YES
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

$opts = getopt('', array('confirm::', 'dry-run'));
$dryRun = array_key_exists('dry-run', $opts);
$confirm = isset($opts['confirm']) ? strtoupper(trim((string) $opts['confirm'])) : '';
if (!$dryRun && $confirm !== 'YES') {
    fwrite(STDERR, "Refusing to run. Use --dry-run or --confirm=YES\n");
    exit(1);
}

$safeMin = 7;
$safeMax = 15;

mysqli_report(MYSQLI_REPORT_OFF);
$mysqli = @new mysqli($dbHost, $dbUser, $dbPass, $dbName);
if ($mysqli->connect_errno) {
    fwrite(STDERR, "MySQL connection failed: " . $mysqli->connect_error . "\n");
    exit(1);
}
$mysqli->set_charset('utf8mb4');

$hasSupplierName = false;
$cols = $mysqli->query("SHOW COLUMNS FROM products");
$productCols = array();
while ($cols && ($c = $cols->fetch_assoc())) {
    $productCols[$c['Field']] = true;
}
if (!isset($productCols['ship_min_days']) || !isset($productCols['ship_max_days'])) {
    fwrite(STDERR, "products.ship_min_days / ship_max_days missing\n");
    exit(1);
}

$supplierJoin = '';
$supplierSelect = "'' AS ae_supplier";
$aeWhere = "(p.source_url LIKE '%aliexpress.%' OR p.sku LIKE 'AE%')";
if ($mysqli->query("SHOW TABLES LIKE 'suppliers'")->num_rows > 0) {
    $supplierJoin = ' LEFT JOIN suppliers s ON s.id = p.supplier_id ';
    $supplierSelect = 'IFNULL(s.name, \'\') AS ae_supplier';
    $aeWhere = "(p.source_url LIKE '%aliexpress.%' OR p.sku LIKE 'AE%' OR IFNULL(s.name, '') LIKE '%AliExpress%')";
}

$sql = "SELECT p.id, p.sku, p.name, p.source_url, p.store_id, p.parent_sku,
               p.ship_min_days, p.ship_max_days, {$supplierSelect}
        FROM products p
        {$supplierJoin}
        WHERE {$aeWhere}
        ORDER BY p.id ASC";
$result = $mysqli->query($sql);
if (!$result) {
    fwrite(STDERR, "Query failed: " . $mysqli->error . "\n");
    exit(1);
}

$detected = array();
while ($row = $result->fetch_assoc()) {
    $detected[] = $row;
}
$detectedCount = count($detected);

$backupDir = dirname(__DIR__) . '/uploads/delivery_backups';
if (!is_dir($backupDir)) {
    @mkdir($backupDir, 0755, true);
}
$backupFile = $backupDir . '/aliexpress_ship_days_' . date('Ymd_His') . '.csv';
$fp = fopen($backupFile, 'w');
if (!$fp) {
    fwrite(STDERR, "Could not write backup CSV: {$backupFile}\n");
    exit(1);
}
fputcsv($fp, array(
    'id', 'sku', 'name', 'source_url', 'store_id', 'parent_sku',
    'ship_min_days', 'ship_max_days', 'supplier', 'action', 'new_min', 'new_max',
), ',', '"', '\\');

$updated = 0;
$skipped = 0;
$groups = array();
$manualReview = array();

foreach ($detected as $row) {
    $min = (int) $row['ship_min_days'];
    $max = (int) $row['ship_max_days'];
    $needSafe = ($min <= 0 && $max <= 0) || ($min === 7 && $max === 9);
    $action = 'skip_keep';
    $newMin = $min;
    $newMax = $max;

    if ($needSafe) {
        $action = ($min === 7 && $max === 9) ? 'replace_old_fallback_7_9' : 'set_missing_default';
        $newMin = $safeMin;
        $newMax = $safeMax;
    } else {
        // Keep product-specific window; flag unusually short AE ETAs for manual check.
        if ($max > 0 && $max < 7) {
            $manualReview[] = array(
                'id' => (int) $row['id'],
                'sku' => $row['sku'],
                'window' => $min . '-' . $max,
                'reason' => 'short_window_verify_eu_or_local_warehouse',
            );
        }
        $skipped++;
    }

    $groupKey = $newMin . '-' . $newMax;
    if (!isset($groups[$groupKey])) {
        $groups[$groupKey] = 0;
    }
    $groups[$groupKey]++;

    fputcsv($fp, array(
        $row['id'],
        $row['sku'],
        $row['name'],
        $row['source_url'],
        $row['store_id'],
        $row['parent_sku'],
        $min,
        $max,
        $row['ae_supplier'],
        $action,
        $newMin,
        $newMax,
    ), ',', '"', '\\');

    if ($needSafe && !$dryRun) {
        $id = (int) $row['id'];
        $ok = $mysqli->query(
            "UPDATE products SET ship_min_days={$newMin}, ship_max_days={$newMax} WHERE id={$id}"
        );
        if (!$ok) {
            fwrite(STDERR, "Update failed for id={$id}: " . $mysqli->error . "\n");
            exit(1);
        }
        $updated++;
    } elseif ($needSafe && $dryRun) {
        $updated++;
    }
}
fclose($fp);

echo ($dryRun ? "DRY RUN" : "APPLIED") . "\n";
echo "Detected AliExpress products: {$detectedCount}\n";
echo "Updated: {$updated}\n";
echo "Skipped (kept product-specific window): {$skipped}\n";
echo "Backup CSV: {$backupFile}\n";
echo "Delivery groups after plan:\n";
ksort($groups);
foreach ($groups as $key => $count) {
    echo "  {$key} working days => {$count} products\n";
}
if ($manualReview) {
    echo "Manual verification suggested (" . count($manualReview) . "):\n";
    foreach (array_slice($manualReview, 0, 30) as $item) {
        echo "  id={$item['id']} sku={$item['sku']} window={$item['window']} ({$item['reason']})\n";
    }
    if (count($manualReview) > 30) {
        echo "  ... +" . (count($manualReview) - 30) . " more\n";
    }
}
