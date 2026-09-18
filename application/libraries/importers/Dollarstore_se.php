<?php
defined('BASEPATH') OR exit('No direct script access allowed');

require_once APPPATH . 'libraries/importers/Importer_base.php';

class Dollarstore_se extends Importer_base {

    protected $fetchLanguage = 'sv-SE,sv;q=0.9,en;q=0.8';

    public function extract($html, $url)
    {
        $sku = $this->sku_from_url($url);
        $rsc = $this->rsc_product($html, $sku);

        $name = '';
        if (!empty($rsc['name'])) {
            $name = $this->clean_text($rsc['name']);
        }
        if ($name === '') {
            $name = $this->clean_text($this->first_match('/property="og:title"\s+content="([^"]+)"/i', $html));
        }
        if ($name === '') {
            $name = $this->clean_text($this->first_match('/<h1[^>]*>(.*?)<\/h1>/is', $html));
        }
        if ($name === '') {
            $name = $this->clean_text($this->first_match('/<title>(.*?)<\/title>/is', $html));
            $name = preg_replace('/\s*\|\s*Dollarstore\s*$/i', '', $name);
        }

        if ($sku === '' && !empty($rsc['articleNumber'])) {
            $sku = trim((string) $rsc['articleNumber']);
        }
        if ($sku === '') {
            $sku = $this->first_match('/Artikelnummer:\s*(?:<!--\s*-->)?\s*([0-9]+)/i', $html);
        }

        $price = 0.0;
        $compare = 0.0;
        if (!empty($rsc['price']) && is_array($rsc['price'])) {
            $unit = $this->parse_price(isset($rsc['price']['unitPriceIncludingVat']) ? $rsc['price']['unitPriceIncludingVat'] : 0);
            $discount = $this->parse_price(isset($rsc['price']['discountPriceIncludingVat']) ? $rsc['price']['discountPriceIncludingVat'] : 0);
            if ($discount > 0 && $discount < $unit) {
                $price = $discount;
                $compare = $unit;
            } else {
                $price = $unit;
            }
        }
        if ($price <= 0) {
            $price = $this->parse_price($this->first_match('/property="og:description"\s+content="[^"]*Pris:\s*([0-9]+(?:[.,][0-9]+)?)/i', $html));
        }
        if ($price <= 0) {
            $price = $this->parse_price($this->first_match('/role="heading"[^>]*>\s*([0-9]+(?:[.,][0-9]+)?)\s*kr/i', $html));
        }
        if ($price <= 0) {
            $price = $this->parse_price($this->first_match('/>([0-9]+(?:[.,][0-9]+)?)\s*kr<\/span>/i', $html));
        }

        $stock = 0;
        if (!empty($rsc['stockStatus']) && isset($rsc['stockStatus']['inStockQuantity'])) {
            $stock = (int) $rsc['stockStatus']['inStockQuantity'];
        }

        $description = '';
        if (!empty($rsc['description'])) {
            $description = $this->clean_text($rsc['description']);
        }
        if ($description === '') {
            $description = $this->clean_text($this->first_match('/property="og:description"\s+content="([^"]+)"/i', $html));
            $description = preg_replace('/\s*\|\s*Pris:\s*[0-9].*$/i', '', $description);
        }
        if ($description === '') {
            $description = $this->clean_text($this->first_match('/Beskrivning<\/p>\s*<p[^>]*>(.*?)<\/p>/is', $html));
        }

        $details = $this->details_html($rsc, $html, $description);

        $images = $this->product_images($rsc, $html);
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

    protected function sku_from_url($url)
    {
        return $this->first_match('#/(\d+)--[^/]*$#', $url);
    }

    protected function rsc_payload($html)
    {
        $raw = $this->first_match('/self\.__next_f\.push\(\[1,"([\s\S]*)"\]\)\s*<\/script>/i', $html);
        if ($raw === '') {
            return '';
        }
        $raw = str_replace(array('\\"', '\\n', '\\/', '\\u0026'), array('"', "\n", '/', '&'), $raw);
        return $raw;
    }

    protected function rsc_product($html, $sku)
    {
        $payload = $this->rsc_payload($html);
        if ($payload === '') {
            return array();
        }
        $sku = preg_replace('/[^0-9]/', '', (string) $sku);
        if ($sku === '') {
            $sku = $this->first_match('/"articleNumber":"([0-9]+)"/', $payload);
        }
        if ($sku === '') {
            return array();
        }
        $needle = '"articleNumber":"' . $sku . '"';
        $start = strpos($payload, $needle);
        if ($start === false) {
            return array();
        }
        $open = strrpos(substr($payload, 0, $start + 1), '{');
        if ($open === false) {
            $open = $start;
        }
        $chunk = substr($payload, $open, 12000);
        $end = strpos($chunk, '"relatedProducts"');
        if ($end !== false) {
            $chunk = substr($chunk, 0, $end);
        }
        $product = array(
            'articleNumber' => $sku,
            'name' => $this->first_match('/"name":"([^"]*)"/', $chunk),
            'description' => $this->first_match('/"description":"([^"]*)"/', $chunk),
            'stockStatus' => array(
                'inStockQuantity' => (int) $this->first_match('/"inStockQuantity":([0-9]+)/', $chunk),
            ),
            'price' => array(
                'unitPriceIncludingVat' => $this->first_match('/"unitPriceIncludingVat":([0-9]+(?:\.[0-9]+)?)/', $chunk),
                'discountPriceIncludingVat' => $this->first_match('/"discountPriceIncludingVat":([0-9]+(?:\.[0-9]+)?)/', $chunk),
            ),
            'fields' => array(
                'measurement' => $this->first_match('/"measurement":"([^"]*)"/', $chunk),
                'colour' => $this->first_match('/"colour":"([^"]*)"/', $chunk),
                'material' => $this->first_match('/"material":"([^"]*)"/', $chunk),
                'clothesSize' => $this->first_match('/"clothesSize":"([^"]*)"/', $chunk),
            ),
            'images' => array(),
        );
        if (preg_match_all('#"url":"(/storage/[^"]+)"#', $chunk, $matches)) {
            $product['images'] = $matches[1];
        }
        return $product;
    }

    protected function details_html($rsc, $html, $description)
    {
        $parts = array();
        $description = trim((string) $description);
        if ($description !== '') {
            $parts[] = '<p>' . htmlspecialchars($description, ENT_QUOTES, 'UTF-8') . '</p>';
        }
        $specs = array();
        $fields = isset($rsc['fields']) && is_array($rsc['fields']) ? $rsc['fields'] : array();
        $labels = array(
            'measurement' => 'Storlek',
            'colour' => 'Färg',
            'material' => 'Material',
            'clothesSize' => 'Klädstorlek',
        );
        foreach ($labels as $key => $label) {
            $value = isset($fields[$key]) ? trim((string) $fields[$key]) : '';
            if ($value !== '') {
                $specs[] = '<li><strong>' . htmlspecialchars($label, ENT_QUOTES, 'UTF-8') . ':</strong> '
                    . htmlspecialchars($value, ENT_QUOTES, 'UTF-8') . '</li>';
            }
        }
        if (!$specs) {
            $feature = $this->clean_text($this->first_match('/Produktegenskaper<\/p>\s*<ul>(.*?)<\/ul>/is', $html));
            if ($feature !== '') {
                $feature = preg_replace('/^\s*-\s*/', '', $feature);
                $specs[] = '<li>' . htmlspecialchars($feature, ENT_QUOTES, 'UTF-8') . '</li>';
            }
        }
        if ($specs) {
            $parts[] = '<h4>Produktegenskaper</h4><ul>' . implode('', $specs) . '</ul>';
        }
        return $this->clean_html(implode("\n", $parts));
    }

    protected function product_images($rsc, $html)
    {
        $images = array();
        if (!empty($rsc['images']) && is_array($rsc['images'])) {
            $images = array_merge($images, $rsc['images']);
        }
        if (preg_match_all('/property="og:image"\s+content="([^"]+)"/i', $html, $matches)) {
            $images = array_merge($images, $matches[1]);
        }
        $clean = array();
        foreach ($images as $url) {
            $url = html_entity_decode((string) $url, ENT_QUOTES | ENT_HTML5, 'UTF-8');
            $url = preg_replace('/\\\\+$/', '', $url);
            $url = preg_replace('/\?.*$/', '', $url);
            if ($this->is_product_image($url)) {
                $clean[] = $url;
            }
        }
        return array_values(array_unique($clean));
    }

    protected function is_product_image($url)
    {
        $url = strtolower((string) $url);
        if ($url === '' || strpos($url, '/storage/') === false) {
            return false;
        }
        if (strpos($url, 'logo') !== false || strpos($url, 'blad') !== false) {
            return false;
        }
        return (bool) preg_match('#(\.jpg|\.jpeg|\.png|\.webp)(\?|$)#i', $url);
    }
}
