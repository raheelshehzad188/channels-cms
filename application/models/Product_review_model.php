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
            source VARCHAR(32) NOT NULL DEFAULT 'customer',
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME NULL,
            deleted_at DATETIME NULL,
            PRIMARY KEY (id),
            KEY store_id (store_id),
            KEY product_id (product_id),
            KEY approval_status (approval_status),
            KEY status (status)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
        if ($this->db->table_exists($this->table) && !$this->db->field_exists('source', $this->table)) {
            $this->db->query("ALTER TABLE product_reviews ADD COLUMN source VARCHAR(32) NOT NULL DEFAULT 'customer' AFTER approval_status");
            $this->db->query("ALTER TABLE product_reviews ADD INDEX source (source)");
        }
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
        if (array_key_exists('source', $data)) {
            $payload['source'] = $this->normalize_source($data['source']);
        }
        if ($id) {
            $existing = $this->get_owned($storeId, $id);
            if (!$existing) {
                return 0;
            }
            $this->db->where('id', (int) $id)->where('store_id', (int) $storeId)->update($this->table, $payload);
            return (int) $id;
        }
        if (!isset($payload['source'])) {
            $payload['source'] = 'customer';
        }
        $payload['created_at'] = !empty($data['created_at']) ? $data['created_at'] : date('Y-m-d H:i:s');
        $this->db->insert($this->table, $payload);
        return (int) $this->db->insert_id();
    }

    public function replace_ai_generated($storeId, $productId, $reviews)
    {
        $storeId = (int) $storeId;
        $productId = (int) $productId;
        if ($storeId < 1 || $productId < 1 || !is_array($reviews) || !$reviews) {
            return false;
        }
        $this->ensure_tables();
        $this->db->trans_begin();
        $this->db
            ->where('store_id', $storeId)
            ->where('product_id', $productId)
            ->where('source', 'ai_generated')
            ->where('deleted_at IS NULL', null, false)
            ->update($this->table, array(
                'deleted_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ));
        if ($this->db->trans_status() === false) {
            $this->db->trans_rollback();
            return false;
        }
        foreach ($reviews as $review) {
            $id = $this->save($storeId, array(
                'product_id' => $productId,
                'customer_id' => 0,
                'customer_name' => isset($review['name']) ? $review['name'] : '',
                'rating' => isset($review['rating']) ? $review['rating'] : 5,
                'title' => isset($review['title']) ? $review['title'] : '',
                'content' => isset($review['text']) ? $review['text'] : '',
                'status' => 1,
                'approval_status' => 'approved',
                'source' => 'ai_generated',
                'created_at' => isset($review['created_at']) ? $review['created_at'] : date('Y-m-d H:i:s'),
            ));
            if ($id < 1) {
                $this->db->trans_rollback();
                return false;
            }
        }
        if ($this->db->trans_status() === false) {
            $this->db->trans_rollback();
            return false;
        }
        $this->db->trans_commit();
        return true;
    }

    protected function normalize_source($source)
    {
        $source = strtolower(trim((string) $source));
        if ($source === 'ai_generated' || $source === 'ai') {
            return 'ai_generated';
        }
        return 'customer';
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

    /**
     * Cross-store review listing for admin analytics (optional store/country/date filters).
     */
    protected function apply_admin_filters($filters = array())
    {
        $this->db->where('product_reviews.deleted_at IS NULL', null, false);
        if (!empty($filters['store_id'])) {
            $this->db->where('product_reviews.store_id', (int) $filters['store_id']);
        }
        if (!empty($filters['product_id'])) {
            $this->db->where('product_reviews.product_id', (int) $filters['product_id']);
        }
        if (!empty($filters['approval_status'])) {
            $this->db->where('product_reviews.approval_status', $filters['approval_status']);
        }
        if (!empty($filters['start'])) {
            $this->db->where('product_reviews.created_at >=', $filters['start']);
        }
        if (!empty($filters['end'])) {
            $this->db->where('product_reviews.created_at <=', $filters['end']);
        }
        if (!empty($filters['country_code']) && $this->db->table_exists('stores') && $this->db->table_exists('countries')) {
            $this->db->join('stores', 'stores.id = product_reviews.store_id', 'left');
            $this->db->join('countries', 'countries.id = stores.country_id', 'left');
            $this->db->where('countries.code', strtoupper($filters['country_code']));
        }
    }

    public function admin_stats($filters = array())
    {
        $empty = array(
            'total' => 0,
            'approved' => 0,
            'pending' => 0,
            'rejected' => 0,
            'average' => 0,
            'customer' => 0,
            'ai_generated' => 0,
        );
        if (!$this->db->table_exists($this->table)) {
            return $empty;
        }
        $this->db->select("COUNT(*) as total,
            SUM(product_reviews.approval_status = 'approved') as approved,
            SUM(product_reviews.approval_status = 'pending') as pending,
            SUM(product_reviews.approval_status = 'rejected') as rejected,
            AVG(product_reviews.rating) as average,
            SUM(product_reviews.source = 'customer' OR product_reviews.source = '' OR product_reviews.source IS NULL) as customer,
            SUM(product_reviews.source = 'ai_generated') as ai_generated", false);
        $this->db->from($this->table);
        $this->apply_admin_filters($filters);
        $row = $this->db->get()->row();
        if (!$row) {
            return $empty;
        }
        return array(
            'total' => (int) $row->total,
            'approved' => (int) $row->approved,
            'pending' => (int) $row->pending,
            'rejected' => (int) $row->rejected,
            'average' => $row->average !== null ? round((float) $row->average, 1) : 0,
            'customer' => (int) $row->customer,
            'ai_generated' => (int) $row->ai_generated,
        );
    }

    public function admin_recent($filters = array(), $limit = 40)
    {
        if (!$this->db->table_exists($this->table)) {
            return array();
        }
        $hasStores = $this->db->table_exists('stores');
        $hasCountries = $this->db->table_exists('countries');
        $this->db->select(
            'product_reviews.*, products.name AS product_name, products.sku AS product_sku' .
            ($hasStores ? ', stores.name AS store_name' : ', \'\' AS store_name') .
            ($hasCountries && $hasStores ? ', countries.name AS country_name, countries.code AS country_code' : ', \'\' AS country_name, \'\' AS country_code'),
            false
        );
        $this->db->from($this->table);
        $this->db->join('products', 'products.id = product_reviews.product_id', 'left');
        if ($hasStores) {
            $this->db->join('stores', 'stores.id = product_reviews.store_id', 'left');
        }
        if ($hasCountries && $hasStores) {
            $this->db->join('countries', 'countries.id = stores.country_id', 'left');
        }
        // Country filter without double-joining stores/countries.
        $countryCode = !empty($filters['country_code']) ? strtoupper($filters['country_code']) : '';
        $filtersNoCountry = $filters;
        unset($filtersNoCountry['country_code']);
        $this->apply_admin_filters($filtersNoCountry);
        if ($countryCode !== '' && $hasCountries && $hasStores) {
            $this->db->where('countries.code', $countryCode);
        }
        $this->db->order_by('product_reviews.id', 'desc');
        $this->db->limit((int) $limit);
        return $this->db->get()->result();
    }
}
