<?php
defined('BASEPATH') OR exit('No direct script access allowed');

abstract class Importer_base {

    abstract public function extract($html, $url);

    protected $pageUrl = '';
    protected $downloadImages = true;

    public function parse($url, $downloadImages = true)
    {
        $this->downloadImages = (bool) $downloadImages;
        $this->pageUrl = $url;
        $html = $this->fetch($url);
        if ($html === '') {
            throw new Exception('Could not fetch this product page.');
        }
        $data = $this->extract($html, $url);
        if (empty($data['name'])) {
            throw new Exception('Could not read product details from this URL.');
        }
        if (empty($data['details'])) {
            $data['details'] = $this->fallback_details($html, isset($data['description']) ? $data['description'] : '');
        } else {
            $data['details'] = $this->clean_html($data['details']);
        }
        return $data;
    }

    protected function fetch($url)
    {
        $ch = curl_init($url);
        curl_setopt_array($ch, array(
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_MAXREDIRS => 5,
            CURLOPT_TIMEOUT => 25,
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_USERAGENT => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124.0.0.0 Safari/537.36',
            CURLOPT_HTTPHEADER => array(
                'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,*/*;q=0.8',
                'Accept-Language: en-GB,en;q=0.9',
                'Cache-Control: no-cache',
            ),
            CURLOPT_ENCODING => '',
        ));
        $html = curl_exec($ch);
        $code = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        if ($html === false || $code >= 400) {
            return '';
        }
        return $html;
    }

    protected function first_match($pattern, $html, $group = 1)
    {
        if (!preg_match($pattern, $html, $match)) {
            return '';
        }
        return isset($match[$group]) ? trim($match[$group]) : '';
    }

