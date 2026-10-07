<?php
defined('BASEPATH') OR exit('No direct script access allowed');

require_once APPPATH . 'modules/store/controllers/Store_base.php';

class Meta_events extends Store_base {

    public function __construct()
    {
        parent::__construct();
        $this->load->model('Store_meta_event_model');
        $this->load->library(array('meta_commerce', 'meta_events_integration'));
        $this->Store_meta_event_model->ensure_schema();
    }

    public function index()
    {
        $this->requireAuth();
        $this->requirePermission('settings');
        $cfg = $this->Store_meta_event_model->public_config($this->store->id);
        $this->template->store('meta_events/index', $this->viewData(array(
            'page' => 'Meta Integration',
            'title' => 'Meta Integration',
            'config' => $cfg,
            'logs' => $this->Store_meta_event_model->recent($this->store->id, 50),
        )));
    }

    public function connect()
    {
        $this->manual_only();
    }

    public function callback()
    {
        $this->manual_only();
    }

    public function save_assets()
    {
        $this->manual_only();
    }

    public function disconnect()
    {
        $this->manual_only();
    }

    public function save_dataset()
    {
        $this->requireAuth();
        $this->requirePermission('settings');
        $this->session->set_flashdata('error', 'Save the Pixel ID and Events Manager token with Save Meta Integration.');
        redirect('store/settings/meta');
    }

    public function save()
    {
        $this->requireAuth();
        $this->requirePermission('settings');
        if (strtoupper((string) $this->input->method()) !== 'POST') {
            redirect('store/settings/meta');
            return;
        }
        $result = $this->Store_meta_event_model->save_integration($this->store->id, array(
            'pixel_enabled' => (int) $this->input->post('pixel_enabled') === 1,
            'capi_enabled' => (int) $this->input->post('capi_enabled') === 1,
            'pixel_id' => $this->input->post('pixel_id'),
            'capi_token' => $this->input->post('capi_token'),
            'ad_account_id' => $this->input->post('ad_account_id'),
            'test_event_code' => $this->input->post('test_event_code'),
        ));
        $this->load->library('channel_events');
        $this->channel_events->clear_meta_config_cache($this->store->id);
        if (empty($result['ok'])) {
            $this->session->set_flashdata('error', $this->safe_flash(isset($result['error']) ? $result['error'] : 'Meta settings could not be saved.'));
        } else {
            $this->session->set_flashdata('success', 'Meta integration saved for this store.');
        }
        redirect('store/settings/meta');
    }

    public function disconnect_capi()
    {
        $this->requireAuth();
        $this->requirePermission('settings');
        if (strtoupper((string) $this->input->method()) !== 'POST') {
            redirect('store/settings/meta');
            return;
        }
        $this->meta_events_integration->disconnect_capi($this->store);
        $this->session->set_flashdata('success', 'Conversions API token removed for this store.');
        redirect('store/settings/meta');
    }

    public function test_pixel()
    {
        $this->test();
    }

    public function test()
    {
        $this->requireAuth();
        $this->requirePermission('settings');
        if (strtoupper((string) $this->input->method()) !== 'POST') {
            redirect('store/settings/meta');
            return;
        }
        $cfg = $this->Store_meta_event_model->config($this->store->id);
        $pixel = isset($cfg['pixel_id']) ? preg_replace('/\D+/', '', (string) $cfg['pixel_id']) : '';
        if ($pixel === '') {
            $this->session->set_flashdata('error', 'Save this store’s Meta Pixel / Dataset ID first.');
            redirect('store/settings/meta');
            return;
        }
        if (empty($cfg['ready'])) {
            $this->session->set_flashdata('error', 'Pixel ' . $pixel . ' is saved. Turn on Conversions API and save an Events Manager access token before Meta can accept a server test.');
            redirect('store/settings/meta');
            return;
        }
        $result = $this->meta_events_integration->test_connection($this->store);
        if (!empty($result['ok'])) {
            $http = isset($result['http_code']) ? (int) $result['http_code'] : 0;
            $received = isset($result['events_received']) ? (int) $result['events_received'] : 0;
            $this->session->set_flashdata('success', 'Meta accepted the test PageView for dataset ' . $pixel . '. HTTP ' . $http . ', events received ' . $received . '.');
        } else {
            $err = isset($result['error']) ? $result['error'] : 'Connection failed.';
            $http = isset($result['http_code']) ? (int) $result['http_code'] : 0;
            $this->session->set_flashdata('error', 'Meta rejected the test. HTTP ' . $http . '. ' . $this->safe_flash($err));
        }
        redirect('store/settings/meta');
    }

    protected function manual_only()
    {
        $this->requireAuth();
        $this->requirePermission('settings');
        $this->session->set_flashdata('error', 'Facebook Login is not used for Meta Pixel or Conversions API. Enter this store’s Pixel ID, ad account, and Events Manager token on this page.');
        redirect('store/settings/meta');
    }

    protected function safe_flash($message)
    {
        return $this->Store_meta_event_model->sanitize_error($message);
    }
}
