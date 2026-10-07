<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Product_analyzer_model extends CI_Model {

    public function ensure_schema()
    {
        if (!$this->db->table_exists('products')) {
            return;
        }
        if (!$this->db->field_exists('analyzer_code', 'products')) {
            $this->db->query("ALTER TABLE products ADD COLUMN analyzer_code VARCHAR(64) NOT NULL DEFAULT '' AFTER sku");
            $this->db->query("ALTER TABLE products ADD INDEX analyzer_code (analyzer_code)");
        }
        if (!$this->db->field_exists('cost_price', 'products')) {
            $this->db->query("ALTER TABLE products ADD COLUMN cost_price DECIMAL(12,2) NOT NULL DEFAULT 0 AFTER price");
        }
        if (!$this->db->field_exists('shipping_cost', 'products')) {
            $after = $this->db->field_exists('cost_price', 'products') ? ' AFTER cost_price' : '';
            $this->db->query("ALTER TABLE products ADD COLUMN shipping_cost DECIMAL(12,2) NOT NULL DEFAULT 0" . $after);
        }

        $this->db->query("CREATE TABLE IF NOT EXISTS analyzer_settings (
            id TINYINT NOT NULL PRIMARY KEY,
            target_profit DECIMAL(12,2) NOT NULL DEFAULT 100,
            daily_ad_budget DECIMAL(12,2) NOT NULL DEFAULT 6000,
            expected_orders_per_day DECIMAL(12,2) NOT NULL DEFAULT 5,
            payment_fee_percent DECIMAL(8,4) NOT NULL DEFAULT 2.9,
            payment_fixed_fee DECIMAL(12,2) NOT NULL DEFAULT 1,
            other_cost DECIMAL(12,2) NOT NULL DEFAULT 10,
            return_rate DECIMAL(8,4) NOT NULL DEFAULT 5,
            default_currency VARCHAR(10) NOT NULL DEFAULT 'SEK',
            ad_budget_currency VARCHAR(10) NOT NULL DEFAULT 'PKR',
            manual_cpa DECIMAL(12,2) NULL,
            actual_ad_spend DECIMAL(12,2) NULL,
            actual_orders DECIMAL(12,2) NULL,
            updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
        if ($this->db->table_exists('analyzer_settings') && !$this->db->field_exists('ad_budget_currency', 'analyzer_settings')) {
            $this->db->query("ALTER TABLE analyzer_settings ADD COLUMN ad_budget_currency VARCHAR(10) NOT NULL DEFAULT 'PKR' AFTER default_currency");
        }

        $this->db->query("CREATE TABLE IF NOT EXISTS analyzer_currency_rates (
            id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
            currency VARCHAR(10) NOT NULL,
            rate_to_pkr DECIMAL(12,4) NOT NULL,
            updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            UNIQUE KEY currency (currency)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

        $this->db->query("CREATE TABLE IF NOT EXISTS product_analyzer_meta (
            product_id INT NOT NULL PRIMARY KEY,
            payment_fee_percent DECIMAL(8,4) NULL,
            payment_fixed_fee DECIMAL(12,2) NULL,
            other_cost DECIMAL(12,2) NULL,
            return_rate DECIMAL(8,4) NULL,
            manual_cpa DECIMAL(12,2) NULL,
            actual_cpa DECIMAL(12,2) NULL,
            actual_ad_spend DECIMAL(12,2) NULL,
            actual_orders DECIMAL(12,2) NULL,
            updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

        $this->db->query("CREATE TABLE IF NOT EXISTS product_supplier_options (
            id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
            product_id INT NOT NULL,
            supplier_id INT NOT NULL,
            supplier_product_url VARCHAR(500) NOT NULL DEFAULT '',
            product_cost DECIMAL(12,2) NOT NULL DEFAULT 0,
            shipping_cost DECIMAL(12,2) NOT NULL DEFAULT 0,
            currency VARCHAR(10) NOT NULL DEFAULT '',
            delivery_min_days INT NULL,
            delivery_max_days INT NULL,
            moq INT NULL,
            availability VARCHAR(80) NOT NULL DEFAULT '',
            notes TEXT,
            source VARCHAR(40) NOT NULL DEFAULT '',
            checked_date DATE NULL,
            is_preferred TINYINT(1) NOT NULL DEFAULT 0,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            UNIQUE KEY product_supplier (product_id, supplier_id),
            KEY product_id (product_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

        $this->db->query("CREATE TABLE IF NOT EXISTS product_supplier_price_history (
            id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
            option_id INT NOT NULL,
            product_id INT NOT NULL,
            supplier_name VARCHAR(200) NOT NULL DEFAULT '',
            old_product_cost DECIMAL(12,2) NULL,
            new_product_cost DECIMAL(12,2) NULL,
            old_shipping_cost DECIMAL(12,2) NULL,
            new_shipping_cost DECIMAL(12,2) NULL,
            old_landed_cost DECIMAL(12,2) NULL,
            new_landed_cost DECIMAL(12,2) NULL,
            changed_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            KEY option_id (option_id),
            KEY product_id (product_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

        if (!$this->db->get_where('analyzer_settings', array('id' => 1))->row()) {
            $this->db->insert('analyzer_settings', array('id' => 1) + profit_default_settings());
        } else {
            $current = $this->db->get_where('analyzer_settings', array('id' => 1))->row();
            $patch = array();
            if ($this->db->field_exists('ad_budget_currency', 'analyzer_settings') && empty($current->ad_budget_currency)) {
                $patch['ad_budget_currency'] = 'PKR';
            }
            if ((float) $current->daily_ad_budget == 500) {
                $patch['daily_ad_budget'] = 6000;
                $patch['ad_budget_currency'] = 'PKR';
            }
            if ($patch) {
                $this->db->where('id', 1)->update('analyzer_settings', $patch);
            }
        }
        $defaults = array('SEK' => 33, 'GBP' => 375, 'EUR' => 330, 'RON' => 66, 'PLN' => 76, 'BGN' => 168, 'USD' => 278, 'AUD' => 185, 'AED' => 76, 'PKR' => 1);
        foreach ($defaults as $currency => $rate) {
            $exists = $this->db->get_where('analyzer_currency_rates', array('currency' => $currency))->row();
            if (!$exists) {
                $this->db->insert('analyzer_currency_rates', array('currency' => $currency, 'rate_to_pkr' => $rate));
            }
        }
        $this->backfill_analyzer_codes();
        $this->sync_current_supplier_options();
    }

    public function backfill_analyzer_codes()
    {
        $rows = $this->db->select('id, sku, analyzer_code')
            ->from('products')
            ->group_start()
                ->where('analyzer_code', '')
                ->or_where('analyzer_code IS NULL', null, false)
            ->group_end()
            ->get()->result();
        foreach ($rows as $row) {
            $code = trim((string) $row->sku) !== '' ? trim($row->sku) : ('P' . (int) $row->id);
            $dup = $this->db->where('analyzer_code', $code)->where('id !=', (int) $row->id)->count_all_results('products');
            if ($dup) {
                $code = 'P' . (int) $row->id;
            }
            $this->db->where('id', (int) $row->id)->update('products', array('analyzer_code' => $code));
        }
    }

    public function sync_current_supplier_options()
    {
        $rows = $this->db->select('id, supplier_id, cost_price, shipping_cost, analyzer_code')
            ->from('products')
            ->where('supplier_id IS NOT NULL', null, false)
            ->where('supplier_id >', 0)
            ->get()->result();
        foreach ($rows as $row) {
            $exists = $this->db->get_where('product_supplier_options', array(
                'product_id' => (int) $row->id,
                'supplier_id' => (int) $row->supplier_id,
            ))->row();
            if ($exists) {
                continue;
            }
            $this->db->insert('product_supplier_options', array(
                'product_id' => (int) $row->id,
                'supplier_id' => (int) $row->supplier_id,
                'product_cost' => (float) $row->cost_price,
                'shipping_cost' => (float) $row->shipping_cost,
                'source' => 'current',
                'is_preferred' => 1,
            ));
        }
    }

    public function get_settings()
    {
        $row = $this->db->get_where('analyzer_settings', array('id' => 1))->row_array();
        $settings = $row ? array_merge(profit_default_settings(), $row) : profit_default_settings();
        $settings['ad_budget_currency'] = profit_budget_currency($settings);
        foreach (array('manual_cpa', 'actual_ad_spend', 'actual_orders') as $key) {
            if (!array_key_exists($key, $settings) || profit_blank($settings[$key])) {
                $settings[$key] = null;
            }
        }
        return $settings;
    }

    public function save_settings($data)
    {
        $current = $this->get_settings();
        $value = function ($key, $emptyNull = false) use ($data, $current) {
            if (!array_key_exists($key, $data)) {
                return $current[$key];
            }
            if ($emptyNull && ($data[$key] === '' || $data[$key] === null)) {
                return null;
            }
            return $data[$key];
        };
        $payload = array(
            'target_profit' => (float) $value('target_profit'),
            'daily_ad_budget' => (float) $value('daily_ad_budget'),
            'expected_orders_per_day' => (float) $value('expected_orders_per_day'),
            'payment_fee_percent' => (float) $value('payment_fee_percent'),
            'payment_fixed_fee' => (float) $value('payment_fixed_fee'),
            'other_cost' => (float) $value('other_cost'),
            'return_rate' => (float) $value('return_rate'),
            'default_currency' => strtoupper(trim((string) $value('default_currency'))),
            'ad_budget_currency' => strtoupper(trim((string) $value('ad_budget_currency'))) ?: 'PKR',
            'manual_cpa' => $value('manual_cpa', true) === null ? null : (float) $value('manual_cpa', true),
            'actual_ad_spend' => $value('actual_ad_spend', true) === null ? null : (float) $value('actual_ad_spend', true),
            'actual_orders' => $value('actual_orders', true) === null ? null : (float) $value('actual_orders', true),
        );
        $this->db->where('id', 1)->update('analyzer_settings', $payload);
        return $this->get_settings();
    }

    public function get_rates()
    {
        $rows = $this->db->get('analyzer_currency_rates')->result();
        $rates = array();
        foreach ($rows as $row) {
            $rates[strtoupper($row->currency)] = (float) $row->rate_to_pkr;
        }
        if (!isset($rates['PKR']) || $rates['PKR'] <= 0) {
            $rates['PKR'] = 1;
        }
        return $rates;
    }

    public function save_rates($rates)
    {
        foreach ($rates as $currency => $rate) {
            $currency = strtoupper(trim($currency));
            $rate = (float) $rate;
            if ($currency === '' || $rate <= 0) {
                continue;
            }
            $exists = $this->db->get_where('analyzer_currency_rates', array('currency' => $currency))->row();
            if ($exists) {
                $this->db->where('id', $exists->id)->update('analyzer_currency_rates', array('rate_to_pkr' => $rate));
            } else {
                $this->db->insert('analyzer_currency_rates', array('currency' => $currency, 'rate_to_pkr' => $rate));
            }
        }
        return $this->get_rates();
    }

    protected function listing_model()
    {
        $CI =& get_instance();
        if (isset($CI->Store_listing_model)) {
            return $CI->Store_listing_model;
        }
        $CI->load->model('Store_listing_model');
        if (isset($CI->Store_listing_model)) {
            return $CI->Store_listing_model;
        }
        $CI->load->model('admin/Store_listing_model');
        return isset($CI->Store_listing_model) ? $CI->Store_listing_model : null;
    }

    protected function catalog_scope($ownerId = 0)
    {
        $this->db->from('products');
        $this->db->group_start()
            ->where('products.store_id IS NULL', null, false)
            ->or_where('products.store_id', 0)
        ->group_end();
        if ($ownerId) {
            $this->db->where('products.created_by', (int) $ownerId);
        }
    }

    protected function apply_catalog_where($ownerId = 0)
    {
        $this->db->group_start()
            ->where('products.store_id IS NULL', null, false)
            ->or_where('products.store_id', 0)
        ->group_end();
        if ($ownerId) {
            $this->db->where('products.created_by', (int) $ownerId);
        }
    }

    public function find_by_code($code, $ownerId = 0)
    {
        $code = trim((string) $code);
        if ($code === '') {
            return null;
        }
        $this->apply_catalog_where($ownerId);
        $row = $this->db->group_start()
                ->where('analyzer_code', $code)
                ->or_where('sku', $code)
            ->group_end()
            ->get('products')->row();
        if ($row) {
            return $row;
        }
        if (preg_match('/^P(\d+)$/i', $code, $m)) {
            $this->apply_catalog_where($ownerId);
            $row = $this->db->where('id', (int) $m[1])->get('products')->row();
            if ($row) {
                return $row;
            }
            $code = $m[1];
        }
        if (ctype_digit($code)) {
            $this->apply_catalog_where($ownerId);
            $row = $this->db->where('id', (int) $code)->get('products')->row();
            if ($row) {
                return $row;
            }
        }
        if ($this->db->field_exists('source_product_id', 'products')) {
            $this->db->group_start()
                    ->where('analyzer_code', $code)
                    ->or_where('sku', $code);
            if (ctype_digit($code)) {
                $this->db->or_where('id', (int) $code);
            }
            $copy = $this->db->group_end()
                ->group_start()
                    ->where('store_id IS NOT NULL', null, false)
                    ->where('store_id >', 0)
                ->group_end()
                ->where('source_product_id IS NOT NULL', null, false)
                ->where('source_product_id >', 0)
                ->get('products')->row();
            if ($copy) {
                $this->apply_catalog_where($ownerId);
                return $this->db->where('id', (int) $copy->source_product_id)->get('products')->row();
            }
        }
        return null;
    }

    public function storefront_copy_map($catalogIds)
    {
        $grouped = array();
        $catalogIds = array_values(array_unique(array_filter(array_map('intval', (array) $catalogIds))));
        if (!$catalogIds || !$this->db->field_exists('source_product_id', 'products')) {
            return $grouped;
        }

        $listingModel = $this->listing_model();
        if ($listingModel && $listingModel->is_ready()) {
            $wanted = array_flip($catalogIds);
            foreach ($listingModel->listings_for_catalog_ids($catalogIds) as $listing) {
                $catalogId = (int) $listing->catalog_id;
                if ($catalogId < 1) {
                    $catalogId = (int) $listing->source_product_id;
                }
                if ($catalogId < 1 || !isset($wanted[$catalogId])) {
                    continue;
                }
                $listing->price = (float) $listing->listed_amount;
                $listing->country_id = (int) $listing->store_country_id;
                $grouped[$catalogId][] = $listing;
            }
        }

        $missing = array();
        foreach ($catalogIds as $catalogId) {
            if (empty($grouped[$catalogId])) {
                $missing[] = $catalogId;
            }
        }
        if (!$missing) {
            return $grouped;
        }

        $catalogs = $this->db->select('id, sku, analyzer_code, country_id')
            ->from('products')
            ->where_in('id', $missing)
            ->get()->result();
        $skuToCatalog = array();
        foreach ($catalogs as $catalog) {
            foreach (array('sku', 'analyzer_code') as $field) {
                $value = strtoupper(trim((string) $catalog->$field));
                if ($value !== '') {
                    $skuToCatalog[$value] = (int) $catalog->id;
                }
            }
        }

        $this->db->select('id, store_id, source_product_id, price, country_id, status, sku, analyzer_code, updated_at');
        $this->db->from('products');
        $this->db->group_start()
            ->where('store_id IS NOT NULL', null, false)
            ->where('store_id >', 0)
        ->group_end();
        $this->db->group_start();
        $this->db->where_in('source_product_id', $missing);
        foreach ($skuToCatalog as $sku => $unused) {
            $this->db->or_where('sku', $sku);
            $this->db->or_like('sku', $sku . '-S', 'after');
            $this->db->or_where('analyzer_code', $sku);
            $this->db->or_like('analyzer_code', $sku . '-S', 'after');
        }
        $this->db->group_end();
        $copies = $this->db->get()->result();
        foreach ($copies as $copy) {
            $catalogId = (int) $copy->source_product_id;
            if (!in_array($catalogId, $catalogIds, true)) {
                $catalogId = 0;
            }
            if (!$catalogId) {
                $catalogId = $this->catalog_id_from_storefront_sku($copy->sku, $skuToCatalog);
            }
            if (!$catalogId) {
                $catalogId = $this->catalog_id_from_storefront_sku($copy->analyzer_code, $skuToCatalog);
            }
            if (!$catalogId) {
                continue;
            }
            $copy->listed_amount = (float) $copy->price;
            $grouped[$catalogId][] = $copy;
        }
        return $grouped;
    }

    protected function catalog_id_from_storefront_sku($sku, $skuToCatalog)
    {
        $sku = strtoupper(trim((string) $sku));
        if ($sku === '') {
            return 0;
        }
        if (isset($skuToCatalog[$sku])) {
            return (int) $skuToCatalog[$sku];
        }
        if (preg_match('/^(.*)-S(\d+)$/i', $sku, $m)) {
            $base = strtoupper($m[1]);
            if (isset($skuToCatalog[$base])) {
                return (int) $skuToCatalog[$base];
            }
        }
        return 0;
    }

    public function pick_storefront_copy($copies, $catalog = null)
    {
        if (!$copies) {
            return null;
        }
        $countryId = (int) (is_object($catalog) && isset($catalog->country_id) ? $catalog->country_id : 0);
        $best = null;
        $bestScore = -1;
        foreach ($copies as $copy) {
            $score = 0;
            if ((int) $copy->status === 1) {
                $score += 100;
            }
            if ($countryId > 0 && (int) $copy->country_id === $countryId) {
                $score += 50;
            }
            if ((float) $copy->price > 0) {
                $score += 10;
            }
            if ($score > $bestScore) {
                $best = $copy;
                $bestScore = $score;
            }
        }
        return $best;
    }

    public function catalog_ids_for_storefront_code($code)
    {
        $ids = array();
        $code = trim((string) $code);
        if ($code === '' || !$this->db->field_exists('source_product_id', 'products')) {
            return $ids;
        }
        $copies = $this->db->select('source_product_id')
            ->from('products')
            ->group_start()
                ->like('sku', $code)
                ->or_like('analyzer_code', $code)
            ->group_end()
            ->group_start()
                ->where('store_id IS NOT NULL', null, false)
                ->where('store_id >', 0)
            ->group_end()
            ->where('source_product_id IS NOT NULL', null, false)
            ->where('source_product_id >', 0)
            ->get()->result();
        foreach ($copies as $copy) {
            $ids[] = (int) $copy->source_product_id;
        }
        return array_values(array_unique($ids));
    }

    public function apply_storefront_selling_price($row, $copy = false)
    {
        if (!$row) {
            return $row;
        }
        if ($copy === false) {
            $map = $this->storefront_copy_map(array((int) $row->id));
            $copy = $this->pick_storefront_copy(isset($map[(int) $row->id]) ? $map[(int) $row->id] : array(), $row);
        }
        if (!isset($row->catalog_price)) {
            $row->catalog_price = (float) $row->price;
        }
        $selling = (float) $row->catalog_price;
        if ($copy) {
            if (isset($copy->listed_amount) && (float) $copy->listed_amount > 0) {
                $selling = (float) $copy->listed_amount;
            } elseif ((float) $copy->price > 0) {
                $selling = (float) $copy->price;
            }
        }
        $row->price = $selling;
        $row->selling_price = $selling;
        $row->storefront_copy_id = $copy ? (int) $copy->id : 0;
        $row->storefront_store_id = $copy ? (int) $copy->store_id : 0;
        return $row;
    }

    public function update_product_costs($productId, $data)
    {
        $productId = (int) $productId;
        $payload = array();
        if (array_key_exists('cost_price', $data) && $data['cost_price'] !== '' && $data['cost_price'] !== null) {
            $payload['cost_price'] = (float) $data['cost_price'];
        }
        if (array_key_exists('shipping_cost', $data) && $data['shipping_cost'] !== '' && $data['shipping_cost'] !== null) {
            $payload['shipping_cost'] = (float) $data['shipping_cost'];
        } elseif (array_key_exists('shipping_cost', $data) && ($data['shipping_cost'] === '' || $data['shipping_cost'] === null)) {
            // Leave the stored shipping cost unchanged. An empty import cell is not zero.
        }
        if ($payload) {
            $this->db->where('id', $productId)->update('products', $payload);
        }
    }

    public function list_raw($ownerId, $filters = array())
    {
        $q = isset($filters['q']) ? trim((string) $filters['q']) : '';
        $linkedIds = $q !== '' ? $this->catalog_ids_for_storefront_code($q) : array();
        $categorySelect = $this->db->table_exists('product_categories')
            ? "GROUP_CONCAT(DISTINCT categories.name ORDER BY categories.name SEPARATOR ', ') as category_names"
            : "'' as category_names";
        $this->db->select("products.*, suppliers.name as supplier_name, suppliers.address as supplier_url,
            COALESCE(product_country.name, supplier_country.name) as country_name,
            COALESCE(product_country.currency, supplier_country.currency, 'SEK') as currency,
            {$categorySelect},
            meta.payment_fee_percent as meta_payment_fee_percent,
            meta.payment_fixed_fee as meta_payment_fixed_fee,
            meta.other_cost as meta_other_cost,
            meta.return_rate as meta_return_rate,
            meta.manual_cpa, meta.actual_cpa, meta.actual_ad_spend, meta.actual_orders", false);
        $this->catalog_scope($ownerId);
        $this->db->join('suppliers', 'suppliers.id = products.supplier_id', 'left');
        $this->db->join('countries as product_country', 'product_country.id = products.country_id', 'left');
        $this->db->join('countries as supplier_country', 'supplier_country.id = suppliers.country_id', 'left');
        $this->db->join('product_analyzer_meta meta', 'meta.product_id = products.id', 'left');
        if ($this->db->table_exists('product_categories')) {
            $this->db->join('product_categories', 'product_categories.product_id = products.id', 'left');
            $this->db->join('categories', 'categories.id = product_categories.category_id', 'left');
        }
        if (!empty($filters['country_id'])) {
            $this->db->where('products.country_id', (int) $filters['country_id']);
        }
        if (!empty($filters['category_id']) && $this->db->table_exists('product_categories')) {
            $this->db->join('product_categories pc_filter', 'pc_filter.product_id = products.id', 'inner');
            $this->db->where('pc_filter.category_id', (int) $filters['category_id']);
        }
        if (!empty($filters['supplier_id'])) {
            $this->db->where('products.supplier_id', (int) $filters['supplier_id']);
        }
        if (!empty($filters['product_id'])) {
            $this->db->where('products.id', (int) $filters['product_id']);
        }
        if (!empty($filters['ids']) && is_array($filters['ids'])) {
            $ids = array_values(array_unique(array_filter(array_map('intval', $filters['ids']))));
            if ($ids) {
                $this->db->where_in('products.id', $ids);
            } else {
                $this->db->where('1 = 0', null, false);
            }
        }
        if ($q !== '') {
            $this->db->group_start()
                ->like('products.analyzer_code', $q)
                ->or_like('products.sku', $q)
                ->or_like('products.name', $q)
                ->or_like('suppliers.name', $q);
            if ($this->db->field_exists('source_url', 'products')) {
                $this->db->or_like('products.source_url', $q);
            }
            if ($linkedIds) {
                $this->db->or_where_in('products.id', $linkedIds);
            }
            $this->db->group_end();
        }
        $this->db->group_by('products.id');
        $this->db->order_by('products.id', 'desc');
        return $this->db->get()->result();
    }

    public function all_options_map($productIds)
    {
        $map = array();
        if (!$productIds) {
            return $map;
        }
        $rows = $this->db->select('product_supplier_options.*, suppliers.name as supplier_name, suppliers.address as supplier_url')
            ->from('product_supplier_options')
            ->join('suppliers', 'suppliers.id = product_supplier_options.supplier_id', 'left')
            ->where_in('product_supplier_options.product_id', $productIds)
            ->order_by('(product_supplier_options.product_cost + product_supplier_options.shipping_cost)', 'asc', false)
            ->get()->result();
        foreach ($rows as $row) {
            $map[(int) $row->product_id][] = $row;
        }
        return $map;
    }

    public function enrich_row($row, $settings, $rates, $options = array(), $storefrontCopy = false)
    {
        $shipping = $row->shipping_cost;
        if ($shipping === null || $shipping === '') {
            $shipping = null;
            foreach ($options as $option) {
                if ((int) $option->is_preferred === 1) {
                    $shipping = (float) $option->shipping_cost;
                    break;
                }
            }
            if ($shipping === null) {
                $shipping = 0;
            }
        }
        $shipping = (float) $shipping;
        $row->shipping_cost = $shipping;
        if (!$storefrontCopy) {
            $map = $this->storefront_copy_map(array((int) $row->id));
            $storefrontCopy = $this->pick_storefront_copy(isset($map[(int) $row->id]) ? $map[(int) $row->id] : array(), $row);
        }
        $this->apply_storefront_selling_price($row, $storefrontCopy);
        $input = array(
            'selling_price' => (float) $row->price,
            'product_cost' => (float) $row->cost_price,
            'shipping_cost' => $shipping,
            'currency' => $row->currency ?: $settings['default_currency'],
            'payment_fee_percent' => $row->meta_payment_fee_percent,
            'payment_fixed_fee' => $row->meta_payment_fixed_fee,
            'other_cost' => $row->meta_other_cost,
            'return_rate' => $row->meta_return_rate,
            'manual_cpa' => profit_blank($row->manual_cpa) ? null : $row->manual_cpa,
            'actual_cpa' => profit_blank($row->actual_cpa) ? null : $row->actual_cpa,
            'actual_ad_spend' => profit_blank($row->actual_ad_spend) ? null : $row->actual_ad_spend,
            'actual_orders' => profit_blank($row->actual_orders) ? null : $row->actual_orders,
        );
        $metrics = profit_calculate($input, $settings, $rates);
        $currentLanded = (float) $metrics['landed_cost'];
        $best = null;
        foreach ($options as $option) {
            $landed = (float) $option->product_cost + (float) $option->shipping_cost;
            if ($best === null || $landed < $best['landed']) {
                $best = array('option' => $option, 'landed' => $landed);
            }
        }
        $saving = ($best && $best['landed'] < $currentLanded - 0.001) ? ($currentLanded - $best['landed']) : 0;
        $recommended = ($saving > 0 && (int) $best['option']->supplier_id !== (int) $row->supplier_id) ? $best : null;
        $row->analyzer_id = profit_analyzer_id($row);
        $row->metrics = profit_round_metrics($metrics);
        $row->price = $row->metrics['selling_price'];
        $row->cost_price = $row->metrics['product_cost'];
        $row->shipping_cost = $row->metrics['shipping_cost'];
        $row->supplier_saving = profit_round2($saving);
        $savingPkr = profit_to_pkr($saving, $row->currency ?: $settings['default_currency'], $rates);
        $row->supplier_saving_pkr = $savingPkr === null ? null : profit_round2($savingPkr);
        $row->supplier_saving_percent = $currentLanded > 0 && $saving > 0 ? profit_round2(($saving / $currentLanded) * 100) : 0;
        $row->recommended_supplier = $recommended ? $recommended['option'] : null;
        $row->options = $options;
        $row->cheaper_count = 0;
        foreach ($options as $option) {
            if ((int) $option->supplier_id !== (int) $row->supplier_id
                && ((float) $option->product_cost + (float) $option->shipping_cost) < $currentLanded - 0.001) {
                $row->cheaper_count++;
            }
        }
        return $row;
    }

    public function enrich_list($rows, $settings, $rates)
    {
        $ids = array();
        foreach ($rows as $row) {
            $ids[] = (int) $row->id;
        }
        $options = $this->all_options_map($ids);
        $copies = $this->storefront_copy_map($ids);
        $out = array();
        foreach ($rows as $row) {
            $copy = $this->pick_storefront_copy(isset($copies[(int) $row->id]) ? $copies[(int) $row->id] : array(), $row);
            $out[] = $this->enrich_row(
                $row,
                $settings,
                $rates,
                isset($options[(int) $row->id]) ? $options[(int) $row->id] : array(),
                $copy
            );
        }
        return $out;
    }

    public function analyze($rows, $settings = null, $rates = null)
    {
        if ($settings === null) {
            $settings = $this->get_settings();
        }
        if ($rates === null) {
            $rates = $this->get_rates();
        }
        if (!is_array($rows)) {
            if (!$rows) {
                return null;
            }
            $out = $this->enrich_list(array($rows), $settings, $rates);
            return $out ? $out[0] : null;
        }
        return $this->enrich_list($rows, $settings, $rates);
    }

    public function analyze_owner($ownerId, $filters = array())
    {
        return $this->analyze($this->list_raw($ownerId, $filters));
    }

    public function analyzer_stores($ownerId = 0)
    {
        if ($this->db->table_exists('stores')) {
            $this->db
                ->select('stores.id, stores.name, stores.domain, stores.country_id, countries.name as country_name')
                ->from('stores')
                ->join('countries', 'countries.id = stores.country_id', 'left')
                ->order_by('stores.name', 'asc');
            return $this->db->get()->result();
        }
        $listingModel = $this->listing_model();
        if ($listingModel && $listingModel->is_ready()) {
            return $listingModel->filter_stores();
        }
        if (!$this->db->field_exists('store_id', 'products')) {
            return array();
        }
        $rows = $this->db->select('store_id')
            ->from('products')
            ->where('store_id IS NOT NULL', null, false)
            ->where('store_id >', 0)
            ->group_by('store_id')
            ->order_by('store_id', 'asc')
            ->get()->result();
        $out = array();
        foreach ($rows as $row) {
            $out[] = (object) array(
                'id' => (int) $row->store_id,
                'name' => 'Store #' . (int) $row->store_id,
                'domain' => '',
                'country_id' => 0,
                'country_name' => '',
            );
        }
        return $out;
    }

    public function analyze_listings($ownerId, $filters = array())
    {
        $rows = $this->list_listings($ownerId, $filters);
        $settings = $this->get_settings();
        $rates = $this->get_rates();
        $catalogIds = array();
        foreach ($rows as $row) {
            $catalogIds[] = (int) $row->id;
        }
        $options = $this->all_options_map($catalogIds);
        $out = array();
        foreach ($rows as $row) {
            $copy = (object) array(
                'id' => (int) $row->listing_id,
                'store_id' => (int) $row->store_id,
                'listed_amount' => (float) $row->listed_amount,
                'price' => (float) $row->listed_amount,
                'country_id' => (int) $row->country_id,
                'status' => 1,
            );
            $out[] = $this->enrich_row(
                $row,
                $settings,
                $rates,
                isset($options[(int) $row->id]) ? $options[(int) $row->id] : array(),
                $copy
            );
        }
        return $out;
    }

    public function list_listings($ownerId, $filters = array())
    {
        $listings = $this->raw_store_listings($filters);
        if (!$listings) {
            return array();
        }
        $catalogIds = array();
        foreach ($listings as $listing) {
            $catalogId = (int) (isset($listing->catalog_id) ? $listing->catalog_id : 0);
            if ($catalogId < 1) {
                $catalogId = (int) (isset($listing->source_product_id) ? $listing->source_product_id : 0);
            }
            if ($catalogId > 0) {
                $catalogIds[$catalogId] = $catalogId;
            }
        }
        $catalogMap = array();
        if ($catalogIds) {
            $catalogFilters = array('ids' => array_values($catalogIds));
            if (!empty($filters['category_id'])) {
                $catalogFilters['category_id'] = $filters['category_id'];
            }
            if (!empty($filters['supplier_id'])) {
                $catalogFilters['supplier_id'] = $filters['supplier_id'];
            }
            foreach ($this->list_raw($ownerId, $catalogFilters) as $catalog) {
                $catalogMap[(int) $catalog->id] = $catalog;
            }
        }
        $out = array();
        foreach ($listings as $listing) {
            $catalogId = (int) (isset($listing->catalog_id) ? $listing->catalog_id : 0);
            if ($catalogId < 1) {
                $catalogId = (int) (isset($listing->source_product_id) ? $listing->source_product_id : 0);
            }
            if ($catalogId < 1 || !isset($catalogMap[$catalogId])) {
                continue;
            }
            $row = clone $catalogMap[$catalogId];
            $row->listing_id = (int) $listing->id;
            $row->store_id = (int) $listing->store_id;
            $row->store_name = isset($listing->store_name) ? $listing->store_name : ('Store #' . (int) $listing->store_id);
            $row->store_domain = isset($listing->store_domain) ? $listing->store_domain : '';
            $row->listed_amount = (float) $listing->listed_amount;
            $row->price = $row->listed_amount;
            if (!empty($listing->country_currency)) {
                $row->currency = $listing->country_currency;
            }
            if (!empty($listing->country_name)) {
                $row->country_name = $listing->country_name;
            }
            if (!empty($listing->store_country_id)) {
                $row->country_id = (int) $listing->store_country_id;
            }
            $out[] = $row;
        }
        return $out;
    }

    protected function raw_store_listings($filters = array())
    {
        $listingModel = $this->listing_model();
        if ($listingModel && $listingModel->is_ready()) {
            $listingFilters = array(
                'store_id' => !empty($filters['store_id']) ? (int) $filters['store_id'] : 0,
                'country_id' => !empty($filters['country_id']) ? (int) $filters['country_id'] : 0,
                'q' => isset($filters['q']) ? $filters['q'] : '',
                'product_id' => !empty($filters['product_id']) ? (int) $filters['product_id'] : 0,
                'listing_id' => !empty($filters['listing_id']) ? (int) $filters['listing_id'] : 0,
                'sort' => 'id',
                'dir' => 'desc',
            );
            return $listingModel->all($listingFilters);
        }
        if (!$this->db->field_exists('store_id', 'products') || !$this->db->field_exists('source_product_id', 'products')) {
            return array();
        }
        $hasStores = $this->db->table_exists('stores');
        $select = 'products.id, products.store_id, products.source_product_id, products.source_product_id as catalog_id, products.price as listed_amount, products.status, products.sku, products.name';
        if ($hasStores) {
            $select .= ', stores.name as store_name, stores.domain as store_domain, stores.country_id as store_country_id, country.name as country_name, country.currency as country_currency';
        } else {
            $select .= ', products.country_id as store_country_id, country.name as country_name, country.currency as country_currency';
        }
        $this->db->select($select, false);
        $this->db->from('products');
        $this->db->join('products catalog', 'catalog.id = products.source_product_id', 'left');
        if ($hasStores) {
            $this->db->join('stores', 'stores.id = products.store_id', 'inner');
            $this->db->join('countries country', 'country.id = stores.country_id', 'left');
        } else {
            $this->db->join('countries country', 'country.id = products.country_id', 'left');
        }
        $this->db->group_start()
            ->where('products.store_id IS NOT NULL', null, false)
            ->where('products.store_id >', 0)
        ->group_end();
        $this->db->where('products.source_product_id IS NOT NULL', null, false);
        $this->db->where('products.source_product_id >', 0);
        if (!empty($filters['store_id'])) {
            $this->db->where('products.store_id', (int) $filters['store_id']);
        }
        if (!empty($filters['product_id'])) {
            $this->db->where('products.source_product_id', (int) $filters['product_id']);
        }
        if (!empty($filters['listing_id'])) {
            $this->db->where('products.id', (int) $filters['listing_id']);
        }
        if (!empty($filters['country_id'])) {
            if ($hasStores) {
                $this->db->where('stores.country_id', (int) $filters['country_id']);
            } else {
                $this->db->where('products.country_id', (int) $filters['country_id']);
            }
        }
        $q = isset($filters['q']) ? trim((string) $filters['q']) : '';
        if ($q !== '') {
            $this->db->group_start()
                ->like('products.name', $q)
                ->or_like('products.sku', $q)
                ->or_like('products.analyzer_code', $q)
                ->or_like('catalog.sku', $q)
                ->or_like('catalog.analyzer_code', $q);
            if ($hasStores) {
                $this->db->or_like('stores.name', $q);
                $this->db->or_like('stores.domain', $q);
            }
            $this->db->group_end();
        }
        $this->db->order_by('products.id', 'desc');
        $copies = $this->db->get()->result();
        foreach ($copies as $copy) {
            if (empty($copy->store_name)) {
                $copy->store_name = 'Store #' . (int) $copy->store_id;
            }
            if (!isset($copy->store_domain)) {
                $copy->store_domain = '';
            }
        }
        return $copies;
    }

    public function find_or_create_supplier($name, $url = '')
    {
        $name = trim((string) $name);
        if ($name === '') {
            return 0;
        }
        $existing = $this->db->where('name', $name)->get('suppliers')->row();
        if ($existing) {
            return (int) $existing->id;
        }
        $this->db->insert('suppliers', array(
            'country_id' => 1,
            'name' => $name,
            'address' => $url,
            'status' => 1,
        ));
        return (int) $this->db->insert_id();
    }

    public function upsert_option($product, $data, $makePreferred = false)
    {
        $supplierId = $this->find_or_create_supplier($data['supplier_name'], isset($data['supplier_url']) ? $data['supplier_url'] : '');
        if (!$supplierId) {
            return array('error' => 'Supplier name is required.');
        }
        $existing = $this->db->get_where('product_supplier_options', array(
            'product_id' => (int) $product->id,
            'supplier_id' => $supplierId,
        ))->row();
        $payload = array(
            'product_id' => (int) $product->id,
            'supplier_id' => $supplierId,
            'supplier_product_url' => isset($data['supplier_product_url']) ? $data['supplier_product_url'] : '',
            'product_cost' => (float) $data['product_cost'],
            'shipping_cost' => (float) $data['shipping_cost'],
            'currency' => !empty($data['currency']) ? $data['currency'] : '',
            'delivery_min_days' => isset($data['delivery_min_days']) && $data['delivery_min_days'] !== '' ? (int) $data['delivery_min_days'] : null,
            'delivery_max_days' => isset($data['delivery_max_days']) && $data['delivery_max_days'] !== '' ? (int) $data['delivery_max_days'] : null,
            'moq' => isset($data['moq']) && $data['moq'] !== '' ? (int) $data['moq'] : null,
            'notes' => isset($data['notes']) ? $data['notes'] : '',
            'source' => isset($data['source']) ? $data['source'] : 'research',
            'checked_date' => isset($data['checked_date']) && $data['checked_date'] ? $data['checked_date'] : date('Y-m-d'),
        );
        if ($existing) {
            if ((float) $existing->product_cost != (float) $payload['product_cost']
                || (float) $existing->shipping_cost != (float) $payload['shipping_cost']) {
                $this->db->insert('product_supplier_price_history', array(
                    'option_id' => (int) $existing->id,
                    'product_id' => (int) $product->id,
                    'supplier_name' => $data['supplier_name'],
                    'old_product_cost' => $existing->product_cost,
                    'new_product_cost' => $payload['product_cost'],
                    'old_shipping_cost' => $existing->shipping_cost,
                    'new_shipping_cost' => $payload['shipping_cost'],
                    'old_landed_cost' => (float) $existing->product_cost + (float) $existing->shipping_cost,
                    'new_landed_cost' => (float) $payload['product_cost'] + (float) $payload['shipping_cost'],
                ));
            }
            $this->db->where('id', $existing->id)->update('product_supplier_options', $payload);
            $optionId = (int) $existing->id;
            $created = false;
        } else {
            $this->db->insert('product_supplier_options', $payload);
            $optionId = (int) $this->db->insert_id();
            $created = true;
        }
        if ($makePreferred) {
            $this->set_preferred((int) $product->id, $optionId);
        }
        return array('option_id' => $optionId, 'created' => $created);
    }

    public function set_preferred($productId, $optionId)
    {
        $option = $this->db->get_where('product_supplier_options', array(
            'id' => (int) $optionId,
            'product_id' => (int) $productId,
        ))->row();
        if (!$option) {
            return false;
        }
        $this->db->where('product_id', (int) $productId)->update('product_supplier_options', array('is_preferred' => 0));
        $this->db->where('id', (int) $optionId)->update('product_supplier_options', array('is_preferred' => 1));
        $this->db->where('id', (int) $productId)->update('products', array(
            'supplier_id' => (int) $option->supplier_id,
            'cost_price' => (float) $option->product_cost,
            'shipping_cost' => (float) $option->shipping_cost,
        ));
        return true;
    }

    public function save_meta($productId, $data)
    {
        $payload = array('product_id' => (int) $productId);
        foreach (array('payment_fee_percent', 'payment_fixed_fee', 'other_cost', 'return_rate', 'manual_cpa', 'actual_cpa', 'actual_ad_spend', 'actual_orders') as $field) {
            if (array_key_exists($field, $data)) {
                $payload[$field] = ($data[$field] === '' || $data[$field] === null) ? null : (float) $data[$field];
            }
        }
        $exists = $this->db->get_where('product_analyzer_meta', array('product_id' => (int) $productId))->row();
        if ($exists) {
            $this->db->where('product_id', (int) $productId)->update('product_analyzer_meta', $payload);
        } else {
            $this->db->insert('product_analyzer_meta', $payload);
        }
    }

    public function history($productId)
    {
        return $this->db->where('product_id', (int) $productId)
            ->order_by('changed_at', 'desc')
            ->limit(40)
            ->get('product_supplier_price_history')
            ->result();
    }
}