    protected function clean_text($value)
    {
        $value = html_entity_decode(strip_tags((string) $value), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $value = preg_replace('/\s+/u', ' ', $value);
        return trim($value);
    }

    protected function clean_html($html)
    {
        $html = html_entity_decode((string) $html, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $html = preg_replace('#<(script|style|iframe|object|embed|form|noscript|link|meta)[^>]*>.*?</\1>#is', '', $html);
        $html = preg_replace('#<(script|style|iframe|object|embed|form|noscript|link|meta)[^>]*/?>#is', '', $html);
        $html = preg_replace('/\son\w+\s*=\s*("[^"]*"|\'[^\']*\'|[^\s>]+)/i', '', $html);
        $html = preg_replace('/javascript\s*:/i', '', $html);
        $allowed = '<p><br><ul><ol><li><strong><b><em><i><u><h2><h3><h4><h5><table><thead><tbody><tr><th><td><img><a><span><div><blockquote><hr><sup><sub>';
        $html = strip_tags($html, $allowed);
        $html = preg_replace('/(href|src)\s*=\s*([\'"])\s*javascript:[^\'"]*\2/i', '', $html);
        $html = preg_replace('/<(p|div|li|h[2-5]|td|th)[^>]*>\s*(&nbsp;|\s)*<\/\1>/i', '', $html);
        return trim($html);
    }

    protected function fallback_details($html, $plain = '')
    {
        $parts = array();
        $patterns = array(
            '/id="pqv-feature-bullets"[^>]*>([\s\S]*?)<\/div>\s*(?:<div|<\/div>)/i',
            '/id="feature-bullets"[^>]*>[\s\S]*?(<ul[^>]*>[\s\S]*?<\/ul>)/i',
            '/id="featurebullets_feature_div"[^>]*>[\s\S]*?(<ul[^>]*>[\s\S]*?<\/ul>)/i',
            '/id="productFactsDesktop_feature_div"[^>]*>([\s\S]*?)<div id="/i',
            '/id="productDescription"[^>]*>([\s\S]*?)<\/div>/i',
            '/id="aplus_feature_div"[^>]*>([\s\S]*?)<div id="(?:HLCXComparisonTable|ask-btf_feature_div|productDetails_feature_div)"/i',
            '/id="change_child_des"[^>]*>([\s\S]*?)<\/div>/i',
            '/class="[^"]*product-description[^"]*"[^>]*>([\s\S]*?)<\/div>/i',
            '/itemprop="description"[^>]*>([\s\S]*?)<\/div>/i',
        );
        foreach ($patterns as $pattern) {
            $chunk = $this->clean_html($this->first_match($pattern, $html));
            if ($chunk !== '' && !in_array($chunk, $parts, true)) {
                $parts[] = $chunk;
            }
        }
        if ($parts) {
            return implode("\n", $parts);
        }
        $plain = trim((string) $plain);
        if ($plain === '') {
            return '';
        }
        return '<p>' . htmlspecialchars($plain, ENT_QUOTES, 'UTF-8') . '</p>';
    }

    protected function parse_price($value)
    {
        $value = html_entity_decode((string) $value, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $value = preg_replace('/[^\d.,]/', '', $value);
        if ($value === '') {
            return 0.0;
        }
        if (substr_count($value, ',') === 1 && substr_count($value, '.') === 0) {
            $value = str_replace(',', '.', $value);
        } else {
            $value = str_replace(',', '', $value);
        }
        return round((float) $value, 2);
    }

    protected function json_ld_products($html)
    {
        $out = array();
        if (!preg_match_all('#<script[^>]*type=["\']application/ld\+json["\'][^>]*>(.*?)</script>#is', $html, $matches)) {
            return $out;
        }
        foreach ($matches[1] as $json) {
            $decoded = json_decode(html_entity_decode(trim($json), ENT_QUOTES | ENT_HTML5, 'UTF-8'), true);
            if (!is_array($decoded)) {
                continue;
            }
            $items = isset($decoded['@graph']) && is_array($decoded['@graph']) ? $decoded['@graph'] : array($decoded);
            foreach ($items as $item) {
                if (!is_array($item)) {
                    continue;
                }
                $type = isset($item['@type']) ? $item['@type'] : '';
                if (is_array($type)) {
                    $type = implode(',', $type);
                }
                if (stripos((string) $type, 'Product') !== false) {
                    $out[] = $item;
                }
            }
        }
        return $out;
    }

    protected function asset_url($url)
    {
        $url = html_entity_decode(trim((string) $url), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        if ($url === '' || stripos($url, 'data:') === 0) {
            return '';
        }
        if (strpos($url, '//') === 0) {
            return 'https:' . $url;
        }
        if (preg_match('#^https?://#i', $url)) {
            return $url;
        }
        if ($this->pageUrl === '') {
            return '';
        }
        $parts = parse_url($this->pageUrl);
        $origin = (isset($parts['scheme']) ? $parts['scheme'] : 'https') . '://' . (isset($parts['host']) ? $parts['host'] : '');
        if (isset($url[0]) && $url[0] === '/') {
            return $origin . $url;
        }
        return $origin . '/' . ltrim($url, '/');
    }

    protected function html_image_urls($html)
    {
        $urls = array();
        if (preg_match_all('/<img[^>]+(?:src|data-src|data-original|data-lazy)=["\']([^"\']+)["\']/i', (string) $html, $matches)) {
            $urls = array_merge($urls, $matches[1]);
        }
        return $urls;
    }

    protected function flatten_image_list($value)
    {
        $out = array();
        if (is_string($value) || is_numeric($value)) {
            return array((string) $value);
        }
        if (!is_array($value)) {
            return $out;
        }
        foreach ($value as $item) {
            if (is_array($item)) {
                if (!empty($item['url'])) {
                    $out[] = (string) $item['url'];
                } elseif (!empty($item['contentUrl'])) {
                    $out[] = (string) $item['contentUrl'];
                } else {
                    $out = array_merge($out, $this->flatten_image_list($item));
                }
            } elseif ($item) {
                $out[] = (string) $item;
            }
        }
        return $out;
    }

    protected function download_images($urls, $limit = 20)
    {
        $saved = array();
        $seen = array();
        foreach ((array) $urls as $url) {
            $url = $this->asset_url($url);
            if ($url === '' || isset($seen[$url])) {
                continue;
            }
            $seen[$url] = true;
            $path = $this->download_image($url);
            if ($path !== '') {
                $saved[] = $path;
            }
            if (count($saved) >= $limit) {
                break;
            }
        }
        return $saved;
    }

    protected function download_image($url)
    {
        $url = $this->asset_url($url);
        if ($url === '' || !preg_match('#^https?://#i', $url)) {
            return '';
        }

        $dir = FCPATH . 'uploads/products/';
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $ch = curl_init($url);
        curl_setopt_array($ch, array(
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_TIMEOUT => 20,
            CURLOPT_USERAGENT => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124.0.0.0 Safari/537.36',
            CURLOPT_HTTPHEADER => array(
                'Accept: image/avif,image/webp,image/apng,image/*,*/*;q=0.8',
                'Referer: ' . ($this->pageUrl !== '' ? $this->pageUrl : $url),
            ),
        ));
        $bin = curl_exec($ch);
        $code = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $ctype = (string) curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
        curl_close($ch);
        if ($bin === false || $code >= 400 || strlen($bin) < 500) {
            return '';
        }
        if (preg_match('/^\s*</', $bin)) {
            return '';
        }

        $base = 'imp_' . md5($url);
        $webpName = $base . '.webp';
        if (is_file($dir . $webpName)) {
            return 'uploads/products/' . $webpName;
        }

        $alreadyWebp = (stripos($ctype, 'webp') !== false)
            || (substr($bin, 0, 4) === 'RIFF' && stripos(substr($bin, 0, 16), 'WEBP') !== false);
        if ($alreadyWebp) {
            if (@file_put_contents($dir . $webpName, $bin) !== false) {
                return 'uploads/products/' . $webpName;
            }
            return '';
        }

        $ext = 'jpg';
        if (stripos($ctype, 'png') !== false || substr($bin, 0, 8) === "\x89PNG\r\n\x1a\n") {
            $ext = 'png';
        } elseif (stripos($ctype, 'gif') !== false || substr($bin, 0, 3) === 'GIF') {
            $ext = 'gif';
        }

        $tmpName = $base . '.src.' . $ext;
        if (@file_put_contents($dir . $tmpName, $bin) === false) {
            return '';
        }

        $converted = function_exists('ec_convert_image_to_webp')
            ? ec_convert_image_to_webp($dir . $tmpName)
            : ($dir . $tmpName);
        $public = function_exists('ec_public_upload_path')
            ? ec_public_upload_path($converted)
            : ('uploads/products/' . basename($converted));

        if ($public !== '' && substr($public, -5) === '.webp') {
            return $public;
        }

        if (is_file($dir . $webpName)) {
            @unlink($dir . $tmpName);
            return 'uploads/products/' . $webpName;
        }

        return $public !== '' ? $public : ('uploads/products/' . basename($converted));
    }
}
