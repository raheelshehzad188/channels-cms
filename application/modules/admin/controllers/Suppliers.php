<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Suppliers extends CI_Controller {

    public function __construct()
    {
        parent::__construct();
        ec_require_admin();
        $this->load->model('Supplier_model');
        $this->load->model('Country_model');
    }

    public function index()
    {
        $data = array(
            'title' => 'Suppliers',
            'suppliers' => $this->Supplier_model->all(),
        );
        $this->template->admin('suppliers/index', $data);
    }

    public function form($id = 0)
    {
        $supplier = $id ? $this->Supplier_model->get($id) : null;
        if ($id && !$supplier) {
            $this->session->set_flashdata('error', 'Supplier not found.');
            redirect('/admin/suppliers');
            return;
        }

        $data = array(
            'title' => $supplier ? 'Edit Supplier' : 'Add Supplier',
            'supplier' => $supplier,
            'countries' => $this->Country_model->all(),
        );
        $this->template->admin('suppliers/form', $data);
    }

    public function save($id = 0)
    {
        $this->form_validation->set_rules('name', 'Supplier Name', 'required|trim');
        $this->form_validation->set_rules('country_id', 'Country', 'required|integer');

        if ($this->form_validation->run() === FALSE) {
            $this->session->set_flashdata('error', validation_errors());
            redirect($id ? '/admin/suppliers/form/' . $id : '/admin/suppliers/form');
            return;
        }

        $countryId = (int) $this->input->post('country_id');
        if (!$this->Country_model->get($countryId)) {
            $this->session->set_flashdata('error', 'Selected country is invalid.');
            redirect($id ? '/admin/suppliers/form/' . $id : '/admin/suppliers/form');
            return;
        }

        $payload = array(
            'country_id' => $countryId,
            'name' => trim($this->input->post('name')),
            'email' => trim($this->input->post('email')),
            'phone' => trim($this->input->post('phone')),
            'company' => trim($this->input->post('company')),
            'address' => trim($this->input->post('address')),
            'status' => (int) $this->input->post('status') === 1 ? 1 : 0,
        );

        $this->Supplier_model->save($payload, $id);
        $this->session->set_flashdata('success', $id ? 'Supplier updated successfully.' : 'Supplier added successfully.');
        redirect('/admin/suppliers');
    }

    public function delete($id = 0)
    {
        if (!$id || !$this->Supplier_model->get($id)) {
            $this->session->set_flashdata('error', 'Supplier not found.');
            redirect('/admin/suppliers');
            return;
        }

        $this->Supplier_model->delete($id);
        $this->session->set_flashdata('success', 'Supplier deleted successfully.');
        redirect('/admin/suppliers');
    }
}
