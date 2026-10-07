<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Offers extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        ec_require_admin();
        if (function_exists('ensure_product_offer_columns')) {
            ensure_product_offer_columns();
        }
        if (function_exists('ensure_offer_campaign_tables')) {
            ensure_offer_campaign_tables();
        }
        $this->load->model('admin/Product_model');
    }

    public function index()
    {
        $status = strtolower(trim((string) $this->input->get('status')));
        $allowed = array('all', 'active', 'scheduled', 'expired', 'disabled');
        if (!in_array($status, $allowed, true)) {
            $status = 'all';
        }
        $now = offer_now();
        $this->db->from('products');
        $this->db->where('offer_enabled', 1);
        // Prefer storefront parents / standalone (hide children when parent exists)
        if ($this->db->field_exists('parent_sku', 'products')) {
            $this->db->group_start();
            $this->db->where('parent_sku', '');
            $this->db->or_where('parent_sku IS NULL', null, false);
            $this->db->group_end();
        }
        $this->db->order_by('offer_priority', 'DESC');
        $this->db->order_by('id', 'DESC');
        $this->db->limit(500);
        $rows = $this->db->get()->result();
        $items = array();
        foreach ($rows as $row) {
            $st = offer_status_label(1, $row->offer_starts_at, $row->offer_ends_at, $now);
            if ($status !== 'all' && $st !== $status) {
                continue;
            }
            $original = (float) $row->price;
            $final = $original;
            if ($row->offer_type === 'percent' || $row->offer_type === 'fixed') {
                $final = offer_compute_unit_price($original, $row->offer_type, $row->offer_value);
            }
            $items[] = array(
                'product' => $row,
                'status' => $st,
                'original' => $original,
                'final' => $final,
                'discount' => max(0, round($original - $final, 2)),
            );
        }
        // Also show disabled filter via separate query
        if ($status === 'disabled' || $status === 'all') {
            // already filtered enabled for active/scheduled/expired; for disabled pull separately
        }
        if ($status === 'disabled') {
            $this->db->from('products');
            $this->db->where('offer_enabled', 0);
            $this->db->where("(offer_type IS NOT NULL AND offer_type != '')", null, false);
            $this->db->where('offer_value >', 0);
            if ($this->db->field_exists('parent_sku', 'products')) {
                $this->db->group_start();
                $this->db->where('parent_sku', '');
                $this->db->or_where('parent_sku IS NULL', null, false);
                $this->db->group_end();
            }
            $this->db->order_by('id', 'DESC');
            $this->db->limit(200);
            $disabled = $this->db->get()->result();
            $items = array();
            foreach ($disabled as $row) {
                $original = (float) $row->price;
                $final = offer_compute_unit_price($original, $row->offer_type, $row->offer_value);
                $items[] = array(
                    'product' => $row,
                    'status' => 'disabled',
                    'original' => $original,
                    'final' => $final,
                    'discount' => max(0, round($original - $final, 2)),
                );
            }
        }

        $campaigns = $this->db->order_by('id', 'DESC')->limit(100)->get('store_offer_campaigns')->result();
        foreach ($campaigns as $c) {
            $c->status_label = offer_status_label((int) $c->status, $c->starts_at, $c->ends_at, $now);
        }

        $bulkProducts = $this->db->select('id, name, sku, price')
            ->where('(parent_sku IS NULL OR parent_sku = "")', null, false)
            ->order_by('id', 'DESC')
            ->limit(300)
            ->get('products')
            ->result();

        $data = array(
            'title' => 'Offers',
            'items' => $items,
            'status' => $status,
            'campaigns' => $campaigns,
            'bulk_products' => $bulkProducts,
            'stores' => $this->db->select('id, name')->order_by('name', 'asc')->get('stores')->result(),
            'flash_success' => $this->session->flashdata('success'),
            'flash_error' => $this->session->flashdata('error'),
        );
        $this->template->admin('offers/index', $data);
    }

    public function toggle($id = 0)
    {
        $id = (int) $id;
        $product = $this->Product_model->get($id);
        if (!$product) {
            $this->session->set_flashdata('error', 'Product not found.');
            redirect('/admin/offers');
            return;
        }
        $next = !empty($product->offer_enabled) ? 0 : 1;
        $this->Product_model->save(array('offer_enabled' => $next), $id);
        if (empty($product->store_id)) {
            $this->Product_model->sync_copy_fields($id, array('offer_enabled' => $next));
        }
        $this->session->set_flashdata('success', $next ? 'Offer enabled.' : 'Offer disabled.');
        redirect('/admin/offers');
    }

    public function bulk()
    {
        if (strtoupper((string) $this->input->method()) !== 'POST') {
            redirect('/admin/offers');
            return;
        }
        $ids = $this->input->post('product_ids');
        if (!is_array($ids) || !$ids) {
            $this->session->set_flashdata('error', 'Select at least one product.');
            redirect('/admin/offers');
            return;
        }
        $payload = offer_sanitize_admin_payload($this->input->post());
        $payload['offer_enabled'] = 1;
        $n = 0;
        foreach ($ids as $id) {
            $id = (int) $id;
            if ($id < 1) {
                continue;
            }
            $this->Product_model->save($payload, $id);
            $row = $this->Product_model->get($id);
            if ($row && empty($row->store_id)) {
                $this->Product_model->sync_copy_fields($id, $payload);
            }
            $n++;
        }
        $this->session->set_flashdata('success', 'Offer applied to ' . $n . ' product(s).');
        redirect('/admin/offers');
    }

    public function campaign()
    {
        $id = (int) $this->input->get('id');
        $campaign = $id ? $this->db->where('id', $id)->get('store_offer_campaigns')->row() : null;
        $productIds = array();
        $categoryIds = array();
        if ($campaign) {
            foreach ($this->db->where('campaign_id', $id)->get('store_offer_campaign_products')->result() as $r) {
                $productIds[] = (int) $r->product_id;
            }
            foreach ($this->db->where('campaign_id', $id)->get('store_offer_campaign_categories')->result() as $r) {
                $categoryIds[] = (int) $r->category_id;
            }
        }
        $data = array(
            'title' => $campaign ? 'Edit Campaign' : 'New Campaign',
            'campaign' => $campaign,
            'product_ids' => $productIds,
            'category_ids' => $categoryIds,
            'stores' => $this->db->select('id, name')->order_by('name', 'asc')->get('stores')->result(),
            'categories' => $this->db->order_by('name', 'asc')->get('categories')->result(),
            'products' => $this->db->select('id, name, sku, store_id, price')
                ->where('(parent_sku IS NULL OR parent_sku = "")', null, false)
                ->order_by('id', 'DESC')
                ->limit(400)
                ->get('products')
                ->result(),
            'flash_error' => $this->session->flashdata('error'),
        );
        $this->template->admin('offers/campaign', $data);
    }

    public function campaign_save()
    {
        if (strtoupper((string) $this->input->method()) !== 'POST') {
            redirect('/admin/offers');
            return;
        }
        $id = (int) $this->input->post('id');
        $name = trim((string) $this->input->post('name'));
        if ($name === '') {
            $this->session->set_flashdata('error', 'Campaign name is required.');
            redirect('/admin/offers/campaign' . ($id ? '?id=' . $id : ''));
            return;
        }
        $type = offer_normalize_type($this->input->post('discount_type'));
        $value = (float) $this->input->post('discount_value');
        if ($type === 'percent') {
            $value = max(0, min(95, $value));
        } else {
            $value = max(0, $value);
        }
        $starts = trim((string) $this->input->post('starts_at'));
        $ends = trim((string) $this->input->post('ends_at'));
        $row = array(
            'store_id' => (int) $this->input->post('store_id'),
            'name' => mb_substr($name, 0, 160),
            'label' => mb_substr(trim((string) $this->input->post('label')), 0, 120),
            'discount_type' => $type,
            'discount_value' => $value,
            'buy_qty' => max(0, (int) $this->input->post('buy_qty')),
            'get_qty' => max(0, (int) $this->input->post('get_qty')),
            'bundle_qty' => max(0, (int) $this->input->post('bundle_qty')),
            'bundle_price' => max(0, (float) $this->input->post('bundle_price')),
            'starts_at' => $starts !== '' ? date('Y-m-d H:i:s', strtotime($starts)) : null,
            'ends_at' => $ends !== '' ? date('Y-m-d H:i:s', strtotime($ends)) : null,
            'priority' => (int) $this->input->post('priority'),
            'show_badge' => $this->input->post('show_badge') ? 1 : 0,
            'show_countdown' => $this->input->post('show_countdown') ? 1 : 0,
            'status' => $this->input->post('status') ? 1 : 0,
            'updated_at' => date('Y-m-d H:i:s'),
        );
        if ($id > 0) {
            $this->db->where('id', $id)->update('store_offer_campaigns', $row);
        } else {
            $row['created_at'] = date('Y-m-d H:i:s');
            $this->db->insert('store_offer_campaigns', $row);
            $id = (int) $this->db->insert_id();
        }
        $this->db->where('campaign_id', $id)->delete('store_offer_campaign_products');
        $this->db->where('campaign_id', $id)->delete('store_offer_campaign_categories');
        $pids = $this->input->post('product_ids');
        if (is_array($pids)) {
            foreach ($pids as $pid) {
                $pid = (int) $pid;
                if ($pid > 0) {
                    $this->db->insert('store_offer_campaign_products', array(
                        'campaign_id' => $id,
                        'product_id' => $pid,
                    ));
                }
            }
        }
        $cids = $this->input->post('category_ids');
        if (is_array($cids)) {
            foreach ($cids as $cid) {
                $cid = (int) $cid;
                if ($cid > 0) {
                    $this->db->insert('store_offer_campaign_categories', array(
                        'campaign_id' => $id,
                        'category_id' => $cid,
                    ));
                }
            }
        }
        $this->session->set_flashdata('success', 'Campaign saved.');
        redirect('/admin/offers');
    }

    public function campaign_toggle($id = 0)
    {
        $id = (int) $id;
        $c = $this->db->where('id', $id)->get('store_offer_campaigns')->row();
        if (!$c) {
            redirect('/admin/offers');
            return;
        }
        $this->db->where('id', $id)->update('store_offer_campaigns', array(
            'status' => ((int) $c->status === 1) ? 0 : 1,
            'updated_at' => date('Y-m-d H:i:s'),
        ));
        $this->session->set_flashdata('success', 'Campaign status updated.');
        redirect('/admin/offers');
    }
}
