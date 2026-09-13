<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Supplier_model extends CI_Model {

    protected $table = 'suppliers';

    public function all()
    {
        return $this->db
            ->select('suppliers.*, countries.name as country_name, countries.code as country_code')
            ->from($this->table)
            ->join('countries', 'countries.id = suppliers.country_id', 'left')
            ->order_by('suppliers.id', 'desc')
            ->get()
            ->result();
    }

    public function get($id)
    {
        return $this->db
            ->select('suppliers.*, countries.name as country_name, countries.code as country_code')
            ->from($this->table)
            ->join('countries', 'countries.id = suppliers.country_id', 'left')
            ->where('suppliers.id', (int) $id)
            ->get()
            ->row();
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
        $this->db->where('supplier_id', (int) $id)->update('products', array('supplier_id' => null));
        return $this->db->where('id', (int) $id)->delete($this->table);
    }
}
