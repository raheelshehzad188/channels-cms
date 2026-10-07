<?php
/**
 * Set a store's WhatsApp number used for order staff notifications.
 *
 * Usage:
 *   php db/set_store_whatsapp.php --domain=zenvello.se --phone=923437128470
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

$opts = getopt('', array('domain:', 'phone:'));
$domain = isset($opts['domain']) ? strtolower(trim($opts['domain'])) : '';
$phone = isset($opts['phone']) ? preg_replace('/\D+/', '', (string) $opts['phone']) : '';
if ($domain === '' || strlen($phone) < 10) {
    fwrite(STDERR, "Usage: php db/set_store_whatsapp.php --domain=zenvello.se --phone=923437128470\n");
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
$storeResult = $mysqli->query(
    "SELECT id, name, domain FROM stores WHERE domain='{$domainEsc}' OR domain LIKE '%{$domainEsc}%' LIMIT 1"
);
if (!$storeResult) {
    fwrite(STDERR, "Could not read stores: " . $mysqli->error . "\n");
    exit(1);
}
$store = $storeResult->fetch_assoc();
if (!$store) {
    fwrite(STDERR, "No store found for domain {$domain}\n");
    exit(1);
}
$storeId = (int) $store['id'];

function wa_col_exists($mysqli, $table, $col)
{
    $r = $mysqli->query("SHOW COLUMNS FROM `{$table}` LIKE '" . $mysqli->real_escape_string($col) . "'");
    return $r && $r->num_rows > 0;
}

function wa_setting_set($mysqli, $storeId, $key, $value)
{
    $keyCol = wa_col_exists($mysqli, 'store_settings', 'field_key') ? 'field_key' : 'setting_key';
    $valCol = $keyCol === 'field_key' ? 'field_value' : 'setting_value';
    $keyEsc = $mysqli->real_escape_string($key);
    $valEsc = $mysqli->real_escape_string($value);
    $existing = $mysqli->query(
        "SELECT id FROM store_settings WHERE store_id={$storeId} AND {$keyCol}='{$keyEsc}' LIMIT 1"
    )->fetch_assoc();
    if ($existing) {
        $mysqli->query("UPDATE store_settings SET {$valCol}='{$valEsc}' WHERE id=" . (int) $existing['id']);
        if ($mysqli->error) {
            fwrite(STDERR, $mysqli->error . "\n");
            exit(1);
        }
        return 'updated';
    }
    $cols = "store_id, {$keyCol}, {$valCol}";
    $vals = "{$storeId}, '{$keyEsc}', '{$valEsc}'";
    if (wa_col_exists($mysqli, 'store_settings', 'theme_id')) {
        $cols .= ', theme_id';
        $vals .= ', 0';
    }
    if ($keyCol !== 'field_key' && wa_col_exists($mysqli, 'store_settings', 'field_key')) {
        $cols .= ', field_key, field_value';
        $vals .= ", '{$keyEsc}', '{$valEsc}'";
    }
    if ($keyCol !== 'setting_key' && wa_col_exists($mysqli, 'store_settings', 'setting_key')) {
        $cols .= ', setting_key, setting_value';
        $vals .= ", '{$keyEsc}', '{$valEsc}'";
    }
    $mysqli->query("INSERT INTO store_settings ({$cols}) VALUES ({$vals})");
    if ($mysqli->error) {
        fwrite(STDERR, $mysqli->error . "\n");
        exit(1);
    }
    return 'inserted';
}

$action = wa_setting_set($mysqli, $storeId, 'whatsapp_number', $phone);
echo "WhatsApp {$action} for {$store['name']} ({$store['domain']}) store_id={$storeId}: {$phone}\n";
