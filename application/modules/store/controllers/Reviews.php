<?php
defined('BASEPATH') OR exit('No direct script access allowed');

require_once APPPATH . 'modules/store/controllers/Store_base.php';

class Reviews extends Store_base {

    public function __construct()
    {
        parent::__construct();
        $this->load->model('Product_review_model');
        $this->load->model('Store_product_model');
    }

    public function index()
    {
        $this->requireAuth();
        $filters = array(
            'q' => trim((string) $this->input->get('q')),
            'product_id' => (int) $this->input->get('product_id'),
            'rating' => (int) $this->input->get('rating'),
            'status' => $this->input->get('status'),
            'approval_status' => trim((string) $this->input->get('approval_status')),
        );
        if ($filters['status'] === null || $filters['status'] === false) {
            $filters['status'] = '';
        }

        $this->template->store('reviews/index', $this->viewData(array(
            'page' => 'Product Reviews',
            'title' => 'Product Reviews',
            'items' => $this->Product_review_model->all($this->store->id, $filters),
            'products' => $this->catalog_products(),
            'filters' => $filters,
        )));
    }

    public function form($id = 0)
    {
        $this->requireAuth();
        $item = $id ? $this->Product_review_model->get_owned($this->store->id, $id) : null;
        if ($id && !$item) {
            $this->session->set_flashdata('error', 'Review not found.');
            redirect('store/reviews');
            return;
        }

        $this->template->store('reviews/form', $this->viewData(array(
            'page' => $item ? 'Edit Review' : 'Add Review',
            'title' => $item ? 'Edit Review' : 'Add Review',
            'item' => $item,
            'products' => $this->catalog_products(),
            'selected_product_id' => $item ? (int) $item->product_id : (int) $this->input->get('product_id'),
        )));
    }

    public function save($id = 0)
    {
        $this->requireAuth();
        $existing = $id ? $this->Product_review_model->get_owned($this->store->id, $id) : null;
        if ($id && !$existing) {
            $this->session->set_flashdata('error', 'Review not found.');
            redirect('store/reviews');
            return;
        }

        $productId = (int) $this->input->post('product_id');
        $product = $this->Store_product_model->get_owned($this->store->id, $productId);
        $name = trim((string) $this->input->post('customer_name'));
        $content = trim((string) $this->input->post('content'));
        if (!$product || $name === '' || $content === '') {
            $this->session->set_flashdata('error', 'Product, reviewer name and review content are required.');
            redirect($id ? 'store/reviews/form/' . (int) $id : 'store/reviews/form');
            return;
        }

        $saved = $this->Product_review_model->save($this->store->id, array(
            'product_id' => $productId,
            'customer_id' => $existing ? (int) $existing->customer_id : 0,
            'customer_name' => $name,
            'rating' => (int) $this->input->post('rating'),
            'title' => trim((string) $this->input->post('title')),
            'content' => $content,
            'status' => $this->input->post('status') ? 1 : 0,
            'approval_status' => $this->input->post('approval_status') ?: 'approved',
        ), $id);

        if (!$saved) {
            $this->session->set_flashdata('error', 'Could not save review.');
            redirect('store/reviews');
            return;
        }

        $this->session->set_flashdata('success', $id ? 'Review updated.' : 'Review added.');
        redirect('store/reviews');
    }

    public function approve($id = 0)
    {
        $this->requireAuth();
        if (!$this->Product_review_model->set_approval($this->store->id, $id, 'approved')) {
            $this->session->set_flashdata('error', 'Review not found.');
        } else {
            $this->session->set_flashdata('success', 'Review approved.');
        }
        redirect('store/reviews');
    }

    public function reject($id = 0)
    {
        $this->requireAuth();
        if (!$this->Product_review_model->set_approval($this->store->id, $id, 'rejected')) {
            $this->session->set_flashdata('error', 'Review not found.');
        } else {
            $this->session->set_flashdata('success', 'Review rejected.');
        }
        redirect('store/reviews');
    }

    public function toggle($id = 0)
    {
        $this->requireAuth();
        $item = $this->Product_review_model->get_owned($this->store->id, $id);
        if (!$item) {
            $this->session->set_flashdata('error', 'Review not found.');
            redirect('store/reviews');
            return;
        }
        $this->Product_review_model->set_status($this->store->id, $id, ((int) $item->status === 1) ? 0 : 1);
        $this->session->set_flashdata('success', ((int) $item->status === 1) ? 'Review disabled.' : 'Review enabled.');
        redirect('store/reviews');
    }

    public function delete($id = 0)
    {
        $this->requireAuth();
        if (!$this->Product_review_model->delete_owned($this->store->id, $id)) {
            $this->session->set_flashdata('error', 'Review not found.');
            redirect('store/reviews');
            return;
        }
        $this->session->set_flashdata('success', 'Review deleted.');
        redirect('store/reviews');
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
