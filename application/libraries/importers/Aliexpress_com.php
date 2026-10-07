<?php
defined('BASEPATH') OR exit('No direct script access allowed');

require_once APPPATH . 'libraries/importers/Importer_base.php';

class Aliexpress_com extends Importer_base {

    protected $fetchLanguage = 'sv-SE,sv;q=0.9,en-US,en;q=0.8';

    public function extract($html, $url)
    {
        $name = $this->clean_text($this->first_match('/property=["\']og:title["\'][^>]*content=["\']([^"\']+)/i', $html));
        if ($name === '') {
            $name = $this->clean_text($this->first_match('/<h1[^>]*>(.*?)<\/h1>/is', $html));
        }
        if ($name === '') {
            $name = $this->clean_text($this->first_match('/<title>(.*?)<\/title>/is', $html));
            $name = preg_replace('/\s*[|\-]\s*AliExpress.*$/iu', '', $name);
        }

        $sku = '';
        if (preg_match('#/item/(\d+)#', (string) $url, $match)) {
            $sku = 'AE' . $match[1];
        }
        if ($sku === '') {
            $sku = $this->first_match('/"productId"\s*:\s*"?(\d+)/', $html);
            if ($sku !== '') {
                $sku = 'AE' . $sku;
            }
        }

        $price = $this->parse_price($this->first_match('/property=["\']product:price:amount["\'][^>]*content=["\']([^"\']+)/i', $html));
        if ($price <= 0) {
            $price = $this->parse_price($this->first_match('/"formattedPrice"\s*:\s*"([^"]+)"/', $html));
        }
        if ($price <= 0) {
            $price = $this->parse_price($this->first_match('/"salePriceActivityMinPrice"\s*:\s*"?([0-9.]+)/', $html));
        }
        if ($price <= 0) {
            $price = $this->parse_price($this->first_match('/pdp_npi=\d+@dis![A-Z]{3}!([0-9.]+)/', (string) $url));
        }

        $description = $this->clean_text($this->first_match('/property=["\']og:description["\'][^>]*content=["\']([^"\']+)/i', $html));
        $details = $this->fallback_details($html, $description);
        if ($details === '' && $description !== '') {
            $details = '<p>' . htmlspecialchars($description, ENT_QUOTES, 'UTF-8') . '</p>';
        }

        $images = $this->aliexpress_images($html);
        $saved = $this->downloadImages ? $this->download_images($images, 20) : array();
        $ship = $this->extract_shipping_days($html, $url);

        return array(
            'name' => $name,
            'sku' => $sku,
            'price' => $price,
            'compare_price' => 0,
            'brand' => $this->extract_brand($html),
            'description' => $description !== '' ? $description : $name,
            'details' => $details,
            'stock' => 1,
            'ship_min_days' => $ship['min'],
            'ship_max_days' => $ship['max'],
            'image' => isset($saved[0]) ? $saved[0] : '',
            'gallery' => array_slice($saved, 1),
            'sizes' => $this->extract_sizes($html),
            'seo_title' => $name,
            'seo_description' => function_exists('mb_substr') ? mb_substr($description !== '' ? $description : $name, 0, 180) : substr($description !== '' ? $description : $name, 0, 180),
        );
    }

    public function is_listing_url($url)
    {
        $path = (string) parse_url((string) $url, PHP_URL_PATH);
        if (preg_match('#/item/\d+#', $path)) {
            return false;
        }
        return (bool) preg_match('#/(?:w/)?wholesale|/wholesale-|/category/#i', $path);
    }

    public function listing_product_urls($url)
    {
        $html = $this->fetch($url);
        if ($html === '') {
            return array();
        }
        $out = array();
        if (preg_match_all('#/item/(\d{10,})#', $html, $matches)) {
            foreach ($matches[1] as $id) {
                $out[$id] = 'https://www.aliexpress.com/item/' . $id . '.html';
            }
        }
        if (preg_match_all('#"productId"\s*:\s*"?(\d{10,})#', $html, $matches)) {
            foreach ($matches[1] as $id) {
                $out[$id] = 'https://www.aliexpress.com/item/' . $id . '.html';
            }
        }
        return array_values($out);
    }

