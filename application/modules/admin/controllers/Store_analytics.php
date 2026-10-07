<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Store_analytics extends CI_Controller {

    public function __construct()
    {
        parent::__construct();
        ec_require_products();
        $this->load->helper(array('store_analytics', 'profit_calc'));
        $this->load->model('Store_analytics_model');
        $this->load->model('Store_model');
        $this->load->model('Country_model');
        $this->load->model('Product_review_model');
        $this->Store_analytics_model->ensure_schema();
    }

    protected function filters()
    {
        return array(
            'range' => trim((string) $this->input->get('range')) ?: 'today',
            'from' => trim((string) $this->input->get('from')),
            'to' => trim((string) $this->input->get('to')),
            'store_id' => (int) $this->input->get('store_id'),
            'country_code' => strtoupper(trim((string) $this->input->get('country_code'))),
            'product_id' => (int) $this->input->get('product_id'),
            'sort' => trim((string) $this->input->get('sort')),
            'dir' => trim((string) $this->input->get('dir')),
        );
    }

    protected function dashboard_payload($filters)
    {
        $summary = $this->Store_analytics_model->summary($filters);
        $countries = $this->Store_analytics_model->by_country($filters);
        $liveProducts = $this->Store_analytics_model->live_products($filters);
        $products = $this->Store_analytics_model->by_product($filters, 40);
        return array(
            'summary' => $summary,
            'online' => $summary['online'],
            'active_minutes' => $this->Store_analytics_model->get_settings()['active_minutes'],
            'feed' => $this->Store_analytics_model->live_feed($filters, 20),
            'live_sessions' => $this->Store_analytics_model->live_sessions($filters, 20),
            'live_products' => $liveProducts,
            'countries' => $countries,
            'stores' => $this->Store_analytics_model->by_store($filters),
            'products' => $products,
            'funnel' => array(
                array('label' => 'Visitors', 'value' => $summary['visitors']),
                array('label' => 'Product Views', 'value' => $summary['product_views']),
                array('label' => 'Add to Cart', 'value' => $summary['add_to_cart']),
                array('label' => 'Checkout', 'value' => $summary['checkout']),
                array('label' => 'Purchase', 'value' => $summary['orders']),
            ),
            'checkout' => array(
                'started' => $summary['checkout'],
                'payment_page' => $summary['payment_page'],
                'payment_attempt' => $summary['payment_attempt'],
                'purchases' => $summary['orders'],
                'cart_to_checkout' => $summary['cart_to_checkout'],
                'checkout_to_purchase' => $summary['checkout_to_purchase'],
            ),
            'sources' => $this->Store_analytics_model->by_source($filters),
            'campaigns' => $this->Store_analytics_model->by_campaign($filters),
            'devices' => $this->Store_analytics_model->by_device($filters),
            'trend' => $this->Store_analytics_model->trend($filters),
            'alerts' => $this->Store_analytics_model->alerts($summary, $liveProducts, $countries, $products),
        );
    }

    public function index()
    {
        $filters = $this->filters();
        $data = $this->dashboard_payload($filters);
        $data['title'] = 'Live Store Analytics';
        $data['filters'] = $filters;
        $data['all_stores'] = $this->db->table_exists('stores') ? $this->Store_model->all() : array();
        $data['all_countries'] = $this->Country_model->all();
        $data['settings'] = $this->Store_analytics_model->get_settings();
        list($start, $end) = $this->Store_analytics_model->range($filters);
        $reviewFilters = array(
            'store_id' => !empty($filters['store_id']) ? (int) $filters['store_id'] : 0,
            'country_code' => !empty($filters['country_code']) ? $filters['country_code'] : '',
            'product_id' => !empty($filters['product_id']) ? (int) $filters['product_id'] : 0,
            'start' => $start,
            'end' => $end,
        );
        $data['review_stats'] = $this->Product_review_model->admin_stats($reviewFilters);
        $data['reviews'] = $this->Product_review_model->admin_recent($reviewFilters, 40);
        $this->template->admin('store_analytics/index', $data);
    }

    public function live()
    {
        $filters = $this->filters();
        $payload = $this->dashboard_payload($filters);
        $this->output->set_content_type('application/json')->set_output(json_encode($payload));
    }

    public function session($sessionId = '')
    {
        $detail = $this->Store_analytics_model->session_detail($sessionId);
        if (!$detail) {
            show_404();
            return;
        }
        $this->load->view('inspinia/admin/store_analytics/session', $detail);
    }

    public function product($id = 0)
    {
        $filters = $this->filters();
        $detail = $this->Store_analytics_model->product_detail((int) $id, $filters);
        if (!$detail['product']) {
            show_404();
            return;
        }
        $this->load->view('inspinia/admin/store_analytics/product', array_merge($detail, array('filters' => $filters)));
    }

    public function save_settings()
    {
        $this->Store_analytics_model->save_settings($this->input->post());
        $this->session->set_flashdata('success', 'Live analytics settings saved.');
        redirect('admin/store-analytics');
    }

    public function clear_data()
    {
        if (strtoupper((string) $this->input->server('REQUEST_METHOD')) !== 'POST') {
            show_error('Method not allowed', 405);
            return;
        }
        if (!function_exists('ec_is_admin') || !ec_is_admin()) {
            show_error('Only Super Admin can clear analytics data.', 403);
            return;
        }
        $confirm = trim((string) $this->input->post('confirm'));
        if ($confirm !== 'CLEAR') {
            $this->session->set_flashdata('error', 'Type CLEAR to confirm wiping analytics data.');
            redirect('admin/store-analytics');
            return;
        }
        $cleared = $this->Store_analytics_model->clear_all_data();
        $this->session->set_flashdata(
            'success',
            'All store analytics data cleared (' . count($cleared) . ' tables). Fresh tracking starts now.'
        );
        redirect('admin/store-analytics');
    }
}
