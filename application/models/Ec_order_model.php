<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Ec_order_model extends CI_Model {

    public static function statuses()
    {
        $map = array(
            'pending' => 'order.status_pending',
            'confirmed' => 'order.status_confirmed',
            'processing' => 'order.status_processing',
            'shipped' => 'order.status_shipped',
            'dispatching' => 'order.status_dispatching',
            'delivered' => 'order.status_delivered',
            'completed' => 'order.status_completed',
            'refund_requested' => 'order.status_refund_requested',
            'refunded' => 'order.status_refunded',
            'cancelled' => 'order.status_cancelled',
        );
        $out = array();
        foreach ($map as $code => $key) {
            $out[$code] = function_exists('store_ui') ? store_ui($key) : ucfirst(str_replace('_', ' ', $code));
        }
        return $out;
    }

    public static function customer_status_bucket($item)
    {
        $status = '';
        if (is_object($item)) {
            if (!empty($item->fulfillment_status)) {
                $status = $item->fulfillment_status;
            } elseif (!empty($item->order_status)) {
                $status = $item->order_status;
            }
        } elseif (is_string($item)) {
            $status = $item;
        }
        $status = strtolower(trim((string) $status));
        if ($status === '' || $status === 'processing' || $status === 'confirmed') {
            return 'pending';
        }
        return $status;
    }

    public static function item_statuses()
    {
        return array(
            'pending' => 'Pending',
            'processing' => 'Processing',
            'dispatching' => 'Dispatching',
            'shipped' => 'Shipped',
            'delivered' => 'Delivered',
            'completed' => 'Completed',
            'refund_requested' => 'Refund requested',
            'refunded' => 'Refunded',
            'cancelled' => 'Cancelled',
        );
    }

    public static function shipping_companies()
    {
        return array(
            'PostNord' => 'PostNord',
            'DHL' => 'DHL',
            'UPS' => 'UPS',
            'FedEx' => 'FedEx',
            'DPD' => 'DPD',
            'GLS' => 'GLS',
            'Bring' => 'Bring',
            'Cainiao' => 'Cainiao',
            'AliExpress Standard Shipping' => 'AliExpress Standard Shipping',
            'YunExpress' => 'YunExpress',
            '4PX' => '4PX',
            'Yanwen' => 'Yanwen',
            'Other' => 'Other',
        );
    }

    public static function ecommerce_next_status($current)
    {
        $map = array(
            'pending' => 'dispatching',
            'processing' => 'dispatching',
            'dispatching' => 'shipped',
            'shipped' => 'delivered',
        );
        return isset($map[$current]) ? $map[$current] : '';
    }

    public static function refund_allowed($status)
    {
        return in_array($status, array('pending', 'processing'), true);
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

    public function find_by_paypal_order_id($paypalOrderId, $storeId = 0)
    {
        $paypalOrderId = trim((string) $paypalOrderId);
        if ($paypalOrderId === '' || !$this->db->table_exists('store_orders') || !$this->db->field_exists('paypal_order_id', 'store_orders')) {
            return null;
        }
        $this->db->where('paypal_order_id', $paypalOrderId);
        if ($storeId > 0) {
            $this->db->where('store_id', (int) $storeId);
        }
        return $this->db->order_by('id', 'desc')->get('store_orders')->row();
    }

    public function mark_payment_status($orderId, $status, $extra = array())
    {
        $orderId = (int) $orderId;
        $status = strtolower(trim((string) $status));
        if ($orderId < 1 || $status === '') {
            return false;
        }
        $payload = array('payment_status' => $status);
        if (is_array($extra)) {
            foreach (array('paid_amount', 'payment_currency', 'payment_method', 'paypal_order_id') as $field) {
                if (array_key_exists($field, $extra)) {
                    $payload[$field] = $extra[$field];
                }
            }
        }
        $this->db->where('id', $orderId)->update('store_orders', $payload);
        return true;
    }

    public function items($orderId)
    {
        $this->ensure_item_columns();
        return $this->db
            ->select('store_order_items.*, products.id as linked_product_id, products.slug as product_slug, products.image as product_image')
            ->from('store_order_items')
            ->join('products', 'products.id = store_order_items.product_id', 'left')
            ->where('store_order_items.order_id', (int) $orderId)
            ->order_by('store_order_items.id', 'asc')
            ->get()
            ->result();
    }

    public function get_item($itemId)
    {
        $this->ensure_item_columns();
        return $this->db
            ->select('store_order_items.*, store_orders.store_id, store_orders.order_no, store_orders.currency, store_orders.status as order_status, store_orders.payout_status')
            ->from('store_order_items')
            ->join('store_orders', 'store_orders.id = store_order_items.order_id')
            ->where('store_order_items.id', (int) $itemId)
            ->get()
            ->row();
    }

    public function item_logs($itemId)
    {
        if (!$this->db->table_exists('order_item_status_logs')) {
            return array();
        }
        return $this->db
            ->where('item_id', (int) $itemId)
            ->order_by('id', 'asc')
            ->get('order_item_status_logs')
            ->result();
    }

    public function item_status_counts($items)
    {
        $counts = array(
            'pending' => 0,
            'processing' => 0,
            'dispatching' => 0,
            'shipped' => 0,
            'delivered' => 0,
            'completed' => 0,
            'refund_requested' => 0,
            'refunded' => 0,
            'cancelled' => 0,
        );
        foreach ($items as $item) {
            $status = !empty($item->fulfillment_status) ? $item->fulfillment_status : 'pending';
            if (!isset($counts[$status])) {
                $counts[$status] = 0;
            }
            $counts[$status]++;
        }
        return $counts;
    }

    public function for_customer($storeId, $customerId)
    {
        return $this->db
            ->select('store_orders.*, (SELECT COUNT(*) FROM store_order_items WHERE store_order_items.order_id = store_orders.id) as item_count', false)
            ->where('store_id', (int) $storeId)
            ->where('customer_id', (int) $customerId)
            ->order_by('id', 'desc')
            ->get('store_orders')
            ->result();
    }

    public function for_customer_items($storeId, $customerId)
    {
        $this->ensure_item_columns();
        return $this->db
            ->select('store_order_items.*, store_orders.order_no, store_orders.currency, store_orders.created_at as order_created_at, store_orders.status as order_status, products.slug as product_slug, products.image as product_image, products.id as linked_product_id')
            ->from('store_order_items')
            ->join('store_orders', 'store_orders.id = store_order_items.order_id')
            ->join('products', 'products.id = store_order_items.product_id', 'left')
            ->where('store_orders.store_id', (int) $storeId)
            ->where('store_orders.customer_id', (int) $customerId)
            ->order_by('store_orders.id', 'desc')
            ->order_by('store_order_items.id', 'asc')
            ->get()
            ->result();
    }

    public function for_ecommerce_items($userId, $filters = array())
    {
        $this->ensure_item_columns();
        $this->db
            ->select('store_order_items.*, store_orders.order_no, store_orders.currency, store_orders.created_at as order_created_at, store_orders.customer_name, store_orders.customer_email, store_orders.customer_phone, store_orders.shipping_address, store_orders.store_id, store_orders.status as order_status, stores.name as store_name')
            ->from('store_order_items')
            ->join('store_orders', 'store_orders.id = store_order_items.order_id')
            ->join('stores', 'stores.id = store_orders.store_id', 'left')
            ->where('store_order_items.ecommerce_user_id', (int) $userId)
            ->order_by('store_orders.id', 'desc')
            ->order_by('store_order_items.id', 'asc');
        if (!empty($filters['store_id'])) {
            $this->db->where('store_orders.store_id', (int) $filters['store_id']);
        }
        if (!empty($filters['country_id'])) {
            $this->db->where('stores.country_id', (int) $filters['country_id']);
        }
        return $this->db->get()->result();
    }

    public function for_customer_by_no($storeId, $customerId, $orderNo)
    {
        $orderNo = trim((string) $orderNo);
        if ($orderNo === '') {
            return null;
        }
        return $this->db
            ->select('store_orders.*, stores.name as store_name, stores.email as store_email, stores.domain as store_domain')
            ->from('store_orders')
            ->join('stores', 'stores.id = store_orders.store_id', 'left')
            ->where('store_orders.store_id', (int) $storeId)
            ->where('store_orders.customer_id', (int) $customerId)
            ->where('store_orders.order_no', $orderNo)
            ->get()
            ->row();
    }

    public function logs($orderId)
    {
        return $this->db
            ->where('order_id', (int) $orderId)
            ->order_by('id', 'asc')
            ->get('order_status_logs')
            ->result();
    }

    public function item_logs_for_order($orderId)
    {
        if (!$this->db->table_exists('order_item_status_logs')) {
            return array();
        }
        return $this->db
            ->where('order_id', (int) $orderId)
            ->order_by('id', 'asc')
            ->get('order_item_status_logs')
            ->result();
    }

    public static function customer_track_step($status)
    {
        $status = strtolower(trim((string) $status));
        if ($status === 'completed') {
            return 'complete';
        }
        if ($status === 'delivered') {
            return 'delivered';
        }
        if ($status === 'shipped') {
            return 'dispatched';
        }
        if (in_array($status, array('processing', 'dispatching'), true)) {
            return 'processing';
        }
        if (in_array($status, array('pending', 'confirmed'), true)) {
            return 'pending';
        }
        return '';
    }

    public function for_store($storeId, $limit = 100)
    {
        if (!$this->db->table_exists('store_orders')) {
            return array();
        }
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
        $this->apply_order_filters($filters);
        return $this->db->get()->result();
    }

    public function for_ecommerce($userId, $filters = array())
    {
        $filters['ecommerce_user_id'] = (int) $userId;
        $this->db
            ->select('store_orders.*, stores.name as store_name')
            ->from('store_orders')
            ->join('stores', 'stores.id = store_orders.store_id', 'left')
            ->join('store_order_items', 'store_order_items.order_id = store_orders.id')
            ->where('store_order_items.ecommerce_user_id', (int) $userId)
            ->group_by('store_orders.id')
            ->order_by('store_orders.id', 'desc');
        $this->apply_order_filters($filters, true);
        return $this->db->get()->result();
    }

    protected function apply_order_filters($filters, $itemsJoined = false)
    {
        if (!empty($filters['status'])) {
            $this->db->where('store_orders.status', $filters['status']);
        }
        if (!empty($filters['payout_status'])) {
            $this->db->where('store_orders.payout_status', $filters['payout_status']);
        }
        if (!empty($filters['store_id'])) {
            $this->db->where('store_orders.store_id', (int) $filters['store_id']);
        }
        if (!empty($filters['country_id'])) {
            $this->db->where('stores.country_id', (int) $filters['country_id']);
        }
        $ecomId = !empty($filters['ecommerce_user_id']) ? (int) $filters['ecommerce_user_id'] : 0;
        if ($ecomId > 0) {
            if ($itemsJoined) {
                $this->db->where('store_order_items.ecommerce_user_id', $ecomId);
            } else {
                $this->db->where(
                    'store_orders.id IN (SELECT soi.order_id FROM store_order_items soi WHERE soi.ecommerce_user_id = ' . $ecomId . ')',
                    null,
                    false
                );
            }
        }
        if (!empty($filters['supplier_id'])) {
            $sid = (int) $filters['supplier_id'];
            $sql = 'store_orders.id IN (
                SELECT soi.order_id FROM store_order_items soi
                LEFT JOIN products p ON p.id = soi.product_id
                LEFT JOIN products sp ON sp.id = IFNULL(NULLIF(soi.source_product_id, 0), soi.product_id)
                WHERE (p.supplier_id = ' . $sid . ' OR sp.supplier_id = ' . $sid . ')';
            if ($ecomId > 0) {
                $sql .= ' AND soi.ecommerce_user_id = ' . $ecomId;
            }
            $sql .= ')';
            $this->db->where($sql, null, false);
        }
    }

    public function filter_summary($filters = array())
    {
        $this->ensure_item_columns();
        $platform = platform_currency();
        $totals = array(
            'store_commission' => 0.0,
            'ecommerce_commission' => 0.0,
            'platform_commission' => 0.0,
            'total_cost' => 0.0,
            'total_sale' => 0.0,
            'total_profit' => 0.0,
            'order_count' => 0,
            'platform_waiting' => 0.0,
            'commission_waiting' => 0.0,
        );
        $orders = $this->all($filters);
        $totals['order_count'] = count($orders);
        $ids = array();
        foreach ($orders as $order) {
            $ids[] = (int) $order->id;
            if (isset($order->payout_status) && $order->payout_status === 'waiting') {
                $totals['platform_waiting'] += order_amount_in_platform($order, 'platform_fee');
                $totals['commission_waiting'] += order_amount_in_platform($order, 'commission');
            }
        }
        if (!$ids) {
            return (object) $totals;
        }
        $this->db
            ->select('store_order_items.base_price, store_order_items.platform_fee, store_order_items.commission, store_order_items.store_markup, store_order_items.line_total, store_orders.currency')
            ->from('store_order_items')
            ->join('store_orders', 'store_orders.id = store_order_items.order_id')
            ->where_in('store_order_items.order_id', $ids);
        if (!empty($filters['ecommerce_user_id'])) {
            $this->db->where('store_order_items.ecommerce_user_id', (int) $filters['ecommerce_user_id']);
        }
        $items = $this->db->get()->result();
        foreach ($items as $item) {
            $from = !empty($item->currency) ? $item->currency : $platform;
            $cost = convert_money((float) $item->base_price, $from, $platform);
            $sale = convert_money((float) $item->line_total, $from, $platform);
            $totals['store_commission'] += convert_money((float) (isset($item->store_markup) ? $item->store_markup : 0), $from, $platform);
            $totals['ecommerce_commission'] += convert_money((float) $item->commission, $from, $platform);
            $totals['platform_commission'] += convert_money((float) $item->platform_fee, $from, $platform);
            $totals['total_cost'] += $cost;
            $totals['total_sale'] += $sale;
            $totals['total_profit'] += ($sale - $cost);
        }
        foreach ($totals as $key => $value) {
            if ($key === 'order_count') {
                continue;
            }
            $totals[$key] = round((float) $value, 2);
        }
        return (object) $totals;
    }

    public function create_from_cart($store, $customer, $cartItems, $shipping, $payment = array())
    {
        $this->ensure_vat_columns();
        $this->ensure_shipping_columns();
        $this->ensure_payment_columns();
        if (function_exists('ensure_customer_address_columns')) {
            ensure_customer_address_columns();
        }
        $this->ensure_fx_columns();
        $this->ensure_item_columns();
        $currency = store_currency($store);
        $fxRate = currency_rate_to_platform($currency);
        $items = array();
        $subtotal = 0;
        $platformTotal = 0;
        $commissionTotal = 0;
        $itemQtyTotal = 0;

        foreach ($cartItems as $product) {
            $qty = max(1, (int) $product->qty);
            $itemQtyTotal += $qty;
            $unit = (float) $product->price;
            $line = round($unit * $qty, 2);
            $commissionUnit = product_commission_amount($product);
            $feeUnit = product_platform_fee($store->id, $product);

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
                'fulfillment_status' => 'pending',
            );
            $subtotal += $line;
            $platformTotal += $fee;
            $commissionTotal += $commission;
        }

        $orderNo = 'ORD' . date('ymd') . strtoupper(substr(uniqid(), -6));
        $vatPercent = (float) platform_setting('vat', 0);
        $vatAmount = cart_vat_amount($subtotal);
        $shipSettings = store_front_settings((int) $store->id);
        $shippingAmount = cart_shipping_amount($shipSettings, $itemQtyTotal, $subtotal, $cartItems);
        $grandTotal = cart_total_payable($subtotal, $shippingAmount);
        $storeAmount = round($subtotal - $platformTotal - $commissionTotal + $shippingAmount, 2);

        $this->db->insert('store_orders', array(
            'store_id' => (int) $store->id,
            'customer_id' => $customer ? (int) $customer->id : null,
            'order_no' => $orderNo,
            'customer_name' => $shipping['name'],
            'customer_email' => $shipping['email'],
            'customer_phone' => $shipping['phone'],
            'shipping_address' => $shipping['address'],
            'billing_address' => isset($shipping['billing_address']) ? $shipping['billing_address'] : '',
            'subtotal' => $subtotal,
            'vat_percent' => $vatPercent,
            'vat_amount' => $vatAmount,
            'shipping_amount' => $shippingAmount,
            'shipping_qty' => $itemQtyTotal,
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

        $payNote = '';
        $payStatus = isset($payment['payment_status']) ? strtolower(trim((string) $payment['payment_status'])) : '';
        if (!empty($payment['payment_method']) && $payStatus === 'paid') {
            $payNote = ' · Paid via ' . $payment['payment_method'];
        } elseif (!empty($payment['payment_method']) && $payStatus !== '') {
            $payNote = ' · Payment ' . $payStatus . ' via ' . $payment['payment_method'];
        }
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

        if ($status === 'cancelled') {
            $this->bulk_set_open_items($orderId, 'cancelled', $note, $byType, $byId);
        } elseif ($status === 'completed') {
            $this->complete_delivered_items($orderId, $note, $byType, $byId, true);
        }

        $updated = $this->get($orderId);
        $this->notify_status($updated);
        return $updated;
    }

    public function add_item_log($orderId, $itemId, $status, $note, $byType = '', $byId = 0)
    {
        if (!$this->db->table_exists('order_item_status_logs')) {
            return;
        }
        $this->db->insert('order_item_status_logs', array(
            'order_id' => (int) $orderId,
            'item_id' => (int) $itemId,
            'status' => $status,
            'note' => $note,
            'created_by_type' => $byType,
            'created_by_id' => $byId ? (int) $byId : null,
        ));
    }

    public function update_item_status($itemId, $status, $note, $byType, $byId, $role = 'ecommerce', $extra = array())
    {
        $this->ensure_item_columns();
        $item = $this->get_item($itemId);
        if (!$item) {
            return false;
        }
        $current = !empty($item->fulfillment_status) ? $item->fulfillment_status : 'pending';
        $allowed = array_keys(self::item_statuses());
        if (!in_array($status, $allowed, true) || $status === $current) {
            return false;
        }
        if (!is_array($extra)) {
            $extra = array();
        }

        if ($role === 'ecommerce') {
            $next = self::ecommerce_next_status($current);
            $canRefund = ($status === 'refunded' && $current === 'refund_requested');
            if ($status === 'refunded' && $current !== 'refund_requested') {
                return false;
            }
            if ($status === 'processing' && $current === 'refund_requested') {
                // reject refund, back to processing/pending handled separately
            } elseif (!$canRefund && $next !== $status) {
                return false;
            }
        } elseif ($role === 'customer') {
            if ($status !== 'refund_requested' || !self::refund_allowed($current)) {
                return false;
            }
        } elseif ($role === 'admin') {
            if ($status === 'completed' && $current !== 'delivered' && $current !== 'completed') {
                return false;
            }
        }

        $supplierNo = trim((string) (isset($extra['supplier_order_no']) ? $extra['supplier_order_no'] : ''));
        $trackingNo = trim((string) (isset($extra['tracking_number']) ? $extra['tracking_number'] : ''));
        $company = trim((string) (isset($extra['shipping_company']) ? $extra['shipping_company'] : ''));
        if ($company === 'Other') {
            $other = trim((string) (isset($extra['shipping_company_other']) ? $extra['shipping_company_other'] : ''));
            if ($other !== '') {
                $company = $other;
            }
        }

        if ($role === 'ecommerce' && $status === 'dispatching' && $supplierNo === '') {
            return false;
        }
        if ($role === 'ecommerce' && $status === 'shipped' && ($trackingNo === '' || $company === '')) {
            return false;
        }

        $payload = array(
            'fulfillment_status' => $status,
            'status_updated_at' => date('Y-m-d H:i:s'),
        );
        if ($supplierNo !== '') {
            $payload['supplier_order_no'] = substr($supplierNo, 0, 80);
        }
        if ($trackingNo !== '') {
            $payload['tracking_number'] = substr($trackingNo, 0, 80);
        }
        if ($company !== '') {
            $payload['shipping_company'] = substr($company, 0, 80);
        }
        if ($note === '') {
            if ($status === 'dispatching' && $supplierNo !== '') {
                $note = 'Supplier order ' . $supplierNo;
            } elseif ($status === 'shipped' && $trackingNo !== '') {
                $note = 'Shipped via ' . $company . ' ' . $trackingNo;
            }
        }
        if ($status === 'refund_requested') {
            $payload['refund_reason'] = $note;
            $payload['refund_requested_at'] = date('Y-m-d H:i:s');
        }
        $this->db->where('id', (int) $itemId)->update('store_order_items', $payload);
        $this->add_item_log($item->order_id, $itemId, $status, $note, $byType, $byId);

        $updated = $this->get_item($itemId);
        if ($status === 'completed') {
            $order = $this->get($item->order_id);
            $this->load->model('Accounting_model');
            $this->Accounting_model->credit_from_item($order, $updated);
        }

        $this->sync_order_status($item->order_id, $byType, $byId);
        $updated = $this->get_item($itemId);
        $this->notify_item_status($this->get($item->order_id), $updated);
        return $updated;
    }

    public function reject_item_refund($itemId, $note, $byType, $byId)
    {
        $item = $this->get_item($itemId);
        if (!$item || $item->fulfillment_status !== 'refund_requested') {
            return false;
        }
        $back = 'processing';
        $logs = $this->item_logs($itemId);
        foreach ($logs as $log) {
            if ($log->status === 'pending' || $log->status === 'processing') {
                $back = $log->status;
            }
        }
        $this->db->where('id', (int) $itemId)->update('store_order_items', array(
            'fulfillment_status' => $back,
            'status_updated_at' => date('Y-m-d H:i:s'),
        ));
        $this->add_item_log($item->order_id, $itemId, $back, $note ?: 'Refund request declined', $byType, $byId);
        $this->sync_order_status($item->order_id, $byType, $byId);
        $updated = $this->get_item($itemId);
        $this->notify_item_status($this->get($item->order_id), $updated);
        return $updated;
    }

    public function complete_delivered_items($orderId, $note, $byType, $byId, $forceAll = false)
    {
        $items = $this->items($orderId);
        $count = 0;
        foreach ($items as $item) {
            $status = !empty($item->fulfillment_status) ? $item->fulfillment_status : 'pending';
            if ($status === 'delivered' || ($forceAll && !in_array($status, array('completed', 'refunded', 'cancelled', 'refund_requested'), true))) {
                if ($status !== 'delivered' && !$forceAll) {
                    continue;
                }
                $this->db->where('id', (int) $item->id)->update('store_order_items', array(
                    'fulfillment_status' => 'completed',
                    'status_updated_at' => date('Y-m-d H:i:s'),
                ));
                $this->add_item_log($orderId, $item->id, 'completed', $note, $byType, $byId);
                $updated = $this->get_item($item->id);
                $order = $this->get($orderId);
                $this->load->model('Accounting_model');
                $this->Accounting_model->credit_from_item($order, $updated);
                $count++;
            }
        }
        $this->sync_order_status($orderId, $byType, $byId);
        return $count;
    }

    public function bulk_set_open_items($orderId, $status, $note, $byType, $byId)
    {
        $items = $this->items($orderId);
        foreach ($items as $item) {
            $current = !empty($item->fulfillment_status) ? $item->fulfillment_status : 'pending';
            if (in_array($current, array('completed', 'refunded', 'cancelled'), true)) {
                continue;
            }
            $this->db->where('id', (int) $item->id)->update('store_order_items', array(
                'fulfillment_status' => $status,
                'status_updated_at' => date('Y-m-d H:i:s'),
            ));
            $this->add_item_log($orderId, $item->id, $status, $note, $byType, $byId);
        }
    }

    public function sync_order_status($orderId, $byType = 'system', $byId = 0)
    {
        $order = $this->get($orderId);
        if (!$order) {
            return;
        }
        $items = $this->items($orderId);
        if (empty($items)) {
            return;
        }
        $counts = $this->item_status_counts($items);
        $open = (int) $counts['pending'] + (int) $counts['processing'] + (int) $counts['dispatching'] + (int) $counts['shipped'] + (int) $counts['delivered'] + (int) $counts['refund_requested'];
        $newStatus = $order->status;
        $payout = $order->payout_status;

        if ($open === 0) {
            if ($counts['completed'] > 0 && $counts['refunded'] + $counts['cancelled'] < count($items)) {
                $newStatus = 'completed';
            } elseif ($counts['refunded'] > 0 && $counts['cancelled'] === 0) {
                $newStatus = 'refunded';
            } elseif ($counts['cancelled'] === count($items)) {
                $newStatus = 'cancelled';
            } elseif ($counts['completed'] === count($items)) {
                $newStatus = 'completed';
            } else {
                $newStatus = 'completed';
            }
        } elseif ($counts['delivered'] > 0 && $counts['pending'] + $counts['processing'] + $counts['dispatching'] + $counts['shipped'] === 0) {
            $newStatus = 'delivered';
        } elseif ($counts['shipped'] > 0 && $counts['pending'] + $counts['processing'] + $counts['dispatching'] === 0) {
            $newStatus = 'shipped';
        } elseif ($counts['dispatching'] > 0) {
            $newStatus = 'dispatching';
        } elseif ($counts['processing'] > 0) {
            $newStatus = 'processing';
        } elseif ($counts['refund_requested'] > 0) {
            $newStatus = 'refund_requested';
        } else {
            $newStatus = 'pending';
        }

        if ($newStatus === 'completed' && $payout === 'waiting') {
            $payout = 'released';
        }
        if ($newStatus === 'cancelled' && $payout === 'waiting') {
            $payout = 'cancelled';
        }
        if ($newStatus === 'refunded' && $payout === 'waiting') {
            $payout = 'cancelled';
        }

        $payload = array('status' => $newStatus, 'payout_status' => $payout);
        if ($payout === 'released' && $order->payout_status !== 'released') {
            $payload['payout_released_at'] = date('Y-m-d H:i:s');
        }
        if ($newStatus !== $order->status || $payout !== $order->payout_status) {
            $this->db->where('id', (int) $orderId)->update('store_orders', $payload);
            if ($newStatus !== $order->status) {
                $this->add_log($orderId, $newStatus, 'Order status updated from item tracking', $byType, $byId);
            }
        }
    }

    public static function customer_notify_channel()
    {
        $channel = strtolower(trim((string) platform_setting('customer_notify_channel', 'mail')));
        return $channel === 'whatsapp' ? 'whatsapp' : 'mail';
    }

    public static function customer_wants_email($order)
    {
        if (self::is_test_buyer_order($order)) {
            return true;
        }
        return self::customer_notify_channel() === 'mail';
    }

    public static function customer_wants_whatsapp($order)
    {
        if (self::is_test_buyer_order($order)) {
            return true;
        }
        return self::customer_notify_channel() === 'whatsapp';
    }

    public static function is_test_buyer_order($order)
    {
        if (!$order || empty($order->customer_email) || !function_exists('is_checkout_test_buyer')) {
            return false;
        }
        return is_checkout_test_buyer($order->customer_email);
    }

    public function notify_item_status($order, $item)
    {
        $this->load->library('ec_mail');
        $order = $this->hydrate_order($order);
        if (!$order || !$item) {
            return;
        }
        $labels = self::item_statuses();
        $status = !empty($item->fulfillment_status) ? $item->fulfillment_status : 'pending';
        $statusLabel = isset($labels[$status]) ? $labels[$status] : $status;
        $trackingNo = !empty($item->tracking_number) ? $item->tracking_number : '';
        $company = !empty($item->shipping_company) ? $item->shipping_company : '';
        if (self::customer_wants_email($order)) {
            $custText = $this->order_mail_ui('order.mail_item_update', array(
                '{no}' => $order->order_no,
                '{product}' => $item->product_name,
                '{status}' => $statusLabel,
            ), $order);
            if ($trackingNo !== '') {
                $custText .= ' ' . $this->order_mail_ui('order.mail_tracking', array(
                    '{company}' => $company,
                    '{tracking}' => $trackingNo,
                ), $order);
            }
            $custHtml = '<div style="font-family:Arial,sans-serif;font-size:14px;color:#222;line-height:1.5">'
                . '<p>' . $this->mail_h($custText) . '</p>'
                . '</div>';
            $this->ec_mail->send(
                $order->customer_email,
                $this->order_mail_ui('order.mail_item_subject', array('{no}' => $order->order_no, '{status}' => $statusLabel), $order),
                $custHtml
            );
        }
        $this->load->library('ec_whatsapp');
        $this->ec_whatsapp->notify_item($order, $item);
    }

    public function notify_status($order)
    {
        $order = $this->hydrate_order($order);
        if (!$order) {
            return false;
        }
        $this->load->library('ec_mail');
        $items = $this->items($order->id);
        $statusLabel = $this->status_label($order->status);
        $isNew = ($order->status === 'pending');

        if (self::customer_wants_email($order)) {
            $this->ec_mail->send(
                $order->customer_email,
                $this->order_mail_ui($isNew ? 'order.mail_customer_subject' : 'order.mail_status_subject', array(
                    '{no}' => $order->order_no,
                    '{status}' => $statusLabel,
                    '{store}' => isset($order->store_name) ? $order->store_name : '',
                ), $order),
                $this->customer_order_email_html($order, $items, $isNew)
            );
        }

        $this->load->library('ec_whatsapp');
        $this->ec_whatsapp->notify_order($order, $items);
        return true;
    }

    protected function hydrate_order($order)
    {
        if (!$order) {
            return $order;
        }
        if (empty($order->store_name) && !empty($order->id)) {
            $fresh = $this->get((int) $order->id);
            if ($fresh) {
                return $fresh;
            }
        }
        return $order;
    }

    protected function status_label($status)
    {
        $labels = self::statuses();
        return isset($labels[$status]) ? $labels[$status] : $status;
    }

    protected function mail_h($value)
    {
        return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
    }

    protected function order_mail_locale($order)
    {
        $store = null;
        if (!empty($order->store_id) && $this->db->table_exists('stores')) {
            $store = $this->db->where('id', (int) $order->store_id)->get('stores')->row();
        }
        if (function_exists('storefront_ui_locale')) {
            return storefront_ui_locale($store, array());
        }
        return 'en';
    }

    protected function order_mail_ui($key, $replace, $order)
    {
        $locale = $this->order_mail_locale($order);
        $english = $key;
        if (function_exists('storefront_ui_catalog')) {
            $catalog = storefront_ui_catalog();
            if (isset($catalog[$key]['d'])) {
                $english = $catalog[$key]['d'];
            }
        }
        $value = $english;
        if ($locale === 'sv' && function_exists('storefront_ui_locale_strings')) {
            $sv = storefront_ui_locale_strings('sv');
            if (isset($sv[$key])) {
                $value = $sv[$key];
            }
        }
        if (!empty($replace)) {
            $value = strtr($value, $replace);
        }
        return $value;
    }

    protected function staff_emails($order, $items)
    {
        $emails = array(
            isset($order->store_email) ? $order->store_email : '',
            platform_setting('admin_notify_email', ''),
        );
        if ($this->db->table_exists('users')) {
            $admin = $this->db->where('roleID', 1)->where('status', 1)->get('users')->row();
            if ($admin && !empty($admin->email)) {
                $emails[] = $admin->email;
            }
        }
        $storeId = isset($order->store_id) ? (int) $order->store_id : 0;
        if ($storeId > 0 && $this->db->table_exists('staff')) {
            $staffRows = $this->db->where('store_id', $storeId)->where('status', 1)->get('staff')->result();
            foreach ($staffRows as $staffRow) {
                if (!empty($staffRow->email)) {
                    $emails[] = $staffRow->email;
                }
            }
        }
        $userIds = array();
        foreach ((array) $items as $item) {
            if (!empty($item->ecommerce_user_id)) {
                $userIds[(int) $item->ecommerce_user_id] = true;
            }
        }
        if ($userIds && $this->db->table_exists('users')) {
            $users = $this->db->where_in('UserID', array_keys($userIds))->get('users')->result();
            foreach ($users as $user) {
                if (!empty($user->email)) {
                    $emails[] = $user->email;
                }
            }
        }
        return $emails;
    }

    protected function staff_customer_block($order)
    {
        return '<table cellpadding="6" cellspacing="0" border="0" style="border-collapse:collapse;margin:0 0 16px">'
            . '<tr><td><strong>Name</strong></td><td>' . $this->mail_h($order->customer_name) . '</td></tr>'
            . '<tr><td><strong>Email</strong></td><td>' . $this->mail_h($order->customer_email) . '</td></tr>'
            . '<tr><td><strong>Phone / WhatsApp</strong></td><td>' . $this->mail_h($order->customer_phone) . '</td></tr>'
            . '<tr><td valign="top"><strong>Shipping address</strong></td><td>' . nl2br($this->mail_h($order->shipping_address)) . '</td></tr>'
            . (!empty($order->billing_address) ? '<tr><td valign="top"><strong>Billing address</strong></td><td>' . nl2br($this->mail_h($order->billing_address)) . '</td></tr>' : '')
            . '</table>';
    }

    protected function staff_order_email_html($order, $items, $isNew)
    {
        $statusLabel = $this->status_label($order->status);
        $rows = '';
        foreach ((array) $items as $item) {
            $sku = !empty($item->sku) ? $this->mail_h($item->sku) : '—';
            $rows .= '<tr>'
                . '<td>' . $this->mail_h($item->product_name) . '<br><small>SKU: ' . $sku . '</small></td>'
                . '<td align="center">' . (int) $item->qty . '</td>'
                . '<td align="right">' . format_money((float) $item->unit_price, $order->currency) . '</td>'
                . '<td align="right">' . format_money((float) $item->line_total, $order->currency) . '</td>'
                . '</tr>';
        }
        $pay = trim((string) (isset($order->payment_method) ? $order->payment_method : ''));
        $payStatus = trim((string) (isset($order->payment_status) ? $order->payment_status : ''));
        $heading = $isNew
            ? 'New order ' . $this->mail_h($order->order_no) . ' — place with supplier'
            : 'Order ' . $this->mail_h($order->order_no) . ' — ' . $this->mail_h($statusLabel);
        $html = '<div style="font-family:Arial,sans-serif;font-size:14px;color:#222;line-height:1.5">'
            . '<h2 style="margin:0 0 8px">' . $heading . '</h2>'
            . '<p>Store: <strong>' . $this->mail_h(isset($order->store_name) ? $order->store_name : '') . '</strong>'
            . (!empty($order->store_domain) ? ' (' . $this->mail_h($order->store_domain) . ')' : '')
            . '</p>'
            . '<p>Status: <strong>' . $this->mail_h($statusLabel) . '</strong>'
            . ($pay !== '' ? ' · Payment: <strong>' . $this->mail_h($pay) . '</strong>' . ($payStatus !== '' ? ' (' . $this->mail_h($payStatus) . ')' : '') : '')
            . '</p>'
            . ($isNew ? '<p style="background:#fff8e1;padding:10px 12px;border-radius:6px">Use the customer name, phone and address below to place this order with the supplier.</p>' : '')
            . '<h3 style="margin:18px 0 8px">Customer</h3>'
            . $this->staff_customer_block($order)
            . '<h3 style="margin:18px 0 8px">Items</h3>'
            . '<table cellpadding="8" cellspacing="0" border="1" style="border-collapse:collapse;width:100%;max-width:640px">'
            . '<thead><tr><th align="left">Product</th><th>Qty</th><th align="right">Unit</th><th align="right">Total</th></tr></thead>'
            . '<tbody>' . $rows . '</tbody></table>'
            . '<p>Subtotal: <strong>' . format_money((float) $order->subtotal, $order->currency) . '</strong>';
        if (!empty($order->shipping_amount) && (float) $order->shipping_amount > 0) {
            $html .= '<br>Shipping × ' . (int) (isset($order->shipping_qty) ? $order->shipping_qty : 0) . ': <strong>' . format_money((float) $order->shipping_amount, $order->currency) . '</strong>';
        }
        if (!empty($order->vat_amount) && (float) $order->vat_amount > 0) {
            $html .= '<br>VAT (' . number_format((float) $order->vat_percent, 2) . '%): <strong>' . format_money((float) $order->vat_amount, $order->currency) . '</strong>';
        }
        $html .= '<br>Grand total: <strong>' . format_money((float) $order->total, $order->currency) . '</strong></p>'
            . '</div>';
        return $html;
    }

    protected function customer_order_email_html($order, $items, $isNew)
    {
        $statusLabel = $this->status_label($order->status);
        $storeName = isset($order->store_name) ? $order->store_name : '';
        $rows = '';
        foreach ((array) $items as $item) {
            $rows .= '<tr>'
                . '<td>' . $this->mail_h($item->product_name) . '</td>'
                . '<td align="center">' . (int) $item->qty . '</td>'
                . '<td align="right">' . format_money((float) $item->line_total, $order->currency) . '</td>'
                . '</tr>';
        }
        $intro = $this->order_mail_ui($isNew ? 'order.mail_customer_intro' : 'order.mail_status_intro', array(
            '{store}' => $storeName,
            '{no}' => $order->order_no,
            '{status}' => $statusLabel,
        ), $order);
        $html = '<div style="font-family:Arial,sans-serif;font-size:14px;color:#222;line-height:1.5">'
            . '<p>' . $this->mail_h($intro) . '</p>'
            . '<p><strong>' . $this->mail_h($this->order_mail_ui('order.heading', array('{order}' => $order->order_no), $order)) . '</strong></p>'
            . '<p>' . $this->mail_h($this->order_mail_ui('order.is_status', array('{status}' => $statusLabel), $order)) . '</p>'
            . '<table cellpadding="8" cellspacing="0" border="1" style="border-collapse:collapse;width:100%;max-width:560px">'
            . '<thead><tr><th align="left">' . $this->mail_h($this->order_mail_ui('order.items', array(), $order)) . '</th><th>Qty</th><th align="right">Total</th></tr></thead>'
            . '<tbody>' . $rows . '</tbody></table>'
            . '<p>' . $this->mail_h($this->order_mail_ui('cart.total', array(), $order)) . ': <strong>' . format_money((float) $order->total, $order->currency) . '</strong></p>'
            . '<p><strong>' . $this->mail_h($this->order_mail_ui('order.shipping', array(), $order)) . '</strong><br>'
            . $this->mail_h($order->customer_name) . '<br>'
            . nl2br($this->mail_h($order->shipping_address))
            . (!empty($order->billing_address) ? '<br><br><strong>Billing</strong><br>' . nl2br($this->mail_h($order->billing_address)) : '')
            . '</p>'
            . '</div>';
        return $html;
    }

    public function store_stats($storeId)
    {
        $storeId = (int) $storeId;
        $orders = 0;
        if ($this->db->table_exists('store_orders')) {
            $orders = (int) $this->db->where('store_id', $storeId)->count_all_results('store_orders');
        }
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
                ->where_not_in('store_order_items.fulfillment_status', array('refunded', 'cancelled'))
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

    protected function ensure_shipping_columns()
    {
        if (!$this->db->table_exists('store_orders')) {
            return;
        }
        if (!$this->db->field_exists('shipping_amount', 'store_orders')) {
            $after = $this->db->field_exists('vat_amount', 'store_orders') ? 'vat_amount' : 'subtotal';
            $this->db->query('ALTER TABLE store_orders ADD COLUMN shipping_amount DECIMAL(12,2) NOT NULL DEFAULT 0 AFTER ' . $after);
        }
        if (!$this->db->field_exists('shipping_qty', 'store_orders')) {
            $this->db->query('ALTER TABLE store_orders ADD COLUMN shipping_qty INT NOT NULL DEFAULT 0 AFTER shipping_amount');
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

    public function ensure_item_columns()
    {
        if (!$this->db->table_exists('store_order_items')) {
            return;
        }
        $added = false;
        if (!$this->db->field_exists('fulfillment_status', 'store_order_items')) {
            $this->db->query("ALTER TABLE store_order_items ADD COLUMN fulfillment_status VARCHAR(30) NOT NULL DEFAULT 'pending'");
            $added = true;
        }
        if (!$this->db->field_exists('refund_reason', 'store_order_items')) {
            $this->db->query("ALTER TABLE store_order_items ADD COLUMN refund_reason VARCHAR(255) NOT NULL DEFAULT ''");
        }
        if (!$this->db->field_exists('refund_requested_at', 'store_order_items')) {
            $this->db->query('ALTER TABLE store_order_items ADD COLUMN refund_requested_at DATETIME NULL');
        }
        if (!$this->db->field_exists('status_updated_at', 'store_order_items')) {
            $this->db->query('ALTER TABLE store_order_items ADD COLUMN status_updated_at DATETIME NULL');
        }
        if (!$this->db->field_exists('supplier_order_no', 'store_order_items')) {
            $this->db->query("ALTER TABLE store_order_items ADD COLUMN supplier_order_no VARCHAR(80) NOT NULL DEFAULT ''");
        }
        if (!$this->db->field_exists('tracking_number', 'store_order_items')) {
            $this->db->query("ALTER TABLE store_order_items ADD COLUMN tracking_number VARCHAR(80) NOT NULL DEFAULT ''");
        }
        if (!$this->db->field_exists('shipping_company', 'store_order_items')) {
            $this->db->query("ALTER TABLE store_order_items ADD COLUMN shipping_company VARCHAR(80) NOT NULL DEFAULT ''");
        }
        $this->db->query("CREATE TABLE IF NOT EXISTS order_item_status_logs (
            id INT(11) NOT NULL AUTO_INCREMENT,
            order_id INT(11) NOT NULL,
            item_id INT(11) NOT NULL,
            status VARCHAR(30) NOT NULL,
            note VARCHAR(255) NOT NULL DEFAULT '',
            created_by_type VARCHAR(30) NOT NULL DEFAULT '',
            created_by_id INT(11) NULL,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY order_id (order_id),
            KEY item_id (item_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

        if ($added && $this->db->table_exists('store_orders')) {
            $this->db->query("UPDATE store_order_items soi
                JOIN store_orders so ON so.id = soi.order_id
                SET soi.fulfillment_status = CASE so.status
                    WHEN 'confirmed' THEN 'processing'
                    WHEN 'processing' THEN 'processing'
                    WHEN 'shipped' THEN 'dispatching'
                    WHEN 'dispatching' THEN 'dispatching'
                    WHEN 'delivered' THEN 'delivered'
                    WHEN 'completed' THEN 'completed'
                    WHEN 'cancelled' THEN 'cancelled'
                    WHEN 'refunded' THEN 'refunded'
                    ELSE 'pending'
                END
                WHERE soi.fulfillment_status = 'pending' OR soi.fulfillment_status = ''");
        }
    }
}
