<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Product_importer {

    /** @var CI_Controller */
    protected $ci;

    public function __construct()
    {
        $this->ci =& get_instance();
        $this->ci->load->model('Product_import_model');
        $this->ci->load->model('Product_model');
        $this->ci->load->model('Country_model');
        $this->ci->load->model('Ec_category_model');
    }

    public function parse_url($url)
    {
        $url = trim((string) $url);
        if ($url === '' || !preg_match('#^https?://#i', $url)) {
            throw new Exception('Please paste a valid product URL.');
        }
        $parts = parse_url($url);
        if (empty($parts['host'])) {
            throw new Exception('Please paste a valid product URL.');
        }
        $host = $this->ci->Product_import_model->normalize_host($parts['host']);
        $path = isset($parts['path']) ? $parts['path'] : '/';
        $scheme = isset($parts['scheme']) ? strtolower($parts['scheme']) : 'https';
        $canonical = $scheme . '://' . $host . $path;
        return array(
            'url' => $url,
            'canonical' => $canonical,
            'host' => $host,
        );
    }

    public function resolve_source($url, $countryId, $userId)
    {
        $parsed = $this->parse_url($url);
        $source = $this->ci->Product_import_model->find_source($parsed['host']);
        $className = $source ? $source->importer_class : '';
        if (!$source || !$this->ci->Product_import_model->has_importer_class($className)) {
            $this->ci->Product_import_model->record_unknown($parsed['url'], $parsed['host'], $countryId, $userId);
            throw new Exception('Unknown domain');
        }
        $parsed['source'] = $source;
        $parsed['class'] = $className;
        return $parsed;
    }

    public function preview($url, $countryId, $userId)
    {
        $country = $this->ci->Country_model->get((int) $countryId);
        if (!$country) {
            throw new Exception('Please select a valid country.');
        }
        $resolved = $this->resolve_source($url, $countryId, $userId);
        $className = $resolved['class'];
        require_once $this->ci->Product_import_model->class_file($className);
        $importer = new $className();
        $data = $importer->parse($resolved['url'], false);
        $cost = (float) (isset($data['price']) ? $data['price'] : 0);
        if ($cost <= 0) {
            throw new Exception('Could not read a price from this product page.');
        }
        return array(
            'name' => trim(isset($data['name']) ? $data['name'] : ''),
            'sku' => trim(isset($data['sku']) ? $data['sku'] : ''),
            'cost' => $cost,
        );
    }

    public function import($url, $countryId, $userId, $costOverride = null)
    {
        $result = $this->run_import($url, $countryId, $userId, array(
            'cost_override' => $costOverride,
            'existing_mode' => 'return',
        ));
        return array(
            'product_id' => (int) $result['product_id'],
            'existing' => !empty($result['existing']) || !empty($result['skipped']),
        );
    }

    public function import_csv_row($url, $countryId, $userId, $row)
    {
        return $this->run_import($url, $countryId, $userId, array(
            'selling_price' => isset($row['price']) ? (float) $row['price'] : 0,
            'force_selling_price' => true,
            'ship_min_days' => isset($row['delivery_min_days']) ? (int) $row['delivery_min_days'] : 0,
            'ship_max_days' => isset($row['delivery_max_days']) ? (int) $row['delivery_max_days'] : 0,
            'category' => isset($row['category']) ? trim((string) $row['category']) : '',
            'sub_category' => isset($row['sub_category']) ? trim((string) $row['sub_category']) : '',
            'csv_name' => isset($row['product']) ? trim((string) $row['product']) : '',
            'existing_mode' => 'skip',
        ));
    }

    protected function run_import($url, $countryId, $userId, $options = array())
    {
        $countryId = (int) $countryId;
        $country = $this->ci->Country_model->get($countryId);
        if (!$country) {
            throw new Exception('Please select a valid country.');
        }

        $resolved = $this->resolve_source($url, $countryId, $userId);
        $existing = $this->find_existing_product($resolved, $countryId);
        if ($existing) {
            if (isset($options['existing_mode']) && $options['existing_mode'] === 'skip') {
                return array(
                    'status' => 'skipped',
                    'skipped' => true,
                    'product_id' => (int) $existing->id,
                    'message' => 'Product already exists for the selected country.',
                );
            }
            $this->convert_product_images((int) $existing->id);
            return array(
                'status' => 'existing',
                'existing' => true,
                'product_id' => (int) $existing->id,
            );
        }

        $className = $resolved['class'];
        require_once $this->ci->Product_import_model->class_file($className);
        $importer = new $className();
        $data = $importer->parse($resolved['url']);

        $name = trim(isset($data['name']) ? $data['name'] : '');
        if ($name === '' && !empty($options['csv_name'])) {
            $name = trim((string) $options['csv_name']);
        }
        $sku = trim(isset($data['sku']) ? $data['sku'] : '');
        $fetched = (float) (isset($data['price']) ? $data['price'] : 0);
        $forceSelling = !empty($options['force_selling_price']);
        $sellingPrice = $forceSelling
            ? (float) (isset($options['selling_price']) ? $options['selling_price'] : 0)
            : 0;

        if ($forceSelling) {
            if ($sellingPrice <= 0) {
                throw new Exception('CSV price must be a number greater than 0.');
            }
            $catalogPrice = $sellingPrice;
            $cost = $fetched;
        } else {
            $costOverride = isset($options['cost_override']) ? $options['cost_override'] : null;
            $cost = ($costOverride !== null && $costOverride !== '') ? (float) $costOverride : $fetched;
            if ($cost <= 0) {
                $cost = $fetched;
            }
            if ($cost <= 0) {
                throw new Exception('Please enter a base price, or use a URL that includes a price.');
            }
            $catalogPrice = $cost;
        }

        $payload = array(
            'store_id' => null,
            'source_product_id' => null,
            'supplier_id' => (int) $resolved['source']->supplier_id,
            'country_id' => $countryId,
            'name' => $name,
            'sku' => $this->ci->Product_model->unique_sku($sku !== '' ? $sku : strtoupper(substr(md5($resolved['canonical']), 0, 10))),
            'slug' => $this->ci->Product_model->unique_slug(url_title($name, '-', true)),
            'price' => $catalogPrice,
            'compare_price' => (float) (isset($data['compare_price']) ? $data['compare_price'] : 0),
            'cost_price' => $cost,
            'max_sale_price' => 0,
            'stock' => (int) (isset($data['stock']) ? $data['stock'] : 0),
            'description' => isset($data['description']) ? $data['description'] : '',
            'details' => isset($data['details']) ? $data['details'] : '',
            'seo_title' => isset($data['seo_title']) ? $data['seo_title'] : $name,
            'seo_description' => isset($data['seo_description']) ? $data['seo_description'] : '',
            'seo_keywords' => '',
            'image' => $this->local_image_path(isset($data['image']) ? $data['image'] : ''),
            'source_url' => $resolved['canonical'],
            'status' => 1,
            'created_by' => (int) $userId,
        );
        if (isset($options['ship_min_days']) || isset($options['ship_max_days'])) {
            $payload['ship_min_days'] = max(0, (int) (isset($options['ship_min_days']) ? $options['ship_min_days'] : 0));
            $payload['ship_max_days'] = max(0, (int) (isset($options['ship_max_days']) ? $options['ship_max_days'] : 0));
        }

        $productId = $this->ci->Product_model->save($payload, 0);
        $this->ci->Product_model->replace_sources($productId, array(
            array(
                'source_url' => $resolved['url'],
                'source_price' => $fetched > 0 ? $fetched : $cost,
                'label' => $resolved['host'],
            ),
        ));
        if (!empty($data['gallery']) && is_array($data['gallery'])) {
            foreach ($data['gallery'] as $path) {
                $path = $this->local_image_path($path);
                if ($path !== '') {
                    $this->ci->Product_model->add_image($productId, $path);
                }
            }
        }

        $this->assign_csv_categories($productId, $countryId, $options);

        return array(
            'status' => 'imported',
            'existing' => false,
            'product_id' => $productId,
        );
    }

    protected function find_existing_product($resolved, $countryId = 0)
    {
        $urls = array($resolved['canonical']);
        if (!empty($resolved['url']) && $resolved['url'] !== $resolved['canonical']) {
            $urls[] = $resolved['url'];
        }
        foreach ($urls as $url) {
            $existing = $this->ci->Product_model->find_by_source_url($url, $countryId);
            if ($existing) {
                return $existing;
            }
        }
        return null;
    }

    protected function assign_csv_categories($productId, $countryId, $options)
    {
        $categoryName = isset($options['category']) ? trim((string) $options['category']) : '';
        $subName = isset($options['sub_category']) ? trim((string) $options['sub_category']) : '';
        if ($categoryName === '') {
            return;
        }
        $parentId = $this->ci->Ec_category_model->find_or_create($countryId, $categoryName, 0);
        $ids = array();
        if ($parentId) {
            $ids[] = $parentId;
        }
        if ($subName !== '') {
            $childId = $this->ci->Ec_category_model->find_or_create($countryId, $subName, $parentId);
            if ($childId) {
                $ids[] = $childId;
            }
        }
        if ($ids) {
            $this->ci->Ec_category_model->set_product_categories($productId, $ids);
        }
    }

    protected function convert_product_images($productId)
    {
        $product = $this->ci->Product_model->get((int) $productId);
        if ($product && !empty($product->image)) {
            $converted = $this->convert_saved_image($product->image);
            if ($converted !== '' && $converted !== $product->image) {
                $this->ci->Product_model->save(array('image' => $converted), (int) $productId);
            }
        }
        foreach ($this->ci->Product_model->images((int) $productId) as $image) {
            if (empty($image->image)) {
                continue;
            }
            $converted = $this->convert_saved_image($image->image);
            if ($converted !== '' && $converted !== $image->image) {
                $this->ci->db->where('id', (int) $image->id)->update('product_images', array('image' => $converted));
            }
        }
    }

    protected function convert_saved_image($path)
    {
        $path = $this->local_image_path($path);
        if ($path === '' || !function_exists('ec_convert_image_to_webp')) {
            return $path;
        }
        $absolute = FCPATH . $path;
        if (!is_file($absolute)) {
            return $path;
        }
        $converted = ec_convert_image_to_webp($absolute);
        $public = function_exists('ec_public_upload_path') ? ec_public_upload_path($converted) : $path;
        return $public !== '' ? $public : $path;
    }

    protected function local_image_path($path)
    {
        $path = trim((string) $path);
        if ($path === '' || preg_match('#^(https?:)?//#i', $path)) {
            return '';
        }
        $path = ltrim($path, '/');
        if (strpos($path, 'uploads/products/') !== 0) {
            return '';
        }
        return $path;
    }

    public function details_from_url($url)
    {
        $parsed = $this->parse_url($url);
        $source = $this->ci->Product_import_model->find_source($parsed['host']);
        $className = $source ? $source->importer_class : '';
        if (!$source || !$this->ci->Product_import_model->has_importer_class($className)) {
            throw new Exception('No importer for this source link.');
        }
        require_once $this->ci->Product_import_model->class_file($className);
        $importer = new $className();
        $data = $importer->parse($parsed['url'], false);
        if (!empty($data['details'])) {
            return $data['details'];
        }
        if (!empty($data['description'])) {
            return '<p>' . htmlspecialchars($data['description'], ENT_QUOTES, 'UTF-8') . '</p>';
        }
        return '';
    }
}
