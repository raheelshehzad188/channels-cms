<?php
defined('BASEPATH') OR exit('No direct script access allowed');

require_once APPPATH . 'libraries/importers/Importer_base.php';

class Ebay_uk extends Importer_base {

    protected $cookieFile = '';
    protected $ebayPrimed = false;

    public function parse($url, $downloadImages = true)
    {
        $this->cookieFile = sys_get_temp_dir() . '/ec_ebay_' . md5($url . microtime(true)) . '.txt';
        $this->ebayPrimed = false;
        try {
            return parent::parse($url, $downloadImages);
        } finally {
            if ($this->cookieFile !== '' && is_file($this->cookieFile)) {
                @unlink($this->cookieFile);
            }
            $this->cookieFile = '';
            $this->ebayPrimed = false;
        }
    }

    protected function fetch($url)
    {
        $this->prime_ebay_session();
        $html = $this->ebay_curl($url, false);
        $checkBlock = (stripos($url, 'ebaydesc.com') === false);
        if ($checkBlock && $this->is_ebay_block($html)) {
            $this->ebayPrimed = false;
            $this->prime_ebay_session();
            $retry = $this->ebay_curl($url, false);
            if ($retry !== '') {
                $html = $retry;
            }
        }
        if ($checkBlock && $this->is_ebay_block($html)) {
            return '';
        }
        return $html;
    }

    protected function prime_ebay_session()
    {
        if ($this->ebayPrimed) {
            return;
        }
        $host = strtolower((string) parse_url($this->pageUrl, PHP_URL_HOST));
        if ($host === '') {
            $host = 'www.ebay.com';
        }
        $this->ebay_curl('https://' . $host . '/', true);
        $this->ebayPrimed = true;
    }

    protected function is_ebay_block($html)
    {
        $html = (string) $html;
        if ($html === '' || strlen($html) < 4000) {
            return true;
        }
        if (stripos($html, 'Pardon Our Interruption') !== false) {
            return true;
        }
        if (preg_match('/<title>\s*Error Page\s*\|\s*eBay/i', $html)) {
            return true;
        }
        return false;
    }

    protected function ebay_curl($url, $allowError)
    {
        $headers = array(
            'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,*/*;q=0.8',
            'Accept-Language: ' . $this->fetchLanguage,
            'Cache-Control: no-cache',
            'Upgrade-Insecure-Requests: 1',
        );
        if ($this->pageUrl !== '' && stripos($url, 'ebaydesc.com') !== false) {
            $headers[] = 'Referer: ' . $this->pageUrl;
        }
        $ch = curl_init($url);
        $opts = array(
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_MAXREDIRS => 5,
            CURLOPT_TIMEOUT => 25,
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_USERAGENT => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124.0.0.0 Safari/537.36',
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_ENCODING => '',
        );
        if ($this->cookieFile !== '') {
            $opts[CURLOPT_COOKIEJAR] = $this->cookieFile;
            $opts[CURLOPT_COOKIEFILE] = $this->cookieFile;
        }
        curl_setopt_array($ch, $opts);
        $html = curl_exec($ch);
        $code = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        if ($html === false) {
            return '';
        }
        if (!$allowError && $code >= 400) {
            return '';
        }
        return is_string($html) ? $html : '';
    }

