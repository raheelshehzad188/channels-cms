<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Whatsapp_settings extends CI_Controller {

    public function __construct()
    {
        parent::__construct();
        ec_require_admin();
        ensure_whatsapp_schema();
    }

    public function index()
    {
        $data = array(
            'title' => 'WhatsApp',
            'whatsapp' => $this->settings(),
        );
        $this->template->admin('whatsapp/index', $data);
    }

    public function save()
    {
        $fields = array(
            'whatsapp_enabled' => $this->input->post('enabled') ? '1' : '0',
            'whatsapp_api_url' => trim((string) $this->input->post('api_url')),
            'whatsapp_api_key' => trim((string) $this->input->post('api_key')),
            'whatsapp_session_name' => trim((string) $this->input->post('session_name')),
            'admin_whatsapp_number' => trim((string) $this->input->post('admin_whatsapp_number')),
        );
        $secret = trim((string) $this->input->post('api_secret'));
        if ($secret !== '') {
            $fields['whatsapp_api_secret'] = $secret;
        }
        foreach ($fields as $key => $value) {
            $this->save_setting($key, $value);
        }
        $this->session->set_flashdata('success', 'WhatsApp settings saved.');
        redirect('/admin/whatsapp');
    }

    public function test()
    {
        $to = trim((string) $this->input->post('test_phone'));
        if ($to === '') {
            $to = trim((string) $this->input->get('test_phone'));
        }
        if ($to === '') {
            $to = platform_setting('admin_whatsapp_number', '');
        }
        $this->load->library('ec_whatsapp');
        $ok = $this->ec_whatsapp->send($to, 'Test message from Ecommerce Platform. WhatsApp connection is working.', '', true);
        $normalized = $this->ec_whatsapp->normalize_phone($to);
        $msg = $ok
            ? 'Message sent to ' . $normalized . '. Check WhatsApp.'
            : ($this->ec_whatsapp->lastError !== '' ? $this->ec_whatsapp->lastError : 'Failed to send test WhatsApp.');

        $wantsJson = $this->input->is_ajax_request()
            || strpos((string) $this->input->server('HTTP_ACCEPT'), 'application/json') !== false
            || $this->input->method() === 'post';

        if ($wantsJson) {
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(array(
                'ok' => (bool) $ok,
                'message' => $msg,
            ));
            exit;
        }

        $this->session->set_flashdata($ok ? 'success' : 'error', $msg);
        redirect('/admin/whatsapp');
    }

    protected function settings()
    {
        return array(
            'enabled' => platform_setting('whatsapp_enabled', '0'),
            'api_url' => platform_setting('whatsapp_api_url', 'https://khaki-gerbil-447172.hostingersite.com/api/send-message.php'),
            'api_key' => platform_setting('whatsapp_api_key', ''),
            'api_secret' => platform_setting('whatsapp_api_secret', ''),
            'session_name' => platform_setting('whatsapp_session_name', 'user_30'),
            'admin_whatsapp_number' => platform_setting('admin_whatsapp_number', ''),
        );
    }

    protected function save_setting($key, $value)
    {
        $exists = $this->db->where('setting_key', $key)->get('platform_settings')->row();
        if ($exists) {
            $this->db->where('setting_key', $key)->update('platform_settings', array('setting_value' => $value));
            return;
        }
        $this->db->insert('platform_settings', array(
            'setting_key' => $key,
            'setting_value' => $value,
        ));
    }
}
