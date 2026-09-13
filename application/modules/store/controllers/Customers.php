<?php
defined('BASEPATH') OR exit('No direct script access allowed');

require_once APPPATH . 'modules/store/controllers/Store_base.php';

class Customers extends Store_base {

    public function index()
    {
        $this->requireAuth();
        $customers = $this->db
            ->select('store_customers.*, (SELECT COUNT(*) FROM store_orders WHERE store_orders.customer_id = store_customers.id) as order_count, (SELECT COALESCE(SUM(total),0) FROM store_orders WHERE store_orders.customer_id = store_customers.id AND store_orders.status != "cancelled") as spent', false)
            ->where('store_id', (int) $this->store->id)
            ->order_by('id', 'desc')
            ->get('store_customers')
            ->result();

        $this->template->store('customers/index', $this->viewData(array(
            'page' => 'Customers',
            'title' => 'Customers',
            'customers' => $customers,
        )));
    }

    public function view($id = 0)
    {
        $this->requireAuth();
        $customer = $this->db
            ->where('store_id', (int) $this->store->id)
            ->where('id', (int) $id)
            ->get('store_customers')
            ->row();
        if (!$customer) {
            $this->session->set_flashdata('error', 'Customer not found.');
            redirect('store/customers');
            return;
        }
        $orders = $this->db
            ->where('store_id', (int) $this->store->id)
            ->where('customer_id', (int) $id)
            ->order_by('id', 'desc')
            ->get('store_orders')
            ->result();

        $this->template->store('customers/view', $this->viewData(array(
            'page' => $customer->name,
            'title' => $customer->name,
            'customer' => $customer,
            'orders' => $orders,
        )));
    }
}
