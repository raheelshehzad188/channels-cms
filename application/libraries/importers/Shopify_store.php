<?php
defined('BASEPATH') OR exit('No direct script access allowed');

require_once APPPATH . 'libraries/importers/Importer_base.php';

class Shopify_store extends Importer_base {

    public function extract($html, $url)
    {
        $product = $this->shopify_product($html, $url);
        $ld = $this->ld_product($html);
        $offer = $this->ld_offer($ld);

        $name = '';
        if (!empty($product['title'])) {
            $name = $this->clean_text($product['title']);
        }
        if ($name === '' && !empty($ld['name'])) {
            $name = $this->clean_text($ld['name']);
        }
        if ($name === '') {
            $name = $this->clean_text($this->first_match('/property=["\']og:title["\'][^>]*content=["\']([^"\']+)/i', $html));
        }
        $name = html_entity_decode($name, ENT_QUOTES | ENT_HTML5, 'UTF-8');

        $sku = '';
        $variant = $this->primary_variant($product);
        if (!empty($variant['sku'])) {
            $sku = trim((string) $variant['sku']);
        }
        if ($sku === '' && !empty($ld['sku'])) {
            $sku = trim((string) $ld['sku']);
        }
        if ($sku === '') {
            $sku = strtolower((string) $this->first_match('~/products/([^/?#]+)~i', $url));
        }

        $price = $this->money_from_cents(isset($product['price']) ? $product['price'] : 0);
        if ($price <= 0 && isset($variant['price'])) {
            $price = $this->money_from_cents($variant['price']);
        }
        if ($price <= 0) {
            $price = $this->parse_price(isset($offer['price']) ? $offer['price'] : 0);
        }

        $compare = $this->money_from_cents(isset($product['compare_at_price']) ? $product['compare_at_price'] : 0);
        if ($compare <= 0 && isset($variant['compare_at_price'])) {
            $compare = $this->money_from_cents($variant['compare_at_price']);
        }
        if ($compare <= $price) {
            $compare = 0;
        }

        $stock = 0;
        if (!empty($product['available']) || !empty($variant['available'])) {
            $stock = 1;
        }
        if ($stock <= 0 && !empty($offer['availability']) && stripos((string) $offer['availability'], 'InStock') !== false) {
            $stock = 1;
        }

        $details = '';
        if (!empty($product['description'])) {
            $details = $this->clean_html($product['description']);
        }
        if ($details === '' && !empty($ld['description'])) {
            $details = $this->clean_html($ld['description']);
        }
        $description = $this->clean_text($details);
        if ($description === '') {
            $description = $this->clean_text($this->first_match('/property=["\']og:description["\'][^>]*content=["\']([^"\']+)/i', $html));
        }

        $brand = '';
        if (!empty($product['vendor'])) {
            $brand = $product['vendor'];
        }

        $images = $this->shopify_images($product);
        if (!empty($ld['image'])) {
            $images = array_merge($images, $this->flatten_image_list($ld['image']));
        }
        $saved = $this->downloadImages ? $this->download_images($images, 20) : array();

        return array(
            'name' => $name,
            'sku' => $sku,
            'price' => $price,
            'compare_price' => $compare,
            'brand' => $this->normalize_brand($brand),
            'description' => $description,
            'details' => $details,
            'stock' => $stock,
            'image' => isset($saved[0]) ? $saved[0] : '',
            'gallery' => array_slice($saved, 1),
            'seo_title' => $name,
            'seo_description' => function_exists('mb_substr') ? mb_substr($description, 0, 180) : substr($description, 0, 180),
        );
    }

    protected function shopify_product($html, $url)
    {
        $jsUrl = $this->product_js_url($url);
        if ($jsUrl !== '') {
            $raw = $this->fetch_json($jsUrl);
            $decoded = json_decode((string) $raw, true);
            if (is_array($decoded) && !empty($decoded['title'])) {
                return $decoded;
            }
        }
        return array();
    }

    protected function fetch_json($url)
    {
        $ch = curl_init($url);
        curl_setopt_array($ch, array(
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_MAXREDIRS => 5,
            CURLOPT_TIMEOUT => 25,
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_USERAGENT => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124.0.0.0 Safari/537.36',
            CURLOPT_HTTPHEADER => array(
                'Accept: application/json,text/javascript,*/*;q=0.8',
                'Accept-Language: ' . $this->fetchLanguage,
            ),
            CURLOPT_ENCODING => '',
        ));
        $body = curl_exec($ch);
        $code = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        if ($body === false || $code >= 400) {
            return '';
        }
        return $body;
    }

    protected function product_js_url($url)
    {
        $path = (string) parse_url($url, PHP_URL_PATH);
        if (!preg_match('~/products/([^/?#]+)~i', $path, $match)) {
            return '';
        }
        $parts = parse_url($url);
        $host = isset($parts['host']) ? $parts['host'] : '';
        $scheme = isset($parts['scheme']) && $parts['scheme'] !== '' ? $parts['scheme'] : 'https';
        if ($host === '') {
            return '';
        }
        return $scheme . '://' . $host . '/products/' . $match[1] . '.js';
    }

    protected function primary_variant($product)
    {
        if (empty($product['variants'][0]) || !is_array($product['variants'][0])) {
            return array();
        }
        foreach ($product['variants'] as $variant) {
            if (is_array($variant) && !empty($variant['available'])) {
                return $variant;
            }
        }
        return $product['variants'][0];
    }

    protected function ld_product($html)
    {
        foreach ($this->json_ld_products($html) as $item) {
            $type = isset($item['@type']) ? $item['@type'] : '';
            if (is_array($type)) {
                $type = implode(',', $type);
            }
            if (stripos((string) $type, 'ProductGroup') === false) {
                return $item;
            }
        }
        $items = $this->json_ld_products($html);
        return isset($items[0]) ? $items[0] : array();
    }

    protected function shopify_images($product)
    {
        $out = array();
        if (!empty($product['featured_image'])) {
            $out[] = $this->shopify_image_url($product['featured_image']);
        }
        if (!empty($product['images']) && is_array($product['images'])) {
            foreach ($product['images'] as $image) {
                $out[] = $this->shopify_image_url($image);
            }
        }
        return $out;
    }

    protected function shopify_image_url($image)
    {
        if (is_array($image)) {
            if (!empty($image['src'])) {
                return (string) $image['src'];
            }
            if (!empty($image['url'])) {
                return (string) $image['url'];
            }
            return '';
        }
        return (string) $image;
    }

    protected function money_from_cents($value)
    {
        $value = (float) $value;
        if ($value <= 0) {
            return 0.0;
        }
        return round($value / 100, 2);
    }
}
