<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Hunting extends CI_Controller {

    public function __construct()
    {
        parent::__construct();
        ec_require_products();
        $this->load->model('Product_hunting_model');
        $this->load->model('Country_model');
    }

    public function index()
    {
        $q = trim((string) $this->input->get('q'));
        $statusRaw = $this->input->get('status');
        $countryId = (int) $this->input->get('country_id');
        $filters = array('q' => $q);
        if ($statusRaw !== null && $statusRaw !== '') {
            $filters['status'] = (int) $statusRaw;
        }
        if ($countryId) {
            $filters['country_id'] = $countryId;
        }

        $data = array(
            'title' => 'Product Hunting',
            'items' => $this->Product_hunting_model->all($filters),
            'countries' => $this->Country_model->all(),
            'q' => $q,
            'status' => ($statusRaw === null || $statusRaw === '') ? '' : (int) $statusRaw,
            'country_id' => $countryId,
            'can_manage' => $this->can_manage(),
        );
        $this->template->admin('hunting/index', $data);
    }

    public function form($id = 0)
    {
        if (!$this->can_manage()) {
            $this->session->set_flashdata('error', 'You cannot add or edit hunting products.');
            redirect('admin/hunting');
            return;
        }

        $item = $id ? $this->Product_hunting_model->get($id) : null;
        if ($id && !$item) {
            $this->session->set_flashdata('error', 'Hunting product not found.');
            redirect('admin/hunting');
            return;
        }

        $data = array(
            'title' => $item ? 'Edit Hunting Product' : 'Add Hunting Product',
            'item' => $item,
            'countries' => $this->Country_model->all(),
        );
        $this->template->admin('hunting/form', $data);
    }

    public function view($id = 0)
    {
        $item = $id ? $this->Product_hunting_model->get($id) : null;
        if (!$item) {
            $this->session->set_flashdata('error', 'Hunting product not found.');
            redirect('admin/hunting');
            return;
        }

        $data = array(
            'title' => $item->title,
            'item' => $item,
            'can_manage' => $this->can_manage(),
        );
        $this->template->admin('hunting/view', $data);
    }

    public function save($id = 0)
    {
        if (!$this->can_manage()) {
            $this->session->set_flashdata('error', 'You cannot save hunting products.');
            redirect('admin/hunting');
            return;
        }

        $existing = $id ? $this->Product_hunting_model->get($id) : null;
        if ($id && !$existing) {
            $this->session->set_flashdata('error', 'Hunting product not found.');
            redirect('admin/hunting');
            return;
        }

        $title = trim((string) $this->input->post('title'));
        if ($title === '') {
            $this->session->set_flashdata('error', 'Title is required.');
            redirect($id ? 'admin/hunting/form/' . $id : 'admin/hunting/form');
            return;
        }

        $image = $this->upload_image();
        if ($image === false) {
            $this->session->set_flashdata('error', 'Image upload failed. Use JPG, PNG, GIF, WEBP or AVIF.');
            redirect($id ? 'admin/hunting/form/' . $id : 'admin/hunting/form');
            return;
        }
        if ($image === '' && (!$existing || empty($existing->image))) {
            $this->session->set_flashdata('error', 'Product image is required.');
            redirect($id ? 'admin/hunting/form/' . $id : 'admin/hunting/form');
            return;
        }

        $payload = array(
            'title' => $title,
            'notes' => trim((string) $this->input->post('notes')),
            'status' => (int) $this->input->post('status') === 1 ? 1 : 0,
        );
        if ($image !== '') {
            $payload['image'] = $image;
            if ($existing && !empty($existing->image) && $existing->image !== $image) {
                $this->delete_image_file($existing->image);
            }
        }
        if (!$id) {
            $payload['created_by'] = (int) ec_user()->UserID;
        }

        $savedId = $this->Product_hunting_model->save($payload, $id);
        $this->Product_hunting_model->save_creatives($savedId, $this->input->post('creative_links'));
        $this->Product_hunting_model->save_suppliers($savedId, $this->posted_suppliers());

        $this->session->set_flashdata('success', $id ? 'Hunting product updated.' : 'Hunting product added.');
        redirect('admin/hunting');
    }

    public function delete($id = 0)
    {
        if (!$this->can_manage()) {
            $this->session->set_flashdata('error', 'You cannot delete hunting products.');
            redirect('admin/hunting');
            return;
        }

        $row = $this->Product_hunting_model->delete($id);
        if (!$row) {
            $this->session->set_flashdata('error', 'Hunting product not found.');
            redirect('admin/hunting');
            return;
        }
        if (!empty($row->image)) {
            $this->delete_image_file($row->image);
        }
        $this->session->set_flashdata('success', 'Hunting product deleted.');
        redirect('admin/hunting');
    }

    protected function can_manage()
    {
        return ec_is_admin() || ec_is_ecommerce();
    }

    protected function posted_suppliers()
    {
        $countryIds = $this->input->post('supplier_country_id');
        $links = $this->input->post('supplier_links');
        $rows = array();
        if (!is_array($countryIds) || !is_array($links)) {
            return $rows;
        }
        $count = max(count($countryIds), count($links));
        for ($i = 0; $i < $count; $i++) {
            $rows[] = array(
                'country_id' => isset($countryIds[$i]) ? $countryIds[$i] : 0,
                'link' => isset($links[$i]) ? $links[$i] : '',
            );
        }
        return $rows;
    }

    protected function upload_image()
    {
        if (empty($_FILES['image']['name'])) {
            return '';
        }

        $dir = FCPATH . 'uploads/hunting/';
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $config = array(
            'upload_path' => $dir,
            'allowed_types' => 'jpg|jpeg|png|gif|webp|avif',
            'max_size' => 0,
            'encrypt_name' => true,
        );
        $this->load->library('upload', $config);
        $this->upload->initialize($config);
        if (!$this->upload->do_upload('image')) {
            return false;
        }

        $uploaded = $this->upload->data();
        return 'uploads/hunting/' . $uploaded['file_name'];
    }

    protected function delete_image_file($path)
    {
        $path = ltrim((string) $path, '/');
        if ($path === '' || strpos($path, 'uploads/hunting/') !== 0) {
            return;
        }
        $full = FCPATH . $path;
        if (is_file($full)) {
            @unlink($full);
        }
    }
}
