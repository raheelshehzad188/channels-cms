<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Flush_data extends CI_Controller {

    public function __construct()
    {
        parent::__construct();
        ec_require_admin();
    }

    public function index()
    {
        $data = array(
            'title' => 'Flush Data',
            'groups' => $this->flush_groups(),
        );
        $this->template->admin('flush_data/index', $data);
    }

    public function submit()
    {
        $selected = $this->input->post('flush');
        if (!is_array($selected) || empty($selected)) {
            $this->session->set_flashdata('error', 'Select at least one item to flush.');
            redirect('/admin/flush-data');
            return;
        }

        $groups = $this->flush_groups();
        $flushed = array();
        $this->db->query('SET FOREIGN_KEY_CHECKS=0');

        foreach ($selected as $key) {
            if (!isset($groups[$key])) {
                continue;
            }
            if ($key === 'users') {
                $this->flush_users();
                $flushed[] = 'Users';
                continue;
            }
            $this->truncate_tables($groups[$key]['tables']);
            if ($key === 'stores') {
                $this->delete_store_product_copies();
            }
            $flushed[] = $groups[$key]['label'];
        }

        $this->db->query('SET FOREIGN_KEY_CHECKS=1');

        if (empty($flushed)) {
            $this->session->set_flashdata('error', 'Nothing was flushed.');
        } else {
            $this->session->set_flashdata('success', 'Flushed: ' . implode(', ', $flushed) . '.');
        }
        redirect('/admin/flush-data');
    }

    protected function flush_groups()
    {
        return array(
            'categories' => array(
                'label' => 'Categories',
                'hint' => 'categories, product_categories, store category settings',
                'tables' => array(
                    'product_categories',
                    'store_category_settings',
                    'store_home_categories',
                    'categories',
                ),
            ),
            'products' => array(
                'label' => 'Products',
                'hint' => 'products, images, variations, sources, prices',
                'tables' => array(
                    'product_images',
                    'product_variations',
                    'product_attributes',
                    'product_sources',
                    'product_categories',
                    'store_product_prices',
                    'product_import_unknown_links',
                    'products',
                ),
            ),
            'stores' => array(
                'label' => 'Stores',
                'hint' => 'stores, settings, staff, customers, store product copies',
                'tables' => array(
                    'store_settings',
                    'store_customers',
                    'store_sessions',
                    'store_themes',
                    'store_apps',
                    'staff',
                    'store_product_prices',
                    'store_category_settings',
                    'store_home_categories',
                    'stores',
                ),
            ),
            'users' => array(
                'label' => 'Users',
                'hint' => 'all users except the logged-in super admin',
                'tables' => array('users'),
            ),
            'orders' => array(
                'label' => 'Orders',
                'hint' => 'store_orders, order items, status logs',
                'tables' => array(
                    'store_order_items',
                    'order_status_logs',
                    'store_orders',
                ),
            ),
        );
    }

    protected function truncate_tables($tables)
    {
        foreach ($tables as $table) {
            if ($this->db->table_exists($table)) {
                $this->db->truncate($table);
            }
        }
    }

    protected function flush_users()
    {
        $keepId = (int) ec_user()->UserID;
        $this->db->where('UserID !=', $keepId)->delete('users');
    }

    protected function delete_store_product_copies()
    {
        if (!$this->db->table_exists('products') || !$this->db->field_exists('store_id', 'products')) {
            return;
        }

        $copies = $this->db
            ->select('id')
            ->where('store_id IS NOT NULL', null, false)
            ->where('store_id !=', 0)
            ->get('products')
            ->result();
        if (empty($copies)) {
            return;
        }

        $ids = array();
        foreach ($copies as $row) {
            $ids[] = (int) $row->id;
        }
        $childTables = array(
            'product_images',
            'product_variations',
            'product_attributes',
            'product_sources',
            'product_categories',
            'store_product_prices',
        );
        foreach ($childTables as $table) {
            if ($this->db->table_exists($table)) {
                $this->db->where_in('product_id', $ids)->delete($table);
            }
        }
        $this->db->where_in('id', $ids)->delete('products');
    }
}
