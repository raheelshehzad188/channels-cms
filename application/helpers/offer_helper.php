<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * ZENvello Offer / Discount System
 *
 * OFFER PRECEDENCE (highest wins; only one price offer applies):
 * 1. Active product-level offer (on the product row, or inherited from its parent)
 * 2. Active store/campaign offer matching the product or its categories
 *    (highest offer_priority / campaign priority wins among campaigns)
 * 3. Existing display-only store discount (compare_price inflate) — never stacks
 *    with a real offer from (1) or (2)
 *
 * COUPON RULE:
 * Legacy coupon tables are not integrated into storefront checkout.
 * Product/campaign offers reduce unit/line totals server-side before VAT/shipping.
 * Default policy: do NOT stack a coupon on top of an active product/campaign offer.
 * If coupons are wired later, apply them only when no active offer is present
 * unless business logic explicitly enables stacking.
 *
 * SECURITY:
 * Never trust discounted prices from the browser. Cart stores product id + qty only;
 * final prices are always recalculated from DB offer data via apply_storefront_pricing().
 */

function ensure_product_offer_columns()
{
    static $done = false;
    if ($done) {
        return;
    }
    $done = true;
    $CI =& get_instance();
    if (!$CI->db->table_exists('products')) {
        return;
    }
    $cols = array(
        'offer_enabled' => "TINYINT(1) NOT NULL DEFAULT 0",
        'offer_type' => "VARCHAR(32) NOT NULL DEFAULT 'percent'",
        'offer_value' => "DECIMAL(12,2) NOT NULL DEFAULT 0.00",
        'offer_buy_qty' => "INT(11) NOT NULL DEFAULT 0",
        'offer_get_qty' => "INT(11) NOT NULL DEFAULT 0",
        'offer_bundle_qty' => "INT(11) NOT NULL DEFAULT 0",
        'offer_bundle_price' => "DECIMAL(12,2) NOT NULL DEFAULT 0.00",
        'offer_bundle2_qty' => "INT(11) NOT NULL DEFAULT 0",
        'offer_bundle2_price' => "DECIMAL(12,2) NOT NULL DEFAULT 0.00",
        'offer_label' => "VARCHAR(120) NOT NULL DEFAULT ''",
        'offer_starts_at' => "DATETIME NULL DEFAULT NULL",
        'offer_ends_at' => "DATETIME NULL DEFAULT NULL",
        'offer_priority' => "INT(11) NOT NULL DEFAULT 0",
        'offer_show_badge' => "TINYINT(1) NOT NULL DEFAULT 1",
        'offer_show_countdown' => "TINYINT(1) NOT NULL DEFAULT 0",
        'offer_free_shipping' => "TINYINT(1) NOT NULL DEFAULT 0",
    );
    $after = $CI->db->field_exists('trending_order', 'products')
        ? 'trending_order'
        : ($CI->db->field_exists('is_trending', 'products') ? 'is_trending' : 'status');
    foreach ($cols as $name => $def) {
        if ($CI->db->field_exists($name, 'products')) {
            continue;
        }
        $CI->db->query('ALTER TABLE products ADD COLUMN `' . $name . '` ' . $def . ' AFTER `' . $after . '`');
        $after = $name;
    }
}

