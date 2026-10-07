<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Product_importer {

    /** @var CI_Controller */
    protected $ci;

    public function __construct()
    {
        $this->ci =& get_instance();
        $this->ci->load->model('admin/Product_import_model');
        $this->ci->load->model('admin/Product_model');
        $this->ci->load->model('admin/Country_model');
        $this->ci->load->model('Ec_category_model');
    }

    public function parse_url($url)
    {
        $url = $this->normalize_import_url($url);
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

    protected function normalize_import_url($url)
    {
        $url = trim((string) $url);
        if ($url === '') {
            return '';
        }
        if (preg_match('/[?&]a=([0-9a-fA-F]{20,})/', $url, $match)) {
            $decoded = @hex2bin($match[1]);
            if (is_string($decoded) && preg_match('#https?://#i', $decoded)) {
                $url = $decoded;
            }
        }
        if (preg_match('#https?://(?:www\.)?(amazon\.[a-z.]+)/(?:[^/\\s"\']+/)?(?:dp|gp/product|gp/aw/d)/([A-Z0-9]{8,13})#i', $url, $match)) {
            return 'https://www.' . strtolower($match[1]) . '/dp/' . strtoupper($match[2]);
        }
        if (!preg_match('#^https?://#i', $url) && preg_match('#https?://[^\s]+#i', $url, $match)) {
            return $this->normalize_import_url($match[0]);
        }
        return $url;
    }

    public function country_id_for_url($url, $fallbackCountryId = 0)
    {
        try {
            $parsed = $this->parse_url($url);
        } catch (Exception $e) {
            return (int) $fallbackCountryId;
        }
        $fromDomain = (int) $this->ci->Product_import_model->country_id_for_host($parsed['host']);
        return $fromDomain > 0 ? $fromDomain : (int) $fallbackCountryId;
    }

    public function source_info($url)
    {
        $parsed = $this->parse_url($url);
        $source = $this->ci->Product_import_model->ensure_source($parsed['host']);
        $countryId = (int) $this->ci->Product_import_model->country_id_for_host($parsed['host']);
        $country = $countryId ? $this->ci->Country_model->get($countryId) : null;
        return array(
            'host' => $parsed['host'],
            'country_id' => $countryId,
            'country_name' => $country ? $country->name : '',
            'known' => $source && $this->ci->Product_import_model->has_importer_class($source->importer_class),
        );
    }

    public function resolve_source($url, $countryId, $userId)
    {
        $parsed = $this->parse_url($url);
        $source = $this->ci->Product_import_model->ensure_source($parsed['host'], $countryId);
        $className = $source ? $source->importer_class : '';
        if (!$source || !$this->ci->Product_import_model->has_importer_class($className)) {
            throw new Exception('Unknown domain');
        }
        $domainCountryId = !empty($source->country_id)
            ? (int) $source->country_id
            : (int) $this->ci->Product_import_model->country_id_for_host($parsed['host']);
        $parsed['source'] = $source;
        $parsed['class'] = $className;
        $parsed['country_id'] = $domainCountryId > 0 ? $domainCountryId : (int) $countryId;
        return $parsed;
    }

    public function preview($url, $countryId, $userId)
    {
        $countryId = $this->country_id_for_url($url, $countryId);
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
            'cost' => round($cost, 2),
            'country_id' => (int) (isset($resolved['country_id']) ? $resolved['country_id'] : $countryId),
            'country_name' => $country->name,
        );
    }

    public function import($url, $countryId, $userId, $costOverride = null, $extra = array())
    {
        $options = is_array($extra) ? $extra : array();
        $options['cost_override'] = $costOverride;
        if (empty($options['existing_mode'])) {
            $options['existing_mode'] = 'return';
        }
        $countryId = $this->country_id_for_url($url, $countryId);
        $userId = $this->resolve_catalog_owner_id($userId, $countryId);
        $listing = $this->import_listing_if_needed($url, $countryId, $userId, $options);
        if ($listing !== null) {
            return $listing;
        }
        $result = $this->run_import($url, $countryId, $userId, $options);
        return array(
            'product_id' => (int) $result['product_id'],
            'existing' => !empty($result['existing']) || !empty($result['skipped']),
            'auto_added' => isset($result['auto_added']) ? (int) $result['auto_added'] : 0,
            'child_ids' => isset($result['child_ids']) ? $result['child_ids'] : array(),
        );
    }

    protected function import_listing_if_needed($url, $countryId, $userId, $options)
    {
        if (!empty($options['csv_name']) || !empty($options['product_name'])) {
            return null;
        }
        try {
            $resolved = $this->resolve_source($url, $countryId, $userId);
        } catch (Exception $e) {
            return null;
        }
        $className = $resolved['class'];
        require_once $this->ci->Product_import_model->class_file($className);
        $importer = new $className();
        if (!method_exists($importer, 'is_listing_url') || !$importer->is_listing_url($resolved['url'])) {
            return null;
        }
        if (!method_exists($importer, 'listing_product_urls')) {
            return null;
        }
        $productUrls = $importer->listing_product_urls($resolved['url']);
        if (!$productUrls) {
            throw new Exception('No products were found on this listing page.');
        }
        $listingOptions = $options;
        unset($listingOptions['cost_override']);
        $imported = 0;
        $existing = 0;
        $failed = 0;
        $lastId = 0;
        $autoAdded = 0;
        foreach ($productUrls as $productUrl) {
            try {
                $row = $this->run_import($productUrl, $countryId, $userId, $listingOptions);
                $lastId = (int) $row['product_id'];
                $autoAdded += isset($row['auto_added']) ? (int) $row['auto_added'] : 0;
                if (!empty($row['existing']) || !empty($row['skipped'])) {
                    $existing++;
                } else {
                    $imported++;
                }
            } catch (Exception $e) {
                $failed++;
            }
        }
        if ($imported === 0 && $existing === 0) {
            throw new Exception('Could not import products from this listing page.');
        }
        $this->ci->Product_import_model->clear_failed($resolved['url'], $userId);
        return array(
            'product_id' => $lastId,
            'existing' => $imported === 0 && $existing > 0,
            'auto_added' => $autoAdded,
            'listing_imported' => $imported,
            'listing_existing' => $existing,
            'listing_failed' => $failed,
            'listing_total' => count($productUrls),
        );
    }

    public function import_csv_row($url, $countryId, $userId, $row, $extra = array())
    {
        return $this->run_import($url, $countryId, $userId, array(
            'selling_price' => isset($row['price']) ? (float) $row['price'] : 0,
            'force_selling_price' => true,
            'ship_min_days' => isset($row['delivery_min_days']) ? (int) $row['delivery_min_days'] : 0,
            'ship_max_days' => isset($row['delivery_max_days']) ? (int) $row['delivery_max_days'] : 0,
            'category' => isset($row['category']) ? trim((string) $row['category']) : '',
            'sub_category' => isset($row['sub_category']) ? trim((string) $row['sub_category']) : '',
            'csv_name' => isset($row['product']) ? trim((string) $row['product']) : '',
            'existing_mode' => 'skip_name',
            'reuse_source' => true,
            'auto_add_to_stores' => !empty($extra['auto_add_to_stores']),
        ));
    }

    protected function run_import($url, $countryId, $userId, $options = array())
    {
        $countryId = $this->country_id_for_url($url, $countryId);
        $country = $this->ci->Country_model->get($countryId);
        if (!$country) {
            throw new Exception('Please select a valid country.');
        }
        $userId = $this->resolve_catalog_owner_id($userId, $countryId);

        $resolved = $this->resolve_source($url, $countryId, $userId);
        if (!empty($resolved['country_id'])) {
            $countryId = (int) $resolved['country_id'];
            $matched = $this->ci->Country_model->get($countryId);
            if ($matched) {
                $country = $matched;
            }
        }
        $csvName = isset($options['csv_name']) ? trim((string) $options['csv_name']) : '';
        $existingMode = isset($options['existing_mode']) ? $options['existing_mode'] : 'return';

        if ($existingMode === 'skip_name' && $csvName !== '') {
            $existing = $this->ci->Product_model->find_catalog_by_name($csvName, $countryId);
            if ($existing) {
                $this->refresh_missing_details((int) $existing->id, $resolved, $options);
                $this->refresh_missing_images((int) $existing->id, $resolved);
                $this->assert_required_media((int) $existing->id, $options);
                $this->ci->Product_import_model->clear_failed($resolved['url'], $userId);
                return array(
                    'status' => 'skipped',
                    'skipped' => true,
                    'product_id' => (int) $existing->id,
                    'message' => 'A product with this name already exists for the selected country.',
                    'auto_added' => $this->maybe_auto_add_product((int) $existing->id, $options),
                );
            }
        } else {
            $existing = $this->find_existing_product($resolved, $countryId);
            if ($existing) {
                if ($existingMode === 'skip') {
                    $this->refresh_missing_images((int) $existing->id, $resolved);
                    $this->refresh_missing_details((int) $existing->id, $resolved, $options);
                    $this->assert_required_media((int) $existing->id, $options);
                    $this->ci->Product_import_model->clear_failed($resolved['url'], $userId);
                    return array(
                        'status' => 'skipped',
                        'skipped' => true,
                        'product_id' => (int) $existing->id,
                        'message' => 'Product already exists for the selected country.',
                    );
                }
                $this->convert_product_images((int) $existing->id);
                $this->refresh_missing_images((int) $existing->id, $resolved);
                $this->refresh_missing_details((int) $existing->id, $resolved, $options);
                $this->assign_csv_categories((int) $existing->id, $countryId, $options);
                $this->apply_shipping_days((int) $existing->id, $options);
                $this->apply_stock((int) $existing->id, $options);
                if (isset($options['cost_override']) && $options['cost_override'] !== null && $options['cost_override'] !== '') {
                    $update = array('cost_price' => (float) $options['cost_override']);
                    if (isset($options['catalog_price']) && (float) $options['catalog_price'] > 0) {
                        $update['price'] = (float) $options['catalog_price'];
                    }
                    if (isset($options['max_sale_price'])) {
                        $update['max_sale_price'] = (float) $options['max_sale_price'];
                    }
                    $this->ci->Product_model->save($update, (int) $existing->id);
                }
                $this->apply_creatives((int) $existing->id, $options, false);
                $this->assert_required_media((int) $existing->id, $options);
                $this->ci->Product_import_model->clear_failed($resolved['url'], $userId);
                $existingData = !empty($options['import_data']) && is_array($options['import_data']) ? $options['import_data'] : array();
                $existingData = $this->hydrate_import_images($existingData, $resolved);
                if (!empty($existingData['options_title'])) {
                    $this->ci->Product_model->save(array(
                        'options_title' => trim((string) $existingData['options_title']),
                    ), (int) $existing->id);
                }
                $childIds = $this->create_size_children((int) $existing->id, $existingData, $options);
                return array(
                    'status' => 'existing',
                    'existing' => true,
                    'product_id' => (int) $existing->id,
                    'child_ids' => $childIds,
                    'auto_added' => $this->maybe_auto_add_product((int) $existing->id, $options),
                );
            }
        }

        $reuse = !empty($options['reuse_source']) ? $this->find_existing_product($resolved, $countryId) : null;
        if (!empty($options['import_data']) && is_array($options['import_data'])) {
            $data = $options['import_data'];
        } elseif ($reuse) {
            $data = $this->data_from_existing_product($reuse, $resolved);
        } else {
            $className = $resolved['class'];
            require_once $this->ci->Product_import_model->class_file($className);
            $importer = new $className();
            try {
                $data = $importer->parse($resolved['url']);
            } catch (Exception $e) {
                $data = $this->fallback_import_data($resolved, $options, $csvName, $e);
            }
        }

        $data = $this->hydrate_import_images($data, $resolved);
        $data = $this->apply_provided_images($data, $options);
        $data = $this->apply_provided_details($data, $options);
        if (!empty($options['require_images']) && !$this->import_data_has_image($data)) {
            throw new Exception('Product images are required.');
        }
        if (!empty($options['require_details']) && !$this->import_data_has_details($data)) {
            throw new Exception('Product details are required.');
        }

        $name = $csvName !== '' ? $csvName : trim(isset($data['name']) ? $data['name'] : '');
        $sku = trim(isset($data['sku']) ? $data['sku'] : '');
        $fetched = (float) (isset($data['price']) ? $data['price'] : 0);
        $rate = isset($options['conversion_rate']) ? (float) $options['conversion_rate'] : 1;
        if ($rate <= 0) {
            $rate = 1;
        }
        $converted = round($fetched * $rate, 2);
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
            $cost = ($costOverride !== null && $costOverride !== '') ? (float) $costOverride : $converted;
            if ($cost <= 0) {
                $cost = $converted > 0 ? $converted : $fetched;
            }
            if ($cost <= 0) {
                throw new Exception('Please enter a base price, or use a URL that includes a price.');
            }
            $catalogPrice = $cost;
        }
        if (isset($options['catalog_price']) && (float) $options['catalog_price'] > 0) {
            $catalogPrice = (float) $options['catalog_price'];
        }

        $payload = array(
            'store_id' => null,
            'source_product_id' => null,
            'supplier_id' => $this->supplier_id_from_options($options, $resolved, $country),
            'country_id' => $countryId,
            'name' => $name,
            'sku' => $this->ci->Product_model->unique_sku($sku !== '' ? $sku : strtoupper(substr(md5($resolved['canonical']), 0, 10))),
            'brand' => isset($data['brand']) ? trim((string) $data['brand']) : '',
            'slug' => $this->ci->Product_model->unique_slug(!empty($data['slug']) ? $data['slug'] : $name),
            'price' => $catalogPrice,
            'compare_price' => $this->converted_compare_price($data, $rate, $forceSelling),
            'cost_price' => $cost,
            'max_sale_price' => isset($options['max_sale_price']) ? (float) $options['max_sale_price'] : 0,
            'stock' => $this->stock_from_options($options, (int) (isset($data['stock']) ? $data['stock'] : 0)),
            'description' => isset($data['description']) ? $data['description'] : '',
            'short_details' => isset($data['short_details']) ? $data['short_details'] : (isset($data['short_detail']) ? $data['short_detail'] : ''),
            'details' => isset($data['details']) ? $data['details'] : '',
            'seo_title' => $csvName !== '' ? $name : (isset($data['seo_title']) ? $data['seo_title'] : $name),
            'seo_description' => isset($data['seo_description']) ? $data['seo_description'] : '',
            'seo_keywords' => isset($data['seo_keywords']) ? trim((string) $data['seo_keywords']) : '',
            'options_title' => isset($data['options_title']) ? trim((string) $data['options_title']) : '',
            'image' => $this->local_image_path(isset($data['image']) ? $data['image'] : ''),
            'source_url' => $resolved['canonical'],
            'status' => isset($options['status']) ? ((int) $options['status'] ? 1 : 0) : 0,
            'auto_add_to_stores' => !empty($options['auto_add_to_stores']) ? 1 : 0,
            'created_by' => (int) $userId,
        );
        $shipping = $this->shipping_days_from_options($options);
        if (!$shipping) {
            $shipping = $this->shipping_days_from_options($data);
        }
        if ($shipping) {
            $payload = array_merge($payload, $shipping);
        }

        $productId = $this->ci->Product_model->save($payload, 0);
        $this->ci->Product_model->replace_sources($productId, $this->source_rows_from_options($options, $resolved, $fetched > 0 ? $fetched : $cost));
        $this->apply_creatives($productId, $options, true);
        if (!empty($data['gallery']) && is_array($data['gallery'])) {
            foreach ($data['gallery'] as $path) {
                $path = $this->local_image_path($path);
                if ($path !== '') {
                    $this->ci->Product_model->add_image($productId, $path);
                }
            }
        }

        $this->assign_csv_categories($productId, $countryId, $options);
        $childIds = $this->create_size_children($productId, $data, $options);
        $this->sync_images_to_store_copies($productId);
        $this->ci->Product_import_model->clear_failed($resolved['url'], $userId);

        return array(
            'status' => 'imported',
            'existing' => false,
            'product_id' => $productId,
            'child_ids' => $childIds,
            'auto_added' => $this->maybe_auto_add_product($productId, $options),
        );
    }

    protected function refresh_missing_images($productId, $resolved)
    {
        $product = $this->ci->Product_model->get((int) $productId);
        if (!$product || !empty($product->image)) {
            $this->sync_images_to_store_copies($productId);
            return;
        }
        $className = isset($resolved['class']) ? $resolved['class'] : '';
        if ($className === '') {
            return;
        }
        require_once $this->ci->Product_import_model->class_file($className);
        $importer = new $className();
        try {
            $data = $importer->parse($resolved['url']);
        } catch (Exception $e) {
            return;
        }
        $main = $this->local_image_path(isset($data['image']) ? $data['image'] : '');
        if ($main === '') {
            return;
        }
        $this->ci->Product_model->save(array('image' => $main), (int) $productId);
        if (!empty($data['gallery']) && is_array($data['gallery'])) {
            foreach ($data['gallery'] as $path) {
                $path = $this->local_image_path($path);
                if ($path !== '') {
                    $this->ci->Product_model->add_image((int) $productId, $path);
                }
            }
        }
        $this->convert_product_images((int) $productId);
        $this->sync_images_to_store_copies($productId);
    }

    protected function apply_provided_details($data, $options)
    {
        if (!is_array($data)) {
            $data = array();
        }
        $details = isset($options['details']) ? trim((string) $options['details']) : '';
        $description = isset($options['description']) ? trim((string) $options['description']) : '';
        if ($details !== '') {
            $data['details'] = $details;
        }
        if ($description !== '') {
            $data['description'] = $description;
            if (empty($data['seo_description'])) {
                $data['seo_description'] = function_exists('mb_substr') ? mb_substr($description, 0, 180) : substr($description, 0, 180);
            }
        }
        if (empty($data['details']) && $description !== '') {
            $data['details'] = '<p>' . htmlspecialchars($description, ENT_QUOTES, 'UTF-8') . '</p>';
        }
        return $data;
    }

    protected function refresh_missing_details($productId, $resolved, $options = array())
    {
        $product = $this->ci->Product_model->get((int) $productId);
        if (!$product) {
            return;
        }
        $force = !empty($options['force_refresh_details']);
        $hasDetails = trim((string) $product->details) !== '';
        $data = $this->apply_provided_details(array(), $options);
        if (empty($data['details']) && empty($data['description'])) {
            if ($hasDetails && !$force) {
                return;
            }
            $className = isset($resolved['class']) ? $resolved['class'] : '';
            if ($className === '') {
                return;
            }
            require_once $this->ci->Product_import_model->class_file($className);
            $importer = new $className();
            try {
                $data = $importer->parse($resolved['url'], false);
            } catch (Exception $e) {
                return;
            }
        }
        $update = array();
        if (!empty($data['details'])) {
            $update['details'] = $data['details'];
        }
        if (!empty($data['description'])) {
            $update['description'] = $data['description'];
        }
        if (!empty($data['seo_description'])) {
            $update['seo_description'] = $data['seo_description'];
        } elseif (!empty($data['description'])) {
            $update['seo_description'] = function_exists('mb_substr')
                ? mb_substr($data['description'], 0, 180)
                : substr($data['description'], 0, 180);
        }
        if (isset($data['stock']) && (int) $data['stock'] > 0 && (int) $product->stock <= 0) {
            $update['stock'] = (int) $data['stock'];
        }
        if (!$update) {
            return;
        }
        $this->ci->Product_model->save($update, (int) $productId);
        $this->ci->Product_model->sync_copy_fields((int) $productId, $update);
    }

    protected function sync_images_to_store_copies($productId)
    {
        $productId = (int) $productId;
        $catalog = $this->ci->Product_model->get($productId);
        if (!$catalog || empty($catalog->image) || !empty($catalog->store_id)) {
            return;
        }
        $copies = $this->ci->db
            ->where('source_product_id', $productId)
            ->where('store_id IS NOT NULL', null, false)
            ->where('store_id !=', 0)
            ->get('products')
            ->result();
        if (!$copies) {
            return;
        }
        $gallery = $this->ci->Product_model->images($productId);
        foreach ($copies as $copy) {
            if (empty($copy->image)) {
                $this->ci->Product_model->save(array('image' => $catalog->image), (int) $copy->id);
            }
            if ($gallery && !$this->ci->Product_model->images((int) $copy->id)) {
                foreach ($gallery as $image) {
                    if (!empty($image->image)) {
                        $this->ci->Product_model->add_image((int) $copy->id, $image->image);
                    }
                }
            }
        }
    }

    protected function maybe_auto_add_product($productId, $options)
    {
        if (!empty($options['add_to_all_stores']) && function_exists('ec_add_catalog_to_all_country_stores')) {
            return (int) ec_add_catalog_to_all_country_stores((int) $productId, true);
        }
        if (empty($options['auto_add_to_stores']) || !function_exists('ec_auto_add_catalog_product')) {
            return 0;
        }
        return (int) ec_auto_add_catalog_product((int) $productId, true);
    }

    protected function apply_creatives($productId, $options, $replace = false)
    {
        $rows = array();
        if (!empty($options['creative_links']) && is_array($options['creative_links'])) {
            $rows = $options['creative_links'];
        }
        if (empty($rows)) {
            return;
        }
        if ($replace) {
            $this->ci->Product_model->replace_creatives($productId, $rows);
            return;
        }
        $this->ci->Product_model->add_creatives($productId, $rows);
    }

    protected function resolve_catalog_owner_id($userId, $countryId)
    {
        $userId = (int) $userId;
        if (!function_exists('ec_catalog_import_user_id')) {
            return $userId > 0 ? $userId : 1;
        }
        if ($userId > 0) {
            $user = $this->ci->db
                ->select('UserID, roleID, commission')
                ->where('UserID', $userId)
                ->get('users')
                ->row();
            if ($user && (int) $user->roleID === ROLE_ECOMMERCE) {
                return $userId;
            }
        }
        return ec_catalog_import_user_id($countryId, $userId);
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

    protected function data_from_existing_product($product, $resolved)
    {
        $gallery = array();
        foreach ($this->ci->Product_model->images((int) $product->id) as $image) {
            if (!empty($image->image)) {
                $gallery[] = $image->image;
            }
        }
        $sku = $this->asin_from_url(isset($resolved['url']) ? $resolved['url'] : '');
        if ($sku === '' && !empty($resolved['canonical'])) {
            $sku = $this->asin_from_url($resolved['canonical']);
        }
        if ($sku === '' && !empty($product->sku)) {
            $sku = preg_replace('/-\d+$/', '', (string) $product->sku);
        }
        $cost = (float) $product->cost_price;
        if ($cost <= 0) {
            $sources = $this->ci->Product_model->sources((int) $product->id);
            if ($sources && (float) $sources[0]->source_price > 0) {
                $cost = (float) $sources[0]->source_price;
            }
        }
        return array(
            'name' => $product->name,
            'sku' => $sku,
            'price' => $cost,
            'compare_price' => (float) $product->compare_price,
            'description' => isset($product->description) ? $product->description : '',
            'details' => isset($product->details) ? $product->details : '',
            'stock' => (int) $product->stock,
            'image' => isset($product->image) ? $product->image : '',
            'gallery' => $gallery,
            'seo_title' => $product->name,
            'seo_description' => isset($product->seo_description) ? $product->seo_description : '',
        );
    }

    protected function fallback_import_data($resolved, $options, $csvName, Exception $parseError)
    {
        $name = $csvName !== '' ? $csvName : '';
        if ($name === '' && !empty($options['product_name'])) {
            $name = trim((string) $options['product_name']);
        }
        if ($name === '') {
            $name = $this->name_from_source_url(isset($resolved['url']) ? $resolved['url'] : '');
        }

        $forceSelling = !empty($options['force_selling_price']);
        $sellingPrice = $forceSelling ? (float) (isset($options['selling_price']) ? $options['selling_price'] : 0) : 0;
        $costOverride = isset($options['cost_override']) ? $options['cost_override'] : null;
        $hasPrice = $sellingPrice > 0 || ($costOverride !== null && $costOverride !== '' && (float) $costOverride > 0);
        if ($name === '' || !$hasPrice) {
            throw $parseError;
        }
        if (!empty($options['require_images']) && empty($options['image_files'])) {
            throw $parseError;
        }

        return array(
            'name' => $name,
            'sku' => $this->asin_from_url(isset($resolved['url']) ? $resolved['url'] : ''),
            'price' => 0,
            'compare_price' => 0,
            'description' => '',
            'details' => '',
            'stock' => 0,
            'image' => '',
            'gallery' => array(),
            'seo_title' => $name,
            'seo_description' => '',
        );
    }

    protected function name_from_source_url($url)
    {
        $path = (string) parse_url((string) $url, PHP_URL_PATH);
        if ($path === '') {
            return '';
        }
        if (preg_match('#/([^/]+)/dp/[A-Z0-9]{8,13}#i', $path, $match)) {
            $slug = urldecode($match[1]);
            if (!preg_match('/^(dp|gp|product)$/i', $slug) && strlen($slug) > 4) {
                return trim(preg_replace('/\s+/', ' ', str_replace(array('-', '_'), ' ', $slug)));
            }
        }
        if (preg_match('#/itm/([^/]+)/[0-9]{8,}#i', $path, $match)) {
            $slug = urldecode($match[1]);
            return trim(preg_replace('/\s+/', ' ', str_replace(array('-', '_'), ' ', $slug)));
        }
        return '';
    }

    protected function asin_from_url($url)
    {
        if (preg_match('#/dp/([A-Z0-9]{8,13})#i', (string) $url, $match)) {
            return strtoupper($match[1]);
        }
        return '';
    }

    protected function supplier_id_from_options($options, $resolved, $country)
    {
        $supplierId = !empty($resolved['source']->supplier_id) ? (int) $resolved['source']->supplier_id : 0;
        $name = isset($options['supplier_name']) ? trim((string) $options['supplier_name']) : '';
        if ($name === '' || !method_exists($this->ci->Product_import_model, 'ensure_supplier_named')) {
            return $supplierId;
        }
        $code = '';
        if (is_object($country) && !empty($country->code)) {
            $code = $country->code;
        }
        $named = (int) $this->ci->Product_import_model->ensure_supplier_named($name, $code);
        return $named > 0 ? $named : $supplierId;
    }

    protected function source_rows_from_options($options, $resolved, $price)
    {
        $rows = array(
            array(
                'source_url' => $resolved['url'],
                'source_price' => (float) $price,
                'label' => $resolved['host'],
            ),
        );
        $extras = array();
        if (!empty($options['extra_sources']) && is_array($options['extra_sources'])) {
            $extras = $options['extra_sources'];
        }
        $seen = array($resolved['url'] => true);
        foreach ($extras as $extra) {
            if (is_string($extra) || is_numeric($extra)) {
                $extra = array('source_url' => (string) $extra);
            }
            if (!is_array($extra)) {
                continue;
            }
            $url = isset($extra['source_url']) ? trim((string) $extra['source_url']) : '';
            if ($url === '' || isset($seen[$url])) {
                continue;
            }
            $seen[$url] = true;
            $rows[] = array(
                'source_url' => $url,
                'source_price' => isset($extra['source_price']) ? (float) $extra['source_price'] : (float) $price,
                'label' => isset($extra['label']) ? trim((string) $extra['label']) : $resolved['host'],
            );
        }
        return $rows;
    }

    protected function shipping_days_from_options($options)
    {
        if (!isset($options['ship_min_days']) && !isset($options['ship_max_days'])) {
            return null;
        }
        $min = max(0, (int) (isset($options['ship_min_days']) ? $options['ship_min_days'] : 0));
        $max = max(0, (int) (isset($options['ship_max_days']) ? $options['ship_max_days'] : 0));
        if ($max > 0 && $min > $max) {
            $tmp = $min;
            $min = $max;
            $max = $tmp;
        }
        return array(
            'ship_min_days' => $min,
            'ship_max_days' => $max,
        );
    }

    protected function apply_shipping_days($productId, $options)
    {
        $days = $this->shipping_days_from_options($options);
        if (!$days) {
            return;
        }
        $this->ci->Product_model->save($days, (int) $productId);
    }

    protected function stock_from_options($options, $fallback = 0)
    {
        if (!array_key_exists('stock', $options) || $options['stock'] === '' || $options['stock'] === null) {
            return max(0, (int) $fallback);
        }
        return max(0, (int) $options['stock']);
    }

    protected function apply_stock($productId, $options)
    {
        if (!array_key_exists('stock', $options) || $options['stock'] === '' || $options['stock'] === null) {
            return;
        }
        $this->ci->Product_model->save(array(
            'stock' => max(0, (int) $options['stock']),
        ), (int) $productId);
    }

    protected function converted_compare_price($data, $rate, $forceSelling)
    {
        $compare = (float) (isset($data['compare_price']) ? $data['compare_price'] : 0);
        if ($compare <= 0 || $forceSelling) {
            return $compare;
        }
        return round($compare * $rate, 2);
    }

    protected function assign_csv_categories($productId, $countryId, $options)
    {
        $ids = $this->selected_category_ids($countryId, $options);
        if ($ids) {
            $this->ci->Ec_category_model->set_product_categories($productId, $ids);
            return;
        }

        $categoryName = isset($options['category']) ? trim((string) $options['category']) : '';
        $subName = isset($options['sub_category']) ? trim((string) $options['sub_category']) : '';
        if ($categoryName === '') {
            return;
        }
        $parentId = $this->ci->Ec_category_model->find_or_create($countryId, $categoryName, 0);
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

    protected function selected_category_ids($countryId, $options)
    {
        $ids = array();
        $categoryId = isset($options['category_id']) ? (int) $options['category_id'] : 0;
        $subcategoryId = isset($options['subcategory_id']) ? (int) $options['subcategory_id'] : 0;
        if ($categoryId > 0) {
            $cat = $this->ci->Ec_category_model->get($categoryId);
            if ($cat && (int) $cat->country_id === (int) $countryId) {
                $ids[] = $categoryId;
            }
        }
        if ($subcategoryId > 0) {
            $sub = $this->ci->Ec_category_model->get($subcategoryId);
            if ($sub && (int) $sub->country_id === (int) $countryId) {
                if (!$categoryId || !(int) $sub->parent_id || (int) $sub->parent_id === $categoryId) {
                    $ids[] = $subcategoryId;
                    $parentId = (int) $sub->parent_id;
                    if ($parentId && !in_array($parentId, $ids, true)) {
                        $ids[] = $parentId;
                    }
                }
            }
        }
        return array_values(array_unique($ids));
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

    protected function import_data_has_image($data)
    {
        if (!empty($data['image'])) {
            return true;
        }
        if (!empty($data['gallery']) && is_array($data['gallery'])) {
            foreach ($data['gallery'] as $path) {
                if (trim((string) $path) !== '') {
                    return true;
                }
            }
        }
        return false;
    }

    protected function import_data_has_details($data)
    {
        $details = isset($data['details']) ? trim(strip_tags((string) $data['details'])) : '';
        $description = isset($data['description']) ? trim(strip_tags((string) $data['description'])) : '';
        return $details !== '' || $description !== '';
    }

    protected function assert_required_media($productId, $options)
    {
        $product = $this->ci->Product_model->get((int) $productId);
        if (!$product) {
            throw new Exception('Product was not saved.');
        }
        if (!empty($options['require_images']) && trim((string) $product->image) === '') {
            throw new Exception('Product images are required.');
        }
        if (!empty($options['require_details'])) {
            $details = trim(strip_tags((string) $product->details));
            $description = trim(strip_tags((string) $product->description));
            if ($details === '' && $description === '') {
                throw new Exception('Product details are required.');
            }
        }
    }

    protected function apply_provided_images($data, $options)
    {
        if (!is_array($data)) {
            $data = array();
        }
        $files = array();
        if (!empty($options['image_files']) && is_array($options['image_files'])) {
            $files = $options['image_files'];
        }
        if (!$files) {
            return $data;
        }
        $saved = array();
        foreach ($files as $file) {
            $path = $this->ingest_local_image($file);
            if ($path !== '') {
                $saved[] = $path;
            }
        }
        if (!$saved) {
            return $data;
        }
        if (empty($data['image'])) {
            $data['image'] = $saved[0];
        }
        $gallery = (!empty($data['gallery']) && is_array($data['gallery'])) ? $data['gallery'] : array();
        foreach ($saved as $i => $path) {
            if ($i === 0 && isset($data['image']) && $data['image'] === $path) {
                continue;
            }
            if ($path !== '' && $path !== $data['image'] && !in_array($path, $gallery, true)) {
                $gallery[] = $path;
            }
        }
        $data['gallery'] = $gallery;
        return $data;
    }

    protected function ingest_local_image($file)
    {
        $file = trim((string) $file);
        if ($file === '' || !is_file($file) || filesize($file) < 500) {
            return '';
        }
        $bin = @file_get_contents($file);
        if ($bin === false || strlen($bin) < 500 || preg_match('/^\s*</', $bin)) {
            return '';
        }
        $dir = FCPATH . 'uploads/products/';
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        $base = 'imp_' . md5($file . filesize($file));
        $webpName = $base . '.webp';
        if (is_file($dir . $webpName)) {
            return 'uploads/products/' . $webpName;
        }
        $alreadyWebp = (substr($bin, 0, 4) === 'RIFF' && stripos(substr($bin, 0, 16), 'WEBP') !== false)
            || strtolower(pathinfo($file, PATHINFO_EXTENSION)) === 'webp';
        if ($alreadyWebp) {
            if (@file_put_contents($dir . $webpName, $bin) !== false) {
                return 'uploads/products/' . $webpName;
            }
            return '';
        }
        $ext = 'jpg';
        $head = substr($bin, 0, 8);
        if ($head === "\x89PNG\r\n\x1a\n" || strtolower(pathinfo($file, PATHINFO_EXTENSION)) === 'png') {
            $ext = 'png';
        } elseif (substr($bin, 0, 3) === 'GIF' || strtolower(pathinfo($file, PATHINFO_EXTENSION)) === 'gif') {
            $ext = 'gif';
        }
        $tmpName = $base . '.src.' . $ext;
        if (@file_put_contents($dir . $tmpName, $bin) === false) {
            return '';
        }
        $converted = function_exists('ec_convert_image_to_webp')
            ? ec_convert_image_to_webp($dir . $tmpName)
            : ($dir . $tmpName);
        $public = function_exists('ec_public_upload_path')
            ? ec_public_upload_path($converted)
            : ('uploads/products/' . basename($converted));
        if ($public !== '' && substr($public, -5) === '.webp') {
            return $public;
        }
        if (is_file($dir . $webpName)) {
            @unlink($dir . $tmpName);
            return 'uploads/products/' . $webpName;
        }
        return $public !== '' ? $public : ('uploads/products/' . basename($converted));
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

    protected function hydrate_import_images($data, $resolved)
    {
        if (!is_array($data)) {
            $data = array();
        }
        $urls = array();
        if (!empty($data['image_urls']) && is_array($data['image_urls'])) {
            $urls = $data['image_urls'];
        }
        $colorUrls = array();
        foreach (array('colors', 'variants', 'children') as $groupKey) {
            if (empty($data[$groupKey]) || !is_array($data[$groupKey])) {
                continue;
            }
            foreach ($data[$groupKey] as $item) {
                if (is_array($item) && !empty($item['image_url'])) {
                    $colorUrls[] = trim((string) $item['image_url']);
                }
            }
        }
        $all = array_values(array_unique(array_merge($urls, $colorUrls)));
        if (!$all) {
            return $data;
        }
        $className = isset($resolved['class']) ? $resolved['class'] : '';
        if ($className === '' || !$this->ci->Product_import_model->has_importer_class($className)) {
            return $data;
        }
        require_once $this->ci->Product_import_model->class_file($className);
        $importer = new $className();
        $pageUrl = isset($resolved['url']) ? $resolved['url'] : '';
        $savedMap = array();
        foreach ($all as $url) {
            $one = method_exists($importer, 'save_images')
                ? $importer->save_images(array($url), 1, $pageUrl)
                : array();
            if (!empty($one[0])) {
                $savedMap[$url] = $one[0];
            }
        }
        if (!$savedMap) {
            return $data;
        }
        $parentSaved = array();
        foreach ($urls as $url) {
            if (isset($savedMap[$url])) {
                $parentSaved[] = $savedMap[$url];
            }
        }
        if (!$parentSaved) {
            $parentSaved = array_values($savedMap);
        }
        if (empty($data['image'])) {
            $data['image'] = $parentSaved[0];
        }
        $gallery = (!empty($data['gallery']) && is_array($data['gallery'])) ? $data['gallery'] : array();
        foreach ($parentSaved as $i => $path) {
            if ($i === 0 && isset($data['image']) && $data['image'] === $path) {
                continue;
            }
            if ($path !== '' && $path !== $data['image'] && !in_array($path, $gallery, true)) {
                $gallery[] = $path;
            }
        }
        $data['gallery'] = $gallery;
        if (!empty($data['colors']) && is_array($data['colors'])) {
            foreach ($data['colors'] as $i => $color) {
                if (!is_array($color) || empty($color['image_url'])) {
                    continue;
                }
                $url = trim((string) $color['image_url']);
                if (isset($savedMap[$url])) {
                    $data['colors'][$i]['image'] = $savedMap[$url];
                }
            }
        }
        foreach (array('variants', 'children') as $groupKey) {
            if (empty($data[$groupKey]) || !is_array($data[$groupKey])) {
                continue;
            }
            foreach ($data[$groupKey] as $i => $item) {
                if (!is_array($item) || empty($item['image_url'])) {
                    continue;
                }
                $url = trim((string) $item['image_url']);
                if (isset($savedMap[$url])) {
                    $data[$groupKey][$i]['image'] = $savedMap[$url];
                }
            }
        }
        return $data;
    }

    protected function create_size_children($parentId, $data, $options)
    {
        $parentId = (int) $parentId;
        $variants = $this->variation_labels($data, $options);
        if ($parentId < 1 || !$variants) {
            return array();
        }

        $parent = $this->ci->Product_model->get($parentId);
        if (!$parent || !empty($parent->store_id)) {
            return array();
        }
        if (function_exists('ensure_product_parent_columns')) {
            ensure_product_parent_columns();
        }

        $parentSku = isset($parent->parent_sku) ? trim((string) $parent->parent_sku) : '';
        if ($parentSku === '') {
            $parentSku = trim((string) $parent->sku);
        }
        if ($parentSku === '') {
            $parentSku = $this->ci->Product_model->unique_sku('P' . (int) $parent->id);
            $this->ci->Product_model->save(array('sku' => $parentSku), (int) $parent->id);
            $parent->sku = $parentSku;
        }

        $this->ci->load->model('Ec_category_model');
        $categoryIds = $this->ci->Ec_category_model->ids_for_product($parent->id);
        $created = array();
        $hasDefault = false;
        if ($this->ci->db->field_exists('parent_sku', 'products')) {
            $this->ci->db
                ->group_start()
                    ->where('store_id IS NULL', null, false)
                    ->or_where('store_id', 0)
                ->group_end()
                ->where('parent_sku', $parentSku)
                ->where('is_default', 1);
            $hasDefault = $this->ci->db->count_all_results('products') > 0;
        }

        foreach ($variants as $variant) {
            $size = $variant['name'];
            $name = trim((string) $parent->name);
            if (!preg_match('/\s[-–]\s' . preg_quote($size, '/') . '$/u', $name)) {
                $name .= ' - ' . $size;
            }
            $existing = $this->find_catalog_child_by_name($parentSku, $name);
            $variantPrice = isset($variant['price']) ? (float) $variant['price'] : 0;
            $variantCost = isset($variant['cost_price']) ? (float) $variant['cost_price'] : $variantPrice;
            if ($variantCost <= 0) {
                $variantCost = $variantPrice;
            }
            if ($existing) {
                if ($variantCost > 0) {
                    $this->ci->Product_model->save(array(
                        'price' => $variantPrice > 0 ? $variantPrice : $variantCost,
                        'cost_price' => $variantCost,
                        'compare_price' => isset($variant['compare_price']) ? (float) $variant['compare_price'] : (isset($existing->compare_price) ? (float) $existing->compare_price : 0),
                    ), (int) $existing->id);
                }
                $created[] = (int) $existing->id;
                $this->maybe_auto_add_product((int) $existing->id, $options);
                continue;
            }

            $suffix = strtoupper(preg_replace('/[^A-Za-z0-9]+/', '-', $size));
            $suffix = trim($suffix, '-');
            if ($suffix === '') {
                $suffix = 'CHILD';
            }
            $sku = $this->ci->Product_model->unique_sku($parentSku . '-' . $suffix);
            $sortOrder = function_exists('product_next_child_sort') ? product_next_child_sort($parentSku) : count($created) + 1;
            $image = !empty($variant['image']) ? $this->local_image_path($variant['image']) : '';
            if ($image === '') {
                $image = isset($parent->image) ? $parent->image : '';
            }
            $payload = array(
                'name' => $name,
                'sku' => $sku,
                'parent_sku' => $parentSku,
                'is_default' => $hasDefault ? 0 : 1,
                'sort_order' => $sortOrder,
                'slug' => $this->ci->Product_model->unique_slug($name),
                'brand' => isset($parent->brand) ? $parent->brand : '',
                'made_by' => isset($parent->made_by) ? $parent->made_by : '',
                'price' => $variantPrice > 0 ? $variantPrice : (isset($parent->price) ? (float) $parent->price : 0),
                'compare_price' => isset($variant['compare_price']) && (float) $variant['compare_price'] > 0
                    ? (float) $variant['compare_price']
                    : (isset($parent->compare_price) ? (float) $parent->compare_price : 0),
                'cost_price' => $variantCost > 0 ? $variantCost : (isset($parent->cost_price) ? (float) $parent->cost_price : 0),
                'max_sale_price' => isset($parent->max_sale_price) ? (float) $parent->max_sale_price : 0,
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
                'status' => isset($parent->status) ? (int) $parent->status : 1,
                'auto_add_to_stores' => !empty($parent->auto_add_to_stores) ? 1 : 0,
                'image' => $image,
                'source_url' => isset($parent->source_url) ? $parent->source_url : '',
                'created_by' => !empty($parent->created_by) ? (int) $parent->created_by : 0,
            );
            $childId = $this->ci->Product_model->save($payload, 0);
            if ($childId < 1) {
                continue;
            }
            if (function_exists('product_sync_default_child')) {
                product_sync_default_child($childId);
            }
            $this->ci->Ec_category_model->set_product_categories($childId, $categoryIds);
            $this->maybe_auto_add_product($childId, $options);
            $created[] = $childId;
            $hasDefault = true;
        }

        return $created;
    }

    protected function variation_labels($data, $options)
    {
        $raw = array();
        foreach (array('colors', 'sizes', 'variants', 'children') as $key) {
            if (!empty($data[$key]) && is_array($data[$key])) {
                $raw = array_merge($raw, $data[$key]);
            }
            if (!empty($options[$key]) && is_array($options[$key])) {
                $raw = array_merge($raw, $options[$key]);
            }
        }
        $out = array();
        $seen = array();
        foreach ($raw as $item) {
            $name = '';
            $image = '';
            $price = 0.0;
            $cost = 0.0;
            $compare = 0.0;
            if (is_array($item)) {
                $name = trim(isset($item['name']) ? (string) $item['name'] : '');
                $image = isset($item['image']) ? trim((string) $item['image']) : '';
                $price = isset($item['price']) ? (float) $item['price'] : 0;
                $cost = isset($item['cost_price']) ? (float) $item['cost_price'] : $price;
                $compare = isset($item['compare_price']) ? (float) $item['compare_price'] : 0;
            } else {
                $name = trim((string) $item);
            }
            if ($name === '') {
                continue;
            }
            $key = strtoupper($name);
            if (isset($seen[$key])) {
                continue;
            }
            $seen[$key] = true;
            $out[] = array(
                'name' => $name,
                'image' => $image,
                'price' => $price,
                'cost_price' => $cost,
                'compare_price' => $compare,
            );
        }
        return $out;
    }

    protected function find_catalog_child_by_name($parentSku, $name)
    {
        $parentSku = trim((string) $parentSku);
        $name = trim((string) $name);
        if ($parentSku === '' || $name === '' || !$this->ci->db->field_exists('parent_sku', 'products')) {
            return null;
        }
        $this->ci->db
            ->group_start()
                ->where('store_id IS NULL', null, false)
                ->or_where('store_id', 0)
            ->group_end()
            ->where('parent_sku', $parentSku)
            ->where('name', $name);
        return $this->ci->db->get('products')->row();
    }

    public function details_from_url($url)
    {
        $parsed = $this->parse_url($url);
        $source = $this->ci->Product_import_model->ensure_source($parsed['host']);
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
