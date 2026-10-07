<?php
defined('BASEPATH') OR exit('No direct script access allowed');

function sa_uuid()
{
    return sprintf(
        '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
        mt_rand(0, 0xffff), mt_rand(0, 0xffff),
        mt_rand(0, 0xffff),
        mt_rand(0, 0x0fff) | 0x4000,
        mt_rand(0, 0x3fff) | 0x8000,
        mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
    );
}

function sa_flag($code)
{
    $code = strtoupper(preg_replace('/[^A-Z]/', '', (string) $code));
    if (strlen($code) !== 2) {
        return '';
    }
    $out = '';
    foreach (str_split($code) as $ch) {
        $out .= mb_convert_encoding('&#' . (127397 + ord($ch)) . ';', 'UTF-8', 'HTML-ENTITIES');
    }
    return $out;
}

function sa_parse_device($ua)
{
    $ua = (string) $ua;
    if (preg_match('/iPad|Tablet|PlayBook/i', $ua)) {
        return 'tablet';
    }
    if (preg_match('/Mobile|iPhone|Android.+Mobile|webOS|BlackBerry|IEMobile/i', $ua)) {
        return 'mobile';
    }
    return 'desktop';
}

function sa_parse_browser($ua)
{
    $ua = (string) $ua;
    if (stripos($ua, 'Edg/') !== false || stripos($ua, 'Edge/') !== false) {
        return 'Edge';
    }
    if (stripos($ua, 'Chrome/') !== false && stripos($ua, 'Chromium') === false) {
        return 'Chrome';
    }
    if (stripos($ua, 'Safari/') !== false && stripos($ua, 'Chrome') === false) {
        return 'Safari';
    }
    if (stripos($ua, 'Firefox/') !== false) {
        return 'Firefox';
    }
    return 'Other';
}

function sa_is_bot($ua)
{
    return (bool) preg_match('/bot|crawl|spider|slurp|bingpreview|facebookexternalhit|whatsapp|telegram/i', (string) $ua);
}

function sa_country_from_request($fallback = '')
{
    $keys = array('HTTP_CF_IPCOUNTRY', 'HTTP_X_APPENGINE_COUNTRY', 'HTTP_X_COUNTRY_CODE', 'GEOIP_COUNTRY_CODE');
    foreach ($keys as $key) {
        if (!empty($_SERVER[$key]) && strlen($_SERVER[$key]) === 2 && strtoupper($_SERVER[$key]) !== 'XX') {
            return strtoupper($_SERVER[$key]);
        }
    }
    $lang = isset($_SERVER['HTTP_ACCEPT_LANGUAGE']) ? $_SERVER['HTTP_ACCEPT_LANGUAGE'] : '';
    if (preg_match('/[a-z]{2}-([A-Z]{2})/', $lang, $m)) {
        return $m[1];
    }
    return strtoupper(trim((string) $fallback));
}

function sa_traffic_source($referrer, $utmSource = '', $utmMedium = '')
{
    $utmSource = strtolower(trim((string) $utmSource));
    $utmMedium = strtolower(trim((string) $utmMedium));
    if ($utmSource !== '') {
        if (preg_match('/facebook|fb|meta|ig|instagram/', $utmSource . ' ' . $utmMedium)) {
            if (strpos($utmSource, 'ig') !== false || strpos($utmSource, 'instagram') !== false) {
                return 'instagram';
            }
            return 'facebook';
        }
        if (strpos($utmSource, 'tiktok') !== false || strpos($utmMedium, 'tiktok') !== false) {
            return 'tiktok';
        }
        if (strpos($utmSource, 'google') !== false) {
            return (strpos($utmMedium, 'cpc') !== false || strpos($utmMedium, 'paid') !== false) ? 'google' : 'organic';
        }
        return $utmSource;
    }
    $host = strtolower((string) parse_url((string) $referrer, PHP_URL_HOST));
    if ($host === '') {
        return 'direct';
    }
    if (preg_match('/facebook|fb\.com|instagram|l\.instagram/', $host)) {
        return strpos($host, 'instagram') !== false ? 'instagram' : 'facebook';
    }
    if (strpos($host, 'tiktok') !== false) {
        return 'tiktok';
    }
    if (preg_match('/google\.|bing\.|yahoo\.|duckduckgo/', $host)) {
        return 'organic';
    }
    return 'referral';
}

function sa_source_label($source)
{
    $map = array(
        'facebook' => 'Facebook',
        'instagram' => 'Instagram',
        'tiktok' => 'TikTok',
        'google' => 'Google',
        'direct' => 'Direct',
        'organic' => 'Organic',
        'referral' => 'Referral',
        'other' => 'Other',
    );
    $source = strtolower((string) $source);
    return isset($map[$source]) ? $map[$source] : ucfirst($source);
}

function sa_event_label($type)
{
    $map = array(
        'page_view' => 'Page View',
        'product_view' => 'Product View',
        'add_to_cart' => 'Add to Cart',
        'begin_checkout' => 'Checkout',
        'payment_page_view' => 'Payment Page',
        'payment_attempt' => 'Payment Attempt',
        'purchase' => 'Purchase',
        'heartbeat' => 'Active',
    );
    return isset($map[$type]) ? $map[$type] : ucfirst(str_replace('_', ' ', (string) $type));
}

function sa_cookie($name, $default = '')
{
    return isset($_COOKIE[$name]) ? trim((string) $_COOKIE[$name]) : $default;
}

function sa_set_cookie($name, $value, $days = 365)
{
    $value = substr(preg_replace('/[^a-zA-Z0-9\-_]/', '', (string) $value), 0, 64);
    if ($value === '') {
        return;
    }
    setcookie($name, $value, time() + ($days * 86400), '/', '', false, false);
    $_COOKIE[$name] = $value;
}

function sa_pct($part, $whole)
{
    $whole = (float) $whole;
    if ($whole <= 0) {
        return 0;
    }
    return round(((float) $part / $whole) * 100, 2);
}

function sa_int($n)
{
    return number_format((int) $n);
}

function sa_ago($datetime)
{
    $ts = strtotime((string) $datetime);
    if (!$ts) {
        return '';
    }
    $diff = max(0, time() - $ts);
    if ($diff < 60) {
        return $diff . ' second' . ($diff === 1 ? '' : 's') . ' ago';
    }
    if ($diff < 3600) {
        $m = (int) floor($diff / 60);
        return $m . ' minute' . ($m === 1 ? '' : 's') . ' ago';
    }
    if ($diff < 86400) {
        $h = (int) floor($diff / 3600);
        return $h . ' hour' . ($h === 1 ? '' : 's') . ' ago';
    }
    $d = (int) floor($diff / 86400);
    return $d . ' day' . ($d === 1 ? '' : 's') . ' ago';
}
