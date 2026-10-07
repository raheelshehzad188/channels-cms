<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Ai_settings extends CI_Controller {

    public function __construct()
    {
        parent::__construct();
        ec_require_admin();
        $this->load->library('Gemini_content');
    }

    public function index()
    {
        $key = $this->gemini_content->api_key();
        $this->template->admin('ai_settings/index', array(
            'title' => 'AI Settings',
            'bilingual_instruction' => $this->gemini_content->bilingual_instruction('sv'),
            'agent' => array(
                'api_key' => $key,
                'api_key_set' => $key !== '',
                'api_key_hint' => $this->mask_key($key),
                'api_url' => $this->gemini_content->api_url(),
            ),
        ));
    }

    public function save()
    {
        $key = trim((string) $this->input->post('api_key'));
        if ($key !== '') {
            $this->save_setting('ai_agent_api_key', $key);
        }
        $this->session->set_flashdata('success', 'AI settings saved.');
        redirect('/admin/ai-settings');
    }

    public function test()
    {
        if (function_exists('set_time_limit')) {
            @set_time_limit(90);
        }
        $this->output->set_content_type('application/json');
        if (!$this->gemini_content->has_api_key()) {
            $this->output->set_status_header(400)->set_output(json_encode(array(
                'ok' => false,
                'error' => 'Save the agent API key first.',
            )));
            return;
        }
        $probe = (object) array(
            'name' => '4-pack LED Finger Projection Lights',
            'short_details' => 'LED party finger lights.',
            'details' => 'Battery powered LED projection finger lights for Halloween parties. Plastic housing. 4-pack.',
        );
        $content = $this->gemini_content->rewrite($probe, array(
            'country_id' => 11,
            'country_name' => 'Sweden',
            'country_code' => 'SE',
            'currency' => 'SEK',
            'currency_symbol' => 'Kr',
            'quantity' => 4,
        ));
        if (!$content) {
            $this->output->set_status_header(400)->set_output(json_encode(array(
                'ok' => false,
                'error' => $this->gemini_content->last_error() ?: 'AI agent test failed.',
            )));
            return;
        }
        $this->output->set_output(json_encode(array(
            'ok' => true,
            'message' => 'Connected. Sample title: ' . $content['title'],
        )));
    }

    protected function mask_key($key)
    {
        $key = trim((string) $key);
        $len = strlen($key);
        if ($len < 8) {
            return $len ? 'Saved' : '';
        }
        return substr($key, 0, 4) . str_repeat('•', max(4, $len - 8)) . substr($key, -4);
    }

    protected function save_setting($key, $value)
    {
        $exists = $this->db->where('setting_key', $key)->get('platform_settings')->row();
        if ($exists) {
            $this->db->where('setting_key', $key)->update('platform_settings', array(
                'setting_value' => (string) $value,
            ));
            return;
        }
        $this->db->insert('platform_settings', array(
            'setting_key' => $key,
            'setting_value' => (string) $value,
        ));
    }
}
