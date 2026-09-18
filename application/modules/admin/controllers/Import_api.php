<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Import_api extends CI_Controller {

    public function __construct()
    {
        parent::__construct();
        $this->load->model('Product_import_model');
        $this->load->model('Product_model');
        $this->load->model('Country_model');
        $this->load->library('product_importer');
    }

    public function index()
    {
        $this->cors();
        if (strtoupper((string) $this->input->server('REQUEST_METHOD')) === 'OPTIONS') {
            $this->output->set_status_header(204);
            return;
        }

        @set_time_limit(180);
        $input = $this->read_input();
        $url = isset($input['link']) ? trim((string) $input['link']) : '';
        if ($url === '' && !empty($input['url'])) {
            $url = trim((string) $input['url']);
        }
        $countryId = isset($input['country_id']) ? (int) $input['country_id'] : 0;
        $basePrice = isset($input['base_price']) ? (float) $input['base_price'] : 0;
        $maxPrice = isset($input['max_price']) ? (float) $input['max_price'] : 0;

        if ($url === '' || !$countryId || $basePrice <= 0) {
            $this->json_out(array(
                'status' => 0,
                'msg' => 'Please send link, country_id and base_price.',
            ), 400);
            return;
        }

        $country = $this->Country_model->get($countryId);
        if (!$country) {
            $this->json_out(array(
                'status' => 0,
                'msg' => 'Invalid country_id.',
            ), 400);
            return;
        }

        $userId = $this->resolve_user($input);
        $priced = $this->priced_from_base($basePrice, $userId);

        $creativeLinks = array();
        if (!empty($input['creative_links']) && is_array($input['creative_links'])) {
            $creativeLinks = $input['creative_links'];
        } elseif (!empty($input['creatives']) && is_array($input['creatives'])) {
            $creativeLinks = $input['creatives'];
        }

        try {
            $result = $this->product_importer->import($url, $countryId, $userId, $basePrice, array(
                'max_sale_price' => $maxPrice,
                'catalog_price' => $priced['price'],
                'creative_links' => $creativeLinks,
            ));
        } catch (Exception $e) {
            $error = $e->getMessage();
            $this->Product_import_model->record_failed(array(
                'url' => $url,
                'country_id' => $countryId,
                'user_id' => $userId,
                'base_price' => $basePrice,
                'error' => $error,
            ));
            $this->json_out(array(
                'status' => 0,
                'msg' => $error,
                'failed' => 1,
            ));
            return;
        }

        $productId = (int) $result['product_id'];
        $this->Product_model->save(array(
            'cost_price' => $priced['cost_price'],
            'price' => $priced['price'],
            'max_sale_price' => $maxPrice,
        ), $productId);
        $this->Product_import_model->clear_failed($url, $userId);

        $product = $this->Product_model->get($productId);
        $name = $product && !empty($product->name) ? trim($product->name) : 'Product';
        $currency = $product && !empty($product->country_currency) ? $product->country_currency : '';

        $this->json_out(array(
            'status' => 1,
            'msg' => $name . ' imported successfully',
            'product_id' => $productId,
            'name' => $name,
            'country_id' => $countryId,
            'base_price' => $priced['cost_price'],
            'price' => $priced['price'],
            'max_price' => $maxPrice,
            'platform_fee' => $priced['platform_fee'],
            'commission' => $priced['commission'],
            'currency' => $currency,
            'existing' => !empty($result['existing']) ? 1 : 0,
        ));
    }

    public function countries()
    {
        $this->cors();
        if (strtoupper((string) $this->input->server('REQUEST_METHOD')) === 'OPTIONS') {
            $this->output->set_status_header(204);
            return;
        }

        $this->db->order_by('name', 'asc');
        if ($this->db->field_exists('status', 'countries')) {
            $this->db->where('status', 1);
        }
        $rows = $this->db->get('countries')->result();
        $list = array();
        foreach ($rows as $row) {
            $list[] = array(
                'id' => (int) $row->id,
                'name' => $row->name,
                'code' => isset($row->code) ? $row->code : '',
                'iso3' => isset($row->iso3) ? $row->iso3 : '',
                'currency' => isset($row->currency) ? $row->currency : '',
                'phone_code' => isset($row->phone_code) ? $row->phone_code : '',
            );
        }

        $this->json_out(array(
            'status' => 1,
            'msg' => 'Countries fetched successfully',
            'count' => count($list),
            'countries' => $list,
        ));
    }

    protected function read_input()
    {
        $raw = file_get_contents('php://input');
        $json = json_decode($raw, true);
        $data = is_array($json) ? $json : array();
        foreach (array('link', 'url', 'country_id', 'base_price', 'max_price', 'user_id') as $key) {
            if (!isset($data[$key]) || $data[$key] === '') {
                $val = $this->input->get_post($key);
                if ($val !== null && $val !== '') {
                    $data[$key] = $val;
                }
            }
        }
        return $data;
    }

    protected function priced_from_base($basePrice, $userId)
    {
        $basePrice = round((float) $basePrice, 2);
        $stub = (object) array(
            'store_id' => null,
            'cost_price' => $basePrice,
            'price' => $basePrice,
            'created_by' => (int) $userId,
        );
        $fee = product_platform_fee(0, $stub);
        $commission = product_commission_amount($stub);
        return array(
            'cost_price' => $basePrice,
            'platform_fee' => round($fee, 2),
            'commission' => round($commission, 2),
            'price' => round($basePrice + $fee + $commission, 2),
        );
    }

    protected function resolve_user($input = array())
    {
        $userId = isset($input['user_id']) ? (int) $input['user_id'] : (int) $this->input->get_post('user_id');
        if ($userId) {
            return $userId;
        }
        $admin = $this->db->where('roleID', 1)->where('status', 1)->order_by('UserID', 'asc')->get('users')->row();
        if ($admin) {
            return (int) $admin->UserID;
        }
        $ecom = $this->db->where('roleID', ROLE_ECOMMERCE)->where('status', 1)->order_by('UserID', 'asc')->get('users')->row();
        return $ecom ? (int) $ecom->UserID : 0;
    }

    protected function cors()
    {
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type, Authorization');
    }

    protected function json_out($data, $code = 200)
    {
        $this->output
            ->set_status_header((int) $code)
            ->set_content_type('application/json', 'utf-8')
            ->set_output(json_encode($data));
    }
}
