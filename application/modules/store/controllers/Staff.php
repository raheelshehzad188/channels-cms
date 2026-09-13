<?php
defined('BASEPATH') OR exit('No direct script access allowed');

require_once APPPATH . 'modules/store/controllers/Store_base.php';

class Staff extends Store_base {

    public function __construct()
    {
        parent::__construct();
        $this->load->model('Staff_model');
    }

    public function index()
    {
        $this->requireAuth();
        $this->requirePermission('staff');

        $data = $this->viewData(array(
            'page' => 'Staff',
            'staff_list' => $this->Staff_model->getByStore($this->store->id),
        ));
        $this->template->store('staff/index', $data);
    }

    public function create($id = 0)
    {
        $this->requireAuth();
        $this->requirePermission('staff');

        $edit = null;
        if ($id) {
            $edit = $this->Staff_model->getById($id, $this->store->id);
            if (!$edit) {
                redirect('store/staff');
            }
        }

        if ($this->input->post()) {
            $this->form_validation->set_rules('name', 'Name', 'required');
            $this->form_validation->set_rules('email', 'Email', 'required|valid_email');
            $this->form_validation->set_rules('role', 'Role', 'required');

            if (!$id) {
                $this->form_validation->set_rules('password', 'Password', 'required|min_length[6]');
                $this->form_validation->set_rules('confirm_password', 'Confirm Password', 'required|matches[password]');
            } elseif ($this->input->post('password')) {
                $this->form_validation->set_rules('password', 'Password', 'min_length[6]');
                $this->form_validation->set_rules('confirm_password', 'Confirm Password', 'matches[password]');
            }

            if ($this->form_validation->run()) {
                $email = $this->input->post('email');
                if ($this->Staff_model->emailExists($this->store->id, $email, $id)) {
                    $this->session->set_flashdata('error', 'Email already exists for this store.');
                } else {
                    $row = array(
                        'store_id' => $this->store->id,
                        'name' => $this->input->post('name'),
                        'email' => $email,
                        'phone' => $this->input->post('phone'),
                        'role' => $this->input->post('role'),
                        'status' => $this->input->post('status') ? 1 : 0,
                        'permissions' => $this->input->post('permissions') ?: null,
                    );

                    if ($this->input->post('password')) {
                        $row['password'] = md5($this->input->post('password'));
                    }

                    if ($id) {
                        $this->Staff_model->update($id, $this->store->id, $row);
                        $this->session->set_flashdata('success', 'Staff updated successfully.');
                    } else {
                        $this->Staff_model->add($row);
                        $this->session->set_flashdata('success', 'Staff invited successfully.');
                    }
                    redirect('store/staff');
                }
            }
        }

        $this->template->store('staff/form', $this->viewData(array(
            'page' => $id ? 'Edit Staff' : 'Invite Staff',
            'edit' => $edit,
            'roles' => $this->Staff_model->getRoles(),
        )));
    }

    public function delete($id = 0)
    {
        $this->requireAuth();
        $this->requirePermission('staff');

        if ($id) {
            $this->Staff_model->update($id, $this->store->id, array('status' => 0));
            $this->session->set_flashdata('success', 'Staff removed successfully.');
        }
        redirect('store/staff');
    }
}
