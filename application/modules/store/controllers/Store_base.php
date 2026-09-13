<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Store_base extends CI_Controller {

    protected $store = null;
    protected $staff = null;
    protected $storeUrl = '';

    public function __construct()
    {
        parent::__construct();
        $this->load->library(array('template', 'tenant', 'form_validation'));
        $this->storeUrl = base_url('store');

        $domainHint = $this->input->get_post('store_domain');
        $this->tenant->resolve($domainHint);

        if (isset($_SESSION['store_login'])) {
            $login = $_SESSION['store_login'];
            if (isset($login['store_id'])) {
                $store = $this->db->where('id', $login['store_id'])->get('stores')->row();
                if ($store) {
                    $this->tenant->set_store($store);
                    $this->store = $store;
                }
            }
            if (isset($login['staff'])) {
                $this->staff = (object) $login['staff'];
            }
        }

        if (!$this->store) {
            $this->store = $this->tenant->get_store();
        }
        if ($this->store) {
            $this->store = hydrate_store_currency($this->store);
        }
    }

    protected function requireAuth()
    {
        if (!isset($_SESSION['store_login']) || !$this->store) {
            redirect('store/login');
            exit;
        }

        $loginStoreId = (int) $_SESSION['store_login']['store_id'];
        if ($loginStoreId !== (int) $this->store->id) {
            unset($_SESSION['store_login']);
            redirect('store/login');
            exit;
        }
    }

    protected function requirePermission($permission)
    {
        $this->requireAuth();
        if (!$this->hasPermission($permission)) {
            $this->session->set_flashdata('error', 'You do not have permission to access this area.');
            redirect('store/dashboard');
            exit;
        }
    }

    protected function hasPermission($permission)
    {
        if (!$this->staff) {
            return true;
        }
        if ($this->staff->role === 'owner') {
            return true;
        }
        $perms = json_decode($this->staff->permissions, true);
        if (!is_array($perms)) {
            $row = $this->db->where('role_slug', $this->staff->role)->get('store_permissions')->row();
            $perms = $row ? json_decode($row->permissions, true) : array();
        }
        if (in_array('*', $perms, true)) {
            return true;
        }
        return in_array($permission, $perms, true);
    }

    protected function viewData($extra = array())
    {
        $data = array(
            'store' => $this->store,
            'staff' => $this->staff,
            'storeUrl' => $this->storeUrl,
            'page' => '',
            'title' => $this->store ? $this->store->name : 'Store Admin',
        );
        return array_merge($data, $extra);
    }
}
