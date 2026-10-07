<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Store_profitability_model extends CI_Model {

    public function ensure_schema()
    {
        $this->db->query("CREATE TABLE IF NOT EXISTS store_profitability_settings (
            id TINYINT NOT NULL PRIMARY KEY,
            budget_mode VARCHAR(20) NOT NULL DEFAULT 'per_product',
            ads_budget DECIMAL(12,2) NOT NULL DEFAULT 1000,
            ads_currency VARCHAR(10) NOT NULL DEFAULT 'PKR',
            expected_orders DECIMAL(12,2) NOT NULL DEFAULT 1,
            target_profit DECIMAL(12,2) NOT NULL DEFAULT 100,
            payment_fee_percent DECIMAL(8,4) NOT NULL DEFAULT 2.9,
            payment_fixed_fee DECIMAL(12,2) NOT NULL DEFAULT 1,
            other_cost DECIMAL(12,2) NOT NULL DEFAULT 10,
            return_rate DECIMAL(8,4) NOT NULL DEFAULT 5,
            updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

        $this->db->query("CREATE TABLE IF NOT EXISTS store_profitability_rates (
            id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
            currency VARCHAR(10) NOT NULL,
            rate_to_pkr DECIMAL(12,4) NOT NULL,
            updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            UNIQUE KEY currency (currency)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

        if (!$this->db->get_where('store_profitability_settings', array('id' => 1))->row()) {
            $this->db->insert('store_profitability_settings', array('id' => 1));
        }
        $defaults = array('SEK' => 33, 'GBP' => 375, 'EUR' => 330, 'RON' => 66, 'PLN' => 76, 'BGN' => 168, 'USD' => 278, 'AUD' => 185, 'AED' => 76, 'PKR' => 1);
        foreach ($defaults as $currency => $rate) {
            if (!$this->db->get_where('store_profitability_rates', array('currency' => $currency))->row()) {
                $this->db->insert('store_profitability_rates', array('currency' => $currency, 'rate_to_pkr' => $rate));
            }
        }
    }

    public function default_settings()
    {
        return array(
            'budget_mode' => 'per_product',
            'ads_budget' => 1000,
            'ads_currency' => 'PKR',
            'expected_orders' => 1,
            'target_profit' => 100,
            'payment_fee_percent' => 2.9,
            'payment_fixed_fee' => 1,
            'other_cost' => 10,
            'return_rate' => 5,
        );
    }

    public function get_settings()
    {
        $row = $this->db->get_where('store_profitability_settings', array('id' => 1))->row_array();
        return $row ? array_merge($this->default_settings(), $row) : $this->default_settings();
    }

    public function save_settings($data)
    {
        $current = $this->get_settings();
        $mode = isset($data['budget_mode']) ? strtolower(trim((string) $data['budget_mode'])) : $current['budget_mode'];
        if ($mode !== 'shared') {
            $mode = 'per_product';
        }
        $payload = array(
            'budget_mode' => $mode,
            'ads_budget' => isset($data['ads_budget']) ? (float) $data['ads_budget'] : (float) $current['ads_budget'],
            'ads_currency' => strtoupper(trim(isset($data['ads_currency']) ? $data['ads_currency'] : $current['ads_currency'])) ?: 'PKR',
            'expected_orders' => isset($data['expected_orders']) ? (float) $data['expected_orders'] : (float) $current['expected_orders'],
            'target_profit' => isset($data['target_profit']) ? (float) $data['target_profit'] : (float) $current['target_profit'],
            'payment_fee_percent' => isset($data['payment_fee_percent']) ? (float) $data['payment_fee_percent'] : (float) $current['payment_fee_percent'],
            'payment_fixed_fee' => isset($data['payment_fixed_fee']) ? (float) $data['payment_fixed_fee'] : (float) $current['payment_fixed_fee'],
            'other_cost' => isset($data['other_cost']) ? (float) $data['other_cost'] : (float) $current['other_cost'],
            'return_rate' => isset($data['return_rate']) ? (float) $data['return_rate'] : (float) $current['return_rate'],
        );
        if ($payload['expected_orders'] <= 0) {
            $payload['expected_orders'] = 1;
        }
        $this->db->where('id', 1)->update('store_profitability_settings', $payload);
        return $this->get_settings();
    }

    public function get_rates()
    {
        $out = array();
        foreach ($this->db->get('store_profitability_rates')->result() as $row) {
            $out[strtoupper($row->currency)] = (float) $row->rate_to_pkr;
        }
        if (!isset($out['PKR'])) {
            $out['PKR'] = 1;
        }
        return $out;
    }

    public function save_rate($currency, $rate)
    {
        $currency = strtoupper(trim((string) $currency));
        $rate = (float) $rate;
        if ($currency === '' || $rate <= 0) {
            return $this->get_rates();
        }
        $exists = $this->db->get_where('store_profitability_rates', array('currency' => $currency))->row();
        if ($exists) {
            $this->db->where('id', (int) $exists->id)->update('store_profitability_rates', array('rate_to_pkr' => $rate));
        } else {
            $this->db->insert('store_profitability_rates', array('currency' => $currency, 'rate_to_pkr' => $rate));
        }
        return $this->get_rates();
    }

    public function countries_with_stores()
    {
        if (!$this->db->table_exists('stores')) {
            return array();
        }
        return $this->db
            ->select('countries.id, countries.name, countries.currency, COUNT(stores.id) as store_count', false)
            ->from('stores')
            ->join('countries', 'countries.id = stores.country_id', 'inner')
            ->group_by('countries.id')
            ->order_by('countries.name', 'asc')
            ->get()->result();
    }

    public function stores($countryId = 0)
    {
        if ($this->db->table_exists('stores')) {
            $this->db
                ->select('stores.id, stores.name, stores.domain, stores.country_id, countries.name as country_name, countries.currency as country_currency')
                ->from('stores')
                ->join('countries', 'countries.id = stores.country_id', 'left')
                ->order_by('stores.name', 'asc');
            if ($countryId) {
                $this->db->where('stores.country_id', (int) $countryId);
            }
            return $this->db->get()->result();
        }
        return $this->fallback_store_options($countryId);
    }

    public function store($storeId)
    {
        $storeId = (int) $storeId;
        if ($storeId < 1) {
            return null;
        }
        if ($this->db->table_exists('stores')) {
            return $this->db
                ->select('stores.id, stores.name, stores.domain, stores.country_id, countries.name as country_name, countries.currency as country_currency')
                ->from('stores')
                ->join('countries', 'countries.id = stores.country_id', 'left')
                ->where('stores.id', $storeId)
                ->get()->row();
        }
        foreach ($this->fallback_store_options() as $row) {
            if ((int) $row->id === $storeId) {
                return $row;
            }
        }
        return null;
    }

    protected function fallback_store_options($countryId = 0)
    {
        if (!$this->db->field_exists('store_id', 'products')) {
            return array();
        }
        $this->db->select('products.store_id as id, products.country_id, country.name as country_name, country.currency as country_currency', false);
        $this->db->from('products');
        $this->db->join('countries country', 'country.id = products.country_id', 'left');
        $this->db->where('products.store_id >', 0);
        if ($countryId) {
            $this->db->where('products.country_id', (int) $countryId);
        }
        $this->db->group_by('products.store_id');
        $this->db->order_by('products.store_id', 'asc');
        $out = array();
        foreach ($this->db->get()->result() as $row) {
            $row->name = 'Store #' . (int) $row->id;
            $row->domain = '';
            $out[] = $row;
        }
        return $out;
    }

    public function listing_rows($ownerId, $filters = array())
    {
        $storeId = !empty($filters['store_id']) ? (int) $filters['store_id'] : 0;
        $countryId = !empty($filters['country_id']) ? (int) $filters['country_id'] : 0;
        $listingId = !empty($filters['listing_id']) ? (int) $filters['listing_id'] : 0;
        $productId = !empty($filters['product_id']) ? (int) $filters['product_id'] : 0;
        if ($storeId < 1 && $listingId < 1) {
            return array();
        }

        $CI =& get_instance();
        if (!isset($CI->Store_listing_model)) {
            $CI->load->model('Store_listing_model');
        }
        $listings = array();
        if (isset($CI->Store_listing_model) && $CI->Store_listing_model->is_ready()) {
            $listings = $CI->Store_listing_model->all(array(
                'store_id' => $storeId,
                'country_id' => $countryId,
                'q' => isset($filters['q']) ? $filters['q'] : '',
                'product_id' => $productId,
                'listing_id' => $listingId,
                'sort' => 'id',
                'dir' => 'desc',
            ));
        } else {
            $listings = $this->fallback_listings($filters);
        }
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
        $catalogMap = $this->catalog_map($ownerId, array_values($catalogIds), $filters);

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
            $row->listing_sku = isset($listing->sku) ? $listing->sku : '';
            $row->listing_slug = isset($listing->slug) ? $listing->slug : $row->slug;
            $row->listing_status = isset($listing->status) ? $listing->status : 1;
            $row->listing_image = !empty($listing->image) ? $listing->image : $row->image;
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

    protected function fallback_listings($filters)
    {
        if (!$this->db->field_exists('store_id', 'products') || !$this->db->field_exists('source_product_id', 'products')) {
            return array();
        }
        $hasStores = $this->db->table_exists('stores');
        $select = 'products.id, products.store_id, products.source_product_id, products.source_product_id as catalog_id, products.price as listed_amount, products.status, products.sku, products.name, products.slug, products.image';
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
        $this->db->where('products.store_id >', 0);
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
        return $this->db->get()->result();
    }

    protected function catalog_map($ownerId, $ids, $filters = array())
    {
        $map = array();
        $ids = array_values(array_unique(array_filter(array_map('intval', (array) $ids))));
        if (!$ids) {
            return $map;
        }
        $categorySelect = $this->db->table_exists('product_categories')
            ? "GROUP_CONCAT(DISTINCT categories.name ORDER BY categories.name SEPARATOR ', ') as category_names"
            : "'' as category_names";
        $extraSelect = $this->db->field_exists('extra_amount', 'products') ? 'products.extra_amount' : '0 as extra_amount';
        $this->db->select("products.id, products.name, products.sku, products.analyzer_code, products.image, products.slug, products.source_url, products.cost_price, products.shipping_cost, {$extraSelect}, products.supplier_id, products.country_id, suppliers.name as supplier_name, {$categorySelect}", false);
        $this->db->from('products');
        $this->db->group_start()
            ->where('products.store_id IS NULL', null, false)
            ->or_where('products.store_id', 0)
        ->group_end();
        $this->db->where_in('products.id', $ids);
        if ($ownerId) {
            $this->db->where('products.created_by', (int) $ownerId);
        }
        $this->db->join('suppliers', 'suppliers.id = products.supplier_id', 'left');
        if ($this->db->table_exists('product_categories')) {
            $this->db->join('product_categories', 'product_categories.product_id = products.id', 'left');
            $this->db->join('categories', 'categories.id = product_categories.category_id', 'left');
        }
        if (!empty($filters['category_id']) && $this->db->table_exists('product_categories')) {
            $this->db->join('product_categories pc_filter', 'pc_filter.product_id = products.id', 'inner');
            $this->db->where('pc_filter.category_id', (int) $filters['category_id']);
        }
        if (!empty($filters['supplier_id'])) {
            $this->db->where('products.supplier_id', (int) $filters['supplier_id']);
        }
        $this->db->group_by('products.id');
        foreach ($this->db->get()->result() as $row) {
            $map[(int) $row->id] = $row;
        }
        return $map;
    }

    public function categories()
    {
        if (!$this->db->table_exists('categories')) {
            return array();
        }
        return $this->db->select('id, name')->order_by('name', 'asc')->get('categories')->result();
    }

    public function analyze_rows($rows, $pageSettings, $rates, $overrides = array())
    {
        $out = array();
        foreach ($rows as $row) {
            $out[] = $this->analyze_row($row, $pageSettings, $rates, isset($overrides[(int) $row->listing_id]) ? $overrides[(int) $row->listing_id] : array());
        }
        return $out;
    }

    public function analyze_row($row, $pageSettings, $rates, $override = array())
    {
        $currency = !empty($row->currency) ? strtoupper($row->currency) : 'SEK';
        $selling = (float) $row->listed_amount;
        $cost = (float) $row->cost_price;
        $shipping = (float) $row->shipping_cost;
        $mode = isset($pageSettings['budget_mode']) ? $pageSettings['budget_mode'] : 'per_product';
        $adsBudget = (float) $pageSettings['ads_budget'];
        $adsCurrency = strtoupper($pageSettings['ads_currency'] ?: 'PKR');
        $expectedOrders = (float) $pageSettings['expected_orders'];
        $targetProfit = (float) $pageSettings['target_profit'];
        $feePct = (float) $pageSettings['payment_fee_percent'];
        $fixedFee = (float) $pageSettings['payment_fixed_fee'];
        $other = (float) $pageSettings['other_cost'];
        $returnRate = (float) $pageSettings['return_rate'];

        if (isset($override['selling_price']) && $override['selling_price'] !== '') {
            $selling = (float) $override['selling_price'];
        }
        if (isset($override['product_cost']) && $override['product_cost'] !== '') {
            $cost = (float) $override['product_cost'];
        }
        if (isset($override['shipping_cost']) && $override['shipping_cost'] !== '') {
            $shipping = (float) $override['shipping_cost'];
        }
        if (isset($override['ads_budget']) && $override['ads_budget'] !== '') {
            $adsBudget = (float) $override['ads_budget'];
        }
        if (isset($override['expected_orders']) && $override['expected_orders'] !== '') {
            $expectedOrders = (float) $override['expected_orders'];
        }
        if (isset($override['ads_currency']) && $override['ads_currency'] !== '') {
            $adsCurrency = strtoupper($override['ads_currency']);
        }
        if (isset($override['target_profit']) && $override['target_profit'] !== '') {
            $targetProfit = (float) $override['target_profit'];
        }
        if (isset($override['payment_fee_percent']) && $override['payment_fee_percent'] !== '') {
            $feePct = (float) $override['payment_fee_percent'];
        }
        if (isset($override['payment_fixed_fee']) && $override['payment_fixed_fee'] !== '') {
            $fixedFee = (float) $override['payment_fixed_fee'];
        }
        if (isset($override['other_cost']) && $override['other_cost'] !== '') {
            $other = (float) $override['other_cost'];
        }
        if (isset($override['return_rate']) && $override['return_rate'] !== '') {
            $returnRate = (float) $override['return_rate'];
        }
        if ($expectedOrders <= 0) {
            $expectedOrders = 1;
        }

        $calcSettings = array(
            'target_profit' => $targetProfit,
            'daily_ad_budget' => $adsBudget,
            'expected_orders_per_day' => $expectedOrders,
            'payment_fee_percent' => $feePct,
            'payment_fixed_fee' => $fixedFee,
            'other_cost' => $other,
            'return_rate' => $returnRate,
            'default_currency' => $currency,
            'ad_budget_currency' => $adsCurrency,
            'manual_cpa' => null,
            'actual_ad_spend' => null,
            'actual_orders' => null,
        );
        $metrics = profit_calculate(array(
            'selling_price' => $selling,
            'product_cost' => $cost,
            'shipping_cost' => $shipping,
            'currency' => $currency,
            'payment_fee_percent' => $feePct,
            'payment_fixed_fee' => $fixedFee,
            'other_cost' => $other,
            'return_rate' => $returnRate,
        ), $calcSettings, $rates);
        $metrics = profit_round_metrics($metrics);
        $row->analyzer_id = profit_analyzer_id($row);
        $row->currency = $currency;
        $row->metrics = $metrics;
        $row->ads_budget = $adsBudget;
        $row->ads_currency = $adsCurrency;
        $row->budget_mode = $mode;
        $row->expected_orders = $expectedOrders;
        $row->daily_profit = profit_round2($metrics['net_profit'] * $expectedOrders);
        $row->daily_profit_pkr = $metrics['local_profit'] === null ? null : profit_round2((float) $metrics['local_profit'] * $expectedOrders);
        $row->customer_shipping = 0;
        $row->rate_to_pkr = isset($rates[$currency]) ? (float) $rates[$currency] : null;
        return $row;
    }

    public function summarize($items, $pageSettings)
    {
        $total = count($items);
        $above = $below = $loss = 0;
        $sumNative = $sumPkr = $sumMargin = $sumCpaPkr = $sumCost = $sumPrice = $sumDaily = $sumDailyPkr = $sumRev = $sumCostDaily = 0;
        $nPkr = $nCpa = 0;
        $currency = '';
        $mixed = false;
        $highest = $lowest = null;
        foreach ($items as $item) {
            $status = $item->metrics['status'];
            if ($status === 'above_target') {
                $above++;
            } elseif ($status === 'below_target') {
                $below++;
            } else {
                $loss++;
            }
            $sumNative += (float) $item->metrics['net_profit'];
            $sumMargin += (float) $item->metrics['profit_margin'];
            $sumCost += (float) $item->metrics['product_cost'];
            $sumPrice += (float) $item->metrics['selling_price'];
            $sumDaily += (float) $item->daily_profit;
            $sumRev += (float) $item->metrics['selling_price'] * (float) $item->expected_orders;
            $sumCostDaily += (float) $item->metrics['product_cost'] * (float) $item->expected_orders;
            if ($item->metrics['local_profit'] !== null && $item->metrics['local_profit'] !== '') {
                $sumPkr += (float) $item->metrics['local_profit'];
                $nPkr++;
            }
            if ($item->daily_profit_pkr !== null) {
                $sumDailyPkr += (float) $item->daily_profit_pkr;
            }
            $cpaPkr = profit_to_pkr($item->metrics['cpa'], $item->currency, array($item->currency => $item->rate_to_pkr, 'PKR' => 1));
            if ($cpaPkr !== null) {
                $sumCpaPkr += (float) $cpaPkr;
                $nCpa++;
            }
            if ($currency === '') {
                $currency = $item->currency;
            } elseif ($currency !== $item->currency) {
                $mixed = true;
            }
            if ($highest === null || $item->metrics['net_profit'] > $highest->metrics['net_profit']) {
                $highest = $item;
            }
            if ($lowest === null || $item->metrics['net_profit'] < $lowest->metrics['net_profit']) {
                $lowest = $item;
            }
        }
        $mode = isset($pageSettings['budget_mode']) ? $pageSettings['budget_mode'] : 'per_product';
        $adsBudget = (float) $pageSettings['ads_budget'];
        $expectedOrders = (float) $pageSettings['expected_orders'];
        $totalAds = ($mode === 'shared') ? $adsBudget : ($adsBudget * $total);
        $totalOrders = ($mode === 'shared') ? $expectedOrders : ($expectedOrders * $total);
        $sorted = $items;
        usort($sorted, function ($a, $b) {
            if ($a->metrics['net_profit'] == $b->metrics['net_profit']) {
                return 0;
            }
            return ($a->metrics['net_profit'] > $b->metrics['net_profit']) ? -1 : 1;
        });
        return array(
            'total' => $total,
            'above' => $above,
            'below' => $below,
            'loss' => $loss,
            'currency' => $mixed ? '' : $currency,
            'mixed' => $mixed,
            'avg_native' => $total ? $sumNative / $total : 0,
            'avg_pkr' => $nPkr ? $sumPkr / $nPkr : 0,
            'avg_margin' => $total ? $sumMargin / $total : 0,
            'avg_cpa_pkr' => $nCpa ? $sumCpaPkr / $nCpa : 0,
            'avg_cost' => $total ? $sumCost / $total : 0,
            'avg_price' => $total ? $sumPrice / $total : 0,
            'total_pkr' => $sumPkr,
            'total_daily_native' => $sumDaily,
            'total_daily_pkr' => $sumDailyPkr,
            'total_ads' => $totalAds,
            'total_orders' => $totalOrders,
            'total_revenue' => $sumRev,
            'total_product_cost' => $sumCostDaily,
            'ads_currency' => strtoupper($pageSettings['ads_currency'] ?: 'PKR'),
            'highest' => $highest,
            'lowest' => $lowest,
            'top' => array_slice($sorted, 0, 8),
            'bottom' => array_slice(array_reverse($sorted), 0, 8),
        );
    }

    public function catalog_extra_amount($catalogId)
    {
        $catalogId = (int) $catalogId;
        if ($catalogId < 1 || !$this->db->field_exists('extra_amount', 'products')) {
            return 0.0;
        }
        $row = $this->db->select('extra_amount')->where('id', $catalogId)->get('products')->row();
        return $row ? profit_round2($row->extra_amount) : 0.0;
    }

    public function apply_store_extra_amounts($storeId, $catalogExtras)
    {
        if (function_exists('ensure_product_extra_amount_column')) {
            ensure_product_extra_amount_column();
        }
        $storeId = (int) $storeId;
        if ($storeId < 1 || !is_array($catalogExtras) || !$catalogExtras) {
            return 0;
        }
        $store = $this->db->table_exists('stores') ? $this->db->where('id', $storeId)->get('stores')->row() : null;
        if (!$store) {
            return 0;
        }
        $ids = array();
        $when = array();
        foreach ($catalogExtras as $catalogId => $extraAmount) {
            $catalogId = (int) $catalogId;
            if ($catalogId < 1) {
                continue;
            }
            $extraAmount = profit_round2($extraAmount);
            $ids[$catalogId] = $catalogId;
            $when[] = 'WHEN ' . $catalogId . ' THEN ' . number_format($extraAmount, 2, '.', '');
        }
        $ids = array_values($ids);
        if (!$ids || !$when) {
            return 0;
        }
        $idList = implode(',', $ids);
        $caseSql = implode(' ', $when);
        $this->db->query("UPDATE products SET extra_amount = CASE id {$caseSql} ELSE extra_amount END WHERE id IN ({$idList})");
        if ($this->db->field_exists('source_product_id', 'products')) {
            $this->db->query("UPDATE products SET extra_amount = CASE source_product_id {$caseSql} ELSE extra_amount END WHERE source_product_id IN ({$idList}) AND store_id = " . $storeId);
        }
        if (function_exists('storefront_pricing_source_reset')) {
            storefront_pricing_source_reset(0);
        }
        $this->load->model('store/Store_product_model');
        $sources = array();
        foreach ($this->db->where_in('id', $ids)->get('products')->result() as $source) {
            $sources[(int) $source->id] = $source;
        }
        $copies = $this->db
            ->where('store_id', $storeId)
            ->where_in('source_product_id', $ids)
            ->get('products')
            ->result();
        foreach ($copies as $copy) {
            $sourceId = (int) $copy->source_product_id;
            if (!isset($sources[$sourceId])) {
                continue;
            }
            $source = $sources[$sourceId];
            $wholesale = product_wholesale_price($source, $store->id);
            $plus = store_price_plus_amount($store);
            $price = $this->Store_product_model->listing_price_from_source($store, $source, $wholesale, $plus);
            $this->db->where('id', (int) $copy->id)->update('products', array(
                'cost_price' => round($wholesale, 2),
                'price' => round($price, 2),
                'extra_amount' => function_exists('product_extra_amount') ? product_extra_amount($source) : profit_round2($source->extra_amount),
            ));
        }
        return count($ids);
    }

    public function update_catalog_shipping($productId, $shipping)
    {
        $productId = (int) $productId;
        if ($productId < 1 || !$this->db->field_exists('shipping_cost', 'products')) {
            return false;
        }
        $this->db->where('id', $productId)->update('products', array('shipping_cost' => (float) $shipping));
        return true;
    }

    public function storefront_url($row)
    {
        $slug = isset($row->listing_slug) ? trim((string) $row->listing_slug) : '';
        if ($slug === '' && isset($row->slug)) {
            $slug = trim((string) $row->slug);
        }
        $path = $slug !== '' ? ('product/' . rawurlencode($slug)) : ('product/' . (int) $row->listing_id);
        $url = site_url($path);
        $domain = isset($row->store_domain) ? trim((string) $row->store_domain) : '';
        if ($domain !== '') {
            $url .= (strpos($url, '?') === false ? '?' : '&') . 'domain=' . rawurlencode($domain);
        }
        return $url;
    }
}
