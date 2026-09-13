<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Store_settings_model extends CI_Model {

    public function getAll($storeId)
    {
        $rows = $this->db->where('store_id', $storeId)->get('store_settings')->result_array();
        $settings = array();
        foreach ($rows as $row) {
            $settings[$row['setting_key']] = $row['setting_value'];
        }
        return $settings;
    }

    public function get($storeId, $key, $default = '')
    {
        $row = $this->db
            ->where('store_id', $storeId)
            ->where('setting_key', $key)
            ->get('store_settings')
            ->row();
        return $row ? $row->setting_value : $default;
    }

    public function set($storeId, $key, $value)
    {
        $exists = $this->db
            ->where('store_id', $storeId)
            ->where('setting_key', $key)
            ->count_all_results('store_settings');

        if ($exists) {
            return $this->db
                ->where('store_id', $storeId)
                ->where('setting_key', $key)
                ->update('store_settings', array('setting_value' => $value));
        }

        return $this->db->insert('store_settings', array(
            'store_id' => $storeId,
            'setting_key' => $key,
            'setting_value' => $value,
        ));
    }

    public function setMany($storeId, $pairs)
    {
        foreach ($pairs as $key => $value) {
            $this->set($storeId, $key, $value);
        }
        return true;
    }
}
