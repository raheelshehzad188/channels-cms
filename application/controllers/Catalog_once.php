<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Catalog_once extends CI_Controller {

    public function delete_glove()
    {
        if (php_sapi_name() !== 'cli') {
            show_error('Forbidden', 403);
            return;
        }
        $this->load->model('admin/Product_model');
        $ids = array(1078, 1080, 1082, 1077);
        $out = array();
        foreach ($ids as $id) {
            $row = $this->db->where('id', $id)->get('products')->row();
            if (!$row) {
                $out[] = array('id' => $id, 'missing' => 1);
                continue;
            }
            $ok = $this->Product_model->delete($id);
            $out[] = array('id' => $id, 'sku' => (string) $row->sku, 'deleted' => $ok ? 1 : 0);
        }
        $left = $this->db->like('sku', 'AE1005009104164006', 'after')->count_all_results('products');
        $out[] = array('remaining' => (int) $left);
        $this->output->set_content_type('text/plain')->set_output(json_encode($out) . "\n");
    }

    public function rewrite()
    {
        if (php_sapi_name() !== 'cli') {
            show_error('Forbidden', 403);
            return;
        }
        $id = (int) getenv('REWRITE_PRODUCT_ID');
        $count = (int) getenv('REVIEW_COUNT');
        if ($count < 1) {
            $count = 5;
        }
        $this->load->model('admin/Ai_content_model');
        $result = $this->Ai_content_model->rewrite_listing($id, $count);
        if (isset($result['error'])) {
            $result['error'] = preg_replace('/ag_[A-Za-z0-9]+|AIza[0-9A-Za-z_\-]+|sk-[A-Za-z0-9]+/', '[redacted]', (string) $result['error']);
        }
        $this->output->set_content_type('text/plain')->set_output(json_encode($result) . "\n");
    }
}
