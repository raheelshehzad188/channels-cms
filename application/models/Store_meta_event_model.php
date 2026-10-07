<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Store_meta_event_model extends CI_Model {

    public function ensure_schema()
    {
        if (!$this->db->table_exists('store_meta_event_logs')) {
            $this->db->query("CREATE TABLE IF NOT EXISTS `store_meta_event_logs` (
                `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
                `store_id` int(10) unsigned NOT NULL,
                `event_name` varchar(80) NOT NULL DEFAULT '',
                `event_id` varchar(80) NOT NULL DEFAULT '',
                `status` varchar(20) NOT NULL DEFAULT 'failed',
                `http_code` int(11) NOT NULL DEFAULT 0,
                `events_received` int(11) NOT NULL DEFAULT 0,
                `error_message` text DEFAULT NULL,
                `payload_json` mediumtext DEFAULT NULL,
                `response_json` mediumtext DEFAULT NULL,
                `created_at` datetime NOT NULL DEFAULT current_timestamp(),
                PRIMARY KEY (`id`),
                KEY `store_created` (`store_id`,`created_at`),
                KEY `store_status` (`store_id`,`status`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
        }
        if (!$this->db->table_exists('store_meta_connections')) {
            $this->db->query("CREATE TABLE IF NOT EXISTS `store_meta_connections` (
                `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
                `store_id` int(10) unsigned NOT NULL,
                `status` varchar(30) NOT NULL DEFAULT 'disconnected',
                `access_token` text DEFAULT NULL,
                `capi_token` text DEFAULT NULL,
                `token_expires_at` datetime DEFAULT NULL,
                `external_user_id` varchar(64) DEFAULT NULL,
                `user_name` varchar(255) DEFAULT NULL,
                `business_id` varchar(64) DEFAULT NULL,
                `business_name` varchar(255) DEFAULT NULL,
                `pixel_id` varchar(64) DEFAULT NULL,
                `pixel_name` varchar(255) DEFAULT NULL,
                `extra_json` mediumtext DEFAULT NULL,
                `last_error` text DEFAULT NULL,
                `last_test_at` datetime DEFAULT NULL,
                `last_test_ok` tinyint(1) NOT NULL DEFAULT 0,
                `connected_at` datetime DEFAULT NULL,
                `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
                PRIMARY KEY (`id`),
                UNIQUE KEY `store_id` (`store_id`),
                KEY `status` (`status`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
        } else        if (!$this->db->field_exists('capi_token', 'store_meta_connections')) {
            $this->db->query("ALTER TABLE `store_meta_connections` ADD `capi_token` text DEFAULT NULL AFTER `access_token`");
        }
        if ($this->db->table_exists('store_orders') && !$this->db->field_exists('meta_event_id', 'store_orders')) {
            $this->db->query("ALTER TABLE `store_orders` ADD `meta_event_id` varchar(80) DEFAULT NULL");
        }
    }

    protected function setting_get($storeId, $key, $default = '')
    {
        if (!$this->db->table_exists('store_settings')) {
            return $default;
        }
        $keyCol = $this->db->field_exists('field_key', 'store_settings') ? 'field_key' : 'setting_key';
        $valCol = $keyCol === 'field_key' ? 'field_value' : 'setting_value';
        $row = $this->db
            ->where('store_id', (int) $storeId)
            ->where($keyCol, $key)
            ->get('store_settings')
            ->row_array();
        return $row && isset($row[$valCol]) ? $row[$valCol] : $default;
    }

    protected function setting_set($storeId, $key, $value)
    {
        if (!$this->db->table_exists('store_settings')) {
            return false;
        }
        $keyCol = $this->db->field_exists('field_key', 'store_settings') ? 'field_key' : 'setting_key';
        $valCol = $keyCol === 'field_key' ? 'field_value' : 'setting_value';
        $exists = $this->db
            ->where('store_id', (int) $storeId)
            ->where($keyCol, $key)
            ->count_all_results('store_settings');
        $payload = array(
            'store_id' => (int) $storeId,
            $keyCol => $key,
            $valCol => $value,
        );
        if ($this->db->field_exists('theme_id', 'store_settings') && $exists < 1) {
            $payload['theme_id'] = 0;
        }
        if ($exists) {
            return $this->db
                ->where('store_id', (int) $storeId)
                ->where($keyCol, $key)
                ->update('store_settings', array($valCol => $value));
        }
        return $this->db->insert('store_settings', $payload);
    }

    public function get_connection($storeId)
    {
        $this->ensure_schema();
        $this->load->model('Store_channel_model');
        $row = $this->db
            ->where('store_id', (int) $storeId)
            ->get('store_meta_connections')
            ->row();
        if ($row) {
            $row->access_token = $this->Store_channel_model->decrypt_setting($row->access_token);
            $row->capi_token = $this->db->field_exists('capi_token', 'store_meta_connections')
                ? $this->Store_channel_model->decrypt_setting($row->capi_token)
                : '';
            $row->extra = json_decode((string) $row->extra_json, true);
            if (!is_array($row->extra)) {
                $row->extra = array();
            }
            $row = $this->migrate_events_manager_token($row);
        }
        return $row;
    }

    public function public_connection($row)
    {
        if (!$row) {
            return null;
        }
        $copy = clone $row;
        $copy->has_token = trim((string) $copy->access_token) !== '';
        $copy->has_capi_token = $this->capi_token_from($copy) !== '';
        unset($copy->access_token, $copy->capi_token, $copy->extra_json);
        return $copy;
    }

    public function save_connection($storeId, $data)
    {
        $this->ensure_schema();
        $this->load->model('Store_channel_model');
        $storeId = (int) $storeId;
        $existing = $this->db->where('store_id', $storeId)->get('store_meta_connections')->row();
        $payload = $data;
        if (isset($payload['extra']) && is_array($payload['extra'])) {
            $existingExtra = array();
            if ($existing && !empty($existing->extra_json)) {
                $decoded = json_decode((string) $existing->extra_json, true);
                if (is_array($decoded)) {
                    $existingExtra = $decoded;
                }
            }
            $mergedExtra = array_merge($existingExtra, $payload['extra']);
            if (!empty($mergedExtra['capi_source'])) {
                unset($mergedExtra['dataset_source']);
            }
            $payload['extra_json'] = json_encode($mergedExtra);
            unset($payload['extra']);
        }
        foreach (array('access_token', 'capi_token') as $secret) {
            if (!array_key_exists($secret, $payload)) {
                continue;
            }
            if ($secret === 'capi_token' && !$this->db->field_exists('capi_token', 'store_meta_connections')) {
                unset($payload[$secret]);
                continue;
            }
            $token = (string) $payload[$secret];
            if ($token === '') {
                $payload[$secret] = '';
                continue;
            }
            $enc = $this->Store_channel_model->encrypt_setting($token);
            if ($enc === false || $enc === '') {
                unset($payload[$secret]);
            } else {
                $payload[$secret] = $enc;
            }
        }
        if (isset($payload['last_error'])) {
            $payload['last_error'] = $this->sanitize_error($payload['last_error']);
        }
        if ($existing) {
            $this->db->where('id', (int) $existing->id)->update('store_meta_connections', $payload);
            return (int) $existing->id;
        }
        $payload['store_id'] = $storeId;
        $this->db->insert('store_meta_connections', $payload);
        return (int) $this->db->insert_id();
    }

    public function disconnect($storeId)
    {
        $this->disconnect_oauth($storeId);
        $this->disconnect_capi($storeId);
    }

    public function disconnect_oauth($storeId)
    {
        $this->ensure_schema();
        $this->db->where('store_id', (int) $storeId)->update('store_meta_connections', array(
            'status' => 'disconnected',
            'access_token' => '',
            'token_expires_at' => null,
            'external_user_id' => '',
            'user_name' => '',
            'business_id' => '',
            'business_name' => '',
            'connected_at' => null,
        ));
    }

    public function disconnect_capi($storeId)
    {
        $this->ensure_schema();
        $storeId = (int) $storeId;
        $payload = array(
            'last_error' => '',
            'last_test_at' => null,
            'last_test_ok' => 0,
        );
        if ($this->db->field_exists('capi_token', 'store_meta_connections')) {
            $payload['capi_token'] = '';
        }
        $this->db->where('store_id', $storeId)->update('store_meta_connections', $payload);
        $this->setting_set($storeId, 'meta_capi_enabled', '0');
        $conn = $this->db->where('store_id', $storeId)->get('store_meta_connections')->row();
        if ($conn) {
            $extra = json_decode((string) $conn->extra_json, true);
            if (!is_array($extra)) {
                $extra = array();
            }
            unset($extra['capi_verified'], $extra['capi_source'], $extra['dataset_source']);
            $this->db->where('id', (int) $conn->id)->update('store_meta_connections', array(
                'extra_json' => json_encode($extra),
            ));
        }
        $this->clear_token($storeId);
    }

    public function oauth_ready($row)
    {
        return $this->oauth_connected($row);
    }

    public function oauth_connected($row)
    {
        return $row
            && $row->status !== 'disconnected'
            && trim((string) $row->access_token) !== ''
            && !$this->access_token_is_legacy_capi($row);
    }

    public function capi_token_from($row)
    {
        if (!$row) {
            return '';
        }
        $token = isset($row->capi_token) ? trim((string) $row->capi_token) : '';
        if ($token !== '') {
            return $token;
        }
        if ($this->access_token_is_legacy_capi($row)) {
            return trim((string) $row->access_token);
        }
        return '';
    }

    public function capi_verified($row)
    {
        if (!$row || $this->capi_token_from($row) === '' || trim((string) $row->pixel_id) === '') {
            return false;
        }
        if (!empty($row->extra['capi_verified'])) {
            return true;
        }
        return (int) $row->last_test_ok === 1;
    }

    public function config($storeId)
    {
        $this->load->model('Store_channel_model');
        $storeId = (int) $storeId;
        $settingsPixel = preg_replace('/\D+/', '', (string) $this->setting_get($storeId, 'meta_pixel_id', ''));
        $tokenEnc = (string) $this->setting_get($storeId, 'meta_capi_token', '');
        $token = $tokenEnc !== '' ? (string) $this->Store_channel_model->decrypt_setting($tokenEnc) : '';
        $testCode = trim((string) $this->setting_get($storeId, 'meta_test_event_code', ''));
        $pixelEnabledRaw = (string) $this->setting_get($storeId, 'meta_pixel_enabled', '');
        if ($pixelEnabledRaw === '') {
            $pixelEnabledRaw = (string) $this->setting_get($storeId, 'meta_events_enabled', '');
        }
        $capiRaw = (string) $this->setting_get($storeId, 'meta_capi_enabled', '');
        $settingsBusiness = trim((string) $this->setting_get($storeId, 'meta_business_id', ''));
        $adAccount = preg_replace('/\D+/', '', (string) $this->setting_get($storeId, 'meta_ad_account_id', ''));
        $pixel = $settingsPixel;
        $source = ($pixel !== '' || $token !== '') ? 'manual' : 'none';

        $enabled = ($pixelEnabledRaw === '1');
        if ($capiRaw === '1') {
            $capiToggle = true;
        } elseif ($capiRaw === '0') {
            $capiToggle = false;
        } else {
            $capiToggle = ($token !== '' && $pixel !== '');
        }

        $statusRow = null;
        if ($this->db->table_exists('store_meta_connections')) {
            $statusRow = $this->db
                ->select('last_error, last_test_at, last_test_ok, pixel_name')
                ->where('store_id', $storeId)
                ->get('store_meta_connections')
                ->row();
        }
        $lastError = $statusRow ? $this->sanitize_error($statusRow->last_error) : '';
        $capiConnected = $capiToggle && $pixel !== '' && $token !== '';
        $lastEventError = $this->sanitize_error($this->setting_get($storeId, 'meta_last_error', ''));
        $connected = ($enabled && $pixel !== '') || $capiConnected;

        return array(
            'enabled' => $enabled,
            'connected' => $connected,
            'pixel_id' => $pixel,
            'pixel_name' => $statusRow && !empty($statusRow->pixel_name) ? (string) $statusRow->pixel_name : '',
            'token' => $token,
            'token_set' => $token !== '',
            'token_hint' => '',
            'test_event_code' => $testCode,
            'from_settings' => $token !== '' && $pixel !== '',
            'from_channel' => false,
            'source' => $source,
            'oauth_status' => 'disconnected',
            'oauth_connected' => false,
            'business_id' => $settingsBusiness,
            'business_name' => '',
            'ad_account_id' => $adAccount,
            'user_name' => '',
            'token_expires_at' => '',
            'token_mask' => $token !== '' ? '••••••••••••' : '',
            'last_error' => $lastEventError !== '' ? $lastEventError : $lastError,
            'last_event_at' => (string) $this->setting_get($storeId, 'meta_last_event_at', ''),
            'last_event_status' => (string) $this->setting_get($storeId, 'meta_last_event_status', ''),
            'last_event_error' => $lastEventError,
            'needs_review' => false,
            'legacy_manual' => false,
            'legacy_kept' => false,
            'pixel_connected' => $enabled && $pixel !== '',
            'capi_enabled' => $capiToggle,
            'capi_connected' => $capiConnected,
            'last_test_ok' => $statusRow ? (int) $statusRow->last_test_ok === 1 : false,
            'last_test_at' => $statusRow ? (string) $statusRow->last_test_at : '',
            'granted_permissions' => array(),
            'ready' => $capiConnected,
        );
    }

    public function public_config($storeId)
    {
        $cfg = $this->config($storeId);
        unset($cfg['token']);
        $cfg['token'] = '';
        return $cfg;
    }

    public function persist_verified_capi($storeId, $pixelId, $token)
    {
        $this->load->model('Store_channel_model');
        $storeId = (int) $storeId;
        $pixelId = preg_replace('/\D+/', '', (string) $pixelId);
        $token = trim((string) $token);
        if ($pixelId === '' || $token === '') {
            return false;
        }
        $this->setting_set($storeId, 'meta_pixel_id', $pixelId);
        $enc = $this->Store_channel_model->encrypt_setting($token);
        if ($enc === false || $enc === '') {
            return false;
        }
        $this->setting_set($storeId, 'meta_capi_token', $enc);
        return true;
    }

    public function save_options($storeId, $data)
    {
        $storeId = (int) $storeId;
        $testCode = isset($data['test_event_code']) ? trim((string) $data['test_event_code']) : '';
        $enabled = !empty($data['enabled']) ? '1' : '0';
        $this->setting_set($storeId, 'meta_test_event_code', $testCode);
        $this->setting_set($storeId, 'meta_events_enabled', $enabled);
        if (array_key_exists('capi_enabled', $data)) {
            $this->setting_set($storeId, 'meta_capi_enabled', !empty($data['capi_enabled']) ? '1' : '0');
        }
        return $this->config($storeId);
    }

    public function save_integration($storeId, $data)
    {
        $this->load->model('Store_channel_model');
        $storeId = (int) $storeId;
        $pixelRaw = isset($data['pixel_id']) ? trim((string) $data['pixel_id']) : '';
        $adRaw = isset($data['ad_account_id']) ? trim((string) $data['ad_account_id']) : '';
        $pixel = preg_replace('/\D+/', '', $pixelRaw);
        $business = array_key_exists('business_id', $data) ? preg_replace('/\D+/', '', (string) $data['business_id']) : null;
        $adAccount = preg_replace('/\D+/', '', $adRaw);
        $enabled = !empty($data['pixel_enabled']) || !empty($data['enabled']);
        $capiEnabled = !empty($data['capi_enabled']);
        $token = isset($data['capi_token']) ? trim((string) $data['capi_token']) : '';
        $before = $this->config($storeId);

        if ($pixelRaw !== '' && $pixel === '') {
            return array('ok' => false, 'error' => 'Meta Pixel / Dataset ID must be the numeric ID from Events Manager.');
        }
        if ($adRaw !== '' && $adAccount === '') {
            return array('ok' => false, 'error' => 'Meta Ad Account ID must be numeric.');
        }
        if ($enabled && $pixel === '') {
            return array('ok' => false, 'error' => 'Enter this store’s Meta Pixel / Dataset ID before enabling the Pixel.');
        }
        if ($capiEnabled && $pixel === '') {
            return array('ok' => false, 'error' => 'Conversions API needs this store’s Pixel / Dataset ID.');
        }
        if ($capiEnabled && $token === '' && empty($before['token_set'])) {
            return array('ok' => false, 'error' => 'Enter the Events Manager access token for this store, or turn Conversions API off.');
        }
        if ($token !== '' && (strlen($token) < 20 || preg_match('/\s/', $token))) {
            return array('ok' => false, 'error' => 'That access token does not look valid. Paste the Events Manager token for this store.');
        }

        $this->setting_set($storeId, 'meta_pixel_id', $pixel);
        $this->setting_set($storeId, 'meta_pixel_enabled', $enabled ? '1' : '0');
        $this->setting_set($storeId, 'meta_events_enabled', $enabled ? '1' : '0');
        $this->setting_set($storeId, 'meta_capi_enabled', $capiEnabled ? '1' : '0');
        if ($business !== null) {
            $this->setting_set($storeId, 'meta_business_id', $business);
        }
        $this->setting_set($storeId, 'meta_ad_account_id', $adAccount);
        if (array_key_exists('test_event_code', $data)) {
            $this->setting_set($storeId, 'meta_test_event_code', trim((string) $data['test_event_code']));
        }

        $pixelChanged = $pixel !== preg_replace('/\D+/', '', (string) $before['pixel_id']);
        $connection = array(
            'pixel_id' => $pixel,
            'extra' => array('ad_account_id' => $adAccount),
        );
        if ($business !== null && $business !== '') {
            $connection['business_id'] = $business;
        }
        if ($token !== '') {
            $enc = $this->Store_channel_model->encrypt_setting($token);
            if ($enc === false || $enc === '') {
                return array('ok' => false, 'error' => 'The access token could not be encrypted with this server’s settings key.');
            }
            $this->setting_set($storeId, 'meta_capi_token', $enc);
            $connection['capi_token'] = $token;
            $connection['last_test_ok'] = 0;
            $connection['last_error'] = '';
            $connection['extra']['capi_source'] = 'events_manager';
            $connection['extra']['capi_verified'] = false;
            $connection['extra']['ad_account_id'] = $adAccount;
        } elseif ($pixelChanged) {
            $connection['last_test_ok'] = 0;
            $connection['extra']['capi_verified'] = false;
            $connection['extra']['ad_account_id'] = $adAccount;
        }
        $this->save_connection($storeId, $connection);
        return array('ok' => true, 'config' => $this->config($storeId));
    }

    public function purchase_already_sent($storeId, $orderId, $eventId)
    {
        $this->ensure_schema();
        $orderId = (int) $orderId;
        if ($orderId > 0 && $this->db->table_exists('store_orders') && $this->db->field_exists('meta_event_id', 'store_orders')) {
            $order = $this->db
                ->select('meta_event_id')
                ->where('id', $orderId)
                ->where('store_id', (int) $storeId)
                ->get('store_orders')
                ->row();
            if ($order && trim((string) $order->meta_event_id) !== '') {
                return true;
            }
        }
        if (!$this->db->table_exists('store_meta_event_logs')) {
            return false;
        }
        return $this->db
            ->where('store_id', (int) $storeId)
            ->where('event_name', 'Purchase')
            ->where('event_id', (string) $eventId)
            ->where('status', 'sent')
            ->count_all_results('store_meta_event_logs') > 0;
    }

    public function mark_purchase_sent($storeId, $orderId, $eventId)
    {
        $this->ensure_schema();
        $orderId = (int) $orderId;
        if ($orderId < 1 || !$this->db->table_exists('store_orders') || !$this->db->field_exists('meta_event_id', 'store_orders')) {
            return;
        }
        $this->db
            ->where('id', $orderId)
            ->where('store_id', (int) $storeId)
            ->update('store_orders', array('meta_event_id' => substr((string) $eventId, 0, 80)));
    }

    public function save_config($storeId, $data)
    {
        return $this->save_options($storeId, $data);
    }

    public function clear_token($storeId)
    {
        $this->setting_set((int) $storeId, 'meta_capi_token', '');
    }

    public function log($storeId, $row)
    {
        $this->ensure_schema();
        $payload = isset($row['payload']) ? $row['payload'] : array();
        $response = isset($row['response']) ? $row['response'] : array();
        $status = !empty($row['ok']) ? 'sent' : 'failed';
        $error = $this->sanitize_error(isset($row['error']) ? $row['error'] : '');
        $this->setting_set((int) $storeId, 'meta_last_event_at', date('Y-m-d H:i:s'));
        $this->setting_set((int) $storeId, 'meta_last_event_status', $status);
        $this->setting_set((int) $storeId, 'meta_last_error', $error);
        $this->db->insert('store_meta_event_logs', array(
            'store_id' => (int) $storeId,
            'event_name' => isset($row['event_name']) ? substr((string) $row['event_name'], 0, 80) : '',
            'event_id' => isset($row['event_id']) ? substr((string) $row['event_id'], 0, 80) : '',
            'status' => $status,
            'http_code' => isset($row['http_code']) ? (int) $row['http_code'] : 0,
            'events_received' => isset($row['events_received']) ? (int) $row['events_received'] : 0,
            'error_message' => $error,
            'payload_json' => json_encode($this->safe_payload($payload)),
            'response_json' => json_encode($this->safe_payload($response)),
            'created_at' => date('Y-m-d H:i:s'),
        ));
        $this->prune((int) $storeId);
    }

    public function recent($storeId, $limit = 50)
    {
        $this->ensure_schema();
        return $this->db
            ->where('store_id', (int) $storeId)
            ->order_by('id', 'desc')
            ->limit(max(1, min(200, (int) $limit)))
            ->get('store_meta_event_logs')
            ->result();
    }

    public function needs_app_review($error, $granted, $status)
    {
        $error = strtolower((string) $error);
        $needles = array(
            'permission',
            'permissions',
            '(#200)',
            '(#10)',
            'missing permission',
            'not been granted',
            'insufficient',
            'app review',
            'advanced access',
        );
        foreach ($needles as $needle) {
            if ($needle !== '' && strpos($error, $needle) !== false) {
                return true;
            }
        }
        return false;
    }

    public function sanitize_error($message)
    {
        $message = substr((string) $message, 0, 2000);
        $message = preg_replace('/EAA[A-Za-z0-9]+/', '[redacted]', $message);
        $message = preg_replace('/access_token=[^&\s]+/i', 'access_token=[redacted]', $message);
        $message = preg_replace('/client_secret=[^&\s]+/i', 'client_secret=[redacted]', $message);
        $message = preg_replace('/code=[A-Za-z0-9_-]{20,}/', 'code=[redacted]', $message);
        return $message;
    }

    protected function access_token_is_legacy_capi($row)
    {
        if (!$row) {
            return false;
        }
        $col = isset($row->capi_token) ? trim((string) $row->capi_token) : '';
        if ($col !== '') {
            return false;
        }
        return !empty($row->extra['dataset_source']) && $row->extra['dataset_source'] === 'events_manager'
            && trim((string) $row->access_token) !== '';
    }

    protected function migrate_events_manager_token($row)
    {
        if (!$this->access_token_is_legacy_capi($row) || !$this->db->field_exists('capi_token', 'store_meta_connections')) {
            return $row;
        }
        $token = trim((string) $row->access_token);
        $extra = is_array($row->extra) ? $row->extra : array();
        $extra['capi_source'] = 'events_manager';
        $extra['capi_verified'] = !empty($row->last_test_ok);
        unset($extra['dataset_source']);
        $this->save_connection($row->store_id, array(
            'capi_token' => $token,
            'access_token' => '',
            'extra' => $extra,
        ));
        $row->capi_token = $token;
        $row->access_token = '';
        $row->extra = $extra;
        return $row;
    }

    protected function prune($storeId)
    {
        $keep = 300;
        $row = $this->db
            ->select('id')
            ->where('store_id', (int) $storeId)
            ->order_by('id', 'desc')
            ->limit(1, $keep)
            ->get('store_meta_event_logs')
            ->row();
        if ($row) {
            $this->db
                ->where('store_id', (int) $storeId)
                ->where('id <', (int) $row->id)
                ->delete('store_meta_event_logs');
        }
    }

    protected function safe_payload($data)
    {
        if (!is_array($data)) {
            return array();
        }
        $out = $data;
        foreach (array('access_token', 'token', 'capi_token', 'appsecret_proof', 'client_secret', 'code') as $key) {
            if (isset($out[$key])) {
                unset($out[$key]);
            }
        }
        if (isset($out['user_data']) && is_array($out['user_data'])) {
            foreach (array('em', 'ph', 'fn', 'ln', 'external_id') as $hashKey) {
                if (isset($out['user_data'][$hashKey])) {
                    $out['user_data'][$hashKey] = '[hashed]';
                }
            }
        }
        return $out;
    }
}
