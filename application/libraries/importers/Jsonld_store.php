<?php
defined('BASEPATH') OR exit('No direct script access allowed');

require_once APPPATH . 'libraries/importers/Importer_base.php';

class Jsonld_store extends Importer_base {

    protected $fetchLanguage = 'sv-SE,sv;q=0.9,en;q=0.8';

    public function extract($html, $url)
    {
        $ld = $this->primary_product($html);
        $offer = $this->ld_offer($ld);

        $name = '';
        if (!empty($ld['name'])) {
            $name = $this->clean_text($ld['name']);
        }
        if ($name === '') {
            $name = $this->clean_text($this->first_match('/property=["\']og:title["\'][^>]*content=["\']([^"\']+)/i', $html));
        }
        if ($name === '') {
            $name = $this->clean_text($this->first_match('/<h1[^>]*>(.*?)<\/h1>/is', $html));
        }
        if ($name === '') {
            $name = $this->clean_text($this->first_match('/<title>(.*?)<\/title>/is', $html));
            $name = preg_replace('/\s*[|\-]\s*.*$/u', '', $name);
        }

        $sku = '';
        if (!empty($ld['sku'])) {
            $sku = trim((string) $ld['sku']);
        }
        if ($sku === '' && !empty($offer['sku'])) {
            $sku = trim((string) $offer['sku']);
        }
        if ($sku === '') {
            $sku = $this->first_match('~/products/([a-z0-9]+)~i', $url);
        }

        $price = $this->parse_price(isset($offer['price']) ? $offer['price'] : 0);
        if ($price <= 0) {
            $price = $this->parse_price(isset($offer['lowPrice']) ? $offer['lowPrice'] : 0);
        }
        if ($price <= 0) {
            $price = $this->parse_price($this->first_match('/property=["\']product:price:amount["\'][^>]*content=["\']([^"\']+)/i', $html));
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
        if ($description === '') {
            $description = $this->clean_text($this->first_match('/property=["\']og:description["\'][^>]*content=["\']([^"\']+)/i', $html));
        }
        $details = $description !== '' ? '<p>' . htmlspecialchars($description, ENT_QUOTES, 'UTF-8') . '</p>' : '';
        if ($details === '') {
            $details = $this->fallback_details($html, $description);
        }
        if ($description === '' && $details !== '') {
            $description = $this->clean_text($details);
        }
        if ($description === '' && $name !== '') {
            $description = $name;
        }
        if ($details === '' && $name !== '') {
            $details = '<p>' . htmlspecialchars($name, ENT_QUOTES, 'UTF-8') . '</p>';
        }

        $brand = '';
        if (!empty($ld['brand']['name'])) {
            $brand = $ld['brand']['name'];
        } elseif (!empty($ld['brand']) && is_string($ld['brand'])) {
            $brand = $ld['brand'];
        }

        $images = array();
        if (!empty($ld['image'])) {
            $images = $this->flatten_image_list($ld['image']);
        }
        $og = $this->first_match('/property=["\']og:image["\'][^>]*content=["\']([^"\']+)/i', $html);
        if ($og !== '') {
            array_unshift($images, $og);
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

    protected function primary_product($html)
    {
        foreach ($this->json_ld_products($html) as $item) {
            $type = isset($item['@type']) ? $item['@type'] : '';
            if (is_array($type)) {
                $type = implode(',', $type);
            }
            if (stripos((string) $type, 'ProductGroup') !== false) {
                continue;
            }
            return $item;
        }
        $items = $this->json_ld_products($html);
        return isset($items[0]) ? $items[0] : array();
    }

    protected function fetch($url)
    {
        $htmlFile = trim((string) getenv('IMPORT_HTML_FILE'));
        if ($htmlFile !== '' && is_file($htmlFile)) {
            $cached = file_get_contents($htmlFile);
            if (is_string($cached) && $cached !== '') {
                return $cached;
            }
        }

        $cookie = tempnam(sys_get_temp_dir(), 'jsonldck');
        $ch = curl_init($url);
        curl_setopt_array($ch, array(
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_MAXREDIRS => 8,
            CURLOPT_TIMEOUT => 35,
            CURLOPT_CONNECTTIMEOUT => 12,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_COOKIEJAR => $cookie,
            CURLOPT_COOKIEFILE => $cookie,
            CURLOPT_USERAGENT => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124.0.0.0 Safari/537.36',
            CURLOPT_HTTPHEADER => array(
                'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,*/*;q=0.8',
                'Accept-Language: ' . $this->fetchLanguage,
                'Cache-Control: no-cache',
            ),
            CURLOPT_ENCODING => '',
        ));
        $html = curl_exec($ch);
        $code = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        if (is_file($cookie)) {
            @unlink($cookie);
        }
        if ($html === false || $code >= 400) {
            return '';
        }
        return $html;
    }
}
