<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Product_faq_model extends CI_Model {

    protected $table = 'product_faqs';

    public function __construct()
    {
        parent::__construct();
        $this->ensure_tables();
    }

    public function ensure_tables()
    {
        $this->db->query("CREATE TABLE IF NOT EXISTS product_faqs (
            id INT(11) NOT NULL AUTO_INCREMENT,
            store_id INT(11) NOT NULL,
            product_id INT(11) NOT NULL,
            question VARCHAR(500) NOT NULL,
            answer TEXT NOT NULL,
            status TINYINT(1) NOT NULL DEFAULT 1,
            sort_order INT(11) NOT NULL DEFAULT 0,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME NULL,
            deleted_at DATETIME NULL,
            PRIMARY KEY (id),
            KEY store_id (store_id),
            KEY product_id (product_id),
            KEY status (status)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    }

    public function all($storeId, $filters = array())
    {
        $this->apply_filters($storeId, $filters, false);
        return $this->db
            ->select('product_faqs.*, products.name AS product_name, products.slug AS product_slug')
            ->from($this->table)
            ->join('products', 'products.id = product_faqs.product_id', 'left')
            ->order_by('product_faqs.sort_order', 'asc')
            ->order_by('product_faqs.id', 'desc')
            ->get()
            ->result();
    }

    public function for_product($storeId, $productId, $activeOnly = true)
    {
        $this->db
            ->from($this->table)
            ->where('store_id', (int) $storeId)
            ->where('product_id', (int) $productId)
            ->where('deleted_at IS NULL', null, false);
        if ($activeOnly) {
            $this->db->where('status', 1);
        }
        return $this->db
            ->order_by('sort_order', 'asc')
            ->order_by('id', 'asc')
            ->get()
            ->result();
    }

    public function get_owned($storeId, $id)
    {
        return $this->db
            ->where('store_id', (int) $storeId)
            ->where('id', (int) $id)
            ->where('deleted_at IS NULL', null, false)
            ->get($this->table)
            ->row();
    }

    public function save($storeId, $data, $id = 0)
    {
        $payload = array(
            'store_id' => (int) $storeId,
            'product_id' => (int) $data['product_id'],
            'question' => $data['question'],
            'answer' => $data['answer'],
            'status' => !empty($data['status']) ? 1 : 0,
            'sort_order' => isset($data['sort_order']) ? (int) $data['sort_order'] : 0,
            'updated_at' => date('Y-m-d H:i:s'),
        );
        if ($id) {
            $existing = $this->get_owned($storeId, $id);
            if (!$existing) {
                return 0;
            }
            $this->db->where('id', (int) $id)->where('store_id', (int) $storeId)->update($this->table, $payload);
            return (int) $id;
        }
        $payload['created_at'] = date('Y-m-d H:i:s');
        $this->db->insert($this->table, $payload);
        return (int) $this->db->insert_id();
    }

    public function set_status($storeId, $id, $status)
    {
        $existing = $this->get_owned($storeId, $id);
        if (!$existing) {
            return false;
        }
        return $this->db
            ->where('id', (int) $id)
            ->where('store_id', (int) $storeId)
            ->update($this->table, array(
                'status' => $status ? 1 : 0,
                'updated_at' => date('Y-m-d H:i:s'),
            ));
    }

    public function delete_owned($storeId, $id)
    {
        $existing = $this->get_owned($storeId, $id);
        if (!$existing) {
            return false;
        }
        return $this->db
            ->where('id', (int) $id)
            ->where('store_id', (int) $storeId)
            ->update($this->table, array(
                'deleted_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ));
    }

    protected function apply_filters($storeId, $filters, $countOnly = false)
    {
        $this->db->where('product_faqs.store_id', (int) $storeId);
        $this->db->where('product_faqs.deleted_at IS NULL', null, false);
        if (!empty($filters['product_id'])) {
            $this->db->where('product_faqs.product_id', (int) $filters['product_id']);
        }
        if (isset($filters['status']) && $filters['status'] !== '' && $filters['status'] !== null) {
            $this->db->where('product_faqs.status', (int) $filters['status']);
        }
        if (!empty($filters['q'])) {
            $q = $this->db->escape_like_str($filters['q']);
            $this->db->group_start()
                ->like('product_faqs.question', $q)
                ->or_like('product_faqs.answer', $q)
                ->or_like('products.name', $q)
                ->group_end();
        }
    }
}
