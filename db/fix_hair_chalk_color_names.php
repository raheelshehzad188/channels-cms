<?php
/**
 * Hair chalk children: force color-only names (SV + EN), keep prices/offers.
 *   php db/fix_hair_chalk_color_names.php
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
$colors = array(
    '01-PINK' => array('en' => 'Pink', 'sv' => 'Rosa'),
    '02-BLUE' => array('en' => 'Blue', 'sv' => 'Blå'),
    '03-GREEN' => array('en' => 'Green', 'sv' => 'Grön'),
    '04-ORANGE' => array('en' => 'Orange', 'sv' => 'Orange'),
    '05-YELLOW' => array('en' => 'Yellow', 'sv' => 'Gul'),
    '06-PURPLE' => array('en' => 'Purple', 'sv' => 'Lila'),
    '07-BLUE' => array('en' => 'Blue 2', 'sv' => 'Blå 2'),
    '08-RED' => array('en' => 'Red', 'sv' => 'Röd'),
);

$m = new mysqli($dbHost, $dbUser, $dbPass, $dbName);
if ($m->connect_errno) {
    fwrite(STDERR, $m->connect_error . "\n");
    exit(1);
}
$m->set_charset('utf8mb4');

// Parent titles without "1 st" noise
$m->query("UPDATE products SET
    name='Vattenlöslig Hårkrita – Tillfällig Hårfärg för Event',
    name_en='Water-Soluble Hair Chalk – Temporary Hair Color',
    options_title='Välj färg'
  WHERE sku='AE1005012577529604-S8' AND store_id={$storeId}");
$m->query("UPDATE products SET
    name='Water Soluble Hair Chalk Powder – Temporary Hair Color',
    name_en='Water Soluble Hair Chalk Powder – Temporary Hair Color',
    options_title='Select color'
  WHERE sku='AE1005012577529604' AND (store_id IS NULL OR store_id=0)");

foreach ($colors as $code => $names) {
    $en = $m->real_escape_string($names['en']);
    $sv = $m->real_escape_string($names['sv']);
    $codeEsc = $m->real_escape_string($code);

    $m->query("UPDATE products SET name='{$en}', name_en='{$en}'
      WHERE (store_id IS NULL OR store_id=0)
        AND sku LIKE 'AE1005012577529604-{$codeEsc}%'
        AND sku NOT LIKE '%-S%'");
    echo "catalog {$code} => {$names['en']} ({$m->affected_rows})\n";

    $m->query("UPDATE products SET name='{$sv}', name_en='{$en}'
      WHERE store_id={$storeId}
        AND sku LIKE 'AE1005012577529604-{$codeEsc}%'");
    echo "store {$code} => {$names['sv']} / {$names['en']} ({$m->affected_rows})\n";
}

$q = $m->query("SELECT id, store_id, name, name_en, sku FROM products WHERE sku LIKE 'AE1005012577529604%' ORDER BY store_id IS NULL, id");
while ($r = $q->fetch_assoc()) {
    echo json_encode($r, JSON_UNESCAPED_UNICODE) . "\n";
}
echo "DONE\n";
