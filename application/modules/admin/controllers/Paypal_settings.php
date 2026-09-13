<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Paypal_settings extends CI_Controller {

    public function __construct()
    {
        parent::__construct();
        ec_require_admin();
    }

    public function index()
    {
        $data = array(
            'title' => 'Payment Gateway',
            'paypal_mode' => platform_setting('paypal_mode', 'sandbox'),
            'paypal_app' => platform_setting('paypal_app', 'default'),
            'paypal_currency' => platform_setting('paypal_currency', 'USD'),
            'paypal_sandbox_client_id' => platform_setting('paypal_sandbox_client_id', ''),
            'paypal_sandbox_secret' => platform_setting('paypal_sandbox_secret', ''),
            'paypal_nexa_sandbox_client_id' => platform_setting('paypal_nexa_sandbox_client_id', ''),
            'paypal_nexa_sandbox_secret' => platform_setting('paypal_nexa_sandbox_secret', ''),
            'paypal_live_client_id' => platform_setting('paypal_live_client_id', ''),
            'paypal_live_secret' => platform_setting('paypal_live_secret', ''),
            'paypal_live_email' => platform_setting('paypal_live_email', ''),
            'paypal_sandbox_region' => platform_setting('paypal_sandbox_region', 'AU'),
            'paypal_sandbox_buyer_email' => platform_setting('paypal_sandbox_buyer_email', ''),
        );
        $this->template->admin('paypal/index', $data);
    }

    public function save()
    {
        $mode = $this->input->post('paypal_mode') === 'live' ? 'live' : 'sandbox';
        $app = $this->input->post('paypal_app') === 'nexa' ? 'nexa' : 'default';
        $currency = strtoupper(trim((string) $this->input->post('paypal_currency')));
        if ($currency === '') {
            $currency = 'USD';
        }

        $fields = array(
            'paypal_mode' => $mode,
            'paypal_app' => $app,
            'paypal_currency' => $currency,
            'paypal_sandbox_client_id' => trim((string) $this->input->post('paypal_sandbox_client_id')),
            'paypal_sandbox_secret' => trim((string) $this->input->post('paypal_sandbox_secret')),
            'paypal_nexa_sandbox_client_id' => trim((string) $this->input->post('paypal_nexa_sandbox_client_id')),
            'paypal_nexa_sandbox_secret' => trim((string) $this->input->post('paypal_nexa_sandbox_secret')),
            'paypal_live_client_id' => trim((string) $this->input->post('paypal_live_client_id')),
            'paypal_live_secret' => trim((string) $this->input->post('paypal_live_secret')),
            'paypal_live_email' => trim((string) $this->input->post('paypal_live_email')),
            'paypal_sandbox_region' => strtoupper(trim((string) $this->input->post('paypal_sandbox_region'))),
            'paypal_sandbox_buyer_email' => trim((string) $this->input->post('paypal_sandbox_buyer_email')),
        );

        foreach ($fields as $key => $value) {
            $this->save_setting($key, $value);
        }

        $this->session->set_flashdata('success', 'Payment gateway settings saved.');
        redirect('/admin/paypal');
    }

    protected function save_setting($key, $value)
    {
        $exists = $this->db->where('setting_key', $key)->get('platform_settings')->row();
        if ($exists) {
            $this->db->where('setting_key', $key)->update('platform_settings', array('setting_value' => $value));
            return;
        }
        $this->db->insert('platform_settings', array(
            'setting_key' => $key,
            'setting_value' => $value,
        ));
    }
}
