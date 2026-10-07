<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Cron extends CI_Controller {

    public function __construct()
    {
        parent::__construct();
        $this->load->model('Channel_job_model');
    }

    public function recalculate_store_prices($storeId = 0)
    {
        if (php_sapi_name() !== 'cli') {
            show_error('Forbidden', 403);
            return;
        }
        $n = function_exists('ec_recalculate_store_listing_prices')
            ? ec_recalculate_store_listing_prices((int) $storeId)
            : 0;
        $this->output->set_content_type('text/plain')->set_output('updated=' . (int) $n . "\n");
    }

    public function purge_orphan_store_copies()
    {
        if (php_sapi_name() !== 'cli') {
            show_error('Forbidden', 403);
            return;
        }
        $this->load->model('admin/Product_model');
        $n = $this->Product_model->purge_orphan_store_copies();
        $this->output->set_content_type('text/plain')->set_output('purged=' . (int) $n . "\n");
    }

    public function import_cli()
    {
        if (php_sapi_name() !== 'cli') {
            show_error('Forbidden', 403);
            return;
        }
        $url = trim((string) getenv('IMPORT_URL'));
        if ($url === '') {
            $this->output->set_content_type('text/plain')->set_output("IMPORT_URL missing\n");
            return;
        }
        $this->load->library('Product_importer');
        try {
            $userId = (int) getenv('IMPORT_USER_ID');
            $extra = array();
            if (getenv('IMPORT_AUTO_ADD')) {
                $extra['auto_add_to_stores'] = 1;
            }
            if (getenv('IMPORT_ALL_STORES')) {
                $extra['auto_add_to_stores'] = 1;
                $extra['add_to_all_stores'] = 1;
            }
            $category = trim((string) getenv('IMPORT_CATEGORY'));
            $sub = trim((string) getenv('IMPORT_SUBCATEGORY'));
            if ($category !== '') {
                $extra['category'] = $category;
            }
            if ($sub !== '') {
                $extra['sub_category'] = $sub;
            }
            $result = $this->product_importer->import($url, 0, $userId, null, $extra);
            $this->output->set_content_type('text/plain')->set_output(json_encode($result) . "\n");
        } catch (Exception $e) {
            $this->output->set_content_type('text/plain')->set_output('ERROR: ' . $e->getMessage() . "\n");
        }
    }

    public function retry_unknown_cli()
    {
        if (php_sapi_name() !== 'cli') {
            show_error('Forbidden', 403);
            return;
        }
        $this->load->model('admin/Product_import_model');
        $this->load->library('Product_importer');
        $rows = $this->Product_import_model->unknown_links();
        $out = array();
        foreach ($rows as $row) {
            $userId = (int) $row->user_id;
            $costOverride = (float) $row->base_price > 0 ? (float) $row->base_price : null;
            $extra = array(
                'category_id' => (int) $row->category_id,
                'subcategory_id' => (int) $row->subcategory_id,
                'product_name' => isset($row->product_name) ? trim((string) $row->product_name) : '',
                'auto_add_to_stores' => 1,
                'require_images' => 1,
                'require_details' => 1,
                'force_refresh_details' => 1,
            );
            if ((int) $row->ship_min_days > 0 || (int) $row->ship_max_days > 0) {
                $extra['ship_min_days'] = (int) $row->ship_min_days;
                $extra['ship_max_days'] = (int) $row->ship_max_days;
            }
            if (isset($row->stock) && $row->stock !== '') {
                $extra['stock'] = max(0, (int) $row->stock);
            }
            try {
                $this->Product_import_model->learn_source($row->domain, (int) $row->country_id);
                $result = $this->product_importer->import($row->url, (int) $row->country_id, $userId, $costOverride, $extra);
                $this->Product_import_model->delete_unknown((int) $row->id);
                $out[] = array(
                    'ok' => 1,
                    'id' => (int) $row->id,
                    'domain' => $row->domain,
                    'product_id' => (int) $result['product_id'],
                    'existing' => !empty($result['existing']),
                );
            } catch (Exception $e) {
                $out[] = array(
                    'ok' => 0,
                    'id' => (int) $row->id,
                    'domain' => $row->domain,
                    'error' => $e->getMessage(),
                );
            }
        }
        $this->output->set_content_type('text/plain')->set_output(json_encode($out) . "\n");
    }

    public function import_batch_cli()
    {
        if (php_sapi_name() !== 'cli') {
            show_error('Forbidden', 403);
            return;
        }
        $path = trim((string) getenv('IMPORT_BATCH'));
        if ($path === '' || !is_file($path)) {
            $this->output->set_content_type('text/plain')->set_output("IMPORT_BATCH missing\n");
            return;
        }
        $rows = json_decode((string) file_get_contents($path), true);
        if (!is_array($rows)) {
            $this->output->set_content_type('text/plain')->set_output("IMPORT_BATCH invalid json\n");
            return;
        }
        $this->load->model('admin/Product_import_model');
        $this->load->library('Product_importer');
        $userId = (int) getenv('IMPORT_USER_ID');
        $countryId = (int) getenv('IMPORT_COUNTRY_ID');
        $out = array();
        foreach ($rows as $i => $row) {
            if (!is_array($row)) {
                continue;
            }
            $url = isset($row['url']) ? trim((string) $row['url']) : '';
            if ($url === '') {
                $out[] = array('ok' => 0, 'index' => $i, 'error' => 'Missing url');
                continue;
            }
            $cost = isset($row['base_price']) ? (float) $row['base_price'] : 0;
            $extra = array(
                'csv_name' => isset($row['product']) ? trim((string) $row['product']) : '',
                'product_name' => isset($row['product']) ? trim((string) $row['product']) : '',
                'supplier_name' => isset($row['supplier']) ? trim((string) $row['supplier']) : '',
                'auto_add_to_stores' => 1,
                'existing_mode' => 'skip_name',
                'reuse_source' => true,
            );
            if (!empty($row['creative'])) {
                $extra['creative_links'] = array($row['creative']);
            }
            if (!empty($row['creatives']) && is_array($row['creatives'])) {
                $extra['creative_links'] = $row['creatives'];
            }
            if (!empty($row['extra_sources']) && is_array($row['extra_sources'])) {
                $extra['extra_sources'] = $row['extra_sources'];
            }
            if (!empty($row['image_files']) && is_array($row['image_files'])) {
                $extra['image_files'] = $row['image_files'];
            }
            if (!empty($row['require_images']) || getenv('IMPORT_REQUIRE_IMAGES')) {
                $extra['require_images'] = 1;
            }
            if (!empty($row['details'])) {
                $extra['details'] = (string) $row['details'];
            }
            if (!empty($row['description'])) {
                $extra['description'] = (string) $row['description'];
            }
            if (!empty($row['force_refresh_details'])) {
                $extra['force_refresh_details'] = 1;
            }
            if (array_key_exists('stock', $row) && $row['stock'] !== '' && $row['stock'] !== null) {
                $extra['stock'] = (int) $row['stock'];
            }
            if (array_key_exists('stock', $row) && $row['stock'] !== '' && $row['stock'] !== null) {
                $extra['stock'] = (int) $row['stock'];
            }
            $shipMin = isset($row['ship_min_days']) ? (int) $row['ship_min_days'] : 0;
            $shipMax = isset($row['ship_max_days']) ? (int) $row['ship_max_days'] : 0;
            if ($shipMin > 0 || $shipMax > 0) {
                $extra['ship_min_days'] = $shipMin;
                $extra['ship_max_days'] = $shipMax > 0 ? $shipMax : $shipMin;
            }
            $rowCountry = !empty($row['country_id']) ? (int) $row['country_id'] : $countryId;
            $rowUser = !empty($row['user_id']) ? (int) $row['user_id'] : $userId;
            try {
                $host = (string) parse_url($url, PHP_URL_HOST);
                $this->Product_import_model->learn_source($host, $rowCountry);
                $result = $this->product_importer->import($url, $rowCountry, $rowUser, $cost > 0 ? $cost : null, $extra);
                $out[] = array(
                    'ok' => 1,
                    'index' => $i,
                    'product' => $extra['csv_name'],
                    'product_id' => (int) $result['product_id'],
                    'existing' => !empty($result['existing']),
                    'auto_added' => isset($result['auto_added']) ? (int) $result['auto_added'] : 0,
                );
            } catch (Exception $e) {
                $out[] = array(
                    'ok' => 0,
                    'index' => $i,
                    'product' => $extra['csv_name'],
                    'url' => $url,
                    'error' => $e->getMessage(),
                );
            }
            @file_put_contents('/tmp/sweden_import_progress.json', json_encode($out, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
        }
        $this->output->set_content_type('text/plain')->set_output(json_encode($out) . "\n");
    }

    public function refresh_incomplete_cli()
    {
        if (php_sapi_name() !== 'cli') {
            show_error('Forbidden', 403);
            return;
        }
        $this->load->library('Product_importer');
        $this->load->model('admin/Product_model');
        $this->load->model('admin/Product_import_model');
        $rows = $this->db
            ->select('id, name, source_url, image, details, description')
            ->group_start()
                ->where('store_id IS NULL', null, false)
                ->or_where('store_id', 0)
            ->group_end()
            ->group_start()
                ->where('image', '')
                ->or_where('image IS NULL', null, false)
                ->or_where('details', '')
                ->or_where('details IS NULL', null, false)
            ->group_end()
            ->where('source_url !=', '')
            ->order_by('id', 'desc')
            ->get('products')
            ->result();
        $out = array();
        foreach ($rows as $row) {
            $url = trim((string) $row->source_url);
            if ($url === '') {
                $out[] = array('ok' => 0, 'id' => (int) $row->id, 'error' => 'Missing source URL');
                continue;
            }
            try {
                $host = (string) parse_url($url, PHP_URL_HOST);
                $this->Product_import_model->learn_source($host, 0);
                $result = $this->product_importer->import($url, 0, 0, null, array(
                    'auto_add_to_stores' => 1,
                    'require_images' => 1,
                    'require_details' => 1,
                    'force_refresh_details' => 1,
                ));
                $fresh = $this->Product_model->get((int) $result['product_id']);
                $out[] = array(
                    'ok' => 1,
                    'id' => (int) $row->id,
                    'product_id' => (int) $result['product_id'],
                    'has_image' => $fresh && trim((string) $fresh->image) !== '',
                    'has_details' => $fresh && trim(strip_tags((string) $fresh->details . $fresh->description)) !== '',
                );
            } catch (Exception $e) {
                $out[] = array(
                    'ok' => 0,
                    'id' => (int) $row->id,
                    'name' => $row->name,
                    'url' => $url,
                    'error' => $e->getMessage(),
                );
            }
        }
        $this->output->set_content_type('text/plain')->set_output(json_encode($out) . "\n");
    }

    public function normalize_slugs_cli()
    {
        if (php_sapi_name() !== 'cli') {
            show_error('Forbidden', 403);
            return;
        }
        $this->load->model('admin/Product_model');
        $this->load->model('store/Store_product_model');
        $this->load->model('Ec_category_model');

        $out = array('products' => array(), 'categories' => array());
        $rows = $this->db->select('id, store_id, slug, name')->get('products')->result();
        foreach ($rows as $row) {
            $current = trim((string) $row->slug);
            $source = $current !== '' ? $current : (string) $row->name;
            $ascii = ec_ascii_slug($source, 'product');
            if ($ascii === $current) {
                continue;
            }
            $storeId = (int) $row->store_id;
            $next = $storeId > 0
                ? $this->Store_product_model->unique_slug($ascii, $storeId, (int) $row->id)
                : $this->Product_model->unique_slug($ascii, (int) $row->id);
            if ($next === $current) {
                continue;
            }
            $this->db->where('id', (int) $row->id)->update('products', array('slug' => $next));
            $out['products'][] = array(
                'id' => (int) $row->id,
                'store_id' => $storeId,
                'from' => $current,
                'to' => $next,
            );
        }

        $cats = $this->db->select('id, country_id, slug, name')->get('categories')->result();
        $used = array();
        foreach ($cats as $cat) {
            $current = trim((string) $cat->slug);
            $source = $current !== '' ? $current : (string) $cat->name;
            $ascii = ec_ascii_slug($source, 'category');
            if ($ascii === $current) {
                continue;
            }
            $countryId = (int) $cat->country_id;
            if (!isset($used[$countryId])) {
                $used[$countryId] = array();
            }
            $next = $this->Ec_category_model->unique_slug($countryId, $ascii, $used[$countryId], (int) $cat->id);
            if ($next === $current) {
                continue;
            }
            $this->db->where('id', (int) $cat->id)->update('categories', array('slug' => $next));
            $out['categories'][] = array(
                'id' => (int) $cat->id,
                'country_id' => $countryId,
                'from' => $current,
                'to' => $next,
            );
        }

        $out['product_count'] = count($out['products']);
        $out['category_count'] = count($out['categories']);
        $this->output->set_content_type('text/plain')->set_output(json_encode($out, JSON_UNESCAPED_UNICODE) . "\n");
    }

    public function channel_jobs()
    {
        if (php_sapi_name() !== 'cli') {
            $key = (string) $this->input->get_post('key');
            $expected = (string) platform_setting('channel_cron_key', '');
            if ($expected === '' || !hash_equals($expected, $key)) {
                show_error('Forbidden', 403);
                return;
            }
        }
        $count = $this->Channel_job_model->process_batch(10);
        $this->output->set_content_type('text/plain')->set_output('processed=' . (int) $count);
    }

    public function ai_content_jobs()
    {
        if (php_sapi_name() !== 'cli') {
            $key = (string) $this->input->get_post('key');
            $expected = (string) platform_setting('channel_cron_key', '');
            if ($expected === '' || !hash_equals($expected, $key)) {
                show_error('Forbidden', 403);
                return;
            }
        }
        $this->load->model('admin/Ai_content_model');
        $count = $this->Ai_content_model->process_next();
        $this->output->set_content_type('text/plain')->set_output('processed=' . (int) $count);
    }

    public function import_snapshot_cli()
    {
        if (php_sapi_name() !== 'cli') {
            show_error('Forbidden', 403);
            return;
        }
        $path = trim((string) getenv('IMPORT_SNAPSHOT'));
        if ($path === '' || !is_file($path)) {
            $this->output->set_content_type('text/plain')->set_output("IMPORT_SNAPSHOT missing\n");
            return;
        }
        $json = json_decode((string) file_get_contents($path), true);
        if (!is_array($json) || empty($json['url'])) {
            $this->output->set_content_type('text/plain')->set_output("IMPORT_SNAPSHOT invalid\n");
            return;
        }
        $this->load->library('Product_importer');
        try {
            $userId = !empty($json['user_id']) ? (int) $json['user_id'] : (int) getenv('IMPORT_USER_ID');
            $countryId = !empty($json['country_id']) ? (int) $json['country_id'] : 0;
            $extra = array(
                'import_data' => $json,
                'product_name' => isset($json['name']) ? $json['name'] : '',
                'csv_name' => isset($json['name']) ? $json['name'] : '',
                'sizes' => isset($json['sizes']) ? $json['sizes'] : array(),
                'colors' => isset($json['colors']) ? $json['colors'] : array(),
                'auto_add_to_stores' => !empty($json['auto_add_to_stores']) ? 1 : 0,
                'add_to_all_stores' => !empty($json['add_to_all_stores']) ? 1 : 0,
                'status' => isset($json['status']) ? (int) $json['status'] : 1,
            );
            if (!empty($json['category'])) {
                $extra['category'] = $json['category'];
            }
            if (!empty($json['sub_category'])) {
                $extra['sub_category'] = $json['sub_category'];
            }
            if (!empty($json['category_id'])) {
                $extra['category_id'] = (int) $json['category_id'];
            }
            if (!empty($json['subcategory_id'])) {
                $extra['subcategory_id'] = (int) $json['subcategory_id'];
            }
            $isAliExpress = (stripos((string) $json['url'], 'aliexpress.') !== false);
            if ($isAliExpress) {
                $jsonMin = isset($json['ship_min_days']) ? (int) $json['ship_min_days'] : 0;
                $jsonMax = isset($json['ship_max_days']) ? (int) $json['ship_max_days'] : 0;
                if ($jsonMin > 0 || $jsonMax > 0) {
                    $extra['ship_min_days'] = $jsonMin > 0 ? $jsonMin : $jsonMax;
                    $extra['ship_max_days'] = $jsonMax > 0 ? $jsonMax : $jsonMin;
                } else {
                    require_once APPPATH . 'libraries/importers/Aliexpress_com.php';
                    $aeShip = (new Aliexpress_com())->shipping_days_for_url($json['url']);
                    $extra['ship_min_days'] = (int) $aeShip['ship_min_days'];
                    $extra['ship_max_days'] = (int) $aeShip['ship_max_days'];
                }
            } elseif (isset($json['ship_min_days']) || isset($json['ship_max_days'])) {
                $extra['ship_min_days'] = isset($json['ship_min_days']) ? (int) $json['ship_min_days'] : 0;
                $extra['ship_max_days'] = isset($json['ship_max_days']) ? (int) $json['ship_max_days'] : 0;
            }
            if (isset($json['stock'])) {
                $extra['stock'] = (int) $json['stock'];
            }
            $cost = isset($json['price']) ? $json['price'] : null;
            $result = $this->product_importer->import($json['url'], $countryId, $userId, $cost, $extra);
            $catalogId = isset($result['product_id']) ? (int) $result['product_id'] : 0;
            if ($catalogId > 0 && isset($json['extra_amount']) && $json['extra_amount'] !== '' && $json['extra_amount'] !== null && function_exists('ec_apply_catalog_extra_amount')) {
                $extraAmt = round((float) $json['extra_amount'], 2);
                $parent = $this->db->where('id', $catalogId)->get('products')->row();
                $ids = array($catalogId);
                if ($parent && $this->db->field_exists('parent_sku', 'products')) {
                    $sku = trim((string) $parent->sku);
                    if ($sku !== '') {
                        $kids = $this->db
                            ->select('id')
                            ->from('products')
                            ->group_start()
                                ->where('store_id IS NULL', null, false)
                                ->or_where('store_id', 0)
                            ->group_end()
                            ->where('parent_sku', $sku)
                            ->get()
                            ->result();
                        foreach ($kids as $kid) {
                            $ids[] = (int) $kid->id;
                        }
                    }
                }
                foreach (array_unique($ids) as $id) {
                    ec_apply_catalog_extra_amount((int) $id, $extraAmt);
                }
                $result['extra_amount'] = $extraAmt;
                $result['extra_ids'] = array_values(array_unique($ids));
            }
            $this->output->set_content_type('text/plain')->set_output(json_encode($result) . "\n");
        } catch (Exception $e) {
            $this->output->set_content_type('text/plain')->set_output('ERROR: ' . $e->getMessage() . "\n");
        }
    }

    public function apply_extra_cli()
    {
        if (php_sapi_name() !== 'cli') {
            show_error('Forbidden', 403);
            return;
        }
        $path = trim((string) getenv('APPLY_EXTRA_JSON'));
        if ($path === '' || !is_file($path)) {
            $this->output->set_content_type('text/plain')->set_output("APPLY_EXTRA_JSON missing\n");
            return;
        }
        $json = json_decode((string) file_get_contents($path), true);
        if (!is_array($json) || empty($json['sku'])) {
            $this->output->set_content_type('text/plain')->set_output("APPLY_EXTRA_JSON invalid\n");
            return;
        }
        $sku = trim((string) $json['sku']);
        $extraAmount = isset($json['extra_amount']) ? round((float) $json['extra_amount'], 2) : 0;
        $childCosts = (!empty($json['child_costs']) && is_array($json['child_costs'])) ? $json['child_costs'] : array();
        $this->db
            ->group_start()
                ->where('store_id IS NULL', null, false)
                ->or_where('store_id', 0)
            ->group_end()
            ->where('sku', $sku);
        $parent = $this->db->get('products')->row();
        if (!$parent) {
            $this->output->set_content_type('text/plain')->set_output("parent not found\n");
            return;
        }
        $this->db
            ->group_start()
                ->where('store_id IS NULL', null, false)
                ->or_where('store_id', 0)
            ->group_end()
            ->where('parent_sku', $sku)
            ->order_by('sort_order', 'asc')
            ->order_by('id', 'asc');
        $children = $this->db->get('products')->result();
        $normLabel = function ($value) {
            $value = strtolower(trim((string) $value));
            $value = preg_replace('/\s+/', ' ', $value);
            $value = preg_replace('/\bfor\s+/i', '', $value);
            $value = preg_replace('/\s*caps?\b/i', '', $value);
            $value = preg_replace('/\s*\/\s*/', '/', $value);
            $value = preg_replace('/[-–]+/', ' ', $value);
            $value = preg_replace('/\s+/', ' ', $value);
            $aliases = array(
                '1xl' => 'xl',
                '2xl' => 'xxl',
                '3xl' => 'xxxl',
                'coffe' => 'coffee',
            );
            $parts = explode('/', $value);
            foreach ($parts as $i => $part) {
                $part = trim($part);
                if (isset($aliases[$part])) {
                    $parts[$i] = $aliases[$part];
                } else {
                    $parts[$i] = $part;
                }
            }
            return implode('/', $parts);
        };
        $updated = array();
        foreach ($children as $child) {
            $name = trim((string) $child->name);
            $suffix = $name;
            if (preg_match('/\s[-–]\s(.+)$/u', $name, $m)) {
                $suffix = $m[1];
            }
            $nameKey = $normLabel($name);
            $suffixKey = $normLabel($suffix);
            $suffixParts = preg_split('/\s*\/\s*/', $suffix);
            $suffixRev = (count($suffixParts) === 2) ? $normLabel(trim($suffixParts[1]) . ' / ' . trim($suffixParts[0])) : '';
            $matched = null;
            foreach ($childCosts as $label => $cost) {
                $label = trim((string) $label);
                if ($label === '') {
                    continue;
                }
                $labelKey = $normLabel($label);
                $labelParts = preg_split('/\s*\/\s*/', $label);
                $labelRev = (count($labelParts) === 2) ? $normLabel(trim($labelParts[1]) . ' / ' . trim($labelParts[0])) : '';
                if ($labelKey === $nameKey || $labelKey === $suffixKey || ($suffixRev !== '' && $labelKey === $suffixRev) || ($labelRev !== '' && ($labelRev === $suffixKey || $labelRev === $nameKey))) {
                    $matched = (float) $cost;
                    break;
                }
            }
            if ($matched === null) {
                continue;
            }
            $this->db->where('id', (int) $child->id)->update('products', array(
                'cost_price' => $matched,
                'price' => $matched,
            ));
            $updated[] = array('id' => (int) $child->id, 'cost' => $matched);
        }
        $applied = array();
        $ids = array((int) $parent->id);
        foreach ($children as $child) {
            $ids[] = (int) $child->id;
        }
        foreach (array_unique($ids) as $catalogId) {
            if (function_exists('ec_apply_catalog_extra_amount')) {
                ec_apply_catalog_extra_amount($catalogId, $extraAmount);
                $applied[] = (int) $catalogId;
            }
        }
        $this->output->set_content_type('text/plain')->set_output(json_encode(array(
            'parent_id' => (int) $parent->id,
            'extra_amount' => $extraAmount,
            'costs_updated' => $updated,
            'extra_applied' => $applied,
        )) . "\n");
    }

    public function seed_paypal_test_cli()
    {
        if (php_sapi_name() !== 'cli') {
            show_error('Forbidden', 403);
            return;
        }
        $this->load->model('admin/Product_model');
        $this->load->model('Ec_category_model');
        $this->load->model('store/Store_product_model');

        $countryId = 11;
        $storeId = 8;
        $userId = 3;
        $parentSku = 'PAYPAL-TEST-SE';
        $parentName = 'PayPal testprodukt';
        $children = array(
            array('label' => 'L Testwith', 'price' => 2.00, 'default' => 0, 'sort' => 1),
            array('label' => 'Test with 1 SEK', 'price' => 1.00, 'default' => 1, 'sort' => 2),
            array('label' => 'Test with 3 SEK', 'price' => 3.00, 'default' => 0, 'sort' => 3),
        );

        $img = $this->db
            ->select('image')
            ->from('products')
            ->where('country_id', $countryId)
            ->where('image !=', '')
            ->group_start()
                ->where('store_id IS NULL', null, false)
                ->or_where('store_id', 0)
            ->group_end()
            ->order_by('id', 'desc')
            ->limit(1)
            ->get()
            ->row();
        $image = $img ? $img->image : '';

        $this->db
            ->group_start()
                ->where('store_id IS NULL', null, false)
                ->or_where('store_id', 0)
            ->group_end()
            ->where('sku', $parentSku);
        $parent = $this->db->get('products')->row();

        $parentPayload = array(
            'store_id' => null,
            'source_product_id' => null,
            'country_id' => $countryId,
            'name' => $parentName,
            'sku' => $parentSku,
            'parent_sku' => '',
            'is_default' => 0,
            'sort_order' => 0,
            'slug' => $this->Product_model->unique_slug($parent ? $parent->slug : 'paypal-testprodukt', $parent ? (int) $parent->id : 0),
            'brand' => '',
            'price' => 1.00,
            'cost_price' => 1.00,
            'compare_price' => 0,
            'max_sale_price' => 0,
            'stock' => 50,
            'ship_min_days' => 1,
            'ship_max_days' => 2,
            'description' => 'PayPal testprodukt. Används endast för att testa checkout.',
            'short_details' => 'Testprodukt för PayPal-betalning. Välj en variant och betala 1–3 kr.',
            'details' => '<p>Testprodukt för PayPal. Priserna är låga med flit så checkout kan testas.</p>',
            'seo_title' => $parentName,
            'seo_description' => 'PayPal testprodukt för checkout.',
            'seo_keywords' => 'paypal, test',
            'options_title' => 'Välj variant',
            'image' => $image,
            'status' => 1,
            'auto_add_to_stores' => 1,
            'created_by' => $userId,
        );

        if ($parent) {
            $parentId = $this->Product_model->save($parentPayload, (int) $parent->id);
        } else {
            $parentId = $this->Product_model->save($parentPayload, 0);
        }

        $this->Ec_category_model->set_product_categories($parentId, array(494, 625));

        $created = array();
        foreach ($children as $child) {
            $name = $parentName . ' - ' . $child['label'];
            $suffix = strtoupper(preg_replace('/[^A-Za-z0-9]+/', '-', $child['label']));
            $suffix = trim($suffix, '-');
            $sku = $parentSku . '-' . $suffix;
            $this->db
                ->group_start()
                    ->where('store_id IS NULL', null, false)
                    ->or_where('store_id', 0)
                ->group_end()
                ->where('sku', $sku);
            $existing = $this->db->get('products')->row();
            $payload = array(
                'store_id' => null,
                'source_product_id' => null,
                'country_id' => $countryId,
                'name' => $name,
                'sku' => $existing ? $existing->sku : $this->Product_model->unique_sku($sku, $existing ? (int) $existing->id : 0),
                'parent_sku' => $parentSku,
                'is_default' => !empty($child['default']) ? 1 : 0,
                'sort_order' => (int) $child['sort'],
                'slug' => $this->Product_model->unique_slug($existing ? $existing->slug : $name, $existing ? (int) $existing->id : 0),
                'brand' => '',
                'price' => (float) $child['price'],
                'cost_price' => (float) $child['price'],
                'compare_price' => 0,
                'max_sale_price' => 0,
                'stock' => 50,
                'ship_min_days' => 1,
                'ship_max_days' => 2,
                'description' => $parentPayload['description'],
                'short_details' => $parentPayload['short_details'],
                'details' => $parentPayload['details'],
                'seo_title' => $name,
                'seo_description' => $parentPayload['seo_description'],
                'seo_keywords' => $parentPayload['seo_keywords'],
                'image' => $image,
                'status' => 1,
                'auto_add_to_stores' => 1,
                'created_by' => $userId,
            );
            $childId = $existing
                ? $this->Product_model->save($payload, (int) $existing->id)
                : $this->Product_model->save($payload, 0);
            if (function_exists('product_sync_default_child')) {
                product_sync_default_child($childId);
            }
            $this->Ec_category_model->set_product_categories($childId, array(494, 625));
            $created[] = array('id' => (int) $childId, 'label' => $child['label'], 'price' => (float) $child['price']);
        }

        $family = array($parentId);
        foreach ($created as $row) {
            $family[] = (int) $row['id'];
        }
        foreach (array_unique($family) as $catalogId) {
            if (function_exists('ec_add_catalog_to_all_country_stores')) {
                ec_add_catalog_to_all_country_stores($catalogId, true);
            } else {
                $this->Store_product_model->auto_add_catalog_to_stores($catalogId);
            }
        }

        $store = $this->db->where('id', $storeId)->get('stores')->row();
        $pinned = array();
        $pinMap = array((int) $parentId => 1.00);
        foreach ($created as $row) {
            $pinMap[(int) $row['id']] = (float) $row['price'];
        }
        foreach ($pinMap as $catalogId => $desired) {
            $source = $this->db->where('id', (int) $catalogId)->get('products')->row();
            if (!$source || !$store) {
                continue;
            }
            $wholesale = product_wholesale_price($source, $storeId);
            $plus = store_price_plus_amount($store);
            $catalogSell = isset($source->price) ? (float) $source->price : 0.0;
            $catalogBase = function_exists('product_base_price') ? product_base_price($source) : $catalogSell;
            $extra = round($desired - $wholesale - $plus - ($catalogSell - $catalogBase), 2);
            if (function_exists('ec_apply_catalog_extra_amount')) {
                ec_apply_catalog_extra_amount($catalogId, $extra);
            }
            $this->db
                ->where('source_product_id', (int) $catalogId)
                ->where('store_id', $storeId)
                ->update('products', array(
                    'price' => $desired,
                    'extra_amount' => $extra,
                ));
            $copy = $this->db
                ->where('source_product_id', (int) $catalogId)
                ->where('store_id', $storeId)
                ->get('products')
                ->row();
            $pinned[] = array(
                'catalog_id' => (int) $catalogId,
                'copy_id' => $copy ? (int) $copy->id : 0,
                'desired' => $desired,
                'listed' => $copy ? (float) $copy->price : 0,
                'extra' => $extra,
            );
        }

        $this->output->set_content_type('text/plain')->set_output(json_encode(array(
            'parent_id' => (int) $parentId,
            'slug' => 'paypal-testprodukt',
            'children' => $created,
            'pinned' => $pinned,
        )) . "\n");
    }

    public function import_sheet_cli()
    {
        if (php_sapi_name() !== 'cli') {
            show_error('Forbidden', 403);
            return;
        }
        $path = trim((string) getenv('IMPORT_SHEET_JSON'));
        if ($path === '' || !is_file($path)) {
            $this->output->set_content_type('text/plain')->set_output("IMPORT_SHEET_JSON missing\n");
            return;
        }
        $rows = json_decode((string) file_get_contents($path), true);
        if (!is_array($rows)) {
            $this->output->set_content_type('text/plain')->set_output("IMPORT_SHEET_JSON invalid\n");
            return;
        }
        $countryId = (int) getenv('IMPORT_COUNTRY_ID') ?: 11;
        $storeId = (int) getenv('IMPORT_STORE_ID') ?: 8;
        $userId = (int) getenv('IMPORT_USER_ID');
        $logPath = trim((string) getenv('IMPORT_SHEET_LOG'));
        if ($logPath === '') {
            $logPath = '/tmp/sweden_ae_import.log';
        }
        $this->load->library('Product_importer');
        require_once APPPATH . 'libraries/importers/Aliexpress_com.php';
        $ae = new Aliexpress_com();
        $out = array();
        foreach ($rows as $row) {
            $name = isset($row['name']) ? trim((string) $row['name']) : '';
            $item = isset($row['item']) ? trim((string) $row['item']) : '';
            $search = isset($row['search']) ? trim((string) $row['search']) : '';
            $url = $item;
            $resolvedFrom = 'item';
            if ($url === '' || !preg_match('#/item/\d+#', $url)) {
                $url = $search !== '' ? $ae->first_item_url($search) : '';
                $resolvedFrom = 'search';
            }
            $entry = array(
                'row' => isset($row['row']) ? (int) $row['row'] : 0,
                'name' => $name,
                'url' => $url,
                'resolved_from' => $resolvedFrom,
            );
            if ($url === '') {
                $entry['ok'] = 0;
                $entry['error'] = 'No AliExpress item URL';
                $out[] = $entry;
                $this->sheet_log($logPath, $entry);
                continue;
            }
            $extra = array(
                'csv_name' => $name,
                'product_name' => $name,
                'existing_mode' => 'skip_name',
                'auto_add_to_stores' => 1,
                'add_to_all_stores' => 1,
                'category' => isset($row['category']) ? trim((string) $row['category']) : '',
                'sub_category' => isset($row['sub_category']) ? trim((string) $row['sub_category']) : '',
            );
            if (!empty($row['sizes']) && is_array($row['sizes'])) {
                $extra['sizes'] = $row['sizes'];
            }
            if (!empty($row['ship_min_days']) || !empty($row['ship_max_days'])) {
                $extra['ship_min_days'] = (int) $row['ship_min_days'];
                $extra['ship_max_days'] = (int) $row['ship_max_days'];
            }
            $cost = isset($row['cost']) && $row['cost'] !== '' && $row['cost'] !== null ? (float) $row['cost'] : null;
            try {
                $result = $this->product_importer->import($url, $countryId, $userId, $cost, $extra);
                $catalogId = (int) $result['product_id'];
                if (isset($row['extra_amount']) && $row['extra_amount'] !== '' && $row['extra_amount'] !== null && function_exists('ec_apply_catalog_extra_amount')) {
                    $extraAmt = round((float) $row['extra_amount'], 2);
                    $parent = $this->db->where('id', $catalogId)->get('products')->row();
                    $ids = array($catalogId);
                    if ($parent && $this->db->field_exists('parent_sku', 'products')) {
                        $sku = trim((string) $parent->sku);
                        if ($sku !== '') {
                            $kids = $this->db
                                ->select('id')
                                ->from('products')
                                ->group_start()
                                    ->where('store_id IS NULL', null, false)
                                    ->or_where('store_id', 0)
                                ->group_end()
                                ->where('parent_sku', $sku)
                                ->get()
                                ->result();
                            foreach ($kids as $kid) {
                                $ids[] = (int) $kid->id;
                            }
                        }
                    }
                    foreach (array_unique($ids) as $id) {
                        ec_apply_catalog_extra_amount((int) $id, $extraAmt);
                    }
                    $entry['extra_amount'] = $extraAmt;
                    $entry['extra_ids'] = array_values(array_unique($ids));
                }
                $copy = $this->db
                    ->select('id')
                    ->from('products')
                    ->where('source_product_id', $catalogId)
                    ->where('store_id', $storeId)
                    ->group_start()
                        ->where('parent_sku', '')
                        ->or_where('parent_sku IS NULL', null, false)
                    ->group_end()
                    ->order_by('id', 'desc')
                    ->limit(1)
                    ->get()
                    ->row();
                if (!$copy) {
                    $copy = $this->db
                        ->select('id')
                        ->from('products')
                        ->where('source_product_id', $catalogId)
                        ->where('store_id', $storeId)
                        ->order_by('id', 'desc')
                        ->limit(1)
                        ->get()
                        ->row();
                }
                $entry['ok'] = 1;
                $entry['product_id'] = $catalogId;
                $entry['copy_id'] = $copy ? (int) $copy->id : 0;
                $entry['existing'] = !empty($result['existing']) ? 1 : 0;
                $entry['auto_added'] = isset($result['auto_added']) ? (int) $result['auto_added'] : 0;
            } catch (Exception $e) {
                $entry['ok'] = 0;
                $entry['error'] = $e->getMessage();
            }
            $out[] = $entry;
            $this->sheet_log($logPath, $entry);
        }
        $summary = array(
            'total' => count($out),
            'ok' => 0,
            'failed' => 0,
            'existing' => 0,
            'copy_ids' => array(),
            'catalog_ids' => array(),
            'rows' => $out,
        );
        foreach ($out as $entry) {
            if (!empty($entry['ok'])) {
                $summary['ok']++;
                if (!empty($entry['existing'])) {
                    $summary['existing']++;
                }
                if (!empty($entry['product_id'])) {
                    $summary['catalog_ids'][] = (int) $entry['product_id'];
                }
                if (!empty($entry['copy_id'])) {
                    $summary['copy_ids'][] = (int) $entry['copy_id'];
                }
            } else {
                $summary['failed']++;
            }
        }
        $resultPath = preg_replace('/\.log$/', '.results.json', $logPath);
        @file_put_contents($resultPath, json_encode($summary));
        $this->output->set_content_type('text/plain')->set_output(json_encode(array(
            'total' => $summary['total'],
            'ok' => $summary['ok'],
            'failed' => $summary['failed'],
            'existing' => $summary['existing'],
            'copy_ids' => $summary['copy_ids'],
            'results' => $resultPath,
        )) . "\n");
    }

    public function ai_queue_selected_cli()
    {
        if (php_sapi_name() !== 'cli') {
            show_error('Forbidden', 403);
            return;
        }
        $storeId = (int) getenv('IMPORT_STORE_ID') ?: 8;
        $countryId = (int) getenv('IMPORT_COUNTRY_ID') ?: 11;
        $idsRaw = trim((string) getenv('IMPORT_COPY_IDS'));
        $resultPath = trim((string) getenv('IMPORT_SHEET_RESULTS'));
        $copyIds = array();
        if ($idsRaw !== '') {
            foreach (preg_split('/[,\s]+/', $idsRaw) as $id) {
                if ((int) $id > 0) {
                    $copyIds[] = (int) $id;
                }
            }
        } elseif ($resultPath !== '' && is_file($resultPath)) {
            $json = json_decode((string) file_get_contents($resultPath), true);
            if (!empty($json['copy_ids']) && is_array($json['copy_ids'])) {
                foreach ($json['copy_ids'] as $id) {
                    if ((int) $id > 0) {
                        $copyIds[] = (int) $id;
                    }
                }
            }
        }
        $copyIds = array_values(array_unique($copyIds));
        if (!$copyIds) {
            $this->output->set_content_type('text/plain')->set_output("IMPORT_COPY_IDS missing\n");
            return;
        }
        $this->load->model('admin/Ai_content_model');
        $this->Ai_content_model->ensure_schema();
        $active = $this->Ai_content_model->active_job_for_store($storeId);
        if ($active && !$this->Ai_content_model->worker_is_busy()) {
            $this->Ai_content_model->abandon_job($active->id);
        } elseif ($active && $this->Ai_content_model->worker_is_busy()) {
            $this->output->set_content_type('text/plain')->set_output(json_encode(array(
                'ok' => 0,
                'error' => 'An AI worker is already running. New rewrite was not started.',
                'job_id' => (int) $active->id,
            )) . "\n");
            return;
        }
        $job = $this->Ai_content_model->create_job($countryId, $storeId, 0, $copyIds);
        $job = $this->Ai_content_model->start_job($job->id);
        $this->Ai_content_model->try_dispatch($job ? (int) $job->id : 0);
        $this->output->set_content_type('text/plain')->set_output(json_encode(array(
            'ok' => 1,
            'job_id' => $job ? (int) $job->id : 0,
            'queued' => $job ? (int) $job->total_products : 0,
            'copy_ids' => $copyIds,
        )) . "\n");
    }

    public function rewrite_listing_cli()
    {
        if (php_sapi_name() !== 'cli') {
            show_error('Forbidden', 403);
            return;
        }
        $productId = (int) getenv('REWRITE_PRODUCT_ID');
        $reviewCount = (int) getenv('REWRITE_REVIEW_COUNT');
        if ($reviewCount < 1) {
            $reviewCount = 5;
        }
        $this->load->model('admin/Ai_content_model');
        $result = $this->Ai_content_model->rewrite_listing($productId, $reviewCount);
        $this->output->set_content_type('text/plain')->set_output(json_encode($result) . "\n");
    }

    public function seed_legal_pages_cli($storeId = 8)
    {
        if (php_sapi_name() !== 'cli') {
            show_error('Forbidden', 403);
            return;
        }
        $n = function_exists('store_legal_pages_seed')
            ? store_legal_pages_seed((int) $storeId)
            : 0;
        $this->output->set_content_type('text/plain')->set_output("seeded {$n} pages for store {$storeId}\n");
    }

    protected function sheet_log($path, $entry)
    {
        $line = date('c') . ' ' . json_encode($entry) . "\n";
        @file_put_contents($path, $line, FILE_APPEND);
    }
}
