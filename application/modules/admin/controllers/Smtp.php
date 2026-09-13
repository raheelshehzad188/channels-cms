<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Smtp extends CI_Controller {

    public function __construct()
    {
        parent::__construct();
        ec_require_admin();
    }

    public function index()
    {
        $data = array(
            'title' => 'SMTP Settings',
            'smtp' => array(
                'enabled' => platform_setting('smtp_enabled', '0'),
                'host' => platform_setting('smtp_host', ''),
                'port' => platform_setting('smtp_port', '587'),
                'user' => platform_setting('smtp_user', ''),
                'pass' => platform_setting('smtp_pass', ''),
                'crypto' => platform_setting('smtp_crypto', 'tls'),
                'from_email' => platform_setting('smtp_from_email', 'noreply@ecommerce.local'),
                'from_name' => platform_setting('smtp_from_name', 'Ecommerce Platform'),
                'admin_notify_email' => platform_setting('admin_notify_email', ''),
            ),
        );
        $this->template->admin('smtp/index', $data);
    }

    public function save()
    {
        $fields = array(
            'smtp_enabled' => $this->input->post('enabled') ? '1' : '0',
            'smtp_host' => trim((string) $this->input->post('host')),
            'smtp_port' => trim((string) $this->input->post('port')),
            'smtp_user' => trim((string) $this->input->post('user')),
            'smtp_crypto' => trim((string) $this->input->post('crypto')),
            'smtp_from_email' => trim((string) $this->input->post('from_email')),
            'smtp_from_name' => trim((string) $this->input->post('from_name')),
            'admin_notify_email' => trim((string) $this->input->post('admin_notify_email')),
        );
        $pass = (string) $this->input->post('pass');
        if ($pass !== '') {
            $fields['smtp_pass'] = $pass;
        }
        foreach ($fields as $key => $value) {
            $this->save_setting($key, $value);
        }
        $this->session->set_flashdata('success', 'SMTP settings saved.');
        redirect('/admin/smtp');
    }

    public function test()
    {
        $to = trim((string) $this->input->post('test_email'));
        if ($to === '') {
            $to = platform_setting('admin_notify_email', '');
        }
        $this->load->library('ec_mail');
        $ok = $this->ec_mail->send($to, 'SMTP test from Ecommerce Platform', '<p>This is a test email from your SMTP settings.</p><p>If you received this, SMTP is working.</p>');
        $this->session->set_flashdata($ok ? 'success' : 'error', $ok ? 'Test email sent to ' . $to : 'Failed to send test email. Check SMTP settings.');
        redirect('/admin/smtp');
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
