<?php
defined('BASEPATH') OR exit('No direct script access allowed');

require_once APPPATH . 'modules/store/controllers/Store_base.php';

class Product_analytics extends Store_base {

    public function index()
    {
        $this->requireAuth();
        $this->requirePermission('products');
        $this->load->helper('store_analytics');
        $this->load->model('Store_analytics_model');
        $this->Store_analytics_model->ensure_schema();

        $range = trim((string) $this->input->get('range'));
        if ($range === '') {
            $range = 'week';
        }
        $allowed = array('day', 'today', 'week', 'month', 'year', '7d', '30d');
        if (!in_array($range, $allowed, true)) {
            $range = 'week';
        }
        $filters = array(
            'range' => $range,
            'store_id' => (int) $this->store->id,
        );
        $data = $this->Store_analytics_model->product_dashboard($filters);
        $this->template->store('product_analytics/index', $this->viewData(array_merge($data, array(
            'page' => 'Product analytics',
            'title' => 'Product analytics',
            'filters' => $filters,
            'is_admin' => false,
            'base_url_path' => 'store/product-analytics',
            'countries' => array(),
            'stores' => array(),
        ))));
    }
}
