<?php
defined('BASEPATH') OR exit('No direct script access allowed');

require_once APPPATH . 'modules/store/controllers/Store_base.php';

class Apps extends Store_base {

    public function __construct()
    {
        parent::__construct();
        $this->load->model('Store_app_model');
    }

    public function index()
    {
        $this->requireAuth();
        $this->requirePermission('apps');

        $this->Store_app_model->seedDefaults($this->store->id);

        $this->template->store('apps/index', $this->viewData(array(
            'page' => 'Apps',
            'apps' => $this->Store_app_model->getByStore($this->store->id),
        )));
    }

    public function toggle($id = 0, $action = 'enable')
    {
        $this->requireAuth();
        $this->requirePermission('apps');

        if ($id) {
            $this->Store_app_model->toggle($this->store->id, $id, $action === 'enable');
            $this->session->set_flashdata('success', 'App updated.');
        }
        redirect('store/apps');
    }

    public function configure($id = 0)
    {
        $this->requireAuth();
        $this->requirePermission('apps');

        $app = $this->Store_app_model->getById($id, $this->store->id);
        if (!$app) {
            redirect('store/apps');
        }

        if ($this->input->post()) {
            $config = array(
                'api_key' => $this->input->post('api_key'),
                'webhook_url' => $this->input->post('webhook_url'),
                'notes' => $this->input->post('notes'),
            );
            $this->Store_app_model->updateConfig($this->store->id, $id, $config);
            $this->session->set_flashdata('success', 'App configuration saved.');
            redirect('store/apps');
        }

        $config = json_decode($app->config, true);
        if (!is_array($config)) {
            $config = array();
        }

        $this->template->store('apps/configure', $this->viewData(array(
            'page' => 'Configure ' . $app->app_name,
            'app' => $app,
            'config' => $config,
        )));
    }
}