    public function first_item_url($url)
    {
        $urls = $this->listing_product_urls($url);
        return $urls ? $urls[0] : '';
    }

    public function shipping_days_for_url($url)
    {
        $html = $this->fetch($url);
        return $this->extract_shipping_days($html, $url);
    }

    public function extract_shipping_days($html, $url = '')
    {
        $min = 0;
        $max = 0;
        $text = (string) $html;

        $patterns = array(
            '/"deliveryDayMin"\s*:\s*"?(\d+)/',
            '/"minDeliveryDays"\s*:\s*"?(\d+)/',
            '/"minDeliveryDay"\s*:\s*"?(\d+)/',
            '/"leadTimeMin"\s*:\s*"?(\d+)/',
        );
        foreach ($patterns as $pattern) {
            $min = (int) $this->first_match($pattern, $text);
            if ($min > 0) {
                break;
            }
        }
        $patterns = array(
            '/"deliveryDayMax"\s*:\s*"?(\d+)/',
            '/"maxDeliveryDays"\s*:\s*"?(\d+)/',
            '/"maxDeliveryDay"\s*:\s*"?(\d+)/',
            '/"leadTimeMax"\s*:\s*"?(\d+)/',
        );
        foreach ($patterns as $pattern) {
            $max = (int) $this->first_match($pattern, $text);
            if ($max > 0) {
                break;
            }
        }

        if ($min <= 0 || $max <= 0) {
            $fromDates = $this->shipping_days_from_eta_dates($text);
            if ($min <= 0 && $fromDates['min'] > 0) {
                $min = $fromDates['min'];
            }
            if ($max <= 0 && $fromDates['max'] > 0) {
                $max = $fromDates['max'];
            }
        }

        if ($min <= 0 && $max <= 0) {
            // Safe Sweden-facing fallback when AliExpress does not expose an ETA.
            $safe = function_exists('aliexpress_default_ship_days')
                ? aliexpress_default_ship_days()
                : array('min' => 7, 'max' => 15);
            return array(
                'min' => (int) $safe['min'],
                'max' => (int) $safe['max'],
                'ship_min_days' => (int) $safe['min'],
                'ship_max_days' => (int) $safe['max'],
            );
        }
        if ($min <= 0) {
            $min = $max;
        }
        if ($max <= 0) {
            $max = $min;
        }
        if ($min > $max) {
            $tmp = $min;
            $min = $max;
            $max = $tmp;
        }
        // Keep supplier window as source of truth; never shorten max/min.
        // Optional +1 on max only (safer end), never a shorter promise like 5–12 for a 6–14 supplier ETA.
        if (function_exists('aliexpress_customer_ship_days')) {
            $buffered = aliexpress_customer_ship_days($min, $max, 0);
            $min = (int) $buffered['min'];
            $max = (int) $buffered['max'];
        } else {
            $min = max(1, min(60, $min));
            $max = max($min, min(60, $max));
        }
        return array(
            'min' => $min,
            'max' => $max,
            'ship_min_days' => $min,
            'ship_max_days' => $max,
        );
    }

    protected function shipping_days_from_eta_dates($html)
    {
        $minDate = $this->first_match('/"displayEtaMinDate"\s*:\s*"([^"]+)"/', $html);
        $maxDate = $this->first_match('/"displayEtaMaxDate"\s*:\s*"([^"]+)"/', $html);
        if ($minDate === '' && $maxDate === '') {
            if (preg_match('/Delivery:\s*([A-Za-z]{3,9}\.?\s*\d{1,2})(?:\s*[\-–]\s*([A-Za-z]{3,9}\.?\s*\d{1,2}))?/i', $html, $match)) {
                $minDate = $match[1];
                $maxDate = isset($match[2]) ? $match[2] : $match[1];
            } elseif (preg_match('/Leverans:\s*(\d{1,2}\s+[A-Za-zåäö.]+)(?:\s*[\-–]\s*(\d{1,2}\s+[A-Za-zåäö.]+))?/iu', $html, $match)) {
                $minDate = $match[1];
                $maxDate = isset($match[2]) ? $match[2] : $match[1];
            }
        }
        $min = $this->days_until_label($minDate);
        $max = $this->days_until_label($maxDate);
        if ($min <= 0 && $max > 0) {
            $min = $max;
        }
        if ($max <= 0 && $min > 0) {
            $max = $min;
        }
        return array('min' => $min, 'max' => $max);
    }

