<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Channel_job_model extends CI_Model {

    const MAX_ATTEMPTS = 5;

    public function ensure_schema()
    {
        if ($this->db->table_exists('store_channel_jobs')) {
            return;
        }
        $this->db->query("CREATE TABLE IF NOT EXISTS `store_channel_jobs` (
            `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
            `store_id` int(10) unsigned NOT NULL,
            `platform` varchar(20) NOT NULL DEFAULT '',
            `job_type` varchar(40) NOT NULL,
            `payload` mediumtext DEFAULT NULL,
            `status` varchar(20) NOT NULL DEFAULT 'queued',
            `attempts` tinyint(3) unsigned NOT NULL DEFAULT 0,
            `available_at` datetime DEFAULT NULL,
            `last_error` text DEFAULT NULL,
            `created_at` datetime DEFAULT current_timestamp(),
            `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
            PRIMARY KEY (`id`),
            KEY `due` (`status`,`available_at`),
            KEY `store_platform` (`store_id`,`platform`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    }

    public function enqueue($storeId, $platform, $jobType, $payload = array())
    {
        $this->ensure_schema();
        $storeId = (int) $storeId;
        $platform = (string) $platform;
        $jobType = (string) $jobType;

        if (in_array($jobType, array('sync_store', 'sync_product', 'delete_product'), true)) {
            $existing = $this->db
                ->where('store_id', $storeId)
                ->where('platform', $platform)
                ->where('job_type', $jobType)
                ->where_in('status', array('queued', 'retry'))
                ->get('store_channel_jobs')
                ->row();
            if ($existing) {
                $this->db->where('id', (int) $existing->id)->update('store_channel_jobs', array(
                    'payload' => json_encode($payload),
                    'available_at' => date('Y-m-d H:i:s'),
                ));
                return (int) $existing->id;
            }
        }

        $this->db->insert('store_channel_jobs', array(
            'store_id' => $storeId,
            'platform' => $platform,
            'job_type' => $jobType,
            'payload' => json_encode($payload),
            'status' => 'queued',
            'attempts' => 0,
            'available_at' => date('Y-m-d H:i:s'),
        ));
        return (int) $this->db->insert_id();
    }

    public function cancel_for($storeId, $platform)
    {
        $this->ensure_schema();
        return $this->db
            ->where('store_id', (int) $storeId)
            ->where('platform', $platform)
            ->where_in('status', array('queued', 'retry'))
            ->update('store_channel_jobs', array(
                'status' => 'cancelled',
                'last_error' => 'Connection disconnected',
            ));
    }

    public function try_dispatch()
    {
        if (php_sapi_name() === 'cli') {
            return;
        }
        $php = defined('PHP_BINARY') && PHP_BINARY ? PHP_BINARY : 'php';
        $script = FCPATH . 'index.php';
        if (!is_file($script)) {
            return;
        }
        $cmd = escapeshellarg($php) . ' ' . escapeshellarg($script) . ' cron channel_jobs';
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            @pclose(@popen('start /B ' . $cmd, 'r'));
            return;
        }
        @exec($cmd . ' > /dev/null 2>&1 &');
    }

    public function process_batch($limit = 8)
    {
        $this->ensure_schema();
        $this->load->model('Store_channel_model');
        $this->Store_channel_model->ensure_schema();
        $limit = max(1, min(20, (int) $limit));
        $processed = 0;

        for ($i = 0; $i < $limit; $i++) {
            $job = $this->claim_next();
            if (!$job) {
                break;
            }
            $this->run_job($job);
            $processed++;
        }
        return $processed;
    }

    protected function claim_next()
    {
        $now = date('Y-m-d H:i:s');
        $row = $this->db
            ->where_in('status', array('queued', 'retry'))
            ->group_start()
                ->where('available_at IS NULL', null, false)
                ->or_where('available_at <=', $now)
            ->group_end()
            ->order_by('id', 'asc')
            ->limit(1)
            ->get('store_channel_jobs')
            ->row();
        if (!$row) {
            return null;
        }
        $this->db
            ->where('id', (int) $row->id)
            ->where_in('status', array('queued', 'retry'))
            ->update('store_channel_jobs', array(
                'status' => 'processing',
                'attempts' => (int) $row->attempts + 1,
            ));
        if ($this->db->affected_rows() < 1) {
            return null;
        }
        $row->attempts = (int) $row->attempts + 1;
        $row->payload_data = json_decode($row->payload, true);
        if (!is_array($row->payload_data)) {
            $row->payload_data = array();
        }
        return $row;
    }

    protected function run_job($job)
    {
        $ok = false;
        $retryable = false;
        $error = '';
        try {
            $result = $this->execute($job);
            $ok = !empty($result['ok']);
            $retryable = !empty($result['retryable']);
            $error = isset($result['error']) ? $result['error'] : '';
        } catch (Exception $e) {
            $ok = false;
            $retryable = true;
            $error = $e->getMessage();
        }

        if ($ok) {
            $this->db->where('id', (int) $job->id)->update('store_channel_jobs', array(
                'status' => 'done',
                'last_error' => '',
            ));
            return;
        }

        if ($retryable && (int) $job->attempts < self::MAX_ATTEMPTS) {
            $delay = min(900, 30 * pow(2, max(0, (int) $job->attempts - 1)));
            $this->db->where('id', (int) $job->id)->update('store_channel_jobs', array(
                'status' => 'retry',
                'available_at' => date('Y-m-d H:i:s', time() + $delay),
                'last_error' => substr($error, 0, 2000),
            ));
            return;
        }

        $this->db->where('id', (int) $job->id)->update('store_channel_jobs', array(
            'status' => 'failed',
            'last_error' => substr($error, 0, 2000),
        ));
        if ($job->platform !== '') {
            $this->Store_channel_model->mark_error($job->store_id, $job->platform, $error);
        }
    }

    protected function execute($job)
    {
        $store = $this->db->where('id', (int) $job->store_id)->get('stores')->row();
        if (!$store) {
            return array('ok' => false, 'retryable' => false, 'error' => 'Store not found.');
        }
        $store = hydrate_store_currency($store);
        $type = $job->job_type;
        $platform = $job->platform;
        $payload = $job->payload_data;

        if ($type === 'sync_store') {
            $errors = $this->Store_channel_model->sync_store($store, $platform);
            return array(
                'ok' => empty($errors),
                'retryable' => $this->Store_channel_model->last_retryable,
                'error' => empty($errors) ? '' : implode(' ', $errors),
            );
        }
        if ($type === 'sync_product') {
            $this->Store_channel_model->sync_product($store, isset($payload['product_id']) ? $payload['product_id'] : 0);
            return array(
                'ok' => $this->Store_channel_model->last_job_ok,
                'retryable' => $this->Store_channel_model->last_retryable,
                'error' => $this->Store_channel_model->last_job_error,
            );
        }
        if ($type === 'delete_product') {
            $this->Store_channel_model->delete_product($store, isset($payload['product_id']) ? $payload['product_id'] : 0);
            return array('ok' => true, 'retryable' => false, 'error' => '');
        }
        if ($type === 'meta_event' || $type === 'tiktok_event') {
            return $this->Store_channel_model->send_queued_event($store, $platform, $payload);
        }
        return array('ok' => false, 'retryable' => false, 'error' => 'Unknown job type.');
    }
}
