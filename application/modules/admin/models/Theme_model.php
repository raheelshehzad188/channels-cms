<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Theme_model extends CI_Model {

    public function all()
    {
        $this->sync_from_disk();
        return $this->db->order_by('name', 'asc')->get('themes')->result();
    }

    public function get($id)
    {
        return $this->db->where('id', (int) $id)->get('themes')->row();
    }

    public function get_by_slug($slug)
    {
        return $this->db->where('slug', $slug)->get('themes')->row();
    }

    public function fields($themeId)
    {
        $this->ensure_favicon_field($themeId);
        return $this->db
            ->where('theme_id', (int) $themeId)
            ->order_by('sort_order', 'asc')
            ->order_by('id', 'asc')
            ->get('theme_setting_fields')
            ->result();
    }

    public function field($id)
    {
        return $this->db->where('id', (int) $id)->get('theme_setting_fields')->row();
    }

    public function save_field($data, $id = 0)
    {
        if ($id) {
            $this->db->where('id', (int) $id)->update('theme_setting_fields', $data);
            return (int) $id;
        }
        $this->db->insert('theme_setting_fields', $data);
        return (int) $this->db->insert_id();
    }

    public function delete_field($id)
    {
        return $this->db->where('id', (int) $id)->delete('theme_setting_fields');
    }

    public function screenshot_url($slug)
    {
        return theme_screenshot($slug);
    }

    public function sync_from_disk()
    {
        $base = APPPATH . 'views/frontend/';
        if (!is_dir($base)) {
            return;
        }

        foreach (scandir($base) as $slug) {
            if ($slug === '.' || $slug === '..') {
                continue;
            }
            $dir = $base . $slug;
            if (!is_dir($dir) || !is_file($dir . '/screenshot.jpg')) {
                continue;
            }
            if ($this->get_by_slug($slug)) {
                continue;
            }
            $this->db->insert('themes', array(
                'name' => $slug === 'zenvello' ? 'ZENVello' : ucfirst($slug),
                'slug' => $slug,
                'description' => $slug === 'fruitables'
                    ? 'Organic fruits and vegetables storefront.'
                    : ($slug === 'zenvello'
                        ? 'Modern marketplace storefront — shop smart, live better.'
                        : 'Frontend theme ' . $slug),
                'status' => 1,
            ));
            $themeId = (int) $this->db->insert_id();
            $this->seed_default_fields($themeId, $slug);
        }
    }

    protected function seed_default_fields($themeId, $slug)
    {
        $defaults = array(
            array('logo', 'Header Logo', 'image', 0, '', 1),
            array('favicon', 'Favicon', 'image', 0, '', 2),
            array('primary_color', 'Primary Color', 'color', 1, $slug === 'fruitables' ? '#81C408' : ($slug === 'zenvello' ? '#ffd814' : '#c9a227'), 3),
            array('secondary_color', 'Secondary Color', 'color', 1, $slug === 'fruitables' ? '#FFB524' : ($slug === 'zenvello' ? '#111111' : '#333333'), 4),
            array('footer_text', 'Footer Text', 'text', 1, ($slug === 'zenvello' ? 'ZENVello' : ucfirst($slug)) . '. All rights reserved.', 5),
        );
        if ($slug === 'fruitables') {
            $defaults[] = array('hero_title', 'Hero Title', 'text', 0, 'Organic Veggies & Fruits Foods', 5);
            $defaults[] = array('hero_subtitle', 'Hero Subtitle', 'text', 0, '100% Organic Foods', 6);
            $defaults[] = array('address', 'Address', 'text', 0, '123 Street, New York', 7);
            $defaults[] = array('email', 'Email', 'text', 0, 'email@example.com', 8);
            $defaults[] = array('phone', 'Phone', 'text', 0, '+0123 4567 8910', 9);
        }
        if ($slug === 'zenvello') {
            $defaults[] = array('footer_about', 'Footer About', 'text', 0, 'Your one-stop shop for quality products at the best prices. Shop smart, live better.', 5);
            $defaults[] = array('promo_text', 'Top Promo Text', 'text', 0, 'Free Shipping on Orders Over £50', 6);
            $defaults[] = array('hero_title', 'Hero Title', 'text', 0, "Make Your Home\nFeel Like You", 7);
            $defaults[] = array('hero_subtitle', 'Hero Subtitle', 'text', 0, 'Discover smart, stylish and affordable products for a better everyday life.', 8);
            $defaults[] = array('address', 'Address', 'text', 0, '123 Market Street', 9);
            $defaults[] = array('email', 'Email', 'text', 0, 'hello@zenvello.ecommerce.test', 10);
            $defaults[] = array('phone', 'Phone', 'text', 0, '+44 20 0000 0000', 11);
        }
        foreach ($defaults as $field) {
            $this->db->insert('theme_setting_fields', array(
                'theme_id' => (int) $themeId,
                'field_key' => $field[0],
                'field_label' => $field[1],
                'field_type' => $field[2],
                'is_required' => $field[3],
                'default_value' => $field[4],
                'sort_order' => $field[5],
            ));
        }
    }

    protected function ensure_favicon_field($themeId)
    {
        $themeId = (int) $themeId;
        if ($themeId < 1 || !$this->db->table_exists('theme_setting_fields')) {
            return;
        }
        $exists = $this->db
            ->where('theme_id', $themeId)
            ->where('field_key', 'favicon')
            ->count_all_results('theme_setting_fields');
        if ($exists) {
            return;
        }
        $this->db->insert('theme_setting_fields', array(
            'theme_id' => $themeId,
            'field_key' => 'favicon',
            'field_label' => 'Favicon',
            'field_type' => 'image',
            'is_required' => 0,
            'default_value' => '',
            'sort_order' => 2,
        ));
    }
}
