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

    public function send_many($phones, $message, $dialCode = '')
    {
        $sent = 0;
        $unique = array();
        foreach ((array) $phones as $phone) {
            $normalized = $this->normalize_phone($phone, $dialCode);
            if ($normalized === '' || isset($unique[$normalized])) {
                continue;
            }
            $unique[$normalized] = true;
            if ($this->send($normalized, $message)) {
                $sent++;
            }
        }
        return $sent;
    }

    public function notify_order($order, $items = array())
    {
        if (!$this->enabled() || !$order) {
            return 0;
        }

        $labels = class_exists('Ec_order_model') ? Ec_order_model::statuses() : array();
        $status = isset($order->status) ? $order->status : '';
        $statusLabel = isset($labels[$status]) ? $labels[$status] : $status;
        $storeName = !empty($order->store_name) ? $order->store_name : 'store';
        $total = format_money((float) $order->total, $order->currency);
        $itemLines = array();
        foreach ((array) $items as $item) {
            $itemLines[] = $item->product_name . ' x' . (int) $item->qty;
        }
        $itemText = $itemLines ? implode(', ', $itemLines) : 'See order details';
        $dial = $this->store_dial_code(isset($order->store_id) ? $order->store_id : 0);

        $customerMsg = 'Your order ' . $order->order_no . ' at ' . $storeName . ' is ' . $statusLabel . '. Total: ' . $total . '. Items: ' . $itemText;
        $staffMsg = ($status === 'pending' ? 'New order ' : 'Order ')
            . $order->order_no . ' at ' . $storeName . ' is ' . $statusLabel . '.'
            . ' Customer: ' . $order->customer_name
            . ' (' . $this->normalize_phone($order->customer_phone, $dial) . ').'
            . ' Total: ' . $total . '. Items: ' . $itemText;

        $sent = 0;
        if ($this->send($order->customer_phone, $customerMsg, $dial)) {
            $sent++;
        }

        $staffPhones = $this->staff_phones($order, $items);
        $sent += $this->send_many($staffPhones, $staffMsg, $dial);
        return $sent;
    }

    public function notify_item($order, $item)
    {
        if (!$this->enabled() || !$order || !$item) {
            return 0;
        }

        $labels = class_exists('Ec_order_model') ? Ec_order_model::item_statuses() : array();
        $status = !empty($item->fulfillment_status) ? $item->fulfillment_status : 'pending';
        $statusLabel = isset($labels[$status]) ? $labels[$status] : $status;
        $storeName = !empty($order->store_name) ? $order->store_name : 'store';
        $dial = $this->store_dial_code(isset($order->store_id) ? $order->store_id : 0);
        $message = 'Order ' . $order->order_no . ' at ' . $storeName . ': ' . $item->product_name
            . ' is now ' . $statusLabel . '. Qty: ' . (int) $item->qty . '.';

        $phones = array($order->customer_phone);
        $phones = array_merge($phones, $this->staff_phones($order, array($item)));
        return $this->send_many($phones, $message, $dial);
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

    protected function staff_phones($order, $items)
    {
        $phones = array();
        $storeId = isset($order->store_id) ? (int) $order->store_id : 0;
        $storePhone = $this->store_number($storeId);
        if ($storePhone !== '') {
            $phones[] = $storePhone;
        }

        $adminPhone = $this->admin_number();
        if ($adminPhone !== '') {
            $phones[] = $adminPhone;
        }

        $userIds = array();
        foreach ((array) $items as $item) {
            if (!empty($item->ecommerce_user_id)) {
                $userIds[(int) $item->ecommerce_user_id] = true;
            }
        }
        if ($userIds) {
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

    protected function admin_number()
    {
        $adminPhone = trim((string) platform_setting('admin_whatsapp_number', ''));
        if ($adminPhone !== '') {
            return $adminPhone;
        }
        $admin = $this->CI->db->where('roleID', ROLE_ADMIN)->where('status', 1)->order_by('UserID', 'asc')->get('users')->row();
        return $this->user_number($admin);
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
        return !empty($user->phone) ? trim((string) $user->phone) : '';
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
