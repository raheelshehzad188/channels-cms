<?php
defined('BASEPATH') OR exit('No direct script access allowed');

require_once APPPATH . 'modules/store/controllers/Store_base.php';

class Themes extends Store_base {

    public function __construct()
    {
        parent::__construct();
        $this->load->model('Store_theme_model');
    }

    public function index()
    {
        $this->requireAuth();
        $this->requirePermission('themes');

        $this->Store_theme_model->seedDefaults($this->store->id);

        $this->template->store('themes/index', $this->viewData(array(
            'page' => 'Themes',
            'themes' => $this->Store_theme_model->getByStore($this->store->id),
        )));
    }

    public function activate($id = 0)
    {
        $this->requireAuth();
        $this->requirePermission('themes');

        if ($id) {
            $this->Store_theme_model->activate($this->store->id, $id);
            $this->session->set_flashdata('success', 'Theme activated.');
        }
        redirect('store/themes');
    }

    public function deactivate($id = 0)
    {
        $this->requireAuth();
        $this->requirePermission('themes');

        if ($id) {
            $this->Store_theme_model->deactivate($this->store->id, $id);
            $this->session->set_flashdata('success', 'Theme deactivated.');
        }
        redirect('store/themes');
    }

    public function preview($id = 0)
    {
        $this->requireAuth();
        $this->requirePermission('themes');

        $theme = $this->Store_theme_model->getById($id, $this->store->id);
        if ($theme) {
            $this->session->set_flashdata('success', 'Preview: ' . $theme->theme_name . ' (storefront coming in Phase 3)');
        }
        redirect('store/themes');
    }
}
