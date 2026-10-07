<?php
defined('BASEPATH') OR exit('No direct script access allowed');

require_once APPPATH . 'libraries/importers/Importer_base.php';

class Eselic_us extends Importer_base {

    public function extract($html, $url)
    {
        $items = $this->json_ld_products($html);
        $ld = isset($items[0]) ? $items[0] : array();
        $offer = $this->ld_offer($ld);

        $name = '';
        if (!empty($ld['name'])) {
            $name = $this->clean_text($ld['name']);
        }
        if ($name === '') {
            $name = $this->clean_text($this->first_match('/<h1[^>]*>(.*?)<\/h1>/is', $html));
        }

        $sku = !empty($ld['sku']) ? trim((string) $ld['sku']) : '';
        if ($sku === '') {
            $sku = strtolower((string) $this->first_match('~/products/([^/?#]+)~i', $url));
        }

        $price = $this->parse_price(isset($offer['price']) ? $offer['price'] : 0);
        if ($price <= 0) {
            $price = $this->parse_price(isset($offer['lowPrice']) ? $offer['lowPrice'] : 0);
        }
        if ($price <= 0) {
            $price = $this->parse_price($this->first_match('/"lowPrice"\s*:\s*"([0-9]+(?:\.[0-9]+)?)"/i', $html));
        }

        $compare = $this->parse_price(isset($offer['highPrice']) ? $offer['highPrice'] : 0);
        if ($compare <= $price) {
            $compare = 0;
        }

        $stock = 0;
        $availability = isset($offer['availability']) ? (string) $offer['availability'] : '';
        if (stripos($availability, 'InStock') !== false) {
            $stock = 1;
        }

        $description = '';
        if (!empty($ld['description'])) {
            $description = $this->clean_text($ld['description']);
        }
        $details = $description !== '' ? '<p>' . htmlspecialchars($description, ENT_QUOTES, 'UTF-8') . '</p>' : '';

        $images = array();
        if (!empty($ld['image'])) {
            $images = $this->flatten_image_list($ld['image']);
        }
        $saved = $this->downloadImages ? $this->download_images($images, 20) : array();

        return array(
            'name' => $name,
            'sku' => $sku,
            'price' => $price,
            'compare_price' => $compare,
            'brand' => 'Eselic',
            'description' => $description,
            'details' => $details,
            'stock' => $stock,
            'image' => isset($saved[0]) ? $saved[0] : '',
            'gallery' => array_slice($saved, 1),
            'seo_title' => $name,
            'seo_description' => function_exists('mb_substr') ? mb_substr($description, 0, 180) : substr($description, 0, 180),
        );
    }
}
