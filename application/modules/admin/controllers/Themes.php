<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Themes extends CI_Controller {

    public function __construct()
    {
        parent::__construct();
        ec_require_admin();
        $this->load->model('Theme_model');
    }

    public function index()
    {
        $data = array(
            'title' => 'Themes',
            'themes' => $this->Theme_model->all(),
        );
        $this->template->admin('themes/index', $data);
    }

    public function settings($themeId = 0)
    {
        $theme = $this->Theme_model->get($themeId);
        if (!$theme) {
            $this->session->set_flashdata('error', 'Theme not found.');
            redirect('/admin/themes');
            return;
        }

        $data = array(
            'title' => $theme->name . ' Settings',
            'theme' => $theme,
            'fields' => $this->Theme_model->fields($themeId),
            'field_types' => array('text' => 'Text', 'textarea' => 'Textarea', 'color' => 'Color', 'image' => 'Image'),
        );
        $this->template->admin('themes/settings', $data);
    }

    public function save_field($themeId = 0, $fieldId = 0)
    {
        $theme = $this->Theme_model->get($themeId);
        if (!$theme) {
            $this->session->set_flashdata('error', 'Theme not found.');
            redirect('/admin/themes');
            return;
        }

        $this->form_validation->set_rules('field_label', 'Field Label', 'required|trim');
        $this->form_validation->set_rules('field_key', 'Field Key', 'required|trim');
        $this->form_validation->set_rules('field_type', 'Field Type', 'required|trim');

        if ($this->form_validation->run() === FALSE) {
            $this->session->set_flashdata('error', validation_errors());
            redirect('/admin/themes/settings/' . $themeId);
            return;
        }

        $key = preg_replace('/[^a-z0-9_]/', '', strtolower(trim($this->input->post('field_key'))));
        if ($key === '') {
            $this->session->set_flashdata('error', 'Field key must contain letters or numbers.');
            redirect('/admin/themes/settings/' . $themeId);
            return;
        }

        $payload = array(
            'theme_id' => (int) $themeId,
            'field_key' => $key,
            'field_label' => trim($this->input->post('field_label')),
            'field_type' => $this->input->post('field_type'),
            'is_required' => (int) $this->input->post('is_required') === 1 ? 1 : 0,
            'default_value' => trim($this->input->post('default_value')),
            'sort_order' => (int) $this->input->post('sort_order'),
        );

        $this->Theme_model->save_field($payload, $fieldId);
        $this->session->set_flashdata('success', $fieldId ? 'Setting field updated.' : 'Setting field added.');
        redirect('/admin/themes/settings/' . $themeId);
    }

    public function delete_field($themeId = 0, $fieldId = 0)
    {
        $this->Theme_model->delete_field($fieldId);
        $this->session->set_flashdata('success', 'Setting field deleted.');
        redirect('/admin/themes/settings/' . $themeId);
    }
}
