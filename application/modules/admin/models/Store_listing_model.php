<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Store_listing_model extends CI_Model {

    public function is_ready()
    {
        return $this->db->table_exists('products') && $this->db->table_exists('stores');
    }

    public function filter_stores($countryId = 0)
    {
        if (!$this->is_ready()) {
            return array();
        }
        $this->db
            ->select('stores.id, stores.name, stores.domain, stores.country_id, countries.name as country_name')
            ->from('stores')
            ->join('countries', 'countries.id = stores.country_id', 'left')
            ->order_by('stores.name', 'asc');
        if ($countryId) {
            $this->db->where('stores.country_id', (int) $countryId);
        }
        return $this->db->get()->result();
    }

    public function count_filtered($filters = array())
    {
        if (!$this->is_ready()) {
            return 0;
        }
        $row = $this->db->query(
            'SELECT COUNT(*) AS total FROM (' . $this->listing_sql($filters) . ') listing'
        )->row();
        return $row ? (int) $row->total : 0;
    }

    public function all($filters = array())
    {
        if (!$this->is_ready()) {
            return array();
        }
        $sort = $this->sort_column(isset($filters['sort']) ? $filters['sort'] : '');
        $dir = (isset($filters['dir']) && strtolower($filters['dir']) === 'asc') ? 'ASC' : 'DESC';
        $sql = 'SELECT * FROM (' . $this->listing_sql($filters) . ') listing ORDER BY ' . $sort . ' ' . $dir;
        if (!empty($filters['limit'])) {
            $sql .= ' LIMIT ' . (int) $filters['limit'];
            if (!empty($filters['offset'])) {
                $sql .= ' OFFSET ' . (int) $filters['offset'];
            }
        }
        return $this->db->query($sql)->result();
    }

    public function stats($filters = array())
    {
        if (!$this->is_ready()) {
            return $this->empty_stats();
        }
        $sql = 'SELECT
                COUNT(*) AS listed_count,
                COUNT(DISTINCT store_id) AS store_count,
                COUNT(DISTINCT store_country_id) AS country_count,
                COALESCE(AVG(base_price * fx_rate), 0) AS avg_base,
                COALESCE(AVG(ecommerce_commission * fx_rate), 0) AS avg_commission,
                COALESCE(AVG(platform_commission * fx_rate), 0) AS avg_platform,
                COALESCE(AVG(listed_amount * fx_rate), 0) AS avg_listed,
                COALESCE(AVG(total_revenue * fx_rate), 0) AS avg_revenue,
                COALESCE(AVG(store_markup * fx_rate), 0) AS avg_markup,
                COALESCE(AVG(source_count), 0) AS avg_sources,
                COALESCE(SUM(ecommerce_commission * fx_rate), 0) AS sum_commission,
                COALESCE(SUM(platform_commission * fx_rate), 0) AS sum_platform,
                COALESCE(SUM(listed_amount * fx_rate), 0) AS sum_listed,
                COALESCE(SUM(total_revenue * fx_rate), 0) AS sum_revenue,
                COALESCE(SUM(store_markup * fx_rate), 0) AS sum_markup,
                COALESCE(AVG(CASE WHEN listed_amount > 0 THEN (ecommerce_commission / listed_amount) * 100 ELSE 0 END), 0) AS avg_commission_pct,
                COALESCE(AVG(CASE WHEN listed_amount > 0 THEN (platform_commission / listed_amount) * 100 ELSE 0 END), 0) AS avg_platform_pct,
                COALESCE(AVG(CASE WHEN listed_amount > 0 THEN ((ecommerce_commission + platform_commission) / listed_amount) * 100 ELSE 0 END), 0) AS avg_take_pct,
                COALESCE(AVG(CASE WHEN listed_amount > 0 THEN (store_markup / listed_amount) * 100 ELSE 0 END), 0) AS avg_markup_pct,
                COALESCE(AVG(CASE WHEN listed_amount > 0 THEN (total_revenue / listed_amount) * 100 ELSE 0 END), 0) AS avg_revenue_pct
            FROM (' . $this->listing_sql($filters) . ') listing';
        $row = $this->db->query($sql)->row();
        return $row ? $row : $this->empty_stats();
    }

    protected function empty_stats()
    {
        return (object) array(
            'listed_count' => 0,
            'store_count' => 0,
            'country_count' => 0,
            'avg_base' => 0,
            'avg_commission' => 0,
            'avg_platform' => 0,
            'avg_listed' => 0,
            'avg_revenue' => 0,
            'avg_markup' => 0,
            'avg_sources' => 0,
            'sum_commission' => 0,
            'sum_platform' => 0,
            'sum_listed' => 0,
            'sum_revenue' => 0,
            'sum_markup' => 0,
            'avg_commission_pct' => 0,
            'avg_platform_pct' => 0,
            'avg_take_pct' => 0,
            'avg_markup_pct' => 0,
            'avg_revenue_pct' => 0,
        );
    }

    public function source_urls_map($catalogIds)
    {
        $this->ensure_sources_table();
        $map = array();
        $ids = array();
        foreach ((array) $catalogIds as $id) {
            $id = (int) $id;
            if ($id > 0) {
                $ids[$id] = $id;
                $map[$id] = array();
            }
        }
        if (empty($ids)) {
            return $map;
        }
        $idList = implode(',', $ids);

        $primary = $this->db->query(
            'SELECT id, source_url FROM products WHERE id IN (' . $idList . ') AND source_url IS NOT NULL AND source_url != \'\''
        )->result();
        foreach ($primary as $row) {
            $this->push_source_url($map, (int) $row->id, $row->source_url);
        }

        $rows = $this->db->query(
            'SELECT product_id, source_url, label, source_price
             FROM product_sources
             WHERE product_id IN (' . $idList . ')
               AND source_url IS NOT NULL AND source_url != \'\'
             ORDER BY sort_order ASC, id ASC'
        )->result();
        foreach ($rows as $row) {
            $this->push_source_url($map, (int) $row->product_id, $row->source_url, $row->label, $row->source_price);
        }
        return $map;
    }

    protected function push_source_url(&$map, $productId, $url, $label = '', $price = 0)
    {
        $url = trim((string) $url);
        if ($url === '' || $productId < 1) {
            return;
        }
        if (!isset($map[$productId])) {
            $map[$productId] = array();
        }
        foreach ($map[$productId] as $existing) {
            if (isset($existing['url']) && $existing['url'] === $url) {
                return;
            }
        }
        $map[$productId][] = array(
            'url' => $url,
            'label' => trim((string) $label),
            'price' => (float) $price,
        );
    }

    protected function listing_sql($filters)
    {
        $this->ensure_sources_table();
        if (function_exists('ensure_user_commission_schema')) {
            ensure_user_commission_schema();
        }
        $fee = number_format((float) platform_fee_percent(), 4, '.', '');
        $commPct = 'COALESCE(users.commission_percent, 0)';
        $commFlat = 'COALESCE(users.commission, 0)';
        $hasRates = $this->db->table_exists('currency_rates');
        $fxSelect = $hasRates
            ? 'COALESCE(NULLIF(currency_rates.rate_to_platform, 0), 1)'
            : '1';
        $ratesJoin = $hasRates
            ? 'LEFT JOIN currency_rates ON currency_rates.currency = countries.currency'
            : '';

        $baseExpr = "CASE
            WHEN catalog.id IS NOT NULL THEN COALESCE(NULLIF(catalog.cost_price, 0), catalog.price, 0)
            WHEN COALESCE(products.cost_price, 0) > 0 THEN GREATEST(0, ROUND((products.cost_price - {$commFlat}) / (1 + ({$fee} / 100) + ({$commPct} / 100)), 2))
            ELSE COALESCE(products.price, 0)
        END";
        $ecomCommExpr = "ROUND(({$commFlat}) + (({$baseExpr}) * ({$commPct} / 100)), 2)";

        $where = $this->where_sql($filters);

        return "SELECT
                products.id,
                products.name,
                products.image,
                products.slug,
                products.sku,
                products.status,
                products.store_id,
                products.source_product_id,
                products.created_by,
                stores.name AS store_name,
                stores.domain AS store_domain,
                stores.country_id AS store_country_id,
                stores.price_plus_amount AS store_plus,
                countries.name AS country_name,
                countries.currency AS country_currency,
                catalog.id AS catalog_id,
                {$ecomCommExpr} AS ecommerce_commission,
                COALESCE(products.price, 0) AS listed_amount,
                ROUND(({$baseExpr}), 2) AS base_price,
                ROUND(({$baseExpr}) * ({$fee} / 100), 2) AS platform_commission,
                ROUND(COALESCE(products.price, 0) - ({$baseExpr}), 2) AS total_revenue,
                ROUND(
                    COALESCE(products.price, 0)
                    - ({$baseExpr})
                    - ({$ecomCommExpr})
                    - ROUND(({$baseExpr}) * ({$fee} / 100), 2)
                , 2) AS store_markup,
                COALESCE(NULLIF(products.ship_min_days, 0), NULLIF(catalog.ship_min_days, 0), 0) AS ship_min_days,
                COALESCE(NULLIF(products.ship_max_days, 0), NULLIF(catalog.ship_max_days, 0), 0) AS ship_max_days,
                COALESCE(src_counts.source_count, 0) AS source_count,
                {$fxSelect} AS fx_rate
            FROM products
            INNER JOIN stores ON stores.id = products.store_id
            LEFT JOIN countries ON countries.id = stores.country_id
            LEFT JOIN products catalog ON catalog.id = products.source_product_id
            LEFT JOIN users ON users.UserID = products.created_by
            LEFT JOIN (
                SELECT pid, COUNT(DISTINCT url) AS source_count
                FROM (
                    SELECT id AS pid, TRIM(source_url) AS url
                    FROM products
                    WHERE source_url IS NOT NULL AND TRIM(source_url) <> ''
                    UNION
                    SELECT product_id AS pid, TRIM(source_url) AS url
                    FROM product_sources
                    WHERE source_url IS NOT NULL AND TRIM(source_url) <> ''
                ) src_raw
                GROUP BY pid
            ) src_counts ON src_counts.pid = COALESCE(products.source_product_id, products.id)
            {$ratesJoin}
            WHERE {$where}";
    }

    protected function where_sql($filters)
    {
        $parts = array(
            'products.store_id IS NOT NULL',
            'products.store_id > 0',
        );
        if (!empty($filters['country_id'])) {
            $parts[] = 'stores.country_id = ' . (int) $filters['country_id'];
        }
        if (!empty($filters['store_id'])) {
            $parts[] = 'products.store_id = ' . (int) $filters['store_id'];
        }
        if (!empty($filters['catalog_ids'])) {
            $ids = array();
            foreach ((array) $filters['catalog_ids'] as $id) {
                $id = (int) $id;
                if ($id > 0) {
                    $ids[$id] = $id;
                }
            }
            if ($ids) {
                $list = implode(',', $ids);
                $parts[] = "(products.source_product_id IN ({$list}) OR catalog.id IN ({$list}))";
            }
        }
        $q = isset($filters['q']) ? trim((string) $filters['q']) : '';
        if ($q !== '') {
            $like = $this->db->escape_like_str($q);
            $parts[] = "(products.name LIKE '%{$like}%' ESCAPE '!'
                OR products.sku LIKE '%{$like}%' ESCAPE '!'
                OR catalog.sku LIKE '%{$like}%' ESCAPE '!'
                OR catalog.analyzer_code LIKE '%{$like}%' ESCAPE '!'
                OR stores.name LIKE '%{$like}%' ESCAPE '!'
                OR stores.domain LIKE '%{$like}%' ESCAPE '!')";
        }
        if (!empty($filters['product_id'])) {
            $parts[] = 'products.source_product_id = ' . (int) $filters['product_id'];
        }
        if (!empty($filters['listing_id'])) {
            $parts[] = 'products.id = ' . (int) $filters['listing_id'];
        }
        return implode(' AND ', $parts);
    }

    protected function sort_column($sort)
    {
        $map = array(
            'name' => 'name',
            'store' => 'store_name',
            'country' => 'country_name',
            'ship_min_days' => 'ship_min_days',
            'ship_max_days' => 'ship_max_days',
            'base_price' => 'base_price',
            'ecommerce_commission' => 'ecommerce_commission',
            'platform_commission' => 'platform_commission',
            'listed_amount' => 'listed_amount',
            'total_revenue' => 'total_revenue',
            'source_count' => 'source_count',
            'id' => 'id',
        );
        return isset($map[$sort]) ? $map[$sort] : 'id';
    }

    public function listings_for_catalog_ids($catalogIds)
    {
        $catalogIds = array_values(array_unique(array_filter(array_map('intval', (array) $catalogIds))));
        if (!$this->is_ready() || !$catalogIds) {
            return array();
        }
        return $this->all(array(
            'catalog_ids' => $catalogIds,
            'sort' => 'id',
            'dir' => 'desc',
        ));
    }

    protected function ensure_sources_table()
    {
        if ($this->db->table_exists('product_sources')) {
            return;
        }
        $this->db->query("CREATE TABLE IF NOT EXISTS product_sources (
            id INT(11) NOT NULL AUTO_INCREMENT,
            product_id INT(11) NOT NULL,
            source_url VARCHAR(500) NOT NULL DEFAULT '',
            source_price DECIMAL(12,2) NOT NULL DEFAULT 0.00,
            label VARCHAR(150) NOT NULL DEFAULT '',
            sort_order INT(11) NOT NULL DEFAULT 0,
            PRIMARY KEY (id),
            KEY product_id (product_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    }
}
