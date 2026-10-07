<?php
defined('BASEPATH') OR exit('No direct script access allowed');

require_once APPPATH . 'libraries/importers/Importer_base.php';

class Partykungen_se extends Importer_base {

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
            $name = preg_replace('/\s*[|\-]\s*Partykungen.*$/i', '', $name);
        }

        $sku = '';
        if (!empty($ld['sku'])) {
            $sku = trim((string) $ld['sku']);
        }
        if ($sku === '' && !empty($ld['mpn'])) {
            $sku = trim((string) $ld['mpn']);
        }
        if ($sku === '') {
            $sku = $this->clean_text($this->first_match('/Art\.?\s*nr\.?\s*<\/[^>]+>\s*[^<]*<[^>]+>([^<]+)/i', $html));
        }
        if ($sku === '') {
            $sku = $this->first_match('/Art\.?\s*nr\.?\s*([0-9]+(?:-[0-9]+)?)/i', $html);
        }
        if ($sku === '' && preg_match('/-(\d{4,})(?:-\d+)?\.html/i', (string) $url, $match)) {
            $sku = $match[1];
        }

        $price = $this->parse_price(isset($offer['price']) ? $offer['price'] : 0);
        if ($price <= 0) {
            $price = $this->parse_price($this->first_match('/itemprop=["\']price["\'][^>]*content=["\']([^"\']+)/i', $html));
        }
        if ($price <= 0) {
            $price = $this->parse_price($this->first_match('/([0-9]+(?:[.,][0-9]+)?)\s*kr/i', $html));
        }

        $compare = $this->parse_price(isset($offer['priceSpecification']['referencePrice']) ? $offer['priceSpecification']['referencePrice'] : 0);
        if ($compare <= 0) {
            $compare = $this->parse_price($this->first_match('/(?:ordinarie|tidigare)\s+pris[^0-9]*([0-9]+(?:[.,][0-9]+)?)\s*kr/i', $html));
        }
        if ($compare <= $price) {
            $compare = 0;
        }

        $availability = isset($offer['availability']) ? (string) $offer['availability'] : '';
        $stock = 0;
        if (stripos($availability, 'OutOfStock') !== false || stripos($html, 'Slut i lager') !== false) {
            $stock = 0;
        } elseif (stripos($availability, 'InStock') !== false || stripos($html, 'I lager') !== false) {
            $qty = (int) $this->first_match('/I lager\s*\(\s*([0-9]+)\s*st/i', $html);
            $stock = $qty > 0 ? $qty : 1;
        }

        $description = '';
        if (!empty($ld['description'])) {
            $description = $this->clean_text($ld['description']);
        }
        if ($description === '') {
            $description = $this->clean_text($this->first_match('/property=["\']og:description["\'][^>]*content=["\']([^"\']+)/i', $html));
        }
        $details = '';
        if (!empty($ld['description'])) {
            $details = $this->clean_html('<p>' . $ld['description'] . '</p>');
        }
        if ($details === '' && $description !== '') {
            $details = '<p>' . htmlspecialchars($description, ENT_QUOTES, 'UTF-8') . '</p>';
        }

        $brand = '';
        if (!empty($ld['brand'])) {
            $brandVal = $ld['brand'];
            if (is_array($brandVal) && isset($brandVal['name'])) {
                $brand = $brandVal['name'];
            } elseif (is_string($brandVal)) {
                $brand = $brandVal;
            }
        }

        $images = $this->collect_product_images($html, $ld, $sku, $url);
        $saved = $this->downloadImages ? $this->download_images($images, 20) : array();

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
            'ship_min_days' => 1,
            'ship_max_days' => 3,
        );
    }

    protected function primary_product($html)
    {
        $items = $this->json_ld_products($html);
        $group = array();
        foreach ($items as $item) {
            $type = isset($item['@type']) ? $item['@type'] : '';
            if (is_array($type)) {
                $type = implode(',', $type);
            }
            $type = (string) $type;
            if (stripos($type, 'ProductGroup') !== false) {
                $group = $item;
                if (!empty($item['hasVariant'][0]) && is_array($item['hasVariant'][0])) {
                    $variant = $item['hasVariant'][0];
                    if (empty($variant['description']) && !empty($item['description'])) {
                        $variant['description'] = $item['description'];
                    }
                    if (empty($variant['brand']) && !empty($item['brand'])) {
                        $variant['brand'] = $item['brand'];
                    }
                    if (empty($variant['name']) && !empty($item['name'])) {
                        $variant['name'] = $item['name'];
                    }
                    return $variant;
                }
            }
        }
        foreach ($items as $item) {
            $type = isset($item['@type']) ? $item['@type'] : '';
            if (is_array($type)) {
                $type = implode(',', $type);
            }
            if (stripos((string) $type, 'ProductGroup') === false && stripos((string) $type, 'Product') !== false) {
                return $item;
            }
        }
        return $group;
    }

    protected function collect_product_images($html, $ld, $sku, $url)
    {
        $urls = array();
        if (!empty($ld['image'])) {
            $urls = array_merge($urls, $this->flatten_image_list($ld['image']));
        }
        if (preg_match_all('#https?://images\.partyking\.org/[^"\'\s>]+#i', (string) $html, $matches)) {
            $urls = array_merge($urls, $matches[0]);
        }

        $token = '';
        if (preg_match('/(\d{4,})(?:-\d+)?$/', (string) $sku, $match)) {
            $token = $match[1];
        }
        if ($token === '' && preg_match('/-(\d{4,})(?:-\d+)?(?:\.html)?$/i', (string) parse_url($url, PHP_URL_PATH), $match)) {
            $token = $match[1];
        }

        $out = array();
        $seen = array();
        foreach ($urls as $raw) {
            $image = $this->upgrade_image_url($raw);
            if ($image === '' || !preg_match('#/products/original/#i', $image)) {
                continue;
            }
            if (preg_match('#\.(svg|gif)(?:$|\?)#i', $image)) {
                continue;
            }
            if ($token !== '' && stripos($image, $token) === false) {
                continue;
            }
            $key = strtolower(preg_replace('#\?.*$#', '', $image));
            if (isset($seen[$key])) {
                continue;
            }
            $seen[$key] = 1;
            $out[] = $image;
        }
        if ($out) {
            return $out;
        }

        $fallback = array();
        foreach ($urls as $raw) {
            $image = $this->upgrade_image_url($raw);
            if ($image === '' || preg_match('#\.(svg|gif)(?:$|\?)#i', $image)) {
                continue;
            }
            $key = strtolower(preg_replace('#\?.*$#', '', $image));
            if (isset($seen[$key])) {
                continue;
            }
            $seen[$key] = 1;
            $fallback[] = $image;
        }
        return $fallback;
    }

    protected function upgrade_image_url($url)
    {
        $url = $this->asset_url($url);
        $url = preg_replace('#https?://images\.partyking\.org/fit-in/\d+x\d+/#i', 'https://images.partyking.org/', $url);
        return $url;
    }
}
