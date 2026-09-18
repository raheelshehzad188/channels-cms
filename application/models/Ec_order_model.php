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
            'dispatching' => 'Dispatching',
            'delivered' => 'Delivered',
            'completed' => 'Completed',
            'refund_requested' => 'Refund requested',
            'refunded' => 'Refunded',
            'cancelled' => 'Cancelled',
        );
    }

    public static function item_statuses()
    {
        return array(
            'pending' => 'Pending',
            'processing' => 'Processing',
            'dispatching' => 'Dispatching',
            'delivered' => 'Delivered',
            'completed' => 'Completed',
            'refund_requested' => 'Refund requested',
            'refunded' => 'Refunded',
            'cancelled' => 'Cancelled',
        );
    }

    public static function ecommerce_next_status($current)
    {
        $map = array(
            'pending' => 'processing',
            'processing' => 'dispatching',
            'dispatching' => 'delivered',
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
        if (!empty($filters['supplier_id']) || !empty($filters['country_id'])) {
            $sql = 'store_orders.id IN (
                SELECT soi.order_id FROM store_order_items soi
                LEFT JOIN products p ON p.id = soi.product_id
                LEFT JOIN products sp ON sp.id = IFNULL(NULLIF(soi.source_product_id, 0), soi.product_id)
                WHERE 1=1';
            if (!empty($filters['ecommerce_user_id'])) {
                $sql .= ' AND soi.ecommerce_user_id = ' . (int) $filters['ecommerce_user_id'];
            }
            if (!empty($filters['supplier_id'])) {
                $sid = (int) $filters['supplier_id'];
                $sql .= ' AND (p.supplier_id = ' . $sid . ' OR sp.supplier_id = ' . $sid . ')';
            }
            if (!empty($filters['country_id'])) {
                $cid = (int) $filters['country_id'];
                $sql .= ' AND (p.country_id = ' . $cid . ' OR sp.country_id = ' . $cid . ' OR EXISTS (
                    SELECT 1 FROM stores st WHERE st.id = (SELECT o2.store_id FROM store_orders o2 WHERE o2.id = soi.order_id LIMIT 1) AND st.country_id = ' . $cid . '
                ))';
            }
            $sql .= ')';
            $this->db->where($sql, null, false);
        }
    }

    public function create_from_cart($store, $customer, $cartItems, $shipping, $payment = array())
    {
        $this->ensure_vat_columns();
        $this->ensure_payment_columns();
        $this->ensure_fx_columns();
        $this->ensure_item_columns();
        $currency = store_currency($store);
        $fxRate = currency_rate_to_platform($currency);
        $items = array();
        $subtotal = 0;
        $platformTotal = 0;
        $commissionTotal = 0;

        foreach ($cartItems as $product) {
            $qty = max(1, (int) $product->qty);
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

        if ($status === 'cancelled') {
            $this->bulk_set_open_items($orderId, 'cancelled', $note, $byType, $byId);
        } elseif ($status === 'completed') {
            $this->complete_delivered_items($orderId, $note, $byType, $byId, true);
        }

        return $this->get($orderId);
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

    public function update_item_status($itemId, $status, $note, $byType, $byId, $role = 'ecommerce')
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

        if ($role === 'ecommerce') {
            $next = self::ecommerce_next_status($current);
            $canRefund = ($status === 'refunded' && $current === 'refund_requested');
            $canReject = ($status === $item->fulfillment_status);
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

        $payload = array(
            'fulfillment_status' => $status,
            'status_updated_at' => date('Y-m-d H:i:s'),
        );
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
        return $this->get_item($itemId);
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
        return $this->get_item($itemId);
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
        $active = (int) $counts['pending'] + (int) $counts['processing'] + (int) $counts['dispatching'] + (int) $counts['delivered'] + (int) $counts['refund_requested'];
        $newStatus = $order->status;
        $payout = $order->payout_status;

        if ($active === 0) {
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
        } elseif ($counts['delivered'] > 0 && $counts['pending'] + $counts['processing'] + $counts['dispatching'] === 0) {
            $newStatus = 'delivered';
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

    public function notify_item_status($order, $item)
    {
        $this->load->library('ec_mail');
        $labels = self::item_statuses();
        $status = !empty($item->fulfillment_status) ? $item->fulfillment_status : 'pending';
        $statusLabel = isset($labels[$status]) ? $labels[$status] : $status;
        $html = '<div style="font-family:Arial,sans-serif;font-size:14px;color:#222">'
            . '<h2>Order ' . htmlspecialchars($order->order_no) . ' — item update</h2>'
            . '<p><strong>' . htmlspecialchars($item->product_name) . '</strong> is now <strong>' . htmlspecialchars($statusLabel) . '</strong>.</p>'
            . '<p>Qty: ' . (int) $item->qty . ' · ' . format_money((float) $item->line_total, $order->currency) . '</p>'
            . '</div>';
        $emails = array(
            $order->customer_email,
            $order->store_email,
            platform_setting('admin_notify_email', ''),
        );
        if (!empty($item->ecommerce_user_id)) {
            $user = $this->db->where('UserID', (int) $item->ecommerce_user_id)->get('users')->row();
            if ($user && !empty($user->email)) {
                $emails[] = $user->email;
            }
        }
        $this->ec_mail->send_many($emails, 'Order ' . $order->order_no . ': ' . $item->product_name . ' is ' . $statusLabel, $html);
        $this->load->library('ec_whatsapp');
        $this->ec_whatsapp->notify_item($order, $item);
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
        $this->load->library('ec_whatsapp');
        $this->ec_whatsapp->notify_order($order, $items);
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
