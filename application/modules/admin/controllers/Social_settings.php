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
        $this->load->model('Store_model');
        $this->load->library('meta_commerce');
        $this->Store_channel_model->ensure_schema();
        $storeUris = array();
        foreach ($this->Store_model->all() as $store) {
            $host = strtolower(trim((string) $store->domain));
            $host = preg_replace('/^https?:\/\//', '', $host);
            $host = rtrim($host, '/');
            if ($host === '') {
                continue;
            }
            $base = 'https://' . $host;
            $storeUris[] = array(
                'name' => $store->name,
                'channels' => $base . '/store/channels/meta/callback',
                'events' => $base . '/store/settings/meta/callback',
            );
        }
        $appId = trim((string) platform_setting('meta_app_id', ''));
        $hasSecret = $this->channel_secret('meta_app_secret') !== '';
        $data = array(
            'title' => 'Social Channels',
            'meta_app_id' => $appId,
            'meta_app_secret' => $hasSecret ? '1' : '',
            'meta_configured' => ($appId !== '' && $hasSecret),
            'tiktok_app_id' => platform_setting('tiktok_app_id', ''),
            'tiktok_app_secret' => $this->channel_secret('tiktok_app_secret'),
            'meta_redirect' => $this->meta_commerce->redirect_uri(),
            'meta_events_redirect' => $this->meta_commerce->events_redirect_uri(),
            'meta_store_uris' => $storeUris,
            'tiktok_redirect' => site_url('store/channels/tiktok/callback'),
            'meta_api_version' => 'v26.0',
            'tiktok_api_version' => 'v1.3',
            'cron_url' => site_url('cron/channel_jobs') . '?key=' . rawurlencode(platform_setting('channel_cron_key', '')),
        );
        $this->template->admin('social/index', $data);
    }

    public function save()
    {
        $this->save_meta();
    }

    public function save_meta()
    {
        if (strtoupper((string) $this->input->method()) !== 'POST') {
            redirect('/admin/social');
            return;
        }
        $this->load->model('Store_channel_model');
        $this->Store_channel_model->ensure_schema();
        $appId = trim((string) $this->input->post('meta_app_id'));
        $metaSecret = trim((string) $this->input->post('meta_app_secret'));
        $hasExistingSecret = $this->channel_secret('meta_app_secret') !== '';
        if ($appId === '') {
            $this->session->set_flashdata('error', 'Meta App ID is required.');
            redirect('/admin/social');
            return;
        }
        if ($metaSecret === '' && !$hasExistingSecret) {
            $this->session->set_flashdata('error', 'Meta App Secret is required the first time. Paste it from Meta for Developers → App settings → Basic.');
            redirect('/admin/social');
            return;
        }
        $this->save_setting('meta_app_id', $appId);
        if ($metaSecret !== '') {
            $enc = $this->Store_channel_model->encrypt_setting($metaSecret);
            if ($enc === false || $enc === '') {
                $this->session->set_flashdata('error', 'Could not encrypt the Meta App Secret. Try again.');
                redirect('/admin/social');
                return;
            }
            $this->save_setting('meta_app_secret', $enc);
        }
        $this->session->set_flashdata('success', 'Meta Integration settings saved. Store admins can now click Connect Meta.');
        redirect('/admin/social');
    }

    public function save_tiktok()
    {
        if (strtoupper((string) $this->input->method()) !== 'POST') {
            redirect('/admin/social');
            return;
        }
        $this->load->model('Store_channel_model');
        $this->Store_channel_model->ensure_schema();
        $this->save_setting('tiktok_app_id', trim((string) $this->input->post('tiktok_app_id')));
        $tiktokSecret = trim((string) $this->input->post('tiktok_app_secret'));
        if ($tiktokSecret !== '') {
            $enc = $this->Store_channel_model->encrypt_setting($tiktokSecret);
            if ($enc !== false) {
                $this->save_setting('tiktok_app_secret', $enc);
            }
        }
        $this->session->set_flashdata('success', 'TikTok app settings saved.');
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
