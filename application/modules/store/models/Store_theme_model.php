<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Store_theme_model extends CI_Model {

    public function getByStore($storeId)
    {
        return $this->db->where('store_id', $storeId)->order_by('is_active', 'desc')->get('store_themes')->result_array();
    }

    public function getById($id, $storeId)
    {
        return $this->db->where('id', $id)->where('store_id', $storeId)->get('store_themes')->row();
    }

    public function activate($storeId, $themeId)
    {
        $this->db->where('store_id', $storeId)->update('store_themes', array('is_active' => 0));
        return $this->db->where('id', $themeId)->where('store_id', $storeId)->update('store_themes', array('is_active' => 1));
    }

    public function deactivate($storeId, $themeId)
    {
        return $this->db->where('id', $themeId)->where('store_id', $storeId)->update('store_themes', array('is_active' => 0));
    }

    public function seedDefaults($storeId)
    {
        if ($this->db->where('store_id', $storeId)->count_all_results('store_themes') > 0) {
            return;
        }
        $themes = array(
            array('theme_name' => 'Dawn', 'theme_slug' => 'dawn', 'version' => '1.0', 'is_active' => 1, 'preview_url' => '#'),
            array('theme_name' => 'Craft', 'theme_slug' => 'craft', 'version' => '1.0', 'is_active' => 0, 'preview_url' => '#'),
            array('theme_name' => 'Sense', 'theme_slug' => 'sense', 'version' => '1.0', 'is_active' => 0, 'preview_url' => '#'),
        );
        foreach ($themes as $theme) {
            $theme['store_id'] = $storeId;
            $this->db->insert('store_themes', $theme);
        }
    }
}
