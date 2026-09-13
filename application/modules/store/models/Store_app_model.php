<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Store_app_model extends CI_Model {

    public function getByStore($storeId)
    {
        return $this->db->where('store_id', $storeId)->order_by('app_name', 'asc')->get('store_apps')->result_array();
    }

    public function getById($id, $storeId)
    {
        return $this->db->where('id', $id)->where('store_id', $storeId)->get('store_apps')->row();
    }

    public function toggle($storeId, $id, $enabled)
    {
        return $this->db
            ->where('id', $id)
            ->where('store_id', $storeId)
            ->update('store_apps', array('is_enabled' => $enabled ? 1 : 0));
    }

    public function updateConfig($storeId, $id, $config)
    {
        return $this->db
            ->where('id', $id)
            ->where('store_id', $storeId)
            ->update('store_apps', array('config' => json_encode($config)));
    }

    public function seedDefaults($storeId)
    {
        if ($this->db->where('store_id', $storeId)->count_all_results('store_apps') > 0) {
            return;
        }
        $apps = array(
            array('app_name' => 'Email Marketing', 'app_slug' => 'email-marketing', 'is_enabled' => 1, 'config' => '{}'),
            array('app_name' => 'Reviews', 'app_slug' => 'reviews', 'is_enabled' => 0, 'config' => '{}'),
            array('app_name' => 'Analytics', 'app_slug' => 'analytics', 'is_enabled' => 1, 'config' => '{}'),
        );
        foreach ($apps as $app) {
            $app['store_id'] = $storeId;
            $this->db->insert('store_apps', $app);
        }
    }
}
