<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Login extends CI_Controller {

    public function post()
    {
        $this->form_validation->set_rules('uname', 'Username', 'required');
        $this->form_validation->set_rules('upass', 'Password', 'required');

        if ($this->form_validation->run() == FALSE) {
            $this->session->set_flashdata('error', 'All fields required');
            redirect('/login');
            return;
        }

        $uname = $this->input->post('uname');
        $upass = $this->input->post('upass');
        $this->load->model('login_model');
        $user = $this->login_model->login($uname, $upass);

        if (!$user) {
            $this->session->set_flashdata('error', 'Enter Correct Username or Password!');
            redirect('/login');
            return;
        }

        unset($user->upass);
        $role = $this->login_model->getrolebyid($user->roleID);
        $roleName = $role && !empty($role->name) ? $role->name : 'user';

        $session = array();
        $session[$roleName . '_login'] = $user;
        $session['knet_login'] = $user;
        $this->login_model->updateuserbyid($user->UserID, array('ip' => $this->input->ip_address()));
        $this->session->set_userdata($session);

        if ((int) $user->roleID === ROLE_ECOMMERCE) {
            redirect('/admin/products');
            return;
        }

        redirect('/admin/admin');
    }

    public function index()
    {
        if (isset($_SESSION['knet_login'])) {
            if (ec_is_ecommerce()) {
                redirect('/admin/products');
                return;
            }
            redirect('/admin/admin');
            return;
        }

        $data = array();
        $data['assets'] = base_url('assets/');
        $this->load->library('template');
        $this->template->full('login', $data);
    }
}
