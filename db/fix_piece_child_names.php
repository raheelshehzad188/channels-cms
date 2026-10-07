<?php
/**
 * Fix child product names after piece-label cleanup.
 * - Recover Set/Size variants from slug
 * - Keep pack-only variants as "N Piece" / "Style · N Piece"
 * - Remove bad AE* sku-as-style labels
 *
 * Usage: php db/fix_piece_child_names.php
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

$m = new mysqli($dbHost, $dbUser, $dbPass, $dbName);
if ($m->connect_error) {
    fwrite(STDERR, $m->connect_error . "\n");
    exit(1);
}
$m->set_charset('utf8mb4');

function rebuild_set_size_name($slug)
{
    if (preg_match('/^(.*)-(\d+)-pack-set-(\d+)-([a-z0-9]+)$/i', $slug, $mm)) {
        $base = ucwords(str_replace('-', ' ', $mm[1]));
        return $base . ' ' . ((int) $mm[2]) . '-Pack - Set ' . ((int) $mm[3]) . ' / ' . strtoupper($mm[4]);
    }
    return '';
}

function pack_only_label($slug, $sku)
{
    $skuU = strtoupper((string) $sku);
    $slugL = strtolower((string) $slug);

    // Not pack-only if set/size present
    if (preg_match('/-SET-\d+-/i', $skuU) || preg_match('/set-\d+-[a-z0-9]+$/i', $slugL)) {
        return '';
    }

    // style-Npcs
    if (preg_match('/(?:^|-)([a-z][a-z0-9]{1,20})-(\d+)pcs(?:-|$)/i', $slugL, $mm)
        || preg_match('/(?:^|-)([A-Z][A-Z0-9]{1,20})-(\d+)PCS(?:-|$)/', $skuU, $mm)) {
        $style = ucwords(strtolower($mm[1]));
        $qty = (int) $mm[2];
        if (preg_match('/^ae\d/i', $style) || preg_match('/^ty\d/i', $style)) {
            return $qty . ' Piece';
        }
        return $style . ' · ' . $qty . ' Piece';
    }

    // color-N-pack at end
    if (preg_match('/-([a-z]{3,20})-(\d+)-pack(?:-s\d+)?$/i', $slugL, $mm)
        || preg_match('/-([A-Z]{3,20})-(\d+)-PACK(?:-S\d+)?$/i', $skuU, $mm)) {
        $style = ucwords(strtolower($mm[1]));
        $qty = (int) $mm[2];
        $ignore = array('led', 'one', 'size', 'pack', 'piece', 'pieces', 'for', 'with', 'and', 'the', 'non', 'set');
        if (in_array(strtolower($style), $ignore, true) || preg_match('/^ae\d/i', $style)) {
            return $qty . ' Piece';
        }
        return $style . ' · ' . $qty . ' Piece';
    }

    // N-piece / N-pack
    if (preg_match('/(?:^|-)(\d+)-(?:piece|pack|pcs)(?:-|$)/i', $slugL . '-' . $skuU, $mm)) {
        return ((int) $mm[1]) . ' Piece';
    }

    return '';
}

function looks_damaged($name)
{
    $name = trim((string) $name);
    if ($name === '') {
        return true;
    }
    if (preg_match('/^Ae\d/i', $name) || preg_match('/^AE\d/', $name) || preg_match('/^TY\d/i', $name)) {
        return true;
    }
    if (preg_match('/^[0-9]+ Piece$/i', $name)) {
        return true;
    }
    if (preg_match('/^[A-Za-z0-9]+ · [0-9]+ Piece$/i', $name)) {
        return true;
    }
    return false;
}

$r = $m->query("SELECT id, store_id, sku, slug, name, parent_sku
    FROM products
    WHERE TRIM(IFNULL(parent_sku,'')) != ''");
if (!$r) {
    fwrite(STDERR, $m->error . "\n");
    exit(1);
}

$updated = 0;
$samples = array();
while ($row = $r->fetch_assoc()) {
    $id = (int) $row['id'];
    $name = $row['name'];
    $slug = $row['slug'];
    $sku = $row['sku'];

    $new = rebuild_set_size_name($slug);
    if ($new === '') {
        $pack = pack_only_label($slug, $sku);
        // Only rewrite pack-only when current name looks damaged OR already a Piece label
        if ($pack !== '' && (looks_damaged($name) || preg_match('/Piece$/i', $name))) {
            $new = $pack;
        }
    } elseif (!looks_damaged($name) && stripos($name, 'Set ') !== false) {
        // Already recovered set/size name
        $new = '';
    }

    // Force fix AE-as-style names
    if ($new === '' && preg_match('/^Ae\d/i', $name)) {
        $new = pack_only_label($slug, $sku);
        if ($new === '') {
            $new = ucwords(str_replace('-', ' ', preg_replace('/^ae\d+-/i', '', $slug)));
        }
    }

    if ($new === '' || $new === $name) {
        continue;
    }

    $stmt = $m->prepare('UPDATE products SET name = ? WHERE id = ?');
    $stmt->bind_param('si', $new, $id);
    $stmt->execute();
    $stmt->close();
    $updated++;
    if (count($samples) < 35) {
        $samples[] = $id . ' | ' . $name . ' => ' . $new;
    }
}

echo "updated={$updated}\n";
foreach ($samples as $s) {
    echo $s . "\n";
}

$bad = $m->query("SELECT COUNT(*) c FROM products WHERE name LIKE 'Ae%' OR name LIKE 'AE100%'")->fetch_assoc();
echo 'bad_ae_names=' . $bad['c'] . "\n";

echo "---UNDERWEAR---\n";
$q = $m->query("SELECT id,name FROM products WHERE sku LIKE 'AE1005007662261213-SET-1-%' ORDER BY id LIMIT 4");
while ($x = $q->fetch_assoc()) {
    echo $x['id'] . ' ' . $x['name'] . "\n";
}
echo "---NIPPLE---\n";
$q = $m->query("SELECT id,name FROM products WHERE store_id=8 AND parent_sku='AE1005010046306829-S8' ORDER BY id LIMIT 6");
while ($x = $q->fetch_assoc()) {
    echo $x['id'] . ' ' . $x['name'] . "\n";
}
echo "---GLOVES---\n";
$q = $m->query("SELECT id,name FROM products WHERE store_id=8 AND parent_sku='AE1005012917688341-S8' ORDER BY id");
while ($x = $q->fetch_assoc()) {
    echo $x['id'] . ' ' . $x['name'] . "\n";
}
echo "DONE\n";