function ensure_offer_campaign_tables()
{
    static $done = false;
    if ($done) {
        return;
    }
    $done = true;
    $CI =& get_instance();
    $CI->db->query("CREATE TABLE IF NOT EXISTS store_offer_campaigns (
        id INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
        store_id INT(11) NOT NULL DEFAULT 0,
        name VARCHAR(160) NOT NULL DEFAULT '',
        label VARCHAR(120) NOT NULL DEFAULT '',
        discount_type VARCHAR(32) NOT NULL DEFAULT 'percent',
        discount_value DECIMAL(12,2) NOT NULL DEFAULT 0.00,
        buy_qty INT(11) NOT NULL DEFAULT 0,
        get_qty INT(11) NOT NULL DEFAULT 0,
        bundle_qty INT(11) NOT NULL DEFAULT 0,
        bundle_price DECIMAL(12,2) NOT NULL DEFAULT 0.00,
        starts_at DATETIME NULL DEFAULT NULL,
        ends_at DATETIME NULL DEFAULT NULL,
        priority INT(11) NOT NULL DEFAULT 0,
        show_badge TINYINT(1) NOT NULL DEFAULT 1,
        show_countdown TINYINT(1) NOT NULL DEFAULT 0,
        status TINYINT(1) NOT NULL DEFAULT 1,
        created_at DATETIME NULL DEFAULT NULL,
        updated_at DATETIME NULL DEFAULT NULL,
        PRIMARY KEY (id),
        KEY store_id (store_id),
        KEY status (status),
        KEY starts_at (starts_at),
        KEY ends_at (ends_at)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    $CI->db->query("CREATE TABLE IF NOT EXISTS store_offer_campaign_products (
        campaign_id INT(11) UNSIGNED NOT NULL,
        product_id INT(11) UNSIGNED NOT NULL,
        PRIMARY KEY (campaign_id, product_id),
        KEY product_id (product_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    $CI->db->query("CREATE TABLE IF NOT EXISTS store_offer_campaign_categories (
        campaign_id INT(11) UNSIGNED NOT NULL,
        category_id INT(11) UNSIGNED NOT NULL,
        PRIMARY KEY (campaign_id, category_id),
        KEY category_id (category_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
}

function offer_now()
{
    return date('Y-m-d H:i:s');
}

function offer_normalize_type($type)
{
    $type = strtolower(trim((string) $type));
    $allowed = array('percent', 'fixed', 'buy_x_get_y', 'bundle');
    return in_array($type, $allowed, true) ? $type : 'percent';
}

function offer_window_active($startsAt, $endsAt, $now = null)
{
    $now = $now ?: offer_now();
    $startsAt = $startsAt !== null && $startsAt !== '' ? (string) $startsAt : null;
    $endsAt = $endsAt !== null && $endsAt !== '' ? (string) $endsAt : null;
    if ($startsAt && strcmp($now, $startsAt) < 0) {
        return false;
    }
    if ($endsAt && strcmp($now, $endsAt) > 0) {
        return false;
    }
    return true;
}

function offer_status_label($enabled, $startsAt, $endsAt, $now = null)
{
    $now = $now ?: offer_now();
    if (!(int) $enabled) {
        return 'disabled';
    }
    $startsAt = $startsAt !== null && $startsAt !== '' ? (string) $startsAt : null;
    $endsAt = $endsAt !== null && $endsAt !== '' ? (string) $endsAt : null;
    if ($startsAt && strcmp($now, $startsAt) < 0) {
        return 'scheduled';
    }
    if ($endsAt && strcmp($now, $endsAt) > 0) {
        return 'expired';
    }
    return 'active';
}

function offer_min_price()
{
    return 0.01;
}

/**
 * Compute discounted unit price for percent/fixed. Never negative or zero.
 */
function offer_compute_unit_price($original, $type, $value)
{
    $original = (float) $original;
    if ($original <= 0) {
        return 0.0;
    }
    $type = offer_normalize_type($type);
    $value = (float) $value;
    $final = $original;
    if ($type === 'percent') {
        $value = max(0, min(95, $value));
        $final = round($original * (1 - ($value / 100)), 2);
    } elseif ($type === 'fixed') {
        $value = max(0, $value);
        $final = round($original - $value, 2);
    }
    $min = offer_min_price();
    if ($final < $min) {
        $final = $min;
    }
    if ($final >= $original) {
        return $original;
    }
    return $final;
}

function offer_config_from_product_row($row)
{
    if (!$row || empty($row->offer_enabled)) {
        return null;
    }
    $type = offer_normalize_type(isset($row->offer_type) ? $row->offer_type : 'percent');
    $value = isset($row->offer_value) ? (float) $row->offer_value : 0;
    $buy = isset($row->offer_buy_qty) ? (int) $row->offer_buy_qty : 0;
    $get = isset($row->offer_get_qty) ? (int) $row->offer_get_qty : 0;
    $bundleQty = isset($row->offer_bundle_qty) ? (int) $row->offer_bundle_qty : 0;
    $bundlePrice = isset($row->offer_bundle_price) ? (float) $row->offer_bundle_price : 0;
    $bundle2Qty = isset($row->offer_bundle2_qty) ? (int) $row->offer_bundle2_qty : 0;
    $bundle2Price = isset($row->offer_bundle2_price) ? (float) $row->offer_bundle2_price : 0;
    if ($type === 'percent' || $type === 'fixed') {
        if ($value <= 0) {
            return null;
        }
    } elseif ($type === 'buy_x_get_y') {
        if ($buy < 1 || $get < 1) {
            return null;
        }
    } elseif ($type === 'bundle') {
        if ($bundleQty < 2 || $bundlePrice <= 0) {
            return null;
        }
        if ($bundle2Qty > 0 && ($bundle2Qty < 2 || $bundle2Price <= 0)) {
            $bundle2Qty = 0;
            $bundle2Price = 0;
        }
    }
    $status = offer_status_label(1, isset($row->offer_starts_at) ? $row->offer_starts_at : null, isset($row->offer_ends_at) ? $row->offer_ends_at : null);
    if ($status !== 'active') {
        return null;
    }
    return array(
        'source' => 'product',
        'type' => $type,
        'value' => $value,
        'buy_qty' => $buy,
        'get_qty' => $get,
        'bundle_qty' => $bundleQty,
        'bundle_price' => $bundlePrice,
        'bundle2_qty' => $bundle2Qty,
        'bundle2_price' => $bundle2Price,
        'label' => trim((string) (isset($row->offer_label) ? $row->offer_label : '')),
        'starts_at' => !empty($row->offer_starts_at) ? (string) $row->offer_starts_at : null,
        'ends_at' => !empty($row->offer_ends_at) ? (string) $row->offer_ends_at : null,
        'priority' => isset($row->offer_priority) ? (int) $row->offer_priority : 0,
        'show_badge' => !isset($row->offer_show_badge) || (int) $row->offer_show_badge === 1,
        'show_countdown' => !empty($row->offer_show_countdown),
        'free_shipping' => !empty($row->offer_free_shipping),
        'campaign_id' => 0,
        'campaign_name' => '',
    );
}

function offer_parent_for_product($product)
{
    if (!$product) {
        return null;
    }
    ensure_product_parent_columns();
    $parentSku = isset($product->parent_sku) ? trim((string) $product->parent_sku) : '';
    if ($parentSku === '') {
        return null;
    }
    $CI =& get_instance();
    $storeId = !empty($product->store_id) ? (int) $product->store_id : 0;
    $CI->db->from('products');
    if (function_exists('product_scope_store')) {
        product_scope_store($storeId);
    } elseif ($storeId > 0) {
        $CI->db->where('store_id', $storeId);
    }
    $CI->db->where('sku', $parentSku);
    return $CI->db->get()->row();
}

function offer_active_campaigns($storeId)
{
    static $cache = array();
    $storeId = (int) $storeId;
    if (isset($cache[$storeId])) {
        return $cache[$storeId];
    }
    ensure_offer_campaign_tables();
    $CI =& get_instance();
    $now = offer_now();
    $CI->db->from('store_offer_campaigns');
    $CI->db->where('status', 1);
    $CI->db->group_start();
    $CI->db->where('store_id', $storeId);
    $CI->db->or_where('store_id', 0);
    $CI->db->group_end();
    $CI->db->group_start();
    $CI->db->where('starts_at IS NULL', null, false);
    $CI->db->or_where('starts_at <=', $now);
    $CI->db->group_end();
    $CI->db->group_start();
    $CI->db->where('ends_at IS NULL', null, false);
    $CI->db->or_where('ends_at >=', $now);
    $CI->db->group_end();
    $CI->db->order_by('priority', 'DESC');
    $CI->db->order_by('id', 'DESC');
    $rows = $CI->db->get()->result();
    $out = array();
    foreach ($rows as $row) {
        $type = offer_normalize_type($row->discount_type);
        $cfg = array(
            'source' => 'campaign',
            'type' => $type,
            'value' => (float) $row->discount_value,
            'buy_qty' => (int) $row->buy_qty,
            'get_qty' => (int) $row->get_qty,
            'bundle_qty' => (int) $row->bundle_qty,
            'bundle_price' => (float) $row->bundle_price,
            'bundle2_qty' => 0,
            'bundle2_price' => 0.0,
            'label' => trim((string) $row->label) !== '' ? trim((string) $row->label) : trim((string) $row->name),
            'starts_at' => !empty($row->starts_at) ? (string) $row->starts_at : null,
            'ends_at' => !empty($row->ends_at) ? (string) $row->ends_at : null,
            'priority' => (int) $row->priority,
            'show_badge' => (int) $row->show_badge === 1,
            'show_countdown' => (int) $row->show_countdown === 1,
            'free_shipping' => false,
            'campaign_id' => (int) $row->id,
            'campaign_name' => (string) $row->name,
            'product_ids' => array(),
            'category_ids' => array(),
            'applies_all' => false,
        );
        $prods = $CI->db->select('product_id')->where('campaign_id', (int) $row->id)->get('store_offer_campaign_products')->result();
        foreach ($prods as $p) {
            $cfg['product_ids'][(int) $p->product_id] = true;
        }
        $cats = $CI->db->select('category_id')->where('campaign_id', (int) $row->id)->get('store_offer_campaign_categories')->result();
        foreach ($cats as $c) {
            $cfg['category_ids'][(int) $c->category_id] = true;
        }
        if (empty($cfg['product_ids']) && empty($cfg['category_ids'])) {
            $cfg['applies_all'] = true;
        }
        $out[] = $cfg;
    }
    $cache[$storeId] = $out;
    return $out;
}

function offer_campaign_matches($cfg, $productId, $categoryIds)
{
    if (!empty($cfg['applies_all'])) {
        return true;
    }
    if (!empty($cfg['product_ids'][(int) $productId])) {
        return true;
    }
    foreach ((array) $categoryIds as $cid) {
        if (!empty($cfg['category_ids'][(int) $cid])) {
            return true;
        }
    }
    return false;
}

/**
 * Resolve the single winning offer config for a product (may be null).
 */
function product_resolve_offer($product, $storeId = 0, $categoryMap = null)
{
    if (!$product || empty($product->id)) {
        return null;
    }
    ensure_product_offer_columns();
    $cfg = offer_config_from_product_row($product);
    if ($cfg) {
        return $cfg;
    }
    $parent = offer_parent_for_product($product);
    if ($parent) {
        $cfg = offer_config_from_product_row($parent);
        if ($cfg) {
            $cfg['inherited'] = true;
            return $cfg;
        }
    }

    $storeId = (int) ($storeId ?: (!empty($product->store_id) ? $product->store_id : 0));
    if ($storeId < 1) {
        return null;
    }
    $campaigns = offer_active_campaigns($storeId);
    if (!$campaigns) {
        return null;
    }
    $productId = (int) $product->id;
    $catIds = array();
    if (is_array($categoryMap) && isset($categoryMap[$productId])) {
        $catIds = $categoryMap[$productId];
    } elseif (function_exists('storefront_product_category_map')) {
        $map = storefront_product_category_map(array($productId));
        $catIds = isset($map[$productId]) ? $map[$productId] : array();
    }
    // Also match catalog source id for campaign product picks
    $sourceId = !empty($product->source_product_id) ? (int) $product->source_product_id : 0;
    foreach ($campaigns as $camp) {
        if (offer_campaign_matches($camp, $productId, $catIds)) {
            return $camp;
        }
        if ($sourceId > 0 && offer_campaign_matches($camp, $sourceId, $catIds)) {
            return $camp;
        }
    }
    return null;
}

function offer_default_label($cfg, $original = 0, $final = 0)
{
    if (!$cfg) {
        return '';
    }
    if (!empty($cfg['label'])) {
        return (string) $cfg['label'];
    }
    $type = $cfg['type'];
    if ($type === 'percent' && (float) $cfg['value'] > 0) {
        return (int) round($cfg['value']) . '% OFF';
    }
    if ($type === 'fixed' && (float) $cfg['value'] > 0) {
        return 'SAVE ' . format_money((float) $cfg['value']);
    }
    if ($type === 'buy_x_get_y') {
        return 'BUY ' . (int) $cfg['buy_qty'] . ' GET ' . (int) $cfg['get_qty'];
    }
    if ($type === 'bundle') {
        $label = 'BUY ' . (int) $cfg['bundle_qty'] . ' FOR ' . format_money((float) $cfg['bundle_price']);
        $b2q = isset($cfg['bundle2_qty']) ? (int) $cfg['bundle2_qty'] : 0;
        $b2p = isset($cfg['bundle2_price']) ? (float) $cfg['bundle2_price'] : 0;
        if ($b2q >= 2 && $b2p > 0) {
            $label .= ' · BUY ' . $b2q . ' FOR ' . format_money($b2p);
        }
        return $label;
    }
    if ($original > $final && $final > 0) {
        $pct = (int) round((($original - $final) / $original) * 100);
        if ($pct > 0) {
            return $pct . '% OFF';
        }
    }
    return 'SPECIAL OFFER';
}

function offer_badge_text($cfg, $original = 0, $final = 0)
{
    $label = offer_default_label($cfg, $original, $final);
    if ($label === '') {
        return '';
    }
    // Light decorative prefix without spam
    if (stripos($label, 'halloween') !== false) {
        return '🎃 ' . $label;
    }
    if (stripos($label, 'limited') !== false) {
        return '⚡ ' . $label;
    }
    if (preg_match('/^\d+%\s*OFF/i', $label) || stripos($label, 'save') === 0) {
        return '🔥 ' . $label;
    }
    return '💥 ' . $label;
}

/**
 * Apply winning offer onto product price fields for storefront display/cart.
 */
function apply_product_offer($product, $storeId = 0, $categoryMap = null)
{
    if (!$product || empty($product->id)) {
        return $product;
    }
    if (!empty($product->offer_applied)) {
        return $product;
    }
    $cfg = product_resolve_offer($product, $storeId, $categoryMap);
    $product->active_offer = null;
    $product->offer_applied = true;
    if (!$cfg) {
        return $product;
    }

    $original = (float) $product->price;
    if ($original <= 0) {
        return $product;
    }

    $type = $cfg['type'];
    $final = $original;
    $unitDiscount = 0.0;

    if ($type === 'percent' || $type === 'fixed') {
        $final = offer_compute_unit_price($original, $type, $cfg['value']);
        $unitDiscount = max(0, round($original - $final, 2));
        if ($unitDiscount <= 0) {
            return $product;
        }
        $product->offer_original_price = $original;
        $product->price = $final;
        $existingCompare = isset($product->compare_price) ? (float) $product->compare_price : 0;
        if ($original > $existingCompare) {
            $product->compare_price = $original;
        }
        // Prevent display-only inflate from stacking on real offers
        $product->display_discount_percent = 0;
        if ($type === 'percent') {
            $product->display_discount_percent = (int) round($cfg['value']);
        } elseif ($original > 0) {
            $product->display_discount_percent = (int) round(($unitDiscount / $original) * 100);
        }
    } elseif ($type === 'bundle') {
        $bQty = max(2, (int) $cfg['bundle_qty']);
        $bPrice = (float) $cfg['bundle_price'];
        if ($bQty > 0 && $bPrice > 0) {
            $bundleGross = round($original * $bQty, 2);
            $unitDiscount = max(0, round($bundleGross - $bPrice, 2));
        }
        $b2Qty = isset($cfg['bundle2_qty']) ? (int) $cfg['bundle2_qty'] : 0;
        $b2Price = isset($cfg['bundle2_price']) ? (float) $cfg['bundle2_price'] : 0;
        if ($b2Qty >= 2 && $b2Price > 0) {
            $save2 = max(0, round(($original * $b2Qty) - $b2Price, 2));
            if ($save2 > $unitDiscount) {
                $unitDiscount = $save2;
            }
        }
    }

    $product->active_offer = array(
        'source' => $cfg['source'],
        'type' => $type,
        'value' => (float) $cfg['value'],
        'buy_qty' => (int) $cfg['buy_qty'],
        'get_qty' => (int) $cfg['get_qty'],
        'bundle_qty' => (int) $cfg['bundle_qty'],
        'bundle_price' => (float) $cfg['bundle_price'],
        'bundle2_qty' => isset($cfg['bundle2_qty']) ? (int) $cfg['bundle2_qty'] : 0,
        'bundle2_price' => isset($cfg['bundle2_price']) ? (float) $cfg['bundle2_price'] : 0,
        'label' => offer_default_label($cfg, $original, $final),
        'badge' => $cfg['show_badge'] ? offer_badge_text($cfg, $original, $final) : '',
        'show_badge' => !empty($cfg['show_badge']),
        'show_countdown' => !empty($cfg['show_countdown']) && !empty($cfg['ends_at']),
        'free_shipping' => !empty($cfg['free_shipping']),
        'starts_at' => $cfg['starts_at'],
        'ends_at' => $cfg['ends_at'],
        'ends_at_ts' => !empty($cfg['ends_at']) ? strtotime($cfg['ends_at']) : 0,
        'original_price' => $original,
        'final_price' => $final,
        'unit_discount' => $unitDiscount,
        'save_amount' => $unitDiscount,
        'campaign_id' => (int) $cfg['campaign_id'],
        'campaign_name' => (string) $cfg['campaign_name'],
        'inherited' => !empty($cfg['inherited']),
    );
    return $product;
}

/**
 * Best line total for bundle tiers (qty => pack price), using unit price for leftovers.
 *
 * @param array $tiers list of array(qty, price)
 */
function offer_bundle_best_line($unit, $qty, $tiers)
{
    $qty = max(0, (int) $qty);
    $unit = (float) $unit;
    if ($qty < 1) {
        return 0.0;
    }
    $clean = array();
    foreach ($tiers as $tier) {
        $tq = isset($tier[0]) ? (int) $tier[0] : 0;
        $tp = isset($tier[1]) ? (float) $tier[1] : 0;
        if ($tq >= 2 && $tp > 0) {
            $clean[] = array($tq, $tp);
        }
    }
    if (!$clean) {
        return round($unit * $qty, 2);
    }
    $dp = array_fill(0, $qty + 1, null);
    $dp[0] = 0.0;
    for ($i = 1; $i <= $qty; $i++) {
        $best = round($unit * $i, 2);
        foreach ($clean as $tier) {
            $tq = $tier[0];
            $tp = $tier[1];
            if ($i >= $tq && $dp[$i - $tq] !== null) {
                $cand = round($dp[$i - $tq] + $tp, 2);
                if ($cand < $best) {
                    $best = $cand;
                }
            }
        }
        // also allow buying leftover units one-by-one from previous state
        if ($dp[$i - 1] !== null) {
            $cand = round($dp[$i - 1] + $unit, 2);
            if ($cand < $best) {
                $best = $cand;
            }
        }
        $dp[$i] = $best;
    }
    return (float) $dp[$qty];
}

function apply_storefront_offers($products, $storeId)
{
    ensure_product_offer_columns();
    ensure_offer_campaign_tables();
    $list = is_array($products) ? $products : array($products);
    $ids = array();
    foreach ($list as $product) {
        if ($product && !empty($product->id)) {
            $ids[] = (int) $product->id;
        }
    }
    $catMap = function_exists('storefront_product_category_map')
        ? storefront_product_category_map($ids)
        : array();
    foreach ($list as $product) {
        apply_product_offer($product, $storeId, $catMap);
    }
    return $products;
}

/**
 * Line totals with Buy X Get Y / Bundle support. Percent/fixed already baked into unit price.
 */
function offer_cart_line_calc($product, $qty)
{
    $qty = max(1, (int) $qty);
    $unit = (float) $product->price;
    $originalUnit = isset($product->offer_original_price) ? (float) $product->offer_original_price : $unit;
    if (!empty($product->active_offer['original_price'])) {
        $originalUnit = (float) $product->active_offer['original_price'];
    }
    $offer = !empty($product->active_offer) ? $product->active_offer : null;
    $type = $offer ? $offer['type'] : '';

    $gross = round($originalUnit * $qty, 2);
    $line = round($unit * $qty, 2);
    $discount = max(0, round($gross - $line, 2));

    if ($offer && $type === 'buy_x_get_y') {
        $buy = max(1, (int) $offer['buy_qty']);
        $get = max(1, (int) $offer['get_qty']);
        $cycle = $buy + $get;
        $free = (int) floor($qty / $cycle) * $get;
        $chargeable = max(0, $qty - $free);
        $line = round($unit * $chargeable, 2);
        $gross = round($unit * $qty, 2);
        $discount = max(0, round($gross - $line, 2));
        $originalUnit = $unit;
    } elseif ($offer && $type === 'bundle') {
        $tiers = array();
        $bQty = (int) $offer['bundle_qty'];
        $bPrice = (float) $offer['bundle_price'];
        if ($bQty >= 2 && $bPrice > 0) {
            $tiers[] = array($bQty, $bPrice);
        }
        $b2Qty = isset($offer['bundle2_qty']) ? (int) $offer['bundle2_qty'] : 0;
        $b2Price = isset($offer['bundle2_price']) ? (float) $offer['bundle2_price'] : 0;
        if ($b2Qty >= 2 && $b2Price > 0) {
            $tiers[] = array($b2Qty, $b2Price);
        }
        if ($tiers) {
            $line = offer_bundle_best_line($unit, $qty, $tiers);
            $gross = round($unit * $qty, 2);
            if ($line >= $gross) {
                $line = $gross;
                $discount = 0;
            } else {
                $discount = max(0, round($gross - $line, 2));
            }
            $originalUnit = $unit;
        }
    }

    return array(
        'qty' => $qty,
        'unit_price' => $unit,
        'original_unit' => $originalUnit,
        'line_total' => $line,
        'gross_total' => $gross,
        'discount' => $discount,
        'effective_unit' => $qty > 0 ? round($line / $qty, 2) : $unit,
    );
}

function offer_payload_for_js($product)
{
    if (empty($product->active_offer)) {
        return null;
    }
    $o = $product->active_offer;
    return array(
        'type' => $o['type'],
        'value' => (float) $o['value'],
        'buy_qty' => (int) $o['buy_qty'],
        'get_qty' => (int) $o['get_qty'],
        'bundle_qty' => (int) $o['bundle_qty'],
        'bundle_price' => (float) $o['bundle_price'],
        'bundle_price_formatted' => ((float) $o['bundle_price'] > 0) ? format_money((float) $o['bundle_price']) : '',
        'bundle2_qty' => isset($o['bundle2_qty']) ? (int) $o['bundle2_qty'] : 0,
        'bundle2_price' => isset($o['bundle2_price']) ? (float) $o['bundle2_price'] : 0,
        'bundle2_price_formatted' => (!empty($o['bundle2_price']) && (float) $o['bundle2_price'] > 0)
            ? format_money((float) $o['bundle2_price']) : '',
        'label' => $o['label'],
        'badge' => $o['badge'],
        'show_badge' => !empty($o['show_badge']),
        'show_countdown' => !empty($o['show_countdown']),
        'free_shipping' => !empty($o['free_shipping']),
        'free_shipping_min_qty' => ($o['type'] === 'bundle')
            ? offer_bundle_min_qty($o)
            : (($o['type'] === 'buy_x_get_y')
                ? (max(1, (int) $o['buy_qty']) + max(1, (int) $o['get_qty']))
                : 1),
        'cards' => function_exists('offer_pdp_cards')
            ? offer_pdp_cards($o, !empty($o['original_price']) ? (float) $o['original_price'] : (float) $product->price)
            : array(),
        'ends_at' => $o['ends_at'],
        'ends_at_ts' => (int) $o['ends_at_ts'],
        'original_price' => (float) $o['original_price'],
        'original_formatted' => format_money((float) $o['original_price']),
        'final_price' => (float) $o['final_price'],
        'final_formatted' => format_money((float) $o['final_price']),
        'save_amount' => (float) $o['save_amount'],
        'save_formatted' => ((float) $o['save_amount'] > 0) ? format_money((float) $o['save_amount']) : '',
        'percent' => !empty($product->display_discount_percent) ? (int) $product->display_discount_percent : product_sale_off_percent($product),
    );
}

function offer_sanitize_admin_payload($post)
{
    $enabled = !empty($post['offer_enabled']) ? 1 : 0;
    $type = offer_normalize_type(isset($post['offer_type']) ? $post['offer_type'] : 'percent');
    $value = isset($post['offer_value']) ? (float) $post['offer_value'] : 0;
    if ($type === 'percent') {
        $value = max(0, min(95, $value));
    } else {
        $value = max(0, $value);
    }
    $starts = isset($post['offer_starts_at']) ? trim((string) $post['offer_starts_at']) : '';
    $ends = isset($post['offer_ends_at']) ? trim((string) $post['offer_ends_at']) : '';
    if ($starts !== '') {
        $starts = date('Y-m-d H:i:s', strtotime($starts));
    } else {
        $starts = null;
    }
    if ($ends !== '') {
        $ends = date('Y-m-d H:i:s', strtotime($ends));
    } else {
        $ends = null;
    }
    return array(
        'offer_enabled' => $enabled,
        'offer_type' => $type,
        'offer_value' => $value,
        'offer_buy_qty' => max(0, (int) (isset($post['offer_buy_qty']) ? $post['offer_buy_qty'] : 0)),
        'offer_get_qty' => max(0, (int) (isset($post['offer_get_qty']) ? $post['offer_get_qty'] : 0)),
        'offer_bundle_qty' => max(0, (int) (isset($post['offer_bundle_qty']) ? $post['offer_bundle_qty'] : 0)),
        'offer_bundle_price' => max(0, (float) (isset($post['offer_bundle_price']) ? $post['offer_bundle_price'] : 0)),
        'offer_bundle2_qty' => max(0, (int) (isset($post['offer_bundle2_qty']) ? $post['offer_bundle2_qty'] : 0)),
        'offer_bundle2_price' => max(0, (float) (isset($post['offer_bundle2_price']) ? $post['offer_bundle2_price'] : 0)),
        'offer_label' => mb_substr(trim((string) (isset($post['offer_label']) ? $post['offer_label'] : '')), 0, 120),
        'offer_starts_at' => $starts,
        'offer_ends_at' => $ends,
        'offer_priority' => (int) (isset($post['offer_priority']) ? $post['offer_priority'] : 0),
        'offer_show_badge' => !empty($post['offer_show_badge']) ? 1 : 0,
        'offer_show_countdown' => !empty($post['offer_show_countdown']) ? 1 : 0,
        'offer_free_shipping' => !empty($post['offer_free_shipping']) ? 1 : 0,
    );
}

function offer_sync_fields()
{
    return array(
        'offer_enabled', 'offer_type', 'offer_value', 'offer_buy_qty', 'offer_get_qty',
        'offer_bundle_qty', 'offer_bundle_price', 'offer_bundle2_qty', 'offer_bundle2_price',
        'offer_label', 'offer_starts_at',
        'offer_ends_at', 'offer_priority', 'offer_show_badge', 'offer_show_countdown',
        'offer_free_shipping',
    );
}

/**
 * True when a cart line qualifies for free shipping via its active offer.
 * Bundle / Buy X Get Y: only after the offer qty is met (not on a single unit).
 * Percent / fixed: anytime the offer is active on the line.
 */
function product_offer_has_free_shipping($product, $qty = null)
{
    if (!$product) {
        return false;
    }
    // Product-level free shipping (always, any qty).
    if (!empty($product->free_shipping)) {
        return true;
    }
    if (empty($product->active_offer)) {
        return false;
    }
    $offer = $product->active_offer;
    $hasFree = !empty($offer['free_shipping']) || !empty($product->offer_free_shipping);
    if (!$hasFree) {
        return false;
    }

    $type = isset($offer['type']) ? $offer['type'] : '';
    $lineQty = $qty !== null
        ? max(0, (int) $qty)
        : (isset($product->qty) ? max(0, (int) $product->qty) : 0);

    if ($type === 'bundle') {
        $min = offer_bundle_min_qty($offer);
        return $min >= 2 && $lineQty >= $min;
    }
    if ($type === 'buy_x_get_y') {
        $cycle = max(1, (int) $offer['buy_qty']) + max(1, (int) $offer['get_qty']);
        return $lineQty >= $cycle;
    }
    // percent / fixed: offer already applied at unit level
    return true;
}

/** Product or offer grants free shipping (for PDP badges / ETA). */
function product_has_free_shipping($product)
{
    if (!$product) {
        return false;
    }
    if (!empty($product->free_shipping)) {
        return true;
    }
    return product_offer_has_free_shipping($product, isset($product->qty) ? (int) $product->qty : 1);
}

/** Smallest bundle tier qty (for free-shipping / UI thresholds). */
function offer_bundle_min_qty($offer)
{
    if (!$offer) {
        return 0;
    }
    $min = 0;
    $q1 = isset($offer['bundle_qty']) ? (int) $offer['bundle_qty'] : 0;
    if ($q1 >= 2 && !empty($offer['bundle_price']) && (float) $offer['bundle_price'] > 0) {
        $min = $q1;
    }
    $q2 = isset($offer['bundle2_qty']) ? (int) $offer['bundle2_qty'] : 0;
    if ($q2 >= 2 && !empty($offer['bundle2_price']) && (float) $offer['bundle2_price'] > 0) {
        $min = $min > 0 ? min($min, $q2) : $q2;
    }
    return $min;
}

/**
 * PDP clickable offer cards (one per bundle / deal tier).
 *
 * @return array list of cards with qty, deal, hero, save, free_shipping
 */
function offer_pdp_cards($offer, $unitPrice = 0)
{
    if (!$offer || empty($offer['type'])) {
        return array();
    }
    $type = $offer['type'];
    $unit = (float) $unitPrice;
    if ($unit <= 0 && !empty($offer['original_price'])) {
        $unit = (float) $offer['original_price'];
    }
    if ($unit <= 0 && !empty($offer['final_price'])) {
        $unit = (float) $offer['final_price'];
    }
    $freeShip = !empty($offer['free_shipping']);
    $cards = array();

    if ($type === 'bundle') {
        $tiers = array();
        $q1 = isset($offer['bundle_qty']) ? (int) $offer['bundle_qty'] : 0;
        $p1 = isset($offer['bundle_price']) ? (float) $offer['bundle_price'] : 0;
        if ($q1 >= 2 && $p1 > 0) {
            $tiers[] = array($q1, $p1);
        }
        $q2 = isset($offer['bundle2_qty']) ? (int) $offer['bundle2_qty'] : 0;
        $p2 = isset($offer['bundle2_price']) ? (float) $offer['bundle2_price'] : 0;
        if ($q2 >= 2 && $p2 > 0) {
            $tiers[] = array($q2, $p2);
        }
        usort($tiers, function ($a, $b) {
            return $a[0] - $b[0];
        });
        foreach ($tiers as $tier) {
            $qty = (int) $tier[0];
            $price = (float) $tier[1];
            $gross = round($unit * $qty, 2);
            $save = max(0, round($gross - $price, 2));
            $payUnits = $unit > 0 ? round($price / $unit, 2) : 0;
            $isBogo = ($qty >= 3 && abs($payUnits - ($qty - 1)) < 0.05);
            if ($isBogo) {
                $deal = function_exists('e_ui')
                    ? e_ui('product.offer_card_bogo', array('{qty}' => $qty))
                    : ('Buy ' . $qty . ' get 1 free');
                $hero = (string) $qty;
                $heroSub = function_exists('e_ui') ? e_ui('product.offer_card_bogo_short') : 'FREE';
            } else {
                $deal = function_exists('e_ui')
                    ? e_ui('product.offer_card_bundle', array('{qty}' => $qty, '{amount}' => format_money($price)))
                    : ('Buy ' . $qty . ' for ' . format_money($price));
                $hero = (string) $qty;
                $heroSub = function_exists('e_ui') ? e_ui('product.offer_card_bundle_short') : 'PACK';
            }
            $cards[] = array(
                'qty' => $qty,
                'deal' => $deal,
                'hero' => $hero,
                'hero_sub' => $heroSub,
                'save' => $save,
                'save_formatted' => $save > 0 ? format_money($save) : '',
                'price' => $price,
                'price_formatted' => format_money($price),
                'free_shipping' => $freeShip,
                'type' => 'bundle',
            );
        }
        return $cards;
    }

    if ($type === 'buy_x_get_y') {
        $buy = max(1, (int) $offer['buy_qty']);
        $get = max(1, (int) $offer['get_qty']);
        $qty = $buy + $get;
        $save = round($unit * $get, 2);
        $cards[] = array(
            'qty' => $qty,
            'deal' => function_exists('e_ui')
                ? e_ui('product.offer_card_bxgy', array('{buy}' => $buy, '{get}' => $get))
                : ('Buy ' . $buy . ' get ' . $get),
            'hero' => $buy . '+' . $get,
            'hero_sub' => function_exists('e_ui') ? e_ui('product.offer_card_bxgy_short') : 'DEAL',
            'save' => $save,
            'save_formatted' => $save > 0 ? format_money($save) : '',
            'price' => round($unit * $buy, 2),
            'price_formatted' => format_money(round($unit * $buy, 2)),
            'free_shipping' => $freeShip,
            'type' => 'buy_x_get_y',
        );
        return $cards;
    }

    // percent / fixed — single informational card (still claimable as qty 1 buy-now)
    $qty = 1;
    $deal = !empty($offer['label']) ? (string) $offer['label'] : '';
    $hero = '';
    $heroSub = '';
    $save = !empty($offer['save_amount']) ? (float) $offer['save_amount'] : 0;
    if ($type === 'percent') {
        $pct = !empty($offer['value']) ? (int) round((float) $offer['value']) : 0;
        if ($pct < 1 && $save > 0 && $unit > 0) {
            $pct = (int) round(($save / $unit) * 100);
        }
        $pct = max(1, $pct);
        $deal = function_exists('e_ui') ? e_ui('product.offer_card_off', array('{n}' => $pct)) : ($pct . '% OFF');
        $hero = $pct . '%';
        $heroSub = function_exists('e_ui') ? e_ui('product.offer_card_off_short') : 'OFF';
    } elseif ($type === 'fixed') {
        $deal = function_exists('e_ui')
            ? e_ui('product.offer_card_save', array('{amount}' => format_money((float) $offer['value'])))
            : ('Save ' . format_money((float) $offer['value']));
        $hero = '−' . format_money((float) $offer['value']);
        $heroSub = function_exists('e_ui') ? e_ui('product.offer_card_save_short') : 'SAVE';
    }
    $final = !empty($offer['final_price']) ? (float) $offer['final_price'] : $unit;
    $cards[] = array(
        'qty' => $qty,
        'deal' => $deal,
        'hero' => $hero,
        'hero_sub' => $heroSub,
        'save' => $save,
        'save_formatted' => $save > 0 ? format_money($save) : '',
        'price' => $final,
        'price_formatted' => format_money($final),
        'free_shipping' => $freeShip,
        'type' => $type,
    );
    return $cards;
}
