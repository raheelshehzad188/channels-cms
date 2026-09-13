<?php
defined('BASEPATH') OR exit('No direct script access allowed');

require_once APPPATH . 'libraries/importers/Importer_base.php';

class Godropship_uk extends Importer_base {

    public function extract($html, $url)
    {
        $products = $this->json_ld_products($html);
        $ld = $products ? $products[0] : array();

        $name = '';
        if (!empty($ld['name'])) {
            $name = $this->clean_text($ld['name']);
        }
        if ($name === '') {
            $name = $this->clean_text($this->first_match('/<meta[^>]+name=["\']description["\'][^>]+content=["\']([^"\']+)/i', $html));
        }
        if ($name === '') {
            $name = $this->clean_text($this->first_match('/<title>(.*?)<\/title>/is', $html));
            $name = preg_replace('/^\s*Dropshipping\s+/i', '', $name);
            $name = preg_replace('/\s*-\s*Go Dropship.*$/i', '', $name);
        }

        $sku = '';
        if (!empty($ld['sku'])) {
            $sku = trim((string) $ld['sku']);
        }
        if ($sku === '') {
            $sku = $this->first_match('/id="hidden_sku"[^>]*value="([^"]+)"/i', $html);
        }
        if ($sku === '') {
            $sku = $this->first_match('/id="change_child_sku"[^>]*>([^<]+)/i', $html);
        }
        if ($sku === '') {
            $sku = $this->first_match('/Item Code\s*:?\s*<\/[^>]+>\s*([^<]+)/i', $html);
        }

        $price = 0.0;
        if (!empty($ld['offers']['price'])) {
            $price = $this->parse_price($ld['offers']['price']);
        }
        if ($price <= 0) {
            $price = $this->parse_price($this->first_match('/id="change_child_price"[^>]*>\s*([^<]+)/i', $html));
        }
        if ($price <= 0) {
            $price = $this->parse_price($this->first_match('/class="details-price-n"[^>]*>\s*([^<]+)/i', $html));
        }

        $stock = (int) $this->first_match('/id="change_child_inventory"[^>]*>\s*([0-9]+)/i', $html);
        if ($stock <= 0 && !empty($ld['offers']['availability']) && stripos((string) $ld['offers']['availability'], 'InStock') !== false) {
            $stock = 1;
        }

        $description = '';
        $details = $this->first_match('/id="change_child_des"[^>]*>([\s\S]*?)<\/div>/is', $html);
        if ($details === '' && !empty($ld['description'])) {
            $details = $ld['description'];
        }
        if (!empty($ld['description'])) {
            $description = $this->clean_text($ld['description']);
        }
        if ($description === '') {
            $description = $this->clean_text($details);
        }

        $images = array();
        if (!empty($ld['image'])) {
            $images = array_merge($images, $this->flatten_image_list($ld['image']));
        }
        if (preg_match_all('#https?://www\.godropship\.co\.uk/uploadfile/images/[^"\'\s>]+#i', $html, $matches)) {
            $images = array_merge($images, $matches[0]);
        }
        $images = array_merge($images, $this->html_image_urls(isset($ld['description']) ? $ld['description'] : ''));
        $images = array_values(array_filter($images, array($this, 'is_product_image')));

        $saved = $this->downloadImages ? $this->download_images($images, 20) : array();

        return array(
            'name' => $name,
            'sku' => $sku,
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

    protected function is_product_image($url)
    {
        $url = strtolower((string) $url);
        if ($url === '') {
            return false;
        }
        if (strpos($url, '/template/') !== false || strpos($url, '/imgicon/') !== false) {
            return false;
        }
        if (strpos($url, '/uploadfile/products/s/') !== false) {
            return false;
        }
        return (bool) preg_match('#(\.jpg|\.jpeg|\.png|\.webp|\.gif)(\?|$)#i', $url)
            || strpos($url, '/uploadfile/images/') !== false
            || strpos($url, 'erpbft.com') !== false;
    }
}
