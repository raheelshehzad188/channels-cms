<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Ai_content extends CI_Controller {

    public function __construct()
    {
        parent::__construct();
        ec_require_admin();
        $this->load->model('Ai_content_model');
        $this->load->model('Country_model');
        $this->load->model('Store_model');
        $this->load->model('Store_listing_model');
        $this->load->library('Gemini_content');
        $this->Ai_content_model->ensure_schema();
    }

    public function index()
    {
        $countryId = (int) $this->input->get('country_id');
        $storeId = (int) $this->input->get('store_id');
        $job = null;
        if ($storeId) {
            $stores = $this->Store_listing_model->filter_stores($countryId);
            $valid = false;
            foreach ($stores as $store) {
                if ((int) $store->id === $storeId) {
                    $valid = true;
                    break;
                }
            }
            if (!$valid) {
                $storeId = 0;
                $stores = $this->Store_listing_model->filter_stores($countryId);
            } else {
                $job = $this->Ai_content_model->active_job_for_store($storeId);
                if (!$job) {
                    $job = $this->Ai_content_model->latest_job_for_store($storeId);
                }
            }
        } else {
            $job = $this->Ai_content_model->latest_active_job();
            if ($job) {
                $storeId = (int) $job->store_id;
                $countryId = (int) $job->country_id;
            }
            $stores = $this->Store_listing_model->filter_stores($countryId);
        }
        $this->template->admin('ai_content/index', array(
            'title' => 'AI Content Rewrite',
            'countries' => $this->Country_model->all(),
            'stores' => $stores,
            'country_id' => $countryId,
            'store_id' => $storeId,
            'has_api_key' => $this->gemini_content->has_api_key(),
            'job' => $job,
            'progress' => $job ? $this->Ai_content_model->progress_payload($job) : null,
        ));
    }

    public function stores()
    {
        $countryId = (int) $this->input->get_post('country_id');
        $rows = $this->Store_listing_model->filter_stores($countryId);
        $out = array();
        foreach ($rows as $row) {
            $out[] = array(
                'id' => (int) $row->id,
                'name' => $row->name,
                'domain' => $row->domain,
                'country_id' => (int) $row->country_id,
            );
        }
        $this->json($out);
    }

    public function load_store()
    {
        $countryId = (int) $this->input->post('country_id');
        $storeId = (int) $this->input->post('store_id');
        $store = $this->validated_store($storeId, $countryId);
        if (!$store) {
            $this->json_error('Select a valid country and store.');
            return;
        }
        $total = $this->Ai_content_model->store_product_count($storeId);
        $active = $this->Ai_content_model->active_job_for_store($storeId);
        $latest = $active ? $active : $this->Ai_content_model->latest_job_for_store($storeId);
        $processed = 0;
        $successful = 0;
        $failed = 0;
        $remaining = $total;
        if ($latest) {
            $processed = (int) $latest->processed_products;
            $successful = (int) $latest->successful_products;
            $failed = (int) $latest->failed_products;
            $remaining = (int) $latest->remaining_products;
        }
        $this->json(array(
            'ok' => true,
            'store' => array(
                'id' => (int) $store->id,
                'name' => $store->name,
                'country_id' => (int) $store->country_id,
                'country_name' => $store->country_name,
            ),
            'total_products' => $total,
            'processed_products' => $processed,
            'successful_products' => $successful,
            'failed_products' => $failed,
            'remaining_products' => $remaining,
            'active_job' => $active ? $this->Ai_content_model->progress_payload($active) : null,
            'latest_job' => $latest ? $this->Ai_content_model->progress_payload($latest) : null,
            'already_running' => $active ? true : false,
        ));
    }

    public function start()
    {
        $countryId = (int) $this->input->post('country_id');
        $storeId = (int) $this->input->post('store_id');
        $store = $this->validated_store($storeId, $countryId);
        if (!$store) {
            $this->json_error('Select a valid country and store.');
            return;
        }
        if (!$this->gemini_content->has_api_key()) {
            $this->json_error('Add the agent API key in Admin > AI Settings before starting.');
            return;
        }
        $active = $this->Ai_content_model->active_job_for_store($storeId);
        if ($active) {
            $this->json(array(
                'ok' => false,
                'already_running' => true,
                'error' => 'An AI content rewrite is already running for this store.',
                'job' => $this->Ai_content_model->progress_payload($active),
            ), 409);
            return;
        }

        $stopped = $this->Ai_content_model->latest_job_for_store($storeId);
        if ($stopped && $stopped->status === 'stopped' && (int) $stopped->remaining_products > 0) {
            $job = $this->Ai_content_model->resume_job($stopped->id);
        } else {
            $total = $this->Ai_content_model->store_product_count($storeId);
            if ($total < 1) {
                $this->json_error('This store has no products to rewrite.');
                return;
            }
            $job = $this->Ai_content_model->create_job((int) $store->country_id, $storeId, (int) ec_user()->UserID, array(), $this->requested_review_count());
            $job = $this->Ai_content_model->start_job($job->id);
        }
        $this->Ai_content_model->try_dispatch($job ? (int) $job->id : 0);
        $this->json(array(
            'ok' => true,
            'job' => $this->Ai_content_model->progress_payload($job),
        ));
    }

    public function progress()
    {
        $jobId = (int) $this->input->get_post('job_id');
        $storeId = (int) $this->input->get_post('store_id');
        $job = null;
        if ($jobId > 0) {
            $job = $this->Ai_content_model->get_job($jobId);
        } elseif ($storeId > 0) {
            $job = $this->Ai_content_model->active_job_for_store($storeId);
            if (!$job) {
                $job = $this->Ai_content_model->latest_job_for_store($storeId);
            }
        }
        if (!$job) {
            $this->json_error('No AI content job found.', 404);
            return;
        }
        if (in_array($job->status, array('running', 'pending'), true)) {
            $this->Ai_content_model->try_dispatch((int) $job->id);
            $job = $this->Ai_content_model->get_job($job->id);
        }
        $this->json(array(
            'ok' => true,
            'job' => $this->Ai_content_model->progress_payload($job),
        ));
    }

    public function stop()
    {
        $job = $this->job_from_request();
        if (!$job) {
            $this->json_error('Job not found.', 404);
            return;
        }
        $job = $this->Ai_content_model->request_stop($job->id);
        $this->json(array(
            'ok' => true,
            'job' => $this->Ai_content_model->progress_payload($job),
        ));
    }

    public function resume()
    {
        $job = $this->job_from_request();
        if (!$job) {
            $this->json_error('Job not found.', 404);
            return;
        }
        if (!$this->gemini_content->has_api_key()) {
            $this->json_error('Add the agent API key in Admin > AI Settings before resuming.');
            return;
        }
        $active = $this->Ai_content_model->active_job_for_store($job->store_id);
        if ($active && (int) $active->id !== (int) $job->id) {
            $this->json(array(
                'ok' => false,
                'already_running' => true,
                'error' => 'An AI content rewrite is already running for this store.',
                'job' => $this->Ai_content_model->progress_payload($active),
            ), 409);
            return;
        }
        $job = $this->Ai_content_model->resume_job($job->id);
        $this->Ai_content_model->try_dispatch((int) $job->id);
        $this->json(array(
            'ok' => true,
            'job' => $this->Ai_content_model->progress_payload($job),
        ));
    }

    public function retry()
    {
        $job = $this->job_from_request();
        if (!$job) {
            $this->json_error('Job not found.', 404);
            return;
        }
        if (!$this->gemini_content->has_api_key()) {
            $this->json_error('Add the agent API key in Admin > AI Settings before retrying.');
            return;
        }
        $active = $this->Ai_content_model->active_job_for_store($job->store_id);
        if ($active && (int) $active->id !== (int) $job->id) {
            $this->json(array(
                'ok' => false,
                'already_running' => true,
                'error' => 'An AI content rewrite is already running for this store.',
                'job' => $this->Ai_content_model->progress_payload($active),
            ), 409);
            return;
        }
        $job = $this->Ai_content_model->retry_failed($job->id);
        $this->Ai_content_model->try_dispatch((int) $job->id);
        $this->json(array(
            'ok' => true,
            'job' => $this->Ai_content_model->progress_payload($job),
        ));
    }

    public function start_new()
    {
        $countryId = (int) $this->input->post('country_id');
        $storeId = (int) $this->input->post('store_id');
        $store = $this->validated_store($storeId, $countryId);
        if (!$store) {
            $this->json_error('Select a valid country and store.');
            return;
        }
        if (!$this->gemini_content->has_api_key()) {
            $this->json_error('Add the agent API key in Admin > AI Settings before starting.');
            return;
        }
        $active = $this->Ai_content_model->active_job_for_store($storeId);
        if ($active) {
            $this->Ai_content_model->abandon_job($active->id);
        }
        $total = $this->Ai_content_model->store_product_count($storeId);
        if ($total < 1) {
            $this->json_error('This store has no products to rewrite.');
            return;
        }
        $job = $this->Ai_content_model->create_job((int) $store->country_id, $storeId, (int) ec_user()->UserID, array(), $this->requested_review_count());
        $job = $this->Ai_content_model->start_job($job->id);
        $this->Ai_content_model->try_dispatch($job ? (int) $job->id : 0);
        $this->json(array(
            'ok' => true,
            'job' => $this->Ai_content_model->progress_payload($job),
        ));
    }

    public function failed()
    {
        $job = $this->job_from_request();
        if (!$job) {
            $this->json_error('Job not found.', 404);
            return;
        }
        $rows = $this->Ai_content_model->failed_products($job->id);
        $out = array();
        foreach ($rows as $row) {
            $out[] = array(
                'id' => (int) $row->id,
                'product_id' => (int) $row->product_id,
                'name' => $row->name,
                'sku' => $row->sku,
                'error_message' => $row->error_message,
                'attempts' => (int) $row->attempts,
                'completed_at' => $row->completed_at,
            );
        }
        $this->json(array(
            'ok' => true,
            'items' => $out,
        ));
    }

    public function save_key()
    {
        $key = trim((string) $this->input->post('gemini_api_key'));
        if ($key === '') {
            $this->json_error('Paste a Gemini API key.');
            return;
        }
        $exists = $this->db->where('setting_key', 'gemini_api_key')->get('platform_settings')->row();
        if ($exists) {
            $this->db->where('setting_key', 'gemini_api_key')->update('platform_settings', array(
                'setting_value' => $key,
            ));
        } else {
            $this->db->insert('platform_settings', array(
                'setting_key' => 'gemini_api_key',
                'setting_value' => $key,
            ));
        }
        $this->json(array('ok' => true));
    }

    protected function job_from_request()
    {
        $jobId = (int) $this->input->get_post('job_id');
        if ($jobId < 1) {
            return null;
        }
        return $this->Ai_content_model->get_job($jobId);
    }

    protected function requested_review_count()
    {
        return 0;
    }

    protected function validated_store($storeId, $countryId = 0)
    {
        $storeId = (int) $storeId;
        if ($storeId < 1) {
            return null;
        }
        $store = $this->Store_model->get($storeId);
        if (!$store) {
            return null;
        }
        if ($countryId > 0 && (int) $store->country_id !== $countryId) {
            return null;
        }
        return $store;
    }

    protected function json($data, $status = 200)
    {
        if ($status !== 200) {
            $this->output->set_status_header($status);
        }
        $this->output->set_content_type('application/json')->set_output(json_encode($data));
    }

    protected function json_error($message, $status = 400)
    {
        $this->json(array('ok' => false, 'error' => $message), $status);
    }
}
