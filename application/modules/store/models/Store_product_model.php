<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Store_product_model extends CI_Model {

    public function __construct()
    {
        parent::__construct();
        if (!$this->db->table_exists('products')) {
            return;
        }
        if (!$this->db->field_exists('ship_min_days', 'products')) {
            $this->db->query('ALTER TABLE products ADD COLUMN ship_min_days INT(11) NOT NULL DEFAULT 0 AFTER stock');
        }
        if (!$this->db->field_exists('ship_max_days', 'products')) {
            $this->db->query('ALTER TABLE products ADD COLUMN ship_max_days INT(11) NOT NULL DEFAULT 0 AFTER ship_min_days');
        }
        if (!$this->db->field_exists('details', 'products')) {
            $this->db->query('ALTER TABLE products ADD COLUMN details MEDIUMTEXT NULL AFTER description');
        }
        if (!$this->db->field_exists('brand', 'products')) {
            $after = $this->db->field_exists('sku', 'products') ? ' AFTER sku' : '';
            $this->db->query("ALTER TABLE products ADD COLUMN brand VARCHAR(150) NOT NULL DEFAULT ''" . $after);
        }
        if (!$this->db->field_exists('made_by', 'products')) {
            $after = $this->db->field_exists('brand', 'products') ? ' AFTER brand' : ($this->db->field_exists('sku', 'products') ? ' AFTER sku' : '');
            $this->db->query("ALTER TABLE products ADD COLUMN made_by VARCHAR(150) NOT NULL DEFAULT ''" . $after);
        }
        if (!$this->db->field_exists('auto_add_to_stores', 'products')) {
            $this->db->query("ALTER TABLE products ADD COLUMN auto_add_to_stores TINYINT(1) NOT NULL DEFAULT 0 AFTER status");
        }
        if (function_exists('ensure_product_parent_columns')) {
            ensure_product_parent_columns();
        }
        if (function_exists('ensure_product_trending_columns')) {
            ensure_product_trending_columns();
        }
        if (function_exists('ensure_user_commission_schema')) {
            ensure_user_commission_schema();
        }
        if (function_exists('ensure_product_extra_amount_column')) {
            ensure_product_extra_amount_column();
        }
        if (function_exists('ensure_product_i18n_columns')) {
            ensure_product_i18n_columns();
        }
    }

    public function available_for_store($store, $filters = array())
    {
        $filters = $this->normalize_available_filters($filters);
        $withCategories = $this->db->table_exists('product_categories');
        $select = 'products.*, suppliers.name as supplier_name, users.commission as owner_commission, users.commission_percent as owner_commission_percent, users.first_name as owner_first, users.last_name as owner_last, COALESCE(product_country.name, supplier_country.name) as country_name, COALESCE(product_country.currency, supplier_country.currency) as country_currency';
        if ($withCategories) {
            $select .= ", GROUP_CONCAT(DISTINCT categories.name ORDER BY categories.parent_id, categories.name SEPARATOR ', ') as category_names";
        }
        $this->db->select($select, false);
        $this->apply_available_scope($store, $filters, true);
        if ($withCategories) {
            $this->db->group_by('products.id');
        }
        if (function_exists('storefront_order_by_sort')) {
            storefront_order_by_sort('products', 'desc');
        } else {
            $this->db->order_by('products.id', 'desc');
        }
        if (!empty($filters['limit'])) {
            $this->db->limit((int) $filters['limit'], isset($filters['offset']) ? (int) $filters['offset'] : 0);
        }
        return $this->db->get()->result();
    }

    public function count_available_for_store($store, $filters = array())
    {
        $filters = $this->normalize_available_filters($filters);
        $this->db->select('COUNT(DISTINCT products.id) as total', false);
        $this->apply_available_scope($store, $filters, false);
        $row = $this->db->get()->row();
        return $row ? (int) $row->total : 0;
    }

    protected function normalize_available_filters($filters)
    {
        if (!is_array($filters)) {
            $filters = array();
        }
        $filters['q'] = isset($filters['q']) ? trim((string) $filters['q']) : '';
        $filters['category_id'] = isset($filters['category_id']) ? (int) $filters['category_id'] : 0;
        $filters['subcategory_id'] = isset($filters['subcategory_id']) ? (int) $filters['subcategory_id'] : 0;
        $filters['trending'] = isset($filters['trending']) ? trim((string) $filters['trending']) : '';
        if ($filters['trending'] !== '1' && $filters['trending'] !== '0') {
            $filters['trending'] = '';
        }
        $filters['auto_add_to_stores'] = !empty($filters['auto_add_to_stores']) ? 1 : 0;
        if ($filters['subcategory_id'] > 0) {
            $filters['category_ids'] = array($filters['subcategory_id']);
        } elseif ($filters['category_id'] > 0) {
            $filters['category_ids'] = $this->category_ids_with_children($filters['category_id']);
        } else {
            $filters['category_ids'] = array();
        }
        return $filters;
    }

    protected function apply_available_scope($store, $filters, $withDisplayJoins = true)
    {
        $countryId = !empty($store->country_id) ? (int) $store->country_id : 0;
        $q = isset($filters['q']) ? $filters['q'] : '';
        $categoryIds = !empty($filters['category_ids']) && is_array($filters['category_ids']) ? $filters['category_ids'] : array();
        $hasCategories = $this->db->table_exists('product_categories') && $this->db->table_exists('categories');
        $joinCategories = $hasCategories && ($withDisplayJoins || $q !== '' || !empty($categoryIds));

        $this->db->from('products');
        if ($withDisplayJoins) {
            $this->db
                ->join('suppliers', 'suppliers.id = products.supplier_id', 'left')
                ->join('countries as product_country', 'product_country.id = products.country_id', 'left')
                ->join('countries as supplier_country', 'supplier_country.id = suppliers.country_id', 'left')
                ->join('users', 'users.UserID = products.created_by', 'left');
        } elseif ($countryId) {
            $this->db->join('suppliers', 'suppliers.id = products.supplier_id', 'left');
        }
        if ($joinCategories) {
            $this->db
                ->join('product_categories', 'product_categories.product_id = products.id', 'left')
                ->join('categories', 'categories.id = product_categories.category_id', 'left')
                ->join('categories as parent_cat', 'parent_cat.id = categories.parent_id', 'left');
        }

        $this->db
            ->where('products.status', 1)
            ->group_start()
                ->where('products.store_id IS NULL', null, false)
                ->or_where('products.store_id', 0)
            ->group_end();
        if (!empty($filters['auto_add_to_stores']) && $this->db->field_exists('auto_add_to_stores', 'products')) {
            $this->db->where('products.auto_add_to_stores', 1);
        }

        $storeId = !empty($store->id) ? (int) $store->id : 0;
        if ($storeId > 0) {
            $this->db->join(
                'products as store_copies',
                'store_copies.source_product_id = products.id AND store_copies.store_id = ' . $storeId,
                'left'
            );
            $this->db->where('store_copies.id IS NULL', null, false);
        }

        if ($countryId) {
            $this->db->group_start()
                ->where('products.country_id', $countryId)
                ->or_where('suppliers.country_id', $countryId)
                ->group_end();
        }

        if ($q !== '') {
            $this->db->group_start()
                ->like('products.name', $q)
                ->or_like('products.sku', $q)
                ->or_like('products.description', $q);
            if ($this->db->field_exists('brand', 'products')) {
                $this->db->or_like('products.brand', $q);
            }
            if ($joinCategories) {
                $this->db
                    ->or_like('categories.name', $q)
                    ->or_like('parent_cat.name', $q);
                if ($this->db->field_exists('local_name', 'categories')) {
                    $this->db
                        ->or_like('categories.local_name', $q)
                        ->or_like('parent_cat.local_name', $q);
                }
            }
            $this->db->group_end();
        }

        if (!empty($categoryIds) && $hasCategories) {
            $this->db->join('product_categories pc_filter', 'pc_filter.product_id = products.id', 'inner');
            $this->db->where_in('pc_filter.category_id', $categoryIds);
        }

        $this->where_parents_only('products');
    }

    protected function where_parents_only($table = 'products')
    {
        if (!$this->db->field_exists('parent_sku', 'products')) {
            return;
        }
        $col = ($table ? $table . '.' : '') . 'parent_sku';
        $this->db->group_start()
            ->where($col, '')
            ->or_where($col . ' IS NULL', null, false)
        ->group_end();
    }

    protected function category_ids_with_children($categoryId)
    {
        $ids = array((int) $categoryId);
        if ($categoryId < 1 || !$this->db->table_exists('categories')) {
            return $ids;
        }
        $children = $this->db->query('SELECT id FROM categories WHERE parent_id = ?', array((int) $categoryId))->result();
        foreach ($children as $child) {
            $ids[] = (int) $child->id;
        }
        return $ids;
    }

    public function catalog_item_for_store($store, $id)
    {
        foreach ($this->available_for_store($store) as $row) {
            if ((int) $row->id === (int) $id) {
                return $row;
            }
        }
        return null;
    }

    public function mine($storeId, $filters = array())
    {
        $filters = $this->normalize_available_filters($filters);
        $q = isset($filters['q']) ? $filters['q'] : '';
        $categoryIds = !empty($filters['category_ids']) && is_array($filters['category_ids']) ? $filters['category_ids'] : array();
        $hasCategories = $this->db->table_exists('product_categories');
        $trending = isset($filters['trending']) ? $filters['trending'] : '';

        $this->db->from('products');
        $this->db->where('products.store_id', (int) $storeId);
        if ($q !== '') {
            $this->db->group_start()
                ->like('products.name', $q)
                ->or_like('products.sku', $q)
                ->or_like('products.description', $q);
            if ($this->db->field_exists('brand', 'products')) {
                $this->db->or_like('products.brand', $q);
            }
            $this->db->group_end();
        }
        if (!empty($categoryIds) && $hasCategories) {
            $this->db->join('product_categories pc_filter', 'pc_filter.product_id = products.id', 'inner');
            $this->db->where_in('pc_filter.category_id', $categoryIds);
            $this->db->group_by('products.id');
        }
        if (($trending === '1' || $trending === '0') && $this->db->field_exists('is_trending', 'products')) {
            $this->db->where('products.is_trending', (int) $trending);
        }
        $this->where_parents_only('products');
        if (function_exists('storefront_order_by_sort')) {
            storefront_order_by_sort('products', 'desc');
        } else {
            $this->db->order_by('products.id', 'desc');
        }
        return $this->db
            ->get()
            ->result();
    }

    public function get_owned($storeId, $id)
    {
        return $this->db
            ->where('store_id', (int) $storeId)
            ->where('id', (int) $id)
            ->get('products')
            ->row();
    }

    public function copied_source_ids($storeId)
    {
        $rows = $this->db
            ->select('source_product_id, id')
            ->where('store_id', (int) $storeId)
            ->where('source_product_id IS NOT NULL', null, false)
            ->get('products')
            ->result();
        $map = array();
        foreach ($rows as $row) {
            $map[(int) $row->source_product_id] = (int) $row->id;
        }
        return $map;
    }

    public function find_copy($storeId, $sourceId)
    {
        return $this->db
            ->where('store_id', (int) $storeId)
            ->where('source_product_id', (int) $sourceId)
            ->get('products')
            ->row();
    }

    public function save($data, $id = 0)
    {
        if ($id) {
            $this->db->where('id', (int) $id)->update('products', $data);
            return (int) $id;
        }
        $this->db->insert('products', $data);
        return (int) $this->db->insert_id();
    }

    public function delete_owned($storeId, $id)
    {
        $this->db->where('product_id', (int) $id)->delete('product_images');
        $this->db->where('product_id', (int) $id)->delete('product_variations');
        $this->db->where('product_id', (int) $id)->delete('product_attributes');
        return $this->db->where('store_id', (int) $storeId)->where('id', (int) $id)->delete('products');
    }

    public function images($productId)
    {
        return $this->db
            ->where('product_id', (int) $productId)
            ->order_by('id', 'asc')
            ->get('product_images')
            ->result();
    }

    public function copy_options($fromId, $toId)
    {
        $attrs = $this->db
            ->where('product_id', (int) $fromId)
            ->order_by('sort_order', 'asc')
            ->get('product_attributes')
            ->result();
        foreach ($attrs as $attr) {
            $this->db->insert('product_attributes', array(
                'product_id' => (int) $toId,
                'name' => $attr->name,
                'values_text' => $attr->values_text,
                'sort_order' => $attr->sort_order,
            ));
        }
        $vars = $this->db->where('product_id', (int) $fromId)->get('product_variations')->result();
        foreach ($vars as $var) {
            $this->db->insert('product_variations', array(
                'product_id' => (int) $toId,
                'option_name' => $var->option_name,
                'option_value' => $var->option_value,
                'sku' => $var->sku,
                'price' => $var->price,
                'stock' => $var->stock,
                'combination_key' => isset($var->combination_key) ? $var->combination_key : '',
                'attributes_json' => isset($var->attributes_json) ? $var->attributes_json : null,
                'image' => $this->copy_file(isset($var->image) ? $var->image : ''),
            ));
        }
    }

    public function add_image($productId, $path)
    {
        $this->db->insert('product_images', array(
            'product_id' => (int) $productId,
            'image' => $path,
        ));
        return (int) $this->db->insert_id();
    }

    public function delete_image($storeId, $imageId)
    {
        $image = $this->db->where('id', (int) $imageId)->get('product_images')->row();
        if (!$image) {
            return false;
        }
        $product = $this->get_owned($storeId, $image->product_id);
        if (!$product) {
            return false;
        }
        $full = FCPATH . ltrim($image->image, '/');
        if (is_file($full)) {
            @unlink($full);
        }
        $this->db->where('id', (int) $imageId)->delete('product_images');
        return $image->product_id;
    }

    public function unique_slug($slug, $storeId, $ignoreId = 0)
    {
        $slug = function_exists('ec_ascii_slug')
            ? ec_ascii_slug($slug, 'product')
            : trim((string) $slug);
        $base = $slug !== '' ? $slug : 'product';
        $try = $base;
        $i = 2;
        while (true) {
            $this->db->where('store_id', (int) $storeId)->where('slug', $try);
            if ($ignoreId) {
                $this->db->where('id !=', (int) $ignoreId);
            }
            if ($this->db->count_all_results('products') === 0) {
                return $try;
            }
            $try = $base . '-' . $i;
            $i++;
        }
    }

    public function copy_file($path)
    {
        $path = ltrim((string) $path, '/');
        if ($path === '' || !is_file(FCPATH . $path)) {
            return $path;
        }
        $dir = FCPATH . 'uploads/products/';
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        $ext = pathinfo($path, PATHINFO_EXTENSION);
        $name = 'store_' . uniqid() . ($ext ? '.' . $ext : '');
        if (!@copy(FCPATH . $path, $dir . $name)) {
            return $path;
        }
        return 'uploads/products/' . $name;
    }

    public function catalog_source($id)
    {
        return $this->db
            ->select('products.*, users.commission as owner_commission, users.commission_percent as owner_commission_percent', false)
            ->from('products')
            ->join('users', 'users.UserID = products.created_by', 'left')
            ->where('products.id', (int) $id)
            ->group_start()
                ->where('products.store_id IS NULL', null, false)
                ->or_where('products.store_id', 0)
            ->group_end()
            ->get()
            ->row();
    }

    public function auto_add_stores_for_product($source)
    {
        ensure_store_pricing_columns();
        if (!$source) {
            return array();
        }
        $countryId = (int) (isset($source->country_id) ? $source->country_id : 0);
        $this->db
            ->from('stores')
            ->where('status', 1)
            ->where('auto_add_products', 1);
        if ($countryId > 0) {
            $this->db->where('country_id', $countryId);
        }
        return $this->db->get()->result();
    }

    public function copy_from_catalog($store, $source)
    {
        if (!$store || !$source) {
            return 0;
        }
        $existing = $this->find_copy($store->id, $source->id);
        if ($existing) {
            $this->refresh_copy_from_catalog($store, $source, $existing);
            return (int) $existing->id;
        }

        $this->load->model('Ec_category_model');
        $wholesale = product_wholesale_price($source, $store->id);
        $plus = store_price_plus_amount($store);
        $price = $this->listing_price_from_source($store, $source, $wholesale, $plus);

        $slug = $this->unique_slug($source->slug ?: $source->name, $store->id);
        $sku = trim((string) $source->sku);
        if ($sku !== '') {
            $sku .= '-S' . (int) $store->id;
        }
        $parentSku = isset($source->parent_sku) ? trim((string) $source->parent_sku) : '';
        if ($parentSku !== '') {
            $parentSku .= '-S' . (int) $store->id;
        }

        $copyId = $this->save(array(
            'store_id' => (int) $store->id,
            'source_product_id' => (int) $source->id,
            'supplier_id' => $source->supplier_id,
            'country_id' => $source->country_id ?: $store->country_id,
            'name' => $source->name,
            'sku' => $sku,
            'parent_sku' => $parentSku,
            'is_default' => !empty($source->is_default) ? 1 : 0,
            'sort_order' => isset($source->sort_order) ? (int) $source->sort_order : 0,
            'options_title' => isset($source->options_title) ? trim((string) $source->options_title) : '',
            'brand' => isset($source->brand) ? $source->brand : '',
            'made_by' => isset($source->made_by) ? $source->made_by : '',
            'slug' => $slug,
            'price' => $price,
            'max_sale_price' => $source->max_sale_price,
            'compare_price' => $source->compare_price,
            'cost_price' => $wholesale,
            'extra_amount' => function_exists('product_extra_amount') ? product_extra_amount($source) : 0,
            'stock' => $source->stock,
            'description' => $source->description,
            'short_details' => isset($source->short_details) ? $source->short_details : '',
            'details' => isset($source->details) ? $source->details : '',
            'seo_title' => isset($source->seo_title) ? $source->seo_title : '',
            'seo_description' => isset($source->seo_description) ? $source->seo_description : '',
            'seo_keywords' => isset($source->seo_keywords) ? $source->seo_keywords : '',
            'name_en' => isset($source->name_en) ? $source->name_en : '',
            'short_details_en' => isset($source->short_details_en) ? $source->short_details_en : '',
            'details_en' => isset($source->details_en) ? $source->details_en : '',
            'seo_title_en' => isset($source->seo_title_en) ? $source->seo_title_en : '',
            'seo_description_en' => isset($source->seo_description_en) ? $source->seo_description_en : '',
            'seo_keywords_en' => isset($source->seo_keywords_en) ? $source->seo_keywords_en : '',
            'image' => isset($source->image) ? $source->image : '',
            'ship_min_days' => isset($source->ship_min_days) ? (int) $source->ship_min_days : 0,
            'ship_max_days' => isset($source->ship_max_days) ? (int) $source->ship_max_days : 0,
            'status' => 1,
            'is_trending' => !empty($source->is_trending) ? 1 : 0,
            'trending_order' => isset($source->trending_order) ? max(0, (int) $source->trending_order) : 0,
            'created_by' => $source->created_by,
        ));

        foreach ($this->images($source->id) as $image) {
            if (!empty($image->image)) {
                $this->add_image($copyId, $image->image);
            }
        }
        $this->copy_options($source->id, $copyId);
        $this->sync_copy_categories($source->id, $copyId);
        $this->load->model('Store_channel_model');
        $this->Store_channel_model->queue_sync_product($store, $copyId);
        return $copyId;
    }

    public function store_parent_sku($source, $storeId)
    {
        $parentSku = isset($source->parent_sku) ? trim((string) $source->parent_sku) : '';
        if ($parentSku === '') {
            return '';
        }
        return $parentSku . '-S' . (int) $storeId;
    }

    public function catalog_family_ids($productId)
    {
        $source = $this->catalog_source($productId);
        if (!$source) {
            return array();
        }
        $ids = array((int) $source->id => (int) $source->id);
        $parentSku = isset($source->parent_sku) ? trim((string) $source->parent_sku) : '';
        $sku = isset($source->sku) ? trim((string) $source->sku) : '';
        $groupSku = $parentSku !== '' ? $parentSku : $sku;
        if ($groupSku === '') {
            return array_values($ids);
        }
        $this->db->select('id')->from('products');
        $this->db->group_start()
            ->where('store_id IS NULL', null, false)
            ->or_where('store_id', 0)
        ->group_end();
        $this->db->group_start()
            ->where('sku', $groupSku)
            ->or_where('parent_sku', $groupSku)
        ->group_end();
        foreach ($this->db->get()->result() as $row) {
            $ids[(int) $row->id] = (int) $row->id;
        }
        return array_values($ids);
    }

    public function stores_with_catalog_ids($catalogIds)
    {
        $storeIds = array();
        if (!$catalogIds) {
            return $storeIds;
        }
        $rows = $this->db->select('store_id')
            ->from('products')
            ->where('store_id >', 0)
            ->where_in('source_product_id', $catalogIds)
            ->group_by('store_id')
            ->get()
            ->result();
        foreach ($rows as $row) {
            $storeIds[(int) $row->store_id] = (int) $row->store_id;
        }
        return array_values($storeIds);
    }

    public function refresh_copy_from_catalog($store, $source, $existing)
    {
        if (!$store || !$source || !$existing) {
            return false;
        }
        $payload = array(
            'source_product_id' => (int) $source->id,
            'name' => $source->name,
            'parent_sku' => $this->store_parent_sku($source, $store->id),
            'is_default' => !empty($source->is_default) ? 1 : 0,
            'sort_order' => isset($source->sort_order) ? (int) $source->sort_order : 0,
            'options_title' => isset($source->options_title) ? trim((string) $source->options_title) : '',
            'brand' => isset($source->brand) ? $source->brand : '',
            'made_by' => isset($source->made_by) ? $source->made_by : '',
            'stock' => isset($source->stock) ? (int) $source->stock : 0,
            'description' => isset($source->description) ? $source->description : '',
            'short_details' => isset($source->short_details) ? $source->short_details : '',
            'details' => isset($source->details) ? $source->details : '',
            'name_en' => isset($source->name_en) ? $source->name_en : '',
            'short_details_en' => isset($source->short_details_en) ? $source->short_details_en : '',
            'details_en' => isset($source->details_en) ? $source->details_en : '',
            'seo_title_en' => isset($source->seo_title_en) ? $source->seo_title_en : '',
            'seo_description_en' => isset($source->seo_description_en) ? $source->seo_description_en : '',
            'seo_keywords_en' => isset($source->seo_keywords_en) ? $source->seo_keywords_en : '',
            'max_sale_price' => isset($source->max_sale_price) ? $source->max_sale_price : 0,
            'compare_price' => isset($source->compare_price) ? $source->compare_price : 0,
            'ship_min_days' => isset($source->ship_min_days) ? (int) $source->ship_min_days : 0,
            'ship_max_days' => isset($source->ship_max_days) ? (int) $source->ship_max_days : 0,
            'supplier_id' => $source->supplier_id,
            'country_id' => $source->country_id ?: $store->country_id,
        );
        if (!empty($source->image)) {
            $payload['image'] = $source->image;
        }
        $this->save($payload, (int) $existing->id);
        $this->sync_copy_categories($source->id, $existing->id);
        $this->sync_copy_pricing($store, $source, $this->find_copy($store->id, $source->id) ?: $existing);
        $have = array();
        foreach ($this->images($existing->id) as $row) {
            if (!empty($row->image)) {
                $have[$row->image] = true;
            }
        }
        foreach ($this->images($source->id) as $image) {
            if (!empty($image->image) && empty($have[$image->image])) {
                $this->add_image($existing->id, $image->image);
            }
        }
        $this->load->model('Store_channel_model');
        $this->Store_channel_model->queue_sync_product($store, (int) $existing->id);
        return true;
    }

    public function sync_family_to_stores($productId)
    {
        $CI =& get_instance();
        if (!isset($CI->Product_model)) {
            $CI->load->model('admin/Product_model');
        }
        if (isset($CI->Product_model) && method_exists($CI->Product_model, 'recreate_store_copies')) {
            return (int) $CI->Product_model->recreate_store_copies($productId);
        }
        $source = $this->catalog_source($productId);
        if (!$source) {
            return 0;
        }
        $familyIds = $this->catalog_family_ids($productId);
        $storeIds = $this->stores_with_catalog_ids($familyIds);
        foreach ($this->auto_add_stores_for_product($source) as $store) {
            $storeIds[] = (int) $store->id;
        }
        $storeIds = array_values(array_unique(array_filter($storeIds)));
        $added = 0;
        foreach ($storeIds as $storeId) {
            $store = $this->db->where('id', (int) $storeId)->where('status', 1)->get('stores')->row();
            if (!$store) {
                continue;
            }
            foreach ($familyIds as $catalogId) {
                $member = $this->catalog_source($catalogId);
                if (!$member) {
                    continue;
                }
                $before = $this->find_copy($store->id, $member->id);
                $copyId = $this->copy_from_catalog($store, $member);
                if ($copyId && !$before) {
                    $added++;
                }
            }
        }
        return $added;
    }

    public function sync_copy_pricing($store, $source, $existing)
    {
        if (!$store || !$source || !$existing) {
            return false;
        }
        $wholesale = product_wholesale_price($source, $store->id);
        $plus = store_price_plus_amount($store);
        $price = $this->listing_price_from_source($store, $source, $wholesale, $plus);
        $payload = array(
            'cost_price' => round($wholesale, 2),
            'price' => round($price, 2),
            'extra_amount' => function_exists('product_extra_amount') ? product_extra_amount($source) : 0,
        );
        $sameCost = abs((float) $existing->cost_price - (float) $payload['cost_price']) < 0.001;
        $samePrice = abs((float) $existing->price - (float) $payload['price']) < 0.001;
        $sameExtra = !isset($existing->extra_amount) || abs((float) $existing->extra_amount - (float) $payload['extra_amount']) < 0.001;
        if ($sameCost && $samePrice && $sameExtra) {
            return false;
        }
        $this->db->where('id', (int) $existing->id)->update('products', $payload);
        return true;
    }

    public function listing_price_from_source($store, $source, $wholesale = null, $plus = null)
    {
        if (function_exists('product_listed_price_from_source')) {
            return product_listed_price_from_source($store, $source, $wholesale, $plus);
        }
        $wholesale = $wholesale === null ? product_wholesale_price($source, $store->id) : (float) $wholesale;
        $plus = $plus === null ? store_price_plus_amount($store) : (float) $plus;
        $catalogSell = isset($source->price) ? (float) $source->price : 0.0;
        $catalogBase = function_exists('product_base_price') ? product_base_price($source) : $catalogSell;
        $price = round($wholesale + $plus + ($catalogSell - $catalogBase), 2);
        if ($price < $wholesale) {
            $price = $wholesale;
        }
        return $price > 0 ? $price : round($wholesale + $plus, 2);
    }

    public function restore_inherited_child_prices($storeId = 0)
    {
        return 0;
    }

    public function sync_copy_categories($sourceId, $copyId)
    {
        $this->load->model('Ec_category_model');
        $ids = $this->Ec_category_model->ids_for_product($sourceId);
        $this->Ec_category_model->set_product_categories($copyId, $ids);
    }

    public function auto_add_catalog_to_stores($productId)
    {
        $source = $this->catalog_source($productId);
        if (!$source) {
            return 0;
        }
        $added = 0;
        foreach ($this->auto_add_stores_for_product($source) as $store) {
            $before = $this->find_copy($store->id, $source->id);
            $copyId = $this->copy_from_catalog($store, $source);
            if ($copyId && !$before) {
                $added++;
            }
        }
        return $added;
    }

    public function country_stores_for_product($source)
    {
        ensure_store_pricing_columns();
        if (!$source) {
            return array();
        }
        $countryId = (int) (isset($source->country_id) ? $source->country_id : 0);
        $this->db->from('stores')->where('status', 1);
        if ($countryId > 0) {
            $this->db->where('country_id', $countryId);
        }
        return $this->db->get()->result();
    }

    public function add_catalog_to_all_country_stores($productId)
    {
        $source = $this->catalog_source($productId);
        if (!$source) {
            return 0;
        }
        $added = 0;
        foreach ($this->country_stores_for_product($source) as $store) {
            $before = $this->find_copy($store->id, $source->id);
            $copyId = $this->copy_from_catalog($store, $source);
            if ($copyId && !$before) {
                $added++;
            }
        }
        return $added;
    }

    public function auto_add_available_to_store($store)
    {
        if (!$store) {
            return 0;
        }
        $added = 0;
        foreach ($this->available_for_store($store, array('auto_add_to_stores' => 1)) as $source) {
            $before = $this->find_copy($store->id, $source->id);
            $copyId = $this->copy_from_catalog($store, $source);
            if ($copyId && !$before) {
                $added++;
            }
        }
        return $added;
    }
}
