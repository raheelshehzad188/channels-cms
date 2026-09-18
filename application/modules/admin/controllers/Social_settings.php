<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Social_settings extends CI_Controller {

    public function __construct()
    {
        parent::__construct();
        ec_require_admin();
    }

    public function index()
    {
        $this->load->model('Store_channel_model');
        $this->Store_channel_model->ensure_schema();
        $data = array(
            'title' => 'Social Channels',
            'meta_app_id' => platform_setting('meta_app_id', ''),
            'meta_app_secret' => $this->channel_secret('meta_app_secret'),
            'tiktok_app_id' => platform_setting('tiktok_app_id', ''),
            'tiktok_app_secret' => $this->channel_secret('tiktok_app_secret'),
            'meta_redirect' => site_url('store/channels/meta/callback'),
            'tiktok_redirect' => site_url('store/channels/tiktok/callback'),
            'meta_api_version' => 'v21.0',
            'tiktok_api_version' => 'v1.3',
            'cron_url' => site_url('cron/channel_jobs') . '?key=' . rawurlencode(platform_setting('channel_cron_key', '')),
        );
        $this->template->admin('social/index', $data);
    }

    public function save()
    {
        $this->load->model('Store_channel_model');
        $this->Store_channel_model->ensure_schema();
        $fields = array(
            'meta_app_id' => trim((string) $this->input->post('meta_app_id')),
            'tiktok_app_id' => trim((string) $this->input->post('tiktok_app_id')),
        );
        $metaSecret = trim((string) $this->input->post('meta_app_secret'));
        if ($metaSecret !== '') {
            $enc = $this->Store_channel_model->encrypt_setting($metaSecret);
            if ($enc !== false) {
                $fields['meta_app_secret'] = $enc;
            }
        }
        $tiktokSecret = trim((string) $this->input->post('tiktok_app_secret'));
        if ($tiktokSecret !== '') {
            $enc = $this->Store_channel_model->encrypt_setting($tiktokSecret);
            if ($enc !== false) {
                $fields['tiktok_app_secret'] = $enc;
            }
        }
        foreach ($fields as $key => $value) {
            $this->save_setting($key, $value);
        }
        $this->session->set_flashdata('success', 'Social channel apps saved. Stores can now connect with one button.');
        redirect('/admin/social');
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

    protected function channel_secret($key)
    {
        $this->load->model('Store_channel_model');
        $raw = platform_setting($key, '');
        $plain = $this->Store_channel_model->decrypt_setting($raw);
        if ($plain !== '' && strpos($raw, 'enc:') !== 0) {
            $enc = $this->Store_channel_model->encrypt_setting($plain);
            if ($enc !== false) {
                $this->save_setting($key, $enc);
            }
        }
        return $plain !== '' ? '1' : '';
    }
}
