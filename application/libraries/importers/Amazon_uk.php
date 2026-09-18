<?php
defined('BASEPATH') OR exit('No direct script access allowed');

require_once APPPATH . 'libraries/importers/Importer_base.php';

class Amazon_uk extends Importer_base {

    protected $cookieFile = '';

    public function parse($url, $downloadImages = true)
    {
        $this->cookieFile = sys_get_temp_dir() . '/ec_amz_' . md5($url . microtime(true)) . '.txt';
        try {
            return parent::parse($url, $downloadImages);
        } finally {
            if ($this->cookieFile !== '' && is_file($this->cookieFile)) {
                @unlink($this->cookieFile);
            }
            $this->cookieFile = '';
        }
    }

    protected function fetch($url)
    {
        $session = $this->marketplace_session();
        $html = $this->curl_get($url, $session);
        if ($html !== '' && $this->needs_marketplace_session($html)) {
            $this->prime_marketplace_session($url, $html, $session);
            $primed = $this->curl_get($url, $session);
            if ($primed !== '') {
                $html = $primed;
            }
        }
        if ($html === '') {
            return '';
        }
        return $html;
    }

    protected function marketplace_session()
    {
        $host = strtolower((string) parse_url($this->pageUrl, PHP_URL_HOST));
        if (strpos($host, 'amazon.com.au') !== false) {
            return array('zip' => '2000', 'prefs' => 'AUD', 'lang' => 'en-AU,en;q=0.9');
        }
        if (strpos($host, 'amazon.in') !== false) {
            return array('zip' => '110001', 'prefs' => 'INR', 'lang' => 'en-IN,en;q=0.9');
        }
        if (preg_match('/amazon\.com$/', $host) || substr($host, -12) === '.amazon.com') {
            return array('zip' => '10001', 'prefs' => 'USD', 'lang' => 'en-US,en;q=0.9');
        }
        return array('zip' => 'SW1A 1AA', 'prefs' => 'GBP', 'lang' => 'en-GB,en;q=0.9');
    }

    protected function needs_marketplace_session($html)
    {
        if (stripos($html, 'cannot be dispatched to your selected delivery location') !== false) {
            return true;
        }
        if (stripos($html, 'No featured offers available') !== false) {
            return true;
        }
        if (stripos($html, 'See All Buying Options') !== false && stripos($html, 'priceToPay') === false) {
            return true;
        }
        $local = false;
        foreach ($this->currency_tokens() as $token) {
            if (stripos($html, $token) !== false) {
                $local = true;
                break;
            }
        }
        if (!$local && (stripos($html, 'PKR') !== false || stripos($html, 'INR') !== false)) {
            return true;
        }
        return false;
    }

    protected function prime_marketplace_session($url, $html, $session)
    {
        $parts = parse_url($url);
        $origin = (isset($parts['scheme']) ? $parts['scheme'] : 'https') . '://' . (isset($parts['host']) ? $parts['host'] : 'www.amazon.co.uk');
        $token = $this->first_match('/id="glowValidationToken"[^>]*value="([^"]+)"/i', $html);
        $store = $this->first_match('/storeContext(?:=|&#x3D;)([a-z0-9_-]+)/i', $html);
        if ($store === '') {
            $store = 'generic';
        }
        $zip = isset($session['zip']) ? $session['zip'] : 'SW1A 1AA';
        $this->curl_post(
            $origin . '/portal-migration/hz/glow/address-change?actionSource=glow',
            http_build_query(array(
                'locationType' => 'LOCATION_INPUT',
                'zipCode' => $zip,
                'deviceType' => 'web',
                'storeContext' => $store,
                'pageType' => 'Detail',
                'actionSource' => 'glow',
            )),
            $session,
            $token
        );
    }

    protected function curl_get($url, $session)
    {
        return $this->curl_request($url, $session, false, '', '');
    }

    protected function curl_post($url, $body, $session, $csrf = '')
    {
        return $this->curl_request($url, $session, true, $body, $csrf);
    }

