<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Countries extends CI_Controller {

    public function __construct()
    {
        parent::__construct();
        ec_require_admin();
        $this->load->model('Country_model');
    }

    public function index()
    {
        $data = array(
            'title' => 'Countries',
            'countries' => $this->Country_model->all(),
        );
        $this->template->admin('countries/index', $data);
    }

    public function form($id = 0)
    {
        $country = $id ? $this->Country_model->get($id) : null;
        if ($id && !$country) {
            $this->session->set_flashdata('error', 'Country not found.');
            redirect('/admin/countries');
            return;
        }

        $data = array(
            'title' => $country ? 'Edit Country' : 'Add Country',
            'country' => $country,
        );
        $this->template->admin('countries/form', $data);
    }

    public function save($id = 0)
    {
        $this->form_validation->set_rules('name', 'Country Name', 'required|trim');
        $this->form_validation->set_rules('code', 'Country Code', 'required|trim|max_length[10]');

        if ($this->form_validation->run() === FALSE) {
            $this->session->set_flashdata('error', validation_errors());
            redirect($id ? '/admin/countries/form/' . $id : '/admin/countries/form');
            return;
        }

        $code = strtoupper(trim($this->input->post('code')));
        if ($this->Country_model->exists_code($code, $id)) {
            $this->session->set_flashdata('error', 'Country code already exists.');
            redirect($id ? '/admin/countries/form/' . $id : '/admin/countries/form');
            return;
        }

        $payload = array(
            'name' => trim($this->input->post('name')),
            'code' => $code,
            'iso3' => strtoupper(trim($this->input->post('iso3'))),
            'phone_code' => trim($this->input->post('phone_code')),
            'currency' => strtoupper(trim($this->input->post('currency'))),
            'status' => (int) $this->input->post('status') === 1 ? 1 : 0,
        );

        $this->Country_model->save($payload, $id);
        $this->session->set_flashdata('success', $id ? 'Country updated successfully.' : 'Country added successfully.');
        redirect('/admin/countries');
    }

    public function delete($id = 0)
    {
        if (!$id || !$this->Country_model->get($id)) {
            $this->session->set_flashdata('error', 'Country not found.');
            redirect('/admin/countries');
            return;
        }

        if (!$this->Country_model->delete($id)) {
            $this->session->set_flashdata('error', 'Cannot delete country because suppliers are linked to it.');
            redirect('/admin/countries');
            return;
        }

        $this->session->set_flashdata('success', 'Country deleted successfully.');
        redirect('/admin/countries');
    }
}
