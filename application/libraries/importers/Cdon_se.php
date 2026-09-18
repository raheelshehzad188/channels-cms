<?php
defined('BASEPATH') OR exit('No direct script access allowed');

require_once APPPATH . 'libraries/importers/Nordic_se.php';

class Cdon_se extends Nordic_se {

    protected $fetchLanguage = 'sv-SE,sv;q=0.9,en;q=0.8';

    public function is_listing_url($url)
    {
        $path = (string) parse_url($url, PHP_URL_PATH);
        return $path !== '' && !preg_match('#/produkt/#i', $path);
    }

    public function listing_product_urls($url)
    {
        $html = $this->fetch($url);
        if ($html === '' || $this->is_challenge_page($html)) {
            throw new Exception('Could not fetch this product page.');
        }
        $urls = array();
        if (preg_match_all('#https?://(?:www\.)?cdon\.se/produkt/[^"\'\s?#]+#i', $html, $matches)) {
            $urls = array_merge($urls, $matches[0]);
        }
        if (preg_match_all('#(?:href|content)="(/produkt/[^"]+)"#i', $html, $matches)) {
            foreach ($matches[1] as $path) {
                $urls[] = 'https://cdon.se' . $path;
            }
        }
        $out = array();
        foreach ($urls as $item) {
            $item = html_entity_decode((string) $item, ENT_QUOTES | ENT_HTML5, 'UTF-8');
            $item = strtok($item, '?#');
            $item = rtrim($item, '/');
            if ($item !== '' && preg_match('#/produkt/[^/]+#i', $item)) {
                $out[$item] = $item;
            }
        }
        return array_slice(array_values($out), 0, 50);
    }

    public function extract($html, $url)
    {
        if ($this->is_challenge_page($html)) {
            throw new Exception('Could not fetch this product page.');
        }
        if ($this->is_listing_url($url)) {
            throw new Exception('This CDON link is a category listing. Open a product page and paste that URL.');
        }
        return parent::extract($html, $url);
    }

    protected function fetch($url)
    {
        $cookie = tempnam(sys_get_temp_dir(), 'cdonck');
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
                'Referer: https://cdon.se/',
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
            || stripos((string) $html, 'cdn-cgi/challenge-platform') !== false;
    }
}
