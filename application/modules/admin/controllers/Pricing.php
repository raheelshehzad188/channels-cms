<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Pricing extends CI_Controller {

    public function __construct()
    {
        parent::__construct();
        ec_require_admin();
        $this->load->model('Country_model');
        ec_ensure_currency_schema();
    }

    public function index()
    {
        $countries = $this->Country_model->all();
        $platformCountryId = platform_country_id();
        $data = array(
            'title' => 'Pricing',
            'platform_fee' => platform_setting('platform_fee', '0'),
            'vat' => platform_setting('vat', '0'),
            'platform_country_id' => $platformCountryId,
            'platform_currency' => platform_currency(),
            'countries' => $countries,
            'rate_currencies' => $this->rate_currencies($countries, $platformCountryId),
            'rates' => $this->rates_map(),
        );
        $this->template->admin('pricing/index', $data);
    }

    public function save()
    {
        $this->form_validation->set_rules('platform_fee', 'Platform Fee', 'required|numeric');
        $this->form_validation->set_rules('vat', 'VAT', 'required|numeric');
        $this->form_validation->set_rules('platform_country_id', 'Platform Country', 'required|integer');

        if ($this->form_validation->run() === FALSE) {
            $this->session->set_flashdata('error', validation_errors());
            redirect('/admin/pricing');
            return;
        }

        $countryId = (int) $this->input->post('platform_country_id');
        $country = $this->Country_model->get($countryId);
        if (!$country) {
            $this->session->set_flashdata('error', 'Select a valid platform country.');
            redirect('/admin/pricing');
            return;
        }

        $this->save_setting('platform_country_id', (string) $countryId);
        $this->save_setting('platform_fee', number_format((float) $this->input->post('platform_fee'), 2, '.', ''));
        $this->save_setting('vat', number_format((float) $this->input->post('vat'), 2, '.', ''));

        $platformCurrency = strtoupper(trim($country->currency));
        $this->save_rate($platformCurrency, 1);

        $posted = $this->input->post('rates');
        if (is_array($posted)) {
            foreach ($posted as $code => $rate) {
                $code = strtoupper(trim((string) $code));
                if ($code === '' || $code === $platformCurrency) {
                    continue;
                }
                $this->save_rate($code, (float) $rate);
            }
        }

        $this->session->set_flashdata('success', 'Platform pricing and currency settings updated.');
        redirect('/admin/pricing');
    }

    protected function rate_currencies($countries, $platformCountryId)
    {
        $seen = array();
        $list = array();
        $platformCurrency = country_currency($platformCountryId);
        foreach ($countries as $country) {
            $code = strtoupper(trim($country->currency));
            if ($code === '' || $code === $platformCurrency || isset($seen[$code])) {
                continue;
            }
            $seen[$code] = true;
            $list[] = $code;
        }
        sort($list);
        return $list;
    }

    protected function rates_map()
    {
        $rows = $this->db->get('currency_rates')->result();
        $map = array();
        foreach ($rows as $row) {
            $map[strtoupper($row->currency)] = $row->rate_to_platform;
        }
        return $map;
    }

    protected function save_rate($currency, $rate)
    {
        $currency = strtoupper(trim((string) $currency));
        if ($currency === '') {
            return;
        }
        if ($rate <= 0) {
            $rate = 1;
        }
        $exists = $this->db->where('currency', $currency)->get('currency_rates')->row();
        $value = number_format((float) $rate, 8, '.', '');
        if ($exists) {
            $this->db->where('currency', $currency)->update('currency_rates', array('rate_to_platform' => $value));
            return;
        }
        $this->db->insert('currency_rates', array(
            'currency' => $currency,
            'rate_to_platform' => $value,
        ));
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
