<?php
defined('BASEPATH') OR exit('No direct script access allowed');

if (!defined('ROLE_ADMIN')) {
    define('ROLE_ADMIN', 1);
}
if (!defined('ROLE_ECOMMERCE')) {
    define('ROLE_ECOMMERCE', 5);
}

function ec_user()
{
    return isset($_SESSION['knet_login']) ? $_SESSION['knet_login'] : null;
}

function ec_is_admin()
{
    $user = ec_user();
    return $user && (int) $user->roleID === ROLE_ADMIN;
}

function ec_is_ecommerce()
{
    $user = ec_user();
    return $user && (int) $user->roleID === ROLE_ECOMMERCE;
}

function ec_require_login()
{
    if (!ec_user()) {
        redirect('/login');
        exit;
    }
}

function ec_require_admin()
{
    ec_require_login();
    if (!ec_is_admin()) {
        redirect('/admin/products');
        exit;
    }
}

function ec_require_products()
{
    ec_require_login();
    if (!ec_is_admin() && !ec_is_ecommerce()) {
        redirect('/login');
        exit;
    }
}

function ec_display_name($user = null)
{
    $user = $user ?: ec_user();
    if (!$user) {
        return 'Guest';
    }
    $name = trim(($user->first_name ?? '') . ' ' . ($user->last_name ?? ''));
    return $name !== '' ? $name : ($user->uname ?? 'User');
}

function ec_role_label($roleID)
{
    if ((int) $roleID === ROLE_ADMIN) {
        return 'Super Admin';
    }
    if ((int) $roleID === ROLE_ECOMMERCE) {
        return 'Ecommerce';
    }
    return 'User';
}

function ec_status_label($status)
{
    return ((int) $status === 1) ? 'Active' : 'Inactive';
}

function theme_setting($settings, $key, $default = '')
{
    return (isset($settings[$key]) && $settings[$key] !== '') ? $settings[$key] : $default;
}

function setting_flag_on($settings, $key)
{
    $value = theme_setting($settings, $key, '');
    if (is_bool($value)) {
        return $value;
    }
    return in_array(strtolower(trim((string) $value)), array('1', 'true', 'on', 'yes'), true);
}

function store_setting_row_pair($row)
{
    if (is_object($row)) {
        $row = (array) $row;
    }
    if (!is_array($row)) {
        return null;
    }
    $key = '';
    $value = '';
    if (!empty($row['field_key'])) {
        $key = $row['field_key'];
        $value = isset($row['field_value']) ? $row['field_value'] : '';
    } elseif (!empty($row['setting_key'])) {
        $key = $row['setting_key'];
        $value = isset($row['setting_value']) ? $row['setting_value'] : '';
    }
    if ($key === '') {
        return null;
    }
    return array($key, $value);
}

function category_store_name($category)
{
    if (!is_object($category)) {
        return '';
    }
    $local = isset($category->local_name) ? trim((string) $category->local_name) : '';
    if ($local !== '') {
        return $local;
    }
    if (!empty($category->display_name)) {
        return trim((string) $category->display_name);
    }
    return isset($category->name) ? trim((string) $category->name) : '';
}

function storefront_url($path = '')
{
    $url = site_url($path);
    $hint = isset($_GET['domain']) ? $_GET['domain'] : '';
    if ($hint !== '') {
        $url .= (strpos($url, '?') === false ? '?' : '&') . 'domain=' . rawurlencode($hint);
    }
    return $url;
}

function product_url($product)
{
    $slug = '';
    $id = 0;
    if (is_object($product)) {
        $slug = isset($product->slug) ? trim((string) $product->slug) : '';
        $id = isset($product->id) ? (int) $product->id : 0;
    } else {
        $id = (int) $product;
    }
    if ($slug !== '') {
        return storefront_url('product/' . rawurlencode($slug));
    }
    return $id > 0 ? storefront_url('product/' . $id) : storefront_url('shop');
}

function storefront_asset_url($path = '')
{
    $path = trim((string) $path);
    if ($path === '') {
        return '';
    }
    if (preg_match('#^https?://#i', $path) || strpos($path, '//') === 0) {
        return $path;
    }
    $full = base_url(ltrim($path, '/'));
    $parts = parse_url($full);
    $rel = (isset($parts['path']) && $parts['path'] !== '') ? $parts['path'] : '/' . ltrim($path, '/');
    if ($rel[0] !== '/') {
        $rel = '/' . $rel;
    }
    if (!empty($parts['query'])) {
        $rel .= '?' . $parts['query'];
    }
    return $rel;
}

function product_image_url($path, $fallback = '')
{
    $path = trim((string) $path);
    if ($path === '') {
        return $fallback;
    }
    return storefront_asset_url($path);
}

function product_gallery_urls($product, $images = array())
{
    $urls = array();
    $seen = array();
    $add = function ($path) use (&$urls, &$seen) {
        $url = product_image_url($path);
        if ($url === '' || isset($seen[$url])) {
            return;
        }
        $seen[$url] = true;
        $urls[] = $url;
    };

    if (is_object($product) && !empty($product->image)) {
        $add($product->image);
    }
    foreach ((array) $images as $row) {
        if (is_object($row) && !empty($row->image)) {
            $add($row->image);
        } elseif (is_array($row) && !empty($row['image'])) {
            $add($row['image']);
        } elseif (is_string($row)) {
            $add($row);
        }
    }
    return $urls;
}

