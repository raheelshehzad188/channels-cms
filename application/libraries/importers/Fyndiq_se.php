<?php
defined('BASEPATH') OR exit('No direct script access allowed');

require_once APPPATH . 'libraries/importers/Nordic_se.php';

class Fyndiq_se extends Nordic_se {

    protected $fetchLanguage = 'sv-SE,sv;q=0.9,en;q=0.8';

    public function extract($html, $url)
    {
        if ($this->is_challenge_page($html)) {
            throw new Exception('Could not fetch this product page.');
        }
        return parent::extract($html, $url);
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

        $cookie = tempnam(sys_get_temp_dir(), 'fyndiqck');
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
                'Referer: https://www.fyndiq.se/',
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
