<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Product_model extends CI_Model {

    protected $table = 'products';

    public function __construct()
    {
        parent::__construct();
        $this->ensure_shipping_columns();
        $this->ensure_details_column();
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

    public function all($ownerId = 0)
    {
        $this->db
            ->select('products.*, suppliers.name as supplier_name, COALESCE(product_country.name, supplier_country.name) as country_name, COALESCE(product_country.currency, supplier_country.currency) as country_currency, users.commission as owner_commission', false)
            ->from($this->table)
            ->join('suppliers', 'suppliers.id = products.supplier_id', 'left')
            ->join('countries as product_country', 'product_country.id = products.country_id', 'left')
            ->join('countries as supplier_country', 'supplier_country.id = suppliers.country_id', 'left')
            ->join('users', 'users.UserID = products.created_by', 'left')
            ->group_start()
                ->where('products.store_id IS NULL', null, false)
                ->or_where('products.store_id', 0)
            ->group_end();
        if ($ownerId) {
            $this->db->where('products.created_by', (int) $ownerId);
        }
        return $this->db->order_by('products.id', 'desc')->get()->result();
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
        $slug = trim((string) $slug);
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
            ->select('products.*, suppliers.name as supplier_name, COALESCE(product_country.name, supplier_country.name) as country_name, COALESCE(product_country.currency, supplier_country.currency) as country_currency, users.commission as owner_commission', false)
            ->from($this->table)
            ->join('suppliers', 'suppliers.id = products.supplier_id', 'left')
            ->join('countries as product_country', 'product_country.id = products.country_id', 'left')
            ->join('countries as supplier_country', 'supplier_country.id = suppliers.country_id', 'left')
            ->join('users', 'users.UserID = products.created_by', 'left')
            ->where('products.status', 1)
            ->group_start()
                ->where('products.store_id IS NULL', null, false)
                ->or_where('products.store_id', 0)
            ->group_end()
            ->order_by('products.id', 'desc');
        if ($limit) {
            $this->db->limit((int) $limit);
        }
        return $this->db->get()->result();
    }

    public function get($id)
    {
        return $this->db
            ->select('products.*, suppliers.name as supplier_name, COALESCE(product_country.name, supplier_country.name) as country_name, COALESCE(product_country.currency, supplier_country.currency) as country_currency, users.commission as owner_commission', false)
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

    public function save($data, $id = 0)
    {
        if ($id) {
            $this->db->where('id', (int) $id)->update($this->table, $data);
            return (int) $id;
        }
        $this->db->insert($this->table, $data);
        return (int) $this->db->insert_id();
    }

    public function delete($id)
    {
        $id = (int) $id;
        $this->db->where('product_id', $id)->delete('product_images');
        $this->db->where('product_id', $id)->delete('product_variations');
        $this->db->where('product_id', $id)->delete('product_attributes');
        $this->ensure_sources_table();
        $this->db->where('product_id', $id)->delete('product_sources');
        if ($this->db->table_exists('product_categories')) {
            $this->db->where('product_id', $id)->delete('product_categories');
        }
        if ($this->db->table_exists('store_product_prices')) {
            $this->db->where('product_id', $id)->delete('store_product_prices');
        }
        return $this->db->where('id', $id)->delete($this->table);
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

    public function images($productId)
    {
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
