<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Country_model extends CI_Model {

    protected $table = 'countries';

    public function all()
    {
        return $this->db->order_by('name', 'asc')->get($this->table)->result();
    }

    public function get($id)
    {
        return $this->db->where('id', (int) $id)->get($this->table)->row();
    }

    public function exists_code($code, $ignoreId = 0)
    {
        $this->db->where('code', $code);
        if ($ignoreId) {
            $this->db->where('id !=', (int) $ignoreId);
        }
        return $this->db->count_all_results($this->table) > 0;
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
        $used = $this->db->where('country_id', (int) $id)->count_all_results('suppliers');
        if ($used > 0) {
            return false;
        }
        return $this->db->where('id', (int) $id)->delete($this->table);
    }
}
