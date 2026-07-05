<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Stores extends CI_Controller {

        function __construct() {
                parent::__construct();
                if (!isset($_SESSION['knet_login'])) {
                        redirect('/login');
                }
                $this->load->library('template');
                $this->load->model('Store_model');
                $this->url = base_url('/admin/stores');
        }

        private $single = 'Store';
        private $multi = 'Stores';
        private $add = 'addstore';
        private $all = 'allstores';
        private $url = '';

        public function save($id = 0)
        {
                $domain = trim($this->input->post('domain'));
                $password = $this->input->post('password');
                $confirmPassword = $this->input->post('confirm_password');

                $this->form_validation->set_rules('domain', 'Domain Name', 'required|callback__check_domain_unique[' . $id . ']');

                if (!$id) {
                        $this->form_validation->set_rules('password', 'Password', 'required|min_length[6]');
                        $this->form_validation->set_rules('confirm_password', 'Confirm Password', 'required|matches[password]');
                } elseif ($password !== '' || $confirmPassword !== '') {
                        $this->form_validation->set_rules('password', 'Password', 'required|min_length[6]');
                        $this->form_validation->set_rules('confirm_password', 'Confirm Password', 'required|matches[password]');
                }

                if ($this->form_validation->run() == FALSE) {
                        $this->session->set_flashdata('error', validation_errors());
                } else {
                        $arr = array(
                                'domain' => $domain,
                                'name' => $domain,
                                'slug' => url_title($domain, '-', TRUE),
                        );

                        if ($password !== '') {
                                $arr['password'] = md5($password);
                        }

                        if ($id) {
                                if ($this->Store_model->update($id, $arr)) {
                                        $this->session->set_flashdata('success', $this->single . ' updated successfully!');
                                } else {
                                        $this->session->set_flashdata('error', 'Server error');
                                }
                        } else {
                                if ($this->Store_model->add($arr)) {
                                        $this->session->set_flashdata('success', $this->single . ' created successfully!');
                                } else {
                                        $this->session->set_flashdata('error', 'Server error');
                                }
                        }
                }
                redirect($_SERVER['HTTP_REFERER']);
        }

        public function _check_domain_unique($domain, $id = 0)
        {
                $this->db->where('domain', $domain);
                $this->db->where('status', 0);
                if ($id) {
                        $this->db->where('id !=', $id);
                }
                if ($this->db->count_all_results('stores') > 0) {
                        $this->form_validation->set_message('_check_domain_unique', 'This domain is already registered.');
                        return FALSE;
                }
                return TRUE;
        }

        public function delete($id = 0)
        {
                if ($id) {
                        if ($this->Store_model->update($id, array('status' => 1))) {
                                $this->session->set_flashdata('success', $this->single . ' deleted successfully!');
                        } else {
                                $this->session->set_flashdata('error', 'Server error');
                        }
                }
                redirect($_SERVER['HTTP_REFERER']);
        }

        public function create($id = 0)
        {
                $data = array();
                $data['url'] = $this->url;
                $data['assets'] = base_url('assets/admin/');
                $data['page'] = ($id ? 'Edit ' : 'Create ') . $this->single;
                $data['breed'] = array(
                        'Home' => base_url('/admin/admin'),
                        $data['page'] => '',
                );

                if ($id) {
                        $data['edit'] = $this->Store_model->getbyid($id);
                        if (!$data['edit']) {
                                redirect($this->url . '/all');
                        }
                }

                $this->template->admin($this->add, $data);
        }

        public function all()
        {
                $data = array();
                $data['url'] = $this->url;
                $data['assets'] = base_url('/assets/') . config_item('app_theme') . '/';
                $data['page'] = 'Manage ' . $this->multi;
                $data['breed'] = array(
                        'Home' => base_url('/admin/admin'),
                        $data['page'] => '',
                );
                $data['data'] = $this->Store_model->get(array('status' => 0));
                $this->template->admin($this->all, $data);
        }
}
