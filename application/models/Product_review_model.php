<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Product_review_model extends CI_Model {

    protected $table = 'product_reviews';

    public function __construct()
    {
        parent::__construct();
        $this->ensure_tables();
    }

    public function ensure_tables()
    {
        $this->db->query("CREATE TABLE IF NOT EXISTS product_reviews (
            id INT(11) NOT NULL AUTO_INCREMENT,
            store_id INT(11) NOT NULL,
            product_id INT(11) NOT NULL,
            customer_id INT(11) NOT NULL DEFAULT 0,
            customer_name VARCHAR(150) NOT NULL DEFAULT '',
            rating TINYINT(1) NOT NULL DEFAULT 5,
            title VARCHAR(255) NOT NULL DEFAULT '',
            content TEXT NOT NULL,
            status TINYINT(1) NOT NULL DEFAULT 1,
            approval_status VARCHAR(20) NOT NULL DEFAULT 'pending',
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME NULL,
            deleted_at DATETIME NULL,
            PRIMARY KEY (id),
            KEY store_id (store_id),
            KEY product_id (product_id),
            KEY approval_status (approval_status),
            KEY status (status)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    }

    public function all($storeId, $filters = array())
    {
        $this->apply_filters($storeId, $filters);
        return $this->db
            ->select('product_reviews.*, products.name AS product_name, products.slug AS product_slug')
            ->from($this->table)
            ->join('products', 'products.id = product_reviews.product_id', 'left')
            ->order_by('product_reviews.id', 'desc')
            ->get()
            ->result();
    }

    public function published_for_product($storeId, $productId, $limit = 20, $offset = 0)
    {
        return $this->db
            ->from($this->table)
            ->where('store_id', (int) $storeId)
            ->where('product_id', (int) $productId)
            ->where('status', 1)
            ->where('approval_status', 'approved')
            ->where('deleted_at IS NULL', null, false)
            ->order_by('id', 'desc')
            ->limit((int) $limit, (int) $offset)
            ->get()
            ->result();
    }

    public function published_count($storeId, $productId)
    {
        return (int) $this->db
            ->where('store_id', (int) $storeId)
            ->where('product_id', (int) $productId)
            ->where('status', 1)
            ->where('approval_status', 'approved')
            ->where('deleted_at IS NULL', null, false)
            ->count_all_results($this->table);
    }

    public function summary($storeId, $productId)
    {
        $empty = array(
            'count' => 0,
            'average' => 0,
            'breakdown' => array(5 => 0, 4 => 0, 3 => 0, 2 => 0, 1 => 0),
        );
        $rows = $this->db
            ->select('rating, COUNT(*) AS total', false)
            ->from($this->table)
            ->where('store_id', (int) $storeId)
            ->where('product_id', (int) $productId)
            ->where('status', 1)
            ->where('approval_status', 'approved')
            ->where('deleted_at IS NULL', null, false)
            ->group_by('rating')
            ->get()
            ->result();
        if (!$rows) {
            return $empty;
        }
        $sum = 0;
        $count = 0;
        foreach ($rows as $row) {
            $rating = (int) $row->rating;
            $total = (int) $row->total;
            if ($rating < 1 || $rating > 5) {
                continue;
            }
            $empty['breakdown'][$rating] = $total;
            $sum += $rating * $total;
            $count += $total;
        }
        $empty['count'] = $count;
        $empty['average'] = $count > 0 ? round($sum / $count, 1) : 0;
        return $empty;
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
        $rating = (int) $data['rating'];
        if ($rating < 1) {
            $rating = 1;
        }
        if ($rating > 5) {
            $rating = 5;
        }
        $approval = isset($data['approval_status']) ? strtolower(trim((string) $data['approval_status'])) : 'pending';
        if (!in_array($approval, array('pending', 'approved', 'rejected'), true)) {
            $approval = 'pending';
        }
        $payload = array(
            'store_id' => (int) $storeId,
            'product_id' => (int) $data['product_id'],
            'customer_id' => isset($data['customer_id']) ? (int) $data['customer_id'] : 0,
            'customer_name' => isset($data['customer_name']) ? $data['customer_name'] : '',
            'rating' => $rating,
            'title' => isset($data['title']) ? $data['title'] : '',
            'content' => $data['content'],
            'status' => !empty($data['status']) ? 1 : 0,
            'approval_status' => $approval,
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

    public function set_approval($storeId, $id, $approval)
    {
        $existing = $this->get_owned($storeId, $id);
        if (!$existing) {
            return false;
        }
        $approval = strtolower(trim((string) $approval));
        if (!in_array($approval, array('pending', 'approved', 'rejected'), true)) {
            return false;
        }
        return $this->db
            ->where('id', (int) $id)
            ->where('store_id', (int) $storeId)
            ->update($this->table, array(
                'approval_status' => $approval,
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

    protected function apply_filters($storeId, $filters)
    {
        $this->db->where('product_reviews.store_id', (int) $storeId);
        $this->db->where('product_reviews.deleted_at IS NULL', null, false);
        if (!empty($filters['product_id'])) {
            $this->db->where('product_reviews.product_id', (int) $filters['product_id']);
        }
        if (isset($filters['status']) && $filters['status'] !== '' && $filters['status'] !== null) {
            $this->db->where('product_reviews.status', (int) $filters['status']);
        }
        if (!empty($filters['approval_status'])) {
            $this->db->where('product_reviews.approval_status', $filters['approval_status']);
        }
        if (!empty($filters['rating'])) {
            $this->db->where('product_reviews.rating', (int) $filters['rating']);
        }
        if (!empty($filters['q'])) {
            $q = $this->db->escape_like_str($filters['q']);
            $this->db->group_start()
                ->like('product_reviews.customer_name', $q)
                ->or_like('product_reviews.title', $q)
                ->or_like('product_reviews.content', $q)
                ->or_like('products.name', $q)
                ->group_end();
        }
    }
}
