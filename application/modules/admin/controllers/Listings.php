<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Listings extends CI_Controller {

    public function __construct()
    {
        parent::__construct();
        ec_require_admin();
        $this->load->model('Store_listing_model');
        $this->load->model('Country_model');
        ec_ensure_currency_schema();
    }

    public function index()
    {
        $countryId = (int) $this->input->get('country_id');
        $storeId = (int) $this->input->get('store_id');
        $q = trim((string) $this->input->get('q'));
        $sort = trim((string) $this->input->get('sort'));
        $dir = strtolower(trim((string) $this->input->get('dir'))) === 'asc' ? 'asc' : 'desc';
        $allowedSort = array(
            'name', 'store', 'country', 'ship_min_days', 'ship_max_days', 'base_price', 'ecommerce_commission',
            'platform_commission', 'listed_amount', 'total_revenue', 'source_count', 'id',
        );
        if (!in_array($sort, $allowedSort, true)) {
            $sort = 'id';
        }

        $stores = $this->Store_listing_model->filter_stores($countryId);
        if ($storeId) {
            $validStore = false;
            foreach ($stores as $store) {
                if ((int) $store->id === $storeId) {
                    $validStore = true;
                    break;
                }
            }
            if (!$validStore) {
                $storeId = 0;
            }
        }

        $filters = array(
            'country_id' => $countryId,
            'store_id' => $storeId,
            'q' => $q,
            'sort' => $sort,
            'dir' => $dir,
        );

        $perPageOptions = array(10, 25, 50, 100);
        $requestedPerPage = $this->input->get('per_page');
        $perPage = (int) $requestedPerPage;
        if (!in_array($perPage, $perPageOptions, true)) {
            $cookiePerPage = (int) $this->input->cookie('admin_listings_per_page');
            $perPage = in_array($cookiePerPage, $perPageOptions, true) ? $cookiePerPage : 25;
        }
        $this->load->helper('cookie');
        $this->input->set_cookie(array(
            'name' => 'admin_listings_per_page',
            'value' => (string) $perPage,
            'expire' => 30 * 24 * 3600,
            'path' => '/',
        ));

        $total = $this->Store_listing_model->count_filtered($filters);
        $totalPages = $total > 0 ? (int) ceil($total / $perPage) : 1;
        $page = (int) $this->input->get('page');
        if ($page < 1) {
            $page = 1;
        }
        if ($page > $totalPages) {
            $page = $totalPages;
        }
        $offset = ($page - 1) * $perPage;
        $listQuery = array(
            'q' => $q,
            'country_id' => $countryId,
            'store_id' => $storeId,
            'sort' => $sort,
            'dir' => $dir,
            'per_page' => $perPage,
        );
        $pageUrl = function ($pageNum) use ($listQuery) {
            $query = $listQuery;
            $query['page'] = max(1, (int) $pageNum);
            return base_url('admin/listings') . '?' . http_build_query($query);
        };

        $filters['limit'] = $perPage;
        $filters['offset'] = $offset;
        $products = $this->Store_listing_model->all($filters);
        $catalogIds = array();
        foreach ($products as $product) {
            $catalogId = !empty($product->catalog_id) ? (int) $product->catalog_id : (int) $product->id;
            if ($catalogId > 0) {
                $catalogIds[] = $catalogId;
            }
            $product->storefront_url = $this->storefront_product_url($product);
        }
        $sourcesMap = $this->Store_listing_model->source_urls_map($catalogIds);
        foreach ($products as $product) {
            $catalogId = !empty($product->catalog_id) ? (int) $product->catalog_id : (int) $product->id;
            $product->source_urls = isset($sourcesMap[$catalogId]) ? $sourcesMap[$catalogId] : array();
        }

        $from = $total > 0 ? ($offset + 1) : 0;
        $to = min($offset + $perPage, $total);
        $window = 2;
        $pageStart = max(1, $page - $window);
        $pageEnd = min($totalPages, $page + $window);
        $pageNumbers = array();
        if ($pageStart > 1) {
            $pageNumbers[] = array('label' => 1, 'url' => $pageUrl(1), 'current' => false);
            if ($pageStart > 2) {
                $pageNumbers[] = array('label' => '…', 'url' => '', 'current' => false);
            }
        }
        for ($i = $pageStart; $i <= $pageEnd; $i++) {
            $pageNumbers[] = array('label' => $i, 'url' => $pageUrl($i), 'current' => $i === $page);
        }
        if ($pageEnd < $totalPages) {
            if ($pageEnd < $totalPages - 1) {
                $pageNumbers[] = array('label' => '…', 'url' => '', 'current' => false);
            }
            $pageNumbers[] = array('label' => $totalPages, 'url' => $pageUrl($totalPages), 'current' => false);
        }

        $data = array(
            'title' => 'Store Listings',
            'products' => $products,
            'stats' => $this->Store_listing_model->stats(array(
                'country_id' => $countryId,
                'store_id' => $storeId,
                'q' => $q,
            )),
            'countries' => $this->Country_model->all(),
            'stores' => $stores,
            'country_id' => $countryId,
            'store_id' => $storeId,
            'q' => $q,
            'sort' => $sort,
            'dir' => $dir,
            'platform_currency' => platform_currency(),
            'platform_fee_percent' => platform_fee_percent(),
            'list_page' => $page,
            'per_page' => $perPage,
            'per_page_options' => $perPageOptions,
            'total' => $total,
            'total_pages' => $totalPages,
            'from_row' => $from,
            'to_row' => $to,
            'page_numbers' => $pageNumbers,
            'first_page_url' => $pageUrl(1),
            'prev_page_url' => $pageUrl($page - 1),
            'next_page_url' => $pageUrl($page + 1),
            'last_page_url' => $pageUrl($totalPages),
            'has_prev_page' => $page > 1,
            'has_next_page' => $page < $totalPages,
            'filter_query' => $listQuery,
        );
        $this->template->admin('listings/index', $data);
    }

    protected function storefront_product_url($product)
    {
        $slug = isset($product->slug) ? trim((string) $product->slug) : '';
        $path = $slug !== '' ? ('product/' . (function_exists('storefront_path_slug') ? storefront_path_slug($slug, 'product') : rawurlencode($slug))) : ('product/' . (int) $product->id);
        $domain = isset($product->store_domain) ? trim((string) $product->store_domain) : '';
        $url = site_url($path);
        if ($domain !== '') {
            $url .= (strpos($url, '?') === false ? '?' : '&') . 'domain=' . rawurlencode($domain);
        }
        return $url;
    }
}
