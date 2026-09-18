<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Meta_commerce {

    const GRAPH = 'https://graph.facebook.com/v21.0';
    const DIALOG = 'https://www.facebook.com/v21.0/dialog/oauth';

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

    public function refresh_long_lived($token)
    {
        $this->reset_flags();
        $long = $this->get_json(self::GRAPH . '/oauth/access_token', array(
            'grant_type' => 'fb_exchange_token',
            'client_id' => $this->app_id(),
            'client_secret' => $this->app_secret(),
            'fb_exchange_token' => $token,
        ));
        if (empty($long['access_token'])) {
            return null;
        }
        return array(
            'access_token' => $long['access_token'],
            'expires_in' => !empty($long['expires_in']) ? (int) $long['expires_in'] : 5184000,
        );
    }

    public function app_id()
    {
        return trim((string) platform_setting('meta_app_id', ''));
    }

    public function app_secret()
    {
        $CI =& get_instance();
        $CI->load->model('Store_channel_model');
        return trim((string) $CI->Store_channel_model->setting_secret('meta_app_secret'));
    }

    public function configured()
    {
        return $this->app_id() !== '' && $this->app_secret() !== '';
    }

    public function redirect_uri()
    {
        return site_url('store/channels/meta/callback');
    }

    public function oauth_url($state, $store)
    {
        $query = http_build_query(array(
            'client_id' => $this->app_id(),
            'redirect_uri' => $this->redirect_uri(),
            'state' => $state,
            'response_type' => 'code',
            'scope' => implode(',', array(
                'public_profile',
                'email',
                'pages_show_list',
                'pages_read_engagement',
                'pages_manage_metadata',
                'pages_manage_ads',
                'business_management',
                'catalog_management',
                'ads_management',
                'ads_read',
                'instagram_basic',
                'instagram_manage_insights',
                'instagram_content_publish',
            )),
        ));
        return self::DIALOG . '?' . $query;
    }

    public function exchange_code($code)
    {
        $this->reset_flags();
        $short = $this->get_json(self::GRAPH . '/oauth/access_token', array(
            'client_id' => $this->app_id(),
            'client_secret' => $this->app_secret(),
            'redirect_uri' => $this->redirect_uri(),
            'code' => $code,
        ));
        if (empty($short['access_token'])) {
            $this->lastError = $this->error_from($short, 'Could not exchange Meta login code.');
            return null;
        }
        $long = $this->get_json(self::GRAPH . '/oauth/access_token', array(
            'grant_type' => 'fb_exchange_token',
            'client_id' => $this->app_id(),
            'client_secret' => $this->app_secret(),
            'fb_exchange_token' => $short['access_token'],
        ));
        $token = !empty($long['access_token']) ? $long['access_token'] : $short['access_token'];
        $expires = !empty($long['expires_in']) ? (int) $long['expires_in'] : (!empty($short['expires_in']) ? (int) $short['expires_in'] : 5184000);
        $me = $this->get_json(self::GRAPH . '/me', array(
            'fields' => 'id,name,email',
            'access_token' => $token,
        ));
        return array(
            'access_token' => $token,
            'expires_in' => $expires,
            'user_id' => isset($me['id']) ? $me['id'] : '',
            'user_name' => isset($me['name']) ? $me['name'] : '',
        );
    }

    public function fbe_installs($token, $storeId)
    {
        return $this->get_json(self::GRAPH . '/fbe_business/fbe_installs', array(
            'fbe_external_business_id' => 'ec3-store-' . (int) $storeId,
            'access_token' => $token,
        ));
    }

    public function pages($token)
    {
        $json = $this->get_json(self::GRAPH . '/me/accounts', array(
            'fields' => 'id,name,access_token,instagram_business_account{id,username}',
            'limit' => 100,
            'access_token' => $token,
        ));
        return isset($json['data']) && is_array($json['data']) ? $json['data'] : array();
    }

    public function businesses($token)
    {
        $json = $this->get_json(self::GRAPH . '/me/businesses', array(
            'fields' => 'id,name',
            'limit' => 100,
            'access_token' => $token,
        ));
        return isset($json['data']) && is_array($json['data']) ? $json['data'] : array();
    }

    public function catalogs($businessId, $token)
    {
        if ($businessId === '') {
            return array();
        }
        $json = $this->get_json(self::GRAPH . '/' . rawurlencode($businessId) . '/owned_product_catalogs', array(
            'fields' => 'id,name',
            'limit' => 100,
            'access_token' => $token,
        ));
        return isset($json['data']) && is_array($json['data']) ? $json['data'] : array();
    }

    public function create_catalog($businessId, $name, $token)
    {
        $json = $this->post_json(self::GRAPH . '/' . rawurlencode($businessId) . '/owned_product_catalogs', array(
            'name' => $name,
            'access_token' => $token,
        ));
        return !empty($json['id']) ? $json : null;
    }

    public function pixels($businessId, $token)
    {
        if ($businessId === '') {
            return array();
        }
        $json = $this->get_json(self::GRAPH . '/' . rawurlencode($businessId) . '/adspixels', array(
            'fields' => 'id,name',
            'limit' => 100,
            'access_token' => $token,
        ));
        return isset($json['data']) && is_array($json['data']) ? $json['data'] : array();
    }

    public function create_pixel($businessId, $name, $token)
    {
        $json = $this->post_json(self::GRAPH . '/' . rawurlencode($businessId) . '/adspixels', array(
            'name' => $name,
            'access_token' => $token,
        ));
        return !empty($json['id']) ? $json : null;
    }

    public function ad_accounts($businessId, $token)
    {
        if ($businessId === '') {
            return array();
        }
        $json = $this->get_json(self::GRAPH . '/' . rawurlencode($businessId) . '/owned_ad_accounts', array(
            'fields' => 'id,name,account_id',
            'limit' => 50,
            'access_token' => $token,
        ));
        return isset($json['data']) && is_array($json['data']) ? $json['data'] : array();
    }

    public function instagram_for_page($pageId, $pageToken)
    {
        $json = $this->get_json(self::GRAPH . '/' . rawurlencode($pageId), array(
            'fields' => 'instagram_business_account{id,username}',
            'access_token' => $pageToken,
        ));
        if (!empty($json['instagram_business_account']['id'])) {
            return $json['instagram_business_account'];
        }
        return null;
    }

    public function sync_products($conn, $products)
    {
        $this->reset_flags();
        if (empty($conn->catalog_id) || empty($conn->access_token)) {
            $this->lastError = 'Meta catalog is not selected yet.';
            return false;
        }
        if (empty($products)) {
            return true;
        }
        $chunks = array_chunk($products, 40);
        foreach ($chunks as $chunk) {
            $requests = array();
            foreach ($chunk as $product) {
                $price = number_format((float) $product['price'], 2, '.', '') . ' ' . $product['currency'];
                $data = array(
                    'name' => $product['name'],
                    'description' => $product['description'],
                    'availability' => ((int) $product['stock'] > 0) ? 'in stock' : 'out of stock',
                    'condition' => 'new',
                    'price' => $price,
                    'url' => $product['url'],
                    'brand' => $product['brand'],
                    'retailer_id' => (string) $product['id'],
                    'quantity_to_sell_on_facebook' => max(0, (int) $product['stock']),
                );
                if (!empty($product['image'])) {
                    $data['image_url'] = $product['image'];
                }
                if (!empty($product['images']) && is_array($product['images']) && count($product['images']) > 1) {
                    $data['additional_image_urls'] = implode(',', array_slice($product['images'], 1, 10));
                }
                if (!empty($product['sku'])) {
                    $data['custom_label_0'] = $product['sku'];
                }
                if (!empty($product['item_group_id'])) {
                    $data['item_group_id'] = (string) $product['item_group_id'];
                }
                $requests[] = array(
                    'method' => 'UPDATE',
                    'retailer_id' => (string) $product['id'],
                    'data' => $data,
                );
            }
            $json = $this->post_json(self::GRAPH . '/' . rawurlencode($conn->catalog_id) . '/items_batch', array(
                'item_type' => 'PRODUCT_ITEM',
                'requests' => json_encode($requests),
                'access_token' => $conn->access_token,
            ));
            if (!empty($json['error'])) {
                $this->lastError = $this->error_from($json, 'Meta catalog sync failed.');
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
        $json = $this->post_json(self::GRAPH . '/' . rawurlencode($conn->catalog_id) . '/items_batch', array(
            'item_type' => 'PRODUCT_ITEM',
            'requests' => json_encode(array(array(
                'method' => 'DELETE',
                'retailer_id' => (string) $productId,
            ))),
            'access_token' => $conn->access_token,
        ));
        return empty($json['error']);
    }

    public function send_events($conn, $events)
    {
        $this->reset_flags();
        if (empty($conn->pixel_id) || empty($conn->access_token) || empty($events)) {
            return false;
        }
        $json = $this->post_json(self::GRAPH . '/' . rawurlencode($conn->pixel_id) . '/events', array(
            'data' => json_encode($events),
            'access_token' => $conn->access_token,
        ));
        if (!empty($json['error'])) {
            $this->lastError = $this->error_from($json, 'Meta CAPI failed.');
            return false;
        }
        return true;
    }

    public function revoke($token)
    {
        if ($token === '') {
            return;
        }
        $this->request('DELETE', self::GRAPH . '/me/permissions', array('access_token' => $token));
    }

    protected function get_json($url, $query)
    {
        return $this->request('GET', $url, $query);
    }

    protected function post_json($url, $fields)
    {
        return $this->request('POST', $url, $fields);
    }

    protected function request($method, $url, $fields)
    {
        $ch = curl_init();
        if (strtoupper($method) === 'GET' || strtoupper($method) === 'DELETE') {
            $url .= (strpos($url, '?') === false ? '?' : '&') . http_build_query($fields);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, strtoupper($method));
        } else {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);
        }
        curl_setopt_array($ch, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 40,
            CURLOPT_HTTPHEADER => array('Accept: application/json'),
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
            $this->lastError = 'Invalid response from Meta.';
            $this->last_retryable = true;
            return array();
        }
        if (!empty($json['error']['message'])) {
            $this->lastError = $json['error']['message'];
            $code = isset($json['error']['code']) ? (int) $json['error']['code'] : 0;
            $this->last_retryable = in_array($code, array(4, 17, 32, 613, 80004), true) || $http >= 500;
            $this->last_auth_error = in_array($code, array(190, 102, 10), true) || stripos($this->lastError, 'expired') !== false;
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
        if (!empty($json['error']['message'])) {
            return $json['error']['message'];
        }
        return $this->lastError !== '' ? $this->lastError : $fallback;
    }
}
