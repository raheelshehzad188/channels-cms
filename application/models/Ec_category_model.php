<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Ec_category_model extends CI_Model {

    public function ensure_tables()
    {
        $this->db->query("CREATE TABLE IF NOT EXISTS categories (
            id INT(11) NOT NULL AUTO_INCREMENT,
            country_id INT(11) DEFAULT NULL,
            parent_id INT(11) DEFAULT NULL,
            name VARCHAR(150) NOT NULL,
            slug VARCHAR(160) NOT NULL,
            icon VARCHAR(20) NOT NULL DEFAULT '',
            image VARCHAR(255) NOT NULL DEFAULT '',
            hero_image VARCHAR(255) NOT NULL DEFAULT '',
            hero_kicker VARCHAR(120) NOT NULL DEFAULT '',
            hero_title VARCHAR(255) NOT NULL DEFAULT '',
            hero_text TEXT NULL,
            hero_btn_text VARCHAR(120) NOT NULL DEFAULT '',
            hero_btn_link VARCHAR(500) NOT NULL DEFAULT '',
            description TEXT NULL,
            seo_title VARCHAR(255) NOT NULL DEFAULT '',
            seo_description TEXT NULL,
            seo_keywords VARCHAR(255) NOT NULL DEFAULT '',
            status TINYINT(1) NOT NULL DEFAULT 1,
            sort_order INT(11) NOT NULL DEFAULT 0,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY country_id (country_id),
            KEY parent_id (parent_id),
            UNIQUE KEY country_slug (country_id, slug)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

        $this->db->query("CREATE TABLE IF NOT EXISTS product_categories (
            product_id INT(11) NOT NULL,
            category_id INT(11) NOT NULL,
            PRIMARY KEY (product_id, category_id),
            KEY category_id (category_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

        $this->db->query("CREATE TABLE IF NOT EXISTS store_category_settings (
            id INT(11) NOT NULL AUTO_INCREMENT,
            store_id INT(11) NOT NULL,
            category_id INT(11) NOT NULL,
            enabled TINYINT(1) NOT NULL DEFAULT 1,
            show_on_home TINYINT(1) NOT NULL DEFAULT 0,
            image VARCHAR(255) NOT NULL DEFAULT '',
            hero_image VARCHAR(255) NOT NULL DEFAULT '',
            hero_kicker VARCHAR(120) NOT NULL DEFAULT '',
            hero_title VARCHAR(255) NOT NULL DEFAULT '',
            hero_text TEXT NULL,
            hero_btn_text VARCHAR(120) NOT NULL DEFAULT '',
            hero_btn_link VARCHAR(500) NOT NULL DEFAULT '',
            seo_title VARCHAR(255) NOT NULL DEFAULT '',
            seo_description TEXT NULL,
            seo_keywords VARCHAR(255) NOT NULL DEFAULT '',
            sort_order INT(11) NOT NULL DEFAULT 0,
            PRIMARY KEY (id),
            UNIQUE KEY store_category (store_id, category_id),
            KEY category_id (category_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

        $this->db->query("CREATE TABLE IF NOT EXISTS store_home_categories (
            store_id INT(11) NOT NULL,
            category_id INT(11) NOT NULL,
            sort_order INT(11) NOT NULL DEFAULT 0,
            PRIMARY KEY (store_id, category_id),
            KEY category_id (category_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

        $this->migrate_columns();
    }

    protected function migrate_columns()
    {
        $cols = array(
            'country_id' => "ALTER TABLE categories ADD COLUMN country_id INT(11) DEFAULT NULL AFTER id",
            'parent_id' => "ALTER TABLE categories ADD COLUMN parent_id INT(11) DEFAULT NULL AFTER country_id",
            'hero_image' => "ALTER TABLE categories ADD COLUMN hero_image VARCHAR(255) NOT NULL DEFAULT '' AFTER image",
            'seo_title' => "ALTER TABLE categories ADD COLUMN seo_title VARCHAR(255) NOT NULL DEFAULT '' AFTER description",
            'seo_description' => "ALTER TABLE categories ADD COLUMN seo_description TEXT NULL AFTER seo_title",
            'seo_keywords' => "ALTER TABLE categories ADD COLUMN seo_keywords VARCHAR(255) NOT NULL DEFAULT '' AFTER seo_description",
            'hero_kicker' => "ALTER TABLE categories ADD COLUMN hero_kicker VARCHAR(120) NOT NULL DEFAULT '' AFTER hero_image",
            'hero_title' => "ALTER TABLE categories ADD COLUMN hero_title VARCHAR(255) NOT NULL DEFAULT '' AFTER hero_kicker",
            'hero_text' => "ALTER TABLE categories ADD COLUMN hero_text TEXT NULL AFTER hero_title",
            'hero_btn_text' => "ALTER TABLE categories ADD COLUMN hero_btn_text VARCHAR(120) NOT NULL DEFAULT '' AFTER hero_text",
            'hero_btn_link' => "ALTER TABLE categories ADD COLUMN hero_btn_link VARCHAR(500) NOT NULL DEFAULT '' AFTER hero_btn_text",
            'hero_disc_small' => "ALTER TABLE categories ADD COLUMN hero_disc_small VARCHAR(40) NOT NULL DEFAULT '' AFTER hero_btn_link",
            'hero_disc_big' => "ALTER TABLE categories ADD COLUMN hero_disc_big VARCHAR(40) NOT NULL DEFAULT '' AFTER hero_disc_small",
            'hero_disc_span' => "ALTER TABLE categories ADD COLUMN hero_disc_span VARCHAR(40) NOT NULL DEFAULT '' AFTER hero_disc_big",
        );
        foreach ($cols as $field => $sql) {
            if (!$this->db->field_exists($field, 'categories')) {
                $this->db->query($sql);
            }
        }

        $scsCols = array(
            'hero_kicker' => "ALTER TABLE store_category_settings ADD COLUMN hero_kicker VARCHAR(120) NOT NULL DEFAULT '' AFTER hero_image",
            'hero_title' => "ALTER TABLE store_category_settings ADD COLUMN hero_title VARCHAR(255) NOT NULL DEFAULT '' AFTER hero_kicker",
            'hero_text' => "ALTER TABLE store_category_settings ADD COLUMN hero_text TEXT NULL AFTER hero_title",
            'hero_btn_text' => "ALTER TABLE store_category_settings ADD COLUMN hero_btn_text VARCHAR(120) NOT NULL DEFAULT '' AFTER hero_text",
            'hero_btn_link' => "ALTER TABLE store_category_settings ADD COLUMN hero_btn_link VARCHAR(500) NOT NULL DEFAULT '' AFTER hero_btn_text",
            'hero_disc_small' => "ALTER TABLE store_category_settings ADD COLUMN hero_disc_small VARCHAR(40) NOT NULL DEFAULT '' AFTER hero_btn_link",
            'hero_disc_big' => "ALTER TABLE store_category_settings ADD COLUMN hero_disc_big VARCHAR(40) NOT NULL DEFAULT '' AFTER hero_disc_small",
            'hero_disc_span' => "ALTER TABLE store_category_settings ADD COLUMN hero_disc_span VARCHAR(40) NOT NULL DEFAULT '' AFTER hero_disc_big",
        );
        foreach ($scsCols as $field => $sql) {
            if (!$this->db->field_exists($field, 'store_category_settings')) {
                $this->db->query($sql);
            }
        }

        // Prefer unique slug per country (drop legacy global slug unique if present).
        $indexes = $this->db->query("SHOW INDEX FROM categories WHERE Key_name = 'slug'")->result();
        if (!empty($indexes)) {
            $this->db->query("ALTER TABLE categories DROP INDEX slug");
        }
        $countrySlug = $this->db->query("SHOW INDEX FROM categories WHERE Key_name = 'country_slug'")->result();
        if (empty($countrySlug)) {
            $this->db->query("ALTER TABLE categories ADD UNIQUE KEY country_slug (country_id, slug)");
        }
    }

    public function all($filters = array())
    {
        $this->ensure_tables();
        $this->db
            ->select('categories.*, countries.name as country_name, parent.name as parent_name')
            ->from('categories')
            ->join('countries', 'countries.id = categories.country_id', 'left')
            ->join('categories parent', 'parent.id = categories.parent_id', 'left')
            ->order_by('categories.country_id', 'asc')
            ->order_by('categories.parent_id', 'asc')
            ->order_by('categories.sort_order', 'asc')
            ->order_by('categories.name', 'asc');
        if (!empty($filters['country_id'])) {
            $this->db->where('categories.country_id', (int) $filters['country_id']);
        }
        if (isset($filters['parent_id'])) {
            if ($filters['parent_id'] === 0 || $filters['parent_id'] === '0') {
                $this->db->group_start()
                    ->where('categories.parent_id IS NULL', null, false)
                    ->or_where('categories.parent_id', 0)
                    ->group_end();
            } else {
                $this->db->where('categories.parent_id', (int) $filters['parent_id']);
            }
        }
        return $this->db->get()->result();
    }

    public function all_active($countryId = 0)
    {
        $this->ensure_tables();
        $this->db->where('status', 1);
        if ($countryId) {
            $this->db->where('country_id', (int) $countryId);
        }
        return $this->db->order_by('sort_order', 'asc')->order_by('name', 'asc')->get('categories')->result();
    }

    public function roots_for_country($countryId)
    {
        $this->ensure_tables();
        return $this->db
            ->where('status', 1)
            ->where('country_id', (int) $countryId)
            ->group_start()
                ->where('parent_id IS NULL', null, false)
                ->or_where('parent_id', 0)
            ->group_end()
            ->order_by('sort_order', 'asc')
            ->order_by('name', 'asc')
            ->get('categories')
            ->result();
    }

    public function children($parentId)
    {
        $this->ensure_tables();
        return $this->db
            ->where('status', 1)
            ->where('parent_id', (int) $parentId)
            ->order_by('sort_order', 'asc')
            ->order_by('name', 'asc')
            ->get('categories')
            ->result();
    }

    public function get($id)
    {
        $this->ensure_tables();
        return $this->db->where('id', (int) $id)->get('categories')->row();
    }

    public function get_by_slug($slug, $countryId = 0)
    {
        $this->ensure_tables();
        $this->db->where('slug', $slug)->where('status', 1);
        if ($countryId) {
            $this->db->where('country_id', (int) $countryId);
        }
        return $this->db->get('categories')->row();
    }

    public function save($data, $id = 0)
    {
        $this->ensure_tables();
        if ($id) {
            $this->db->where('id', (int) $id)->update('categories', $data);
            return (int) $id;
        }
        $this->db->insert('categories', $data);
        return (int) $this->db->insert_id();
    }

    public function delete($id)
    {
        $this->ensure_tables();
        $id = (int) $id;
        $this->db->where('parent_id', $id)->update('categories', array('parent_id' => null));
        $this->db->where('category_id', $id)->delete('product_categories');
        $this->db->where('category_id', $id)->delete('store_category_settings');
        $this->db->where('category_id', $id)->delete('store_home_categories');
        return $this->db->where('id', $id)->delete('categories');
    }

    public function ids_for_product($productId)
    {
        $this->ensure_tables();
        $rows = $this->db->select('category_id')->where('product_id', (int) $productId)->get('product_categories')->result();
        $ids = array();
        foreach ($rows as $row) {
            $ids[] = (int) $row->category_id;
        }
        return $ids;
    }

    public function set_product_categories($productId, $categoryIds)
    {
        $this->ensure_tables();
        $productId = (int) $productId;
        $this->db->where('product_id', $productId)->delete('product_categories');
        $seen = array();
        foreach ((array) $categoryIds as $cid) {
            $cid = (int) $cid;
            if ($cid < 1 || isset($seen[$cid])) {
                continue;
            }
            $seen[$cid] = true;
            $this->db->insert('product_categories', array('product_id' => $productId, 'category_id' => $cid));
        }
    }

    public function store_setting($storeId, $categoryId)
    {
        $this->ensure_tables();
        return $this->db
            ->where('store_id', (int) $storeId)
            ->where('category_id', (int) $categoryId)
            ->get('store_category_settings')
            ->row();
    }

    public function save_store_setting($storeId, $categoryId, $data)
    {
        $this->ensure_tables();
        $existing = $this->store_setting($storeId, $categoryId);
        $data['store_id'] = (int) $storeId;
        $data['category_id'] = (int) $categoryId;
        if ($existing) {
            $this->db->where('id', (int) $existing->id)->update('store_category_settings', $data);
            return (int) $existing->id;
        }
        $this->db->insert('store_category_settings', $data);
        return (int) $this->db->insert_id();
    }

    public function sync_store_selection($storeId, $categoryIds, $homeIds = array())
    {
        $this->ensure_tables();
        $storeId = (int) $storeId;
        $categoryIds = array_map('intval', (array) $categoryIds);
        $homeIds = array_map('intval', (array) $homeIds);
        $homeMap = array_flip($homeIds);

        $existing = $this->db->where('store_id', $storeId)->get('store_category_settings')->result();
        $keep = array();
        foreach ($existing as $row) {
            $cid = (int) $row->category_id;
            if (!in_array($cid, $categoryIds, true)) {
                $this->db->where('id', (int) $row->id)->update('store_category_settings', array(
                    'enabled' => 0,
                    'show_on_home' => 0,
                ));
            } else {
                $keep[$cid] = true;
                $this->db->where('id', (int) $row->id)->update('store_category_settings', array(
                    'enabled' => 1,
                    'show_on_home' => isset($homeMap[$cid]) ? 1 : 0,
                ));
            }
        }

        $sort = 1;
        foreach ($categoryIds as $cid) {
            if ($cid < 1) {
                continue;
            }
            if (isset($keep[$cid])) {
                $this->db->where('store_id', $storeId)->where('category_id', $cid)->update('store_category_settings', array(
                    'sort_order' => $sort++,
                    'show_on_home' => isset($homeMap[$cid]) ? 1 : 0,
                    'enabled' => 1,
                ));
                continue;
            }
            $this->db->insert('store_category_settings', array(
                'store_id' => $storeId,
                'category_id' => $cid,
                'enabled' => 1,
                'show_on_home' => isset($homeMap[$cid]) ? 1 : 0,
                'sort_order' => $sort++,
            ));
        }

        // keep legacy home table in sync
        $this->db->where('store_id', $storeId)->delete('store_home_categories');
        $hs = 1;
        foreach ($homeIds as $cid) {
            if ($cid < 1) {
                continue;
            }
            $this->db->insert('store_home_categories', array(
                'store_id' => $storeId,
                'category_id' => $cid,
                'sort_order' => $hs++,
            ));
        }
    }

    public function for_store($storeId, $countryId, $onlyEnabled = true)
    {
        $this->ensure_tables();
        $this->db
            ->select('categories.*, scs.enabled, scs.show_on_home, scs.image as store_image, scs.hero_image as store_hero_image, scs.seo_title as store_seo_title, scs.seo_description as store_seo_description, scs.seo_keywords as store_seo_keywords, scs.sort_order as store_sort', false)
            ->from('categories')
            ->join('store_category_settings scs', 'scs.category_id = categories.id AND scs.store_id = ' . (int) $storeId, 'left', false)
            ->where('categories.status', 1)
            ->where('categories.country_id', (int) $countryId)
            ->order_by('COALESCE(scs.sort_order, categories.sort_order)', 'asc', false)
            ->order_by('categories.name', 'asc');
        if ($onlyEnabled) {
            $this->db->where('scs.enabled', 1);
        }
        return $this->db->get()->result();
    }

    public function home_for_store($storeId)
    {
        $this->ensure_tables();
        $rows = $this->db
            ->select('categories.*, scs.image as store_image, scs.hero_image as store_hero_image, scs.sort_order as home_sort', false)
            ->from('store_category_settings scs')
            ->join('categories', 'categories.id = scs.category_id')
            ->where('scs.store_id', (int) $storeId)
            ->where('scs.enabled', 1)
            ->where('scs.show_on_home', 1)
            ->where('categories.status', 1)
            ->group_start()
                ->where('categories.parent_id IS NULL', null, false)
                ->or_where('categories.parent_id', 0)
            ->group_end()
            ->order_by('scs.sort_order', 'asc')
            ->get()
            ->result();

        if (!empty($rows)) {
            return $rows;
        }

        // fallback legacy
        return $this->db
            ->select('categories.*, store_home_categories.sort_order as home_sort')
            ->from('store_home_categories')
            ->join('categories', 'categories.id = store_home_categories.category_id')
            ->where('store_home_categories.store_id', (int) $storeId)
            ->where('categories.status', 1)
            ->order_by('store_home_categories.sort_order', 'asc')
            ->get()
            ->result();
    }

    public function home_ids_for_store($storeId)
    {
        $ids = array();
        foreach ($this->home_for_store($storeId) as $row) {
            $ids[] = (int) $row->id;
        }
        return $ids;
    }

    public function resolve_for_store($category, $storeId)
    {
        if (!$category) {
            return null;
        }
        $setting = $this->store_setting($storeId, $category->id);
        $category->display_image = ($setting && trim((string) $setting->image) !== '') ? $setting->image : $category->image;
        $category->display_hero = ($setting && trim((string) $setting->hero_image) !== '') ? $setting->hero_image : $category->hero_image;
        $storeSeoTitle = $setting ? trim((string) $setting->seo_title) : '';
        $storeSeoDesc = $setting ? trim((string) $setting->seo_description) : '';
        $storeSeoKeys = $setting ? trim((string) $setting->seo_keywords) : '';
        $category->display_seo_title = $storeSeoTitle !== ''
            ? $storeSeoTitle
            : (trim((string) $category->seo_title) !== '' ? $category->seo_title : $category->name);
        $category->display_seo_description = $storeSeoDesc !== ''
            ? $storeSeoDesc
            : (trim((string) $category->seo_description) !== '' ? $category->seo_description : (string) $category->description);
        $category->display_seo_keywords = $storeSeoKeys !== ''
            ? $storeSeoKeys
            : (string) $category->seo_keywords;

        $pick = function ($storeVal, $baseVal, $fallback = '') {
            $storeVal = trim((string) $storeVal);
            if ($storeVal !== '') {
                return $storeVal;
            }
            $baseVal = trim((string) $baseVal);
            return $baseVal !== '' ? $baseVal : $fallback;
        };
        $category->display_hero_kicker = $pick(
            $setting ? $setting->hero_kicker : '',
            isset($category->hero_kicker) ? $category->hero_kicker : '',
            'Shop category'
        );
        $category->display_hero_title = $pick(
            $setting ? $setting->hero_title : '',
            isset($category->hero_title) ? $category->hero_title : '',
            $category->display_seo_title
        );
        $category->display_hero_text = $pick(
            $setting ? $setting->hero_text : '',
            isset($category->hero_text) ? $category->hero_text : '',
            $category->display_seo_description
        );
        $category->display_hero_btn_text = $pick(
            $setting ? $setting->hero_btn_text : '',
            isset($category->hero_btn_text) ? $category->hero_btn_text : '',
            'Shop Now'
        );
        $category->display_hero_btn_link = $pick(
            $setting ? $setting->hero_btn_link : '',
            isset($category->hero_btn_link) ? $category->hero_btn_link : '',
            ''
        );
        $category->display_hero_disc_small = $pick(
            $setting && isset($setting->hero_disc_small) ? $setting->hero_disc_small : '',
            isset($category->hero_disc_small) ? $category->hero_disc_small : '',
            'UP TO'
        );
        $category->display_hero_disc_big = $pick(
            $setting && isset($setting->hero_disc_big) ? $setting->hero_disc_big : '',
            isset($category->hero_disc_big) ? $category->hero_disc_big : '',
            '50%'
        );
        $category->display_hero_disc_span = $pick(
            $setting && isset($setting->hero_disc_span) ? $setting->hero_disc_span : '',
            isset($category->hero_disc_span) ? $category->hero_disc_span : '',
            'OFF'
        );

        $category->store_enabled = $setting ? (int) $setting->enabled : 0;
        $category->show_on_home = $setting ? (int) $setting->show_on_home : 0;
        return $category;
    }

    public function with_counts_for_store($storeId, $countryId = 0)
    {
        $this->ensure_tables();
        $this->db
            ->select('categories.*, COUNT(DISTINCT products.id) as product_count, scs.enabled, scs.show_on_home, scs.image as store_image', false)
            ->from('categories')
            ->join('product_categories', 'product_categories.category_id = categories.id', 'left')
            ->join('products', 'products.id = product_categories.product_id AND products.store_id = ' . (int) $storeId . ' AND products.status = 1', 'left', false)
            ->join('store_category_settings scs', 'scs.category_id = categories.id AND scs.store_id = ' . (int) $storeId, 'left', false)
            ->where('categories.status', 1)
            ->group_by('categories.id')
            ->order_by('categories.sort_order', 'asc')
            ->order_by('categories.name', 'asc');
        if ($countryId) {
            $this->db->where('categories.country_id', (int) $countryId);
        }
        return $this->db->get()->result();
    }

    public function category_product_ids($categoryId, $includeChildren = true)
    {
        $ids = array((int) $categoryId);
        if ($includeChildren) {
            foreach ($this->children($categoryId) as $child) {
                $ids[] = (int) $child->id;
            }
        }
        return $ids;
    }

    public function name_key($name)
    {
        $name = trim((string) $name);
        if (function_exists('mb_strtolower')) {
            return mb_strtolower($name, 'UTF-8');
        }
        return strtolower($name);
    }

    public function find_or_create($countryId, $name, $parentId = 0)
    {
        $name = trim((string) $name);
        $countryId = (int) $countryId;
        if ($name === '' || $countryId < 1) {
            return 0;
        }
        $existing = $this->find_by_name($countryId, $name, $parentId);
        if ($existing) {
            return (int) $existing->id;
        }
        $usedSlugs = array();
        return $this->save(array(
            'country_id' => $countryId,
            'parent_id' => $parentId ? (int) $parentId : null,
            'name' => $name,
            'slug' => $this->unique_slug($countryId, $name, $usedSlugs),
            'status' => 1,
            'sort_order' => 0,
        ));
    }

    public function find_by_name($countryId, $name, $parentId = 0)
    {
        $this->ensure_tables();
        $nameKey = $this->name_key($name);
        if ($nameKey === '') {
            return null;
        }
        $this->db->where('country_id', (int) $countryId);
        $this->db->where('LOWER(TRIM(name)) = ' . $this->db->escape($nameKey), null, false);
        if ($parentId === 0 || $parentId === null) {
            $this->db->group_start()
                ->where('parent_id IS NULL', null, false)
                ->or_where('parent_id', 0)
                ->group_end();
        } else {
            $this->db->where('parent_id', (int) $parentId);
        }
        return $this->db->limit(1)->get('categories')->row();
    }

    public function unique_slug($countryId, $name, &$usedSlugs = array())
    {
        $slug = url_title($name, 'dash', true);
        if ($slug === '') {
            $slug = 'category';
        }
        $base = $slug;
        $n = 0;
        while (isset($usedSlugs[$slug]) || $this->slug_exists($countryId, $slug)) {
            $n++;
            $slug = $base . '-' . $n;
        }
        $usedSlugs[$slug] = true;
        return $slug;
    }

    protected function slug_exists($countryId, $slug)
    {
        $this->ensure_tables();
        $row = $this->db
            ->select('id')
            ->where('country_id', (int) $countryId)
            ->where('slug', $slug)
            ->limit(1)
            ->get('categories')
            ->row();
        return !empty($row);
    }

    public function plan_csv_import($rows, $format = 'full', $countryId = 0)
    {
        if ($format === 'name_parent') {
            return $this->plan_name_parent_import((int) $countryId, $rows);
        }
        return $this->plan_full_import($rows);
    }

    public function execute_csv_import($plan)
    {
        if (empty($plan['ok'])) {
            return $plan;
        }
        if (empty($plan['creates']) || (int) $plan['imported'] < 1) {
            return $plan;
        }

        $this->ensure_tables();
        $this->db->trans_begin();
        $tempIds = array();
        foreach ($plan['creates'] as $payload) {
            if (!empty($payload['_parent_key'])) {
                $parentKey = $payload['_parent_key'];
                if (empty($payload['parent_id']) && isset($tempIds[$parentKey])) {
                    $payload['parent_id'] = $tempIds[$parentKey];
                }
                if (empty($payload['parent_id'])) {
                    $this->db->trans_rollback();
                    return array(
                        'ok' => false,
                        'error' => 'Import failed because parent "' . $parentKey . '" could not be resolved. No categories were changed.',
                    );
                }
            }
            $tempKey = isset($payload['_temp_key']) ? $payload['_temp_key'] : '';
            unset($payload['_temp_key'], $payload['_parent_key'], $payload['_image_url'], $payload['_hero_url']);
            $id = $this->save($payload);
            if (!$id) {
                $this->db->trans_rollback();
                $name = isset($payload['name']) ? $payload['name'] : '';
                return array(
                    'ok' => false,
                    'error' => 'Import failed while creating "' . $name . '". No categories were changed.',
                );
            }
            if ($tempKey !== '') {
                $tempIds[$tempKey] = $id;
            }
        }
        if ($this->db->trans_status() === false) {
            $this->db->trans_rollback();
            return array(
                'ok' => false,
                'error' => 'Import failed and was rolled back. No categories were changed.',
            );
        }
        $this->db->trans_commit();
        return $plan;
    }

    protected function plan_full_import($rows)
    {
        $this->ensure_tables();
        $countries = array();
        foreach ($this->db->select('id, name')->get('countries')->result() as $country) {
            $countries[(int) $country->id] = $country->name;
        }

        $existing = $this->all(array());
        $roots = array();
        $dupName = array();
        $usedSlugs = array();
        foreach ($existing as $cat) {
            $cid = (int) $cat->country_id;
            $pid = (int) $cat->parent_id;
            $nameKey = $this->name_key($cat->name);
            if ($pid === 0) {
                $roots[$cid][$nameKey] = $cat;
            }
            $dupName[$this->dup_key($cid, $pid, $nameKey)] = (int) $cat->id;
            if (!isset($usedSlugs[$cid])) {
                $usedSlugs[$cid] = array();
            }
            $usedSlugs[$cid][$cat->slug] = true;
        }

        $plannedRoots = array();
        $creates = array();
        $preview = array();
        $imported = 0;
        $skipped = 0;
        $failed = 0;

        $ensureRoot = function ($countryId, $parentName) use (
            &$roots, &$plannedRoots, &$creates, &$dupName, &$usedSlugs, &$imported
        ) {
            $parentName = trim((string) $parentName);
            if ($parentName === '') {
                return array('id' => 0, 'key' => '', 'created' => false);
            }
            $key = $this->name_key($parentName);
            $tempKey = $countryId . ':' . $key;
            if (isset($roots[$countryId][$key])) {
                return array('id' => (int) $roots[$countryId][$key]->id, 'key' => $tempKey, 'created' => false);
            }
            if (isset($plannedRoots[$tempKey])) {
                return array('id' => 0, 'key' => $tempKey, 'created' => false);
            }
            if (!isset($usedSlugs[$countryId])) {
                $usedSlugs[$countryId] = array();
            }
            $plannedRoots[$tempKey] = true;
            $dupName[$this->dup_key($countryId, 0, $key)] = true;
            $creates[] = array(
                'country_id' => $countryId,
                'parent_id' => null,
                'name' => $parentName,
                'slug' => $this->unique_slug($countryId, $parentName, $usedSlugs[$countryId]),
                'icon' => '',
                'image' => '',
                'hero_image' => '',
                'status' => 1,
                'sort_order' => 0,
                '_temp_key' => $tempKey,
            );
            $imported++;
            return array('id' => 0, 'key' => $tempKey, 'created' => true, 'name' => $parentName, 'country_id' => $countryId);
        };

        foreach ((array) $rows as $row) {
            $line = isset($row['line']) ? (int) $row['line'] : 0;
            $countryId = (int) (isset($row['country_id']) ? $row['country_id'] : 0);
            $parentName = trim((string) (isset($row['parent']) ? $row['parent'] : ''));
            $name = trim((string) (isset($row['name']) ? $row['name'] : ''));
            $slugIn = trim((string) (isset($row['slug']) ? $row['slug'] : ''));
            $icon = trim((string) (isset($row['icon']) ? $row['icon'] : ''));
            $imageUrl = trim((string) (isset($row['category_image_url']) ? $row['category_image_url'] : ''));
            $heroUrl = trim((string) (isset($row['default_hero_image_url']) ? $row['default_hero_image_url'] : ''));
            $heroKicker = trim((string) (isset($row['hero_kicker']) ? $row['hero_kicker'] : ''));
            $heroTitle = trim((string) (isset($row['hero_title']) ? $row['hero_title'] : ''));
            $heroText = trim((string) (isset($row['hero_text']) ? $row['hero_text'] : ''));

            $item = array(
                'line' => $line,
                'country_id' => $countryId,
                'name' => $name,
                'slug' => $slugIn,
                'parent' => $parentName !== '' ? $parentName : '—',
                'status' => 'import',
                'message' => '',
            );

            $error = $this->validate_import_row($countries, $name, $countryId, $parentName, $slugIn, $icon, $imageUrl, $heroUrl, $heroKicker, $heroTitle);
            if ($error) {
                $failed++;
                $item['status'] = 'fail';
                $item['message'] = $error;
                $preview[] = $item;
                continue;
            }

            $parentInfo = array('id' => 0, 'key' => '', 'created' => false);
            if ($parentName !== '') {
                $parentInfo = $ensureRoot($countryId, $parentName);
                if (!empty($parentInfo['created'])) {
                    $preview[] = array(
                        'line' => $line,
                        'country_id' => $countryId,
                        'name' => $parentName,
                        'slug' => '',
                        'parent' => '—',
                        'status' => 'import',
                        'message' => 'Parent will be created as a top-level category.',
                    );
                }
            }

            $parentId = (int) $parentInfo['id'];
            $nameKey = $this->name_key($name);
            $dupParentId = $parentId;
            if ($parentName !== '' && $parentId < 1) {
                $dupParentId = 'new:' . $this->name_key($parentName);
            }
            $nameDupKey = $this->dup_key($countryId, $dupParentId, $nameKey);
            if (isset($dupName[$nameDupKey]) || isset($dupName[$this->dup_key($countryId, $parentId, $nameKey)])) {
                $skipped++;
                $item['status'] = 'duplicate';
                $item['message'] = 'Skipped: same name already exists for this country and parent.';
                $preview[] = $item;
                continue;
            }

            $slug = $slugIn !== '' ? url_title($slugIn, 'dash', true) : url_title($name, 'dash', true);
            if ($slug === '') {
                $slug = 'category';
            }
            if (!isset($usedSlugs[$countryId])) {
                $usedSlugs[$countryId] = array();
            }
            $slug = $this->unique_slug($countryId, $slug, $usedSlugs[$countryId]);
            $dupName[$nameDupKey] = true;
            if ($parentName === '') {
                $plannedRoots[$countryId . ':' . $nameKey] = true;
                $roots[$countryId][$nameKey] = (object) array('id' => 0, 'name' => $name);
            }

            $payload = array(
                'country_id' => $countryId,
                'parent_id' => $parentId ? $parentId : null,
                'name' => $name,
                'slug' => $slug,
                'icon' => $icon,
                'image' => '',
                'hero_image' => '',
                'hero_kicker' => $heroKicker,
                'hero_title' => $heroTitle,
                'hero_text' => $heroText,
                'status' => 1,
                'sort_order' => 0,
                '_image_url' => $imageUrl,
                '_hero_url' => $heroUrl,
            );
            if ($parentName === '') {
                $payload['_temp_key'] = $countryId . ':' . $nameKey;
            } elseif ($parentId < 1) {
                $payload['_parent_key'] = $parentInfo['key'];
            }

            $creates[] = $payload;
            $imported++;
            $item['slug'] = $slug;
            if ($parentName === '') {
                $item['message'] = 'Will be created as a top-level category.';
            } elseif ($parentId) {
                $item['message'] = 'Will be created under existing parent "' . $parentName . '" (#' . $parentId . ').';
            } else {
                $item['message'] = 'Will be created under parent "' . $parentName . '".';
            }
            $preview[] = $item;
        }

        $ordered = array();
        foreach ($creates as $payload) {
            if (empty($payload['parent_id']) && empty($payload['_parent_key'])) {
                $ordered[] = $payload;
            }
        }
        foreach ($creates as $payload) {
            if (!empty($payload['parent_id']) || !empty($payload['_parent_key'])) {
                $ordered[] = $payload;
            }
        }

        return array(
            'ok' => true,
            'imported' => $imported,
            'skipped' => $skipped,
            'failed' => $failed,
            'rows' => $preview,
            'creates' => $ordered,
        );
    }

    protected function validate_import_row($countries, $name, $countryId, $parentName, $slugIn, $icon, $imageUrl, $heroUrl, $heroKicker, $heroTitle)
    {
        if ($countryId < 1) {
            return 'country_id is required and must be a database ID.';
        }
        if (!isset($countries[$countryId])) {
            return 'country_id ' . $countryId . ' does not exist.';
        }
        if ($name === '') {
            return 'Name is required.';
        }
        if (strlen($name) > 150) {
            return 'Name is too long (max 150 characters).';
        }
        if ($parentName !== '' && strlen($parentName) > 150) {
            return 'Parent name is too long (max 150 characters).';
        }
        if ($slugIn !== '' && strlen(url_title($slugIn, 'dash', true)) > 160) {
            return 'Slug is too long (max 160 characters).';
        }
        if (strlen($icon) > 20) {
            return 'Icon is too long (max 20 characters).';
        }
        if (strlen($heroKicker) > 120) {
            return 'Hero kicker is too long (max 120 characters).';
        }
        if (strlen($heroTitle) > 255) {
            return 'Hero title is too long (max 255 characters).';
        }
        if ($imageUrl !== '' && !preg_match('#^https?://#i', $imageUrl)) {
            return 'category_image_url must be an http(s) URL.';
        }
        if ($heroUrl !== '' && !preg_match('#^https?://#i', $heroUrl)) {
            return 'default_hero_image_url must be an http(s) URL.';
        }
        return '';
    }

    protected function dup_key($countryId, $parentId, $valueKey)
    {
        return (int) $countryId . '|' . $parentId . '|' . $valueKey;
    }

    protected function plan_name_parent_import($countryId, $rows)
    {
        $countryId = (int) $countryId;
        $existing = $this->all(array('country_id' => $countryId));
        $rootByKey = array();
        $childByKey = array();
        $usedSlugs = array();
        foreach ($existing as $cat) {
            $key = $this->name_key($cat->name);
            $pid = (int) $cat->parent_id;
            if ($pid === 0) {
                $rootByKey[$key] = $cat;
            }
            $childByKey[$pid . '|' . $key] = $cat;
            $usedSlugs[$cat->slug] = true;
        }

        $newRoots = array();
        $newChildren = array();
        $creates = array();
        $preview = array();
        $imported = 0;
        $skipped = 0;
        $failed = 0;

        foreach ((array) $rows as $row) {
            $line = isset($row['line']) ? (int) $row['line'] : 0;
            $name = trim((string) (isset($row['name']) ? $row['name'] : ''));
            $parent = trim((string) (isset($row['parent']) ? $row['parent'] : ''));
            $item = array(
                'line' => $line,
                'country_id' => $countryId,
                'parent_id' => '',
                'name' => $name,
                'slug' => '',
                'parent' => $parent !== '' ? $parent : '—',
                'status' => 'import',
                'message' => '',
            );

            if ($name === '') {
                $failed++;
                $item['status'] = 'fail';
                $item['message'] = 'Name is required.';
                $preview[] = $item;
                continue;
            }
            if (strlen($name) > 150) {
                $failed++;
                $item['status'] = 'fail';
                $item['message'] = 'Name is too long (max 150 characters).';
                $preview[] = $item;
                continue;
            }
            if ($parent !== '' && strlen($parent) > 150) {
                $failed++;
                $item['status'] = 'fail';
                $item['message'] = 'Parent is too long (max 150 characters).';
                $preview[] = $item;
                continue;
            }

            $nameKey = $this->name_key($name);
            $parentKey = $parent === '' ? '' : $this->name_key($parent);
            $parentNote = '';

            if ($parentKey !== '') {
                if (!isset($rootByKey[$parentKey]) && !isset($newRoots[$parentKey])) {
                    $newRoots[$parentKey] = $parent;
                    $imported++;
                    $parentNote = 'Parent will be created.';
                } else {
                    $parentNote = 'Parent exists.';
                }
            }

            if ($parentKey === '') {
                if (isset($rootByKey[$nameKey]) || isset($newRoots[$nameKey])) {
                    $skipped++;
                    $item['status'] = 'duplicate';
                    $item['message'] = 'Skipped: category already exists as a top-level parent.';
                    $preview[] = $item;
                    continue;
                }
                $newRoots[$nameKey] = $name;
                $imported++;
                $item['message'] = 'Will be created as a top-level category.';
                $preview[] = $item;
                continue;
            }

            $parentId = isset($rootByKey[$parentKey]) ? (int) $rootByKey[$parentKey]->id : 0;
            $plannedChildKey = $parentKey . '|' . $nameKey;
            $exists = ($parentId && isset($childByKey[$parentId . '|' . $nameKey]))
                || isset($newChildren[$plannedChildKey]);

            if ($exists) {
                $skipped++;
                $item['status'] = 'duplicate';
                $item['message'] = 'Skipped: same Name already exists under this Parent.';
                $preview[] = $item;
                continue;
            }

            $newChildren[$plannedChildKey] = array(
                'name' => $name,
                'parent' => $parent,
                'parent_key' => $parentKey,
            );
            $imported++;
            $item['message'] = trim($parentNote . ' Child category will be created.');
            $preview[] = $item;
        }

        foreach ($newRoots as $key => $rootName) {
            if (isset($rootByKey[$key])) {
                continue;
            }
            $creates[] = array(
                'country_id' => $countryId,
                'parent_id' => null,
                'name' => $rootName,
                'slug' => $this->unique_slug($countryId, $rootName, $usedSlugs),
                'icon' => '',
                'image' => '',
                'hero_image' => '',
                'status' => 1,
                'sort_order' => 0,
                '_temp_key' => $key,
            );
        }
        foreach ($newChildren as $child) {
            $creates[] = array(
                'country_id' => $countryId,
                'parent_id' => isset($rootByKey[$child['parent_key']]) ? (int) $rootByKey[$child['parent_key']]->id : null,
                'name' => $child['name'],
                'slug' => $this->unique_slug($countryId, $child['name'], $usedSlugs),
                'icon' => '',
                'image' => '',
                'hero_image' => '',
                'status' => 1,
                'sort_order' => 0,
                '_parent_key' => $child['parent_key'],
            );
        }

        return array(
            'ok' => true,
            'imported' => $imported,
            'skipped' => $skipped,
            'failed' => $failed,
            'rows' => $preview,
            'creates' => $creates,
            'resolve_parents' => true,
        );
    }
}
