<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Tiktok_commerce {

    const API = 'https://business-api.tiktok.com/open_api/v1.3';
    const AUTH = 'https://business-api.tiktok.com/portal/auth';

    public $lastError = '';
    public $last_retryable = false;
    public $last_auth_error = false;
    protected $CI;

    public function __construct()
    {
        $this->CI =& get_instance();
    }

    public function last_error()
    {
        return $this->lastError;
    }

    public function app_id()
    {
        return trim((string) platform_setting('tiktok_app_id', ''));
    }

    public function app_secret()
    {
        $CI =& get_instance();
        $CI->load->model('Store_channel_model');
        return trim((string) $CI->Store_channel_model->setting_secret('tiktok_app_secret'));
    }

    public function configured()
    {
        return $this->app_id() !== '' && $this->app_secret() !== '';
    }

    public function redirect_uri()
    {
        return site_url('store/channels/tiktok/callback');
    }

    public function oauth_url($state)
    {
        return self::AUTH . '?' . http_build_query(array(
            'app_id' => $this->app_id(),
            'state' => $state,
            'redirect_uri' => $this->redirect_uri(),
        ));
    }

    public function exchange_code($code)
    {
        $this->reset_flags();
        $json = $this->post_json(self::API . '/oauth2/access_token/', array(
            'app_id' => $this->app_id(),
            'secret' => $this->app_secret(),
            'auth_code' => $code,
        ), false);
        $data = isset($json['data']) && is_array($json['data']) ? $json['data'] : array();
        if (empty($data['access_token'])) {
            $this->lastError = $this->error_from($json, 'Could not exchange TikTok login code.');
            return null;
        }
        return array(
            'access_token' => $data['access_token'],
            'refresh_token' => isset($data['refresh_token']) ? $data['refresh_token'] : '',
            'expires_in' => isset($data['expires_in']) ? (int) $data['expires_in'] : 86400,
            'advertiser_ids' => isset($data['advertiser_ids']) && is_array($data['advertiser_ids']) ? $data['advertiser_ids'] : array(),
            'scope' => isset($data['scope']) ? $data['scope'] : array(),
        );
    }

    public function refresh_access($refreshToken)
    {
        $this->reset_flags();
        $json = $this->post_json(self::API . '/oauth2/refresh_token/', array(
            'app_id' => $this->app_id(),
            'secret' => $this->app_secret(),
            'grant_type' => 'refresh_token',
            'refresh_token' => $refreshToken,
        ), true, '');
        $data = isset($json['data']) && is_array($json['data']) ? $json['data'] : array();
        if (empty($data['access_token'])) {
            $this->lastError = $this->error_from($json, 'Could not refresh TikTok token.');
            return null;
        }
        return array(
            'access_token' => $data['access_token'],
            'refresh_token' => isset($data['refresh_token']) ? $data['refresh_token'] : $refreshToken,
            'expires_in' => isset($data['expires_in']) ? (int) $data['expires_in'] : 86400,
        );
    }

    public function revoke($token)
    {
        if ($token === '') {
            return;
        }
        $this->post_json(self::API . '/oauth2/revoke_token/', array(
            'app_id' => $this->app_id(),
            'secret' => $this->app_secret(),
            'access_token' => $token,
        ), true, '');
    }

    public function advertisers($token)
    {
        $json = $this->get_json(self::API . '/oauth2/advertiser/get/', array(
            'app_id' => $this->app_id(),
            'secret' => $this->app_secret(),
        ), $token);
        $list = array();
        if (!empty($json['data']['list']) && is_array($json['data']['list'])) {
            $list = $json['data']['list'];
        } elseif (!empty($json['data']['advertiser_ids']) && is_array($json['data']['advertiser_ids'])) {
            foreach ($json['data']['advertiser_ids'] as $id) {
                $list[] = array('advertiser_id' => $id, 'advertiser_name' => $id);
            }
        }
        return $list;
    }

    public function pixels($advertiserId, $token)
    {
        $json = $this->get_json(self::API . '/pixel/list/', array(
            'advertiser_id' => $advertiserId,
        ), $token);
        $pixels = array();
        if (!empty($json['data']['pixels']) && is_array($json['data']['pixels'])) {
            $pixels = $json['data']['pixels'];
        } elseif (!empty($json['data']['list']) && is_array($json['data']['list'])) {
            $pixels = $json['data']['list'];
        }
        return $pixels;
    }

    public function catalogs($advertiserId, $token)
    {
        $json = $this->get_json(self::API . '/catalog/get/', array(
            'advertiser_id' => $advertiserId,
        ), $token);
        if (!empty($json['data']['list']) && is_array($json['data']['list'])) {
            return $json['data']['list'];
        }
        return array();
    }

    public function create_catalog($advertiserId, $name, $token)
    {
        $json = $this->post_json(self::API . '/catalog/create/', array(
            'advertiser_id' => $advertiserId,
            'name' => $name,
            'catalog_type' => 'ECOM',
        ), true, $token);
        if (!empty($json['data']['catalog_id'])) {
            return $json['data'];
        }
        $this->lastError = $this->error_from($json, 'Could not create TikTok catalog.');
        return null;
    }

    public function sync_products($conn, $products)
    {
        $this->reset_flags();
        if (empty($conn->catalog_id) || empty($conn->access_token)) {
            $this->lastError = 'TikTok catalog is not selected yet.';
            return false;
        }
        if (empty($products)) {
            return true;
        }
        $chunks = array_chunk($products, 20);
        foreach ($chunks as $chunk) {
            $items = array();
            foreach ($chunk as $product) {
                $items[] = array(
                    'sku_id' => (string) $product['id'],
                    'seller_sku' => !empty($product['sku']) ? $product['sku'] : (string) $product['id'],
                    'title' => $product['name'],
                    'description' => $product['description'],
                    'availability' => ((int) $product['stock'] > 0) ? 'IN_STOCK' : 'OUT_OF_STOCK',
                    'brand' => $product['brand'],
                    'image_url' => $product['image'],
                    'additional_image_urls' => !empty($product['images']) ? array_slice($product['images'], 1, 8) : array(),
                    'item_group_id' => isset($product['item_group_id']) ? (string) $product['item_group_id'] : (string) $product['id'],
                    'product_detail' => array(
                        'product_url' => $product['url'],
                    ),
                    'price' => array(
                        'price' => (float) $product['price'],
                        'currency' => $product['currency'],
                    ),
                    'inventory' => array(
                        'quantity' => max(0, (int) $product['stock']),
                    ),
                );
            }
            $payload = array(
                'catalog_id' => $conn->catalog_id,
                'bc_id' => $conn->business_id,
                'advertiser_id' => $conn->advertiser_id,
                'products' => $items,
            );
            if ($payload['bc_id'] === '') {
                unset($payload['bc_id']);
            }
            $json = $this->post_json(self::API . '/catalog/product/upload/', $payload, true, $conn->access_token);
            if (isset($json['code']) && (int) $json['code'] !== 0) {
                $this->lastError = $this->error_from($json, 'TikTok catalog sync failed.');
                return false;
            }
        }
        return true;
    }

    public function delete_product($conn, $productId)
    {
        if (empty($conn->catalog_id) || empty($conn->access_token)) {
            return false;
        }
        $payload = array(
            'catalog_id' => $conn->catalog_id,
            'advertiser_id' => $conn->advertiser_id,
            'sku_ids' => array((string) $productId),
        );
        $json = $this->post_json(self::API . '/catalog/product/delete/', $payload, true, $conn->access_token);
        return !(isset($json['code']) && (int) $json['code'] !== 0);
    }

    public function send_event($conn, $event)
    {
        $this->reset_flags();
        if (empty($conn->pixel_id) || empty($conn->access_token)) {
            return false;
        }
        $payload = array(
            'event_source' => 'web',
            'event_source_id' => $conn->pixel_id,
            'data' => array($event),
        );
        $json = $this->post_json(self::API . '/event/track/', $payload, true, $conn->access_token);
        if (isset($json['code']) && (int) $json['code'] !== 0) {
            $this->lastError = $this->error_from($json, 'TikTok Events API failed.');
            return false;
        }
        return true;
    }

    protected function get_json($url, $query, $token = '')
    {
        return $this->request('GET', $url, $query, $token);
    }

    protected function post_json($url, $body, $asJson = true, $token = '')
    {
        return $this->request('POST', $url, $body, $token, $asJson);
    }

    protected function request($method, $url, $fields, $token = '', $asJson = true)
    {
        $ch = curl_init();
        $headers = array('Accept: application/json');
        if ($token !== '') {
            $headers[] = 'Access-Token: ' . $token;
        }
        if (strtoupper($method) === 'GET') {
            $url .= (strpos($url, '?') === false ? '?' : '&') . http_build_query($fields);
        } else {
            curl_setopt($ch, CURLOPT_POST, true);
            if ($asJson) {
                $headers[] = 'Content-Type: application/json';
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));
            } else {
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));
                $headers[] = 'Content-Type: application/json';
            }
        }
        curl_setopt_array($ch, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 40,
            CURLOPT_HTTPHEADER => $headers,
        ));
        $raw = curl_exec($ch);
        $http = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        if ($raw === false) {
            $this->lastError = curl_error($ch);
            $this->last_retryable = true;
            curl_close($ch);
            return array();
        }
        curl_close($ch);
        $json = json_decode($raw, true);
        if (!is_array($json)) {
            $this->lastError = 'Invalid response from TikTok.';
            $this->last_retryable = true;
            return array();
        }
        if (isset($json['code']) && (int) $json['code'] !== 0 && !empty($json['message'])) {
            $this->lastError = $json['message'];
            $code = (int) $json['code'];
            $this->last_retryable = in_array($code, array(40100, 40101, 50000, 50001), true) || $http === 429 || $http >= 500;
            $this->last_auth_error = in_array($code, array(40001, 40102, 40103, 40104), true) || stripos($this->lastError, 'access token') !== false;
        }
        return $json;
    }

    protected function reset_flags()
    {
        $this->lastError = '';
        $this->last_retryable = false;
        $this->last_auth_error = false;
    }

    protected function error_from($json, $fallback)
    {
        if (!empty($json['message'])) {
            return $json['message'];
        }
        if (!empty($json['data']['message'])) {
            return $json['data']['message'];
        }
        return $this->lastError !== '' ? $this->lastError : $fallback;
    }
}
