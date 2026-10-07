<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Meta_commerce {

    const GRAPH = 'https://graph.facebook.com/v26.0';
    const DIALOG = 'https://www.facebook.com/v26.0/dialog/oauth';

    public $lastError = '';
    public $last_retryable = false;
    public $last_auth_error = false;
    public $lastHttp = 0;
    public $lastResponse = array();
    public $lastEventsReceived = 0;
    public $lastVerifyEventId = '';
    protected $CI;

    public function __construct()
    {
        $this->CI =& get_instance();
    }

    public function last_error()
    {
        return $this->redact((string) $this->lastError);
    }

    public function redact($message)
    {
        $message = (string) $message;
        $message = preg_replace('/EAA[A-Za-z0-9]+/', '[redacted]', $message);
        $message = preg_replace('/access_token=[^&\s]+/i', 'access_token=[redacted]', $message);
        $message = preg_replace('/client_secret=[^&\s]+/i', 'client_secret=[redacted]', $message);
        $message = preg_replace('/fb_exchange_token=[^&\s]+/i', 'fb_exchange_token=[redacted]', $message);
        $message = preg_replace('/code=[A-Za-z0-9_-]{20,}/', 'code=[redacted]', $message);
        return $message;
    }

    public function oauth_trace($event, $data, $store = null)
    {
        $safe = $this->safe_oauth_payload(is_array($data) ? $data : array());
        error_log('meta_oauth ' . $event . ' ' . json_encode($safe));
        $storeId = 0;
        if (is_object($store) && isset($store->id)) {
            $storeId = (int) $store->id;
        }
        if ($storeId < 1) {
            return;
        }
        try {
            $this->CI->load->model('Store_meta_event_model');
            $this->CI->Store_meta_event_model->log($storeId, array(
                'event_name' => 'oauth_' . substr((string) $event, 0, 64),
                'ok' => !isset($safe['ok']) || !empty($safe['ok']),
                'http_code' => isset($safe['http']) ? (int) $safe['http'] : 0,
                'error' => isset($safe['error']) ? (string) $safe['error'] : '',
                'payload' => $safe,
            ));
        } catch (Exception $e) {
        }
    }

    protected function safe_oauth_payload($data)
    {
        $blocked = array(
            'access_token', 'token', 'capi_token', 'client_secret', 'app_secret', 'code',
            'appsecret_proof', 'fb_exchange_token', 'state',
        );
        $out = array();
        foreach ($data as $key => $value) {
            if (in_array(strtolower((string) $key), $blocked, true)) {
                continue;
            }
            if (is_string($value)) {
                $value = $this->redact($value);
            } elseif (is_array($value)) {
                $value = $this->safe_oauth_payload($value);
            }
            $out[$key] = $value;
        }
        return $out;
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
        $id = trim((string) platform_setting('meta_app_id', ''));
        if ($id === '') {
            $id = trim((string) getenv('META_APP_ID'));
        }
        return $id;
    }

    public function app_secret()
    {
        $CI =& get_instance();
        $CI->load->model('Store_channel_model');
        $secret = trim((string) $CI->Store_channel_model->setting_secret('meta_app_secret'));
        if ($secret === '') {
            $secret = trim((string) getenv('META_APP_SECRET'));
        }
        return $secret;
    }

    public function configured()
    {
        return $this->app_id() !== '' && $this->app_secret() !== '';
    }

    public function store_public_base($store = null)
    {
        if (is_object($store) && !empty($store->domain)) {
            $host = preg_replace('/^https?:\/\//', '', strtolower(trim((string) $store->domain)));
            $host = rtrim($host, '/');
            if ($host !== '') {
                return 'https://' . $host;
            }
        }
        return rtrim((string) site_url(), '/');
    }

    public function redirect_uri($store = null)
    {
        $env = trim((string) getenv('META_REDIRECT_URI'));
        if ($env !== '') {
            return $env;
        }
        if (is_object($store) && !empty($store->domain)) {
            return $this->store_public_base($store) . '/store/channels/meta/callback';
        }
        return site_url('store/channels/meta/callback');
    }

    public function events_redirect_uri($store = null)
    {
        $env = trim((string) getenv('META_EVENTS_REDIRECT_URI'));
        if ($env !== '') {
            return $env;
        }
        if (is_object($store) && !empty($store->domain)) {
            return $this->store_public_base($store) . '/store/settings/meta/callback';
        }
        return site_url('store/settings/meta/callback');
    }

    public function oauth_scopes($purpose = 'channels')
    {
        if ($purpose === 'events') {
            // App 1041082522085418 Facebook Login rejects ads_read and
            // business_management (Invalid Scopes). public_profile is the only
            // proven-valid Event API Login permission.
            return array(
                'public_profile',
            );
        }
        return array(
            'public_profile',
            'pages_show_list',
            'business_management',
            'catalog_management',
            'ads_read',
        );
    }

    public function oauth_url($state, $store, $purpose = 'channels')
    {
        $redirect = ($purpose === 'events') ? $this->events_redirect_uri($store) : $this->redirect_uri($store);
        $scopes = $this->oauth_scopes($purpose);
        if ($purpose === 'events') {
            $scopes = $this->events_oauth_scopes_safe($scopes);
        }
        $scopeString = implode(',', $scopes);
        $queryParams = array(
            'client_id' => $this->app_id(),
            'redirect_uri' => $redirect,
            'state' => $state,
            'response_type' => 'code',
        );
        $query = http_build_query($queryParams);
        $url = self::DIALOG . '?' . $query . '&scope=' . $scopeString;
        $sent = array();
        $qs = parse_url($url, PHP_URL_QUERY);
        if (is_string($qs) && $qs !== '') {
            parse_str($qs, $sent);
        }
        $finalScope = isset($sent['scope']) ? (string) $sent['scope'] : $scopeString;
        $this->oauth_trace('oauth_start', array(
            'purpose' => $purpose,
            'scope_string' => $finalScope,
            'scopes' => $scopes,
            'redirect_uri' => $redirect,
            'client_id' => $this->app_id(),
            'dialog' => self::DIALOG,
            'has_pages_show_list' => strpos($finalScope, 'pages_show_list') !== false,
            'has_business_management' => strpos($finalScope, 'business_management') !== false,
            'has_catalog_management' => strpos($finalScope, 'catalog_management') !== false,
            'has_ads_read' => strpos($finalScope, 'ads_read') !== false,
        ), $store);
        return $url;
    }

    protected function events_oauth_scopes_safe($scopes)
    {
        $blocked = array(
            'ads_read',
            'business_management',
            'pages_show_list',
            'catalog_management',
            'ads_management',
            'pages_read_engagement',
            'pages_manage_metadata',
            'pages_manage_ads',
            'instagram_basic',
            'instagram_manage_insights',
            'instagram_content_publish',
        );
        $clean = array();
        if (is_array($scopes)) {
            foreach ($scopes as $scope) {
                $scope = trim((string) $scope);
                if ($scope === '' || in_array($scope, $blocked, true)) {
                    continue;
                }
                $clean[] = $scope;
            }
        }
        if (empty($clean)) {
            $clean = array('public_profile');
        }
        return array_values(array_unique($clean));
    }

    public function exchange_code($code, $redirectUri = null)
    {
        $this->reset_flags();
        if ($redirectUri === null || $redirectUri === '') {
            $redirectUri = $this->redirect_uri();
        }
        $this->oauth_trace('token_exchange_start', array(
            'redirect_uri' => $redirectUri,
            'has_code' => $code !== '',
            'client_id' => $this->app_id(),
        ));
        $short = $this->get_json(self::GRAPH . '/oauth/access_token', array(
            'client_id' => $this->app_id(),
            'client_secret' => $this->app_secret(),
            'redirect_uri' => $redirectUri,
            'code' => $code,
        ));
        if (empty($short['access_token'])) {
            $this->lastError = $this->error_from($short, 'Could not exchange Meta login code.');
            $this->oauth_trace('token_exchange', array(
                'ok' => false,
                'http' => $this->lastHttp,
                'error' => $this->last_error(),
            ));
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
        $ok = $token !== '';
        $this->oauth_trace('token_exchange', array(
            'ok' => $ok,
            'http' => $this->lastHttp,
            'has_user' => !empty($me['id']),
            'expires_in' => $expires,
            'error' => $ok ? '' : $this->last_error(),
        ));
        if (!$ok) {
            return null;
        }
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
        $rows = array();
        $json = $this->get_json(self::GRAPH . '/me/businesses', array(
            'fields' => 'id,name',
            'limit' => 100,
            'access_token' => $token,
        ));
        if (!empty($json['data']) && is_array($json['data'])) {
            foreach ($json['data'] as $row) {
                $id = isset($row['id']) ? (string) $row['id'] : '';
                if ($id === '') {
                    continue;
                }
                $rows[$id] = array(
                    'id' => $id,
                    'name' => isset($row['name']) ? $row['name'] : $id,
                );
            }
        }
        foreach ($this->user_ad_accounts($token) as $account) {
            if (empty($account['business']['id'])) {
                continue;
            }
            $id = (string) $account['business']['id'];
            if (isset($rows[$id])) {
                continue;
            }
            $rows[$id] = array(
                'id' => $id,
                'name' => isset($account['business']['name']) ? $account['business']['name'] : $id,
            );
        }
        return array_values($rows);
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
        $rows = array();
        foreach (array('owned_pixels', 'adspixels', 'client_pixels') as $edge) {
            $this->merge_pixel_rows($rows, $this->get_json(self::GRAPH . '/' . rawurlencode($businessId) . '/' . $edge, array(
                'fields' => 'id,name',
                'limit' => 100,
                'access_token' => $token,
            )));
        }
        foreach ($this->ad_accounts($businessId, $token) as $account) {
            $actId = isset($account['id']) ? (string) $account['id'] : '';
            if ($actId === '') {
                continue;
            }
            $this->merge_pixel_rows($rows, $this->get_json(self::GRAPH . '/' . rawurlencode($actId) . '/adspixels', array(
                'fields' => 'id,name',
                'limit' => 50,
                'access_token' => $token,
            )));
        }
        foreach ($this->user_ad_accounts($token) as $account) {
            $bizId = !empty($account['business']['id']) ? (string) $account['business']['id'] : '';
            if ($bizId !== '' && $bizId !== (string) $businessId) {
                continue;
            }
            $actId = isset($account['id']) ? (string) $account['id'] : '';
            if ($actId === '') {
                continue;
            }
            $this->merge_pixel_rows($rows, $this->get_json(self::GRAPH . '/' . rawurlencode($actId) . '/adspixels', array(
                'fields' => 'id,name',
                'limit' => 50,
                'access_token' => $token,
            )));
        }
        return array_values($rows);
    }

    protected function merge_pixel_rows(&$rows, $json)
    {
        if (empty($json['data']) || !is_array($json['data'])) {
            return;
        }
        foreach ($json['data'] as $row) {
            $id = isset($row['id']) ? (string) $row['id'] : '';
            if ($id === '') {
                continue;
            }
            $rows[$id] = array(
                'id' => $id,
                'name' => isset($row['name']) ? $row['name'] : $id,
            );
        }
    }

    public function user_ad_accounts($token)
    {
        $json = $this->get_json(self::GRAPH . '/me/adaccounts', array(
            'fields' => 'id,name,account_id,business{id,name}',
            'limit' => 100,
            'access_token' => $token,
        ));
        return isset($json['data']) && is_array($json['data']) ? $json['data'] : array();
    }

    public function pixel($pixelId, $token)
    {
        $pixelId = preg_replace('/\D+/', '', (string) $pixelId);
        if ($pixelId === '' || $token === '') {
            return null;
        }
        $json = $this->get_json(self::GRAPH . '/' . rawurlencode($pixelId), array(
            'fields' => 'id,name',
            'access_token' => $token,
        ));
        if (empty($json['id'])) {
            return null;
        }
        return array(
            'id' => (string) $json['id'],
            'name' => isset($json['name']) ? $json['name'] : (string) $json['id'],
        );
    }

    public function granted_permissions($token)
    {
        $json = $this->get_json(self::GRAPH . '/me/permissions', array(
            'access_token' => $token,
        ));
        $granted = array();
        if (!empty($json['data']) && is_array($json['data'])) {
            foreach ($json['data'] as $row) {
                if (empty($row['permission']) || empty($row['status']) || $row['status'] !== 'granted') {
                    continue;
                }
                $granted[] = (string) $row['permission'];
            }
        }
        return $granted;
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
        $rows = array();
        foreach (array('owned_ad_accounts', 'client_ad_accounts') as $edge) {
            $json = $this->get_json(self::GRAPH . '/' . rawurlencode($businessId) . '/' . $edge, array(
                'fields' => 'id,name,account_id',
                'limit' => 50,
                'access_token' => $token,
            ));
            if (empty($json['data']) || !is_array($json['data'])) {
                continue;
            }
            foreach ($json['data'] as $row) {
                $id = isset($row['id']) ? (string) $row['id'] : '';
                if ($id === '') {
                    continue;
                }
                $rows[$id] = $row;
            }
        }
        return array_values($rows);
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

    public function send_capi($pixelId, $token, $events, $testEventCode = '')
    {
        $conn = (object) array(
            'pixel_id' => $pixelId,
            'access_token' => $token,
        );
        return $this->send_events($conn, $events, $testEventCode, 8);
    }

    public function verify_events_manager_dataset($pixelId, $token, $store = null)
    {
        $pixelId = preg_replace('/\D+/', '', (string) $pixelId);
        $token = trim((string) $token);
        $this->lastVerifyEventId = '';
        if ($pixelId === '' || $token === '') {
            $this->lastError = 'Enter the Pixel / Dataset ID and the Events Manager CAPI access token.';
            return null;
        }
        $info = $this->pixel($pixelId, $token);
        $name = $info && !empty($info['name']) ? $info['name'] : $pixelId;
        $this->lastVerifyEventId = 'em-verify-' . substr(md5(uniqid('', true)), 0, 16);
        $ok = $this->send_capi($pixelId, $token, array(array(
            'event_name' => 'PageView',
            'event_time' => time(),
            'action_source' => 'website',
            'event_id' => $this->lastVerifyEventId,
            'user_data' => array(
                'client_user_agent' => 'ZENvello-Events-Manager-verify',
            ),
        )));
        $errorCode = 0;
        $errorMessage = '';
        if (!$ok && !empty($this->lastResponse['error'])) {
            $errorCode = isset($this->lastResponse['error']['code']) ? (int) $this->lastResponse['error']['code'] : 0;
            $errorMessage = isset($this->lastResponse['error']['message']) ? $this->redact($this->lastResponse['error']['message']) : $this->last_error();
        }
        $this->oauth_trace('capi_verify_pageview', array(
            'ok' => $ok,
            'event_name' => 'PageView',
            'http' => $this->lastHttp,
            'pixel_id' => $pixelId,
            'events_received' => $this->lastEventsReceived,
            'error_code' => $errorCode,
            'error' => $errorMessage,
        ), $store);
        if (!$ok) {
            return null;
        }
        return array(
            'id' => $pixelId,
            'name' => $name,
        );
    }

    public function send_events($conn, $events, $testEventCode = '', $timeout = 40)
    {
        $this->reset_flags();
        if (empty($conn->pixel_id) || empty($conn->access_token) || empty($events)) {
            $this->lastError = 'Pixel ID or access token is missing.';
            return false;
        }
        $fields = array(
            'data' => json_encode($events),
            'access_token' => $conn->access_token,
        );
        $testEventCode = trim((string) $testEventCode);
        if ($testEventCode !== '') {
            $fields['test_event_code'] = $testEventCode;
        }
        $json = $this->post_json(self::GRAPH . '/' . rawurlencode($conn->pixel_id) . '/events', $fields, (int) $timeout);
        $this->lastEventsReceived = isset($json['events_received']) ? (int) $json['events_received'] : 0;
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

    protected function post_json($url, $fields, $timeout = 40)
    {
        return $this->request('POST', $url, $fields, $timeout);
    }

    protected function request($method, $url, $fields, $timeout = 40)
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
            CURLOPT_CONNECTTIMEOUT => min(8, max(2, (int) $timeout)),
            CURLOPT_TIMEOUT => max(3, (int) $timeout),
            CURLOPT_HTTPHEADER => array('Accept: application/json'),
        ));
        $raw = curl_exec($ch);
        $http = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $this->lastHttp = $http;
        if ($raw === false) {
            $this->lastError = curl_error($ch);
            $this->last_retryable = true;
            curl_close($ch);
            return array();
        }
        curl_close($ch);
        $json = json_decode($raw, true);
        $this->lastResponse = is_array($json) ? $json : array('raw' => substr((string) $raw, 0, 2000));
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
        $this->lastHttp = 0;
        $this->lastResponse = array();
        $this->lastEventsReceived = 0;
        $this->lastVerifyEventId = '';
    }

    protected function error_from($json, $fallback)
    {
        if (!empty($json['error']['message'])) {
            $code = isset($json['error']['code']) ? $json['error']['code'] : '';
            $message = $json['error']['message'];
            return $code !== '' ? '(#' . $code . ') ' . $message : $message;
        }
        return $this->lastError !== '' ? $this->lastError : $fallback;
    }
}
