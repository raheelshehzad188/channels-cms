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
                'discount_enabled', 'discount_percent', 'discount_scope', 'discount_category_id',
            );
            foreach ($keys as $key) {
                $value = $this->input->post($key);
                if ($key === 'shipping_enabled') {
                    $value = $value ? '1' : '0';
                }
                if ($key === 'shipping_flat_rate') {
                    $value = number_format(max(0, (float) $value), 2, '.', '');
                }
                if ($key === 'hide_empty_subcategories') {
                    $value = $value ? '1' : '0';
                }
                if ($key === 'product_detail_design') {
                    $value = $value === 'new' ? 'new' : 'old';
                }
                if ($key === 'discount_enabled') {
                    $value = $value ? '1' : '0';
                }
                if ($key === 'discount_percent') {
                    $value = (string) max(0, min(90, (float) $value));
                }
                if ($key === 'discount_scope') {
                    $value = in_array($value, array('store', 'category', 'subcategory'), true) ? $value : 'store';
                }
                if ($key === 'discount_category_id') {
                    $scope = $this->input->post('discount_scope');
                    if ($scope === 'category' || $scope === 'subcategory') {
                        $value = (string) max(0, (int) $value);
                    } else {
                        $value = '0';
                    }
                }
                $this->Store_settings_model->set($storeId, $key, $value);
            }

            ensure_store_pricing_columns();
            $oldPlus = store_price_plus_amount($this->store);
            $plus = max(0, (float) $this->input->post('price_plus_amount'));
            $autoAdd = $this->input->post('auto_add_products') ? 1 : 0;
            $wantRecalc = $this->input->post('recalculate_prices') ? true : abs($plus - $oldPlus) > 0.001;
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

            $repriced = 0;
            if ($wantRecalc && function_exists('ec_recalculate_store_listing_prices')) {
                $repriced = ec_recalculate_store_listing_prices($storeId);
            }

            if ($wantRecalc) {
                $this->session->set_flashdata('success', 'Settings saved. ' . (int) $repriced . ' product prices were recalculated from cost + plus amount.');
            } else {
                $this->session->set_flashdata('success', 'Settings saved successfully.');
            }
            redirect('store/settings');
        }

        $settings = $this->Store_settings_model->getAll($storeId);
        $this->load->model('Ec_category_model');
        $countryId = (int) (isset($this->store->country_id) ? $this->store->country_id : 0);

        $this->template->store('settings/index', $this->viewData(array(
            'page' => 'Store Settings',
            'settings' => $settings,
            'category_tree' => $this->Ec_category_model->tree_for_country($countryId),
        )));
    }

    public function texts()
    {
        $this->requireAuth();
        $this->requirePermission('settings');

        $storeId = (int) $this->store->id;
        $this->store = $this->db->where('id', $storeId)->get('stores')->row();
        $this->tenant->set_store($this->store);

        if ($this->input->post('seed_swedish')) {
            storefront_ui_seed_store($storeId, 'sv');
            $this->store = $this->db->where('id', $storeId)->get('stores')->row();
            $this->tenant->set_store($this->store);
            $this->session->set_flashdata('success', 'Swedish storefront texts loaded. You can edit them below.');
            redirect('store/settings/texts');
        }

        if ($this->input->post()) {
            $locale = strtolower(trim((string) $this->input->post('ui_locale')));
            if (!in_array($locale, array('en', 'sv'), true)) {
                $locale = storefront_ui_locale($this->store);
            }
            $this->Store_settings_model->set($storeId, 'ui.locale', $locale);
            $posted = $this->input->post('ui');
            if (is_array($posted)) {
                $catalog = storefront_ui_catalog();
                foreach ($catalog as $key => $meta) {
                    if (!array_key_exists($key, $posted)) {
                        continue;
                    }
                    $this->Store_settings_model->set($storeId, 'ui.' . $key, $posted[$key]);
                }
            }
            $this->store = $this->db->where('id', $storeId)->get('stores')->row();
            if ($this->db->field_exists('language', 'stores')) {
                $this->db->where('id', $storeId)->update('stores', array('language' => $locale));
                $this->store = $this->db->where('id', $storeId)->get('stores')->row();
            }
            $this->tenant->set_store($this->store);
            $this->session->set_flashdata('success', 'Storefront texts saved.');
            redirect('store/settings/texts');
        }

        $this->template->store('settings/texts', $this->viewData(array(
            'page' => 'Storefront texts',
            'ui_groups' => storefront_ui_groups(),
            'ui_locale' => storefront_ui_locale($this->store, $this->tenant->get_settings()),
        )));
    }
}