    protected function days_until_label($label)
    {
        $label = trim(html_entity_decode((string) $label, ENT_QUOTES | ENT_HTML5, 'UTF-8'));
        if ($label === '') {
            return 0;
        }
        $months = array(
            'jan' => 1, 'january' => 1, 'januari' => 1,
            'feb' => 2, 'february' => 2, 'februari' => 2,
            'mar' => 3, 'march' => 3, 'mars' => 3,
            'apr' => 4, 'april' => 4,
            'may' => 5, 'maj' => 5,
            'jun' => 6, 'june' => 6, 'juni' => 6,
            'jul' => 7, 'july' => 7, 'juli' => 7,
            'aug' => 8, 'august' => 8, 'augusti' => 8,
            'sep' => 9, 'sept' => 9, 'september' => 9,
            'oct' => 10, 'october' => 10, 'okt' => 10, 'oktober' => 10,
            'nov' => 11, 'november' => 11,
            'dec' => 12, 'december' => 12,
        );
        $day = 0;
        $month = 0;
        $year = (int) date('Y');
        if (preg_match('/^(\d{4})-(\d{1,2})-(\d{1,2})/', $label, $match)) {
            $year = (int) $match[1];
            $month = (int) $match[2];
            $day = (int) $match[3];
        } elseif (preg_match('/([A-Za-zåäö]{3,9})\.?\s+(\d{1,2})/u', $label, $match)) {
            $key = strtolower(str_replace('.', '', $match[1]));
            $month = isset($months[$key]) ? $months[$key] : 0;
            $day = (int) $match[2];
        } elseif (preg_match('/(\d{1,2})\s+([A-Za-zåäö]{3,9})/u', $label, $match)) {
            $day = (int) $match[1];
            $key = strtolower(str_replace('.', '', $match[2]));
            $month = isset($months[$key]) ? $months[$key] : 0;
        }
        if ($day < 1 || $month < 1) {
            $ts = strtotime($label);
            if (!$ts) {
                return 0;
            }
            $days = (int) ceil(($ts - strtotime('today')) / 86400);
            return $days > 0 ? $days : 0;
        }
        $ts = strtotime(sprintf('%04d-%02d-%02d', $year, $month, $day));
        if (!$ts) {
            return 0;
        }
        $today = strtotime('today');
        if ($ts < $today) {
            $ts = strtotime(sprintf('%04d-%02d-%02d', $year + 1, $month, $day));
        }
        $days = (int) ceil(($ts - $today) / 86400);
        return $days > 0 ? $days : 0;
    }

