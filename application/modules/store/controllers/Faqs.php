<?php
defined('BASEPATH') OR exit('No direct script access allowed');

require_once APPPATH . 'modules/store/controllers/Store_base.php';

class Faqs extends Store_base {

    public function __construct()
    {
        parent::__construct();
        $this->load->model('Product_faq_model');
        $this->load->model('Store_product_model');
    }

    public function index()
    {
        $this->requireAuth();
        $filters = array(
            'q' => trim((string) $this->input->get('q')),
            'product_id' => (int) $this->input->get('product_id'),
            'status' => $this->input->get('status'),
        );
        if ($filters['status'] === null || $filters['status'] === false) {
            $filters['status'] = '';
        }

        $this->template->store('faqs/index', $this->viewData(array(
            'page' => 'Product FAQs',
            'title' => 'Product FAQs',
            'items' => $this->Product_faq_model->all($this->store->id, $filters),
            'products' => $this->catalog_products(),
            'filters' => $filters,
        )));
    }

    public function form($id = 0)
    {
        $this->requireAuth();
        $item = $id ? $this->Product_faq_model->get_owned($this->store->id, $id) : null;
        if ($id && !$item) {
            $this->session->set_flashdata('error', 'FAQ not found.');
            redirect('store/faqs');
            return;
        }

        $this->template->store('faqs/form', $this->viewData(array(
            'page' => $item ? 'Edit FAQ' : 'Add FAQ',
            'title' => $item ? 'Edit FAQ' : 'Add FAQ',
            'item' => $item,
            'products' => $this->catalog_products(),
            'selected_product_id' => $item ? (int) $item->product_id : (int) $this->input->get('product_id'),
        )));
    }

    public function save($id = 0)
    {
        $this->requireAuth();
        $existing = $id ? $this->Product_faq_model->get_owned($this->store->id, $id) : null;
        if ($id && !$existing) {
            $this->session->set_flashdata('error', 'FAQ not found.');
            redirect('store/faqs');
            return;
        }

        $productId = (int) $this->input->post('product_id');
        $product = $this->Store_product_model->get_owned($this->store->id, $productId);
        $question = trim((string) $this->input->post('question'));
        $answer = trim((string) $this->input->post('answer'));
        if (!$product || $question === '' || $answer === '') {
            $this->session->set_flashdata('error', 'Product, question and answer are required.');
            redirect($id ? 'store/faqs/form/' . (int) $id : 'store/faqs/form');
            return;
        }

        $saved = $this->Product_faq_model->save($this->store->id, array(
            'product_id' => $productId,
            'question' => $question,
            'answer' => $answer,
            'status' => $this->input->post('status') ? 1 : 0,
            'sort_order' => (int) $this->input->post('sort_order'),
        ), $id);

        if (!$saved) {
            $this->session->set_flashdata('error', 'Could not save FAQ.');
            redirect('store/faqs');
            return;
        }

        $this->session->set_flashdata('success', $id ? 'FAQ updated.' : 'FAQ added.');
        redirect('store/faqs');
    }

    public function toggle($id = 0)
    {
        $this->requireAuth();
        $item = $this->Product_faq_model->get_owned($this->store->id, $id);
        if (!$item) {
            $this->session->set_flashdata('error', 'FAQ not found.');
            redirect('store/faqs');
            return;
        }
        $this->Product_faq_model->set_status($this->store->id, $id, ((int) $item->status === 1) ? 0 : 1);
        $this->session->set_flashdata('success', ((int) $item->status === 1) ? 'FAQ disabled.' : 'FAQ enabled.');
        redirect('store/faqs');
    }

    public function delete($id = 0)
    {
        $this->requireAuth();
        if (!$this->Product_faq_model->delete_owned($this->store->id, $id)) {
            $this->session->set_flashdata('error', 'FAQ not found.');
            redirect('store/faqs');
            return;
        }
        $this->session->set_flashdata('success', 'FAQ deleted.');
        redirect('store/faqs');
    }

    protected function catalog_products()
    {
        $rows = $this->Store_product_model->mine($this->store->id);
        $out = array();
        foreach ($rows as $row) {
            $parentSku = isset($row->parent_sku) ? trim((string) $row->parent_sku) : '';
            if ($parentSku !== '') {
                continue;
            }
            $out[] = $row;
        }
        return $out;
    }
}
