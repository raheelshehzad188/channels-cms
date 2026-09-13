<?php
/**
 * Seed ZENVello catalog + store products from theme assets.
 * Run: php db/seed_zenvello_products.php
 */
$host = '127.0.0.1';
$db = 'ecommerce';
$user = 'root';
$pass = '';

$mysqli = @new mysqli($host, $user, $pass, $db);
if ($mysqli->connect_errno) {
    fwrite(STDERR, "DB connect failed: {$mysqli->connect_error}\n");
    exit(1);
}
$mysqli->set_charset('utf8mb4');

function upsert_setting($mysqli, $key, $value) {
    $stmt = $mysqli->prepare('INSERT INTO platform_settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)');
    $stmt->bind_param('ss', $key, $value);
    $stmt->execute();
    $stmt->close();
}

// UK supplier
$supplierId = 0;
$res = $mysqli->query("SELECT id FROM suppliers WHERE name = 'ZENVello UK Supply' LIMIT 1");
if ($row = $res->fetch_assoc()) {
    $supplierId = (int) $row['id'];
} else {
    $mysqli->query("INSERT INTO suppliers (name, country_id, status) VALUES ('ZENVello UK Supply', 5, 1)");
    $supplierId = (int) $mysqli->insert_id;
    // fallback if status column missing
    if (!$supplierId) {
        $mysqli->query("INSERT INTO suppliers (name, country_id) VALUES ('ZENVello UK Supply', 5)");
        $supplierId = (int) $mysqli->insert_id;
    }
}

$store = $mysqli->query("SELECT id FROM stores WHERE domain = 'zenvello.ecommerce.test' LIMIT 1")->fetch_assoc();
if (!$store) {
    fwrite(STDERR, "ZENVello store not found\n");
    exit(1);
}
$storeId = (int) $store['id'];
$mysqli->query("UPDATE stores SET country_id = 5, owner_name = 'ZENVello Owner' WHERE id = {$storeId}");

$platformFee = 2.00;
$commission = 2.00;
$createdBy = 2; // ecommerce user