function ensure_product_parent_columns()
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
    if (!$CI->db->field_exists('parent_sku', 'products')) {
        $CI->db->query("ALTER TABLE products ADD COLUMN parent_sku VARCHAR(100) NOT NULL DEFAULT '' AFTER sku");
    }
    if (!$CI->db->field_exists('is_default', 'products')) {
        $CI->db->query("ALTER TABLE products ADD COLUMN is_default TINYINT(1) NOT NULL DEFAULT 0 AFTER parent_sku");
    }
    if (!$CI->db->table_exists('product_images')) {
        $CI->db->query("CREATE TABLE IF NOT EXISTS product_images (
            id INT(11) NOT NULL AUTO_INCREMENT,
            product_id INT(11) NOT NULL,
            image VARCHAR(255) NOT NULL DEFAULT '',
            sort_order INT(11) NOT NULL DEFAULT 0,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY product_id (product_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    }
}

function product_family($product, $activeOnly = true)
{
    $empty = array(
        'parent' => $product,
        'selected' => $product,
        'children' => array(),
    );
    if (!$product) {
        return $empty;
    }
    ensure_product_parent_columns();
    $CI =& get_instance();
    $storeId = !empty($product->store_id) ? (int) $product->store_id : 0;
    $parentSku = isset($product->parent_sku) ? trim((string) $product->parent_sku) : '';
    $parent = $product;
    $groupSku = isset($product->sku) ? trim((string) $product->sku) : '';

    if ($parentSku !== '') {
        $CI->db->from('products');
        product_scope_store($storeId);
        $CI->db->where('products.sku', $parentSku);
        if ($activeOnly) {
            $CI->db->where('products.status', 1);
        }
        $found = $CI->db->get()->row();
        if ($found) {
            $parent = $found;
            $groupSku = trim((string) $found->sku);
        } else {
            $groupSku = $parentSku;
        }
    }

    $children = array();
    if ($groupSku !== '') {
        $CI->db->from('products');
        product_scope_store($storeId);
        $CI->db->where('products.parent_sku', $groupSku);
        if ($activeOnly) {
            $CI->db->where('products.status', 1);
        }
        $CI->db->order_by('products.is_default', 'desc');
        $CI->db->order_by('products.id', 'asc');
        $children = $CI->db->get()->result();
    }

    $selected = $product;
    if ($parentSku === '' && !empty($children)) {
        $selected = $children[0];
        foreach ($children as $child) {
            if (!empty($child->is_default)) {
                $selected = $child;
                break;
            }
        }
    }

    return array(
        'parent' => $parent,
        'selected' => $selected,
        'children' => $children,
    );
}

function product_scope_store($storeId)
{
    $CI =& get_instance();
    $storeId = (int) $storeId;
    if ($storeId > 0) {
        $CI->db->where('products.store_id', $storeId);
        return;
    }
    $CI->db->group_start()
        ->where('products.store_id IS NULL', null, false)
        ->or_where('products.store_id', 0)
        ->group_end();
}

function product_sync_default_child($productId)
{
    ensure_product_parent_columns();
    $CI =& get_instance();
    $product = $CI->db->where('id', (int) $productId)->get('products')->row();
    if (!$product) {
        return;
    }
    $parentSku = isset($product->parent_sku) ? trim((string) $product->parent_sku) : '';
    if ($parentSku === '') {
        if (!empty($product->is_default)) {
            $CI->db->where('id', (int) $product->id)->update('products', array('is_default' => 0));
        }
        return;
    }
    if (empty($product->is_default)) {
        return;
    }
    $storeId = !empty($product->store_id) ? (int) $product->store_id : 0;
    if ($storeId > 0) {
        $CI->db->where('store_id', $storeId);
    } else {
        $CI->db->group_start()
            ->where('store_id IS NULL', null, false)
            ->or_where('store_id', 0)
            ->group_end();
    }
    $CI->db->where('parent_sku', $parentSku);
    $CI->db->where('id !=', (int) $product->id);
    $CI->db->update('products', array('is_default' => 0));
}

function product_option_url($child, $isPreview = false, $themeSlug = '')
{
    if ($isPreview && $themeSlug !== '') {
        return base_url('admin/products/preview/' . (int) $child->id . '/' . rawurlencode($themeSlug));
    }
    return product_url($child);
}

function sanitize_custom_css($css)
{
    $css = (string) $css;
    $css = str_replace(array('<', '>', "\0"), '', $css);
    $css = preg_replace('/expression\s*\(/i', '', $css);
    $css = preg_replace('/javascript\s*:/i', '', $css);
    $css = preg_replace('/@import/i', '', $css);
    $css = preg_replace('/behavior\s*:/i', '', $css);
    return trim($css);
}

function store_custom_css($store)
{
    if (!$store || empty($store->custom_css)) {
        return '';
    }
    return sanitize_custom_css($store->custom_css);
}

function storefront_customer()
{
    if (empty($_SESSION['storefront_customer']) || !is_array($_SESSION['storefront_customer'])) {
        return null;
    }
    return (object) $_SESSION['storefront_customer'];
}

function storefront_cart($storeId)
{
    if (empty($_SESSION['storefront_cart'][$storeId]) || !is_array($_SESSION['storefront_cart'][$storeId])) {
        return array();
    }
    return $_SESSION['storefront_cart'][$storeId];
}

function storefront_cart_count($storeId)
{
    return (int) array_sum(storefront_cart($storeId));
}

function theme_screenshot($slug)
{
    $paths = array(
        'assets/frontend/' . $slug . '/screenshot.jpg',
        'application/views/frontend/' . $slug . '/screenshot.jpg',
    );
    foreach ($paths as $relative) {
        if (is_file(FCPATH . $relative)) {
            return base_url($relative);
        }
    }
    return base_url('assets/inspinia/img/profile_small.jpg');
}

function platform_setting($key, $default = '0')
{
    $CI =& get_instance();
    if (!$CI->db->table_exists('platform_settings')) {
        return $default;
    }
    $row = $CI->db->where('setting_key', $key)->get('platform_settings')->row();
    return ($row && $row->setting_value !== '') ? $row->setting_value : $default;
}

function ec_ensure_currency_schema()
{
    static $done = false;
    if ($done) {
        return;
    }
    $done = true;
    $CI =& get_instance();
    if (!$CI->db->table_exists('currency_rates')) {
        $CI->db->query("CREATE TABLE IF NOT EXISTS `currency_rates` (
            `currency` varchar(10) NOT NULL,
            `rate_to_platform` decimal(18,8) NOT NULL DEFAULT 1.00000000,
            PRIMARY KEY (`currency`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    }
    if ($CI->db->table_exists('stores') && !$CI->db->field_exists('currency', 'stores')) {
        $CI->db->query("ALTER TABLE stores ADD COLUMN currency VARCHAR(10) NOT NULL DEFAULT '' AFTER country_id");
    }
    if ($CI->db->table_exists('store_orders')) {
        if (!$CI->db->field_exists('fx_rate', 'store_orders')) {
            $CI->db->query("ALTER TABLE store_orders ADD COLUMN fx_rate DECIMAL(18,8) NOT NULL DEFAULT 1 AFTER currency");
        }
        if (!$CI->db->field_exists('platform_fee_platform', 'store_orders')) {
            $CI->db->query("ALTER TABLE store_orders ADD COLUMN platform_fee_platform DECIMAL(12,2) NOT NULL DEFAULT 0 AFTER platform_fee_total");
        }
        if (!$CI->db->field_exists('commission_platform', 'store_orders')) {
            $CI->db->query("ALTER TABLE store_orders ADD COLUMN commission_platform DECIMAL(12,2) NOT NULL DEFAULT 0 AFTER commission_total");
        }
    }

    if ($CI->db->table_exists('platform_settings')) {
        $hasCountry = $CI->db->where('setting_key', 'platform_country_id')->get('platform_settings')->row();
        if (!$hasCountry) {
            $CI->db->insert('platform_settings', array(
                'setting_key' => 'platform_country_id',
                'setting_value' => '1',
            ));
        }
    }

    $defaults = array(
        'PKR' => '1.00000000',
        'USD' => '278.00000000',
        'GBP' => '370.00000000',
        'AED' => '76.00000000',
        'SAR' => '74.00000000',
        'EUR' => '305.00000000',
        'INR' => '3.35000000',
        'SEK' => '26.50000000',
        'AUD' => '185.00000000',
    );
    foreach ($defaults as $code => $rate) {
        $exists = $CI->db->where('currency', $code)->get('currency_rates')->row();
        if (!$exists) {
            $CI->db->insert('currency_rates', array(
                'currency' => $code,
                'rate_to_platform' => $rate,
            ));
        }
    }
}

function country_currency($countryId)
{
    static $cache = array();
    $id = (int) $countryId;
    if ($id <= 0) {
        return '';
    }
    if (!isset($cache[$id])) {
        $CI =& get_instance();
        if (!$CI->db->table_exists('countries')) {
            $cache[$id] = '';
        } else {
            $row = $CI->db->select('currency')->where('id', $id)->get('countries')->row();
            $cache[$id] = $row ? strtoupper(trim($row->currency)) : '';
        }
    }
    return $cache[$id];
}

function platform_country_id()
{
    $id = (int) platform_setting('platform_country_id', 0);
    if ($id > 0) {
        return $id;
    }
    $CI =& get_instance();
    if ($CI->db->table_exists('countries')) {
        $row = $CI->db->where('status', 1)->order_by('id', 'asc')->get('countries')->row();
        if ($row) {
            return (int) $row->id;
        }
    }
    return 0;
}

function platform_currency()
{
    $code = country_currency(platform_country_id());
    return $code !== '' ? $code : 'PKR';
}

function store_currency($store = null)
{
    if ($store === null) {
        $CI =& get_instance();
        if (!empty($CI->store)) {
            $store = $CI->store;
        } elseif (isset($CI->tenant)) {
            $store = $CI->tenant->get_store();
        }
    }
    if (is_numeric($store)) {
        static $byId = array();
        $id = (int) $store;
        if (!isset($byId[$id])) {
            $CI =& get_instance();
            $row = $CI->db
                ->select('stores.id, stores.country_id, stores.currency, countries.currency as country_currency')
                ->from('stores')
                ->join('countries', 'countries.id = stores.country_id', 'left')
                ->where('stores.id', $id)
                ->get()
                ->row();
            $byId[$id] = $row ? store_currency($row) : platform_currency();
        }
        return $byId[$id];
    }
    if (is_object($store)) {
        if (!empty($store->country_currency)) {
            return strtoupper(trim($store->country_currency));
        }
        if (!empty($store->country_id)) {
            $code = country_currency($store->country_id);
            if ($code !== '') {
                return $code;
            }
        }
        if (!empty($store->currency)) {
            return strtoupper(trim($store->currency));
        }
    }
    return platform_currency();
}

function product_currency($product)
{
    if (is_object($product) && !empty($product->country_currency)) {
        return strtoupper(trim($product->country_currency));
    }
    if (is_object($product) && !empty($product->country_id)) {
        $code = country_currency($product->country_id);
        if ($code !== '') {
            return $code;
        }
    }
    return platform_currency();
}

function hydrate_store_currency($store)
{
    if ($store) {
        $store->currency = store_currency($store);
    }
    return $store;
}

function money_currency($currency = null)
{
    if ($currency !== null && $currency !== '') {
        return strtoupper(trim((string) $currency));
    }
    return store_currency();
}

function format_money($amount, $currency = null)
{
    return money_currency($currency) . ' ' . number_format((float) $amount, 2);
}

function currency_rate_to_platform($currency)
{
    $currency = strtoupper(trim((string) $currency));
    $platform = platform_currency();
    if ($currency === '' || $currency === $platform) {
        return 1.0;
    }
    ec_ensure_currency_schema();
    static $cache = array();
    $key = $platform . ':' . $currency;
    if (!isset($cache[$key])) {
        $CI =& get_instance();
        $row = $CI->db->where('currency', $currency)->get('currency_rates')->row();
        $rate = $row ? (float) $row->rate_to_platform : 1.0;
        $cache[$key] = $rate > 0 ? $rate : 1.0;
    }
    return $cache[$key];
}

function convert_money($amount, $from, $to)
{
    $from = strtoupper(trim((string) $from));
    $to = strtoupper(trim((string) $to));
    $amount = (float) $amount;
    if ($from === $to || $amount == 0.0) {
        return round($amount, 2);
    }
    $fromRate = currency_rate_to_platform($from);
    $toRate = currency_rate_to_platform($to);
    if ($toRate <= 0) {
        $toRate = 1.0;
    }
    return round(($amount * $fromRate) / $toRate, 2);
}

function platform_fee_percent()
{
    return max(0, (float) platform_setting('platform_fee', 0));
}

function product_platform_fee($storeId = 0, $product = null)
{
    $percent = platform_fee_percent();
    if ($percent <= 0) {
        return 0.0;
    }
    $base = product_platform_fee_base($product);
    return round($base * ($percent / 100), 2);
}

function product_platform_fee_base($product)
{
    return product_commission_base($product);
}

function ec_refresh_store_copy_costs($storeId = 0, $ownerUserId = 0)
{
    $CI =& get_instance();
    if (!$CI->db->table_exists('products')) {
        return 0;
    }
    ensure_user_commission_schema();
    product_owner_commission_reset_cache();

    $catalogIds = array();
    if ($ownerUserId) {
        $catalogRows = $CI->db
            ->select('id')
            ->from('products')
            ->where('created_by', (int) $ownerUserId)
            ->group_start()
                ->where('store_id IS NULL', null, false)
                ->or_where('store_id', 0)
            ->group_end()
            ->get()
            ->result();
        foreach ($catalogRows as $row) {
            $catalogIds[] = (int) $row->id;
        }
    }

    $CI->db
        ->where('store_id IS NOT NULL', null, false)
        ->where('store_id !=', 0)
        ->where('source_product_id IS NOT NULL', null, false)
        ->where('source_product_id !=', 0);
    if ($storeId) {
        $CI->db->where('store_id', (int) $storeId);
    }
    if ($ownerUserId) {
        $CI->db->group_start();
        if ($catalogIds) {
            $CI->db->where_in('source_product_id', $catalogIds);
            $CI->db->or_where('created_by', (int) $ownerUserId);
        } else {
            $CI->db->where('created_by', (int) $ownerUserId);
        }
        $CI->db->group_end();
    }
    $copies = $CI->db->get('products')->result();
    if (!$copies) {
        return 0;
    }

    $sourceIds = array();
    $storeIds = array();
    foreach ($copies as $copy) {
        $sourceIds[(int) $copy->source_product_id] = true;
        $storeIds[(int) $copy->store_id] = true;
    }

    $sources = array();
    $ids = array_keys($sourceIds);
    foreach (array_chunk($ids, 500) as $chunk) {
        $rows = $CI->db
            ->select('products.*, users.commission as owner_commission, users.commission_percent as owner_commission_percent', false)
            ->from('products')
            ->join('users', 'users.UserID = products.created_by', 'left')
            ->where_in('products.id', $chunk)
            ->get()
            ->result();
        foreach ($rows as $row) {
            $sources[(int) $row->id] = $row;
        }
    }

    $plusByStore = array();
    foreach (array_keys($storeIds) as $sid) {
        $plusByStore[(int) $sid] = store_price_plus_amount((int) $sid);
    }

    $updated = 0;
    foreach ($copies as $copy) {
        $source = isset($sources[(int) $copy->source_product_id]) ? $sources[(int) $copy->source_product_id] : null;
        if (!$source) {
            continue;
        }
        $wholesale = product_wholesale_price($source, (int) $copy->store_id);
        $plus = isset($plusByStore[(int) $copy->store_id]) ? $plusByStore[(int) $copy->store_id] : 0.0;
        $payload = ec_store_copy_price_payload($copy, $wholesale, $plus);
        if (!$payload) {
            continue;
        }
        $CI->db->where('id', (int) $copy->id)->update('products', $payload);
        $updated++;
    }
    return $updated;
}

function ec_store_copy_price_payload($copy, $wholesale, $plus = 0.0)
{
    $oldCost = (float) $copy->cost_price;
    $oldPrice = (float) $copy->price;
    $payload = array('cost_price' => round((float) $wholesale, 2));
    $wholesale = (float) $payload['cost_price'];
    if ($oldCost > 0) {
        $newPrice = round($oldPrice + ($wholesale - $oldCost), 2);
    } else {
        $newPrice = round($wholesale + max(0, (float) $plus), 2);
    }
    if ($newPrice < $wholesale) {
        $newPrice = $wholesale;
    }
    if (abs($newPrice - $oldPrice) > 0.001) {
        $payload['price'] = $newPrice;
    }
    if (abs($wholesale - $oldCost) < 0.001 && !isset($payload['price'])) {
        return null;
    }
    return $payload;
}

function order_amount_in_platform($order, $kind = 'platform_fee')
{
    $platformField = $kind === 'commission' ? 'commission_platform' : 'platform_fee_platform';
    $storeField = $kind === 'commission' ? 'commission_total' : 'platform_fee_total';
    $storeAmount = (is_object($order) && isset($order->$storeField)) ? (float) $order->$storeField : 0.0;
    $stored = (is_object($order) && isset($order->$platformField)) ? (float) $order->$platformField : 0.0;
    if ($stored > 0) {
        return round($stored, 2);
    }
    $from = (is_object($order) && !empty($order->currency)) ? $order->currency : platform_currency();
    return convert_money($storeAmount, $from, platform_currency());
}

/**
 * Read a flash message once, then force-remove it from the session.
 */
function ec_take_flash($key)
{
    $CI =& get_instance();
    $value = $CI->session->flashdata($key);
    $CI->session->unset_userdata($key);
    if (isset($_SESSION['__ci_vars'][$key])) {
        unset($_SESSION['__ci_vars'][$key]);
    }
    if (isset($_SESSION[$key])) {
        unset($_SESSION[$key]);
    }
    return $value;
}

function product_base_price($product)
{
    if (empty($product->store_id) && isset($product->cost_price) && (float) $product->cost_price > 0) {
        return (float) $product->cost_price;
    }
    return (float) (isset($product->base_price) ? $product->base_price : $product->price);
}

function product_pricing_breakdown($product, $storeId = 0)
{
    $platformFee = product_platform_fee($storeId, $product);
    $commission = product_commission_amount($product);
    $cost = 0.0;
    if (isset($product->source_cost) && $product->source_cost !== null && $product->source_cost !== '') {
        $cost = (float) $product->source_cost;
    } elseif (empty($product->store_id) && isset($product->cost_price) && (float) $product->cost_price > 0) {
        $cost = (float) $product->cost_price;
    } else {
        $cost = product_base_price($product);
    }
    $yourCost = round($cost + $commission + $platformFee, 2);
    if (!empty($product->store_id) && (!isset($product->source_cost) || $product->source_cost === null || $product->source_cost === '')) {
        $yourCost = (float) (isset($product->cost_price) && (float) $product->cost_price > 0 ? $product->cost_price : $product->price);
        $cost = max(0, round($yourCost - $commission - $platformFee, 2));
    }
    return array(
        'cost' => round($cost, 2),
        'ecommerce_commission' => round($commission, 2),
        'platform_fee' => round($platformFee, 2),
        'your_cost' => $yourCost,
    );
}

function ensure_user_commission_schema()
{
    static $done = false;
    if ($done) {
        return;
    }
    $done = true;
    $CI =& get_instance();
    if (!$CI->db->table_exists('users')) {
        return;
    }
    if (!$CI->db->field_exists('commission_percent', 'users')) {
        $CI->db->query("ALTER TABLE users ADD COLUMN commission_percent DECIMAL(8,2) NOT NULL DEFAULT 0.00 AFTER commission");
    }
}

function product_owner_commission_reset_cache()
{
    product_owner_commission_rates(null);
}

function product_owner_commission_rates($product)
{
    static $cache = array();
    if ($product === null) {
        $cache = array();
        return array('flat' => 0.0, 'percent' => 0.0);
    }
    $flat = null;
    $percent = null;
    if (isset($product->owner_commission) && $product->owner_commission !== null && $product->owner_commission !== '') {
        $flat = (float) $product->owner_commission;
    }
    if (isset($product->owner_commission_percent) && $product->owner_commission_percent !== null && $product->owner_commission_percent !== '') {
        $percent = (float) $product->owner_commission_percent;
    }
    if (($flat === null || $percent === null) && !empty($product->created_by)) {
        $uid = (int) $product->created_by;
        if (!isset($cache[$uid])) {
            ensure_user_commission_schema();
            $CI =& get_instance();
            $fields = 'commission';
            if ($CI->db->field_exists('commission_percent', 'users')) {
                $fields .= ', commission_percent';
            }
            $user = $CI->db->select($fields)->where('UserID', $uid)->get('users')->row();
            $cache[$uid] = array(
                'flat' => $user ? (float) $user->commission : 0.0,
                'percent' => ($user && isset($user->commission_percent)) ? (float) $user->commission_percent : 0.0,
            );
        }
        if ($flat === null) {
            $flat = $cache[$uid]['flat'];
        }
        if ($percent === null) {
            $percent = $cache[$uid]['percent'];
        }
    }
    return array(
        'flat' => max(0, (float) $flat),
        'percent' => max(0, (float) $percent),
    );
}

function product_commission_base($product)
{
    if (!$product) {
        return 0.0;
    }
    if (empty($product->store_id)) {
        return product_base_price($product);
    }
    $rates = product_owner_commission_rates($product);
    $wholesale = (isset($product->cost_price) && (float) $product->cost_price > 0)
        ? (float) $product->cost_price
        : 0.0;
    if ($wholesale <= 0) {
        return 0.0;
    }
    $divisor = 1 + ($rates['percent'] / 100) + (platform_fee_percent() / 100);
    if ($divisor <= 0) {
        return 0.0;
    }
    return max(0, round(($wholesale - $rates['flat']) / $divisor, 2));
}

function product_commission_amount($product)
{
    $rates = product_owner_commission_rates($product);
    $base = product_commission_base($product);
    return round($rates['flat'] + ($base * ($rates['percent'] / 100)), 2);
}

function ec_catalog_import_user_id($countryId = 0, $fallback = 0)
{
    $CI =& get_instance();
    ensure_user_commission_schema();
    $countryId = (int) $countryId;
    $fallback = (int) $fallback;
    if ($countryId > 0 && $CI->db->table_exists('products')) {
        $row = $CI->db
            ->select('products.created_by, COUNT(*) as owned', false)
            ->from('products')
            ->join('users', 'users.UserID = products.created_by')
            ->where('users.roleID', ROLE_ECOMMERCE)
            ->where('users.status', 1)
            ->group_start()
                ->where('users.commission >', 0)
                ->or_where('users.commission_percent >', 0)
            ->group_end()
            ->group_start()
                ->where('products.store_id IS NULL', null, false)
                ->or_where('products.store_id', 0)
            ->group_end()
            ->where('products.country_id', $countryId)
            ->group_by('products.created_by')
            ->order_by('owned', 'DESC')
            ->order_by('products.created_by', 'ASC')
            ->limit(1)
            ->get()
            ->row();
        if ($row) {
            return (int) $row->created_by;
        }
    }
    $ecom = $CI->db
        ->where('roleID', ROLE_ECOMMERCE)
        ->where('status', 1)
        ->group_start()
            ->where('commission >', 0)
            ->or_where('commission_percent >', 0)
        ->group_end()
        ->order_by('UserID', 'asc')
        ->get('users')
        ->row();
    if ($ecom) {
        return (int) $ecom->UserID;
    }
    return $fallback > 0 ? $fallback : 1;
}

function product_wholesale_price($product, $storeId = 0)
{
    return round(product_base_price($product) + product_commission_amount($product) + product_platform_fee($storeId, $product), 2);
}

function product_store_markup($storeId, $productId)
{
    $CI =& get_instance();
    if (!$storeId || !$CI->db->table_exists('store_product_prices')) {
        return 0.0;
    }
    $row = $CI->db
        ->where('store_id', (int) $storeId)
        ->where('product_id', (int) $productId)
        ->get('store_product_prices')
        ->row();
    return $row ? (float) $row->markup : 0.0;
}

function ensure_whatsapp_schema()
{
    static $done = false;
    if ($done) {
        return;
    }
    $done = true;
    $CI =& get_instance();
    if (!$CI->db->table_exists('platform_settings')) {
        $CI->db->query("CREATE TABLE IF NOT EXISTS `platform_settings` (
            `setting_key` varchar(100) NOT NULL,
            `setting_value` varchar(255) NOT NULL DEFAULT '',
            PRIMARY KEY (`setting_key`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    }
    if ($CI->db->table_exists('users') && !$CI->db->field_exists('whatsapp_number', 'users')) {
        $after = $CI->db->field_exists('phone', 'users') ? ' AFTER phone' : '';
        $CI->db->query("ALTER TABLE users ADD COLUMN whatsapp_number VARCHAR(40) NOT NULL DEFAULT ''" . $after);
    }
}

function ensure_store_pricing_columns()
{
    static $done = false;
    if ($done) {
        return;
    }
    $CI =& get_instance();
    if (!$CI->db->table_exists('stores')) {
        $done = true;
        return;
    }
    if (!$CI->db->field_exists('auto_add_products', 'stores')) {
        $CI->db->query("ALTER TABLE stores ADD COLUMN auto_add_products TINYINT(1) NOT NULL DEFAULT 0 AFTER country_id");
    }
    if (!$CI->db->field_exists('price_plus_amount', 'stores')) {
        $CI->db->query("ALTER TABLE stores ADD COLUMN price_plus_amount DECIMAL(12,2) NOT NULL DEFAULT 0.00 AFTER auto_add_products");
    }
    $done = true;
}

function store_price_plus_amount($store = null)
{
    ensure_store_pricing_columns();
    if (is_object($store) && isset($store->price_plus_amount)) {
        return max(0, (float) $store->price_plus_amount);
    }
    $storeId = is_numeric($store) ? (int) $store : 0;
    if ($storeId < 1) {
        return 0.0;
    }
    $CI =& get_instance();
    $row = $CI->db->select('price_plus_amount')->where('id', $storeId)->get('stores')->row();
    return $row ? max(0, (float) $row->price_plus_amount) : 0.0;
}

function store_auto_add_enabled($store = null)
{
    ensure_store_pricing_columns();
    if (is_object($store) && isset($store->auto_add_products)) {
        return (int) $store->auto_add_products === 1;
    }
    $storeId = is_numeric($store) ? (int) $store : 0;
    if ($storeId < 1) {
        return false;
    }
    $CI =& get_instance();
    $row = $CI->db->select('auto_add_products')->where('id', $storeId)->get('stores')->row();
    return $row && (int) $row->auto_add_products === 1;
}

function ec_load_store_product_model()
{
    $CI =& get_instance();
    if (isset($CI->Store_product_model)) {
        return $CI->Store_product_model;
    }
    $CI->load->model('store/Store_product_model');
    if (isset($CI->Store_product_model)) {
        return $CI->Store_product_model;
    }
    $CI->load->model('Store_product_model');
    return isset($CI->Store_product_model) ? $CI->Store_product_model : null;
}

function ec_auto_add_catalog_product($productId, $mark = false)
{
    $productId = (int) $productId;
    if ($productId < 1) {
        return 0;
    }
    $CI =& get_instance();
    if ($mark && $CI->db->table_exists('products')) {
        if (isset($CI->Product_model) && method_exists($CI->Product_model, 'ensure_auto_add_column')) {
            $CI->Product_model->ensure_auto_add_column();
        } elseif (!$CI->db->field_exists('auto_add_to_stores', 'products')) {
            $CI->db->query("ALTER TABLE products ADD COLUMN auto_add_to_stores TINYINT(1) NOT NULL DEFAULT 0 AFTER status");
        }
        $CI->db->where('id', $productId)->update('products', array('auto_add_to_stores' => 1));
    }
    $model = ec_load_store_product_model();
    if (!$model) {
        return 0;
    }
    return $model->auto_add_catalog_to_stores($productId);
}

function product_customer_price($product, $storeId)
{
    // Product price includes base + ecommerce commission + platform fee + store plus/markup.
    // VAT is applied once at checkout on the order subtotal, not per product.
    $extra = product_store_markup($storeId, isset($product->id) ? $product->id : 0);
    if ($extra <= 0) {
        $extra = store_price_plus_amount($storeId);
    }
    return round(product_wholesale_price($product, $storeId) + $extra, 2);
}

function cart_vat_amount($subtotal)
{
    $vat = (float) platform_setting('vat', 0);
    if ($vat <= 0) {
        return 0.0;
    }
    return round(((float) $subtotal) * ($vat / 100), 2);
}

function cart_total_with_vat($subtotal)
{
    return round(((float) $subtotal) + cart_vat_amount($subtotal), 2);
}

function product_ship_days($product)
{
    $min = isset($product->ship_min_days) ? (int) $product->ship_min_days : 0;
    $max = isset($product->ship_max_days) ? (int) $product->ship_max_days : 0;
    if ($min < 0) {
        $min = 0;
    }
    if ($max < 0) {
        $max = 0;
    }
    if ($min === 0 && $max === 0) {
        return null;
    }
    if ($max > 0 && $min > $max) {
        $tmp = $min;
        $min = $max;
        $max = $tmp;
    }
    if ($min === 0) {
        $min = $max;
    }
    if ($max === 0) {
        $max = $min;
    }
    return array('min' => $min, 'max' => $max);
}

function product_delivery_window($product, $fromDate = null)
{
    $days = product_ship_days($product);
    if (!$days) {
        return null;
    }
    try {
        $start = new DateTime($fromDate ? $fromDate : 'today');
    } catch (Exception $e) {
        $start = new DateTime('today');
    }
    $from = clone $start;
    $to = clone $start;
    $from->modify('+' . $days['min'] . ' days');
    $to->modify('+' . $days['max'] . ' days');
    $crossYear = $from->format('Y') !== $to->format('Y');
    $fromLabel = $from->format($crossYear ? 'j M Y' : 'j M');
    $toLabel = $to->format($crossYear ? 'j M Y' : 'j M');
    if ($days['min'] === $days['max']) {
        $text = 'This product will arrive on ' . $fromLabel . '.';
        $short = $fromLabel;
    } else {
        $text = 'This product will arrive between ' . $fromLabel . ' and ' . $toLabel . '.';
        $short = $fromLabel . ' – ' . $toLabel;
    }
    return array(
        'from' => $from,
        'to' => $to,
        'from_label' => $fromLabel,
        'to_label' => $toLabel,
        'text' => $text,
        'short' => $short,
        'min' => $days['min'],
        'max' => $days['max'],
    );
}

/**
 * Convert a raster upload to WebP and remove the original file.
 * Returns the WebP absolute path, or the original path if conversion is skipped.
 */
function ec_convert_image_to_webp($absolutePath, $quality = 82)
{
    $absolutePath = (string) $absolutePath;
    if ($absolutePath === '' || !is_file($absolutePath) || !is_readable($absolutePath)) {
        return $absolutePath;
    }

    $quality = (int) $quality;
    if ($quality < 1) {
        $quality = 1;
    } elseif ($quality > 100) {
        $quality = 100;
    }

    $info = @getimagesize($absolutePath);
    $mime = ($info && !empty($info['mime'])) ? strtolower((string) $info['mime']) : '';
    $ext = strtolower((string) pathinfo($absolutePath, PATHINFO_EXTENSION));
    if ($mime === '' && $ext === 'webp') {
        $mime = 'image/webp';
    }

    $webpPath = preg_replace('/\.[^.]+$/', '.webp', $absolutePath);
    if (!$webpPath || $webpPath === $absolutePath) {
        $webpPath = $absolutePath . '.webp';
    }

    if ($mime === 'image/webp') {
        if ($ext === 'webp') {
            return $absolutePath;
        }
        if (@rename($absolutePath, $webpPath) || (@copy($absolutePath, $webpPath) && @unlink($absolutePath))) {
            return $webpPath;
        }
        return $absolutePath;
    }

    $ok = ec_webp_encode_gd($absolutePath, $webpPath, $quality, $mime)
        || ec_webp_encode_imagick($absolutePath, $webpPath, $quality)
        || ec_webp_encode_cli($absolutePath, $webpPath, $quality);

    if (!$ok || !is_file($webpPath) || filesize($webpPath) < 32) {
        if (function_exists('log_message')) {
            log_message('error', 'WebP conversion failed for ' . $absolutePath
                . ' gd=' . (function_exists('imagewebp') ? '1' : '0')
                . ' imagick=' . (class_exists('Imagick') ? '1' : '0')
                . ' exec=' . (function_exists('ec_php_fn_enabled') && ec_php_fn_enabled('exec') ? '1' : '0'));
        }
        return $absolutePath;
    }

    if (realpath($absolutePath) !== realpath($webpPath)) {
        @unlink($absolutePath);
    }

    return $webpPath;
}

function ec_webp_encode_gd($absolutePath, $webpPath, $quality, $mime)
{
    if (!function_exists('imagewebp')) {
        return false;
    }

    $im = false;
    switch ($mime) {
        case 'image/jpeg':
        case 'image/pjpeg':
            $im = @imagecreatefromjpeg($absolutePath);
            break;
        case 'image/png':
            $im = @imagecreatefrompng($absolutePath);
            break;
        case 'image/gif':
            $im = @imagecreatefromgif($absolutePath);
            break;
        case 'image/bmp':
        case 'image/x-ms-bmp':
            $im = function_exists('imagecreatefrombmp') ? @imagecreatefrombmp($absolutePath) : false;
            break;
        case 'image/avif':
            $im = function_exists('imagecreatefromavif') ? @imagecreatefromavif($absolutePath) : false;
            break;
        default:
            if (function_exists('imagecreatefromstring')) {
                $bin = @file_get_contents($absolutePath);
                $im = ($bin !== false) ? @imagecreatefromstring($bin) : false;
            }
    }

    if (!$im) {
        return false;
    }

    if (function_exists('imagepalettetotruecolor') && !imageistruecolor($im)) {
        imagepalettetotruecolor($im);
    }
    imagealphablending($im, true);
    imagesavealpha($im, true);

    $ok = @imagewebp($im, $webpPath, (int) $quality);
    return $ok && is_file($webpPath);
}

function ec_webp_encode_imagick($absolutePath, $webpPath, $quality)
{
    if (!class_exists('Imagick')) {
        return false;
    }
    try {
        $im = new Imagick($absolutePath);
        if (method_exists($im, 'setImageFormat')) {
            $im->setImageFormat('webp');
        }
        if (method_exists($im, 'setImageCompressionQuality')) {
            $im->setImageCompressionQuality((int) $quality);
        }
        $ok = $im->writeImage($webpPath);
        $im->clear();
        $im->destroy();
        return $ok && is_file($webpPath);
    } catch (Exception $e) {
        return false;
    }
}

function ec_php_fn_enabled($name)
{
    if (!function_exists($name)) {
        return false;
    }
    $disabled = array_map('trim', explode(',', strtolower((string) ini_get('disable_functions'))));
    return !in_array(strtolower($name), $disabled, true);
}

function ec_run_cmd($cmd)
{
    $cmd = (string) $cmd;
    $code = 1;
    $out = array();
    if (ec_php_fn_enabled('exec')) {
        @exec($cmd . ' 2>&1', $out, $code);
        return array((int) $code, $out);
    }
    if (ec_php_fn_enabled('proc_open')) {
        $des = array(
            0 => array('pipe', 'r'),
            1 => array('pipe', 'w'),
            2 => array('pipe', 'w'),
        );
        $proc = @proc_open($cmd, $des, $pipes);
        if (is_resource($proc)) {
            fclose($pipes[0]);
            $stdout = stream_get_contents($pipes[1]);
            $stderr = stream_get_contents($pipes[2]);
            fclose($pipes[1]);
            fclose($pipes[2]);
            $code = proc_close($proc);
            return array((int) $code, array((string) $stdout, (string) $stderr));
        }
    }
    if (ec_php_fn_enabled('shell_exec')) {
        $stdout = (string) @shell_exec($cmd . ' ; echo __ECCODE:$?');
        if (preg_match('/__ECCODE:(\d+)/', $stdout, $m)) {
            $code = (int) $m[1];
        }
        return array($code, array($stdout));
    }
    return array(1, array());
}

function ec_webp_prepare_bin($src)
{
    $src = (string) $src;
    if ($src === '' || !is_file($src)) {
        return '';
    }
    @chmod($src, 0755);
    $tmp = rtrim(sys_get_temp_dir(), '/\\') . DIRECTORY_SEPARATOR . 'ec_' . preg_replace('/[^a-zA-Z0-9._-]+/', '_', basename($src));
    if (!is_file($tmp) || filesize($tmp) !== filesize($src)) {
        @copy($src, $tmp);
    }
    @chmod($tmp, 0755);
    if (is_file($tmp)) {
        return $tmp;
    }
    return is_executable($src) ? $src : '';
}

function ec_webp_encode_cli($absolutePath, $webpPath, $quality)
{
    if (!ec_php_fn_enabled('exec') && !ec_php_fn_enabled('proc_open') && !ec_php_fn_enabled('shell_exec')) {
        return false;
    }

    $quality = (int) $quality;
    $app = defined('APPPATH') ? APPPATH : (defined('FCPATH') ? rtrim(FCPATH, '/\\') . '/application/' : '');
    $arch = strtolower((string) php_uname('m'));
    $bundled = (strpos($arch, 'aarch64') !== false || strpos($arch, 'arm64') !== false)
        ? $app . 'third_party/cwebp/cwebp-linux-arm64'
        : $app . 'third_party/cwebp/cwebp-linux';

    $bins = array(
        $bundled,
        $app . 'third_party/cwebp/cwebp-darwin',
        '/usr/local/bin/cwebp',
        '/usr/bin/cwebp',
        'cwebp',
        '/usr/local/bin/magick',
        '/usr/bin/magick',
        'magick',
        '/usr/local/bin/convert',
        '/usr/bin/convert',
        'convert',
    );

    foreach ($bins as $bin) {
        $named = in_array($bin, array('cwebp', 'magick', 'convert'), true);
        if (!$named && !is_file($bin)) {
            continue;
        }
        $run = $bin;
        if (!$named && strpos($bin, 'third_party/cwebp') !== false) {
            $run = ec_webp_prepare_bin($bin);
        }
        if ($run === '' || (!$named && !is_file($run))) {
            continue;
        }
        $base = strtolower(basename($run, '.exe'));
        if (strpos($base, 'cwebp') !== false) {
            $cmd = escapeshellarg($run) . ' -quiet -q ' . $quality . ' ' . escapeshellarg($absolutePath) . ' -o ' . escapeshellarg($webpPath);
        } else {
            $cmd = escapeshellarg($run) . ' ' . escapeshellarg($absolutePath) . ' -quality ' . $quality . ' ' . escapeshellarg($webpPath);
        }
        $result = ec_run_cmd($cmd);
        if ((int) $result[0] === 0 && is_file($webpPath) && filesize($webpPath) > 32) {
            return true;
        }
        @unlink($webpPath);
    }

    return false;
}

function ec_public_upload_path($absolutePath)
{
    $absolutePath = str_replace('\\', '/', (string) $absolutePath);
    $root = str_replace('\\', '/', rtrim(FCPATH, '/\\'));
    if (strpos($absolutePath, $root . '/') === 0) {
        return ltrim(substr($absolutePath, strlen($root)), '/');
    }
    return ltrim(str_replace('\\', '/', str_replace(FCPATH, '', $absolutePath)), '/');
}

function ec_sanitize_product_html($html)
{
    $html = html_entity_decode((string) $html, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    $html = preg_replace('#<(script|style|iframe|object|embed|form|noscript|link|meta)[^>]*>.*?</\1>#is', '', $html);
    $html = preg_replace('#<(script|style|iframe|object|embed|form|noscript|link|meta)[^>]*/?>#is', '', $html);
    $html = preg_replace('/\son\w+\s*=\s*("[^"]*"|\'[^\']*\'|[^\s>]+)/i', '', $html);
    $html = preg_replace('/javascript\s*:/i', '', $html);
    $allowed = '<p><br><ul><ol><li><strong><b><em><i><u><h2><h3><h4><h5><table><thead><tbody><tr><th><td><img><a><span><div><blockquote><hr><sup><sub>';
    $html = strip_tags($html, $allowed);
    $html = preg_replace('/(href|src)\s*=\s*([\'"])\s*javascript:[^\'"]*\2/i', '', $html);
    $html = preg_replace('#<a\b[^>]*href\s*=\s*(["\']?)void\s*\(\s*0\s*\)\1[^>]*>.*?</a>#is', '', $html);
    return ec_balance_html_fragment(trim($html));
}

function ec_balance_html_fragment($html)
{
    if ($html === '' || !class_exists('DOMDocument')) {
        return $html;
    }
    $prev = libxml_use_internal_errors(true);
    $doc = new DOMDocument();
    $wrapped = '<article id="ec-html-root">' . $html . '</article>';
    $ok = @$doc->loadHTML('<?xml encoding="UTF-8">' . $wrapped);
    if (!$ok) {
        libxml_clear_errors();
        libxml_use_internal_errors($prev);
        return $html;
    }
    $root = $doc->getElementById('ec-html-root');
    if (!$root) {
        foreach ($doc->getElementsByTagName('article') as $node) {
            if ($node->getAttribute('id') === 'ec-html-root') {
                $root = $node;
                break;
            }
        }
    }
    if (!$root) {
        libxml_clear_errors();
        libxml_use_internal_errors($prev);
        return $html;
    }
    $inner = '';
    foreach ($root->childNodes as $child) {
        $inner .= $doc->saveHTML($child);
    }
    libxml_clear_errors();
    libxml_use_internal_errors($prev);
    return trim($inner);
}

function ec_product_details_html($product)
{
    if (!$product) {
        return '';
    }
    $raw = '';
    if (!empty($product->details)) {
        $raw = $product->details;
    } elseif (!empty($product->description) && preg_match('/<[a-z][\s\S]*>/i', $product->description)) {
        $raw = $product->description;
    }
    return ec_sanitize_product_html($raw);
}

function apply_storefront_pricing($products, $storeId)
{
    $list = is_array($products) ? $products : array($products);
    foreach ($list as $product) {
        if (!$product) {
            continue;
        }
        if (!empty($product->store_id)) {
            $product->base_price = (float) $product->price;
            $product->wholesale_price = (float) $product->price;
            $product->store_markup = 0;
            $product->customer_price = (float) $product->price;
            continue;
        }
        $product->base_price = product_base_price($product);
        $product->wholesale_price = product_wholesale_price($product, $storeId);
        $product->store_markup = product_store_markup($storeId, $product->id);
        $product->customer_price = product_customer_price($product, $storeId);
        $product->price = $product->customer_price;
    }
    return $products;
}

function product_detail_design($settings = array())
{
    $value = '';
    if (is_array($settings) && isset($settings['product_detail_design'])) {
        $value = strtolower(trim((string) $settings['product_detail_design']));
    }
    return $value === 'new' ? 'new' : 'old';
}

function product_option_label($child, $parent = null)
{
    $shipLabel = product_ship_option_label($child);
    $name = trim((string) (is_object($child) && isset($child->name) ? $child->name : ''));
    $parentName = '';
    if (is_object($parent) && isset($parent->name)) {
        $parentName = trim((string) $parent->name);
    }
    if ($parentName !== '' && $name !== '' && stripos($name, $parentName) === 0) {
        $rest = trim(substr($name, strlen($parentName)), " \t-–—:|,");
        if ($rest !== '') {
            return $rest;
        }
    }
    if ($shipLabel !== '' && ($name === '' || ($parentName !== '' && strcasecmp($name, $parentName) === 0))) {
        return $shipLabel;
    }
    return $name !== '' ? $name : ($shipLabel !== '' ? $shipLabel : 'Option');
}

function product_ship_option_label($product)
{
    $days = function_exists('product_ship_days') ? product_ship_days($product) : null;
    if (!$days) {
        return '';
    }
    if ((int) $days['min'] === (int) $days['max']) {
        $n = (int) $days['min'];
        return $n . ' day delivery';
    }
    return (int) $days['min'] . '–' . (int) $days['max'] . ' day delivery';
}

function product_description_bullets($product)
{
    if (!$product || empty($product->description)) {
        return array();
    }
    $text = trim(html_entity_decode(strip_tags((string) $product->description), ENT_QUOTES | ENT_HTML5, 'UTF-8'));
    if ($text === '') {
        return array();
    }
    $parts = preg_split('/\r\n|\r|\n|(?<=\.)\s+(?=[A-Z])/', $text);
    $bullets = array();
    foreach ((array) $parts as $part) {
        $line = trim((string) $part, " \t-•*");
        if ($line === '') {
            continue;
        }
        $bullets[] = $line;
    }
    return $bullets;
}

function product_feature_highlights($product, $attributes = array(), $cartProduct = null)
{
    $items = array();
    foreach ((array) $attributes as $attr) {
        $title = trim((string) (isset($attr->name) ? $attr->name : ''));
        $text = trim((string) (isset($attr->values_text) ? $attr->values_text : ''));
        if ($title === '') {
            continue;
        }
        $items[] = array('title' => $title, 'text' => $text);
        if (count($items) >= 6) {
            return $items;
        }
    }

    $source = $cartProduct ? $cartProduct : $product;
    if ($product) {
        if (!empty($product->brand)) {
            $items[] = array('title' => 'Brand', 'text' => trim((string) $product->brand));
        }
        if (!empty($product->made_by)) {
            $items[] = array('title' => 'Made by', 'text' => trim((string) $product->made_by));
        }
    }
    if ($source && function_exists('product_delivery_window')) {
        $window = product_delivery_window($source);
        if ($window && !empty($window['short'])) {
            $items[] = array('title' => 'Delivery', 'text' => $window['short']);
        }
    }
    foreach (product_description_bullets($product) as $line) {
        if (count($items) >= 6) {
            break;
        }
        $items[] = array('title' => $line, 'text' => '');
    }
    $unique = array();
    $out = array();
    foreach ($items as $item) {
        $key = strtolower($item['title']);
        if (isset($unique[$key])) {
            continue;
        }
        $unique[$key] = true;
        $out[] = $item;
        if (count($out) >= 6) {
            break;
        }
    }
    return $out;
}

function product_spec_rows($product, $cartProduct = null, $attributes = array())
{
    $cartProduct = $cartProduct ? $cartProduct : $product;
    $rows = array();
    $sku = '';
    if ($cartProduct && !empty($cartProduct->sku)) {
        $sku = $cartProduct->sku;
    } elseif ($product && !empty($product->sku)) {
        $sku = $product->sku;
    }
    if ($sku !== '') {
        $rows[] = array('label' => 'SKU', 'value' => $sku);
    }
    if ($product && !empty($product->brand)) {
        $rows[] = array('label' => 'Brand', 'value' => $product->brand);
    }
    if ($product && !empty($product->made_by)) {
        $rows[] = array('label' => 'Made by', 'value' => $product->made_by);
    }
    foreach ((array) $attributes as $attr) {
        $label = trim((string) (isset($attr->name) ? $attr->name : ''));
        $value = trim((string) (isset($attr->values_text) ? $attr->values_text : ''));
        if ($label === '' || $value === '') {
            continue;
        }
        $rows[] = array('label' => $label, 'value' => $value);
    }
    if ($cartProduct && isset($cartProduct->stock)) {
        $stock = (int) $cartProduct->stock;
        $rows[] = array('label' => 'Availability', 'value' => $stock > 0 ? 'In stock' : 'Out of stock');
    }
    return $rows;
}

function product_trust_items($store, $settings = array())
{
    $settings = is_array($settings) ? $settings : array();
    $items = array();
    $promo = function_exists('theme_setting') ? theme_setting($settings, 'promo_text', '') : '';
    $shippingOn = function_exists('setting_flag_on') ? setting_flag_on($settings, 'shipping_enabled') : !empty($settings['shipping_enabled']);
    $rate = isset($settings['shipping_flat_rate']) ? (float) $settings['shipping_flat_rate'] : 0;
    if ($shippingOn && $rate <= 0) {
        $items[] = array(
            'key' => 'delivery',
            'title' => 'Free Delivery',
            'text' => $promo !== '' ? $promo : 'On qualifying orders',
        );
    } elseif ($shippingOn) {
        $items[] = array(
            'key' => 'delivery',
            'title' => 'Delivery',
            'text' => 'Flat rate ' . format_money($rate),
        );
    } elseif ($promo !== '') {
        $items[] = array(
            'key' => 'delivery',
            'title' => 'Delivery',
            'text' => $promo,
        );
    } else {
        $items[] = array(
            'key' => 'delivery',
            'title' => 'Delivery',
            'text' => 'Tracked shipping available',
        );
    }
    $items[] = array('key' => 'returns', 'title' => 'Easy Returns', 'text' => 'Simple returns process');
    $items[] = array('key' => 'secure', 'title' => 'Secure Checkout', 'text' => 'Protected payments');
    $support = '';
    if (!empty($settings['general_support_phone'])) {
        $support = trim((string) $settings['general_support_phone']);
    } elseif ($store && !empty($store->phone)) {
        $support = trim((string) $store->phone);
    } elseif (!empty($settings['general_contact_email'])) {
        $support = trim((string) $settings['general_contact_email']);
    } elseif ($store && !empty($store->email)) {
        $support = trim((string) $store->email);
    }
    $items[] = array(
        'key' => 'support',
        'title' => 'Customer Support',
        'text' => $support !== '' ? $support : 'We are here to help',
    );
    return $items;
}

function product_video_embed($product)
{
    $html = '';
    if ($product && !empty($product->details)) {
        $html .= ' ' . $product->details;
    }
    if ($product && !empty($product->description)) {
        $html .= ' ' . $product->description;
    }
    if (preg_match('#(?:youtube\.com/embed/|youtube\.com/watch\?v=|youtu\.be/)([A-Za-z0-9_-]{6,})#i', $html, $m)) {
        return 'https://www.youtube.com/embed/' . $m[1];
    }
    if (preg_match('#vimeo\.com/(?:video/)?([0-9]+)#i', $html, $m)) {
        return 'https://player.vimeo.com/video/' . $m[1];
    }
    return '';
}

function storefront_wishlist($storeId)
{
    $storeId = (int) $storeId;
    if ($storeId < 1) {
        return array();
    }
    if (empty($_SESSION['storefront_wishlist'][$storeId]) || !is_array($_SESSION['storefront_wishlist'][$storeId])) {
        return array();
    }
    $ids = array();
    foreach ($_SESSION['storefront_wishlist'][$storeId] as $id) {
        $id = (int) $id;
        if ($id > 0) {
            $ids[$id] = $id;
        }
    }
    return $ids;
}

function product_in_wishlist($storeId, $productId)
{
    $ids = storefront_wishlist($storeId);
    return isset($ids[(int) $productId]);
}

function product_star_html($rating, $max = 5)
{
    $rating = max(0, min((float) $max, (float) $rating));
    $html = '<span class="pdp-new-stars" aria-label="' . htmlspecialchars(number_format($rating, 1)) . ' out of ' . (int) $max . '">';
    for ($i = 1; $i <= (int) $max; $i++) {
        $fill = 0;
        if ($rating >= $i) {
            $fill = 100;
        } elseif ($rating > ($i - 1)) {
            $fill = (int) round(($rating - ($i - 1)) * 100);
        }
        $html .= '<span class="pdp-new-star"><span class="pdp-new-star__fill" style="width:' . $fill . '%">★</span>★</span>';
    }
    $html .= '</span>';
    return $html;
}

function product_attributes_rows($productId)
{
    $CI =& get_instance();
    $productId = (int) $productId;
    if ($productId < 1 || !$CI->db->table_exists('product_attributes')) {
        return array();
    }
    $rows = $CI->db
        ->where('product_id', $productId)
        ->order_by('sort_order', 'asc')
        ->order_by('id', 'asc')
        ->get('product_attributes')
        ->result();
    return $rows ? $rows : array();
}
