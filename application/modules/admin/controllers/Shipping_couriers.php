<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Shipping_couriers extends CI_Controller {

    public function __construct()
    {
        parent::__construct();
        ec_require_admin();
        if (function_exists('ec_ensure_shipping_courier_schema')) {
            ec_ensure_shipping_courier_schema();
        }
        $this->load->model('Shipping_courier_model');
        $this->load->model('Country_model');
    }

    public function index()
    {
        $countryId = (int) $this->input->get('country_id');
        $data = array(
            'title' => 'Shipping Couriers',
            'couriers' => $this->Shipping_courier_model->all($countryId),
            'countries' => $this->Country_model->all(),
            'country_id' => $countryId,
        );
        $this->template->admin('shipping_couriers/index', $data);
    }

    public function form($id = 0)
    {
        $courier = $id ? $this->Shipping_courier_model->get($id) : null;
        if ($id && !$courier) {
            $this->session->set_flashdata('error', 'Courier not found.');
            redirect('/admin/couriers');
            return;
        }

        $data = array(
            'title' => $courier ? 'Edit Courier' : 'Add Courier',
            'courier' => $courier,
            'countries' => $this->Country_model->all(),
            'preselect_country_id' => (int) $this->input->get('country_id'),
        );
        $this->template->admin('shipping_couriers/form', $data);
    }

    public function save($id = 0)
    {
        $this->form_validation->set_rules('country_id', 'Country', 'required|integer');
        $this->form_validation->set_rules('name', 'Courier Name', 'required|trim|max_length[120]');

        if ($this->form_validation->run() === FALSE) {
            $this->session->set_flashdata('error', validation_errors());
            redirect($id ? '/admin/couriers/form/' . $id : '/admin/couriers/form');
            return;
        }

        $countryId = (int) $this->input->post('country_id');
        $name = trim($this->input->post('name'));
        if (!$this->Country_model->get($countryId)) {
            $this->session->set_flashdata('error', 'Select a valid country.');
            redirect($id ? '/admin/couriers/form/' . $id : '/admin/couriers/form');
            return;
        }
        if ($this->Shipping_courier_model->exists_name($countryId, $name, $id)) {
            $this->session->set_flashdata('error', 'This courier already exists for that country.');
            redirect($id ? '/admin/couriers/form/' . $id : '/admin/couriers/form');
            return;
        }

        $payload = array(
            'country_id' => $countryId,
            'name' => $name,
            'tracking_url' => trim((string) $this->input->post('tracking_url')),
            'sort_order' => (int) $this->input->post('sort_order'),
            'status' => (int) $this->input->post('status') === 1 ? 1 : 0,
        );

        $this->Shipping_courier_model->save($payload, $id);
        $this->session->set_flashdata('success', $id ? 'Courier updated successfully.' : 'Courier added successfully.');
        redirect('/admin/couriers?country_id=' . $countryId);
    }

    public function delete($id = 0)
    {
        $courier = $id ? $this->Shipping_courier_model->get($id) : null;
        if (!$courier) {
            $this->session->set_flashdata('error', 'Courier not found.');
            redirect('/admin/couriers');
            return;
        }

        $countryId = (int) $courier->country_id;
        $this->Shipping_courier_model->delete($id);
        $this->session->set_flashdata('success', 'Courier deleted successfully.');
        redirect('/admin/couriers?country_id=' . $countryId);
    }
}
