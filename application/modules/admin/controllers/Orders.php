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
        $this->load->model('Country_model');
        $this->load->model('Store_listing_model');
        $this->load->model('User_model');
        ec_ensure_currency_schema();
    }

    public function index()
    {
        $isAdmin = ec_is_admin();
        $countryId = $isAdmin ? (int) $this->input->get('country_id') : 0;
        $storeId = $isAdmin ? (int) $this->input->get('store_id') : 0;
        $ecommerceUserId = $isAdmin ? (int) $this->input->get('ecommerce_user_id') : (int) ec_user()->UserID;
        $countries = $isAdmin ? $this->Country_model->all() : array();
        $stores = $isAdmin ? $this->Store_listing_model->filter_stores($countryId) : array();
        if ($storeId) {
            $validStore = false;
            foreach ($stores as $store) {
                if ((int) $store->id === $storeId) {
                    $validStore = true;
                    break;
                }
            }
            if (!$validStore) {
                $storeId = 0;
            }
        }
        $ecommerceUsers = $isAdmin ? $this->User_model->ecommerce_users() : array();
        if ($ecommerceUserId && $isAdmin) {
            $validUser = false;
            foreach ($ecommerceUsers as $user) {
                if ((int) $user->UserID === $ecommerceUserId) {
                    $validUser = true;
                    break;
                }
            }
            if (!$validUser) {
                $ecommerceUserId = 0;
            }
        }

        $filters = array();
        if ($countryId) {
            $filters['country_id'] = $countryId;
        }
        if ($storeId) {
            $filters['store_id'] = $storeId;
        }
        if ($ecommerceUserId) {
            $filters['ecommerce_user_id'] = $ecommerceUserId;
        }

        if ($isAdmin) {
            $orders = $this->Ec_order_model->all($filters);
            $orderItems = array();
        } else {
            $orders = array();
            $orderItems = $this->Ec_order_model->for_ecommerce_items((int) ec_user()->UserID, $filters);
        }
        $summary = $this->Ec_order_model->filter_summary($filters);

        $data = array(
            'title' => 'Orders',
            'orders' => $orders,
            'order_items' => $orderItems,
            'summary' => $summary,
            'statuses' => Ec_order_model::statuses(),
            'item_statuses' => Ec_order_model::item_statuses(),
            'platform_waiting' => $summary->platform_waiting,
            'commission_waiting' => $summary->commission_waiting,
            'platform_currency' => platform_currency(),
            'is_admin' => $isAdmin,
            'countries' => $countries,
            'stores' => $stores,
            'ecommerce_users' => $ecommerceUsers,
            'country_id' => $countryId,
            'store_id' => $storeId,
            'ecommerce_user_id' => $isAdmin ? $ecommerceUserId : 0,
            'shipping_companies' => Ec_order_model::shipping_companies(),
        );
        $this->template->admin('orders/index', $data);
    }

    public function stores()
    {
        ec_require_admin();
        $countryId = (int) $this->input->get('country_id');
        $rows = $this->Store_listing_model->filter_stores($countryId);
        $out = array();
        foreach ($rows as $row) {
            $out[] = array(
                'id' => (int) $row->id,
                'name' => $row->name,
            );
        }
        $this->output->set_content_type('application/json')->set_output(json_encode($out));
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
            'item_statuses' => Ec_order_model::item_statuses(),
            'is_admin' => ec_is_admin(),
            'ecommerce_user_id' => (int) ec_user()->UserID,
            'shipping_companies' => Ec_order_model::shipping_companies(),
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
        $this->Ec_order_model->update_status(
            $order->id,
            'completed',
            'Super Admin marked order complete. Platform and ecommerce payouts released.',
            'admin',
            ec_user()->UserID
        );
        $this->session->set_flashdata('success', 'Order completed. Waiting payouts released and notifications sent.');
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
        $this->session->set_flashdata('success', 'Order updated and notifications sent.');
        redirect('/admin/orders/view/' . $id);
    }

    public function item_status($itemId = 0)
    {
        if (strtoupper((string) $this->input->method()) !== 'POST') {
            redirect('/admin/orders');
            return;
        }
        $item = $this->Ec_order_model->get_item($itemId);
        if (!$item) {
            $this->session->set_flashdata('error', 'Item not found.');
            redirect('/admin/orders');
            return;
        }
        if (!ec_is_admin() && (int) $item->ecommerce_user_id !== (int) ec_user()->UserID) {
            $this->session->set_flashdata('error', 'You do not have access to this item.');
            redirect('/admin/orders');
            return;
        }

        $action = trim((string) $this->input->post('action'));
        $status = trim((string) $this->input->post('status'));
        $note = trim((string) $this->input->post('note'));
        $byType = ec_is_admin() ? 'admin' : 'ecommerce';
        $byId = (int) ec_user()->UserID;
        $role = $byType === 'admin' ? 'admin' : 'ecommerce';
        $updated = false;

        if ($action === 'reject_refund') {
            $updated = $this->Ec_order_model->reject_item_refund($itemId, $note ?: 'Refund request declined', $byType, $byId);
        } else {
            if ($status === '') {
                $current = !empty($item->fulfillment_status) ? $item->fulfillment_status : 'pending';
                $status = Ec_order_model::ecommerce_next_status($current);
            }
            if ($status === '') {
                $this->session->set_flashdata('error', 'This item has no next status.');
                redirect($this->item_status_redirect($item));
                return;
            }
            if ($role === 'ecommerce' && $status === 'dispatching' && trim((string) $this->input->post('supplier_order_no')) === '') {
                $this->session->set_flashdata('error', 'Enter the AliExpress / supplier order number first.');
                redirect($this->item_status_redirect($item));
                return;
            }
            if ($role === 'ecommerce' && $status === 'shipped') {
                $company = trim((string) $this->input->post('shipping_company'));
                if ($company === 'Other') {
                    $company = trim((string) $this->input->post('shipping_company_other'));
                }
                if (trim((string) $this->input->post('tracking_number')) === '' || $company === '') {
                    $this->session->set_flashdata('error', 'Enter tracking number and shipping company.');
                    redirect($this->item_status_redirect($item));
                    return;
                }
            }
            $updated = $this->Ec_order_model->update_item_status(
                $itemId,
                $status,
                $note,
                $byType,
                $byId,
                $role,
                array(
                    'supplier_order_no' => trim((string) $this->input->post('supplier_order_no')),
                    'tracking_number' => trim((string) $this->input->post('tracking_number')),
                    'shipping_company' => trim((string) $this->input->post('shipping_company')),
                    'shipping_company_other' => trim((string) $this->input->post('shipping_company_other')),
                )
            );
        }

        if (!$updated) {
            $this->session->set_flashdata('error', 'Unable to update this item.');
            redirect($this->item_status_redirect($item));
            return;
        }

        $this->session->set_flashdata('success', 'Item updated and notifications sent.');
        redirect($this->item_status_redirect($item));
    }

    protected function item_status_redirect($item)
    {
        $to = trim((string) $this->input->post('redirect'));
        if ($to === 'view') {
            return '/admin/orders/view/' . (int) $item->order_id;
        }
        return '/admin/orders';
    }
}
