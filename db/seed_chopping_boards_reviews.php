<?php
/**
 * Dummy reviews and FAQs for Amazon Basics chopping boards.
 * Run: php db/seed_chopping_boards_reviews.php
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

$mysqli = @new mysqli($dbHost, $dbUser, $dbPass, $dbName);
if ($mysqli->connect_errno) {
    fwrite(STDERR, "MySQL is not running.\n" . $mysqli->connect_error . "\n");
    exit(1);
}
$mysqli->set_charset('utf8mb4');

$slugNeedle = 'amazon-basics-wooden-chopping-boards-3-piece-set-acacia-wood-pre-oiled';
$skuNeedle = 'B0DYSDL97C';
$parents = array();
$sql = "SELECT id, store_id, sku, slug FROM products
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
    fwrite(STDERR, "Chopping boards product not found.\n");
    exit(1);
}

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

$reviews = array(
    array('Olivia M.', 5, 'Lovely set for everyday cooking', 'The three sizes cover chopping veg, fruit and a small serving board. Grain is nice and they arrived well packed.'),
    array('Daniel W.', 4, 'Solid acacia, easy to clean', 'Hand wash as instructed and they dry quickly. Juice groove on the larger board is actually useful.'),
    array('Hannah B.', 5, 'Looks better in person', 'Colour is richer than the photos. Using the medium board daily and it has not warped.'),
    array('Tom R.', 4, 'Good value three-piece set', 'Knife-friendly surface and the handles make them easy to lift. Would buy again for a gift.'),
);

$faqs = array(
    array('Do I need to oil the boards before first use?', 'They come pre-oiled. Hand wash, dry straight away, and add a little food-safe oil now and then to keep the finish.', 1),
    array('Are they dishwasher safe?', 'No. Hand wash only and towel dry immediately. Do not soak them.', 2),
    array('What sizes are in the set?', 'You get three boards: 38.1 x 25.4 cm, 30.5 x 20.3 cm and 22.9 x 15.2 cm.', 3),
    array('Can I use both sides?', 'Yes. One side is flat and the other has a juice groove to catch run-off.', 4),
);

foreach ($parents as $parent) {
    $storeId = isset($parent['store_id']) ? (int) $parent['store_id'] : 0;
    $productId = (int) $parent['id'];
    if ($storeId < 1) {
        echo "Skip catalog parent id={$productId}\n";
        continue;
    }
    foreach ($reviews as $review) {
        $name = $mysqli->real_escape_string($review[0]);
        $title = $mysqli->real_escape_string($review[2]);
        $content = $mysqli->real_escape_string($review[3]);
        $rating = (int) $review[1];
        $exists = $mysqli->query("SELECT id FROM product_reviews WHERE store_id={$storeId} AND product_id={$productId} AND customer_name='{$name}' AND title='{$title}' AND deleted_at IS NULL LIMIT 1")->fetch_assoc();
        $payload = "customer_name='{$name}', rating={$rating}, title='{$title}', content='{$content}', status=1, approval_status='approved'";
        if ($exists) {
            $mysqli->query("UPDATE product_reviews SET {$payload} WHERE id=" . (int) $exists['id']);
        } else {
            $mysqli->query("INSERT INTO product_reviews (store_id, product_id, customer_id, customer_name, rating, title, content, status, approval_status, created_at)
                VALUES ({$storeId}, {$productId}, 0, '{$name}', {$rating}, '{$title}', '{$content}', 1, 'approved', NOW())");
        }
        if ($mysqli->error) {
            fwrite(STDERR, $mysqli->error . "\n");
        }
    }
    foreach ($faqs as $faq) {
        $q = $mysqli->real_escape_string($faq[0]);
        $a = $mysqli->real_escape_string($faq[1]);
        $sort = (int) $faq[2];
        $exists = $mysqli->query("SELECT id FROM product_faqs WHERE store_id={$storeId} AND product_id={$productId} AND question='{$q}' AND deleted_at IS NULL LIMIT 1")->fetch_assoc();
        if ($exists) {
            $mysqli->query("UPDATE product_faqs SET answer='{$a}', status=1, sort_order={$sort} WHERE id=" . (int) $exists['id']);
        } else {
            $mysqli->query("INSERT INTO product_faqs (store_id, product_id, question, answer, status, sort_order, created_at)
                VALUES ({$storeId}, {$productId}, '{$q}', '{$a}', 1, {$sort}, NOW())");
        }
    }
    echo "Reviews and FAQs saved for product {$productId} store {$storeId}\n";
}

echo "Done.\n";
