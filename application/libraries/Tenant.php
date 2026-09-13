<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Tenant {

    protected $CI;
    protected $store = null;
    protected $theme = null;
    protected $settings = array();
    protected $resolved = false;

    public function __construct()
    {
        $this->CI =& get_instance();
        $this->CI->load->database();
    }

    public function resolve($domainHint = null)
    {
        if ($this->resolved) {
            return $this->store;
        }

        $host = $domainHint ? strtolower(trim($domainHint)) : $this->current_host();
        $this->store = $this->findByHost($this->normalize_host($host));
        $this->resolved = true;
        if ($this->store) {
            $this->store = hydrate_store_currency($this->store);
            $this->loadTheme();
        }
        return $this->store;
    }

    public function current_host()
    {
        $host = isset($_SERVER['HTTP_HOST']) ? strtolower(trim($_SERVER['HTTP_HOST'])) : '';
        return preg_replace('/:\d+$/', '', $host);
    }

    public function is_storefront()
    {
        $host = $this->current_host();
        return $host !== '' && !in_array($host, array('localhost', '127.0.0.1'), true);
    }

    protected function normalize_host($host)
    {
        if (preg_match('/^(theme[12]|fruitables|zenvello)\.localhost$/', $host, $match)) {
            return $match[1] . '.ecommerce.test';
        }
        if (strpos($host, 'www.') === 0) {
            $host = substr($host, 4);
        }
        return $host;
    }

    protected function findByHost($host)
    {
        if ($host === '') {
            return null;
        }

        return $this->CI->db
            ->select('stores.*, countries.currency as country_currency, countries.name as country_name')
            ->from('stores')
            ->join('countries', 'countries.id = stores.country_id', 'left')
            ->where('stores.status', 1)
            ->where('stores.domain', $host)
            ->get()
            ->row();
    }

    protected function loadTheme()
    {
        if (!$this->store || empty($this->store->theme_id)) {
            return;
        }

        $this->theme = $this->CI->db->where('id', $this->store->theme_id)->get('themes')->row();
        $rows = $this->CI->db
            ->where('store_id', $this->store->id)
            ->get('store_settings')
            ->result();

        $this->settings = array();
        foreach ($rows as $row) {
            $this->settings[$row->field_key] = $row->field_value;
        }
    }

    public function set_store($store)
    {
        $this->store = $store;
        $this->resolved = true;
        $this->theme = null;
        $this->settings = array();
        if ($this->store) {
            $this->store = hydrate_store_currency($this->store);
            $this->loadTheme();
        }
        return $this->store;
    }

    public function get_store()
    {
        return $this->store;
    }

    public function get_theme()
    {
        return $this->theme;
    }

    public function get_settings()
    {
        return $this->settings;
    }

    public function setting($key, $default = '')
    {
        return isset($this->settings[$key]) && $this->settings[$key] !== ''
            ? $this->settings[$key]
            : $default;
    }
}
