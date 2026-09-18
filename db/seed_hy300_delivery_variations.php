<?php
/**
 * Create 2 delivery variations for the HY300 Mini Projector,
 * plus sample FAQs and reviews.
 *
 * Run: php db/seed_hy300_delivery_variations.php
 */
$dbHost = '127.0.0.1';
$dbName = 'ecommerce';
$dbUser = 'root';
$dbPass = '';
$configFile = dirname(__DIR__) . '/config.php';
if (is_file($configFile)) {
    include $configFile;
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
    fwrite(STDERR, "MySQL is not running.\n" . $mysqli->connect_error . "\n");
    exit(1);
}
$mysqli->set_charset('utf8mb4');

function col_exists($mysqli, $table, $col)
{
    $r = $mysqli->query("SHOW COLUMNS FROM `{$table}` LIKE '" . $mysqli->real_escape_string($col) . "'");
    return $r && $r->num_rows > 0;
}

if (!col_exists($mysqli, 'products', 'parent_sku')) {
    $mysqli->query("ALTER TABLE products ADD COLUMN parent_sku VARCHAR(100) NOT NULL DEFAULT '' AFTER sku");
}
if (!col_exists($mysqli, 'products', 'is_default')) {
    $mysqli->query("ALTER TABLE products ADD COLUMN is_default TINYINT(1) NOT NULL DEFAULT 0 AFTER parent_sku");
}

$slugNeedle = 'hy300-mini-projector-pro-4k-smart-projector';
$skuNeedle = 'B0D2QTTZXN';
$parents = array();
$sql = "SELECT * FROM products
        WHERE (slug LIKE '%" . $mysqli->real_escape_string($slugNeedle) . "%'
           OR sku LIKE '%" . $mysqli->real_escape_string($skuNeedle) . "%')
          AND (parent_sku IS NULL OR parent_sku = '')";
$res = $mysqli->query($sql);
if ($res) {
    while ($row = $res->fetch_assoc()) {
        $parents[] = $row;
    }
}
if (empty($parents)) {
    fwrite(STDERR, "HY300 product not found.\n");
    exit(1);
}

$variants = array(
    array(
        'suffix' => '2-3',
        'name_suffix' => '2–3 day delivery',
        'min' => 2,
        'max' => 3,
        'default' => 1,
        'description' => 'Express delivery. This option arrives in 2 to 3 days.',
    ),
    array(
        'suffix' => '5-7',
        'name_suffix' => '5–7 day delivery',
        'min' => 5,
        'max' => 7,
        'default' => 0,
        'description' => 'Standard delivery. This option arrives in 5 to 7 days.',
    ),
);

