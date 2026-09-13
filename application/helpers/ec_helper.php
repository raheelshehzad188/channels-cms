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

function product_image_url($path, $fallback = '')
{
    $path = trim((string) $path);
    if ($path === '') {
        return $fallback;
    }
    if (preg_match('#^https?://#i', $path) || strpos($path, '//') === 0) {
        return $path;
    }
    return base_url(ltrim($path, '/'));
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

function product_platform_fee($storeId = 0, $product = null)
{
    $fee = (float) platform_setting('platform_fee', 0);
    $target = '';
    if ($storeId) {
        $target = store_currency($storeId);
    } elseif ($product) {
        $target = product_currency($product);
    } else {
        $target = platform_currency();
    }
    return convert_money($fee, platform_currency(), $target);
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

function product_commission_amount($product)
{
    if (isset($product->owner_commission) && $product->owner_commission !== null && $product->owner_commission !== '') {
        return (float) $product->owner_commission;
    }
    if (empty($product->created_by)) {
        return 0.0;
    }
    $CI =& get_instance();
    $user = $CI->db->select('commission')->where('UserID', (int) $product->created_by)->get('users')->row();
    return $user ? (float) $user->commission : 0.0;
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

function product_customer_price($product, $storeId)
{
    // Product price includes base + ecommerce commission + platform fee + store markup.
    // VAT is applied once at checkout on the order subtotal, not per product.
    return round(product_wholesale_price($product, $storeId) + product_store_markup($storeId, isset($product->id) ? $product->id : 0), 2);
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
    return trim($html);
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
