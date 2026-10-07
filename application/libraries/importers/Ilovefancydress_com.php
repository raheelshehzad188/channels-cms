<?php
defined('BASEPATH') OR exit('No direct script access allowed');

require_once APPPATH . 'libraries/importers/Importer_base.php';

class Ilovefancydress_com extends Importer_base {

    protected $fetchLanguage = 'en-GB,en;q=0.9';

    public function extract($html, $url)
    {
        $ld = $this->primary_product($html);
        $offer = $this->ld_offer($ld);

        $name = '';
        if (!empty($ld['name'])) {
            $name = $this->clean_text($ld['name']);
            $name = preg_replace('/\s*-\s*(X-?Small|Small|Medium|Large|X-?Large|XX-?Large|One Size)\s*$/i', '', $name);
        }
        if ($name === '') {
            $name = $this->clean_text($this->first_match('/<h1[^>]*>(.*?)<\/h1>/is', $html));
        }

        $sku = '';
        if (!empty($ld['sku'])) {
            $sku = trim((string) $ld['sku']);
        }
        if ($sku === '') {
            $sku = $this->first_match('/\b(ILFD[0-9]+[A-Z]*)\b/i', $html);
        }

        $price = $this->parse_price(isset($offer['price']) ? $offer['price'] : 0);
        if ($price <= 0) {
            $price = $this->parse_price($this->first_match('/([0-9]+(?:\.[0-9]{2})?)\s*(?:GBP|£)/i', $html));
        }

        $availability = isset($offer['availability']) ? (string) $offer['availability'] : '';
        $stock = (stripos($availability, 'InStock') !== false) ? 1 : 0;

        $description = '';
        if (!empty($ld['description'])) {
            $description = $this->clean_text($ld['description']);
        }
        $details = $description !== '' ? '<p>' . htmlspecialchars($description, ENT_QUOTES, 'UTF-8') . '</p>' : '';

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
            'ship_min_days' => 1,
            'ship_max_days' => 3,
        );
    }

    protected function primary_product($html)
    {
        $group = array();
        foreach ($this->ld_graph($html) as $item) {
            $type = isset($item['@type']) ? $item['@type'] : '';
            if (is_array($type)) {
                $type = implode(',', $type);
            }
            if (stripos((string) $type, 'ProductGroup') === false) {
                continue;
            }
            $group = $item;
            $variants = isset($item['hasVariant']) && is_array($item['hasVariant']) ? $item['hasVariant'] : array();
            foreach ($variants as $variant) {
                if (!is_array($variant)) {
                    continue;
                }
                $offer = $this->ld_offer($variant);
                $availability = isset($offer['availability']) ? (string) $offer['availability'] : '';
                if (stripos($availability, 'InStock') !== false || $offer) {
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
        return $group;
    }

    protected function ld_graph($html)
    {
        $out = array();
        if (!preg_match_all('~<script[^>]*type=["\']?application/ld(?:\+|&#x2B;)json["\']?[^>]*>(.*?)</script>~is', $html, $matches)) {
            return $out;
        }
        foreach ($matches[1] as $json) {
            $decoded = $this->decode_ld_json($json);
            if (!is_array($decoded)) {
                continue;
            }
            if (isset($decoded['@graph']) && is_array($decoded['@graph'])) {
                foreach ($decoded['@graph'] as $item) {
                    if (is_array($item)) {
                        $out[] = $item;
                    }
                }
            } else {
                $out[] = $decoded;
            }
        }
        return $out;
    }
}
