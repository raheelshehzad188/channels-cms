<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Orders extends CI_Controller {

    public function __construct()
    {
        parent::__construct();
        ec_require_login();
        if (!ec_is_admin() && !ec_is_ecommerce()) {
            redirect('/login');
            exit;
        }
        $this->load->model('Ec_order_model');
        ec_ensure_currency_schema();
    }

    public function index()
    {
        if (ec_is_admin()) {
            $orders = $this->Ec_order_model->all();
            $waitingRows = $this->db
                ->select('platform_fee_total, commission_total, currency, fx_rate, platform_fee_platform, commission_platform')
                ->where('payout_status', 'waiting')
                ->get('store_orders')
                ->result();
            $platformWaiting = 0.0;
            $commissionWaiting = 0.0;
            foreach ($waitingRows as $row) {
                $platformWaiting += order_amount_in_platform($row, 'platform_fee');
                $commissionWaiting += order_amount_in_platform($row, 'commission');
            }
        } else {
            $orders = $this->Ec_order_model->for_ecommerce(ec_user()->UserID);
            $waitingRows = $this->db
                ->select('store_order_items.commission, store_orders.currency, store_orders.fx_rate')
                ->from('store_order_items')
                ->join('store_orders', 'store_orders.id = store_order_items.order_id')
                ->where('store_order_items.ecommerce_user_id', (int) ec_user()->UserID)
                ->where('store_orders.payout_status', 'waiting')
                ->get()
                ->result();
            $platformWaiting = 0.0;
            $commissionWaiting = 0.0;
            foreach ($waitingRows as $row) {
                $from = !empty($row->currency) ? $row->currency : platform_currency();
                $commissionWaiting += convert_money((float) $row->commission, $from, platform_currency());
            }
        }

        $data = array(
            'title' => 'Orders',
            'orders' => $orders,
            'statuses' => Ec_order_model::statuses(),
            'platform_waiting' => $platformWaiting,
            'commission_waiting' => $commissionWaiting,
            'platform_currency' => platform_currency(),
            'is_admin' => ec_is_admin(),
        );
        $this->template->admin('orders/index', $data);
    }

    public function view($id = 0)
    {
        $order = $this->Ec_order_model->get($id);
        if (!$order) {
            $this->session->set_flashdata('error', 'Order not found.');
            redirect('/admin/orders');
            return;
        }
        if (!ec_is_admin()) {
            $ok = false;
            foreach ($this->Ec_order_model->items($order->id) as $item) {
                if ((int) $item->ecommerce_user_id === (int) ec_user()->UserID) {
                    $ok = true;
                    break;
                }
            }
            if (!$ok) {
                $this->session->set_flashdata('error', 'You do not have access to this order.');
                redirect('/admin/orders');
                return;
            }
        }

        $data = array(
            'title' => 'Order ' . $order->order_no,
            'order' => $order,
            'items' => $this->Ec_order_model->items($order->id),
            'logs' => $this->Ec_order_model->logs($order->id),
            'statuses' => Ec_order_model::statuses(),
            'is_admin' => ec_is_admin(),
            'platform_currency' => platform_currency(),
        );
        $this->template->admin('orders/view', $data);
    }

    public function complete($id = 0)
    {
        ec_require_admin();
        $order = $this->Ec_order_model->get($id);
        if (!$order) {
            $this->session->set_flashdata('error', 'Order not found.');
            redirect('/admin/orders');
            return;
        }
        if ($order->status === 'cancelled') {
            $this->session->set_flashdata('error', 'Cancelled orders cannot be completed.');
            redirect('/admin/orders/view/' . $id);
            return;
        }
        $updated = $this->Ec_order_model->update_status(
            $order->id,
            'completed',
            'Super Admin marked order complete. Platform and ecommerce payouts released.',
            'admin',
            ec_user()->UserID
        );
        $this->Ec_order_model->notify_status($updated);
        $this->session->set_flashdata('success', 'Order completed. Waiting payouts released and emails sent.');
        redirect('/admin/orders/view/' . $id);
    }

    public function status($id = 0)
    {
        ec_require_admin();
        $status = trim((string) $this->input->post('status'));
        $allowed = array_keys(Ec_order_model::statuses());
        if (!in_array($status, $allowed, true)) {
            $this->session->set_flashdata('error', 'Invalid status.');
            redirect('/admin/orders/view/' . $id);
            return;
        }
        $note = trim((string) $this->input->post('note')) ?: ('Admin updated status to ' . $status);
        $updated = $this->Ec_order_model->update_status($id, $status, $note, 'admin', ec_user()->UserID);
        if (!$updated) {
            $this->session->set_flashdata('error', 'Unable to update order.');
            redirect('/admin/orders');
            return;
        }
        $this->Ec_order_model->notify_status($updated);
        $this->session->set_flashdata('success', 'Order updated and emails sent.');
        redirect('/admin/orders/view/' . $id);
    }
}
