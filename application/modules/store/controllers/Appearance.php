<?php
defined('BASEPATH') OR exit('No direct script access allowed');

require_once APPPATH . 'modules/store/controllers/Store_base.php';

class Appearance extends Store_base {

    public function css()
    {
        $this->requireAuth();

        if ($this->input->post()) {
            $css = sanitize_custom_css($this->input->post('custom_css'));
            $this->db->where('id', $this->store->id)->update('stores', array(
                'custom_css' => $css,
            ));
            $this->store->custom_css = $css;
            $this->session->set_flashdata('success', 'Custom CSS saved. It applies to your storefront only. HTML cannot be changed.');
            redirect('store/custom-css');
            return;
        }

        $this->template->store('appearance/css', $this->viewData(array(
            'page' => 'Custom CSS',
            'title' => 'Custom CSS',
            'custom_css' => isset($this->store->custom_css) ? $this->store->custom_css : '',
        )));
    }
}
