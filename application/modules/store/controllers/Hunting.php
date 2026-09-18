<?php
defined('BASEPATH') OR exit('No direct script access allowed');

require_once APPPATH . 'modules/store/controllers/Store_base.php';

class Hunting extends Store_base {

    public function __construct()
    {
        parent::__construct();
        $this->load->model('Product_hunting_model');
    }

    public function index()
    {
        $this->requireAuth();
        $q = trim((string) $this->input->get('q'));
        $countryId = (int) $this->input->get('country_id');
        $filters = array('q' => $q, 'status' => 1);
        if ($countryId) {
            $filters['country_id'] = $countryId;
        }

        $this->template->store('hunting/index', $this->viewData(array(
            'page' => 'Product Hunting',
            'title' => 'Product Hunting',
            'items' => $this->Product_hunting_model->all($filters),
            'countries' => $this->Product_hunting_model->countries(),
            'q' => $q,
            'country_id' => $countryId,
            'store_country_id' => (int) (isset($this->store->country_id) ? $this->store->country_id : 0),
        )));
    }

    public function view($id = 0)
    {
        $this->requireAuth();
        $item = $id ? $this->Product_hunting_model->get($id) : null;
        if (!$item || (int) $item->status !== 1) {
            $this->session->set_flashdata('error', 'Hunting product not found.');
            redirect('store/hunting');
            return;
        }

        $this->template->store('hunting/view', $this->viewData(array(
            'page' => $item->title,
            'title' => $item->title,
            'item' => $item,
            'store_country_id' => (int) (isset($this->store->country_id) ? $this->store->country_id : 0),
        )));
    }
}
