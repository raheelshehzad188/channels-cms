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

function store_is_sweden($store = null)
{
    if (!is_object($store)) {
        return false;
    }
    if (isset($store->country_id) && (int) $store->country_id === 11) {
        return true;
    }
    return isset($store->id) && (int) $store->id === 8;
}

function store_theme_toggle($settings, $key, $default = true)
{
    if (!is_array($settings) || !array_key_exists($key, $settings) || $settings[$key] === '' || $settings[$key] === null) {
        return (bool) $default;
    }
    return setting_flag_on($settings, $key);
}

function store_pdp_show_short_description($settings = array(), $store = null)
{
    return store_theme_toggle($settings, 'show_short_description', !store_is_sweden($store));
}

function store_pdp_show_trust_icons($settings = array(), $store = null)
{
    return store_theme_toggle($settings, 'show_pdp_trust_icons', !store_is_sweden($store));
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
    $english = isset($category->name) ? trim((string) $category->name) : '';
    $native = isset($category->local_name) ? trim((string) $category->local_name) : '';
    $locale = function_exists('storefront_ui_locale') ? storefront_ui_locale() : '';
    if ($locale === 'en') {
        return $english !== '' ? $english : $native;
    }
    if ($native !== '') {
        return $native;
    }
    return $english;
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

function ec_ascii_slug($str, $fallback = 'item')
{
    $str = trim((string) $str);
    if ($str === '') {
        return $fallback;
    }
    if (strpos($str, '%') !== false) {
        $decoded = rawurldecode($str);
        if ($decoded !== '') {
            $str = $decoded;
        }
    }
    $map = array(
        'å' => 'a', 'ä' => 'a', 'ö' => 'o', 'ø' => 'o', 'æ' => 'ae',
        'Å' => 'a', 'Ä' => 'a', 'Ö' => 'o', 'Ø' => 'o', 'Æ' => 'ae',
        'á' => 'a', 'à' => 'a', 'â' => 'a', 'ã' => 'a',
        'é' => 'e', 'è' => 'e', 'ê' => 'e', 'ë' => 'e',
        'í' => 'i', 'ì' => 'i', 'î' => 'i', 'ï' => 'i',
        'ó' => 'o', 'ò' => 'o', 'ô' => 'o', 'õ' => 'o',
        'ú' => 'u', 'ù' => 'u', 'û' => 'u', 'ü' => 'u',
        'ý' => 'y', 'ÿ' => 'y', 'ñ' => 'n', 'ç' => 'c', 'ß' => 'ss',
        'Á' => 'a', 'À' => 'a', 'Â' => 'a', 'Ã' => 'a',
        'É' => 'e', 'È' => 'e', 'Ê' => 'e', 'Ë' => 'e',
        'Í' => 'i', 'Ì' => 'i', 'Î' => 'i', 'Ï' => 'i',
        'Ó' => 'o', 'Ò' => 'o', 'Ô' => 'o', 'Õ' => 'o',
        'Ú' => 'u', 'Ù' => 'u', 'Û' => 'u', 'Ü' => 'u',
        'Ý' => 'y', 'Ñ' => 'n', 'Ç' => 'c',
    );
    $str = strtr($str, $map);
    if (function_exists('iconv')) {
        $trans = @iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $str);
        if (is_string($trans) && $trans !== '') {
            $str = $trans;
        }
    }
    $str = strtolower($str);
    $str = preg_replace('/[^a-z0-9]+/', '-', $str);
    $str = trim((string) $str, '-');
    return $str !== '' ? $str : $fallback;
}

function ec_slug_is_ascii($str)
{
    return (string) $str !== '' && (bool) preg_match('/^[a-z0-9]+(?:-[a-z0-9]+)*$/', (string) $str);
}

function ec_slug_candidates($slug)
{
    $slug = trim((string) $slug);
    $out = array();
    $add = function ($value) use (&$out) {
        $value = trim((string) $value);
        if ($value !== '' && !in_array($value, $out, true)) {
            $out[] = $value;
        }
    };
    $add($slug);
    if (strpos($slug, '%') !== false) {
        $add(rawurldecode($slug));
    }
    $ascii = ec_ascii_slug($slug, '');
    if ($ascii !== '') {
        $add($ascii);
    }
    return $out;
}

function storefront_path_slug($slug, $fallback = 'item')
{
    return ec_ascii_slug($slug, $fallback);
}

function storefront_header_menu_url($item)
{
    $type = is_object($item) && isset($item->item_type) ? $item->item_type : 'custom';
    if ($type === 'page') {
        $slug = '';
        if (is_object($item) && !empty($item->page_slug)) {
            $slug = $item->page_slug;
        } elseif (is_object($item) && !empty($item->slug)) {
            $slug = $item->slug;
        }
        return function_exists('page_url') ? page_url($slug) : storefront_url('page/' . $slug);
    }
    $path = is_object($item) && isset($item->slug) ? trim((string) $item->slug) : '';
    if ($path === '') {
        return storefront_url('');
    }
    if (preg_match('#^https?://#i', $path)) {
        return $path;
    }
    return storefront_url(ltrim($path, '/'));
}

function storefront_header_menu_items($storeId, $current = '')
{
    $CI =& get_instance();
    $CI->load->model('Store_header_menu_model');
    $rows = $CI->Store_header_menu_model->active_resolved((int) $storeId);
    $out = array();
    $current = strtolower(trim((string) $current));
    foreach ($rows as $row) {
        $slug = strtolower(trim((string) $row->slug));
        $label = isset($row->label) ? trim((string) $row->label) : '';
        $labelEn = isset($row->label_en) ? trim((string) $row->label_en) : '';
        $locale = function_exists('storefront_ui_locale') ? storefront_ui_locale() : '';
        if ($locale === 'en' && $labelEn !== '') {
            $label = $labelEn;
        }
        $item = (object) array(
            'label' => $label,
            'url' => storefront_header_menu_url($row),
            'slug' => $slug,
            'active' => ($current !== '' && ($slug === $current || rtrim($slug, '/') === $current)),
        );
        $out[] = $item;
    }
    return $out;
}

function storefront_cms_root_slugs()
{
    return array('contact', 'privacy-policy', 'data-deletion', 'terms-of-service');
}

function contact_url()
{
    return storefront_url('contact');
}

function page_url($pageOrSlug)
{
    $slug = '';
    if (is_object($pageOrSlug) && isset($pageOrSlug->slug)) {
        $slug = (string) $pageOrSlug->slug;
    } else {
        $slug = (string) $pageOrSlug;
    }
    $slug = storefront_path_slug($slug, 'page');
    if (in_array($slug, storefront_cms_root_slugs(), true)) {
        return storefront_url($slug);
    }
    return storefront_url('page/' . $slug);
}

function storefront_contact_is_placeholder($value, $type = 'text')
{
    $value = trim((string) $value);
    if ($value === '') {
        return true;
    }
    $lower = strtolower($value);
    $dummy = array(
        '123 street, new york',
        '1429 netus rd, ny 48247',
        'example@gmail.com',
        'email@example.com',
        'hello@example.com',
        'sweden',
        'new york',
        '+0123 4567 8910',
        '+46 8 000 00 00',
        '000 00 00',
    );
    if (in_array($lower, $dummy, true)) {
        return true;
    }
    if ($type === 'email' || $type === 'any') {
        if (preg_match('/@(example\.com|email\.com|test\.com)$/i', $value)) {
            return true;
        }
    }
    if ($type === 'phone' || $type === 'any') {
        $digits = preg_replace('/\D+/', '', $value);
        if ($digits !== '' && (preg_match('/0{5,}/', $digits) || $digits === '1234567890' || $digits === '012345678910')) {
            return true;
        }
    }
    if ($type === 'address' || $type === 'text') {
        if (preg_match('/^(sweden|norge|norway|denmark|danmark|finland|uk|united kingdom)$/i', $value)) {
            return true;
        }
    }
    return false;
}

function storefront_contact_info($store = null, $settings = null)
{
    $info = array(
        'name' => '',
        'email' => '',
        'phone' => '',
        'address' => '',
        'domain' => '',
    );
    if (is_object($store)) {
        if (!empty($store->name)) {
            $info['name'] = trim((string) $store->name);
        }
        if (!empty($store->domain)) {
            $info['domain'] = strtolower(trim((string) $store->domain));
        }
        if (!empty($store->email) && filter_var(trim((string) $store->email), FILTER_VALIDATE_EMAIL)) {
            $info['email'] = trim((string) $store->email);
        }
        if (!empty($store->phone)) {
            $info['phone'] = trim((string) $store->phone);
        }
    }
    if (is_array($settings)) {
        if (!empty($settings['general_store_name'])) {
            $info['name'] = trim((string) $settings['general_store_name']);
        }
        foreach (array('general_contact_email', 'email', 'email_reply_to') as $key) {
            $candidate = isset($settings[$key]) ? trim((string) $settings[$key]) : '';
            if ($candidate !== '' && filter_var($candidate, FILTER_VALIDATE_EMAIL) && !storefront_contact_is_placeholder($candidate, 'email')) {
                $info['email'] = $candidate;
                break;
            }
        }
        foreach (array('general_support_phone', 'phone', 'whatsapp_number') as $key) {
            $candidate = isset($settings[$key]) ? trim((string) $settings[$key]) : '';
            if ($candidate !== '' && !storefront_contact_is_placeholder($candidate, 'phone')) {
                $info['phone'] = $candidate;
                break;
            }
        }
        if (!empty($settings['address']) && !storefront_contact_is_placeholder(trim((string) $settings['address']), 'address')) {
            $info['address'] = trim((string) $settings['address']);
        }
    }
    if ($info['email'] !== '' && storefront_contact_is_placeholder($info['email'], 'email')) {
        $info['email'] = '';
    }
    if ($info['phone'] !== '' && storefront_contact_is_placeholder($info['phone'], 'phone')) {
        $info['phone'] = '';
    }
    if ($info['address'] !== '' && storefront_contact_is_placeholder($info['address'], 'address')) {
        $info['address'] = '';
    }
    return $info;
}

function store_page_html($html)
{
    $html = (string) $html;
    $html = preg_replace('#<script\b[^>]*>.*?</script>#is', '', $html);
    $html = preg_replace('#<style\b[^>]*>.*?</style>#is', '', $html);
    $html = preg_replace('/on\w+\s*=\s*("[^"]*"|\'[^\']*\'|[^\s>]+)/i', '', $html);
    $html = preg_replace('/javascript\s*:/i', '', $html);
    $clean = strip_tags($html, '<p><br><strong><b><em><i><u><ul><ol><li><a><h2><h3><h4><blockquote><hr><span>');
    if (strpos($html, '<') === false) {
        return nl2br(htmlspecialchars($html, ENT_QUOTES, 'UTF-8'));
    }
    return $clean;
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
        return storefront_url('product/' . storefront_path_slug($slug, 'product'));
    }
    return $id > 0 ? storefront_url('product/' . $id) : storefront_url('shop');
}