    protected function curl_request($url, $session, $post, $body, $csrf)
    {
        $headers = array(
            'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,*/*;q=0.8',
            'Accept-Language: ' . (isset($session['lang']) ? $session['lang'] : $this->fetchLanguage),
            'Cache-Control: no-cache',
        );
        if (!empty($session['prefs'])) {
            $headers[] = 'Cookie: i18n-prefs=' . $session['prefs'];
        }
        if ($csrf !== '') {
            $headers[] = 'anti-csrftoken-a2z: ' . $csrf;
            $headers[] = 'X-Requested-With: XMLHttpRequest';
        }
        $ch = curl_init($url);
        $opts = array(
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_MAXREDIRS => 5,
            CURLOPT_TIMEOUT => 25,
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_USERAGENT => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124.0.0.0 Safari/537.36',
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_ENCODING => '',
        );
        if ($this->cookieFile !== '') {
            $opts[CURLOPT_COOKIEJAR] = $this->cookieFile;
            $opts[CURLOPT_COOKIEFILE] = $this->cookieFile;
        }
        if ($post) {
            $opts[CURLOPT_POST] = true;
            $opts[CURLOPT_POSTFIELDS] = $body;
            $opts[CURLOPT_TIMEOUT] = 20;
        }
        curl_setopt_array($ch, $opts);
        $html = curl_exec($ch);
        $code = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        if ($html === false || $code >= 400) {
            return '';
        }
        return $html;
    }

