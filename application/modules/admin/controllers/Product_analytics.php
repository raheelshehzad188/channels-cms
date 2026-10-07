<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Product_analytics extends CI_Controller {

    public function __construct()
    {
        parent::__construct();
        ec_require_admin();
        $this->load->helper('store_analytics');
        $this->load->model('Store_analytics_model');
        $this->load->model('Country_model');
        $this->load->model('Store_listing_model');
        $this->Store_analytics_model->ensure_schema();
    }

    public function index()
    {
        $filters = $this->filters();
        $data = $this->Store_analytics_model->product_dashboard($filters);
        $data['title'] = 'Product Analytics';
        $data['filters'] = $filters;
        $data['is_admin'] = true;
        $data['base_url_path'] = 'admin/product-analytics';
        $data['countries'] = $this->Country_model->all();
        $data['stores'] = $this->Store_listing_model->filter_stores($filters['country_id']);
        $this->template->admin('product_analytics/index', $data);
    }

    public function stores()
    {
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

    protected function filters()
    {
        $range = trim((string) $this->input->get('range'));
        if ($range === '') {
            $range = 'week';
        }
        $allowed = array('day', 'today', 'week', 'month', 'year', '7d', '30d');
        if (!in_array($range, $allowed, true)) {
            $range = 'week';
        }
        $countryId = (int) $this->input->get('country_id');
        $storeId = (int) $this->input->get('store_id');
        $stores = $this->Store_listing_model->filter_stores($countryId);
        if ($storeId) {
            $valid = false;
            foreach ($stores as $store) {
                if ((int) $store->id === $storeId) {
                    $valid = true;
                    break;
                }
            }
            if (!$valid) {
                $storeId = 0;
            }
        }
        return array(
            'range' => $range,
            'country_id' => $countryId,
            'store_id' => $storeId,
        );
    }
}
