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
        $options['existing_mode'] = 'return';
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
        );
    }

    protected function import_listing_if_needed($url, $countryId, $userId, $options)
    {
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
                $this->ci->Product_import_model->clear_failed($resolved['url'], $userId);
                return array(
                    'status' => 'existing',
                    'existing' => true,
                    'product_id' => (int) $existing->id,
                    'auto_added' => $this->maybe_auto_add_product((int) $existing->id, $options),
                );
            }
        }

        $reuse = !empty($options['reuse_source']) ? $this->find_existing_product($resolved, $countryId) : null;
        if ($reuse) {
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
            'supplier_id' => (int) $resolved['source']->supplier_id,
            'country_id' => $countryId,
            'name' => $name,
            'sku' => $this->ci->Product_model->unique_sku($sku !== '' ? $sku : strtoupper(substr(md5($resolved['canonical']), 0, 10))),
            'brand' => isset($data['brand']) ? trim((string) $data['brand']) : '',
            'slug' => $this->ci->Product_model->unique_slug(url_title($name, '-', true)),
            'price' => $catalogPrice,
            'compare_price' => $this->converted_compare_price($data, $rate, $forceSelling),
            'cost_price' => $cost,
            'max_sale_price' => isset($options['max_sale_price']) ? (float) $options['max_sale_price'] : 0,
            'stock' => $this->stock_from_options($options, (int) (isset($data['stock']) ? $data['stock'] : 0)),
            'description' => isset($data['description']) ? $data['description'] : '',
            'details' => isset($data['details']) ? $data['details'] : '',
            'seo_title' => $csvName !== '' ? $name : (isset($data['seo_title']) ? $data['seo_title'] : $name),
            'seo_description' => isset($data['seo_description']) ? $data['seo_description'] : '',
            'seo_keywords' => '',
            'image' => $this->local_image_path(isset($data['image']) ? $data['image'] : ''),
            'source_url' => $resolved['canonical'],
            'status' => 0,
            'auto_add_to_stores' => !empty($options['auto_add_to_stores']) ? 1 : 0,
            'created_by' => (int) $userId,
        );
        $shipping = $this->shipping_days_from_options($options);
        if ($shipping) {
            $payload = array_merge($payload, $shipping);
        }

        $productId = $this->ci->Product_model->save($payload, 0);
        $this->ci->Product_model->replace_sources($productId, array(
            array(
                'source_url' => $resolved['url'],
                'source_price' => $fetched > 0 ? $fetched : $cost,
                'label' => $resolved['host'],
            ),
        ));
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
        $this->sync_images_to_store_copies($productId);
        $this->ci->Product_import_model->clear_failed($resolved['url'], $userId);

        return array(
            'status' => 'imported',
            'existing' => false,
            'product_id' => $productId,
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
