<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Paypal {

    protected $CI;
    protected $mode = 'sandbox';
    protected $clientId = '';
    protected $secret = '';
    protected $accessToken = '';
    protected $lastError = '';

    public function __construct()
    {
        $this->CI =& get_instance();
        $this->boot_credentials();
    }

    public function boot_credentials()
    {
        $this->mode = platform_setting('paypal_mode', 'sandbox') === 'live' ? 'live' : 'sandbox';
        $app = platform_setting('paypal_app', 'default');

        if ($this->mode === 'live') {
            $this->clientId = platform_setting('paypal_live_client_id', '');
            $this->secret = platform_setting('paypal_live_secret', '');
            return;
        }

        if ($app === 'nexa') {
            $this->clientId = platform_setting('paypal_nexa_sandbox_client_id', '');
            $this->secret = platform_setting('paypal_nexa_sandbox_secret', '');
        } else {
            $this->clientId = platform_setting('paypal_sandbox_client_id', '');
            $this->secret = platform_setting('paypal_sandbox_secret', '');
        }
    }

    public function client_id()
    {
        return $this->clientId;
    }

    public function mode()
    {
        return $this->mode;
    }

    public function currency($store = null)
    {
        if ($store) {
            $code = store_currency($store);
            if ($code !== '') {
                return $code;
            }
        }
        $fallback = strtoupper(trim(platform_setting('paypal_currency', '')));
        if ($fallback !== '') {
            return $fallback;
        }
        return platform_currency();
    }

    public function last_error()
    {
        return $this->lastError;
    }

    public function base_url()
    {
        return $this->mode === 'live'
            ? 'https://api-m.paypal.com'
            : 'https://api-m.sandbox.paypal.com';
    }

    public function get_access_token()
    {
        if ($this->accessToken) {
            return $this->accessToken;
        }
        if ($this->clientId === '' || $this->secret === '') {
            $this->lastError = 'PayPal credentials are missing.';
            return '';
        }

        $ch = curl_init($this->base_url() . '/v1/oauth2/token');
        curl_setopt_array($ch, array(
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_USERPWD => $this->clientId . ':' . $this->secret,
            CURLOPT_POSTFIELDS => 'grant_type=client_credentials',
            CURLOPT_HTTPHEADER => array('Accept: application/json', 'Accept-Language: en_US'),
        ));
        $raw = curl_exec($ch);
        $code = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        $json = json_decode($raw, true);
        if ($code >= 200 && $code < 300 && !empty($json['access_token'])) {
            $this->accessToken = $json['access_token'];
            return $this->accessToken;
        }
        $this->lastError = isset($json['error_description']) ? $json['error_description'] : 'Unable to authenticate with PayPal.';
        return '';
    }

    public function create_order($amount, $currency, $meta = array())
    {
        $payload = array(
            'intent' => 'CAPTURE',
            'purchase_units' => array(
                array(
                    'reference_id' => isset($meta['order_no']) ? $meta['order_no'] : uniqid('ord_'),
                    'description' => isset($meta['description']) ? $meta['description'] : 'Store order',
                    'amount' => array(
                        'currency_code' => $currency,
                        'value' => number_format((float) $amount, 2, '.', ''),
                    ),
                ),
            ),
            'application_context' => array(
                'shipping_preference' => 'NO_SHIPPING',
                'user_action' => 'PAY_NOW',
                'return_url' => isset($meta['return_url']) ? $meta['return_url'] : '',
                'cancel_url' => isset($meta['cancel_url']) ? $meta['cancel_url'] : '',
            ),
        );
        return $this->api('POST', '/v2/checkout/orders', $payload);
    }

    public function capture_order($orderId)
    {
        return $this->api('POST', '/v2/checkout/orders/' . rawurlencode($orderId) . '/capture', new stdClass());
    }

    public function pay_with_card($amount, $currency, $card, $meta = array())
    {
        $payload = array(
            'intent' => 'CAPTURE',
            'purchase_units' => array(
                array(
                    'reference_id' => isset($meta['order_no']) ? $meta['order_no'] : uniqid('ord_'),
                    'description' => isset($meta['description']) ? $meta['description'] : 'Card payment',
                    'amount' => array(
                        'currency_code' => $currency,
                        'value' => number_format((float) $amount, 2, '.', ''),
                    ),
                ),
            ),
            'payment_source' => array(
                'card' => array(
                    'number' => preg_replace('/\D+/', '', $card['number']),
                    'expiry' => sprintf('%04d-%02d', (int) $card['exp_year'], (int) $card['exp_month']),
                    'security_code' => (string) $card['cvv'],
                    'name' => isset($card['name']) ? $card['name'] : 'Card Holder',
                ),
            ),
        );
        return $this->api('POST', '/v2/checkout/orders', $payload, array('PayPal-Request-Id: ' . uniqid('card_', true)));
    }

    public function approve_link($order)
    {
        if (empty($order['links']) || !is_array($order['links'])) {
            return '';
        }
        foreach ($order['links'] as $link) {
            if (!empty($link['rel']) && $link['rel'] === 'approve' && !empty($link['href'])) {
                return $link['href'];
            }
        }
        return '';
    }

    protected function api($method, $path, $body = null, $extraHeaders = array())
    {
        $token = $this->get_access_token();
        if ($token === '') {
            return null;
        }

        $headers = array_merge(array(
            'Content-Type: application/json',
            'Authorization: Bearer ' . $token,
        ), $extraHeaders);

        $ch = curl_init($this->base_url() . $path);
        $opts = array(
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_HTTPHEADER => $headers,
        );
        if ($body !== null) {
            $opts[CURLOPT_POSTFIELDS] = json_encode($body);
        }
        curl_setopt_array($ch, $opts);
        $raw = curl_exec($ch);
        $code = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        $json = json_decode($raw, true);
        if ($code >= 200 && $code < 300) {
            return is_array($json) ? $json : array();
        }

        $message = 'PayPal request failed.';
        if (!empty($json['message'])) {
            $message = $json['message'];
        } elseif (!empty($json['details'][0]['description'])) {
            $message = $json['details'][0]['description'];
        } elseif (!empty($json['error_description'])) {
            $message = $json['error_description'];
        }
        $this->lastError = $message;
        return null;
    }
}
