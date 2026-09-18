<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Cron extends CI_Controller {

    public function __construct()
    {
        parent::__construct();
        $this->load->model('Channel_job_model');
    }

    public function import_cli()
    {
        if (php_sapi_name() !== 'cli') {
            show_error('Forbidden', 403);
            return;
        }
        $url = trim((string) getenv('IMPORT_URL'));
        if ($url === '') {
            $this->output->set_content_type('text/plain')->set_output("IMPORT_URL missing\n");
            return;
        }
        $this->load->library('Product_importer');
        try {
            $userId = (int) getenv('IMPORT_USER_ID');
            $result = $this->product_importer->import($url, 0, $userId);
            $this->output->set_content_type('text/plain')->set_output(json_encode($result) . "\n");
        } catch (Exception $e) {
            $this->output->set_content_type('text/plain')->set_output('ERROR: ' . $e->getMessage() . "\n");
        }
    }

    public function channel_jobs()
    {
        if (php_sapi_name() !== 'cli') {
            $key = (string) $this->input->get_post('key');
            $expected = (string) platform_setting('channel_cron_key', '');
            if ($expected === '' || !hash_equals($expected, $key)) {
                show_error('Forbidden', 403);
                return;
            }
        }
        $count = $this->Channel_job_model->process_batch(10);
        $this->output->set_content_type('text/plain')->set_output('processed=' . (int) $count);
    }
}
