<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Store_profitability extends CI_Controller {

    public function __construct()
    {
        parent::__construct();
        ec_require_products();
        $this->load->helper('profit_calc');
        $this->load->model('Store_profitability_model');
        $this->load->model('Store_listing_model');
        $this->load->model('Country_model');
        $this->load->model('Supplier_model');
        $this->load->model('Ec_category_model');
        $this->Store_profitability_model->ensure_schema();
    }

    protected function owner_id()
    {
        return ec_is_ecommerce() ? (int) ec_user()->UserID : 0;
    }

    protected function page_settings_from_request($saved)
    {
        $mode = strtolower(trim((string) $this->input->get_post('budget_mode')));
        if ($mode === '') {
            $mode = $saved['budget_mode'];
        }
        if ($mode !== 'shared') {
            $mode = 'per_product';
        }
        $ads = $this->input->get_post('ads_budget');
        $orders = $this->input->get_post('expected_orders');
        $currency = strtoupper(trim((string) $this->input->get_post('ads_currency')));
        $target = $this->input->get_post('target_profit');
        return array(
            'budget_mode' => $mode,
            'ads_budget' => ($ads === null || $ads === '') ? (float) $saved['ads_budget'] : (float) $ads,
            'ads_currency' => $currency !== '' ? $currency : $saved['ads_currency'],
            'expected_orders' => ($orders === null || $orders === '') ? (float) $saved['expected_orders'] : (float) $orders,
            'target_profit' => ($target === null || $target === '') ? (float) $saved['target_profit'] : (float) $target,
            'payment_fee_percent' => (float) $saved['payment_fee_percent'],
            'payment_fixed_fee' => (float) $saved['payment_fixed_fee'],
            'other_cost' => (float) $saved['other_cost'],
            'return_rate' => (float) $saved['return_rate'],
        );
    }

    protected function overrides()
    {
        $data = $this->session->userdata('store_profit_overrides');
        return is_array($data) ? $data : array();
    }

    protected function set_override($listingId, $values)
    {
        $all = $this->overrides();
        $listingId = (int) $listingId;
        if (!isset($all[$listingId])) {
            $all[$listingId] = array();
        }
        foreach ($values as $key => $value) {
            $all[$listingId][$key] = $value;
        }
        $this->session->set_userdata('store_profit_overrides', $all);
    }

    protected function filters()
    {
        return array(
            'country_id' => (int) $this->input->get('country_id'),
            'store_id' => (int) $this->input->get('store_id'),
            'q' => trim((string) $this->input->get('q')),
            'category_id' => (int) $this->input->get('category_id'),
            'supplier_id' => (int) $this->input->get('supplier_id'),
            'status' => profit_status_key($this->input->get('status')),
            'min_profit' => $this->input->get('min_profit'),
            'max_profit' => $this->input->get('max_profit'),
            'min_margin' => $this->input->get('min_margin'),
            'max_margin' => $this->input->get('max_margin'),
            'sort' => trim((string) $this->input->get('sort')),
            'dir' => strtolower(trim((string) $this->input->get('dir'))) === 'asc' ? 'asc' : 'desc',
            'page' => max(1, (int) $this->input->get('page')),
            'analyze' => $this->input->get('analyze') ? '1' : '',
        );
    }

    public function index()
    {
        $saved = $this->Store_profitability_model->get_settings();
        $settings = $this->page_settings_from_request($saved);
        $filters = $this->filters();
        $rates = $this->Store_profitability_model->get_rates();
        $store = $filters['store_id'] ? $this->Store_profitability_model->store($filters['store_id']) : null;
        if ($store && $filters['country_id'] && (int) $store->country_id !== $filters['country_id']) {
            $store = null;
            $filters['store_id'] = 0;
        }
        $stores = $this->Store_profitability_model->stores($filters['country_id']);
        $items = array();
        $summary = null;
        $slice = array();
        $total = 0;
        $pages = 1;
        if ($store && $filters['analyze'] === '1') {
            $rows = $this->Store_profitability_model->listing_rows($this->owner_id(), array(
                'store_id' => (int) $store->id,
                'country_id' => (int) $store->country_id,
                'category_id' => $filters['category_id'],
                'supplier_id' => $filters['supplier_id'],
                'q' => $filters['q'],
            ));
            $items = $this->Store_profitability_model->analyze_rows($rows, $settings, $rates, $this->overrides());
            $items = $this->apply_table_filters($items, $filters);
            $items = $this->sort_items($items, $filters['sort'], $filters['dir']);
            $summary = $this->Store_profitability_model->summarize($items, $settings);
            $total = count($items);
            $limit = 50;
            $pages = max(1, (int) ceil($total / $limit));
            $page = min($filters['page'], $pages);
            $filters['page'] = $page;
            $slice = array_slice($items, ($page - 1) * $limit, $limit);
        }

        $storeCurrency = $store && !empty($store->country_currency) ? strtoupper($store->country_currency) : 'SEK';
        $this->template->admin('store_profitability/index', array(
            'title' => 'Store Profitability Analyzer',
            'filters' => $filters,
            'settings' => $settings,
            'rates' => $rates,
            'countries' => $this->Country_model->all(),
            'countries_with_stores' => $this->Store_profitability_model->countries_with_stores(),
            'stores' => $stores,
            'store' => $store,
            'store_currency' => $storeCurrency,
            'categories' => $this->Ec_category_model->all(),
            'suppliers' => $this->Supplier_model->all(),
            'items' => $slice,
            'chart_items' => $items,
            'summary' => $summary,
            'total' => $total,
            'pages' => $pages,
            'page' => $filters['page'],
            'currencies' => profit_currency_codes($rates),
        ));
    }

    public function analyze()
    {
        $this->Store_profitability_model->save_settings($this->input->post());
        $query = array(
            'country_id' => (int) $this->input->post('country_id'),
            'store_id' => (int) $this->input->post('store_id'),
            'budget_mode' => $this->input->post('budget_mode'),
            'ads_budget' => $this->input->post('ads_budget'),
            'ads_currency' => $this->input->post('ads_currency'),
            'expected_orders' => $this->input->post('expected_orders'),
            'target_profit' => $this->input->post('target_profit'),
            'analyze' => 1,
        );
        if ((int) $query['store_id'] < 1) {
            $this->session->set_flashdata('error', 'Select a store before analyzing.');
            unset($query['analyze']);
        }
        redirect('admin/store-profitability?' . http_build_query(array_filter($query, function ($v) {
            return $v !== '' && $v !== null;
        })));
    }

    public function fix_profit()
    {
        @set_time_limit(0);
        ignore_user_abort(true);
        $saved = $this->Store_profitability_model->get_settings();
        $settings = $this->page_settings_from_request($saved);
        $storeId = (int) $this->input->post('store_id');
        $countryId = (int) $this->input->post('country_id');
        $query = array(
            'country_id' => $countryId,
            'store_id' => $storeId,
            'budget_mode' => $settings['budget_mode'],
            'ads_budget' => $settings['ads_budget'],
            'ads_currency' => $settings['ads_currency'],
            'expected_orders' => $settings['expected_orders'],
            'target_profit' => $settings['target_profit'],
            'analyze' => 1,
        );
        $redirect = 'admin/store-profitability?' . http_build_query(array_filter($query, function ($v) {
            return $v !== '' && $v !== null;
        }));
        if ($storeId < 1) {
            $this->session->set_flashdata('error', 'Select a store before fixing profit.');
            redirect('admin/store-profitability?' . http_build_query(array_filter(array(
                'country_id' => $countryId,
                'store_id' => $storeId,
                'budget_mode' => $settings['budget_mode'],
                'ads_budget' => $settings['ads_budget'],
                'ads_currency' => $settings['ads_currency'],
                'expected_orders' => $settings['expected_orders'],
                'target_profit' => $settings['target_profit'],
            ), function ($v) {
                return $v !== '' && $v !== null;
            })));
            return;
        }
        $rates = $this->Store_profitability_model->get_rates();
        $ownerId = $this->owner_id();
        $updatedIds = array();
        $alreadyOk = 0;
        for ($pass = 0; $pass < 4; $pass++) {
            $rows = $this->Store_profitability_model->listing_rows($ownerId, array(
                'store_id' => $storeId,
                'country_id' => $countryId,
            ));
            $items = $this->Store_profitability_model->analyze_rows($rows, $settings, $rates, $this->overrides());
            $updates = array();
            $alreadyOk = 0;
            foreach ($items as $item) {
                $catalogId = (int) $item->id;
                if ($catalogId < 1 || isset($updates[$catalogId])) {
                    continue;
                }
                $status = isset($item->metrics['status']) ? $item->metrics['status'] : '';
                if ($status === 'above_target') {
                    $alreadyOk++;
                    continue;
                }
                $gap = isset($item->metrics['profit_gap']) ? (float) $item->metrics['profit_gap'] : 0;
                $required = isset($item->metrics['required_selling_price']) ? (float) $item->metrics['required_selling_price'] : 0;
                $currentSell = isset($item->metrics['selling_price']) ? (float) $item->metrics['selling_price'] : 0;
                $feePct = ((float) $settings['payment_fee_percent']) / 100;
                $returnRate = ((float) $settings['return_rate']) / 100;
                $denom = 1 - $feePct - $returnRate;
                if ($denom <= 0.2) {
                    $denom = 0.92;
                }
                $delta = max($required - $currentSell, $gap / $denom, 0.35);
                $currentExtra = isset($item->extra_amount) ? (float) $item->extra_amount : $this->Store_profitability_model->catalog_extra_amount($catalogId);
                $newExtra = profit_round2($currentExtra + $delta);
                if ($newExtra <= $currentExtra) {
                    $alreadyOk++;
                    continue;
                }
                $updates[$catalogId] = $newExtra;
            }
            if (!$updates) {
                break;
            }
            $this->Store_profitability_model->apply_store_extra_amounts($storeId, $updates);
            foreach ($updates as $catalogId => $unused) {
                $updatedIds[(int) $catalogId] = true;
            }
        }
        $updated = count($updatedIds);
        if ($updated > 0) {
            $this->session->set_flashdata('success', 'Fix Profit updated extra amount on ' . $updated . ' products in one click so net profit reaches the target. ' . $alreadyOk . ' products were already at or above target.');
        } else {
            $this->session->set_flashdata('success', 'All products already reach the target profit. No extra amount was added.');
        }
        redirect($redirect);
    }

    public function apply_rate()
    {
        $currency = strtoupper(trim((string) $this->input->post('rate_currency')));
        $rate = (float) $this->input->post('rate_to_pkr');
        if ($currency === '' || $rate <= 0) {
            $this->session->set_flashdata('error', 'Enter a valid currency and PKR rate.');
        } else {
            $this->Store_profitability_model->save_rate($currency, $rate);
            $this->session->set_flashdata('success', 'Conversion rate applied for this page: 1 ' . $currency . ' = ' . number_format($rate, 4) . ' PKR.');
        }
        $back = $this->input->post('return_query');
        redirect('admin/store-profitability' . ($back ? ('?' . ltrim($back, '?')) : ''));
    }

    public function stores()
    {
        $countryId = (int) $this->input->get('country_id');
        $rows = $this->Store_profitability_model->stores($countryId);
        $out = array();
        foreach ($rows as $row) {
            $out[] = array(
                'id' => (int) $row->id,
                'name' => $row->name,
                'domain' => $row->domain,
                'country_id' => (int) $row->country_id,
                'country_name' => $row->country_name,
                'currency' => $row->country_currency,
            );
        }
        $this->output->set_content_type('application/json')->set_output(json_encode($out));
    }

    public function product($id = 0)
    {
        $id = (int) $id;
        $storeId = (int) $this->input->get('store_id');
        $listingId = (int) $this->input->get('listing_id');
        $saved = $this->Store_profitability_model->get_settings();
        $settings = $this->page_settings_from_request($saved);
        $rates = $this->Store_profitability_model->get_rates();
        $rows = $this->Store_profitability_model->listing_rows($this->owner_id(), array(
            'store_id' => $storeId,
            'product_id' => $id,
            'listing_id' => $listingId,
        ));
        if (!$rows) {
            show_404();
            return;
        }
        $row = $rows[0];
        $item = $this->Store_profitability_model->analyze_row($row, $settings, $rates, $this->override_for((int) $row->listing_id));
        $item->storefront_url = $this->Store_profitability_model->storefront_url($item);
        $storeCurrency = $item->currency ?: 'SEK';
        $this->template->admin('store_profitability/product', array(
            'title' => 'Store Product Profitability',
            'item' => $item,
            'settings' => $settings,
            'rates' => $rates,
            'currencies' => profit_currency_codes($rates),
            'store_currency' => $storeCurrency,
            'override' => $this->override_for((int) $item->listing_id),
            'back_query' => $this->input->get('return') ?: http_build_query(array(
                'country_id' => (int) $item->country_id,
                'store_id' => (int) $item->store_id,
                'budget_mode' => $settings['budget_mode'],
                'ads_budget' => $settings['ads_budget'],
                'ads_currency' => $settings['ads_currency'],
                'expected_orders' => $settings['expected_orders'],
                'analyze' => 1,
            )),
        ));
    }

    public function recalculate($id = 0)
    {
        $listingId = (int) $this->input->post('listing_id');
        $this->set_override($listingId, array(
            'ads_budget' => $this->input->post('ads_budget'),
            'ads_currency' => $this->input->post('ads_currency'),
            'expected_orders' => $this->input->post('expected_orders'),
            'shipping_cost' => $this->input->post('shipping_cost'),
            'other_cost' => $this->input->post('other_cost'),
            'payment_fee_percent' => $this->input->post('payment_fee_percent'),
            'payment_fixed_fee' => $this->input->post('payment_fixed_fee'),
            'return_rate' => $this->input->post('return_rate'),
            'target_profit' => $this->input->post('target_profit'),
        ));
        $rate = $this->input->post('rate_to_pkr');
        $currency = strtoupper(trim((string) $this->input->post('rate_currency')));
        if ($currency !== '' && $rate !== '' && $rate !== null && (float) $rate > 0) {
            $this->Store_profitability_model->save_rate($currency, (float) $rate);
        }
        $this->session->set_flashdata('success', 'Product analysis recalculated. Master store/product records were not changed.');
        redirect('admin/store-profitability/product/' . (int) $id . '?' . http_build_query(array(
            'store_id' => (int) $this->input->post('store_id'),
            'listing_id' => $listingId,
            'budget_mode' => $this->input->post('budget_mode'),
            'ads_budget' => $this->input->post('page_ads_budget'),
            'ads_currency' => $this->input->post('page_ads_currency'),
            'expected_orders' => $this->input->post('page_expected_orders'),
        )));
    }

    public function save_product($id = 0)
    {
        $shipping = $this->input->post('shipping_cost');
        if ($shipping === '' || $shipping === null) {
            $this->session->set_flashdata('error', 'Enter a supplier shipping cost to save.');
        } else {
            $this->Store_profitability_model->update_catalog_shipping((int) $id, $shipping);
            $this->session->set_flashdata('success', 'Supplier shipping cost saved to the product record. Store selling price was not changed.');
        }
        redirect('admin/store-profitability/product/' . (int) $id . '?' . http_build_query(array(
            'store_id' => (int) $this->input->post('store_id'),
            'listing_id' => (int) $this->input->post('listing_id'),
        )));
    }

    public function scenario()
    {
        $saved = $this->Store_profitability_model->get_settings();
        $settings = $this->page_settings_from_request($saved);
        $rates = $this->Store_profitability_model->get_rates();
        $row = (object) array(
            'id' => (int) $this->input->post('product_id'),
            'listing_id' => (int) $this->input->post('listing_id'),
            'sku' => $this->input->post('sku'),
            'analyzer_code' => $this->input->post('sku'),
            'listed_amount' => (float) $this->input->post('selling_price'),
            'cost_price' => (float) $this->input->post('product_cost'),
            'shipping_cost' => (float) $this->input->post('shipping_cost'),
            'currency' => strtoupper(trim((string) $this->input->post('currency'))) ?: 'SEK',
        );
        $item = $this->Store_profitability_model->analyze_row($row, $settings, $rates, array(
            'selling_price' => $this->input->post('selling_price'),
            'product_cost' => $this->input->post('product_cost'),
            'shipping_cost' => $this->input->post('shipping_cost'),
            'ads_budget' => $this->input->post('ads_budget'),
            'expected_orders' => $this->input->post('expected_orders'),
            'ads_currency' => $this->input->post('ads_currency'),
        ));
        $this->output->set_content_type('application/json')->set_output(json_encode(array(
            'net_profit' => $item->metrics['net_profit'],
            'profit_margin' => $item->metrics['profit_margin'],
            'cpa' => $item->metrics['cpa'],
            'local_profit' => $item->metrics['local_profit'],
            'status' => $item->metrics['status'],
            'status_label' => profit_status_label($item->metrics['status']),
            'status_class' => profit_status_class($item->metrics['status']),
            'daily_profit' => $item->daily_profit,
            'currency' => $item->currency,
        )));
    }

    protected function override_for($listingId)
    {
        $all = $this->overrides();
        return isset($all[(int) $listingId]) ? $all[(int) $listingId] : array();
    }

    protected function apply_table_filters($items, $filters)
    {
        $out = array();
        $status = $filters['status'];
        $q = strtolower($filters['q']);
        foreach ($items as $item) {
            if ($status !== '' && $item->metrics['status'] !== $status) {
                continue;
            }
            if ($q !== '') {
                $hay = strtolower($item->analyzer_id . ' ' . $item->name . ' ' . $item->sku . ' ' . $item->supplier_name);
                if (strpos($hay, $q) === false) {
                    continue;
                }
            }
            if ($filters['min_profit'] !== null && $filters['min_profit'] !== '' && $item->metrics['net_profit'] < (float) $filters['min_profit']) {
                continue;
            }
            if ($filters['max_profit'] !== null && $filters['max_profit'] !== '' && $item->metrics['net_profit'] > (float) $filters['max_profit']) {
                continue;
            }
            if ($filters['min_margin'] !== null && $filters['min_margin'] !== '' && $item->metrics['profit_margin'] < (float) $filters['min_margin']) {
                continue;
            }
            if ($filters['max_margin'] !== null && $filters['max_margin'] !== '' && $item->metrics['profit_margin'] > (float) $filters['max_margin']) {
                continue;
            }
            $out[] = $item;
        }
        return $out;
    }

    protected function sort_items($items, $sort, $dir)
    {
        $map = array(
            'selling_price' => function ($item) { return $item->metrics['selling_price']; },
            'product_cost' => function ($item) { return $item->metrics['product_cost']; },
            'shipping' => function ($item) { return $item->metrics['shipping_cost']; },
            'cpa' => function ($item) { return $item->metrics['cpa']; },
            'net_profit' => function ($item) { return $item->metrics['net_profit']; },
            'pkr_profit' => function ($item) { return (float) $item->metrics['local_profit']; },
            'margin' => function ($item) { return $item->metrics['profit_margin']; },
            'profit_gap' => function ($item) { return $item->metrics['profit_gap']; },
        );
        if (!isset($map[$sort])) {
            return $items;
        }
        $getter = $map[$sort];
        usort($items, function ($a, $b) use ($getter, $dir) {
            $av = $getter($a);
            $bv = $getter($b);
            if ($av == $bv) {
                return 0;
            }
            $cmp = ($av < $bv) ? -1 : 1;
            return $dir === 'asc' ? $cmp : -$cmp;
        });
        return $items;
    }
}
