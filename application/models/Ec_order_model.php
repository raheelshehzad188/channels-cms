<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Ec_order_model extends CI_Model {

    public static function statuses()
    {
        return array(
            'pending' => 'Pending',
            'confirmed' => 'Confirmed',
            'processing' => 'Processing',
            'shipped' => 'Shipped',
            'delivered' => 'Delivered',
            'completed' => 'Completed',
            'cancelled' => 'Cancelled',
        );
    }

    public function get($id)
    {
        return $this->db
            ->select('store_orders.*, stores.name as store_name, stores.email as store_email, stores.domain as store_domain')
            ->from('store_orders')
            ->join('stores', 'stores.id = store_orders.store_id', 'left')
            ->where('store_orders.id', (int) $id)
            ->get()
            ->row();
    }

    public function get_by_no($orderNo)
    {
        return $this->db->where('order_no', $orderNo)->get('store_orders')->row();
    }

    public function items($orderId)
    {
        return $this->db
            ->where('order_id', (int) $orderId)
            ->order_by('id', 'asc')
            ->get('store_order_items')
            ->result();
    }

    public function logs($orderId)
    {
        return $this->db
            ->where('order_id', (int) $orderId)
            ->order_by('id', 'asc')
            ->get('order_status_logs')
            ->result();
    }

    public function for_store($storeId, $limit = 100)
    {
        return $this->db
            ->where('store_id', (int) $storeId)
            ->order_by('id', 'desc')
            ->limit((int) $limit)
            ->get('store_orders')
            ->result();
    }

    public function all($filters = array())
    {
        $this->db
            ->select('store_orders.*, stores.name as store_name')
            ->from('store_orders')
            ->join('stores', 'stores.id = store_orders.store_id', 'left')
            ->order_by('store_orders.id', 'desc');
        if (!empty($filters['status'])) {
            $this->db->where('store_orders.status', $filters['status']);
        }
        if (!empty($filters['payout_status'])) {
            $this->db->where('store_orders.payout_status', $filters['payout_status']);
        }
        if (!empty($filters['store_id'])) {
            $this->db->where('store_orders.store_id', (int) $filters['store_id']);
        }
        return $this->db->get()->result();
    }

    public function for_ecommerce($userId)
    {
        return $this->db
            ->select('store_orders.*, stores.name as store_name')
            ->from('store_orders')
            ->join('stores', 'stores.id = store_orders.store_id', 'left')
            ->join('store_order_items', 'store_order_items.order_id = store_orders.id')
            ->where('store_order_items.ecommerce_user_id', (int) $userId)
            ->group_by('store_orders.id')
            ->order_by('store_orders.id', 'desc')
            ->get()
            ->result();
    }

    public function create_from_cart($store, $customer, $cartItems, $shipping, $payment = array())
    {
        $this->ensure_vat_columns();
        $this->ensure_payment_columns();
        $this->ensure_fx_columns();
        $currency = store_currency($store);
        $fxRate = currency_rate_to_platform($currency);
        $platformFeeUnit = product_platform_fee($store->id);
        $items = array();
        $subtotal = 0;
        $platformTotal = 0;
        $commissionTotal = 0;

        foreach ($cartItems as $product) {
            $qty = max(1, (int) $product->qty);
            $unit = (float) $product->price;
            $line = round($unit * $qty, 2);
            $commissionUnit = product_commission_amount($product);
            $feeUnit = $platformFeeUnit;

            // Prefer stored wholesale/cost for store-owned copies.
            if (!empty($product->store_id) && isset($product->cost_price) && (float) $product->cost_price > 0) {
                $wholesaleUnit = (float) $product->cost_price;
                $baseUnit = max(0, $wholesaleUnit - $commissionUnit - $feeUnit);
            } else {
                $baseUnit = product_base_price($product);
                // If base was overwritten to customer price, fall back.
                if (!empty($product->store_id) && abs($baseUnit - $unit) < 0.0001) {
                    $baseUnit = max(0, $unit - $commissionUnit - $feeUnit);
                }
                $wholesaleUnit = $baseUnit + $commissionUnit + $feeUnit;
            }
            $markupUnit = max(0, $unit - $wholesaleUnit);

            $fee = round($feeUnit * $qty, 2);
            $commission = round($commissionUnit * $qty, 2);
            $base = round($baseUnit * $qty, 2);
            $markup = round($markupUnit * $qty, 2);

            $items[] = array(
                'product_id' => (int) $product->id,
                'source_product_id' => !empty($product->source_product_id) ? (int) $product->source_product_id : null,
                'ecommerce_user_id' => !empty($product->created_by) ? (int) $product->created_by : null,
                'product_name' => $product->name,
                'sku' => isset($product->sku) ? $product->sku : '',
                'qty' => $qty,
                'unit_price' => $unit,
                'line_total' => $line,
                'base_price' => $base,
                'platform_fee' => $fee,
                'commission' => $commission,
                'store_markup' => $markup,
            );
            $subtotal += $line;
            $platformTotal += $fee;
            $commissionTotal += $commission;
        }

        $orderNo = 'ORD' . date('ymd') . strtoupper(substr(uniqid(), -6));
        $vatPercent = (float) platform_setting('vat', 0);
        $vatAmount = cart_vat_amount($subtotal);
        $grandTotal = cart_total_with_vat($subtotal);
        $storeAmount = round($subtotal - $platformTotal - $commissionTotal, 2);

        $this->db->insert('store_orders', array(
            'store_id' => (int) $store->id,
            'customer_id' => $customer ? (int) $customer->id : null,
            'order_no' => $orderNo,
            'customer_name' => $shipping['name'],
            'customer_email' => $shipping['email'],
            'customer_phone' => $shipping['phone'],
            'shipping_address' => $shipping['address'],
            'subtotal' => $subtotal,
            'vat_percent' => $vatPercent,
            'vat_amount' => $vatAmount,
            'total' => $grandTotal,
            'currency' => $currency,
            'fx_rate' => $fxRate,
            'status' => 'pending',
            'platform_fee_total' => $platformTotal,
            'platform_fee_platform' => round($platformTotal * $fxRate, 2),
            'commission_total' => $commissionTotal,
            'commission_platform' => round($commissionTotal * $fxRate, 2),
            'store_amount' => $storeAmount,
            'payout_status' => 'waiting',
            'payment_method' => isset($payment['payment_method']) ? $payment['payment_method'] : '',
            'payment_status' => isset($payment['payment_status']) ? $payment['payment_status'] : 'unpaid',
            'paypal_order_id' => isset($payment['paypal_order_id']) ? $payment['paypal_order_id'] : '',
            'payment_currency' => isset($payment['payment_currency']) ? $payment['payment_currency'] : '',
            'paid_amount' => isset($payment['paid_amount']) ? (float) $payment['paid_amount'] : 0,
        ));
        $orderId = (int) $this->db->insert_id();

        foreach ($items as $item) {
            $item['order_id'] = $orderId;
            $this->db->insert('store_order_items', $item);
        }

        $payNote = !empty($payment['payment_method'])
            ? ' · Paid via ' . $payment['payment_method']
            : '';
        $this->add_log($orderId, 'pending', 'Order placed by customer' . $payNote, 'customer', $customer ? $customer->id : 0);
        return $this->get($orderId);
    }

    public function add_log($orderId, $status, $note, $byType = '', $byId = 0)
    {
        $this->db->insert('order_status_logs', array(
            'order_id' => (int) $orderId,
            'status' => $status,
            'note' => $note,
            'created_by_type' => $byType,
            'created_by_id' => $byId ? (int) $byId : null,
        ));
    }

    public function update_status($orderId, $status, $note, $byType, $byId)
    {
        $order = $this->get($orderId);
        if (!$order) {
            return false;
        }
        $allowed = array_keys(self::statuses());
        if (!in_array($status, $allowed, true)) {
            return false;
        }

        $payload = array('status' => $status);
        if ($status === 'completed' && $order->payout_status === 'waiting') {
            $payload['payout_status'] = 'released';
            $payload['payout_released_at'] = date('Y-m-d H:i:s');
        }
        if ($status === 'cancelled' && $order->payout_status === 'waiting') {
            $payload['payout_status'] = 'cancelled';
        }

        $this->db->where('id', (int) $orderId)->update('store_orders', $payload);
        $this->add_log($orderId, $status, $note, $byType, $byId);
        return $this->get($orderId);
    }

    public function notify_status($order)
    {
        $this->load->library('ec_mail');
        $labels = self::statuses();
        $statusLabel = isset($labels[$order->status]) ? $labels[$order->status] : $order->status;
        $items = $this->items($order->id);
        $rows = '';
        foreach ($items as $item) {
            $rows .= '<tr><td>' . htmlspecialchars($item->product_name) . '</td><td>' . (int) $item->qty . '</td><td>' . format_money($item->line_total, $order->currency) . '</td></tr>';
        }

        $payoutNote = '';
        if ($order->status === 'pending') {
            $payoutNote = '<p>Platform fee <strong>' . format_money($order->platform_fee_total, $order->currency) . '</strong> and ecommerce commission <strong>' . format_money($order->commission_total, $order->currency) . '</strong> are in <strong>waiting</strong> until the order is completed by Super Admin.</p>';
        } elseif ($order->status === 'completed') {
            $payoutNote = '<p>Payout status: <strong>released</strong>. Platform and ecommerce amounts are now cleared from waiting.</p>';
        } elseif ($order->payout_status === 'waiting') {
            $payoutNote = '<p>Platform/ecommerce payout is still <strong>waiting</strong>.</p>';
        }

        $html = '<div style="font-family:Arial,sans-serif;font-size:14px;color:#222">'
            . '<h2>Order ' . htmlspecialchars($order->order_no) . ' — ' . htmlspecialchars($statusLabel) . '</h2>'
            . '<p>Store: <strong>' . htmlspecialchars($order->store_name ?: '') . '</strong></p>'
            . '<p>Customer: ' . htmlspecialchars($order->customer_name) . ' (' . htmlspecialchars($order->customer_email) . ')</p>'
            . '<p>Subtotal: <strong>' . format_money($order->subtotal, $order->currency) . '</strong>'
            . (!empty($order->vat_amount) && (float) $order->vat_amount > 0
                ? ' · VAT (' . number_format((float) $order->vat_percent, 2) . '%): <strong>' . format_money((float) $order->vat_amount, $order->currency) . '</strong>'
                : '')
            . '</p>'
            . '<p>Total: <strong>' . format_money($order->total, $order->currency) . '</strong></p>'
            . $payoutNote
            . '<table cellpadding="8" cellspacing="0" border="1" style="border-collapse:collapse;width:100%;max-width:560px">'
            . '<thead><tr><th align="left">Item</th><th>Qty</th><th align="right">Total</th></tr></thead><tbody>' . $rows . '</tbody></table>'
            . '<p>Shipping:<br>' . nl2br(htmlspecialchars($order->shipping_address)) . '</p>'
            . '</div>';

        $subject = 'Order ' . $order->order_no . ' is ' . $statusLabel;
        $emails = array(
            $order->customer_email,
            $order->store_email,
            platform_setting('admin_notify_email', ''),
            platform_setting('smtp_from_email', ''),
        );

        $admin = $this->db->where('roleID', 1)->where('status', 1)->get('users')->row();
        if ($admin && !empty($admin->email)) {
            $emails[] = $admin->email;
        }

        $userIds = array();
        foreach ($items as $item) {
            if (!empty($item->ecommerce_user_id)) {
                $userIds[(int) $item->ecommerce_user_id] = true;
            }
        }
        if ($userIds) {
            $users = $this->db->where_in('UserID', array_keys($userIds))->get('users')->result();
            foreach ($users as $user) {
                if (!empty($user->email)) {
                    $emails[] = $user->email;
                }
            }
        }

        $this->ec_mail->send_many($emails, $subject, $html);
        return true;
    }

    public function store_stats($storeId)
    {
        $storeId = (int) $storeId;
        $orders = (int) $this->db->where('store_id', $storeId)->count_all_results('store_orders');
        $customers = (int) $this->db->where('store_id', $storeId)->count_all_results('store_customers');
        // Only the store's own product plus. Never platform fee or commission.
        $markupEarnings = 0.0;
        if ($this->db->table_exists('store_order_items') && $this->db->field_exists('store_markup', 'store_order_items')) {
            $markup = $this->db
                ->select('SUM(store_order_items.store_markup) as markup_earnings', false)
                ->from('store_order_items')
                ->join('store_orders', 'store_orders.id = store_order_items.order_id')
                ->where('store_orders.store_id', $storeId)
                ->where_not_in('store_orders.status', array('cancelled'))
                ->get()
                ->row();
            $markupEarnings = $markup && $markup->markup_earnings ? (float) $markup->markup_earnings : 0.0;
        }

        return array(
            'orders' => $orders,
            'customers' => $customers,
            'markup_earnings' => $markupEarnings,
        );
    }

    protected function ensure_vat_columns()
    {
        if (!$this->db->table_exists('store_orders')) {
            return;
        }
        if (!$this->db->field_exists('vat_percent', 'store_orders')) {
            $this->db->query('ALTER TABLE store_orders ADD COLUMN vat_percent DECIMAL(8,2) NOT NULL DEFAULT 0 AFTER subtotal');
        }
        if (!$this->db->field_exists('vat_amount', 'store_orders')) {
            $this->db->query('ALTER TABLE store_orders ADD COLUMN vat_amount DECIMAL(12,2) NOT NULL DEFAULT 0 AFTER vat_percent');
        }
    }

    protected function ensure_payment_columns()
    {
        if (!$this->db->table_exists('store_orders')) {
            return;
        }
        $cols = array(
            'payment_method' => "ALTER TABLE store_orders ADD COLUMN payment_method VARCHAR(30) NOT NULL DEFAULT '' AFTER payout_status",
            'payment_status' => "ALTER TABLE store_orders ADD COLUMN payment_status VARCHAR(30) NOT NULL DEFAULT 'unpaid' AFTER payment_method",
            'paypal_order_id' => "ALTER TABLE store_orders ADD COLUMN paypal_order_id VARCHAR(64) NOT NULL DEFAULT '' AFTER payment_status",
            'payment_currency' => "ALTER TABLE store_orders ADD COLUMN payment_currency VARCHAR(10) NOT NULL DEFAULT '' AFTER paypal_order_id",
            'paid_amount' => "ALTER TABLE store_orders ADD COLUMN paid_amount DECIMAL(12,2) NOT NULL DEFAULT 0 AFTER payment_currency",
        );
        foreach ($cols as $field => $sql) {
            if (!$this->db->field_exists($field, 'store_orders')) {
                $this->db->query($sql);
            }
        }
        $this->ensure_fx_columns();
    }

    protected function ensure_fx_columns()
    {
        ec_ensure_currency_schema();
    }
}
