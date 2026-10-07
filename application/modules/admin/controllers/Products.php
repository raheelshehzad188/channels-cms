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
        if (ec_is_admin()) {
            $this->Product_model->purge_orphan_store_copies();
        }
        $this->load->model('Theme_model');
        $this->load->model('Country_model');
        $this->load->model('Ec_category_model');
        $this->load->model('User_model');
        $ownerId = ec_is_ecommerce() ? (int) ec_user()->UserID : 0;
        $countryId = (int) $this->input->get('country_id');
        $categoryId = (int) $this->input->get('category_id');
        $createdBy = (int) $this->input->get('created_by');
        $q = trim((string) $this->input->get('q'));
        $trending = trim((string) $this->input->get('trending'));
        if ($trending !== '1' && $trending !== '0') {
            $trending = '';
        }
        $ecommerceUsers = ec_is_admin() ? $this->User_model->ecommerce_users() : array();
        if (ec_is_admin() && $createdBy) {
            $validOwner = false;
            foreach ($ecommerceUsers as $owner) {
                if ((int) $owner->UserID === $createdBy) {
                    $validOwner = true;
                    break;
                }
            }
            $ownerId = $validOwner ? $createdBy : 0;
            if (!$validOwner) {
                $createdBy = 0;
            }
        } else {
            $createdBy = $ownerId;
        }

        $categoryFilters = array();
        if ($countryId) {
            $categoryFilters['country_id'] = $countryId;
        }
        $categories = $this->Ec_category_model->all($categoryFilters);
        if ($categoryId) {
            $valid = false;
            foreach ($categories as $cat) {
                if ((int) $cat->id === $categoryId) {
                    $valid = true;
                    break;
                }
            }
            if (!$valid) {
                $categoryId = 0;
            }
        }

        $filters = array(
            'country_id' => $countryId,
            'category_id' => $categoryId,
            'q' => $q,
            'trending' => $trending,
        );
        $perPageOptions = array(10, 25, 50, 100);
        $requestedPerPage = $this->input->get('per_page');
        $requestedPage = $this->input->get('page');
        $perPage = (int) $requestedPerPage;
        if (!in_array($perPage, $perPageOptions, true)) {
            $cookiePerPage = (int) $this->input->cookie('admin_products_per_page');
            $perPage = in_array($cookiePerPage, $perPageOptions, true) ? $cookiePerPage : 25;
        }
        $this->load->helper('cookie');
        $this->input->set_cookie(array(
            'name' => 'admin_products_per_page',
            'value' => (string) $perPage,
            'expire' => 30 * 24 * 3600,
            'path' => '/',
        ));
        $total = $this->Product_model->count_filtered($ownerId, $filters);
        $totalPages = $total > 0 ? (int) ceil($total / $perPage) : 1;
        $page = (int) $requestedPage;
        if ($page < 1) {
            $page = 1;
        }
        if ($page > $totalPages) {
            $page = $totalPages;
        }
        $offset = ($page - 1) * $perPage;
        $filters['limit'] = $perPage;
        $filters['offset'] = $offset;
        $from = $total > 0 ? ($offset + 1) : 0;
        $to = min($offset + $perPage, $total);
        $listQuery = array(
            'q' => $q,
            'country_id' => $countryId,
            'category_id' => $categoryId,
            'created_by' => $createdBy,
            'trending' => $trending,
            'per_page' => $perPage,
        );
        $pageUrl = function ($pageNum) use ($listQuery) {
            $query = $listQuery;
            $query['page'] = max(1, (int) $pageNum);
            return base_url('admin/products') . '?' . http_build_query($query);
        };
        $canonicalNeeded = ($requestedPage === false || $requestedPage === null || $requestedPerPage === false || $requestedPerPage === null
            || (int) $requestedPage !== $page
            || (int) $requestedPerPage !== $perPage);
        if ($canonicalNeeded) {
            redirect($pageUrl($page));
            return;
        }
        $window = 2;
        $pageStart = max(1, $page - $window);
        $pageEnd = min($totalPages, $page + $window);
        $pageNumbers = array();
        if ($pageStart > 1) {
            $pageNumbers[] = array('label' => 1, 'url' => $pageUrl(1), 'current' => false);
            if ($pageStart > 2) {
                $pageNumbers[] = array('label' => '…', 'url' => '', 'current' => false);
            }
        }
        for ($i = $pageStart; $i <= $pageEnd; $i++) {
            $pageNumbers[] = array(
                'label' => $i,
                'url' => $pageUrl($i),
                'current' => ($i === $page),
            );
        }
        if ($pageEnd < $totalPages) {
            if ($pageEnd < $totalPages - 1) {
                $pageNumbers[] = array('label' => '…', 'url' => '', 'current' => false);
            }
            $pageNumbers[] = array('label' => $totalPages, 'url' => $pageUrl($totalPages), 'current' => false);
        }

        $data = array(
            'title' => 'Products',
            'products' => $this->Product_model->all($ownerId, $filters),
            'themes' => $this->Theme_model->all(),
            'countries' => $this->Country_model->all(),
            'categories' => $categories,
            'ecommerce_users' => $ecommerceUsers,
            'country_id' => $countryId,
            'category_id' => $categoryId,
            'created_by' => $createdBy,
            'q' => $q,
            'trending' => $trending,
            'list_page' => $page,
            'per_page' => $perPage,
            'per_page_options' => $perPageOptions,
            'total' => $total,
            'total_pages' => $totalPages,
            'from_row' => $from,
            'to_row' => $to,
            'page_numbers' => $pageNumbers,
            'first_page_url' => $pageUrl(1),
            'prev_page_url' => $pageUrl($page - 1),
            'next_page_url' => $pageUrl($page + 1),
            'last_page_url' => $pageUrl($totalPages),
            'has_prev_page' => $page > 1,
            'has_next_page' => $page < $totalPages,
        );
        $this->template->admin('products/index', $data);
    }

    public function import()
    {
        $this->load->model('Country_model');
        $prefs = $this->import_prefs();
        $data = array(
            'title' => 'Import Product',
            'countries' => $this->Country_model->all(),
            'import_prefs' => $prefs,
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
                    'country_id' => isset($result['country_id']) ? (int) $result['country_id'] : $countryId,
                    'country_name' => isset($result['country_name']) ? $result['country_name'] : '',
                )));
        } catch (Exception $e) {
            $this->record_import_failure($url, $e->getMessage());
            $this->output
                ->set_status_header(400)
                ->set_content_type('application/json')
                ->set_output(json_encode(array(
                    'ok' => false,
                    'error' => $e->getMessage(),
                )));
        }
    }

    public function import_source()
    {
        $url = trim((string) $this->input->post('source_url'));
        $this->load->library('Product_importer');
        try {
            $info = $this->product_importer->source_info($url);
            $this->json_out(array(
                'ok' => true,
                'host' => $info['host'],
                'country_id' => (int) $info['country_id'],
                'country_name' => $info['country_name'],
                'known' => !empty($info['known']),
            ));
        } catch (Exception $e) {
            $this->json_out(array('ok' => false, 'error' => $e->getMessage()), 400);
        }
    }

    public function import_categories()
    {
        $countryId = (int) $this->input->get('country_id');
        if (!$countryId) {
            $countryId = (int) $this->input->post('country_id');
        }
        $this->load->model('Ec_category_model');
        $this->json_out(array('ok' => true, 'categories' => $this->Ec_category_model->tree_for_country($countryId)));
    }

    public function import_save()
    {
        $countryId = (int) $this->input->post('country_id');
        $url = trim((string) $this->input->post('source_url'));
        $basePrice = trim((string) $this->input->post('base_price'));
        $costOverride = $basePrice !== '' ? (float) $basePrice : null;
        $categoryId = (int) $this->input->post('category_id');
        $subcategoryId = (int) $this->input->post('subcategory_id');
        $shipMin = max(0, (int) $this->input->post('ship_min_days'));
        $shipMax = max(0, (int) $this->input->post('ship_max_days'));
        $stockRaw = trim((string) $this->input->post('stock'));
        $autoAdd = $this->input->post('auto_add_to_stores') ? 1 : 0;
        $creativeLinks = $this->posted_creative_links();
        $this->load->model('Product_import_model');
        $this->load->library('Product_importer');
        $countryId = $this->product_importer->country_id_for_url($url, $countryId);
        $this->remember_import_prefs($countryId);
        $extra = array(
            'category_id' => $categoryId,
            'subcategory_id' => $subcategoryId,
            'ship_min_days' => $shipMin,
            'ship_max_days' => $shipMax,
            'auto_add_to_stores' => $autoAdd,
            'creative_links' => $creativeLinks,
        );
        if ($stockRaw !== '') {
            $extra['stock'] = max(0, (int) $stockRaw);
        }
        try {
            $result = $this->product_importer->import($url, $countryId, (int) ec_user()->UserID, $costOverride, $extra);
        } catch (Exception $e) {
            $this->record_import_failure($url, $e->getMessage());
            $this->session->set_flashdata('error', $e->getMessage());
            redirect('/admin/products/import');
            return;
        }

        $this->Product_import_model->clear_failed($url, (int) ec_user()->UserID);

        $autoAdded = isset($result['auto_added']) ? (int) $result['auto_added'] : 0;
        if (!empty($result['existing'])) {
            $this->session->set_flashdata('success', 'This URL is already imported. Review the product below.' . $this->auto_add_flash($autoAdded, $autoAdd));
        } else {
            $this->session->set_flashdata('success', 'Product imported as Inactive. Ecommerce users can review the detail page; store owners and customers will not see it until it is set Active.' . $this->auto_add_flash($autoAdded, $autoAdd));
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
                $row,
                array('auto_add_to_stores' => $this->input->post('auto_add_to_stores') ? 1 : 0)
            );
            $status = !empty($result['skipped']) ? 'skipped' : 'imported';
            $this->Product_import_model->clear_failed($row['link'], (int) ec_user()->UserID);
            $this->json_out($base + array(
                'status' => $status,
                'product_id' => isset($result['product_id']) ? (int) $result['product_id'] : 0,
                'message' => isset($result['message']) ? $result['message'] : '',
            ));
        } catch (Exception $e) {
            $this->record_csv_failure($row, $countryId, $e->getMessage());
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

    protected function import_prefs()
    {
        $raw = $this->input->cookie('ec_import_prefs', false);
        $data = json_decode((string) $raw, true);
        if (!is_array($data)) {
            $data = array();
        }
        return array(
            'country_id' => (int) (isset($data['country_id']) ? $data['country_id'] : 0),
            'category_id' => (int) (isset($data['category_id']) ? $data['category_id'] : 0),
            'subcategory_id' => (int) (isset($data['subcategory_id']) ? $data['subcategory_id'] : 0),
            'ship_min_days' => (int) (isset($data['ship_min_days']) ? $data['ship_min_days'] : 0),
            'ship_max_days' => (int) (isset($data['ship_max_days']) ? $data['ship_max_days'] : 0),
            'stock' => isset($data['stock']) && $data['stock'] !== '' ? (int) $data['stock'] : '',
            'auto_add_to_stores' => !empty($data['auto_add_to_stores']) ? 1 : 0,
        );
    }

    protected function remember_import_prefs($countryId = null)
    {
        $stockRaw = trim((string) $this->input->post('stock'));
        $prefs = array(
            'country_id' => $countryId !== null ? (int) $countryId : (int) $this->input->post('country_id'),
            'category_id' => (int) $this->input->post('category_id'),
            'subcategory_id' => (int) $this->input->post('subcategory_id'),
            'ship_min_days' => max(0, (int) $this->input->post('ship_min_days')),
            'ship_max_days' => max(0, (int) $this->input->post('ship_max_days')),
            'stock' => $stockRaw === '' ? '' : max(0, (int) $stockRaw),
            'auto_add_to_stores' => $this->input->post('auto_add_to_stores') ? 1 : 0,
        );
        $this->load->helper('cookie');
        $this->input->set_cookie(array(
            'name' => 'ec_import_prefs',
            'value' => json_encode($prefs),
            'expire' => 365 * 24 * 3600,
            'path' => '/',
            'httponly' => false,
        ));
    }

    protected function auto_add_flash($added, $requested = false)
    {
        if (!$requested) {
            return '';
        }
        $added = (int) $added;
        if ($added > 0) {
            return ' Auto-added to ' . $added . ' recommended store' . ($added === 1 ? '' : 's') . ' with each store plus amount.';
        }
        return ' Marked for recommended stores. No store in this country currently has Auto-add enabled.';
    }

    protected function record_import_failure($url, $error)
    {
        $this->load->model('Product_import_model');
        $stockRaw = trim((string) $this->input->post('stock'));
        $basePrice = trim((string) $this->input->post('base_price'));
        $this->Product_import_model->record_failed(array(
            'url' => $url,
            'country_id' => (int) $this->input->post('country_id'),
            'user_id' => (int) ec_user()->UserID,
            'category_id' => (int) $this->input->post('category_id'),
            'subcategory_id' => (int) $this->input->post('subcategory_id'),
            'base_price' => $basePrice !== '' ? (float) $basePrice : 0,
            'ship_min_days' => max(0, (int) $this->input->post('ship_min_days')),
            'ship_max_days' => max(0, (int) $this->input->post('ship_max_days')),
            'stock' => $stockRaw === '' ? 0 : max(0, (int) $stockRaw),
            'error' => $error,
        ));
    }

    protected function record_csv_failure($row, $countryId, $error)
    {
        $this->load->model('Product_import_model');
        $this->load->model('Ec_category_model');
        $categoryId = 0;
        $subcategoryId = 0;
        $categoryName = isset($row['category']) ? trim((string) $row['category']) : '';
        $subName = isset($row['sub_category']) ? trim((string) $row['sub_category']) : '';
        if ($categoryName !== '') {
            $categoryId = (int) $this->Ec_category_model->find_or_create($countryId, $categoryName, 0);
            if ($subName !== '' && $categoryId) {
                $subcategoryId = (int) $this->Ec_category_model->find_or_create($countryId, $subName, $categoryId);
            }
        }
        $this->Product_import_model->record_failed(array(
            'url' => isset($row['link']) ? $row['link'] : '',
            'country_id' => (int) $countryId,
            'user_id' => (int) ec_user()->UserID,
            'category_id' => $categoryId,
            'subcategory_id' => $subcategoryId,
            'base_price' => isset($row['price']) ? $row['price'] : 0,
            'ship_min_days' => isset($row['delivery_min_days']) ? $row['delivery_min_days'] : 0,
            'ship_max_days' => isset($row['delivery_max_days']) ? $row['delivery_max_days'] : 0,
            'product_name' => isset($row['product']) ? $row['product'] : '',
            'error' => $error,
        ));
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

    public function write_ai($id = 0)
    {
        if (function_exists('set_time_limit')) {
            @set_time_limit(120);
        }
        $id = (int) $id;
        $product = $id ? $this->Product_model->get($id) : null;
        if ($id && !$product) {
            $this->output
                ->set_status_header(404)
                ->set_content_type('application/json')
                ->set_output(json_encode(array('ok' => false, 'error' => 'Product not found.')));
            return;
        }
        if ($product && !$this->_can_manage($product)) {
            $this->output
                ->set_status_header(403)
                ->set_content_type('application/json')
                ->set_output(json_encode(array('ok' => false, 'error' => 'You cannot edit this product.')));
            return;
        }
        if (!$product) {
            $product = (object) array(
                'id' => 0,
                'name' => '',
                'short_details' => '',
                'details' => '',
                'brand' => '',
                'sku' => '',
                'country_id' => 0,
                'store_id' => 0,
            );
        }

        $postedTitle = trim((string) $this->input->post('name'));
        $postedShort = (string) $this->input->post('short_details');
        $postedLong = (string) $this->input->post('details');
        $postedBrand = trim((string) $this->input->post('brand'));
        $postedCountry = (int) $this->input->post('country_id');
        if ($postedTitle !== '') {
            $product->name = $postedTitle;
        }
        if ($this->input->post('short_details') !== null) {
            $product->short_details = $postedShort;
        }
        if ($this->input->post('details') !== null) {
            $product->details = $postedLong;
        }
        if ($postedBrand !== '') {
            $product->brand = $postedBrand;
        }
        if ($postedCountry > 0) {
            $product->country_id = $postedCountry;
        }
        if (trim((string) $product->name) === '') {
            $this->output
                ->set_status_header(400)
                ->set_content_type('application/json')
                ->set_output(json_encode(array('ok' => false, 'error' => 'Enter a product title first.')));
            return;
        }

        $this->load->library('Gemini_content');
        if (!$this->gemini_content->has_api_key()) {
            $this->output
                ->set_status_header(400)
                ->set_content_type('application/json')
                ->set_output(json_encode(array('ok' => false, 'error' => 'AI agent API key is not configured. Add it under Admin > AI Settings.')));
            return;
        }

        $this->load->model('Country_model');
        $this->load->model('Store_model');
        $country = !empty($product->country_id) ? $this->Country_model->get($product->country_id) : null;
        $store = null;
        if (!empty($product->store_id)) {
            $store = $this->Store_model->get($product->store_id);
            if ($store && $country && !empty($country->code)) {
                $store->country_code = $country->code;
            }
        }
        $language = $this->gemini_content->language_for($country, $store);
        $currency = $country && !empty($country->currency) ? $country->currency : '';
        $symbol = '';
        if ($country && !empty($country->currency_symbol)) {
            $symbol = $country->currency_symbol;
        } elseif ($currency && function_exists('currency_symbol')) {
            $symbol = currency_symbol($currency);
        }
        $content = $this->gemini_content->rewrite($product, array(
            'language' => $language,
            'country_id' => $country ? (int) $country->id : (int) $product->country_id,
            'country_name' => $country ? $country->name : '',
            'country_code' => $country ? $country->code : '',
            'currency' => $currency,
            'currency_symbol' => $symbol,
            'form_title' => $product->name,
            'form_short' => $product->short_details,
            'form_long' => $product->details,
            'quantity' => $this->gemini_content->extract_quantity($product->name),
        ));
        if (!$content) {
            $this->output
                ->set_status_header(400)
                ->set_content_type('application/json')
                ->set_output(json_encode(array(
                    'ok' => false,
                    'error' => $this->gemini_content->last_error() ?: 'AI generation failed.',
                )));
            return;
        }

        $this->load->model('Ai_content_model');
        $cats = $this->Ai_content_model->apply_ai_categories(
            $product,
            $content,
            $country ? (int) $country->id : (int) $product->country_id,
            !empty($product->store_id) ? (int) $product->store_id : 0
        );

        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode(array(
                'ok' => true,
                'title' => $content['title'],
                'name' => isset($content['name']) ? $content['name'] : $content['title'],
                'short_details' => isset($content['short_details']) ? $content['short_details'] : $content['short_detail'],
                'details' => isset($content['details']) ? $content['details'] : $content['long_detail'],
                'seo_title' => $content['seo_title'],
                'seo_description' => $content['seo_description'],
                'seo_keywords' => $content['seo_keywords'],
                'slug' => isset($content['slug']) ? $content['slug'] : $content['seo_slug'],
                'language' => $language,
                'name_en' => isset($content['name_en']) ? $content['name_en'] : (isset($content['title_en']) ? $content['title_en'] : ''),
                'short_details_en' => isset($content['short_details_en']) ? $content['short_details_en'] : (isset($content['short_detail_en']) ? $content['short_detail_en'] : ''),
                'details_en' => isset($content['details_en']) ? $content['details_en'] : (isset($content['long_detail_en']) ? $content['long_detail_en'] : ''),
                'seo_title_en' => isset($content['seo_title_en']) ? $content['seo_title_en'] : '',
                'seo_description_en' => isset($content['seo_description_en']) ? $content['seo_description_en'] : '',
                'seo_keywords_en' => isset($content['seo_keywords_en']) ? $content['seo_keywords_en'] : '',
                'category_id' => isset($cats['category_id']) ? (int) $cats['category_id'] : 0,
                'subcategory_id' => isset($cats['subcategory_id']) ? (int) $cats['subcategory_id'] : 0,
                'category_name' => isset($cats['category_name']) ? $cats['category_name'] : '',
                'subcategory_name' => isset($cats['subcategory_name']) ? $cats['subcategory_name'] : '',
            )));
    }

    public function unknown()
    {
        if (!ec_is_admin()) {
            $this->session->set_flashdata('error', 'You do not have permission to view failed import links.');
            redirect('/admin/products');
            return;
        }
        $this->load->model('Product_import_model');
        $data = array(
            'title' => 'Failed Imports',
            'links' => $this->Product_import_model->unknown_links(),
            'failed_ids' => $this->Product_import_model->unknown_ids(),
        );
        $this->template->admin('products/unknown', $data);
    }

    public function unknown_retry($id = 0)
    {
        if (!ec_is_admin()) {
            $this->session->set_flashdata('error', 'You do not have permission to retry failed imports.');
            redirect('/admin/products');
            return;
        }
        $this->load->model('Product_import_model');
        $result = $this->retry_failed_row((int) $id);
        if (empty($result['ok'])) {
            $this->session->set_flashdata('error', $result['error']);
            redirect('/admin/products/unknown');
            return;
        }
        if (!empty($result['existing'])) {
            $this->session->set_flashdata('success', 'This URL was already imported. Review the product below.');
        } else {
            $this->session->set_flashdata('success', 'Product re-imported successfully.');
        }
        redirect('/admin/products/form/' . (int) $result['product_id']);
    }

    public function unknown_retry_row()
    {
        if (!ec_is_admin()) {
            $this->json_out(array('ok' => false, 'error' => 'You do not have permission to retry failed imports.'), 403);
            return;
        }
        @set_time_limit(90);
        $this->load->model('Product_import_model');
        $id = (int) $this->input->post('id');
        $result = $this->retry_failed_row($id);
        $this->json_out($result, !empty($result['ok']) ? 200 : 400);
    }

    protected function retry_failed_row($id)
    {
        $this->load->model('Product_import_model');
        $row = $this->Product_import_model->get_failed($id);
        if (!$row) {
            return array('ok' => false, 'id' => (int) $id, 'error' => 'Failed import not found.');
        }

        $userId = (int) $row->user_id ?: (int) ec_user()->UserID;
        $costOverride = (float) $row->base_price > 0 ? (float) $row->base_price : null;
        $extra = array(
            'category_id' => (int) $row->category_id,
            'subcategory_id' => (int) $row->subcategory_id,
            'ship_min_days' => (int) $row->ship_min_days,
            'ship_max_days' => (int) $row->ship_max_days,
            'product_name' => isset($row->product_name) ? trim((string) $row->product_name) : '',
            'auto_add_to_stores' => 1,
            'require_images' => 1,
            'require_details' => 1,
            'force_refresh_details' => 1,
        );
        if (isset($row->stock) && $row->stock !== '') {
            $extra['stock'] = max(0, (int) $row->stock);
        }
        $this->load->library('Product_importer');
        try {
            $this->Product_import_model->learn_source($row->domain, (int) $row->country_id);
            $result = $this->product_importer->import($row->url, (int) $row->country_id, $userId, $costOverride, $extra);
        } catch (Exception $e) {
            $this->Product_import_model->record_failed(array(
                'url' => $row->url,
                'domain' => $row->domain,
                'country_id' => $row->country_id,
                'user_id' => $userId,
                'category_id' => $row->category_id,
                'subcategory_id' => $row->subcategory_id,
                'base_price' => $row->base_price,
                'ship_min_days' => $row->ship_min_days,
                'ship_max_days' => $row->ship_max_days,
                'stock' => $row->stock,
                'product_name' => isset($row->product_name) ? $row->product_name : '',
                'error' => $e->getMessage(),
            ));
            return array(
                'ok' => false,
                'id' => (int) $row->id,
                'url' => $row->url,
                'error' => $e->getMessage(),
            );
        }

        $this->Product_import_model->delete_unknown((int) $row->id);
        return array(
            'ok' => true,
            'id' => (int) $row->id,
            'url' => $row->url,
            'existing' => !empty($result['existing']),
            'product_id' => (int) $result['product_id'],
            'status' => !empty($result['existing']) ? 'existing' : 'imported',
        );
    }

    public function unknown_delete($id = 0)
    {
        if (!ec_is_admin()) {
            $this->session->set_flashdata('error', 'You do not have permission to manage failed import links.');
            redirect('/admin/products');
            return;
        }
        $this->load->model('Product_import_model');
        $this->Product_import_model->delete_unknown($id);
        $this->session->set_flashdata('success', 'Failed import removed.');
        redirect('/admin/products/unknown');
    }

    public function form($id = 0)
    {
        if ($id !== 0 && $id !== '0' && !ctype_digit((string) $id)) {
            show_404();
            return;
        }
        $id = (int) $id;

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
        $this->load->model('Ec_category_model');
        $ecommerceUsers = $this->User_model->ecommerce_users();
        $storeCopies = $product ? $this->Product_model->store_copies($product->id) : array();
        $countryId = $product ? (int) $product->country_id : 0;
        $selection = $product
            ? $this->Ec_category_model->selection_for_product($product->id)
            : array('category_id' => 0, 'subcategory_id' => 0);
        $selectedCategory = !empty($selection['category_id']) ? $this->Ec_category_model->get($selection['category_id']) : null;
        $selectedSubcategory = !empty($selection['subcategory_id']) ? $this->Ec_category_model->get($selection['subcategory_id']) : null;
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
            'creatives' => $product ? $this->Product_model->creatives($product->id) : array(),
            'store_copies' => $storeCopies,
            'price_locked' => $product && ec_is_ecommerce() && !empty($storeCopies),
            'category_tree' => $this->Ec_category_model->tree_for_country($countryId),
            'selected_category_id' => (int) $selection['category_id'],
            'selected_subcategory_id' => (int) $selection['subcategory_id'],
            'selected_category_name' => $selectedCategory ? $selectedCategory->name : '',
            'selected_subcategory_name' => $selectedSubcategory ? $selectedSubcategory->name : '',
        );
        $this->template->admin('products/form', $data);
    }

    public function view($id = 0)
    {
        if (!ctype_digit((string) $id) || (int) $id < 1) {
            show_404();
            return;
        }
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
        $this->load->model('Ec_category_model');
        $trail = $this->Ec_category_model->trail_for_storefront($product->id, $storeRow ? (int) $storeRow->id : 0);
        $data['product_category'] = $trail['category'];
        $data['product_subcategory'] = $trail['subcategory'];

        if (function_exists('product_family')) {
            $family = product_family($product, true);
            $data['product'] = $family['parent'];
            $data['cart_product'] = $family['selected'];
            $data['child_products'] = $family['children'];
            $data['product_images'] = $this->Product_model->images($family['parent']->id);
        } else {
            $data['cart_product'] = $product;
            $data['child_products'] = array();
        }

        $data['pdp_design'] = function_exists('product_detail_design') ? product_detail_design($settings) : 'old';
        if ($data['pdp_design'] === 'new' && !empty($data['product'])) {
            $this->load->model('Product_faq_model');
            $this->load->model('Product_review_model');
            $storeId = $storeRow ? (int) $storeRow->id : 0;
            $parent = $data['product'];
            $data['product_attributes'] = product_attributes_rows($parent->id);
            $data['product_faqs'] = $storeId ? $this->Product_faq_model->for_product($storeId, $parent->id, true) : array();
            $data['review_summary'] = $storeId ? $this->Product_review_model->summary($storeId, $parent->id) : array('count' => 0, 'average' => 0, 'breakdown' => array(5 => 0, 4 => 0, 3 => 0, 2 => 0, 1 => 0));
            $data['product_reviews'] = $storeId ? $this->Product_review_model->published_for_product($storeId, $parent->id, 8, 0) : array();
            $data['review_page'] = 1;
            $data['review_limit'] = 8;
            $data['review_total'] = (int) $data['review_summary']['count'];
            $data['review_pages'] = 1;
            $data['child_galleries'] = array();
            $data['in_wishlist'] = false;
            $data['open_tab'] = '';
        }

        $this->load->view('frontend/' . $theme->slug . '/header', $data);
        if (!empty($data['pdp_design']) && $data['pdp_design'] === 'new') {
            $this->load->view('frontend/shared/product_detail/new', $data);
        } else {
            $this->load->view('frontend/' . $theme->slug . '/detail', $data);
        }
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
            'parent_sku' => trim((string) $this->input->post('parent_sku')),
            'is_default' => ($this->input->post('parent_sku') && $this->input->post('is_default')) ? 1 : 0,
            'sort_order' => max(0, (int) $this->input->post('sort_order')),
            'options_title' => trim((string) ($this->input->post('options_title') ?: $this->input->post('variation_type'))),
            'brand' => trim((string) $this->input->post('brand')),
            'made_by' => trim((string) $this->input->post('made_by')),
            'slug' => $this->Product_model->unique_slug($this->_slug($this->input->post('slug'), $this->input->post('name')), (int) $id),
            'stock' => (int) $this->input->post('stock'),
            'supplier_id' => $this->input->post('supplier_id') ? (int) $this->input->post('supplier_id') : null,
            'country_id' => $this->input->post('country_id') ? (int) $this->input->post('country_id') : null,
            'ship_min_days' => max(0, (int) $this->input->post('ship_min_days')),
            'ship_max_days' => max(0, (int) $this->input->post('ship_max_days')),
            'description' => trim($this->input->post('description')),
            'short_details' => ec_sanitize_product_html($this->input->post('short_details')),
            'details' => ec_sanitize_product_html($this->input->post('details')),
            'seo_title' => trim($this->input->post('seo_title')),
            'seo_description' => trim($this->input->post('seo_description')),
            'seo_keywords' => trim($this->input->post('seo_keywords')),
            'name_en' => trim((string) $this->input->post('name_en')),
            'short_details_en' => ec_sanitize_product_html($this->input->post('short_details_en')),
            'details_en' => ec_sanitize_product_html($this->input->post('details_en')),
            'seo_title_en' => trim((string) $this->input->post('seo_title_en')),
            'seo_description_en' => trim((string) $this->input->post('seo_description_en')),
            'seo_keywords_en' => trim((string) $this->input->post('seo_keywords_en')),
            'status' => (int) $this->input->post('status') === 1 ? 1 : 0,
            'auto_add_to_stores' => $this->input->post('auto_add_to_stores') ? 1 : 0,
            'is_trending' => $this->input->post('is_trending') ? 1 : 0,
            'trending_order' => max(0, (int) $this->input->post('trending_order')),
        );
        $payload['max_sale_price'] = (float) $this->input->post('max_sale_price');
        $payload['extra_amount'] = (float) $this->input->post('extra_amount');
        if (!$priceLocked) {
            $payload['price'] = (float) $this->input->post('price');
            $payload['compare_price'] = (float) $this->input->post('compare_price');
            $payload['cost_price'] = (float) $this->input->post('cost_price');
        }
        if (function_exists('offer_sanitize_admin_payload')) {
            ensure_product_offer_columns();
            $offerPayload = offer_sanitize_admin_payload($this->input->post());
            foreach ($offerPayload as $ok => $ov) {
                $payload[$ok] = $ov;
            }
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
        if (function_exists('product_sync_default_child')) {
            product_sync_default_child($productId);
        }
        $this->_save_gallery($productId);
        $this->_save_attributes($productId);
        $this->_save_variations($productId, $priceLocked);
        $this->_save_sources($productId);
        $this->_save_creatives($productId);
        $this->_save_categories($productId, (int) $payload['country_id']);
        if (empty($payload['store_id']) && (empty($existing) || empty($existing->store_id))) {
            $refreshed = 0;
            if ($id && function_exists('ec_sync_catalog_family_to_stores')) {
                $refreshed = (int) ec_sync_catalog_family_to_stores($productId);
            }
            $added = 0;
            if (!empty($payload['auto_add_to_stores']) && function_exists('ec_auto_add_catalog_product')) {
                $added = (int) ec_auto_add_catalog_product($productId, true);
            }
            if (!$id && function_exists('ec_sync_catalog_family_to_stores')) {
                $added += (int) ec_sync_catalog_family_to_stores($productId);
            }
            // Push offer settings to store copies (after any recreate).
            if (function_exists('offer_sync_fields')) {
                $offerSync = array();
                foreach (offer_sync_fields() as $field) {
                    if (array_key_exists($field, $payload)) {
                        $offerSync[$field] = $payload[$field];
                    }
                }
                if ($offerSync) {
                    $this->Product_model->sync_copy_fields($productId, $offerSync);
                }
            }
            $msg = ($id ? 'Product updated successfully.' : 'Product added successfully.') . $this->auto_add_flash($added, !empty($payload['auto_add_to_stores']));
            if ($refreshed > 0) {
                $msg .= ' Store copies were deleted and created again.';
            }
            $this->session->set_flashdata('success', $msg);
            redirect('/admin/products/form/' . $productId);
            return;
        }

        $this->session->set_flashdata('success', $id ? 'Product updated successfully.' : 'Product added successfully.');
        redirect('/admin/products/form/' . $productId);
    }

    public function create_child()
    {
        $parentId = (int) $this->input->post('parent_id');
        $name = trim((string) $this->input->post('name'));
        $price = $this->input->post('price');
        $fail = function ($message, $code = 400) {
            $this->output
                ->set_status_header($code)
                ->set_content_type('application/json')
                ->set_output(json_encode(array('ok' => false, 'error' => $message)));
        };

        $parent = $parentId ? $this->Product_model->get($parentId) : null;
        if (!$parent || !$this->_can_manage($parent)) {
            $fail('You do not have permission to add a child for this product.', 403);
            return;
        }
        if (!empty($parent->store_id)) {
            $fail('Store copies cannot have children from this list.');
            return;
        }
        if ($name === '') {
            $fail('Title is required.');
            return;
        }
        if ($price === '' || $price === null || !is_numeric($price) || (float) $price < 0) {
            $fail('Enter a valid base price.');
            return;
        }

        if (function_exists('ensure_product_parent_columns')) {
            ensure_product_parent_columns();
        }

        $parentSku = isset($parent->parent_sku) ? trim((string) $parent->parent_sku) : '';
        if ($parentSku === '') {
            $parentSku = trim((string) $parent->sku);
        }
        if ($parentSku === '') {
            $parentSku = $this->Product_model->unique_sku('P' . (int) $parent->id);
            $this->Product_model->save(array('sku' => $parentSku), (int) $parent->id);
        }

        $suffix = strtoupper(preg_replace('/[^A-Za-z0-9]+/', '-', $name));
        $suffix = trim($suffix, '-');
        if ($suffix === '') {
            $suffix = 'CHILD';
        }
        $sku = $this->Product_model->unique_sku($parentSku . '-' . $suffix);
        $slug = $this->Product_model->unique_slug($this->_slug('', $name));
        $image = $this->_upload_named('image');
        if ($image === '') {
            $image = isset($parent->image) ? $parent->image : '';
        }

        $hasDefault = false;
        if ($this->db->field_exists('parent_sku', 'products')) {
            $this->db
                ->group_start()
                    ->where('store_id IS NULL', null, false)
                    ->or_where('store_id', 0)
                ->group_end()
                ->where('parent_sku', $parentSku)
                ->where('is_default', 1);
            $hasDefault = $this->db->count_all_results('products') > 0;
        }

        $postedSort = $this->input->post('sort_order');
        if ($postedSort === null || $postedSort === '') {
            $sortOrder = function_exists('product_next_child_sort') ? product_next_child_sort($parentSku) : 0;
        } else {
            $sortOrder = max(0, (int) $postedSort);
            if ($sortOrder < 1 && function_exists('product_next_child_sort')) {
                $sortOrder = product_next_child_sort($parentSku);
            }
        }

        $payload = array(
            'name' => $name,
            'sku' => $sku,
            'parent_sku' => $parentSku,
            'is_default' => $hasDefault ? 0 : 1,
            'sort_order' => $sortOrder,
            'slug' => $slug,
            'brand' => isset($parent->brand) ? $parent->brand : '',
            'made_by' => isset($parent->made_by) ? $parent->made_by : '',
            'price' => round((float) $price, 2),
            'compare_price' => isset($parent->compare_price) ? (float) $parent->compare_price : 0,
            'cost_price' => isset($parent->cost_price) ? (float) $parent->cost_price : 0,
            'max_sale_price' => isset($parent->max_sale_price) ? (float) $parent->max_sale_price : 0,
            'extra_amount' => 0,
            'stock' => isset($parent->stock) ? (int) $parent->stock : 0,
            'ship_min_days' => isset($parent->ship_min_days) ? (int) $parent->ship_min_days : 0,
            'ship_max_days' => isset($parent->ship_max_days) ? (int) $parent->ship_max_days : 0,
            'supplier_id' => !empty($parent->supplier_id) ? (int) $parent->supplier_id : null,
            'country_id' => !empty($parent->country_id) ? (int) $parent->country_id : null,
            'description' => isset($parent->description) ? $parent->description : '',
            'short_details' => isset($parent->short_details) ? $parent->short_details : '',
            'details' => isset($parent->details) ? $parent->details : '',
            'seo_title' => $name,
            'seo_description' => isset($parent->seo_description) ? $parent->seo_description : '',
            'seo_keywords' => isset($parent->seo_keywords) ? $parent->seo_keywords : '',
            'status' => 1,
            'auto_add_to_stores' => !empty($parent->auto_add_to_stores) ? 1 : 0,
            'image' => $image,
            'created_by' => !empty($parent->created_by) ? (int) $parent->created_by : (int) ec_user()->UserID,
        );

        $childId = $this->Product_model->save($payload, 0);
        if ($childId < 1) {
            $fail('Could not create child product.', 500);
            return;
        }
        if (function_exists('product_sync_default_child')) {
            product_sync_default_child($childId);
        }

        $this->load->model('Ec_category_model');
        $this->Ec_category_model->set_product_categories($childId, $this->Ec_category_model->ids_for_product($parent->id));

        $added = 0;
        if (!empty($payload['auto_add_to_stores']) && function_exists('ec_auto_add_catalog_product')) {
            $added = (int) ec_auto_add_catalog_product($childId, true);
        }
        if (function_exists('ec_sync_catalog_family_to_stores')) {
            $added += (int) ec_sync_catalog_family_to_stores($childId);
        }

        $this->output->set_content_type('application/json')->set_output(json_encode(array(
            'ok' => true,
            'id' => $childId,
            'added' => $added,
        )));
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

        $copyIds = $this->Product_model->store_copy_ids($id);
        $this->Product_model->delete($id);
        $copyCount = count($copyIds);
        if ($copyCount > 0) {
            $this->session->set_flashdata('success', 'Product deleted from admin and from ' . $copyCount . ' store listing' . ($copyCount === 1 ? '' : 's') . '.');
        } else {
            $this->session->set_flashdata('success', 'Product deleted successfully.');
        }
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

    private function posted_creative_links()
    {
        $links = $this->input->post('creative_links');
        $rows = $this->input->post('creatives');
        $clean = array();
        $seen = array();
        $candidates = array();
        if (is_array($links)) {
            foreach ($links as $link) {
                $candidates[] = array('link' => $link, 'label' => '');
            }
        }
        if (is_array($rows)) {
            foreach ($rows as $row) {
                if (!is_array($row)) {
                    $candidates[] = array('link' => $row, 'label' => '');
                    continue;
                }
                $candidates[] = array(
                    'link' => isset($row['link']) ? $row['link'] : '',
                    'label' => isset($row['label']) ? $row['label'] : '',
                );
            }
        }
        foreach ($candidates as $row) {
            $link = trim((string) $row['link']);
            if ($link === '') {
                continue;
            }
            $key = strtolower(rtrim($link, '/'));
            if (isset($seen[$key])) {
                continue;
            }
            $seen[$key] = true;
            $clean[] = array(
                'link' => $link,
                'label' => trim((string) $row['label']),
            );
        }
        return $clean;
    }

    private function _save_creatives($productId)
    {
        $this->Product_model->replace_creatives($productId, $this->posted_creative_links());
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

    private function _save_categories($productId, $countryId)
    {
        $this->load->model('Ec_category_model');
        $ids = $this->Ec_category_model->ids_from_form(
            $countryId,
            (int) $this->input->post('category_id'),
            (int) $this->input->post('subcategory_id'),
            $this->input->post('category_name'),
            $this->input->post('subcategory_name')
        );
        $this->Ec_category_model->set_product_categories($productId, $ids);
    }

    private function _slug($slug, $name)
    {
        $slug = trim((string) $slug);
        if ($slug === '') {
            $slug = $name;
        }
        return function_exists('ec_ascii_slug') ? ec_ascii_slug($slug, 'product') : url_title($slug, '-', true);
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
