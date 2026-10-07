<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Channel_events {

    protected $CI;
    protected $metaConfigCache = array();
    protected $pageViewSent = array();

    public function __construct()
    {
        $this->CI =& get_instance();
        $this->CI->load->model('Store_channel_model');
    }

    public function tracking_payload($store, $context = array())
    {
        $tiktok = $this->CI->Store_channel_model->get($store->id, 'tiktok');
        $cfg = $this->meta_config($store);
        $pixel = '';
        if (!empty($cfg['enabled']) && !empty($cfg['pixel_id'])) {
            $pixel = (string) $cfg['pixel_id'];
        }
        return array(
            'meta_pixel_id' => $pixel,
            'tiktok_pixel_id' => ($tiktok && $tiktok->status === 'connected') ? (string) $tiktok->pixel_id : '',
            'event' => isset($context['event']) ? $context['event'] : '',
            'event_id' => isset($context['event_id']) ? $context['event_id'] : '',
            'currency' => isset($context['currency']) ? $context['currency'] : store_currency($store),
            'value' => isset($context['value']) ? (float) $context['value'] : 0,
            'content_ids' => isset($context['content_ids']) ? $context['content_ids'] : array(),
            'content_name' => isset($context['content_name']) ? $context['content_name'] : '',
            'content_type' => 'product',
            'contents' => isset($context['contents']) && is_array($context['contents']) ? $context['contents'] : array(),
            'num_items' => isset($context['num_items']) ? (int) $context['num_items'] : 0,
            'order_id' => isset($context['order_id']) ? $context['order_id'] : '',
        );
    }

    public function attach_page_view($store, $tracking = array())
    {
        if (!is_array($tracking)) {
            $tracking = array();
        }
        $tracking = array_merge($this->tracking_payload($store), $tracking);
        $cfg = $this->meta_config($store);
        if (empty($cfg['enabled']) || empty($cfg['pixel_id'])) {
            return $tracking;
        }
        $storeId = (int) $store->id;
        if (isset($this->pageViewSent[$storeId])) {
            $tracking['pageview_event_id'] = $this->pageViewSent[$storeId];
            return $tracking;
        }
        $method = strtoupper((string) $this->CI->input->server('REQUEST_METHOD'));
        $eventId = $this->new_event_id('pv');
        $this->pageViewSent[$storeId] = $eventId;
        $tracking['pageview_event_id'] = $eventId;
        if (!empty($cfg['ready']) && $method !== 'POST') {
            $this->send_meta($store, 'PageView', array(
                'event' => 'PageView',
                'event_id' => $eventId,
                'currency' => store_currency($store),
                'value' => 0,
                'content_ids' => array(),
                'content_name' => '',
                'event_source_url' => current_url(),
            ), $this->user_from_customer());
        }
        return $tracking;
    }

    public function view_content($store, $product)
    {
        $eventId = $this->new_event_id('view');
        $contents = $this->contents_from_products(array($product), 1);
        $context = array(
            'event' => 'ViewContent',
            'event_id' => $eventId,
            'currency' => store_currency($store),
            'value' => (float) $product->price,
            'content_ids' => array((string) $product->id),
            'content_name' => $product->name,
            'contents' => $contents,
            'num_items' => 1,
        );
        $this->send_meta($store, 'ViewContent', $context, $this->user_from_customer());
        $this->queue_tiktok($store, 'ViewContent', $context, $this->user_from_customer());
        return $this->tracking_payload($store, $context);
    }

    public function add_to_cart($store, $product, $qty = 1)
    {
        $qty = max(1, (int) $qty);
        $eventId = $this->new_event_id('cart');
        $contents = $this->contents_from_products(array($product), $qty);
        $context = array(
            'event' => 'AddToCart',
            'event_id' => $eventId,
            'currency' => store_currency($store),
            'value' => (float) $product->price * $qty,
            'content_ids' => array((string) $product->id),
            'content_name' => $product->name,
            'contents' => $contents,
            'num_items' => $qty,
        );
        $this->send_meta($store, 'AddToCart', $context, $this->user_from_customer());
        $this->queue_tiktok($store, 'AddToCart', $context, $this->user_from_customer());
        $_SESSION['channel_pixel_event'][(int) $store->id] = $context;
        return $context;
    }

    public function initiate_checkout($store, $items, $value = 0)
    {
        $ids = array();
        $name = '';
        $numItems = 0;
        foreach ($items as $item) {
            $ids[] = (string) $item->id;
            $numItems += isset($item->qty) ? max(1, (int) $item->qty) : 1;
            if ($name === '' && !empty($item->name)) {
                $name = $item->name;
            }
        }
        $eventId = $this->new_event_id('checkout');
        $contents = $this->contents_from_products($items, 0);
        $context = array(
            'event' => 'InitiateCheckout',
            'event_id' => $eventId,
            'currency' => store_currency($store),
            'value' => (float) $value,
            'content_ids' => $ids,
            'content_name' => $name,
            'contents' => $contents,
            'num_items' => $numItems,
        );
        $this->send_meta($store, 'InitiateCheckout', $context, $this->user_from_customer());
        $this->queue_tiktok($store, 'InitiateCheckout', $context, $this->user_from_customer());
        return $this->tracking_payload($store, $context);
    }

    public function consume_browser_event($store)
    {
        $storeId = (int) $store->id;
        if (empty($_SESSION['channel_pixel_event'][$storeId]) || !is_array($_SESSION['channel_pixel_event'][$storeId])) {
            return $this->tracking_payload($store);
        }
        $context = $_SESSION['channel_pixel_event'][$storeId];
        unset($_SESSION['channel_pixel_event'][$storeId]);
        return $this->tracking_payload($store, $context);
    }

    public function purchase($store, $order, $items)
    {
        $ids = array();
        $numItems = 0;
        foreach ($items as $item) {
            $ids[] = (string) $item->product_id;
            $numItems += isset($item->qty) ? max(1, (int) $item->qty) : 1;
        }
        $eventId = 'purchase_' . $order->order_no;
        $currency = !empty($order->currency) ? (string) $order->currency : store_currency($store);
        $context = array(
            'event' => 'Purchase',
            'event_id' => $eventId,
            'currency' => $currency,
            'value' => (float) $order->total,
            'content_ids' => $ids,
            'content_name' => 'Order ' . $order->order_no,
            'contents' => $this->contents_from_order_items($items),
            'num_items' => $numItems,
            'order_id' => $order->order_no,
            'order_row_id' => (int) $order->id,
        );
        $user = array(
            'email' => isset($order->customer_email) ? $order->customer_email : '',
            'phone' => isset($order->customer_phone) ? $order->customer_phone : '',
            'name' => isset($order->customer_name) ? $order->customer_name : '',
            'external_id' => !empty($order->customer_id) ? (string) $order->customer_id : '',
        );
        $this->send_meta($store, 'Purchase', $context, $user);
        $this->queue_tiktok($store, 'CompletePayment', $context, $user);
        return $this->tracking_payload($store, $context);
    }

    public function test_event($store)
    {
        $cfg = $this->meta_config($store);
        if (empty($cfg['ready'])) {
            return array('ok' => false, 'error' => 'Save a Pixel / Dataset ID and Events Manager CAPI access token first.');
        }
        $eventId = $this->new_event_id('test');
        $context = array(
            'event' => 'PageView',
            'event_id' => $eventId,
            'currency' => store_currency($store),
            'value' => 0,
            'content_ids' => array(),
            'content_name' => 'Meta Event API test',
        );
        return $this->send_meta($store, 'PageView', $context, $this->user_from_customer());
    }

    protected function send_meta($store, $eventName, $context, $user)
    {
        $this->CI->load->library('meta_tracking');
        if ($eventName === 'PageView') {
            return $this->CI->meta_tracking->trackPageView($store, $context, $user);
        }
        if ($eventName === 'ViewContent') {
            return $this->CI->meta_tracking->trackViewContent($store, $context, $user);
        }
        if ($eventName === 'AddToCart') {
            return $this->CI->meta_tracking->trackAddToCart($store, $context, $user);
        }
        if ($eventName === 'InitiateCheckout') {
            return $this->CI->meta_tracking->trackInitiateCheckout($store, $context, $user);
        }
        if ($eventName === 'Purchase') {
            return $this->CI->meta_tracking->trackPurchase($store, $context, $user);
        }
        return $this->CI->meta_tracking->send($store, $eventName, $context, $user);
    }

    protected function contents_from_products($items, $forcedQty = 0)
    {
        $contents = array();
        foreach ($items as $item) {
            if (!is_object($item) || empty($item->id)) {
                continue;
            }
            $qty = $forcedQty > 0 ? (int) $forcedQty : (isset($item->qty) ? max(1, (int) $item->qty) : 1);
            $price = isset($item->price) ? (float) $item->price : 0;
            $contents[] = array(
                'id' => (string) $item->id,
                'quantity' => $qty,
                'item_price' => round($price, 2),
            );
        }
        return $contents;
    }

    protected function contents_from_order_items($items)
    {
        $contents = array();
        foreach ($items as $item) {
            if (!is_object($item)) {
                continue;
            }
            $id = !empty($item->product_id) ? (string) $item->product_id : '';
            if ($id === '') {
                continue;
            }
            $qty = isset($item->qty) ? max(1, (int) $item->qty) : 1;
            if (isset($item->unit_price)) {
                $price = (float) $item->unit_price;
            } elseif (isset($item->line_total)) {
                $price = (float) $item->line_total / $qty;
            } else {
                $price = 0;
            }
            $contents[] = array(
                'id' => $id,
                'quantity' => $qty,
                'item_price' => round($price, 2),
            );
        }
        return $contents;
    }

    protected function queue_tiktok($store, $eventName, $context, $user)
    {
        $conn = $this->CI->Store_channel_model->get($store->id, 'tiktok');
        if (!$conn || $conn->status !== 'connected' || $conn->pixel_id === '' || $conn->access_token === '') {
            return;
        }
        $event = array(
            'event' => $eventName,
            'event_time' => time(),
            'event_id' => $context['event_id'],
            'user' => $this->tiktok_user($user),
            'page' => array('url' => current_url()),
            'properties' => array(
                'currency' => $context['currency'],
                'value' => $context['value'],
                'content_ids' => $context['content_ids'],
                'content_type' => 'product',
                'content_name' => $context['content_name'],
            ),
        );
        $this->CI->Store_channel_model->queue_event($store->id, 'tiktok', $event, $eventName !== 'ViewContent');
    }

    public function clear_meta_config_cache($storeId = null)
    {
        if ($storeId === null) {
            $this->metaConfigCache = array();
            return;
        }
        unset($this->metaConfigCache[(int) $storeId]);
    }

    protected function meta_config($store)
    {
        $storeId = (int) $store->id;
        if (!isset($this->metaConfigCache[$storeId])) {
            $this->CI->load->model('Store_meta_event_model');
            $this->metaConfigCache[$storeId] = $this->CI->Store_meta_event_model->config($storeId);
        }
        return $this->metaConfigCache[$storeId];
    }

    protected function new_event_id($prefix)
    {
        return $prefix . '_' . bin2hex($this->byte_string(8));
    }

    protected function byte_string($len)
    {
        if (function_exists('\\random_bytes')) {
            return \random_bytes($len);
        }
        return openssl_random_pseudo_bytes($len);
    }

    protected function meta_user($user)
    {
        $out = array(
            'client_ip_address' => $this->CI->input->ip_address(),
            'client_user_agent' => (string) $this->CI->input->user_agent(),
        );
        $email = isset($user['email']) ? strtolower(trim($user['email'])) : '';
        if ($email !== '') {
            $out['em'] = array(hash('sha256', $email));
        }
        $phone = isset($user['phone']) ? preg_replace('/\D+/', '', (string) $user['phone']) : '';
        if ($phone !== '') {
            $out['ph'] = array(hash('sha256', $phone));
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

    protected function tiktok_user($user)
    {
        $out = array(
            'ip' => $this->CI->input->ip_address(),
            'user_agent' => (string) $this->CI->input->user_agent(),
        );
        $email = isset($user['email']) ? strtolower(trim($user['email'])) : '';
        if ($email !== '') {
            $out['email'] = hash('sha256', $email);
        }
        $phone = isset($user['phone']) ? preg_replace('/\D+/', '', (string) $user['phone']) : '';
        if ($phone !== '') {
            $out['phone'] = hash('sha256', $phone);
        }
        return $out;
    }

    protected function user_from_customer()
    {
        $customer = function_exists('storefront_customer') ? storefront_customer() : null;
        if (!$customer) {
            return array();
        }
        return array(
            'email' => isset($customer->email) ? $customer->email : '',
            'phone' => isset($customer->phone) ? $customer->phone : '',
        );
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
        return 'fb.1.' . time() . '.' . $fbclid;
    }
}
