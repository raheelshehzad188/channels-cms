<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Meta_tracking {

    protected $CI;

    public function __construct()
    {
        $this->CI =& get_instance();
        $this->CI->load->model('Store_meta_event_model');
        $this->CI->load->library('meta_commerce');
    }

    public function trackPageView($store, $context = array(), $user = array())
    {
        return $this->send($store, 'PageView', $context, $user);
    }

    public function trackViewContent($store, $context = array(), $user = array())
    {
        return $this->send($store, 'ViewContent', $context, $user);
    }

    public function trackAddToCart($store, $context = array(), $user = array())
    {
        return $this->send($store, 'AddToCart', $context, $user);
    }

    public function trackInitiateCheckout($store, $context = array(), $user = array())
    {
        return $this->send($store, 'InitiateCheckout', $context, $user);
    }

    public function trackPurchase($store, $context = array(), $user = array())
    {
        return $this->send($store, 'Purchase', $context, $user);
    }

    public function send($store, $eventName, $context, $user)
    {
        $storeId = (int) $store->id;
        $cfg = $this->CI->Store_meta_event_model->config($storeId);
        if (empty($cfg['ready']) || $cfg['pixel_id'] === '' || $cfg['token'] === '') {
            return array('ok' => false, 'error' => 'Meta Conversions API is not enabled for this store.');
        }

        $eventId = isset($context['event_id']) ? (string) $context['event_id'] : '';
        $orderId = isset($context['order_row_id']) ? (int) $context['order_row_id'] : 0;
        if ($eventName === 'Purchase' && $eventId !== '' && $this->CI->Store_meta_event_model->purchase_already_sent($storeId, $orderId, $eventId)) {
            return array(
                'ok' => true,
                'skipped' => true,
                'error' => '',
                'event_name' => $eventName,
                'event_id' => $eventId,
            );
        }

        $event = array(
            'event_name' => $eventName,
            'event_time' => time(),
            'event_id' => $eventId,
            'action_source' => 'website',
            'event_source_url' => !empty($context['event_source_url']) ? (string) $context['event_source_url'] : current_url(),
            'user_data' => $this->user_data($user),
        );
        $custom = $this->custom_data($context);
        if (!empty($custom)) {
            $event['custom_data'] = $custom;
        }

        $ok = $this->CI->meta_commerce->send_capi(
            $cfg['pixel_id'],
            $cfg['token'],
            array($event),
            isset($cfg['test_event_code']) ? $cfg['test_event_code'] : ''
        );
        $error = $ok ? '' : $this->CI->meta_commerce->last_error();
        $result = array(
            'ok' => $ok,
            'skipped' => false,
            'error' => $error,
            'event_name' => $eventName,
            'event_id' => $eventId,
            'http_code' => (int) $this->CI->meta_commerce->lastHttp,
            'events_received' => (int) $this->CI->meta_commerce->lastEventsReceived,
            'payload' => $event,
            'response' => $this->CI->meta_commerce->lastResponse,
        );
        $this->CI->Store_meta_event_model->log($storeId, $result);
        if ($ok && $eventName === 'Purchase' && $orderId > 0) {
            $this->CI->Store_meta_event_model->mark_purchase_sent($storeId, $orderId, $eventId);
        }
        return $result;
    }

    protected function custom_data($context)
    {
        $custom = array();
        if (!empty($context['currency'])) {
            $custom['currency'] = (string) $context['currency'];
        }
        if (isset($context['value']) && (float) $context['value'] > 0) {
            $custom['value'] = (float) $context['value'];
        }
        if (!empty($context['content_ids']) && is_array($context['content_ids'])) {
            $custom['content_ids'] = array_values($context['content_ids']);
            $custom['content_type'] = 'product';
        }
        if (!empty($context['contents']) && is_array($context['contents'])) {
            $custom['contents'] = array_values($context['contents']);
            $custom['content_type'] = 'product';
        }
        if (!empty($context['content_name'])) {
            $custom['content_name'] = (string) $context['content_name'];
        }
        if (!empty($context['order_id'])) {
            $custom['order_id'] = (string) $context['order_id'];
        }
        if (!empty($context['num_items'])) {
            $custom['num_items'] = (int) $context['num_items'];
        }
        return $custom;
    }

    protected function user_data($user)
    {
        $out = array(
            'client_ip_address' => $this->CI->input->ip_address(),
            'client_user_agent' => substr((string) $this->CI->input->user_agent(), 0, 500),
        );
        $email = $this->hash_value(isset($user['email']) ? $user['email'] : '');
        if ($email !== '') {
            $out['em'] = array($email);
        }
        $phone = isset($user['phone']) ? preg_replace('/\D+/', '', (string) $user['phone']) : '';
        $phone = $this->hash_value($phone);
        if ($phone !== '') {
            $out['ph'] = array($phone);
        }
        $name = isset($user['name']) ? trim((string) $user['name']) : '';
        if ($name !== '') {
            $parts = preg_split('/\s+/', $name, 2);
            $first = $this->hash_name(isset($parts[0]) ? $parts[0] : '');
            $last = $this->hash_name(isset($parts[1]) ? $parts[1] : '');
            if ($first !== '') {
                $out['fn'] = array($first);
            }
            if ($last !== '') {
                $out['ln'] = array($last);
            }
        }
        $external = isset($user['external_id']) ? trim((string) $user['external_id']) : '';
        if ($external !== '' && $external !== '0') {
            $out['external_id'] = array(hash('sha256', $external));
        }
        $fbp = $this->cookie_value('_fbp');
        if ($fbp !== '') {
            $out['fbp'] = $fbp;
        }
        $fbc = $this->fbc_value();
        if ($fbc !== '') {
            $out['fbc'] = $fbc;
        }
        return $out;
    }

    protected function hash_value($value)
    {
        $value = strtolower(trim((string) $value));
        if ($value === '') {
            return '';
        }
        return hash('sha256', $value);
    }

    protected function hash_name($value)
    {
        $value = strtolower(trim((string) $value));
        $value = preg_replace('/[^\p{L}]/u', '', $value);
        if ($value === null || $value === '') {
            return '';
        }
        return hash('sha256', $value);
    }

    protected function cookie_value($name)
    {
        if (isset($_COOKIE[$name]) && is_string($_COOKIE[$name])) {
            return trim($_COOKIE[$name]);
        }
        return '';
    }

    protected function fbc_value()
    {
        $fbc = $this->cookie_value('_fbc');
        if ($fbc !== '') {
            return $fbc;
        }
        $fbclid = trim((string) $this->CI->input->get('fbclid'));
        if ($fbclid === '') {
            return '';
        }
        return 'fb.1.' . (int) round(microtime(true) * 1000) . '.' . $fbclid;
    }
}
