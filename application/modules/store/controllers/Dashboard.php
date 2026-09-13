<?php
defined('BASEPATH') OR exit('No direct script access allowed');

require_once APPPATH . 'modules/store/controllers/Store_base.php';

class Dashboard extends Store_base {

    public function index()
    {
        $this->requireAuth();
        $this->requirePermission('dashboard');

        $this->load->model('Store_product_model');
        $this->load->model('Ec_order_model');
        $mine = $this->Store_product_model->mine($this->store->id);
        $statsDb = $this->Ec_order_model->store_stats($this->store->id);
        $recentOrders = $this->Ec_order_model->for_store($this->store->id, 5);
        $recentCustomers = $this->db
            ->where('store_id', (int) $this->store->id)
            ->order_by('id', 'desc')
            ->limit(5)
            ->get('store_customers')
            ->result();

        $stats = array(
            'products' => count($mine),
            'orders' => $statsDb['orders'],
            'customers' => $statsDb['customers'],
            'markup_earnings' => $statsDb['markup_earnings'],
            'low_stock' => 0,
        );

        $data = $this->viewData(array(
            'page' => 'Dashboard',
            'stats' => $stats,
            'recent_orders' => $recentOrders,
            'recent_customers' => $recentCustomers,
        ));

        $this->template->store('dashboard/index', $data);
    }
}
