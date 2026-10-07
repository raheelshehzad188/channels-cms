<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Product_model extends CI_Model {

    protected $table = 'products';

    public function __construct()
    {
        parent::__construct();
        $this->ensure_shipping_columns();
        $this->ensure_details_column();
        $this->ensure_short_details_column();
        $this->ensure_brand_column();
        $this->ensure_made_by_column();
        $this->ensure_auto_add_column();
        $this->ensure_creatives_table();
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
        if (function_exists('ensure_product_offer_columns')) {
            ensure_product_offer_columns();
        }
        if (function_exists('ensure_offer_campaign_tables')) {
            ensure_offer_campaign_tables();
        }
    }

    public function ensure_shipping_columns()
    {
        if (!$this->db->table_exists($this->table)) {
            return;
        }
        if (!$this->db->field_exists('ship_min_days', $this->table)) {
            $this->db->query('ALTER TABLE products ADD COLUMN ship_min_days INT(11) NOT NULL DEFAULT 0 AFTER stock');
        }
        if (!$this->db->field_exists('ship_max_days', $this->table)) {
            $this->db->query('ALTER TABLE products ADD COLUMN ship_max_days INT(11) NOT NULL DEFAULT 0 AFTER ship_min_days');
        }
    }

    public function ensure_details_column()
    {
        if (!$this->db->table_exists($this->table)) {
            return;
        }
        if (!$this->db->field_exists('details', $this->table)) {
            $this->db->query('ALTER TABLE products ADD COLUMN details MEDIUMTEXT NULL AFTER description');
        }
    }

    public function ensure_short_details_column()
    {
        if (!$this->db->table_exists($this->table)) {
            return;
        }
        if (!$this->db->field_exists('short_details', $this->table)) {
            $this->db->query('ALTER TABLE products ADD COLUMN short_details MEDIUMTEXT NULL AFTER description');
        }
    }

    public function ensure_brand_column()
    {
        if (!$this->db->table_exists($this->table)) {
            return;
        }
        if (!$this->db->field_exists('brand', $this->table)) {
            $after = $this->db->field_exists('sku', $this->table) ? ' AFTER sku' : '';
            $this->db->query('ALTER TABLE products ADD COLUMN brand VARCHAR(150) NOT NULL DEFAULT \'\'' . $after);
        }
    }

    public function ensure_made_by_column()
    {
        if (!$this->db->table_exists($this->table)) {
            return;
        }
        if (!$this->db->field_exists('made_by', $this->table)) {
            $after = $this->db->field_exists('brand', $this->table) ? ' AFTER brand' : ($this->db->field_exists('sku', $this->table) ? ' AFTER sku' : '');
            $this->db->query("ALTER TABLE products ADD COLUMN made_by VARCHAR(150) NOT NULL DEFAULT ''" . $after);
        }
    }

    public function ensure_auto_add_column()
    {
        if (!$this->db->table_exists($this->table)) {
            return;
        }
        if (!$this->db->field_exists('auto_add_to_stores', $this->table)) {
            $this->db->query("ALTER TABLE products ADD COLUMN auto_add_to_stores TINYINT(1) NOT NULL DEFAULT 0 AFTER status");
        }
    }

    public function all($ownerId = 0, $filters = array())
    {
        $ownerSelect = "users.uname as created_by_uname, TRIM(CONCAT(COALESCE(users.first_name, ''), ' ', COALESCE(users.last_name, ''))) as created_by_name";
        if ($this->db->table_exists('product_categories')) {
            $this->db->select("products.*, suppliers.name as supplier_name, COALESCE(product_country.name, supplier_country.name) as country_name, COALESCE(product_country.currency, supplier_country.currency) as country_currency, users.commission as owner_commission, users.commission_percent as owner_commission_percent, {$ownerSelect}, GROUP_CONCAT(DISTINCT categories.name ORDER BY categories.parent_id, categories.name SEPARATOR ', ') as category_names", false);
        } else {
            $this->db->select("products.*, suppliers.name as supplier_name, COALESCE(product_country.name, supplier_country.name) as country_name, COALESCE(product_country.currency, supplier_country.currency) as country_currency, users.commission as owner_commission, users.commission_percent as owner_commission_percent, {$ownerSelect}", false);
        }
        $this->apply_listing_scope($ownerId, $filters, true);
        if ($this->db->table_exists('product_categories')) {
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

    public function count_filtered($ownerId = 0, $filters = array())
    {
        $this->db->select('COUNT(DISTINCT products.id) as total', false);
        $this->apply_listing_scope($ownerId, $filters, false);
        $row = $this->db->get()->row();
        return $row ? (int) $row->total : 0;
    }

    protected function apply_listing_scope($ownerId, $filters, $withDisplayJoins = true)
    {
        $this->db->from($this->table);
        if ($withDisplayJoins) {
            $this->db
                ->join('suppliers', 'suppliers.id = products.supplier_id', 'left')
                ->join('countries as product_country', 'product_country.id = products.country_id', 'left')
                ->join('countries as supplier_country', 'supplier_country.id = suppliers.country_id', 'left')
                ->join('users', 'users.UserID = products.created_by', 'left');
            if ($this->db->table_exists('product_categories')) {
                $this->db
                    ->join('product_categories', 'product_categories.product_id = products.id', 'left')
                    ->join('categories', 'categories.id = product_categories.category_id', 'left');
            }
        }
        $this->db
            ->group_start()
                ->where('products.store_id IS NULL', null, false)
                ->or_where('products.store_id', 0)
            ->group_end();
        if ($ownerId) {
            $this->db->where('products.created_by', (int) $ownerId);
        }
        if (!empty($filters['country_id'])) {
            $this->db->where('products.country_id', (int) $filters['country_id']);
        }
        $q = isset($filters['q']) ? trim((string) $filters['q']) : '';
        if ($q !== '') {
            $this->db->group_start()
                ->like('products.name', $q)
                ->or_like('products.sku', $q)
                ->or_like('products.description', $q)
                ->or_like('products.source_url', $q)
                ->group_end();
        }
        if (!empty($filters['category_id']) && $this->db->table_exists('product_categories')) {
            $categoryIds = $this->category_ids_with_children((int) $filters['category_id']);
            $this->db->join('product_categories pc_filter', 'pc_filter.product_id = products.id', 'inner');
            $this->db->where_in('pc_filter.category_id', $categoryIds);
        }
        $trending = isset($filters['trending']) ? trim((string) $filters['trending']) : '';
        if (($trending === '1' || $trending === '0') && $this->db->field_exists('is_trending', 'products')) {
            $this->db->where('products.is_trending', (int) $trending);
        }
    }

    protected function category_ids_with_children($categoryId)
    {
        $ids = array((int) $categoryId);
        if ($categoryId < 1 || !$this->db->table_exists('categories')) {
            return $ids;
        }
        $children = $this->db->select('id')->where('parent_id', (int) $categoryId)->get('categories')->result();
        foreach ($children as $child) {
            $ids[] = (int) $child->id;
        }
        return $ids;
    }

    public function find_catalog_by_name($name, $countryId = 0)
    {
        $name = trim((string) $name);
        if ($name === '') {
            return null;
        }
        $this->db
            ->from($this->table)
            ->group_start()
                ->where('store_id IS NULL', null, false)
                ->or_where('store_id', 0)
            ->group_end()
            ->where('name', $name);
        if ($countryId) {
            $this->db->where('country_id', (int) $countryId);
        }
        return $this->db->limit(1)->get()->row();
    }

    public function find_by_source_url($url, $countryId = 0)
    {
        $url = trim((string) $url);
        if ($url === '') {
            return null;
        }

        $this->db
            ->from($this->table)
            ->group_start()
                ->where('store_id IS NULL', null, false)
                ->or_where('store_id', 0)
            ->group_end()
            ->where('source_url', $url);
        if ($countryId) {
            $this->db->where('country_id', (int) $countryId);
        }
        $row = $this->db->get()->row();
        if ($row) {
            return $row;
        }

        $this->ensure_sources_table();
        $this->db
            ->select('products.*')
            ->from($this->table)
            ->join('product_sources', 'product_sources.product_id = products.id', 'inner')
            ->group_start()
                ->where('products.store_id IS NULL', null, false)
                ->or_where('products.store_id', 0)
            ->group_end()
            ->where('product_sources.source_url', $url);
        if ($countryId) {
            $this->db->where('products.country_id', (int) $countryId);
        }
        return $this->db->limit(1)->get()->row();
    }

    public function unique_slug($slug, $ignoreId = 0)
    {
        $slug = function_exists('ec_ascii_slug')
            ? ec_ascii_slug($slug, 'product')
            : trim((string) $slug);
        if ($slug === '') {
            $slug = 'product';
        }
        $base = $slug;
        $i = 1;
        while ($this->slug_taken($slug, $ignoreId)) {
            $slug = $base . '-' . $i;
            $i++;
        }
        return $slug;
    }

    public function unique_sku($sku, $ignoreId = 0)
    {
        $sku = trim((string) $sku);
        if ($sku === '') {
            $sku = strtoupper(substr(md5(uniqid('', true)), 0, 10));
        }
        $base = $sku;
        $i = 1;
        while ($this->sku_taken($sku, $ignoreId)) {
            $sku = $base . '-' . $i;
            $i++;
        }
        return $sku;
    }

    protected function slug_taken($slug, $ignoreId = 0)
    {
        $this->db
            ->group_start()
                ->where('store_id IS NULL', null, false)
                ->or_where('store_id', 0)
            ->group_end()
            ->where('slug', $slug);
        if ($ignoreId) {
            $this->db->where('id !=', (int) $ignoreId);
        }
        return $this->db->count_all_results($this->table) > 0;
    }

    protected function sku_taken($sku, $ignoreId = 0)
    {
        $this->db
            ->group_start()
                ->where('store_id IS NULL', null, false)
                ->or_where('store_id', 0)
            ->group_end()
            ->where('sku', $sku);
        if ($ignoreId) {
            $this->db->where('id !=', (int) $ignoreId);
        }
        return $this->db->count_all_results($this->table) > 0;
    }

    public function all_active($limit = 0)
    {
        $this->db
            ->select('products.*, suppliers.name as supplier_name, COALESCE(product_country.name, supplier_country.name) as country_name, COALESCE(product_country.currency, supplier_country.currency) as country_currency, users.commission as owner_commission, users.commission_percent as owner_commission_percent', false)
            ->from($this->table)
            ->join('suppliers', 'suppliers.id = products.supplier_id', 'left')
            ->join('countries as product_country', 'product_country.id = products.country_id', 'left')
            ->join('countries as supplier_country', 'supplier_country.id = suppliers.country_id', 'left')
            ->join('users', 'users.UserID = products.created_by', 'left')
            ->where('products.status', 1)
            ->group_start()
                ->where('products.store_id IS NULL', null, false)
                ->or_where('products.store_id', 0)
            ->group_end();
        if (function_exists('storefront_order_by_sort')) {
            storefront_order_by_sort('products', 'desc');
        } else {
            $this->db->order_by('products.id', 'desc');
        }
        if ($limit) {
            $this->db->limit((int) $limit);
        }
        return $this->db->get()->result();
    }

    public function get($id)
    {
        return $this->db
            ->select('products.*, suppliers.name as supplier_name, COALESCE(product_country.name, supplier_country.name) as country_name, COALESCE(product_country.currency, supplier_country.currency) as country_currency, users.commission as owner_commission, users.commission_percent as owner_commission_percent', false)
            ->from($this->table)
            ->join('suppliers', 'suppliers.id = products.supplier_id', 'left')
            ->join('countries as product_country', 'product_country.id = products.country_id', 'left')
            ->join('countries as supplier_country', 'supplier_country.id = suppliers.country_id', 'left')
            ->join('users', 'users.UserID = products.created_by', 'left')
            ->where('products.id', (int) $id)
            ->get()
            ->row();
    }

    public function store_copies($productId)
    {
        return $this->db
            ->select('products.id, products.store_id, stores.name as store_name')
            ->from($this->table)
            ->join('stores', 'stores.id = products.store_id', 'left')
            ->where('products.source_product_id', (int) $productId)
            ->where('products.store_id IS NOT NULL', null, false)
            ->where('products.store_id !=', 0)
            ->order_by('stores.name', 'asc')
            ->get()
            ->result();
    }

    public function store_copy_count($productId)
    {
        return (int) $this->db
            ->where('source_product_id', (int) $productId)
            ->where('store_id IS NOT NULL', null, false)
            ->where('store_id !=', 0)
            ->count_all_results($this->table);
    }

    public function is_picked_by_store($productId)
    {
        return $this->store_copy_count($productId) > 0;
    }

    public function sync_max_sale_price($sourceProductId, $maxSalePrice)
    {
        $this->sync_copy_fields($sourceProductId, array('max_sale_price' => (float) $maxSalePrice));
    }

    public function sync_copy_fields($sourceProductId, $fields)
    {
        $fields = $this->filter_product_data($fields);
        if (!$fields) {
            return;
        }
        $this->db
            ->where('source_product_id', (int) $sourceProductId)
            ->where('store_id IS NOT NULL', null, false)
            ->where('store_id !=', 0)
            ->update($this->table, $fields);
    }

    /**
     * Push catalog content to every store copy. Store price, SEO, shipping and status stay local.
     */
    public function sync_catalog_to_copies($sourceProductId, $payload)
    {
        if (!is_array($payload)) {
            return;
        }
        $keys = array(
            'name', 'description', 'short_details', 'details', 'brand', 'made_by',
            'stock', 'max_sale_price', 'sort_order', 'is_default', 'options_title',
        );
        $sync = array();
        foreach ($keys as $key) {
            if (array_key_exists($key, $payload)) {
                $sync[$key] = $payload[$key];
            }
        }
        if (!empty($payload['image'])) {
            $sync['image'] = $payload['image'];
        }
        $this->sync_copy_fields($sourceProductId, $sync);
        if (array_key_exists('is_default', $sync) && function_exists('product_sync_default_child')) {
            foreach ($this->store_copies($sourceProductId) as $copy) {
                product_sync_default_child((int) $copy->id);
            }
        }
        $this->mirror_gallery_to_copies($sourceProductId);
    }

    public function mirror_gallery_to_copies($sourceProductId)
    {
        $copies = $this->store_copies($sourceProductId);
        if (!$copies) {
            return;
        }
        $images = $this->images($sourceProductId);
        $paths = array();
        foreach ($images as $image) {
            if (!empty($image->image)) {
                $paths[] = $image->image;
            }
        }
        if (!$paths) {
            return;
        }
        foreach ($copies as $copy) {
            $existing = $this->images($copy->id);
            $have = array();
            foreach ($existing as $row) {
                if (!empty($row->image)) {
                    $have[$row->image] = true;
                }
            }
            foreach ($paths as $path) {
                if (empty($have[$path])) {
                    $this->add_image($copy->id, $path);
                }
            }
        }
    }

    public function catalog_parent_by_sku($sku)
    {
        $sku = trim((string) $sku);
        if ($sku === '' || !$this->db->table_exists($this->table)) {
            return null;
        }
        $this->db->from($this->table)->where('sku', $sku);
        $this->db->group_start()
            ->where('store_id IS NULL', null, false)
            ->or_where('store_id', 0)
        ->group_end();
        return $this->db->get()->row();
    }

    public function catalog_children_by_sku($sku)
    {
        $sku = trim((string) $sku);
        if ($sku === '' || !$this->db->table_exists($this->table) || !$this->db->field_exists('parent_sku', $this->table)) {
            return array();
        }
        $this->db->from($this->table)->where('parent_sku', $sku);
        $this->db->group_start()
            ->where('store_id IS NULL', null, false)
            ->or_where('store_id', 0)
        ->group_end();
        $this->db->order_by('id', 'asc');
        return $this->db->get()->result();
    }

    /**
     * Catalog ids whose store copies should be deleted/recreated for this save.
     * Parent: parent + children. Child: that child only.
     */
    public function catalog_recreate_ids($product)
    {
        if (!$product || empty($product->id)) {
            return array();
        }
        $id = (int) $product->id;
        $ids = array($id);
        $parentSku = isset($product->parent_sku) ? trim((string) $product->parent_sku) : '';
        if ($parentSku !== '') {
            return $ids;
        }
        $sku = isset($product->sku) ? trim((string) $product->sku) : '';
        foreach ($this->catalog_children_by_sku($sku) as $child) {
            $ids[] = (int) $child->id;
        }
        return array_values(array_unique($ids));
    }

    /**
     * Delete store copies of a catalog parent/child and create them again
     * on every store that already listed them (plus auto-add stores).
     */
    public function recreate_store_copies($catalogId)
    {
        $catalogId = (int) $catalogId;
        if ($catalogId < 1) {
            return 0;
        }
        $source = $this->get($catalogId);
        if (!$source || !empty($source->store_id)) {
            return 0;
        }

        $deleteIds = $this->catalog_recreate_ids($source);
        if (!$deleteIds) {
            return 0;
        }

        $storeIds = array();
        $copyIds = array();
        $copyRows = $this->db->select('id, store_id, sku')
            ->from($this->table)
            ->where_in('source_product_id', $deleteIds)
            ->where('store_id >', 0)
            ->get()
            ->result();
        foreach ($copyRows as $row) {
            $copyIds[(int) $row->id] = (int) $row->id;
            $storeIds[(int) $row->store_id] = (int) $row->store_id;
        }

        $parentSku = isset($source->parent_sku) ? trim((string) $source->parent_sku) : '';
        $parent = null;
        if ($parentSku !== '') {
            $parent = $this->catalog_parent_by_sku($parentSku);
            if ($parent) {
                foreach ($this->store_copies($parent->id) as $copy) {
                    if (!empty($copy->store_id)) {
                        $storeIds[(int) $copy->store_id] = (int) $copy->store_id;
                    }
                }
            }
        }

        $storeModel = function_exists('ec_load_store_product_model') ? ec_load_store_product_model() : null;
        if ($storeModel && !empty($source->auto_add_to_stores)) {
            foreach ($storeModel->auto_add_stores_for_product($source) as $store) {
                $storeIds[(int) $store->id] = (int) $store->id;
            }
        }
        if (!$storeIds) {
            return 0;
        }

        if ($copyIds) {
            $copies = $this->db->select('id, store_id, sku')->from($this->table)->where_in('id', array_values($copyIds))->get()->result();
            foreach ($copies as $copy) {
                $this->queue_store_copy_channel_delete($copy);
                $this->delete_related((int) $copy->id);
                $this->db->where('id', (int) $copy->id)->where('store_id >', 0)->delete($this->table);
            }
        }

        if (!$storeModel) {
            return 0;
        }

        $createIds = $deleteIds;
        if ($parent && !in_array((int) $parent->id, $createIds, true)) {
            array_unshift($createIds, (int) $parent->id);
        }
        $sources = array();
        foreach ($createIds as $cid) {
            $row = $storeModel->catalog_source($cid);
            if ($row && empty($row->store_id)) {
                $sources[] = $row;
            }
        }
        usort($sources, function ($a, $b) {
            $ap = isset($a->parent_sku) ? trim((string) $a->parent_sku) : '';
            $bp = isset($b->parent_sku) ? trim((string) $b->parent_sku) : '';
            if ($ap === '' && $bp !== '') {
                return -1;
            }
            if ($ap !== '' && $bp === '') {
                return 1;
            }
            return 0;
        });

        $stores = $this->db->from('stores')->where_in('id', array_values($storeIds))->get()->result();
        $created = 0;
        foreach ($stores as $store) {
            foreach ($sources as $src) {
                $isParentSrc = (isset($src->parent_sku) ? trim((string) $src->parent_sku) : '') === '';
                if ($isParentSrc && $parent && (int) $src->id === (int) $parent->id && !in_array((int) $src->id, $deleteIds, true)) {
                    if ($storeModel->find_copy($store->id, $src->id)) {
                        continue;
                    }
                }
                $copyId = $storeModel->copy_from_catalog($store, $src);
                if ($copyId) {
                    $created++;
                }
            }
        }
        return $created;
    }

    public function save($data, $id = 0)
    {
        $data = $this->filter_product_data($data);
        if ($id) {
            $this->db->where('id', (int) $id)->update($this->table, $data);
            return (int) $id;
        }
        $this->db->insert($this->table, $data);
        return (int) $this->db->insert_id();
    }

    protected function filter_product_data($data)
    {
        if (!is_array($data) || !$this->db->table_exists($this->table)) {
            return is_array($data) ? $data : array();
        }
        $allowed = $this->db->list_fields($this->table);
        $clean = array();
        foreach ($data as $key => $value) {
            if (in_array($key, $allowed, true)) {
                $clean[$key] = $value;
            }
        }
        return $clean;
    }

    public function store_copy_ids($catalogId)
    {
        $catalogId = (int) $catalogId;
        $ids = array();
        if ($catalogId < 1 || !$this->db->table_exists($this->table) || !$this->db->field_exists('store_id', $this->table)) {
            return $ids;
        }
        if ($this->db->field_exists('source_product_id', $this->table)) {
            $rows = $this->db->select('id')
                ->from($this->table)
                ->where('source_product_id', $catalogId)
                ->where('store_id >', 0)
                ->get()->result();
            foreach ($rows as $row) {
                $ids[(int) $row->id] = (int) $row->id;
            }
        }
        $catalog = $this->db->select('id, sku, analyzer_code')->where('id', $catalogId)->get($this->table)->row();
        if ($catalog) {
            foreach (array('sku', 'analyzer_code') as $field) {
                if (!$this->db->field_exists($field, $this->table)) {
                    continue;
                }
                $base = trim((string) $catalog->$field);
                if ($base === '') {
                    continue;
                }
                $this->db->select('id')->from($this->table)->where('store_id >', 0);
                $this->db->group_start()
                    ->like($field, $base . '-S', 'after')
                    ->or_where($field, $base)
                ->group_end();
                foreach ($this->db->get()->result() as $row) {
                    $copyId = (int) $row->id;
                    if ($copyId !== $catalogId) {
                        $ids[$copyId] = $copyId;
                    }
                }
            }
        }
        unset($ids[$catalogId]);
        return array_values($ids);
    }

    public function orphan_store_copies()
    {
        if (!$this->db->table_exists($this->table) || !$this->db->field_exists('store_id', $this->table)) {
            return array();
        }
        $hasSource = $this->db->field_exists('source_product_id', $this->table);
        $hasCode = $this->db->field_exists('analyzer_code', $this->table);
        $sql = "SELECT copy.id, copy.store_id, copy.sku, copy.name"
            . ($hasSource ? ", copy.source_product_id" : ", 0 AS source_product_id")
            . " FROM {$this->table} copy"
            . ($hasSource ? " LEFT JOIN {$this->table} catalog ON catalog.id = copy.source_product_id" : "")
            . " WHERE copy.store_id IS NOT NULL AND copy.store_id != 0 AND (";
        $parts = array();
        if ($hasSource) {
            $parts[] = "(copy.source_product_id IS NOT NULL AND copy.source_product_id > 0 AND catalog.id IS NULL)";
            $skuOrphan = "( (copy.source_product_id IS NULL OR copy.source_product_id = 0)";
        } else {
            $skuOrphan = "(";
        }
        $skuOrphan .= " AND copy.sku LIKE CONCAT('%-S', copy.store_id)"
            . " AND NOT EXISTS ("
            . " SELECT 1 FROM {$this->table} p"
            . " WHERE (p.store_id IS NULL OR p.store_id = 0)"
            . " AND (p.sku = SUBSTRING_INDEX(copy.sku, CONCAT('-S', copy.store_id), 1)";
        if ($hasCode) {
            $skuOrphan .= " OR (p.analyzer_code IS NOT NULL AND p.analyzer_code != '' AND p.analyzer_code = SUBSTRING_INDEX(copy.sku, CONCAT('-S', copy.store_id), 1))";
        }
        $skuOrphan .= ")))";
        $parts[] = $skuOrphan;
        $sql .= implode(' OR ', $parts) . ') ORDER BY copy.store_id, copy.id';
        return $this->db->query($sql)->result();
    }

    public function purge_orphan_store_copies()
    {
        $copies = $this->orphan_store_copies();
        foreach ($copies as $copy) {
            $this->queue_store_copy_channel_delete($copy);
            $this->delete_related((int) $copy->id);
            $this->db->where('id', (int) $copy->id)->where('store_id >', 0)->delete($this->table);
        }
        return count($copies);
    }

    protected function delete_related($productId)
    {
        $productId = (int) $productId;
        if ($productId < 1) {
            return;
        }
        $tables = array(
            'product_images',
            'product_variations',
            'product_attributes',
            'product_sources',
            'product_creatives',
            'product_categories',
            'product_faqs',
            'product_reviews',
            'product_analyzer_meta',
            'product_supplier_options',
            'product_supplier_price_history',
            'store_product_prices',
        );
        foreach ($tables as $table) {
            if ($this->db->table_exists($table)) {
                $this->db->where('product_id', $productId)->delete($table);
            }
        }
    }

    public function delete($id)
    {
        $id = (int) $id;
        $copyIds = $this->store_copy_ids($id);
        $copies = array();
        if ($copyIds) {
            $copies = $this->db->select('id, store_id, sku')->from($this->table)->where_in('id', $copyIds)->get()->result();
        }
        foreach ($copies as $copy) {
            $this->queue_store_copy_channel_delete($copy);
            $this->delete_related((int) $copy->id);
            $this->db->where('id', (int) $copy->id)->where('store_id >', 0)->delete($this->table);
        }
        $this->delete_related($id);
        return $this->db->where('id', $id)->delete($this->table);
    }

    protected function queue_store_copy_channel_delete($copy)
    {
        if (!$copy || empty($copy->store_id) || empty($copy->id)) {
            return;
        }
        if (!$this->db->table_exists('stores')) {
            return;
        }
        $this->load->model('Store_channel_model');
        $store = (object) array('id' => (int) $copy->store_id);
        $this->Store_channel_model->queue_delete_product($store, (int) $copy->id);
    }

    public function ensure_sources_table()
    {
        $this->db->query("CREATE TABLE IF NOT EXISTS product_sources (
            id INT(11) NOT NULL AUTO_INCREMENT,
            product_id INT(11) NOT NULL,
            source_url VARCHAR(500) NOT NULL DEFAULT '',
            source_price DECIMAL(12,2) NOT NULL DEFAULT 0.00,
            label VARCHAR(150) NOT NULL DEFAULT '',
            sort_order INT(11) NOT NULL DEFAULT 0,
            PRIMARY KEY (id),
            KEY product_id (product_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    }

    public function sources($productId)
    {
        $this->ensure_sources_table();
        return $this->db
            ->where('product_id', (int) $productId)
            ->order_by('sort_order', 'asc')
            ->order_by('id', 'asc')
            ->get('product_sources')
            ->result();
    }

    public function replace_sources($productId, $rows)
    {
        $this->ensure_sources_table();
        $this->db->where('product_id', (int) $productId)->delete('product_sources');
        $sort = 1;
        foreach ((array) $rows as $row) {
            $url = isset($row['source_url']) ? trim((string) $row['source_url']) : '';
            if ($url === '') {
                continue;
            }
            $this->db->insert('product_sources', array(
                'product_id' => (int) $productId,
                'source_url' => $url,
                'source_price' => isset($row['source_price']) ? (float) $row['source_price'] : 0,
                'label' => isset($row['label']) ? trim((string) $row['label']) : '',
                'sort_order' => $sort++,
            ));
        }
    }

    public function ensure_creatives_table()
    {
        $this->db->query("CREATE TABLE IF NOT EXISTS product_creatives (
            id INT(11) NOT NULL AUTO_INCREMENT,
            product_id INT(11) NOT NULL,
            link VARCHAR(1000) NOT NULL DEFAULT '',
            label VARCHAR(150) NOT NULL DEFAULT '',
            sort_order INT(11) NOT NULL DEFAULT 0,
            PRIMARY KEY (id),
            KEY product_id (product_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    }

    public function creatives($productId)
    {
        $this->ensure_creatives_table();
        return $this->db
            ->where('product_id', (int) $productId)
            ->order_by('sort_order', 'asc')
            ->order_by('id', 'asc')
            ->get('product_creatives')
            ->result();
    }

    public function replace_creatives($productId, $rows)
    {
        $this->ensure_creatives_table();
        $this->db->where('product_id', (int) $productId)->delete('product_creatives');
        $this->insert_creatives((int) $productId, $rows, 1);
    }

    public function add_creatives($productId, $rows)
    {
        $this->ensure_creatives_table();
        $existing = array();
        foreach ($this->creatives($productId) as $row) {
            $key = strtolower(rtrim((string) $row->link, '/'));
            if ($key !== '') {
                $existing[$key] = true;
            }
        }
        $fresh = array();
        foreach ((array) $rows as $row) {
            $parsed = $this->normalize_creative_row($row);
            if ($parsed === null) {
                continue;
            }
            $key = strtolower(rtrim($parsed['link'], '/'));
            if (isset($existing[$key])) {
                continue;
            }
            $existing[$key] = true;
            $fresh[] = $parsed;
        }
        if (empty($fresh)) {
            return;
        }
        $next = 1;
        $last = $this->db
            ->select_max('sort_order')
            ->where('product_id', (int) $productId)
            ->get('product_creatives')
            ->row();
        if ($last && (int) $last->sort_order > 0) {
            $next = (int) $last->sort_order + 1;
        }
        $this->insert_creatives((int) $productId, $fresh, $next);
    }

    protected function insert_creatives($productId, $rows, $sortStart = 1)
    {
        $sort = (int) $sortStart;
        foreach ((array) $rows as $row) {
            $parsed = $this->normalize_creative_row($row);
            if ($parsed === null) {
                continue;
            }
            $this->db->insert('product_creatives', array(
                'product_id' => (int) $productId,
                'link' => $parsed['link'],
                'label' => $parsed['label'],
                'sort_order' => $sort++,
            ));
        }
    }

    protected function normalize_creative_row($row)
    {
        if (is_string($row) || is_numeric($row)) {
            $row = array('link' => (string) $row);
        }
        if (!is_array($row)) {
            return null;
        }
        $link = isset($row['link']) ? trim((string) $row['link']) : '';
        if ($link === '' && isset($row['creative_url'])) {
            $link = trim((string) $row['creative_url']);
        }
        if ($link === '') {
            return null;
        }
        return array(
            'link' => $link,
            'label' => isset($row['label']) ? trim((string) $row['label']) : '',
        );
    }

    public function images($productId)
    {
        if (function_exists('ensure_product_parent_columns')) {
            ensure_product_parent_columns();
        }
        if (!$this->db->table_exists('product_images')) {
            return array();
        }
        return $this->db
            ->where('product_id', (int) $productId)
            ->order_by('sort_order', 'asc')
            ->order_by('id', 'asc')
            ->get('product_images')
            ->result();
    }

    public function add_image($productId, $path)
    {
        $this->db->insert('product_images', array(
            'product_id' => (int) $productId,
            'image' => $path,
        ));
        return (int) $this->db->insert_id();
    }

    public function get_image($id)
    {
        return $this->db->where('id', (int) $id)->get('product_images')->row();
    }

    public function delete_image($id)
    {
        return $this->db->where('id', (int) $id)->delete('product_images');
    }

    public function variations($productId)
    {
        return $this->db
            ->where('product_id', (int) $productId)
            ->order_by('id', 'asc')
            ->get('product_variations')
            ->result();
    }

    public function attributes($productId)
    {
        $rows = $this->db
            ->where('product_id', (int) $productId)
            ->order_by('sort_order', 'asc')
            ->order_by('id', 'asc')
            ->get('product_attributes')
            ->result();
        if ($rows) {
            return $rows;
        }
        return $this->attributes_from_variations($productId);
    }

    public function attributes_from_variations($productId)
    {
        $map = array();
        foreach ($this->variations($productId) as $variation) {
            $decoded = array();
            if (!empty($variation->attributes_json)) {
                $decoded = json_decode($variation->attributes_json, true);
            }
            if (!is_array($decoded) || !$decoded) {
                if ($variation->option_name !== '') {
                    $decoded = array($variation->option_name => $variation->option_value);
                }
            }
            foreach ($decoded as $name => $value) {
                $name = trim((string) $name);
                $value = trim((string) $value);
                if ($name === '' || $value === '') {
                    continue;
                }
                if (!isset($map[$name])) {
                    $map[$name] = array();
                }
                $map[$name][$value] = $value;
            }
        }
        $out = array();
        $i = 0;
        foreach ($map as $name => $values) {
            $out[] = (object) array(
                'id' => 0,
                'name' => $name,
                'values_text' => implode(', ', array_values($values)),
                'sort_order' => $i,
            );
            $i++;
        }
        return $out;
    }

    public function replace_attributes($productId, $rows)
    {
        $this->db->where('product_id', (int) $productId)->delete('product_attributes');
        foreach ($rows as $row) {
            $row['product_id'] = (int) $productId;
            $this->db->insert('product_attributes', $row);
        }
    }

    public function replace_variations($productId, $rows)
    {
        $this->db->where('product_id', (int) $productId)->delete('product_variations');
        foreach ($rows as $row) {
            if ($row['option_value'] === '' && $row['combination_key'] === '') {
                continue;
            }
            $row['product_id'] = (int) $productId;
            $this->db->insert('product_variations', $row);
        }
    }
}
