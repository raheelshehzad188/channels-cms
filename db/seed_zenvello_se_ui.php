<?php
/**
 * Seed Swedish storefront UI labels + marketing copy for zenvello.se
 * Run on Contabo: php db/seed_zenvello_se_ui.php
 */
define('BASEPATH', true);
require dirname(__DIR__) . '/application/helpers/storefront_ui_helper.php';

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
    fwrite(STDERR, $mysqli->connect_error . "\n");
    exit(1);
}
$mysqli->set_charset('utf8mb4');

$store = $mysqli->query("SELECT id FROM stores WHERE domain='zenvello.se' LIMIT 1")->fetch_assoc();
if (!$store) {
    fwrite(STDERR, "zenvello.se store missing\n");
    exit(1);
}
$storeId = (int) $store['id'];
echo "store_id={$storeId}\n";

$hasLang = $mysqli->query("SHOW COLUMNS FROM stores LIKE 'language'");
if ($hasLang && $hasLang->num_rows) {
    $mysqli->query("UPDATE stores SET language='sv' WHERE id={$storeId}");
}

$hasThemeId = false;
$col = $mysqli->query("SHOW COLUMNS FROM store_settings LIKE 'theme_id'");
if ($col && $col->num_rows) {
    $hasThemeId = true;
}
$themeId = 0;
$theme = $mysqli->query("SELECT theme_id FROM stores WHERE id={$storeId}")->fetch_assoc();
if ($theme) {
    $themeId = (int) $theme['theme_id'];
}

function upsert_setting($mysqli, $storeId, $key, $value, $hasThemeId, $themeId)
{
    $ek = $mysqli->real_escape_string($key);
    $ev = $mysqli->real_escape_string($value);
    $exists = $mysqli->query("SELECT id FROM store_settings WHERE store_id={$storeId} AND field_key='{$ek}' LIMIT 1")->fetch_assoc();
    if ($exists) {
        $mysqli->query("UPDATE store_settings SET field_value='{$ev}'" . ($hasThemeId ? ", theme_id={$themeId}" : '') . " WHERE id=" . (int) $exists['id']);
        return;
    }
    $extraCols = $hasThemeId ? ', theme_id' : '';
    $extraVals = $hasThemeId ? ", {$themeId}" : '';
    $mysqli->query("INSERT INTO store_settings (store_id{$extraCols}, field_key, field_value) VALUES ({$storeId}{$extraVals}, '{$ek}', '{$ev}')");
}

$pairs = array('ui.locale' => 'sv');
$catalog = storefront_ui_catalog();
$sv = storefront_ui_locale_strings('sv');
foreach ($catalog as $key => $meta) {
    $pairs['ui.' . $key] = isset($sv[$key]) ? $sv[$key] : $meta['d'];
}
foreach (storefront_ui_marketing_defaults('sv') as $key => $value) {
    $pairs[$key] = $value;
}

$n = 0;
foreach ($pairs as $key => $value) {
    upsert_setting($mysqli, $storeId, $key, $value, $hasThemeId, $themeId);
    $n++;
}
echo "saved {$n} settings\n";

$hero = $mysqli->query("SHOW TABLES LIKE 'store_hero_slides'");
if ($hero && $hero->num_rows) {
    $mysqli->query("UPDATE store_hero_slides SET btn_text='Handla nu' WHERE store_id={$storeId} AND (btn_text='' OR btn_text='Shop Now' OR btn_text='Browse Shop' OR btn_text='Shop Deals')");
    echo "hero slides updated=" . $mysqli->affected_rows . "\n";
}

echo "OK Swedish UI seeded for zenvello.se\n";
