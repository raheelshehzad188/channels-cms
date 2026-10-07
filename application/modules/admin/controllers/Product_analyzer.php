<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Product_analyzer extends CI_Controller {

    public function __construct()
    {
        parent::__construct();
        ec_require_products();
        $this->load->helper('profit_calc');
        $this->load->model('Product_analyzer_model');
        $this->load->model('Store_listing_model');
        $this->load->model('Country_model');
        $this->load->model('Supplier_model');
        $this->load->model('Ec_category_model');
        $this->Product_analyzer_model->ensure_schema();
    }

    protected function owner_id()
    {
        return ec_is_ecommerce() ? (int) ec_user()->UserID : 0;
    }

    protected function catalog($filters = array())
    {
        return $this->Product_analyzer_model->analyze_owner($this->owner_id(), $filters);
    }

    protected function listings($filters = array())
    {
        return $this->Product_analyzer_model->analyze_listings($this->owner_id(), $filters);
    }

    protected function store_summaries($items, $stores = array())
    {
        $by = array();
        foreach ($stores as $store) {
            $id = (int) $store->id;
            if ($id < 1) {
                continue;
            }
            $by[$id] = array(
                'store_id' => $id,
                'store_name' => $store->name,
                'products' => 0,
                'above' => 0,
                'below' => 0,
                'loss' => 0,
            );
        }
        foreach ($items as $item) {
            $id = (int) $item->store_id;
            if ($id < 1) {
                continue;
            }
            if (!isset($by[$id])) {
                $by[$id] = array(
                    'store_id' => $id,
                    'store_name' => !empty($item->store_name) ? $item->store_name : ('Store #' . $id),
                    'products' => 0,
                    'above' => 0,
                    'below' => 0,
                    'loss' => 0,
                );
            }
            $by[$id]['products']++;
            $status = $item->metrics['status'];
            if ($status === 'above_target') {
                $by[$id]['above']++;
            } elseif ($status === 'below_target') {
                $by[$id]['below']++;
            } else {
                $by[$id]['loss']++;
            }
        }
        uasort($by, function ($a, $b) {
            return strcasecmp($a['store_name'], $b['store_name']);
        });
        return array_values($by);
    }

    protected function apply_metric_filters($items, $filters)
    {
        $out = array();
        $status = !empty($filters['status']) ? profit_status_key($filters['status']) : '';
        foreach ($items as $item) {
            if ($status !== '' && $item->metrics['status'] !== $status) {
                continue;
            }
            if (!empty($filters['cheaper']) && (int) $item->cheaper_count <= 0) {
                continue;
            }
            if (isset($filters['min_price']) && $filters['min_price'] !== '' && (float) $item->price < (float) $filters['min_price']) {
                continue;
            }
            if (isset($filters['max_price']) && $filters['max_price'] !== '' && (float) $item->price > (float) $filters['max_price']) {
                continue;
            }
            if (isset($filters['min_profit']) && $filters['min_profit'] !== '' && $item->metrics['net_profit'] < (float) $filters['min_profit']) {
                continue;
            }
            if (isset($filters['max_profit']) && $filters['max_profit'] !== '' && $item->metrics['net_profit'] > (float) $filters['max_profit']) {
                continue;
            }
            if (isset($filters['min_margin']) && $filters['min_margin'] !== '' && $item->metrics['profit_margin'] < (float) $filters['min_margin']) {
                continue;
            }
            if (isset($filters['max_margin']) && $filters['max_margin'] !== '' && $item->metrics['profit_margin'] > (float) $filters['max_margin']) {
                continue;
            }
            if (isset($filters['min_saving']) && $filters['min_saving'] !== '' && $item->supplier_saving < (float) $filters['min_saving']) {
                continue;
            }
            if (isset($filters['max_saving']) && $filters['max_saving'] !== '' && $item->supplier_saving > (float) $filters['max_saving']) {
                continue;
            }
            $out[] = $item;
        }
        return $out;
    }

    protected function page_data($extra = array())
    {
        return array_merge(array(
            'title' => 'Product Analyzer',
            'section' => 'products',
            'countries' => $this->Country_model->all(),
            'suppliers' => $this->Supplier_model->all(),
            'categories' => $this->Ec_category_model->all(),
            'stores' => $this->Product_analyzer_model->analyzer_stores($this->owner_id()),
            'settings' => $this->Product_analyzer_model->get_settings(),
            'rates' => $this->Product_analyzer_model->get_rates(),
        ), $extra);
    }

    public function index()
    {
        $filters = array(
            'q' => trim((string) $this->input->get('q')),
            'store_id' => (int) $this->input->get('store_id'),
            'country_id' => (int) $this->input->get('country_id'),
            'category_id' => (int) $this->input->get('category_id'),
            'supplier_id' => (int) $this->input->get('supplier_id'),
            'status' => profit_status_key($this->input->get('status')),
            'cheaper' => $this->input->get('cheaper') ? '1' : '',
            'min_price' => $this->input->get('min_price'),
            'max_price' => $this->input->get('max_price'),
            'min_profit' => $this->input->get('min_profit'),
            'max_profit' => $this->input->get('max_profit'),
            'min_margin' => $this->input->get('min_margin'),
            'max_margin' => $this->input->get('max_margin'),
            'min_saving' => $this->input->get('min_saving'),
            'max_saving' => $this->input->get('max_saving'),
        );
        foreach (array('min_price', 'max_price', 'min_profit', 'max_profit', 'min_margin', 'max_margin', 'min_saving', 'max_saving') as $key) {
            if ($filters[$key] === null) {
                $filters[$key] = '';
            }
        }

        $storeId = (int) $filters['store_id'];
        $fetch = $filters;
        $fetch['store_id'] = 0;
        $allListings = $this->listings($fetch);
        $pageStores = $this->Product_analyzer_model->analyzer_stores($this->owner_id());
        $storeSummaries = $this->store_summaries($allListings, $pageStores);
        $scoped = $allListings;
        if ($storeId > 0) {
            $scoped = array();
            foreach ($allListings as $item) {
                if ((int) $item->store_id === $storeId) {
                    $scoped[] = $item;
                }
            }
        }
        $metricFilters = $filters;
        $metricFilters['status'] = '';
        $metricFilters['cheaper'] = '';
        $metrics = $this->dashboard_metrics($this->apply_metric_filters($scoped, $metricFilters));
        $items = $this->apply_metric_filters($scoped, $filters);
        $page = max(1, (int) $this->input->get('page'));
        $limit = 50;
        $total = count($items);
        $slice = array_slice($items, ($page - 1) * $limit, $limit);

        $this->template->admin('product_analyzer/index', $this->page_data(array(
            'section' => 'products',
            'items' => $slice,
            'filters' => $filters,
            'page' => $page,
            'limit' => $limit,
            'total' => $total,
            'metrics' => $metrics,
            'stores' => $pageStores,
            'store_summaries' => $storeSummaries,
            'all_store_count' => count($allListings),
            'product_preview' => $this->session->userdata('analyzer_product_import_preview'),
        )));
    }

    public function overview()
    {
        $items = $this->listings();
        $this->template->admin('product_analyzer/overview', $this->page_data(array(
            'section' => 'overview',
            'metrics' => $this->dashboard_metrics($items),
        )));
    }

    protected function dashboard_metrics($items)
    {
        $total = count($items);
        $above = $below = $loss = $cheaper = 0;
        $sumProfitPkr = $sumMargin = $sumCpaPkr = $sumCostPkr = $sumPricePkr = $savingsPkr = 0;
        $nProfit = $nCpa = $nCost = $nPrice = 0;
        $best = $worst = null;
        $byCountry = array();
        $byCategory = array();
        $rates = $this->Product_analyzer_model->get_rates();
        foreach ($items as $item) {
            $status = $item->metrics['status'];
            if ($status === 'above_target') {
                $above++;
            } elseif ($status === 'below_target') {
                $below++;
            } else {
                $loss++;
            }
            $currency = $item->currency ?: 'PKR';
            $profitPkr = $item->metrics['local_profit'];
            if ($profitPkr !== null && $profitPkr !== '') {
                $sumProfitPkr += (float) $profitPkr;
                $nProfit++;
            }
            $sumMargin += $item->metrics['profit_margin'];
            $cpaPkr = profit_to_pkr($item->metrics['cpa'], $currency, $rates);
            if ($cpaPkr !== null) {
                $sumCpaPkr += $cpaPkr;
                $nCpa++;
            }
            $costPkr = profit_to_pkr((float) $item->cost_price, $currency, $rates);
            if ($costPkr !== null) {
                $sumCostPkr += $costPkr;
                $nCost++;
            }
            $pricePkr = profit_to_pkr((float) $item->price, $currency, $rates);
            if ($pricePkr !== null) {
                $sumPricePkr += $pricePkr;
                $nPrice++;
            }
            if (isset($item->supplier_saving_pkr) && $item->supplier_saving_pkr !== null) {
                $savingsPkr += (float) $item->supplier_saving_pkr;
            }
            if ($item->cheaper_count > 0) {
                $cheaper++;
            }
            $rank = $profitPkr === null ? $item->metrics['net_profit'] : (float) $profitPkr;
            $bestRank = $best ? (($best->metrics['local_profit'] === null) ? $best->metrics['net_profit'] : (float) $best->metrics['local_profit']) : null;
            $worstRank = $worst ? (($worst->metrics['local_profit'] === null) ? $worst->metrics['net_profit'] : (float) $worst->metrics['local_profit']) : null;
            if ($best === null || $rank > $bestRank) {
                $best = $item;
            }
            if ($worst === null || $rank < $worstRank) {
                $worst = $item;
            }
            $country = $item->country_name ?: 'Other';
            if (!isset($byCountry[$country])) {
                $byCountry[$country] = array('n' => 0, 'profit_pkr' => 0, 'savings_pkr' => 0, 'cpa_pkr' => 0, 'be_pkr' => 0, 'margin' => 0, 'currencies' => array());
            }
            $byCountry[$country]['n']++;
            $byCountry[$country]['profit_pkr'] += (float) ($profitPkr === null ? 0 : $profitPkr);
            $byCountry[$country]['savings_pkr'] += (float) (isset($item->supplier_saving_pkr) && $item->supplier_saving_pkr !== null ? $item->supplier_saving_pkr : 0);
            $byCountry[$country]['cpa_pkr'] += (float) ($cpaPkr === null ? 0 : $cpaPkr);
            $bePkr = profit_to_pkr($item->metrics['break_even_cpa'], $currency, $rates);
            $byCountry[$country]['be_pkr'] += (float) ($bePkr === null ? 0 : $bePkr);
            $byCountry[$country]['margin'] += $item->metrics['profit_margin'];
            $byCountry[$country]['currencies'][$currency] = true;
            $cat = $item->category_names ? explode(',', $item->category_names)[0] : 'Other';
            $cat = trim($cat) ?: 'Other';
            if (!isset($byCategory[$cat])) {
                $byCategory[$cat] = array('n' => 0, 'profit_pkr' => 0);
            }
            $byCategory[$cat]['n']++;
            $byCategory[$cat]['profit_pkr'] += (float) ($profitPkr === null ? 0 : $profitPkr);
        }
        $avgN = function ($sum, $n) {
            return $n ? profit_round2($sum / $n) : 0;
        };
        $countryChart = array();
        foreach ($byCountry as $name => $row) {
            $n = max(1, $row['n']);
            $countryChart[] = array(
                'name' => $name,
                'profit' => profit_round2($row['profit_pkr'] / $n),
                'savings' => profit_round2($row['savings_pkr']),
                'cpa' => profit_round2($row['cpa_pkr'] / $n),
                'break_even' => profit_round2($row['be_pkr'] / $n),
                'margin' => profit_round2($row['margin'] / $n),
                'currency' => count($row['currencies']) === 1 ? key($row['currencies']) : 'PKR',
            );
        }
        $categoryChart = array();
        foreach ($byCategory as $name => $row) {
            $categoryChart[] = array(
                'name' => $name,
                'profit' => profit_round2($row['profit_pkr'] / max(1, $row['n'])),
            );
        }
        return array(
            'total' => $total,
            'above' => $above,
            'below' => $below,
            'loss' => $loss,
            'avg_profit' => $avgN($sumProfitPkr, $nProfit),
            'avg_margin' => $avgN($sumMargin, $total),
            'avg_cpa' => $avgN($sumCpaPkr, $nCpa),
            'avg_cost' => $avgN($sumCostPkr, $nCost),
            'avg_price' => $avgN($sumPricePkr, $nPrice),
            'savings' => profit_round2($savingsPkr),
            'cheaper' => $cheaper,
            'achievement' => $total ? profit_round2(($above / $total) * 100) : 0,
            'best' => $best,
            'worst' => $worst,
            'country_chart' => $countryChart,
            'category_chart' => $categoryChart,
        );
    }

    public function product($id = 0)
    {
        $listingFilters = array(
            'product_id' => (int) $id,
            'store_id' => (int) $this->input->get('store_id'),
            'listing_id' => (int) $this->input->get('listing_id'),
        );
        $items = $this->Product_analyzer_model->analyze_listings($this->owner_id(), $listingFilters);
        if (!$items) {
            $items = $this->Product_analyzer_model->analyze_owner($this->owner_id(), array('product_id' => (int) $id));
        }
        $item = $items ? $items[0] : null;
        if (!$item) {
            show_404();
            return;
        }
        $settings = $this->Product_analyzer_model->get_settings();
        $rates = $this->Product_analyzer_model->get_rates();
        foreach ($item->options as $option) {
            $option->landed = profit_round2((float) $option->product_cost + (float) $option->shipping_cost);
            $option->is_recommended = $item->recommended_supplier && (int) $item->recommended_supplier->id === (int) $option->id;
            if ((int) $option->supplier_id !== (int) $item->supplier_id) {
                $alt = profit_calculate(array(
                    'selling_price' => (float) $item->metrics['selling_price'],
                    'product_cost' => (float) $option->product_cost,
                    'shipping_cost' => (float) $option->shipping_cost,
                    'currency' => $item->currency,
                    'payment_fee_percent' => $item->meta_payment_fee_percent,
                    'payment_fixed_fee' => $item->meta_payment_fixed_fee,
                    'other_cost' => $item->meta_other_cost,
                    'return_rate' => $item->meta_return_rate,
                    'manual_cpa' => $item->manual_cpa,
                    'actual_cpa' => $item->actual_cpa,
                    'actual_ad_spend' => $item->actual_ad_spend,
                    'actual_orders' => $item->actual_orders,
                ), $settings, $rates);
                $option->impact = profit_round_metrics(array(
                    'current_landed' => $item->metrics['landed_cost'],
                    'new_landed' => $alt['landed_cost'],
                    'savings' => $item->metrics['landed_cost'] - $alt['landed_cost'],
                    'current_profit' => $item->metrics['net_profit'],
                    'new_profit' => $alt['net_profit'],
                    'profit_increase' => $alt['net_profit'] - $item->metrics['net_profit'],
                ));
            } else {
                $option->impact = null;
            }
        }
        $this->load->view('inspinia/admin/product_analyzer/drawer', array(
            'item' => $item,
            'history' => $this->Product_analyzer_model->history((int) $id),
        ));
    }

    public function set_preferred($productId = 0)
    {
        if (!$this->input->post('confirm')) {
            $this->session->set_flashdata('error', 'Setting a preferred supplier requires confirmation.');
            redirect('admin/product-analyzer');
            return;
        }
        $ok = $this->Product_analyzer_model->set_preferred((int) $productId, (int) $this->input->post('option_id'));
        $this->session->set_flashdata($ok ? 'success' : 'error', $ok
            ? 'Preferred supplier updated. Profit was recalculated. Selling price and CPA were not changed.'
            : 'Supplier option not found.');
        redirect('admin/product-analyzer');
    }

    public function research()
    {
        $items = $this->catalog();
        $cheaper = array();
        $without = array();
        foreach ($items as $item) {
            if ($item->cheaper_count > 0) {
                $cheaper[] = $item;
            }
            if (count($item->options) <= 1) {
                $without[] = $item;
            }
        }
        $this->template->admin('product_analyzer/research', $this->page_data(array(
            'section' => 'research',
            'cheaper' => $cheaper,
            'without' => $without,
            'preview' => $this->session->userdata('analyzer_import_preview'),
        )));
    }

    public function analysis()
    {
        $items = $this->listings();
        $metrics = $this->dashboard_metrics($items);
        $countries = array();
        $categories = array();
        $suppliers = array();
        $stores = array();
        foreach ($items as $item) {
            $store = !empty($item->store_name) ? $item->store_name : 'Unknown store';
            if (!isset($stores[$store])) {
                $stores[$store] = $this->empty_group();
            }
            $this->add_group($stores[$store], $item);
            $country = $item->country_name ?: 'Other';
            if (!isset($countries[$country])) {
                $countries[$country] = $this->empty_group();
            }
            $this->add_group($countries[$country], $item);
            $cat = $item->category_names ? trim(explode(',', $item->category_names)[0]) : 'Other';
            if ($cat === '') {
                $cat = 'Other';
            }
            if (!isset($categories[$cat])) {
                $categories[$cat] = $this->empty_group();
            }
            $this->add_group($categories[$cat], $item);
            $sup = $item->supplier_name ?: 'Unassigned';
            if (!isset($suppliers[$sup])) {
                $suppliers[$sup] = $this->empty_group();
            }
            $this->add_group($suppliers[$sup], $item);
        }
        $this->template->admin('product_analyzer/analysis', $this->page_data(array(
            'section' => 'analysis',
            'store_rows' => $this->finalize_groups($stores),
            'country_rows' => $this->finalize_groups($countries),
            'category_rows' => $this->finalize_groups($categories),
            'supplier_rows' => $this->finalize_groups($suppliers),
            'metrics' => $metrics,
        )));
    }

    protected function empty_group()
    {
        return array(
            'products' => 0, 'price' => 0, 'cost' => 0, 'shipping' => 0, 'cpa' => 0,
            'profit' => 0, 'profit_pkr' => 0, 'margin' => 0, 'above' => 0, 'below' => 0, 'loss' => 0,
            'savings' => 0, 'cheaper' => 0, 'currencies' => array(),
        );
    }

    protected function add_group(&$g, $item)
    {
        $currency = $item->currency ?: '';
        $g['products']++;
        $g['price'] += (float) $item->price;
        $g['cost'] += (float) $item->cost_price;
        $g['shipping'] += (float) $item->shipping_cost;
        $g['cpa'] += $item->metrics['cpa'];
        $g['profit'] += $item->metrics['net_profit'];
        $g['profit_pkr'] += (float) ($item->metrics['local_profit'] === null ? 0 : $item->metrics['local_profit']);
        $g['margin'] += $item->metrics['profit_margin'];
        $g['savings'] += $item->supplier_saving;
        if ($currency !== '') {
            $g['currencies'][$currency] = true;
        }
        if ($item->metrics['status'] === 'above_target') {
            $g['above']++;
        } elseif ($item->metrics['status'] === 'below_target') {
            $g['below']++;
        } else {
            $g['loss']++;
        }
        if ($item->cheaper_count > 0) {
            $g['cheaper']++;
        }
    }

    protected function finalize_groups($groups)
    {
        $out = array();
        foreach ($groups as $name => $g) {
            $n = max(1, $g['products']);
            $mixed = count($g['currencies']) > 1;
            $currency = count($g['currencies']) === 1 ? key($g['currencies']) : '';
            $out[] = array(
                'name' => $name,
                'currency' => $mixed ? 'MIXED' : $currency,
                'mixed' => $mixed,
                'products' => $g['products'],
                'avg_price' => $mixed ? null : profit_round2($g['price'] / $n),
                'avg_cost' => $mixed ? null : profit_round2($g['cost'] / $n),
                'avg_shipping' => $mixed ? null : profit_round2($g['shipping'] / $n),
                'avg_cpa' => $mixed ? null : profit_round2($g['cpa'] / $n),
                'avg_profit' => $mixed ? null : profit_round2($g['profit'] / $n),
                'avg_profit_pkr' => profit_round2($g['profit_pkr'] / $n),
                'avg_margin' => profit_round2($g['margin'] / $n),
                'above' => $g['above'],
                'below' => $g['below'],
                'loss' => $g['loss'],
                'savings' => $mixed ? null : profit_round2($g['savings']),
                'cheaper' => $g['cheaper'],
            );
        }
        return $out;
    }

    protected function ads_preview($settings = null, $rates = null)
    {
        $settings = $settings ? $settings : $this->Product_analyzer_model->get_settings();
        $rates = $rates ? $rates : $this->Product_analyzer_model->get_rates();
        $budgetCurrency = profit_budget_currency($settings);
        $calculated = profit_calculated_cpa_budget($settings);
        $examples = array();
        foreach (array('SEK', 'GBP', 'EUR', 'USD') as $code) {
            $examples[$code] = profit_round2(profit_convert_via_pkr($calculated, $budgetCurrency, $code, $rates));
        }
        $actual = ($settings['actual_orders'] && $settings['actual_orders'] > 0 && $settings['actual_ad_spend'] !== null)
            ? $settings['actual_ad_spend'] / $settings['actual_orders']
            : null;
        $mode = $actual !== null ? 'actual' : ($settings['manual_cpa'] !== null ? 'manual' : 'calculated');
        return array(
            'budget_currency' => $budgetCurrency,
            'calculated_cpa' => profit_round2($calculated),
            'calculated_cpa_native' => profit_round2(profit_convert_via_pkr($calculated, $budgetCurrency, $settings['default_currency'], $rates)),
            'cpa_examples' => $examples,
            'actual_cpa' => $actual === null ? null : profit_round2($actual),
            'active_mode' => $mode,
            'currency_codes' => profit_currency_codes($rates),
        );
    }

    public function ads()
    {
        $this->template->admin('product_analyzer/ads', $this->page_data($this->ads_preview() + array(
            'section' => 'ads',
        )));
    }

    public function scenario()
    {
        $this->template->admin('product_analyzer/scenario', $this->page_data(array(
            'section' => 'scenario',
        )));
    }

    public function scenario_calc()
    {
        $settings = $this->Product_analyzer_model->get_settings();
        $rates = $this->Product_analyzer_model->get_rates();
        $input = array(
            'selling_price' => (float) $this->input->post('selling_price'),
            'product_cost' => (float) $this->input->post('product_cost'),
            'shipping_cost' => (float) $this->input->post('shipping_cost'),
            'currency' => strtoupper(trim((string) $this->input->post('currency'))) ?: $settings['default_currency'],
            'payment_fee_percent' => $this->input->post('payment_fee_percent'),
            'payment_fixed_fee' => $this->input->post('payment_fixed_fee'),
            'other_cost' => $this->input->post('other_cost'),
            'return_rate' => $this->input->post('return_rate'),
            'manual_cpa' => $this->input->post('cpa'),
            'actual_cpa' => null,
            'actual_ad_spend' => null,
            'actual_orders' => null,
        );
        $scenarioSettings = $settings;
        $scenarioSettings['target_profit'] = $this->input->post('target_profit') !== '' ? (float) $this->input->post('target_profit') : $settings['target_profit'];
        $scenarioSettings['default_currency'] = $input['currency'];
        $scenarioSettings['actual_ad_spend'] = null;
        $scenarioSettings['actual_orders'] = null;
        $scenarioSettings['manual_cpa'] = null;
        $result = profit_round_metrics(profit_calculate($input, $scenarioSettings, $rates));
        $table = array();
        foreach (array(40, 50, 60, 70, 80, 100, 120) as $cpa) {
            $copy = $input;
            $copy['manual_cpa'] = $cpa;
            $row = profit_calculate($copy, $scenarioSettings, $rates);
            $table[] = array('cpa' => $cpa, 'net_profit' => profit_round2($row['net_profit']));
        }
        $this->output->set_content_type('application/json')->set_output(json_encode(array(
            'result' => $result,
            'cpa_table' => $table,
        )));
    }

    public function settings()
    {
        $this->template->admin('product_analyzer/settings', $this->page_data($this->ads_preview() + array(
            'section' => 'settings',
        )));
    }

    public function save_settings()
    {
        $this->Product_analyzer_model->save_settings($this->input->post());
        $rates = $this->input->post('rates');
        if (!is_array($rates)) {
            $rates = array();
        }
        $newCode = strtoupper(trim((string) $this->input->post('rates_new_code')));
        $newRate = $this->input->post('rates_new_rate');
        if ($newCode !== '' && $newRate !== '' && $newRate !== null) {
            $rates[$newCode] = $newRate;
        }
        if ($rates) {
            $this->Product_analyzer_model->save_rates($rates);
        }
        $this->session->set_flashdata('success', 'Analyzer settings saved. All product metrics now recalculate from the shared engine.');
        redirect('admin/product-analyzer/settings');
    }

    public function export($type = 'products')
    {
        $items = ($type === 'research' || $type === 'research-results') ? $this->catalog() : $this->listings();
        if ($type === 'research') {
            $columns = array('product_id', 'product_name', 'sku', 'country', 'currency', 'category', 'current_supplier', 'current_product_cost', 'current_shipping_cost', 'current_landed_cost', 'selling_price', 'current_cpa', 'current_net_profit', 'current_profit_margin', 'product_url');
            $filename = 'supplier-research-sheet.csv';
        } elseif ($type === 'research-results') {
            $columns = array('product_id', 'product_name', 'current_supplier', 'current_product_cost', 'current_shipping_cost', 'current_landed_cost', 'new_supplier', 'new_supplier_product_url', 'new_product_cost', 'new_shipping_cost', 'new_landed_cost', 'currency', 'delivery_min_days', 'delivery_max_days', 'moq', 'saving_amount', 'saving_percentage', 'supplier_status', 'research_notes', 'checked_date');
            $filename = 'supplier-research-results.csv';
        } else {
            $columns = array('product_id', 'product_name', 'store', 'sku', 'country', 'currency', 'category', 'supplier', 'product_cost', 'shipping_cost', 'selling_price', 'payment_fee', 'return_allowance', 'cpa', 'net_profit', 'profit_margin', 'break_even_cpa', 'target_cpa', 'required_selling_price', 'local_profit', 'supplier_saving');
            $filename = 'product-analyzer-export.csv';
        }
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        $out = fopen('php://output', 'w');
        fputcsv($out, $columns);
        foreach ($items as $item) {
            if ($type === 'research-results') {
                $alts = array();
                foreach ($item->options as $option) {
                    if ((int) $option->supplier_id !== (int) $item->supplier_id) {
                        $alts[] = $option;
                    }
                }
                if (!$alts) {
                    fputcsv($out, $this->csv_sanitize(array(
                        $item->analyzer_id, $item->name, $item->supplier_name, $item->cost_price, $item->shipping_cost,
                        $item->metrics['landed_cost'], '', '', '', '', '', $item->currency, '', '', '', '', '', 'no_alternative', '', '',
                    )));
                    continue;
                }
                foreach ($alts as $alt) {
                    $landed = (float) $alt->product_cost + (float) $alt->shipping_cost;
                    $saving = $item->metrics['landed_cost'] - $landed;
                    fputcsv($out, $this->csv_sanitize(array(
                        $item->analyzer_id, $item->name, $item->supplier_name, $item->cost_price, $item->shipping_cost,
                        $item->metrics['landed_cost'], $alt->supplier_name, $alt->supplier_product_url, $alt->product_cost, $alt->shipping_cost,
                        $landed, $item->currency, $alt->delivery_min_days, $alt->delivery_max_days, $alt->moq,
                        $saving, $item->metrics['landed_cost'] ? ($saving / $item->metrics['landed_cost']) * 100 : 0,
                        $saving > 0 ? 'cheaper_found' : 'same', $alt->notes, $alt->checked_date,
                    )));
                }
                continue;
            }
            $base = array(
                'product_id' => $item->analyzer_id,
                'product_name' => $item->name,
                'store' => isset($item->store_name) ? $item->store_name : '',
                'sku' => $item->sku,
                'country' => $item->country_name,
                'currency' => $item->currency,
                'category' => $item->category_names,
                'supplier' => $item->supplier_name,
                'current_supplier' => $item->supplier_name,
                'selling_price' => $item->metrics['selling_price'],
                'product_cost' => $item->metrics['product_cost'],
                'current_product_cost' => $item->metrics['product_cost'],
                'shipping_cost' => $item->metrics['shipping_cost'],
                'current_shipping_cost' => $item->metrics['shipping_cost'],
                'current_landed_cost' => $item->metrics['landed_cost'],
                'payment_fee' => $item->metrics['payment_fee'],
                'return_allowance' => $item->metrics['return_allowance'],
                'cpa' => $item->metrics['cpa'],
                'current_cpa' => $item->metrics['cpa'],
                'net_profit' => $item->metrics['net_profit'],
                'current_net_profit' => $item->metrics['net_profit'],
                'profit_margin' => $item->metrics['profit_margin'],
                'current_profit_margin' => $item->metrics['profit_margin'],
                'break_even_cpa' => $item->metrics['break_even_cpa'],
                'target_cpa' => $item->metrics['target_cpa'],
                'required_selling_price' => $item->metrics['required_selling_price'],
                'local_profit' => $item->metrics['local_profit'],
                'supplier_saving' => $item->supplier_saving,
                'product_url' => $item->source_url,
            );
            $line = array();
            foreach ($columns as $col) {
                $line[] = isset($base[$col]) ? $base[$col] : '';
            }
            fputcsv($out, $this->csv_sanitize($line));
        }
        fclose($out);
        exit;
    }

    protected function csv_sanitize($row)
    {
        $out = array();
        foreach ($row as $value) {
            $value = (string) $value;
            if (preg_match('/^[=+\-@]/', $value)) {
                $value = "'" . $value;
            }
            $out[] = $value;
        }
        return $out;
    }

    public function import_preview()
    {
        if (empty($_FILES['file']['tmp_name'])) {
            $this->session->set_flashdata('error', 'Upload a CSV file.');
            redirect('admin/product-analyzer/research');
            return;
        }
        $allowNew = (string) $this->input->post('allow_new') === '1';
        $handle = fopen($_FILES['file']['tmp_name'], 'r');
        if (!$handle) {
            $this->session->set_flashdata('error', 'The CSV could not be read.');
            redirect('admin/product-analyzer/research');
            return;
        }
        $header = fgetcsv($handle);
        if (!$header) {
            fclose($handle);
            $this->session->set_flashdata('error', 'The CSV has no header row.');
            redirect('admin/product-analyzer/research');
            return;
        }
        $map = array();
        foreach ($header as $i => $col) {
            $map[strtolower(trim(str_replace(' ', '_', $col)))] = $i;
        }
        $cell = function ($row, $keys) use ($map) {
            foreach ((array) $keys as $key) {
                if (isset($map[$key]) && isset($row[$map[$key]]) && trim((string) $row[$map[$key]]) !== '') {
                    return trim((string) $row[$map[$key]]);
                }
            }
            return '';
        };
        $rows = array();
        $line = 1;
        $settings = $this->Product_analyzer_model->get_settings();
        $rates = $this->Product_analyzer_model->get_rates();
        while (($raw = fgetcsv($handle)) !== false) {
            $line++;
            $productId = $cell($raw, array('product_id', 'analyzer_code', 'sku'));
            $supplierName = $cell($raw, array('supplier_name', 'new_supplier'));
            $cost = $cell($raw, array('supplier_cost', 'new_product_cost', 'product_cost'));
            $ship = $cell($raw, array('shipping_cost', 'new_shipping_cost'));
            $issues = array();
            if ($productId === '') {
                $issues[] = 'Product ID is required.';
            }
            if ($supplierName === '') {
                $issues[] = 'Supplier name is required.';
            }
            if ($cost === '' || !is_numeric($cost) || (float) $cost < 0) {
                $issues[] = 'Supplier cost must be 0 or greater.';
            }
            if ($ship === '' || !is_numeric($ship) || (float) $ship < 0) {
                $issues[] = 'Shipping cost must be 0 or greater.';
            }
            $product = $productId !== '' ? $this->Product_analyzer_model->find_by_code($productId, $this->owner_id()) : null;
            $action = 'skip';
            $impact = null;
            if (!$product) {
                $issues[] = 'Product ID not found. Existing catalog products are not duplicated.';
                if ($allowNew) {
                    $issues[] = 'New product creation from research import is disabled to protect Product IDs.';
                }
            } elseif (!$issues) {
                $action = 'add_or_update';
                $product = $this->Product_analyzer_model->analyze($product);
                $productCurrency = $product->currency ?: $settings['default_currency'];
                $input = array(
                    'selling_price' => (float) $product->metrics['selling_price'],
                    'product_cost' => (float) $product->metrics['product_cost'],
                    'shipping_cost' => (float) $product->metrics['shipping_cost'],
                    'currency' => $productCurrency,
                    'manual_cpa' => null,
                    'actual_cpa' => null,
                    'actual_ad_spend' => null,
                    'actual_orders' => null,
                );
                $current = profit_calculate($input, $settings, $rates);
                $next = profit_calculate(array_merge($input, array(
                    'product_cost' => (float) $cost,
                    'shipping_cost' => (float) $ship,
                )), $settings, $rates);
                $impact = array(
                    'current_landed' => profit_round2($current['landed_cost']),
                    'new_landed' => profit_round2($next['landed_cost']),
                    'savings' => profit_round2($current['landed_cost'] - $next['landed_cost']),
                    'current_profit' => profit_round2($current['net_profit']),
                    'new_profit' => profit_round2($next['net_profit']),
                );
            }
            $rows[] = array(
                'row' => $line,
                'action' => $action,
                'product_id' => $productId,
                'product_name' => $product ? $product->name : $cell($raw, 'product_name'),
                'new_supplier' => $supplierName,
                'product_cost' => $cost,
                'shipping_cost' => $ship,
                'supplier_url' => $cell($raw, array('supplier_url', 'new_supplier_url')),
                'supplier_product_url' => $cell($raw, array('supplier_product_url', 'new_supplier_product_url')),
                'delivery_min_days' => $cell($raw, 'delivery_min_days'),
                'delivery_max_days' => $cell($raw, 'delivery_max_days'),
                'moq' => $cell($raw, 'moq'),
                'notes' => $cell($raw, array('notes', 'research_notes')),
                'checked_date' => $cell($raw, 'checked_date') ?: date('Y-m-d'),
                'currency' => $cell($raw, 'currency'),
                'issues' => $issues,
                'impact' => $impact,
                'found' => (bool) $product,
            );
        }
        fclose($handle);
        $this->session->set_userdata('analyzer_import_preview', $rows);
        redirect('admin/product-analyzer/research');
    }

    public function import_confirm()
    {
        $rows = $this->session->userdata('analyzer_import_preview');
        if (!$rows) {
            $this->session->set_flashdata('error', 'Import preview expired. Upload the file again.');
            redirect('admin/product-analyzer/research');
            return;
        }
        $added = $updated = 0;
        foreach ($rows as $row) {
            if ($row['action'] !== 'add_or_update') {
                continue;
            }
            $product = $this->Product_analyzer_model->find_by_code($row['product_id'], $this->owner_id());
            if (!$product) {
                continue;
            }
            $result = $this->Product_analyzer_model->upsert_option($product, array(
                'supplier_name' => $row['new_supplier'],
                'supplier_url' => $row['supplier_url'],
                'supplier_product_url' => $row['supplier_product_url'],
                'product_cost' => $row['product_cost'],
                'shipping_cost' => $row['shipping_cost'],
                'currency' => $row['currency'],
                'delivery_min_days' => $row['delivery_min_days'],
                'delivery_max_days' => $row['delivery_max_days'],
                'moq' => $row['moq'],
                'notes' => $row['notes'],
                'checked_date' => $row['checked_date'],
                'source' => 'research',
            ), false);
            if (!empty($result['created'])) {
                $added++;
            } else {
                $updated++;
            }
        }
        $this->session->unset_userdata('analyzer_import_preview');
        $this->session->set_flashdata('success', "Import complete. Added {$added} supplier options, updated {$updated}. Preferred suppliers were not changed.");
        redirect('admin/product-analyzer/research');
    }

    public function import_cancel()
    {
        $this->session->unset_userdata('analyzer_import_preview');
        redirect('admin/product-analyzer/research');
    }

    public function save_product($id = 0)
    {
        $product = $this->Product_analyzer_model->find_by_code('P' . (int) $id, $this->owner_id());
        if (!$product) {
            $this->session->set_flashdata('error', 'Product not found in the catalog.');
            redirect('admin/product-analyzer');
            return;
        }
        $this->Product_analyzer_model->update_product_costs((int) $id, array(
            'cost_price' => $this->input->post('product_cost'),
            'shipping_cost' => $this->input->post('shipping_cost'),
        ));
        $this->Product_analyzer_model->save_meta((int) $id, array(
            'payment_fee_percent' => $this->input->post('payment_fee_percent'),
            'payment_fixed_fee' => $this->input->post('payment_fixed_fee'),
            'other_cost' => $this->input->post('other_cost'),
            'return_rate' => $this->input->post('return_rate'),
            'manual_cpa' => $this->input->post('manual_cpa'),
            'actual_cpa' => $this->input->post('actual_cpa'),
            'actual_ad_spend' => $this->input->post('actual_ad_spend'),
            'actual_orders' => $this->input->post('actual_orders'),
        ));
        $this->session->set_flashdata('success', 'Product analyzer fields saved. Metrics were recalculated. Product ID was not changed.');
        redirect('admin/product-analyzer');
    }

    public function import_products_preview()
    {
        if (empty($_FILES['file']['tmp_name'])) {
            $this->session->set_flashdata('error', 'Upload a CSV file.');
            redirect('admin/product-analyzer');
            return;
        }
        $handle = fopen($_FILES['file']['tmp_name'], 'r');
        if (!$handle) {
            $this->session->set_flashdata('error', 'The CSV could not be read.');
            redirect('admin/product-analyzer');
            return;
        }
        $header = fgetcsv($handle);
        if (!$header) {
            fclose($handle);
            $this->session->set_flashdata('error', 'The CSV has no header row.');
            redirect('admin/product-analyzer');
            return;
        }
        $map = array();
        foreach ($header as $i => $col) {
            $map[strtolower(trim(str_replace(' ', '_', $col)))] = $i;
        }
        $cell = function ($row, $keys) use ($map) {
            foreach ((array) $keys as $key) {
                if (isset($map[$key]) && isset($row[$map[$key]]) && trim((string) $row[$map[$key]]) !== '') {
                    return trim((string) $row[$map[$key]]);
                }
            }
            return '';
        };
        $rows = array();
        $line = 1;
        while (($raw = fgetcsv($handle)) !== false) {
            $line++;
            $productId = $cell($raw, array('product_id', 'analyzer_code', 'sku'));
            $issues = array();
            if ($productId === '') {
                $issues[] = 'Product ID is required.';
            }
            $product = $productId !== '' ? $this->Product_analyzer_model->find_by_code($productId, $this->owner_id()) : null;
            $action = 'skip';
            if (!$product) {
                $issues[] = 'Product ID not found. Existing catalog products are not duplicated.';
            } else {
                $action = 'update';
            }
            $rows[] = array(
                'row' => $line,
                'action' => $action,
                'product_id' => $productId,
                'product_name' => $product ? $product->name : $cell($raw, 'product_name'),
                'selling_price' => $cell($raw, array('selling_price', 'price')),
                'product_cost' => $cell($raw, array('product_cost', 'cost_price')),
                'shipping_cost' => $cell($raw, 'shipping_cost'),
                'manual_cpa' => $cell($raw, 'manual_cpa'),
                'actual_cpa' => $cell($raw, 'actual_cpa'),
                'payment_fee_percent' => $cell($raw, 'payment_fee_percent'),
                'payment_fixed_fee' => $cell($raw, 'payment_fixed_fee'),
                'other_cost' => $cell($raw, 'other_cost'),
                'return_rate' => $cell($raw, 'return_rate'),
                'issues' => $issues,
            );
        }
        fclose($handle);
        $this->session->set_userdata('analyzer_product_import_preview', $rows);
        redirect('admin/product-analyzer');
    }

    public function import_products_confirm()
    {
        $rows = $this->session->userdata('analyzer_product_import_preview');
        if (!$rows) {
            $this->session->set_flashdata('error', 'Import preview expired. Upload the file again.');
            redirect('admin/product-analyzer');
            return;
        }
        $updated = 0;
        foreach ($rows as $row) {
            if ($row['action'] !== 'update') {
                continue;
            }
            $product = $this->Product_analyzer_model->find_by_code($row['product_id'], $this->owner_id());
            if (!$product) {
                continue;
            }
            $this->Product_analyzer_model->update_product_costs((int) $product->id, array(
                'cost_price' => $row['product_cost'] !== '' ? $row['product_cost'] : null,
                'shipping_cost' => $row['shipping_cost'] !== '' ? $row['shipping_cost'] : null,
            ));
            $meta = array();
            foreach (array('manual_cpa', 'actual_cpa', 'payment_fee_percent', 'payment_fixed_fee', 'other_cost', 'return_rate') as $field) {
                if ($row[$field] !== '') {
                    $meta[$field] = $row[$field];
                }
            }
            if ($meta) {
                $this->Product_analyzer_model->save_meta((int) $product->id, $meta);
            }
            $updated++;
        }
        $this->session->unset_userdata('analyzer_product_import_preview');
        $this->session->set_flashdata('success', "Product import complete. Updated {$updated} existing products by Product ID. No new products were created.");
        redirect('admin/product-analyzer');
    }

    public function import_products_cancel()
    {
        $this->session->unset_userdata('analyzer_product_import_preview');
        redirect('admin/product-analyzer');
    }
}
