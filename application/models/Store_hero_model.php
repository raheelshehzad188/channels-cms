<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Store_hero_model extends CI_Model {

    public function __construct()
    {
        parent::__construct();
        $this->ensure_tables();
    }

    public function ensure_tables()
    {
        $this->db->query("CREATE TABLE IF NOT EXISTS store_hero_slides (
            id INT(11) NOT NULL AUTO_INCREMENT,
            store_id INT(11) NOT NULL,
            image VARCHAR(255) NOT NULL DEFAULT '',
            slide_bg VARCHAR(32) NOT NULL DEFAULT '',
            kicker VARCHAR(120) NOT NULL DEFAULT '',
            kicker_color VARCHAR(32) NOT NULL DEFAULT '',
            title VARCHAR(255) NOT NULL DEFAULT '',
            text TEXT NULL,
            btn_text VARCHAR(120) NOT NULL DEFAULT 'Shop Now',
            btn_link VARCHAR(500) NOT NULL DEFAULT 'shop',
            light_text TINYINT(1) NOT NULL DEFAULT 0,
            disc_small VARCHAR(40) NOT NULL DEFAULT '',
            disc_big VARCHAR(40) NOT NULL DEFAULT '',
            disc_span VARCHAR(40) NOT NULL DEFAULT '',
            disc_bg VARCHAR(32) NOT NULL DEFAULT '',
            sort_order INT(11) NOT NULL DEFAULT 0,
            status TINYINT(1) NOT NULL DEFAULT 1,
            starts_on DATE NULL,
            ends_on DATE NULL,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY store_id (store_id),
            KEY store_status_sort (store_id, status, sort_order)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

        if ($this->db->table_exists('store_hero_slides')) {
            if (!$this->db->field_exists('starts_on', 'store_hero_slides')) {
                $this->db->query('ALTER TABLE store_hero_slides ADD COLUMN starts_on DATE NULL AFTER status');
            }
            if (!$this->db->field_exists('ends_on', 'store_hero_slides')) {
                $this->db->query('ALTER TABLE store_hero_slides ADD COLUMN ends_on DATE NULL AFTER starts_on');
            }
        }
    }

    public function all_for_store($storeId)
    {
        return $this->db
            ->where('store_id', (int) $storeId)
            ->order_by('sort_order', 'asc')
            ->order_by('id', 'asc')
            ->get('store_hero_slides')
            ->result();
    }

    public function active_for_store($storeId)
    {
        $today = date('Y-m-d');
        $storeId = (int) $storeId;
        $dated = $this->db
            ->where('store_id', $storeId)
            ->where('status', 1)
            ->where('starts_on IS NOT NULL', null, false)
            ->where('starts_on !=', '0000-00-00')
            ->where('starts_on <=', $today)
            ->group_start()
                ->where('ends_on IS NULL', null, false)
                ->or_where('ends_on', '0000-00-00')
                ->or_where('ends_on >=', $today)
            ->group_end()
            ->order_by('sort_order', 'asc')
            ->order_by('id', 'asc')
            ->get('store_hero_slides')
            ->result();
        if (!empty($dated)) {
            return $dated;
        }

        return $this->db
            ->where('store_id', $storeId)
            ->where('status', 1)
            ->group_start()
                ->where('starts_on IS NULL', null, false)
                ->or_where('starts_on', '0000-00-00')
                ->or_where('starts_on', '')
            ->group_end()
            ->order_by('sort_order', 'asc')
            ->order_by('id', 'asc')
            ->get('store_hero_slides')
            ->result();
    }

    public function get_owned($storeId, $id)
    {
        return $this->db
            ->where('store_id', (int) $storeId)
            ->where('id', (int) $id)
            ->get('store_hero_slides')
            ->row();
    }

    public function next_sort($storeId)
    {
        $row = $this->db
            ->select_max('sort_order')
            ->where('store_id', (int) $storeId)
            ->get('store_hero_slides')
            ->row();
        return $row && $row->sort_order !== null ? ((int) $row->sort_order + 1) : 1;
    }

    public function save($storeId, $data, $id = 0)
    {
        $data['store_id'] = (int) $storeId;
        foreach (array('starts_on', 'ends_on') as $dateField) {
            if (array_key_exists($dateField, $data) && ($data[$dateField] === '' || $data[$dateField] === '0000-00-00')) {
                $data[$dateField] = null;
            }
        }
        $id = (int) $id;
        if ($id > 0) {
            unset($data['created_at']);
            $this->db->where('id', $id)->where('store_id', (int) $storeId)->update('store_hero_slides', $data);
            return $id;
        }
        if (!isset($data['created_at'])) {
            $data['created_at'] = date('Y-m-d H:i:s');
        }
        $this->db->insert('store_hero_slides', $data);
        return (int) $this->db->insert_id();
    }

    public function delete_owned($storeId, $id)
    {
        $row = $this->get_owned($storeId, $id);
        if (!$row) {
            return false;
        }
        $this->delete_upload($row->image);
        $this->db->where('id', (int) $id)->where('store_id', (int) $storeId)->delete('store_hero_slides');
        return true;
    }

    public function delete_upload($path)
    {
        $path = trim((string) $path);
        if ($path === '' || strpos($path, 'uploads/hero/') !== 0) {
            return;
        }
        $abs = FCPATH . ltrim($path, '/');
        if (is_file($abs)) {
            @unlink($abs);
        }
    }

    public function seed_defaults_if_empty($storeId, $defaults = array())
    {
        $storeId = (int) $storeId;
        $existing = $this->db->where('store_id', $storeId)->count_all_results('store_hero_slides');
        if ($existing > 0) {
            return false;
        }

        $flag = $this->db
            ->where('store_id', $storeId)
            ->where('field_key', 'hero_slides_initialized')
            ->get('store_settings')
            ->row();
        if ($flag && trim((string) $flag->field_value) === '1') {
            return false;
        }

        $storeName = isset($defaults['store_name']) ? $defaults['store_name'] : 'your store';
        $title = isset($defaults['title']) ? $defaults['title'] : "Make Your Home\nFeel Like You";
        $text = isset($defaults['text']) ? $defaults['text'] : 'Discover smart, stylish and affordable products for a better everyday life.';
        $base = 'assets/frontend/zenvello/assets/images/';

        $slides = array(
            array(
                'image' => $base . 'hero/hero-living.jpg',
                'slide_bg' => '',
                'kicker' => 'Modern Living',
                'kicker_color' => '',
                'title' => $title,
                'text' => $text,
                'btn_text' => 'Shop Now',
                'btn_link' => 'shop',
                'light_text' => 0,
                'disc_small' => 'UP TO',
                'disc_big' => '50%',
                'disc_span' => 'OFF',
                'disc_bg' => '',
                'sort_order' => 1,
                'status' => 1,
            ),
            array(
                'image' => $base . 'banners/promo-halloween.jpg',
                'slide_bg' => '#1b1016',
                'kicker' => 'New Season',
                'kicker_color' => '#ffb74d',
                'title' => "Fresh Picks\nFor You",
                'text' => 'Explore the latest products curated for ' . $storeName . '.',
                'btn_text' => 'Browse Shop',
                'btn_link' => 'shop',
                'light_text' => 1,
                'disc_small' => '',
                'disc_big' => '',
                'disc_span' => '',
                'disc_bg' => '',
                'sort_order' => 2,
                'status' => 1,
            ),
            array(
                'image' => $base . 'banners/promo-christmas.jpg',
                'slide_bg' => '#12231b',
                'kicker' => 'Best Value',
                'kicker_color' => '#8fe0a8',
                'title' => "Shop Smart\nLive Better",
                'text' => "Quality products at prices you'll love.",
                'btn_text' => 'Shop Deals',
                'btn_link' => 'shop',
                'light_text' => 1,
                'disc_small' => '',
                'disc_big' => '',
                'disc_span' => '',
                'disc_bg' => '',
                'sort_order' => 3,
                'status' => 1,
            ),
        );

        foreach ($slides as $slide) {
            $this->save($storeId, $slide);
        }

        $themeId = 0;
        if (!empty($defaults['theme_id'])) {
            $themeId = (int) $defaults['theme_id'];
        }
        if ($flag) {
            $this->db->where('id', (int) $flag->id)->update('store_settings', array(
                'field_value' => '1',
                'theme_id' => $themeId,
            ));
        } else {
            $this->db->insert('store_settings', array(
                'store_id' => $storeId,
                'theme_id' => $themeId,
                'field_key' => 'hero_slides_initialized',
                'field_value' => '1',
            ));
        }

        return true;
    }
}
