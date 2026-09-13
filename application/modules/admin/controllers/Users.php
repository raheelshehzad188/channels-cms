<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Users extends CI_Controller {

    public function __construct()
    {
        parent::__construct();
        ec_require_admin();
        $this->load->model('User_model');
    }

    public function index()
    {
        $data = array(
            'title' => 'Users',
            'users' => $this->User_model->all(),
        );
        $this->template->admin('users/index', $data);
    }

    public function form($id = 0)
    {
        $user = $id ? $this->User_model->get($id) : null;
        if ($id && !$user) {
            $this->session->set_flashdata('error', 'User not found.');
            redirect('/admin/users');
            return;
        }

        $data = array(
            'title' => $user ? 'Edit User' : 'Add User',
            'user' => $user,
            'roles' => $this->User_model->roles(),
        );
        $this->template->admin('users/form', $data);
    }

    public function save($id = 0)
    {
        $this->form_validation->set_rules('first_name', 'First Name', 'required|trim');
        $this->form_validation->set_rules('last_name', 'Last Name', 'required|trim');
        $this->form_validation->set_rules('email', 'Email', 'required|trim|valid_email');
        $this->form_validation->set_rules('uname', 'Username', 'required|trim');
        $this->form_validation->set_rules('roleID', 'Role', 'required|integer');
        if (!$id) {
            $this->form_validation->set_rules('upass', 'Password', 'required|min_length[4]');
        }

        if ($this->form_validation->run() === FALSE) {
            $this->session->set_flashdata('error', validation_errors());
            redirect($id ? '/admin/users/form/' . $id : '/admin/users/form');
            return;
        }

        $email = trim($this->input->post('email'));
        $uname = trim($this->input->post('uname'));
        if ($this->User_model->exists_email($email, $id)) {
            $this->session->set_flashdata('error', 'Email already exists.');
            redirect($id ? '/admin/users/form/' . $id : '/admin/users/form');
            return;
        }
        if ($this->User_model->exists_uname($uname, $id)) {
            $this->session->set_flashdata('error', 'Username already exists.');
            redirect($id ? '/admin/users/form/' . $id : '/admin/users/form');
            return;
        }

        $roleID = (int) $this->input->post('roleID');
        if (!in_array($roleID, array(ROLE_ADMIN, ROLE_ECOMMERCE), true)) {
            $roleID = ROLE_ECOMMERCE;
        }

        $payload = array(
            'first_name' => trim($this->input->post('first_name')),
            'last_name' => trim($this->input->post('last_name')),
            'email' => $email,
            'uname' => $uname,
            'phone' => trim($this->input->post('phone')),
            'roleID' => $roleID,
            'commission' => $roleID === ROLE_ECOMMERCE ? (float) $this->input->post('commission') : 0,
            'status' => (int) $this->input->post('status') === 1 ? 1 : 0,
        );

        $password = $this->input->post('upass');
        if ($password !== '') {
            $payload['upass'] = md5($password);
        }

        $this->User_model->save($payload, $id);
        $this->session->set_flashdata('success', $id ? 'User updated successfully.' : 'User added successfully.');
        redirect('/admin/users');
    }

    public function delete($id = 0)
    {
        $current = ec_user();
        if ((int) $id === (int) $current->UserID) {
            $this->session->set_flashdata('error', 'You cannot delete your own account.');
            redirect('/admin/users');
            return;
        }

        if (!$id || !$this->User_model->get($id)) {
            $this->session->set_flashdata('error', 'User not found.');
            redirect('/admin/users');
            return;
        }

        $this->User_model->delete($id);
        $this->session->set_flashdata('success', 'User deleted successfully.');
        redirect('/admin/users');
    }
}
