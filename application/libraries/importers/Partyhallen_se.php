<?php
defined('BASEPATH') OR exit('No direct script access allowed');

require_once APPPATH . 'libraries/importers/Importer_base.php';

class Partyhallen_se extends Importer_base {

    protected $fetchLanguage = 'sv-SE,sv;q=0.9,en;q=0.8';

    public function extract($html, $url)
    {
        $page = $this->product_html($html);

        $name = $this->clean_text($this->first_match('/<h1[^>]*itemprop="name"[^>]*>(.*?)<\/h1>/is', $page));
        if ($name === '') {
            $name = $this->clean_text($this->first_match('/itemprop="name"[^>]*>(.*?)<\/h1>/is', $page));
        }
        if ($name === '') {
            $name = $this->clean_text($this->first_match('/<title>(.*?)<\/title>/is', $html));
            $name = preg_replace('/\s*-\s*Partyhallen\.se\s*$/i', '', $name);
        }

        $sku = $this->first_match('/id="prodProdno"[^>]*value="([^"]+)"/i', $page);
        if ($sku === '') {
            $sku = $this->first_match('/id="o_prodno"[^>]*>([^<]+)/i', $page);
        }
        if ($sku === '') {
            $sku = $this->first_match('/name="prodno"[^>]*value="([^"]+)"/i', $page);
        }
        if ($sku === '') {
            $sku = $this->first_match('/Art(?:ikel)?\s*nr:?\s*<\/abbr>\s*<span[^>]*>([^<]+)/i', $page);
        }
        if ($sku === '') {
            $sku = $this->first_match('/Art(?:ikel)?\s*nr:?\s*([0-9]+)/i', $page);
        }
        $sku = trim($sku);

        $price = $this->parse_price($this->first_match('/itemprop="price"\s+content="([^"]+)"/i', $page));
        if ($price <= 0) {
            $price = $this->parse_price($this->first_match('/class="priceNew"[^>]*>[\s\S]*?([0-9]+(?:[.,][0-9]+)?)\s*kr/i', $page));
        }
        if ($price <= 0) {
            $price = $this->parse_price($this->first_match('/id="updPrice"[^>]*>([^<]+)/i', $page));
        }
        if ($price <= 0) {
            $price = $this->parse_price($this->first_match('/class="priceRegular"[^>]*>[\s\S]*?([0-9]+(?:[.,][0-9]+)?)\s*kr/i', $page));
        }
        if ($price <= 0) {
            $price = $this->parse_price($this->first_match('/(?:rea\s+)?pris\s+([0-9]+(?:[.,][0-9]+)?)\s*kr/i', $page));
        }

        $compare = $this->parse_price($this->first_match('/class="priceOld"[^>]*>[\s\S]*?([0-9]+(?:[.,][0-9]+)?)\s*kr/i', $page));
        if ($compare <= 0) {
            $compare = $this->parse_price($this->first_match('/tidigare\s+pris[^0-9]*([0-9]+(?:[.,][0-9]+)?)\s*kr/i', $page));
        }
        if ($compare <= $price) {
            $compare = 0;
        }

        $availability = $this->first_match('/itemprop="availability"[^>]*href="[^"]*(InStock|OutOfStock|LimitedAvailability)/i', $page);
        if ($availability === '') {
            $availability = $this->first_match('/itemprop="availability"[^>]*content="[^"]*(InStock|OutOfStock|LimitedAvailability)/i', $page);
        }
        $stock = 0;
        if (stripos($availability, 'OutOfStock') !== false
            || preg_match('/class="stockstatustext[^"]*outofstock/i', $page)
            || stripos($page, 'Slut i lager') !== false) {
            $stock = 0;
        } elseif (stripos($availability, 'InStock') !== false || stripos($availability, 'LimitedAvailability') !== false) {
            $stock = 1;
        } elseif (preg_match('/class="stockstatustext[^"]*instock/i', $page) || stripos($page, 'Lagervara') !== false) {
            $stock = 1;
        }

        $detailsHtml = $this->first_match('/id="prodDescUpper"[^>]*>([\s\S]*?)<div id="descMovies">/i', $page);
        if ($detailsHtml === '') {
            $detailsHtml = $this->first_match('/id="prodDescUpper"[^>]*>([\s\S]*?)<\/div>\s*<div id="extracontainer"/i', $page);
        }
        $details = $this->clean_html($detailsHtml);
        $description = $this->clean_text(preg_replace('/<br\s*\/?>/i', ' ', $detailsHtml));
        if ($description === '') {
            $description = $this->clean_text($this->first_match('/itemprop="description"[^>]*>([\s\S]*?)<\/div>/i', $page));
        }
        if ($details === '' && $description !== '') {
            $details = '<p>' . htmlspecialchars($description, ENT_QUOTES, 'UTF-8') . '</p>';
        }

        $images = $this->collect_product_images($page, $url);
        $saved = $this->downloadImages ? $this->download_images($images, 20) : array();

        $brand = $this->first_match('/itemprop="brand"[^>]*content="([^"]+)"/i', $page);
        if ($brand === '') {
            $brand = $this->clean_text($this->first_match('/itemprop="brand"[^>]*>(.*?)<\/(?:span|div|a|meta)/is', $page));
        }

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

    protected function collect_product_images($page, $url)
    {
        $galleryHtml = $this->first_match('/id="prodImage"[^>]*>([\s\S]*?)<div id="prodInfo"/i', $page);
        if ($galleryHtml === '') {
            $galleryHtml = $this->first_match('/id="prodSwiper"[^>]*>([\s\S]*?)<div id="prodInfo"/i', $page);
        }
        $scoped = $galleryHtml !== '';
        $haystack = $scoped ? $galleryHtml : $page;

        $found = array();
        foreach (array(
            '#href="((?:https?:)?(?://[^/]+)?/(?:resource|upload)/[^"]+\.(?:jpg|jpeg|png|webp)[^"]*)"#i',
            '#(?:src|data-src|data-original|data-lazy)="((?:https?:)?(?://[^/]+)?/(?:resource|upload)/[^"]+\.(?:jpg|jpeg|png|webp)[^"]*)"#i',
        ) as $pattern) {
            if (preg_match_all($pattern, $haystack, $matches)) {
                foreach ($matches[1] as $src) {
                    $found[] = $src;
                }
            }
        }

        $slug = strtolower((string) basename((string) parse_url((string) $url, PHP_URL_PATH)));
        $slug = preg_replace('/\.[a-z0-9]+$/', '', $slug);
        $hasSlug = false;
        if ($slug !== '') {
            foreach ($found as $src) {
                if (stripos($src, $slug) !== false) {
                    $hasSlug = true;
                    break;
                }
            }
        }
        $byFile = array();
        foreach ($found as $src) {
            if (!$this->is_product_image($src)) {
                continue;
            }
            $file = strtolower(basename((string) parse_url($src, PHP_URL_PATH)));
            if ($file === '') {
                continue;
            }
            if (!$scoped && $hasSlug && strpos($file, $slug) === false) {
                continue;
            }
            if (!isset($byFile[$file])) {
                $byFile[$file] = $src;
            }
        }
        return array_values($byFile);
    }

    protected function product_html($html)
    {
        $chunk = $this->first_match('/id="productPageUpper"[^>]*>([\s\S]*?)<div id="prodRelCat"/i', $html);
        if ($chunk === '') {
            $chunk = $this->first_match('/id="productPageUpper"[^>]*>([\s\S]*?)<div id="prodReviews"/i', $html);
        }
        return $chunk !== '' ? $chunk : $html;
    }

    protected function is_product_image($url)
    {
        $url = strtolower((string) $url);
        if ($url === '' || strpos($url, 'placeholder') !== false) {
            return false;
        }
        if (preg_match('#/(kategoribilder|bildspel|partner|templates)/#i', $url)) {
            return false;
        }
        if (!preg_match('#(\.jpg|\.jpeg|\.png|\.webp)(\?|$)#i', $url)) {
            return false;
        }
        return strpos($url, '/resource/') !== false
            || strpos($url, '/upload/') !== false;
    }
}
