<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Shipping_courier_model extends CI_Model {

    protected $table = 'shipping_couriers';

    public function __construct()
    {
        parent::__construct();
        if (function_exists('ec_ensure_shipping_courier_schema')) {
            ec_ensure_shipping_courier_schema();
        }
    }

    public function all($countryId = 0)
    {
        $this->db
            ->select('shipping_couriers.*, countries.name as country_name, countries.code as country_code')
            ->from($this->table)
            ->join('countries', 'countries.id = shipping_couriers.country_id', 'left');
        if ($countryId > 0) {
            $this->db->where('shipping_couriers.country_id', (int) $countryId);
        }
        return $this->db
            ->order_by('countries.name', 'asc')
            ->order_by('shipping_couriers.sort_order', 'asc')
            ->order_by('shipping_couriers.name', 'asc')
            ->get()
            ->result();
    }

    public function get($id)
    {
        return $this->db->where('id', (int) $id)->get($this->table)->row();
    }

    public function for_country($countryId, $activeOnly = true)
    {
        $this->db->where('country_id', (int) $countryId);
        if ($activeOnly) {
            $this->db->where('status', 1);
        }
        return $this->db
            ->order_by('sort_order', 'asc')
            ->order_by('name', 'asc')
            ->get($this->table)
            ->result();
    }

    public function grouped_active()
    {
        $out = array();
        if (!$this->db->table_exists($this->table)) {
            return $out;
        }
        $rows = $this->db
            ->where('status', 1)
            ->order_by('sort_order', 'asc')
            ->order_by('name', 'asc')
            ->get($this->table)
            ->result();
        foreach ($rows as $row) {
            $cid = (int) $row->country_id;
            if (!isset($out[$cid])) {
                $out[$cid] = array();
            }
            $out[$cid][$row->name] = $row->name;
        }
        foreach ($out as $cid => $list) {
            $out[$cid]['Other'] = 'Other';
        }
        return $out;
    }

    public function exists_name($countryId, $name, $ignoreId = 0)
    {
        $this->db->where('country_id', (int) $countryId);
        $this->db->where('name', $name);
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
        return $this->db->where('id', (int) $id)->delete($this->table);
    }
}
