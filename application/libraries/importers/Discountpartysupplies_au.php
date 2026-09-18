<?php
defined('BASEPATH') OR exit('No direct script access allowed');

require_once APPPATH . 'libraries/importers/Importer_base.php';

class Discountpartysupplies_au extends Importer_base {

    protected $fetchLanguage = 'en-AU,en;q=0.9';

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
            $name = $this->clean_text($this->first_match('/property="og:title"\s+content="([^"]+)"/i', $html));
        }
        if ($name === '') {
            $name = $this->clean_text($this->first_match('/<h1[^>]*>(.*?)<\/h1>/is', $html));
        }
        if ($name === '') {
            $name = $this->clean_text($this->first_match('/<title>(.*?)<\/title>/is', $html));
            $name = preg_replace('/\s*-\s*Discount Party Supplies.*$/i', '', $name);
        }

        $sku = '';
        if (!empty($ld['sku'])) {
            $sku = trim((string) $ld['sku']);
        }
        if ($sku === '') {
            $sku = $this->first_match('/"sku"\s*:\s*"([^"]+)"/i', $html);
        }
        if ($sku === '') {
            $sku = $this->first_match('/SKU:\s*<\/[^>]+>\s*(?:<[^>]+>)?([^<]+)/i', $html);
        }
        if ($sku === '') {
            $sku = strtoupper($this->first_match('/-([a-z0-9]+)\.html(?:\?|$)/i', $url));
        }

        $price = 0.0;
        if (!empty($offer['price'])) {
            $price = $this->parse_price($offer['price']);
        }
        if ($price <= 0) {
            $price = $this->parse_price($this->first_match('/property="product:price:amount"\s+content="([^"]+)"/i', $html));
        }
        if ($price <= 0) {
            $price = $this->parse_price($this->first_match('/"price"\s*:\s*"([0-9]+(?:\.[0-9]+)?)"/i', $html));
        }
        if ($price <= 0) {
            $price = $this->parse_price($this->first_match('/Price:\s*([0-9]+(?:\.[0-9]+)?)\s*AUD/i', $html));
        }

        $availability = '';
        if (!empty($offer['availability'])) {
            $availability = (string) $offer['availability'];
        }
        if ($availability === '') {
            $availability = $this->first_match('/"availability"\s*:\s*"([^"]+)"/i', $html);
        }
        $stock = 0;
        if (stripos($availability, 'InStock') !== false) {
            $stock = 1;
        }

        $rawDetails = '';
        if (!empty($ld['description'])) {
            $rawDetails = (string) $ld['description'];
        }
        if ($rawDetails === '') {
            $rawDetails = $this->first_match('/property="og:description"\s+content="([^"]+)"/i', $html);
        }
        $rawDetails = $this->normalize_source_text($rawDetails);
        $description = $this->clean_text($rawDetails);
        $details = $this->details_from_text($rawDetails);

        $images = array();
        if (!empty($ld['image'])) {
            $images = array_merge($images, $this->flatten_image_list($ld['image']));
        }
        if (preg_match_all('/property="og:image"\s+content="([^"]+)"/i', $html, $matches)) {
            $images = array_merge($images, $matches[1]);
        }
        if (preg_match_all('#https?://[^"\'\s>]+/media/catalog/product/[^"\'\s>]+#i', $html, $matches)) {
            $images = array_merge($images, $matches[0]);
        }
        $images = array_values(array_unique(array_filter($images, array($this, 'is_product_image'))));

        $brand = '';
        if (!empty($ld['brand']['name'])) {
            $brand = $ld['brand']['name'];
        } elseif (!empty($ld['brand']) && is_string($ld['brand'])) {
            $brand = $ld['brand'];
        }

        $saved = $this->downloadImages ? $this->download_images($images, 20) : array();

        return array(
            'name' => $name,
            'sku' => $sku,
            'price' => $price,
            'compare_price' => 0,
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

    protected function normalize_source_text($text)
    {
        $text = str_ireplace(array('&#xD;', '&#13;', '&#x0D;'), "\n", (string) $text);
        $text = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $text = preg_replace("/\R/u", "\n", $text);
        return $text;
    }

    protected function details_from_text($text)
    {
        $text = $this->normalize_source_text($text);
        $lines = preg_split("/\n+/", $text);
        $html = '';
        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '') {
                continue;
            }
            if (preg_match('/^(Measurements|Includes|Material|Warning)\s*:?\s*$/i', $line, $match)) {
                $html .= '<h4>' . htmlspecialchars($match[1], ENT_QUOTES, 'UTF-8') . '</h4>';
                continue;
            }
            $html .= '<p>' . htmlspecialchars($line, ENT_QUOTES, 'UTF-8') . '</p>';
        }
        return $this->clean_html($html);
    }

    protected function is_product_image($url)
    {
        $url = strtolower((string) $url);
        if ($url === '' || strpos($url, 'placeholder') !== false) {
            return false;
        }
        if (strpos($url, '/wysiwyg/') !== false || strpos($url, '/footer/') !== false) {
            return false;
        }
        return strpos($url, '/media/catalog/product/') !== false
            || (bool) preg_match('#(\.jpg|\.jpeg|\.png|\.webp)(\?|$)#i', $url);
    }
}
