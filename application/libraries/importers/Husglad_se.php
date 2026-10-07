<?php
defined('BASEPATH') OR exit('No direct script access allowed');

require_once APPPATH . 'libraries/importers/Importer_base.php';

class Husglad_se extends Importer_base {

    protected $fetchLanguage = 'sv-SE,sv;q=0.9,en;q=0.8';

    public function is_listing_url($url)
    {
        $path = (string) parse_url($url, PHP_URL_PATH);
        return $path !== '' && !preg_match('#/products/[^/]+#i', $path);
    }

    public function extract($html, $url)
    {
        if ($this->is_challenge_page($html)) {
            throw new Exception('Could not fetch this product page.');
        }

        $info = $this->nuxt_info($html);

        $name = '';
        if (!empty($info['name'])) {
            $name = $this->clean_text($info['name']);
        }
        if ($name === '') {
            $name = $this->clean_text($this->first_match('/<title>(.*?)<\/title>/is', $html));
            $name = preg_replace('/\s*[|\-]\s*Husglad.*$/i', '', $name);
        }
        if ($name === '' || strcasecmp($name, 'Kundvagn') === 0) {
            if (preg_match_all('/<h1[^>]*>(.*?)<\/h1>/is', $html, $matches)) {
                foreach ($matches[1] as $heading) {
                    $heading = $this->clean_text($heading);
                    if ($heading !== '' && strcasecmp($heading, 'Kundvagn') !== 0) {
                        $name = $heading;
                    }
                }
            }
        }

        $variant = $this->primary_variant($info);
        $sku = '';
        if (!empty($variant['sku'])) {
            $sku = trim((string) $variant['sku']);
        }
        if ($sku === '') {
            $sku = $this->first_match('/\b(TY[0-9]{4,}-[A-Z0-9]+)\b/i', $html);
        }
        if ($sku === '') {
            $sku = strtolower((string) $this->first_match('~/products/([^/?#]+)~i', $url));
        }

        $price = $this->parse_price(isset($variant['price']) ? $variant['price'] : 0);
        if ($price <= 0) {
            $price = $this->parse_price(isset($info['minPrice']) ? $info['minPrice'] : 0);
        }
        if ($price <= 0) {
            $price = $this->html_sale_price($html);
        }

        $compare = $this->parse_price(isset($variant['marketPrice']) ? $variant['marketPrice'] : 0);
        if ($compare <= 0) {
            $compare = $this->parse_price(isset($info['marketPrice']) ? $info['marketPrice'] : 0);
        }
        if ($compare <= 0) {
            $compare = $this->parse_price($this->first_match('/class="[^"]*market-price[^"]*"[^>]*>\s*([0-9][0-9\s]*)\s*kr/i', $html));
        }
        if ($compare <= $price) {
            $compare = 0;
        }

        $details = '';
        if (!empty($info['description'])) {
            $details = $this->clean_html($info['description']);
        }
        if ($details === '') {
            $details = $this->clean_html($this->first_match('/class="[^"]*product-details-tab-description[^"]*"[^>]*>([\s\S]*?)<\/div>\s*<(?:div|section|script)/i', $html));
        }
        $description = $this->clean_text($details);

        $images = $this->product_images($info, $html);
        $saved = $this->downloadImages ? $this->download_images($images, 20) : array();

        $stock = 0;
        $status = isset($info['status']) ? strtoupper((string) $info['status']) : '';
        if ($status === 'ONSELL' || $status === 'ON_SELL' || $price > 0) {
            $stock = 1;
        }

        return array(
            'name' => $name,
            'sku' => $sku,
            'price' => $price,
            'compare_price' => $compare,
            'brand' => 'Husglad',
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
        $htmlFile = trim((string) getenv('IMPORT_HTML_FILE'));
        if ($htmlFile !== '' && is_file($htmlFile)) {
            $cached = file_get_contents($htmlFile);
            if (is_string($cached) && $cached !== '' && !$this->is_challenge_page($cached)) {
                return $cached;
            }
        }

        $cookie = tempnam(sys_get_temp_dir(), 'husgladck');
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
                'Referer: https://www.husglad.se/',
                'Upgrade-Insecure-Requests: 1',
            ),
            CURLOPT_ENCODING => '',
        ));
        $html = curl_exec($ch);
        $code = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        if (is_file($cookie)) {
            @unlink($cookie);
        }
        if ($html === false || $code >= 400 || $this->is_challenge_page((string) $html)) {
            return '';
        }
        return $html;
    }

    protected function is_challenge_page($html)
    {
        return stripos((string) $html, 'Just a moment') !== false
            || stripos((string) $html, 'cf-browser-verification') !== false
            || stripos((string) $html, 'cdn-cgi/challenge-platform') !== false
            || stripos((string) $html, 'cf-mitigated') !== false;
    }

    protected function nuxt_info($html)
    {
        if (!preg_match('/window\.__NUXT__\s*=\s*([\s\S]+?)<\/script>/i', (string) $html, $match)) {
            return array();
        }
        $expr = trim($match[1]);
        $expr = rtrim($expr, "; \n\r\t");
        if ($expr === '') {
            return array();
        }

        if (isset($expr[0]) && $expr[0] === '{') {
            $decoded = json_decode($expr, true);
            return $this->info_from_nuxt($decoded);
        }

        $fromNode = $this->nuxt_info_from_node($expr);
        if ($fromNode) {
            return $fromNode;
        }

        return array();
    }

    protected function nuxt_info_from_node($expr)
    {
        $node = trim((string) shell_exec('command -v node 2>/dev/null'));
        if ($node === '') {
            return array();
        }
        $tmp = tempnam(sys_get_temp_dir(), 'husgladnuxt');
        if ($tmp === false) {
            return array();
        }
        $js = "const window={};\nwindow.__NUXT__=" . $expr . ";\n"
            . "const n=window.__NUXT__||{};\n"
            . "let info=null;\n"
            . "if(n.data&&n.data[0]&&n.data[0].info){info=n.data[0].info;}\n"
            . "process.stdout.write(JSON.stringify(info||{}));\n";
        if (@file_put_contents($tmp, $js) === false) {
            @unlink($tmp);
            return array();
        }
        $out = shell_exec(escapeshellcmd($node) . ' ' . escapeshellarg($tmp) . ' 2>/dev/null');
        @unlink($tmp);
        $decoded = json_decode((string) $out, true);
        return is_array($decoded) ? $decoded : array();
    }

    protected function info_from_nuxt($nuxt)
    {
        if (!is_array($nuxt)) {
            return array();
        }
        if (!empty($nuxt['data'][0]['info']) && is_array($nuxt['data'][0]['info'])) {
            return $nuxt['data'][0]['info'];
        }
        if (!empty($nuxt['name']) || !empty($nuxt['imgCollection'])) {
            return $nuxt;
        }
        return array();
    }

    protected function primary_variant($info)
    {
        if (empty($info['optionCombines']) || !is_array($info['optionCombines'])) {
            return array();
        }
        foreach ($info['optionCombines'] as $row) {
            if (is_array($row) && (!isset($row['status']) || strtoupper((string) $row['status']) !== 'OFF')) {
                return $row;
            }
        }
        return is_array($info['optionCombines'][0]) ? $info['optionCombines'][0] : array();
    }

    protected function html_sale_price($html)
    {
        if (preg_match_all('/>\s*([0-9][0-9\s]*)\s*kr\s*</u', (string) $html, $matches)) {
            foreach ($matches[1] as $raw) {
                $price = $this->parse_price($raw);
                if ($price >= 100 && $price !== 499.0) {
                    return $price;
                }
            }
        }
        return 0.0;
    }

    protected function product_images($info, $html)
    {
        $urls = array();
        $items = array();
        if (!empty($info['imgCollection']['items']) && is_array($info['imgCollection']['items'])) {
            $items = $info['imgCollection']['items'];
        } elseif (!empty($info['images']) && is_array($info['images'])) {
            $items = $info['images'];
        }
        foreach ($items as $item) {
            if (is_string($item)) {
                $urls[] = $item;
                continue;
            }
            if (!is_array($item)) {
                continue;
            }
            if (!empty($item['file']['url'])) {
                $urls[] = $item['file']['url'];
            } elseif (!empty($item['url'])) {
                $urls[] = $item['url'];
            } elseif (!empty($item['link'])) {
                $urls[] = $item['link'];
            }
        }

        if (preg_match_all('#https://cdn\.myshopage\.com/[a-f0-9-]+(?:/w\d+)?\.(?:jpg|jpeg|png|gif|webp)#i', (string) $html, $matches)) {
            $urls = array_merge($urls, $matches[0]);
        }
        if (preg_match_all('#https:\\\\u002F\\\\u002Fcdn\\.myshopage\\.com\\\\u002F[a-f0-9-]+(?:\\\\u002Fw\\d+)?\\.(?:jpg|jpeg|png|gif|webp)#i', (string) $html, $matches)) {
            foreach ($matches[0] as $escaped) {
                $decoded = json_decode('"' . $escaped . '"');
                if (is_string($decoded) && $decoded !== '') {
                    $urls[] = $decoded;
                }
            }
        }

        $out = array();
        $seen = array();
        foreach ($urls as $url) {
            $url = $this->normalize_cdn_image($url);
            if ($url === '' || isset($seen[$url]) || !$this->is_product_image($url)) {
                continue;
            }
            $seen[$url] = true;
            $out[] = $url;
        }
        return $out;
    }

    protected function normalize_cdn_image($url)
    {
        $url = html_entity_decode(trim((string) $url), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $url = preg_replace('#/w[0-9]+(\.(?:jpg|jpeg|png|gif|webp))$#i', '$1', $url);
        return $url;
    }

    protected function is_product_image($url)
    {
        $url = strtolower((string) $url);
        if ($url === '' || strpos($url, 'cdn.myshopage.com') === false) {
            return false;
        }
        if (preg_match('/\.(svg)(\?|$)/', $url)) {
            return false;
        }
        if (preg_match('/\.(gif)(\?|$)/', $url)) {
            return false;
        }
        $logo = 'cc6a02ec-1b42-4997-54d3-e1d412610400';
        if (strpos($url, $logo) !== false) {
            return false;
        }
        return (bool) preg_match('/\.(jpg|jpeg|png|gif|webp)(\?|$)/', $url);
    }
}
