<?php
/**
 * Migrate categories to country/parent + seed UK (ZENVello) tree + store picks.
 * Run: php db/seed_zenvello_categories.php
 */
$mysqli = @new mysqli('127.0.0.1', 'root', '', 'ecommerce');
if ($mysqli->connect_errno) {
    fwrite(STDERR, $mysqli->connect_error . "\n");
    exit(1);
}
$mysqli->set_charset('utf8mb4');

function col_exists($mysqli, $table, $col) {
    $r = $mysqli->query("SHOW COLUMNS FROM `{$table}` LIKE '" . $mysqli->real_escape_string($col) . "'");
    return $r && $r->num_rows > 0;
}

$mysqli->query("CREATE TABLE IF NOT EXISTS categories (
  id INT(11) NOT NULL AUTO_INCREMENT,
  country_id INT(11) DEFAULT NULL,
  parent_id INT(11) DEFAULT NULL,
  name VARCHAR(150) NOT NULL,
  slug VARCHAR(160) NOT NULL,
  icon VARCHAR(20) NOT NULL DEFAULT '',
  image VARCHAR(255) NOT NULL DEFAULT '',
  hero_image VARCHAR(255) NOT NULL DEFAULT '',
  description TEXT NULL,
  seo_title VARCHAR(255) NOT NULL DEFAULT '',
  seo_description TEXT NULL,
  seo_keywords VARCHAR(255) NOT NULL DEFAULT '',
  status TINYINT(1) NOT NULL DEFAULT 1,
  sort_order INT(11) NOT NULL DEFAULT 0,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY country_id (country_id),
  KEY parent_id (parent_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

$alters = array(
    'country_id' => "ALTER TABLE categories ADD COLUMN country_id INT(11) DEFAULT NULL AFTER id",
    'parent_id' => "ALTER TABLE categories ADD COLUMN parent_id INT(11) DEFAULT NULL AFTER country_id",
    'hero_image' => "ALTER TABLE categories ADD COLUMN hero_image VARCHAR(255) NOT NULL DEFAULT '' AFTER image",
    'seo_title' => "ALTER TABLE categories ADD COLUMN seo_title VARCHAR(255) NOT NULL DEFAULT '' AFTER description",
    'seo_description' => "ALTER TABLE categories ADD COLUMN seo_description TEXT NULL AFTER seo_title",
    'seo_keywords' => "ALTER TABLE categories ADD COLUMN seo_keywords VARCHAR(255) NOT NULL DEFAULT '' AFTER seo_description",
);
foreach ($alters as $col => $sql) {
    if (!col_exists($mysqli, 'categories', $col)) {
        $mysqli->query($sql);
    }
}

$idx = $mysqli->query("SHOW INDEX FROM categories WHERE Key_name='slug'");
if ($idx && $idx->num_rows > 0) {
    $mysqli->query("ALTER TABLE categories DROP INDEX slug");
}
$idx = $mysqli->query("SHOW INDEX FROM categories WHERE Key_name='country_slug'");
if (!$idx || $idx->num_rows === 0) {
    $mysqli->query("ALTER TABLE categories ADD UNIQUE KEY country_slug (country_id, slug)");
}

$mysqli->query("CREATE TABLE IF NOT EXISTS product_categories (
  product_id INT(11) NOT NULL,
  category_id INT(11) NOT NULL,
  PRIMARY KEY (product_id, category_id),
  KEY category_id (category_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

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

$uk = $mysqli->query("SELECT id FROM countries WHERE id=5 LIMIT 1")->fetch_assoc();
if (!$uk) {
    $uk = $mysqli->query("SELECT id FROM countries WHERE name LIKE '%United Kingdom%' OR name='UK' OR code='GB' LIMIT 1")->fetch_assoc();
}
$countryId = $uk ? (int) $uk['id'] : 5;
echo "UK country_id={$countryId}\n";

$roots = array(
    array('Halloween', 'halloween', '🎃', 1, 'Spooky costumes, décor and party essentials.'),
    array('Christmas', 'christmas', '🎄', 2, 'Festive gifts, lights and holiday décor.'),
    array('Home & Living', 'home-living', '🏠', 3, 'Everyday home upgrades and living essentials.'),
    array('Electronics', 'electronics', '🎧', 4, 'Gadgets, audio, cameras and smart devices.'),
    array('Beauty & Health', 'beauty-health', '🧴', 5, 'Self-care and wellness picks.'),
    array('Toys & Kids', 'toys-kids', '🧸', 6, 'Fun finds for kids and families.'),
    array('Pet Supplies', 'pet-supplies', '🐾', 7, 'Gear and accessories for pets.'),
    array('Gift Ideas', 'gift-ideas', '🎁', 8, 'Ready-to-gift favourites.'),
    array('Deals', 'deals', '🏷️', 9, 'Limited-time offers and sale items.'),
);

$subs = array(
    'halloween' => array(
        array('Costumes', 'halloween-costumes', '👗'),
        array('Decorations', 'halloween-decor', '🕸️'),
        array('Party Props', 'halloween-props', '🩸'),
    ),
    'electronics' => array(
        array('Audio', 'electronics-audio', '🔊'),
        array('Smart Home', 'electronics-smart', '📡'),
        array('Wearables', 'electronics-wearables', '⌚'),
    ),
    'home-living' => array(
        array('Kitchen', 'home-kitchen', '🍳'),
        array('Lighting', 'home-lighting', '💡'),
    ),
);

function upsert_category($mysqli, $countryId, $name, $slug, $icon, $sort, $desc, $parentId = null) {
    $esc = function ($s) use ($mysqli) { return $mysqli->real_escape_string($s); };
    $parentSql = $parentId ? (int) $parentId : 'NULL';
    $row = $mysqli->query("SELECT id FROM categories WHERE country_id={$countryId} AND slug='" . $esc($slug) . "' LIMIT 1")->fetch_assoc();
    if (!$row) {
        // also match legacy global slug
        $row = $mysqli->query("SELECT id FROM categories WHERE slug='" . $esc($slug) . "' AND (country_id IS NULL OR country_id=0 OR country_id={$countryId}) LIMIT 1")->fetch_assoc();
    }
    $seoTitle = $esc($name . ' | Shop');
    $seoDesc = $esc($desc);
    if ($row) {
        $id = (int) $row['id'];
        $mysqli->query("UPDATE categories SET country_id={$countryId}, parent_id={$parentSql}, name='" . $esc($name) . "', icon='" . $esc($icon) . "', description='{$seoDesc}', seo_title='{$seoTitle}', seo_description='{$seoDesc}', sort_order={$sort}, status=1 WHERE id={$id}");
        return $id;
    }
    $mysqli->query("INSERT INTO categories (country_id, parent_id, name, slug, icon, description, seo_title, seo_description, status, sort_order) VALUES (
        {$countryId}, {$parentSql}, '" . $esc($name) . "', '" . $esc($slug) . "', '" . $esc($icon) . "', '{$seoDesc}', '{$seoTitle}', '{$seoDesc}', 1, {$sort})");
    return (int) $mysqli->insert_id;
}

$catIds = array();
foreach ($roots as $c) {
    list($name, $slug, $icon, $sort, $desc) = $c;
    $catIds[$slug] = upsert_category($mysqli, $countryId, $name, $slug, $icon, $sort, $desc, null);
    echo "root {$slug} => {$catIds[$slug]}\n";
}

foreach ($subs as $parentSlug => $children) {
    $pid = isset($catIds[$parentSlug]) ? $catIds[$parentSlug] : 0;
    if (!$pid) {
        continue;
    }
    $i = 1;
    foreach ($children as $child) {
        list($name, $slug, $icon) = $child;
        $catIds[$slug] = upsert_category($mysqli, $countryId, $name, $slug, $icon, $i++, $name . ' in ' . $parentSlug, $pid);
        echo "  sub {$slug} => {$catIds[$slug]}\n";
    }
}

$store = $mysqli->query("SELECT id, theme_id FROM stores WHERE domain='zenvello.ecommerce.test' LIMIT 1")->fetch_assoc();
if (!$store) {
    fwrite(STDERR, "ZENVello store missing\n");
    exit(1);
}
$storeId = (int) $store['id'];
$themeId = (int) $store['theme_id'];
$mysqli->query("UPDATE stores SET country_id={$countryId} WHERE id={$storeId}");

// theme default category hero field
$mysqli->query("INSERT INTO theme_setting_fields (theme_id, field_key, field_label, field_type, is_required, default_value, sort_order)
SELECT {$themeId}, 'category_hero_default', 'Default Category Hero Image', 'image', 0, '', 8
FROM DUAL WHERE NOT EXISTS (
  SELECT 1 FROM theme_setting_fields WHERE theme_id={$themeId} AND field_key='category_hero_default'
)");

$rules = array(
    'halloween' => array('halloween', 'skeleton', 'witch', 'clown', 'spider', 'ghost', 'pumpkin', 'tombstone', 'fog', 'scream', 'bloody', 'handprint', 'mask', 'apron', 'gloves', 'fake blood', 'carved'),
    'halloween-costumes' => array('costume', 'witch', 'clown', 'skeleton', 'mask'),
    'halloween-decor' => array('pumpkin', 'spider', 'tombstone', 'fog', 'ghost', 'inflatable', 'lights'),
    'halloween-props' => array('blood', 'handprint', 'apron', 'gloves', 'scream'),
    'electronics' => array('earbuds', 'smart watch', 'security camera', 'projector', 'led strip', 'phone holder'),
    'electronics-audio' => array('earbuds'),
    'electronics-smart' => array('security camera', 'projector', 'led strip'),
    'electronics-wearables' => array('smart watch'),
    'home-living' => array('air fryer', 'blender', 'led strip', 'projector'),
    'home-kitchen' => array('air fryer', 'blender'),
    'home-lighting' => array('led strip', 'lights'),
    'pet-supplies' => array('pet', 'grooming'),
    'gift-ideas' => array('smart watch', 'earbuds', 'projector', 'air fryer', 'blender'),
    'deals' => array('led mask', 'earbuds', 'smart watch', 'security camera', 'grooming', 'pumpkin lights', 'inflatable', 'spider', 'handprint', 'fog', 'hanging ghost', 'scream', 'witch hat', 'gloves', 'apron', 'fake blood', 'carved'),
    'toys-kids' => array('clown', 'skeleton', 'pet costume'),
);

$products = $mysqli->query("SELECT id, name, compare_price, price FROM products WHERE store_id={$storeId}");
$linked = 0;
while ($p = $products->fetch_assoc()) {
    $name = strtolower($p['name']);
    $matched = array();
    foreach ($rules as $slug => $keywords) {
        foreach ($keywords as $kw) {
            if ($kw !== '' && strpos($name, $kw) !== false && isset($catIds[$slug])) {
                $matched[$slug] = true;
                break;
            }
        }
    }
    if (empty($matched) && isset($catIds['gift-ideas'])) {
        $matched['gift-ideas'] = true;
    }
    foreach (array_keys($matched) as $slug) {
        $cid = (int) $catIds[$slug];
        $mysqli->query("INSERT IGNORE INTO product_categories (product_id, category_id) VALUES (" . (int) $p['id'] . ", {$cid})");
        $linked++;
    }
}

$homeSlugs = array('halloween', 'christmas', 'home-living', 'electronics', 'deals', 'gift-ideas');
$sort = 1;
foreach ($catIds as $slug => $cid) {
    $onHome = in_array($slug, $homeSlugs, true) ? 1 : 0;
    $isRoot = !isset($subs[$slug]) && strpos($slug, '-') === false || in_array($slug, array_column($roots, 1), true);
    // enable all roots + subcats for store
    $exists = $mysqli->query("SELECT id FROM store_category_settings WHERE store_id={$storeId} AND category_id={$cid}")->fetch_assoc();
    if ($exists) {
        $mysqli->query("UPDATE store_category_settings SET enabled=1, show_on_home={$onHome}, sort_order={$sort} WHERE id=" . (int) $exists['id']);
    } else {
        $mysqli->query("INSERT INTO store_category_settings (store_id, category_id, enabled, show_on_home, sort_order) VALUES ({$storeId}, {$cid}, 1, {$onHome}, {$sort})");
    }
    $sort++;
}

$mysqli->query("DELETE FROM store_home_categories WHERE store_id={$storeId}");
$hs = 1;
foreach ($homeSlugs as $slug) {
    if (!isset($catIds[$slug])) {
        continue;
    }
    $mysqli->query("INSERT INTO store_home_categories (store_id, category_id, sort_order) VALUES ({$storeId}, " . (int) $catIds[$slug] . ", {$hs})");
    $hs++;
}

echo "Linked product-category rows≈{$linked}\n";
echo "Done.\n";
