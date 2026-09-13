<?php
defined('BASEPATH') OR exit('No direct script access allowed');

require_once APPPATH . 'modules/store/controllers/Store_base.php';

class Categories extends Store_base {

    public function __construct()
    {
        parent::__construct();
        $this->load->model('Ec_category_model');
    }

    public function index()
    {
        $this->requireAuth();
        $countryId = (int) (isset($this->store->country_id) ? $this->store->country_id : 0);
        $categories = $countryId
            ? $this->Ec_category_model->with_counts_for_store($this->store->id, $countryId)
            : array();
        $enabled = $this->enabled_ids();
        $homeIds = $this->Ec_category_model->home_ids_for_store($this->store->id);
        $heroRow = $this->db
            ->where('store_id', (int) $this->store->id)
            ->where('field_key', 'category_hero_default')
            ->get('store_settings')
            ->row();

        if ($this->input->post()) {
            $selected = $this->input->post('categories');
            $home = $this->input->post('home_categories');
            if (!is_array($selected)) {
                $selected = array();
            }
            if (!is_array($home)) {
                $home = array();
            }
            // home picks must be enabled
            $home = array_values(array_intersect(array_map('intval', $home), array_map('intval', $selected)));
            $this->Ec_category_model->sync_store_selection($this->store->id, $selected, $home);

            $defaultHero = $this->upload_field('category_hero_default');
            if ($defaultHero !== '') {
                $themeId = (int) (isset($this->store->theme_id) ? $this->store->theme_id : 0);
                $exists = $this->db
                    ->where('store_id', (int) $this->store->id)
                    ->where('field_key', 'category_hero_default')
                    ->get('store_settings')
                    ->row();
                if ($exists) {
                    $this->db->where('id', (int) $exists->id)->update('store_settings', array(
                        'field_value' => $defaultHero,
                        'theme_id' => $themeId,
                    ));
                } else {
                    $this->db->insert('store_settings', array(
                        'store_id' => (int) $this->store->id,
                        'theme_id' => $themeId,
                        'field_key' => 'category_hero_default',
                        'field_value' => $defaultHero,
                    ));
                }
            }

            $this->session->set_flashdata('success', 'Theme categories updated.');
            redirect('store/categories');
            return;
        }

        $this->template->store('categories/index', $this->viewData(array(
            'page' => 'Categories',
            'title' => 'Theme categories',
            'categories' => $categories,
            'enabled_ids' => $enabled,
            'home_ids' => $homeIds,
            'country_id' => $countryId,
            'category_hero_default' => $heroRow ? $heroRow->field_value : '',
        )));
    }

    public function edit($id = 0)
    {
        $this->requireAuth();
        $category = $this->Ec_category_model->get($id);
        $countryId = (int) (isset($this->store->country_id) ? $this->store->country_id : 0);
        if (!$category || (int) $category->country_id !== $countryId) {
            $this->session->set_flashdata('error', 'Category not available for your country.');
            redirect('store/categories');
            return;
        }

        $category = $this->Ec_category_model->resolve_for_store($category, $this->store->id);
        $setting = $this->Ec_category_model->store_setting($this->store->id, $category->id);

        if ($this->input->post()) {
            $payload = array(
                'enabled' => 1,
                'seo_title' => trim((string) $this->input->post('seo_title')),
                'seo_description' => trim((string) $this->input->post('seo_description')),
                'seo_keywords' => trim((string) $this->input->post('seo_keywords')),
                'hero_kicker' => trim((string) $this->input->post('hero_kicker')),
                'hero_title' => trim((string) $this->input->post('hero_title')),
                'hero_text' => trim((string) $this->input->post('hero_text')),
                'hero_btn_text' => trim((string) $this->input->post('hero_btn_text')),
                'hero_btn_link' => trim((string) $this->input->post('hero_btn_link')),
                'show_on_home' => (int) $this->input->post('show_on_home') === 1 ? 1 : 0,
            );
            $image = $this->upload_field('image');
            if ($image !== '') {
                $payload['image'] = $image;
            }
            $hero = $this->upload_field('hero_image');
            if ($hero !== '') {
                $payload['hero_image'] = $hero;
            }
            if ($setting) {
                $payload['sort_order'] = (int) $setting->sort_order;
            }
            $this->Ec_category_model->save_store_setting($this->store->id, $category->id, $payload);

            // keep legacy home table aligned
            $enabled = $this->enabled_ids();
            if (!in_array((int) $category->id, $enabled, true)) {
                $enabled[] = (int) $category->id;
            }
            $homeIds = $this->Ec_category_model->home_ids_for_store($this->store->id);
            if ($payload['show_on_home']) {
                if (!in_array((int) $category->id, $homeIds, true)) {
                    $homeIds[] = (int) $category->id;
                }
            } else {
                $homeIds = array_values(array_diff($homeIds, array((int) $category->id)));
            }
            $this->Ec_category_model->sync_store_selection($this->store->id, $enabled, $homeIds);

            $this->session->set_flashdata('success', 'Category customization saved.');
            redirect('store/categories/edit/' . (int) $category->id);
            return;
        }

        $this->template->store('categories/edit', $this->viewData(array(
            'page' => 'Categories',
            'title' => 'Customize: ' . $category->name,
            'category' => $category,
            'setting' => $setting,
        )));
    }

    protected function enabled_ids()
    {
        $rows = $this->db
            ->where('store_id', (int) $this->store->id)
            ->where('enabled', 1)
            ->get('store_category_settings')
            ->result();
        $ids = array();
        foreach ($rows as $row) {
            $ids[] = (int) $row->category_id;
        }
        return $ids;
    }

    protected function upload_field($field)
    {
        if (empty($_FILES[$field]['name'])) {
            return '';
        }
        $dir = FCPATH . 'uploads/categories/store_' . (int) $this->store->id . '/';
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        $config = array(
            'upload_path' => $dir,
            'allowed_types' => 'jpg|jpeg|png|gif|webp',
            'max_size' => 4096,
            'encrypt_name' => true,
        );
        $this->load->library('upload', $config);
        $this->upload->initialize($config);
        if (!$this->upload->do_upload($field)) {
            return '';
        }
        $data = $this->upload->data();
        $converted = ec_convert_image_to_webp($data['full_path']);
        return ec_public_upload_path($converted);
    }
}
