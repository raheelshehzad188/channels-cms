<?php
defined('BASEPATH') OR exit('No direct script access allowed');

require_once APPPATH . 'modules/store/controllers/Store_base.php';

class Settings extends Store_base {

    public function __construct()
    {
        parent::__construct();
        $this->load->model('Store_settings_model');
    }

    public function index()
    {
        $this->requireAuth();
        $this->requirePermission('settings');

        $storeId = (int) $this->store->id;

        if ($this->input->post()) {
            $keys = array(
                'general_store_name', 'general_contact_email', 'general_support_phone',
                'email_from_name', 'email_from_address', 'email_reply_to',
                'invoice_prefix', 'invoice_footer', 'invoice_logo_text',
                'tax_enabled', 'tax_rate', 'tax_label',
                'shipping_enabled', 'shipping_flat_rate',
                'notify_new_order', 'notify_low_stock', 'notify_new_customer',
            );
            foreach ($keys as $key) {
                $this->Store_settings_model->set($storeId, $key, $this->input->post($key));
            }
            $this->session->set_flashdata('success', 'Settings saved successfully.');
            redirect('store/settings');
        }

        $settings = $this->Store_settings_model->getAll($storeId);

        $this->template->store('settings/index', $this->viewData(array(
            'page' => 'Store Settings',
            'settings' => $settings,
        )));
    }
}
