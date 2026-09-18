<?php
defined('BASEPATH') OR exit('No direct script access allowed');

require_once APPPATH . 'libraries/importers/Importer_base.php';

class Aw_dropship extends Importer_base {

    protected $fetchLanguage = 'en-GB,en;q=0.9';

    public function extract($html, $url)
    {
        $product = $this->inertia_product($html);

        $name = '';
        if (!empty($product['name'])) {
            $name = $this->clean_text($product['name']);
        }
        if ($name === '') {
            $name = $this->clean_text($this->first_match('/property="og:title"\s+content="([^"]+)"/i', $html));
        }
        if ($name === '') {
            $name = $this->clean_text($this->first_match('/<h1[^>]*>(.*?)<\/h1>/is', $html));
            $name = preg_replace('/^\s*\d+x\s+/i', '', $name);
        }
        $name = preg_replace('/\s*\|\s*Dropshipping\s*$/i', '', $name);

        $sku = '';
        if (!empty($product['code'])) {
            $sku = trim((string) $product['code']);
        }
        if ($sku === '') {
            $sku = strtoupper($this->first_match('#/([a-z0-9-]+)/?$#i', $url));
        }

        $price = $this->parse_price(isset($product['discounted_price']) ? $product['discounted_price'] : 0);
        if ($price <= 0) {
            $price = $this->parse_price(isset($product['price']) ? $product['price'] : 0);
        }
        if ($price <= 0) {
            $price = $this->parse_price($this->first_match('/"price"\s*:\s*"?([0-9]+(?:\.[0-9]+)?)/i', $html));
        }

        $compare = $this->parse_price(isset($product['rrp']) ? $product['rrp'] : 0);
        if ($compare <= $price) {
            $compare = 0;
        }

        $stock = 0;
        if (isset($product['stock'])) {
            $stock = (int) $product['stock'];
        }
        if ($stock <= 0 && !empty($product['status']) && $product['status'] === 'for-sale') {
            $stock = 1;
        }

        $details = '';
        if (!empty($product['description'])) {
            $details = $this->clean_html($product['description']);
        }
        $specs = $this->specs_html(isset($product['specifications']) ? $product['specifications'] : array());
        if ($specs !== '') {
            $details .= $specs;
        }
        $description = $this->clean_text($details);
        if ($description === '') {
            $description = $this->clean_text($this->first_match('/property="og:description"\s+content="([^"]+)"/i', $html));
        }

        $images = $this->product_images($product, $html);
        $saved = $this->downloadImages ? $this->download_images($images, 20) : array();

        return array(
            'name' => $name,
            'sku' => $sku,
            'price' => $price,
            'compare_price' => $compare,
            'brand' => '',
            'description' => $description,
            'details' => $details,
            'stock' => $stock,
            'image' => isset($saved[0]) ? $saved[0] : '',
            'gallery' => array_slice($saved, 1),
            'seo_title' => $name,
            'seo_description' => function_exists('mb_substr') ? mb_substr($description, 0, 180) : substr($description, 0, 180),
        );
    }

    protected function inertia_product($html)
    {
        $raw = $this->first_match('/data-page="([^"]+)"/i', $html);
        if ($raw === '') {
            return array();
        }
        $raw = html_entity_decode($raw, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $decoded = json_decode($raw, true);
        if (!is_array($decoded)) {
            return array();
        }
        $blocks = array();
        if (!empty($decoded['props']['web_blocks']) && is_array($decoded['props']['web_blocks'])) {
            $blocks = $decoded['props']['web_blocks'];
        }
        foreach ($blocks as $block) {
            if (!empty($block['structure']['product']) && is_array($block['structure']['product'])) {
                return $block['structure']['product'];
            }
        }
        return array();
    }

    protected function product_images($product, $html)
    {
        $images = array();
        if (!empty($product['images']) && is_array($product['images'])) {
            foreach ($product['images'] as $image) {
                if (!is_array($image) || empty($image['images']) || !is_array($image['images'])) {
                    continue;
                }
                foreach (array('original', 'webp', 'png') as $key) {
                    if (!empty($image['images'][$key])) {
                        $images[] = $image['images'][$key];
                        break;
                    }
                }
            }
        }
        if (!empty($product['web_images']['all']) && is_array($product['web_images']['all'])) {
            foreach ($product['web_images']['all'] as $image) {
                if (!empty($image['gallery']['png'])) {
                    $images[] = $image['gallery']['png'];
                } elseif (!empty($image['gallery']['webp'])) {
                    $images[] = $image['gallery']['webp'];
                }
            }
        }
        if (preg_match_all('/property="og:image"\s+content="([^"]+)"/i', $html, $matches)) {
            $images = array_merge($images, $matches[1]);
        }
        $clean = array();
        foreach ($images as $url) {
            $url = html_entity_decode(trim((string) $url), ENT_QUOTES | ENT_HTML5, 'UTF-8');
            if ($this->is_product_image($url)) {
                $clean[] = $url;
            }
        }
        return array_values(array_unique($clean));
    }

    protected function specs_html($specs)
    {
        if (!is_array($specs) || !$specs) {
            return '';
        }
        $rows = array();
        $map = array(
            'barcode' => 'Barcode',
            'ingredients' => 'Ingredients',
            'gross_weight' => 'Weight (g)',
            'marketing_weight' => 'Marketing weight (g)',
            'dimensions' => 'Dimensions',
            'unit' => 'Unit',
        );
        foreach ($map as $key => $label) {
            if (empty($specs[$key]) || !is_scalar($specs[$key])) {
                continue;
            }
            $value = trim((string) $specs[$key]);
            if ($value === '') {
                continue;
            }
            $rows[] = '<li><strong>' . htmlspecialchars($label, ENT_QUOTES, 'UTF-8') . ':</strong> '
                . htmlspecialchars($value, ENT_QUOTES, 'UTF-8') . '</li>';
        }
        if (!empty($specs['countries_of_origin'][0]['name'])) {
            $rows[] = '<li><strong>Country of origin:</strong> '
                . htmlspecialchars($specs['countries_of_origin'][0]['name'], ENT_QUOTES, 'UTF-8') . '</li>';
        }
        if (!$rows) {
            return '';
        }
        return '<h4>Specifications</h4><ul>' . implode('', $rows) . '</ul>';
    }

    protected function is_product_image($url)
    {
        $url = strtolower((string) $url);
        if ($url === '' || strpos($url, 'placeholder') !== false) {
            return false;
        }
        return strpos($url, 'media.aiku.io') !== false
            || (bool) preg_match('#(\.jpg|\.jpeg|\.png|\.webp|\.avif)(\?|$)#i', $url);
    }
}
