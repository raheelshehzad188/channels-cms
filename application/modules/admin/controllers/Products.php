<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Products extends CI_Controller {

    public function __construct()
    {
        parent::__construct();
        ec_require_products();
        $this->load->model('Product_model');
        $this->load->model('Supplier_model');
    }

    public function index()
    {
        $this->load->model('Theme_model');
        $this->load->model('Country_model');
        $ownerId = ec_is_ecommerce() ? (int) ec_user()->UserID : 0;
        $data = array(
            'title' => 'Products',
            'products' => $this->Product_model->all($ownerId),
            'themes' => $this->Theme_model->all(),
            'countries' => $this->Country_model->all(),
        );
        $this->template->admin('products/index', $data);
    }

    public function import()
    {
        $this->load->model('Country_model');
        $data = array(
            'title' => 'Import Product',
            'countries' => $this->Country_model->all(),
        );
        $this->template->admin('products/import', $data);
    }

    public function import_preview()
    {
        $countryId = (int) $this->input->post('country_id');
        $url = trim((string) $this->input->post('source_url'));
        $this->load->library('Product_importer');
        try {
            $result = $this->product_importer->preview($url, $countryId, (int) ec_user()->UserID);
            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode(array(
                    'ok' => true,
                    'name' => $result['name'],
                    'sku' => $result['sku'],
                    'cost' => $result['cost'],
                )));
        } catch (Exception $e) {
            $this->output
                ->set_status_header(400)
                ->set_content_type('application/json')
                ->set_output(json_encode(array(
                    'ok' => false,
                    'error' => $e->getMessage(),
                )));
        }
    }

    public function import_save()
    {
        $countryId = (int) $this->input->post('country_id');
        $url = trim((string) $this->input->post('source_url'));
        $basePrice = trim((string) $this->input->post('base_price'));
        $costOverride = $basePrice !== '' ? (float) $basePrice : null;
        $this->load->model('Product_import_model');
        $this->load->library('Product_importer');
        try {
            $result = $this->product_importer->import($url, $countryId, (int) ec_user()->UserID, $costOverride);
        } catch (Exception $e) {
            $this->session->set_flashdata('error', $e->getMessage());
            redirect('/admin/products/import');
            return;
        }

        if (!empty($result['existing'])) {
            $this->session->set_flashdata('success', 'This URL is already imported. Review the product below.');
        } else {
            $this->session->set_flashdata('success', 'Product imported successfully. Review and save any changes.');
        }
        redirect('/admin/products/form/' . (int) $result['product_id']);
    }

    public function csv_template()
    {
        $this->load->model('Product_import_model');
        $header = $this->Product_import_model->csv_template_header();
        $sample = 'Wireless earbuds,https://www.amazon.co.uk/dp/B0EXAMPLE,Electronics,Audio,19.99,3,7';
        $csv = $header . "\n" . $sample . "\n";
        $this->output
            ->set_content_type('text/csv', 'utf-8')
            ->set_header('Content-Disposition: attachment; filename="product_import_template.csv"')
            ->set_output($csv);
    }

    public function csv_preview()
    {
        $this->load->model('Country_model');
        $this->load->model('Product_import_model');

        $countryId = (int) $this->input->post('country_id');
        $country = $this->Country_model->get($countryId);
        if (!$country) {
            $this->json_out(array('ok' => false, 'error' => 'Please select a country before uploading the CSV.'), 400);
            return;
        }

        $parsed = $this->read_product_csv();
        if (empty($parsed['ok'])) {
            $this->json_out($parsed, 400);
            return;
        }

        try {
            $batch = $this->Product_import_model->store_batch(
                (int) $country->id,
                $country->name,
                (int) ec_user()->UserID,
                $parsed['rows']
            );
        } catch (Exception $e) {
            $this->json_out(array('ok' => false, 'error' => $e->getMessage()), 500);
            return;
        }

        $valid = 0;
        $invalid = 0;
        $previewRows = array();
        foreach ($batch['rows'] as $row) {
            if (!empty($row['valid'])) {
                $valid++;
            } else {
                $invalid++;
            }
            if (count($previewRows) < 300) {
                $previewRows[] = array(
                    'line' => (int) $row['line'],
                    'product' => $row['product'],
                    'link' => $row['link'],
                    'category' => $row['category'],
                    'sub_category' => $row['sub_category'],
                    'price' => $row['price'],
                    'delivery_min_days' => $row['delivery_min_days'],
                    'delivery_max_days' => $row['delivery_max_days'],
                    'valid' => !empty($row['valid']),
                    'error' => isset($row['error']) ? $row['error'] : '',
                );
            }
        }

        $this->json_out(array(
            'ok' => true,
            'token' => $batch['token'],
            'country_id' => (int) $country->id,
            'country_name' => $country->name,
            'total' => count($batch['rows']),
            'valid' => $valid,
            'invalid' => $invalid,
            'rows' => $previewRows,
            'preview_truncated' => count($batch['rows']) > 300,
        ));
    }

    public function csv_import_row()
    {
        @set_time_limit(90);
        $this->load->model('Product_import_model');
        $this->load->library('Product_importer');

        $token = (string) $this->input->post('token');
        $index = (int) $this->input->post('index');
        $found = $this->Product_import_model->batch_row($token, (int) ec_user()->UserID, $index);
        if (!$found) {
            $this->json_out(array('ok' => false, 'error' => 'Import session expired. Please upload the CSV again.'), 400);
            return;
        }

        $batch = $found['batch'];
        $row = $found['row'];
        $total = (int) $found['total'];
        $countryId = (int) $batch['country_id'];
        $base = array(
            'ok' => true,
            'index' => $index,
            'line' => isset($row['line']) ? (int) $row['line'] : ($index + 2),
            'product' => isset($row['product']) ? $row['product'] : '',
            'link' => isset($row['link']) ? $row['link'] : '',
            'total' => $total,
            'done' => ($index + 1) >= $total,
        );

        if (empty($row['valid'])) {
            $this->json_out($base + array(
                'status' => 'failed',
                'error' => !empty($row['error']) ? $row['error'] : 'This row failed CSV validation.',
            ));
            if (!empty($base['done'])) {
                $this->Product_import_model->delete_batch($token);
            }
            return;
        }

        try {
            $result = $this->product_importer->import_csv_row(
                $row['link'],
                $countryId,
                (int) ec_user()->UserID,
                $row
            );
            $status = !empty($result['skipped']) ? 'skipped' : 'imported';
            $this->json_out($base + array(
                'status' => $status,
                'product_id' => isset($result['product_id']) ? (int) $result['product_id'] : 0,
                'message' => isset($result['message']) ? $result['message'] : '',
            ));
        } catch (Exception $e) {
            $this->json_out($base + array(
                'status' => 'failed',
                'error' => $e->getMessage(),
            ));
        }

        if (!empty($base['done'])) {
            $this->Product_import_model->delete_batch($token);
        }
    }

    protected function read_product_csv()
    {
        if (empty($_FILES['csv']['name']) || empty($_FILES['csv']['tmp_name'])) {
            return array('ok' => false, 'error' => 'Please select a CSV file.');
        }
        if (!empty($_FILES['csv']['error']) && (int) $_FILES['csv']['error'] !== UPLOAD_ERR_OK) {
            return array('ok' => false, 'error' => 'Could not upload the CSV file.');
        }
        if ((int) $_FILES['csv']['size'] > 10 * 1024 * 1024) {
            return array('ok' => false, 'error' => 'CSV file is too large (max 10 MB).');
        }
        $ext = strtolower(pathinfo($_FILES['csv']['name'], PATHINFO_EXTENSION));
        if ($ext !== 'csv') {
            return array('ok' => false, 'error' => 'Please upload a .csv file.');
        }
        if (!is_uploaded_file($_FILES['csv']['tmp_name'])) {
            return array('ok' => false, 'error' => 'Invalid upload.');
        }
        return $this->Product_import_model->parse_csv_file($_FILES['csv']['tmp_name']);
    }

    protected function json_out($data, $code = 200)
    {
        $this->output
            ->set_status_header((int) $code)
            ->set_content_type('application/json', 'utf-8')
            ->set_output(json_encode($data));
    }

    public function fetch_details($id = 0)
    {
        $product = $this->Product_model->get($id);
        if (!$product || !$this->_can_manage($product)) {
            $this->output
                ->set_status_header(403)
                ->set_content_type('application/json')
                ->set_output(json_encode(array('ok' => false, 'error' => 'You cannot edit this product.')));
            return;
        }

        $urls = array();
        if (!empty($product->source_url)) {
            $urls[] = $product->source_url;
        }
        foreach ($this->Product_model->sources($product->id) as $source) {
            if (!empty($source->source_url)) {
                $urls[] = $source->source_url;
            }
        }
        $urls = array_values(array_unique(array_filter($urls)));
        if (!$urls) {
            $this->output
                ->set_status_header(400)
                ->set_content_type('application/json')
                ->set_output(json_encode(array('ok' => false, 'error' => 'Add a source link first.')));
            return;
        }

        $this->load->library('Product_importer');
        $lastError = 'Could not read details from this URL.';
        foreach ($urls as $url) {
            try {
                $html = $this->product_importer->details_from_url($url);
                if ($html !== '') {
                    $this->output
                        ->set_content_type('application/json')
                        ->set_output(json_encode(array('ok' => true, 'details' => $html)));
                    return;
                }
            } catch (Exception $e) {
                $lastError = $e->getMessage();
            }
        }

        $this->output
            ->set_status_header(400)
            ->set_content_type('application/json')
            ->set_output(json_encode(array('ok' => false, 'error' => $lastError)));
    }

    public function unknown()
    {
        if (!ec_is_admin()) {
            $this->session->set_flashdata('error', 'You do not have permission to view unknown import links.');
            redirect('/admin/products');
            return;
        }
        $this->load->model('Product_import_model');
        $data = array(
            'title' => 'Unknown Import Links',
            'links' => $this->Product_import_model->unknown_links(),
        );
        $this->template->admin('products/unknown', $data);
    }

    public function unknown_delete($id = 0)
    {
        if (!ec_is_admin()) {
            $this->session->set_flashdata('error', 'You do not have permission to manage unknown import links.');
            redirect('/admin/products');
            return;
        }
        $this->load->model('Product_import_model');
        $this->Product_import_model->delete_unknown($id);
        $this->session->set_flashdata('success', 'Unknown link removed.');
        redirect('/admin/products/unknown');
    }

    public function form($id = 0)
    {

        $product = $id ? $this->Product_model->get($id) : null;
        if ($id && !$product) {
            $this->session->set_flashdata('error', 'Product not found.');
            redirect('/admin/products');
            return;
        }
        if ($product && !$this->_can_manage($product)) {
            $this->session->set_flashdata('error', 'You do not have permission to edit this product.');
            redirect('/admin/products');
            return;
        }

        $this->load->model('Country_model');
        $this->load->model('User_model');
        $ecommerceUsers = array();
        foreach ($this->User_model->all() as $user) {
            if ((int) $user->roleID === ROLE_ECOMMERCE) {
                $ecommerceUsers[] = $user;
            }
        }
        $storeCopies = $product ? $this->Product_model->store_copies($product->id) : array();
        $data = array(
            'title' => $product ? 'Edit Product' : 'Add Product',
            'product' => $product,
            'suppliers' => $this->Supplier_model->all(),
            'countries' => $this->Country_model->all(),
            'ecommerce_users' => $ecommerceUsers,
            'images' => $product ? $this->Product_model->images($product->id) : array(),
            'attributes' => $product ? $this->Product_model->attributes($product->id) : array(),
            'variations' => $product ? $this->Product_model->variations($product->id) : array(),
            'sources' => $product ? $this->Product_model->sources($product->id) : array(),
            'store_copies' => $storeCopies,
            'price_locked' => $product && ec_is_ecommerce() && !empty($storeCopies),
        );
        $this->template->admin('products/form', $data);
    }

    public function view($id = 0)
    {
        $product = $this->Product_model->get($id);
        if (!$product) {
            $this->session->set_flashdata('error', 'Product not found.');
            redirect('/admin/products');
            return;
        }

        $data = array(
            'title' => 'Product Detail',
            'product' => $product,
        );
        $this->template->admin('products/view', $data);
    }

    public function preview($id = 0, $slug = '')
    {
        $this->load->model('Theme_model');
        $product = $this->Product_model->get($id);
        $theme = $this->Theme_model->get_by_slug($slug);
        if (!$product || !$theme) {
            show_404();
        }

        $settings = array();
        foreach ($this->Theme_model->fields($theme->id) as $field) {
            $settings[$field->field_key] = $field->default_value;
        }

        $storeRow = $this->db->where('theme_id', $theme->id)->where('status', 1)->get('stores')->row();
        if ($storeRow) {
            $rows = $this->db->where('store_id', $storeRow->id)->get('store_settings')->result();
            foreach ($rows as $row) {
                if ($row->field_value !== '') {
                    $settings[$row->field_key] = $row->field_value;
                }
            }
        }

        $data = array(
            'title' => $product->name . ' — ' . $theme->name,
            'product' => $product,
            'theme' => $theme,
            'settings' => $settings,
            'store' => (object) array(
                'name' => $storeRow ? $storeRow->name : $theme->name,
                'domain' => $storeRow ? $storeRow->domain : '',
            ),
            'assets' => base_url('assets/frontend/' . $theme->slug . '/'),
            'is_preview' => true,
            'preview_back' => base_url('admin/products'),
            'current_page' => 'detail',
            'products' => $this->Product_model->all_active(8),
            'product_images' => $this->Product_model->images($product->id),
        );

        $this->load->view('frontend/' . $theme->slug . '/header', $data);
        $this->load->view('frontend/' . $theme->slug . '/detail', $data);
        $this->load->view('frontend/' . $theme->slug . '/footer', $data);
    }

    public function save($id = 0)
    {
        $existing = null;
        if ($id) {
            $existing = $this->Product_model->get($id);
            if (!$existing || !$this->_can_manage($existing)) {
                $this->session->set_flashdata('error', 'You do not have permission to edit this product.');
                redirect('/admin/products');
                return;
            }
        }

        $priceLocked = !empty($existing) && $this->_price_locked($existing);

        $this->form_validation->set_rules('name', 'Product Name', 'required|trim');
        if (!$priceLocked) {
            $this->form_validation->set_rules('price', 'Price', 'required|numeric');
        }
        $this->form_validation->set_rules('stock', 'Stock', 'required|integer');
        $this->form_validation->set_rules('country_id', 'Country', 'required|integer');

        if ($this->form_validation->run() === FALSE) {
            $this->session->set_flashdata('error', validation_errors());
            redirect($id ? '/admin/products/form/' . $id : '/admin/products/form');
            return;
        }

        $payload = array(
            'name' => trim($this->input->post('name')),
            'sku' => trim($this->input->post('sku')),
            'slug' => $this->_slug($this->input->post('slug'), $this->input->post('name')),
            'stock' => (int) $this->input->post('stock'),
            'supplier_id' => $this->input->post('supplier_id') ? (int) $this->input->post('supplier_id') : null,
            'country_id' => $this->input->post('country_id') ? (int) $this->input->post('country_id') : null,
            'ship_min_days' => max(0, (int) $this->input->post('ship_min_days')),
            'ship_max_days' => max(0, (int) $this->input->post('ship_max_days')),
            'description' => trim($this->input->post('description')),
            'details' => ec_sanitize_product_html($this->input->post('details')),
            'seo_title' => trim($this->input->post('seo_title')),
            'seo_description' => trim($this->input->post('seo_description')),
            'seo_keywords' => trim($this->input->post('seo_keywords')),
            'status' => (int) $this->input->post('status') === 1 ? 1 : 0,
        );
        if (!$priceLocked) {
            $payload['price'] = (float) $this->input->post('price');
            $payload['compare_price'] = (float) $this->input->post('compare_price');
            $payload['cost_price'] = (float) $this->input->post('cost_price');
            $payload['max_sale_price'] = (float) $this->input->post('max_sale_price');
        }

        if (ec_is_ecommerce()) {
            $payload['created_by'] = (int) ec_user()->UserID;
        } elseif ($this->input->post('created_by')) {
            $payload['created_by'] = (int) $this->input->post('created_by');
        } elseif (!$id) {
            $payload['created_by'] = (int) ec_user()->UserID;
        }

        $image = $this->_upload_named('image');
        if ($image) {
            $payload['image'] = $image;
        }

        $productId = $this->Product_model->save($payload, $id);
        $this->_save_gallery($productId);
        $this->_save_attributes($productId);
        $this->_save_variations($productId, $priceLocked);
        $this->_save_sources($productId);

        $this->session->set_flashdata('success', $id ? 'Product updated successfully.' : 'Product added successfully.');
        redirect('/admin/products/form/' . $productId);
    }

    public function delete_image($id = 0)
    {
        $image = $this->Product_model->get_image($id);
        if (!$image) {
            $this->session->set_flashdata('error', 'Image not found.');
            redirect('/admin/products');
            return;
        }
        $product = $this->Product_model->get($image->product_id);
        if (!$product || !$this->_can_manage($product)) {
            $this->session->set_flashdata('error', 'You do not have permission to delete this image.');
            redirect('/admin/products');
            return;
        }

        $path = FCPATH . ltrim($image->image, '/');
        if (is_file($path)) {
            @unlink($path);
        }
        $this->Product_model->delete_image($id);
        $this->session->set_flashdata('success', 'Image deleted.');
        redirect('/admin/products/form/' . $image->product_id);
    }

    public function delete($id = 0)
    {
        $product = $this->Product_model->get($id);
        if (!$product || !$this->_can_manage($product)) {
            $this->session->set_flashdata('error', 'You do not have permission to delete this product.');
            redirect('/admin/products');
            return;
        }

        if (!empty($product->image)) {
            $path = FCPATH . ltrim($product->image, '/');
            if (is_file($path)) {
                @unlink($path);
            }
        }
        foreach ($this->Product_model->images($id) as $image) {
            $imgPath = FCPATH . ltrim($image->image, '/');
            if ($image->image && is_file($imgPath)) {
                @unlink($imgPath);
            }
        }

        $this->Product_model->delete($id);
        $this->session->set_flashdata('success', 'Product deleted successfully.');
        redirect('/admin/products');
    }

    private function _save_gallery($productId)
    {
        if (empty($_FILES['gallery']['name']) || !is_array($_FILES['gallery']['name'])) {
            return;
        }

        $dir = FCPATH . 'uploads/products/';
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $count = count($_FILES['gallery']['name']);
        for ($i = 0; $i < $count; $i++) {
            if (empty($_FILES['gallery']['name'][$i]) || (int) $_FILES['gallery']['error'][$i] !== 0) {
                continue;
            }

            $_FILES['gallery_item'] = array(
                'name' => $_FILES['gallery']['name'][$i],
                'type' => $_FILES['gallery']['type'][$i],
                'tmp_name' => $_FILES['gallery']['tmp_name'][$i],
                'error' => $_FILES['gallery']['error'][$i],
                'size' => $_FILES['gallery']['size'][$i],
            );

            $path = $this->_upload_named('gallery_item');
            if ($path) {
                $this->Product_model->add_image($productId, $path);
            }
        }
    }

    private function _save_attributes($productId)
    {
        $rows = $this->input->post('attributes');
        $clean = array();
        if (is_array($rows)) {
            foreach ($rows as $i => $row) {
                $name = trim(isset($row['name']) ? $row['name'] : '');
                $values = trim(isset($row['values']) ? $row['values'] : '');
                if ($name === '' || $values === '') {
                    continue;
                }
                $clean[] = array(
                    'name' => $name,
                    'values_text' => $values,
                    'sort_order' => (int) $i,
                );
            }
        }
        $this->Product_model->replace_attributes($productId, $clean);
    }

    private function _save_variations($productId, $lockPrices = false)
    {
        $existingPrices = array();
        $fallbackPrice = 0.0;
        if ($lockPrices) {
            $product = $this->Product_model->get($productId);
            $fallbackPrice = $product ? (float) $product->price : 0.0;
            foreach ($this->Product_model->variations($productId) as $variation) {
                $key = !empty($variation->combination_key)
                    ? $variation->combination_key
                    : ($variation->option_name . ':' . $variation->option_value);
                $existingPrices[$key] = (float) $variation->price;
            }
        }

        $rows = $this->input->post('variations');
        $clean = array();
        if (is_array($rows)) {
            foreach ($rows as $i => $row) {
                $optionName = trim(isset($row['option_name']) ? $row['option_name'] : '');
                $optionValue = trim(isset($row['option_value']) ? $row['option_value'] : '');
                $combinationKey = trim(isset($row['combination_key']) ? $row['combination_key'] : '');
                $attributesJson = trim(isset($row['attributes_json']) ? $row['attributes_json'] : '');
                if ($optionValue === '' && $combinationKey === '') {
                    continue;
                }
                $image = $this->_upload_variation_image($i);
                if ($image === '' && !empty($row['image'])) {
                    $image = trim($row['image']);
                }
                $price = (float) (isset($row['price']) ? $row['price'] : 0);
                if ($lockPrices) {
                    if ($combinationKey !== '' && isset($existingPrices[$combinationKey])) {
                        $price = $existingPrices[$combinationKey];
                    } elseif (isset($existingPrices[$optionName . ':' . $optionValue])) {
                        $price = $existingPrices[$optionName . ':' . $optionValue];
                    } else {
                        $price = $fallbackPrice;
                    }
                }
                $clean[] = array(
                    'option_name' => $optionName,
                    'option_value' => $optionValue,
                    'sku' => trim(isset($row['sku']) ? $row['sku'] : ''),
                    'price' => $price,
                    'stock' => (int) (isset($row['stock']) ? $row['stock'] : 0),
                    'combination_key' => $combinationKey,
                    'attributes_json' => $attributesJson,
                    'image' => $image,
                );
            }
        }
        $this->Product_model->replace_variations($productId, $clean);
    }

    private function _save_sources($productId)
    {
        $rows = $this->input->post('sources');
        $clean = array();
        if (is_array($rows)) {
            foreach ($rows as $row) {
                $url = trim(isset($row['source_url']) ? $row['source_url'] : '');
                if ($url === '') {
                    continue;
                }
                $clean[] = array(
                    'source_url' => $url,
                    'source_price' => isset($row['source_price']) ? (float) $row['source_price'] : 0,
                    'label' => isset($row['label']) ? trim((string) $row['label']) : '',
                );
            }
        }
        $this->Product_model->replace_sources($productId, $clean);
    }

    private function _upload_variation_image($index)
    {
        $field = null;
        if (!empty($_FILES['variation_image']['name']) && is_array($_FILES['variation_image']['name']) && !empty($_FILES['variation_image']['name'][$index]) && (int) $_FILES['variation_image']['error'][$index] === 0) {
            $field = 'variation_item_' . $index;
            $_FILES[$field] = array(
                'name' => $_FILES['variation_image']['name'][$index],
                'type' => $_FILES['variation_image']['type'][$index],
                'tmp_name' => $_FILES['variation_image']['tmp_name'][$index],
                'error' => $_FILES['variation_image']['error'][$index],
                'size' => $_FILES['variation_image']['size'][$index],
            );
        } elseif (!empty($_FILES['variation_image']['name']) && !is_array($_FILES['variation_image']['name']) && (int) $index === 0) {
            $field = 'variation_image';
        }
        return $field ? $this->_upload_named($field) : '';
    }

    private function _slug($slug, $name)
    {
        $slug = trim((string) $slug);
        if ($slug === '') {
            $slug = $name;
        }
        return url_title($slug, '-', true);
    }

    private function _upload_named($field)
    {
        if (empty($_FILES[$field]['name'])) {
            return '';
        }

        $dir = FCPATH . 'uploads/products/';
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $config = array(
            'upload_path' => $dir,
            'allowed_types' => 'jpg|jpeg|png|gif|webp',
            'max_size' => 4096,
            'encrypt_name' => true,
        );
        $this->load->library('upload', $config);
        $this->upload->initialize($config);
        if (!$this->upload->do_upload($field)) {
            return '';
        }

        $uploaded = $this->upload->data();
        return 'uploads/products/' . $uploaded['file_name'];
    }

    private function _can_manage($product)
    {
        if (ec_is_admin()) {
            return true;
        }
        if (!$product) {
            return false;
        }
        return (int) $product->created_by === (int) ec_user()->UserID;
    }

    private function _price_locked($product)
    {
        return $product && ec_is_ecommerce() && $this->Product_model->is_picked_by_store($product->id);
    }
}