    public function extract($html, $url)
    {
        $products = $this->json_ld_products($html);
        $ld = $products ? $products[0] : array();
        $offer = $this->ld_offer($ld);

        $name = '';
        if (!empty($ld['name'])) {
            $name = $this->clean_text($ld['name']);
        }
        if ($name === '') {
            $name = $this->clean_text($this->first_match('/class=["\']?x-item-title__mainTitle[^>]*>.*?class=["\'][^"\']*ux-textspans[^"\']*["\'][^>]*>(.*?)<\/span>/is', $html));
        }
        if ($name === '') {
            $name = $this->clean_text($this->first_match('/property="og:title"\s+content="([^"]+)"/i', $html));
        }
        if ($name === '') {
            $name = $this->clean_text($this->first_match('/<h1[^>]*>(.*?)<\/h1>/is', $html));
        }
        if ($name === '') {
            $name = $this->clean_text($this->first_match('/<title>(.*?)<\/title>/is', $html));
        }
        $name = trim(preg_replace('/\s*\|\s*eBay\s*$/i', '', $name));

        $sku = $this->first_match('#/itm/(?:[^/]+/)?([0-9]{8,})#', $url);
        if ($sku === '' && !empty($ld['sku'])) {
            $sku = (string) $ld['sku'];
        }
        if ($sku === '') {
            $sku = $this->first_match('#/itm/(?:[^/]+/)?([0-9]{8,})#', $html);
        }

        $price = 0.0;
        if (!empty($offer['price'])) {
            $price = $this->parse_price($offer['price']);
        } elseif (!empty($offer['lowPrice'])) {
            $price = $this->parse_price($offer['lowPrice']);
        }
        if ($price <= 0) {
            $price = $this->parse_price($this->first_match('/class=["\']?x-price-primary(?:__price)?[^>]*>.*?(?:US\s*)?(?:£|\$|€)\s*([0-9,.]+)/is', $html));
        }
        if ($price <= 0) {
            $price = $this->parse_price($this->first_match('/itemprop="price"\s+content="([^"]+)"/i', $html));
        }
        if ($price <= 0) {
            $price = $this->parse_price($this->first_match('/"priceCurrency"\s*:\s*"[A-Z]{3}"\s*,\s*"price"\s*:\s*"([0-9.]+)"/i', $html));
        }

        $description = '';
        $details = '';
        if (!empty($ld['description'])) {
            $details = $ld['description'];
            $description = $this->clean_text($ld['description']);
        }
        if ($description === '') {
            $description = $this->clean_text($this->first_match('/property="og:description"\s+content="([^"]+)"/i', $html));
        }

        $iframe = $this->first_match('/iframe[^>]+src=["\'](https?:\/\/[^"\']*ebaydesc[^"\']+)/i', $html);
        if ($iframe === '') {
            $iframe = $this->first_match('/src=["\']?(https?:\/\/(?:itm|vi\.vipr)\.ebaydesc\.com[^"\'\s>]+)/i', $html);
        }
        $iframe = html_entity_decode($iframe, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        if ($iframe !== '') {
            $descPage = $this->fetch($iframe);
            $body = $this->first_match('/<body[^>]*>([\s\S]*)<\/body>/i', $descPage);
            if ($body !== '') {
                $details = $body;
            } elseif ($descPage !== '') {
                $details = $descPage;
            }
        }

        $images = array();
        if (!empty($ld['image'])) {
            $images = $this->flatten_image_list($ld['image']);
        }
        if (preg_match_all('/property="og:image"\s+content="([^"]+)"/i', $html, $matches)) {
            $images = array_merge($images, $matches[1]);
        }
        if (preg_match_all('#https://i\.ebayimg\.com/images/g/[^/"\'\s<>]+/s-l(?:1600|1200|960|500)\.jpg#i', $html, $matches)) {
            $images = array_merge($images, $matches[0]);
        }
        $images = $this->normalize_ebay_images($images);

        $stock = 1;
        $availability = '';
        if (!empty($offer['availability'])) {
            $availability = (string) $offer['availability'];
        }
        if ($availability !== '' && stripos($availability, 'OutOfStock') !== false) {
            $stock = 0;
        }

        $brand = '';
        if (!empty($ld['brand'])) {
            $brand = $this->extract_brand($html);
        }

        $saved = $this->downloadImages ? $this->download_images($images, 20) : array();

        return array(
            'name' => $name,
            'sku' => $sku,
            'brand' => $brand,
            'price' => $price,
            'compare_price' => 0,
            'description' => $description,
            'details' => $details,
            'stock' => $stock,
            'image' => isset($saved[0]) ? $saved[0] : '',
            'gallery' => array_slice($saved, 1),
            'seo_title' => $name,
            'seo_description' => mb_substr($description, 0, 180),
        );
    }

    protected function normalize_ebay_images($urls)
    {
        $out = array();
        $seen = array();
        foreach ((array) $urls as $url) {
            $url = $this->asset_url($url);
            if ($url === '' || !preg_match('#^https://i\.ebayimg\.com/images/g/([^/]+)/#i', $url, $match)) {
                if ($url !== '' && !isset($seen[$url])) {
                    $seen[$url] = true;
                    $out[] = $url;
                }
                continue;
            }
            $key = strtolower($match[1]);
            if (isset($seen[$key])) {
                continue;
            }
            $seen[$key] = true;
            $out[] = 'https://i.ebayimg.com/images/g/' . $match[1] . '/s-l1600.jpg';
        }
        return $out;
    }
}
