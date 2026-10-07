<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Paypal {

    protected $CI;
    protected $mode = 'sandbox';
    protected $clientId = '';
    protected $secret = '';
    protected $secretAlt = '';
    protected $accessToken = '';
    protected $lastError = '';
    protected $lastLogFile = '';

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
            $this->secretAlt = platform_setting('paypal_live_secret_2', '');
            return;
        }

        $this->boot_sandbox_credentials($app);
    }

    /**
     * Request-scoped override for QA buyer checkout. Does not change platform_settings.
     */
    public function force_sandbox()
    {
        $this->mode = 'sandbox';
        $this->accessToken = '';
        $this->lastError = '';
        $this->secretAlt = '';
        $this->boot_sandbox_credentials(platform_setting('paypal_app', 'default'));
    }

    protected function boot_sandbox_credentials($app = 'default')
    {
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

    public function last_log_file()
    {
        return $this->lastLogFile;
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
        if ($this->clientId === '' || ($this->secret === '' && $this->secretAlt === '')) {
            $this->lastError = 'PayPal credentials are missing.';
            $this->write_log('oauth', array(
                'error' => $this->lastError,
                'request' => array('client_id' => $this->mask_id($this->clientId)),
                'response' => null,
            ));
            return '';
        }

        $secrets = array($this->secret);
        if ($this->secretAlt !== '' && $this->secretAlt !== $this->secret) {
            $secrets[] = $this->secretAlt;
        }
        $lastJson = array();
        $attempt = 0;
        foreach ($secrets as $secret) {
            if ($secret === '') {
                continue;
            }
            $attempt++;
            $url = $this->base_url() . '/v1/oauth2/token';
            $ch = curl_init($url);
            curl_setopt_array($ch, array(
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_POST => true,
                CURLOPT_USERPWD => $this->clientId . ':' . $secret,
                CURLOPT_POSTFIELDS => 'grant_type=client_credentials',
                CURLOPT_HTTPHEADER => array('Accept: application/json', 'Accept-Language: en_US'),
            ));
            $raw = curl_exec($ch);
            $code = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $curlErr = curl_error($ch);
            curl_close($ch);
            $json = json_decode($raw, true);
            $lastJson = is_array($json) ? $json : array();
            $this->write_log('oauth', array(
                'url' => $url,
                'secret_attempt' => $attempt,
                'http' => $code,
                'curl_error' => $curlErr,
                'request' => array('grant_type' => 'client_credentials', 'client_id' => $this->mask_id($this->clientId)),
                'response' => $this->redact_for_log($lastJson ?: $raw),
            ));
            if ($code >= 200 && $code < 300 && !empty($json['access_token'])) {
                $this->accessToken = $json['access_token'];
                $this->secret = $secret;
                return $this->accessToken;
            }
        }
        $this->lastError = isset($lastJson['error_description']) ? $lastJson['error_description'] : 'Unable to authenticate with PayPal.';
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
        return $this->api('POST', '/v2/checkout/orders', $payload, array(), 'wallet');
    }

    public function capture_order($orderId)
    {
        return $this->api('POST', '/v2/checkout/orders/' . rawurlencode($orderId) . '/capture', new stdClass(), array(), 'capture');
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
        return $this->api('POST', '/v2/checkout/orders', $payload, array('PayPal-Request-Id: ' . uniqid('card_', true)), 'card');
    }

    /**
     * Nested capture from a PayPal order body (CAPTURE intent).
     * Order status COMPLETED is not enough — the capture can still be DECLINED.
     */
    public function capture_summary($order)
    {
        $summary = array(
            'order_id' => '',
            'order_status' => '',
            'capture_id' => '',
            'capture_status' => null,
            'processor_response_code' => '',
            'amount' => '',
            'currency' => '',
            'intent' => '',
        );
        if (!is_array($order) || !$order) {
            return $summary;
        }
        $summary['order_id'] = isset($order['id']) ? trim((string) $order['id']) : '';
        $summary['order_status'] = isset($order['status']) ? strtoupper(trim((string) $order['status'])) : '';
        $summary['intent'] = isset($order['intent']) ? strtoupper(trim((string) $order['intent'])) : '';

        $capture = null;
        if (!empty($order['purchase_units'][0]['payments']['captures'][0]) && is_array($order['purchase_units'][0]['payments']['captures'][0])) {
            $capture = $order['purchase_units'][0]['payments']['captures'][0];
        }
        if ($capture) {
            $summary['capture_id'] = isset($capture['id']) ? trim((string) $capture['id']) : '';
            $summary['capture_status'] = isset($capture['status']) ? strtoupper(trim((string) $capture['status'])) : null;
            if (!empty($capture['processor_response']['response_code'])) {
                $summary['processor_response_code'] = trim((string) $capture['processor_response']['response_code']);
            }
            if (!empty($capture['amount']) && is_array($capture['amount'])) {
                $summary['amount'] = isset($capture['amount']['value']) ? (string) $capture['amount']['value'] : '';
                $summary['currency'] = isset($capture['amount']['currency_code']) ? (string) $capture['amount']['currency_code'] : '';
            }
        }
        if ($summary['amount'] === '' && !empty($order['purchase_units'][0]['amount']) && is_array($order['purchase_units'][0]['amount'])) {
            $unit = $order['purchase_units'][0]['amount'];
            $summary['amount'] = isset($unit['value']) ? (string) $unit['value'] : '';
            $summary['currency'] = isset($unit['currency_code']) ? (string) $unit['currency_code'] : '';
        }
        return $summary;
    }

    public function capture_is_completed($order)
    {
        $summary = $this->capture_summary($order);
        return $summary['capture_status'] === 'COMPLETED';
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

    public static function log_dir()
    {
        return APPPATH . 'logs/paypal';
    }

    public static function log_files()
    {
        $dir = self::log_dir();
        if (!is_dir($dir)) {
            return array();
        }
        $files = glob($dir . '/paypal-*.log');
        if (!$files) {
            return array();
        }
        rsort($files);
        return $files;
    }

    public function log_event($tag, $entry)
    {
        $this->write_log($tag, is_array($entry) ? $entry : array('message' => $entry));
    }

    protected function api($method, $path, $body = null, $extraHeaders = array(), $tag = 'api')
    {
        $token = $this->get_access_token();
        if ($token === '') {
            $this->write_log($tag, array(
                'url' => $this->base_url() . $path,
                'method' => $method,
                'http' => 0,
                'error' => $this->lastError ?: 'PayPal credentials are missing.',
                'request' => $this->redact_for_log($body),
                'response' => null,
            ));
            return null;
        }

        $headers = array_merge(array(
            'Content-Type: application/json',
            'Authorization: Bearer ' . $token,
        ), $extraHeaders);

        $url = $this->base_url() . $path;
        $ch = curl_init($url);
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
        $curlErr = curl_error($ch);
        curl_close($ch);
        $json = json_decode($raw, true);
        $ok = $code >= 200 && $code < 300;
        $this->write_log($tag, array(
            'url' => $url,
            'method' => $method,
            'http' => $code,
            'curl_error' => $curlErr,
            'request' => $this->redact_for_log($body),
            'response' => $this->redact_for_log(is_array($json) ? $json : $raw),
        ));
        if ($ok) {
            return is_array($json) ? $json : array();
        }

        $message = 'PayPal request failed.';
        if (!empty($json['message'])) {
            $message = $json['message'];
        } elseif (!empty($json['details'][0]['description'])) {
            $message = $json['details'][0]['description'];
        } elseif (!empty($json['error_description'])) {
            $message = $json['error_description'];
        } elseif ($curlErr !== '') {
            $message = $curlErr;
        }
        $this->lastError = $message;
        return null;
    }

    protected function mask_id($value)
    {
        $value = trim((string) $value);
        $len = strlen($value);
        if ($len <= 8) {
            return $value === '' ? '' : '****';
        }
        return substr($value, 0, 6) . '…' . substr($value, -4);
    }

    protected function redact_for_log($data)
    {
        if ($data === null) {
            return null;
        }
        if (is_object($data)) {
            $data = json_decode(json_encode($data), true);
        }
        if (is_string($data)) {
            $decoded = json_decode($data, true);
            if (is_array($decoded)) {
                return $this->redact_for_log($decoded);
            }
            return $data;
        }
        if (!is_array($data)) {
            return $data;
        }
        $out = array();
        foreach ($data as $key => $value) {
            $lk = strtolower((string) $key);
            if (in_array($lk, array('number', 'card_number', 'pan', 'account_number'), true)) {
                $digits = preg_replace('/\D+/', '', (string) $value);
                $out[$key] = strlen($digits) >= 4
                    ? str_repeat('*', max(0, strlen($digits) - 4)) . substr($digits, -4)
                    : '****';
                continue;
            }
            if (in_array($lk, array('security_code', 'cvv', 'cvc', 'cvv2', 'access_token', 'authorization', 'secret', 'client_secret'), true)) {
                $out[$key] = '***';
                continue;
            }
            $out[$key] = $this->redact_for_log($value);
        }
        return $out;
    }

    protected function write_log($tag, $entry)
    {
        $dir = self::log_dir();
        if (!is_dir($dir)) {
            @mkdir($dir, 0750, true);
        }
        $file = $dir . '/paypal-' . date('Y-m-d') . '.log';
        $this->lastLogFile = $file;
        $block = str_repeat('=', 72) . "\n"
            . date('Y-m-d H:i:s') . ' [' . strtoupper((string) $tag) . '] mode=' . $this->mode . "\n"
            . json_encode($entry, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) . "\n\n";
        @file_put_contents($file, $block, FILE_APPEND | LOCK_EX);
    }
}