$products = array(
    // Flash deals / featured from home
    array('Halloween LED Mask', 'zen-led-mask', 'led-mask.jpg', 14.99, 24.99, 8.00, 'Light-up LED face mask with multiple glow modes for Halloween nights.'),
    array('Wireless Earbuds', 'zen-earbuds', 'earbuds.jpg', 19.99, 29.99, 12.00, 'Compact wireless earbuds with clear sound and long battery life.'),
    array('Smart Watch', 'zen-smart-watch', 'smart-watch.jpg', 24.99, 49.99, 14.00, 'Fitness-ready smart watch with heart-rate tracking and notifications.'),
    array('Home Security Camera', 'zen-security-camera', 'security-camera.jpg', 29.99, 49.99, 18.00, 'Wi-Fi home security camera with night vision and motion alerts.'),
    array('Pet Grooming Kit', 'zen-grooming-kit', 'grooming-kit.jpg', 12.99, 19.99, 6.00, 'Complete pet grooming kit for dogs and cats.'),
    array('Halloween Pumpkin Lights', 'zen-pumpkin-lights', 'pumpkin-lights.jpg', 9.99, 15.99, 4.00, 'Festive pumpkin string lights for seasonal décor.'),
    array('Wireless Projector', 'zen-projector', 'projector.jpg', 34.99, 0, 22.00, 'Portable wireless projector for movies and presentations.'),
    array('Air Fryer 5L', 'zen-air-fryer', 'air-fryer.jpg', 44.99, 0, 30.00, '5 litre air fryer for healthier everyday cooking.'),
    array('Halloween Skeleton', 'zen-skeleton', 'skeleton.jpg', 12.99, 0, 6.00, 'Classic Halloween skeleton prop for indoor or outdoor display.'),
    array('LED Strip Lights', 'zen-led-strip', 'led-strip.jpg', 16.99, 0, 8.00, 'Colour-changing LED strip lights for rooms and gaming setups.'),
    array('Portable Blender', 'zen-blender', 'blender.jpg', 18.99, 0, 10.00, 'USB rechargeable portable blender for smoothies on the go.'),
    array('Car Phone Holder', 'zen-phone-holder', 'phone-holder.jpg', 9.99, 0, 4.00, 'Secure magnetic car phone holder for dash or vent mounts.'),
    // Halloween range
    array('Halloween Pumpkin Lights (3 Pack)', 'zen-h-pumpkin-lights', 'h-pumpkin-lights.jpg', 9.99, 16.99, 4.00, 'Pack of 3 LED pumpkin lights for pathways and porches.'),
    array('6FT Inflatable Ghost Decoration', 'zen-h-ghost', 'h-ghost-inflatable.jpg', 29.99, 44.99, 18.00, 'Eye-catching 6ft inflatable ghost for outdoor Halloween displays.'),
    array('Life Size Poseable Skeleton 170cm', 'zen-h-skeleton', 'h-skeleton.jpg', 49.99, 0, 32.00, 'Life-size poseable skeleton for haunted house setups.'),
    array('Witch Costume Set (Dress + Hat)', 'zen-h-witch', 'h-witch-costume.jpg', 24.99, 32.99, 14.00, 'Complete witch costume set including dress and hat.'),
    array('Halloween LED Mask (3 Modes)', 'zen-h-led-mask', 'h-led-mask.jpg', 14.99, 23.99, 8.00, 'Rechargeable LED mask with three lighting modes.'),
    array('Scary Clown Costume (Adult)', 'zen-h-clown', 'h-clown-costume.jpg', 34.99, 0, 20.00, 'Adult scary clown costume for parties and events.'),
    array('Giant Spider Decoration (200cm)', 'zen-h-spider', 'h-spider.jpg', 27.99, 39.99, 16.00, 'Giant 200cm spider decoration for walls and gardens.'),
    array('Pumpkin Pet Costume (Dog & Cat)', 'zen-h-pet-pumpkin', 'h-pet-pumpkin.jpg', 12.99, 15.99, 6.00, 'Cute pumpkin costume suitable for dogs and cats.'),
    array('Bloody Handprint Window Stickers (12 pcs)', 'zen-h-handprints', 'h-handprints.jpg', 6.99, 10.99, 2.50, 'Pack of 12 bloody handprint stickers for windows.'),
    array('Halloween Fog Machine (400W)', 'zen-h-fog', 'h-fog-machine.jpg', 29.99, 41.99, 18.00, '400W fog machine for spooky party atmospheres.'),
    array('Solar Tombstone Pathway Lights (4 Pack)', 'zen-h-tombstone', 'h-tombstone.jpg', 19.99, 0, 10.00, 'Solar-powered tombstone lights for garden pathways.'),
    array('Hanging Ghost Decoration (2 Pack)', 'zen-h-hanging-ghost', 'h-hanging-ghost.jpg', 11.99, 19.99, 5.00, 'Pair of hanging ghosts for trees, porches and ceilings.'),
    // Cab extras
    array('Scream Mask', 'zen-cab-scream', 'cab-scream.jpg', 11.99, 16.99, 5.00, 'Classic scream-style Halloween mask.'),
    array('Witch Hat', 'zen-cab-hat', 'cab-hat.jpg', 7.99, 12.99, 3.00, 'Pointed witch hat accessory for costumes.'),
    array('Skeleton Gloves', 'zen-cab-gloves', 'cab-gloves.jpg', 5.99, 9.99, 2.00, 'Skeleton print gloves for Halloween outfits.'),
    array('Bloody Apron', 'zen-cab-apron', 'cab-apron.jpg', 8.99, 13.99, 3.50, 'Horror bloody apron prop for costume parties.'),
    array('Fake Blood Spray', 'zen-cab-blood', 'cab-blood.jpg', 4.99, 7.99, 1.50, 'Washable fake blood spray for effects and costumes.'),
    array('Carved Pumpkin Decor', 'zen-cab-pumpkin', 'cab-pumpkin.jpg', 9.99, 14.99, 4.00, 'Decorative carved pumpkin centrepiece.'),
);

$imgBase = 'uploads/products/zenvello/';
$createdCatalog = 0;
$createdStore = 0;