$mysqli->query("CREATE TABLE IF NOT EXISTS product_faqs (
    id INT(11) NOT NULL AUTO_INCREMENT,
    store_id INT(11) NOT NULL,
    product_id INT(11) NOT NULL,
    question VARCHAR(500) NOT NULL,
    answer TEXT NOT NULL,
    status TINYINT(1) NOT NULL DEFAULT 1,
    sort_order INT(11) NOT NULL DEFAULT 0,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NULL,
    deleted_at DATETIME NULL,
    PRIMARY KEY (id),
    KEY store_id (store_id),
    KEY product_id (product_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

$mysqli->query("CREATE TABLE IF NOT EXISTS product_reviews (
    id INT(11) NOT NULL AUTO_INCREMENT,
    store_id INT(11) NOT NULL,
    product_id INT(11) NOT NULL,
    customer_id INT(11) NOT NULL DEFAULT 0,
    customer_name VARCHAR(150) NOT NULL DEFAULT '',
    rating TINYINT(1) NOT NULL DEFAULT 5,
    title VARCHAR(255) NOT NULL DEFAULT '',
    content TEXT NOT NULL,
    status TINYINT(1) NOT NULL DEFAULT 1,
    approval_status VARCHAR(20) NOT NULL DEFAULT 'pending',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NULL,
    deleted_at DATETIME NULL,
    PRIMARY KEY (id),
    KEY store_id (store_id),
    KEY product_id (product_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

$faqs = array(
    array('How long does delivery take?', 'Choose 2–3 day delivery for a faster option, or 5–7 day delivery for the standard option. The estimated arrival dates are shown on the product page after you select an option.', 1),
    array('What is the difference between the two options?', 'Both options are the same projector. The only difference is shipping speed: 2 to 3 days, or 5 to 7 days.', 2),
    array('Does it work with Netflix or YouTube?', 'The projector runs Android 11 with Wi-Fi 6, so you can install and use popular streaming apps. Availability of specific apps depends on your account and region.', 3),
    array('Is it suitable for indoor and outdoor use?', 'Yes. It is compact and portable, so it can be used at home or taken on trips. Use a stable surface and keep it dry.', 4),
);

$reviews = array(
    array('Amelia H.', 5, 'Bright and easy to set up', 'Picture is sharp on a living-room wall and Wi-Fi connected quickly. Express delivery arrived in three days.'),
    array('James P.', 4, 'Great for movie nights', 'Android apps installed without fuss. Auto keystone saved a lot of setup time. Would buy again.'),
    array('Sofia R.', 5, 'Proper portable projector', 'Small enough to pack, speakers are decent, and the 130 inch claim is realistic in a dark room.'),
    array('Liam K.', 4, 'Fast delivery option is worth it', 'Chose the 2–3 day option and it turned up on day 2. Product matches the listing.'),
);

$skipCopy = array('id' => true);
$storeIds = array();

foreach ($parents as $parent) {
    $parentId = (int) $parent['id'];
    $storeId = isset($parent['store_id']) ? (int) $parent['store_id'] : 0;
    $parentSku = trim((string) $parent['sku']);
    if ($parentSku === '') {
        $parentSku = 'HY300-' . $parentId;
        $mysqli->query("UPDATE products SET sku='" . $mysqli->real_escape_string($parentSku) . "' WHERE id={$parentId}");
        $parent['sku'] = $parentSku;
    }
    if ($storeId > 0) {
        $storeIds[$storeId] = $storeId;
    }

    $gallery = array();
    $imgRes = $mysqli->query("SELECT image FROM product_images WHERE product_id={$parentId} ORDER BY sort_order, id");
    if ($imgRes) {
        while ($img = $imgRes->fetch_assoc()) {
            $gallery[] = $img['image'];
        }
    }

    foreach ($variants as $variant) {
        $childSku = $parentSku . '-' . $variant['suffix'];
        $storeSql = $storeId > 0 ? 'store_id=' . $storeId : '(store_id IS NULL OR store_id=0)';
        $existing = $mysqli->query("SELECT id FROM products WHERE sku='" . $mysqli->real_escape_string($childSku) . "' AND {$storeSql} LIMIT 1")->fetch_assoc();

        $fields = array();
        foreach ($parent as $key => $value) {
            if (isset($skipCopy[$key]) || $value === null) {
                continue;
            }
            $fields[$key] = $value;
        }
        $fields['sku'] = $childSku;
        $fields['parent_sku'] = $parentSku;
        $fields['is_default'] = $variant['default'];
        $fields['name'] = $parent['name'] . ' — ' . $variant['name_suffix'];
        $fields['slug'] = rtrim((string) $parent['slug'], '-') . '-' . $variant['suffix'] . '-day-delivery';
        $fields['ship_min_days'] = $variant['min'];
        $fields['ship_max_days'] = $variant['max'];
        $fields['description'] = $variant['description'];
        $fields['status'] = 1;
        if (isset($fields['stock']) && (int) $fields['stock'] < 1) {
            $fields['stock'] = 8;
        }

        $set = array();
        foreach ($fields as $key => $value) {
            $set[] = "`{$key}`='" . $mysqli->real_escape_string((string) $value) . "'";
        }
        $setSql = implode(', ', $set);

        if ($existing) {
            $childId = (int) $existing['id'];
            $mysqli->query("UPDATE products SET {$setSql} WHERE id={$childId}");
        } else {
            $mysqli->query("INSERT INTO products SET {$setSql}");
            $childId = (int) $mysqli->insert_id;
        }
        if ($mysqli->error) {
            fwrite(STDERR, $mysqli->error . "\n");
            continue;
        }

        $mysqli->query("DELETE FROM product_images WHERE product_id={$childId}");
        $sort = 1;
        foreach ($gallery as $image) {
            $img = $mysqli->real_escape_string($image);
            $mysqli->query("INSERT INTO product_images (product_id, image, sort_order) VALUES ({$childId}, '{$img}', {$sort})");
            $sort++;
        }
        echo "Store {$storeId} child sku={$childSku} id={$childId} delivery={$variant['min']}-{$variant['max']}\n";
    }

    if ($storeId > 0) {
        $mysqli->query("DELETE FROM product_faqs WHERE store_id={$storeId} AND product_id={$parentId} AND deleted_at IS NULL AND question LIKE 'How long does delivery take?'");
        foreach ($faqs as $faq) {
            $q = $mysqli->real_escape_string($faq[0]);
            $exists = $mysqli->query("SELECT id FROM product_faqs WHERE store_id={$storeId} AND product_id={$parentId} AND question='{$q}' AND deleted_at IS NULL LIMIT 1")->fetch_assoc();
            if ($exists) {
                $mysqli->query("UPDATE product_faqs SET answer='" . $mysqli->real_escape_string($faq[1]) . "', status=1, sort_order=" . (int) $faq[2] . " WHERE id=" . (int) $exists['id']);
                continue;
            }
            $mysqli->query("INSERT INTO product_faqs (store_id, product_id, question, answer, status, sort_order, created_at)
                VALUES ({$storeId}, {$parentId}, '{$q}', '" . $mysqli->real_escape_string($faq[1]) . "', 1, " . (int) $faq[2] . ", NOW())");
        }
        foreach ($reviews as $review) {
            $name = $mysqli->real_escape_string($review[0]);
            $title = $mysqli->real_escape_string($review[2]);
            $exists = $mysqli->query("SELECT id FROM product_reviews WHERE store_id={$storeId} AND product_id={$parentId} AND customer_name='{$name}' AND title='{$title}' AND deleted_at IS NULL LIMIT 1")->fetch_assoc();
            $payload = "customer_name='{$name}', rating=" . (int) $review[1] . ", title='{$title}', content='" . $mysqli->real_escape_string($review[3]) . "', status=1, approval_status='approved'";
            if ($exists) {
                $mysqli->query("UPDATE product_reviews SET {$payload} WHERE id=" . (int) $exists['id']);
                continue;
            }
            $mysqli->query("INSERT INTO product_reviews (store_id, product_id, customer_id, customer_name, rating, title, content, status, approval_status, created_at)
                VALUES ({$storeId}, {$parentId}, 0, '{$name}', " . (int) $review[1] . ", '{$title}', '" . $mysqli->real_escape_string($review[3]) . "', 1, 'approved', NOW())");
        }
        echo "FAQs and reviews saved for product {$parentId} store {$storeId}\n";
    }
}

foreach ($storeIds as $storeId) {
    $hasField = col_exists($mysqli, 'store_settings', 'field_key');
    $hasSetting = col_exists($mysqli, 'store_settings', 'setting_key');
    if ($hasField) {
        $row = $mysqli->query("SELECT id FROM store_settings WHERE store_id={$storeId} AND field_key='product_detail_design' LIMIT 1")->fetch_assoc();
        if ($row) {
            $mysqli->query("UPDATE store_settings SET field_value='new'" . ($hasSetting ? ", setting_value='new'" : '') . " WHERE id=" . (int) $row['id']);
        } else {
            $themeId = $hasField ? 0 : null;
            $cols = 'store_id, field_key, field_value';
            $vals = "{$storeId}, 'product_detail_design', 'new'";
            if (col_exists($mysqli, 'store_settings', 'theme_id')) {
                $cols = 'store_id, theme_id, field_key, field_value';
                $vals = "{$storeId}, 0, 'product_detail_design', 'new'";
            }
            if ($hasSetting) {
                $cols .= ', setting_key, setting_value';
                $vals .= ", 'product_detail_design', 'new'";
            }
            $mysqli->query("INSERT INTO store_settings ({$cols}) VALUES ({$vals})");
        }
    } elseif ($hasSetting) {
        $row = $mysqli->query("SELECT id FROM store_settings WHERE store_id={$storeId} AND setting_key='product_detail_design' LIMIT 1")->fetch_assoc();
        if ($row) {
            $mysqli->query("UPDATE store_settings SET setting_value='new' WHERE id=" . (int) $row['id']);
        } else {
            $mysqli->query("INSERT INTO store_settings (store_id, setting_key, setting_value) VALUES ({$storeId}, 'product_detail_design', 'new')");
        }
    }
    echo "Store {$storeId} product detail design set to New Design\n";
}

echo "Done.\n";
