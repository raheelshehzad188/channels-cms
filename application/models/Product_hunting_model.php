<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Product_hunting_model extends CI_Model {

    protected $table = 'product_hunting';
    protected $creativesTable = 'product_hunting_creatives';
    protected $suppliersTable = 'product_hunting_suppliers';

    public function __construct()
    {
        parent::__construct();
        $this->ensure_tables();
    }

    public function ensure_tables()
    {
        $this->db->query("CREATE TABLE IF NOT EXISTS product_hunting (
            id INT(11) NOT NULL AUTO_INCREMENT,
            title VARCHAR(255) NOT NULL,
            image VARCHAR(255) NOT NULL DEFAULT '',
            notes TEXT NULL,
            status TINYINT(1) NOT NULL DEFAULT 1,
            created_by INT(11) NOT NULL DEFAULT 0,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME NULL,
            PRIMARY KEY (id),
            KEY status (status),
            KEY created_by (created_by)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

        $this->db->query("CREATE TABLE IF NOT EXISTS product_hunting_creatives (
            id INT(11) NOT NULL AUTO_INCREMENT,
            hunting_id INT(11) NOT NULL,
            link VARCHAR(1000) NOT NULL,
            sort_order INT(11) NOT NULL DEFAULT 0,
            PRIMARY KEY (id),
            KEY hunting_id (hunting_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

        $this->db->query("CREATE TABLE IF NOT EXISTS product_hunting_suppliers (
            id INT(11) NOT NULL AUTO_INCREMENT,
            hunting_id INT(11) NOT NULL,
            country_id INT(11) NOT NULL,
            link VARCHAR(1000) NOT NULL,
            sort_order INT(11) NOT NULL DEFAULT 0,
            PRIMARY KEY (id),
            KEY hunting_id (hunting_id),
            KEY country_id (country_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    }

    public function countries()
    {
        if (!$this->db->table_exists('countries')) {
            return array();
        }
        return $this->db->order_by('name', 'asc')->get('countries')->result();
    }

    public function all($filters = array())
    {
        $this->apply_filters($filters);
        return $this->db
            ->select("product_hunting.*, CONCAT(TRIM(IFNULL(users.first_name,'')), ' ', TRIM(IFNULL(users.last_name,''))) AS creator_name, users.uname AS creator_uname", false)
            ->select('(SELECT COUNT(*) FROM product_hunting_creatives c WHERE c.hunting_id = product_hunting.id) AS creative_count', false)
            ->select('(SELECT COUNT(*) FROM product_hunting_suppliers s WHERE s.hunting_id = product_hunting.id) AS supplier_count', false)
            ->from($this->table)
            ->join('users', 'users.UserID = product_hunting.created_by', 'left')
            ->order_by('product_hunting.id', 'desc')
            ->get()
            ->result();
    }

    public function get($id)
    {
        $row = $this->db
            ->select("product_hunting.*, CONCAT(TRIM(IFNULL(users.first_name,'')), ' ', TRIM(IFNULL(users.last_name,''))) AS creator_name, users.uname AS creator_uname", false)
            ->from($this->table)
            ->join('users', 'users.UserID = product_hunting.created_by', 'left')
            ->where('product_hunting.id', (int) $id)
            ->get()
            ->row();
        if (!$row) {
            return null;
        }
        $row->creatives = $this->creatives((int) $id);
        $row->suppliers = $this->suppliers((int) $id);
        return $row;
    }

    public function creatives($huntingId)
    {
        return $this->db
            ->where('hunting_id', (int) $huntingId)
            ->order_by('sort_order', 'asc')
            ->order_by('id', 'asc')
            ->get($this->creativesTable)
            ->result();
    }

    public function suppliers($huntingId)
    {
        return $this->db
            ->select('product_hunting_suppliers.*, countries.name AS country_name, countries.code AS country_code')
            ->from($this->suppliersTable)
            ->join('countries', 'countries.id = product_hunting_suppliers.country_id', 'left')
            ->where('product_hunting_suppliers.hunting_id', (int) $huntingId)
            ->order_by('product_hunting_suppliers.sort_order', 'asc')
            ->order_by('product_hunting_suppliers.id', 'asc')
            ->get()
            ->result();
    }

    public function save($data, $id = 0)
    {
        if ($id) {
            $data['updated_at'] = date('Y-m-d H:i:s');
            $this->db->where('id', (int) $id)->update($this->table, $data);
            return (int) $id;
        }
        $this->db->insert($this->table, $data);
        return (int) $this->db->insert_id();
    }

    public function save_creatives($huntingId, $links)
    {
        $this->db->where('hunting_id', (int) $huntingId)->delete($this->creativesTable);
        $order = 0;
        foreach ($this->normalize_links($links) as $link) {
            $this->db->insert($this->creativesTable, array(
                'hunting_id' => (int) $huntingId,
                'link' => $link,
                'sort_order' => $order++,
            ));
        }
    }

    public function save_suppliers($huntingId, $rows)
    {
        $this->db->where('hunting_id', (int) $huntingId)->delete($this->suppliersTable);
        if (!is_array($rows)) {
            return;
        }
        $order = 0;
        foreach ($rows as $row) {
            $countryId = isset($row['country_id']) ? (int) $row['country_id'] : 0;
            $link = isset($row['link']) ? $this->clean_link($row['link']) : '';
            if ($countryId < 1 || $link === '') {
                continue;
            }
            $this->db->insert($this->suppliersTable, array(
                'hunting_id' => (int) $huntingId,
                'country_id' => $countryId,
                'link' => $link,
                'sort_order' => $order++,
            ));
        }
    }

    public function delete($id)
    {
        $row = $this->db->where('id', (int) $id)->get($this->table)->row();
        if (!$row) {
            return false;
        }
        $this->db->where('hunting_id', (int) $id)->delete($this->creativesTable);
        $this->db->where('hunting_id', (int) $id)->delete($this->suppliersTable);
        $this->db->where('id', (int) $id)->delete($this->table);
        return $row;
    }

    protected function apply_filters($filters)
    {
        $q = isset($filters['q']) ? trim((string) $filters['q']) : '';
        if ($q !== '') {
            $this->db->group_start()
                ->like('product_hunting.title', $q)
                ->or_like('product_hunting.notes', $q)
                ->group_end();
        }
        if (isset($filters['status']) && $filters['status'] !== '' && $filters['status'] !== null) {
            $this->db->where('product_hunting.status', (int) $filters['status']);
        }
        if (!empty($filters['country_id'])) {
            $countryId = (int) $filters['country_id'];
            $this->db->where('EXISTS (SELECT 1 FROM product_hunting_suppliers hs WHERE hs.hunting_id = product_hunting.id AND hs.country_id = ' . $countryId . ')', null, false);
        }
    }

    protected function normalize_links($links)
    {
        $out = array();
        if (!is_array($links)) {
            return $out;
        }
        foreach ($links as $link) {
            $clean = $this->clean_link($link);
            if ($clean === '') {
                continue;
            }
            $out[] = $clean;
        }
        return $out;
    }

    protected function clean_link($link)
    {
        $link = trim((string) $link);
        if ($link === '') {
            return '';
        }
        if (!preg_match('#^https?://#i', $link)) {
            $link = 'https://' . $link;
        }
        return substr($link, 0, 1000);
    }
}
