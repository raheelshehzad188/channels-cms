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
        ensure_store_pricing_columns();

        $storeId = (int) $this->store->id;
        $this->store = $this->db->where('id', $storeId)->get('stores')->row();
        $this->tenant->set_store($this->store);

        if ($this->input->post()) {
            $keys = array(
                'general_store_name', 'general_contact_email', 'general_support_phone',
                'email_from_name', 'email_from_address', 'email_reply_to',
                'invoice_prefix', 'invoice_footer', 'invoice_logo_text',
                'tax_enabled', 'tax_rate', 'tax_label',
                'shipping_enabled', 'shipping_flat_rate',
                'notify_new_order', 'notify_low_stock', 'notify_new_customer',
                'hide_empty_subcategories', 'whatsapp_number', 'product_detail_design',
            );
            foreach ($keys as $key) {
                $value = $this->input->post($key);
                if ($key === 'hide_empty_subcategories') {
                    $value = $value ? '1' : '0';
                }
                if ($key === 'product_detail_design') {
                    $value = $value === 'new' ? 'new' : 'old';
                }
                $this->Store_settings_model->set($storeId, $key, $value);
            }

            ensure_store_pricing_columns();
            $plus = max(0, (float) $this->input->post('price_plus_amount'));
            $autoAdd = $this->input->post('auto_add_products') ? 1 : 0;
            $this->db->where('id', $storeId)->update('stores', array(
                'auto_add_products' => $autoAdd,
                'price_plus_amount' => $plus,
            ));
            $this->store = $this->db->where('id', $storeId)->get('stores')->row();
            $this->tenant->set_store($this->store);

            if ($autoAdd) {
                $this->load->model('Store_product_model');
                $this->Store_product_model->auto_add_available_to_store($this->store);
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
