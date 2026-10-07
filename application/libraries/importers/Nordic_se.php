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

        $details = $this->article_details_html($article, $ld, $html);
        $description = $this->clean_text($details);
        if ($description === '' && !empty($ld['description'])) {
            $description = $this->clean_text($ld['description']);
        }
        if ($description === '') {
            $description = $this->clean_text($this->first_match('/property=["\']og:description["\'][^>]*content=["\']([^"\']+)/i', $html));
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

        $out = array(
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
            'seo_description' => function_exists('mb_substr') ? mb_substr($description, 0, 180) : substr($description, 0, 180),
        );
        if (!empty($article['shippingTime']) && is_array($article['shippingTime'])) {
            $min = isset($article['shippingTime']['min']) ? (int) $article['shippingTime']['min'] : 0;
            $max = isset($article['shippingTime']['max']) ? (int) $article['shippingTime']['max'] : 0;
            if ($min > 0 || $max > 0) {
                $out['ship_min_days'] = $min > 0 ? $min : $max;
                $out['ship_max_days'] = $max > 0 ? $max : $min;
            }
        }
        return $out;
    }

    protected function article_details_html($article, $ld, $html)
    {
        $raw = '';
        if (!empty($article['description'])) {
            $raw = (string) $article['description'];
        } elseif (!empty($ld['description'])) {
            $raw = (string) $ld['description'];
        } else {
            $raw = $this->first_match('/property=["\']og:description["\'][^>]*content=["\']([^"\']+)/i', $html);
        }
        $raw = trim($raw);
        if ($raw === '') {
            return '';
        }
        if (!preg_match('/<[a-z][\s\S]*>/i', $raw)) {
            $raw = htmlspecialchars($raw, ENT_QUOTES, 'UTF-8');
            $raw = nl2br($raw, false);
        }
        $raw = preg_replace('/<br\s*\/?>/i', '</p><p>', $raw);
        return $this->clean_html('<p>' . $raw . '</p>');
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
        $paths = array(
            array('props', 'pageProps', 'data', 'article'),
            array('props', 'pageProps', 'article'),
            array('props', 'pageProps', 'data', 'product'),
            array('props', 'pageProps', 'product'),
        );
        foreach ($paths as $path) {
            $node = $decoded;
            foreach ($path as $key) {
                if (!is_array($node) || !isset($node[$key])) {
                    $node = null;
                    break;
                }
                $node = $node[$key];
            }
            if (is_array($node) && (!empty($node['description']) || !empty($node['title']))) {
                return $node;
            }
        }
        $found = $this->find_article_node($decoded, 0);
        return $found ? $found : array();
    }

    protected function find_article_node($node, $depth)
    {
        if ($depth > 8 || !is_array($node)) {
            return null;
        }
        if (!empty($node['title']) && (isset($node['price']) || !empty($node['images']) || !empty($node['description'])) && (isset($node['articleId']) || isset($node['sku']) || isset($node['mainImage']))) {
            return $node;
        }
        foreach ($node as $child) {
            $found = $this->find_article_node($child, $depth + 1);
            if ($found) {
                return $found;
            }
        }
        return null;
    }
}
