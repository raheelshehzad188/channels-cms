<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Accounting_model extends CI_Model {

    public function __construct()
    {
        parent::__construct();
        $this->ensure_tables();
    }

    public function ensure_tables()
    {
        $this->db->query("CREATE TABLE IF NOT EXISTS accounting_wallets (
            id INT(11) NOT NULL AUTO_INCREMENT,
            party_type VARCHAR(20) NOT NULL,
            party_id INT(11) NOT NULL,
            balance DECIMAL(12,2) NOT NULL DEFAULT 0,
            currency VARCHAR(10) NOT NULL DEFAULT '',
            updated_at DATETIME NULL,
            PRIMARY KEY (id),
            UNIQUE KEY party (party_type, party_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

        $this->db->query("CREATE TABLE IF NOT EXISTS accounting_ledger (
            id INT(11) NOT NULL AUTO_INCREMENT,
            party_type VARCHAR(20) NOT NULL,
            party_id INT(11) NOT NULL,
            order_id INT(11) NULL,
            item_id INT(11) NULL,
            payout_id INT(11) NULL,
            entry_type VARCHAR(30) NOT NULL,
            amount DECIMAL(12,2) NOT NULL,
            currency VARCHAR(10) NOT NULL,
            balance_after DECIMAL(12,2) NOT NULL DEFAULT 0,
            note VARCHAR(255) NOT NULL DEFAULT '',
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY party (party_type, party_id),
            KEY order_id (order_id),
            KEY item_id (item_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

        $this->db->query("CREATE TABLE IF NOT EXISTS accounting_bank_details (
            id INT(11) NOT NULL AUTO_INCREMENT,
            party_type VARCHAR(20) NOT NULL,
            party_id INT(11) NOT NULL,
            account_holder VARCHAR(150) NOT NULL DEFAULT '',
            bank_name VARCHAR(150) NOT NULL DEFAULT '',
            account_number VARCHAR(64) NOT NULL DEFAULT '',
            iban VARCHAR(64) NOT NULL DEFAULT '',
            swift VARCHAR(32) NOT NULL DEFAULT '',
            routing_number VARCHAR(64) NOT NULL DEFAULT '',
            paypal_email VARCHAR(150) NOT NULL DEFAULT '',
            updated_at DATETIME NULL,
            PRIMARY KEY (id),
            UNIQUE KEY party (party_type, party_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

        $this->db->query("CREATE TABLE IF NOT EXISTS accounting_payout_requests (
            id INT(11) NOT NULL AUTO_INCREMENT,
            party_type VARCHAR(20) NOT NULL,
            party_id INT(11) NOT NULL,
            amount DECIMAL(12,2) NOT NULL,
            currency VARCHAR(10) NOT NULL,
            status VARCHAR(20) NOT NULL DEFAULT 'pending',
            bank_snapshot TEXT NULL,
            admin_note VARCHAR(255) NOT NULL DEFAULT '',
            requested_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            processed_at DATETIME NULL,
            processed_by INT(11) NULL,
            receipt VARCHAR(255) NOT NULL DEFAULT '',
            PRIMARY KEY (id),
            KEY party (party_type, party_id),
            KEY status (status)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

        if ($this->db->table_exists('accounting_payout_requests') && !$this->db->field_exists('receipt', 'accounting_payout_requests')) {
            $this->db->query("ALTER TABLE accounting_payout_requests ADD COLUMN receipt VARCHAR(255) NOT NULL DEFAULT '' AFTER processed_by");
        }

        if ($this->db->table_exists('store_permissions')) {
            $row = $this->db->where('role_slug', 'manager')->get('store_permissions')->row();
            if ($row) {
                $perms = json_decode($row->permissions, true);
                if (is_array($perms) && !in_array('accounting', $perms, true) && !in_array('*', $perms, true)) {
                    $perms[] = 'accounting';
                    $this->db->where('id', (int) $row->id)->update('store_permissions', array(
                        'permissions' => json_encode($perms),
                    ));
                }
            }
        }
    }

    public function party_currency($partyType, $partyId)
    {
        if ($partyType === 'store') {
            return store_currency((int) $partyId);
        }
        return platform_currency();
    }

    public function wallet($partyType, $partyId)
    {
        $row = $this->db
            ->where('party_type', $partyType)
            ->where('party_id', (int) $partyId)
            ->get('accounting_wallets')
            ->row();
        if ($row) {
            if ($row->currency === '') {
                $row->currency = $this->party_currency($partyType, $partyId);
            }
            return $row;
        }
        $currency = $this->party_currency($partyType, $partyId);
        $this->db->insert('accounting_wallets', array(
            'party_type' => $partyType,
            'party_id' => (int) $partyId,
            'balance' => 0,
            'currency' => $currency,
            'updated_at' => date('Y-m-d H:i:s'),
        ));
        return $this->wallet($partyType, $partyId);
    }

    public function ledger($partyType, $partyId, $limit = 50)
    {
        return $this->db
            ->where('party_type', $partyType)
            ->where('party_id', (int) $partyId)
            ->order_by('id', 'desc')
            ->limit((int) $limit)
            ->get('accounting_ledger')
            ->result();
    }

    public function bank($partyType, $partyId)
    {
        $row = $this->db
            ->where('party_type', $partyType)
            ->where('party_id', (int) $partyId)
            ->get('accounting_bank_details')
            ->row();
        if ($row) {
            return $row;
        }
        return (object) array(
            'account_holder' => '',
            'bank_name' => '',
            'account_number' => '',
            'iban' => '',
            'swift' => '',
            'routing_number' => '',
            'paypal_email' => '',
        );
    }

    public function save_bank($partyType, $partyId, $data)
    {
        $payload = array(
            'party_type' => $partyType,
            'party_id' => (int) $partyId,
            'account_holder' => trim((string) (isset($data['account_holder']) ? $data['account_holder'] : '')),
            'bank_name' => trim((string) (isset($data['bank_name']) ? $data['bank_name'] : '')),
            'account_number' => trim((string) (isset($data['account_number']) ? $data['account_number'] : '')),
            'iban' => trim((string) (isset($data['iban']) ? $data['iban'] : '')),
            'swift' => trim((string) (isset($data['swift']) ? $data['swift'] : '')),
            'routing_number' => trim((string) (isset($data['routing_number']) ? $data['routing_number'] : '')),
            'paypal_email' => trim((string) (isset($data['paypal_email']) ? $data['paypal_email'] : '')),
            'updated_at' => date('Y-m-d H:i:s'),
        );
        $existing = $this->db
            ->where('party_type', $partyType)
            ->where('party_id', (int) $partyId)
            ->get('accounting_bank_details')
            ->row();
        if ($existing) {
            $this->db->where('id', (int) $existing->id)->update('accounting_bank_details', $payload);
            return (int) $existing->id;
        }
        $this->db->insert('accounting_bank_details', $payload);
        return (int) $this->db->insert_id();
    }

    public function bank_is_complete($bank)
    {
        if (!$bank) {
            return false;
        }
        $holder = trim((string) $bank->account_holder);
        $name = trim((string) $bank->bank_name);
        $number = trim((string) $bank->account_number);
        $iban = trim((string) $bank->iban);
        $paypal = trim((string) $bank->paypal_email);
        return $holder !== '' && ($paypal !== '' || ($name !== '' && ($number !== '' || $iban !== '')));
    }

    public function add_ledger($partyType, $partyId, $amount, $entryType, $meta = array())
    {
        $wallet = $this->wallet($partyType, $partyId);
        $amount = round((float) $amount, 2);
        $balance = round((float) $wallet->balance + $amount, 2);
        $currency = !empty($meta['currency']) ? $meta['currency'] : $wallet->currency;
        $this->db->where('id', (int) $wallet->id)->update('accounting_wallets', array(
            'balance' => $balance,
            'currency' => $currency,
            'updated_at' => date('Y-m-d H:i:s'),
        ));
        $this->db->insert('accounting_ledger', array(
            'party_type' => $partyType,
            'party_id' => (int) $partyId,
            'order_id' => !empty($meta['order_id']) ? (int) $meta['order_id'] : null,
            'item_id' => !empty($meta['item_id']) ? (int) $meta['item_id'] : null,
            'payout_id' => !empty($meta['payout_id']) ? (int) $meta['payout_id'] : null,
            'entry_type' => $entryType,
            'amount' => $amount,
            'currency' => $currency,
            'balance_after' => $balance,
            'note' => isset($meta['note']) ? (string) $meta['note'] : '',
        ));
        return $balance;
    }

    public function already_credited($partyType, $partyId, $itemId)
    {
        if (!$itemId) {
            return false;
        }
        return $this->db
            ->where('party_type', $partyType)
            ->where('party_id', (int) $partyId)
            ->where('item_id', (int) $itemId)
            ->where('entry_type', 'credit_sale')
            ->count_all_results('accounting_ledger') > 0;
    }

    public function credit_from_item($order, $item)
    {
        if (!$order || !$item) {
            return;
        }
        $status = isset($item->fulfillment_status) ? $item->fulfillment_status : '';
        if ($status !== 'completed') {
            return;
        }

        $storeId = (int) $order->store_id;
        $markup = isset($item->store_markup) ? (float) $item->store_markup : 0.0;
        if ($storeId && $markup > 0 && !$this->already_credited('store', $storeId, $item->id)) {
            $this->add_ledger('store', $storeId, $markup, 'credit_sale', array(
                'order_id' => $order->id,
                'item_id' => $item->id,
                'currency' => $order->currency,
                'note' => 'Order ' . $order->order_no . ' item completed',
            ));
        }

        $ecomId = !empty($item->ecommerce_user_id) ? (int) $item->ecommerce_user_id : 0;
        $commission = isset($item->commission) ? (float) $item->commission : 0.0;
        if ($ecomId && $commission > 0 && !$this->already_credited('ecommerce', $ecomId, $item->id)) {
            $from = !empty($order->currency) ? $order->currency : platform_currency();
            $amount = convert_money($commission, $from, platform_currency());
            $this->add_ledger('ecommerce', $ecomId, $amount, 'credit_sale', array(
                'order_id' => $order->id,
                'item_id' => $item->id,
                'currency' => platform_currency(),
                'note' => 'Commission for order ' . $order->order_no,
            ));
        }
    }

    public function backfill_completed_items()
    {
        if (!$this->db->table_exists('store_order_items') || !$this->db->field_exists('fulfillment_status', 'store_order_items')) {
            return;
        }
        $rows = $this->db
            ->select('store_order_items.*, store_orders.store_id, store_orders.order_no, store_orders.currency, store_orders.id as order_pk')
            ->from('store_order_items')
            ->join('store_orders', 'store_orders.id = store_order_items.order_id')
            ->where('store_order_items.fulfillment_status', 'completed')
            ->get()
            ->result();
        foreach ($rows as $row) {
            $order = (object) array(
                'id' => (int) $row->order_pk,
                'store_id' => $row->store_id,
                'order_no' => $row->order_no,
                'currency' => $row->currency,
            );
            $this->credit_from_item($order, $row);
        }
    }

    public function request_payout($partyType, $partyId, $amount)
    {
        $wallet = $this->wallet($partyType, $partyId);
        $amount = round((float) $amount, 2);
        if ($amount <= 0) {
            return 'Enter an amount greater than zero.';
        }
        if ($amount > (float) $wallet->balance + 0.0001) {
            return 'Amount is more than the available wallet balance.';
        }
        $bank = $this->bank($partyType, $partyId);
        if (!$this->bank_is_complete($bank)) {
            return 'Add bank or PayPal account details before requesting a payout.';
        }

        $snapshot = json_encode(array(
            'account_holder' => $bank->account_holder,
            'bank_name' => $bank->bank_name,
            'account_number' => $bank->account_number,
            'iban' => $bank->iban,
            'swift' => $bank->swift,
            'routing_number' => $bank->routing_number,
            'paypal_email' => $bank->paypal_email,
        ));

        $this->db->insert('accounting_payout_requests', array(
            'party_type' => $partyType,
            'party_id' => (int) $partyId,
            'amount' => $amount,
            'currency' => $wallet->currency,
            'status' => 'pending',
            'bank_snapshot' => $snapshot,
        ));
        $payoutId = (int) $this->db->insert_id();
        $this->add_ledger($partyType, $partyId, -1 * $amount, 'debit_payout', array(
            'payout_id' => $payoutId,
            'currency' => $wallet->currency,
            'note' => 'Payout request #' . $payoutId,
        ));
        $payout = $this->get_payout($payoutId);
        if ($payout) {
            $this->load->library('ec_whatsapp');
            $this->ec_whatsapp->notify_payout($payout, 'requested');
        }
        return $payoutId;
    }

    public function get_payout($id)
    {
        return $this->db->where('id', (int) $id)->get('accounting_payout_requests')->row();
    }

    public function payouts($filters = array())
    {
        $this->db->from('accounting_payout_requests')->order_by('id', 'desc');
        if (!empty($filters['status'])) {
            $this->db->where('status', $filters['status']);
        }
        if (!empty($filters['party_type'])) {
            $this->db->where('party_type', $filters['party_type']);
        }
        if (!empty($filters['party_id'])) {
            $this->db->where('party_id', (int) $filters['party_id']);
        }
        $rows = $this->db->get()->result();
        foreach ($rows as $row) {
            $row->party_name = $this->party_name($row->party_type, $row->party_id);
            $row->bank = $row->bank_snapshot ? json_decode($row->bank_snapshot) : null;
        }
        return $rows;
    }

    public function process_payout($id, $status, $adminId, $note = '', $receipt = '')
    {
        $payout = $this->get_payout($id);
        if (!$payout || $payout->status !== 'pending') {
            return 'Payout request is not pending.';
        }
        if (!in_array($status, array('paid', 'rejected'), true)) {
            return 'Invalid payout action.';
        }
        if ($status === 'paid' && trim((string) $receipt) === '') {
            return 'Upload a payment slip before marking this payout as paid.';
        }

        $payload = array(
            'status' => $status,
            'admin_note' => trim((string) $note),
            'processed_at' => date('Y-m-d H:i:s'),
            'processed_by' => (int) $adminId,
        );
        if ($receipt !== '') {
            $payload['receipt'] = $receipt;
        }

        $this->db->where('id', (int) $id)->update('accounting_payout_requests', $payload);

        if ($status === 'rejected') {
            $this->add_ledger($payout->party_type, $payout->party_id, (float) $payout->amount, 'credit_payout_reject', array(
                'payout_id' => $payout->id,
                'currency' => $payout->currency,
                'note' => 'Payout request #' . $payout->id . ' rejected',
            ));
        }
        $payout->status = $status;
        $this->load->library('ec_whatsapp');
        $this->ec_whatsapp->notify_payout($payout, $status === 'paid' ? 'paid' : 'rejected');
        return true;
    }

    public function party_name($partyType, $partyId)
    {
        if ($partyType === 'store') {
            $row = $this->db->select('name')->where('id', (int) $partyId)->get('stores')->row();
            return $row ? $row->name : ('Store #' . $partyId);
        }
        $row = $this->db->where('UserID', (int) $partyId)->get('users')->row();
        if (!$row) {
            return 'User #' . $partyId;
        }
        $name = trim(($row->first_name ?? '') . ' ' . ($row->last_name ?? ''));
        return $name !== '' ? $name : ($row->uname ?? ('User #' . $partyId));
    }

    public function pending_payout_total($partyType, $partyId)
    {
        $row = $this->db
            ->select('SUM(amount) as total', false)
            ->where('party_type', $partyType)
            ->where('party_id', (int) $partyId)
            ->where('status', 'pending')
            ->get('accounting_payout_requests')
            ->row();
        return $row && $row->total ? (float) $row->total : 0.0;
    }

    public function paid_payout_total($partyType, $partyId)
    {
        $row = $this->db
            ->select('SUM(amount) as total', false)
            ->where('party_type', $partyType)
            ->where('party_id', (int) $partyId)
            ->where('status', 'paid')
            ->get('accounting_payout_requests')
            ->row();
        return $row && $row->total ? (float) $row->total : 0.0;
    }

    public function party_summary($partyType, $partyId)
    {
        $this->load->model('Ec_order_model');
        $this->Ec_order_model->ensure_item_columns();
        $wallet = $this->wallet($partyType, $partyId);
        $currency = $wallet->currency;
        $platform = platform_currency();

        $vat = 0.0;
        $earnings = 0.0;
        $waiting = 0.0;
        $vatPlatform = 0.0;
        $earningsPlatform = 0.0;
        $waitingPlatform = 0.0;

        if ($partyType === 'store') {
            $orders = $this->db
                ->where('store_id', (int) $partyId)
                ->where_not_in('status', array('cancelled'))
                ->get('store_orders')
                ->result();
            $seenVat = array();
            foreach ($orders as $order) {
                if (empty($seenVat[$order->id]) && $this->order_counts_for_vat($order)) {
                    $vat += (float) $order->vat_amount;
                    $vatPlatform += $this->amount_in_platform($order, (float) $order->vat_amount);
                    $seenVat[$order->id] = true;
                }
            }
            if ($this->db->table_exists('store_order_items')) {
                $items = $this->db
                    ->select('store_order_items.*, store_orders.currency, store_orders.fx_rate, store_orders.status as order_status')
                    ->from('store_order_items')
                    ->join('store_orders', 'store_orders.id = store_order_items.order_id')
                    ->where('store_orders.store_id', (int) $partyId)
                    ->where_not_in('store_orders.status', array('cancelled'))
                    ->get()
                    ->result();
                foreach ($items as $item) {
                    if (in_array($item->fulfillment_status, array('refunded', 'cancelled'), true)) {
                        continue;
                    }
                    $amount = (float) $item->store_markup;
                    $earnings += $amount;
                    $earningsPlatform += $this->amount_in_platform($item, $amount);
                    if ($item->fulfillment_status !== 'completed') {
                        $waiting += $amount;
                        $waitingPlatform += $this->amount_in_platform($item, $amount);
                    }
                }
            }
        } else {
            if ($this->db->table_exists('store_order_items')) {
                $items = $this->db
                    ->select('store_order_items.*, store_orders.currency, store_orders.fx_rate, store_orders.vat_amount, store_orders.subtotal, store_orders.payment_status, store_orders.status as order_status')
                    ->from('store_order_items')
                    ->join('store_orders', 'store_orders.id = store_order_items.order_id')
                    ->where('store_order_items.ecommerce_user_id', (int) $partyId)
                    ->where_not_in('store_orders.status', array('cancelled'))
                    ->get()
                    ->result();
                foreach ($items as $item) {
                    if (in_array($item->fulfillment_status, array('refunded', 'cancelled'), true)) {
                        continue;
                    }
                    $from = !empty($item->currency) ? $item->currency : $platform;
                    $commission = (float) $item->commission;
                    $earnings += convert_money($commission, $from, $platform);
                    if ($item->fulfillment_status !== 'completed') {
                        $waiting += convert_money($commission, $from, $platform);
                    }
                    if ($this->order_counts_for_vat($item) && (float) $item->subtotal > 0) {
                        $share = ((float) $item->vat_amount) * ((float) $item->line_total / (float) $item->subtotal);
                        $vat += convert_money($share, $from, $platform);
                    }
                }
                $earningsPlatform = $earnings;
                $waitingPlatform = $waiting;
                $vatPlatform = $vat;
                $currency = $platform;
            }
        }

        return array(
            'party_type' => $partyType,
            'party_id' => (int) $partyId,
            'name' => $this->party_name($partyType, $partyId),
            'currency' => $currency,
            'vat' => round($vat, 2),
            'earnings' => round($earnings, 2),
            'waiting' => round($waiting, 2),
            'wallet' => round((float) $wallet->balance, 2),
            'pending_payout' => round($this->pending_payout_total($partyType, $partyId), 2),
            'paid_out' => round($this->paid_payout_total($partyType, $partyId), 2),
            'vat_platform' => round($vatPlatform, 2),
            'earnings_platform' => round($earningsPlatform, 2),
            'waiting_platform' => round($waitingPlatform, 2),
            'wallet_platform' => convert_money((float) $wallet->balance, $wallet->currency, $platform),
        );
    }

    public function store_summaries()
    {
        if (!$this->db->table_exists('stores')) {
            return array();
        }
        $stores = $this->db->order_by('name', 'asc')->get('stores')->result();
        $rows = array();
        $totals = array(
            'vat_platform' => 0.0,
            'earnings_platform' => 0.0,
            'waiting_platform' => 0.0,
            'wallet_platform' => 0.0,
        );
        foreach ($stores as $store) {
            $row = $this->party_summary('store', $store->id);
            $rows[] = $row;
            $totals['vat_platform'] += $row['vat_platform'];
            $totals['earnings_platform'] += $row['earnings_platform'];
            $totals['waiting_platform'] += $row['waiting_platform'];
            $totals['wallet_platform'] += $row['wallet_platform'];
        }
        return array('rows' => $rows, 'totals' => $totals);
    }

    public function ecommerce_summaries()
    {
        if (!$this->db->table_exists('users')) {
            return array();
        }
        $users = $this->db->where('roleID', ROLE_ECOMMERCE)->order_by('first_name', 'asc')->get('users')->result();
        $rows = array();
        foreach ($users as $user) {
            $rows[] = $this->party_summary('ecommerce', $user->UserID);
        }
        return $rows;
    }

    public function platform_vat_total()
    {
        if (!$this->db->table_exists('store_orders')) {
            return 0.0;
        }
        $orders = $this->db->where_not_in('status', array('cancelled'))->get('store_orders')->result();
        $total = 0.0;
        foreach ($orders as $order) {
            if ($this->order_counts_for_vat($order)) {
                $total += $this->amount_in_platform($order, (float) $order->vat_amount);
            }
        }
        return round($total, 2);
    }

    public function countries()
    {
        if (!$this->db->table_exists('countries')) {
            return array();
        }
        return $this->db->order_by('name', 'asc')->get('countries')->result();
    }

    public function suppliers()
    {
        if (!$this->db->table_exists('suppliers')) {
            return array();
        }
        return $this->db->order_by('name', 'asc')->get('suppliers')->result();
    }

    protected function order_counts_for_vat($order)
    {
        $payment = isset($order->payment_status) ? $order->payment_status : 'paid';
        if ($payment === 'unpaid') {
            return false;
        }
        $status = isset($order->order_status) ? $order->order_status : (isset($order->status) ? $order->status : '');
        return $status !== 'cancelled';
    }

    protected function amount_in_platform($row, $amount)
    {
        $from = !empty($row->currency) ? $row->currency : platform_currency();
        if (!empty($row->fx_rate) && (float) $row->fx_rate > 0 && strtoupper($from) !== strtoupper(platform_currency())) {
            return round($amount * (float) $row->fx_rate, 2);
        }
        return convert_money($amount, $from, platform_currency());
    }
}
