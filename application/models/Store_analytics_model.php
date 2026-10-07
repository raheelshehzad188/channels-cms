<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Store_analytics_model extends CI_Model {

    public function __construct()
    {
        parent::__construct();
        $this->load->helper('store_analytics');
        if (is_file(APPPATH . 'helpers/profit_calc_helper.php')) {
            $this->load->helper('profit_calc');
        }
    }

    public function ensure_schema()
    {
        $this->db->query("CREATE TABLE IF NOT EXISTS analytics_settings (
            id TINYINT NOT NULL PRIMARY KEY,
            active_minutes INT NOT NULL DEFAULT 5,
            updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
        if (!$this->db->get_where('analytics_settings', array('id' => 1))->row()) {
            $this->db->insert('analytics_settings', array('id' => 1, 'active_minutes' => 5));
        }

        $this->db->query("CREATE TABLE IF NOT EXISTS analytics_visitors (
            id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
            visitor_id VARCHAR(64) NOT NULL,
            first_seen DATETIME NOT NULL,
            last_seen DATETIME NOT NULL,
            country VARCHAR(80) NOT NULL DEFAULT '',
            country_code VARCHAR(8) NOT NULL DEFAULT '',
            device VARCHAR(20) NOT NULL DEFAULT '',
            browser VARCHAR(40) NOT NULL DEFAULT '',
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            UNIQUE KEY visitor_id (visitor_id),
            KEY last_seen (last_seen)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

        $this->db->query("CREATE TABLE IF NOT EXISTS analytics_sessions (
            id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
            session_id VARCHAR(64) NOT NULL,
            visitor_id VARCHAR(64) NOT NULL,
            store_id INT NOT NULL DEFAULT 0,
            country VARCHAR(80) NOT NULL DEFAULT '',
            country_code VARCHAR(8) NOT NULL DEFAULT '',
            landing_page VARCHAR(500) NOT NULL DEFAULT '',
            current_page VARCHAR(500) NOT NULL DEFAULT '',
            current_product_id INT NOT NULL DEFAULT 0,
            traffic_source VARCHAR(80) NOT NULL DEFAULT '',
            utm_source VARCHAR(120) NOT NULL DEFAULT '',
            utm_medium VARCHAR(120) NOT NULL DEFAULT '',
            utm_campaign VARCHAR(190) NOT NULL DEFAULT '',
            utm_content VARCHAR(190) NOT NULL DEFAULT '',
            device VARCHAR(20) NOT NULL DEFAULT '',
            browser VARCHAR(40) NOT NULL DEFAULT '',
            started_at DATETIME NOT NULL,
            last_activity DATETIME NOT NULL,
            ended_at DATETIME NULL,
            UNIQUE KEY session_id (session_id),
            KEY last_activity (last_activity),
            KEY store_last (store_id, last_activity),
            KEY visitor_id (visitor_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

        $this->db->query("CREATE TABLE IF NOT EXISTS analytics_events (
            id BIGINT NOT NULL AUTO_INCREMENT PRIMARY KEY,
            event_key VARCHAR(64) NOT NULL DEFAULT '',
            visitor_id VARCHAR(64) NOT NULL,
            session_id VARCHAR(64) NOT NULL,
            store_id INT NOT NULL DEFAULT 0,
            product_id INT NOT NULL DEFAULT 0,
            event_type VARCHAR(40) NOT NULL,
            country VARCHAR(80) NOT NULL DEFAULT '',
            country_code VARCHAR(8) NOT NULL DEFAULT '',
            page_url VARCHAR(500) NOT NULL DEFAULT '',
            page_title VARCHAR(190) NOT NULL DEFAULT '',
            referrer VARCHAR(500) NOT NULL DEFAULT '',
            traffic_source VARCHAR(80) NOT NULL DEFAULT '',
            utm_source VARCHAR(120) NOT NULL DEFAULT '',
            utm_medium VARCHAR(120) NOT NULL DEFAULT '',
            utm_campaign VARCHAR(190) NOT NULL DEFAULT '',
            utm_content VARCHAR(190) NOT NULL DEFAULT '',
            device VARCHAR(20) NOT NULL DEFAULT '',
            browser VARCHAR(40) NOT NULL DEFAULT '',
            revenue DECIMAL(12,2) NOT NULL DEFAULT 0,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            KEY created_at (created_at),
            KEY store_created (store_id, created_at),
            KEY type_created (event_type, created_at),
            KEY product_created (product_id, created_at),
            KEY session_id (session_id),
            KEY visitor_created (visitor_id, created_at),
            KEY event_key (event_key)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

        $this->db->query("CREATE TABLE IF NOT EXISTS analytics_hourly_summary (
            id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
            bucket DATETIME NOT NULL,
            store_id INT NOT NULL DEFAULT 0,
            country_code VARCHAR(8) NOT NULL DEFAULT '',
            product_id INT NOT NULL DEFAULT 0,
            event_type VARCHAR(40) NOT NULL,
            hits INT NOT NULL DEFAULT 0,
            UNIQUE KEY bucket_dim (bucket, store_id, country_code, product_id, event_type),
            KEY bucket (bucket),
            KEY store_bucket (store_id, bucket)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

        $this->db->query("CREATE TABLE IF NOT EXISTS analytics_daily_summary (
            id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
            day DATE NOT NULL,
            store_id INT NOT NULL DEFAULT 0,
            country_code VARCHAR(8) NOT NULL DEFAULT '',
            product_id INT NOT NULL DEFAULT 0,
            event_type VARCHAR(40) NOT NULL,
            hits INT NOT NULL DEFAULT 0,
            UNIQUE KEY day_dim (day, store_id, country_code, product_id, event_type),
            KEY day_idx (day),
            KEY store_day (store_id, day)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    }

    public function get_settings()
    {
        $this->db->reset_query();
        $row = $this->db->get_where('analytics_settings', array('id' => 1))->row();
        return array(
            'active_minutes' => $row ? max(1, (int) $row->active_minutes) : 5,
        );
    }

    public function save_settings($data)
    {
        $minutes = max(1, min(60, (int) (isset($data['active_minutes']) ? $data['active_minutes'] : 5)));
        $this->db->where('id', 1)->update('analytics_settings', array('active_minutes' => $minutes));
        return $this->get_settings();
    }

    /**
     * Wipe all live analytics event/session data. Keeps analytics_settings.
     */
    public function clear_all_data()
    {
        $this->ensure_schema();
        $tables = array(
            'analytics_events',
            'analytics_sessions',
            'analytics_visitors',
            'analytics_hourly_summary',
            'analytics_daily_summary',
        );
        $cleared = array();
        $this->db->query('SET FOREIGN_KEY_CHECKS=0');
        foreach ($tables as $table) {
            if ($this->db->table_exists($table)) {
                $this->db->query('TRUNCATE TABLE `' . $table . '`');
                $cleared[] = $table;
            }
        }
        $this->db->query('SET FOREIGN_KEY_CHECKS=1');
        return $cleared;
    }

    public function active_since()
    {
        $minutes = $this->get_settings()['active_minutes'];
        return date('Y-m-d H:i:s', time() - ($minutes * 60));
    }

    protected function has_table($name)
    {
        static $cache = array();
        if (!isset($cache[$name])) {
            $cache[$name] = $this->db->table_exists($name);
        }
        return $cache[$name];
    }

    public function ingest($store, $eventType, $extra = array())
    {
        $this->ensure_schema();
        $ua = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '';
        if (sa_is_bot($ua) && $eventType !== 'purchase') {
            return null;
        }

        $eventType = preg_replace('/[^a-z_]/', '', strtolower((string) $eventType));
        $allowed = array('page_view', 'product_view', 'add_to_cart', 'begin_checkout', 'payment_page_view', 'payment_attempt', 'purchase', 'heartbeat');
        if (!in_array($eventType, $allowed, true)) {
            return null;
        }

        $storeId = (int) (is_object($store) ? $store->id : $store);
        $storeCountry = '';
        $storeCountryCode = '';
        if (is_object($store)) {
            $storeCountry = isset($store->country_name) ? $store->country_name : '';
            if ($storeCountry === '' && !empty($store->country_id)) {
                $c = $this->db->where('id', (int) $store->country_id)->get('countries')->row();
                if ($c) {
                    $storeCountry = $c->name;
                    $storeCountryCode = strtoupper($c->code);
                }
            } elseif (!empty($store->country_id)) {
                $c = $this->db->where('id', (int) $store->country_id)->get('countries')->row();
                $storeCountryCode = $c ? strtoupper($c->code) : '';
            }
        }

        $visitorId = isset($extra['visitor_id']) ? $extra['visitor_id'] : sa_cookie('ec_vid');
        $sessionId = isset($extra['session_id']) ? $extra['session_id'] : sa_cookie('ec_sid');
        $visitorId = substr(preg_replace('/[^a-zA-Z0-9\-_]/', '', (string) $visitorId), 0, 64);
        $sessionId = substr(preg_replace('/[^a-zA-Z0-9\-_]/', '', (string) $sessionId), 0, 64);
        if ($visitorId === '') {
            $visitorId = sa_uuid();
        }
        if ($sessionId === '') {
            $sessionId = sa_uuid();
        }
        sa_set_cookie('ec_vid', $visitorId, 365);
        sa_set_cookie('ec_sid', $sessionId, 1);

        $clientCountry = isset($extra['country_code']) ? strtoupper(substr(preg_replace('/[^A-Za-z]/', '', $extra['country_code']), 0, 2)) : '';
        $countryCode = $clientCountry !== '' ? $clientCountry : sa_country_from_request($storeCountryCode);
        $countryName = $this->country_name($countryCode, $storeCountry);
        $device = sa_parse_device($ua);
        $browser = sa_parse_browser($ua);
        $utmSource = isset($extra['utm_source']) ? substr(trim((string) $extra['utm_source']), 0, 120) : '';
        $utmMedium = isset($extra['utm_medium']) ? substr(trim((string) $extra['utm_medium']), 0, 120) : '';
        $utmCampaign = isset($extra['utm_campaign']) ? substr(trim((string) $extra['utm_campaign']), 0, 190) : '';
        $utmContent = isset($extra['utm_content']) ? substr(trim((string) $extra['utm_content']), 0, 190) : '';
        $referrer = isset($extra['referrer']) ? substr(trim((string) $extra['referrer']), 0, 500) : (isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '');
        $source = isset($extra['traffic_source']) && $extra['traffic_source'] !== ''
            ? strtolower($extra['traffic_source'])
            : sa_traffic_source($referrer, $utmSource, $utmMedium);
        $pageUrl = isset($extra['page_url']) ? substr(trim((string) $extra['page_url']), 0, 500) : '';
        $pageTitle = isset($extra['page_title']) ? substr(trim((string) $extra['page_title']), 0, 190) : '';
        $productId = isset($extra['product_id']) ? (int) $extra['product_id'] : 0;
        $now = date('Y-m-d H:i:s');

        $visitor = $this->db->get_where('analytics_visitors', array('visitor_id' => $visitorId))->row();
        if ($visitor) {
            $this->db->where('id', $visitor->id)->update('analytics_visitors', array(
                'last_seen' => $now,
                'country' => $countryName ?: $visitor->country,
                'country_code' => $countryCode ?: $visitor->country_code,
                'device' => $device,
                'browser' => $browser,
            ));
        } else {
            $this->db->insert('analytics_visitors', array(
                'visitor_id' => $visitorId,
                'first_seen' => $now,
                'last_seen' => $now,
                'country' => $countryName,
                'country_code' => $countryCode,
                'device' => $device,
                'browser' => $browser,
            ));
        }

        $session = $this->db->get_where('analytics_sessions', array('session_id' => $sessionId))->row();
        $sessionPayload = array(
            'visitor_id' => $visitorId,
            'store_id' => $storeId,
            'country' => $countryName,
            'country_code' => $countryCode,
            'current_page' => $pageUrl !== '' ? $pageUrl : ($session ? $session->current_page : ''),
            'current_product_id' => $productId > 0 ? $productId : ($eventType === 'heartbeat' && $session ? (int) $session->current_product_id : 0),
            'traffic_source' => $source,
            'utm_source' => $utmSource,
            'utm_medium' => $utmMedium,
            'utm_campaign' => $utmCampaign,
            'utm_content' => $utmContent,
            'device' => $device,
            'browser' => $browser,
            'last_activity' => $now,
            'ended_at' => null,
        );
        if ($session) {
            if ($session->landing_page === '' && $pageUrl !== '') {
                $sessionPayload['landing_page'] = $pageUrl;
            }
            if ($productId <= 0 && $eventType !== 'heartbeat') {
                unset($sessionPayload['current_product_id']);
            }
            $this->db->where('id', $session->id)->update('analytics_sessions', $sessionPayload);
        } else {
            $sessionPayload['session_id'] = $sessionId;
            $sessionPayload['landing_page'] = $pageUrl;
            $sessionPayload['started_at'] = $now;
            $this->db->insert('analytics_sessions', $sessionPayload);
        }

        if ($eventType === 'heartbeat') {
            return array('visitor_id' => $visitorId, 'session_id' => $sessionId);
        }

        $eventKey = isset($extra['event_key']) ? substr((string) $extra['event_key'], 0, 64) : '';
        if ($eventKey !== '') {
            $dup = $this->db->where('event_key', $eventKey)->limit(1)->get('analytics_events')->row();
            if ($dup) {
                return array('visitor_id' => $visitorId, 'session_id' => $sessionId, 'duplicate' => true);
            }
        }

        $this->db->insert('analytics_events', array(
            'event_key' => $eventKey,
            'visitor_id' => $visitorId,
            'session_id' => $sessionId,
            'store_id' => $storeId,
            'product_id' => $productId,
            'event_type' => $eventType,
            'country' => $countryName,
            'country_code' => $countryCode,
            'page_url' => $pageUrl,
            'page_title' => $pageTitle,
            'referrer' => $referrer,
            'traffic_source' => $source,
            'utm_source' => $utmSource,
            'utm_medium' => $utmMedium,
            'utm_campaign' => $utmCampaign,
            'utm_content' => $utmContent,
            'device' => $device,
            'browser' => $browser,
            'revenue' => isset($extra['revenue']) ? (float) $extra['revenue'] : 0,
            'created_at' => $now,
        ));

        $bucket = date('Y-m-d H:00:00');
        $this->db->query(
            "INSERT INTO analytics_hourly_summary (bucket, store_id, country_code, product_id, event_type, hits)
             VALUES (?, ?, ?, ?, ?, 1)
             ON DUPLICATE KEY UPDATE hits = hits + 1",
            array($bucket, $storeId, $countryCode, $productId, $eventType)
        );
        $this->db->query(
            "INSERT INTO analytics_daily_summary (day, store_id, country_code, product_id, event_type, hits)
             VALUES (?, ?, ?, ?, ?, 1)
             ON DUPLICATE KEY UPDATE hits = hits + 1",
            array(date('Y-m-d'), $storeId, $countryCode, $productId, $eventType)
        );

        return array('visitor_id' => $visitorId, 'session_id' => $sessionId);
    }

    protected function country_name($code, $fallback = '')
    {
        $code = strtoupper(trim((string) $code));
        if ($code === '') {
            return $fallback !== '' ? $fallback : 'Unknown';
        }
        static $cache = array();
        if (!isset($cache[$code])) {
            $row = $this->has_table('countries') ? $this->db->where('code', $code)->get('countries')->row() : null;
            $names = array(
                'SE' => 'Sweden', 'GB' => 'United Kingdom', 'UK' => 'United Kingdom', 'RO' => 'Romania',
                'IT' => 'Italy', 'PL' => 'Poland', 'BG' => 'Bulgaria', 'MT' => 'Malta', 'US' => 'United States',
                'DE' => 'Germany', 'FR' => 'France', 'ES' => 'Spain', 'NL' => 'Netherlands', 'DK' => 'Denmark',
                'NO' => 'Norway', 'FI' => 'Finland', 'IE' => 'Ireland', 'AT' => 'Austria', 'PT' => 'Portugal',
                'PK' => 'Pakistan', 'AU' => 'Australia', 'CA' => 'Canada', 'IN' => 'India', 'AE' => 'United Arab Emirates',
            );
            $cache[$code] = $row ? $row->name : (isset($names[$code]) ? $names[$code] : $code);
        }
        return $cache[$code] ?: ($fallback !== '' ? $fallback : 'Unknown');
    }

    public function range($filters)
    {
        $preset = isset($filters['range']) ? $filters['range'] : 'today';
        $from = isset($filters['from']) ? trim((string) $filters['from']) : '';
        $to = isset($filters['to']) ? trim((string) $filters['to']) : '';
        $start = date('Y-m-d 00:00:00');
        $end = date('Y-m-d 23:59:59');
        if ($preset === 'yesterday') {
            $start = date('Y-m-d 00:00:00', strtotime('-1 day'));
            $end = date('Y-m-d 23:59:59', strtotime('-1 day'));
        } elseif ($preset === '7d' || $preset === 'week') {
            $start = date('Y-m-d 00:00:00', strtotime('-6 days'));
        } elseif ($preset === '30d') {
            $start = date('Y-m-d 00:00:00', strtotime('-29 days'));
        } elseif ($preset === 'month') {
            $start = date('Y-m-01 00:00:00');
        } elseif ($preset === 'year') {
            $start = date('Y-01-01 00:00:00');
        } elseif ($preset === 'custom' && $from !== '' && $to !== '') {
            $start = date('Y-m-d 00:00:00', strtotime($from));
            $end = date('Y-m-d 23:59:59', strtotime($to));
        } elseif ($preset === 'custom' && $from !== '') {
            $start = date('Y-m-d 00:00:00', strtotime($from));
            $end = date('Y-m-d 23:59:59', strtotime($from));
        }
        if ($preset === 'day') {
            $preset = 'today';
        }
        return array($start, $end, $preset);
    }

    protected function apply_event_filters($filters, $alias = 'analytics_events')
    {
        if (!empty($filters['store_id'])) {
            $this->db->where($alias . '.store_id', (int) $filters['store_id']);
        } elseif (!empty($filters['country_id']) && $this->has_table('stores')) {
            $this->db->where($alias . '.store_id IN (SELECT id FROM stores WHERE country_id = ' . (int) $filters['country_id'] . ')', null, false);
        }
        if (!empty($filters['country_code'])) {
            $this->db->where($alias . '.country_code', strtoupper($filters['country_code']));
        }
        if (!empty($filters['product_id'])) {
            $this->db->where($alias . '.product_id', (int) $filters['product_id']);
        }
    }

    public function count_events($type, $start, $end, $filters = array())
    {
        $this->db->from('analytics_events');
        $this->db->where('event_type', $type);
        $this->db->where('created_at >=', $start);
        $this->db->where('created_at <=', $end);
        $this->apply_event_filters($filters);
        return (int) $this->db->count_all_results();
    }

    public function count_unique($start, $end, $filters = array(), $field = 'visitor_id')
    {
        $this->db->select('COUNT(DISTINCT ' . $field . ') as n', false);
        $this->db->from('analytics_events');
        $this->db->where('created_at >=', $start);
        $this->db->where('created_at <=', $end);
        $this->apply_event_filters($filters);
        $row = $this->db->get()->row();
        return $row ? (int) $row->n : 0;
    }

    public function online_count($filters = array())
    {
        $since = $this->active_since();
        $this->db->from('analytics_sessions');
        $this->db->where('last_activity >=', $since);
        if (!empty($filters['store_id'])) {
            $this->db->where('store_id', (int) $filters['store_id']);
        }
        if (!empty($filters['country_code'])) {
            $this->db->where('country_code', strtoupper($filters['country_code']));
        }
        if (!empty($filters['product_id'])) {
            $this->db->where('current_product_id', (int) $filters['product_id']);
        }
        return (int) $this->db->count_all_results();
    }

    public function order_stats($start, $end, $filters = array())
    {
        if (!$this->db->table_exists('store_orders')) {
            return array('orders' => 0, 'revenue' => 0);
        }
        // Qualify store_orders.* — joins on stores/countries also have created_at/status.
        $this->db->select('COUNT(*) as orders, COALESCE(SUM(store_orders.total),0) as revenue', false);
        $this->db->from('store_orders');
        $this->db->where('store_orders.created_at >=', $start);
        $this->db->where('store_orders.created_at <=', $end);
        $this->db->where_not_in('store_orders.status', array('cancelled'));
        if (!empty($filters['store_id'])) {
            $this->db->where('store_orders.store_id', (int) $filters['store_id']);
        }
        if (!empty($filters['country_code']) && $this->has_table('stores')) {
            $this->db->join('stores', 'stores.id = store_orders.store_id', 'left');
            $this->db->join('countries', 'countries.id = stores.country_id', 'left');
            $this->db->where('countries.code', strtoupper($filters['country_code']));
        }
        $row = $this->db->get()->row();
        return array(
            'orders' => $row ? (int) $row->orders : 0,
            'revenue' => $row ? (float) $row->revenue : 0,
        );
    }

    public function summary($filters = array())
    {
        list($start, $end) = $this->range($filters);
        $visitors = $this->count_unique($start, $end, $filters, 'session_id');
        $unique = $this->count_unique($start, $end, $filters, 'visitor_id');
        $views = $this->count_events('product_view', $start, $end, $filters);
        $carts = $this->count_events('add_to_cart', $start, $end, $filters);
        $checkout = $this->count_events('begin_checkout', $start, $end, $filters);
        $payView = $this->count_events('payment_page_view', $start, $end, $filters);
        $payAttempt = $this->count_events('payment_attempt', $start, $end, $filters);
        $purchases = $this->count_events('purchase', $start, $end, $filters);
        $orders = $this->order_stats($start, $end, $filters);
        $orderCount = max($purchases, $orders['orders']);
        return array(
            'visitors' => $visitors,
            'unique' => $unique,
            'online' => $this->online_count($filters),
            'product_views' => $views,
            'add_to_cart' => $carts,
            'checkout' => $checkout,
            'payment_page' => $payView,
            'payment_attempt' => $payAttempt,
            'purchases' => $purchases,
            'orders' => $orderCount,
            'revenue' => $orders['revenue'],
            'conversion' => sa_pct($orderCount, $unique),
            'cart_to_checkout' => sa_pct($checkout, $carts),
            'checkout_to_purchase' => sa_pct($orderCount, $checkout),
            'start' => $start,
            'end' => $end,
        );
    }

    public function live_sessions($filters = array(), $limit = 40)
    {
        $since = $this->active_since();
        $this->db->select('analytics_sessions.*, products.name as product_name, products.analyzer_code, products.sku' . ($this->has_table('stores') ? ', stores.name as store_name' : ', \'\' as store_name'));
        $this->db->from('analytics_sessions');
        if ($this->has_table('stores')) {
            $this->db->join('stores', 'stores.id = analytics_sessions.store_id', 'left');
        }
        $this->db->join('products', 'products.id = analytics_sessions.current_product_id', 'left');
        $this->db->where('analytics_sessions.last_activity >=', $since);
        if (!empty($filters['store_id'])) {
            $this->db->where('analytics_sessions.store_id', (int) $filters['store_id']);
        }
        if (!empty($filters['country_code'])) {
            $this->db->where('analytics_sessions.country_code', strtoupper($filters['country_code']));
        }
        if (!empty($filters['product_id'])) {
            $this->db->where('analytics_sessions.current_product_id', (int) $filters['product_id']);
        }
        $this->db->order_by('analytics_sessions.last_activity', 'desc');
        $this->db->limit((int) $limit);
        return $this->db->get()->result();
    }

    public function live_feed($filters = array(), $limit = 25)
    {
        $this->db->select('analytics_events.*, products.name as product_name, products.analyzer_code, products.sku' . ($this->has_table('stores') ? ', stores.name as store_name' : ', \'\' as store_name'));
        $this->db->from('analytics_events');
        if ($this->has_table('stores')) {
            $this->db->join('stores', 'stores.id = analytics_events.store_id', 'left');
        }
        $this->db->join('products', 'products.id = analytics_events.product_id', 'left');
        $this->db->where('analytics_events.event_type !=', 'heartbeat');
        $this->db->where('analytics_events.created_at >=', date('Y-m-d H:i:s', time() - 3600));
        if (!empty($filters['store_id'])) {
            $this->db->where('analytics_events.store_id', (int) $filters['store_id']);
        }
        if (!empty($filters['country_code'])) {
            $this->db->where('analytics_events.country_code', strtoupper($filters['country_code']));
        }
        if (!empty($filters['product_id'])) {
            $this->db->where('analytics_events.product_id', (int) $filters['product_id']);
        }
        $this->db->order_by('analytics_events.id', 'desc');
        $this->db->limit((int) $limit);
        return $this->db->get()->result();
    }

    public function live_products($filters = array())
    {
        $since = $this->active_since();
        $storeSelect = $this->has_table('stores') ? 'MIN(stores.name) as store_name' : '\'\' as store_name';
        $this->db->select("analytics_sessions.current_product_id as product_id, MIN(products.name) as product_name, MIN(products.analyzer_code) as analyzer_code, MIN(products.sku) as sku, {$storeSelect}, COUNT(*) as active_visitors", false);
        $this->db->from('analytics_sessions');
        $this->db->join('products', 'products.id = analytics_sessions.current_product_id', 'left');
        if ($this->has_table('stores')) {
            $this->db->join('stores', 'stores.id = analytics_sessions.store_id', 'left');
        }
        $this->db->where('analytics_sessions.last_activity >=', $since);
        $this->db->where('analytics_sessions.current_product_id >', 0);
        if (!empty($filters['store_id'])) {
            $this->db->where('analytics_sessions.store_id', (int) $filters['store_id']);
        }
        if (!empty($filters['country_code'])) {
            $this->db->where('analytics_sessions.country_code', strtoupper($filters['country_code']));
        }
        if (!empty($filters['product_id'])) {
            $this->db->where('analytics_sessions.current_product_id', (int) $filters['product_id']);
        }
        $this->db->group_by('analytics_sessions.current_product_id');
        $this->db->order_by('active_visitors', 'desc');
        $this->db->limit(12);
        return $this->db->get()->result();
    }

    public function grouped($start, $end, $filters, $group)
    {
        $select = "COUNT(*) as hits,
            SUM(event_type = 'page_view') as page_views,
            SUM(event_type = 'product_view') as product_views,
            SUM(event_type = 'add_to_cart') as add_to_cart,
            SUM(event_type = 'begin_checkout') as checkout,
            SUM(event_type = 'payment_page_view') as payment_page,
            SUM(event_type = 'payment_attempt') as payment_attempt,
            SUM(event_type = 'purchase') as purchases,
            COUNT(DISTINCT visitor_id) as unique_visitors,
            COUNT(DISTINCT session_id) as visitors,
            SUM(revenue) as revenue";
        $this->db->select($group . ', ' . $select, false);
        $this->db->from('analytics_events');
        $this->db->where('created_at >=', $start);
        $this->db->where('created_at <=', $end);
        $this->db->where('event_type !=', 'heartbeat');
        $this->apply_event_filters($filters);
        $this->db->group_by($group);
        $this->db->order_by('visitors', 'desc');
        return $this->db->get()->result();
    }

    public function by_country($filters = array())
    {
        list($start, $end) = $this->range($filters);
        $rows = $this->grouped($start, $end, $filters, 'country_code, country');
        $online = $this->online_by('country_code', $filters);
        foreach ($rows as $row) {
            $row->online = isset($online[$row->country_code]) ? $online[$row->country_code] : 0;
            $row->conversion = sa_pct($row->purchases, $row->unique_visitors);
        }
        return $this->sort_rows($rows, $filters, 'visitors');
    }

    public function by_store($filters = array())
    {
        list($start, $end) = $this->range($filters);
        if ($this->has_table('stores')) {
            $this->db->select("analytics_events.store_id, MIN(stores.name) as store_name, MIN(stores.country_id) as country_id, MIN(countries.name) as country_name, MIN(countries.currency) as currency,
                COUNT(DISTINCT analytics_events.session_id) as visitors,
                COUNT(DISTINCT analytics_events.visitor_id) as unique_visitors,
                SUM(analytics_events.event_type = 'product_view') as product_views,
                SUM(analytics_events.event_type = 'add_to_cart') as add_to_cart,
                SUM(analytics_events.event_type = 'begin_checkout') as checkout,
                SUM(analytics_events.event_type = 'purchase') as purchases,
                SUM(analytics_events.revenue) as event_revenue", false);
            $this->db->from('analytics_events');
            $this->db->join('stores', 'stores.id = analytics_events.store_id', 'left');
            $this->db->join('countries', 'countries.id = stores.country_id', 'left');
            $this->db->where('analytics_events.created_at >=', $start);
            $this->db->where('analytics_events.created_at <=', $end);
            $this->db->where('analytics_events.event_type !=', 'heartbeat');
            $this->apply_event_filters($filters);
            $this->db->group_by('analytics_events.store_id');
            $this->db->order_by('visitors', 'desc');
            $rows = $this->db->get()->result();
        } else {
            $rows = $this->grouped($start, $end, $filters, 'store_id');
            foreach ($rows as $row) {
                $row->store_name = ((int) $row->store_id) ? ('Store #' . (int) $row->store_id) : 'Unknown store';
                $row->country_name = '';
                $row->currency = '';
                $row->event_revenue = isset($row->revenue) ? $row->revenue : 0;
            }
        }
        $online = $this->online_by('store_id', $filters);
        foreach ($rows as $row) {
            $orderFilters = $filters;
            $orderFilters['store_id'] = (int) $row->store_id;
            $orders = $this->order_stats($start, $end, $orderFilters);
            $row->orders = max((int) $row->purchases, $orders['orders']);
            $row->revenue = $orders['revenue'] > 0 ? $orders['revenue'] : (float) (isset($row->event_revenue) ? $row->event_revenue : 0);
            $row->online = isset($online[$row->store_id]) ? $online[$row->store_id] : 0;
            $row->conversion = sa_pct($row->orders, $row->unique_visitors);
            if (empty($row->store_name)) {
                $row->store_name = ((int) $row->store_id) ? ('Store #' . (int) $row->store_id) : 'Unknown store';
            }
        }
        return $rows;
    }

    public function by_product($filters = array(), $limit = 50)
    {
        list($start, $end) = $this->range($filters);
        $this->db->select("analytics_events.product_id, MIN(products.name) as product_name, MIN(products.analyzer_code) as analyzer_code, MIN(products.sku) as sku,
            " . ($this->has_table('stores') ? "MIN(stores.name) as store_name, MIN(countries.name) as country_name, MIN(countries.code) as country_code," : "'' as store_name, MIN(analytics_events.country) as country_name, MIN(analytics_events.country_code) as country_code,") . "
            COUNT(DISTINCT analytics_events.session_id) as visitors,
            COUNT(DISTINCT analytics_events.visitor_id) as unique_visitors,
            SUM(analytics_events.event_type = 'product_view') as views,
            SUM(analytics_events.event_type = 'add_to_cart') as add_to_cart,
            SUM(analytics_events.event_type = 'begin_checkout') as checkout,
            SUM(analytics_events.event_type = 'purchase') as purchases", false);
        $this->db->from('analytics_events');
        $this->db->join('products', 'products.id = analytics_events.product_id', 'left');
        if ($this->has_table('stores')) {
            $this->db->join('stores', 'stores.id = analytics_events.store_id', 'left');
            $this->db->join('countries', 'countries.id = stores.country_id', 'left');
        }
        $this->db->where('analytics_events.created_at >=', $start);
        $this->db->where('analytics_events.created_at <=', $end);
        $this->db->where('analytics_events.product_id >', 0);
        $this->apply_event_filters($filters);
        $this->db->group_by('analytics_events.product_id');
        $this->db->order_by('views', 'desc');
        $this->db->limit((int) $limit);
        $rows = $this->db->get()->result();
        $live = array();
        foreach ($this->live_products($filters) as $item) {
            $live[(int) $item->product_id] = (int) $item->active_visitors;
        }
        foreach ($rows as $row) {
            $row->active_visitors = isset($live[(int) $row->product_id]) ? $live[(int) $row->product_id] : 0;
            $row->analyzer_id = profit_analyzer_id($row);
            $row->conversion = sa_pct($row->purchases, $row->unique_visitors);
            $row->add_rate = sa_pct($row->add_to_cart, $row->views);
            $row->checkout_rate = sa_pct($row->checkout, $row->add_to_cart);
            $row->purchase_rate = sa_pct($row->purchases, $row->checkout);
        }
        return $this->sort_rows($rows, $filters, 'views');
    }

    public function by_source($filters = array())
    {
        list($start, $end) = $this->range($filters);
        $rows = $this->grouped($start, $end, $filters, 'traffic_source');
        foreach ($rows as $row) {
            $row->label = sa_source_label($row->traffic_source);
            $row->conversion = sa_pct($row->purchases, $row->unique_visitors);
        }
        return $rows;
    }

    public function by_campaign($filters = array())
    {
        list($start, $end) = $this->range($filters);
        $this->db->select("utm_campaign, utm_source, utm_medium, utm_content,
            COUNT(DISTINCT session_id) as visitors,
            SUM(event_type = 'product_view') as product_views,
            SUM(event_type = 'add_to_cart') as add_to_cart,
            SUM(event_type = 'begin_checkout') as checkout,
            SUM(event_type = 'purchase') as purchases,
            SUM(revenue) as revenue", false);
        $this->db->from('analytics_events');
        $this->db->where('created_at >=', $start);
        $this->db->where('created_at <=', $end);
        $this->db->where("utm_campaign !=", '');
        $this->apply_event_filters($filters);
        $this->db->group_by('utm_campaign, utm_source');
        $this->db->order_by('visitors', 'desc');
        $this->db->limit(20);
        return $this->db->get()->result();
    }

    public function by_device($filters = array())
    {
        list($start, $end) = $this->range($filters);
        $rows = $this->grouped($start, $end, $filters, 'device');
        foreach ($rows as $row) {
            $row->conversion = sa_pct($row->purchases, $row->unique_visitors);
        }
        return $rows;
    }

    public function trend($filters = array())
    {
        list($start, $end, $preset) = $this->range($filters);
        $hourly = ($preset === 'today' || $preset === 'yesterday' || $preset === 'day');
        $format = $hourly ? '%Y-%m-%d %H:00:00' : '%Y-%m-%d';
        $this->db->select("DATE_FORMAT(created_at, '" . ($hourly ? '%Y-%m-%d %H:00:00' : '%Y-%m-%d') . "') as bucket,
            COUNT(DISTINCT session_id) as visitors,
            COUNT(DISTINCT visitor_id) as unique_visitors,
            SUM(event_type = 'product_view') as product_views,
            SUM(event_type = 'add_to_cart') as add_to_cart,
            SUM(event_type = 'begin_checkout') as checkout,
            SUM(event_type = 'purchase') as orders", false);
        $this->db->from('analytics_events');
        $this->db->where('created_at >=', $start);
        $this->db->where('created_at <=', $end);
        $this->db->where('event_type !=', 'heartbeat');
        $this->apply_event_filters($filters);
        $this->db->group_by('bucket');
        $this->db->order_by('bucket', 'asc');
        $rows = $this->db->get()->result();
        $map = array();
        foreach ($rows as $row) {
            $map[$row->bucket] = $row;
        }
        $out = array();
        if ($hourly) {
            $cursor = strtotime(date('Y-m-d 00:00:00', strtotime($start)));
            $last = strtotime(date('Y-m-d 23:00:00', strtotime($end)));
            for ($t = $cursor; $t <= $last; $t += 3600) {
                $key = date('Y-m-d H:00:00', $t);
                $out[] = isset($map[$key]) ? $map[$key] : (object) array(
                    'bucket' => $key, 'visitors' => 0, 'unique_visitors' => 0, 'product_views' => 0,
                    'add_to_cart' => 0, 'checkout' => 0, 'orders' => 0,
                );
            }
        } else {
            $cursor = strtotime(date('Y-m-d', strtotime($start)));
            $last = strtotime(date('Y-m-d', strtotime($end)));
            for ($t = $cursor; $t <= $last; $t += 86400) {
                $key = date('Y-m-d', $t);
                $out[] = isset($map[$key]) ? $map[$key] : (object) array(
                    'bucket' => $key, 'visitors' => 0, 'unique_visitors' => 0, 'product_views' => 0,
                    'add_to_cart' => 0, 'checkout' => 0, 'orders' => 0,
                );
            }
        }
        return $out;
    }

    public function product_detail($productId, $filters = array())
    {
        $filters['product_id'] = (int) $productId;
        $summary = $this->summary($filters);
        $product = $this->db->select('products.*' . ($this->has_table('stores') ? ', stores.name as store_name, countries.name as country_name, countries.code as country_code' : ', \'\' as store_name, \'\' as country_name, \'\' as country_code'))
            ->from('products');
        if ($this->has_table('stores')) {
            $this->db->join('stores', 'stores.id = products.store_id', 'left');
            $this->db->join('countries', 'countries.id = products.country_id', 'left');
        }
        $product = $this->db->where('products.id', (int) $productId)->get()->row();
        if (!$product) {
            $product = $this->db->where('id', (int) $productId)->get('products')->row();
        }
        return array(
            'product' => $product,
            'analyzer_id' => $product ? profit_analyzer_id($product) : ('P' . (int) $productId),
            'summary' => $summary,
            'online' => $this->online_count($filters),
            'sources' => $this->by_source($filters),
            'countries' => $this->by_country($filters),
            'devices' => $this->by_device($filters),
            'trend' => $this->trend($filters),
            'live' => $this->live_feed($filters, 15),
        );
    }

    public function session_detail($sessionId)
    {
        $session = $this->db->select('analytics_sessions.*, products.name as product_name, products.analyzer_code, products.sku' . ($this->has_table('stores') ? ', stores.name as store_name' : ', \'\' as store_name'))
            ->from('analytics_sessions');
        if ($this->has_table('stores')) {
            $this->db->join('stores', 'stores.id = analytics_sessions.store_id', 'left');
        }
        $session = $this->db
            ->join('products', 'products.id = analytics_sessions.current_product_id', 'left')
            ->where('analytics_sessions.session_id', $sessionId)
            ->get()->row();
        if (!$session) {
            return null;
        }
        $events = $this->db->select('analytics_events.*, products.name as product_name')
            ->from('analytics_events')
            ->join('products', 'products.id = analytics_events.product_id', 'left')
            ->where('analytics_events.session_id', $sessionId)
            ->where('analytics_events.event_type !=', 'heartbeat')
            ->order_by('analytics_events.id', 'asc')
            ->get()->result();
        return array('session' => $session, 'events' => $events);
    }

    public function alerts($summary, $liveProducts, $countries, $products = array())
    {
        $out = array();
        $out[] = $summary['online'] . ' visitors currently online';
        if ($liveProducts) {
            $top = $liveProducts[0];
            $out[] = (int) $top->active_visitors . ' visitors are viewing ' . $top->product_name;
        }
        if ($countries) {
            $out[] = $countries[0]->country . ' has ' . (int) $countries[0]->visitors . ' visitors in this period';
        }
        if ($products) {
            $topP = $products[0];
            $out[] = $topP->product_name . ' received ' . sa_int($topP->views) . ' views';
        }
        $out[] = sa_int($summary['checkout']) . ' checkouts started';
        $out[] = sa_int($summary['orders']) . ' purchases completed';
        return $out;
    }

    public function product_dashboard($filters = array())
    {
        $countries = $this->product_views_by_visitor_country($filters);
        $counted = $this->product_view_totals($filters);
        $top = $this->top_viewed_products($filters, 25);
        $new = $this->new_store_products($filters, 20);
        $this->attach_product_country_splits($top, $filters);
        $this->attach_product_country_splits($new, $filters);
        list($start, $end, $preset) = $this->range($filters);
        return array(
            'total_views' => $counted['views'],
            'unique_visitors' => $counted['visitors'],
            'visitor_countries' => $countries,
            'top_products' => $top,
            'new_products' => $new,
            'trend' => $this->product_view_trend($filters),
            'range_start' => $start,
            'range_end' => $end,
            'range_preset' => $preset,
        );
    }

    public function product_view_totals($filters = array())
    {
        list($start, $end) = $this->range($filters);
        $this->db->select('COUNT(*) as views, COUNT(DISTINCT visitor_id) as visitors', false);
        $this->db->from('analytics_events');
        $this->db->where('event_type', 'product_view');
        $this->db->where('created_at >=', $start);
        $this->db->where('created_at <=', $end);
        $this->apply_event_filters($filters);
        $row = $this->db->get()->row();
        return array(
            'views' => $row ? (int) $row->views : 0,
            'visitors' => $row ? (int) $row->visitors : 0,
        );
    }

    public function product_views_by_visitor_country($filters = array())
    {
        list($start, $end) = $this->range($filters);
        $this->db->select("IFNULL(NULLIF(country_code, ''), '') as country_code, MAX(country) as country, COUNT(*) as views, COUNT(DISTINCT visitor_id) as visitors", false);
        $this->db->from('analytics_events');
        $this->db->where('event_type', 'product_view');
        $this->db->where('created_at >=', $start);
        $this->db->where('created_at <=', $end);
        $this->apply_event_filters($filters);
        $this->db->group_by('country_code');
        $this->db->order_by('views', 'desc');
        $rows = $this->db->get()->result();
        foreach ($rows as $row) {
            $code = strtoupper((string) $row->country_code);
            if ($code === '??' || $code === '') {
                $row->country_code = '';
                $row->country = $row->country ? $row->country : 'Unknown';
            } else {
                $row->country = $this->country_name($code, $row->country);
            }
        }
        return $rows;
    }

    public function top_viewed_products($filters = array(), $limit = 25)
    {
        list($start, $end) = $this->range($filters);
        $createdSelect = $this->db->field_exists('created_at', 'products') ? 'MIN(products.created_at) as created_at' : 'NULL as created_at';
        $imageSelect = $this->db->field_exists('image', 'products') ? 'MIN(products.image) as image' : '\'\' as image';
        $storeSelect = $this->has_table('stores')
            ? 'MIN(stores.name) as store_name, MIN(stores.domain) as store_domain, MIN(countries.name) as store_country'
            : '\'\' as store_name, \'\' as store_domain, \'\' as store_country';
        $this->db->select("analytics_events.product_id, MIN(products.name) as product_name, MIN(products.sku) as sku, MIN(products.store_id) as store_id,
            {$imageSelect}, {$createdSelect}, {$storeSelect},
            COUNT(*) as views, COUNT(DISTINCT analytics_events.visitor_id) as visitors", false);
        $this->db->from('analytics_events');
        $this->db->join('products', 'products.id = analytics_events.product_id', 'left');
        if ($this->has_table('stores')) {
            $this->db->join('stores', 'stores.id = analytics_events.store_id', 'left');
            $this->db->join('countries', 'countries.id = stores.country_id', 'left');
        }
        $this->db->where('analytics_events.event_type', 'product_view');
        $this->db->where('analytics_events.created_at >=', $start);
        $this->db->where('analytics_events.created_at <=', $end);
        $this->db->where('analytics_events.product_id >', 0);
        $this->apply_event_filters($filters);
        $this->db->group_by('analytics_events.product_id');
        $this->db->order_by('views', 'desc');
        $this->db->limit((int) $limit);
        $rows = $this->db->get()->result();
        foreach ($rows as $row) {
            $row->is_new = $this->is_recent_product($row);
            if (empty($row->product_name)) {
                $row->product_name = 'Product #' . (int) $row->product_id;
            }
        }
        return $rows;
    }

    public function new_store_products($filters = array(), $limit = 20)
    {
        if (!$this->db->table_exists('products')) {
            return array();
        }
        list($start, $end) = $this->range($filters);
        $hasCreated = $this->db->field_exists('created_at', 'products');
        $createdSelect = $hasCreated ? 'products.created_at' : 'NULL as created_at';
        $imageSelect = $this->db->field_exists('image', 'products') ? 'products.image' : '\'\' as image';
        $since = date('Y-m-d 00:00:00', strtotime('-30 days'));
        $startEsc = $this->db->escape($start);
        $endEsc = $this->db->escape($end);
        $storeSelect = $this->has_table('stores')
            ? 'MIN(stores.name) as store_name, MIN(stores.domain) as store_domain, MIN(countries.name) as store_country'
            : '\'\' as store_name, \'\' as store_domain, \'\' as store_country';
        $this->db->select("products.id as product_id, products.name as product_name, products.sku, products.store_id, {$imageSelect}, {$createdSelect},
            {$storeSelect}, COUNT(analytics_events.id) as views, COUNT(DISTINCT analytics_events.visitor_id) as visitors", false);
        $this->db->from('products');
        if ($this->has_table('stores')) {
            $this->db->join('stores', 'stores.id = products.store_id', 'left');
            $this->db->join('countries', 'countries.id = stores.country_id', 'left');
        }
        $this->db->join(
            'analytics_events',
            "analytics_events.product_id = products.id AND analytics_events.event_type = 'product_view' AND analytics_events.created_at >= {$startEsc} AND analytics_events.created_at <= {$endEsc}",
            'left'
        );
        $this->db->where('products.store_id >', 0);
        if ($this->db->field_exists('parent_sku', 'products')) {
            $this->db->group_start()
                ->where('products.parent_sku', '')
                ->or_where('products.parent_sku IS NULL', null, false)
            ->group_end();
        }
        if ($hasCreated) {
            $this->db->where('products.created_at >=', $since);
        }
        if (!empty($filters['store_id'])) {
            $this->db->where('products.store_id', (int) $filters['store_id']);
        } elseif (!empty($filters['country_id']) && $this->has_table('stores')) {
            $this->db->where('stores.country_id', (int) $filters['country_id']);
        }
        $this->db->group_by('products.id');
        if ($hasCreated) {
            $this->db->order_by('products.created_at', 'desc');
        } else {
            $this->db->order_by('products.id', 'desc');
        }
        $this->db->limit((int) $limit);
        $rows = $this->db->get()->result();
        foreach ($rows as $row) {
            $row->is_new = true;
            if (empty($row->product_name)) {
                $row->product_name = 'Product #' . (int) $row->product_id;
            }
        }
        return $rows;
    }

    public function product_view_trend($filters = array())
    {
        list($start, $end, $preset) = $this->range($filters);
        $mode = 'day';
        if ($preset === 'today' || $preset === 'yesterday' || $preset === 'day') {
            $mode = 'hour';
        } elseif ($preset === 'year') {
            $mode = 'month';
        }
        $format = $mode === 'hour' ? '%Y-%m-%d %H:00:00' : ($mode === 'month' ? '%Y-%m' : '%Y-%m-%d');
        $this->db->select("DATE_FORMAT(created_at, '{$format}') as bucket, COUNT(*) as views, COUNT(DISTINCT visitor_id) as visitors", false);
        $this->db->from('analytics_events');
        $this->db->where('event_type', 'product_view');
        $this->db->where('created_at >=', $start);
        $this->db->where('created_at <=', $end);
        $this->apply_event_filters($filters);
        $this->db->group_by('bucket');
        $this->db->order_by('bucket', 'asc');
        $map = array();
        foreach ($this->db->get()->result() as $row) {
            $map[$row->bucket] = $row;
        }
        $out = array();
        if ($mode === 'hour') {
            $cursor = strtotime(date('Y-m-d 00:00:00', strtotime($start)));
            $last = strtotime(date('Y-m-d 23:00:00', strtotime($end)));
            for ($t = $cursor; $t <= $last; $t += 3600) {
                $key = date('Y-m-d H:00:00', $t);
                $out[] = isset($map[$key]) ? $map[$key] : (object) array('bucket' => $key, 'views' => 0, 'visitors' => 0);
            }
        } elseif ($mode === 'month') {
            $cursor = strtotime(date('Y-m-01', strtotime($start)));
            $last = strtotime(date('Y-m-01', strtotime($end)));
            for ($t = $cursor; $t <= $last; $t = strtotime('+1 month', $t)) {
                $key = date('Y-m', $t);
                $out[] = isset($map[$key]) ? $map[$key] : (object) array('bucket' => $key, 'views' => 0, 'visitors' => 0);
            }
        } else {
            $cursor = strtotime(date('Y-m-d', strtotime($start)));
            $last = strtotime(date('Y-m-d', strtotime($end)));
            for ($t = $cursor; $t <= $last; $t += 86400) {
                $key = date('Y-m-d', $t);
                $out[] = isset($map[$key]) ? $map[$key] : (object) array('bucket' => $key, 'views' => 0, 'visitors' => 0);
            }
        }
        return $out;
    }

    protected function attach_product_country_splits($rows, $filters)
    {
        $ids = array();
        foreach ($rows as $row) {
            $id = (int) $row->product_id;
            if ($id > 0) {
                $ids[$id] = $id;
            }
            $row->countries = array();
        }
        if (!$ids) {
            return;
        }
        list($start, $end) = $this->range($filters);
        $this->db->select("product_id, IFNULL(NULLIF(country_code, ''), '??') as country_code, MAX(country) as country, COUNT(*) as views", false);
        $this->db->from('analytics_events');
        $this->db->where('event_type', 'product_view');
        $this->db->where('created_at >=', $start);
        $this->db->where('created_at <=', $end);
        $this->db->where_in('product_id', array_values($ids));
        $this->apply_event_filters($filters);
        $this->db->group_by('product_id, country_code');
        $this->db->order_by('views', 'desc');
        $map = array();
        foreach ($this->db->get()->result() as $row) {
            $pid = (int) $row->product_id;
            $code = strtoupper((string) $row->country_code);
            if ($code === '??' || $code === '') {
                $row->country_code = '';
                $row->country = $row->country ? $row->country : 'Unknown';
            } else {
                $row->country = $this->country_name($code, $row->country);
            }
            if (!isset($map[$pid])) {
                $map[$pid] = array();
            }
            $map[$pid][] = $row;
        }
        foreach ($rows as $row) {
            $pid = (int) $row->product_id;
            $row->countries = isset($map[$pid]) ? $map[$pid] : array();
        }
    }

    protected function is_recent_product($row)
    {
        $created = isset($row->created_at) ? trim((string) $row->created_at) : '';
        if ($created === '' || $created === '0000-00-00 00:00:00') {
            return false;
        }
        return strtotime($created) >= strtotime('-14 days');
    }

    protected function online_by($field, $filters)
    {
        $since = $this->active_since();
        $this->db->select($field . ', COUNT(*) as n', false);
        $this->db->from('analytics_sessions');
        $this->db->where('last_activity >=', $since);
        if (!empty($filters['store_id'])) {
            $this->db->where('store_id', (int) $filters['store_id']);
        }
        if (!empty($filters['country_code'])) {
            $this->db->where('country_code', strtoupper($filters['country_code']));
        }
        $this->db->group_by($field);
        $map = array();
        foreach ($this->db->get()->result() as $row) {
            $map[$row->{$field}] = (int) $row->n;
        }
        return $map;
    }

    protected function sort_rows($rows, $filters, $default)
    {
        $sort = isset($filters['sort']) ? $filters['sort'] : $default;
        $dir = isset($filters['dir']) && strtolower($filters['dir']) === 'asc' ? 'asc' : 'desc';
        $allowed = array('visitors', 'unique_visitors', 'views', 'product_views', 'add_to_cart', 'checkout', 'purchases', 'orders', 'conversion', 'online', 'country');
        if (!in_array($sort, $allowed, true)) {
            $sort = $default;
        }
        usort($rows, function ($a, $b) use ($sort, $dir) {
            $av = isset($a->$sort) ? $a->$sort : 0;
            $bv = isset($b->$sort) ? $b->$sort : 0;
            if ($av == $bv) {
                return 0;
            }
            $cmp = ($av < $bv) ? -1 : 1;
            return $dir === 'asc' ? $cmp : -$cmp;
        });
        return $rows;
    }
}
