<?php
defined('BASEPATH') OR exit('No direct script access allowed');

require_once APPPATH . 'modules/store/controllers/Store_base.php';

class Pages extends Store_base {

    public function __construct()
    {
        parent::__construct();
        $this->load->model('Store_page_model');
        $this->Store_page_model->ensure_tables();
    }

    public function index()
    {
        $this->requirePagesAccess();
        $filters = array(
            'q' => trim((string) $this->input->get('q')),
            'status' => $this->input->get('status'),
        );
        if ($filters['status'] === null || $filters['status'] === false) {
            $filters['status'] = '';
        }
        $this->template->store('pages/index', $this->viewData(array(
            'page' => 'Pages',
            'title' => 'Pages',
            'items' => $this->Store_page_model->all($this->store->id, $filters),
            'filters' => $filters,
        )));
    }

    public function form($id = 0)
    {
        $this->requirePagesAccess();
        $item = $id ? $this->Store_page_model->get_owned($this->store->id, $id) : null;
        if ($id && !$item) {
            $this->session->set_flashdata('error', 'Page not found.');
            redirect('store/pages');
            return;
        }
        $this->template->store('pages/form', $this->viewData(array(
            'page' => $item ? 'Edit page' : 'Add page',
            'title' => $item ? 'Edit page' : 'Add page',
            'item' => $item,
        )));
    }

    public function save($id = 0)
    {
        $this->requirePagesAccess();
        if (strtoupper((string) $this->input->method()) !== 'POST') {
            redirect('store/pages');
            return;
        }
        $existing = $id ? $this->Store_page_model->get_owned($this->store->id, $id) : null;
        if ($id && !$existing) {
            $this->session->set_flashdata('error', 'Page not found.');
            redirect('store/pages');
            return;
        }
        $title = trim((string) $this->input->post('title'));
        $slug = trim((string) $this->input->post('slug'));
        $detail = (string) $this->input->post('detail');
        if ($title === '' || trim($detail) === '') {
            $this->session->set_flashdata('error', 'Title and detail are required.');
            redirect($id ? 'store/pages/form/' . (int) $id : 'store/pages/form');
            return;
        }
        $checkSlug = $slug !== '' ? $slug : $title;
        if ($this->Store_page_model->last_error_reserved($checkSlug)) {
            $this->session->set_flashdata('error', 'That slug is reserved (contact, shop, cart, and similar URLs). Choose another.');
            redirect($id ? 'store/pages/form/' . (int) $id : 'store/pages/form');
            return;
        }
        $saved = $this->Store_page_model->save($this->store->id, array(
            'title' => $title,
            'slug' => $slug,
            'detail' => $detail,
            'meta_description' => $this->input->post('meta_description'),
            'status' => (int) $this->input->post('status') === 1,
            'show_in_nav' => (int) $this->input->post('show_in_nav') === 1,
            'show_in_footer' => (int) $this->input->post('show_in_footer') === 1,
            'sort_order' => (int) $this->input->post('sort_order'),
        ), $id);
        if (!$saved) {
            $this->session->set_flashdata('error', 'Could not save the page. The slug may be reserved.');
            redirect($id ? 'store/pages/form/' . (int) $id : 'store/pages/form');
            return;
        }
        $this->session->set_flashdata('success', $id ? 'Page updated.' : 'Page created.');
        redirect('store/pages');
    }

    public function toggle($id = 0)
    {
        $this->requirePagesAccess();
        $item = $this->Store_page_model->get_owned($this->store->id, $id);
        if (!$item) {
            $this->session->set_flashdata('error', 'Page not found.');
            redirect('store/pages');
            return;
        }
        $this->Store_page_model->set_status($this->store->id, $id, ((int) $item->status === 1) ? 0 : 1);
        $this->session->set_flashdata('success', ((int) $item->status === 1) ? 'Page unpublished.' : 'Page published.');
        redirect('store/pages');
    }

    public function delete($id = 0)
    {
        $this->requirePagesAccess();
        if (!$this->Store_page_model->delete_owned($this->store->id, $id)) {
            $this->session->set_flashdata('error', 'Page not found.');
            redirect('store/pages');
            return;
        }
        $this->session->set_flashdata('success', 'Page deleted.');
        redirect('store/pages');
    }

    protected function requirePagesAccess()
    {
        $this->requireAuth();
        if ($this->hasPermission('themes') || $this->hasPermission('settings')) {
            return;
        }
        $this->requirePermission('settings');
    }
}
