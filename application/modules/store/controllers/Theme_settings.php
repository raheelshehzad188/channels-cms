<?php
defined('BASEPATH') OR exit('No direct script access allowed');

require_once APPPATH . 'modules/store/controllers/Store_base.php';

class Theme_settings extends Store_base {

    public function __construct()
    {
        parent::__construct();
        $this->load->model('Store_hero_model');
    }

    public function index()
    {
        $this->render_tabs('general');
    }

    public function slider()
    {
        if (!$this->isZenvello()) {
            $this->session->set_flashdata('error', 'Hero slider is available for the Zenvello theme only.');
            redirect('store/theme-settings');
            return;
        }
        $this->render_tabs('slider');
    }

    public function save()
    {
        $this->requireThemeAccess();
        if (!$this->input->post()) {
            redirect('store/theme-settings');
            return;
        }

        $themeId = (int) (isset($this->store->theme_id) ? $this->store->theme_id : 0);
        $current = $this->settings_map();
        $defaults = $this->theme_defaults();

        $textKeys = array(
            'primary_color', 'secondary_color', 'promo_text', 'footer_about', 'footer_text',
            'banner_1_kicker', 'banner_1_title', 'banner_1_text', 'banner_1_btn_text', 'banner_1_btn_link',
            'banner_2_kicker', 'banner_2_title', 'banner_2_text', 'banner_2_btn_text', 'banner_2_btn_link',
        );
        foreach ($textKeys as $key) {
            $value = trim((string) $this->input->post($key));
            if (in_array($key, array('primary_color', 'secondary_color'), true)) {
                $value = $this->normalize_color($value, isset($defaults[$key]) ? $defaults[$key] : '#000000');
            }
            $this->save_setting($key, $value, $themeId);
        }

        $uploadError = false;
        foreach (array('logo', 'favicon', 'banner_1_image', 'banner_2_image') as $imageKey) {
            $uploaded = $this->upload_theme_file($imageKey, $imageKey === 'favicon');
            if ($uploaded !== '') {
                if (!empty($current[$imageKey])) {
                    $this->delete_theme_upload($current[$imageKey]);
                }
                $this->save_setting($imageKey, $uploaded, $themeId);
                if ($imageKey === 'logo') {
                    $this->db->where('id', (int) $this->store->id)->update('stores', array('logo' => $uploaded));
                    $this->store->logo = $uploaded;
                }
            } elseif (!empty($_FILES[$imageKey]['name'])) {
                $uploadError = true;
            }
        }

        if (!$uploadError) {
            $this->session->set_flashdata('success', 'Theme settings saved.');
        }
        redirect('store/theme-settings');
    }

