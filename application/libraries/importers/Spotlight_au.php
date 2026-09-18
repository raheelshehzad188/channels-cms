<?php
defined('BASEPATH') OR exit('No direct script access allowed');

require_once APPPATH . 'libraries/importers/Importer_base.php';

class Spotlight_au extends Importer_base {

    protected $fetchLanguage = 'en-AU,en;q=0.9';

    public function extract($html, $url)
    {
        $name = $this->clean_text($this->first_match('/class="pdp-title"[^>]*>(.*?)<\/span>/is', $html));
        if ($name === '') {
            $name = $this->clean_text($this->first_match('/<h1[^>]*>(.*?)<\/h1>/is', $html));
        }
        if ($name === '') {
            $name = $this->clean_text($this->first_match('/property="og:title"\s+content="([^"]+)"/i', $html));
        }
        if ($name === '') {
            $name = $this->clean_text($this->first_match('/<title>(.*?)<\/title>/is', $html));
        }
        $name = preg_replace('/\s*\|\s*Spotlight(?:\s+Australia|\s+New Zealand)?\s*$/i', '', $name);
        $name = trim($name);

        $sku = $this->first_match('/itemprop="sku"\s+content="([^"]+)"/i', $html);
        if ($sku === '') {
            $sku = $this->first_match('/data-gtm-sku="([^"]+)"/i', $html);
        }
        if ($sku === '') {
            $sku = $this->first_match("/'productId'\s*:\s*'([^']+)'/", $html);
        }
        if ($sku === '') {
            $sku = strtoupper($this->first_match('#/(BP[A-Z0-9]+)(?:-[a-z0-9]+)?(?:/|$|\?)#i', $url));
        }

        $price = $this->parse_price($this->first_match('/data-gtm-price="([^"]+)"/i', $html));
        if ($price <= 0) {
            $price = $this->parse_price($this->first_match("/'productSalePrice'\s*:\s*'([^']+)'/", $html));
        }
        if ($price <= 0) {
            $price = $this->parse_price($this->first_match('/class="price price-standard"[^>]*>[\s\S]*?class="amount">\s*([^<]+)/i', $html));
        }
        if ($price <= 0) {
            $price = $this->parse_price($this->first_match('/itemprop="price"\s+content="([^"]+)"/i', $html));
        }

        $compare = $this->parse_price($this->first_match('/data-gtm-price-rrp="([^"]+)"/i', $html));
        if ($compare <= $price) {
            $compare = 0;
        }

        $stock = 0;
        $status = '';
        if (preg_match_all('/dlStockLevelStatus\s*=\s*"([^"]+)"/', $html, $matches)) {
            $status = (string) end($matches[1]);
        }
        if ($status === '') {
            $status = $this->first_match("/'product-stock-status'\s*:\s*'([^']+)'/", $html);
        }
        if ($status === '') {
            $status = $this->first_match('/itemprop="availability"\s+href="[^"]*(InStock|OutOfStock)/i', $html);
        }
        if (preg_match_all("/dlStockLevel\s*=\s*'([0-9]+)'/", $html, $matches)) {
            $stock = (int) end($matches[1]);
        }
        if ($stock <= 0) {
            $stock = (int) $this->first_match("/'product-stock-qty'\s*:\s*'([0-9]+)'/", $html);
        }
        if (stripos($status, 'Out') !== false) {
            $stock = 0;
        } elseif ($stock <= 0 && stripos($status, 'In Stock') !== false) {
            $stock = 1;
        } elseif ($stock <= 0 && stripos($status, 'InStock') !== false) {
            $stock = 1;
        }

        $brand = $this->first_match('/itemprop="brand"\s+content="([^"]+)"/i', $html);
        if ($brand === '') {
            $brand = $this->first_match('/"brand"\s*:\s*"([^"]+)"/i', $html);
        }

        $description = $this->clean_text($this->first_match('/itemprop="description"\s+content="([^"]+)"/i', $html));
        if ($description === '') {
            $description = $this->clean_text($this->first_match('/property="og:description"\s+content="([^"]+)"/i', $html));
        }

        $details = $this->details_html($html, $description);

        $images = array();
        if (preg_match_all('#(?:src|href|content)="([^"]+/medias/(?:productHero|responsiveProduct)-[^"]+)"#i', $html, $matches)) {
            $images = array_merge($images, $matches[1]);
        }
        if (preg_match_all('/property="og:image"\s+content="([^"]+)"/i', $html, $matches)) {
            $images = array_merge($images, $matches[1]);
        }
        $images = array_values(array_unique(array_filter($images, array($this, 'is_product_image'))));

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
        );
    }

    protected function fetch($url)
    {
        $cookie = tempnam(sys_get_temp_dir(), 'splck');
        $ch = curl_init($url);
        curl_setopt_array($ch, array(
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_MAXREDIRS => 15,
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
                'Referer: https://www.spotlightstores.com/',
            ),
            CURLOPT_ENCODING => '',
        ));
        $html = curl_exec($ch);
        $code = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $final = (string) curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
        curl_close($ch);
        if (is_file($cookie)) {
            @unlink($cookie);
        }
        if ($html === false || $code >= 400) {
            return '';
        }
        if (stripos($final, 'queue.spotlightstores.com') !== false || stripos($html, 'queueittoken') !== false) {
            if (stripos($html, 'pdp-title') === false && stripos($html, 'itemprop="sku"') === false) {
                return '';
            }
        }
        return $html;
    }

    protected function details_html($html, $description)
    {
        $parts = array();
        $overview = $this->first_match('/class="[^"]*tab-overview[^"]*"[\s\S]*?<div class="container panel-body">([\s\S]*?)<\/div>/i', $html);
        $overview = $this->clean_html($overview);
        if ($overview !== '') {
            $parts[] = $overview;
        }

        $features = $this->first_match('/class="product-details-list pdp-item-list"[^>]*>([\s\S]*?)<\/dl>/i', $html);
        $featuresHtml = $this->definition_list_html($features);
        if ($featuresHtml !== '') {
            $parts[] = $featuresHtml;
        }

        $specs = $this->first_match('/data-component="pdp\/classificationTabs\/pdpSpecificationTab"[\s\S]*?<dl class="product-details-list"[^>]*>([\s\S]*?)<\/dl>/i', $html);
        $specsHtml = $this->definition_list_html($specs);
        if ($specsHtml !== '') {
            $parts[] = '<h4>Specifications</h4>' . $specsHtml;
        }

        if ($parts) {
            return implode("\n", $parts);
        }
        $description = trim((string) $description);
        if ($description === '') {
            return '';
        }
        return '<p>' . htmlspecialchars($description, ENT_QUOTES, 'UTF-8') . '</p>';
    }

    protected function definition_list_html($chunk)
    {
        $chunk = (string) $chunk;
        if ($chunk === '') {
            return '';
        }
        $html = '';
        if (preg_match_all('/<dt[^>]*>([\s\S]*?)<\/dt>\s*(?:<dd[^>]*>([\s\S]*?)<\/dd>|([^<]+))?/i', $chunk, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $row) {
                $label = $this->clean_text($row[1]);
                $rawValue = '';
                if (!empty($row[2])) {
                    $rawValue = $row[2];
                } elseif (!empty($row[3])) {
                    $rawValue = $row[3];
                }
                $value = $this->detail_value_html($rawValue);
                if ($label === '' && $value === '') {
                    continue;
                }
                if ($label !== '') {
                    $html .= '<h4>' . htmlspecialchars($label, ENT_QUOTES, 'UTF-8') . '</h4>';
                }
                if ($value !== '') {
                    $html .= (strpos($value, '<') === 0) ? $value : '<p>' . $value . '</p>';
                }
            }
        }
        return $this->clean_html($html);
    }

    protected function detail_value_html($raw)
    {
        $raw = html_entity_decode((string) $raw, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $items = preg_split('/<br\s*\/?>/i', $raw);
        $clean = array();
        foreach ($items as $item) {
            $item = $this->clean_text($item);
            $item = preg_replace('/^[^A-Za-z0-9]+/', '', $item);
            if ($item !== '') {
                $clean[] = $item;
            }
        }
        if (count($clean) > 1) {
            $html = '<ul>';
            foreach ($clean as $item) {
                $html .= '<li>' . htmlspecialchars($item, ENT_QUOTES, 'UTF-8') . '</li>';
            }
            return $html . '</ul>';
        }
        if (!$clean) {
            return '';
        }
        return '<p>' . htmlspecialchars($clean[0], ENT_QUOTES, 'UTF-8') . '</p>';
    }

    protected function is_product_image($url)
    {
        $url = strtolower((string) $url);
        if ($url === '' || strpos($url, 'thumbnail') !== false) {
            return false;
        }
        return strpos($url, '/medias/producthero') !== false
            || strpos($url, '/medias/responsiveproduct') !== false
            || (bool) preg_match('#(\.jpg|\.jpeg|\.png|\.webp)(\?|$)#i', $url);
    }
}
