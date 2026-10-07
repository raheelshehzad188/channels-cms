<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Ec_whatsapp {

    protected $CI;
    public $lastError = '';
    public $lastResponse = '';

    public function __construct()
    {
        $this->CI =& get_instance();
        ensure_whatsapp_schema();
    }

    public function enabled()
    {
        return (string) platform_setting('whatsapp_enabled', '0') === '1';
    }

    public function normalize_phone($phone, $dialCode = '')
    {
        $digits = preg_replace('/\D+/', '', (string) $phone);
        if ($digits === '') {
            return '';
        }
        if (strpos($digits, '00') === 0) {
            $digits = substr($digits, 2);
        }
        $dial = preg_replace('/\D+/', '', (string) $dialCode);
        if ($dial !== '' && strpos($digits, '0') === 0) {
            $digits = $dial . substr($digits, 1);
        }
        return $digits;
    }

    public function send($phone, $message, $dialCode = '', $force = false)
    {
        $this->lastError = '';
        $this->lastResponse = '';
        if (!$force && !$this->enabled()) {
            $this->lastError = 'WhatsApp notifications are turned off.';
            return false;
        }

        $phone = $this->normalize_phone($phone, $dialCode);
        $message = trim((string) $message);
        if (strlen($phone) < 10 || $message === '') {
            $this->lastError = 'WhatsApp number or message is missing.';
            return false;
        }

        $url = trim((string) platform_setting('whatsapp_api_url', ''));
        $key = trim((string) platform_setting('whatsapp_api_key', ''));
        $secret = trim((string) platform_setting('whatsapp_api_secret', ''));
        $session = trim((string) platform_setting('whatsapp_session_name', ''));
        if ($url === '' || $key === '' || $secret === '' || $session === '') {
            $this->lastError = 'WhatsApp API URL, keys or session name is not configured.';
            return false;
        }

        $payload = json_encode(array(
            'session_name' => $session,
            'phone' => $phone,
            'message' => $message,
        ));

        $headers = array(
            'Content-Type: application/json',
            'Accept: application/json',
            'X-API-Key: ' . $key,
            'X-API-Secret: ' . $secret,
        );

        $ch = curl_init($url);
        curl_setopt_array($ch, array(
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $payload,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_CONNECTTIMEOUT => 12,
            CURLOPT_TIMEOUT => 25,
        ));
        $raw = curl_exec($ch);
        $code = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $err = curl_error($ch);
        curl_close($ch);

        $this->lastResponse = (string) $raw;
        if ($raw === false) {
            $this->lastError = $err !== '' ? $err : 'WhatsApp API request failed.';
            $this->log_failure($phone, $message);
            return false;
        }
        if ($code < 200 || $code >= 300) {
            $this->lastError = 'WhatsApp API returned HTTP ' . $code;
            $json = json_decode($this->lastResponse, true);
            if (is_array($json) && !empty($json['message'])) {
                $this->lastError .= ': ' . $json['message'];
            }
            $this->log_failure($phone, $message);
            return false;
        }
        $json = json_decode($this->lastResponse, true);
        if (is_array($json) && array_key_exists('success', $json) && !$json['success']) {
            $this->lastError = !empty($json['message']) ? $json['message'] : 'WhatsApp API did not send the message.';
            $this->log_failure($phone, $message);
            return false;
        }
        return true;
    }

    public function send_many($phones, $message, $dialCode = '', $force = false)
    {
        $sent = 0;
        $unique = array();
        foreach ((array) $phones as $phone) {
            $normalized = $this->normalize_phone($phone, $dialCode);
            if ($normalized === '' || isset($unique[$normalized])) {
                continue;
            }
            $unique[$normalized] = true;
            if ($this->send($normalized, $message, '', $force)) {
                $sent++;
            }
        }
        return $sent;
    }

    public function notify_order($order, $items = array())
    {
        if (!$order) {
            return 0;
        }

        $status = isset($order->status) ? $order->status : '';
        $storeName = !empty($order->store_name) ? $order->store_name : 'store';
        $total = format_money((float) $order->total, $order->currency);
        $itemLines = array();
        foreach ((array) $items as $item) {
            $line = $item->product_name . ' x' . (int) $item->qty;
            if (!empty($item->sku)) {
                $line .= ' (SKU ' . $item->sku . ')';
            }
            $itemLines[] = $line;
        }
        $itemText = $itemLines ? implode(', ', $itemLines) : '';
        $dial = $this->store_dial_code(isset($order->store_id) ? $order->store_id : 0);

        $customerMsg = $this->customer_ui($status === 'pending' ? 'order.wa_new' : 'order.wa_status', array(
            '{no}' => $order->order_no,
            '{store}' => $storeName,
            '{status}' => $this->customer_status_label($status, $order),
            '{total}' => $total,
            '{items}' => $itemText !== '' ? $itemText : '-',
        ), $order);

        $staffMsg = $this->staff_order_message($order, $items, $dial, $total);

        $sent = 0;
        $wantsWa = class_exists('Ec_order_model')
            ? Ec_order_model::customer_wants_whatsapp($order)
            : false;
        if ($wantsWa && !empty($order->customer_phone) && $this->send($order->customer_phone, $customerMsg, $dial, true)) {
            $sent++;
        }

        $staffPhones = $this->staff_phones($order, $this->order_items_for_staff($order, $items));
        $sent += $this->send_many($staffPhones, $staffMsg, $dial, true);
        return $sent;
    }

    public function notify_item($order, $item)
    {
        if (!$order || !$item) {
            return 0;
        }

        $status = !empty($item->fulfillment_status) ? $item->fulfillment_status : 'pending';
        $storeName = !empty($order->store_name) ? $order->store_name : 'store';
        $dial = $this->store_dial_code(isset($order->store_id) ? $order->store_id : 0);
        $qty = (int) $item->qty;

        $customerMsg = $this->customer_ui('order.wa_item', array(
            '{no}' => $order->order_no,
            '{store}' => $storeName,
            '{product}' => $item->product_name,
            '{status}' => $this->customer_status_label($status, $order),
            '{qty}' => $qty,
        ), $order);

        $staffLines = array(
            '*Order item*',
            'Order: ' . $order->order_no,
            'Store: ' . $storeName,
            'Product: ' . $item->product_name,
            'Status: ' . $this->staff_status_label($status),
            'Qty: ' . $qty,
        );
        if (!empty($item->sku)) {
            $staffLines[] = 'SKU: ' . $item->sku;
        }
        $staffMsg = implode("\n", $staffLines) . "\n\n" . $this->staff_customer_text($order, $dial);
        if (!empty($item->tracking_number)) {
            $company = trim((string) (isset($item->shipping_company) ? $item->shipping_company : ''));
            $trackNo = trim((string) $item->tracking_number);
            $customerMsg .= ' ' . $this->customer_ui('order.wa_tracking', array(
                '{company}' => $company,
                '{tracking}' => $trackNo,
            ), $order);
            $staffMsg .= "\nTracking: " . trim($company . ' ' . $trackNo);
        }
        if (!empty($item->supplier_order_no)) {
            $staffMsg .= "\nSupplier order: " . $item->supplier_order_no;
        }

        $sent = 0;
        $wantsWa = class_exists('Ec_order_model')
            ? Ec_order_model::customer_wants_whatsapp($order)
            : false;
        if ($wantsWa && !empty($order->customer_phone)) {
            $sent += $this->send($order->customer_phone, $customerMsg, $dial, true) ? 1 : 0;
        }
        $sent += $this->send_many($this->staff_phones($order, $this->order_items_for_staff($order, array($item))), $staffMsg, $dial, true);
        return $sent;
    }

    public function notify_payout($payout, $event = 'requested')
    {
        if (!$this->enabled() || !$payout) {
            return 0;
        }

        $partyType = isset($payout->party_type) ? $payout->party_type : '';
        $partyId = isset($payout->party_id) ? (int) $payout->party_id : 0;
        $name = $this->party_label($partyType, $partyId);
        $amount = format_money((float) $payout->amount, $payout->currency);
        $id = (int) $payout->id;
        $who = $partyType === 'store' ? 'Store ' . $name : 'Ecommerce ' . $name;
        $dial = $partyType === 'store' ? $this->store_dial_code($partyId) : '';

        $sent = 0;
        if ($event === 'requested') {
            $adminMsg = 'New payout request #' . $id . ' from ' . $who . '. Amount: ' . $amount . '. Status: pending.';
            $partyMsg = 'Your payout request #' . $id . ' of ' . $amount . ' has been submitted and is pending approval.';
            $sent += $this->send_many(array($this->admin_number()), $adminMsg);
            $sent += $this->send($this->party_number($partyType, $partyId), $partyMsg, $dial) ? 1 : 0;
            return $sent;
        }

        if ($event === 'paid') {
            $partyMsg = 'Payout request #' . $id . ' of ' . $amount . ' has been approved and marked as paid.';
            return $this->send($this->party_number($partyType, $partyId), $partyMsg, $dial) ? 1 : 0;
        }

        if ($event === 'rejected') {
            $partyMsg = 'Payout request #' . $id . ' of ' . $amount . ' was rejected. The amount is back in your wallet.';
            return $this->send($this->party_number($partyType, $partyId), $partyMsg, $dial) ? 1 : 0;
        }

        return 0;
    }

    protected function order_store($order)
    {
        if (!empty($order->store_id) && $this->CI->db->table_exists('stores')) {
            return $this->CI->db->where('id', (int) $order->store_id)->get('stores')->row();
        }
        return null;
    }

    protected function customer_ui($key, $replace, $order)
    {
        $store = $this->order_store($order);
        $locale = function_exists('storefront_ui_locale') ? storefront_ui_locale($store, array()) : 'en';
        $value = $key;
        if (function_exists('storefront_ui_catalog')) {
            $catalog = storefront_ui_catalog();
            if (isset($catalog[$key]['d'])) {
                $value = $catalog[$key]['d'];
            }
        }
        if (function_exists('storefront_ui_locale_strings')) {
            $localized = storefront_ui_locale_strings($locale);
            if (isset($localized[$key])) {
                $value = $localized[$key];
            }
        }
        if (!empty($replace)) {
            $value = strtr($value, $replace);
        }
        return $value;
    }

    protected function customer_status_label($status, $order)
    {
        $status = strtolower(trim((string) $status));
        $label = $this->customer_ui('order.status_' . $status, array(), $order);
        $key = 'order.status_' . $status;
        return ($label === '' || $label === $key) ? $status : $label;
    }

    protected function staff_status_label($status)
    {
        $map = array(
            'pending' => 'pending',
            'confirmed' => 'confirm',
            'processing' => 'process ho raha hai',
            'dispatching' => 'dispatch ho raha hai',
            'shipped' => 'ship ho gaya',
            'delivered' => 'deliver ho gaya',
            'completed' => 'complete',
            'refund_requested' => 'refund request',
            'refunded' => 'refund ho gaya',
            'cancelled' => 'cancel',
        );
        $status = strtolower(trim((string) $status));
        return isset($map[$status]) ? $map[$status] : $status;
    }

    protected function staff_order_message($order, $items, $dial, $total)
    {
        $status = isset($order->status) ? $order->status : '';
        $storeName = !empty($order->store_name) ? $order->store_name : 'store';
        $lines = array(
            $status === 'pending' ? '*Naya order*' : '*Order update*',
            'Order: ' . $order->order_no,
            'Store: ' . $storeName,
            'Status: ' . $this->staff_status_label($status),
            '',
        );
        $customer = trim($this->staff_customer_text($order, $dial));
        if ($customer !== '') {
            $lines[] = $customer;
            $lines[] = '';
        }
        $itemBlock = $this->staff_items_block($items);
        if ($itemBlock !== '') {
            $lines[] = $itemBlock;
            $lines[] = '';
        }
        $lines[] = '*Total:* ' . $total;
        return implode("\n", $lines);
    }

    protected function staff_items_block($items)
    {
        $lines = array();
        $n = 0;
        foreach ((array) $items as $item) {
            $name = trim((string) (isset($item->product_name) ? $item->product_name : ''));
            if ($name === '') {
                continue;
            }
            $n++;
            $lines[] = $n . '. ' . $name;
            $lines[] = '   Qty: ' . (int) $item->qty;
            if (!empty($item->sku)) {
                $lines[] = '   SKU: ' . $item->sku;
            }
        }
        if (!$lines) {
            return '';
        }
        return "*Items*\n" . implode("\n", $lines);
    }

    protected function staff_customer_text($order, $dial = '')
    {
        $phone = $this->normalize_phone(isset($order->customer_phone) ? $order->customer_phone : '', $dial);
        $email = trim((string) (isset($order->customer_email) ? $order->customer_email : ''));
        $name = trim((string) (isset($order->customer_name) ? $order->customer_name : ''));
        $addr = $this->address_block(isset($order->shipping_address) ? $order->shipping_address : '');
        $billing = $this->address_block(isset($order->billing_address) ? $order->billing_address : '');
        $lines = array('*Customer*');
        if ($name !== '') {
            $lines[] = $name;
        }
        if ($phone !== '') {
            $lines[] = 'Phone: ' . $phone;
        }
        if ($email !== '') {
            $lines[] = 'Email: ' . $email;
        }
        if ($addr !== '') {
            $lines[] = '';
            $lines[] = '*Address*';
            $lines[] = $addr;
        }
        if ($billing !== '' && $billing !== $addr) {
            $lines[] = '';
            $lines[] = '*Billing*';
            $lines[] = $billing;
        }
        if (count($lines) === 1) {
            return '';
        }
        return implode("\n", $lines);
    }

    protected function address_block($value)
    {
        $value = str_replace(array("\r\n", "\r"), "\n", (string) $value);
        $lines = array();
        foreach (explode("\n", $value) as $line) {
            $line = trim(preg_replace('/\s+/', ' ', $line));
            if ($line !== '') {
                $lines[] = $line;
            }
        }
        return implode("\n", $lines);
    }

    protected function order_items_for_staff($order, $items)
    {
        $orderId = 0;
        if (is_object($order) && !empty($order->id)) {
            $orderId = (int) $order->id;
        }
        if ($orderId > 0 && $this->CI->db->table_exists('store_order_items')) {
            $loaded = $this->CI->db->where('order_id', $orderId)->get('store_order_items')->result();
            if ($loaded) {
                return $loaded;
            }
        }
        return (array) $items;
    }

    protected function staff_phones($order, $items)
    {
        $phones = array();
        $storeId = isset($order->store_id) ? (int) $order->store_id : 0;
        $storePhone = $this->store_number($storeId);
        if ($storePhone !== '') {
            $phones[] = $storePhone;
        }
        if ($storeId > 0 && $this->CI->db->table_exists('staff')) {
            $staffRows = $this->CI->db->where('store_id', $storeId)->where('status', 1)->get('staff')->result();
            foreach ($staffRows as $staffRow) {
                foreach (array('phone', 'whatsapp', 'mobile') as $phoneField) {
                    if (!empty($staffRow->$phoneField)) {
                        $phones[] = $staffRow->$phoneField;
                        break;
                    }
                }
            }
        }

        foreach ($this->admin_numbers() as $adminPhone) {
            $phones[] = $adminPhone;
        }

        $userIds = array();
        $productIds = array();
        foreach ((array) $items as $item) {
            if (!empty($item->ecommerce_user_id)) {
                $userIds[(int) $item->ecommerce_user_id] = true;
            }
            if (!empty($item->product_id)) {
                $productIds[(int) $item->product_id] = true;
            }
            if (!empty($item->source_product_id)) {
                $productIds[(int) $item->source_product_id] = true;
            }
        }
        if ($productIds && $this->CI->db->table_exists('products') && $this->CI->db->field_exists('created_by', 'products')) {
            $products = $this->CI->db->select('created_by')->where_in('id', array_keys($productIds))->get('products')->result();
            foreach ($products as $product) {
                if (!empty($product->created_by)) {
                    $userIds[(int) $product->created_by] = true;
                }
            }
        }
        if ($userIds && $this->CI->db->table_exists('users')) {
            $users = $this->CI->db->where_in('UserID', array_keys($userIds))->get('users')->result();
            foreach ($users as $user) {
                $number = $this->user_number($user);
                if ($number !== '') {
                    $phones[] = $number;
                }
            }
        }

        return $phones;
    }

    protected function store_number($storeId)
    {
        $storeId = (int) $storeId;
        if ($storeId < 1 || !$this->CI->db->table_exists('store_settings')) {
            return '';
        }
        $map = array();
        $rows = $this->CI->db->where('store_id', $storeId)->get('store_settings')->result();
        foreach ($rows as $row) {
            $pair = store_setting_row_pair($row);
            if ($pair) {
                $map[$pair[0]] = $pair[1];
            }
        }
        foreach (array('whatsapp_number', 'general_support_phone') as $key) {
            if (!empty($map[$key])) {
                return trim((string) $map[$key]);
            }
        }
        if ($this->CI->db->table_exists('stores')) {
            $store = $this->CI->db->select('phone')->where('id', $storeId)->get('stores')->row();
            if ($store && !empty($store->phone)) {
                return trim((string) $store->phone);
            }
        }
        return '';
    }

    protected function store_dial_code($storeId)
    {
        $storeId = (int) $storeId;
        if ($storeId < 1 || !$this->CI->db->table_exists('stores') || !$this->CI->db->table_exists('countries')) {
            return '';
        }
        if (!$this->CI->db->field_exists('phone_code', 'countries')) {
            return '';
        }
        $row = $this->CI->db
            ->select('countries.phone_code')
            ->from('stores')
            ->join('countries', 'countries.id = stores.country_id', 'left')
            ->where('stores.id', $storeId)
            ->get()
            ->row();
        return $row && !empty($row->phone_code) ? $row->phone_code : '';
    }

    protected function admin_numbers()
    {
        $phones = array();
        $adminPhone = trim((string) platform_setting('admin_whatsapp_number', ''));
        if ($adminPhone !== '') {
            foreach (preg_split('/[\s,;|]+/', $adminPhone) as $part) {
                $part = trim((string) $part);
                if ($part === '') {
                    continue;
                }
                $phones[] = $part;
            }
        }
        if ($this->CI->db->table_exists('users')) {
            $admins = $this->CI->db->where('roleID', ROLE_ADMIN)->where('status', 1)->get('users')->result();
            foreach ($admins as $admin) {
                $number = $this->user_number($admin);
                if ($number !== '') {
                    $phones[] = $number;
                }
            }
        }
        return $phones;
    }

    protected function admin_number()
    {
        $phones = $this->admin_numbers();
        return $phones ? $phones[0] : '';
    }

    protected function party_number($partyType, $partyId)
    {
        $partyId = (int) $partyId;
        if ($partyType === 'store') {
            return $this->store_number($partyId);
        }
        if ($partyType === 'ecommerce') {
            $user = $this->CI->db->where('UserID', $partyId)->get('users')->row();
            return $this->user_number($user);
        }
        return '';
    }

    protected function party_label($partyType, $partyId)
    {
        if ($partyType === 'store') {
            $row = $this->CI->db->select('name')->where('id', (int) $partyId)->get('stores')->row();
            return $row && !empty($row->name) ? $row->name : ('Store #' . $partyId);
        }
        $row = $this->CI->db->where('UserID', (int) $partyId)->get('users')->row();
        if (!$row) {
            return 'User #' . $partyId;
        }
        $name = trim(($row->first_name ?? '') . ' ' . ($row->last_name ?? ''));
        return $name !== '' ? $name : ($row->uname ?? ('User #' . $partyId));
    }

    protected function user_number($user)
    {
        if (!$user) {
            return '';
        }
        if (!empty($user->whatsapp_number)) {
            return trim((string) $user->whatsapp_number);
        }
        foreach (array('phone', 'mobile', 'whatsapp') as $field) {
            if (!empty($user->$field)) {
                return trim((string) $user->$field);
            }
        }
        return '';
    }

    protected function log_failure($phone, $message)
    {
        $dir = APPPATH . 'logs/whatsapp';
        if (!is_dir($dir)) {
            @mkdir($dir, 0755, true);
        }
        $file = $dir . '/wa_' . date('Ymd_His') . '_' . preg_replace('/[^0-9]+/', '', (string) $phone) . '.log';
        @file_put_contents($file, "Phone: {$phone}\nError: {$this->lastError}\nResponse: {$this->lastResponse}\n\n{$message}\n");
    }
}
