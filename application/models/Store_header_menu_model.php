<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Store_header_menu_model extends CI_Model {

    public function ensure_tables()
    {
        if (!$this->db->table_exists('store_header_menu')) {
            $this->db->query("CREATE TABLE IF NOT EXISTS `store_header_menu` (
            `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
            `store_id` int(10) unsigned NOT NULL,
            `label` varchar(120) NOT NULL DEFAULT '',
            `label_en` varchar(120) NOT NULL DEFAULT '',
            `item_type` varchar(20) NOT NULL DEFAULT 'custom',
            `page_id` int(10) unsigned NOT NULL DEFAULT 0,
            `slug` varchar(255) NOT NULL DEFAULT '',
            `sort_order` int(11) NOT NULL DEFAULT 0,
            `status` tinyint(1) NOT NULL DEFAULT 1,
            `created_at` datetime NOT NULL DEFAULT current_timestamp(),
            PRIMARY KEY (`id`),
            KEY `store_sort` (`store_id`,`status`,`sort_order`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
        }
        if (function_exists('ensure_header_menu_i18n_columns')) {
            ensure_header_menu_i18n_columns();
        }
        if (function_exists('ensure_header_menu_english_labels')) {
            ensure_header_menu_english_labels();
        }
    }

    public function all_for_store($storeId)
    {
        $this->ensure_tables();
        return $this->db
            ->where('store_id', (int) $storeId)
            ->order_by('sort_order', 'asc')
            ->order_by('id', 'asc')
            ->get('store_header_menu')
            ->result();
    }

    public function get_owned($storeId, $id)
    {
        $this->ensure_tables();
        return $this->db
            ->where('store_id', (int) $storeId)
            ->where('id', (int) $id)
            ->get('store_header_menu')
            ->row();
    }

    public function next_sort($storeId)
    {
        $row = $this->db
            ->select_max('sort_order')
            ->where('store_id', (int) $storeId)
            ->get('store_header_menu')
            ->row();
        return $row && $row->sort_order !== null ? ((int) $row->sort_order + 10) : 10;
    }

    public function save($storeId, $data, $id = 0)
    {
        $this->ensure_tables();
        $payload = array(
            'label' => isset($data['label']) ? substr(trim((string) $data['label']), 0, 120) : '',
            'item_type' => (!empty($data['item_type']) && $data['item_type'] === 'page') ? 'page' : 'custom',
            'page_id' => isset($data['page_id']) ? (int) $data['page_id'] : 0,
            'slug' => isset($data['slug']) ? substr(trim((string) $data['slug']), 0, 255) : '',
            'sort_order' => isset($data['sort_order']) ? (int) $data['sort_order'] : 0,
            'status' => !empty($data['status']) ? 1 : 0,
        );
        if ($this->db->field_exists('label_en', 'store_header_menu')) {
            $payload['label_en'] = isset($data['label_en']) ? substr(trim((string) $data['label_en']), 0, 120) : '';
        }
        if ($id) {
            $this->db->where('id', (int) $id)->where('store_id', (int) $storeId)->update('store_header_menu', $payload);
            return (int) $id;
        }
        $payload['store_id'] = (int) $storeId;
        if ($payload['sort_order'] <= 0) {
            $payload['sort_order'] = $this->next_sort($storeId);
        }
        $this->db->insert('store_header_menu', $payload);
        return (int) $this->db->insert_id();
    }

    public function delete_owned($storeId, $id)
    {
        $this->ensure_tables();
        $this->db->where('store_id', (int) $storeId)->where('id', (int) $id)->delete('store_header_menu');
        return $this->db->affected_rows() > 0;
    }

    public function reorder($storeId, $ids)
    {
        $this->ensure_tables();
        $sort = 10;
        foreach ($ids as $id) {
            $id = (int) $id;
            if ($id < 1) {
                continue;
            }
            $this->db
                ->where('store_id', (int) $storeId)
                ->where('id', $id)
                ->update('store_header_menu', array('sort_order' => $sort));
            $sort += 10;
        }
        return true;
    }

    public function seed_from_nav_pages($storeId)
    {
        $this->ensure_tables();
        if ($this->db->where('store_id', (int) $storeId)->count_all_results('store_header_menu') > 0) {
            return;
        }
        if (!$this->db->table_exists('store_pages')) {
            return;
        }
        $pages = $this->db
            ->where('store_id', (int) $storeId)
            ->where('status', 1)
            ->where('show_in_nav', 1)
            ->order_by('sort_order', 'asc')
            ->order_by('title', 'asc')
            ->get('store_pages')
            ->result();
        $sort = 10;
        foreach ($pages as $page) {
            $this->db->insert('store_header_menu', array(
                'store_id' => (int) $storeId,
                'label' => $page->title,
                'item_type' => 'page',
                'page_id' => (int) $page->id,
                'slug' => $page->slug,
                'sort_order' => $sort,
                'status' => 1,
            ));
            $sort += 10;
        }
    }

    public function active_resolved($storeId)
    {
        $this->ensure_tables();
        $items = $this->all_for_store($storeId);
        $pageIds = array();
        foreach ($items as $item) {
            if ($item->item_type === 'page' && (int) $item->page_id > 0) {
                $pageIds[] = (int) $item->page_id;
            }
        }
        $pages = array();
        if ($pageIds && $this->db->table_exists('store_pages')) {
            $rows = $this->db
                ->where('store_id', (int) $storeId)
                ->where_in('id', $pageIds)
                ->get('store_pages')
                ->result();
            foreach ($rows as $page) {
                $pages[(int) $page->id] = $page;
            }
        }
        $out = array();
        foreach ($items as $item) {
            if ((int) $item->status !== 1) {
                continue;
            }
            if ($item->item_type === 'page') {
                $page = isset($pages[(int) $item->page_id]) ? $pages[(int) $item->page_id] : null;
                if (!$page || (int) $page->status !== 1) {
                    continue;
                }
                $item->page_slug = $page->slug;
                if (trim($item->label) === '') {
                    $item->label = $page->title;
                }
                $item->slug = $page->slug;
            } else {
                $item->page_slug = '';
                if (trim($item->label) === '' || trim($item->slug) === '') {
                    continue;
                }
            }
            $out[] = $item;
        }
        return $out;
    }
}
