<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Meta_events_integration {

    public $lastError = '';
    protected $CI;

    public function __construct()
    {
        $this->CI =& get_instance();
        $this->CI->load->library('meta_commerce');
        $this->CI->load->model('Store_meta_event_model');
        $this->CI->load->model('Store_channel_model');
    }

    public function last_error()
    {
        return $this->CI->Store_meta_event_model->sanitize_error($this->lastError);
    }

    public function oauth_url($store)
    {
        $this->lastError = 'Meta Pixel uses the Pixel ID and Events Manager token saved for this store.';
        return '';
    }

    public function exchange_authorization_code($code, $store = null)
    {
        $this->lastError = '';
        $token = $this->CI->meta_commerce->exchange_code($code, $this->CI->meta_commerce->events_redirect_uri($store));
        $ok = $token && !empty($token['access_token']);
        if (!$ok) {
            $this->lastError = $this->CI->meta_commerce->last_error() ?: 'Could not exchange Meta authorization code.';
        }
        $this->CI->meta_commerce->oauth_trace('token_exchange_result', array(
            'ok' => $ok,
            'purpose' => 'events',
            'has_user' => $ok && !empty($token['user_id']),
            'expires_in' => $ok ? (int) $token['expires_in'] : 0,
            'error' => $ok ? '' : $this->last_error(),
        ), $store);
        if (!$ok) {
            return null;
        }
        return $token;
    }

    public function granted_permissions($token)
    {
        return $this->CI->meta_commerce->granted_permissions($token);
    }

    public function get_authorized_businesses($token, $store = null)
    {
        return array();
    }

    public function get_available_pixels($businessId, $token, $store = null)
    {
        return array();
    }

    public function asset_options($conn)
    {
        return array(
            'businesses' => array(),
            'pixels' => array(),
            'permissions' => array(),
            'missing_permissions' => array(),
        );
    }

    public function complete_login($store, $token)
    {
        $this->lastError = 'Facebook Login is not used for Meta Pixel or Conversions API.';
        return array(
            'status' => 'disconnected',
            'auto' => false,
            'error' => $this->lastError,
            'permissions' => array(),
        );
    }

    public function save_assets($store, $businessId, $pixelId)
    {
        $this->lastError = 'Enter the Pixel / Dataset ID on Meta Integration. Asset selection is no longer used.';
        return array('ok' => false, 'error' => $this->lastError);
    }

    public function save_events_manager_dataset($store, $pixelId, $token)
    {
        $this->lastError = '';
        $pixelId = preg_replace('/\D+/', '', (string) $pixelId);
        $token = trim((string) $token);
        $verified = $this->CI->meta_commerce->verify_events_manager_dataset($pixelId, $token, $store);
        $diag = array(
            'ok' => !empty($verified),
            'event_name' => 'PageView',
            'event_id' => $this->CI->meta_commerce->lastVerifyEventId,
            'http_code' => (int) $this->CI->meta_commerce->lastHttp,
            'events_received' => (int) $this->CI->meta_commerce->lastEventsReceived,
            'error' => $verified ? '' : ($this->CI->meta_commerce->last_error() ?: 'Meta did not accept that Pixel / Dataset and CAPI token.'),
            'payload' => array('event_name' => 'PageView'),
            'response' => $this->CI->meta_commerce->lastResponse,
        );
        $this->CI->Store_meta_event_model->log($store->id, $diag);
        if (!$verified) {
            $this->lastError = $diag['error'];
            return array('ok' => false, 'error' => $this->lastError);
        }
        $this->CI->Store_meta_event_model->save_connection($store->id, array(
            'capi_token' => $token,
            'pixel_id' => $verified['id'],
            'pixel_name' => $verified['name'],
            'last_error' => '',
            'last_test_ok' => 1,
            'last_test_at' => date('Y-m-d H:i:s'),
            'extra' => array(
                'capi_source' => 'events_manager',
                'capi_verified' => true,
            ),
        ));
        $this->CI->Store_meta_event_model->persist_verified_capi($store->id, $verified['id'], $token);
        $this->CI->Store_meta_event_model->save_options($store->id, array(
            'enabled' => true,
            'capi_enabled' => true,
            'test_event_code' => '',
        ));
        $this->CI->load->library('channel_events');
        $this->CI->channel_events->clear_meta_config_cache($store->id);
        $this->CI->meta_commerce->oauth_trace('capi_verified', array(
            'ok' => true,
            'event_name' => 'PageView',
            'http' => (int) $this->CI->meta_commerce->lastHttp,
            'pixel_id' => $verified['id'],
        ), $store);
        return array('ok' => true, 'connected' => true, 'capi_connected' => true, 'error' => '');
    }

    public function disconnect($store)
    {
        $this->disconnect_oauth($store);
        $this->disconnect_capi($store);
    }

    public function disconnect_oauth($store)
    {
        $conn = $this->CI->Store_meta_event_model->get_connection($store->id);
        if ($conn && $this->CI->Store_meta_event_model->oauth_connected($conn)) {
            $this->CI->meta_commerce->revoke($conn->access_token);
        }
        $this->CI->Store_meta_event_model->disconnect_oauth($store->id);
        $this->CI->load->library('channel_events');
        $this->CI->channel_events->clear_meta_config_cache($store->id);
    }

    public function disconnect_capi($store)
    {
        $this->CI->Store_meta_event_model->disconnect_capi($store->id);
        $this->CI->load->library('channel_events');
        $this->CI->channel_events->clear_meta_config_cache($store->id);
    }

    public function test_connection($store)
    {
        $cfg = $this->CI->Store_meta_event_model->config($store->id);
        if (empty($cfg['ready'])) {
            $this->lastError = 'Conversions API is not connected. Save a Pixel / Dataset ID and Events Manager CAPI access token first.';
            return array('ok' => false, 'error' => $this->lastError);
        }
        $this->CI->load->library('channel_events');
        $result = $this->CI->channel_events->test_event($store);
        $ok = !empty($result['ok']);
        $error = $ok ? '' : (isset($result['error']) ? $result['error'] : 'Connection test failed.');
        $payload = array(
            'last_test_at' => date('Y-m-d H:i:s'),
            'last_test_ok' => $ok ? 1 : 0,
            'last_error' => $ok ? '' : $error,
        );
        if ($ok) {
            $payload['extra'] = array('capi_verified' => true);
        }
        $this->CI->Store_meta_event_model->save_connection($store->id, $payload);
        $this->lastError = $error;
        return $result;
    }
}
