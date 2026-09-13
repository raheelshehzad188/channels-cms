<?php
defined('BASEPATH') OR exit('No direct script access allowed');

require_once APPPATH . 'libraries/importers/Importer_base.php';

class Amazon_uk extends Importer_base {

    public function extract($html, $url)
    {
        $name = $this->clean_text($this->first_match('/id="productTitle"[^>]*>(.*?)<\/span>/is', $html));
        if ($name === '') {
            $name = $this->clean_text($this->first_match('/<title>(.*?)<\/title>/is', $html));
            $name = preg_replace('/\s*:\s*Amazon\..*$/i', '', $name);
        }

        $sku = $this->first_match('#/dp/([A-Z0-9]{8,13})#i', $url);
        if ($sku === '') {
            $sku = $this->first_match('/"asin"\s*:\s*"([A-Z0-9]{8,13})"/i', $html);
        }

        $price = $this->price_from_a_price_whole($html);
        if ($price <= 0) {
            $price = $this->parse_price($this->first_match('/id="twister-plus-price-data-price"\s+value="([^"]+)"/i', $html));
        }
        if ($price <= 0) {
            $price = $this->parse_price($this->first_match('/"priceAmount"\s*:\s*([0-9.]+)/i', $html));
        }
        if ($price <= 0) {
            $price = $this->parse_price($this->first_match('/class="a-offscreen">\s*([^<]*\d[^<]*)<\/span>/i', $html));
        }

        $compare = $this->parse_price($this->first_match('/apex-basisprice-offscreen-label[^>]*>([^<]+)/i', $html));
        if ($compare <= 0) {
            $compare = $this->parse_price($this->first_match('/RRP:\s*([^<]+)/i', $html));
        }

        $description = $this->clean_text($this->first_match('/id="bookDescription_feature_div".*?expander-content[^>]*>(.*?)<\/div>/is', $html));
        if ($description === '') {
            $description = $this->clean_text($this->first_match('/id="productDescription"[^>]*>(.*?)<\/div>/is', $html));
        }
        if ($description === '') {
            $bullets = $this->first_match('/id="feature-bullets".*?<ul[^>]*>(.*?)<\/ul>/is', $html);
            $description = $this->clean_text($bullets);
        }

        $detailsParts = array();
        $detailPatterns = array(
            '/id="pqv-feature-bullets"[^>]*>([\s\S]*?)<\/div>\s*(?:<div|<\/div>)/i',
            '/id="feature-bullets"[^>]*>[\s\S]*?(<ul[^>]*>[\s\S]*?<\/ul>)/i',
            '/id="featurebullets_feature_div"[^>]*>[\s\S]*?(<ul[^>]*>[\s\S]*?<\/ul>)/i',
            '/id="productFactsDesktop_feature_div"[^>]*>([\s\S]*?)<div id="/i',
            '/id="productOverview_feature_div"[^>]*>([\s\S]*?)<div id="/i',
            '/id="productDescription"[^>]*>([\s\S]*?)<\/div>/i',
            '/id="bookDescription_feature_div".*?expander-content[^>]*>([\s\S]*?)<\/div>/is',
            '/id="aplus_feature_div"[^>]*>([\s\S]*?)<div id="(?:HLCXComparisonTable|ask-btf_feature_div|productDetails_feature_div|dp-ads-center-promo_feature_div)"/i',
        );
        foreach ($detailPatterns as $pattern) {
            $chunk = $this->first_match($pattern, $html);
            if ($chunk === '' || stripos($chunk, '.aplus-v2') !== false) {
                continue;
            }
            if ($this->clean_text($chunk) === '') {
                continue;
            }
            $detailsParts[] = $chunk;
        }
        $details = implode("\n", $detailsParts);
        if ($description === '' && $details !== '') {
            $description = $this->clean_text($details);
            if (function_exists('mb_substr')) {
                $description = mb_substr($description, 0, 500);
            } else {
                $description = substr($description, 0, 500);
            }
        }

        $images = array();
        if (preg_match_all('/"hiRes"\s*:\s*"(https:[^"]+)"/i', $html, $matches)) {
            $images = $matches[1];
        }
        if (!$images && preg_match('/id="landingImage"[^>]*(?:data-old-hires|src)="([^"]+)"/i', $html, $match)) {
            $images[] = $match[1];
        }

        $stock = (stripos($html, 'Currently unavailable') !== false || stripos($html, 'Out of stock') !== false) ? 0 : 10;
        $saved = $this->downloadImages ? $this->download_images($images, 20) : array();

        return array(
            'name' => $name,
            'sku' => $sku,
            'price' => $price,
            'compare_price' => $compare > $price ? $compare : 0,
            'description' => $description,
            'details' => $details,
            'stock' => $stock,
            'image' => isset($saved[0]) ? $saved[0] : '',
            'gallery' => array_slice($saved, 1),
            'seo_title' => $name,
            'seo_description' => mb_substr($description, 0, 180),
        );
    }

    /**
     * Buy-box listed price: <span class="a-price-whole">10<span class="a-price-decimal">.</span></span>
     * plus optional <span class="a-price-fraction">44</span>.
     */
    protected function price_from_a_price_whole($html)
    {
        $scope = $html;
        if (preg_match('/class="[^"]*priceToPay[^"]*"[\s\S]{0,600}/i', $html, $block)) {
            $scope = $block[0];
        } elseif (preg_match('/id="corePriceDisplay_desktop_feature_div"[\s\S]{0,2500}/i', $html, $block)) {
            $scope = $block[0];
        }

        if (!preg_match('/class="a-price-whole"[^>]*>([\s\S]*?)<\/span>/i', $scope, $match)) {
            if ($scope === $html || !preg_match('/class="a-price-whole"[^>]*>([\s\S]*?)<\/span>/i', $html, $match)) {
                return 0.0;
            }
            $scope = $html;
        }

        $whole = $this->clean_text($match[1]);
        $fraction = $this->clean_text($this->first_match('/class="a-price-fraction"[^>]*>([^<]*)/i', $scope));
        if ($whole === '') {
            return 0.0;
        }
        if ($fraction !== '' && !preg_match('/\.\d/', $whole)) {
            $whole = rtrim($whole, '.') . '.' . $fraction;
        }
        return $this->parse_price($whole);
    }
}
