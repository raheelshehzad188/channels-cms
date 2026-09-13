<?php
defined('BASEPATH') OR exit('No direct script access allowed');

require_once APPPATH . 'modules/store/controllers/Store_base.php';

class Auth extends Store_base {

    public function __construct()
    {
        parent::__construct();
        $this->load->model('Store_auth_model');
    }

    public function login()
    {
        if (isset($_SESSION['store_login'])) {
            redirect('store/dashboard');
        }

        if ($this->input->post()) {
            $this->form_validation->set_rules('email', 'Email', 'required|valid_email');
            $this->form_validation->set_rules('password', 'Password', 'required');

            if ($this->form_validation->run()) {
                $auth = $this->Store_auth_model->authenticate(
                    $this->input->post('email'),
                    $this->input->post('password'),
                    trim((string) $this->input->post('store_domain'))
                );

                if ($auth) {
                    $token = bin2hex(random_bytes(32));
                    $this->Store_auth_model->createSession(
                        $auth['store']->id,
                        $auth['staff']->id,
                        $token,
                        date('Y-m-d H:i:s', strtotime('+7 days'))
                    );

                    $_SESSION['store_login'] = array(
                        'store_id' => $auth['store']->id,
                        'staff' => array(
                            'id' => $auth['staff']->id,
                            'name' => $auth['staff']->name,
                            'email' => $auth['staff']->email,
                            'role' => $auth['staff']->role,
                            'permissions' => $auth['staff']->permissions,
                        ),
                        'token' => $token,
                        'impersonated' => false,
                    );
                    $this->tenant->set_store($auth['store']);
                    redirect('store/dashboard');
                } else {
                    $this->session->set_flashdata('error', 'Invalid store email or password.');
                }
            }
        }

        $this->template->store('auth/login', $this->viewData(array(
            'page' => 'Store Login',
            'title' => 'Sign in to your store',
            'login_defaults' => array(
                'store_domain' => set_value('store_domain'),
                'email' => set_value('email'),
                'password' => '',
            ),
        )));
    }

    public function logout()
    {
        if (isset($_SESSION['store_login']['token'])) {
            $this->db->where('session_token', $_SESSION['store_login']['token'])->delete('store_sessions');
        }
        unset($_SESSION['store_login']);
        unset($_SESSION['store_tenant_id']);
        redirect('store/login');
    }

    public function forgot_password()
    {
        if ($this->input->post()) {
            $this->form_validation->set_rules('email', 'Email', 'required|valid_email');

            if ($this->form_validation->run()) {
                $store = $this->db->where('email', strtolower(trim($this->input->post('email'))))->where('status', 1)->get('stores')->row();
                if ($store && $this->db->field_exists('reset_token', 'stores')) {
                    $token = bin2hex(random_bytes(32));
                    $this->Store_auth_model->setResetToken($store->id, $token, date('Y-m-d H:i:s', strtotime('+1 hour')));
                    $this->session->set_flashdata('success', 'Reset link: ' . base_url('store/reset-password/' . $token));
                } else {
                    $this->session->set_flashdata('success', 'If the store exists, a reset link has been sent.');
                }
            }
        }

        $this->template->store('auth/forgot_password', $this->viewData(array(
            'page' => 'Forgot Password',
            'title' => 'Reset your password',
        )));
    }

    public function reset_password($token = '')
    {
        $store = $this->Store_auth_model->getByResetToken($token);
        if (!$store) {
            $this->session->set_flashdata('error', 'Invalid or expired reset link.');
            redirect('store/forgot-password');
        }

        if ($this->input->post()) {
            $this->form_validation->set_rules('password', 'Password', 'required|min_length[6]');
            $this->form_validation->set_rules('confirm_password', 'Confirm Password', 'required|matches[password]');

            if ($this->form_validation->run()) {
                $this->db->where('id', $store->id)->update('stores', array(
                    'password' => md5($this->input->post('password')),
                ));
                $this->Store_auth_model->clearResetToken($store->id);
                $this->session->set_flashdata('success', 'Password updated. Please login.');
                redirect('store/login');
            }
        }

        $this->tenant->set_store($store);
        $this->store = $store;

        $this->template->store('auth/reset_password', $this->viewData(array(
            'page' => 'Reset Password',
            'title' => 'Set new password',
            'token' => $token,
        )));
    }
}
