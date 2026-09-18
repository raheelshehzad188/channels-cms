<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Channel_events {

    protected $CI;

    public function __construct()
    {
        $this->CI =& get_instance();
        $this->CI->load->model('Store_channel_model');
    }

    public function tracking_payload($store, $context = array())
    {
        $meta = $this->CI->Store_channel_model->get($store->id, 'meta');
        $tiktok = $this->CI->Store_channel_model->get($store->id, 'tiktok');
        return array(
            'meta_pixel_id' => ($meta && $meta->status === 'connected') ? (string) $meta->pixel_id : '',
            'tiktok_pixel_id' => ($tiktok && $tiktok->status === 'connected') ? (string) $tiktok->pixel_id : '',
            'event' => isset($context['event']) ? $context['event'] : '',
            'event_id' => isset($context['event_id']) ? $context['event_id'] : '',
            'currency' => isset($context['currency']) ? $context['currency'] : store_currency($store),
            'value' => isset($context['value']) ? (float) $context['value'] : 0,
            'content_ids' => isset($context['content_ids']) ? $context['content_ids'] : array(),
            'content_name' => isset($context['content_name']) ? $context['content_name'] : '',
            'content_type' => 'product',
            'order_id' => isset($context['order_id']) ? $context['order_id'] : '',
        );
    }

    public function view_content($store, $product)
    {
        $eventId = $this->new_event_id('view');
        $context = array(
            'event' => 'ViewContent',
            'event_id' => $eventId,
            'currency' => store_currency($store),
            'value' => (float) $product->price,
            'content_ids' => array((string) $product->id),
            'content_name' => $product->name,
        );
        $this->queue_meta($store, 'ViewContent', $context, $this->user_from_customer());
        $this->queue_tiktok($store, 'ViewContent', $context, $this->user_from_customer());
        return $this->tracking_payload($store, $context);
    }

    public function add_to_cart($store, $product, $qty = 1)
    {
        $eventId = $this->new_event_id('cart');
        $context = array(
            'event' => 'AddToCart',
            'event_id' => $eventId,
            'currency' => store_currency($store),
            'value' => (float) $product->price * max(1, (int) $qty),
            'content_ids' => array((string) $product->id),
            'content_name' => $product->name,
        );
        $this->queue_meta($store, 'AddToCart', $context, $this->user_from_customer());
        $this->queue_tiktok($store, 'AddToCart', $context, $this->user_from_customer());
        $_SESSION['channel_pixel_event'][(int) $store->id] = $context;
        return $context;
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
        foreach ($items as $item) {
            $ids[] = (string) $item->product_id;
        }
        $eventId = 'purchase_' . $order->order_no;
        $context = array(
            'event' => 'Purchase',
            'event_id' => $eventId,
            'currency' => $order->currency,
            'value' => (float) $order->total,
            'content_ids' => $ids,
            'content_name' => 'Order ' . $order->order_no,
            'order_id' => $order->order_no,
        );
        $user = array(
            'email' => isset($order->customer_email) ? $order->customer_email : '',
            'phone' => isset($order->customer_phone) ? $order->customer_phone : '',
        );
        $this->queue_meta($store, 'Purchase', $context, $user);
        $this->queue_tiktok($store, 'CompletePayment', $context, $user);
        return $this->tracking_payload($store, $context);
    }

    protected function queue_meta($store, $eventName, $context, $user)
    {
        $conn = $this->CI->Store_channel_model->get($store->id, 'meta');
        if (!$conn || $conn->status !== 'connected' || $conn->pixel_id === '' || $conn->access_token === '') {
            return;
        }
        $event = array(
            'event_name' => $eventName,
            'event_time' => time(),
            'event_id' => $context['event_id'],
            'action_source' => 'website',
            'event_source_url' => current_url(),
            'user_data' => $this->meta_user($user),
            'custom_data' => array(
                'currency' => $context['currency'],
                'value' => $context['value'],
                'content_ids' => $context['content_ids'],
                'content_type' => 'product',
                'content_name' => $context['content_name'],
            ),
        );
        if (!empty($context['order_id'])) {
            $event['custom_data']['order_id'] = $context['order_id'];
        }
        $this->CI->Store_channel_model->queue_event($store->id, 'meta', $event, $eventName !== 'ViewContent');
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

    protected function new_event_id($prefix)
    {
        return $prefix . '_' . bin2hex($this->random_bytes(8));
    }

    protected function random_bytes($len)
    {
        if (function_exists('random_bytes')) {
            return random_bytes($len);
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
}
