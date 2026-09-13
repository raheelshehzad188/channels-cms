<?php
defined('BASEPATH') OR exit('No direct script access allowed');

require_once APPPATH . 'libraries/importers/Importer_base.php';

class Nordic_se extends Importer_base {

    public function extract($html, $url)
    {
        $article = $this->next_article($html);
        $products = $this->json_ld_products($html);
        $ld = $products ? $products[0] : array();

        $name = '';
        if (!empty($article['title'])) {
            $name = $this->clean_text($article['title']);
        }
        if ($name === '' && !empty($ld['name'])) {
            $name = $this->clean_text($ld['name']);
        }
        if ($name === '') {
            $name = $this->clean_text($this->first_match('/<h1[^>]*>(.*?)<\/h1>/is', $html));
        }
        $name = preg_replace('/\s*\|\s*(Fyndiq|CDON).*$/i', '', $name);

        $sku = '';
        if (!empty($article['sku'])) {
            $sku = trim((string) $article['sku']);
        }
        if ($sku === '' && !empty($ld['sku'])) {
            $sku = trim((string) $ld['sku']);
        }
        if ($sku === '') {
            $sku = $this->first_match('#/produkt/[^/]+-([a-f0-9]{8,})/?#i', $url);
        }

        $price = 0.0;
        if (!empty($article['price']['amount'])) {
            $price = $this->parse_price($article['price']['amount']);
        }
        if ($price <= 0 && !empty($ld['offers']['price'])) {
            $price = $this->parse_price($ld['offers']['price']);
        }

        $stock = 0;
        if (isset($article['stock'])) {
            $stock = (int) $article['stock'];
        }
        if ($stock <= 0 && !empty($ld['offers']['availability']) && stripos((string) $ld['offers']['availability'], 'InStock') !== false) {
            $stock = 1;
        }
        if ($stock <= 0 && stripos($html, 'I lager') !== false) {
            $stock = 1;
        }

        $description = '';
        $details = '';
        if (!empty($article['description'])) {
            $details = $article['description'];
            $description = $this->clean_text($article['description']);
        }
        if ($description === '' && !empty($ld['description'])) {
            $details = $details !== '' ? $details : $ld['description'];
            $description = $this->clean_text($ld['description']);
        }

        $images = array();
        if (!empty($article['mainImage'])) {
            $images[] = $article['mainImage'];
        }
        if (!empty($article['images']) && is_array($article['images'])) {
            foreach ($article['images'] as $image) {
                if (is_array($image) && !empty($image['url'])) {
                    $images[] = $image['url'];
                } elseif (is_string($image)) {
                    $images[] = $image;
                }
            }
        }
        if (!empty($ld['image'])) {
            $images = array_merge($images, $this->flatten_image_list($ld['image']));
        }
        $images = array_values(array_filter($images));

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

    protected function next_article($html)
    {
        $raw = $this->first_match('/<script id="__NEXT_DATA__"[^>]*>(.*?)<\/script>/is', $html);
        if ($raw === '') {
            return array();
        }
        $decoded = json_decode($raw, true);
        if (!is_array($decoded)) {
            return array();
        }
        $article = array();
        if (!empty($decoded['props']['pageProps']['data']['article']) && is_array($decoded['props']['pageProps']['data']['article'])) {
            $article = $decoded['props']['pageProps']['data']['article'];
        }
        return $article;
    }
}