foreach ($products as $p) {
    list($name, $slug, $file, $sell, $compare, $base, $desc) = $p;
    $image = $imgBase . $file;
    $abs = __DIR__ . '/../' . $image;
    if (!is_file($abs)) {
        echo "Skip missing image: {$file}\n";
        continue;
    }

    $wholesale = round($base + $commission + $platformFee, 2);
    $maxSale = $compare > 0 ? $compare : round($sell * 1.35, 2);
    $sku = strtoupper(str_replace('-', '', $slug));

    // Catalog product (available to stores)
    $exists = $mysqli->query("SELECT id FROM products WHERE slug = '" . $mysqli->real_escape_string($slug) . "' AND (store_id IS NULL OR store_id = 0) LIMIT 1")->fetch_assoc();
    if ($exists) {
        $catalogId = (int) $exists['id'];
        $mysqli->query("UPDATE products SET
            supplier_id = {$supplierId},
            country_id = 5,
            name = '" . $mysqli->real_escape_string($name) . "',
            sku = '" . $mysqli->real_escape_string($sku) . "',
            price = {$base},
            max_sale_price = {$maxSale},
            compare_price = {$compare},
            cost_price = {$base},
            stock = 120,
            description = '" . $mysqli->real_escape_string($desc) . "',
            image = '" . $mysqli->real_escape_string($image) . "',
            status = 1,
            created_by = {$createdBy}
            WHERE id = {$catalogId}");
    } else {
        $mysqli->query("INSERT INTO products
            (store_id, source_product_id, supplier_id, country_id, name, sku, slug, price, max_sale_price, compare_price, cost_price, stock, description, seo_title, seo_description, seo_keywords, image, status, created_by)
            VALUES
            (NULL, NULL, {$supplierId}, 5,
             '" . $mysqli->real_escape_string($name) . "',
             '" . $mysqli->real_escape_string($sku) . "',
             '" . $mysqli->real_escape_string($slug) . "',
             {$base}, {$maxSale}, {$compare}, {$base}, 120,
             '" . $mysqli->real_escape_string($desc) . "',
             '" . $mysqli->real_escape_string($name) . "',
             '" . $mysqli->real_escape_string($desc) . "',
             'zenvello, halloween, home, electronics',
             '" . $mysqli->real_escape_string($image) . "',
             1, {$createdBy})");
        $catalogId = (int) $mysqli->insert_id;
        $createdCatalog++;
    }

    // Store-owned copy
    $storeSlug = $slug . '-zv';
    $storeRow = $mysqli->query("SELECT id FROM products WHERE store_id = {$storeId} AND (source_product_id = {$catalogId} OR slug = '" . $mysqli->real_escape_string($storeSlug) . "') LIMIT 1")->fetch_assoc();
    if ($storeRow) {
        $storeProductId = (int) $storeRow['id'];
        $mysqli->query("UPDATE products SET
            name = '" . $mysqli->real_escape_string($name) . "',
            sku = '" . $mysqli->real_escape_string($sku) . "-ZV',
            price = {$sell},
            max_sale_price = {$maxSale},
            compare_price = {$compare},
            cost_price = {$wholesale},
            stock = 80,
            description = '" . $mysqli->real_escape_string($desc) . "',
            image = '" . $mysqli->real_escape_string($image) . "',
            status = 1,
            created_by = {$createdBy},
            source_product_id = {$catalogId}
            WHERE id = {$storeProductId}");
    } else {
        $mysqli->query("INSERT INTO products
            (store_id, source_product_id, supplier_id, country_id, name, sku, slug, price, max_sale_price, compare_price, cost_price, stock, description, seo_title, seo_description, seo_keywords, image, status, created_by)
            VALUES
            ({$storeId}, {$catalogId}, {$supplierId}, 5,
             '" . $mysqli->real_escape_string($name) . "',
             '" . $mysqli->real_escape_string($sku) . "-ZV',
             '" . $mysqli->real_escape_string($storeSlug) . "',
             {$sell}, {$maxSale}, {$compare}, {$wholesale}, 80,
             '" . $mysqli->real_escape_string($desc) . "',
             '" . $mysqli->real_escape_string($name) . "',
             '" . $mysqli->real_escape_string($desc) . "',
             'zenvello, shop',
             '" . $mysqli->real_escape_string($image) . "',
             1, {$createdBy})");
        $createdStore++;
    }
}

// Ensure store settings exist for theme fields
$theme = $mysqli->query("SELECT id FROM themes WHERE slug = 'zenvello' LIMIT 1")->fetch_assoc();
if ($theme) {
    $themeId = (int) $theme['id'];
    $mysqli->query("UPDATE stores SET theme_id = {$themeId} WHERE id = {$storeId}");
    $fields = $mysqli->query("SELECT field_key, default_value FROM theme_setting_fields WHERE theme_id = {$themeId}");
    while ($f = $fields->fetch_assoc()) {
        $k = $mysqli->real_escape_string($f['field_key']);
        $v = $mysqli->real_escape_string($f['default_value']);
        $exists = $mysqli->query("SELECT id FROM store_settings WHERE store_id = {$storeId} AND field_key = '{$k}' LIMIT 1")->fetch_assoc();
        if (!$exists) {
            $mysqli->query("INSERT INTO store_settings (store_id, theme_id, field_key, field_value) VALUES ({$storeId}, {$themeId}, '{$k}', '{$v}')");
        }
    }
}

$countStore = (int) $mysqli->query("SELECT COUNT(*) c FROM products WHERE store_id = {$storeId} AND status = 1")->fetch_assoc()['c'];
$countCat = (int) $mysqli->query("SELECT COUNT(*) c FROM products WHERE (store_id IS NULL OR store_id = 0) AND slug LIKE 'zen-%'")->fetch_assoc()['c'];

echo "Supplier ID: {$supplierId}\n";
echo "Store ID: {$storeId}\n";
echo "New catalog: {$createdCatalog}, new store copies: {$createdStore}\n";
echo "Active ZENVello store products: {$countStore}\n";
echo "Catalog ZEN products: {$countCat}\n";
