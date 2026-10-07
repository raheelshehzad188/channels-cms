<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Store_page_model extends CI_Model {

    public function ensure_tables()
    {
        if ($this->db->table_exists('store_pages')) {
            return;
        }
        $this->db->query("CREATE TABLE IF NOT EXISTS `store_pages` (
            `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
            `store_id` int(10) unsigned NOT NULL,
            `slug` varchar(180) NOT NULL DEFAULT '',
            `title` varchar(255) NOT NULL DEFAULT '',
            `detail` mediumtext DEFAULT NULL,
            `meta_description` varchar(320) NOT NULL DEFAULT '',
            `status` tinyint(1) NOT NULL DEFAULT 1,
            `show_in_nav` tinyint(1) NOT NULL DEFAULT 0,
            `show_in_footer` tinyint(1) NOT NULL DEFAULT 1,
            `sort_order` int(11) NOT NULL DEFAULT 0,
            `created_at` datetime NOT NULL DEFAULT current_timestamp(),
            `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
            PRIMARY KEY (`id`),
            UNIQUE KEY `store_slug` (`store_id`,`slug`),
            KEY `store_status_sort` (`store_id`,`status`,`sort_order`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    }

    public function reserved_slugs()
    {
        return array(
            'contact', 'shop', 'index', 'home', 'cart', 'checkout', 'payment',
            'account', 'login', 'signup', 'product', 'category', 'page', 'pages',
            'wishlist', 'thanks', 'order', 'tracking', 'search',
        );
    }

    public function all($storeId, $filters = array())
    {
        if (!$this->db->table_exists('store_pages')) {
            return array();
        }
        $this->db->where('store_id', (int) $storeId);
        $q = isset($filters['q']) ? trim((string) $filters['q']) : '';
        if ($q !== '') {
            $this->db->group_start()
                ->like('title', $q)
                ->or_like('slug', $q)
                ->or_like('detail', $q)
                ->group_end();
        }
        if (isset($filters['status']) && $filters['status'] !== '' && $filters['status'] !== null) {
            $this->db->where('status', (int) $filters['status']);
        }
        return $this->db
            ->order_by('sort_order', 'asc')
            ->order_by('id', 'desc')
            ->get('store_pages')
            ->result();
    }

    public function get_owned($storeId, $id)
    {
        if (!$this->db->table_exists('store_pages')) {
            return null;
        }
        return $this->db
            ->where('store_id', (int) $storeId)
            ->where('id', (int) $id)
            ->get('store_pages')
            ->row();
    }

    public function get_published_by_slug($storeId, $slug)
    {
        if (!$this->db->table_exists('store_pages')) {
            return null;
        }
        $candidates = function_exists('ec_slug_candidates') ? ec_slug_candidates($slug) : array($slug);
        if (empty($candidates)) {
            return null;
        }
        return $this->db
            ->where('store_id', (int) $storeId)
            ->where('status', 1)
            ->where_in('slug', $candidates)
            ->get('store_pages')
            ->row();
    }

    public function get_by_slug($storeId, $slug)
    {
        if (!$this->db->table_exists('store_pages')) {
            return null;
        }
        return $this->db
            ->where('store_id', (int) $storeId)
            ->where('slug', (string) $slug)
            ->get('store_pages')
            ->row();
    }

    public function upsert_by_slug($storeId, $data)
    {
        $this->ensure_tables();
        $slug = trim(isset($data['slug']) ? (string) $data['slug'] : '');
        $slug = function_exists('ec_ascii_slug') ? ec_ascii_slug($slug, 'page') : strtolower(preg_replace('/[^a-z0-9]+/', '-', $slug));
        $existing = $this->get_by_slug($storeId, $slug);
        return $this->save($storeId, $data, $existing ? (int) $existing->id : 0);
    }

    public function published_for($storeId, $placement = '')
    {
        if (!$this->db->table_exists('store_pages')) {
            return array();
        }
        $this->db->where('store_id', (int) $storeId)->where('status', 1);
        if ($placement === 'nav') {
            $this->db->where('show_in_nav', 1);
        } elseif ($placement === 'footer') {
            $this->db->where('show_in_footer', 1);
        }
        return $this->db
            ->order_by('sort_order', 'asc')
            ->order_by('title', 'asc')
            ->get('store_pages')
            ->result();
    }

    public function save($storeId, $data, $id = 0)
    {
        $this->ensure_tables();
        $storeId = (int) $storeId;
        $title = trim(isset($data['title']) ? (string) $data['title'] : '');
        $slug = trim(isset($data['slug']) ? (string) $data['slug'] : '');
        if ($slug === '') {
            $slug = function_exists('ec_ascii_slug') ? ec_ascii_slug($title, 'page') : strtolower(preg_replace('/[^a-z0-9]+/', '-', $title));
        } else {
            $slug = function_exists('ec_ascii_slug') ? ec_ascii_slug($slug, 'page') : strtolower(preg_replace('/[^a-z0-9]+/', '-', $slug));
        }
        if (in_array($slug, $this->reserved_slugs(), true)) {
            return false;
        }
        $slug = $this->unique_slug($storeId, $slug, (int) $id);
        $payload = array(
            'store_id' => $storeId,
            'slug' => $slug,
            'title' => $title,
            'detail' => isset($data['detail']) ? (string) $data['detail'] : '',
            'meta_description' => isset($data['meta_description']) ? substr(trim((string) $data['meta_description']), 0, 320) : '',
            'status' => !empty($data['status']) ? 1 : 0,
            'show_in_nav' => !empty($data['show_in_nav']) ? 1 : 0,
            'show_in_footer' => !empty($data['show_in_footer']) ? 1 : 0,
            'sort_order' => isset($data['sort_order']) ? (int) $data['sort_order'] : 0,
        );
        if ($id) {
            unset($payload['store_id']);
            $this->db->where('id', (int) $id)->where('store_id', $storeId)->update('store_pages', $payload);
            return (int) $id;
        }
        $this->db->insert('store_pages', $payload);
        return (int) $this->db->insert_id();
    }

    public function set_status($storeId, $id, $status)
    {
        if (!$this->db->table_exists('store_pages')) {
            return false;
        }
        return $this->db
            ->where('store_id', (int) $storeId)
            ->where('id', (int) $id)
            ->update('store_pages', array('status' => (int) $status ? 1 : 0));
    }

    public function delete_owned($storeId, $id)
    {
        if (!$this->db->table_exists('store_pages')) {
            return false;
        }
        $this->db->where('store_id', (int) $storeId)->where('id', (int) $id)->delete('store_pages');
        return $this->db->affected_rows() > 0;
    }

    public function last_error_reserved($slug)
    {
        $slug = function_exists('ec_ascii_slug') ? ec_ascii_slug($slug, '') : strtolower((string) $slug);
        return in_array($slug, $this->reserved_slugs(), true);
    }

    protected function unique_slug($storeId, $slug, $ignoreId = 0)
    {
        $base = $slug !== '' ? $slug : 'page';
        $try = $base;
        $n = 2;
        while ($this->slug_taken($storeId, $try, $ignoreId)) {
            $try = $base . '-' . $n;
            $n++;
            if ($n > 80) {
                $try = $base . '-' . substr(md5(uniqid('', true)), 0, 6);
                break;
            }
        }
        return $try;
    }

    protected function slug_taken($storeId, $slug, $ignoreId = 0)
    {
        $this->db->where('store_id', (int) $storeId)->where('slug', $slug);
        if ($ignoreId) {
            $this->db->where('id !=', (int) $ignoreId);
        }
        return $this->db->count_all_results('store_pages') > 0;
    }
}
