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
    }

    public function available_for_store($store)
    {
        $countryId = !empty($store->country_id) ? (int) $store->country_id : 0;
        $this->db
            ->select('products.*, suppliers.name as supplier_name, users.commission as owner_commission, users.first_name as owner_first, users.last_name as owner_last, COALESCE(product_country.name, supplier_country.name) as country_name, COALESCE(product_country.currency, supplier_country.currency) as country_currency', false)
            ->from('products')
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
        if ($countryId) {
            $this->db->group_start()
                ->where('products.country_id', $countryId)
                ->or_where('suppliers.country_id', $countryId)
                ->group_end();
        }
        return $this->db->get()->result();
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

    public function mine($storeId)
    {
        return $this->db
            ->where('store_id', (int) $storeId)
            ->order_by('id', 'desc')
            ->get('products')
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
}
