<?php
defined('BASEPATH') OR exit('No direct script access allowed');

require_once APPPATH . 'modules/store/controllers/Store_base.php';

class Profile extends Store_base {

    public function index()
    {
        $this->requireAuth();
        $this->requirePermission('settings');

        if ($this->input->post()) {
            $social = array(
                'facebook' => $this->input->post('facebook'),
                'instagram' => $this->input->post('instagram'),
                'twitter' => $this->input->post('twitter'),
                'linkedin' => $this->input->post('linkedin'),
            );

            $update = array(
                'name' => $this->input->post('name'),
                'description' => $this->input->post('description'),
                'address' => $this->input->post('address'),
                'timezone' => $this->input->post('timezone'),
                'language' => $this->input->post('language'),
                'social_links' => json_encode($social),
                'business_name' => $this->input->post('business_name'),
                'business_registration' => $this->input->post('business_registration'),
                'tax_id' => $this->input->post('tax_id'),
                'email' => $this->input->post('email'),
                'phone' => $this->input->post('phone'),
            );

            if (!empty($_FILES['logo']['name'])) {
                $config = array(
                    'upload_path' => './uploads/stores/',
                    'allowed_types' => 'gif|jpg|jpeg|png|webp|svg',
                    'max_size' => 2048,
                    'encrypt_name' => true,
                );
                if (!is_dir($config['upload_path'])) {
                    mkdir($config['upload_path'], 0755, true);
                }
                $this->load->library('upload', $config);
                if ($this->upload->do_upload('logo')) {
                    $update['logo'] = 'uploads/stores/' . $this->upload->data('file_name');
                }
            }

            $this->db->where('id', $this->store->id)->update('stores', $update);
            $this->store = $this->db->where('id', $this->store->id)->get('stores')->row();
            $this->session->set_flashdata('success', 'Profile updated successfully.');
            redirect('store/profile');
        }

        $social = json_decode($this->store->social_links, true);
        if (!is_array($social)) {
            $social = array();
        }

        $this->template->store('profile/index', $this->viewData(array(
            'page' => 'Store Profile',
            'social' => $social,
        )));
    }
}
