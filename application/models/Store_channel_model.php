<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Store_channel_model extends CI_Model {

    public $last_retryable = false;
    public $last_job_ok = true;
    public $last_job_error = '';

    public function ensure_schema()
    {
        if (!$this->db->table_exists('store_channel_connections')) {
            $this->db->query("CREATE TABLE IF NOT EXISTS `store_channel_connections` (
                `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
                `store_id` int(10) unsigned NOT NULL,
                `platform` varchar(20) NOT NULL,
                `status` varchar(30) NOT NULL DEFAULT 'disconnected',
                `access_token` text DEFAULT NULL,
                `refresh_token` text DEFAULT NULL,
                `token_expires_at` datetime DEFAULT NULL,
                `external_user_id` varchar(64) DEFAULT NULL,
                `business_id` varchar(64) DEFAULT NULL,
                `business_name` varchar(255) DEFAULT NULL,
                `page_id` varchar(64) DEFAULT NULL,
                `page_name` varchar(255) DEFAULT NULL,
                `page_token` text DEFAULT NULL,
                `instagram_id` varchar(64) DEFAULT NULL,
                `instagram_username` varchar(255) DEFAULT NULL,
                `catalog_id` varchar(64) DEFAULT NULL,
                `catalog_name` varchar(255) DEFAULT NULL,
                `pixel_id` varchar(64) DEFAULT NULL,
                `pixel_name` varchar(255) DEFAULT NULL,
                `ad_account_id` varchar(64) DEFAULT NULL,
                `advertiser_id` varchar(64) DEFAULT NULL,
                `advertiser_name` varchar(255) DEFAULT NULL,
                `extra_json` mediumtext DEFAULT NULL,
                `last_sync_at` datetime DEFAULT NULL,
                `last_error` text DEFAULT NULL,
                `connected_at` datetime DEFAULT NULL,
                `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
                PRIMARY KEY (`id`),
                UNIQUE KEY `store_platform` (`store_id`,`platform`),
                KEY `platform` (`platform`),
                KEY `status` (`status`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
        }
        $this->load->model('Channel_job_model');
        $this->Channel_job_model->ensure_schema();
        $this->ensure_hmac_secret();
        $this->ensure_cron_key();
    }

    public function get($storeId, $platform)
    {
        $this->ensure_schema();
        $row = $this->db
            ->where('store_id', (int) $storeId)
            ->where('platform', $platform)
            ->get('store_channel_connections')
            ->row();
        if ($row) {
            $row->access_token = $this->decrypt_value($row->access_token);
            $row->refresh_token = $this->decrypt_value($row->refresh_token);
            $row->page_token = $this->decrypt_value($row->page_token);
            $row->extra = json_decode($row->extra_json, true);
            if (!is_array($row->extra)) {
                $row->extra = array();
            }
        }
        return $row;
    }

    public function public_row($row)
    {
        if (!$row) {
            return null;
        }
        $copy = clone $row;
        $copy->has_token = ($copy->access_token !== '');
        unset($copy->access_token, $copy->refresh_token, $copy->page_token, $copy->extra);
        return $copy;
    }

    public function is_ready($storeId, $platform)
    {
        $row = $this->get($storeId, $platform);
        return $row && $row->status === 'connected' && $row->access_token !== '';
    }

    public function save($storeId, $platform, $data)
    {
        $this->ensure_schema();
        $storeId = (int) $storeId;
        $existing = $this->db
            ->where('store_id', $storeId)
            ->where('platform', $platform)
            ->get('store_channel_connections')
            ->row();

        $payload = $data;
        foreach (array('access_token', 'refresh_token', 'page_token') as $secret) {
            if (array_key_exists($secret, $payload) && $payload[$secret] !== null && $payload[$secret] !== '') {
                $enc = $this->encrypt_value($payload[$secret]);
                if ($enc === false) {
                    unset($payload[$secret]);
                    continue;
                }
                $payload[$secret] = $enc;
            }
        }
        if (isset($payload['extra']) && is_array($payload['extra'])) {
            $payload['extra_json'] = json_encode($payload['extra']);
            unset($payload['extra']);
        }

        if ($existing) {
            $this->db->where('id', (int) $existing->id)->update('store_channel_connections', $payload);
            return (int) $existing->id;
        }
        $payload['store_id'] = $storeId;
        $payload['platform'] = $platform;
        $this->db->insert('store_channel_connections', $payload);
        return (int) $this->db->insert_id();
    }

    public function disconnect($storeId, $platform)
    {
        $this->load->model('Channel_job_model');
        $this->Channel_job_model->cancel_for($storeId, $platform);
        return $this->save($storeId, $platform, array(
            'status' => 'disconnected',
            'access_token' => '',
            'refresh_token' => '',
            'page_token' => '',
            'token_expires_at' => null,
            'external_user_id' => '',
            'business_id' => '',
            'business_name' => '',
            'page_id' => '',
            'page_name' => '',
            'instagram_id' => '',
            'instagram_username' => '',
            'catalog_id' => '',
            'catalog_name' => '',
            'pixel_id' => '',
            'pixel_name' => '',
            'ad_account_id' => '',
            'advertiser_id' => '',
            'advertiser_name' => '',
            'extra_json' => '{}',
            'last_error' => '',
            'connected_at' => null,
        ));
    }

    public function mark_error($storeId, $platform, $error)
    {
        return $this->save($storeId, $platform, array('last_error' => substr((string) $error, 0, 2000)));
    }

    public function mark_synced($storeId, $platform)
    {
        return $this->save($storeId, $platform, array(
            'last_sync_at' => date('Y-m-d H:i:s'),
            'last_error' => '',
        ));
    }

    public function mark_token_expired($storeId, $platform, $error)
    {
        return $this->save($storeId, $platform, array(
            'status' => 'token_expired',
            'last_error' => substr((string) $error, 0, 2000),
        ));
    }

    public function queue_sync_store($store, $platform)
    {
        $this->load->model('Channel_job_model');
        $id = $this->Channel_job_model->enqueue($store->id, $platform, 'sync_store', array('platform' => $platform));
        $this->Channel_job_model->try_dispatch();
        return $id;
    }

    public function queue_sync_product($store, $productId)
    {
        $this->load->model('Channel_job_model');
        foreach (array('meta', 'tiktok') as $platform) {
            $conn = $this->get($store->id, $platform);
            if (!$conn || $conn->status !== 'connected') {
                continue;
            }
            $this->Channel_job_model->enqueue($store->id, $platform, 'sync_product', array('product_id' => (int) $productId));
        }
        $this->Channel_job_model->try_dispatch();
    }

    public function queue_delete_product($store, $productId)
    {
        $this->load->model('Channel_job_model');
        foreach (array('meta', 'tiktok') as $platform) {
            $conn = $this->get($store->id, $platform);
            if (!$conn || $conn->status !== 'connected') {
                continue;
            }
            $this->Channel_job_model->enqueue($store->id, $platform, 'delete_product', array('product_id' => (int) $productId));
        }
        $this->Channel_job_model->try_dispatch();
    }

    public function queue_event($storeId, $platform, $event, $urgent = false)
    {
        $this->load->model('Channel_job_model');
        $type = $platform === 'meta' ? 'meta_event' : 'tiktok_event';
        $this->Channel_job_model->enqueue($storeId, $platform, $type, $event);
        if ($urgent) {
            $this->Channel_job_model->try_dispatch();
        }
    }

    public function catalog_items($store)
    {
        $this->load->model('Store_product_model');
        $rows = $this->Store_product_model->mine($store->id);
        $out = array();
        $currency = store_currency($store);
        foreach ($rows as $row) {
            if ((int) $row->status !== 1) {
                continue;
            }
            apply_storefront_pricing($row, $store->id);
            $images = $this->product_image_urls($row);
            $url = $this->public_product_url($store, $row);
            $variations = array();
            if ($this->db->table_exists('product_variations')) {
                $variations = $this->db->where('product_id', (int) $row->id)->get('product_variations')->result();
            }
            $base = array(
                'product_id' => (int) $row->id,
                'name' => $row->name,
                'description' => $this->plain_description($row),
                'sku' => isset($row->sku) ? trim((string) $row->sku) : '',
                'brand' => !empty($row->brand) ? $row->brand : $store->name,
                'price' => (float) $row->price,
                'currency' => $currency,
                'stock' => (int) $row->stock,
                'url' => $url,
                'image' => isset($images[0]) ? $images[0] : '',
                'images' => $images,
                'item_group_id' => (string) $row->id,
            );
            if (empty($variations)) {
                $base['id'] = (string) $row->id;
                $out[] = $base;
                continue;
            }
            foreach ($variations as $var) {
                $item = $base;
                $item['id'] = (string) $row->id . '_' . (int) $var->id;
                $label = trim($var->option_name . ' ' . $var->option_value);
                if ($label !== '') {
                    $item['name'] = $row->name . ' - ' . $label;
                }
                if (!empty($var->sku)) {
                    $item['sku'] = $var->sku;
                }
                if ((float) $var->price > 0) {
                    $item['price'] = (float) $var->price;
                }
                $item['stock'] = (int) $var->stock;
                if (!empty($var->image)) {
                    $varImage = $this->absolute_media($var->image);
                    if ($varImage !== '') {
                        $item['image'] = $varImage;
                        array_unshift($item['images'], $varImage);
                        $item['images'] = array_values(array_unique($item['images']));
                    }
                }
                $out[] = $item;
            }
        }
        return $out;
    }

    public function retailer_ids_for_product($productId)
    {
        $ids = array((string) $productId);
        if ($this->db->table_exists('product_variations')) {
            $vars = $this->db->select('id')->where('product_id', (int) $productId)->get('product_variations')->result();
            foreach ($vars as $var) {
                $ids[] = (string) $productId . '_' . (int) $var->id;
            }
        }
        return $ids;
    }

    public function store_products($store)
    {
        return $this->catalog_items($store);
    }

    public function sync_store($store, $platform = null)
    {
        $this->last_retryable = false;
        $errors = array();
        $platforms = $platform ? array($platform) : array('meta', 'tiktok');
        $products = $this->catalog_items($store);
        foreach ($platforms as $name) {
            $conn = $this->fresh_connection($store->id, $name);
            if (!$conn || $conn->status !== 'connected' || $conn->access_token === '') {
                continue;
            }
            if ($name === 'meta') {
                $this->load->library('meta_commerce');
                $ok = $this->meta_commerce->sync_products($conn, $products);
                $this->last_retryable = $this->last_retryable || $this->meta_commerce->last_retryable;
                if ($ok) {
                    $this->mark_synced($store->id, 'meta');
                } else {
                    $err = $this->meta_commerce->last_error();
                    $this->handle_api_error($store->id, 'meta', $err, $this->meta_commerce->last_auth_error);
                    $errors[] = 'Meta: ' . $err;
                }
            } else {
                $this->load->library('tiktok_commerce');
                $ok = $this->tiktok_commerce->sync_products($conn, $products);
                $this->last_retryable = $this->last_retryable || $this->tiktok_commerce->last_retryable;
                if ($ok) {
                    $this->mark_synced($store->id, 'tiktok');
                } else {
                    $err = $this->tiktok_commerce->last_error();
                    $this->handle_api_error($store->id, 'tiktok', $err, $this->tiktok_commerce->last_auth_error);
                    $errors[] = 'TikTok: ' . $err;
                }
            }
        }
        return $errors;
    }

    public function sync_product($store, $productId)
    {
        $this->last_retryable = false;
        $this->last_job_ok = true;
        $this->last_job_error = '';
        $productId = (int) $productId;
        $one = array();
        foreach ($this->catalog_items($store) as $row) {
            if ((int) $row['product_id'] === $productId) {
                $one[] = $row;
            }
        }
        if (empty($one)) {
            $this->delete_product($store, $productId);
            return;
        }
        foreach (array('meta', 'tiktok') as $platform) {
            $conn = $this->fresh_connection($store->id, $platform);
            if (!$conn || $conn->status !== 'connected' || $conn->access_token === '') {
                continue;
            }
            if ($platform === 'meta') {
                $this->load->library('meta_commerce');
                $ok = $this->meta_commerce->sync_products($conn, $one);
                if (!$ok) {
                    $this->last_job_ok = false;
                    $this->last_job_error = $this->meta_commerce->last_error();
                    $this->last_retryable = $this->last_retryable || $this->meta_commerce->last_retryable;
                    $this->handle_api_error($store->id, 'meta', $this->last_job_error, $this->meta_commerce->last_auth_error);
                }
            } else {
                $this->load->library('tiktok_commerce');
                $ok = $this->tiktok_commerce->sync_products($conn, $one);
                if (!$ok) {
                    $this->last_job_ok = false;
                    $this->last_job_error = $this->tiktok_commerce->last_error();
                    $this->last_retryable = $this->last_retryable || $this->tiktok_commerce->last_retryable;
                    $this->handle_api_error($store->id, 'tiktok', $this->last_job_error, $this->tiktok_commerce->last_auth_error);
                }
            }
        }
    }

    public function delete_product($store, $productId)
    {
        $ids = $this->retailer_ids_for_product($productId);
        foreach (array('meta', 'tiktok') as $platform) {
            $conn = $this->fresh_connection($store->id, $platform);
            if (!$conn || $conn->status !== 'connected' || $conn->access_token === '') {
                continue;
            }
            if ($platform === 'meta') {
                $this->load->library('meta_commerce');
                foreach ($ids as $rid) {
                    $this->meta_commerce->delete_product($conn, $rid);
                }
            } else {
                $this->load->library('tiktok_commerce');
                foreach ($ids as $rid) {
                    $this->tiktok_commerce->delete_product($conn, $rid);
                }
            }
        }
    }

    public function send_queued_event($store, $platform, $event)
    {
        $conn = $this->fresh_connection($store->id, $platform);
        if (!$conn || $conn->status !== 'connected' || $conn->access_token === '' || $conn->pixel_id === '') {
            return array('ok' => true, 'retryable' => false, 'error' => '');
        }
        if ($platform === 'meta') {
            $this->load->library('meta_commerce');
            $ok = $this->meta_commerce->send_events($conn, array($event));
            return array(
                'ok' => $ok,
                'retryable' => $this->meta_commerce->last_retryable,
                'error' => $ok ? '' : $this->meta_commerce->last_error(),
            );
        }
        $this->load->library('tiktok_commerce');
        $ok = $this->tiktok_commerce->send_event($conn, $event);
        return array(
            'ok' => $ok,
            'retryable' => $this->tiktok_commerce->last_retryable,
            'error' => $ok ? '' : $this->tiktok_commerce->last_error(),
        );
    }

    public function fresh_connection($storeId, $platform)
    {
        $conn = $this->get($storeId, $platform);
        if (!$conn || $conn->access_token === '') {
            return $conn;
        }
        if ($conn->status === 'disconnected') {
            return $conn;
        }
        $this->refresh_token_if_needed($conn);
        return $this->get($storeId, $platform);
    }

    public function refresh_token_if_needed($conn)
    {
        if (!$conn || $conn->access_token === '') {
            return $conn;
        }
        $expires = $conn->token_expires_at ? strtotime($conn->token_expires_at) : 0;
        $soon = $expires > 0 && $expires < (time() + 86400 * 7);
        if ($conn->platform === 'tiktok' && ($soon || $expires < time()) && $conn->refresh_token !== '') {
            $this->load->library('tiktok_commerce');
            $fresh = $this->tiktok_commerce->refresh_access($conn->refresh_token);
            if ($fresh && !empty($fresh['access_token'])) {
                $this->save($conn->store_id, 'tiktok', array(
                    'access_token' => $fresh['access_token'],
                    'refresh_token' => !empty($fresh['refresh_token']) ? $fresh['refresh_token'] : $conn->refresh_token,
                    'token_expires_at' => date('Y-m-d H:i:s', time() + (int) $fresh['expires_in']),
                    'status' => $conn->status === 'token_expired' ? 'connected' : $conn->status,
                    'last_error' => '',
                ));
            } elseif ($this->tiktok_commerce->last_auth_error) {
                $this->mark_token_expired($conn->store_id, 'tiktok', $this->tiktok_commerce->last_error());
            }
            return;
        }
        if ($conn->platform === 'meta' && $soon) {
            $this->load->library('meta_commerce');
            $fresh = $this->meta_commerce->refresh_long_lived($conn->access_token);
            if ($fresh && !empty($fresh['access_token'])) {
                $this->save($conn->store_id, 'meta', array(
                    'access_token' => $fresh['access_token'],
                    'token_expires_at' => date('Y-m-d H:i:s', time() + (int) $fresh['expires_in']),
                    'status' => $conn->status === 'token_expired' ? 'connected' : $conn->status,
                    'last_error' => '',
                ));
            }
        }
    }

    public function make_state($storeId, $platform)
    {
        $nonce = bin2hex($this->random_bytes(8));
        $payload = json_encode(array(
            's' => (int) $storeId,
            'p' => $platform,
            'n' => $nonce,
            't' => time(),
        ));
        $state = $this->base64url($payload) . '.' . $this->sign($payload);
        $CI =& get_instance();
        $CI->session->set_userdata('channel_oauth_nonce_' . $platform, $nonce);
        $CI->session->set_userdata('channel_oauth_store_' . $platform, (int) $storeId);
        return $state;
    }

    public function read_state($state, $platform, $storeId)
    {
        $parts = explode('.', (string) $state, 2);
        if (count($parts) !== 2) {
            return null;
        }
        $payload = $this->base64url_decode($parts[0]);
        if ($payload === '' || !hash_equals($this->sign($payload), $parts[1])) {
            return null;
        }
        $data = json_decode($payload, true);
        if (!is_array($data) || empty($data['s']) || empty($data['p']) || empty($data['n'])) {
            return null;
        }
        if ((int) $data['s'] !== (int) $storeId || $data['p'] !== $platform) {
            return null;
        }
        if ((time() - (int) $data['t']) > 900) {
            return null;
        }
        $CI =& get_instance();
        $nonceKey = 'channel_oauth_nonce_' . $platform;
        $expected = (string) $CI->session->userdata($nonceKey);
        if ($expected === '' || !hash_equals($expected, (string) $data['n'])) {
            return null;
        }
        $CI->session->unset_userdata($nonceKey);
        $CI->session->unset_userdata('channel_oauth_store_' . $platform);
        return $data;
    }

    public function encrypt_setting($value)
    {
        return $this->encrypt_value($value);
    }

    public function decrypt_setting($value)
    {
        return $this->decrypt_value($value);
    }

    public function setting_secret($key)
    {
        return $this->decrypt_value(platform_setting($key, ''));
    }

    public function public_product_url($store, $product)
    {
        $path = 'product/';
        $slug = is_object($product) && !empty($product->slug) ? trim((string) $product->slug) : '';
        $path .= $slug !== '' ? (function_exists('storefront_path_slug') ? storefront_path_slug($slug, 'product') : rawurlencode($slug)) : (is_object($product) ? (int) $product->id : (int) $product);
        return $this->store_public_base($store) . '/' . $path;
    }

    public function store_public_base($store)
    {
        $host = '';
        if (!empty($store->custom_domain)) {
            $host = strtolower(trim($store->custom_domain));
        } elseif (!empty($store->domain)) {
            $host = strtolower(trim($store->domain));
        }
        $host = preg_replace('/:\d+$/', '', $host);
        if ($host === '' || in_array($host, array('localhost', '127.0.0.1'), true)) {
            return rtrim(base_url(), '/');
        }
        $scheme = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'https';
        return $scheme . '://' . $host;
    }

    protected function handle_api_error($storeId, $platform, $error, $authError)
    {
        if ($authError) {
            $this->mark_token_expired($storeId, $platform, $error);
            return;
        }
        $this->mark_error($storeId, $platform, $error);
    }

    protected function product_image_urls($row)
    {
        $urls = array();
        if (!empty($row->image)) {
            $first = $this->absolute_media($row->image);
            if ($first !== '') {
                $urls[] = $first;
            }
        }
        if ($this->db->table_exists('product_images')) {
            $gallery = $this->db->where('product_id', (int) $row->id)->order_by('id', 'asc')->get('product_images')->result();
            foreach ($gallery as $img) {
                $url = $this->absolute_media($img->image);
                if ($url !== '') {
                    $urls[] = $url;
                }
            }
        }
        return array_values(array_unique($urls));
    }

    protected function absolute_media($path)
    {
        $path = trim((string) $path);
        if ($path === '') {
            return '';
        }
        if (strpos($path, 'http://') === 0 || strpos($path, 'https://') === 0) {
            return $path;
        }
        return rtrim(base_url(), '/') . '/' . ltrim($path, '/');
    }

    protected function plain_description($row)
    {
        $desc = trim(preg_replace('/\s+/', ' ', strip_tags((string) $row->description)));
        if ($desc === '') {
            $desc = $row->name;
        }
        return function_exists('mb_substr') ? mb_substr($desc, 0, 4990) : substr($desc, 0, 4990);
    }

    protected function sign($payload)
    {
        return hash_hmac('sha256', $payload, $this->hmac_secret());
    }

    protected function hmac_secret()
    {
        $secret = (string) platform_setting('channel_hmac_secret', '');
        if ($secret !== '') {
            return $secret;
        }
        $this->ensure_hmac_secret();
        return (string) platform_setting('channel_hmac_secret', 'ec3-channels');
    }

    protected function ensure_hmac_secret()
    {
        if (platform_setting('channel_hmac_secret', '') !== '') {
            return;
        }
        $this->write_platform_setting('channel_hmac_secret', bin2hex($this->random_bytes(32)));
    }

    protected function ensure_cron_key()
    {
        if (platform_setting('channel_cron_key', '') !== '') {
            return;
        }
        $this->write_platform_setting('channel_cron_key', bin2hex($this->random_bytes(16)));
    }

    protected function write_platform_setting($key, $value)
    {
        $exists = $this->db->where('setting_key', $key)->get('platform_settings')->row();
        if ($exists) {
            $this->db->where('setting_key', $key)->update('platform_settings', array('setting_value' => $value));
            return;
        }
        if ($this->db->table_exists('platform_settings')) {
            $this->db->insert('platform_settings', array(
                'setting_key' => $key,
                'setting_value' => $value,
            ));
        }
    }

    protected function encrypt_value($value)
    {
        $value = (string) $value;
        if ($value === '') {
            return '';
        }
        $key = hash('sha256', $this->hmac_secret() . '|token', true);
        $iv = $this->random_bytes(16);
        $enc = openssl_encrypt($value, 'AES-256-CBC', $key, OPENSSL_RAW_DATA, $iv);
        if ($enc === false) {
            return false;
        }
        return 'enc:' . base64_encode($iv . $enc);
    }

    protected function decrypt_value($value)
    {
        $value = (string) $value;
        if ($value === '') {
            return '';
        }
        if (strpos($value, 'enc:') !== 0) {
            return $value;
        }
        $raw = base64_decode(substr($value, 4), true);
        if ($raw === false || strlen($raw) < 17) {
            return '';
        }
        $key = hash('sha256', $this->hmac_secret() . '|token', true);
        $iv = substr($raw, 0, 16);
        $plain = openssl_decrypt(substr($raw, 16), 'AES-256-CBC', $key, OPENSSL_RAW_DATA, $iv);
        if ($plain !== false && $plain !== '') {
            return $plain;
        }
        $legacySecret = (string) config_item('encryption_key');
        if ($legacySecret === '') {
            $legacySecret = platform_setting('meta_app_secret', '') . platform_setting('tiktok_app_secret', '') . 'ec3-channels';
        }
        $legacyKey = hash('sha256', hash_hmac('sha256', 'token-key', $legacySecret), true);
        $plain = openssl_decrypt(substr($raw, 16), 'AES-256-CBC', $legacyKey, OPENSSL_RAW_DATA, $iv);
        return $plain === false ? '' : $plain;
    }

    protected function random_bytes($len)
    {
        if (function_exists('random_bytes')) {
            return random_bytes($len);
        }
        return openssl_random_pseudo_bytes($len);
    }

    protected function base64url($data)
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    protected function base64url_decode($data)
    {
        $remainder = strlen($data) % 4;
        if ($remainder) {
            $data .= str_repeat('=', 4 - $remainder);
        }
        $out = base64_decode(strtr($data, '-_', '+/'), true);
        return $out === false ? '' : $out;
    }
}