    protected function extract_sizes($html)
    {
        $html = (string) $html;
        $sizes = array();
        if (preg_match_all('/skuPropertyName"\s*:\s*"(Size|Storlek|Taille|Größe|Taglia)"(.*?)(?:skuPropertyName"|skuPriceList)/is', $html, $blocks)) {
            foreach ($blocks[2] as $block) {
                if (preg_match_all('/"(?:propertyValueDisplayName|propertyValueName|skuPropertyTips)"\s*:\s*"([^"]+)"/i', $block, $matches)) {
                    foreach ($matches[1] as $raw) {
                        $sizes[] = $this->clean_text($raw);
                    }
                }
            }
        }
        if (!$sizes && preg_match_all('/"(?:propertyValueDisplayName|propertyValueName)"\s*:\s*"([^"]+)"/i', $html, $matches)) {
            foreach ($matches[1] as $raw) {
                $label = $this->clean_text($raw);
                if ($this->looks_like_size($label)) {
                    $sizes[] = $label;
                }
            }
        }
        if (preg_match_all('/sku-item--text[^>]*>\s*([^<]+)/i', $html, $matches)) {
            foreach ($matches[1] as $raw) {
                $sizes[] = $this->clean_text($raw);
            }
        }
        if (!$sizes && preg_match_all('/\b((?:XXS|XS|S|M|L|XL|XXL|XXXL|[1-5]XL)(?:\s*\(US\s*\d+\))?)\b/i', $html, $matches)) {
            foreach ($matches[1] as $raw) {
                $sizes[] = $this->clean_text($raw);
            }
        }
        $out = array();
        $seen = array();
        foreach ($sizes as $size) {
            $size = $this->clean_text($size);
            if (!$this->looks_like_size($size)) {
                continue;
            }
            $key = strtoupper($size);
            if (isset($seen[$key])) {
                continue;
            }
            $seen[$key] = true;
            $out[] = $size;
        }
        return $out;
    }

    protected function fetch($url)
    {
        $cookie = tempnam(sys_get_temp_dir(), 'aeck');
        $ua = 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124.0.0.0 Safari/537.36';
        $headers = array(
            'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,*/*;q=0.8',
            'Accept-Language: ' . $this->fetchLanguage,
            'Cache-Control: no-cache',
            'Referer: https://www.aliexpress.com/',
        );
        $this->curl_get('https://www.aliexpress.com/', $cookie, $ua, $headers);
        $html = $this->curl_get($url, $cookie, $ua, $headers);
        if (is_file($cookie)) {
            @unlink($cookie);
        }
        return $html;
    }

    protected function curl_get($url, $cookie, $ua, $headers)
    {
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
            CURLOPT_USERAGENT => $ua,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_ENCODING => '',
        ));
        $html = curl_exec($ch);
        $code = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        if ($html === false || $code >= 400) {
            return '';
        }
        return (string) $html;
    }

    protected function looks_like_size($label)
    {
        $label = trim((string) $label);
        if ($label === '' || strlen($label) > 24) {
            return false;
        }
        if (preg_match('/^(?:XXS|XS|S|M|L|XL|XXL|XXXL|[1-5]XL)(?:\s*\(US\s*\d+\))?$/i', $label)) {
            return true;
        }
        if (preg_match('/^\d{2,3}(?:\s*cm)?$/i', $label)) {
            $n = (int) $label;
            return $n >= 70 && $n <= 180;
        }
        if (preg_match('/^\d{1,2}(?:\.\d)?$/', $label)) {
            return true;
        }
        if (preg_match('/^\d{1,2}Y$/i', $label)) {
            return true;
        }
        return false;
    }

    protected function aliexpress_images($html)
    {
        $urls = array();
        if (preg_match_all('#https?://(?:ae-pic-[^/"\']+|ae01\.alicdn\.com|img\.alicdn\.com)/[^"\'\s]+#i', (string) $html, $matches)) {
            foreach ($matches[0] as $url) {
                if (!preg_match('/kf\/S[A-Za-z0-9]+/i', $url)) {
                    continue;
                }
                $urls[] = $this->original_image_url($url);
            }
        }
        $og = $this->first_match('/property=["\']og:image["\'][^>]*content=["\']([^"\']+)/i', $html);
        if ($og !== '') {
            array_unshift($urls, $this->original_image_url($og));
        }
        $out = array();
        $seen = array();
        foreach ($urls as $url) {
            if ($url === '' || isset($seen[$url])) {
                continue;
            }
            $seen[$url] = true;
            $out[] = $url;
        }
        return $out;
    }

    protected function original_image_url($url)
    {
        $url = html_entity_decode(trim((string) $url), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $url = preg_replace('/\.(?:jpg|png|webp|avif)_[0-9]+x[0-9]+[^?]*/i', '.jpg', $url);
        $url = preg_replace('/\.jpg_.*/i', '.jpg', $url);
        $url = preg_replace('/\.avif$/i', '', $url);
        return $this->asset_url($url);
    }
}
