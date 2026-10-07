<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Ai_content_model extends CI_Model {

    const ACTIVE_STATUSES = array('pending', 'running', 'stopping');
    const STALE_SECONDS = 720;

    public function ensure_schema()
    {
        if (!$this->db->table_exists('ai_content_jobs')) {
            $this->db->query("CREATE TABLE IF NOT EXISTS `ai_content_jobs` (
                `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
                `country_id` int(11) NOT NULL DEFAULT 0,
                `store_id` int(11) NOT NULL DEFAULT 0,
                `status` varchar(20) NOT NULL DEFAULT 'pending',
                `total_products` int(11) NOT NULL DEFAULT 0,
                `processed_products` int(11) NOT NULL DEFAULT 0,
                `successful_products` int(11) NOT NULL DEFAULT 0,
                `failed_products` int(11) NOT NULL DEFAULT 0,
                `remaining_products` int(11) NOT NULL DEFAULT 0,
                `current_product_id` int(11) NOT NULL DEFAULT 0,
                `current_product_name` varchar(255) NOT NULL DEFAULT '',
                `current_step` varchar(80) NOT NULL DEFAULT '',
                `started_at` datetime DEFAULT NULL,
                `completed_at` datetime DEFAULT NULL,
                `last_processed_at` datetime DEFAULT NULL,
                `estimated_seconds_remaining` int(11) NOT NULL DEFAULT 0,
                `error_message` text DEFAULT NULL,
                `created_by` int(11) NOT NULL DEFAULT 0,
                `created_at` datetime DEFAULT current_timestamp(),
                `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
                PRIMARY KEY (`id`),
                KEY `store_status` (`store_id`,`status`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
        }
        if (!$this->db->table_exists('ai_content_job_products')) {
            $this->db->query("CREATE TABLE IF NOT EXISTS `ai_content_job_products` (
                `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
                `job_id` int(10) unsigned NOT NULL,
                `product_id` int(11) NOT NULL,
                `store_id` int(11) NOT NULL DEFAULT 0,
                `status` varchar(20) NOT NULL DEFAULT 'pending',
                `attempts` tinyint(3) unsigned NOT NULL DEFAULT 0,
                `started_at` datetime DEFAULT NULL,
                `completed_at` datetime DEFAULT NULL,
                `error_message` text DEFAULT NULL,
                `processing_time` int(11) NOT NULL DEFAULT 0,
                `created_at` datetime DEFAULT current_timestamp(),
                `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
                PRIMARY KEY (`id`),
                UNIQUE KEY `job_product` (`job_id`,`product_id`),
                KEY `job_status` (`job_id`,`status`),
                KEY `product_id` (`product_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
        }
        if (!$this->db->table_exists('ai_content_job_logs')) {
            $this->db->query("CREATE TABLE IF NOT EXISTS `ai_content_job_logs` (
                `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
                `job_id` int(10) unsigned NOT NULL DEFAULT 0,
                `product_id` int(11) NOT NULL DEFAULT 0,
                `product_name` varchar(255) NOT NULL DEFAULT '',
                `event_type` varchar(40) NOT NULL DEFAULT '',
                `message` varchar(500) NOT NULL DEFAULT '',
                `created_at` datetime NOT NULL DEFAULT current_timestamp(),
                PRIMARY KEY (`id`),
                KEY `job_id` (`job_id`),
                KEY `created_at` (`created_at`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
            if (isset($this->db->data_cache['table_names'])) {
                unset($this->db->data_cache['table_names']);
            }
        }
        if ($this->db->table_exists('ai_content_jobs') && !$this->db->field_exists('review_count', 'ai_content_jobs')) {
            $this->db->query("ALTER TABLE `ai_content_jobs` ADD `review_count` int(11) NOT NULL DEFAULT 5 AFTER `remaining_products`");
        }
    }

    public function store_products($storeId)
    {
        $storeId = (int) $storeId;
        if ($storeId < 1 || !$this->db->table_exists('products')) {
            return array();
        }
        return $this->db
            ->select('id, name, sku, slug, short_details, details, seo_title, seo_description, seo_keywords, brand, store_id, country_id, source_product_id')
            ->from('products')
            ->where('store_id', $storeId)
            ->order_by('id', 'asc')
            ->get()
            ->result();
    }

    public function store_product_count($storeId)
    {
        $storeId = (int) $storeId;
        if ($storeId < 1 || !$this->db->table_exists('products')) {
            return 0;
        }
        $row = $this->db->query(
            'SELECT COUNT(DISTINCT id) AS total FROM products WHERE store_id = ?',
            array($storeId)
        )->row();
        return $row ? (int) $row->total : 0;
    }

    public function active_job_for_store($storeId)
    {
        $this->ensure_schema();
        return $this->db
            ->where('store_id', (int) $storeId)
            ->where_in('status', self::ACTIVE_STATUSES)
            ->order_by('id', 'desc')
            ->limit(1)
            ->get('ai_content_jobs')
            ->row();
    }

    public function latest_job_for_store($storeId)
    {
        $this->ensure_schema();
        return $this->db
            ->where('store_id', (int) $storeId)
            ->order_by('id', 'desc')
            ->limit(1)
            ->get('ai_content_jobs')
            ->row();
    }

    public function get_job($id)
    {
        $this->ensure_schema();
        return $this->db->where('id', (int) $id)->get('ai_content_jobs')->row();
    }

    public function create_job($countryId, $storeId, $userId = 0, $productIds = array(), $reviewCount = 5)
    {
        $this->ensure_schema();
        $products = $this->store_products($storeId);
        if (is_array($productIds) && $productIds) {
            $want = array();
            foreach ($productIds as $id) {
                $want[(int) $id] = true;
            }
            $filtered = array();
            foreach ($products as $product) {
                if (isset($want[(int) $product->id])) {
                    $filtered[] = $product;
                }
            }
            $products = $filtered;
        }
        $total = count($products);
        $now = date('Y-m-d H:i:s');
        $reviewCount = (int) $reviewCount;
        if ($reviewCount < 1) {
            $reviewCount = 5;
        }
        if ($reviewCount > 50) {
            $reviewCount = 50;
        }
        $this->db->insert('ai_content_jobs', array(
            'country_id' => (int) $countryId,
            'store_id' => (int) $storeId,
            'status' => 'pending',
            'total_products' => $total,
            'processed_products' => 0,
            'successful_products' => 0,
            'failed_products' => 0,
            'remaining_products' => $total,
            'review_count' => $reviewCount,
            'created_by' => (int) $userId,
            'created_at' => $now,
            'updated_at' => $now,
        ));
        $jobId = (int) $this->db->insert_id();
        foreach ($products as $product) {
            $this->db->insert('ai_content_job_products', array(
                'job_id' => $jobId,
                'product_id' => (int) $product->id,
                'store_id' => (int) $storeId,
                'status' => 'pending',
            ));
        }
        return $this->get_job($jobId);
    }

    public function start_job($jobId)
    {
        $job = $this->get_job($jobId);
        if (!$job) {
            return null;
        }
        if (!in_array($job->status, array('pending', 'stopped'), true)) {
            return $job;
        }
        $now = date('Y-m-d H:i:s');
        $this->db->where('id', (int) $jobId)->update('ai_content_jobs', array(
            'status' => 'running',
            'started_at' => $job->started_at ? $job->started_at : $now,
            'completed_at' => null,
            'current_step' => 'Starting',
            'error_message' => '',
            'updated_at' => $now,
        ));
        $this->refresh_counts($jobId);
        $this->add_log((int) $jobId, 'started', 'Job started. ' . (int) $job->remaining_products . ' products queued.');
        return $this->get_job($jobId);
    }

    public function request_stop($jobId)
    {
        $job = $this->get_job($jobId);
        if (!$job || !in_array($job->status, array('running', 'pending'), true)) {
            return $job;
        }
        $this->db->where('id', (int) $jobId)->update('ai_content_jobs', array(
            'status' => 'stopping',
            'current_step' => 'Stopping after current product',
            'updated_at' => date('Y-m-d H:i:s'),
        ));
        $this->add_log((int) $jobId, 'stopping', 'Stop requested. Current product will finish first.');
        return $this->get_job($jobId);
    }

    public function abandon_job($jobId)
    {
        $job = $this->get_job($jobId);
        if (!$job) {
            return null;
        }
        if (in_array($job->status, array('completed', 'failed', 'stopped'), true)) {
            return $job;
        }
        $now = date('Y-m-d H:i:s');
        $this->db->where('id', (int) $jobId)->update('ai_content_jobs', array(
            'status' => 'stopped',
            'current_step' => 'Stopped for a new queue',
            'error_message' => '',
            'updated_at' => $now,
        ));
        $this->refresh_counts($jobId);
        $this->add_log((int) $jobId, 'stopped', 'Old queue stopped so a new queue can start. Current product may still finish.');
        return $this->get_job($jobId);
    }

    public function resume_job($jobId)
    {
        $job = $this->get_job($jobId);
        if (!$job || !in_array($job->status, array('stopped', 'completed'), true)) {
            return $job;
        }
        $remaining = $this->db
            ->where('job_id', (int) $jobId)
            ->where('status', 'pending')
            ->count_all_results('ai_content_job_products');
        if ($remaining < 1) {
            return $job;
        }
        $this->db->where('id', (int) $jobId)->update('ai_content_jobs', array(
            'status' => 'running',
            'completed_at' => null,
            'current_step' => 'Resuming',
            'error_message' => '',
            'updated_at' => date('Y-m-d H:i:s'),
        ));
        $this->refresh_counts($jobId);
        $this->add_log((int) $jobId, 'resumed', 'Resuming remaining products.');
        return $this->get_job($jobId);
    }

    public function retry_failed($jobId)
    {
        $job = $this->get_job($jobId);
        if (!$job) {
            return null;
        }
        $this->db->where('job_id', (int) $jobId)->where('status', 'failed')->update('ai_content_job_products', array(
            'status' => 'pending',
            'error_message' => '',
            'started_at' => null,
            'completed_at' => null,
            'processing_time' => 0,
        ));
        $this->db->where('id', (int) $jobId)->update('ai_content_jobs', array(
            'status' => 'running',
            'completed_at' => null,
            'current_step' => 'Retrying failed products',
            'error_message' => '',
            'updated_at' => date('Y-m-d H:i:s'),
        ));
        $this->refresh_counts($jobId);
        $this->add_log((int) $jobId, 'retry', 'Retrying failed products.');
        return $this->get_job($jobId);
    }

    public function failed_products($jobId)
    {
        return $this->db
            ->select('ai_content_job_products.*, products.name, products.sku')
            ->from('ai_content_job_products')
            ->join('products', 'products.id = ai_content_job_products.product_id', 'left')
            ->where('ai_content_job_products.job_id', (int) $jobId)
            ->where('ai_content_job_products.status', 'failed')
            ->order_by('ai_content_job_products.id', 'asc')
            ->get()
            ->result();
    }

    public function progress_payload($job)
    {
        if (!$job) {
            return null;
        }
        $this->load->model('admin/Store_model');
        $this->load->model('admin/Country_model');
        $store = $this->Store_model->get($job->store_id);
        $country = $job->country_id ? $this->Country_model->get($job->country_id) : null;
        $processed = (int) $job->processed_products;
        $total = max(0, (int) $job->total_products);
        $pct = $total > 0 ? round(($processed / $total) * 100, 1) : 0;
        $startedTs = $job->started_at ? strtotime($job->started_at) : 0;
        $elapsed = $startedTs ? max(0, time() - $startedTs) : 0;
        $avg = $this->average_processing_time($job->id);
        $estimateReady = $processed >= 2 && $avg > 0;
        $etaSeconds = $estimateReady ? (int) round($avg * (int) $job->remaining_products) : 0;
        return array(
            'id' => (int) $job->id,
            'status' => $job->status,
            'country_id' => (int) $job->country_id,
            'store_id' => (int) $job->store_id,
            'store_name' => $store ? $store->name : '',
            'country_name' => $country ? $country->name : ($store && !empty($store->country_name) ? $store->country_name : ''),
            'total_products' => $total,
            'processed_products' => $processed,
            'successful_products' => (int) $job->successful_products,
            'failed_products' => (int) $job->failed_products,
            'remaining_products' => (int) $job->remaining_products,
            'percent' => $pct,
            'current_product_id' => (int) $job->current_product_id,
            'current_product_name' => $job->current_product_name,
            'current_step' => $job->current_step,
            'started_at' => $job->started_at,
            'started_label' => $startedTs ? date('H:i', $startedTs) : '',
            'elapsed_seconds' => $elapsed,
            'elapsed_label' => $this->format_duration($elapsed),
            'estimated_seconds_remaining' => $etaSeconds,
            'estimated_label' => $estimateReady ? $this->format_duration($etaSeconds) : '',
            'estimated_completion' => $estimateReady ? date('H:i', time() + $etaSeconds) : '',
            'estimate_ready' => $estimateReady,
            'error_message' => $job->error_message,
            'has_api_key' => $this->gemini()->has_api_key(),
            'review_count' => $this->job_review_count($job),
            'logs' => $this->recent_logs($job->id, 60),
            'current_status' => $this->current_request_status($job),
            'activity' => $this->activity_payload($job),
        );
    }

    public function latest_active_job()
    {
        return $this->next_active_job();
    }

    protected function queue_row($jobId, $status)
    {
        return $this->db
            ->select('ai_content_job_products.product_id, ai_content_job_products.status, ai_content_job_products.started_at, products.name')
            ->from('ai_content_job_products')
            ->join('products', 'products.id = ai_content_job_products.product_id', 'left')
            ->where('ai_content_job_products.job_id', (int) $jobId)
            ->where('ai_content_job_products.status', $status)
            ->order_by('ai_content_job_products.id', 'asc')
            ->limit(1)
            ->get()
            ->row();
    }

    protected function latest_log_row($jobId)
    {
        if (!$this->db->table_exists('ai_content_job_logs')) {
            return null;
        }
        return $this->db
            ->where('job_id', (int) $jobId)
            ->order_by('id', 'desc')
            ->limit(1)
            ->get('ai_content_job_logs')
            ->row();
    }

    protected function activity_payload($job)
    {
        $workerAlive = $this->worker_is_busy();
        $lastAt = $job->last_processed_at ?: $job->updated_at ?: $job->started_at;
        $lastTs = $lastAt ? strtotime($lastAt) : 0;
        $idle = $lastTs ? max(0, time() - $lastTs) : 0;
        $processing = $this->queue_row($job->id, 'processing');
        $next = $this->queue_row($job->id, 'pending');
        $lastLog = $this->latest_log_row($job->id);
        $position = min((int) $job->total_products, (int) $job->processed_products + 1);
        if ((int) $job->remaining_products < 1) {
            $position = (int) $job->processed_products;
        }
        $nextName = '';
        if ($processing && $processing->name) {
            $nextName = $processing->name;
        } elseif ($job->current_product_name) {
            $nextName = $job->current_product_name;
        } elseif ($next && $next->name) {
            $nextName = $next->name;
        }
        $waitSeconds = 0;
        if ($processing && !empty($processing->started_at)) {
            $waitSeconds = max(0, time() - strtotime($processing->started_at));
        }
        $state = 'idle';
        $title = 'Idle';
        $detail = '';
        $stuck = false;
        $status = (string) $job->status;
        if (in_array($status, array('completed', 'failed', 'stopped'), true)) {
            $state = $status;
            $title = ucfirst($status);
            $detail = $job->current_step !== '' ? $job->current_step : $title;
        } elseif ($status === 'stopping') {
            $state = 'stopping';
            if ($workerAlive && $processing) {
                $title = 'Stopping after current product';
                $detail = 'Finishing ' . $nextName . ' before the queue stops.';
            } elseif ($workerAlive) {
                $title = 'Stopping';
                $detail = 'Worker is winding down.';
            } else {
                $stuck = true;
                $title = 'Stop requested — worker is not running';
                $detail = 'Last activity ' . $this->format_duration($idle) . ' ago.';
            }
        } elseif ($workerAlive && ($processing || (int) $job->current_product_id > 0)) {
            $state = 'working';
            $title = $job->current_step !== '' ? $job->current_step : 'Calling AI agent';
            $detail = $nextName !== '' ? $nextName : ('Product #' . (int) $job->current_product_id);
            if ($waitSeconds > 0) {
                $detail .= ' · waiting ' . $this->format_duration($waitSeconds);
            }
        } elseif ($workerAlive) {
            $state = 'starting';
            $title = 'Worker is starting';
            $detail = $nextName !== '' ? ('Next: ' . $nextName) : 'Picking the next product.';
        } elseif (in_array($status, array('running', 'pending'), true)) {
            $stuck = $idle > 20;
            $state = $stuck ? 'stuck' : 'waiting';
            $title = $stuck ? 'No worker activity' : 'Waiting for worker';
            $detail = 'Last activity ' . ($lastTs ? $this->format_duration($idle) . ' ago' : 'unknown') . '.';
            if ($nextName !== '') {
                $detail .= ' Next product: ' . $nextName . '.';
            } else {
                $detail .= ' ' . (int) $job->remaining_products . ' products queued.';
            }
        } else {
            $title = ucfirst($status);
            $detail = $job->current_step !== '' ? $job->current_step : $title;
            $state = $status;
        }
        return array(
            'state' => $state,
            'stuck' => $stuck,
            'worker_alive' => $workerAlive,
            'title' => $title,
            'detail' => $detail,
            'queue_label' => $position . ' of ' . (int) $job->total_products,
            'next_product' => $nextName,
            'last_activity_at' => $lastAt ? (string) $lastAt : '',
            'last_activity_ago' => $lastTs ? $this->format_duration($idle) . ' ago' : '—',
            'last_log' => $lastLog ? (string) $lastLog->message : '',
            'wait_seconds' => $waitSeconds,
        );
    }

    public function recent_logs($jobId, $limit = 50)
    {
        $this->ensure_schema();
        if (!$this->db->table_exists('ai_content_job_logs')) {
            return array();
        }
        $rows = $this->db
            ->where('job_id', (int) $jobId)
            ->order_by('id', 'desc')
            ->limit(max(1, (int) $limit))
            ->get('ai_content_job_logs')
            ->result();
        $out = array();
        foreach (array_reverse($rows) as $row) {
            $out[] = array(
                'id' => (int) $row->id,
                'product_id' => (int) $row->product_id,
                'product_name' => $row->product_name,
                'event_type' => $row->event_type,
                'message' => $row->message,
                'created_at' => $row->created_at,
                'time_label' => $row->created_at ? date('H:i:s', strtotime($row->created_at)) : '',
            );
        }
        return $out;
    }

    public function add_log($jobId, $eventType, $message, $productId = 0, $productName = '')
    {
        $this->ensure_schema();
        $this->db->insert('ai_content_job_logs', array(
            'job_id' => (int) $jobId,
            'product_id' => (int) $productId,
            'product_name' => substr((string) $productName, 0, 255),
            'event_type' => substr((string) $eventType, 0, 40),
            'message' => substr((string) $message, 0, 500),
            'created_at' => date('Y-m-d H:i:s'),
        ));
    }

    public function current_request_status($job)
    {
        if (!$job) {
            return 'Idle';
        }
        if ($job->status === 'running' && (int) $job->current_product_id > 0) {
            $name = $job->current_product_name !== '' ? $job->current_product_name : ('#' . $job->current_product_id);
            $step = $job->current_step !== '' ? $job->current_step : 'Processing';
            return $step . ' — ' . $name . ' (ID ' . (int) $job->current_product_id . ')';
        }
        if ($job->current_step !== '') {
            return $job->current_step;
        }
        return ucfirst((string) $job->status);
    }

    public function worker_is_busy()
    {
        $out = array();
        if (function_exists('exec')) {
            @exec('ps -eo pid=,args= 2>/dev/null', $out);
            $me = (int) getmypid();
            foreach ($out as $line) {
                if (strpos($line, 'index.php cron ai_content_jobs') === false) {
                    continue;
                }
                if (stripos($line, 'pgrep') !== false) {
                    continue;
                }
                $pid = (int) trim($line);
                if ($pid > 1 && $pid !== $me) {
                    return true;
                }
            }
        }
        $lockPath = $this->worker_lock_path();
        $fp = @fopen($lockPath, 'c');
        if (!$fp) {
            return false;
        }
        $got = flock($fp, LOCK_EX | LOCK_NB);
        if ($got) {
            flock($fp, LOCK_UN);
            fclose($fp);
            return false;
        }
        fclose($fp);
        return true;
    }

    protected function worker_lock_path()
    {
        return rtrim(sys_get_temp_dir(), '/') . '/ec_ai_content.lock';
    }

    protected function worker_log_path()
    {
        $dir = APPPATH . 'logs';
        if (is_dir($dir) && is_writable($dir)) {
            $path = $dir . '/ai_content_worker.log';
            if (!is_file($path) || is_writable($path)) {
                return $path;
            }
        }
        return rtrim(sys_get_temp_dir(), '/') . '/ec_ai_content_worker.log';
    }

    public function try_dispatch($jobId = 0)
    {
        if ($this->worker_is_busy()) {
            return;
        }
        $stamp = rtrim(sys_get_temp_dir(), '/') . '/ec_ai_content_dispatch';
        if (is_file($stamp) && (time() - (int) filemtime($stamp)) < 8) {
            return;
        }
        $active = $this->db
            ->where_in('status', array('pending', 'running', 'stopping'))
            ->limit(1)
            ->get('ai_content_jobs')
            ->row();
        if (!$active) {
            return;
        }
        $php = $this->php_cli();
        $script = FCPATH . 'index.php';
        if (!is_file($script) || ($php !== 'php' && !is_executable($php))) {
            $this->add_log((int) ($jobId ?: $active->id), 'error', 'PHP CLI was not found for the background worker.');
            return;
        }
        $disabled = array_map('trim', explode(',', (string) ini_get('disable_functions')));
        if (!function_exists('exec') || in_array('exec', $disabled, true)) {
            $this->add_log((int) ($jobId ?: $active->id), 'error', 'PHP exec() is disabled. Start the worker from CLI.');
            return;
        }
        $log = $this->worker_log_path();
        $cmd = 'cd ' . escapeshellarg(rtrim(FCPATH, '/'))
            . ' && setsid nohup ' . escapeshellarg($php) . ' -d ignore_user_abort=On -d max_execution_time=0 '
            . escapeshellarg($script) . ' cron ai_content_jobs >> ' . escapeshellarg($log) . ' 2>&1 < /dev/null &';
        @touch($stamp);
        @exec($cmd);
        $this->add_log((int) ($jobId ?: $active->id), 'worker', 'Background worker started.');
    }

    protected function php_cli()
    {
        if (defined('PHP_BINARY') && PHP_BINARY && stripos(PHP_BINARY, 'fpm') === false && is_executable(PHP_BINARY)) {
            return PHP_BINARY;
        }
        foreach (array('/usr/bin/php8.5', '/usr/bin/php8.3', '/usr/bin/php', 'php') as $bin) {
            if ($bin === 'php' || is_executable($bin)) {
                return $bin;
            }
        }
        return 'php';
    }

    public function process_next()
    {
        $this->ensure_schema();
        if (function_exists('set_time_limit')) {
            @set_time_limit(0);
        }
        if (function_exists('ignore_user_abort')) {
            @ignore_user_abort(true);
        }
        $lockPath = $this->worker_lock_path();
        $fp = @fopen($lockPath, 'c');
        if (!$fp || !flock($fp, LOCK_EX | LOCK_NB)) {
            if ($fp) {
                fclose($fp);
            }
            return 0;
        }

        $processed = 0;
        $loggedStart = false;
        try {
            while (true) {
                $this->recover_stale();
                $job = $this->next_active_job();
                if (!$job) {
                    break;
                }
                if ($job->status === 'pending') {
                    $now = date('Y-m-d H:i:s');
                    $this->db->where('id', (int) $job->id)->update('ai_content_jobs', array(
                        'status' => 'running',
                        'started_at' => $job->started_at ? $job->started_at : $now,
                        'current_step' => 'Starting',
                    ));
                    $job->status = 'running';
                }
                if (!$loggedStart) {
                    $this->add_log((int) $job->id, 'worker', 'Worker is processing the queue.');
                    $loggedStart = true;
                }
                if ($job->status === 'stopping') {
                    $busy = $this->db
                        ->where('job_id', (int) $job->id)
                        ->where('status', 'processing')
                        ->count_all_results('ai_content_job_products');
                    if ($busy < 1) {
                        $this->db->where('id', (int) $job->id)->update('ai_content_jobs', array(
                            'status' => 'stopped',
                            'current_step' => 'Stopped',
                            'current_product_id' => 0,
                            'current_product_name' => '',
                            'completed_at' => date('Y-m-d H:i:s'),
                        ));
                        $this->refresh_counts($job->id);
                        $this->add_log((int) $job->id, 'stopped', 'Job stopped.');
                        $loggedStart = false;
                        continue;
                    }
                }

                $item = $this->claim_next_product($job->id);
                if (!$item) {
                    $this->finish_if_done($job);
                    $job = $this->get_job($job->id);
                    if (!$job || !in_array($job->status, array('running', 'pending', 'stopping'), true)) {
                        $loggedStart = false;
                        continue;
                    }
                    break;
                }
                $this->process_item($job, $item);
                $processed++;
                $job = $this->get_job($job->id);
                if (!$job || !in_array($job->status, array('running', 'pending'), true)) {
                    if ($job && $job->status === 'stopping') {
                        $this->db->where('id', (int) $job->id)->update('ai_content_jobs', array(
                            'status' => 'stopped',
                            'current_step' => 'Stopped',
                            'current_product_id' => 0,
                            'current_product_name' => '',
                            'completed_at' => date('Y-m-d H:i:s'),
                        ));
                        $this->refresh_counts($job->id);
                        $this->add_log((int) $job->id, 'stopped', 'Job stopped after current product.');
                    }
                    $loggedStart = false;
                    continue;
                }
                $this->finish_if_done($job);
            }
            return $processed;
        } finally {
            flock($fp, LOCK_UN);
            fclose($fp);
        }
    }

    protected function next_active_job()
    {
        return $this->db
            ->where_in('status', array('pending', 'running', 'stopping'))
            ->order_by('id', 'asc')
            ->limit(1)
            ->get('ai_content_jobs')
            ->row();
    }

    protected function recover_stale()
    {
        $cutoff = date('Y-m-d H:i:s', time() - self::STALE_SECONDS);
        $rows = $this->db
            ->where('status', 'processing')
            ->group_start()
                ->where('started_at IS NULL', null, false)
                ->or_where('started_at <', $cutoff)
            ->group_end()
            ->get('ai_content_job_products')
            ->result();
        foreach ($rows as $row) {
            $this->db->where('id', (int) $row->id)->update('ai_content_job_products', array(
                'status' => 'failed',
                'error_message' => 'Timed out while processing.',
                'completed_at' => date('Y-m-d H:i:s'),
            ));
            $this->refresh_counts($row->job_id);
            $this->add_log((int) $row->job_id, 'failed', 'Timed out while processing.', (int) $row->product_id, '');
        }
    }

    protected function claim_next_product($jobId)
    {
        $busy = $this->db
            ->where('job_id', (int) $jobId)
            ->where('status', 'processing')
            ->get('ai_content_job_products')
            ->row();
        if ($busy) {
            return $busy;
        }
        $row = $this->db
            ->where('job_id', (int) $jobId)
            ->where('status', 'pending')
            ->order_by('id', 'asc')
            ->limit(1)
            ->get('ai_content_job_products')
            ->row();
        if (!$row) {
            return null;
        }
        $this->db
            ->where('id', (int) $row->id)
            ->where('status', 'pending')
            ->update('ai_content_job_products', array(
                'status' => 'processing',
                'started_at' => date('Y-m-d H:i:s'),
                'attempts' => (int) $row->attempts + 1,
            ));
        if ($this->db->affected_rows() < 1) {
            return null;
        }
        return $this->db->where('id', (int) $row->id)->get('ai_content_job_products')->row();
    }

    protected function process_item($job, $item)
    {
        $started = microtime(true);
        try {
            $product = $this->db->where('id', (int) $item->product_id)->get('products')->row();
            $name = $product ? $product->name : ('#' . $item->product_id);
            $this->set_step($job->id, (int) $item->product_id, $name, 'Request in process: calling AI agent');
            $job->current_product_id = (int) $item->product_id;
            $job->current_product_name = $name;
            $this->add_log((int) $job->id, 'processing', 'Request in process: calling AI agent.', (int) $item->product_id, $name);

            if (!$product || (int) $product->store_id !== (int) $job->store_id) {
                $this->fail_item($job, $item, $started, 'Product is not a listing for the selected store.');
                return;
            }

            $context = $this->generation_context($job, $product);
            $content = $this->gemini()->rewrite($product, $context);
            if (!$content) {
                $err = $this->gemini()->last_error() ?: 'AI generation failed.';
                $this->fail_item($job, $item, $started, $err);
                return;
            }

            $this->set_step($job->id, (int) $item->product_id, $name, 'AI response received, saving content');
            $saved = $this->apply_content($product, $content, (int) $job->store_id);
            if ($saved === false) {
                $this->fail_item($job, $item, $started, 'Could not save rewritten content.');
                return;
            }
            $reviewNote = $this->import_item_reviews($job, $product, $content, $context);
            $this->set_step($job->id, (int) $item->product_id, $name, 'Completed');
            $catNote = '';
            if (is_array($saved) && !empty($saved['category_label'])) {
                $catNote = ' Category: ' . $saved['category_label'] . '.';
            }
            $this->add_log((int) $job->id, 'success', 'Content saved.' . $catNote . $reviewNote, (int) $item->product_id, $name);

            $seconds = max(1, (int) round(microtime(true) - $started));
            $this->db->where('id', (int) $item->id)->update('ai_content_job_products', array(
                'status' => 'success',
                'error_message' => '',
                'completed_at' => date('Y-m-d H:i:s'),
                'processing_time' => $seconds,
            ));
            $this->refresh_counts($job->id);
        } catch (Exception $e) {
            $this->fail_item($job, $item, $started, $e->getMessage());
        }
    }

    public function apply_content($product, $content, $storeId = 0)
    {
        if (!isset($this->Product_model)) {
            $this->load->model('admin/Product_model');
        }
        $id = (int) $product->id;
        if ($id < 1) {
            return false;
        }
        $storeId = (int) $storeId;
        if ($storeId > 0 && (int) $product->store_id !== $storeId) {
            return false;
        }

        $slug = isset($content['seo_slug']) ? $content['seo_slug'] : $content['slug'];
        if ($storeId > 0) {
            $this->load->model('store/Store_product_model');
            $slug = $this->Store_product_model->unique_slug($slug, $storeId, $id);
        } else {
            $slug = $this->Product_model->unique_slug($slug, $id);
        }

        $payload = array(
            'name' => isset($content['name']) ? $content['name'] : $content['title'],
            'short_details' => isset($content['short_details']) ? $content['short_details'] : $content['short_detail'],
            'details' => isset($content['details']) ? $content['details'] : $content['long_detail'],
            'seo_title' => $content['seo_title'],
            'seo_description' => $content['seo_description'],
            'seo_keywords' => $content['seo_keywords'],
            'slug' => $slug,
        );
        if (function_exists('ensure_product_i18n_columns')) {
            ensure_product_i18n_columns();
        }
        if (!empty($content['name_en']) || !empty($content['title_en'])) {
            $payload['name_en'] = isset($content['name_en']) ? $content['name_en'] : $content['title_en'];
        }
        if (!empty($content['short_details_en']) || !empty($content['short_detail_en'])) {
            $payload['short_details_en'] = isset($content['short_details_en']) ? $content['short_details_en'] : $content['short_detail_en'];
        }
        if (!empty($content['details_en']) || !empty($content['long_detail_en'])) {
            $payload['details_en'] = isset($content['details_en']) ? $content['details_en'] : $content['long_detail_en'];
        }
        if (!empty($content['seo_title_en'])) {
            $payload['seo_title_en'] = $content['seo_title_en'];
        }
        if (!empty($content['seo_description_en'])) {
            $payload['seo_description_en'] = $content['seo_description_en'];
        }
        if (!empty($content['seo_keywords_en'])) {
            $payload['seo_keywords_en'] = $content['seo_keywords_en'];
        }
        $this->Product_model->save($payload, $id);

        $countryId = !empty($product->country_id) ? (int) $product->country_id : 0;
        if ($countryId < 1 && $storeId > 0) {
            $this->load->model('admin/Store_model');
            $store = $this->Store_model->get($storeId);
            if ($store && !empty($store->country_id)) {
                $countryId = (int) $store->country_id;
            }
        }
        $cats = $this->apply_ai_categories($product, $content, $countryId, $storeId);
        return array(
            'ok' => true,
            'category_label' => !empty($cats['label']) ? $cats['label'] : '',
            'category_id' => isset($cats['category_id']) ? (int) $cats['category_id'] : 0,
            'subcategory_id' => isset($cats['subcategory_id']) ? (int) $cats['subcategory_id'] : 0,
            'category_name' => isset($cats['category_name']) ? $cats['category_name'] : '',
            'subcategory_name' => isset($cats['subcategory_name']) ? $cats['subcategory_name'] : '',
        );
    }

    public function apply_ai_categories($product, $content, $countryId = 0, $storeId = 0)
    {
        $this->load->model('Ec_category_model');
        $categoryRef = isset($content['category']) ? $content['category'] : null;
        $subRef = isset($content['sub_category']) ? $content['sub_category'] : (isset($content['subcategory']) ? $content['subcategory'] : null);
        $countryId = (int) $countryId;
        if ($countryId < 1 && !empty($product->country_id)) {
            $countryId = (int) $product->country_id;
        }
        $storeId = (int) $storeId;
        if ($storeId < 1 && !empty($product->store_id)) {
            $storeId = (int) $product->store_id;
        }

        $resolved = $this->Ec_category_model->resolve_from_ai($countryId, $categoryRef, $subRef);
        if (empty($resolved['ids'])) {
            return $resolved;
        }

        $targets = array();
        $productId = isset($product->id) ? (int) $product->id : 0;
        if ($productId > 0) {
            $targets[$productId] = $productId;
        }
        if (!empty($product->source_product_id)) {
            $catalogId = (int) $product->source_product_id;
            $targets[$catalogId] = $catalogId;
        } elseif ($productId > 0 && empty($product->store_id)) {
            if (!isset($this->Product_model)) {
                $this->load->model('admin/Product_model');
            }
            foreach ($this->Product_model->store_copy_ids($productId) as $copyId) {
                $targets[(int) $copyId] = (int) $copyId;
            }
        }

        foreach ($targets as $targetId) {
            $this->Ec_category_model->set_product_categories($targetId, $resolved['ids']);
        }
        if ($storeId > 0) {
            $this->Ec_category_model->enable_for_store($storeId, $resolved['ids']);
        }

        $parts = array();
        if (!empty($resolved['category_name'])) {
            $parts[] = $resolved['category_name'];
        }
        if (!empty($resolved['subcategory_name'])) {
            $parts[] = $resolved['subcategory_name'];
        }
        $resolved['label'] = implode(' / ', $parts);
        return $resolved;
    }

    public function generation_context($job, $product)
    {
        $this->load->model('admin/Store_model');
        $this->load->model('admin/Country_model');
        $store = $this->Store_model->get($job ? $job->store_id : (isset($product->store_id) ? $product->store_id : 0));
        $countryId = 0;
        if ($job && $job->country_id) {
            $countryId = (int) $job->country_id;
        } elseif ($store) {
            $countryId = (int) $store->country_id;
        } elseif (!empty($product->country_id)) {
            $countryId = (int) $product->country_id;
        }
        $country = $countryId ? $this->Country_model->get($countryId) : null;
        if ($store && $country && !empty($country->code) && empty($store->country_code)) {
            $store->country_code = $country->code;
        }
        $language = $this->gemini()->language_for($country, $store);
        $currency = $country && !empty($country->currency) ? $country->currency : '';
        $symbol = '';
        if ($country && !empty($country->currency_symbol)) {
            $symbol = $country->currency_symbol;
        } elseif ($currency && function_exists('currency_symbol')) {
            $symbol = currency_symbol($currency);
        }
        return array(
            'language' => $language,
            'country_id' => $countryId,
            'country_name' => $country ? $country->name : '',
            'country_code' => $country ? $country->code : '',
            'currency' => $currency,
            'currency_symbol' => $symbol,
            'quantity' => $this->gemini()->extract_quantity(isset($product->name) ? $product->name : ''),
            'review_count' => $this->random_review_count(),
        );
    }

    public function random_review_count()
    {
        return mt_rand(3, 10);
    }

    protected function job_review_count($job)
    {
        return $this->random_review_count();
    }

    protected function import_item_reviews($job, $product, $content, $context)
    {
        $parent = $this->store_parent_listing($product);
        $target = $parent ? $parent : $product;
        if (!$target || (int) $target->store_id < 1) {
            return ' Reviews skipped (no store listing).';
        }
        $isChild = $parent && (int) $parent->id !== (int) $product->id;
        $wanted = isset($context['review_count']) ? (int) $context['review_count'] : $this->random_review_count();
        if ($wanted < 3) {
            $wanted = 3;
        }
        if ($wanted > 10) {
            $wanted = 10;
        }
        $reviews = (!empty($content['reviews']) && is_array($content['reviews'])) ? $content['reviews'] : null;
        if ($reviews && count($reviews) !== $wanted) {
            $reviews = null;
        }
        if (!$reviews && !$isChild) {
            $this->set_step($job->id, (int) $product->id, isset($product->name) ? $product->name : '', 'Generating product reviews');
            $fresh = $this->db->where('id', (int) $target->id)->get('products')->row();
            $context['features'] = $fresh ? $this->review_feature_text($fresh) : '';
            $context['variations'] = $this->review_variation_text($target);
            $reviews = $this->gemini()->generate_reviews($fresh ? $fresh : $target, $context, $wanted);
        }
        if (!$reviews) {
            if ($isChild) {
                return ' Reviews are stored on the parent listing.';
            }
            $err = $this->gemini()->last_error();
            return $err ? (' Reviews not imported: ' . substr($err, 0, 120)) : ' Reviews were not returned by the API.';
        }
        $this->load->model('Product_review_model');
        if (!$this->Product_review_model->replace_ai_generated((int) $target->store_id, (int) $target->id, $reviews)) {
            return ' Content saved; reviews could not be saved.';
        }
        return ' Reviews imported: ' . count($reviews) . '.';
    }

    protected function fail_item($job, $item, $started, $error)
    {
        $seconds = max(1, (int) round(microtime(true) - $started));
        $this->db->where('id', (int) $item->id)->update('ai_content_job_products', array(
            'status' => 'failed',
            'error_message' => substr((string) $error, 0, 2000),
            'completed_at' => date('Y-m-d H:i:s'),
            'processing_time' => $seconds,
        ));
        $this->db->where('id', (int) $job->id)->update('ai_content_jobs', array(
            'current_step' => 'Failed: ' . substr((string) $error, 0, 120),
            'last_processed_at' => date('Y-m-d H:i:s'),
        ));
        $this->refresh_counts($job->id);
        $name = !empty($job->current_product_name) ? $job->current_product_name : '';
        $this->add_log((int) $job->id, 'failed', substr((string) $error, 0, 500), (int) $item->product_id, $name);
    }

    protected function finish_if_done($job)
    {
        if (!$job) {
            return;
        }
        $pending = $this->db
            ->where('job_id', (int) $job->id)
            ->where_in('status', array('pending', 'processing'))
            ->count_all_results('ai_content_job_products');
        if ($pending > 0) {
            return;
        }
        $this->refresh_counts($job->id);
        $job = $this->get_job($job->id);
        if (!$job) {
            return;
        }
        $status = ((int) $job->failed_products > 0 && (int) $job->successful_products < 1) ? 'failed' : 'completed';
        $this->db->where('id', (int) $job->id)->update('ai_content_jobs', array(
            'status' => $status,
            'current_step' => $status === 'completed' ? 'Completed' : 'Completed with failures',
            'current_product_id' => 0,
            'current_product_name' => '',
            'completed_at' => date('Y-m-d H:i:s'),
        ));
        $this->add_log((int) $job->id, $status, $status === 'completed' ? 'All products processed.' : 'Finished with failures.');
    }

    protected function refresh_counts($jobId)
    {
        $row = $this->db->query(
            "SELECT
                COUNT(*) AS total,
                SUM(CASE WHEN status IN ('success','failed') THEN 1 ELSE 0 END) AS processed,
                SUM(CASE WHEN status = 'success' THEN 1 ELSE 0 END) AS successful,
                SUM(CASE WHEN status = 'failed' THEN 1 ELSE 0 END) AS failed,
                SUM(CASE WHEN status IN ('pending','processing') THEN 1 ELSE 0 END) AS remaining
             FROM ai_content_job_products
             WHERE job_id = ?",
            array((int) $jobId)
        )->row();
        if (!$row) {
            return;
        }
        $avg = $this->average_processing_time($jobId);
        $eta = ($avg > 0) ? (int) round($avg * (int) $row->remaining) : 0;
        $this->db->where('id', (int) $jobId)->update('ai_content_jobs', array(
            'total_products' => (int) $row->total,
            'processed_products' => (int) $row->processed,
            'successful_products' => (int) $row->successful,
            'failed_products' => (int) $row->failed,
            'remaining_products' => (int) $row->remaining,
            'estimated_seconds_remaining' => $eta,
            'last_processed_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ));
    }

    protected function set_step($jobId, $productId, $name, $step)
    {
        $this->db->where('id', (int) $jobId)->update('ai_content_jobs', array(
            'current_product_id' => (int) $productId,
            'current_product_name' => substr((string) $name, 0, 255),
            'current_step' => $step,
            'last_processed_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ));
    }

    protected function average_processing_time($jobId)
    {
        $row = $this->db->query(
            "SELECT AVG(processing_time) AS avg_time
             FROM ai_content_job_products
             WHERE job_id = ? AND status IN ('success','failed') AND processing_time > 0",
            array((int) $jobId)
        )->row();
        return $row && $row->avg_time !== null ? (float) $row->avg_time : 0;
    }

    public function format_duration($seconds)
    {
        $seconds = max(0, (int) $seconds);
        $hours = (int) floor($seconds / 3600);
        $minutes = (int) floor(($seconds % 3600) / 60);
        if ($hours > 0) {
            $label = $hours . ' hour' . ($hours === 1 ? '' : 's');
            if ($minutes > 0) {
                $label .= ' ' . $minutes . ' minute' . ($minutes === 1 ? '' : 's');
            }
            return $label;
        }
        if ($minutes > 0) {
            return $minutes . ' minute' . ($minutes === 1 ? '' : 's');
        }
        return $seconds . ' second' . ($seconds === 1 ? '' : 's');
    }

    public function rewrite_listing($productId, $reviewCount = 0)
    {
        $productId = (int) $productId;
        $reviewCount = (int) $reviewCount;
        if ($productId < 1) {
            return array('ok' => false, 'error' => 'Product not found.');
        }
        $product = $this->db->where('id', $productId)->get('products')->row();
        if (!$product) {
            return array('ok' => false, 'error' => 'Product not found.');
        }
        $target = $this->store_parent_listing($product);
        if (!$target || (int) $target->store_id < 1) {
            return array('ok' => false, 'error' => 'Open a store product page to regenerate AI content.');
        }
        $lockFile = rtrim(sys_get_temp_dir(), '/') . '/ec_ai_listing_' . (int) $target->id . '.lock';
        $lockHandle = @fopen($lockFile, 'c');
        if (!$lockHandle || !flock($lockHandle, LOCK_EX)) {
            if ($lockHandle) {
                fclose($lockHandle);
            }
            return array('ok' => false, 'error' => 'AI generation is already running for this product.');
        }
        try {
            return $this->rewrite_listing_locked($target, $reviewCount);
        } finally {
            flock($lockHandle, LOCK_UN);
            fclose($lockHandle);
        }
    }

    protected function rewrite_listing_locked($target, $reviewCount)
    {
        $reviewCount = (int) $reviewCount;
        if ($reviewCount < 1) {
            $reviewCount = $this->random_review_count();
        }
        $context = $this->generation_context(null, $target);
        $context['review_count'] = $reviewCount;
        $content = $this->gemini()->rewrite($target, $context);
        if (!$content) {
            $err = $this->gemini()->last_error() ?: 'AI generation failed.';
            if (preg_match('/ag_[A-Za-z0-9]+|AIza|Bearer\s+\S+|sk-[A-Za-z0-9]+/', $err)) {
                $err = 'AI generation failed.';
            }
            return array('ok' => false, 'error' => $err);
        }
        $saved = $this->apply_content($target, $content, (int) $target->store_id);
        if ($saved === false) {
            return array('ok' => false, 'error' => 'Could not save rewritten content.');
        }
        $fresh = $this->db->where('id', (int) $target->id)->get('products')->row();
        $slug = $fresh && !empty($fresh->slug) ? (string) $fresh->slug : '';
        $url = '';
        if ($slug !== '') {
            $url = function_exists('storefront_url') ? storefront_url('product/' . $slug) : site_url('product/' . $slug);
        }
        $result = array(
            'ok' => true,
            'product_id' => (int) $target->id,
            'name' => $fresh ? (string) $fresh->name : '',
            'slug' => $slug,
            'url' => $url,
            'category_label' => (is_array($saved) && !empty($saved['category_label'])) ? $saved['category_label'] : '',
            'content_saved' => true,
            'reviews_ok' => 0,
            'reviews_count' => 0,
        );
        if ($reviewCount > 0) {
            $context['features'] = $fresh ? $this->review_feature_text($fresh) : '';
            $context['variations'] = $this->review_variation_text($target);
            $reviews = $this->gemini()->generate_reviews($fresh ? $fresh : $target, $context, $reviewCount);
            if (!$reviews) {
                $err = $this->gemini()->last_error() ?: 'AI review generation failed.';
                if (preg_match('/ag_[A-Za-z0-9]+|AIza|Bearer\s+\S+|sk-[A-Za-z0-9]+/', $err)) {
                    $err = 'AI review generation failed.';
                }
                $result['ok'] = false;
                $result['error'] = $err;
                return $result;
            }
            $this->load->model('Product_review_model');
            if (!$this->Product_review_model->replace_ai_generated((int) $target->store_id, (int) $target->id, $reviews)) {
                $result['ok'] = false;
                $result['error'] = 'Could not save generated reviews.';
                return $result;
            }
            $result['reviews_ok'] = 1;
            $result['reviews_count'] = count($reviews);
            if ($result['url'] !== '') {
                $result['url'] .= (strpos($result['url'], '?') === false ? '?' : '&') . 'tab=reviews';
            }
        }
        return $result;
    }

    protected function review_feature_text($product)
    {
        $parts = array();
        foreach (array('short_details', 'details', 'description', 'brand') as $key) {
            if (empty($product->$key)) {
                continue;
            }
            $text = trim(preg_replace('/\s+/', ' ', strip_tags((string) $product->$key)));
            if ($text !== '') {
                $parts[] = $text;
            }
        }
        $out = implode(' ', $parts);
        if (function_exists('mb_substr')) {
            return mb_substr($out, 0, 1200, 'UTF-8');
        }
        return substr($out, 0, 1200);
    }

    protected function review_variation_text($parent)
    {
        if (!$parent || empty($parent->store_id) || empty($parent->sku) || !$this->db->field_exists('parent_sku', 'products')) {
            return '';
        }
        $children = $this->db
            ->select('name')
            ->where('store_id', (int) $parent->store_id)
            ->where('parent_sku', (string) $parent->sku)
            ->order_by('id', 'asc')
            ->limit(12)
            ->get('products')
            ->result();
        if (!$children) {
            return '';
        }
        $labels = array();
        foreach ($children as $child) {
            $label = function_exists('product_option_display_label')
                ? product_option_display_label($child, $parent)
                : trim((string) $child->name);
            if ($label !== '') {
                $labels[] = $label;
            }
        }
        return implode(', ', array_slice(array_unique($labels), 0, 12));
    }

    protected function store_parent_listing($product)
    {
        if (!$product || empty($product->store_id)) {
            return null;
        }
        $parentSku = ($this->db->field_exists('parent_sku', 'products') && isset($product->parent_sku))
            ? trim((string) $product->parent_sku)
            : '';
        if ($parentSku !== '') {
            $parent = $this->db
                ->where('store_id', (int) $product->store_id)
                ->where('sku', $parentSku)
                ->get('products')
                ->row();
            if ($parent) {
                return $parent;
            }
        }
        return $product;
    }

    protected function gemini()
    {
        if (!isset($this->CI->gemini_content)) {
            $this->load->library('Gemini_content');
        }
        return $this->gemini_content;
    }
}