    public function slide($id = 0)
    {
        $this->requireZenvello();
        $slide = $id ? $this->Store_hero_model->get_owned($this->store->id, $id) : null;
        if ($id && !$slide) {
            $this->session->set_flashdata('error', 'Hero slide not found.');
            redirect('store/theme-settings/slider');
            return;
        }

        if ($this->input->post()) {
            $title = trim((string) $this->input->post('title'));
            if ($title === '') {
                $this->session->set_flashdata('error', 'Title is required.');
                redirect($id ? 'store/theme-settings/slide/' . (int) $id : 'store/theme-settings/slide');
                return;
            }

            $payload = array(
                'kicker' => trim((string) $this->input->post('kicker')),
                'kicker_color' => $this->normalize_color(trim((string) $this->input->post('kicker_color')), ''),
                'title' => $title,
                'text' => trim((string) $this->input->post('text')),
                'btn_text' => trim((string) $this->input->post('btn_text')),
                'btn_link' => trim((string) $this->input->post('btn_link')),
                'slide_bg' => $this->normalize_color(trim((string) $this->input->post('slide_bg')), ''),
                'light_text' => (int) $this->input->post('light_text') === 1 ? 1 : 0,
                'disc_small' => trim((string) $this->input->post('disc_small')),
                'disc_big' => trim((string) $this->input->post('disc_big')),
                'disc_span' => trim((string) $this->input->post('disc_span')),
                'disc_bg' => $this->normalize_color(trim((string) $this->input->post('disc_bg')), ''),
                'sort_order' => (int) $this->input->post('sort_order'),
                'status' => (int) $this->input->post('status') === 1 ? 1 : 0,
            );
            if ($payload['btn_text'] === '') {
                $payload['btn_text'] = 'Shop Now';
            }
            if ($payload['btn_link'] === '') {
                $payload['btn_link'] = 'shop';
            }
            if (!$id && $payload['sort_order'] <= 0) {
                $payload['sort_order'] = $this->Store_hero_model->next_sort($this->store->id);
            }

            $image = $this->upload_theme_file('image', false, 'uploads/hero/store_' . (int) $this->store->id . '/');
            $uploadAttempted = !empty($_FILES['image']['name']);
            if ($image !== '') {
                if ($slide && !empty($slide->image)) {
                    $this->Store_hero_model->delete_upload($slide->image);
                }
                $payload['image'] = $image;
            } elseif ($uploadAttempted) {
                redirect($id ? 'store/theme-settings/slide/' . (int) $id : 'store/theme-settings/slide');
                return;
            } elseif (!$slide) {
                $this->session->set_flashdata('error', 'Please upload a slide image.');
                redirect('store/theme-settings/slide');
                return;
            }

            $this->Store_hero_model->save($this->store->id, $payload, $slide ? (int) $slide->id : 0);
            $this->session->set_flashdata('success', $slide ? 'Hero slide updated.' : 'Hero slide added.');
            redirect('store/theme-settings/slider');
            return;
        }

        $this->template->store('theme_settings/slide_form', $this->viewData(array(
            'page' => 'Theme Settings',
            'title' => $slide ? 'Edit Hero Slide' : 'Add Hero Slide',
            'tab' => 'slider',
            'is_zenvello' => true,
            'slide' => $slide,
        )));
    }

    public function slide_delete($id = 0)
    {
        $this->requireZenvello();
        if ($this->Store_hero_model->delete_owned($this->store->id, $id)) {
            $this->session->set_flashdata('success', 'Hero slide deleted.');
        } else {
            $this->session->set_flashdata('error', 'Hero slide not found.');
        }
        redirect('store/theme-settings/slider');
    }

    protected function render_tabs($tab)
    {
        $this->requireThemeAccess();
        $isZenvello = $this->isZenvello();
        $slides = array();
        if ($isZenvello) {
            $settings = $this->tenant->get_settings();
            $this->Store_hero_model->seed_defaults_if_empty($this->store->id, array(
                'title' => theme_setting($settings, 'hero_title', "Make Your Home\nFeel Like You"),
                'text' => theme_setting($settings, 'hero_subtitle', 'Discover smart, stylish and affordable products for a better everyday life.'),
                'store_name' => $this->store->name,
                'theme_id' => isset($this->store->theme_id) ? (int) $this->store->theme_id : 0,
            ));
            $slides = $this->Store_hero_model->all_for_store($this->store->id);
        }

        $map = $this->settings_map();
        $defaults = $this->theme_defaults();
        $values = array_merge($defaults, $map);

        $this->template->store('theme_settings/index', $this->viewData(array(
            'page' => 'Theme Settings',
            'title' => 'Theme Settings',
            'tab' => $tab,
            'is_zenvello' => $isZenvello,
            'values' => $values,
            'slides' => $slides,
        )));
    }

    protected function requireThemeAccess()
    {
        $this->requireAuth();
        if ($this->hasPermission('themes') || $this->hasPermission('settings')) {
            return;
        }
        $this->session->set_flashdata('error', 'You do not have permission to access this area.');
        redirect('store/dashboard');
        exit;
    }

    protected function requireZenvello()
    {
        $this->requireThemeAccess();
        if (!$this->isZenvello()) {
            $this->session->set_flashdata('error', 'Hero slider is available for the Zenvello theme only.');
            redirect('store/theme-settings');
            exit;
        }
    }

    protected function isZenvello()
    {
        $theme = $this->tenant->get_theme();
        return $theme && $theme->slug === 'zenvello';
    }