    public function extract($html, $url)
    {
        $name = $this->clean_text($this->first_match('/id="productTitle"[^>]*>(.*?)<\/span>/is', $html));
        if ($name === '') {
            $name = $this->clean_text($this->first_match('/<title>(.*?)<\/title>/is', $html));
            $name = preg_replace('/\s*:\s*Amazon\..*$/i', '', $name);
            $name = preg_replace('/\s+Amazon\.(com\.au|co\.uk|com|in)\s*$/i', '', $name);
        }
        if ($name === '' || preg_match('/^amazon\b/i', $name) || stripos($name, 'Robot Check') !== false) {
            $fromUrl = $this->name_from_amazon_url($url);
            if ($fromUrl !== '') {
                $name = $fromUrl;
            }
        }

        $sku = $this->first_match('#/dp/([A-Z0-9]{8,13})#i', $url);
        if ($sku === '') {
            $sku = $this->first_match('/"asin"\s*:\s*"([A-Z0-9]{8,13})"/i', $html);
        }

        $price = $this->price_from_local_currency($html);
        if ($price <= 0) {
            $price = $this->price_from_a_price_whole($html);
        }
        if ($price <= 0) {
            $price = $this->parse_price($this->first_match('/id="twister-plus-price-data-price"\s+value="([^"]+)"/i', $html));
        }
        if ($price <= 0) {
            $price = $this->parse_price($this->first_match('/"priceAmount"\s*:\s*([0-9.]+)/i', $html));
        }
        if ($price <= 0) {
            $raw = $this->first_local_offscreen($html);
            $candidate = $this->parse_price($raw);
            if ($candidate > 0 && (strpbrk($raw, '.,') !== false || $candidate < 1000)) {
                $price = $candidate;
            }
        }
        if ($price <= 0) {
            $price = $this->price_from_json_ld($html);
        }
        if ($price <= 0) {
            $price = $this->parse_price($this->first_match('/id="pqv-price"[\s\S]{0,500}?(?:£|&pound;|GBP|A\$|AU\$|\$|₹|Rs\.?)\s*([0-9]+(?:[.,][0-9]{2})?)/i', $html));
        }
        if ($price <= 0) {
            $price = $this->parse_price($this->first_match('/(?:New\s*\(\d+\)\s*)?from\s*((?:A\$|AU\$|£|&pound;|\$|₹|Rs\.?)\s*[0-9]+(?:[.,][0-9]{2})?)/i', $html));
        }
        if ($this->is_foreign_marketplace_amount($html, $price)) {
            $price = 0;
        }

        $compare = $this->parse_price($this->first_match('/apex-basisprice-offscreen-label[^>]*>([^<]+)/i', $html));
        if ($compare <= 0) {
            $compare = $this->parse_price($this->first_match('/RRP:\s*([^<]+)/i', $html));
        }
        if ($this->is_foreign_marketplace_amount($html, $compare)) {
            $compare = 0;
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
        if (!$images && preg_match_all('/"large"\s*:\s*"(https:[^"]+)"/i', $html, $matches)) {
            $images = $matches[1];
        }
        if (!$images && preg_match('/id="landingImage"[^>]*(?:data-old-hires|src)="([^"]+)"/i', $html, $match)) {
            $images[] = $match[1];
        }
        if (!$images) {
            $images = $this->images_from_json_ld($html);
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

    protected function name_from_amazon_url($url)
    {
        $path = (string) parse_url((string) $url, PHP_URL_PATH);
        if (preg_match('#/([^/]+)/dp/[A-Z0-9]{8,13}#i', $path, $match)) {
            $slug = urldecode($match[1]);
            if (!preg_match('/^(dp|gp|product)$/i', $slug) && strlen($slug) > 4) {
                return trim(preg_replace('/\s+/', ' ', str_replace(array('-', '_'), ' ', $slug)));
            }
        }
        return '';
    }

    protected function currency_tokens()
    {
        $host = strtolower((string) parse_url($this->pageUrl, PHP_URL_HOST));
        if (strpos($host, 'amazon.com.au') !== false) {
            return array('A$', 'AU$', 'AUD');
        }
        if (strpos($host, 'amazon.in') !== false) {
            return array('₹', 'Rs.', 'INR', 'Rs');
        }
        if (preg_match('/amazon\.com$/', $host) || substr($host, -12) === '.amazon.com') {
            return array('$', 'USD');
        }
        return array('£', '&pound;', 'GBP');
    }

    protected function price_from_local_currency($html)
    {
        foreach ($this->currency_tokens() as $token) {
            $quoted = preg_quote($token, '/');
            $raw = $this->first_match('/' . $quoted . '\s*([0-9]+(?:[.,][0-9]{2})?)/i', $html);
            $price = $this->parse_price($raw);
            if ($price > 0) {
                return $price;
            }
        }
        return 0.0;
    }

    protected function first_local_offscreen($html)
    {
        if (!preg_match_all('/class="a-offscreen">\s*([^<]*\d[^<]*)<\/span>/i', $html, $matches)) {
            return '';
        }
        $tokens = $this->currency_tokens();
        foreach ($matches[1] as $raw) {
            foreach ($tokens as $token) {
                if (stripos($raw, $token) !== false) {
                    return $raw;
                }
            }
        }
        foreach ($matches[1] as $raw) {
            if (preg_match('/PKR|INR|EUR|USD|A\$|AU\$/i', $raw) && !preg_match('/£|GBP|&pound;/i', $raw)) {
                continue;
            }
            return $raw;
        }
        return '';
    }

    protected function is_foreign_marketplace_amount($html, $amount)
    {
        $amount = (float) $amount;
        if ($amount <= 0) {
            return false;
        }
        foreach ($this->currency_tokens() as $token) {
            if (stripos($html, $token) !== false) {
                return false;
            }
        }
        if ($amount < 100) {
            return false;
        }
        return stripos($html, 'PKR') !== false || stripos($html, 'INR') !== false;
    }

    /**
     * Buy-box listed price: <span class="a-price-whole">10<span class="a-price-decimal">.</span></span>
     * plus optional <span class="a-price-fraction">44</span>.
     */
    protected function price_from_a_price_whole($html)
    {
        $scope = '';
        if (preg_match('/class="[^"]*priceToPay[^"]*"[\s\S]{0,600}/i', $html, $block)) {
            $scope = $block[0];
        } elseif (preg_match('/id="corePriceDisplay_desktop_feature_div"[^>]*>[\s\S]{0,2500}/i', $html, $block)) {
            $scope = $block[0];
        } elseif (preg_match('/id="desktop_unifiedPrice"[^>]*>[\s\S]{0,2500}/i', $html, $block)) {
            $scope = $block[0];
        }
        if ($scope === '') {
            return 0.0;
        }

        if (!preg_match('/class="a-price-whole"[^>]*>([\s\S]*?)<\/span>/i', $scope, $match)) {
            return 0.0;
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

    protected function price_from_json_ld($html)
    {
        foreach ($this->json_ld_products($html) as $item) {
            $offers = isset($item['offers']) ? $item['offers'] : array();
            if (isset($offers['price'])) {
                $price = $this->parse_price($offers['price']);
                if ($price > 0) {
                    return $price;
                }
            }
            $list = isset($offers[0]) ? $offers : array($offers);
            foreach ($list as $offer) {
                if (!is_array($offer)) {
                    continue;
                }
                if (!empty($offer['price'])) {
                    $price = $this->parse_price($offer['price']);
                    if ($price > 0) {
                        return $price;
                    }
                }
                if (!empty($offer['lowPrice'])) {
                    $price = $this->parse_price($offer['lowPrice']);
                    if ($price > 0) {
                        return $price;
                    }
                }
            }
        }
        return 0.0;
    }

    protected function images_from_json_ld($html)
    {
        $images = array();
        foreach ($this->json_ld_products($html) as $item) {
            if (empty($item['image'])) {
                continue;
            }
            foreach ($this->flatten_image_list($item['image']) as $url) {
                $url = $this->asset_url($url);
                if ($url !== '' && !in_array($url, $images, true)) {
                    $images[] = $url;
                }
            }
        }
        return $images;
    }
}
