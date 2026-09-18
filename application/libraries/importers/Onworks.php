<?php
defined('BASEPATH') OR exit('No direct script access allowed');

require_once APPPATH . 'libraries/importers/Amazon_uk.php';

class Onworks extends Amazon_uk {

    public function parse($url, $downloadImages = true)
    {
        $inner = $this->unwrap_onworks_url($url);
        if ($inner === '' || !preg_match('#^https?://#i', $inner)) {
            throw new Exception('Could not read the Amazon product URL from this OnWorks link.');
        }
        if (stripos($inner, 'amazon.') === false && !preg_match('#/dp/[A-Z0-9]{8,13}#i', $inner)) {
            $page = $this->fetch($url);
            $fromPage = $this->amazon_url_from_html($page);
            if ($fromPage !== '') {
                $inner = $fromPage;
            }
        }
        $inner = $this->canonical_amazon_url($inner);
        if ($inner === '') {
            throw new Exception('Could not read the Amazon product URL from this OnWorks link.');
        }
        return parent::parse($inner, $downloadImages);
    }

    public function unwrap_onworks_url($url)
    {
        $url = trim((string) $url);
        if ($url === '') {
            return '';
        }
        if (preg_match('/[?&]a=([0-9a-fA-F]{20,})/', $url, $match)) {
            $decoded = @hex2bin($match[1]);
            if (is_string($decoded) && preg_match('#https?://#i', $decoded)) {
                $url = $decoded;
            }
        }
        if (preg_match('#https?://(?:www\.)?amazon\.[a-z.]+/[^\s"\']+/dp/[A-Z0-9]{8,13}#i', $url, $match)) {
            return $match[0];
        }
        if (preg_match('#(?:www\.)?(amazon\.[a-z.]+)/[^\s"\']*/(?:dp|gp/product|gp/aw/d)/([A-Z0-9]{8,13})#i', $url, $match)) {
            return 'https://www.' . strtolower($match[1]) . '/dp/' . strtoupper($match[2]);
        }
        return $url;
    }

    protected function amazon_url_from_html($html)
    {
        if (preg_match('#https?://(?:www\.)?amazon\.[a-z.]+/[^"\'\s]+/dp/[A-Z0-9]{8,13}#i', (string) $html, $match)) {
            return $match[0];
        }
        return '';
    }

    protected function canonical_amazon_url($url)
    {
        $url = trim((string) $url);
        if (preg_match('#(?:www\.)?(amazon\.[a-z.]+)/(?:[^/]+/)?(?:dp|gp/product|gp/aw/d)/([A-Z0-9]{8,13})#i', $url, $match)) {
            return 'https://www.' . strtolower($match[1]) . '/dp/' . strtoupper($match[2]);
        }
        return $url;
    }
}
