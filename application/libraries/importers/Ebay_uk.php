<?php
defined('BASEPATH') OR exit('No direct script access allowed');

require_once APPPATH . 'libraries/importers/Importer_base.php';

class Ebay_uk extends Importer_base {

    public function extract($html, $url)
    {
        $products = $this->json_ld_products($html);
        $ld = $products ? $products[0] : array();

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

        $sku = $this->first_match('#/itm/([0-9]{8,})#', $url);
        if ($sku === '' && !empty($ld['sku'])) {
            $sku = (string) $ld['sku'];
        }

        $price = 0.0;
        if (!empty($ld['offers']['price'])) {
            $price = $this->parse_price($ld['offers']['price']);
        } elseif (!empty($ld['offers'][0]['price'])) {
            $price = $this->parse_price($ld['offers'][0]['price']);
        }
        if ($price <= 0) {
            $price = $this->parse_price($this->first_match('/itemprop="price"\s+content="([^"]+)"/i', $html));
        }
        if ($price <= 0) {
            $price = $this->parse_price($this->first_match('/class="x-price-primary"[^>]*>.*?£\s*([0-9,.]+)/is', $html));
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
            $iframe = $this->first_match('/"(https:\/\/vi\.vipr\.ebaydesc\.com[^"]+)"/i', $html);
        }
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
            $images = is_array($ld['image']) ? $ld['image'] : array($ld['image']);
        }
        if (preg_match_all('/property="og:image"\s+content="([^"]+)"/i', $html, $matches)) {
            $images = array_merge($images, $matches[1]);
        }
        if (preg_match_all('#https://i\.ebayimg\.com/images/g/[^"\']+#i', $html, $matches)) {
            $images = array_merge($images, $matches[0]);
        }

        $saved = $this->downloadImages ? $this->download_images($images, 20) : array();

        return array(
            'name' => $name,
            'sku' => $sku,
            'price' => $price,
            'compare_price' => 0,
            'description' => $description,
            'details' => $details,
            'stock' => 1,
            'image' => isset($saved[0]) ? $saved[0] : '',
            'gallery' => array_slice($saved, 1),
            'seo_title' => $name,
            'seo_description' => mb_substr($description, 0, 180),
        );
    }
}