function category_url($category)
{
    $slug = '';
    if (is_object($category) && isset($category->slug)) {
        $slug = trim((string) $category->slug);
    } elseif (is_string($category)) {
        $slug = trim($category);
    }
    if ($slug === '') {
        return storefront_url('shop');
    }
    return storefront_url('category/' . storefront_path_slug($slug, 'category'));
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

function product_image_is_available($path)
{
    $path = trim((string) $path);
    if ($path === '') {
        return false;
    }
    if (preg_match('#^https?://#i', $path) || strpos($path, '//') === 0) {
        return true;
    }
    $rel = ltrim(str_replace('\\', '/', $path), '/');
    if (defined('FCPATH') && $rel !== '' && is_file(FCPATH . $rel)) {
        return true;
    }
    return false;
}

function product_image_url($path, $fallback = '')
{
    $path = trim((string) $path);
    if ($path === '' || !product_image_is_available($path)) {
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

function product_feature_url($product)
{
    if (!is_object($product) || empty($product->image)) {
        return '';
    }
    return product_image_url($product->image);
}

function product_source_gallery_urls($product)
{
    $urls = array();
    if (!is_object($product) || empty($product->source_product_id)) {
        return $urls;
    }
    $CI =& get_instance();
    $sourceId = (int) $product->source_product_id;
    if ($sourceId < 1 || (int) $product->id === $sourceId) {
        return $urls;
    }
    $source = $CI->db->where('id', $sourceId)->get('products')->row();
    if (!$source) {
        return $urls;
    }
    $rows = array();
    if ($CI->db->table_exists('product_images')) {
        $rows = $CI->db
            ->where('product_id', $sourceId)
            ->order_by('sort_order', 'asc')
            ->order_by('id', 'asc')
            ->get('product_images')
            ->result();
    }
    return product_gallery_urls($source, $rows);
}

function product_child_feature_urls($children)
{
    $urls = array();
    $seen = array();
    foreach ((array) $children as $child) {
        $url = product_feature_url($child);
        if ($url === '' || isset($seen[$url])) {
            continue;
        }
        $seen[$url] = true;
        $urls[] = $url;
    }
    return $urls;
}

function product_pdp_gallery($product, $ownUrls = array(), $parentUrls = array(), $extraUrls = array())
{
    $urls = array();
    $seen = array();
    $add = function ($url) use (&$urls, &$seen) {
        $url = trim((string) $url);
        if ($url === '' || isset($seen[$url])) {
            return;
        }
        $seen[$url] = true;
        $urls[] = $url;
    };
    $feature = product_feature_url($product);
    if ($feature !== '') {
        $add($feature);
    }
    foreach ((array) $ownUrls as $url) {
        $add($url);
    }
    foreach ((array) $extraUrls as $url) {
        $add($url);
    }
    foreach ((array) $parentUrls as $url) {
        $add($url);
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
    if (!$CI->db->field_exists('sort_order', 'products')) {
        $CI->db->query("ALTER TABLE products ADD COLUMN sort_order INT(11) NOT NULL DEFAULT 0 AFTER is_default");
    }
    if (!$CI->db->field_exists('short_details', 'products')) {
        $CI->db->query("ALTER TABLE products ADD COLUMN short_details MEDIUMTEXT NULL AFTER description");
    }
    if (!$CI->db->field_exists('options_title', 'products')) {
        $CI->db->query("ALTER TABLE products ADD COLUMN options_title VARCHAR(150) NOT NULL DEFAULT '' AFTER sort_order");
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

function ensure_product_trending_columns()
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
    if (!$CI->db->field_exists('is_trending', 'products')) {
        $after = $CI->db->field_exists('auto_add_to_stores', 'products')
            ? ' AFTER auto_add_to_stores'
            : ($CI->db->field_exists('status', 'products') ? ' AFTER status' : '');
        $CI->db->query('ALTER TABLE products ADD COLUMN is_trending TINYINT(1) NOT NULL DEFAULT 0' . $after);
    }
    if (!$CI->db->field_exists('trending_order', 'products')) {
        $after = $CI->db->field_exists('is_trending', 'products') ? ' AFTER is_trending' : '';
        $CI->db->query('ALTER TABLE products ADD COLUMN trending_order INT(11) NOT NULL DEFAULT 0' . $after);
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
        if ($CI->db->field_exists('sort_order', 'products')) {
            $CI->db->order_by('CASE WHEN products.sort_order < 1 THEN 2147483647 ELSE products.sort_order END', 'ASC', false);
        }
        $CI->db->order_by('products.id', 'asc');
        $children = $CI->db->get()->result();
        $parent = product_overlay_catalog_variation_flags($parent);
        $children = product_overlay_catalog_variation_flags($children);
        if (is_array($children) && count($children) > 1) {
            usort($children, 'product_price_compare');
        }
    }

    $selected = $parent ? $parent : $product;
    if (!empty($children)) {
        $selected = $children[0];
        foreach ($children as $child) {
            if (isset($child->price) && (float) $child->price > 0) {
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

function product_overlay_catalog_variation_flags($products)
{
    $list = is_array($products) ? $products : array($products);
    $sourceIds = array();
    foreach ($list as $item) {
        if (!$item || empty($item->store_id) || empty($item->source_product_id)) {
            continue;
        }
        $sourceIds[(int) $item->source_product_id] = (int) $item->source_product_id;
    }
    if (!$sourceIds) {
        return $products;
    }
    $CI =& get_instance();
    if (!$CI->db->table_exists('products')) {
        return $products;
    }
    $CI->db->reset_query();
    $select = 'id, is_default, sort_order';
    if ($CI->db->field_exists('options_title', 'products')) {
        $select .= ', options_title';
    }
    $rows = $CI->db->select($select)
        ->where_in('id', array_values($sourceIds))
        ->get('products')
        ->result();
    $flags = array();
    foreach ($rows as $row) {
        $flags[(int) $row->id] = $row;
    }
    foreach ($list as $item) {
        if (!$item || empty($item->source_product_id)) {
            continue;
        }
        $sid = (int) $item->source_product_id;
        if (!isset($flags[$sid])) {
            continue;
        }
        $item->is_default = (int) $flags[$sid]->is_default;
        $item->sort_order = (int) $flags[$sid]->sort_order;
        $storeTitle = isset($item->options_title) ? trim((string) $item->options_title) : '';
        if ($storeTitle === '' && isset($flags[$sid]->options_title)) {
            $item->options_title = $flags[$sid]->options_title;
        }
    }
    return $products;
}

function product_options_title($product)
{
    $title = '';
    if ($product && isset($product->options_title)) {
        $title = trim((string) $product->options_title);
    }
    if ($title !== '') {
        return $title;
    }
    if ($product) {
        $parentSku = isset($product->parent_sku) ? trim((string) $product->parent_sku) : '';
        if ($parentSku !== '') {
            $family = product_family($product, true);
            $parent = !empty($family['parent']) ? $family['parent'] : null;
            if ($parent && (int) $parent->id !== (int) $product->id) {
                $fromParent = isset($parent->options_title) ? trim((string) $parent->options_title) : '';
                if ($fromParent !== '') {
                    return $fromParent;
                }
                if (!empty($parent->source_product_id)) {
                    $product = $parent;
                }
            }
        }
    }
    if (!$product || empty($product->source_product_id)) {
        return '';
    }
    $CI =& get_instance();
    if (!$CI->db->table_exists('products') || !$CI->db->field_exists('options_title', 'products')) {
        return '';
    }
    $CI->db->reset_query();
    $row = $CI->db->select('options_title')
        ->where('id', (int) $product->source_product_id)
        ->get('products')
        ->row();
    return $row ? trim((string) $row->options_title) : '';
}

function storefront_listing_product($product)
{
    if (!$product) {
        return null;
    }
    $parentSku = isset($product->parent_sku) ? trim((string) $product->parent_sku) : '';
    if ($parentSku === '') {
        return storefront_listing_min_price($product);
    }
    $family = product_family($product, true);
    $parent = !empty($family['parent']) ? $family['parent'] : null;
    if (!$parent) {
        return null;
    }
    $stillChild = isset($parent->parent_sku) ? trim((string) $parent->parent_sku) : '';
    if ($stillChild !== '') {
        return null;
    }
    return storefront_listing_min_price($parent);
}

function storefront_listing_min_price($product)
{
    if (!$product) {
        return $product;
    }
    $parentSku = isset($product->parent_sku) ? trim((string) $product->parent_sku) : '';
    if ($parentSku !== '') {
        return $product;
    }
    $family = product_family($product, true);
    $children = !empty($family['children']) ? $family['children'] : array();
    if (!empty($children)) {
        $storeId = !empty($product->store_id) ? (int) $product->store_id : 0;
        if (function_exists('apply_storefront_pricing') && $storeId > 0) {
            apply_storefront_pricing($children, $storeId);
        }
        usort($children, 'product_price_compare');
        $family['selected'] = $children[0];
    }
    $cheap = !empty($family['selected']) ? $family['selected'] : null;
    if (!$cheap || empty($children) || !isset($cheap->price) || (float) $cheap->price <= 0) {
        return $product;
    }
    if ((int) $cheap->id === (int) $product->id) {
        return $product;
    }
    $product->price = $cheap->price;
    if (isset($cheap->compare_price)) {
        $product->compare_price = $cheap->compare_price;
    }
    if (!empty($cheap->active_offer)) {
        $product->active_offer = $cheap->active_offer;
    }
    if (isset($cheap->offer_original_price)) {
        $product->offer_original_price = $cheap->offer_original_price;
    }
    if (!empty($cheap->display_discount_percent)) {
        $product->display_discount_percent = $cheap->display_discount_percent;
    }
    return $product;
}

function storefront_listing_products($products)
{
    $out = array();
    $seen = array();
    foreach ((array) $products as $product) {
        $parent = storefront_listing_product($product);
        if (!$parent) {
            continue;
        }
        $id = (int) $parent->id;
        if ($id < 1 || isset($seen[$id])) {
            continue;
        }
        $seen[$id] = true;
        $out[] = $parent;
    }
    return $out;
}

function product_sort_value($product)
{
    $sort = is_object($product) && isset($product->sort_order) ? (int) $product->sort_order : 0;
    return $sort < 1 ? 2147483647 : $sort;
}

function product_price_compare($a, $b)
{
    $pa = is_object($a) && isset($a->price) ? (float) $a->price : 0;
    $pb = is_object($b) && isset($b->price) ? (float) $b->price : 0;
    if ($pa <= 0 && $pb > 0) {
        return 1;
    }
    if ($pb <= 0 && $pa > 0) {
        return -1;
    }
    if ($pa < $pb) {
        return -1;
    }
    if ($pa > $pb) {
        return 1;
    }
    $aid = is_object($a) && isset($a->id) ? (int) $a->id : 0;
    $bid = is_object($b) && isset($b->id) ? (int) $b->id : 0;
    return $aid - $bid;
}

function product_sort_compare($a, $b)
{
    $diff = product_sort_value($a) - product_sort_value($b);
    if ($diff !== 0) {
        return $diff;
    }
    return (int) $a->id - (int) $b->id;
}

function storefront_order_by_sort($table = 'products', $idDir = 'desc')
{
    $CI =& get_instance();
    $table = preg_replace('/[^a-z0-9_]/i', '', (string) $table);
    if ($table === '') {
        $table = 'products';
    }
    if ($CI->db->field_exists('sort_order', 'products')) {
        $CI->db->order_by('CASE WHEN ' . $table . '.sort_order < 1 THEN 2147483647 ELSE ' . $table . '.sort_order END', 'ASC', false);
    }
    $idDir = strtolower($idDir) === 'asc' ? 'asc' : 'desc';
    $CI->db->order_by($table . '.id', $idDir);
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

function product_next_child_sort($parentSku, $storeId = 0)
{
    $CI =& get_instance();
    if (!$CI->db->table_exists('products') || !$CI->db->field_exists('sort_order', 'products')) {
        return 0;
    }
    $parentSku = trim((string) $parentSku);
    if ($parentSku === '') {
        return 0;
    }
    $CI->db->select_max('sort_order');
    $CI->db->from('products');
    product_scope_store($storeId);
    $CI->db->where('parent_sku', $parentSku);
    $row = $CI->db->get()->row();
    $max = ($row && isset($row->sort_order)) ? (int) $row->sort_order : 0;
    return $max + 1;
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

/**
 * Internal QA buyer: PayPal sandbox + email and WhatsApp order notifications.
 * Live Meta traffic must never hit this path unless checkout email matches exactly.
 */
function checkout_test_buyer_email()
{
    return 'raheel@zenvello.se';
}

function is_checkout_test_buyer($email)
{
    $email = strtolower(trim((string) $email));
    return $email !== '' && $email === checkout_test_buyer_email();
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

function ec_ensure_country_symbol_schema()
{
    static $done = false;
    if ($done) {
        return;
    }
    $done = true;
    $CI =& get_instance();
    if (!$CI->db->table_exists('countries')) {
        return;
    }
    if (!$CI->db->field_exists('currency_symbol', 'countries')) {
        $CI->db->query("ALTER TABLE countries ADD COLUMN currency_symbol VARCHAR(16) NOT NULL DEFAULT '' AFTER currency");
        $CI->db->query("UPDATE countries SET currency_symbol = currency WHERE currency_symbol = '' OR currency_symbol IS NULL");
    }
}

function currency_symbol($currency = null)
{
    static $byCode = null;
    static $byCountry = null;
    ec_ensure_country_symbol_schema();
    if ($byCode === null) {
        $byCode = array();
        $byCountry = array();
        $CI =& get_instance();
        if ($CI->db->table_exists('countries') && $CI->db->field_exists('currency_symbol', 'countries')) {
            foreach ($CI->db->select('id, currency, currency_symbol')->get('countries')->result() as $row) {
                $code = strtoupper(trim((string) $row->currency));
                $symbol = trim((string) $row->currency_symbol);
                if ($symbol === '') {
                    $symbol = $code;
                }
                if ($code !== '') {
                    $byCode[$code] = $symbol;
                }
                $byCountry[(int) $row->id] = $symbol;
            }
        }
    }
    $code = money_currency($currency);
    if ($code !== '' && isset($byCode[$code])) {
        return $byCode[$code];
    }
    if ($currency === null || $currency === '') {
        $CI =& get_instance();
        $store = isset($CI->store) ? $CI->store : null;
        $countryId = ($store && !empty($store->country_id)) ? (int) $store->country_id : 0;
        if ($countryId > 0 && isset($byCountry[$countryId])) {
            return $byCountry[$countryId];
        }
    }
    return $code !== '' ? $code : '';
}

function format_money($amount, $currency = null)
{
    $symbol = trim((string) currency_symbol($currency));
    if ($symbol === '') {
        $symbol = money_currency($currency);
    }
    return $symbol . ' ' . number_format((float) $amount, 2);
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

function ec_recalculate_store_listing_prices($storeId = 0, $ownerUserId = 0)
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

    usort($copies, function ($a, $b) {
        $ap = isset($a->parent_sku) ? trim((string) $a->parent_sku) : '';
        $bp = isset($b->parent_sku) ? trim((string) $b->parent_sku) : '';
        if ($ap === '' && $bp !== '') {
            return -1;
        }
        if ($ap !== '' && $bp === '') {
            return 1;
        }
        return ((int) $a->id) - ((int) $b->id);
    });

    $sourceIds = array();
    $storeIds = array();
    foreach ($copies as $copy) {
        $sourceIds[(int) $copy->source_product_id] = true;
        $storeIds[(int) $copy->store_id] = true;
    }

    $sources = array();
    foreach (array_chunk(array_keys($sourceIds), 500) as $chunk) {
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
        $CI->db->reset_query();
        $plusByStore[(int) $sid] = store_price_plus_amount((int) $sid);
    }

    $updated = 0;
    foreach ($copies as $copy) {
        $source = isset($sources[(int) $copy->source_product_id]) ? $sources[(int) $copy->source_product_id] : null;
        if (!$source) {
            continue;
        }
        $wholesale = product_wholesale_price($source, (int) $copy->store_id);
        $plus = isset($plusByStore[(int) $copy->store_id]) ? (float) $plusByStore[(int) $copy->store_id] : 0.0;
        $price = product_listed_price_from_source((int) $copy->store_id, $source, $wholesale, $plus);
        $payload = array(
            'cost_price' => round($wholesale, 2),
            'price' => round($price, 2),
        );
        $sameCost = abs((float) $copy->cost_price - (float) $payload['cost_price']) < 0.001;
        $samePrice = abs((float) $copy->price - (float) $payload['price']) < 0.001;
        if ($sameCost && $samePrice) {
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

function storefront_admin_can_see_pricing()
{
    return function_exists('storefront_admin_bar_visible') && storefront_admin_bar_visible();
}

function storefront_admin_source_urls($listing)
{
    if (!$listing) {
        return array();
    }
    $catalogId = !empty($listing->source_product_id) ? (int) $listing->source_product_id : (int) $listing->id;
    if ($catalogId < 1) {
        return array();
    }
    if (empty($GLOBALS['ec_admin_source_url_cache']) || !is_array($GLOBALS['ec_admin_source_url_cache'])) {
        $GLOBALS['ec_admin_source_url_cache'] = array();
    }
    if (isset($GLOBALS['ec_admin_source_url_cache'][$catalogId])) {
        return $GLOBALS['ec_admin_source_url_cache'][$catalogId];
    }
    $urls = array();
    $push = function ($url) use (&$urls) {
        $url = trim((string) $url);
        if ($url === '' || !preg_match('#^https?://#i', $url)) {
            return;
        }
        if (!in_array($url, $urls, true)) {
            $urls[] = $url;
        }
    };
    $CI =& get_instance();
    if ($CI->db->table_exists('products') && $CI->db->field_exists('source_url', 'products')) {
        $row = $CI->db->select('source_url')->where('id', $catalogId)->get('products')->row();
        if ($row) {
            $push($row->source_url);
        }
        if ($listing !== $row && !empty($listing->source_url)) {
            $push($listing->source_url);
        }
    } elseif (!empty($listing->source_url)) {
        $push($listing->source_url);
    }
    if ($CI->db->table_exists('product_sources')) {
        $rows = $CI->db
            ->select('source_url')
            ->where('product_id', $catalogId)
            ->order_by('sort_order', 'asc')
            ->order_by('id', 'asc')
            ->get('product_sources')
            ->result();
        foreach ($rows as $row) {
            $push($row->source_url);
        }
    }
    $GLOBALS['ec_admin_source_url_cache'][$catalogId] = $urls;
    return $urls;
}

function storefront_admin_card_meta($listing, $store = null)
{
    if (!$listing || !storefront_admin_can_see_pricing()) {
        return null;
    }
    $storeId = 0;
    if (is_object($store) && !empty($store->id)) {
        $storeId = (int) $store->id;
    } elseif (!empty($listing->store_id)) {
        $storeId = (int) $listing->store_id;
    }
    $source = storefront_pricing_source($listing);
    if (!$source) {
        $source = $listing;
    }
    $days = product_ship_days($source);
    if (!$days) {
        $days = product_ship_days($listing);
    }
    $daysLabel = '';
    if ($days) {
        $daysLabel = ((int) $days['min'] === (int) $days['max'])
            ? ((int) $days['min'] . ' days')
            : ((int) $days['min'] . '–' . (int) $days['max'] . ' days');
    }
    $cost = product_base_price($source);
    $wholesale = product_wholesale_price($source, $storeId);
    $listed = isset($listing->price) ? (float) $listing->price : 0.0;
    $margin = round($listed - $wholesale, 2);
    $diff = round($listed - $cost, 2);
    $currency = store_currency($store !== null ? $store : $storeId);
    $urls = storefront_admin_source_urls($listing);
    return array(
        'days' => $daysLabel,
        'cost' => round($cost, 2),
        'cost_formatted' => format_money($cost, $currency),
        'margin' => $margin,
        'margin_formatted' => format_money($margin, $currency),
        'diff' => $diff,
        'diff_formatted' => format_money($diff, $currency),
        'source_url' => $urls ? $urls[0] : '',
        'source_urls' => $urls,
    );
}

function storefront_pricing_source($product)
{
    if (empty($GLOBALS['ec_pricing_source_cache']) || !is_array($GLOBALS['ec_pricing_source_cache'])) {
        $GLOBALS['ec_pricing_source_cache'] = array();
    }
    if (!$product) {
        return null;
    }
    $id = !empty($product->source_product_id) ? (int) $product->source_product_id : (int) $product->id;
    if ($id < 1) {
        return $product;
    }
    if (isset($GLOBALS['ec_pricing_source_cache'][$id])) {
        return $GLOBALS['ec_pricing_source_cache'][$id];
    }
    $CI =& get_instance();
    if (!$CI->db->table_exists('products')) {
        $GLOBALS['ec_pricing_source_cache'][$id] = $product;
        return $product;
    }
    $row = $CI->db
        ->select('products.*, users.commission as owner_commission, users.commission_percent as owner_commission_percent', false)
        ->from('products')
        ->join('users', 'users.UserID = products.created_by', 'left')
        ->where('products.id', $id)
        ->get()
        ->row();
    $GLOBALS['ec_pricing_source_cache'][$id] = $row ? $row : $product;
    return $GLOBALS['ec_pricing_source_cache'][$id];
}

function storefront_pricing_source_reset($catalogId = 0)
{
    if ($catalogId > 0 && isset($GLOBALS['ec_pricing_source_cache'][(int) $catalogId])) {
        unset($GLOBALS['ec_pricing_source_cache'][(int) $catalogId]);
        return;
    }
    $GLOBALS['ec_pricing_source_cache'] = array();
}

function product_extra_amount($product)
{
    if (!$product || !isset($product->extra_amount)) {
        return 0.0;
    }
    return round((float) $product->extra_amount, 2);
}

function product_listed_price_from_source($store, $source, $wholesale = null, $plus = null)
{
    $storeId = 0;
    if (is_object($store) && !empty($store->id)) {
        $storeId = (int) $store->id;
        if ($plus === null) {
            $plus = store_price_plus_amount($store);
        }
    } else {
        $storeId = (int) $store;
        if ($plus === null) {
            $plus = store_price_plus_amount($storeId);
        }
    }
    $wholesale = $wholesale === null ? product_wholesale_price($source, $storeId) : (float) $wholesale;
    $plus = (float) $plus;
    $catalogSell = isset($source->price) ? (float) $source->price : 0.0;
    $catalogBase = function_exists('product_base_price') ? product_base_price($source) : $catalogSell;
    $extraAmt = product_extra_amount($source);
    $price = round($wholesale + $plus + ($catalogSell - $catalogBase) + $extraAmt, 2);
    if ($extraAmt >= 0 && $price < $wholesale) {
        $price = $wholesale;
    }
    if ($price < 0) {
        $price = 0;
    }
    return $price;
}

function ensure_product_extra_amount_column()
{
    static $done = false;
    if ($done) {
        return;
    }
    $done = true;
    $CI =& get_instance();
    if (!$CI->db->table_exists('products') || $CI->db->field_exists('extra_amount', 'products')) {
        return;
    }
    $after = $CI->db->field_exists('cost_price', 'products') ? ' AFTER cost_price' : '';
    $CI->db->query('ALTER TABLE products ADD COLUMN extra_amount DECIMAL(12,2) NOT NULL DEFAULT 0' . $after);
}

function product_i18n_columns()
{
    return array(
        'name_en' => "ALTER TABLE products ADD COLUMN name_en VARCHAR(255) NOT NULL DEFAULT '' AFTER name",
        'short_details_en' => "ALTER TABLE products ADD COLUMN short_details_en MEDIUMTEXT NULL AFTER short_details",
        'details_en' => "ALTER TABLE products ADD COLUMN details_en MEDIUMTEXT NULL AFTER details",
        'seo_title_en' => "ALTER TABLE products ADD COLUMN seo_title_en VARCHAR(255) NOT NULL DEFAULT '' AFTER seo_title",
        'seo_description_en' => "ALTER TABLE products ADD COLUMN seo_description_en TEXT NULL AFTER seo_description",
        'seo_keywords_en' => "ALTER TABLE products ADD COLUMN seo_keywords_en VARCHAR(255) NOT NULL DEFAULT '' AFTER seo_keywords",
    );
}

function ensure_product_i18n_columns()
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
    foreach (product_i18n_columns() as $field => $sql) {
        if (!$CI->db->field_exists($field, 'products')) {
            $CI->db->query($sql);
        }
    }
}

function product_i18n_payload($source)
{
    ensure_product_i18n_columns();
    $out = array();
    foreach (array_keys(product_i18n_columns()) as $field) {
        $out[$field] = ($source && isset($source->$field)) ? $source->$field : '';
    }
    return $out;
}

function storefront_localize_product($product)
{
    if (!$product || !is_object($product)) {
        return $product;
    }
    if (!function_exists('storefront_ui_locale') || storefront_ui_locale() !== 'en') {
        return $product;
    }
    $map = array(
        'name' => 'name_en',
        'short_details' => 'short_details_en',
        'details' => 'details_en',
        'seo_title' => 'seo_title_en',
        'seo_description' => 'seo_description_en',
        'seo_keywords' => 'seo_keywords_en',
    );
    foreach ($map as $field => $enField) {
        if (!empty($product->$enField)) {
            $product->$field = $product->$enField;
        }
    }
    return $product;
}

function storefront_localize_products($items)
{
    if (is_object($items)) {
        return storefront_localize_product($items);
    }
    if (!is_array($items)) {
        return $items;
    }
    foreach ($items as $i => $item) {
        $items[$i] = storefront_localize_product($item);
    }
    return $items;
}

function storefront_sv_en_map()
{
    return array(
        'Bil & Fordon' => 'Automotive',
        'Baby & Barn' => 'Baby & Kids',
        'Skönhet & Personlig Vård' => 'Beauty & Personal Care',
        'Jul' => 'Christmas',
        'Elektronik & Teknik' => 'Electronics & Tech',
        'Mode & Kläder' => 'Fashion & Clothing',
        'Trädgård & Utomhus' => 'Garden & Outdoor Living',
        'Halloween & Högtidslek' => 'Halloween & Holiday Play',
        'Hem & Hushåll' => 'Home & Household',
        'Hem & Inredning' => 'Home & Living',
        'Hushåll & Vardag' => 'Household & Everyday',
        'Kök & Matplats' => 'Kitchen & Dining',
        'Livsstil & Hobby' => 'Lifestyle & Hobbies',
        'Kontor & Skola' => 'Office & School',
        'Övrigt' => 'Other',
        'Fest & Evenemang' => 'Party & Events',
        'Husdjur' => 'Pets',
        'Säsong & Högtider' => 'Seasonal & Holidays',
        'Sport & Fritid' => 'Sports & Outdoors',
        'System & Utveckling' => 'Systems & Development',
        'Verktyg & Gör-det-själv' => 'Tools & DIY',
        'Leksaker & Spel' => 'Toys & Games',
        'Resor & Livsstil' => 'Travel & Lifestyle',
        'Utveckling & Verktyg' => 'Development & Tools',
        'Ljud & Hörlurar' => 'Audio & Headphones',
        'Bärbar Elektronik' => 'Portable Electronics',
        'Bärbara Fläktar' => 'Portable Fans',
        'Bluetooth-högtalare' => 'Bluetooth Speakers',
        'Laddare & Kablar' => 'Chargers & Cables',
        'Datortillbehör' => 'Computer Accessories',
        'Gamingtillbehör' => 'Gaming Accessories',
        'LED & Belysning' => 'LED & Lighting',
        'Mobilaccessoarer' => 'Mobile Accessories',
        'Personlig Elektronik' => 'Personal Electronics',
        'Mobilskal' => 'Phone Cases',
        'Skärmskydd' => 'Screen Protectors',
        'Smarta Hem' => 'Smart Home',
        'Smartklockor & Wearables' => 'Smartwatches & Wearables',
        'Väskor & Ryggsäckar' => 'Bags & Backpacks',
        'Hattar & Kepsar' => 'Hats & Caps',
        'Barnkläder' => "Kids' Clothing",
        'Herrkläder' => "Men's Clothing",
        'Solglasögon' => 'Sunglasses',
        'Klockor' => 'Watches',
        'Damkläder' => "Women's Clothing",
        'Smycken' => 'Jewelry',
        'Skor' => 'Shoes',
        'Bad & Kropp' => 'Bath & Body',
        'Skönhetsverktyg' => 'Beauty Tools',
        'Parfym' => 'Fragrance',
        'Hårvård' => 'Hair Care',
        'Smink' => 'Makeup',
        'Nagelvård' => 'Nail Care',
        'Personlig Vård' => 'Personal Care',
        'Hudvård' => 'Skincare',
        'Sminkförvaring' => 'Makeup Storage',
        'Sängkläder' => 'Bedding',
        'Ljus & Ljushållare' => 'Candles & Holders',
        'Gardiner & Textilier' => 'Curtains & Textiles',
        'Möbler' => 'Furniture',
        'Hemaccessoarer' => 'Home Accessories',
        'Hemdekoration' => 'Home Decor',
        'Städredskap' => 'Cleaning Tools',
        'Städutrustning' => 'Cleaning Equipment',
        'Förvaring & Organisation' => 'Storage & Organization',
        'Väggdekoration' => 'Wall Art',
        'Bakredskap' => 'Bakeware',
        'Kaffe- & Tetillbehör' => 'Coffee & Tea Accessories',
        'Köksredskap & Kokutrustning' => 'Cookware',
        'Glas & Muggar' => 'Drinkware',
        'Matförvaring' => 'Food Storage',
        'Köksförvaring' => 'Kitchen Organization',
        'Köksverktyg' => 'Kitchen Tools',
        'Serviser' => 'Tableware',
        'Akvarietillbehör' => 'Aquarium Supplies',
        'Kattillbehör' => 'Cat Supplies',
        'Hundtillbehör' => 'Dog Supplies',
        'Husdjursbäddar' => 'Pet Beds',
        'Matning & Matskålar' => 'Pet Feeding',
        'Pälsvård & Grooming' => 'Pet Grooming',
        'Husdjursleksaker' => 'Pet Toys',
        'Husdjur på Resa' => 'Pet Travel',
        'Tillbehör för Smådjur' => 'Small Animal Supplies',
        'Sällskapsspel' => 'Board Games',
        'Byggleksaker' => 'Building Toys',
        'Dockor & Tillbehör' => 'Dolls & Accessories',
        'Pedagogiska Leksaker' => 'Educational Toys',
        'Barnkalas & Party' => "Kids' Party Toys",
        'Utomhusleksaker' => 'Outdoor Toys',
        'Gosedjur' => 'Plush Toys',
        'Pussel' => 'Puzzles',
        'Radiostyrda Leksaker' => 'Remote Control Toys',
        'Babybad' => 'Baby Bath',
        'Babyartiklar' => 'Baby Essentials',
        'Matning för Baby' => 'Baby Feeding',
        'Barnrumsprodukter' => 'Baby Room',
        'Barntillbehör' => "Kids' Accessories",
        'Reseprodukter för Barn' => "Kids' Travel",
        'Skolmaterial' => 'School Supplies',
        'Camping & Vandring' => 'Camping & Hiking',
        'Cykling' => 'Cycling',
        'Träning & Gym' => 'Fitness & Gym',
        'Friluftsliv' => 'Outdoor Recreation',
        'Löpning' => 'Running',
        'Sporttillbehör' => 'Sports Accessories',
        'Vattensport' => 'Water Sports',
        'Vintersport' => 'Winter Sports',
        'Yoga & Pilates' => 'Yoga & Pilates',
        'Biltillbehör' => 'Car Accessories',
        'Bilvård' => 'Car Cleaning',
        'Bilexteriör' => 'Car Exterior',
        'Bilinteriör' => 'Car Interior',
        'Fordonsbelysning' => 'Lighting',
        'Motorcykeltillbehör' => 'Motorcycle Accessories',
        'Mobilhållare för Bil' => 'Phone Holders',
        'Verktyg & Nödutrustning' => 'Tools & Emergency',
        'Trädgårdsverktyg' => 'Gardening Tools',
        'Handverktyg' => 'Hand Tools',
        'Beslag & Byggmaterial' => 'Hardware',
        'Mätverktyg' => 'Measuring Tools',
        'Måleritillbehör' => 'Painting Supplies',
        'Tillbehör för Elverktyg' => 'Power Tool Accessories',
        'Skyddsutrustning' => 'Safety Equipment',
        'Verkstadsförvaring' => 'Workshop Organization',
        'Grill & BBQ' => 'BBQ & Grilling',
        'Trädgårdsdekoration' => 'Garden Decor',
        'Utemöbler' => 'Outdoor Furniture',
        'Utomhusbelysning' => 'Outdoor Lighting',
        'Utomhusförvaring' => 'Outdoor Storage',
        'Uteplats & Balkong' => 'Patio & Balcony',
        'Växttillbehör' => 'Plant Accessories',
        'Ryggsäckar' => 'Backpacks',
        'Skrivbordstillbehör' => 'Desk Accessories',
        'Kontorsförvaring' => 'Office Organization',
        'Utskrift & Tillbehör' => 'Printing & Accessories',
        'Skrivmaterial' => 'Stationery',
        'Vardagsprodukter' => 'Everyday Carry',
        'Resväskor' => 'Luggage',
        'Nackkuddar & Sömn' => 'Neck Pillows & Sleep',
        'Friluftsliv & Livsstil' => 'Outdoor Lifestyle',
        'Resetillbehör' => 'Travel Accessories',
        'Reseorganisatörer' => 'Travel Organizers',
        'Juldekorationer' => 'Christmas Decorations',
        'Julklappar' => 'Christmas Gifts',
        'Julbelysning' => 'Christmas Lights',
        'Julparty & Fest' => 'Christmas Party Supplies',
        'Julstrumpor' => 'Christmas Stockings',
        'Juldukning' => 'Christmas Table Decor',
        'Julgranstillbehör' => 'Christmas Tree Accessories',
        'Halloweenkostymer' => 'Halloween Costumes',
        'Halloween Dekorationer' => 'Halloween Decorations',
        'Halloweenmasker' => 'Halloween Masks',
        'Halloweenparty & Fest' => 'Halloween Party Supplies',
        'Halloweenrekvisita' => 'Halloween Props',
        'Halloween för Barn' => 'Kids Halloween',
        'Festtillbehör' => 'Party Supplies',
        'Babyshower' => 'Baby Shower',
        'Ballonger' => 'Balloons',
        'Födelsedagsfest' => 'Birthday Party',
        'Kostymer & Rekvisita' => 'Costumes & Props',
        'Student & Examen' => 'Graduation',
        'Festdekoration' => 'Party Decorations',
        'Festdukning' => 'Party Tableware',
        'Bröllop' => 'Wedding',
        'Tillbaka till Skolan' => 'Back to School',
        'Påsk' => 'Easter',
        'Fars dag' => "Father's Day",
        'Mors dag' => "Mother's Day",
        'Nyår' => 'New Year',
        'Sommar' => 'Summer',
        'Alla hjärtans dag' => "Valentine's Day",
        'Vinter' => 'Winter',
        'Samlarprodukter' => 'Collectibles',
        'Hantverk & DIY' => 'Crafts & DIY',
        'Kreativt Material' => 'Creative Supplies',
        'Musiktillbehör' => 'Music Accessories',
        'Fototillbehör' => 'Photography Accessories',
        'Läsning & Boktillbehör' => 'Reading Accessories',
        'Underkläder' => 'Underwear & Lingerie',
        'Testprodukter' => 'Test Products',
        'Rengöring & Städning' => 'Cleaning',
        'Betalningssystemstest' => 'Payment System Test',
        'E-handelstestprodukter' => 'Ecommerce Test Products',
        'Generiska verktyg' => 'Generic Tools',
        'LED Projektionsleksaker' => 'LED Projection Toys',
        'Accessoarer' => 'Accessories',
        'Powerbanks' => 'Power Banks',
        'Väskor & Ryggsäckar' => 'Bags & Backpacks',
    );
}

function storefront_en_sv_menu_map()
{
    return array(
        'Terms' => 'Villkor',
        'Terms of Service' => 'Användarvillkor',
        'Privacy Policy' => 'Integritetspolicy',
        'About us' => 'Om oss',
        'About Us' => 'Om oss',
        'Contact' => 'Kontakt',
        'Contact us' => 'Kontakta oss',
        'Contact Us' => 'Kontakta oss',
        'Help' => 'Hjälp',
        'Shop' => 'Butik',
        'Home' => 'Hem',
    );
}

function ensure_nav_i18n()
{
    static $done = false;
    if ($done) {
        return;
    }
    $done = true;
    $CI =& get_instance();
    if (!$CI->db) {
        return;
    }
    ensure_header_menu_i18n_columns();
    ensure_category_english_names();
    ensure_header_menu_english_labels();
}

function ensure_header_menu_i18n_columns()
{
    $CI =& get_instance();
    if (!$CI->db->table_exists('store_header_menu')) {
        return;
    }
    if (!$CI->db->field_exists('label_en', 'store_header_menu')) {
        $CI->db->query("ALTER TABLE store_header_menu ADD COLUMN label_en VARCHAR(120) NOT NULL DEFAULT '' AFTER label");
    }
}

function ensure_category_english_names()
{
    static $done = false;
    if ($done) {
        return;
    }
    $done = true;
    $CI =& get_instance();
    if (!$CI->db->table_exists('categories') || !$CI->db->field_exists('local_name', 'categories')) {
        return;
    }
    $map = storefront_sv_en_map();
    $rows = $CI->db->select('id, name, local_name')->from('categories')->get()->result();
    foreach ($rows as $row) {
        $name = trim((string) $row->name);
        $local = trim((string) $row->local_name);
        $payload = array();
        if ($local === '' && isset($map[$name])) {
            $payload['local_name'] = $name;
            $payload['name'] = $map[$name];
        } elseif ($local !== '' && $name === $local && isset($map[$name])) {
            $payload['name'] = $map[$name];
        } elseif ($local !== '' && isset($map[$local]) && ($name === '' || $name === $local)) {
            $payload['name'] = $map[$local];
        }
        if ($payload) {
            $CI->db->where('id', (int) $row->id)->update('categories', $payload);
        }
    }
}

function ensure_header_menu_english_labels()
{
    static $done = false;
    if ($done) {
        return;
    }
    $done = true;
    $CI =& get_instance();
    if (!$CI->db->table_exists('store_header_menu') || !$CI->db->field_exists('label_en', 'store_header_menu')) {
        return;
    }
    $enToSv = storefront_en_sv_menu_map();
    $svToEn = array_flip($enToSv);
    $rows = $CI->db->select('id, label, label_en')->from('store_header_menu')->get()->result();
    foreach ($rows as $row) {
        $label = trim((string) $row->label);
        $en = trim((string) $row->label_en);
        if ($label === '') {
            continue;
        }
        $payload = array();
        if ($en === '' && isset($enToSv[$label])) {
            $payload['label_en'] = $label;
            $payload['label'] = $enToSv[$label];
        } elseif ($en === '' && isset($svToEn[$label])) {
            $payload['label_en'] = $svToEn[$label];
        }
        if ($payload) {
            $CI->db->where('id', (int) $row->id)->update('store_header_menu', $payload);
        }
    }
}

function ec_apply_catalog_extra_amount($catalogId, $extraAmount)
{
    ensure_product_extra_amount_column();
    $CI =& get_instance();
    $catalogId = (int) $catalogId;
    $extraAmount = round((float) $extraAmount, 2);
    if ($catalogId < 1 || !$CI->db->table_exists('products')) {
        return null;
    }
    $CI->db->where('id', $catalogId)->update('products', array('extra_amount' => $extraAmount));
    if ($CI->db->field_exists('source_product_id', 'products')) {
        $CI->db
            ->where('source_product_id', $catalogId)
            ->where('store_id IS NOT NULL', null, false)
            ->where('store_id !=', 0)
            ->update('products', array('extra_amount' => $extraAmount));
    }
    storefront_pricing_source_reset($catalogId);
    $source = $CI->db->where('id', $catalogId)->get('products')->row();
    if (!$source) {
        return null;
    }
    $CI->load->model('store/Store_product_model');
    $copies = $CI->db
        ->where('source_product_id', $catalogId)
        ->where('store_id >', 0)
        ->get('products')
        ->result();
    foreach ($copies as $copy) {
        $store = $CI->db->where('id', (int) $copy->store_id)->get('stores')->row();
        if ($store) {
            $CI->Store_product_model->sync_copy_pricing($store, $source, $copy);
        }
    }
    return $source;
}

function storefront_admin_price_breakdown($listing, $store = null)
{
    if (!$listing) {
        return null;
    }
    ensure_product_extra_amount_column();
    $storeId = 0;
    if (is_object($store) && !empty($store->id)) {
        $storeId = (int) $store->id;
    } elseif (!empty($listing->store_id)) {
        $storeId = (int) $listing->store_id;
    }
    $source = storefront_pricing_source($listing);
    if (!$source) {
        $source = $listing;
    }
    $rates = product_owner_commission_rates($source);
    $base = product_base_price($source);
    $commission = product_commission_amount($source);
    $feePct = platform_fee_percent();
    $platformFee = product_platform_fee($storeId, $source);
    $wholesale = round($base + $commission + $platformFee, 2);
    $plus = store_price_plus_amount($store !== null ? $store : $storeId);
    $catalogSell = isset($source->price) ? (float) $source->price : $base;
    $catalogDelta = round($catalogSell - $base, 2);
    $extraAmt = product_extra_amount($source);
    $formula = round($wholesale + $plus + $catalogDelta + $extraAmt, 2);
    if ($extraAmt >= 0 && $formula < $wholesale) {
        $formula = $wholesale;
    }
    if ($formula < 0) {
        $formula = 0;
    }
    $listed = isset($listing->price) ? (float) $listing->price : $formula;
    $currency = store_currency($store !== null ? $store : $storeId);
    $pct = function ($n) {
        return rtrim(rtrim(number_format((float) $n, 2, '.', ''), '0'), '.');
    };
    $money = function ($n) use ($currency) {
        return format_money((float) $n, $currency);
    };
    $commLabel = 'Ecommerce plus';
    if ($rates['percent'] > 0 && $rates['flat'] > 0) {
        $commLabel .= ' (' . $pct($rates['percent']) . '% + ' . number_format($rates['flat'], 2, '.', '') . ')';
    } elseif ($rates['percent'] > 0) {
        $commLabel .= ' (' . $pct($rates['percent']) . '%)';
    } elseif ($rates['flat'] > 0) {
        $commLabel .= ' (flat)';
    }
    $rows = array(
        array('key' => 'cost', 'label' => 'Base price', 'amount' => round($base, 2)),
        array('key' => 'commission', 'label' => $commLabel, 'amount' => round($commission, 2)),
        array('key' => 'platform', 'label' => 'Platform fee (' . $pct($feePct) . '%)', 'amount' => round($platformFee, 2)),
    );
    $parts = array($base, $commission, $platformFee);
    if (abs($catalogDelta) >= 0.005) {
        $rows[] = array('key' => 'catalog_delta', 'label' => 'Catalog selling vs cost', 'amount' => $catalogDelta);
        $parts[] = $catalogDelta;
    }
    $rows[] = array('key' => 'plus', 'label' => 'Store plus', 'amount' => round($plus, 2));
    $parts[] = $plus;
    $rows[] = array('key' => 'extra', 'label' => 'Extra amount', 'amount' => round($extraAmt, 2));
    $parts[] = $extraAmt;
    $adjust = round($listed - $formula, 2);
    if (abs($adjust) >= 0.005) {
        $rows[] = array('key' => 'adjust', 'label' => 'Listing adjustment', 'amount' => $adjust);
        $parts[] = $adjust;
    }
    $rows[] = array('key' => 'listed', 'label' => 'Selling price', 'amount' => round($listed, 2), 'total' => true);
    foreach ($rows as $i => $row) {
        $rows[$i]['amount_formatted'] = $money($row['amount']);
    }
    $bits = array();
    foreach ($parts as $i => $n) {
        $n = (float) $n;
        $abs = number_format(abs($n), 2, '.', '');
        if ($i === 0) {
            $bits[] = ($n < 0 ? '−' : '') . $abs;
        } elseif ($n < 0) {
            $bits[] = '− ' . $abs;
        } else {
            $bits[] = '+ ' . $abs;
        }
    }
    $catalogId = !empty($listing->source_product_id) ? (int) $listing->source_product_id : (int) $listing->id;
    $formulaText = implode(' ', $bits) . ' = ' . number_format($listed, 2, '.', '');
    return array(
        'product_id' => (int) $listing->id,
        'catalog_id' => $catalogId,
        'name' => isset($listing->name) ? (string) $listing->name : '',
        'rows' => $rows,
        'formula' => $formulaText,
        'listed' => round($listed, 2),
        'listed_formatted' => $money($listed),
        'wholesale' => $wholesale,
        'plus' => round($plus, 2),
        'extra_amount' => round($extraAmt, 2),
        'save_url' => function_exists('storefront_url') ? storefront_url('admin_bar/extra_amount') : site_url('admin_bar/extra_amount'),
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
    ensure_sweden_store_whatsapp();
}

/**
 * Keep ZENvello Sweden store-keeper WhatsApp for order notifications.
 * Digits only (no + / spaces) so dial-code normalization does not rewrite it.
 */
function ensure_sweden_store_whatsapp()
{
    static $done = false;
    if ($done) {
        return;
    }
    $done = true;
    $CI =& get_instance();
    if (!$CI->db->table_exists('stores') || !$CI->db->table_exists('store_settings')) {
        return;
    }
    $phone = '923437128470';
    $store = $CI->db
        ->group_start()
            ->where('domain', 'zenvello.se')
            ->or_where('domain', 'www.zenvello.se')
            ->or_like('domain', 'zenvello.se', 'both')
        ->group_end()
        ->order_by('id', 'ASC')
        ->limit(1)
        ->get('stores')
        ->row();
    if (!$store) {
        return;
    }
    $storeId = (int) $store->id;
    if (!isset($CI->Store_settings_model)) {
        if (is_file(APPPATH . 'modules/store/models/Store_settings_model.php')) {
            $CI->load->model('store/Store_settings_model');
        }
    }
    if (isset($CI->Store_settings_model) && method_exists($CI->Store_settings_model, 'set')) {
        $current = method_exists($CI->Store_settings_model, 'get')
            ? trim((string) $CI->Store_settings_model->get($storeId, 'whatsapp_number', ''))
            : '';
        if (preg_replace('/\D+/', '', $current) === $phone) {
            return;
        }
        $CI->Store_settings_model->set($storeId, 'whatsapp_number', $phone);
        return;
    }

    $keyCol = $CI->db->field_exists('field_key', 'store_settings') ? 'field_key' : 'setting_key';
    $valCol = $keyCol === 'field_key' ? 'field_value' : 'setting_value';
    $existing = $CI->db->where('store_id', $storeId)->where($keyCol, 'whatsapp_number')->get('store_settings')->row_array();
    if ($existing) {
        $current = isset($existing[$valCol]) ? preg_replace('/\D+/', '', (string) $existing[$valCol]) : '';
        if ($current === $phone) {
            return;
        }
        $CI->db->where('id', (int) $existing['id'])->update('store_settings', array($valCol => $phone));
        return;
    }
    $payload = array(
        'store_id' => $storeId,
        $keyCol => 'whatsapp_number',
        $valCol => $phone,
    );
    if ($CI->db->field_exists('theme_id', 'store_settings')) {
        $payload['theme_id'] = 0;
    }
    $CI->db->insert('store_settings', $payload);
}

function ensure_customer_address_columns()
{
    static $done = false;
    if ($done) {
        return;
    }
    $done = true;
    $CI =& get_instance();
    if ($CI->db->table_exists('store_customers')) {
        $cols = array(
            'first_name' => "VARCHAR(80) NOT NULL DEFAULT ''",
            'last_name' => "VARCHAR(80) NOT NULL DEFAULT ''",
            'street' => "VARCHAR(190) NOT NULL DEFAULT ''",
            'apartment' => "VARCHAR(80) NOT NULL DEFAULT ''",
            'postcode' => "VARCHAR(20) NOT NULL DEFAULT ''",
            'city' => "VARCHAR(80) NOT NULL DEFAULT ''",
            'country' => "VARCHAR(80) NOT NULL DEFAULT ''",
            'billing_same' => "TINYINT(1) NOT NULL DEFAULT 1",
            'billing_street' => "VARCHAR(190) NOT NULL DEFAULT ''",
            'billing_apartment' => "VARCHAR(80) NOT NULL DEFAULT ''",
            'billing_postcode' => "VARCHAR(20) NOT NULL DEFAULT ''",
            'billing_city' => "VARCHAR(80) NOT NULL DEFAULT ''",
            'billing_country' => "VARCHAR(80) NOT NULL DEFAULT ''",
        );
        foreach ($cols as $name => $def) {
            if (!$CI->db->field_exists($name, 'store_customers')) {
                $CI->db->query('ALTER TABLE store_customers ADD COLUMN ' . $name . ' ' . $def);
            }
        }
    }
    if ($CI->db->table_exists('store_orders') && !$CI->db->field_exists('billing_address', 'store_orders')) {
        $CI->db->query('ALTER TABLE store_orders ADD COLUMN billing_address TEXT NULL');
    }
}

function storefront_default_country($store = null)
{
    $name = '';
    if (is_object($store) && !empty($store->country_name)) {
        $name = trim((string) $store->country_name);
    }
    return $name !== '' ? $name : 'Sweden';
}

function storefront_country_options()
{
    $CI =& get_instance();
    if (!$CI->db->table_exists('countries')) {
        return array();
    }
    return $CI->db->order_by('name', 'asc')->get('countries')->result();
}

function customer_address_blank($defaultCountry = '')
{
    return array(
        'first_name' => '',
        'last_name' => '',
        'email' => '',
        'phone' => '',
        'street' => '',
        'apartment' => '',
        'postcode' => '',
        'city' => '',
        'country' => $defaultCountry,
        'billing_same' => 1,
        'billing_street' => '',
        'billing_apartment' => '',
        'billing_postcode' => '',
        'billing_city' => '',
        'billing_country' => $defaultCountry,
    );
}

function customer_address_from_row($customer, $defaultCountry = '')
{
    $addr = customer_address_blank($defaultCountry);
    if (!$customer) {
        return $addr;
    }
    $addr['email'] = isset($customer->email) ? (string) $customer->email : '';
    $addr['phone'] = isset($customer->phone) ? (string) $customer->phone : '';
    $addr['first_name'] = isset($customer->first_name) ? trim((string) $customer->first_name) : '';
    $addr['last_name'] = isset($customer->last_name) ? trim((string) $customer->last_name) : '';
    if ($addr['first_name'] === '' && $addr['last_name'] === '' && !empty($customer->name)) {
        $parts = preg_split('/\s+/', trim((string) $customer->name), 2);
        $addr['first_name'] = isset($parts[0]) ? $parts[0] : '';
        $addr['last_name'] = isset($parts[1]) ? $parts[1] : '';
    }
    foreach (array('street', 'apartment', 'postcode', 'city', 'billing_street', 'billing_apartment', 'billing_postcode', 'billing_city', 'billing_country') as $key) {
        if (isset($customer->$key)) {
            $addr[$key] = trim((string) $customer->$key);
        }
    }
    if (!empty($customer->country) && trim((string) $customer->country) !== '') {
        $addr['country'] = trim((string) $customer->country);
    }
    if ($addr['street'] === '' && !empty($customer->address)) {
        $addr['street'] = trim((string) $customer->address);
    }
    $addr['billing_same'] = !isset($customer->billing_same) || (int) $customer->billing_same === 1 ? 1 : 0;
    if ($addr['billing_country'] === '') {
        $addr['billing_country'] = $addr['country'];
    }
    return $addr;
}

function customer_address_from_post($defaultCountry = '')
{
    $CI =& get_instance();
    $addr = customer_address_blank($defaultCountry);
    foreach (array('first_name', 'last_name', 'email', 'phone', 'street', 'apartment', 'postcode', 'city', 'country', 'billing_street', 'billing_apartment', 'billing_postcode', 'billing_city', 'billing_country') as $key) {
        $addr[$key] = trim((string) $CI->input->post($key));
    }
    $addr['email'] = strtolower($addr['email']);
    $addr['billing_same'] = $CI->input->post('billing_same') ? 1 : 0;
    if ($addr['country'] === '') {
        $addr['country'] = $defaultCountry;
    }
    if ($addr['billing_same']) {
        $addr['billing_street'] = $addr['street'];
        $addr['billing_apartment'] = $addr['apartment'];
        $addr['billing_postcode'] = $addr['postcode'];
        $addr['billing_city'] = $addr['city'];
        $addr['billing_country'] = $addr['country'];
    } elseif ($addr['billing_country'] === '') {
        $addr['billing_country'] = $defaultCountry;
    }
    return $addr;
}

function customer_address_missing($addr, $requireTerms = false)
{
    foreach (array('first_name', 'last_name', 'email', 'phone', 'street', 'postcode', 'city', 'country') as $key) {
        if (!isset($addr[$key]) || trim((string) $addr[$key]) === '') {
            return true;
        }
    }
    if (empty($addr['billing_same'])) {
        foreach (array('billing_street', 'billing_postcode', 'billing_city', 'billing_country') as $key) {
            if (!isset($addr[$key]) || trim((string) $addr[$key]) === '') {
                return true;
            }
        }
    }
    if ($requireTerms) {
        $CI =& get_instance();
        if (!$CI->input->post('terms')) {
            return true;
        }
    }
    return false;
}

function customer_format_address_block($street, $apartment, $postcode, $city, $country)
{
    $lines = array();
    $street = trim((string) $street);
    $apartment = trim((string) $apartment);
    $postcode = trim((string) $postcode);
    $city = trim((string) $city);
    $country = trim((string) $country);
    if ($street !== '') {
        $lines[] = $street;
    }
    if ($apartment !== '') {
        $lines[] = $apartment;
    }
    $cityLine = trim($postcode . ' ' . $city);
    if ($cityLine !== '') {
        $lines[] = $cityLine;
    }
    if ($country !== '') {
        $lines[] = $country;
    }
    return implode("\n", $lines);
}

function customer_address_save_payload($addr)
{
    return array(
        'name' => trim($addr['first_name'] . ' ' . $addr['last_name']),
        'email' => $addr['email'],
        'phone' => $addr['phone'],
        'address' => customer_format_address_block($addr['street'], $addr['apartment'], $addr['postcode'], $addr['city'], $addr['country']),
        'first_name' => $addr['first_name'],
        'last_name' => $addr['last_name'],
        'street' => $addr['street'],
        'apartment' => $addr['apartment'],
        'postcode' => $addr['postcode'],
        'city' => $addr['city'],
        'country' => $addr['country'],
        'billing_same' => !empty($addr['billing_same']) ? 1 : 0,
        'billing_street' => $addr['billing_street'],
        'billing_apartment' => $addr['billing_apartment'],
        'billing_postcode' => $addr['billing_postcode'],
        'billing_city' => $addr['billing_city'],
        'billing_country' => $addr['billing_country'],
    );
}

function customer_shipping_for_order($addr)
{
    $payload = customer_address_save_payload($addr);
    $billing = '';
    if (empty($addr['billing_same'])) {
        $billing = customer_format_address_block($addr['billing_street'], $addr['billing_apartment'], $addr['billing_postcode'], $addr['billing_city'], $addr['billing_country']);
    }
    return array(
        'name' => $payload['name'],
        'email' => $payload['email'],
        'phone' => $payload['phone'],
        'address' => $payload['address'],
        'billing_address' => $billing,
    );
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
    if (is_object($store) && isset($store->price_plus_amount) && $store->price_plus_amount !== null && $store->price_plus_amount !== '') {
        return max(0, (float) $store->price_plus_amount);
    }
    $storeId = 0;
    if (is_object($store) && !empty($store->id)) {
        $storeId = (int) $store->id;
    } elseif (is_numeric($store)) {
        $storeId = (int) $store;
    }
    if ($storeId < 1) {
        return 0.0;
    }
    $CI =& get_instance();
    $CI->db->reset_query();
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

function ec_add_catalog_to_all_country_stores($productId, $mark = false)
{
    $productId = (int) $productId;
    if ($productId < 1) {
        return 0;
    }
    if ($mark) {
        ec_auto_add_catalog_product($productId, true);
    }
    $model = ec_load_store_product_model();
    if (!$model || !method_exists($model, 'add_catalog_to_all_country_stores')) {
        return 0;
    }
    return $model->add_catalog_to_all_country_stores($productId);
}

function ec_sync_catalog_family_to_stores($productId)
{
    $productId = (int) $productId;
    if ($productId < 1) {
        return 0;
    }
    $model = ec_load_store_product_model();
    if (!$model || !method_exists($model, 'sync_family_to_stores')) {
        return 0;
    }
    return (int) $model->sync_family_to_stores($productId);
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

function store_front_settings($storeId = 0)
{
    $storeId = (int) $storeId;
    $CI =& get_instance();
    if (isset($CI->tenant) && method_exists($CI->tenant, 'get_store') && method_exists($CI->tenant, 'get_settings')) {
        $store = $CI->tenant->get_store();
        if ($store && ($storeId < 1 || (int) $store->id === $storeId)) {
            $settings = $CI->tenant->get_settings();
            if (is_array($settings) && $settings) {
                return $settings;
            }
        }
    }
    $settings = array();
    if ($storeId > 0 && $CI->db && $CI->db->table_exists('store_settings')) {
        $keyCol = $CI->db->field_exists('field_key', 'store_settings') ? 'field_key' : 'setting_key';
        $valCol = $keyCol === 'field_key' ? 'field_value' : 'setting_value';
        $rows = $CI->db->where('store_id', $storeId)->get('store_settings')->result_array();
        foreach ($rows as $row) {
            if (isset($row[$keyCol])) {
                $settings[$row[$keyCol]] = isset($row[$valCol]) ? $row[$valCol] : '';
            }
        }
    }
    return $settings;
}

function cart_qty_total($items)
{
    $qty = 0;
    if (!is_array($items)) {
        return 0;
    }
    foreach ($items as $item) {
        if (is_object($item) && isset($item->qty)) {
            $qty += max(0, (int) $item->qty);
        } elseif (is_array($item) && isset($item['qty'])) {
            $qty += max(0, (int) $item['qty']);
        }
    }
    return $qty;
}

function platform_shipping_per_item()
{
    return max(0.0, round((float) platform_setting('shipping_per_item', 0), 2));
}

function platform_shipping_discount_on()
{
    $value = strtolower(trim((string) platform_setting('shipping_discount_enabled', '0')));
    return in_array($value, array('1', 'true', 'on', 'yes'), true);
}

function platform_shipping_free_min()
{
    return max(0.0, round((float) platform_setting('shipping_free_min', 0), 2));
}

function cart_shipping_is_free($subtotal)
{
    if (!platform_shipping_discount_on()) {
        return false;
    }
    $min = platform_shipping_free_min();
    return $min > 0 && ((float) $subtotal + 0.0001) >= $min;
}

function store_shipping_flat_rate($settings = array())
{
    return platform_shipping_per_item();
}

function cart_shipping_amount($settings, $qty, $subtotal = 0, $items = null)
{
    $qty = max(0, (int) $qty);
    $rate = platform_shipping_per_item();
    if ($rate <= 0) {
        return 0.0;
    }

    // Per-product free shipping: only bill qty for lines without free-shipping offer.
    // Does not waive shipping for unrelated cart lines.
    if (is_array($items)) {
        $billableQty = 0;
        foreach ($items as $item) {
            $lineQty = isset($item->qty) ? max(0, (int) $item->qty) : 0;
            if ($lineQty < 1) {
                continue;
            }
            $free = function_exists('product_offer_has_free_shipping')
                ? product_offer_has_free_shipping($item, $lineQty)
                : false;
            if (!$free) {
                $billableQty += $lineQty;
            }
        }
        $qty = $billableQty;
    }

    if ($qty < 1) {
        return 0.0;
    }
    if (cart_shipping_is_free($subtotal)) {
        return 0.0;
    }
    return round($rate * $qty, 2);
}

function cart_show_shipping($settings = array(), $amount = 0)
{
    if ((float) $amount > 0) {
        return true;
    }
    return platform_shipping_per_item() > 0;
}

function cart_total_payable($subtotal, $shipping = 0)
{
    return round(((float) $subtotal) + cart_vat_amount($subtotal) + (float) $shipping, 2);
}

/**
 * Safe default ETA for AliExpress when the supplier window is unknown.
 * Used by importer fallback and bulk delivery updates — never shorter than this.
 */
function aliexpress_default_ship_days()
{
    return array('min' => 7, 'max' => 15);
}

/**
 * Apply a customer-facing safety buffer to a supplier ETA.
 * Never promises earlier than the supplier min, and never shortens the supplier max.
 * Optional buffer only extends the max (e.g. 6–14 → 6–15).
 */
function aliexpress_customer_ship_days($min, $max, $extendMaxBy = 0)
{
    $min = (int) $min;
    $max = (int) $max;
    if ($min <= 0 && $max <= 0) {
        return aliexpress_default_ship_days();
    }
    if ($min <= 0) {
        $min = $max;
    }
    if ($max <= 0) {
        $max = $min;
    }
    if ($min > $max) {
        $tmp = $min;
        $min = $max;
        $max = $tmp;
    }
    $extendMaxBy = max(0, (int) $extendMaxBy);
    $max = $max + $extendMaxBy;
    $min = max(1, min(60, $min));
    $max = max($min, min(60, $max));
    return array('min' => $min, 'max' => $max);
}

function is_aliexpress_product($product)
{
    if (!$product) {
        return false;
    }
    $url = '';
    if (is_object($product)) {
        if (!empty($product->source_url)) {
            $url = (string) $product->source_url;
        }
        if ($url === '' && !empty($product->sku) && stripos((string) $product->sku, 'AE') === 0) {
            return true;
        }
        if (!empty($product->supplier_name) && stripos((string) $product->supplier_name, 'aliexpress') !== false) {
            return true;
        }
    } elseif (is_array($product)) {
        $url = isset($product['source_url']) ? (string) $product['source_url'] : '';
        if ($url === '' && !empty($product['sku']) && stripos((string) $product['sku'], 'AE') === 0) {
            return true;
        }
    }
    return $url !== '' && stripos($url, 'aliexpress.') !== false;
}

/**
 * Whether an existing AE ship window should be replaced by the safe default.
 * Keeps scraped / verified ranges (including faster EU/local windows).
 * Replaces missing (0/0) and the old importer fallback (7/9).
 */
function aliexpress_ship_days_need_safe_default($min, $max)
{
    $min = (int) $min;
    $max = (int) $max;
    if ($min <= 0 && $max <= 0) {
        return true;
    }
    if ($min === 7 && $max === 9) {
        return true;
    }
    return false;
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
        // AliExpress without stored ETA: show safe Sweden default dynamically.
        if (is_aliexpress_product($product)) {
            return aliexpress_default_ship_days();
        }
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

function storefront_shipping_timezone()
{
    $tzName = '';
    $store = null;
    if (function_exists('storefront_ui_context')) {
        list($store) = storefront_ui_context();
    }
    $localeSv = function_exists('storefront_ui_locale') && storefront_ui_locale() === 'sv';
    $countrySe = $store && !empty($store->country_code) && strtoupper((string) $store->country_code) === 'SE';
    if ($localeSv || $countrySe) {
        $tzName = 'Europe/Stockholm';
    } elseif ($store && !empty($store->timezone)) {
        $tzName = (string) $store->timezone;
    }
    if ($tzName === '') {
        $tzName = date_default_timezone_get() ?: 'UTC';
    }
    try {
        return new DateTimeZone($tzName);
    } catch (Exception $e) {
        return new DateTimeZone('UTC');
    }
}

function storefront_easter_sunday($year)
{
    $year = (int) $year;
    $a = $year % 19;
    $b = (int) floor($year / 100);
    $c = $year % 100;
    $d = (int) floor($b / 4);
    $e = $b % 4;
    $f = (int) floor(($b + 8) / 25);
    $g = (int) floor(($b - $f + 1) / 3);
    $h = (19 * $a + $b - $d - $g + 15) % 30;
    $i = (int) floor($c / 4);
    $k = $c % 4;
    $l = (32 + 2 * $e + 2 * $i - $h - $k) % 7;
    $m = (int) floor(($a + 11 * $h + 22 * $l) / 451);
    $month = (int) floor(($h + $l - 7 * $m + 114) / 31);
    $day = (($h + $l - 7 * $m + 114) % 31) + 1;
    return sprintf('%04d-%02d-%02d', $year, $month, $day);
}

function storefront_sweden_holidays($year)
{
    $year = (int) $year;
    $days = array(
        $year . '-01-01' => true,
        $year . '-01-06' => true,
        $year . '-05-01' => true,
        $year . '-06-06' => true,
        $year . '-12-24' => true,
        $year . '-12-25' => true,
        $year . '-12-26' => true,
        $year . '-12-31' => true,
    );
    try {
        $easter = new DateTime(storefront_easter_sunday($year));
        $goodFriday = clone $easter;
        $goodFriday->modify('-2 days');
        $easterMonday = clone $easter;
        $easterMonday->modify('+1 day');
        $ascension = clone $easter;
        $ascension->modify('+39 days');
        $whitSunday = clone $easter;
        $whitSunday->modify('+49 days');
        $days[$goodFriday->format('Y-m-d')] = true;
        $days[$easter->format('Y-m-d')] = true;
        $days[$easterMonday->format('Y-m-d')] = true;
        $days[$ascension->format('Y-m-d')] = true;
        $days[$whitSunday->format('Y-m-d')] = true;
    } catch (Exception $e) {
    }
    try {
        $midsummer = new DateTime($year . '-06-19');
        while ((int) $midsummer->format('N') !== 5) {
            $midsummer->modify('+1 day');
        }
        $days[$midsummer->format('Y-m-d')] = true;
    } catch (Exception $e) {
    }
    return $days;
}

function storefront_is_working_day($dt)
{
    if (!($dt instanceof DateTimeInterface)) {
        return false;
    }
    if ((int) $dt->format('N') >= 6) {
        return false;
    }
    $useSwedish = function_exists('storefront_ui_locale') && storefront_ui_locale() === 'sv';
    if (!$useSwedish) {
        list($store) = function_exists('storefront_ui_context') ? storefront_ui_context() : array(null);
        $useSwedish = $store && !empty($store->country_code) && strtoupper((string) $store->country_code) === 'SE';
    }
    if ($useSwedish) {
        static $holidays = array();
        $year = (int) $dt->format('Y');
        if (!isset($holidays[$year])) {
            $holidays[$year] = storefront_sweden_holidays($year);
        }
        if (isset($holidays[$year][$dt->format('Y-m-d')])) {
            return false;
        }
    }
    return true;
}

function storefront_add_working_days($start, $days)
{
    $days = max(0, (int) $days);
    $cursor = clone $start;
    $added = 0;
    while ($added < $days) {
        $cursor->modify('+1 day');
        if (storefront_is_working_day($cursor)) {
            $added++;
        }
    }
    return $cursor;
}

function product_delivery_working_label($min, $max)
{
    $min = (int) $min;
    $max = (int) $max;
    if ($min === $max) {
        if (function_exists('storefront_ui_count')) {
            return storefront_ui_count('product.eta_working_one', 'product.eta_working', $min);
        }
        return $min === 1 ? ('Delivery within ' . $min . ' working day') : ('Delivery within ' . $min . ' working days');
    }
    if (function_exists('store_ui')) {
        return store_ui('product.eta_working_range', array('{min}' => (string) $min, '{max}' => (string) $max));
    }
    return 'Delivery within ' . $min . '–' . $max . ' working days';
}

function product_delivery_window($product, $fromDate = null)
{
    $days = product_ship_days($product);
    if (!$days) {
        return null;
    }
    $tz = storefront_shipping_timezone();
    try {
        $start = $fromDate ? new DateTime($fromDate, $tz) : new DateTime('today', $tz);
    } catch (Exception $e) {
        $start = new DateTime('today', $tz);
    }
    $from = storefront_add_working_days($start, $days['min']);
    $to = storefront_add_working_days($start, $days['max']);
    $crossYear = $from->format('Y') !== $to->format('Y');
    $fromLabel = function_exists('storefront_date_label') ? storefront_date_label($from, $crossYear) : $from->format($crossYear ? 'j M Y' : 'j M');
    $toLabel = function_exists('storefront_date_label') ? storefront_date_label($to, $crossYear) : $to->format($crossYear ? 'j M Y' : 'j M');
    $working = product_delivery_working_label($days['min'], $days['max']);
    if ($days['min'] === $days['max']) {
        $text = function_exists('store_ui') ? store_ui('product.arrive_on', array('{date}' => $fromLabel)) : ('This product will arrive on ' . $fromLabel . '.');
        $short = $fromLabel;
    } else {
        $text = function_exists('store_ui') ? store_ui('product.arrive_between', array('{from}' => $fromLabel, '{to}' => $toLabel)) : ('This product will arrive between ' . $fromLabel . ' and ' . $toLabel . '.');
        $short = $fromLabel . ' – ' . $toLabel;
    }
    $eta = function_exists('store_ui') ? store_ui('product.eta', array('{dates}' => $short)) : ('Expected delivery: ' . $short);
    $note = function_exists('store_ui') ? store_ui('product.eta_vary') : 'Delivery times may vary depending on the shipping method and destination.';
    return array(
        'from' => $from,
        'to' => $to,
        'from_label' => $fromLabel,
        'to_label' => $toLabel,
        'text' => $text,
        'short' => $short,
        'working' => $working,
        'eta' => $eta,
        'note' => $note,
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

function product_short_details_html($product)
{
    if (!$product) {
        return '';
    }
    $raw = isset($product->short_details) ? trim((string) $product->short_details) : '';
    if ($raw !== '') {
        return ec_sanitize_product_html($raw);
    }
    return '';
}

function storefront_display_discount_config($storeId = 0)
{
    static $cache = array();
    $storeId = (int) $storeId;
    if (isset($cache[$storeId])) {
        return $cache[$storeId];
    }
    $empty = array(
        'percent' => 0,
        'scope' => 'store',
        'category_id' => 0,
        'allowed' => array(),
    );
    if ($storeId < 1) {
        return $cache[$storeId] = $empty;
    }
    $CI =& get_instance();
    $settings = array();
    if (isset($CI->tenant) && method_exists($CI->tenant, 'get_store') && method_exists($CI->tenant, 'get_settings')) {
        $store = $CI->tenant->get_store();
        if ($store && (int) $store->id === $storeId) {
            $settings = $CI->tenant->get_settings();
        }
    }
    if (!is_array($settings) || !$settings) {
        if ($CI->db && $CI->db->table_exists('store_settings')) {
            $keyCol = $CI->db->field_exists('field_key', 'store_settings') ? 'field_key' : 'setting_key';
            $valCol = $keyCol === 'field_key' ? 'field_value' : 'setting_value';
            $rows = $CI->db->where('store_id', $storeId)->get('store_settings')->result_array();
            foreach ($rows as $row) {
                if (isset($row[$keyCol])) {
                    $settings[$row[$keyCol]] = isset($row[$valCol]) ? $row[$valCol] : '';
                }
            }
        }
    }
    $enabled = !empty($settings['discount_enabled']) && (string) $settings['discount_enabled'] !== '0';
    $percent = (float) (isset($settings['discount_percent']) ? $settings['discount_percent'] : 0);
    if (!$enabled || $percent < 0.01) {
        return $cache[$storeId] = $empty;
    }
    if ($percent > 90) {
        $percent = 90;
    }
    $scope = isset($settings['discount_scope']) ? strtolower(trim((string) $settings['discount_scope'])) : 'store';
    if (!in_array($scope, array('store', 'category', 'subcategory'), true)) {
        $scope = 'store';
    }
    $categoryId = (int) (isset($settings['discount_category_id']) ? $settings['discount_category_id'] : 0);
    $allowed = array();
    if ($scope !== 'store') {
        if ($categoryId < 1) {
            return $cache[$storeId] = $empty;
        }
        $allowed[$categoryId] = true;
        if ($scope === 'category' && $CI->db && $CI->db->table_exists('categories')) {
            $kids = $CI->db->select('id')->where('parent_id', $categoryId)->get('categories')->result();
            foreach ($kids as $kid) {
                $allowed[(int) $kid->id] = true;
            }
        }
    }
    return $cache[$storeId] = array(
        'percent' => $percent,
        'scope' => $scope,
        'category_id' => $categoryId,
        'allowed' => $allowed,
    );
}

function storefront_product_category_map($productIds)
{
    $map = array();
    $ids = array();
    foreach ((array) $productIds as $id) {
        $id = (int) $id;
        if ($id > 0) {
            $ids[$id] = $id;
        }
    }
    if (!$ids) {
        return $map;
    }
    $CI =& get_instance();
    if (!$CI->db || !$CI->db->table_exists('product_categories')) {
        return $map;
    }
    $rows = $CI->db
        ->select('product_id, category_id')
        ->where_in('product_id', array_values($ids))
        ->get('product_categories')
        ->result();
    foreach ($rows as $row) {
        $pid = (int) $row->product_id;
        if (!isset($map[$pid])) {
            $map[$pid] = array();
        }
        $map[$pid][] = (int) $row->category_id;
    }
    return $map;
}

function apply_storefront_display_discount($products, $storeId)
{
    $cfg = storefront_display_discount_config($storeId);
    if ($cfg['percent'] <= 0) {
        return $products;
    }
    $list = is_array($products) ? $products : array($products);
    $catMap = array();
    if ($cfg['scope'] !== 'store') {
        $ids = array();
        foreach ($list as $product) {
            if ($product && !empty($product->id)) {
                $ids[] = (int) $product->id;
            }
        }
        $catMap = storefront_product_category_map($ids);
    }
    foreach ($list as $product) {
        if (!$product || empty($product->id)) {
            continue;
        }
        if ($cfg['scope'] !== 'store') {
            $pcats = isset($catMap[(int) $product->id]) ? $catMap[(int) $product->id] : array();
            $hit = false;
            foreach ($pcats as $cid) {
                if (!empty($cfg['allowed'][(int) $cid])) {
                    $hit = true;
                    break;
                }
            }
            if (!$hit) {
                continue;
            }
        }
        $price = (float) $product->price;
        if ($price <= 0) {
            continue;
        }
        $regular = round($price * (1 + $cfg['percent'] / 100), 2);
        $existing = isset($product->compare_price) ? (float) $product->compare_price : 0;
        if ($regular > $existing) {
            $product->compare_price = $regular;
            $product->display_discount_percent = (int) round($cfg['percent']);
        }
    }
    return $products;
}

function product_sale_off_percent($product)
{
    if (!$product) {
        return 0;
    }
    if (!empty($product->display_discount_percent)) {
        return (int) $product->display_discount_percent;
    }
    $price = (float) $product->price;
    $compare = isset($product->compare_price) ? (float) $product->compare_price : 0;
    if ($compare <= $price || $compare <= 0) {
        return 0;
    }
    return (int) round((($compare - $price) / $compare) * 100);
}

function apply_storefront_pricing($products, $storeId)
{
    $list = is_array($products) ? $products : array($products);
    foreach ($list as $product) {
        if (!$product) {
            continue;
        }
        // Safe re-entry: restore list price if an offer was previously baked into ->price.
        if (isset($product->offer_original_price) && (float) $product->offer_original_price > 0) {
            $product->price = (float) $product->offer_original_price;
        }
        $product->offer_applied = false;
        $product->active_offer = null;
        unset($product->offer_original_price);
        unset($product->display_discount_percent);
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
    // Real offers adjust selling price server-side; display discount must not stack on them.
    if (function_exists('apply_storefront_offers')) {
        apply_storefront_offers($list, $storeId);
    }
    $forDisplay = array();
    foreach ($list as $product) {
        if ($product && empty($product->active_offer)) {
            $forDisplay[] = $product;
        }
    }
    if ($forDisplay) {
        apply_storefront_display_discount($forDisplay, $storeId);
    }
    return $products;
}

function ec_scaled_listing_price($childCatalogPrice, $parentCatalogPrice, $parentListingPrice)
{
    $childCatalogPrice = (float) $childCatalogPrice;
    $parentCatalogPrice = (float) $parentCatalogPrice;
    $parentListingPrice = (float) $parentListingPrice;
    if ($childCatalogPrice <= 0) {
        return 0.0;
    }
    if ($parentCatalogPrice > 0 && $parentListingPrice > 0) {
        return round($childCatalogPrice * ($parentListingPrice / $parentCatalogPrice), 2);
    }
    return round($childCatalogPrice, 2);
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

function product_option_display_labels($children, $parent = null)
{
    $children = is_array($children) ? $children : array();
    $out = array();
    $compact = array();
    $pieceLabels = array();
    $preferColor = false;
    if (is_object($parent)) {
        $optTitle = function_exists('product_options_title')
            ? product_options_title($parent)
            : (isset($parent->options_title) ? trim((string) $parent->options_title) : '');
        if ($optTitle !== '' && preg_match('/färg|farg|color|colour|nyans|shade/iu', $optTitle)) {
            $preferColor = true;
        }
    }
    foreach ($children as $child) {
        if (!is_object($child) || empty($child->id)) {
            continue;
        }
        $id = (int) $child->id;
        $raw = product_option_label($child, $parent);
        $out[$id] = $raw;
        // Color variants should keep color names — never collapse to "1 Piece".
        if ($preferColor) {
            $short = product_option_compact_text($raw, $parent);
            // If compact accidentally becomes piece qty, keep the raw color name.
            if ($short !== '' && preg_match('/^\d+\s*(Piece|Pack)$/iu', $short)) {
                $compact[$id] = $raw;
            } else {
                $compact[$id] = ($short !== '') ? $short : $raw;
            }
            continue;
        }
        $piece = product_option_piece_label($child, $parent);
        if ($piece !== '') {
            $pieceLabels[$id] = $piece;
            $compact[$id] = $piece;
            continue;
        }
        $short = product_option_compact_text($raw, $parent);
        $compact[$id] = ($short !== '') ? $short : $raw;
    }
    $compact = product_option_strip_shared_prefix($compact);
    $seen = array();
    foreach ($compact as $id => $label) {
        $key = function_exists('mb_strtolower') ? mb_strtolower($label, 'UTF-8') : strtolower($label);
        if (!isset($seen[$key])) {
            $seen[$key] = array();
        }
        $seen[$key][] = $id;
    }
    foreach ($seen as $ids) {
        if (count($ids) === 1) {
            $out[$ids[0]] = $compact[$ids[0]];
            continue;
        }
        // Same piece count / compact label for multiple styles — disambiguate.
        foreach ($ids as $id) {
            if (!empty($pieceLabels[$id])) {
                $styled = product_option_piece_label_by_id($children, $id, true);
                if ($styled !== '') {
                    $out[$id] = $styled;
                    continue;
                }
            }
            $out[$id] = $compact[$id];
        }
    }
    return $out;
}

/**
 * Build a clean pack/piece label from child name/SKU, e.g. "6 Piece" or "Flower · 6 Piece".
 */
function product_option_piece_label($child, $parent = null, $forceStyle = false)
{
    if (!is_object($child)) {
        return '';
    }
    $parsed = product_option_parse_piece_style($child, $parent);
    if ($parsed['qty'] < 1) {
        return '';
    }
    $qtyLabel = product_option_format_qty($parsed['qty'], 'pcs');
    $style = $parsed['style'];
    if ($style !== '' && $forceStyle) {
        return $style . ' · ' . $qtyLabel;
    }
    return $qtyLabel;
}

function product_option_piece_label_by_id($children, $id, $forceStyle = false)
{
    foreach ((array) $children as $child) {
        if (is_object($child) && (int) $child->id === (int) $id) {
            return product_option_piece_label($child, null, $forceStyle);
        }
    }
    return '';
}

function product_option_parse_piece_style($child, $parent = null)
{
    $name = isset($child->name) ? trim((string) $child->name) : '';
    $sku = isset($child->sku) ? trim((string) $child->sku) : '';
    $hay = trim($name . ' ' . $sku);

    // Set/size clothing variants must keep Set / Size labels — never collapse to "N Piece".
    if (preg_match('/\bset\s*[-_]?\s*\d+\b/iu', $hay) || preg_match('/[-_]SET[-_]\d+/i', $sku)) {
        return array('qty' => 0, 'style' => '');
    }
    if (preg_match('/\s[-–—]\s*(XXS|XS|S|M|L|XL|XXL|XXXL|[1-5]XL)\s*$/iu', $name)) {
        return array('qty' => 0, 'style' => '');
    }

    $style = '';
    $qty = 0;

    // Prefer explicit piece/pcs tokens. Do NOT treat title "3-Pack" / "3 Pack" as a variant qty.
    if (preg_match('/(?:^|[\s\-–—_\/])([A-Za-z][A-Za-z0-9]*)[\s\-–—_]*?(\d+)\s*(?:pcs|pc|pieces?)\b/iu', $hay, $m)) {
        $style = trim((string) $m[1]);
        $qty = (int) $m[2];
    } elseif (preg_match('/\b(\d+)\s*[\-–—]?\s*(?:pcs|pc|pieces?)\b/iu', $hay, $m)) {
        $qty = (int) $m[1];
    } elseif (preg_match('/(?:^|[\s\-–—_\/])([A-Za-z][A-Za-z0-9]*)[\s\-–—_]*?(\d+)\s*-?\s*pack\b/iu', $hay, $m)
        && preg_match('/(?:pcs|pc|pieces?|-PACK(?:-|$))/i', $sku . ' ' . $name)) {
        // Only allow *-N-PACK SKU style packs (e.g. SVART-2-PACK), not "3-Pack" product titles.
        $style = trim((string) $m[1]);
        $qty = (int) $m[2];
        if (!preg_match('/-' . preg_quote((string) $qty, '/') . '-?PACK(?:-|$)/i', $sku)
            && !preg_match('/(?:^|[^0-9])' . preg_quote((string) $qty, '/') . '\s*-?\s*pack\s*$/iu', $name)) {
            $style = '';
            $qty = 0;
        }
    }

    $ignoreStyle = array(
        'one', 'size', 'reusable', 'silicone', 'nipple', 'covers', 'cover',
        'product', 'pack', 'pcs', 'pc', 'piece', 'pieces', 'brief', 'underwear',
        'seamless', 'full', 'trosor',
    );
    $styleKey = function_exists('mb_strtolower') ? mb_strtolower($style, 'UTF-8') : strtolower($style);
    if ($style === '' || in_array($styleKey, $ignoreStyle, true)) {
        $style = '';
    } else {
        $style = function_exists('mb_convert_case')
            ? mb_convert_case($style, MB_CASE_TITLE, 'UTF-8')
            : ucwords(strtolower($style));
    }

    return array(
        'qty' => $qty,
        'style' => $style,
    );
}

function product_option_display_label($child, $parent = null, $siblings = null)
{
    if ($siblings !== null) {
        $map = product_option_display_labels($siblings, $parent);
        $id = (is_object($child) && isset($child->id)) ? (int) $child->id : 0;
        if ($id && isset($map[$id])) {
            return $map[$id];
        }
    }
    $piece = product_option_piece_label($child, $parent, true);
    if ($piece !== '') {
        return $piece;
    }
    $raw = product_option_label($child, $parent);
    $short = product_option_compact_text($raw, $parent);
    return $short !== '' ? $short : $raw;
}

function product_option_compact_text($label, $parent = null)
{
    $label = trim(preg_replace('/\s+/u', ' ', (string) $label));
    if ($label === '') {
        return '';
    }
    $parentName = '';
    if (is_object($parent) && isset($parent->name)) {
        $parentName = trim((string) $parent->name);
    } elseif (is_string($parent)) {
        $parentName = trim($parent);
    }
    $chunks = preg_split('/\s*[\/|]+\s*/u', $label);
    if (!is_array($chunks) || !$chunks) {
        $chunks = array($label);
    }
    $parts = array();
    foreach ($chunks as $chunk) {
        $part = product_option_compact_part(trim((string) $chunk), $parentName);
        if ($part === '') {
            continue;
        }
        $key = function_exists('mb_strtolower') ? mb_strtolower($part, 'UTF-8') : strtolower($part);
        if (isset($parts[$key])) {
            continue;
        }
        $parts[$key] = $part;
    }
    if (!$parts) {
        return $label;
    }
    return implode(' · ', array_values($parts));
}

function product_option_compact_part($text, $parentName = '')
{
    $text = trim((string) $text);
    if ($text === '') {
        return '';
    }

    $qty = product_option_compact_qty($text);
    if ($qty !== '') {
        return $qty;
    }

    $attr = 'color|colour|färg|size|storlek|pack|quantity|qty|antal|type|typ|model|modell|capacity|kapacitet|variant|style|stil';
    if (preg_match('/^(?:product\s+)?(?:' . $attr . ')\s*[:\-–—]\s*(.+)$/iu', $text, $m)) {
        $text = trim($m[1]);
        $qty = product_option_compact_qty($text);
        if ($qty !== '') {
            return $qty;
        }
    } elseif (preg_match('/\b(?:' . $attr . ')\s*[:\-–—]\s*(.+)$/iu', $text, $m)) {
        $text = trim($m[1]);
        $qty = product_option_compact_qty($text);
        if ($qty !== '') {
            return $qty;
        }
    }

    if (preg_match('/^(.+?)\s+(?:color|colour|färg|size|storlek)$/iu', $text, $m)) {
        $text = trim($m[1]);
    }

    if ($parentName !== '') {
        $stripped = product_option_strip_parent_name($text, $parentName);
        if ($stripped !== '') {
            $text = $stripped;
        }
        $qty = product_option_compact_qty($text);
        if ($qty !== '') {
            return $qty;
        }
    }

    $text = trim($text, " \t-–—:|,");
    $text = trim(preg_replace('/\s+/u', ' ', $text));
    return $text;
}

function product_option_compact_qty($text)
{
    $text = trim((string) $text);
    if ($text === '') {
        return '';
    }
    // Clothing set/size labels — never reduce to pack qty.
    if (preg_match('/\bset\s*[-_]?\s*\d+\b/iu', $text)) {
        return '';
    }
    $unit = 'pack|packs|paket|pcs|pc|pieces|piece|styck|stk|st';
    if (preg_match('/^(\d+)\s*[-\s]?(?:' . $unit . ')\b/iu', $text, $m)) {
        return product_option_format_qty($m[1], $m[0]);
    }
    if (preg_match('/(?:^|[-–—,:]\s*)(\d+)\s*[-\s]?(pack|packs|paket|pcs|pc|pieces|piece|styck|stk|st)\s*$/iu', $text, $m)) {
        return product_option_format_qty($m[1], $m[2]);
    }
    // Avoid matching "3-Pack" inside a longer product title (e.g. "Underwear 3-Pack - Set 1").
    if (preg_match('/\b(\d+)\s*[-\s]?(pcs|pc|pieces|piece|styck|stk|st)\b/iu', $text, $m)) {
        return product_option_format_qty($m[1], $m[2]);
    }
    return '';
}

function product_option_format_qty($number, $unitHint)
{
    $n = (int) $number;
    $hint = function_exists('mb_strtolower') ? mb_strtolower((string) $unitHint, 'UTF-8') : strtolower((string) $unitHint);
    if (preg_match('/\b(piece|pieces|pcs|pc|styck|stk|st)\b/u', $hint)) {
        return $n . ' Piece';
    }
    return $n . ' Pack';
}

function product_option_strip_shared_prefix($labels)
{
    if (!is_array($labels) || count($labels) < 2) {
        return $labels;
    }
    $strings = array_values($labels);
    $prefix = product_option_shared_prefix($strings);
    if ($prefix === '') {
        return $labels;
    }
    $out = array();
    foreach ($labels as $id => $label) {
        $rest = function_exists('mb_substr')
            ? mb_substr((string) $label, function_exists('mb_strlen') ? mb_strlen($prefix) : strlen($prefix), null, 'UTF-8')
            : substr((string) $label, strlen($prefix));
        $rest = trim(preg_replace('/\s+/u', ' ', (string) $rest), " \t-–—:|,");
        if ($rest === '') {
            return $labels;
        }
        $out[$id] = $rest;
    }
    return $out;
}

function product_option_shared_prefix($strings)
{
    $strings = array_values((array) $strings);
    if (count($strings) < 2) {
        return '';
    }
    $mb = function_exists('mb_strlen') && function_exists('mb_substr') && function_exists('mb_strtolower');
    $len = function ($s) use ($mb) {
        return $mb ? mb_strlen((string) $s, 'UTF-8') : strlen((string) $s);
    };
    $sub = function ($s, $start, $count = null) use ($mb) {
        $s = (string) $s;
        if ($mb) {
            return $count === null
                ? mb_substr($s, $start, null, 'UTF-8')
                : mb_substr($s, $start, $count, 'UTF-8');
        }
        return $count === null ? substr($s, $start) : substr($s, $start, $count);
    };
    $low = function ($s) use ($mb) {
        return $mb ? mb_strtolower((string) $s, 'UTF-8') : strtolower((string) $s);
    };

    $first = (string) $strings[0];
    $max = $len($first);
    foreach ($strings as $s) {
        $s = (string) $s;
        $n = min($max, $len($s));
        $i = 0;
        while ($i < $n && $low($sub($first, $i, 1)) === $low($sub($s, $i, 1))) {
            $i++;
        }
        $max = $i;
        if ($max < 1) {
            return '';
        }
    }

    while ($max > 0 && !preg_match('/[\s\-–—:|\/]/u', $sub($first, $max - 1, 1))) {
        $max--;
    }
    if ($max < 2) {
        return '';
    }

    $prefix = $sub($first, 0, $max);
    foreach ($strings as $s) {
        $rest = trim($sub((string) $s, $max), " \t-–—:|,");
        if ($rest === '') {
            return '';
        }
    }
    return $prefix;
}

function product_option_strip_parent_name($text, $parentName)
{
    $text = trim((string) $text);
    $parentName = trim((string) $parentName);
    if ($text === '' || $parentName === '') {
        return $text;
    }
    $pattern = '/' . preg_quote($parentName, '/') . '/iu';
    $stripped = trim(preg_replace($pattern, ' ', $text));
    $stripped = trim(preg_replace('/\s+/u', ' ', $stripped), " \t-–—:|,");
    if ($stripped === '') {
        return $text;
    }
    return $stripped;
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
    if (preg_match('/\?{3,}|�|\xEF\xBF\xBD/u', $text)) {
        return array();
    }
    $parts = preg_split('/\r\n|\r|\n|(?<=\.)\s+(?=[A-Z])/', $text);
    $bullets = array();
    foreach ((array) $parts as $part) {
        $line = trim((string) $part, " \t-•*");
        if ($line === '' || preg_match('/\?{3,}|�|\xEF\xBF\xBD/u', $line)) {
            continue;
        }
        if (function_exists('mb_strlen') ? mb_strlen($line) > 280 : strlen($line) > 280) {
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
    $rate = function_exists('platform_shipping_per_item') ? platform_shipping_per_item() : 0;
    $freeMin = function_exists('platform_shipping_free_min') ? platform_shipping_free_min() : 0;
    $discountOn = function_exists('platform_shipping_discount_on') ? platform_shipping_discount_on() : false;
    $shippingOn = $rate > 0 || $discountOn || (function_exists('setting_flag_on') ? setting_flag_on($settings, 'shipping_enabled') : !empty($settings['shipping_enabled']));
    if ($discountOn && $freeMin > 0) {
        $items[] = array(
            'key' => 'delivery',
            'title' => function_exists('store_ui') ? store_ui('trust.free_delivery') : 'Free Delivery',
            'text' => function_exists('store_ui') ? store_ui('trust.free_from', array('{amount}' => format_money($freeMin))) : ('Free from ' . format_money($freeMin)),
        );
    } elseif ($shippingOn && $rate <= 0) {
        $items[] = array(
            'key' => 'delivery',
            'title' => function_exists('store_ui') ? store_ui('trust.free_delivery') : 'Free Delivery',
            'text' => $promo !== '' ? $promo : (function_exists('store_ui') ? store_ui('trust.free_shipping_text') : 'On qualifying orders'),
        );
    } elseif ($shippingOn) {
        $items[] = array(
            'key' => 'delivery',
            'title' => function_exists('store_ui') ? store_ui('trust.delivery') : 'Delivery',
            'text' => function_exists('store_ui') ? store_ui('trust.flat_rate', array('{amount}' => format_money($rate))) : ('Flat rate ' . format_money($rate)),
        );
    } elseif ($promo !== '') {
        $items[] = array(
            'key' => 'delivery',
            'title' => function_exists('store_ui') ? store_ui('trust.delivery') : 'Delivery',
            'text' => $promo,
        );
    } else {
        $items[] = array(
            'key' => 'delivery',
            'title' => function_exists('store_ui') ? store_ui('trust.delivery') : 'Delivery',
            'text' => function_exists('store_ui') ? store_ui('trust.tracked') : 'Tracked shipping available',
        );
    }
    $items[] = array(
        'key' => 'returns',
        'title' => function_exists('store_ui') ? store_ui('trust.returns') : 'Easy Returns',
        'text' => function_exists('store_ui') ? store_ui('trust.returns_simple') : 'Simple returns process',
    );
    $items[] = array(
        'key' => 'secure',
        'title' => function_exists('store_ui') ? store_ui('trust.secure_checkout') : 'Secure Checkout',
        'text' => function_exists('store_ui') ? store_ui('trust.protected') : 'Protected payments',
    );
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
        'title' => function_exists('store_ui') ? store_ui('trust.customer_support') : 'Customer Support',
        'text' => $support !== '' ? $support : (function_exists('store_ui') ? store_ui('trust.help') : 'We are here to help'),
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

function product_attribute_axes($productId)
{
    $axes = array();
    foreach (product_attributes_rows($productId) as $row) {
        $name = trim((string) (isset($row->name) ? $row->name : ''));
        $values = array();
        foreach (preg_split('/\s*,\s*/', (string) (isset($row->values_text) ? $row->values_text : '')) as $value) {
            $value = trim((string) $value);
            if ($value !== '' && !in_array($value, $values, true)) {
                $values[] = $value;
            }
        }
        if ($name !== '' && $values) {
            $axes[] = array('name' => $name, 'values' => $values);
        }
    }
    return $axes;
}

function product_child_attr_map($child, $parent, $axes)
{
    $axes = is_array($axes) ? $axes : array();
    if (!$axes) {
        return array();
    }
    $label = function_exists('product_option_label')
        ? product_option_label($child, $parent)
        : (isset($child->name) ? trim((string) $child->name) : '');
    $parts = preg_split('/\s*\/\s*/', $label);
    $map = array();
    if (count($parts) === count($axes)) {
        foreach ($axes as $i => $axis) {
            $part = trim((string) $parts[$i]);
            foreach ($axis['values'] as $value) {
                if (strcasecmp($part, $value) === 0) {
                    $map[$axis['name']] = $value;
                    break;
                }
            }
            if (!isset($map[$axis['name']])) {
                return array();
            }
        }
        return $map;
    }
    $haystack = $label . ' ' . (isset($child->sku) ? (string) $child->sku : '');
    foreach ($axes as $axis) {
        $matched = '';
        $bestLen = 0;
        foreach ($axis['values'] as $value) {
            $len = function_exists('mb_strlen') ? mb_strlen($value) : strlen($value);
            if ($len < $bestLen) {
                continue;
            }
            $quoted = preg_quote($value, '/');
            if (preg_match('/(?:^|[\s\/|,_\-])' . $quoted . '(?:$|[\s\/|,_\-])/iu', $haystack)) {
                $matched = $value;
                $bestLen = $len;
            }
        }
        if ($matched === '') {
            return array();
        }
        $map[$axis['name']] = $matched;
    }
    return $map;
}

function platform_base_url()
{
    if (!empty($_SESSION['local_config']['base_url'])) {
        return rtrim((string) $_SESSION['local_config']['base_url'], '/') . '/';
    }
    return rtrim(base_url(), '/') . '/';
}

function storefront_admin_edit_url($product)
{
    if (!$product || empty($product->id)) {
        return '';
    }
    $id = (int) $product->id;
    if (!empty($product->source_product_id)) {
        $id = (int) $product->source_product_id;
    }
    return platform_base_url() . 'admin/products/form/' . $id;
}

function storefront_admin_profitability_url($product, $store = null)
{
    if (!$product || empty($product->id)) {
        return '';
    }
    $listingId = (int) $product->id;
    $catalogId = !empty($product->source_product_id) ? (int) $product->source_product_id : $listingId;
    $storeId = 0;
    if (is_object($store) && !empty($store->id)) {
        $storeId = (int) $store->id;
    } elseif (!empty($product->store_id)) {
        $storeId = (int) $product->store_id;
    }
    if ($catalogId < 1 || $storeId < 1) {
        return '';
    }
    $currency = function_exists('store_currency') ? strtoupper(trim((string) store_currency($store !== null ? $store : $storeId))) : '';
    if ($currency === '') {
        $currency = 'SEK';
    }
    return platform_base_url() . 'admin/store-profitability/product/' . $catalogId . '?' . http_build_query(array(
        'store_id' => $storeId,
        'listing_id' => $listingId,
        'budget_mode' => 'per_product',
        'ads_budget' => 10,
        'ads_currency' => $currency,
        'expected_orders' => 1,
    ));
}

function storefront_admin_bar_name()
{
    $user = function_exists('ec_user') ? ec_user() : null;
    if ($user) {
        $name = '';
        if (!empty($user->first_name)) {
            $name = trim((string) $user->first_name);
        }
        if ($name === '' && !empty($user->uname)) {
            $name = trim((string) $user->uname);
        }
        if ($name !== '') {
            return $name;
        }
    }
    $token = storefront_admin_bar_parse_cookie();
    return $token && !empty($token['name']) ? (string) $token['name'] : '';
}

function storefront_admin_bar_secret()
{
    $CI =& get_instance();
    $key = (string) $CI->config->item('encryption_key');
    if ($key === '') {
        $key = (string) $CI->config->item('sess_cookie_name');
    }
    if (!empty($_SESSION['local_config']['db_name'])) {
        $key .= '|' . $_SESSION['local_config']['db_name'];
    }
    return hash('sha256', 'ec-admin-bar|' . $key, true);
}

function storefront_admin_bar_make_token($user)
{
    if (!$user || (int) $user->roleID !== ROLE_ADMIN) {
        return '';
    }
    $name = '';
    if (!empty($user->first_name)) {
        $name = trim((string) $user->first_name);
    }
    if ($name === '' && !empty($user->uname)) {
        $name = trim((string) $user->uname);
    }
    $body = rtrim(strtr(base64_encode(json_encode(array(
        'uid' => (int) $user->UserID,
        'role' => (int) $user->roleID,
        'name' => $name,
        'exp' => time() + 43200,
    ))), '+/', '-_'), '=');
    return $body . '.' . hash_hmac('sha256', $body, storefront_admin_bar_secret());
}

function storefront_admin_bar_parse_token($token)
{
    if (!is_string($token) || $token === '' || strpos($token, '.') === false) {
        return null;
    }
    $parts = explode('.', $token, 2);
    if (count($parts) !== 2 || $parts[0] === '' || $parts[1] === '') {
        return null;
    }
    $calc = hash_hmac('sha256', $parts[0], storefront_admin_bar_secret());
    if (!hash_equals($calc, $parts[1])) {
        return null;
    }
    $json = base64_decode(strtr($parts[0], '-_', '+/'));
    $data = json_decode($json, true);
    if (!is_array($data) || empty($data['exp']) || (int) $data['exp'] < time()) {
        return null;
    }
    if ((int) $data['role'] !== ROLE_ADMIN) {
        return null;
    }
    return $data;
}

function storefront_admin_bar_parse_cookie()
{
    if (empty($_COOKIE['ec_sf_admin'])) {
        return null;
    }
    return storefront_admin_bar_parse_token((string) $_COOKIE['ec_sf_admin']);
}

function storefront_admin_bar_visible()
{
    if (function_exists('ec_is_admin') && ec_is_admin()) {
        return true;
    }
    return storefront_admin_bar_parse_cookie() !== null;
}

function storefront_admin_bar_hosts()
{
    $hosts = array();
    $platform = parse_url(platform_base_url(), PHP_URL_HOST);
    if ($platform) {
        $hosts[] = strtolower($platform);
    }
    $CI =& get_instance();
    if (isset($CI->db)) {
        $rows = $CI->db->select('domain')->from('stores')->get()->result();
        foreach ($rows as $row) {
            $d = strtolower(trim((string) $row->domain));
            $d = preg_replace('#^https?://#', '', $d);
            $d = preg_replace('#/.*$#', '', $d);
            $d = preg_replace('#:\d+$#', '', $d);
            if ($d === '' || strpos($d, 'localhost') !== false || substr($d, -5) === '.test') {
                continue;
            }
            $hosts[] = $d;
        }
    }
    return array_values(array_unique($hosts));
}

function storefront_admin_bar_allowed_url($url)
{
    if (!is_string($url) || $url === '') {
        return false;
    }
    $parts = parse_url($url);
    if (empty($parts['scheme']) || !in_array(strtolower($parts['scheme']), array('http', 'https'), true)) {
        return false;
    }
    if (empty($parts['host'])) {
        return false;
    }
    return in_array(strtolower($parts['host']), storefront_admin_bar_hosts(), true);
}

function storefront_admin_bar_set_cookie($token, $exp)
{
    $secure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');
    $opts = array(
        'expires' => (int) $exp,
        'path' => '/',
        'secure' => $secure,
        'httponly' => true,
        'samesite' => 'Lax',
    );
    setcookie('ec_sf_admin', $token, $opts);
    $_COOKIE['ec_sf_admin'] = $token;
}

function storefront_admin_bar_host_ok($host)
{
    if ($host === '') {
        return false;
    }
    if (!function_exists('curl_init')) {
        return true;
    }
    $ch = curl_init('https://' . $host . '/');
    curl_setopt_array($ch, array(
        CURLOPT_NOBODY => true,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 3,
        CURLOPT_CONNECTTIMEOUT => 2,
        CURLOPT_SSL_VERIFYPEER => true,
        CURLOPT_SSL_VERIFYHOST => 2,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_USERAGENT => 'ZenvelloAdminBar/1',
    ));
    curl_exec($ch);
    $err = curl_errno($ch);
    $code = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    return $err === 0 && $code > 0 && $code < 500;
}
