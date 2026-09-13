<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Staff_model extends CI_Model {

    protected $table = 'staff';

    public function getByStore($storeId, $where = array())
    {
        $this->db->where('store_id', $storeId);
        if ($where) {
            $this->db->where($where);
        }
        $this->db->order_by('id', 'desc');
        return $this->db->get($this->table)->result_array();
    }

    public function getById($id, $storeId)
    {
        return $this->db
            ->where('id', $id)
            ->where('store_id', $storeId)
            ->get($this->table)
            ->row();
    }

    public function add($data)
    {
        $this->db->insert($this->table, $data);
        return $this->db->insert_id();
    }

    public function update($id, $storeId, $data)
    {
        return $this->db->where('id', $id)->where('store_id', $storeId)->update($this->table, $data);
    }

    public function emailExists($storeId, $email, $excludeId = 0)
    {
        $this->db->where('store_id', $storeId)->where('email', $email);
        if ($excludeId) {
            $this->db->where('id !=', $excludeId);
        }
        return $this->db->count_all_results($this->table) > 0;
    }

    public function getRoles()
    {
        return $this->db->order_by('id', 'asc')->get('store_permissions')->result_array();
    }
}
