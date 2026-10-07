<?php
/**
 * Save one store's Meta Pixel settings in store_settings.
 * Does not accept an access token. Enter that in the store admin.
 *
 * Usage:
 *   php db/configure_store_meta.php --domain=example.com --pixel=123456 --ad-account=123 [--business=123]
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

$opts = getopt('', array('domain:', 'pixel:', 'ad-account:', 'business::'));
$domain = isset($opts['domain']) ? strtolower(trim($opts['domain'])) : '';
$pixel = isset($opts['pixel']) ? preg_replace('/\D+/', '', $opts['pixel']) : '';
$adAccount = isset($opts['ad-account']) ? preg_replace('/\D+/', '', $opts['ad-account']) : '';
$business = isset($opts['business']) ? preg_replace('/\D+/', '', $opts['business']) : '';
if ($domain === '' || $pixel === '') {
    fwrite(STDERR, "Usage: php db/configure_store_meta.php --domain=example.com --pixel=123456 [--ad-account=123] [--business=123]\n");
    exit(1);
}

mysqli_report(MYSQLI_REPORT_OFF);
$mysqli = @new mysqli($dbHost, $dbUser, $dbPass, $dbName);
if ($mysqli->connect_errno) {
    fwrite(STDERR, "MySQL connection failed: " . $mysqli->connect_error . "\n");
    exit(1);
}
$mysqli->set_charset('utf8mb4');

$domainEsc = $mysqli->real_escape_string($domain);
$storeResult = $mysqli->query("SELECT id, name, domain FROM stores WHERE domain='{$domainEsc}' LIMIT 1");
if (!$storeResult) {
    fwrite(STDERR, "Could not read stores on {$dbName}: " . $mysqli->error . "\n");
    exit(1);
}
$store = $storeResult->fetch_assoc();
if (!$store) {
    fwrite(STDERR, "No store found for domain {$domain} in database {$dbName}\n");
    exit(1);
}
$storeId = (int) $store['id'];

function meta_col_exists($mysqli, $table, $col) {
    $r = $mysqli->query("SHOW COLUMNS FROM `{$table}` LIKE '" . $mysqli->real_escape_string($col) . "'");
    return $r && $r->num_rows > 0;
}

function meta_setting_set($mysqli, $storeId, $key, $value) {
    $keyCol = meta_col_exists($mysqli, 'store_settings', 'field_key') ? 'field_key' : 'setting_key';
    $valCol = $keyCol === 'field_key' ? 'field_value' : 'setting_value';
    $keyEsc = $mysqli->real_escape_string($key);
    $valEsc = $mysqli->real_escape_string($value);
    $existing = $mysqli->query("SELECT id FROM store_settings WHERE store_id={$storeId} AND {$keyCol}='{$keyEsc}' LIMIT 1")->fetch_assoc();
    if ($existing) {
        $mysqli->query("UPDATE store_settings SET {$valCol}='{$valEsc}' WHERE id=" . (int) $existing['id']);
        return;
    }
    $cols = "store_id, {$keyCol}, {$valCol}";
    $vals = "{$storeId}, '{$keyEsc}', '{$valEsc}'";
    if (meta_col_exists($mysqli, 'store_settings', 'theme_id')) {
        $cols .= ', theme_id';
        $vals .= ', 0';
    }
    $mysqli->query("INSERT INTO store_settings ({$cols}) VALUES ({$vals})");
    if ($mysqli->error) {
        fwrite(STDERR, $mysqli->error . "\n");
        exit(1);
    }
}

$pairs = array(
    'meta_pixel_id' => $pixel,
    'meta_pixel_enabled' => '1',
    'meta_events_enabled' => '1',
);
if (isset($opts['ad-account'])) {
    $pairs['meta_ad_account_id'] = $adAccount;
}
if (isset($opts['business'])) {
    $pairs['meta_business_id'] = $business;
}
$keyCol = meta_col_exists($mysqli, 'store_settings', 'field_key') ? 'field_key' : 'setting_key';
$valCol = $keyCol === 'field_key' ? 'field_value' : 'setting_value';
$tokenResult = $mysqli->query("SELECT {$valCol} AS token_value FROM store_settings WHERE store_id={$storeId} AND {$keyCol}='meta_capi_token' LIMIT 1");
$tokenRow = $tokenResult ? $tokenResult->fetch_assoc() : null;
$tokenSaved = $tokenRow && trim((string) $tokenRow['token_value']) !== '';
if (!$tokenSaved) {
    $pairs['meta_capi_enabled'] = '0';
}
foreach ($pairs as $key => $value) {
    meta_setting_set($mysqli, $storeId, $key, $value);
}

$ordersTable = $mysqli->query("SHOW TABLES LIKE 'store_orders'");
if ($ordersTable && $ordersTable->num_rows > 0 && !meta_col_exists($mysqli, 'store_orders', 'meta_event_id')) {
    $mysqli->query("ALTER TABLE store_orders ADD meta_event_id varchar(80) DEFAULT NULL");
}

echo "Saved Meta settings for {$store['name']} ({$store['domain']}) store_id={$storeId}\n";
echo "Pixel configured. Conversions API left off until an access token is saved in this store's admin.\n";