    protected function theme_defaults()
    {
        $slug = $this->tenant->get_theme() ? $this->tenant->get_theme()->slug : '';
        $primary = $slug === 'fruitables' ? '#81C408' : ($slug === 'zenvello' ? '#ffd814' : '#c9a227');
        $secondary = $slug === 'fruitables' ? '#FFB524' : ($slug === 'zenvello' ? '#111111' : '#333333');
        return array(
            'logo' => '',
            'favicon' => '',
            'primary_color' => $primary,
            'secondary_color' => $secondary,
            'promo_text' => 'Free Shipping on Orders Over £50',
            'footer_about' => 'Your one-stop shop for quality products at the best prices. Shop smart, live better.',
            'footer_text' => ($this->store ? $this->store->name : 'Store') . '. All rights reserved.',
            'banner_1_image' => '',
            'banner_1_kicker' => 'Explore',
            'banner_1_title' => 'Top Picks',
            'banner_1_text' => 'Curated essentials & more',
            'banner_1_btn_text' => 'Shop Now',
            'banner_1_btn_link' => 'shop',
            'banner_2_image' => '',
            'banner_2_kicker' => 'Make It Special',
            'banner_2_title' => 'Gift Ideas',
            'banner_2_text' => 'Finds the whole family will love',
            'banner_2_btn_text' => 'Browse Gifts',
            'banner_2_btn_link' => 'shop',
        );
    }

    protected function settings_map()
    {
        $rows = $this->db->where('store_id', (int) $this->store->id)->get('store_settings')->result();
        $map = array();
        foreach ($rows as $row) {
            if (isset($row->field_key)) {
                $map[$row->field_key] = $row->field_value;
            }
        }
        return $map;
    }

    protected function save_setting($key, $value, $themeId)
    {
        $exists = $this->db
            ->where('store_id', (int) $this->store->id)
            ->where('field_key', $key)
            ->get('store_settings')
            ->row();
        $payload = array(
            'store_id' => (int) $this->store->id,
            'theme_id' => (int) $themeId,
            'field_key' => $key,
            'field_value' => $value,
        );
        if ($exists) {
            $this->db->where('id', (int) $exists->id)->update('store_settings', $payload);
            return;
        }
        $this->db->insert('store_settings', $payload);
    }

    protected function normalize_color($value, $fallback)
    {
        $value = trim((string) $value);
        if ($value === '') {
            return $fallback;
        }
        if (preg_match('/^#([0-9a-fA-F]{3}|[0-9a-fA-F]{6})$/', $value)) {
            return strtolower($value);
        }
        return $fallback;
    }

    protected function upload_theme_file($field, $allowIcon = false, $dir = '')
    {
        if (empty($_FILES[$field]['name'])) {
            return '';
        }
        if ($dir === '') {
            $dir = FCPATH . 'uploads/stores/' . (int) $this->store->id . '/theme/';
        } elseif (strpos($dir, FCPATH) !== 0) {
            $dir = FCPATH . ltrim($dir, '/');
        }
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        $types = $allowIcon ? 'jpg|jpeg|png|gif|webp|ico' : 'jpg|jpeg|png|gif|webp';
        $config = array(
            'upload_path' => $dir,
            'allowed_types' => $types,
            'max_size' => 4096,
            'encrypt_name' => true,
        );
        $this->load->library('upload', $config);
        $this->upload->initialize($config);
        if (!$this->upload->do_upload($field)) {
            $this->session->set_flashdata('error', $this->upload->display_errors('', ''));
            return '';
        }
        $data = $this->upload->data();
        $ext = strtolower(isset($data['file_ext']) ? $data['file_ext'] : '');
        if (in_array($ext, array('.jpg', '.jpeg', '.png', '.gif', '.webp'), true)) {
            $converted = ec_convert_image_to_webp($data['full_path']);
            return ec_public_upload_path($converted);
        }
        return ec_public_upload_path($data['full_path']);
    }

    protected function delete_theme_upload($path)
    {
        $path = trim((string) $path);
        if ($path === '' || strpos($path, 'uploads/') !== 0) {
            return;
        }
        $abs = FCPATH . ltrim($path, '/');
        if (is_file($abs)) {
            @unlink($abs);
        }
    }
}
