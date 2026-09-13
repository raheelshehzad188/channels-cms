<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Stores extends CI_Controller {

    public function __construct()
    {
                parent::__construct();
        ec_require_admin();
        $this->load->model('Store_model');
        $this->load->model('Theme_model');
    }

    public function index()
    {
        $data = array(
            'title' => 'Stores',
            'stores' => $this->Store_model->all(),
        );
        $this->template->admin('stores/index', $data);
    }

    public function form($id = 0)
    {
        $store = $id ? $this->Store_model->get($id) : null;
        if ($id && !$store) {
            $this->session->set_flashdata('error', 'Store not found.');
            redirect('/admin/stores');
            return;
        }

        $this->load->model('Country_model');
        $data = array(
            'title' => $store ? 'Edit Store' : 'Add Store',
            'store' => $store,
            'countries' => $this->Country_model->all(),
        );
        $this->template->admin('stores/form', $data);
    }

        public function save($id = 0)
        {
        $this->form_validation->set_rules('name', 'Store Name', 'required|trim');
        $this->form_validation->set_rules('domain', 'Domain', 'required|trim');
        $this->form_validation->set_rules('email', 'Login Email', 'required|trim|valid_email');
        $this->form_validation->set_rules('country_id', 'Country', 'required|integer');
                if (!$id) {
            $this->form_validation->set_rules('password', 'Password', 'required|min_length[4]');
        }

        if ($this->form_validation->run() === FALSE) {
                        $this->session->set_flashdata('error', validation_errors());
            redirect($id ? '/admin/stores/form/' . $id : '/admin/stores/form');
            return;
        }

        $domain = strtolower(trim($this->input->post('domain')));
        $domain = preg_replace('/^https?:\/\//', '', $domain);
        $domain = rtrim($domain, '/');

        if ($this->Store_model->domain_exists($domain, $id)) {
            $this->session->set_flashdata('error', 'This domain is already used by another store.');
            redirect($id ? '/admin/stores/form/' . $id : '/admin/stores/form');
            return;
        }

        $payload = array(
            'name' => trim($this->input->post('name')),
                                'domain' => $domain,
            'email' => strtolower(trim($this->input->post('email'))),
            'owner_name' => trim($this->input->post('owner_name')),
            'country_id' => (int) $this->input->post('country_id'),
        );
        $password = $this->input->post('password');
                        if ($password !== '') {
            $payload['password'] = md5($password);
        }

        $this->load->model('Country_model');
        $country = $this->Country_model->get((int) $payload['country_id']);
        if ($country) {
            if ($this->db->field_exists('currency', 'stores')) {
                $payload['currency'] = strtoupper(trim($country->currency));
            }
            if ($this->db->field_exists('country', 'stores')) {
                $payload['country'] = $country->name;
            }
        }

        $storeId = $this->Store_model->save($payload, $id);
        $this->session->set_flashdata('success', 'Store saved. Select a theme to continue.');
        redirect('/admin/stores/theme/' . $storeId);
    }

    public function theme($id = 0)
    {
        $store = $this->Store_model->get($id);
        if (!$store) {
            $this->session->set_flashdata('error', 'Store not found.');
            redirect('/admin/stores');
            return;
        }

        $data = array(
            'title' => 'Select Theme',
            'store' => $store,
            'themes' => $this->Theme_model->all(),
        );
        $this->template->admin('stores/theme', $data);
    }

    public function save_theme($id = 0)
    {
        $store = $this->Store_model->get($id);
        if (!$store) {
            redirect('/admin/stores');
            return;
        }

        $themeId = (int) $this->input->post('theme_id');
        $theme = $this->Theme_model->get($themeId);
        if (!$theme) {
            $this->session->set_flashdata('error', 'Please select a theme.');
            redirect('/admin/stores/theme/' . $id);
            return;
        }

        $this->Store_model->save(array(
            'theme_id' => $themeId,
            'status' => 0,
        ), $id);

        $this->session->set_flashdata('success', 'Theme selected. Fill required settings to activate.');
        redirect('/admin/stores/settings/' . $id);
    }

    public function settings($id = 0)
    {
        $store = $this->Store_model->get($id);
        if (!$store || empty($store->theme_id)) {
            $this->session->set_flashdata('error', 'Select a theme first.');
            redirect($store ? '/admin/stores/theme/' . $id : '/admin/stores');
            return;
        }

        $data = array(
            'title' => 'Store Theme Settings',
            'store' => $store,
            'theme' => $this->Theme_model->get($store->theme_id),
            'fields' => $this->Theme_model->fields($store->theme_id),
            'values' => $this->Store_model->settings_map($id),
        );
        $this->template->admin('stores/settings', $data);
    }

    public function save_settings($id = 0)
    {
        $store = $this->Store_model->get($id);
        if (!$store || empty($store->theme_id)) {
            redirect('/admin/stores');
            return;
        }

        $fields = $this->Theme_model->fields($store->theme_id);
        $values = $this->Store_model->settings_map($id);

        foreach ($fields as $field) {
            if ($field->field_type === 'image') {
                $uploaded = $this->_upload_setting_image($id, $field->field_key);
                $value = $uploaded !== '' ? $uploaded : (isset($values[$field->field_key]) ? $values[$field->field_key] : '');
            } else {
                $value = trim((string) $this->input->post($field->field_key));
            }

            if ($field->is_required && $value === '') {
                $this->session->set_flashdata('error', $field->field_label . ' is required.');
                redirect('/admin/stores/settings/' . $id);
                return;
            }

            $this->Store_model->save_setting($id, $store->theme_id, $field->field_key, $value);
        }

        $this->Store_model->save(array('status' => 1), $id);
        $this->session->set_flashdata('success', 'Theme activated on ' . $store->domain . '.');
        redirect('/admin/stores');
    }

    public function delete($id = 0)
    {
        $store = $this->Store_model->get($id);
        if (!$store) {
                        $this->session->set_flashdata('error', 'Store not found.');
            redirect('/admin/stores');
            return;
        }

        $this->Store_model->delete($id);
        $this->session->set_flashdata('success', 'Store deleted.');
        redirect('/admin/stores');
    }

    private function _upload_setting_image($storeId, $key)
    {
        if (empty($_FILES[$key]['name'])) {
            return '';
        }

        $dir = FCPATH . 'uploads/stores/' . (int) $storeId . '/';
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $config = array(
            'upload_path' => $dir,
            'allowed_types' => 'jpg|jpeg|png|gif|webp',
            'max_size' => 4096,
            'encrypt_name' => true,
        );
        $this->load->library('upload', $config);
        $this->upload->initialize($config);
        if (!$this->upload->do_upload($key)) {
            $this->session->set_flashdata('error', $this->upload->display_errors('', ''));
            return '';
        }

        $uploaded = $this->upload->data();
        return 'uploads/stores/' . (int) $storeId . '/' . $uploaded['file_name'];
        }
}
