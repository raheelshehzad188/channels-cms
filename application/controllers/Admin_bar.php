<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Admin_bar extends CI_Controller {

    public function ping()
    {
        if (function_exists('header_remove')) {
            header_remove('X-Frame-Options');
        }
        $this->output->set_header('Content-Type: text/html; charset=UTF-8');
        $this->output->set_header('Content-Security-Policy: frame-ancestors *');
        $this->output->set_header('Cache-Control: no-store');
        $ok = function_exists('storefront_admin_bar_visible') && storefront_admin_bar_visible();
        $name = $ok && function_exists('storefront_admin_bar_name') ? storefront_admin_bar_name() : '';
        $payload = json_encode(array(
            'ecAdminBar' => $ok ? 1 : 0,
            'name' => $name,
        ));
        $html = '<!DOCTYPE html><html><head><meta charset="utf-8"></head><body><script>';
        $html .= 'try{var o=document.referrer?new URL(document.referrer).origin:"*";';
        $html .= 'window.parent.postMessage(' . $payload . ',o);}catch(e){window.parent.postMessage(' . $payload . ',"*");}';
        $html .= '</script></body></html>';
        $this->output->set_output($html);
    }

    public function grant()
    {
        $token = (string) $this->input->get('t');
        $next = (string) $this->input->get('next');
        $data = storefront_admin_bar_parse_token($token);
        if (!$data) {
            show_error('Invalid admin bar token', 403);
            return;
        }
        storefront_admin_bar_set_cookie($token, (int) $data['exp']);
        if (!storefront_admin_bar_allowed_url($next)) {
            $next = '/';
        }
        redirect($next);
    }

    public function seed()
    {
        if (!function_exists('ec_is_admin') || !ec_is_admin()) {
            redirect('/login');
            return;
        }
        $token = storefront_admin_bar_make_token(ec_user());
        if ($token === '') {
            redirect('/admin/admin');
            return;
        }
        $next = (string) $this->input->get('next');
        if (!storefront_admin_bar_allowed_url($next)) {
            $next = platform_base_url() . 'admin/admin';
        }
        $sep = (strpos($next, '?') === false) ? '?' : '&';
        $chain = $next . $sep . 'ec_ab=1';
        $current = strtolower((string) $this->input->server('HTTP_HOST'));
        foreach (array_reverse(storefront_admin_bar_hosts()) as $host) {
            if ($host === $current || !storefront_admin_bar_host_ok($host)) {
                continue;
            }
            $chain = 'https://' . $host . '/admin_bar/grant?t=' . rawurlencode($token)
                . '&next=' . rawurlencode($chain);
        }
        redirect($chain);
    }

    public function extra_amount()
    {
        $this->output->set_content_type('application/json');
        if (!function_exists('storefront_admin_bar_visible') || !storefront_admin_bar_visible()) {
            $this->output->set_status_header(403);
            $this->output->set_output(json_encode(array('ok' => 0, 'error' => 'forbidden')));
            return;
        }
        if (strtoupper((string) $this->input->server('REQUEST_METHOD')) !== 'POST') {
            $this->output->set_status_header(405);
            $this->output->set_output(json_encode(array('ok' => 0, 'error' => 'method')));
            return;
        }
        $productId = (int) $this->input->post('product_id');
        $extra = (float) $this->input->post('extra_amount');
        $row = $this->db->where('id', $productId)->get('products')->row();
        if (!$row) {
            $this->output->set_status_header(404);
            $this->output->set_output(json_encode(array('ok' => 0, 'error' => 'Product not found')));
            return;
        }
        $catalogId = !empty($row->source_product_id) ? (int) $row->source_product_id : (int) $row->id;
        if (function_exists('ec_apply_catalog_extra_amount')) {
            ec_apply_catalog_extra_amount($catalogId, $extra);
        }
        $listing = $this->db->where('id', $productId)->get('products')->row();
        $store = null;
        if ($listing && !empty($listing->store_id)) {
            $store = $this->db->where('id', (int) $listing->store_id)->get('stores')->row();
        }
        $calc = function_exists('storefront_admin_price_breakdown')
            ? storefront_admin_price_breakdown($listing, $store)
            : null;
        $this->output->set_output(json_encode(array(
            'ok' => 1,
            'product_id' => $productId,
            'listed' => $listing ? (float) $listing->price : 0,
            'listed_formatted' => $listing ? format_money((float) $listing->price) : '',
            'extra_amount' => $calc ? $calc['extra_amount'] : $extra,
            'calc' => $calc,
        )));
    }

    public function ai_content()
    {
        $this->output->set_content_type('application/json');
        if (!function_exists('storefront_admin_bar_visible') || !storefront_admin_bar_visible()) {
            $this->output->set_status_header(403);
            $this->output->set_output(json_encode(array('ok' => 0, 'error' => 'forbidden')));
            return;
        }
        if (strtoupper((string) $this->input->server('REQUEST_METHOD')) !== 'POST') {
            $this->output->set_status_header(405);
            $this->output->set_output(json_encode(array('ok' => 0, 'error' => 'method')));
            return;
        }
        @set_time_limit(300);
        if (function_exists('ignore_user_abort')) {
            @ignore_user_abort(true);
        }
        $productId = (int) $this->input->post('product_id');
        $rawCount = trim((string) $this->input->post('review_count'));
        if (!preg_match('/^[1-9][0-9]*$/', $rawCount)) {
            $this->output->set_status_header(422);
            $this->output->set_output(json_encode(array(
                'ok' => 0,
                'error' => 'Enter a whole number of reviews between 1 and 50.',
            )));
            return;
        }
        $reviewCount = (int) $rawCount;
        if ($reviewCount < 1 || $reviewCount > 50) {
            $this->output->set_status_header(422);
            $this->output->set_output(json_encode(array(
                'ok' => 0,
                'error' => 'Enter a whole number of reviews between 1 and 50.',
            )));
            return;
        }
        if ($productId < 1) {
            $this->output->set_status_header(422);
            $this->output->set_output(json_encode(array(
                'ok' => 0,
                'error' => 'Product not found.',
            )));
            return;
        }
        $this->load->model('admin/Ai_content_model');
        $result = $this->Ai_content_model->rewrite_listing($productId, $reviewCount);
        if (empty($result['ok'])) {
            $this->output->set_status_header(422);
            $this->output->set_output(json_encode(array(
                'ok' => 0,
                'error' => isset($result['error']) ? $result['error'] : 'AI generation failed.',
                'content_saved' => !empty($result['content_saved']) ? 1 : 0,
                'url' => isset($result['url']) ? $result['url'] : '',
            )));
            return;
        }
        $this->output->set_output(json_encode(array(
            'ok' => 1,
            'product_id' => isset($result['product_id']) ? (int) $result['product_id'] : $productId,
            'name' => isset($result['name']) ? $result['name'] : '',
            'slug' => isset($result['slug']) ? $result['slug'] : '',
            'url' => isset($result['url']) ? $result['url'] : '',
            'category_label' => isset($result['category_label']) ? $result['category_label'] : '',
            'reviews_ok' => !empty($result['reviews_ok']) ? 1 : 0,
            'reviews_count' => isset($result['reviews_count']) ? (int) $result['reviews_count'] : 0,
            'content_saved' => !empty($result['content_saved']) ? 1 : 0,
        )));
    }

    public function toggle_trending()
    {
        $this->output->set_content_type('application/json');
        if (!function_exists('storefront_admin_bar_visible') || !storefront_admin_bar_visible()) {
            $this->output->set_status_header(403);
            $this->output->set_output(json_encode(array('ok' => 0, 'error' => 'forbidden')));
            return;
        }
        if (strtoupper((string) $this->input->server('REQUEST_METHOD')) !== 'POST') {
            $this->output->set_status_header(405);
            $this->output->set_output(json_encode(array('ok' => 0, 'error' => 'method')));
            return;
        }
        if (function_exists('ensure_product_trending_columns')) {
            ensure_product_trending_columns();
        }
        $productId = (int) $this->input->post('product_id');
        if ($productId < 1) {
            $this->output->set_status_header(422);
            $this->output->set_output(json_encode(array('ok' => 0, 'error' => 'Product not found.')));
            return;
        }
        $row = $this->db->where('id', $productId)->get('products')->row();
        if (!$row) {
            $this->output->set_status_header(404);
            $this->output->set_output(json_encode(array('ok' => 0, 'error' => 'Product not found.')));
            return;
        }

        // Always toggle the parent listing — children are excluded from Trending Picks.
        $target = $row;
        if (function_exists('product_family')) {
            $family = product_family($row, false);
            if (!empty($family['parent'])) {
                $target = $family['parent'];
            }
        }
        $parentSku = isset($target->parent_sku) ? trim((string) $target->parent_sku) : '';
        if ($parentSku !== '' && function_exists('storefront_listing_product')) {
            $listing = storefront_listing_product($target);
            if ($listing) {
                $target = $listing;
            }
        }

        $targetId = (int) $target->id;
        $currentlyOn = !empty($target->is_trending);
        if (!$currentlyOn && function_exists('product_family')) {
            $family = product_family($target, false);
            foreach (!empty($family['children']) ? $family['children'] : array() as $child) {
                if (!empty($child->is_trending)) {
                    $currentlyOn = true;
                    break;
                }
            }
        }
        $next = $currentlyOn ? 0 : 1;

        $this->db->where('id', $targetId)->update('products', array('is_trending' => $next));

        // Keep family consistent: clear child flags (parents own the trending state).
        $groupSku = isset($target->sku) ? trim((string) $target->sku) : '';
        if ($groupSku !== '' && $this->db->field_exists('parent_sku', 'products')) {
            $this->db
                ->where('store_id', (int) $target->store_id)
                ->where('parent_sku', $groupSku)
                ->update('products', array('is_trending' => 0));
        }

        $this->output->set_output(json_encode(array(
            'ok' => 1,
            'product_id' => $targetId,
            'is_trending' => $next,
            'label' => $next ? 'Remove Trending' : 'Make Trending',
            'message' => $next
                ? 'Added to Trending Picks.'
                : 'Removed from Trending Picks.',
        )));
    }
}
