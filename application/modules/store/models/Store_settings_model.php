<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Store_settings_model extends CI_Model {

    protected $keyCol = null;
    protected $valCol = null;

    protected function columns()
    {
        if ($this->keyCol !== null) {
            return;
        }
        if ($this->db->field_exists('field_key', 'store_settings')) {
            $this->keyCol = 'field_key';
            $this->valCol = 'field_value';
            return;
        }
        $this->keyCol = 'setting_key';
        $this->valCol = 'setting_value';
    }

    public function getAll($storeId)
    {
        $this->columns();
        $rows = $this->db->where('store_id', $storeId)->get('store_settings')->result_array();
        $settings = array();
        foreach ($rows as $row) {
            $pair = store_setting_row_pair($row);
            if ($pair) {
                $settings[$pair[0]] = $pair[1];
                continue;
            }
            if (!isset($row[$this->keyCol])) {
                continue;
            }
            $settings[$row[$this->keyCol]] = isset($row[$this->valCol]) ? $row[$this->valCol] : '';
        }
        return $settings;
    }

    public function get($storeId, $key, $default = '')
    {
        $this->columns();
        $row = $this->db
            ->where('store_id', $storeId)
            ->where($this->keyCol, $key)
            ->get('store_settings')
            ->row_array();
        return $row && isset($row[$this->valCol]) ? $row[$this->valCol] : $default;
    }

    public function set($storeId, $key, $value)
    {
        $this->columns();
        $exists = $this->db
            ->where('store_id', $storeId)
            ->where($this->keyCol, $key)
            ->count_all_results('store_settings');

        $payload = array(
            'store_id' => $storeId,
            $this->keyCol => $key,
            $this->valCol => $value,
        );
        if ($this->db->field_exists('theme_id', 'store_settings') && !isset($payload['theme_id'])) {
            $payload['theme_id'] = 0;
        }
        if ($this->keyCol !== 'field_key' && $this->db->field_exists('field_key', 'store_settings')) {
            $payload['field_key'] = $key;
            if ($this->db->field_exists('field_value', 'store_settings')) {
                $payload['field_value'] = $value;
            }
        }
        if ($this->keyCol !== 'setting_key' && $this->db->field_exists('setting_key', 'store_settings')) {
            $payload['setting_key'] = $key;
            if ($this->db->field_exists('setting_value', 'store_settings')) {
                $payload['setting_value'] = $value;
            }
        }

        if ($exists) {
            $update = array($this->valCol => $value);
            if ($this->valCol !== 'field_value' && $this->db->field_exists('field_value', 'store_settings')) {
                $update['field_value'] = $value;
            }
            if ($this->valCol !== 'setting_value' && $this->db->field_exists('setting_value', 'store_settings')) {
                $update['setting_value'] = $value;
            }
            return $this->db
                ->where('store_id', $storeId)
                ->where($this->keyCol, $key)
                ->update('store_settings', $update);
        }

        return $this->db->insert('store_settings', $payload);
    }

    public function setMany($storeId, $pairs)
    {
        foreach ($pairs as $key => $value) {
            $this->set($storeId, $key, $value);
        }
        return true;
    }
}
