<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Store_model extends CI_Model {

    public function all()
    {
        return $this->db
            ->select('stores.*, themes.name as theme_name, themes.slug as theme_slug, countries.name as country_name, countries.currency as country_currency')
            ->from('stores')
            ->join('themes', 'themes.id = stores.theme_id', 'left')
            ->join('countries', 'countries.id = stores.country_id', 'left')
            ->order_by('stores.id', 'desc')
            ->get()
            ->result();
    }

    public function get($id)
    {
        return $this->db
            ->select('stores.*, themes.name as theme_name, themes.slug as theme_slug, countries.name as country_name, countries.currency as country_currency')
            ->from('stores')
            ->join('themes', 'themes.id = stores.theme_id', 'left')
            ->join('countries', 'countries.id = stores.country_id', 'left')
            ->where('stores.id', (int) $id)
            ->get()
            ->row();
    }

    public function domain_exists($domain, $ignoreId = 0)
    {
        $this->db->where('domain', $domain);
        if ($ignoreId) {
            $this->db->where('id !=', (int) $ignoreId);
        }
        return $this->db->count_all_results('stores') > 0;
    }

    public function save($data, $id = 0)
    {
        if ($id) {
            $this->db->where('id', (int) $id)->update('stores', $data);
            return (int) $id;
        }
        $this->db->insert('stores', $data);
        return (int) $this->db->insert_id();
    }

    public function delete($id)
    {
        $this->db->where('store_id', (int) $id)->delete('store_settings');
        return $this->db->where('id', (int) $id)->delete('stores');
    }

    public function settings_map($storeId)
    {
        $rows = $this->db->where('store_id', (int) $storeId)->get('store_settings')->result();
        $map = array();
        foreach ($rows as $row) {
            $map[$row->field_key] = $row->field_value;
        }
        return $map;
    }

    public function save_setting($storeId, $themeId, $key, $value)
    {
        $exists = $this->db
            ->where('store_id', (int) $storeId)
            ->where('field_key', $key)
            ->get('store_settings')
            ->row();

        $payload = array(
            'store_id' => (int) $storeId,
            'theme_id' => (int) $themeId,
            'field_key' => $key,
            'field_value' => $value,
        );

        if ($exists) {
            return $this->db->where('id', $exists->id)->update('store_settings', $payload);
        }
        return $this->db->insert('store_settings', $payload);
    }
}
