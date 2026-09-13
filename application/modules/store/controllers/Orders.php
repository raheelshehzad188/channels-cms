<?php
defined('BASEPATH') OR exit('No direct script access allowed');

require_once APPPATH . 'modules/store/controllers/Store_base.php';

class Orders extends Store_base {

    public function __construct()
    {
        parent::__construct();
        $this->load->model('Ec_order_model');
    }

    public function index()
    {
        $this->requireAuth();
        $this->template->store('orders/index', $this->viewData(array(
            'page' => 'Orders',
            'title' => 'Orders',
            'orders' => $this->Ec_order_model->for_store($this->store->id),
            'statuses' => Ec_order_model::statuses(),
        )));
    }

    public function view($id = 0)
    {
        $this->requireAuth();
        $order = $this->Ec_order_model->get($id);
        if (!$order || (int) $order->store_id !== (int) $this->store->id) {
            $this->session->set_flashdata('error', 'Order not found.');
            redirect('store/orders');
            return;
        }
        $this->template->store('orders/view', $this->viewData(array(
            'page' => 'Order ' . $order->order_no,
            'title' => 'Order ' . $order->order_no,
            'order' => $order,
            'items' => $this->Ec_order_model->items($order->id),
            'logs' => $this->Ec_order_model->logs($order->id),
            'statuses' => Ec_order_model::statuses(),
            'store_actions' => array('confirmed', 'processing', 'shipped', 'delivered', 'cancelled'),
        )));
    }

    public function status($id = 0)
    {
        $this->requireAuth();
        $order = $this->Ec_order_model->get($id);
        if (!$order || (int) $order->store_id !== (int) $this->store->id) {
            $this->session->set_flashdata('error', 'Order not found.');
            redirect('store/orders');
            return;
        }

        $status = trim((string) $this->input->post('status'));
        $allowed = array('confirmed', 'processing', 'shipped', 'delivered', 'cancelled');
        if (!in_array($status, $allowed, true)) {
            $this->session->set_flashdata('error', 'Invalid status. Only Super Admin can mark completed.');
            redirect('store/orders/view/' . $id);
            return;
        }
        if ($order->status === 'completed') {
            $this->session->set_flashdata('error', 'Completed orders cannot be changed by the store.');
            redirect('store/orders/view/' . $id);
            return;
        }

        $note = trim((string) $this->input->post('note')) ?: ('Store updated status to ' . $status);
        $staffId = $this->staff ? $this->staff->id : 0;
        $updated = $this->Ec_order_model->update_status($order->id, $status, $note, 'store', $staffId);
        $this->Ec_order_model->notify_status($updated);
        $this->session->set_flashdata('success', 'Order status updated and emails sent.');
        redirect('store/orders/view/' . $id);
    }
}
